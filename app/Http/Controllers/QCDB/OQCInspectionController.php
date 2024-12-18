<?php
namespace App\Http\Controllers\QCDB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use DB;
use Config;
use App\OQCInspection;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Auth; #Auth facade
use Illuminate\Http\Request;
use App\Http\Requests;
use Dompdf\Dompdf;
use PDF;
use Carbon\Carbon;
use Excel;

class OQCInspectionController extends Controller
{
	protected $mysql;
	protected $mssql;
	protected $common;
	protected $com;
	protected $is_loggedin = false;

	//OQC INSPECTION _CONSTRUCT
	public function __construct()
	{
		try {
			$this->com = new CommonController;
			//$this->middleware('auth');
			$user = Auth::user();

			if (is_null($user) && !isset($_GET['username'])) {
				$this->middleware('auth');
				return redirect('/home');
			}

			$username = (isset($_GET['username']))? $_GET['username'] : Auth::user()->user_id;
			

			$db = DB::connection('common')->table('users')->select("actual_password")->where('user_id',$username)->first();

			if (is_null($db)) {
				Auth::logout();
				session(['productline_error' => "Your credentials did not matched."]);
				return redirect('/');
			}

			if (is_null($user)) {
				if (Auth::attempt(['user_id' => $username, 'password' => $db->actual_password])) {
					$this->is_loggedin = true;
				}
			}

			if (Auth::user() != null) {
				
				$this->mysql = $this->com->userDBcon(Auth::user()->productline,'mysql');
				$this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
				$this->common = $this->com->userDBcon(Auth::user()->productline,'common');
			} else {
				return redirect('/home');
			}
		} catch (\Throwable $th) {
			$this->middleware('auth');
			return redirect('/home');
		}
		
	}

	//OQC INSPECTION INDEX
	// public function index()
	// {
	// 	if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_OQCDB')
	// 								, $userProgramAccess))
	// 	{
	// 		return redirect('/home');
	// 	}
	// 	else
	// 	{
	// 		$po_no = (isset($_GET['po_no']))? $_GET['po_no'] : NULL;
	// 		$po_qty = (isset($_GET['po_qty']))? $_GET['po_qty'] : NULL;
	// 		$lot_no = (isset($_GET['lot_no']))? $_GET['lot_no'] : NULL;
	// 		$ww = (isset($_GET['ww']))? $_GET['ww'] : NULL;
	// 		$app_date_time = (isset($_GET['app_date_time']))? $_GET['app_date_time'] : NULL;

	// 		$is_supervisor = Auth::user()->is_supervisor;
	// 		return view('qcdb.oqcinspection',[	
	// 				'userProgramAccess' => $userProgramAccess,
	// 				'is_supervisor' => $is_supervisor,
	// 				'is_loggedin' => $this->is_loggedin,
	// 				'po_no' => $po_no,
	// 				'po_qty' => $po_qty,
	// 				'lot_no' => $lot_no,
	// 				'ww' => $ww,
	// 				'app_date_time' => $app_date_time
	// 			]);
	// 	}
	// }

	public function index()
	{
		try {
			$access = $this->com->getAccessRights(Config::get('constants.MODULE_CODE_OQCDB'), $userProgramAccess);

			if(!$access)
			{
				return redirect('/home');
			}
			else
			{
				$po_no = (isset($_GET['po_no']))? $_GET['po_no'] : NULL;
				$po_qty = (isset($_GET['po_qty']))? $_GET['po_qty'] : NULL;
				$lot_no = (isset($_GET['lot_no']))? $_GET['lot_no'] : NULL;
				$ww = (isset($_GET['ww']))? $_GET['ww'] : NULL;
				$app_date_time = (isset($_GET['app_date_time']))? $_GET['app_date_time'] : NULL;

				$is_supervisor = Auth::user()->is_supervisor;
				return view('qcdb.oqcinspection',[	
						'userProgramAccess' => $userProgramAccess,
						'is_supervisor' => $is_supervisor,
						'is_loggedin' => $this->is_loggedin,
						'po_no' => $po_no,
						'po_qty' => $po_qty,
						'lot_no' => $lot_no,
						'ww' => $ww,
						'app_date_time' => $app_date_time
					]);
			}
		} catch (\Exception $th) {
			Auth::logout();
			session(['productline_error' => "Your credentials did not matched."]);
			return redirect('/');
		}
		
	}


	//INITIAL DATA
	public function initData()
	{
		$displaymod = DB::connection($this->mysql)->table('oqc_inspections_mod')->get();
		$countrecords = DB::connection($this->mysql)->table('oqc_inspections')->count();
		$family = $this->com->getDropdownByName('Family');
		//$family = $this->com->getDropdownByName('Family');
		$tofinspection = $this->com->getDropdownByName('Type of Inspection');
		$sofinspection = $this->com->getDropdownByName('Severity of Inspection');
		$inspectionlvl = $this->com->getDropdownByName('Inspection Level');
		$assemblyline = $this->com->getDropdownByName('Assembly Line');
		$aql = $this->com->getDropdownByName('AQL');
		$shift = $this->com->getDropdownByName('Shift');
		$submission = $this->com->getDropdownByName('Submission');
		$shift = $this->com->getDropdownByName('Shift');
		$mod = $this->com->getDropdownByName('Mode of Defect - OQC Inscpection Molding');

		return $data = [
			'oqcmod'=>$displaymod,
			'families' =>$family,
			'tofinspections' => $tofinspection,
			'sofinspections' => $sofinspection,
			'inspectionlvls' =>$inspectionlvl,
			'aqls' =>$aql,
			'shifts' =>$shift,
			'submissions'=>$submission,
			'mods'=>$mod,
			'assemblyline'=>$assemblyline,
			'count'=>$countrecords
		];
	}

	//OQC INSPECTION GET MODOQC
	public function getmodoqc(Request $request)
	{
		$pono = $request->pono;
		$table = DB::connection($this->mysql)->table('oqc_inspections_mod')->select('mod')->where('pono',$pono)->get();
		return $table;
	}

	//OQC INSPECTION DATA TABLE
	public function OQCDataTable(Request $req)
	{
		$output = '';
		$po = '';
		$date = '';
		$select = [
					'id',
					'fy',
					'ww',
					'date_inspected',
					'device_name',
					'time_ins_from',
					'time_ins_to',
					'submission',
					'lot_qty',
					'sample_size',
					'num_of_defects',
					'lot_no',
					'po_qty',
					'judgement',
					'inspector',
					'remarks',
					'type',
					'shift',
					'assembly_line',
					'app_date',
					'app_time',
					'prod_category',
					'po_no',
					'customer',
					'family',
					'type_of_inspection',
					'severity_of_inspection',
					'inspection_lvl',
					'aql',
					'accept',
					'reject',
					'coc_req',
					'lot_inspected',
					'lot_accepted',
					'gauge',
                   'accessory',
                   'yd_label_req',
                   'chs_coating',
					'workweek'
				];

		if ($req->type == 'search') {
			if (!empty($req->search_po) || $req->pono !== '') {
				$po = " AND po_no = '".$req->search_po."'";
			}

			if ($req->search_from !== '' || !empty($req->search_from)) {
				$date = " AND date_inspected BETWEEN '".$this->com->convertDate($req->search_from,'Y-m-d')."' 
						AND '".$this->com->convertDate($req->search_to,'Y-m-d')."'";
			}

			$query = DB::connection($this->mysql)->table('oqc_inspections')
						->whereRaw("1=1".$po.$date)
						->orderBy('id','desc')
						->select($select);
		} else {
			$query = DB::connection($this->mysql)->table('oqc_inspections')
					->orderBy('id','desc')
					->select($select);
		}

		return Datatables::of($query)
						->editColumn('id', function($data) {
							return $data->id;
						})
						->editColumn('num_of_defects', function($data) {
							$num_of_defects = DB::connection($this->mysql)
												->select("SELECT SUM(b.qty) as num_of_defects
														from oqc_inspections as a
														inner join oqc_inspections_mod as b
														on a.lot_no = b.lotno and a.po_no = b.pono
														where a.po_no = '".$data->po_no."'
														and a.lot_no = '".$data->lot_no."'
														and a.submission = '".$data->submission."'
														and b.deleted = 0
														and a.judgement = 'Reject' LIMIT 1");
							if (count($num_of_defects) > 0) {
								return (is_null($num_of_defects[0]->num_of_defects))? 0 : $num_of_defects[0]->num_of_defects;
							}

							return 0;
						})
						->addColumn('action', function($data) {
							$num_of_defects = DB::connection($this->mysql)
												->select("SELECT SUM(b.qty) as num_of_defects
														from oqc_inspections as a
														inner join oqc_inspections_mod as b
														on a.lot_no = b.lotno and a.po_no = b.pono
														where a.po_no = '".$data->po_no."'
														and a.lot_no = '".$data->lot_no."'
														and a.submission = '".$data->submission."' 
														and b.deleted = 0
														and a.judgement = 'Reject' LIMIT 1");

							return '<button type="button" class="btn btn-sm btn-primary btn_edit_inspection" data-id="'.$data->id.'" 
									data-assembly_line="'.$data->assembly_line.'" data-app_date="'.$data->app_date.'" 
									data-app_time="'.$data->app_time.'" data-lot_no="'.$data->lot_no.'" 
									data-prod_category="'.$data->prod_category.'" data-po_no="'.$data->po_no.'" 
									data-device_name="'.$data->device_name.'" data-customer="'.$data->customer.'" 
									data-po_qty="'.$data->po_qty.'" data-family="'.$data->family.'" 
									data-type_of_inspection="'.$data->type_of_inspection.'" data-severity_of_inspection="'.$data->severity_of_inspection.'" 
									data-inspection_lvl="'.$data->inspection_lvl.'" data-aql="'.$data->aql.'" 
									data-accept="'.$data->accept.'" data-reject="'.$data->reject.'" 
									data-date_inspected="'.$data->date_inspected.'" data-ww="'.$data->ww.'" 
									data-fy="'.$data->fy.'" data-shift="'.$data->shift.'" 
									data-time_ins_from="'.$data->time_ins_from.'" data-time_ins_to="'.$data->time_ins_to.'" 
									data-inspector="'.$data->inspector.'" data-submission="'.$data->submission.'" 
									data-coc_req="'.$data->coc_req.'" data-judgement="'.$data->judgement.'" 
									data-lot_qty="'.$data->lot_qty.'" data-sample_size="'.$data->sample_size.'" 
									data-lot_inspected="'.$data->lot_inspected.'" data-lot_accepted="'.$data->lot_accepted.'" 
									data-gauge="'.$data->gauge.'" data-accessory="'.$data->accessory.'"
									data-yd_label_req="'.$data->yd_label_req.'" data-chs_coating="'.$data->chs_coating.'" 
									data-type="'.$data->type.'" data-workweek="'.$data->workweek.'"
                                   >'.
								'   <i class="fa fa-edit"></i> '.
							'</button>';
							
							
							
						})
						->addColumn('fyww', function($data) {
							return $data->fy.' - '.$data->ww;
						})
						->addColumn('mod', function($data) use($req) {
							$mode_of_defects = [];
							if ($data->judgement == 'Accept') {
								return 'NDF';
							}else{
								if($req->report_status == "GROUPBY"){
									$table = DB::connection($this->mysql)
												->select("SELECT a.po_no as pono,
																(GROUP_CONCAT(b.mod1 SEPARATOR ' , ')) AS mod1,
																(GROUP_CONCAT(a.lot_no SEPARATOR ' , ')) AS lot_no,
																a.submission,
																b.qty
														from oqc_inspections as a
														inner join oqc_inspections_mod as b
														on a.lot_no = b.lotno and a.po_no = b.pono
														and a.judgement = 'Reject'
														where b.deleted = '0'
														group by a.po_no, a.submission, b.qty");
								
								} else {
									$table = DB::connection($this->mysql)
												->select("SELECT a.po_no,
																b.mod1,
																a.lot_no,
																a.submission
														from oqc_inspections as a
														left join oqc_inspections_mod as b
														on a.lot_no = b.lotno and a.po_no = b.pono
														where a.po_no = '".$data->po_no."'
														and a.lot_no = '".$data->lot_no."'
														and a.submission = '".$data->submission."'
														and a.judgement = 'Reject'
														and b.deleted = '0'");
								}

								foreach ($table as $key => $tb) {
									array_push($mode_of_defects, $tb->mod1);
								}

								$mods = implode(',', $mode_of_defects);

								return $mods;
							}
						})
						->make(true);
	}

	//OQC INSPECTION GET PO DETAILS
	public function getPOdetails(Request $req)
	{
		if (!empty($req->po)) {
			if ($req->is_probe > 0) {
				$msrecords = DB::connection($this->mssql)
							->select("SELECT R.SORDER as po,
											HK.CODE as device_code,
											H.NAME as device_name,
											R.CUST as customer_code,
											C.CNAME as customer_name,
											HK.KVOL as po_qty,
											I.BUNR
									FROM XRECE as R
									LEFT JOIN XSLIP as S on R.SORDER = S.SEIBAN
									LEFT JOIN XHIKI as HK on S.PORDER  = HK.PORDER
									LEFT JOIN XHEAD as H ON HK.CODE = H.CODE
									LEFT JOIN XITEM as I ON HK.CODE = I.CODE
									LEFT JOIN XCUST as C ON R.CUST = C.CUST
									WHERE R.SORDER like '".$req->po."%'
									AND I.BUNR = 'PROBE'");
			} else {
				$msrecords = DB::connection($this->mssql)
								->select("SELECT R.SORDER as po,
											   R.CODE as device_code,
											   H.[NAME] as device_name,
											   R.CUST as customer_code,
											   C.CNAME as customer_name,
											   SUM(R.KVOL) as po_qty
										FROM XRECE as R
										LEFT JOIN XHEAD as H
										ON R.CODE = H.CODE
										LEFT JOIN XCUST as C
										ON R.CUST = C.CUST
										WHERE R.SORDER like '".$req->po."%'
										GROUP BY R.SORDER,
												R.CODE,
												H.[NAME],
												R.CUST,
												C.CNAME");
								// ->table('XRECE as R')
								// ->leftJoin('XHEAD as H','R.CODE','=','H.CODE')
								// ->leftJoin('XCUST as C','R.CUST','=','C.CUST')
								// ->where('R.SORDER','like',$req->po."%")
								// ->select(DB::raw('R.SORDER as po'),
								//         DB::raw('R.CODE as device_code'),
								//         DB::raw('H.NAME as device_name'),
								//         DB::raw('R.CUST as customer_code'),
								//         DB::raw('C.CNAME as customer_name'),
								//         DB::raw('SUM(R.KVOL) as po_qty'))
								// ->groupBy('R.SORDER',
								//         'R.CODE',
								//         'H.NAME',
								//         'R.CUST',
								//         'C.CNAME')
								// ->get();

			}
		}

		$this->utf8_encode_deep($msrecords);

		return $msrecords;
	}

	//utf8_encode_deep
	private function utf8_encode_deep(&$input) 
	{
		if (is_string($input)) {
			//$input = utf8_encode($input);
			mb_convert_encoding($input,"UTF-8","SJIS");
		} else if (is_array($input)) {
			foreach ($input as &$value) {
				if (is_object($value)) {
					$vals = array_keys(get_object_vars($value));

					foreach ($vals as $val) {
						mb_convert_encoding($val,"UTF-8","SJIS");
					}
				} else {
					mb_convert_encoding($value,"UTF-8","SJIS");
				}

			}

			unset($value);
		} else if (is_object($input)) {
			$vars = array_keys(get_object_vars($input));

			foreach ($vars as $var) {
				mb_convert_encoding($var,"UTF-8","SJIS");
			}
		}
	}

	//OQC INSPECTION UPLOAD SERIAL
	public function UploadSerial(Request $req)
	{
		$return_data = [
			'msg' => "Uploading Serial numbers has failed.",
			'status' => "failed",
			'serial_nos' => []
		];

		try {
			$file = $req->file('serial_nos');
			$data = [];
			$serial_nos = [];
			
            Excel::selectSheetsByIndex(0)->load($file, function ($reader) use(&$data)
            {
                $data = $reader->toArray();
            });

			foreach ($data as $key => $dt) {
				if ($dt['product_serial_number'] != null) {
					array_push($serial_nos, [
						'id' => -1,
						'serial_no' => $dt['product_serial_number'],
						'deleted' => 0
					]);
				}
			}

			if (count($serial_nos) > 0) {
				$return_data = [
					'msg' => "Serial numbers were successfully retrieved, Please click Save button to assign to this P.O..",
					'status' => "success",
					'serial_nos' => $serial_nos
				];
			}
		} catch (\Exception $th) {
			$return_data = [
				'msg' => "An Error has occurred while processing serial numbers.",
				'status' => "error",
				'serial_nos' => []
			];
		}

		return $return_data;
	}

	/*public function getProbeProduct(Request $req)
	{
		$msrecords = DB::connection($this->mssql)->table('XHEAD AS H')
						->leftJoin('XPRTS as R','H.CODE','=','R.CODE')
						->where('R.KCODE',$req->code)
						->select('R.CODE as devicecode',
								'H.NAME as DEVNAME',
								'R.KCODE as partcode')
						->get();
		return $msrecords;
	}*/

	//OQC INSPECTION GET PROBE PRODUCT
	public function getProbeProduct(Request $req)
	{
		$device_codes = [];
		foreach ($req->code as $key => $code) {
			array_push($device_codes,$code['device_code']);
		}

		$msrecords = DB::connection($this->mssql)->table('XHEAD AS H')
						->leftJoin('XPRTS as R','H.CODE','=','R.CODE')
						->whereIn('R.KCODE',$device_codes)
						->select('R.CODE as devicecode',
								'H.NAME as DEVNAME',
								'R.KCODE as partcode')
						->get();
		return $msrecords;
	}

	//OQC INSPECTION MODE DATA TABLE
	public function ModDataTable(Request $req)
	{
		$select = [
					'id',
					'pono',
					'device',
					'lotno',
					'submission',
					'mod1',
					'modid',
					'qty'
				];

		$query = DB::connection($this->mysql)->table('oqc_inspections_mod')
					->where('pono',$req->pono)
					// ->where('device',$req->device)
					// ->where('lotno',$req->lotno)
					->where('submission',$req->submission)
					->orderBy('id','desc')
					->select($select);
		return Datatables::of($query)
						->editColumn('id', function($data) {
							return $data->id;
						})
						->addColumn('action', function($data) {
							return '<button type="button" class="btn btn-sm btn-primary btn_edit_mod" data-id="'.$data->id.'"'.
									' data-pono="'.$data->pono.'" data-device="'.$data->device.'" data-lotno="'.$data->lotno.'"'.
									' data-submission="'.$data->submission.'" data-mod1="'.$data->mod1.'" data-qty="'.$data->qty.'"'.
									'data-ins-id="'.$data->modid.'">'.
										'<i class="fa fa-edit"></i>'.
									'</button>';
						})
						->make(true);
	}

	//VALIDATE INSPECTION
	private function validateInspection($req)
	{
		$rules = [
			'assembly_line' => 'required',
			'lot_no' => 'required',
			'app_date' => 'required',
			'app_time' => 'required',
			'prod_category' => 'required',
			'po_no' => 'required',
			'series_name' => 'required',
			'customer' => 'required',
			'po_qty' => 'required|numeric',
			'family' => 'required',
			'type_of_inspection' => 'required',
			'severity_of_inspection' => 'required',
			'inspection_lvl' => 'required',
			'aql' => 'required',
			'date_inspected' => 'required',
			'shift' => 'required',
			'time_ins_from' => 'required',
			'time_ins_to' => 'required',
			'submission' => 'required',
			'coc_req' => 'required',
			'judgement' => 'required',
			'lot_qty' => 'required|numeric',
			'sample_size' => 'required|numeric',
			'lot_inspected' => 'required',
			'lot_accepted' => 'required',
		];

		$msg = [
			'assembly_line.required' => 'Assembly Line is required.',
			'lot_no.required' => 'Lot number is required.',
			'app_date.required' => 'Application Date is required.',
			'app_time.required' => 'Application Time is required.',
			'prod_category.required' => 'Production Category is required.',
			'po_no.required' => 'P.O. number is required.',
			'series_name.required' => 'Series name is required.',
			'customer.required' => 'Customer is required.',
			'po_qty.required' => 'P.O. Quantity is required.',
			'po_qty.numeric' => 'P.O. Quantity must be numeric',
			'family.required' => 'Family is required.',
			'type_of_inspection.required' => 'Type of Inspection is required.',
			'severity_of_inspection.required' => 'Severity of Inspection is required.',
			'inspection_lvl.required' => 'Inspection Level is required.',
			'aql.required' => 'AQL is required.',
			'date_inspected.required' => 'Date Inspected is required.',
			'shift.required' => 'Shift is required.',
			'time_ins_from.required' => 'Time inspection from is required.',
			'time_ins_to.required' => 'Time inspection to is required.',
			'submission.required' => 'Submission is required.',
			'coc_req.required' => 'COC Requirements is required.',
			'judgement.required' => 'Judgement is required.',
			'lot_qty.required' => 'Lot Quantity is required.',
			'lot_qty.numeric' => 'Lot Quantity must be numeric',
			'sample_size.required' => 'Sample Size is required.',
			'sample_size.numeric' => 'Sample Size must be numeric',
			'lot_inspected.required' => 'Lot Inspected is required.',
			'lot_accepted.required' => 'Lot Accepted is required.',
		];

		return $this->validate($req, $rules, $msg);
	}

	//OQC INSPECTION SAVE INSPECTION
	public function saveInspection(Request $req)
	{
		$serial_nos = (json_decode($req->serial_no) == null)? []: json_decode($req->serial_no);
        $defects = (json_decode($req->defects) == null)? [] : json_decode($req->defects);
		$probe_lots = (json_decode($req->probe_lots) == null)? [] : json_decode($req->probe_lots);

		$this->validateInspection($req);

		$data = [
			'msg' => 'Saving failed.',
			'status' => 'failed'

		];

		try {
			if ($req->inspection_save_status == 'ADD') {
				$query = DB::connection($this->mysql)->table("oqc_inspections")
							->insertGetId([
										'assembly_line' => $req->assembly_line,
										'lot_no' => $req->lot_no,
										'app_date' => $this->com->convertDate($req->app_date,'Y-m-d'),
										'app_time' => $req->app_time,
										'prod_category' => $req->prod_category,
										'po_no' => $req->po_no,
										'workweek' => $req->workweek,
										'device_name' => $req->series_name,
										'customer' => $req->customer,
										'po_qty' => $req->po_qty,
										'family' => $req->family,
										'type_of_inspection' => $req->type_of_inspection,
										'severity_of_inspection' => $req->severity_of_inspection,
										'inspection_lvl' => $req->inspection_lvl,
										'aql' => $req->aql,
										'accept' => $req->accept,
										'reject' => $req->reject,
										'date_inspected' => $this->com->convertDate($req->date_inspected,'Y-m-d'),
										'ww' => $req->ww,
										'fy' => $req->fy,
										'time_ins_from' => $req->time_ins_from,
										'time_ins_to' => $req->time_ins_to,
										'shift' => $req->shift,
										'inspector' => $req->inspector,
										'submission' => $req->submission,
										'coc_req' => $req->coc_req,
										'judgement' => $req->judgement,
										'lot_qty' => $req->lot_qty,
										'sample_size' => $req->sample_size,
										'lot_inspected' => $req->lot_inspected,
										'lot_accepted' => $req->lot_accepted,
										'lot_rejected' => ($req->lot_accepted == 1)? 0 : 1,
										'num_of_defects' => ($req->lot_accepted == 1)? 0 : $req->no_of_defects,
										'remarks' => $req->remarks,
										'type'=> ($req->family == 'Probe Pin')? 'PROBE PIN':'IC SOCKET',
										'created_at' => Carbon::now(),
										'updated_at' => Carbon::now(),
										'create_user' => Auth::user()->user_id,
										'update_user' => Auth::user()->user_id,
										'gauge' => (int)$req->gauge,
										'accessory' => (int)$req->accessory,
										'yd_label_req' => (int)$req->yd_label_req,
										'chs_coating' => (int)$req->chs_coating,
									]);
	
				if (count($serial_nos) > 0) {
					foreach ($serial_nos as $key => $pl) {
						DB::connection($this->mysql)->table("oqc_serial_no")
							->insert([
								'oqc_id' => $query,
								'serial_no' => $pl->serial_no,
								'oqc_po' => $req->po_no,
								'created_at' => Carbon::now(),
								'updated_at' => Carbon::now(),
								 'create_user' => Auth::user()->user_id,
								 'update_user' => Auth::user()->user_id
							]);
					}
				}

				if (count($probe_lots) > 0) {
					foreach ($probe_lots as $key => $pbl) {
						DB::connection($this->mysql)->table("oqc_probe_lots")
							->insert([
								'oqc_id' => $query,
								'probe_lot' => $pbl->probe_lot,
								'qty' => $pbl->qty,
								'oqc_po' => $req->po_no,
								'created_at' => Carbon::now(),
								'updated_at' => Carbon::now(),
								 'create_user' => Auth::user()->user_id,
								 'update_user' => Auth::user()->user_id
							]);
					}
				}

				if (count($defects) > 0) {
                    foreach ($defects as $key => $mod) {
                        DB::connection($this->mysql)->table("oqc_inspections_mod")
                            ->insert([
                                'pono' => $req->po_no,
                                'device' => $req->series_name,
                                'lotno' => $req->lot_no,
                                'submission' => $req->submission,
                                'mod1' => $mod->mod1,
                                'qty' => $mod->qty,
                                'oqc_id' => $query,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                 'create_user' => Auth::user()->user_id,
                                 'update_user' => Auth::user()->user_id
                            ]);
                    }
                }
			} else {
				$query = DB::connection($this->mysql)->table("oqc_inspections")
							->where('id',$req->inspection_id)
							->update([
								'assembly_line' => $req->assembly_line,
								'lot_no' => $req->lot_no,
								'app_date' => $this->com->convertDate($req->app_date,'Y-m-d'),
								'app_time' => $req->app_time,
								'prod_category' => $req->prod_category,
								'po_no' => $req->po_no,
								'workweek' => $req->workweek,
								'device_name' => $req->series_name,
								'customer' => $req->customer,
								'po_qty' => $req->po_qty,
								'family' => $req->family,
								'type_of_inspection' => $req->type_of_inspection,
								'severity_of_inspection' => $req->severity_of_inspection,
								'inspection_lvl' => $req->inspection_lvl,
								'aql' => $req->aql,
								'accept' => $req->accept,
								'reject' => $req->reject,
								'date_inspected' => $this->com->convertDate($req->date_inspected,'Y-m-d'),
								'ww' => $req->ww,
								'fy' => $req->fy,
								'time_ins_from' => $req->time_ins_from,
								'time_ins_to' => $req->time_ins_to,
								'shift' => $req->shift,
								'inspector' => $req->inspector,
								'submission' => $req->submission,
								'coc_req' => $req->coc_req,
								'judgement' => $req->judgement,
								'lot_qty' => $req->lot_qty,
								'sample_size' => $req->sample_size,
								'lot_inspected' => $req->lot_inspected,
								'lot_accepted' => $req->lot_accepted,
								'lot_rejected' => ($req->lot_accepted == 1)? 0 : 1,
								'num_of_defects' => ($req->lot_accepted == 1)? 0 : $req->no_of_defects,
								'remarks' => $req->remarks,
								'type'=> ($req->family == 'Probe Pin')? 'PROBE PIN':'IC SOCKET',
								'updated_at' => Carbon::now(),
								'update_user' => Auth::user()->user_id,
								 'gauge' => (int)$req->gauge,
								 'accessory' => (int)$req->accessory,
								 'yd_label_req' => (int)$req->yd_label_req,
								 'chs_coating' => (int)$req->chs_coating,
							]);
							
				$query_group = DB::connection($this->mysql)->table("oqc_inspection_group")
							->where('id',$req->inspection_id)
							->update([
								'assembly_line' => $req->assembly_line,
								'lot_no' => $req->lot_no,
								'app_date' => $this->com->convertDate($req->app_date,'Y-m-d'),
								'app_time' => $req->app_time,
								'prod_category' => $req->prod_category,
								'po_no' => $req->po_no,
								'workweek' => $req->workweek,
								'device_name' => $req->series_name,
								'customer' => $req->customer,
								'po_qty' => $req->po_qty,
								'family' => $req->family,
								'type_of_inspection' => $req->type_of_inspection,
								'severity_of_inspection' => $req->severity_of_inspection,
								'inspection_lvl' => $req->inspection_lvl,
								'aql' => $req->aql,
								'accept' => $req->accept,
								'reject' => $req->reject,
								'date_inspected' => $this->com->convertDate($req->date_inspected,'Y-m-d'),
								'ww' => $req->ww,
								'fy' => $req->fy,
								'time_ins_from' => $req->time_ins_from,
								'time_ins_to' => $req->time_ins_to,
								'shift' => $req->shift,
								'inspector' => $req->inspector,
								'submission' => $req->submission,
								'coc_req' => $req->coc_req,
								'judgement' => $req->judgement,
								'lot_qty' => $req->lot_qty,
								'sample_size' => $req->sample_size,
								'lot_inspected' => $req->lot_inspected,
								'lot_accepted' => $req->lot_accepted,
								'lot_rejected' => ($req->lot_accepted == 1)? 0 : 1,
								'num_of_defects' => ($req->lot_accepted == 1)? 0 : $req->no_of_defects,
								'remarks' => $req->remarks,
								'type'=> ($req->family == 'Probe Pin')? 'PROBE PIN':'IC SOCKET',
								'updated_at' => Carbon::now(),
								'update_user' => Auth::user()->user_id,
								 'gauge' => (int)$req->gauge,
								 'accessory' => (int)$req->accessory,
								 'yd_label_req' => (int)$req->yd_label_req,
								 'chs_coating' => (int)$req->chs_coating,
							]);

				if (count($serial_nos) > 0) {
					foreach ($serial_nos as $key => $pl) {
						if ($pl->id == -1 || $pl->id == "-1") {
							DB::connection($this->mysql)->table("oqc_serial_no")
								->insert([
									'oqc_id' => $req->inspection_id,
									'serial_no' => $pl->serial_no,
									'oqc_po' => $req->po_no,
									'created_at' => Carbon::now(),
									'updated_at' => Carbon::now(),
									 'create_user' => Auth::user()->user_id,
									 'update_user' => Auth::user()->user_id
								]);
						} else {
							DB::connection($this->mysql)->table("oqc_serial_no")
								->where('id', $pl->id)
								->update([
									'oqc_id' => $req->inspection_id,
									'serial_no' => $pl->serial_no,
									'oqc_po' => $req->po_no,
									'deleted' => $pl->deleted,
									'updated_at' => Carbon::now(),
									'update_user' => Auth::user()->user_id
								]);
						}
						
					}
				}

				if (count($probe_lots) > 0) {
					foreach ($probe_lots as $key => $pbl) {
						if ($pbl->id == -1 || $pbl->id == "-1") {
							DB::connection($this->mysql)->table("oqc_probe_lots")
								->insert([
									'oqc_id' => $req->inspection_id,
									'probe_lot' => $pbl->probe_lot,
									'oqc_po' => $req->po_no,
									'qty' => $pbl->qty,
									'created_at' => Carbon::now(),
									'updated_at' => Carbon::now(),
									 'create_user' => Auth::user()->user_id,
									 'update_user' => Auth::user()->user_id
								]);
						} else {
							DB::connection($this->mysql)->table("oqc_probe_lots")
								->where('id', $pbl->id)
								->update([
									'oqc_id' => $req->inspection_id,
									'probe_lot' => $pbl->probe_lot,
									'oqc_po' => $req->po_no,
									'qty' => $pbl->qty,
									'deleted' => $pbl->deleted,
									'updated_at' => Carbon::now(),
									'update_user' => Auth::user()->user_id
								]);
						}
						
					}
				}

				if (count($defects) > 0) {
                    foreach ($defects as $key => $mod) {
                        if ($mod->id == -1 || $mod->id == "-1") {
                            DB::connection($this->mysql)->table("oqc_inspections_mod")
                                ->insert([
                                    'oqc_id' => $req->inspection_id,
                                    'pono' => $req->po_no,
                                    'device' => $req->series_name,
                                    'lotno' => $req->lot_no,
                                    'submission' => $req->submission,
                                    'mod1' => $mod->mod1,
                                    'qty' => $mod->qty,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                     'create_user' => Auth::user()->user_id,
                                     'update_user' => Auth::user()->user_id
                                ]);
                        } else {
                            DB::connection($this->mysql)->table("oqc_inspections_mod")
                                ->where('id', $mod->id)
                                ->update([
                                    'pono' => $req->po_no,
                                    'device' => $req->series_name,
                                    'lotno' => $req->lot_no,
                                    'submission' => $req->submission,
                                    'mod1' => $mod->mod1,
                                    'qty' => $mod->qty,
                                    'deleted' => $mod->deleted,
                                    'updated_at' => Carbon::now(),
                                    'update_user' => Auth::user()->user_id
                                ]);
                        }
                        
                    }
                }
			}
	
			if ($query) {
				$data = [
					'msg' => 'Inspection Successfully saved.',
					'status' => 'success'
	
				];
			}
		} catch (\Exception $th) {
			$data = [
                'msg' => $th->getMessage(),
                'status' => 'error'
            ];
		}

		return $data;
	}

	//OQC INSPECTION GET SERIAL NO
	public function getSerialNo(Request $req)
	{
		$data = [
            'msg' => 'Retrieving Serial No. data has failed.',
            'status' => 'failed',
            'serial' => []
        ];

        try {
            $serial = DB::connection($this->mysql)->table("oqc_serial_no")
                    ->select(
                        'id', 'serial_no', 'deleted'
                    )
                    ->where('oqc_id',$req->inspection_id)
                    ->get();

            $data = [
                'msg' => '',
                'status' => 'success',
                'serial' => $serial
            ];
        } catch (\Exception $th) {
            $data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
                'serial' => []
            ];
        }

        return $data;
	}

	//OQC INSPECTION GET DEFECTS
	public function getDefects(Request $req)
    {
        $data = [
            'msg' => 'Retrieving Mode of Defects data has failed.',
            'status' => 'failed',
            'defects' => []
        ];

        try {
            $defects = [];
            $defects_query = DB::connection($this->mysql)->table("oqc_inspections_mod")->where('oqc_id',$req->inspection_id);
            
            $count = $defects_query->count();

            if ($count > 0) {
                $defects = DB::connection($this->mysql)->table("oqc_inspections_mod")
                            ->select(
                                'id', 'mod1', 'qty', 'deleted'
                            )
                            ->where('oqc_id',$req->inspection_id)
                            ->get();
            } 
			else {
                $defects = DB::connection($this->mysql)->table("oqc_inspections_mod")
                            ->select(
                                'id', 'mod1', 'qty', 'deleted'
                            )
                            ->where('pono',$req->pono)
                            ->where('submission',$req->submission)
							->where('lotno',$req->lotno)
                            ->get();
            }

            $data = [
                'msg' => '',
                'status' => 'success',
                'defects' => $defects
            ];
        } catch (\Exception $th) {
            $data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
                'defects' => []
            ];
        }

        return $data;
    }

	//OQC INSPECTION GET PROBE LOTS
	public function getProbeLots(Request $req)
    {
        $data = [
            'msg' => 'Retrieving Probe Pin Lot numbers has failed.',
            'status' => 'failed',
            'probe_lots' => []
        ];

        try {
            $probe_lots = [];
            $probe_lots_query = DB::connection($this->mysql)->table("oqc_probe_lots")->where('oqc_id',$req->inspection_id);
									// ->select("SELECT * FROM oqc_probe_lots 
									// 		WHERE oqc_id = ".$req->inspection_id )
			
            
            $count = $probe_lots_query->count();

            if ($count > 0) {
                $probe_lots = DB::connection($this->mysql)->table("oqc_probe_lots")
								->select(
									'id', 'probe_lot', 'deleted', 'qty'
								)
								->where('oqc_id',$req->inspection_id)
								->get();
            } 

            $data = [
                'msg' => '',
                'status' => 'success',
                'probe_lots' => $probe_lots
            ];
        } catch (\Exception $th) {
            $data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
                'probe_lots' => []
            ];
        }

        return $data;
    }

	//OQC INSPECTION DELETE INSPECTION
	public function deleteInspection(Request $req)
	{
		$data = [
			'msg' => 'Deleting failed.',
			'status' => 'failed'
		];

		$delete = DB::connection($this->mysql)->table('oqc_inspections')
					->whereIn('id',$req->ids)
					->delete();
		if ($delete) {
			$data = [
				'msg' => 'Successfully deleted.',
				'status' => 'success'
			];
		}

		return $data;
	}

	//OQC INSPECTION GET WORK WEEK
	public function getWorkWeek()
	{
		$yr = 52;
		$apr = date('Y').'-04-01';
		$aprweek = date("W", strtotime($apr));

		$diff = $yr - $aprweek;
		$date = Carbon::now();
		$weeknow = $date->format("W");

		$workweek = $diff + $weeknow + 1;
		if ($workweek > 53) {
			$workweek = $workweek - 53;
		}
		return $workweek;
	}

	//VALIDATE MODE OF DEFECTS
	private function validateModeOfDefects($req)
	{
		$rules = [
			'mode_of_defects_name' => 'required',
			'mod_qty' => 'required|numeric',
		];

		$msg = [
			'mode_of_defects_name.required' => 'Mode of defect is required.',
			'mod_qty.required' => 'Mode quantity is required.',
			'mod_qty.numeric' => 'Mode quantity must be numeric.'
		];

		return $this->validate($req, $rules, $msg);
	}

	//OQC INSPECTION SAVE MODE OF DEFECTS
	public function saveModeOfDefects(Request $req)
	{
		$this->validateModeOfDefects($req);

		$data = [
			'msg' => 'Saving failed.',
			'status' => 'failed'
		];

		if ($req->mode_save_status == 'ADD') {
			$query = DB::connection($this->mysql)->table('oqc_inspections_mod')
						->insert([
							'pono' => $req->mod_po,
							'device' => $req->mod_device,
							'lotno' => $req->mod_lotno,
							'submission' => $req->mod_submission,
							'mod1' => $req->mode_of_defects_name,
							'qty' => $req->mod_qty,
							'modid' => $req->ins_id,
							'created_at' => Carbon::now(),
							'updated_at' => Carbon::now(),
						]);
		} else {
			$query = DB::connection($this->mysql)->table('oqc_inspections_mod')
						->where('id', $req->mod_id)
						->update([
							'pono' => $req->mod_po,
							'device' => $req->mod_device,
							'lotno' => $req->mod_lotno,
							'submission' => $req->mod_submission,
							'mod1' => $req->mode_of_defects_name,
							'qty' => $req->mod_qty,
							'updated_at' => Carbon::now(),
						]);
		}

		if ($query) {
			$data = [
				'msg' => 'Mode of defects successfully saved.',
				'status' => 'success',
			];
		}
	}

	//OQC INSPECTION DELETE MODE OF DEFECTS
	public function deleteModeOfDefects(Request $req)
	{
		$data = [
			'msg' => 'Deleting failed.',
			'status' => 'failed'
		];

		$delete = DB::connection($this->mysql)->table('oqc_inspections_mod')
					->whereIn('id',$req->ids)
					->delete();
		if ($delete) {
			$data = [
				'msg' => 'Successfully deleted.',
				'status' => 'success'
			];
		}

		return $data;
	}

	// //OQC INSPECTION PDF REPORT
	// public function PDFReport(Request $req)
	// {
	// 	$date = '';
 //        $po = '';
 //        if ($req->from !== '' || !empty($req->from)) {
 //            if($req->to === '' || empty($req->to)){
 //                $date = " AND a.date_inspected BETWEEN '".$this->com->convertDate($req->from,'Y-m-d')."' AND '".$this->com->convertDate($req->from,'Y-m-d')."'";
 //            }else{
 //                $date = " AND a.date_inspected BETWEEN '".$this->com->convertDate($req->from,'Y-m-d')."' AND '".$this->com->convertDate($req->to,'Y-m-d')."'";
 //            }
 //        }

 //        $sqlDateInspected = "SELECT DISTINCT(date_inspected) AS date_inspected FROM oqc_inspections where date_inspected = '". $this->com->convertDate($req->from,'Y-m-d') ."'";
 //        $dateIns = DB::connection($this->mysql)->select($sqlDateInspected);
		
	// 	$parameter = $req->chosen;
      
 //        if ($req->po !== '' || !empty($req->po)) {
 //            $po = " AND a.po_no = '".$req->po."'";
 //        }


 //        if(count($dateIns) == 0 ){
 //            return $dateIns;
 //        }else{
 //        //    return $this->PDFwithPO($req,$po,$date, $parameter);
	// 		$sql = "select a.device_name as device_name,
 //                            a.prod_category as prod_category,
 //                            a.po_no as po_no,
 //                            a.po_qty as po_qty,
 //                            a.customer as customer,
 //                            a.coc_req as coc_req,
 //                            a.type_of_inspection as type_of_inspection,
 //                            a.severity_of_inspection as severity_of_inspection,
 //                            (
 //                                select group_concat(glvl.inspection_lvl) as inspection_lvl 
 //                                from (
 //                                    select distinct a.inspection_lvl as inspection_lvl
 //                                    from oqc_inspections as a
 //                                    where 1=1 ".$po.$date."
 //                                ) as glvl
 //                            ) as inspection_lvl,
 //                            a.aql as aql,
 //                            a.accept as accept,
 //                            a.reject as reject,
 //                            sum(a.lot_qty) as total_qty,
 //                            (a.po_qty - sum(a.lot_qty)) as balance
 //                    from oqc_inspections as a
 //                    where 1=1 ".$po.$date."
 //                    group by a.device_name,
 //                            a.prod_category,
 //                            a.po_no,
 //                            a.po_qty,
 //                            a.customer,
 //                            a.coc_req,
 //                            a.type_of_inspection,
 //                            a.severity_of_inspection,
 //                            a.aql,
 //                            a.accept,
 //                            a.reject";

 //        $header = DB::connection($this->mysql)->select($sql);

	// 	foreach ($header as $key => $info){
	// 		$details_sql = "SELECT a.id, 
	// 								CONCAT(a.fy,' - ',a.ww) AS fyww,
	// 								a.date_inspected AS date_inspected,
	// 								a.shift AS shift,
	// 								CONCAT(a.time_ins_from,' - ', a.time_ins_to) as time_inspected,
	// 								a.submission AS submission,
	// 								a.lot_no lot_no,
	// 								a.lot_qty AS lot_size,
	// 								a.severity_of_inspection AS severity_of_inspection,
	// 								a.sample_size AS sample_size,
	// 								a.num_of_defects AS num_of_defects,
	// 								a.judgement AS judgement,
	// 								a.inspector AS inspector,
	// 								a.coc_req AS coc_req,
	// 								a.remarks AS remarks,
	// 								a.workweek AS workweek
	// 						FROM oqc_inspections AS a
	// 						WHERE a.po_no LIKE '".$info->po_no."%' ".$date."
	// 						AND a.type_of_inspection = '".$info->type_of_inspection."'
	// 						AND a.inspection_lvl = '".$info->inspection_lvl."'
	// 						AND a.aql = '".$info->aql."'
	// 						GROUP BY CONCAT(a.fy,' - ',a.ww),
	// 								a.date_inspected,
	// 								a.shift,
	// 								CONCAT(a.time_ins_from,' - ', a.time_ins_to),
	// 								a.submission,
	// 								a.severity_of_inspection,
	// 								a.lot_no,
	// 								a.lot_qty,
	// 								a.sample_size,
	// 								a.num_of_defects,
	// 								a.judgement,
	// 								a.inspector,
	// 								a.coc_req,
	// 								a.remarks,
	// 								a.workweek
	// 						ORDER BY a.lot_no";


	// 		$details = DB::connection($this->mysql)->select($details_sql);
	
	// 	}
		
		
 //        $dt = Carbon::now();
 //        $company_info = $this->com->getCompanyInfo();
 //        $date = substr($dt->format('  M j, Y  h:i A '), 2);

 //        foreach($header as $key => $head){
 //            $html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 //            <html>
 //            <head>
 //                <style>
 //                    .header,
 //                    .footer {
 //                        width: 100%;
 //                        text-align: center;
 //                        position: fixed;
 //                    }
 //                    .header {
 //                        top: 0px;
 //                    }
 //                    .footer {
 //                        bottom: 0px;
 //                    }
 //                    .pagenum:before {
 //                        content: counter(page);
 //                    }
 //                    .fontArial
 //                    {
 //                        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
 //                    }
 //                </style>
 //            </head>
 //            <body>
 //                <div class="footer fontArial">
 //                    <hr />
 //                    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-size:12px;">
 //                        <tbody>
 //                            <tr>
 //                                <td align="left">
 //                                <table border="0" cellpadding="0" cellspacing="0">
 //                                    <tbody>
 //                                        <tr>
 //                                            <td>Date:</td>
 //                                            <td>'. $date .'</td>
 //                                        </tr>
 //                                    </tbody>
 //                                </table>
 //                                </td>
 //                                <td align="right">
 //                                <table align="right" border="0" cellpadding="0" cellspacing="0">
 //                                    <tbody>
 //                                        <tr>
 //                                            <td>Page:</td>
 //                                            <td><span class="pagenum"></span></td>
 //                                        </tr>
 //                                    </tbody>
 //                                </table>
 //                                </td>
 //                            </tr>
 //                        </tbody>
 //                    </table>
 //                </div>
 //                <table class="fontArial" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
 //                    <tbody>
 //                        <tr>
 //                            <td align="center">
 //                            <h4>'. $company_info['name'] .'</h4>
 //                            <p style="line-height: 1.8px; font-size:12px; ">'. $company_info['address'] .'</p>
 //                            <h2><ins>OQC INSPECTIONS</ins></h2>
 //                            </td>
 //                        </tr>
 //                    </tbody>
 //                </table>

 //                <table class="fontArial" border="0" cellpadding="3" cellspacing="3" style="width: 100%; font-size:12px;">
 //                    <tbody>
 //                        <tr>
 //                            <td style="width: 80px;">Product Name:</td>
 //                            <td colspan="2">'. $head->device_name .'</td>
 //                            <td>&nbsp;</td>
 //                            <td style="width: 80px;">Customer Name:</td>
 //                            <td colspan="2">'. $head->customer .'</td>
 //                            <td>&nbsp;</td>
 //                            <td style="width: 80px;">AQL :</td>
 //                            <td colspan="2">'. $head->aql .'</td>
 //                        </tr>
 //                        <tr>
 //                            <td style="width: 80px;">Category:</td>
 //                            <td colspan="2">'. $head->prod_category .'</td>
 //                            <td>&nbsp;</td>
 //                            <td style="width: 80px;">Type of Inspection:</td>
 //                            <td colspan="2">'. $head->type_of_inspection .'</td>
 //                            <td>&nbsp;</td>
 //                            <td style="width: 80px;">Ac :</td>
 //                            <td colspan="2">'. $head->accept .'</td>
                          
 //                        </tr>
 //                        <tr>
 //                            <td style="width: 80px;">PO:</td>
 //                            <td colspan="2">'. $head->po_no .'</td>
 //                            <td>&nbsp;</td>
 //                            <td style="width: 80px;">Inspection Level:</td>
 //                            <td colspan="2">'. $head->inspection_lvl .'</td>
 //                            <td>&nbsp;</td>
 //                            <td style="width: 80px;">Re :</td>
 //                            <td colspan="2">'. $head->reject .'</td>
 //                        </tr>
 //                        <tr>
 //                            <td style="width: 80px;">PO Qty. :</td>
 //                            <td colspan="2">'. $head->po_qty .'</td>
 //                        </tr>
 //                    </tbody>
 //                </table>
 //                <br/>
 //                <table class="fontArial"  style="border: 2px solid black; border-collapse: collapse; width:100%; cellspacing:0; cellpadding:0; font-size:12px;">
 //                    <thead style="border: 2px solid black; text-align: center;">
 //                        <tr>
 //                            <th style="border-right: 1px solid black; width: 6%;" scope="col"><strong>FY - WW</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Date Inspected</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Shift</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Time Inspected</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong># of Sub</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Lot No.</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Lot Size</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Severity of Inspection</strong></th>
 //                            <th style="border-right: 1px solid black; width: 6%;" scope="col"><strong>Sample Size</strong></th>';

	// 						if($parameter === "Workweek"){
	// 							$html = $html . '<th style="border-right: 1px solid black; width: 10%;" scope="col"><strong>Workweek</strong></th>';
	// 						}else if($parameter === "Serial"){
	// 							$html = $html . '<th style="border-right: 1px solid black; width: 10%;" scope="col"><strong>Serial No.</strong></th>';
	// 						}else if($parameter === "Probe"){
	// 							$html = $html . '<th style="border-right: 1px solid black; width: 10%;" scope="col"><strong>Probe Pin Lot No.</strong></th>';
	// 						}
							
 //                            $html = $html . '<th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>No. of Defectives</strong></th>
	// 						<th style="border-right: 1px solid black; width: 15%;" scope="col"><strong>
 //                                <table style="width: 100%; margin:auto; padding : 0;">
 //                                    <tr>
 //                                        <th colspan = "2" style = "border-bottom: 1px solid black; width : 100%; margin:auto; padding:0; text-align: center">Mode of Defects</th>
 //                                    </tr>
 //                                    <tr>
 //                                        <th style= "border-right: 1px solid black; width : 75%; margin:auto; padding:0; text-align: center">M.O.D</th>
 //                                        <th style= "width : 25%; margin:auto; padding:0; text-align: center"> Qty. </th>
 //                                    </tr>
 //                                </table>
 //                            </strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Judgement</strong></th>
 //                            <th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>Inspector</strong></th>
	// 						<th style="border-right: 1px solid black; width: 5%;" scope="col"><strong>COC Req.</strong></th>
 //                            <th style="width: 50%;" scope="col"><strong>Remarks</strong></th>
 //                        </tr>
 //                    </thead>
 //                    <tbody>';

 //        }
		
 //        $html2 = '';
	// 	$html3 = '';
	// 	$html4 = '';
	// 	$html5 = '';
	// 	$html6 = '';
	// 	$html7 = '';
	// 	$html7_ModeOfDefects = '';
	// 	$html8 = '';
	// 	$html9 = '';
	// 	$html9_ModeOfDefectsQty = '';
	// 	$html10 = '';
	// 	$html11 = '';

	// 	$serialNo = 0;
	// 	$probePinLotNo = 0;
	// 	$modeOfDefects = 0;

	// 	$table = '<table align-center style="width: 100%;">';

	// 	$countDetails = count($details);

 //            foreach ($details as $key => $row)
 //            {
         
	// 			$html2 = $html8 . '
	// 				<tr>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->fyww .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->date_inspected .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->shift .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->time_inspected .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->submission .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->lot_no .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->lot_size .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->severity_of_inspection .'</td>
	// 					<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->sample_size .'</td>';

	// 			if($parameter === "Workweek"){

	// 				$html3 = $html2 . '<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->workweek .'</td>';

	// 			}else if($parameter === "Serial"){

	// 				$html3 = $html2 . '<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">
	// 										<table align-center style="width: 100%;">';
	// 				//SERIAL NO
	// 				$serialNo = DB::connection($this->mysql)->select("SELECT serial_no FROM oqc_serial_no WHERE oqc_id = '".$row->id."'"); 

	// 				if(count($serialNo) > 0){
	// 					$html4 = '';
	// 					foreach($serialNo as $key => $serialRow)
	// 					{	
	// 						$html4 = $html4 .'<tr>
	// 											<td style="border-bottom: 2px solid black; width: 100%; margin:auto; padding:0; text-align: center;">'. $serialRow->serial_no .'</td>
	// 										</tr>';			
	// 					}
	// 					$html4 = $html4 . '</table>
	// 									</td>';
	// 				}else{
	// 					$html4 = '<tr>
	// 								<td style = "margin:auto; padding:0; text-align: center;"> 0 </td>
	// 							</tr>
	// 						</table>
	// 					</td>';
	// 				 }

	// 			}else if($parameter === "Probe"){

	// 				$html3 = $html2 . '<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">
	// 										<table align-center style="width: 100%;">';
	// 				//PROBE PIN LOT NO
	// 				$probePinLotNo = DB::connection($this->mysql)->select("SELECT probe_lot FROM oqc_probe_lots WHERE oqc_id = '".$row->id."' AND deleted = 0");

	// 				if (count($probePinLotNo) > 0){
						
	// 					$html4 = '';
	// 					foreach($probePinLotNo as $key => $probeRow)
	// 					{
	// 						$html4 = $html4 . '<tr>
	// 												<td style="border-bottom: 2px solid black; width: 100%; margin:auto; padding:0; text-align: center;">' . $probeRow->probe_lot .'</td>
	// 											</tr>';	
	// 					}
	// 					$html4 = $html4 . '</table>
	// 									</td>';
	// 				}else{
	// 					$html4 = '<tr>
	// 								<td style = "margin:auto; padding:0; text-align: center;"> 0 </td>
	// 							</tr>
	// 						</table>
	// 					</td>';	
	// 				}
	// 			}
				
	// 			$html5 = $html3 . $html4;
			
	// 			$html6 = $html5 . '<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->num_of_defects .'</td>
	// 								<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">
	// 									<table align-center style="width: 100%;">';

				
	// 			//MODE OF DEFECTS
	// 			$modeOfDefects = DB::connection($this->mysql)->select("SELECT mod1, qty  FROM oqc_inspections_mod WHERE oqc_id = '".$row->id."'");

	// 				if (count($modeOfDefects) > 0){
						
	// 					$html7_ModeOfDefects = '';
	// 					foreach($modeOfDefects as $key => $modeRow)
	// 					{
	// 						$html7_ModeOfDefects = $html7_ModeOfDefects . '<tr>
	// 																			<td style= "border-right: 1px solid black; border-bottom: 1px solid black; width : 64%; margin:auto; padding:0; text-align: center;">' .$modeRow->mod1. '</td>
	// 																			<td style= "border-bottom: 1px solid black; width : 46%; margin:auto; padding:0; text-align: center;">' .$modeRow->qty. '</td>
	// 																		</tr>';
	// 					}
	// 				}else{
	// 					$html7_ModeOfDefects = '<tr>
	// 												<td style= "border-right: 1px solid black; width : 64%; margin:auto; padding:0; text-align: center;"> NDF </td>
	// 												<td style= "width : 46%; margin:auto; padding:0; text-align: center;"> 0 </td>
	// 											</tr>';
	// 				}
				
	// 			$html7 = $html6 . $html7_ModeOfDefects;

	// 			$html8 = $html7 . '</table>
	// 							</td>
	// 							<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->judgement .'</td>
	// 							<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->inspector .'</td>
	// 							<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->coc_req .'</td>
	// 							<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->remarks .'</td>
	// 						</tr>';
		
	// 			$html9 = '</tbody>
	// 					</table>
	// 				</body>
	// 			</html>';

 //            }

	// 		$drawHtml = $html .  $html8 . $html9;
			
	// 		$dompdf = new Dompdf();
	// 		$dompdf->loadHTML($drawHtml);
	// 		$dompdf->setPaper('A4', 'landscape');
	// 		$dompdf->render();
       
 //        return $dompdf->stream('OQC_Inspection_'.$req->po.'_'.Carbon::now().'.pdf');
 //        }
    
	// }

	//OQC INSPECTION PDF REPORT
	public function PDFReport(Request $req)
	{
		$dt = Carbon::now();
        $company_info = $this->com->getCompanyInfo();
        $date = substr($dt->format('  M j, Y  h:i A '), 2);

        $po = '';
        $date_inspected = '';

        $yearFrom = substr($req->from, 6);
        $monthFrom = substr($req->from, 0,2);
        $dayFrom = substr($req->from, 3,2);
        $yearTo = substr($req->to, 6);
        $monthTo =substr($req->to, 0,2);
        $dayTo = substr($req->to, 3,2);

        $from = $yearFrom . '-' . $monthFrom . '-' . $dayFrom;
        $to = $yearTo . '-' . $monthTo . '-' . $dayTo;

        if ($req->from !== '' && !empty($req->from)) {
            if($req->to === '' || empty($req->to)){
                $date_inspected = " AND a.date_inspected BETWEEN '".$from."' AND '".$from."'";
            }else{
                $date_inspected = " AND a.date_inspected BETWEEN '".$from."' AND '".$to."'";
            }
        }

		$sqlDateInspected = "";
        if($req->from != "" && $req->to != ""){
            $sqlDateInspected = "SELECT * FROM oqc_inspections where date_inspected = '". $from."'";
        }
        else if($req->from == "" && $req->to == ""){
            $sqlDateInspected = "SELECT * FROM oqc_inspections where po_no = '". $req->po ."'";
        }
        
        $dateIns = DB::connection($this->mysql)->select($sqlDateInspected);
      
        if ($req->po !== '' || !empty($req->po)) {
            $po = " AND a.po_no = '".$req->po."'";
        }


        if(count($dateIns) == 0 ){
            return $dateIns;
        }else{
			$sql = "SELECT GROUP_CONCAT(a.id SEPARATOR ',') AS id,
                            a.device_name AS device_name,
                            a.prod_category AS prod_category,
                            a.po_no AS po_no,
                            a.po_qty AS po_qty,
                            a.customer AS customer,
                            a.coc_req AS coc_req,
                            a.type_of_inspection AS type_of_inspection,
                            a.severity_of_inspection AS severity_of_inspection,
                            (
                                SELECT GROUP_CONCAT(glvl.inspection_lvl) AS inspection_lvl 
                                FROM (
                                    SELECT DISTINCT a.inspection_lvl AS inspection_lvl
                                    FROM oqc_inspections AS a
                                    WHERE 1=1 ".$po.$date_inspected."
                                ) AS glvl
                            ) AS inspection_lvl,
                            a.aql AS aql,
                            a.accept AS accept,
                            a.reject AS reject,
                            SUM(a.lot_qty) AS total_qty,
                            (a.po_qty - SUM(a.lot_qty)) AS balance
                    FROM oqc_inspections AS a
                    WHERE 1=1 ".$po.$date_inspected."
                    GROUP BY a.device_name,
                            a.prod_category,
                            a.po_no,
                            a.po_qty,
                            a.customer,
                            a.coc_req,
                            a.type_of_inspection,
                            a.severity_of_inspection,
                            a.aql,
                            a.accept,
                            a.reject";

            $header = DB::connection($this->mysql)->select($sql);


			$data = [
				'company_info' => $company_info,
				'header' => $header,
				'conn' => $this->mysql,
				'from' => $from,
				'to' => $to,
				'po' => $po,
				'date' => $date_inspected,
				'dateNow' => $date,
				'parameter' => $req->chosen
			];
	
			$pdf = PDF::loadView('pdf.oqc', $data)
			->setPaper('A4')
			->setOption('margin-top', 10)
			->setOption('margin-bottom', 5)
			->setOption('margin-left', 1)
			->setOption('margin-right', 1)
			->setOrientation('landscape');
	
			return $pdf->inline('OQC_Inspection_'.Carbon::now());
		}
	}

	//OQC INSPECTION INSPECTION EXCEL REPORT - UPDATE SOURCE CODE (AUGUST 04, 2022)
	public function ExcelReport(Request $req)
    {
		//dd($req->all());
        $dt = Carbon::now();
        $dates = substr($dt->format('Ymd'), 2);

		$parameter = $req->chosen;
		

        Excel::create('OQC_Inspection_Report'.$dates, function($excel) use($dt,$req, $parameter)
        {
            $com_info = $this->com->getCompanyInfo();
            $date_today = substr($dt->format('  M j, Y  h:i A '), 2);
            $po = "";
			$date_inspected = "";

			$yearFrom = substr($req->from, 6);
			$monthFrom = substr($req->from, 0,2);
			$dayFrom = substr($req->from, 3,2);
			$yearTo = substr($req->to, 6);
			$monthTo =substr($req->to, 0,2);
			$dayTo = substr($req->to, 3,2);
	
			$from = $yearFrom . '-' . $monthFrom . '-' . $dayFrom;
			$to = $yearTo . '-' . $monthTo . '-' . $dayTo;
	
			if ($req->from !== '' && !empty($req->from)) {
				if($req->to === '' || empty($req->to)){
					$date_inspected = " AND a.date_inspected BETWEEN '".$from."' AND '".$from."'";
				}else{
					$date_inspected = " AND a.date_inspected BETWEEN '".$from."' AND '".$to."'";
				}
			}
	
			$sqlDateInspected = "";
			if($req->from != "" && $req->to != ""){
				$sqlDateInspected = "SELECT * FROM oqc_inspections where date_inspected = '". $from."'";
			}
			else if($req->from == "" && $req->to == ""){
				$sqlDateInspected = "SELECT * FROM oqc_inspections where po_no = '". $req->po ."'";
			}
			
			$dateIns = DB::connection($this->mysql)->select($sqlDateInspected);
		  
			if ($req->po !== '' || !empty($req->po)) {
				$po = " AND a.po_no = '".$req->po."'";
			}


			// SELECT DATA FOR HEADER AND FOOTER IN EXCEL FILE
            $sql = "SELECT GROUP_CONCAT(a.id SEPARATOR ',') AS id,
                            a.device_name AS device_name,
                            a.prod_category AS prod_category,
                            a.po_no AS po_no,
                            a.po_qty AS po_qty,
                            a.customer AS customer,
                            a.coc_req AS coc_req,
                            a.type_of_inspection AS type_of_inspection,
                            a.severity_of_inspection AS severity_of_inspection,
                            (
                                SELECT GROUP_CONCAT(glvl.inspection_lvl) AS inspection_lvl 
                                FROM (
                                    SELECT DISTINCT a.inspection_lvl AS inspection_lvl
                                    FROM oqc_inspections AS a
                                    WHERE 1=1 ".$po.$date_inspected."
                                ) AS glvl
                            ) AS inspection_lvl,
                            a.aql AS aql,
                            a.accept AS accept,
                            a.reject AS reject,
                            SUM(a.lot_qty) AS total_qty,
                            (a.po_qty - SUM(a.lot_qty)) AS balance
                    FROM oqc_inspections AS a
                    WHERE 1=1 ".$po.$date_inspected."
                    GROUP BY a.device_name,
                            a.prod_category,
                            a.po_no,
                            a.po_qty,
                            a.customer,
                            a.coc_req,
                            a.type_of_inspection,
                            a.severity_of_inspection,
                            a.aql,
                            a.accept,
                            a.reject";

            $infos = DB::connection($this->mysql)->select($sql);

			
								
            foreach ($infos as $key => $info) {
                $excel->sheet($info->po_no, function($sheet) use($com_info,$po,$date_inspected,$info,$date_today, $req, $parameter)
                {

					//SELECT DATA FOR DATATABLE IN EXCEL FILE
                    $sheet->setFreeze('A12');
					$details_sql = "SELECT a.id, 
									CONCAT(a.fy,' - ',a.ww) AS fyww,
									a.date_inspected AS date_inspected,
									a.shift AS shift,
									CONCAT(a.time_ins_from,' - ', a.time_ins_to) as time_inspected,
									a.submission AS submission,
									CAST(a.lot_no AS UNSIGNED) AS lot_no,
									a.lot_qty AS lot_size,
									a.severity_of_inspection AS severity_of_inspection,
									a.sample_size AS sample_size,
									a.num_of_defects AS num_of_defects,
									a.judgement AS judgement,
									a.inspector AS inspector,
									a.coc_req AS coc_req,
									a.remarks AS remarks,
									a.workweek AS workweek
							FROM oqc_inspections AS a
							WHERE a.id in (". $info->id .")
							ORDER BY CAST(a.lot_no AS UNSIGNED) ASC";

                    $details = DB::connection($this->mysql)->select($details_sql);

                    $sheet->setHeight(1, 15);
                    $sheet->mergeCells('A1:R1');
                    $sheet->cells('A1:R1', function($cells) {
                        $cells->setAlignment('center');
						$cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12'
                        ]);
                    });
                    $sheet->cell('A1',$com_info['name']);

                    $sheet->setHeight(2, 15);
                    $sheet->mergeCells('A2:R2');
                    $sheet->cells('A2:R2', function($cells) {
                        $cells->setAlignment('center');
						$cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12'
                        ]);
                    });
                    $sheet->cell('A2',$com_info['address']);

                    $sheet->setHeight(4, 20);
                    $sheet->mergeCells('A4:R4');
                    $sheet->cells('A4:R4', function($cells) {
                        $cells->setAlignment('center');
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                            'underline'  =>  true
                        ]);
                    });
                    $sheet->cell('A4',"OQC INSPECTION RESULT");

                    $sheet->setHeight(11, 15);
                    $sheet->cells('B11:R11', function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12'
                        ]);
                    });

                    // PRODUCT NAME
                    $sheet->cell('B6', function($cell) {
                        $cell->setValue('Series Name');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('C6',$info->device_name);

                    // CATEGORY
                    $sheet->cell('B7', function($cell) {
                        $cell->setValue('Category');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('C7',$info->prod_category);
                    
                    // P.O.
                    $sheet->cell('B8', function($cell) {
                        $cell->setValue('P.O.');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('C8',$info->po_no);

                    // P.O. QTY
                    $sheet->cell('B9', function($cell) {
                        $cell->setValue('P.O. Qty.');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
					$sheet->cell('C9', function($cell) {
						$cell->setAlignment('left');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                        ]);
                    });
                    $sheet->cell('C9',$info->po_qty);

                    // CUSTOMER NAME
                    $sheet->cell('E6', function($cell) {
                        $cell->setValue('Customer Name');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('F6',$info->customer);
                    
                    
                    // TYPE OF INSPECTION
                    $sheet->cell('E7', function($cell) {
                        $cell->setValue('Type of Inspection');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('F7',$info->type_of_inspection);
                    
                    
                    // INSPECTION LEVEL
                    $sheet->cell('E8', function($cell) {
                        $cell->setValue('Inspection Level');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('F8',$info->inspection_lvl);

					 // AQL
					 $sheet->cell('H6', function($cell) {
                        $cell->setValue('AQL');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    $sheet->cell('I6',$info->aql);
                    
                    
                    // AC
                    $sheet->cell('H7', function($cell) {
                        $cell->setValue('Ac');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
					$sheet->cell('I7',($info->accept < 1)? '0.00': $info->accept);
                    
                    
                    // RE
                    $sheet->cell('H8', function($cell) {
                        $cell->setValue('Re');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
					$sheet->cell('I8',($info->reject < 1)? '0.00': $info->reject);


                    $sheet->setHeight(6, 15);
                    $sheet->setHeight(7, 15);
                    $sheet->setHeight(8, 15);

                    $sheet->cell('B11', function($cell) {
                        $cell->setValue("FY-WW");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('C11', function($cell) {
                        $cell->setValue("Date Inspected");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('D11', function($cell) {
                        $cell->setValue("Shift");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('E11', function($cell) {
                        $cell->setValue("Time Inspected");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('F11', function($cell) {
                        $cell->setValue("# of Sub");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('G11', function($cell) {
                        $cell->setValue("Lot No.");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
					if($parameter === "Workweek" || $parameter === "Serial"){
						$sheet->cell('H11', function($cell) {
							$cell->setValue("Lot Size");
							$cell->setAlignment('center');
							$cell->setValignment('center');
							$cell->setBorder('thin','thin','thin','thin');
							$cell->setFont([
								'family'     => 'Calibri',
								'size'       => '12',
								'bold'       =>  true,
							]);
						});
					}else {
						$sheet->cell('H11', function($cell) {
							$cell->setValue("Quantity");
							$cell->setAlignment('center');
							$cell->setValignment('center');
							$cell->setBorder('thin','thin','thin','thin');
							$cell->setFont([
								'family'     => 'Calibri',
								'size'       => '12',
								'bold'       =>  true,
							]);
						});
					}

                    $sheet->cell('I11', function($cell) {
                        $cell->setValue("Severity of Inspection");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('J11', function($cell) {
                        $cell->setValue("Sample Size");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

					if($parameter == "Workweek"){
						$sheet->cell('K11', function($cell) {
							$cell->setValue("Workweek");
							$cell->setAlignment('center');
							$cell->setValignment('center');
							$cell->setBorder('thin','thin','thin','thin');
							$cell->setFont([
								'family'     => 'Calibri',
								'size'       => '12',
								'bold'       =>  true,
							]);
						});
					}else if($parameter == "Serial"){
						$sheet->cell('K11', function($cell) {
							$cell->setValue("Serial No.");
							$cell->setAlignment('center');
							$cell->setValignment('center');
							$cell->setBorder('thin','thin','thin','thin');
							$cell->setFont([
								'family'     => 'Calibri',
								'size'       => '12',
								'bold'       =>  true,
							]);
						});
					}else if($parameter == "Probe"){
						$sheet->cell('K11', function($cell) {
							$cell->setValue("Probe Pin Lot No.");
							$cell->setAlignment('center');
							$cell->setValignment('center');
							$cell->setBorder('thin','thin','thin','thin');
							$cell->setFont([
								'family'     => 'Calibri',
								'size'       => '12',
								'bold'       =>  true,
							]);
						});
					}
					
               
                    $sheet->cell('L11', function($cell) {
                        $cell->setValue("No. of Defectives");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('M11', function($cell) {
                        $cell->setValue("Mode of Defects");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('N11', function($cell) {
                        $cell->setValue("Qty");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

					$sheet->cell('O11', function($cell) {
                        $cell->setValue("Judgement");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
					
					$sheet->cell('P11', function($cell) {
                        $cell->setValue("Inspector");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

					$sheet->cell('Q11', function($cell) {
                        $cell->setValue("COC Requirements");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

					$sheet->cell('R11', function($cell) {
                        $cell->setValue("Remarks");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                  

                    $sheet->setHeight(10, 15);

					$row = 12;

                    $lot_qty = 0;
                    $po_qty = 0;
                    $balance = 0;

                    $inspection_row = 0;
                    $serial_no_row = $row;
                    $probe_pin_lot_row = $row;
                    $defect_row = $row;
					$defect_qty_row = $row;
                    $merge_start = $row;
                    $merge_end = 0;

                    $arr_row = [];

                    foreach ($details as $key => $qc) {
                        $sheet->cells('B'.$row.':R'.$row, function($cells) {
                         // Set all borders (top, right, bottom, left)
                            $cells->setBorder(array(
                                'top'   => array(
                                    'style' => 'thin'
                                ),
                            ));
                            $cells->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                            ]);
                        });

                        $sheet->cell('B'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->fyww);
                        });

                        $sheet->cell('C'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->date_inspected);
                        });

                        $sheet->cell('D'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->shift);
                        });

                        $sheet->cell('E'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->time_inspected);
                        });

                        $sheet->cell('F'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->submission);
                        });

						$sheet->cell('G'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->lot_no);
                        });
						
						$sheet->cell('H'.$row, function($cell) use($qc) {
							$cell->setBorder('thin','thin','thin','thin');
							$cell->setAlignment('center');
							$cell->setValignment('center');
							$cell->setValue($qc->lot_size);
						});

                        $sheet->cell('I'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->severity_of_inspection);
                        });

                        $sheet->cell('J'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->sample_size);
                        });
						
						//CHECKING IF WORKWEEK / SERIAL NO / PROBE PIN LOT NO
						$serial_nos = "";
						$probe_pin_lots = "";

						if($parameter === "Workweek"){
							$sheet->cell('K'.$row, function($cell) use($qc) {
								$cell->setBorder('thin','thin','thin','thin');
								$cell->setAlignment('center');
								$cell->setValignment('center');
								$cell->setValue($qc->workweek);
							});
						}else if($parameter === "Serial"){
							//SERIAL
							$serial_nos = DB::connection($this->mysql)->select("SELECT serial_no FROM oqc_serial_no WHERE oqc_id LIKE '".$qc->id."%' AND (deleted IS NULL OR deleted = 0)"); 

							if (count($serial_nos) == 0) {
								$sheet->cell('K'.$row, function($cell) {
									$cell->setAlignment('center');
									$cell->setValignment('center');
									$cell->setBorder('thin','thin','thin','thin');
									$cell->setValue("0");
								});
								$serial_no_row++; 
							}else{
								foreach ($serial_nos as $key => $serialNo) {
									$sheet->cell('K'.$serial_no_row, function($cell) use($serialNo) {
										$cell->setBorder('thin','thin','thin','thin');
										$cell->setValue($serialNo->serial_no);
									});
								$serial_no_row++;
								}
							}

						}
						else if($parameter === "Probe"){
							// PROBE PIN LOT NO
							$probe_pin_lots = DB::connection($this->mysql)->select("SELECT probe_lot, qty FROM oqc_probe_lots WHERE oqc_id LIKE '".$qc->id."%' AND deleted = 0"); 

							if (count($probe_pin_lots) == 0) {

								$sheet->cell('H'.$row, function($cell){
									$cell->setBorder('thin','thin','thin','thin');
									$cell->setAlignment('center');
									$cell->setValignment('center');
									$cell->setValue("0");
								});

								$sheet->cell('K'.$row, function($cell) {
									$cell->setAlignment('center');
									$cell->setValignment('center');
									$cell->setBorder('thin','thin','thin','thin');
									$cell->setValue("0");
								});
								$probe_pin_lot_row++; 
							}else{
								foreach ($probe_pin_lots as $key => $probePin) {
									$sheet->cell('H'.$probe_pin_lot_row, function($cell) use($probePin) {
										$cell->setBorder('thin','thin','thin','thin');
										$cell->setValue($probePin->qty);
									});
									$sheet->cell('K'.$probe_pin_lot_row, function($cell) use($probePin) {
										$cell->setBorder('thin','thin','thin','thin');
										$cell->setValue($probePin->probe_lot);
									});
									$probe_pin_lot_row++; 
								}
							}
						}

						$sheet->cell('L'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->num_of_defects);
                        });

						 // MODE OF DEFECTS
						 $defects = DB::connection($this->mysql)->select("SELECT mod1, qty FROM oqc_inspections_mod WHERE oqc_id LIKE '".$qc->id."%' AND deleted = 0");

						// checking of data if empty then apply border
						if (count($defects) == 0) {
							$sheet->cell('M'.$row, function($cell) {
								$cell->setAlignment('center');
								$cell->setValignment('center');
								$cell->setBorder('thin','thin','thin','thin');
								$cell->setValue("NDF");
							});
							$defect_row++; 
						}else {
							foreach ($defects as $key => $def) {
								$sheet->cell('M'.$defect_row, function($cell) use($def) {
									$cell->setBorder('thin','thin','thin','thin');
									$cell->setValue($def->mod1);
								});
								$defect_row++; 
							}
						}

						if (count($defects) == 0) {
							$sheet->cell('N'.$row, function($cell) {
								$cell->setAlignment('center');
								$cell->setValignment('center');
								$cell->setBorder('thin','thin','thin','thin');
								$cell->setValue("0");
							});
							$defect_qty_row++; 
						}else {
							foreach ($defects as $key => $defQty) {
								$sheet->cell('N'.$defect_qty_row, function($cell) use($defQty) {
									$cell->setBorder('thin','thin','thin','thin');
									$cell->setValue($defQty->qty);
								});
								$defect_qty_row++; 
							}
						}

						$sheet->cell('O'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->judgement);
                        });

						$sheet->cell('P'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->inspector);
                        });

						$sheet->cell('Q'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->coc_req);
                        });

						$sheet->cell('R'.$row, function($cell) use($qc) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($qc->remarks);
                        });

                        
                        $sheet->row($row, function ($row) {
                            $row->setFontFamily('Calibri');
                            $row->setFontSize(11);
                        });
                        $sheet->setHeight($row,20);

                        $arr_row = [
                            $serial_no_row,
                            $probe_pin_lot_row,
                            $defect_row,
							$defect_qty_row
                        ];

					    $serial_nos_count = count($serial_nos);
                        $probe_pin_lots_count = count($probe_pin_lots);
                        $defects_count = count($defects);

                        $arr_max_row = max($arr_row);

                        $arr_counts = max([$serial_nos_count,$probe_pin_lots_count,$defects_count]);

                        for ($i = 0; $i < $arr_counts; $i++) {
							
                            $iterate_row = $row + $i;

							$sheet->cell('H'.$iterate_row, function($cell) {
                                $cell->setBorder('thin','thin','thin','thin');
                            });
							$sheet->cell('K'.$iterate_row, function($cell) {
                                $cell->setBorder('thin','thin','thin','thin');
                            });
							$sheet->cell('M'.$iterate_row, function($cell) {
                                $cell->setBorder('thin','thin','thin','thin');
                            });
							$sheet->cell('N'.$iterate_row, function($cell) {
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            
                        }

                        if ($serial_nos_count > 0 || $probe_pin_lots_count > 0 || $defects_count > 0) {
                            $row = $row + ($arr_max_row - $row);
                        } else {
                            $row++;
                        }

                        $end = $row - 1;

                        if ($end >= $merge_start) {
							$merge_end = $end;
                        } else {
                            $merge_end = $row;
                        }

                        $sheet->mergeCells('B'.$merge_start.':B'.$merge_end);
                        $sheet->mergeCells('C'.$merge_start.':C'.$merge_end);
                        $sheet->mergeCells('D'.$merge_start.':D'.$merge_end);
                        $sheet->mergeCells('E'.$merge_start.':E'.$merge_end);
                        $sheet->mergeCells('F'.$merge_start.':F'.$merge_end);
                        $sheet->mergeCells('G'.$merge_start.':G'.$merge_end);
                        $sheet->mergeCells('I'.$merge_start.':I'.$merge_end);
                        $sheet->mergeCells('J'.$merge_start.':J'.$merge_end);
						if($parameter === "Workweek"){
							$sheet->mergeCells('H'.$merge_start.':H'.$merge_end);
							$sheet->mergeCells('K'.$merge_start.':K'.$merge_end);
						}else if($parameter === "Serial"){
							$sheet->mergeCells('H'.$merge_start.':H'.$merge_end);
							if($serial_nos_count == 0){
								$sheet->mergeCells('K'.$merge_start.':K'.$merge_end);
							}
						}else if ($parameter === "Probe"){
							if($probe_pin_lots_count == 0){
								$sheet->mergeCells('H'.$merge_start.':H'.$merge_end);
								$sheet->mergeCells('K'.$merge_start.':K'.$merge_end);
							}
						}
						$sheet->mergeCells('L'.$merge_start.':L'.$merge_end);

						if($defects_count == 0){
							$sheet->mergeCells('M'.$merge_start.':M'.$merge_end);
							$sheet->mergeCells('N'.$merge_start.':N'.$merge_end);
						}
						$sheet->mergeCells('O'.$merge_start.':O'.$merge_end);
					    $sheet->mergeCells('P'.$merge_start.':P'.$merge_end);
						$sheet->mergeCells('Q'.$merge_start.':Q'.$merge_end);
                        $sheet->mergeCells('R'.$merge_start.':R'.$merge_end);
						

                        $merge_start = $row;
                    }

                    if ($serial_no_row > $row) {
                        $sheet->mergeCells('K'.$serial_no_row.':K'.$merge_end);
                    }
                    
                    if ($probe_pin_lot_row > $row) {
						$sheet->mergeCells('H'.$probe_pin_lot_row.':H'.$merge_end);
                        $sheet->mergeCells('M'.$probe_pin_lot_row.':M'.$merge_end);
                    }
                    
                    if ($defect_row > $row) {
                        $sheet->mergeCells('N'.$defect_row.':N'.$merge_end);
                    }
					
					
					$footer = $row + 2;

                    $sheet->cell('B'.$footer, "Total Qty:");
                    $sheet->cell('C'.$footer, $info->total_qty);
                    $sheet->setHeight($footer,20);
                    $footer++;

                    $sheet->cell('B'.$footer, "Balance:");
                    $sheet->cell('C'.$footer, ($info->balance < 1)? '0.00':$info->balance);
                    $sheet->setHeight($footer,20);
                    $footer++;

                    $sheet->cell('B'.$footer, "Date:");
                    $sheet->cell('C'.$footer, $date_today);
                    $sheet->setHeight($footer,20);
				
					

                    $sheet->setWidth([
                        'A' => 2,
                        'B' => 16,
                        'C' => 28,
                        'D' => 10,
                        'E' => 20,
                        'F' => 25,
                        'G' => 10,
                        'H' => 10,
                        'I' => 25,
                        'J' => 15,
                        'K' => 20,
                        'L' => 20,
                        'M' => 20,
                        'N' => 8,
                        'O' => 12,
                        'P' => 20,
                        'Q' => 20,
                        'R' => 100
                    ]);
                });				
				
            }

		})->download('xlsx');
    }


	//OQC INSPECTION REPORT DATA CHECK
	public function ReportDataCheck(Request $req)
	{
		// $po = '';
		// $date = '';
		// $data = [];
		// $check = 0;

		// if ($req->from !== '' || !empty($req->from)) {
		// 	$date = " AND a.date_inspected BETWEEN '".$this->com->convertDate($req->from,'Y-m-d').
		// 			"' AND '".$this->com->convertDate($req->to,'Y-m-d')."'";
		// }

		// if ($req->po !== '' || !empty($req->po)) {
		// 	$po = " AND a.po_no = '".$req->po."'";
		// }

		// if ($req->report_type == 'pdf') {
		// 	if ($req->po !== '' || !empty($req->po)) {
		// 		$check = DB::connection($this->mysql)->table('oqc_inspections as a')
		// 				->whereRaw("1=1".$po.$date)
		// 				->groupBy('a.prod_category',
		// 							'a.po_no',
		// 							'a.device_name',
		// 							'a.customer',
		// 							'a.po_qty',
		// 							'a.severity_of_inspection',
		// 							'a.inspection_lvl',
		// 							'a.aql',
		// 							'a.accept',
		// 							'a.reject',
		// 							'a.coc_req')
		// 				->select('a.prod_category',
		// 							'a.po_no',
		// 							'a.device_name',
		// 							'a.customer',
		// 							'a.po_qty',
		// 							'a.severity_of_inspection',
		// 							'a.inspection_lvl',
		// 							'a.aql',
		// 							'a.accept',
		// 							'a.reject',
		// 							'a.coc_req')
		// 				->count();
		// 	} else {
		// 		$check = DB::connection($this->mysql)->table('oqc_inspections as a')
		// 					->leftJoin('oqc_inspections_mod as b', function ($join) {
		// 						$join->on('a.po_no','=','b.pono');
		// 						$join->on('a.submission','=','b.submission');
		// 					})
		// 					->whereRaw("1=1".$po.$date)
		// 					->groupBy('a.po_no','a.lot_no','a.submission')
		// 					->orderBy('a.id','desc')
		// 					->select('a.id'
		// 						,DB::raw('a.fy as fy')
		// 						,DB::raw('a.ww as ww')
		// 						,DB::raw('a.date_inspected as date_inspected')
		// 						,DB::raw('a.shift as shift')
		// 						,DB::raw('a.time_ins_from as time_ins_from')
		// 						,DB::raw('a.time_ins_to as time_ins_to')
		// 						,DB::raw('a.submission as submission')
		// 						,DB::raw('a.lot_qty as lot_qty')
		// 						,DB::raw('a.sample_size as sample_size')
		// 						,DB::raw('a.num_of_defects as num_of_defects')
		// 						,DB::raw('a.lot_no as lot_no')
		// 						,DB::raw('b.mod1 as mod1')
		// 						,DB::raw("IFNULL(SUM(b.qty),0) as qty")
		// 						,DB::raw('a.judgement as judgement')
		// 						,DB::raw('a.inspector as inspector')
		// 						,DB::raw('a.remarks as remarks')
		// 						,DB::raw('a.assembly_line as assembly_line')
		// 						,DB::raw('a.app_date as app_date')
		// 						,DB::raw('a.app_time as app_time')
		// 						,DB::raw('a.prod_category as prod_category')
		// 						,DB::raw('a.po_no as po_no')
		// 						,DB::raw('a.device_name as device_name')
		// 						,DB::raw('a.customer as customer')
		// 						,DB::raw('a.po_qty as po_qty')
		// 						,DB::raw('a.family as family')
		// 						,DB::raw('a.type_of_inspection as type_of_inspection')
		// 						,DB::raw('a.severity_of_inspection as severity_of_inspection')
		// 						,DB::raw('a.inspection_lvl as inspection_lvl')
		// 						,DB::raw('a.aql as aql')
		// 						,DB::raw('a.accept as accept')
		// 						,DB::raw('a.reject as reject')
		// 						,DB::raw('a.coc_req as coc_req')
		// 						,DB::raw('a.lot_inspected as lot_inspected')
		// 						,DB::raw('a.lot_accepted as lot_accepted')
		// 						,DB::raw('a.dbcon as dbcon')
		// 						,DB::raw("IF(judgement = 'Accept','NDF',a.modid) as modid")
		// 						,DB::raw('a.type as type'))
		// 					->count();
		// 	}

		// } else {
		// 	$check = DB::connection($this->mysql)->table('oqc_inspections as a')
		// 				->leftJoin('oqc_inspections_mod as b', function ($join) {
		// 					$join->on('a.po_no','=','b.pono');
		// 					$join->on('a.submission','=','b.submission');
		// 				})
		// 				->whereRaw("1=1".$po.$date)
		// 				->groupBy('a.po_no','a.device_name','a.date_inspected','a.submission','a.judgement',
		// 						'a.prod_category','a.customer','a.severity_of_inspection',
		// 						'a.inspection_lvl','a.aql','a.accept','a.reject','a.coc_req',
		// 						'a.type_of_inspection','a.po_qty')
		// 				->select(DB::raw('a.fy as fy')
		// 					,DB::raw('a.ww as ww')
		// 					,DB::raw('a.date_inspected as date_inspected')
		// 					,DB::raw('a.shift as shift')
		// 					,DB::raw('a.time_ins_from as time_ins_from')
		// 					,DB::raw('a.time_ins_to as time_ins_to')
		// 					,DB::raw('a.submission as submission')
		// 					,DB::raw('a.lot_qty as lot_qty')
		// 					,DB::raw('a.sample_size as sample_size')
		// 					,DB::raw('a.num_of_defects as num_of_defects')
		// 					,DB::raw('a.lot_no as lot_no')
		// 					,DB::raw('b.mod1 as mod1')
		// 					,DB::raw("IFNULL(SUM(b.qty),0) as qty")
		// 					,DB::raw('a.judgement as judgement')
		// 					,DB::raw('a.inspector as inspector')
		// 					,DB::raw('a.remarks as remarks')
		// 					,DB::raw('a.assembly_line as assembly_line')
		// 					,DB::raw('a.app_date as app_date')
		// 					,DB::raw('a.app_time as app_time')
		// 					,DB::raw('a.prod_category as prod_category')
		// 					,DB::raw('a.po_no as po_no')
		// 					,DB::raw('a.device_name as device_name')
		// 					,DB::raw('a.customer as customer')
		// 					,DB::raw('a.po_qty as po_qty')
		// 					,DB::raw('a.family as family')
		// 					,DB::raw('a.type_of_inspection as type_of_inspection')
		// 					,DB::raw('a.severity_of_inspection as severity_of_inspection')
		// 					,DB::raw('a.inspection_lvl as inspection_lvl')
		// 					,DB::raw('a.aql as aql')
		// 					,DB::raw('a.accept as accept')
		// 					,DB::raw('a.reject as reject')
		// 					,DB::raw('a.coc_req as coc_req')
		// 					,DB::raw('a.lot_inspected as lot_inspected')
		// 					,DB::raw('a.lot_accepted as lot_accepted')
		// 					,DB::raw('a.dbcon as dbcon')
		// 					,DB::raw("IF(judgement = 'Accept','NDF',a.modid) as modid")
		// 					,DB::raw('a.type as type'))
		// 				->count();
		// }

		// $data = ['DataCount' => $check];

		// return response()->json($data);

		$data = [
            'return_status' => 0            
        ];

        $po = $req->po;
		$from = $req->from;
		$to = $req->to;
		$chosen = $req->chosen;


        if ($from !== '' || !empty($from)) {
            if($to === '' || empty($to)){
				$to = $from;
            }
        }

        $countData = "";
        if($from != "" && $to != ""){
            $countData = "SELECT * FROM oqc_inspections WHERE date_inspected BETWEEN '".$this->com->convertDate($from,'Y-m-d')."' AND '". $this->com->convertDate($to,'Y-m-d') ."'";
        }else if ($from == "" && $to == ""){
            $countData = "SELECT * FROM oqc_inspections WHERE po_no = '". $po ."'";
        }
        
        $dataCount = DB::connection($this->mysql)->select($countData);

        if(count($dataCount) == 0 ){
            return $data;
        }else{
            return $data = [
                'return_status' => 1            
            ];
        }
	}

	// //OQC INSPECTION GROUP BY VALUES
	// public function GroupByValues(Request $req)
	// {
	// 	// $results = [];
 //        // $val = (!isset($req->q))? "" : $req->q;
 //        // $id = (!isset($req->id))? "" : $req->id;
 //        // $text = (!isset($req->text))? "" : $req->text;
 //        // $table = (!isset($req->table))? "" : $req->table;
 //        // $condition = (!isset($req->condition))? "" : $req->condition;
 //        // $isDistinct = (!isset($req->isDistinct))? "" : $req->isDistinct;
 //        // $display = (!isset($req->display))? "" : $req->display;
 //        // $addOptionVal = (!isset($req->addOptionVal))? "" : $req->addOptionVal;
 //        // $addOptionText = (!isset($req->addOptionText))? "" : $req->addOptionText;
 //        // $sql_query = (!isset($req->sql_query))? "" : $req->sql_query;
 //        // $orderBy = (!isset($req->orderBy))? "" : $req->orderBy;
	// 	// $field = (!isset($req->field))? "" : $req->field;
	// 	// $from = (!isset($req->from))? "" : $req->from;
	// 	// $to = (!isset($req->to))? "" : $req->to;

 //        // try {
 //        //     if ($addOptionVal != "" && $display == "id&text") {
 //        //         array_push($results, [
 //        //             'id' => $addOptionVal,
 //        //             'text' => $addOptionText
 //        //         ]);
 //        //     }

	// 	// 	$where = "";

 //        //     if ($sql_query == null || $sql_query == "") {
 //        //            $sql_query = "select distinct ".$field." as id,
	// 	// 								".$field." as `text`
	// 	// 						from oqc_inspections WHERE 1=1 "; 
 //        //                         //AND (l.judgement is null OR l.judgement = '' OR l.judgement = 'On-going')
	// 	// 		if (!empty($from) && !empty($to)) {
	// 	// 			$where .= " AND DATE_FORMAT(date_inspected, '%Y-%m-%d') BETWEEN '".$this->com->convertDate($from,'Y-m-d')."' AND '".$this->com->convertDate($to,'Y-m-d')."' ";
	// 	// 		}
	// 	// 		if (!empty($val)) {
	// 	// 			$where .= " AND ".$field." LIKE '%".$val."%' ";
	// 	// 		}
 //        //     }
            
 //        //     $results = DB::connection($this->mysql)->select($sql_query.$where);


 //        // } catch(\Exemption $e) {
 //        //     return [
 //        //         'success' => false,
 //        //         'msessage' => $e->getMessage()
 //        //     ];
 //        // }
        
 //        // return $results;

	// 	$data = DB::connection($this->mysql)->table('oqc_inspections')
	// 			->select($req->field.' as field')
	// 			->orderBy($req->field)
	// 			->distinct()
	// 			->get();

	// 	return $data;
	// }

	// //OQC INSPECTION CALCULATE DPPM
	// public function CalculateDPPM(Request $req)
	// {
	// 	return $this->DPPMTables($req,false);
	// }

	// //DPPM TABLES
	// private function DPPMTables($req,$join)
	// {
	// 	$g1 = ''; $g2 = ''; $g3 = '';
	// 	$g1c = ''; $g2c = ''; $g3c = '';
	// 	$date_inspected = '';
	// 	$groupBy = [];

	// 	// wheres
	// 	if (!empty($req->gfrom) && !empty($req->gto)) {
	// 		$date_inspected = " AND date_inspected BETWEEN '".$this->com->convertDate($req->gfrom,'Y-m-d').
	// 						"' AND '".$this->com->convertDate($req->gto,'Y-m-d')."'";
	// 	}

	// 	if (!empty($req->field1) && !empty($req->content1)) {
	// 		$g1c = " AND ".$req->field1."='".$req->content1."'";
	// 	}

	// 	if (!empty($req->field2) && !empty($req->content2)) {
	// 		$g2c = " AND ".$req->field2."='".$req->content2."'";
	// 	}

	// 	if (!empty($req->field3) && !empty($req->content3)) {
	// 		$g3c = " AND ".$req->field3."='".$req->content3."'";
	// 	}

	// 	if (!empty($req->field1)) {
	// 		$g1 = $req->field1;
	// 		array_push($groupBy, $g1);
	// 	}

	// 	if (!empty($req->field2)) {
	// 		$g2 = $req->field2;
	// 		array_push($groupBy, $g2);
	// 	}

	// 	if (!empty($req->field3)) {
	// 		$g3 = $req->field3;
	// 		array_push($groupBy, $g3);
	// 	}

	// 	$grp = implode(',',$groupBy);
	// 	// $grby = substr($grp,0,-1);

	// 	$grby = "";

	// 	if (count($groupBy) > 0) {
	// 		$grby = " GROUP BY ".$grp;
	// 	}

	// 	if ($join == false) {
	// 		$db = DB::connection($this->mysql)
	// 				->select("SELECT SUM(lot_qty) AS lot_qty,
	// 								SUM(sample_size) AS sample_size,
	// 								SUM(num_of_defects) AS num_of_defects,
	// 								SUM(lot_accepted) AS lot_accepted,
	// 								SUM(lot_rejected) AS lot_rejected,
	// 								SUM(lot_inspected) AS lot_inspected,
	// 								fy,ww,date_inspected,shift,
	// 								time_ins_from,time_ins_to,submission,
	// 								lot_no,judgement,inspector,remarks,
	// 								assembly_line,customer,po_no,aql,
	// 								prod_category,coc_req,type_of_inspection,
	// 								severity_of_inspection,family,device_name
	// 							FROM oqc_inspections
	// 							WHERE 1=1".$date_inspected.$g1c.$g2c.$g3c.$grby);
	// 	} else {

	// 		$db = DB::connection($this->mysql)
	// 			->select("SELECT SUM(i.lot_qty) AS lot_qty,
	// 							SUM(i.sample_size) AS sample_size,
	// 							SUM(i.num_of_defects) AS num_of_defects,
	// 							SUM(i.lot_accepted) AS lot_accepted,
	// 							SUM(i.lot_rejected) AS lot_rejected,
	// 							SUM(i.lot_inspected) AS lot_inspected,
	// 							fy,ww,date_inspected,shift,
	// 							time_ins_from,time_ins_to,submission,
	// 							lot_no,judgement,inspector,remarks,
	// 							assembly_line,customer,po_no,aql,
	// 							prod_category, coc_req, type_of_inspection,
	// 							severity_of_inspection,family,device_name
	// 						FROM oqc_inspections as i
	// 					LEFT JOIN oqc_inspections_mod as m ON i.po_no = m.pono
	// 					WHERE 1=1".$date_inspected.$g1c.$g2c.$g3c.$grby);
	// 	}

	// 	if ($this->com->checkIfExistObject($db) > 0) {
	// 		return $db;
	// 	}
	// }

	// //OQC INSPECTION SAMPLING PLAN
	// public function SamplingPlan(Request $req)
	// {
	// 	$code = DB::connection($this->mysql)->table('oqc_sampling_plan_inspection_level')
	// 				->whereRaw($req->lot_qty .' BETWEEN size_from AND size_to')
	// 				->select(DB::raw($req->il.' as code'))
	// 				->first();
	// 	return $this->getSamplingPlanValues($req,$code->code);
	// }

	// //GET SAMPLING PLAN VALUES
	// private function getSamplingPlanValues($req,$code)
	// {
	// 	$type = '';
	// 	$severity_size = '';
	// 	$data = [];
	// 	switch ($req->soi) {
	// 		case 'Normal':
	// 			$severity_size = 'sample_size_normal';
	// 			$type = 'Normal';
	// 			break;

	// 		case 'Tightened':
	// 			$severity_size = 'sample_size_tightened';
	// 			$type = 'Tightened';
	// 			break;

	// 		case 'Reduced':
	// 			$severity_size = 'sample_size_reduced';
	// 			$type = 'Reduced';
	// 			break;

	// 		default:
	// 			# code...
	// 			break;
	// 	}

	// 	if (is_numeric($req->aql)) {
	// 		$size = DB::connection($this->mysql)->table('oqc_sampling_plan_sample_size')
	// 					->where('sample_size_code',$code)
	// 					->select($severity_size.' as size')
	// 					->first();
	// 		$plan = DB::connection($this->mysql)->table('oqc_aql_ac_re')
	// 					->where('size',$size->size)
	// 					->where('type_of_inspection',$type)
	// 					->select('size',
	// 							DB::raw("`".$req->aql."_ac` as accept"),
	// 							DB::raw("`".$req->aql."_re` as reject"))
	// 					->first();
	// 		if ($plan->accept == null) {
	// 			$splan = DB::connection($this->mysql)->table('oqc_aql_ac_re')
	// 					->where('size',$plan->reject)
	// 					->where('type_of_inspection',$type)
	// 					->select('size',
	// 							DB::raw("`".$req->aql."_ac` as accept"),
	// 							DB::raw("`".$req->aql."_re` as reject"))
	// 					->first();

	// 			if ($req->lot_qty >= $splan->size) {
	// 				$data = [
	// 					'size' => $splan->size,
	// 					'accept' => $splan->accept,
	// 					'reject' => $splan->reject
	// 				];
	// 			} else {
	// 				$data = [
	// 					'size' => $req->lot_qty,
	// 					'accept' => $splan->accept,
	// 					'reject' => $splan->reject
	// 				];
	// 			}
				
	// 			return response()->json($data);
	// 		}

	// 		if ($req->lot_qty >= $plan->size) {
	// 			$data = [
	// 				'size' => $plan->size,
	// 				'accept' => $plan->accept,
	// 				'reject' => $plan->reject
	// 			];
	// 		} else {
	// 			$data = [
	// 				'size' => $req->lot_qty,
	// 				'accept' => $plan->accept,
	// 				'reject' => $plan->reject
	// 			];
	// 		}
	// 	} else {
	// 		//return response()->json($data = ['gago' => 'gago']);
	// 		return $this->nonNumericAQL($req);
	// 	}
		

	// 	return response()->json($data);
	// }

	//OQC INSPECTION GROUP BY VALUES
	public function GroupByValues(Request $req)
	{
		// $results = [];
        // $val = (!isset($req->q))? "" : $req->q;
        // $id = (!isset($req->id))? "" : $req->id;
        // $text = (!isset($req->text))? "" : $req->text;
        // $table = (!isset($req->table))? "" : $req->table;
        // $condition = (!isset($req->condition))? "" : $req->condition;
        // $isDistinct = (!isset($req->isDistinct))? "" : $req->isDistinct;
        // $display = (!isset($req->display))? "" : $req->display;
        // $addOptionVal = (!isset($req->addOptionVal))? "" : $req->addOptionVal;
        // $addOptionText = (!isset($req->addOptionText))? "" : $req->addOptionText;
        // $sql_query = (!isset($req->sql_query))? "" : $req->sql_query;
        // $orderBy = (!isset($req->orderBy))? "" : $req->orderBy;
		// $field = (!isset($req->field))? "" : $req->field;
		// $from = (!isset($req->from))? "" : $req->from;
		// $to = (!isset($req->to))? "" : $req->to;

        // try {
        //     if ($addOptionVal != "" && $display == "id&text") {
        //         array_push($results, [
        //             'id' => $addOptionVal,
        //             'text' => $addOptionText
        //         ]);
        //     }

		// 	$where = "";

        //     if ($sql_query == null || $sql_query == "") {
        //            $sql_query = "select distinct ".$field." as id,
		// 								".$field." as `text`
		// 						from oqc_inspections WHERE 1=1 "; 
        //                         //AND (l.judgement is null OR l.judgement = '' OR l.judgement = 'On-going')
		// 		if (!empty($from) && !empty($to)) {
		// 			$where .= " AND DATE_FORMAT(date_inspected, '%Y-%m-%d') BETWEEN '".$this->com->convertDate($from,'Y-m-d')."' AND '".$this->com->convertDate($to,'Y-m-d')."' ";
		// 		}
		// 		if (!empty($val)) {
		// 			$where .= " AND ".$field." LIKE '%".$val."%' ";
		// 		}
        //     }
            
        //     $results = DB::connection($this->mysql)->select($sql_query.$where);


        // } catch(\Exemption $e) {
        //     return [
        //         'success' => false,
        //         'msessage' => $e->getMessage()
        //     ];
        // }
        
        // return $results;

		$data = DB::connection($this->mysql)->table('oqc_inspections')
				->select($req->field.' as field')
				->orderBy($req->field)
				->distinct()
				->get();

		return $data;
	}

	//OQC INSPECTION CALCULATE DPPM
	public function CalculateDPPM(Request $req)
	{
		return $this->DPPMTables($req,false);
	}

	//DPPM TABLES
	private function DPPMTables($req,$join)
	{
		$g1 = ''; $g2 = ''; $g3 = '';
		$g1c = ''; $g2c = ''; $g3c = '';
		$date_inspected = '';
		$groupBy = [];

		// wheres
		if (!empty($req->gfrom) && !empty($req->gto)) {
			$date_inspected = " AND date_inspected BETWEEN '".$this->com->convertDate($req->gfrom,'Y-m-d').
							"' AND '".$this->com->convertDate($req->gto,'Y-m-d')."'";
		}

		if (!empty($req->field1) && !empty($req->content1)) {
			$g1c = " AND ".$req->field1."='".$req->content1."'";
		}

		if (!empty($req->field2) && !empty($req->content2)) {
			$g2c = " AND ".$req->field2."='".$req->content2."'";
		}

		if (!empty($req->field3) && !empty($req->content3)) {
			$g3c = " AND ".$req->field3."='".$req->content3."'";
		}

		if (!empty($req->field1)) {
			$g1 = $req->field1;
			array_push($groupBy, $g1);
		}

		if (!empty($req->field2)) {
			$g2 = $req->field2;
			array_push($groupBy, $g2);
		}

		if (!empty($req->field3)) {
			$g3 = $req->field3;
			array_push($groupBy, $g3);
		}

		$grp = implode(',',$groupBy);
		// $grby = substr($grp,0,-1);

		$grby = "";

		if (count($groupBy) > 0) {
			$grby = " GROUP BY ".$grp;
		}

		if ($join == false) {
			$db = DB::connection($this->mysql)
					->select("SELECT SUM(lot_qty) AS lot_qty,
									SUM(sample_size) AS sample_size,
									SUM(num_of_defects) AS num_of_defects,
									SUM(lot_accepted) AS lot_accepted,
									SUM(lot_rejected) AS lot_rejected,
									SUM(lot_inspected) AS lot_inspected,
									fy,ww,date_inspected,shift,
									time_ins_from,time_ins_to,submission,
									lot_no,judgement,inspector,remarks,
									assembly_line,customer,po_no,aql,
									prod_category,coc_req,type_of_inspection,
									severity_of_inspection,family,device_name
								FROM oqc_inspections
								WHERE 1=1".$date_inspected.$g1c.$g2c.$g3c.$grby);
		} else {

			$db = DB::connection($this->mysql)
				->select("SELECT SUM(i.lot_qty) AS lot_qty,
								SUM(i.sample_size) AS sample_size,
								SUM(i.num_of_defects) AS num_of_defects,
								SUM(i.lot_accepted) AS lot_accepted,
								SUM(i.lot_rejected) AS lot_rejected,
								SUM(i.lot_inspected) AS lot_inspected,
								fy,ww,date_inspected,shift,
								time_ins_from,time_ins_to,submission,
								lot_no,judgement,inspector,remarks,
								assembly_line,customer,po_no,aql,
								prod_category, coc_req, type_of_inspection,
								severity_of_inspection,family,device_name
							FROM oqc_inspections as i
						LEFT JOIN oqc_inspections_mod as m ON i.po_no = m.pono
						WHERE 1=1".$date_inspected.$g1c.$g2c.$g3c.$grby);
		}

		if ($this->com->checkIfExistObject($db) > 0) {
			return $db;
		}
	}

	//OQC INSPECTION SAMPLING PLAN
	public function SamplingPlan(Request $req)
	{
		$code = DB::connection($this->mysql)->table('oqc_sampling_plan_inspection_level')
					->whereRaw($req->lot_qty .' BETWEEN size_from AND size_to')
					->select(DB::raw($req->il.' as code'))
					->first();
		return $this->getSamplingPlanValues($req,$code->code);
	}

	//GET SAMPLING PLAN VALUES
	private function getSamplingPlanValues($req,$code)
	{
		$type = '';
		$severity_size = '';
		$data = [];
		switch ($req->soi) {
			case 'Normal':
				$severity_size = 'sample_size_normal';
				$type = 'Normal';
				break;

			case 'Tightened':
				$severity_size = 'sample_size_tightened';
				$type = 'Tightened';
				break;

			case 'Reduced':
				$severity_size = 'sample_size_reduced';
				$type = 'Reduced';
				break;

			default:
				# code...
				break;
		}

		if (is_numeric($req->aql)) {
			$size = DB::connection($this->mysql)->table('oqc_sampling_plan_sample_size')
						->where('sample_size_code',$code)
						->select($severity_size.' as size')
						->first();
			$plan = DB::connection($this->mysql)->table('oqc_aql_ac_re')
						->where('size',$size->size)
						->where('type_of_inspection',$type)
						->select('size',
								DB::raw("`".$req->aql."_ac` as accept"),
								DB::raw("`".$req->aql."_re` as reject"))
						->first();
			if ($plan->accept == null) {
				$splan = DB::connection($this->mysql)->table('oqc_aql_ac_re')
						->where('size',$plan->reject)
						->where('type_of_inspection',$type)
						->select('size',
								DB::raw("`".$req->aql."_ac` as accept"),
								DB::raw("`".$req->aql."_re` as reject"))
						->first();

				if ($req->lot_qty >= $splan->size) {
					$data = [
						'size' => $splan->size,
						'accept' => $splan->accept,
						'reject' => $splan->reject
					];
				} else {
					$data = [
						'size' => $req->lot_qty,
						'accept' => $splan->accept,
						'reject' => $splan->reject
					];
				}
				
				return response()->json($data);
			}

			if ($req->lot_qty >= $plan->size) {
				$data = [
					'size' => $plan->size,
					'accept' => $plan->accept,
					'reject' => $plan->reject
				];
			} else {
				$data = [
					'size' => $req->lot_qty,
					'accept' => $plan->accept,
					'reject' => $plan->reject
				];
			}
		} else {
			//return response()->json($data = ['gago' => 'gago']);
			return $this->nonNumericAQL($req);
		}
		

		return response()->json($data);
	}

	//OQC INSPECTION NON-NUMERIC AQL
	public function nonNumericAQL($req)
	{
		$data = [
			'ins_lvl' => 'II',
			'size' => 0,
			'accept' => 0,
			'reject' => 1
		];

		if ($req->lot_qty >= 1 && $req->lot_qty <= 8) {
			$data = [
				'ins_lvl' => 'II',
				'size' => 2,
				'accept' => 0,
				'reject' => 1
			];
		}

		if ($req->lot_qty >= 9 && $req->lot_qty <= 15) {
			$data = [
				'ins_lvl' => 'II',
				'size' => 3,
				'accept' => 0,
				'reject' => 1
			];
		}

		if ($req->lot_qty >= 16 && $req->lot_qty <= 25) {
			$data = [
				'ins_lvl' => 'II',
				'size' => 5,
				'accept' => 0,
				'reject' => 1
			];
		}

		if ($req->lot_qty >= 26 && $req->lot_qty <= 50) {
			$data = [
				'ins_lvl' => 'II',
				'size' => 8,
				'accept' => 0,
				'reject' => 1
			];
		}

		if ($req->lot_qty >= 51) {
			$data = [
				'ins_lvl' => 'II',
				'size' => 13,
				'accept' => 0,
				'reject' => 1
			];
		}

		return response()->json($data);
	}

	//OQC INSPECTION GET NUMBER OF DEFECTIVES
	public function getNumOfDefectives(Request $req)
	{
		// $db = DB::connection($this->mysql)
		// 		->select("SELECT SUM(b.qty) as no_of_defectives
		// 				from oqc_inspections as a
		// 				left join oqc_inspections_mod as b
		// 				on a.lot_no = b.lotno and a.po_no = b.pono
		// 				where a.po_no = '".$req->po_no."'
		// 				and a.lot_no = '".$req->lot_no."'
		// 				and a.submission = '".$req->submission."' LIMIT 1");
		// if (count((array)$db) > 0) {
		// 	return $db[0]->no_of_defectives;
		// } else {
		// 	return 0;
		// }

		$db = DB::connection($this->mysql)->table('oqc_inspections_mod')
                ->where('oqc_id',$req->id)
                ->where('deleted', '0')
                ->select(
                    DB::raw("SUM(qty) as no_of_defectives")
                )
                ->groupBy('oqc_id')->first();
        if (count((array)$db) > 0) {
            return $db->no_of_defectives;
        } else {
            return 0;
        }
	}

	//OQC INSPECTION GET SHIFT
	public function getShift(Request $req)
	{
		$data = [];
		$from = $this->convertTime($req->from);
		$to = $this->convertTime($req->to);
		$shift = DB::connection($this->mysql)->table('oqc_shift')
					->whereRaw("'".$from."' between time_from and time_to")
					->select('shift')
					->first();
		if (count((array)$shift) > 0) {
			return $data = [
						'shift' => $shift->shift
						];
		}

		return $data = [
						'shift' => 'Shift B'
						];
	}

	//CONVERT TIME
	private function convertTime($time)
	{
		return date('H:i:s',strtotime($time));
	}
}
