<?php
namespace App\Http\Controllers\QCDB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use DB;
use Config;
use Yajra\Datatables\Datatables;
use Dompdf\Dompdf;
use Carbon\Carbon;
use App\FGS;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth; #Auth facade
use Excel;

class FGSController extends Controller
{
		protected $mysql;
		protected $mssql;
		protected $common;

		public function __construct()
		{
				$this->middleware('auth');
				$com = new CommonController;

				if (Auth::user() != null) {
						$this->mysql = $com->userDBcon(Auth::user()->productline,'mysql');
						$this->mssql = $com->userDBcon(Auth::user()->productline,'mssql');
						$this->common = $com->userDBcon(Auth::user()->productline,'common');
				} else {
						return redirect('/');
				}
		}

		public function getFGS()
		{
			$common = new CommonController;
			if(!$common->getAccessRights(Config::get('constants.MODULE_CODE_FGSDB')
																	, $userProgramAccess))
			{
					return redirect('/home');
			}
			else
			{
				$tableData = DB::connection($this->mysql)->table("fgs")->get();
					return view('qcdb.fgs',['userProgramAccess' => $userProgramAccess,
			'tableData'=> $tableData]);
			}
		}

		public function getfgsYPICSrecords(Request $request)
		{
			$msrecords = DB::connection($this->mssql)
							->select("SELECT R.SORDER as PO,
												R.CODE as devicecode,
												H.NAME as DEVNAME,
												SUM(R.KVOL) as POQTY
								FROM XRECE as R
								LEFT JOIN XHEAD as H ON R.CODE = H.CODE
								WHERE R.SORDER = '".$request->pono."'
								GROUP BY R.SORDER,
												R.CODE,
												H.NAME");

			return $msrecords;
		}

		public function searchby(Request $request)
		{
			$datefrom = $request->datefrom;
			$dateto = $request->dateto;
			$pono = $request->pono;

			if($pono == ""){
				$table = DB::connection($this->mysql)->table('fgs')
								->whereBetween('date', [$datefrom,$dateto])
								->get();   
			}else{
				$table = DB::connection($this->mysql)->table('fgs')
								->whereBetween('date', [$datefrom,$dateto])
								->where('po_no',$pono)  
								->get();   
			}
			if($datefrom == "" && $dateto == ""){
				$table = DB::connection($this->mysql)->table('fgs')
								->where('po_no',$pono)
								->get();     
			}
		
			return $table;
		}

		public function FGSgetrows(Request $req)
		{
				$tableData = DB::connection($this->mysql)->table('fgs')
												->orderBy('id','desc')
												->select([
														'id',
														'po_no',
														'date',
														'device_name',
														'qty',
														'total_num_of_lots',
														'dbcon'
												]);

				if ($req->mode == 'group') {
						$date = '';
						$g1 = '';
						$g2 = '';
						$g3 = '';

						if ($req->datefrom !=="") {
								$date=" AND date BETWEEN '".$this->convertDate($req->datefrom,'Y-m-d')."' AND '".$this->convertDate($req->dateto,'Y-m-d')."'";
						}

						if($req->g1 !==""){
								$tableData = DB::connection($this->mysql)->table('fgs')
														->whereRaw("1=1".$date)
														->orderBy('id','desc')
														->groupBy($req->g1)
														->select([
																'id',
																'po_no',
																'date',
																'device_name',
																DB::raw("SUM(qty) as qty"),
																DB::raw("SUM(total_num_of_lots) as total_num_of_lots"),
																'dbcon'
														]);
						}
						if($req->g2 !==""){
								$tableData = DB::connection($this->mysql)->table('fgs')
														->whereRaw("1=1".$date)
														->orderBy('id','desc')
														->groupBy($req->g1,$req->g2)
														->select([
																'id',
																'po_no',
																'date',
																'device_name',
																DB::raw("SUM(qty) as qty"),
																DB::raw("SUM(total_num_of_lots) as total_num_of_lots"),
																'dbcon'
														]);
						}
						if($req->g3 !==""){
								$tableData = DB::connection($this->mysql)->table('fgs')
														->whereRaw("1=1".$date)
														->orderBy('id','desc')
														->groupBy($req->g1,$req->g2,$req->g3)
														->select([
																'id',
																'po_no',
																'date',
																'device_name',
																DB::raw("SUM(qty) as qty"),
																DB::raw("SUM(total_num_of_lots) as total_num_of_lots"),
																'dbcon'
														]);
						}
				}

				if ($req->mode == 'search') {
						$date="";
						$pono="";

						if ($req->datefrom !== "") {
								$date=" AND date BETWEEN '".$this->convertDate($req->datefrom,'Y-m-d')."' AND '".$this->convertDate($req->dateto,'Y-m-d')."'";
						}

						if ($req->pono !== "") {
								$date=" AND po_no = '".$req->pono."'";
						}

						$tableData = DB::connection($this->mysql)->table('fgs')
												->whereRaw("1=1".$date.$pono)
												->orderBy('id','desc')
												->select([
														'id',
														'po_no',
														'date',
														'device_name',
														'qty',
														'total_num_of_lots',
														'dbcon'
												]);
				}

				return Datatables::of($tableData)
								->editColumn('id', function($data) {
									return $data->id;
								})
								->addColumn('action', function($data) {
									return '<button type="button" name="edit-task" class="btn btn-sm btn-primary edit-task" value="'.$data->id.'|'.$data->po_no.'|'.$data->date.'|'.$data->device_name.'|'.$data->qty.'|'.$data->total_num_of_lots.'|'.$data->dbcon.'"><i class="fa fa-edit"></i></button>';
								})
								->editColumn('date', function($data) {
									return $data->date;
								})
								->editColumn('po_no', function($data) {
									return $data->po_no;
								})
								->editColumn('device_name', function($data) {
									return $data->device_name;
								})
								->editColumn('qty', function($data) {
									return $data->qty;
								})

								->editColumn('total_num_of_lots', function($data) {
									return $data->total_num_of_lots;
								})
								->make(true);
		}

		private function convertDate($date,$format)
		{
				$time = strtotime($date);
				$newdate = date($format,$time);
				return $newdate;
		}


		public function getFGSreport(Request $request)
		{ 
			$data = json_decode($request->data);
			$date_inspected = $data->date_inspected;
			$pono = $data->pono;
			$device_name = $data->device_name;
			$qty = $data->qty;
			$total_lots = $data->total_lots;  
			$status = $data->status;
			$searchpono = $data->searchpono;
			$datefrom = $data->datefrom;
			$dateto = $data->dateto;

			if($status == "SEARCH"){
				$html1 = '<style>
										#data {
											border-collapse: collapse;
											width: 100%;
											font-size:10px;
											text-align:center;
										}

										#data thead td {
											border: 1px solid black;
											text-align: center;
										}

										#data tbody td {
											border-bottom: 1px solid black;
											alignment:center;
										}

										#info {
											width: 100%;
											font-size:10px;
										}

										#info thead td {
											text-align: center;
										}
										#date{
												text-align:right;
										}
										.label
										{
											font-size:10px;
										}
									</style>
									<table id="info">
										<thead>
											<tr bgcolor="#ADD8E6">
													<td colspan="6">
															<h2>TS OQC FGS</h2>
													</td>
											</tr>
									</thead>                  
									</table>
											<br>
											<table id="data" border="1">
												<thead>
													<tr>
															<td>Date Inspected</td>
															<td>P.O #</td>
															<td>Series Name</td>
															<td>Quantity</td>
															<td>Total No. of Lots</td>
													</tr>
												</thead>
											<tbody>';

				$html3 = '</tbody>
									</table>';
			 
				$html2 = '';
		
				$html4 = '<table width="100%">
											<tr>
												
													<td class="label">Date:</td>
													<td class="label">'.Carbon::now().'</td>
											</tr>
									</table>';
				foreach ($pono as $key => $po) {
					 $html2 .= '<tr>
								<td>'.$date_inspected[$key].'</td>
								<td>'.$pono[$key].'</td>
								<td>'.$device_name[$key].'</td>
								<td>'.$qty[$key].'</td>
								<td>'.$total_lots[$key].'</td>
							 
						</tr>';
				}

				$html_final = $html1.$html2.$html3.$html4;
				$dompdf = new Dompdf();
				$dompdf->loadHtml($html_final);
				$dompdf->setPaper('letter', 'landscape');
				$dompdf->render();
				$dompdf->stream('FGS_'.Carbon::now().'.pdf'); 
			} else {
				$html1 = '<style>
										#data {
											border-collapse: collapse;
											width: 100%;
											font-size:10px;
											text-align:center;
										}

										#data thead td {
											border: 1px solid black;
											text-align: center;
										}

										#data tbody td {
											border-bottom: 1px solid black;
											alignment:center;
										}

										#info {
											width: 100%;
											font-size:10px;
										}

										#info thead td {
											text-align: center;
										}
										#date{
												text-align:right;
										}
										.label
										{
											font-size:10px;
										}
									</style>
									<table id="info">
										<thead>
											<tr bgcolor="#ADD8E6">
													<td colspan="6">
															<h2>TS OQC FGS</h2>
													</td>
											</tr>
									</thead>                  
									</table>
											<br>
											<table id="data" border="1">
												<thead>
													<tr>
															<td>Date Inspected</td>
															<td>P.O #</td>
															<td>Series Name</td>
															<td>Quantity</td>
															<td>Total No. of Lots</td>
													</tr>
												</thead>
											<tbody>';

				$html3 = '</tbody>
									</table>';
			 
				$html2 = '';

				$html4 = '<table width="100%">
											<tr>
													
													<td class="label">Date:</td>
													<td class="label">'.Carbon::now().'</td>
											</tr>
									</table>';
				foreach ($pono as $key => $po) {
					 $html2 .= '<tr>
								<td>'.$date_inspected[$key].'</td>
								<td>'.$pono[$key].'</td>
								<td>'.$device_name[$key].'</td>
								<td>'.$qty[$key].'</td>
								<td>'.$total_lots[$key].'</td>
							 
						</tr>';
				}

				$html_final = $html1.$html2.$html3.$html4;
				$dompdf = new Dompdf();
				$dompdf->loadHtml($html_final);
				$dompdf->setPaper('letter', 'landscape');
				$dompdf->render();
				$dompdf->stream('FGS_'.Carbon::now().'.pdf');
			}
		}

		public function getFGSreportexcel(Request $request)
		{ 
			$data = json_decode($request->data);
			$status = $data->status;

			if($status == "SEARCH"){
				 try
				 { 
						$dt = Carbon::now();
						$date = substr($dt->format('Ymd'), 2);
							
						Excel::create('FGS'.$date, function($excel) use($request)
						{
							 $excel->sheet('Sheet1', function($sheet) use($request)
							 {
									$datefrom = $request->datefrom;
									$dateto = $request->dateto;
									$dt = Carbon::now();
									$date = $dt->format('m/d/Y');
			 
									$sheet->setCellValue('A1', 'TS OQC FGS');
									$sheet->mergeCells('A1:G1');

									$sheet->cell('B3',"Date");
									$sheet->cell('C3',"P.O. Number");
									$sheet->cell('D3',"Device Name");
									$sheet->cell('E3',"Quantity");
									$sheet->cell('F3',"Total No. of Lots");

									$sheet->setHeight(array(
										 1=>30,
										 3=>20
									));
									$sheet->row(1, function ($row) {
										 $row->setFontFamily('Calibri');
										 $row->setBackground('#ADD8E6');
										 $row->setFontSize(15);
										 $row->setAlignment('center');
									});
									$sheet->row(3, function ($row) {
										 $row->setFontFamily('Calibri');
										 $row->setBackground('#ADD8E6');
										 $row->setFontSize(10);
										 $row->setAlignment('center');
									});
									$sheet->setStyle(array(
										 'font' => array(
										 'name'  =>  'Calibri',
										 'size'  =>  10
										 )
									));
									$data = json_decode($request->data);
									$date_inspected = $data->date_inspected;
									$pono = $data->pono;
									$device_name = $data->device_name;
									$qty = $data->qty;
									$total_lots = $data->total_lots;  
									$searchpono = $data->searchpono;

									$row = 4;
									$field = DB::connection($this->mysql)->table('fgs')->get();
									foreach ($pono as $key => $val) {
										 $sheet->cell('B'.$row, $date_inspected[$key]);
										 $sheet->cell('C'.$row, $pono[$key]);
										 $sheet->cell('D'.$row, $device_name[$key]);
										 $sheet->cell('E'.$row, $qty[$key]);
										 $sheet->cell('F'.$row, $total_lots[$key]);
										
										 $sheet->row($row, function ($row) {
												$row->setFontFamily('Calibri');
												$row->setFontSize(10);
												$row->setAlignment('center');
										 });
										 $sheet->setHeight($row,20);
										 $row++;
									}
									$sheet->row($row, function ($row) {
										 $row->setFontFamily('Calibri');
										 $row->setFontSize(10);
										 $row->setAlignment('center');
									});
									$sheet->setHeight($row,20);
								 
									$sheet->cell('B'.$row, "Date:");
									$sheet->cell('C'.$row, Carbon::now());
							 });

						})->download('xls');
				 } catch (Exception $e) {
						return redirect(url('/fgs'))->with(['err_message' => $e]);
				 }       
			}else{
				 try
				 { 
						$dt = Carbon::now();
						$date = substr($dt->format('Ymd'), 2);
							
						Excel::create('FGS'.$date, function($excel) use($request)
						{
							 $excel->sheet('Sheet1', function($sheet) use($request)
							 {
									$datefrom = $request->datefrom;
									$dateto = $request->dateto;
									$dt = Carbon::now();
									$date = $dt->format('m/d/Y');
			 
									$sheet->setCellValue('A1', 'TS OQC FGS');
									$sheet->mergeCells('A1:G1');

									$sheet->cell('B3',"Date");
									$sheet->cell('C3',"P.O. Number");
									$sheet->cell('D3',"Device Name");
									$sheet->cell('E3',"Quantity");
									$sheet->cell('F3',"Total No. of Lots");

									$sheet->setHeight(array(
										 1=>30,
										 3=>20
									));
									$sheet->row(1, function ($row) {
										 $row->setFontFamily('Calibri');
										 $row->setBackground('#ADD8E6');
										 $row->setFontSize(15);
										 $row->setAlignment('center');
									});
									$sheet->row(3, function ($row) {
										 $row->setFontFamily('Calibri');
										 $row->setBackground('#ADD8E6');
										 $row->setFontSize(10);
										 $row->setAlignment('center');
									});
									$sheet->setStyle(array(
										 'font' => array(
										 'name'  =>  'Calibri',
										 'size'  =>  10
										 )
									));
									$data = json_decode($request->data);
									$date_inspected = $data->date_inspected;
									$pono = $data->pono;
									$device_name = $data->device_name;
									$qty = $data->qty;
									$total_lots = $data->total_lots;  
									$searchpono = $data->searchpono;

									$row = 4;
									$field = DB::connection($this->mysql)->table('fgs')->get();
									foreach ($pono as $key => $val) {
										 $sheet->cell('B'.$row, $date_inspected[$key]);
										 $sheet->cell('C'.$row, $pono[$key]);
										 $sheet->cell('D'.$row, $device_name[$key]);
										 $sheet->cell('E'.$row, $qty[$key]);
										 $sheet->cell('F'.$row, $total_lots[$key]);
										
										 $sheet->row($row, function ($row) {
												$row->setFontFamily('Calibri');
												$row->setFontSize(10);
												$row->setAlignment('center');
										 });
										 $sheet->setHeight($row,20);
										 $row++;
									}
									$sheet->row($row, function ($row) {
										 $row->setFontFamily('Calibri');
										 $row->setFontSize(10);
										 $row->setAlignment('center');
									});
									$sheet->setHeight($row,20);
							
									$sheet->cell('B'.$row, "Date:");
									$sheet->cell('C'.$row, Carbon::now());
							 });

						})->download('xls');
				 } catch (Exception $e) {
						return redirect(url('/fgs'))->with(['err_message' => $e]);
				 }       
			}   
		}

		public function getFGSData()
		{
				$data = FGS::all();
				return Datatables::of($data)->make(true);
		}

		public function fgsSave(Request $request)
		{
				$field = $request->data;
				/*return $field;*/
				$status = $field['status'];
				if($status == "ADD"){
						DB::connection($this->mysql)->table('fgs')
								->insert([
									'date' => $this->convertDate($field['date'],'Y-m-d'),
									'po_no' => $field['pono'],
									'device_name' => $field['device'],
									'qty' => $field['quantity'],
									'total_num_of_lots' => $field['tlots'],
									'dbcon' => 'TS',
									'created_at' => date('Y-m-d H:i:s'),
									'updated_at' => date('Y-m-d H:i:s')
								]);  
				}
				if($status == "EDIT"){
						DB::connection($this->mysql)->table('fgs')
								->where('id','=',$field['id'])
								->update(array(
									'date' => $this->convertDate($field['date'],'Y-m-d'),
									'po_no' => $field['pono'],
									'device_name' => $field['device'],
									'qty' => $field['quantity'],
									'total_num_of_lots' => $field['tlots'],
									'dbcon' => 'TS',
									'updated_at' => date('Y-m-d H:i:s')
								));
				}
				
		}

		public function fgsDelete(Request $request)
		{  
				$tray = $request->tray;
				$traycount = $request->traycount;  
				/*return $tray;  */
				if($traycount > 0){
					$ok = DB::connection($this->mysql)->table('fgs')->wherein('id',$tray)->delete();
			
					if ($ok) {
							$msg = "Successfully deleted selected records.";
							return redirect('/fgs')->with(['message'=>$msg]);
					} else {
							$msg = "No Record Exists.";
							return redirect('/fgs')->with(['err_message'=>$msg]);
					}
				} 
		}

		public function fgsdbgroupby(Request $request)
		{        
			/*$data = array_filter($request->input('data'));*/
			//$fields = "'".implode("','",$data)."'";
			$data = $request->data;
			$datefrom = $request->data['datefrom'];
			$dateto = $request->data['dateto'];
			$g1 = $request->data['g1'];
			$g2 = $request->data['g2'];
			$g3 = $request->data['g3'];
			$field='';
			if($g1){
					if($datefrom == "" && $dateto == ""){
							$field = DB::connection($this->mysql)->table('fgs')
							->select('date','po_no','device_name',DB::raw("SUM(qty) as qty"),DB::raw("SUM(total_num_of_lots) as total_num_of_lots"))
							->groupBy($g1)
							->get();    
					} else {
							$field = DB::connection($this->mysql)->table('fgs')
							->whereBetween('date',[$datefrom, $dateto])
							->select('date','po_no','device_name',DB::raw("SUM(qty) as qty"),DB::raw("SUM(total_num_of_lots) as total_num_of_lots"))
							->groupBy($g1)
							->get();        
					}    
			}
			if($g2){
					if($datefrom == "" && $dateto == ""){
							$field = DB::connection($this->mysql)->table('fgs')
							->select('date','po_no','device_name',DB::raw("SUM(qty) as qty"),DB::raw("SUM(total_num_of_lots) as total_num_of_lots"))
							->groupBy($g1,$g2)
							->get();   
					} else {
							$field = DB::connection($this->mysql)->table('fgs')
							->whereBetween('date',[$datefrom, $dateto])
							->select('date','po_no','device_name',DB::raw("SUM(qty) as qty"),DB::raw("SUM(total_num_of_lots) as total_num_of_lots"))
							->groupBy($g1,$g2)
							->get();       
					}
					
			}
			if($g3){
					if($datefrom =="" && $dateto == ""){
							$field = DB::connection($this->mysql)->table('fgs')
							->select('date','po_no','device_name',DB::raw("SUM(qty) as qty"),DB::raw("SUM(total_num_of_lots) as total_num_of_lots"))
							->groupBy($g1,$g2,$g3)
							->get();       
					} else {
							$field = DB::connection($this->mysql)->table('fgs')
							->whereBetween('date',[$datefrom, $dateto])
							->select('date','po_no','device_name',DB::raw("SUM(qty) as qty"),DB::raw("SUM(total_num_of_lots) as total_num_of_lots"))
							->groupBy($g1,$g2,$g3)
							->get();      
					}    
			}
			
				return $field;
		}
	 
		public function fgsdbselectgroupby1(Request $request)
		{        
			$g1 = $request->data;
			$table = DB::connection($this->mysql)->table('fgs')
							->select($g1)
							->distinct()
							->get();

			return $table;
		}

}
