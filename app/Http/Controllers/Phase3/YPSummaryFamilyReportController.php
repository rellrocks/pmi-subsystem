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

class YPSummaryFamilyReportController extends Controller
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

    public function yieldsumfamRpt(Request $request){
        try
        { 
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';
            $datefrom = $request->datefrom;
             $dateto = $request->dateto;
             $yieldtarget = $request->yieldtarget;
             $chosen = $request->chosen;
             $ptype = $request->ptype;

             $check = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('prodtype',$ptype)->count();
             $check1 = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->count();
              $check2 = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('yieldtarget',$yieldtarget)->count();
            if($check > 0 || $check1 > 0 || $check2){
            
            Excel::create('Yield_Summary_Family_Report_'.$date, function($excel) use($request)
            {
                $excel->sheet('Sheet1', function($sheet) use($request)
                {
                     $sheet->setAutoSize(true);
                     $datefrom = $request->datefrom;
                     $dateto = $request->dateto;
                     $yieldtarget = $request->yieldtarget;
                     $chosen = $request->chosen;
                     $ptype = $request->ptype;


                      $Outdata = DB::connection($this->mysql)->table('tbl_targetregistration')
                        ->select('dppm','yield')
                        ->where('yield',$yieldtarget)
                        ->get();
                         $tardppm = 0;
                        foreach ($Outdata as $key => $value) {
                            $tardppm = $value->dppm;
                        }
                
                    $sheet->setCellValue('A1', 'Yield Target Summary Per Family - '. $ptype);
                    $sheet->mergeCells('A1:E1');
                    $sheet->cell('A3',"DATE");
                    $date = date("Y-m-d");
                    $sheet->cell('B3',$date);
                    $sheet->cell('E3',"Date Froms");
                    $sheet->cell('F3',$datefrom);
                    $sheet->cell('E4',"Date To");
                    $sheet->cell('F4',$dateto);
                    $sheet->cell('A6',"Yield Target");
                        $sheet->cell('B6',$yieldtarget);
                    $sheet->cell('A7',"DPPM Target");
                        $sheet->cell('B7',$tardppm);
                    $sheet->getStyle('B6:B7')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
                    $sheet->cells('B6:B7', function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->cell('A8',"Family");
                    $sheet->cell('A9',"Input");
                    $sheet->cell('A10',"Output");
                    $sheet->cell('A11',"Production NG");
                    $sheet->cell('A12',"Material NG");
                    $sheet->cell('A13',"Yield W/o MNG");
                    $sheet->cell('A14',"Total Yield (%)");
                    $sheet->cell('A15',"DPPM");
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
                    $row = 2;
                    $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $yieldtarget = $request->yieldtarget;
                    $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance')
                        //->select('family',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"),DB::raw("SUM(toutput) as toutput"),'ywomng')
                        ->select('family',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"),'toutput','ywomng','tpng')
                        ->groupBy('family')
                        ->orderBy('family')
                         ->whereBetween('productiondate', [$datefrom, $dateto])
                        ->get();

                     $arrayLetter = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                         $modOfFam = [];
                      $fff = 0;
                     foreach ($Outdata as $key => $val) {
                        $modOfFam[$fff] = $val->family;
                        $fff++;
                         
                     }
                    $Start = "A8";
                    $aa = 8;
                    $end = $arrayLetter[$fff];
                    $sheet->cells("$Start:$end$aa", function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->cells("$Start:$end$aa", function($cells) {$cells->setBackground('#3366FF'); });
                    $sheet->cells('A9:A15', function($cells) {$cells->setBackground('#FFFF00'); });
                    $sheet->cells('A9:A15', function($cells) {$cells->setFontWeight('bold'); });
                    $Fams = array_unique($modOfFam);
                    $newFams = array_values($Fams);
                    $countFam = count($Fams);

                    $defe = 7;
                    $lete = 0;
                    for($x=0;$x<$countFam;$x++)
                    {
                        $row = 8;
                        $sheet->cell($arrayLetter[$lete].$row, $newFams[$x]); //family row
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $lete++; 
                    }//FOR FAMILY
                    $nine = 9;
                    $fift = 15;
                    $start = $arrayLetter[$lete].$nine;
                    $end = $arrayLetter[$lete].$fift;
                    $sheet->cells("$start:$end", function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->cell($arrayLetter[$lete].$row, "TOTAL");
                    $TO = [];
                    $tpng = [];
                    $row = 9;
                    $lete = 0;
                     foreach ($Outdata as $key => $val) {
                        $sheet->cell($arrayLetter[$lete].$row,"0.0");
                        $sheet->cell($arrayLetter[$lete].$row, $val->toutput);
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $tpng[$lete] = $val->tpng;
                        $TO[$lete] = $val->toutput;
                        $lete++; 
                     }
                     $ACO = [];
                     $row++;
                     $lete = 0;
                      foreach ($Outdata as $key => $val) {

                        $sheet->cell($arrayLetter[$lete].$row, $val->accumulatedoutput);
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $ACO[$lete] = $val->accumulatedoutput;
                        $lete++; 
                     }
                     $row++;
                    //FOR PRODUCTIOM
                      $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                        ->select(DB::raw("COUNT(*) as classificationCount"),'family')
                        ->groupBy('family')
                        ->orderBy('family')
                        //->where('classification','like','%Production%')
                        ->whereBetween('productiondate', [$datefrom, $dateto])
                        ->get();
                          // for($x=0;$x<$countFam;$x++)
                          // {
                          //   $sheet->cell($arrayLetter[$x].$row, '0.0');
                          //   $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                          // }//zero filler
                         $lete = 0;
                        
                    foreach ($Outdatas as $key => $val) {
                       
                        if (in_array($val->family, $newFams)) {
                            $key = array_search($val->family, $newFams);
                                   $sheet->cell($arrayLetter[$key].$row, $val->classificationCount);
                                   $sheet->getStyle($arrayLetter[$key].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                  
                                   //for DPPM
                                   $privateRow = $row + 4;
                                   $png = $val->classificationCount;
                                   $dppm = ($png/($TO[$lete]+$tpng[$lete]))*1000000;
                                   $percent = (round((float)$dppm));
                                   $sheet->cell($arrayLetter[$key].$privateRow, $percent);
                                   $sheet->getStyle($arrayLetter[$key].$privateRow)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                   $sheet->setColumnFormat(array(
                        $arrayLetter[$key].$privateRow => '0%'
                         ));
                                }
                        $lete++; 
                    }
                    $row++;
                   
                    //FOR MATERIALS
                    $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                        ->select(DB::raw("COUNT(*) as classificationCount"),'family')
                        ->groupBy('family')
                        ->orderBy('family')
                        ->where('classification','like','%Material%')
                         ->whereBetween('productiondate', [$datefrom, $dateto])
                        ->get();
                          for($x=0;$x<$countFam;$x++)
                          {
                            $sheet->cell($arrayLetter[$x].$row, '0.0');
                            $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                          }//zero filler
                          $lete = 0;
                    foreach ($Outdatas as $key => $val) {
                        if (in_array($val->family, $newFams)) {
                            $indexs = array_search($val->family, $newFams);
                                   $sheet->cell($arrayLetter[$indexs].$row, $val->classificationCount);
                                   $sheet->getStyle($arrayLetter[$indexs].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                }
                       
                        $lete++; 
                    }
                    $row++;
                    //TOTAL yield percentage
                    
                    $outputrow = 10;
                    $PNGrow = 11;
                    for($x=0;$x<$countFam;$x++)
                    {
                      
                        $out = $arrayLetter[$x].$outputrow;
                        $png = $arrayLetter[$x].$PNGrow;
                        $sheet->setCellValue($arrayLetter[$x].$row, "=(($out/($out + $png)))");
                        $x1 = $x+1;
                        $sheet->setColumnFormat(array($arrayLetter[$x].$row => '0%'));
                        $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                       
                    }
                    $row++;
                    //FOR TOTAL YIELD %
                    for($x=0;$x<$countFam;$x++)
                    {

                        $Ypercent = (($TO[$x] / $ACO[$x]) * 100);
                        $percent = (round((float)$Ypercent))/100;
                        $sheet->cell($arrayLetter[$x].$row,$percent);
                         $sheet->setColumnFormat(array(
                        $arrayLetter[$x].$row => '0%'
                         ));
                         $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    }
                    //TO TOTAL ALL
                    $row=9;
                    $last = $countFam-1;
                    $per = 13;
                    for($x=1;$x<=7;$x++)
                    {
                        $start = "B".$row;
                        $end = $arrayLetter[$last].$row;
                    $sheet->setCellValue($arrayLetter[$countFam].$row, "=SUM($start:$end)");
                     $sheet->getStyle($arrayLetter[$countFam].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    if($per < 15){
                     $sheet->setColumnFormat(array(
                        $arrayLetter[$countFam].$per => '0%'
                         ));

                    $per++;
                }
                    $row++;
                    }
                   
                 

                });
            })->download('xls');
            }
            else{
                //no data
            }
        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
        }
    }

    public function yieldsumfamRptpdf(Request $request){
        try
        { 
             $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';
         
            
            Excel::create('Yield_Summary_Family_Report_'.$date, function($excel) use($request)
            {
                $excel->sheet('Sheet1', function($sheet) use($request)
                {
                     $sheet->setAutoSize(true);
                     $datefrom = $request->datefrom;
                     $dateto = $request->dateto;
                     $yieldtarget = $request->yieldtarget;
                     $chosen = $request->chosen;
                     $ptype = $request->ptype;

                      $Outdata = DB::connection($this->mysql)->table('tbl_targetregistration')
                        ->select('dppm','yield')
                        ->where('yield',$yieldtarget)
                        ->get();
                         $tardppm = 0;
                        foreach ($Outdata as $key => $value) {
                            $tardppm = $value->dppm;
                        }
                    $wew1 = $yieldtarget;
                    $wew2 = $tardppm;

                
                    $sheet->setCellValue('A1', 'Yield Target Summary Per Family - '. $ptype);
                    $sheet->mergeCells('A1:E1');
                    $sheet->cell('A3',"DATE");
                    $date = date("Y-m-d");
                    $sheet->cell('B3',$date);
                    $sheet->cell('E3',"Date Froms");
                    $sheet->cell('F3',$datefrom);
                    $sheet->cell('E4',"Date To");
                    $sheet->cell('F4',$dateto);
                    $sheet->cell('A6',"Yield Target");
                        $sheet->cell('B6',$yieldtarget);
                    $sheet->cell('A7',"DPPM Target");
                        $sheet->cell('B7',$tardppm);
                    $sheet->getStyle('B6:B7')->getAlignment()->applyFromArray(array('horizontal' => 'center'));
                    $sheet->cells('B6:B7', function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->cell('A8',"Family");
                    $sheet->cell('A9',"Input");
                    $sheet->cell('A10',"Output");
                    $sheet->cell('A11',"Production NG");
                    $sheet->cell('A12',"Material NG");
                    $sheet->cell('A13',"Yield W/o MNG");
                    $sheet->cell('A14',"Total Yield (%)");
                    $sheet->cell('A15',"DPPM");
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
                    $row = 2;
                    $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $yieldtarget = $request->yieldtarget;
                    $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance')
                        //->select('family',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"),DB::raw("SUM(toutput) as toutput"),'ywomng')
                        ->select('family',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"),'toutput','ywomng','tpng')
                        ->groupBy('family')
                        ->orderBy('family')
                         ->whereBetween('productiondate', [$datefrom, $dateto])
                        ->get();

                     $arrayLetter = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                         $modOfFam = [];
                      $fff = 0;
                     foreach ($Outdata as $key => $val) {
                        $modOfFam[$fff] = $val->family;
                        $fff++;
                         
                     }
                    $Start = "A8";
                    $aa = 8;
                    $end = $arrayLetter[$fff];
                    $sheet->cells("$Start:$end$aa", function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->cells("$Start:$end$aa", function($cells) {$cells->setBackground('#3366FF'); });
                    $sheet->cells('A9:A15', function($cells) {$cells->setBackground('#FFFF00'); });
                    $sheet->cells('A9:A15', function($cells) {$cells->setFontWeight('bold'); });
                    $Fams = array_unique($modOfFam);
                    $newFams = array_values($Fams);
                    $countFam = count($Fams);

                    $defe = 7;
                    $lete = 0;
                    for($x=0;$x<$countFam;$x++)
                    {
                        $row = 8;
                        $sheet->cell($arrayLetter[$lete].$row, $newFams[$x]); //family row
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $lete++; 
                    }//FOR FAMILY
                    $nine = 9;
                    $fift = 15;
                    $start = $arrayLetter[$lete].$nine;
                    $end = $arrayLetter[$lete].$fift;
                    $sheet->cells("$start:$end", function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->cell($arrayLetter[$lete].$row, "TOTAL");
                    $TO = [];
                    $tpng = [];
                    $row = 9;
                    $lete = 0;
                     foreach ($Outdata as $key => $val) {
                        $sheet->cell($arrayLetter[$lete].$row,"0.0");
                        $sheet->cell($arrayLetter[$lete].$row, $val->toutput);
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $tpng[$lete] = $val->tpng;
                        $TO[$lete] = $val->toutput;
                        $lete++; 
                     }
                     $ACO = [];
                     $row++;
                     $lete = 0;
                      foreach ($Outdata as $key => $val) {

                        $sheet->cell($arrayLetter[$lete].$row, $val->accumulatedoutput);
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $ACO[$lete] = $val->accumulatedoutput;
                        $lete++; 
                     }
                     $row++;
                    //FOR PRODUCTIOM
                      $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                        ->select(DB::raw("COUNT(*) as classificationCount"),'family')
                        ->groupBy('family')
                        ->orderBy('family')
                        ->where('classification','like','%Production%')
                        ->whereBetween('productiondate', [$datefrom, $dateto])
                        ->get();
                          for($x=0;$x<$countFam;$x++)
                          {
                            $sheet->cell($arrayLetter[$x].$row, '0.0');
                            $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                          }//zero filler
                         $lete = 0;
                    foreach ($Outdatas as $key => $val) {
                        if (in_array($val->family, $newFams)) {
                            $key = array_search($val->family, $newFams);
                                   $sheet->cell($arrayLetter[$key].$row, $val->classificationCount);
                                   $sheet->getStyle($arrayLetter[$key].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 

                                   //for DPPM
                                   $privateRow = $row + 4;
                                   $png = $val->classificationCount;
                                   $dppm = ($png/($TO[$lete]+$tpng[$lete]))*1000000;
                                   $percent = (round((float)$dppm));
                                   $sheet->cell($arrayLetter[$key].$privateRow, $percent);
                                   $sheet->getStyle($arrayLetter[$key].$privateRow)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                   $sheet->setColumnFormat(array(
                        $arrayLetter[$key].$privateRow => '0%'
                         ));
                                }
                        $lete++; 
                    }
                    $row++;
                    //FOR MATERIALS
                    $Outdatas = DB::connection($this->mysql)->table('tbl_yielding_performance as a')
                        ->select(DB::raw("COUNT(*) as classificationCount"),'family')
                        ->groupBy('family')
                        ->orderBy('family')
                        ->where('classification','like','%Material%')
                         ->whereBetween('productiondate', [$datefrom, $dateto])
                        ->get();
                          for($x=0;$x<$countFam;$x++)
                          {
                            $sheet->cell($arrayLetter[$x].$row, '0.0');
                            $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                          }//zero filler
                          $lete = 0;
                    foreach ($Outdatas as $key => $val) {
                        if (in_array($val->family, $newFams)) {
                            $indexs = array_search($val->family, $newFams);
                                   $sheet->cell($arrayLetter[$indexs].$row, $val->classificationCount);
                                   $sheet->getStyle($arrayLetter[$indexs].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                                }
                       
                        $lete++; 
                    }
                    $row++;
                    //TOTAL yield percentage
                    
                    $outputrow = 10;
                    $PNGrow = 11;
                    for($x=0;$x<$countFam;$x++)
                    {
                      
                        $out = $arrayLetter[$x].$outputrow;
                        $png = $arrayLetter[$x].$PNGrow;
                        $sheet->setCellValue($arrayLetter[$x].$row, "=(($out/($out + $png)))");
                        $x1 = $x+1;
                        $sheet->setColumnFormat(array($arrayLetter[$x].$row => '0%'));
                        $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                       
                    }
                    $row++;
                    //FOR TOTAL YIELD %
                    for($x=0;$x<$countFam;$x++)
                    {

                        $Ypercent = (($TO[$x] / $ACO[$x]) * 100);
                        $percent = (round((float)$Ypercent))/100;
                        $sheet->cell($arrayLetter[$x].$row,$percent);
                         $sheet->setColumnFormat(array(
                        $arrayLetter[$x].$row => '0%'
                         ));
                         $sheet->getStyle($arrayLetter[$x].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    }
                    //TO TOTAL ALL
                    $row=9;
                    $last = $countFam-1;
                    $per = 13;
                    for($x=1;$x<=7;$x++)
                    {
                        $start = "B".$row;
                        $end = $arrayLetter[$last].$row;
                    $sheet->setCellValue($arrayLetter[$countFam].$row, "=SUM($start:$end)");
                     $sheet->getStyle($arrayLetter[$countFam].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    if($per < 15){
                     $sheet->setColumnFormat(array(
                        $arrayLetter[$countFam].$per => '0%'
                         ));

                    $per++;
                }
                    $row++;
                    }


                    //==========================================================FOR PDF
                        $arrayLetterpdf =  array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                     
                         
                    $defe = 8;
                    $countFam = $countFam+3;
                    for($x=0;$x<8;$x++){
                        for($y=0;$y<$countFam;$y++){
                    $rowww[$x][$y] = $sheet->getcell($arrayLetterpdf[$y].$defe)->getCalculatedValue();
                                 }
                                  $defe++;
                    }
                    //$sheet->setCellValue('H20', $rowww[1][1]);
                    //$defe = 7;
                    $html9 = "";
                    for($x=0;$x<8;$x++){
                        $html9 .= '<tr>';
                        for($y=0;$y<$countFam;$y++){
                            $html9 .= '<td>'.$rowww[$x][$y].'</td>';
                        }
                        $defe++;
                        $html9 .= '</tr>';
                    }

                    $Outdata = DB::connection($this->mysql)->table('tbl_targetregistration')
                        ->select('dppm','yield')
                       
                        ->get();
                        foreach ($Outdata as $key => $value) {
                            $tardppm = $value->dppm;
                            $yieldtarget=$value->yield;
                        }

                    $dt = Carbon::now();
                        $dompdf = new Dompdf();
                        $date = substr($dt->format('Ymd'), 2);
                        $html = '<div class="container-fluid" style="font-size:10">
                                                        <h2> Yield Target Summary Per Family </h2>
                                                        <p>Inclusive Date</p>
                                                        </br>
                                                        <p style="padding-left:6em;">Date From:'.$datefrom.'</p>
                                                        <p style="padding-left:6em;">Date From:'.$dateto.'</p>
                                                        <div style="width: 100%;margin-top: 3%;">
                                                            <table border="1" style="font-size:10">
                                                            <tr><td>Yield Target: </td> <td>'.$yieldtarget.'</td></tr>
                                                            <tr><td>DPPM Target: </td><td>'.$tardppm.'</td></tr>
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

        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
        }
 }

  

 
}


