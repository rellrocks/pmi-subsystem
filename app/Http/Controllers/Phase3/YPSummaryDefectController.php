<?php
namespace App\Http\Controllers\Phase3;

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

class YPSummaryDefectController extends Controller
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

    public function defectsummaryRpt(Request $request)
    {

        try
        { 
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';
            $datefrom = $request->datefrom;
            $dateto = $request->dateto;
            $ptype = $request->ptype;
            $check = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('prodtype',$ptype)->count();
            $check1 = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->count();
            if($check > 0 || $check1 > 0){
            Excel::create('Defect_Summary_Report_'.$date, function($excel) use($request)
            {
                $excel->sheet('Sheet1', function($sheet) use($request)
              {

                    $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $ptype = $request->ptype;
                    $option = $request->option;
                    $ptype = $request->ptype;
                   // $sheet->cell('Q4',$ptype);
                    $sheet->setAutoSize(true);
                    $sheet->setCellValue('A1', 'Defect Summary Per Family');
                    $sheet->mergeCells('A1:D1');
                    $sheet->cell('A3',"DATE");
                    $date = date("Y-m-d");
                    $sheet->cell('B3',$date);
                    $sheet->cell('E3',"Date Froms");
                    $sheet->cell('F3',$datefrom);
                    $sheet->cell('E4',"Date To");
                    $sheet->cell('F4',$dateto);
                    //$sheet->cell('A6',"PO No.");
                    
                  
                    $sheet->setHeight(1,30);
                    $sheet->row(1, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setBackground('##ADD8E6');
                        $row->setFontSize(15);
                        $row->setAlignment('left');
                    });

                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Calibri',
                            'size'      =>  10
                        )
                    ));
                   
                                                    $sheet->cell('B6',"Defectives");
                                                    $sheet->cells('B6', function($cells) {$cells->setFontWeight('bold'); });
                                                    $sheet->getStyle('B6')->getAlignment()->setTextRotation(90);
                                                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                                                        ->select('family','device','yieldingno','ywomng','pono','twoyield','poqty','mod',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"),DB::raw("COUNT(a.mod) as wew"),DB::raw("SUM(poqty) as sumpoqty"))
                                                        ->groupBy('mod')
                                                        ->orderBy('family')
                                                        ->whereBetween('productiondate', [$datefrom, $dateto])
                                                        ->get();

                                                        $Outdatass = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                                                        ->select('family')
                                                        ->groupBy('family')
                                                       // ->orderBy('family')
                                                        ->whereBetween('productiondate', [$datefrom, $dateto])
                                                        ->get();

                                                     $arrayLetter = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                 if($ptype == "Test Socket")
                   {
                                                      $lete = 0;
                                                      $deff = 0;
                                                      $defe = 0;
                                                     $modOfD = [];

                                                     $row = 2;
                                                     foreach ($Outdata as $key => $val) {
                                                        $modOfD[$row] = $val->mod;
                                                        $row++;
                                                     }
                                                    $Start = "B6";
                                                    $end = $arrayLetter[$row];
                                                    $a = 6;
                                                    $sheet->cells("$Start:$end$a", function($cells) {$cells->setFontWeight('bold'); });
                                                    $defe = 6;
                                                    $y = 2;
                                                    $countmod = count($modOfD);
                                                    for($x = 0 ; $x < $countmod; $x++){
                                                         $sheet->cell($arrayLetter[$x].$defe, $modOfD[$y]);
                                                         $sheet->getStyle($arrayLetter[$x].$defe)->getAlignment()->setTextRotation(90);
                                                         $y++;
                                                    }
                                                    $last = $y;
                                                    //FOR MOD

                                                    $defe = 7;
                                                    foreach ($Outdatass as $key => $val) {
                                                        $fams[$defe] = $val->family;
                                                        $defe++;
                                                     }
                                                    $defe = 7;
                                                   $countfam = count($fams);
                                                    for($x = 0 ; $x < $countfam; $x++){
                                                         $sheet->cell('B'.$defe, $fams[$defe]);
                                                        $defe++;
                                                    }
                                                    $Start = "B7";
                                                    $end = "B$defe";
                                                    $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });
                                                     $sheet->cell('B'.$defe, "TOTAL");
                                                     $l = $defe;
                                                    //FOR FAMILY

                                                     foreach ($Outdata as $key => $val) {
                                                        $famtemp = $val->family;
                                                        $modtemp = $val->mod;
                                                     $key = array_search($famtemp, $fams);
                                                     $key2 = array_search($modtemp, $modOfD);
                                                     $key2 = $key2 - 2;
                                                     $sheet->cell($arrayLetter[$key2].$key, $val->wew);
                                                 }
                                                 //FOR DATA
                                                 $defe = 7;

                                                   $last2 = $l-1;

                                                    for($x=0;$x<$countmod;$x++)
                                                    {
                                                       $start = $arrayLetter[$x].$defe;
                                                       $end = $arrayLetter[$x].$last2;
                                                       $sheet->setCellValue($arrayLetter[$x].$l, "=SUM($start:$end)");
                                                       $sheet->cells($arrayLetter[$x].$l, function($cells) {$cells->setFontWeight('bold'); });
                                                    }
                }
                else{
                     $sheet->cell('B6',"Defects");
                     $sheet->cell('B7',"PNG");
                     $sheet->cell('B8',"MNG");
                     $sheet->cell('B9',"TOTAL");
                     $sheet->cells('B6:B9', function($cells) {$cells->setFontWeight('bold'); });

                  
                   
                         $row = 0;
                         $modofD = [];
                         foreach ($Outdata as $key => $val) {
                            $modOfD[$row] = $val->mod;
                            $row++;
                         }
                        $countmod = count($modOfD);
                        $y=0;
                        $defe = 6;
                        for($x = 0 ; $x < $countmod; $x++){
                             $sheet->cell($arrayLetter[$x].$defe, $modOfD[$y]);
                             $sheet->getStyle($arrayLetter[$x].$defe)->getAlignment()->setTextRotation(90);
                             $sheet->cells($arrayLetter[$x].$defe, function($cells) {$cells->setFontWeight('bold'); });
                             $y++;
                        }
                         $sheet->getStyle('B6')->getAlignment()->setTextRotation(0);
                         $PNGC = [];
                         for($x=0;$x<$countmod;$x++)
                         {
                             $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select(DB::raw("COUNT(*) as wew"),'mod')
                            ->where('mod',$modOfD[$x])
                            ->where('classification','like','%Production%')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->orderBy('family')
                            ->get();
                            foreach ($Outdatas as $key => $value) {
                               $PNGC[$x]  = $value->wew;
                            }
                        }
                        $MNGC = [];
                        for($x=0;$x<$countmod;$x++)
                         {
                             $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select(DB::raw("COUNT(*) as wew"),'mod')
                            ->where('mod',$modOfD[$x])
                            ->where('classification','like','%Material%')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->orderBy('family')
                            ->get();
                            foreach ($Outdatas as $key => $value) {
                               $MNGC[$x]  = $value->wew;
                            }
                        }


                        $defe=7;
                        for($x = 0 ; $x < $countmod; $x++){
                             $sheet->cell($arrayLetter[$x].$defe, $PNGC[$x]);
                        }

                        $defe=8;
                        for($x = 0 ; $x < $countmod; $x++){
                             $sheet->cell($arrayLetter[$x].$defe, $MNGC[$x]);
                        }

                        $defe=9;
                        $seven = 7;
                        $eight = 8;
                        for($x = 0 ; $x < $countmod; $x++){
                            $start = $arrayLetter[$x].$seven;
                            $end = $arrayLetter[$x].$eight;
                         $sheet->setCellValue($arrayLetter[$x].$defe, "=SUM($start:$end)");
                        }


                    $sheet->cell('A12',"Total Input:");
                    $sheet->cell('A13',"Total Output");
                    $sheet->cell('A14',"Total PNG");
                    $sheet->cell('A15',"Total MNG");
                    $sheet->cell('A16',"Yield W/o MNG");
                    $sheet->cell('A17',"Total Yield");
                    $sheet->cells("A17", function($cells) {$cells->setFontColor('#FF0000'); });
                    $sheet->cells('A12:A17', function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->cells("A12:A17", function($cells) {$cells->setBackground('#CCFFCC'); });
                   foreach ($Outdata as $key => $val) {
                    
                        $sheet->cell('B12', $val->sumpoqty);
                        $sheet->cell('B13', $val->accumulatedoutput);
                        $start = $arrayLetter[0].$seven;
                        $end = $arrayLetter[$countmod].$seven;
                        $sheet->setCellValue('B14', "=SUM($start:$end)");
                        $start = $arrayLetter[0].$eight;
                        $end = $arrayLetter[$countmod].$eight;
                        $sheet->setCellValue('B15', "=SUM($start:$end)");
                        $sheet->cell('B16', $val->ywomng);
                        $sheet->cell('B17', $val->twoyield);
                    }
                    $sheet->getStyle('B12:B17')->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells('B12:B17', function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->setColumnFormat(array(
                        'B16' => '0%',
                        'B17' => '0%',
                         ));
                     $sheet->cells("B17", function($cells) {$cells->setFontColor('#FF0000'); });

                }

                 
                $a  = $sheet->getcell('B13')->getCalculatedValue();
                $a1 = $sheet->getcell('B14')->getCalculatedValue();
                $a2 = $sheet->getcell('B15')->getCalculatedValue();
                $sheet->cell('B12', $a+$a1+$a2);
               
           
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

    public function defectsummaryRptpdf(Request $request){
       
        try
        { 
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';
            $datefrom = $request->datefrom;
            $dateto = $request->dateto;
            $ptype = $request->ptype;
            $check = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('prodtype',$ptype)->count();
            if($check > 0)
            {
            Excel::create('Defect_Summary_Report_'.$date, function($excel) use($request)
            {
                $excel->sheet('Sheet1', function($sheet) use($request)
              {

                    $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $ptype = $request->ptype;
                    $option = $request->option;
                    $ptype = $request->ptype;
                   // $sheet->cell('Q4',$ptype);
                    $sheet->setAutoSize(true);
                    $sheet->setCellValue('A1', 'Defect Summary Per Family');
                    $sheet->mergeCells('A1:D1');
                    $sheet->cell('A3',"DATE");
                    $date = date("Y-m-d");
                    $sheet->cell('B3',$date);
                    $sheet->cell('E3',"Date Froms");
                    $sheet->cell('F3',$datefrom);
                    $sheet->cell('E4',"Date To");
                    $sheet->cell('F4',$dateto);
                    //$sheet->cell('A6',"PO No.");
                    
                  
                    $sheet->setHeight(1,30);
                    $sheet->row(1, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setBackground('##ADD8E6');
                        $row->setFontSize(15);
                        $row->setAlignment('left');
                    });

                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Calibri',
                            'size'      =>  10
                        )
                    ));
                   
                                                    $sheet->cell('B6',"Defectives");
                                                    $sheet->cells('B6', function($cells) {$cells->setFontWeight('bold'); });
                                                    $sheet->getStyle('B6')->getAlignment()->setTextRotation(90);
                                                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                                                        ->select('family','device','yieldingno','ywomng','pono','twoyield','poqty','mod',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"),DB::raw("COUNT(a.mod) as wew"),DB::raw("SUM(poqty) as sumpoqty"))
                                                        ->groupBy('mod')
                                                        ->orderBy('family')
                                                        ->whereBetween('productiondate', [$datefrom, $dateto])
                                                        ->get();

                                                        $Outdatass = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                                                        ->select('family')
                                                        ->groupBy('family')
                                                       // ->orderBy('family')
                                                        ->whereBetween('productiondate', [$datefrom, $dateto])
                                                        ->get();

                                                     $arrayLetter = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                 if($ptype == "Test Socket")
                   {
                                                      $lete = 0;
                                                      $deff = 0;
                                                      $defe = 0;
                                                     $modOfD = [];

                                                     $row = 2;
                                                     foreach ($Outdata as $key => $val) {
                                                        $modOfD[$row] = $val->mod;
                                                        $row++;
                                                     }
                                                    $Start = "B6";
                                                    $end = $arrayLetter[$row];
                                                    $a = 6;
                                                    $sheet->cells("$Start:$end$a", function($cells) {$cells->setFontWeight('bold'); });
                                                    $defe = 6;
                                                    $y = 2;
                                                    $countmod = count($modOfD);
                                                    for($x = 0 ; $x < $countmod; $x++){
                                                         $sheet->cell($arrayLetter[$x].$defe, $modOfD[$y]);
                                                         $sheet->getStyle($arrayLetter[$x].$defe)->getAlignment()->setTextRotation(90);
                                                         $y++;
                                                    }
                                                    $last = $y;
                                                    //FOR MOD

                                                    $defe = 7;
                                                    foreach ($Outdatass as $key => $val) {
                                                        $fams[$defe] = $val->family;
                                                        $defe++;
                                                     }
                                                    $defe = 7;
                                                   $countfam = count($fams);
                                                    for($x = 0 ; $x < $countfam; $x++){
                                                         $sheet->cell('B'.$defe, $fams[$defe]);
                                                        $defe++;
                                                    }
                                                    $Start = "B7";
                                                    $end = "B$defe";
                                                    $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });
                                                     $sheet->cell('B'.$defe, "TOTAL");
                                                     $l = $defe;
                                                    //FOR FAMILY

                                                     foreach ($Outdata as $key => $val) {
                                                        $famtemp = $val->family;
                                                        $modtemp = $val->mod;
                                                     $key = array_search($famtemp, $fams);
                                                     $key2 = array_search($modtemp, $modOfD);
                                                     $key2 = $key2 - 2;
                                                     $sheet->cell($arrayLetter[$key2].$key, $val->wew);
                                                 }
                                                 //FOR DATA
                                                 $defe = 7;

                                                   $last2 = $l-1;

                                                    for($x=0;$x<$countmod;$x++)
                                                    {
                                                       $start = $arrayLetter[$x].$defe;
                                                       $end = $arrayLetter[$x].$last2;
                                                       $sheet->setCellValue($arrayLetter[$x].$l, "=SUM($start:$end)");
                                                    }
                                                }
                    else{
                     $sheet->cell('B6',"Defects");
                     $sheet->cell('B7',"PNG");
                     $sheet->cell('B8',"MNG");
                     $sheet->cell('B9',"TOTAL");
                     $sheet->cells('B6:B9', function($cells) {$cells->setFontWeight('bold'); });

                  
                   
                         $row = 0;
                         $modofD = [];
                         foreach ($Outdata as $key => $val) {
                            $modOfD[$row] = $val->mod;
                            $row++;
                         }
                        $countmod = count($modOfD);
                        $y=0;
                        $defe = 6;
                        for($x = 0 ; $x < $countmod; $x++){
                             $sheet->cell($arrayLetter[$x].$defe, $modOfD[$y]);
                             $sheet->getStyle($arrayLetter[$x].$defe)->getAlignment()->setTextRotation(90);
                             $sheet->cells($arrayLetter[$x].$defe, function($cells) {$cells->setFontWeight('bold'); });
                             $y++;
                        }
                         $sheet->getStyle('B6')->getAlignment()->setTextRotation(0);
                         $PNGC = [];
                         for($x=0;$x<$countmod;$x++)
                         {
                             $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select(DB::raw("COUNT(*) as wew"),'mod')
                            ->where('mod',$modOfD[$x])
                            ->where('classification','like','%Production%')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->orderBy('family')
                            ->get();
                            foreach ($Outdatas as $key => $value) {
                               $PNGC[$x]  = $value->wew;
                            }
                        }
                        $MNGC = [];
                        for($x=0;$x<$countmod;$x++)
                         {
                             $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                            ->select(DB::raw("COUNT(*) as wew"),'mod')
                            ->where('mod',$modOfD[$x])
                            ->where('classification','like','%Material%')
                            ->whereBetween('productiondate', [$datefrom, $dateto])
                            ->orderBy('family')
                            ->get();
                            foreach ($Outdatas as $key => $value) {
                               $MNGC[$x]  = $value->wew;
                            }
                        }


                        $defe=7;
                        for($x = 0 ; $x < $countmod; $x++){
                             $sheet->cell($arrayLetter[$x].$defe, $PNGC[$x]);
                        }

                        $defe=8;
                        for($x = 0 ; $x < $countmod; $x++){
                             $sheet->cell($arrayLetter[$x].$defe, $MNGC[$x]);
                        }

                        $defe=9;
                        $seven = 7;
                        $eight = 8;
                        for($x = 0 ; $x < $countmod; $x++){
                            $start = $arrayLetter[$x].$seven;
                            $end = $arrayLetter[$x].$eight;
                         $sheet->setCellValue($arrayLetter[$x].$defe, "=SUM($start:$end)");
                        }


                    $sheet->cell('A12',"Total Input:");
                    $sheet->cell('A13',"Total Output");
                    $sheet->cell('A14',"Total PNG");
                    $sheet->cell('A15',"Total MNG");
                    $sheet->cell('A16',"Yield W/o MNG");
                    $sheet->cell('A17',"Total Yield");
                    $sheet->cells("A17", function($cells) {$cells->setFontColor('#FF0000'); });
                    $sheet->cells('A12:A17', function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->cells("A12:A17", function($cells) {$cells->setBackground('#CCFFCC'); });
                   foreach ($Outdata as $key => $val) {
                    
                        $sheet->cell('B12', $val->sumpoqty);
                        $sheet->cell('B13', $val->accumulatedoutput);
                        $start = $arrayLetter[0].$seven;
                        $end = $arrayLetter[$countmod].$seven;
                        $sheet->setCellValue('B14', "=SUM($start:$end)");
                        $start = $arrayLetter[0].$eight;
                        $end = $arrayLetter[$countmod].$eight;
                        $sheet->setCellValue('B15', "=SUM($start:$end)");
                        $sheet->cell('B16', $val->ywomng);
                        $sheet->cell('B17', $val->twoyield);
                    }
                    $sheet->getStyle('B12:B17')->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells('B12:B17', function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->setColumnFormat(array(
                        'B16' => '0%',
                        'B17' => '0%',
                         ));
                     $sheet->cells("B17", function($cells) {$cells->setFontColor('#FF0000'); });
                }

                    //=========================
                    $arrayLetterpdf = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                    
                     if($ptype == "Test Socket"){
                    $html = "";
                    $html8 = "";
                    $html9 = "";
                    $html10 = "";
                    $defe = 6;

                    $countfam = $countfam+2;
                    $countmod = $countmod+2;
                    for($x=0;$x<$countfam;$x++){
                        for($y=0;$y<$countmod;$y++){
                    $rowww[$x][$y] = $sheet->getcell($arrayLetterpdf[$y].$defe)->getCalculatedValue();
                                 }
                                  $defe++;
                    }
                    
                    $html9 = "";
                    for($x=0;$x<$countfam;$x++){
                        $html9 .= '<tr>';
                        for($y=1;$y<$countmod;$y++){
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
        return $dompdf->stream('Defects Summary Report'.Carbon::now().'.pdf');
        }
            else{

           $html = "";
                    $html8 = "";
                    $html9 = "";
                    $html10 = "";
                    $defe = 6;

                    $countfam = 12;
                    $countmod = 7;
                    for($x=0;$x<$countfam;$x++){
                        for($y=0;$y<$countmod;$y++){
                    $rowww[$x][$y] = $sheet->getcell($arrayLetterpdf[$y].$defe)->getCalculatedValue();
                                 }
                                  $defe++;
                    }
                    
                    $html9 = "";
                    for($x=0;$x<$countfam;$x++){
                        $html9 .= '<tr>';
                        for($y=0;$y<$countmod;$y++){
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
        return $dompdf->stream('Defects Summary Report'.Carbon::now().'.pdf');




        }
        });
        });//->download('xls');
        }
        else{
            //NO DATA
        }
        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
        }

    }


 
}


