<?php
namespace App\Http\Controllers\Yielding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; #Auth facade
use Datatables;
use App\Http\Requests;
use App\Poregistration;
use App\Deviceregistration;
use App\Seriesregistration;
use App\Modregistration;
use Carbon\Carbon;
use Config;
use DB;
use Dompdf\Dompdf;
use Excel;
use PDF;

class YPYieldPerformanceSummaryController extends Controller
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

    public function yieldsumRpt(Request $request)
   {
                    $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $prodtype = $request->prodtype;
                    $family = $request->family;
                    $series = $request->series;
                    $device = $request->device;
                    $pono = $request->pono;



                try
                { 
                $dt = Carbon::now();    
                $date = substr($dt->format('Ymd'), 2);
                $path = public_path().'/Yielding_Performance_Data_Check/export';
                 //$check = DB::connection($this->mysql)->table("tbl_yielding_performance")->where('pono',$pono)->count();
                 $check = 1;
                if($check > 0){
                Excel::create('Yield_Summary_Report_'.$date, function($excel) use($request)
                {
                    $excel->sheet('Sheet1', function($sheet) use($request)
                    {
                        $sheet->setAutoSize(true);
                        $datefrom = $request->datefrom;
                        $dateto = $request->dateto;
                        $prodtype = $request->prodtype;
                        $family = $request->family;
                        $series = $request->series;
                        $device = $request->device;
                        $pono = $request->pono;

                        $arrayLetter = array("B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                      
                       
                       

                        $sheet->setCellValue('A1', 'Yield Performance Report');
                        $sheet->mergeCells('A1:E1');
                        $sheet->cell('A3',"DATE");
                        $date = date("Y-m-d");
                        $sheet->cell('B3',$date);
                        $sheet->cell('E3',"Date Froms");
                        $sheet->cell('F3',$datefrom);
                        $sheet->cell('E4',"Date To");
                        $sheet->cell('F4',$dateto);
                        $sheet->setCellValue('A6',"PRODUCT TYPE:");
                        $sheet->setCellValue('B6',$prodtype);
                        $sheet->setCellValue('A7',"FAMILY: ");
                        $sheet->setCellValue('B7',$family);
                        $sheet->setCellValue('A8',"Series Name: ");
                        if($series == null){$series = '';}
                        $sheet->setCellValue('B8',$series);
                        $sheet->setCellValue('A9',"Device Name: ");
                        $sheet->setCellValue('B9',$device);
                        $sheet->setCellValue('A10',"PO NUMBER: ");
                        $sheet->setCellValue('B10',$pono);
                        $sheet->cells('B6:B10', function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B6:B10')->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        
                        $sheet->setHeight(1,30);
                        $sheet->row(1, function ($row) {
                            $row->setFontFamily('Calibri');
                            $row->setBackground('##ADD8E6');
                            $row->setFontSize(15);
                            $row->setAlignment('center');
                            $row->setFontWeight('bold');

                        });
                        $sheet->setStyle(array(
                            'font' => array(
                                'name'      =>  'Calibri',
                                'size'      =>  10
                            )
                        ));

                       if($pono != '' && $prodtype != '' && $family != '' && $series != '' && $device != ''){
                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('device',$device)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != '' && $prodtype != '' && $family != '' && $series != ''){
                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != '' && $prodtype != '' && $family != ''){
                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != '' && $prodtype != ''){
                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->where('prodtype',$prodtype)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != ''){
                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else{
                             $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        

                        $row=12;
                        $sheet->setCellValue('A12',"DEFECTS");
                        $sheet->cells('A12', function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->mergeCells('A12:A13');
                        

                            $modOfD = [];
                            $defe = 14;
                         foreach ($Outdata as $key => $val) {
                            $modOfD[$defe] = $val->mod;
                            $defe++;
                         }
                        $fff = 14;
                        $c = count($modOfD)+14;
                        for($x = 14 ; $x < $c; $x++){
                             $sheet->setCellValue('A'.$fff,$modOfD[$x]);
                             $fff++;
                        }

                        if($pono != '' && $prodtype != '' && $family != '' && $series != '' && $device != ''){
                          $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate')
                            ->orderBy('productiondate')
                            ->groupBy('productiondate')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('device',$device)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != '' && $prodtype != '' && $family != '' && $series != ''){
                          $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate')
                            ->orderBy('productiondate')
                            ->groupBy('productiondate')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                         else if($pono != '' && $prodtype != ''){
                          $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate')
                            ->orderBy('productiondate')
                            ->groupBy('productiondate')
                            ->where('prodtype',$prodtype)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != ''){
                              $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate')
                            ->orderBy('productiondate')
                            ->groupBy('productiondate')
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else{
                              $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate')
                            ->orderBy('productiondate')
                            ->groupBy('productiondate')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();

                        }



                        $ches = $fff;
                            $twelve = 12;
                            $x=0;
                            $aa=0;
                            $dateholder = [];
                            $da = "";
                         foreach ($Outdata as $key => $val) {  
                             $d = $val->productiondate;
                             $datess = explode("-", $d);
                             switch ($datess[1]) {
                                 case '01':
                                    $da = "Jan-".$datess[2];
                                 break;
                                 case '02':
                                    $da = "Feb-".$datess[2];
                                 break;
                                 case '03':
                                    $da = "Mar-".$datess[2];
                                 break;
                                 case '04':
                                    $da = "Apr-".$datess[2];
                                 break;
                                 case '05':
                                    $da = "May-".$datess[2];
                                 break;
                                 case '06':
                                    $da = "Jun-".$datess[2];
                                 break;
                                 case '07':
                                    $da = "Jul-".$datess[2];
                                 break;
                                 case '08':
                                    $da = "Aug-".$datess[2];
                                 break;
                                 case '09':
                                    $da = "Sep-".$datess[2];
                                 break;
                                 case '10':
                                    $da = "Oct-".$datess[2];
                                 break;
                                 case '11':
                                    $da = "Nov-".$datess[2];
                                 break;
                                 case '12':
                                    $da = "Dec-".$datess[2];
                                 break;
                                 
                                 
                             }
                             //$sheet->setCellValue($arrayLetter[$x].$twelve,$val->productiondate);
                             $sheet->setCellValue($arrayLetter[$x].$twelve,$da);
                             $ot1 = $arrayLetter[$x].$twelve;
                             $y1=$x+1;
                             $ot2 = $arrayLetter[$y1].$twelve;
                             $sheet->mergeCells("$ot1:$ot2");
                             $sheet->getStyle($arrayLetter[$x].$twelve)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $sheet->cells($arrayLetter[$x].$twelve, function($cells) {$cells->setFontWeight('bold'); });
                             
                             $dateholder[$aa] = $val->productiondate;
                             $plus = $twelve+1;
                             $sheet->setCellValue($arrayLetter[$x].$plus,"PNG");
                             $sheet->getStyle($arrayLetter[$x].$plus)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $sheet->cells($arrayLetter[$x].$plus, function($cells) {$cells->setFontWeight('bold'); });
                             $s = $x+1;
                             $sheet->setCellValue($arrayLetter[$s].$plus,"MNG");
                             $sheet->getStyle($arrayLetter[$s].$plus)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $sheet->cells($arrayLetter[$s].$plus, function($cells) {$cells->setFontWeight('bold'); });
                             $x = $x+2;
                             $aa=$aa+2;
                         }
                         $twelve =12;
                         $endA = $arrayLetter[$x].$twelve;
                         $sheet->cells("A12:$endA", function($cells) {$cells->setBackground('#00FFFF'); });
                         $twelve =13;
                         $endA = $arrayLetter[$x].$twelve;
                         $sheet->cells("A12:$endA", function($cells) {$cells->setBackground('#00FFFF'); });


                        if($pono != '' && $prodtype != '' && $family != '' && $series != null && $device != ''){
                         $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('device',$device)
                            ->where('classification',"Production NG (PNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != '' && $prodtype != '' && $family != '' && $series != ''){
                         $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('classification',"Production NG (PNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != '' && $prodtype != ''){
                         $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('pono',$pono)
                            ->where('prodtype',$prodtype)
                            ->where('classification',"Production NG (PNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else if($pono != ''){
                             $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('pono',$pono)
                            ->where('classification',"Production NG (PNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                        else{
                             $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('classification',"Production NG (PNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        }
                      
                           
                           foreach ($Outdatas as $key => $value) {
                                $a = $value->productiondate;
                                $b = $value->mod;
                                $key1 = array_search($a, $dateholder);
                                $key2 = array_search($b, $modOfD);
                                $sheet->setCellValue($arrayLetter[$key1].$key2,$value->classificationCount);
                                $sheet->getStyle($arrayLetter[$key1].$key2)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            }

                            if($pono != '' && $prodtype != '' && $family != '' && $series != '' && $device != ''){
                            $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('device',$device)
                            ->where('classification',"Material NG (MNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                            }
                            else if($pono != '' && $prodtype != '' && $family != '' && $series != ''){
                            $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('classification',"Material NG (MNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                            }
                            else if($pono != '' && $prodtype != ''){
                            $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('pono',$pono)
                            ->where('prodtype',$prodtype)
                            ->where('classification',"Material NG (MNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                            }
                            else if($pono != ''){
                            $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('pono',$pono)
                            ->where('classification',"Material NG (MNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                            }
                            else{
                            $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('classification',"Material NG (MNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                            }
                          
                           

                           foreach ($Outdatas as $key => $value) {
                                $a = $value->productiondate;
                                $b = $value->mod;
                                $key1 = array_search($a, $dateholder);
                                $key2 = array_search($b, $modOfD);
                                $key1 = $key1+1;
                                $sheet->setCellValue($arrayLetter[$key1].$key2,$value->classificationCount);
                                $sheet->getStyle($arrayLetter[$key1].$key2)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            }

                            
                            $out = $ches+1;
                            $y=0;
                            for($x=0;$x<count($dateholder);$x++)
                            {
                                $ywmng = $ches + 4;
                                $twoyieldd = $ches + 5;

                            if($pono != '' && $prodtype != '' && $family != '' && $series != '' && $device != ''){
                             $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate','ywomng','twoyield')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('device',$device)
                            ->where('classification','like','%PNG%')
                            ->orwhere('classification','like','%MNG%')
                            ->where('productiondate', $dateholder[$y])
                            ->get();
                             }
                             else if($pono != '' && $prodtype != '' && $family != '' && $series != ''){
                            $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate','ywomng','twoyield')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('classification','like','%PNG%')
                            ->orwhere('classification','like','%MNG%')
                            ->where('productiondate', $dateholder[$y])
                            ->get();
                             }
                             else if($pono != '' && $prodtype != ''){
                            $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate','ywomng','twoyield')
                            ->where('prodtype',$prodtype)
                            ->where('pono',$pono)
                            ->where('classification','like','%PNG%')
                            ->orwhere('classification','like','%MNG%')
                            ->where('productiondate', $dateholder[$y])
                            ->get();
                             }
                             else if($pono != ''){
                            $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate','ywomng','twoyield')
                            ->where('pono',$pono)
                            ->where('classification','like','%PNG%')
                            ->orwhere('classification','like','%MNG%')
                            ->where('productiondate', $dateholder[$y])
                            ->get();
                             }
                            else{
                            $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate','ywomng','twoyield')
                            ->where('classification','like','%PNG%')
                            ->orwhere('classification','like','%MNG%')
                            ->where('productiondate', $dateholder[$y])
                            ->get();
                             }



                            foreach ($Outdata as $key => $value) {
                                 $sheet->setCellValue($arrayLetter[$y].$out,$value->accumulatedoutput);
                                 $ot1 = $arrayLetter[$y].$out;
                                 $y1=$y+1;
                                 $ot2 = $arrayLetter[$y1].$out;
                                 $sheet->mergeCells("$ot1:$ot2");
                                 $sheet->getStyle($arrayLetter[$y].$out)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                 $sheet->setCellValue($arrayLetter[$y].$ywmng,$value->ywomng/100);
                                 $ot1 = $arrayLetter[$y].$ywmng;
                                 $y1=$y+1;
                                 $ot2 = $arrayLetter[$y1].$ywmng;
                                 $sheet->mergeCells("$ot1:$ot2");
                                 $sheet->getStyle($arrayLetter[$y].$ywmng)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                 $sheet->setColumnFormat(array($arrayLetter[$y].$ywmng => '0%' ));
                                 $sheet->setCellValue($arrayLetter[$y].$twoyieldd,$value->twoyield/100);
                                 $ot1 = $arrayLetter[$y].$twoyieldd;
                                 $y1=$y+1;
                                 $ot2 = $arrayLetter[$y1].$twoyieldd;
                                 $sheet->mergeCells("$ot1:$ot2");
                                 $sheet->getStyle($arrayLetter[$y].$twoyieldd)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                 $sheet->setColumnFormat(array($arrayLetter[$y].$twoyieldd => '0%' ));
                            }
                            $y=$y+2;
                            }
                            
                            $a=count($dateholder);
                            $first = $ches-3;
                            $last = $fff-1;
                            $PNG = $last + 3;
                            $skipPNG = 0;
                            for($x=0;$x<$a;$x++)
                            {
                            $start1 = $arrayLetter[$skipPNG].$first;
                            $end = $arrayLetter[$skipPNG].$last;
                            $sheet->cell($arrayLetter[$skipPNG].$PNG, "=SUM($start1:$end)"); 
                            $ot1 = $arrayLetter[$skipPNG].$PNG;
                            $y1=$skipPNG+1;
                            $ot2 = $arrayLetter[$y1].$PNG;
                            $sheet->mergeCells("$ot1:$ot2");  
                             //PNG
                            $sheet->getStyle($arrayLetter[$skipPNG].$PNG)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $skipPNG = $skipPNG+2;
                            }
                            $first = $ches-3;
                            $last = $fff-1;
                            $MNG = $last + 4;
                            $skipMNG = 1;
                            $skipPNG = 0;
                            for($x=0;$x<$a;$x++)
                            {
                            $start1 = $arrayLetter[$skipMNG].$first;
                            $end = $arrayLetter[$skipMNG].$last;
                            $sheet->cell($arrayLetter[$skipPNG].$MNG, "=SUM($start1:$end)");
                            $ot1 = $arrayLetter[$skipPNG].$MNG;
                            $y1=$skipPNG+1;
                            $ot2 = $arrayLetter[$y1].$MNG;
                            $sheet->mergeCells("$ot1:$ot2");    
                             //MNG
                            $sheet->getStyle($arrayLetter[$skipPNG].$MNG)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $skipMNG = $skipMNG+2;
                            $skipPNG = $skipPNG+2;
                            }
                            $skipper = 0;
                            $inp = $fff;
                           
                            for($x=0;$x<$a;$x++)
                            {
                                $c = $inp+1;
                                $start = $arrayLetter[$skipper].$c;
                                $b = $inp+3;
                                $end = $arrayLetter[$skipper].$b;
                               
                                $sheet->cell($arrayLetter[$skipper].$inp, "=SUM($start:$end)"); //INPUT
                                $ot1 = $arrayLetter[$skipper].$inp;
                                $y1=$skipper+1;
                                $ot2 = $arrayLetter[$y1].$inp;
                                $sheet->mergeCells("$ot1:$ot2"); 
                                $sheet->getStyle($arrayLetter[$skipper].$inp)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                $skipper = $skipper+2;
                            }
                        
                       




                        $defe = $ches;
                        $start = $defe;
                        $sheet->cell('A'.$defe, "INPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "OUTPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "PRODUCTION-NG"); 
                        $defe++; 
                        $sheet->cell('A'.$defe, "MATERIAL-NG"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "Yield w/o MNG(%)"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL Yield(%)"); 
                        $end = $defe;
                       
                        $s = "A$start";
                        $e = "A$end";
                     $sheet->cells("$s:$e", function($cells) {$cells->setBackground('#FFCC00'); });
                     $sheet->cells("$s:$e", function($cells) {$cells->setFontWeight('bold'); });

                     $defe = $defe + 5;
                         $start = $defe;
                        $sheet->cell('A'.$defe, "TOTAL INPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL OUTPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL PRODUCTION-NG"); 
                        $defe++; 
                        $sheet->cell('A'.$defe, "TOTAL MATERIAL-NG"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "Yield w/o MNG(%)"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL Yield(%)"); 
                         $end = $defe;
                          $s = "A$start";
                        $e = "A$end";
                     $sheet->cells("$s:$e", function($cells) {$cells->setBackground('#00FF00'); });
                     $sheet->cells("$s:$e", function($cells) {$cells->setFontWeight('bold'); });

                      $totalInput = $fff+10;
                      $sumTI=0;
                      $skipper = 0;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumTI = $sumTI+$sheet->getcell($arrayLetter[$skipper].$fff)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumTI); 
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FOR TOTAL INPUT
                      

                      $totalInput = $fff+11;
                      $sumTO=0;
                      $skipper = 0;
                      $f1 = $fff+1;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumTO = $sumTO+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumTO); 
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FOR TOTAL OUTPUT
                      

                      $totalInput = $fff+12;
                      $sumPNG=0;
                      $skipper = 0;
                      $f1 = $fff+2;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumPNG = $sumPNG+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumPNG);
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); }); 
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FORPNG
                      

                      $totalInput = $fff+13;
                      $sumMNG=0;
                      $skipper = 0;
                      $f1 = $fff+3;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumMNG = $sumMNG+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumMNG); 
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FORMNG
                      
                      $totalInput = $fff+14;
                      $sumYWM=0;
                      $skipper = 0;
                      $f1 = $fff+4;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumYWM = $sumYWM+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                      //  $sheet->cell('B'.$totalInput, $sumYWM); 
                        $skipper = $skipper+2;
                      }
                      $a = count($dateholder);
                      if($a == 0)
                      {
                        $a = 1;
                      }
                   
                       $sheet->cell('B'.$totalInput, ($sumYWM/$a)); 
                       $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                       $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                       $sheet->setColumnFormat(array('B'.$totalInput => '0%' ));
                
                         
                      //YWMNG
                      
                      $totalInput = $fff+15;
                      $sumTY=0;
                      $skipper = 0;
                      $f1 = $fff+5;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumTY = $sumTY+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                      //  $sheet->cell('B'.$totalInput, $sumYWM); 
                        $skipper = $skipper+2;
                      }
                       $sheet->cell('B'.$totalInput, ($sumTY/$a)); 
                       $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                       $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                       $sheet->setColumnFormat(array('B'.$totalInput => '0%' ));
                      //YWMNG

                    });
                })->download('xls');
                }
                else{
                    //no DATA
                }

                } catch (Exception $e) {
                    return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
                }
    }

    public function yieldsumRptpdf(Request $request)
    {
        $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $prodtype = $request->prodtype;
                    $family = $request->family;
                    $series = $request->series;
                    $device = $request->device;
                    $pono = $request->pono;

        try
            { 
                $dt = Carbon::now();    
                $date = substr($dt->format('Ymd'), 2);
                $path = public_path().'/Yielding_Performance_Data_Check/export';
                 $check = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('prodtype',$prodtype)->where('family',$family)->where('series',$series)->where('pono',$pono)->where('device',$device)->count();
                if($check > 0){
          
                
                Excel::create('Yield_Summary_Report_'.$date, function($excel) use($request)
                {
                    $excel->sheet('Sheet1', function($sheet) use($request)
                    {
                        $sheet->setAutoSize(true);
                        $datefrom = $request->datefrom;
                        $dateto = $request->dateto;
                        $prodtype = $request->prodtype;
                        $family = $request->family;
                        $series = $request->series;
                        $device = $request->device;
                        $pono = $request->pono;

                        $arrayLetter = array("B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                      
                        

                        $sheet->setCellValue('A1', 'Yield Performance Report');
                        $sheet->mergeCells('A1:E1');
                        $sheet->cell('A3',"DATE");
                        $date = date("Y-m-d");
                        $sheet->cell('B3',$date);
                        $sheet->cell('E3',"Date Froms");
                        $sheet->cell('F3',$datefrom);
                        $sheet->cell('E4',"Date To");
                        $sheet->cell('F4',$dateto);
                        $sheet->setCellValue('A6',"PRODUCT TYPE:");
                        $sheet->setCellValue('B6',$prodtype);
                        $sheet->setCellValue('A7',"FAMILY: ");
                        $sheet->setCellValue('B7',$family);
                        $sheet->setCellValue('A8',"Series Name: ");
                        $sheet->setCellValue('B8',$series);
                        $sheet->setCellValue('A9',"Device Name: ");
                        $sheet->setCellValue('B9',$device);
                        $sheet->setCellValue('A10',"PO NUMBER: ");
                        $sheet->setCellValue('B10',$pono);
                        $sheet->cells('B6:B10', function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B6:B10')->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        
                        $sheet->setHeight(1,30);
                        $sheet->row(1, function ($row) {
                            $row->setFontFamily('Calibri');
                            $row->setBackground('##ADD8E6');
                            $row->setFontSize(15);
                            $row->setAlignment('center');
                            $row->setFontWeight('bold');

                        });
                        $sheet->setStyle(array(
                            'font' => array(
                                'name'      =>  'Calibri',
                                'size'      =>  10
                            )
                        ));
                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','productiondate')
                            ->groupBy('mod')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();

                        $row=12;
                        $sheet->setCellValue('A12',"DEFECTS");
                        $sheet->cells('A12', function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->mergeCells('A12:A13');
                        

                            $modOfD = [];
                            $defe = 14;
                         foreach ($Outdata as $key => $val) {
                            $modOfD[$defe] = $val->mod;
                            $defe++;
                         }
                        $fff = 14;
                        $c = count($modOfD)+14;
                        for($x = 14 ; $x < $c; $x++){
                             $sheet->setCellValue('A'.$fff,$modOfD[$x]);
                             $fff++;
                        }


                           $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate')
                            ->orderBy('productiondate')
                            ->groupBy('productiondate')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                        $ches = $fff;
                            $twelve = 12;
                            $x=0;
                            $aa=0;
                            $dateholder = [];
                            $da = "";
                         foreach ($Outdata as $key => $val) {  
                             $d = $val->productiondate;
                             $datess = explode("-", $d);
                             switch ($datess[1]) {
                                 case '01':
                                    $da = "Jan-".$datess[2];
                                 break;
                                 case '02':
                                    $da = "Feb-".$datess[2];
                                 break;
                                 case '03':
                                    $da = "Mar-".$datess[2];
                                 break;
                                 case '04':
                                    $da = "Apr-".$datess[2];
                                 break;
                                 case '05':
                                    $da = "May-".$datess[2];
                                 break;
                                 case '06':
                                    $da = "Jun-".$datess[2];
                                 break;
                                 case '07':
                                    $da = "Jul-".$datess[2];
                                 break;
                                 case '08':
                                    $da = "Aug-".$datess[2];
                                 break;
                                 case '09':
                                    $da = "Sep-".$datess[2];
                                 break;
                                 case '10':
                                    $da = "Oct-".$datess[2];
                                 break;
                                 case '11':
                                    $da = "Nov-".$datess[2];
                                 break;
                                 case '12':
                                    $da = "Dec-".$datess[2];
                                 break;
                                 
                                 
                             }
                             //$sheet->setCellValue($arrayLetter[$x].$twelve,$val->productiondate);
                             $sheet->setCellValue($arrayLetter[$x].$twelve,$da);
                             $ot1 = $arrayLetter[$x].$twelve;
                             $y1=$x+1;
                             $ot2 = $arrayLetter[$y1].$twelve;
                             $sheet->mergeCells("$ot1:$ot2");
                             $sheet->getStyle($arrayLetter[$x].$twelve)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $sheet->cells($arrayLetter[$x].$twelve, function($cells) {$cells->setFontWeight('bold'); });
                             
                             $dateholder[$aa] = $val->productiondate;
                             $plus = $twelve+1;
                             $sheet->setCellValue($arrayLetter[$x].$plus,"PNG");
                             $sheet->getStyle($arrayLetter[$x].$plus)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $sheet->cells($arrayLetter[$x].$plus, function($cells) {$cells->setFontWeight('bold'); });
                             $s = $x+1;
                             $sheet->setCellValue($arrayLetter[$s].$plus,"MNG");
                             $sheet->getStyle($arrayLetter[$s].$plus)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $sheet->cells($arrayLetter[$s].$plus, function($cells) {$cells->setFontWeight('bold'); });
                             $x = $x+2;
                             $aa=$aa+2;
                         }
                         $twelve =12;
                         $endA = $arrayLetter[$x].$twelve;
                         $sheet->cells("A12:$endA", function($cells) {$cells->setBackground('#00FFFF'); });
                         $twelve =13;
                         $endA = $arrayLetter[$x].$twelve;
                         $sheet->cells("A12:$endA", function($cells) {$cells->setBackground('#00FFFF'); });

                         $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('classification',"Production NG (PNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                           
                           foreach ($Outdatas as $key => $value) {
                                $a = $value->productiondate;
                                $b = $value->mod;
                                $key1 = array_search($a, $dateholder);
                                $key2 = array_search($b, $modOfD);
                                $sheet->setCellValue($arrayLetter[$key1].$key2,$value->classificationCount);
                                $sheet->getStyle($arrayLetter[$key1].$key2)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            }


                            $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate',DB::raw("COUNT(*) as classificationCount"))
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('classification',"Material NG (MNG)")
                            ->groupBy('mod')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->get();
                           
                           foreach ($Outdatas as $key => $value) {
                                $a = $value->productiondate;
                                $b = $value->mod;
                                $key1 = array_search($a, $dateholder);
                                $key2 = array_search($b, $modOfD);
                                $key1 = $key1+1;
                                $sheet->setCellValue($arrayLetter[$key1].$key2,$value->classificationCount);
                                $sheet->getStyle($arrayLetter[$key1].$key2)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            }

                            
                            $out = $ches+1;
                            $y=0;
                            for($x=0;$x<count($dateholder);$x++)
                            {
                                $ywmng = $ches + 4;
                                $twoyieldd = $ches + 5;
                             $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select('mod','family','prodtype','series','device','pono','toutput','accumulatedoutput','classification','productiondate','ywomng','twoyield')
                            ->where('family',$family)
                            ->where('prodtype',$prodtype)
                            ->where('series',$series)
                            ->where('pono',$pono)
                            ->where('classification','like','%PNG%')
                            ->orwhere('classification','like','%MNG%')
                            ->where('productiondate', $dateholder[$y])
                            ->get();
                            foreach ($Outdata as $key => $value) {
                                 $sheet->setCellValue($arrayLetter[$y].$out,$value->accumulatedoutput);
                                 $ot1 = $arrayLetter[$y].$out;
                                 $y1=$y+1;
                                 $ot2 = $arrayLetter[$y1].$out;
                                 $sheet->mergeCells("$ot1:$ot2");
                                 $sheet->getStyle($arrayLetter[$y].$out)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                 $sheet->setCellValue($arrayLetter[$y].$ywmng,$value->ywomng/100);
                                 $ot1 = $arrayLetter[$y].$ywmng;
                                 $y1=$y+1;
                                 $ot2 = $arrayLetter[$y1].$ywmng;
                                 $sheet->mergeCells("$ot1:$ot2");
                                 $sheet->getStyle($arrayLetter[$y].$ywmng)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                 $sheet->setColumnFormat(array($arrayLetter[$y].$ywmng => '0%' ));
                                 $sheet->setCellValue($arrayLetter[$y].$twoyieldd,$value->twoyield/100);
                                 $ot1 = $arrayLetter[$y].$twoyieldd;
                                 $y1=$y+1;
                                 $ot2 = $arrayLetter[$y1].$twoyieldd;
                                 $sheet->mergeCells("$ot1:$ot2");
                                 $sheet->getStyle($arrayLetter[$y].$twoyieldd)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                 $sheet->setColumnFormat(array($arrayLetter[$y].$twoyieldd => '0%' ));
                            }
                            $y=$y+2;
                            }
                            
                            $a=count($dateholder);
                            $first = $ches-3;
                            $last = $fff-1;
                            $PNG = $last + 3;
                            $skipPNG = 0;
                            for($x=0;$x<$a;$x++)
                            {
                            $start1 = $arrayLetter[$skipPNG].$first;
                            $end = $arrayLetter[$skipPNG].$last;
                            $sheet->cell($arrayLetter[$skipPNG].$PNG, "=SUM($start1:$end)"); 
                            $ot1 = $arrayLetter[$skipPNG].$PNG;
                            $y1=$skipPNG+1;
                            $ot2 = $arrayLetter[$y1].$PNG;
                            $sheet->mergeCells("$ot1:$ot2");  
                             //PNG
                            $sheet->getStyle($arrayLetter[$skipPNG].$PNG)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $skipPNG = $skipPNG+2;
                            }
                            $first = $ches-3;
                            $last = $fff-1;
                            $MNG = $last + 4;
                            $skipMNG = 1;
                            $skipPNG = 0;
                            for($x=0;$x<$a;$x++)
                            {
                            $start1 = $arrayLetter[$skipMNG].$first;
                            $end = $arrayLetter[$skipMNG].$last;
                            $sheet->cell($arrayLetter[$skipPNG].$MNG, "=SUM($start1:$end)");
                            $ot1 = $arrayLetter[$skipPNG].$MNG;
                            $y1=$skipPNG+1;
                            $ot2 = $arrayLetter[$y1].$MNG;
                            $sheet->mergeCells("$ot1:$ot2");    
                             //MNG
                            $sheet->getStyle($arrayLetter[$skipPNG].$MNG)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $skipMNG = $skipMNG+2;
                            $skipPNG = $skipPNG+2;
                            }
                            $skipper = 0;
                            $inp = $fff;
                           
                            for($x=0;$x<$a;$x++)
                            {
                                $c = $inp+1;
                                $start = $arrayLetter[$skipper].$c;
                                $b = $inp+3;
                                $end = $arrayLetter[$skipper].$b;
                               
                                $sheet->cell($arrayLetter[$skipper].$inp, "=SUM($start:$end)"); //INPUT
                                $ot1 = $arrayLetter[$skipper].$inp;
                                $y1=$skipper+1;
                                $ot2 = $arrayLetter[$y1].$inp;
                                $sheet->mergeCells("$ot1:$ot2"); 
                                $sheet->getStyle($arrayLetter[$skipper].$inp)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                $skipper = $skipper+2;
                            }
                        
                       




                        $defe = $ches;
                        $start = $defe;
                        $sheet->cell('A'.$defe, "INPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "OUTPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "PRODUCTION-NG"); 
                        $defe++; 
                        $sheet->cell('A'.$defe, "MATERIAL-NG"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "Yield w/o MNG(%)"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL Yield(%)"); 
                        $end = $defe;
                       
                        $s = "A$start";
                        $e = "A$end";
                     $sheet->cells("$s:$e", function($cells) {$cells->setBackground('#FFCC00'); });
                     $sheet->cells("$s:$e", function($cells) {$cells->setFontWeight('bold'); });

                     $defe = $defe + 5;
                         $start = $defe;
                        $sheet->cell('A'.$defe, "TOTAL INPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL OUTPUT"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL PRODUCTION-NG"); 
                        $defe++; 
                        $sheet->cell('A'.$defe, "TOTAL MATERIAL-NG"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "Yield w/o MNG(%)"); 
                        $defe++;
                        $sheet->cell('A'.$defe, "TOTAL Yield(%)"); 
                         $end = $defe;
                          $s = "A$start";
                        $e = "A$end";
                     $sheet->cells("$s:$e", function($cells) {$cells->setBackground('#00FF00'); });
                     $sheet->cells("$s:$e", function($cells) {$cells->setFontWeight('bold'); });

                      $totalInput = $fff+10;
                      $sumTI=0;
                      $skipper = 0;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumTI = $sumTI+$sheet->getcell($arrayLetter[$skipper].$fff)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumTI); 
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FOR TOTAL INPUT
                      

                      $totalInput = $fff+11;
                      $sumTO=0;
                      $skipper = 0;
                      $f1 = $fff+1;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumTO = $sumTO+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumTO); 
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FOR TOTAL OUTPUT
                      

                      $totalInput = $fff+12;
                      $sumPNG=0;
                      $skipper = 0;
                      $f1 = $fff+2;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumPNG = $sumPNG+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumPNG);
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); }); 
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FORPNG
                      

                      $totalInput = $fff+13;
                      $sumMNG=0;
                      $skipper = 0;
                      $f1 = $fff+3;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumMNG = $sumMNG+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                        $sheet->cell('B'.$totalInput, $sumMNG); 
                        $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $skipper = $skipper+2;
                      }
                      //FORMNG
                      
                      $totalInput = $fff+14;
                      $sumYWM=0;
                      $skipper = 0;
                      $f1 = $fff+4;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumYWM = $sumYWM+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                      //  $sheet->cell('B'.$totalInput, $sumYWM); 
                        $skipper = $skipper+2;
                      }
                       $sheet->cell('B'.$totalInput, ($sumYWM/count($dateholder))); 
                       $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                       $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                       $sheet->setColumnFormat(array('B'.$totalInput => '0%' ));
                      //YWMNG
                      
                      $totalInput = $fff+15;
                      $sumTY=0;
                      $skipper = 0;
                      $f1 = $fff+5;
                      for($x=0;$x<$a;$x++)
                      {
                        $sumTY = $sumTY+$sheet->getcell($arrayLetter[$skipper].$f1)->getCalculatedValue();
                      //  $sheet->cell('B'.$totalInput, $sumYWM); 
                        $skipper = $skipper+2;
                      }
                       $sheet->cell('B'.$totalInput, ($sumTY/count($dateholder))); 
                       $sheet->cells('B'.$totalInput, function($cells) {$cells->setFontWeight('bold'); });
                       $sheet->getStyle('B'.$totalInput)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                       $sheet->setColumnFormat(array('B'.$totalInput => '0%' ));
                      //YWMNG
                        //================================================FOR PDF
                     $arrayLetterpdf = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                        
                        $html = "";
                        $html8 = "";
                        $html9 = "";
                        $html10 = "";
                        $defe = 12;

                        $countrow = 6 + count($modOfD)+1+11;
                        $countcol = (count($dateholder)*2)+1;
                        for($x=0;$x<$countrow;$x++){
                            for($y=0;$y<$countcol;$y++){
                        $rowww[$x][$y] = $sheet->getcell($arrayLetterpdf[$y].$defe)->getCalculatedValue();
                                     }
                                      $defe++;
                        }
                        
                        $html9 = "";
                        for($x=0;$x<$countrow;$x++){
                            $html9 .= '<tr>';
                            for($y=0;$y<$countcol;$y++){
                                $html9 .= '<td>'.$rowww[$x][$y].'</td>';
                            }
                            $defe++;
                            $html9 .= '</tr>';
                        }
                            
                            $dt = Carbon::now();
                            $dompdf = new Dompdf();
                            $date = substr($dt->format('Ymd'), 2);
                            $html = '<div class="container-fluid" style="font-size:10">
                                                            <h2> DEFECTS SUMMARY</h2>
                                                            <p>Inclusive Date</p>
                                                            </br>
                                                            <p style="padding-left:6em;">Date From:'.$datefrom.'</p>
                                                            <p style="padding-left:6em;">Date From:'.$dateto.'</p>
                                                            <div style="width: 100%;margin-top: 3%;">
                                                                <table border="1" style="font-size:10">
                                                                '.$html8.'
                                                                   '.$html9.'
                                                                    </table>
                                                                </div>
                                                                <hr/>
                                                                <p align="right">
                                                                    <strong>DATE CREATED:</strong> '.$dt->format('l jS \\of F Y h:i:s A').'
                                                                </p>
                                                            </div>';

             $dompdf->loadHtml($html);

            $dompdf->setPaper('letter', 'landscape');
            $dompdf->render();
           return $dompdf->stream('YIELD Summary Report'.Carbon::now().'.pdf');

                    });

                });//->download('xls');
            }else{
                //no data
            }


            } catch (Exception $e) {
                return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
            }
    }

 
}


