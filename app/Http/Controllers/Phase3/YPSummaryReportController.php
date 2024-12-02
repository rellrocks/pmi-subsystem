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

class YPSummaryReportController extends Controller
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



    public function summaryREpt(Request $request)
    {
        try
        { 
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';
            $datefrom = $request->datefrom;
            $dateto = $request->dateto;
            $prodtype = $request->srprodtype;
            $check = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('prodtype',$prodtype)->count();
            $check1 = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->count();

            if ($check > 0 || $check1 > 0) {
                Excel::create('Summary_Report_'.$date, function($excel) use($request)
                {
                    $excel->sheet('Sheet1', function($sheet) use($request)
                    {
                        $sheet->setAutoSize(true);
                        $datefrom = $request->datefrom;
                        $dateto = $request->dateto;
                         $prodtype = $request->srprodtype;
                        $family = $request->srfamily;
                        $sheet->setCellValue('A1', "$family Yield Summary");
                        $sheet->mergeCells('A1:B1');
                        $sheet->cell('A3',"Inclusive Date");
                        $date = date("Y-m-d");
                        $sheet->cell('A4',$date);
                        $sheet->cell('C3',"Date From");
                        $sheet->cell('D3',$datefrom);
                        $sheet->cell('C4',"Date To");
                        $sheet->cell('D4',$dateto);
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

                         

                          $Outdatass = DB::connection($this->mysql)->table('tbl_yielding_performance')
                            ->select('family','device','yieldingno','pono','twoyield','poqty','mod','toutput','qty','twoyield','classification',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"))
                            ->groupBy('mod')

                             ->whereBetween('productiondate', [$datefrom, $dateto])
                             ->where('prodtype',$prodtype)
                             ->where('classification','<>','NDF')
                            ->get();

                            if($prodtype == ''){
                                        $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance')
                                     ->select('family','device','yieldingno','pono','twoyield','poqty','mod','toutput','qty','twoyield',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"))
                                     ->groupBy('pono')
                                     ->whereBetween('productiondate', [$datefrom, $dateto])
                                     ->get();
                            }
                            else{
                                 $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance')
                                     ->select('family','device','yieldingno','pono','twoyield','poqty','mod','toutput','qty','twoyield',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"))
                                     ->groupBy('pono')
                                     ->whereBetween('productiondate', [$datefrom, $dateto])
                                     ->where('prodtype',$prodtype)
                                     ->get();
                                }
                                $row = 6;
                                $ModHold = [];
                                $countMH = 12;
                                 foreach ($Outdatass as $key => $val) { //GET MOD
                                        $ModHold[$countMH] = $val->mod;
                                        $countMH++;
                                 }
                                 $ModHoldcount = count($ModHold);
                                 $modFixx = array_unique($ModHold);
                                 $ponoHold = [];
                                 $countpono = 3;
                                 foreach ($Outdata as $key => $val) { //GET PONO
                                        $ponoHold[$countpono] = $val->pono;
                                        $countpono = $countpono+2;
                                 }
                                 $ponocount = count($ponoHold);
                                 $ponoFixx = array_unique($ponoHold);
                          $arrayLetter = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ");
                          $con = count($arrayLetter)/3;
                          $r=1;
                          for($x=0;$x<$con;$x++){
                            
                           $sheet->setColumnFormat(array(
                            $arrayLetter[$r] => '0%'
                             ));
                           $r=$r+2;
                          }
                          $lete = 0;
                          $defe = 12; 
                          $rowMaintain = 12;
                            

                          $counter = 0;
                        foreach ($Outdata as $key => $val) {
                            $row = 6;
                            $sheet->cell($arrayLetter[$lete].$row, $val->device);
                            $ov1 = $arrayLetter[$lete].$row;
                            $l = $lete+1;
                            $ov2 = $arrayLetter[$l].$row;
                            $sheet->mergeCells("$ov1:$ov2");
                            $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $counter++;
                            $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $row++;
                            $sheet->cell($arrayLetter[$lete].$row, $val->pono);
                            $ov1 = $arrayLetter[$lete].$row;
                            $l = $lete+1;
                            $ov2 = $arrayLetter[$l].$row;
                            $sheet->mergeCells("$ov1:$ov2");
                            $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $row++;
                            // $sheet->cell($arrayLetter[$lete].$row, $val->poqty);
                            $ov1 = $arrayLetter[$lete].$row;
                            $l = $lete+1;
                            $ov2 = $arrayLetter[$l].$row;
                            $sheet->mergeCells("$ov1:$ov2");
                            $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                            $row++;
                            $sheet->cell($arrayLetter[$lete].$row, $val->accumulatedoutput);
                            $ov1 = $arrayLetter[$lete].$row;
                            $l = $lete+1;
                            $ov2 = $arrayLetter[$l].$row;
                            $sheet->mergeCells("$ov1:$ov2");
                            $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                            $row++;    
                            $sheet->cell($arrayLetter[$lete].$row, $val->twoyield/100);
                            $ov1 = $arrayLetter[$lete].$row;
                            $l = $lete+1;
                            $ov2 = $arrayLetter[$l].$row;
                            $sheet->mergeCells("$ov1:$ov2");
                            $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                            $sheet->setColumnFormat(array($arrayLetter[$lete].$row => '0%' ));
                            $row++;
                            $sheet->cell($arrayLetter[$lete].$row, "QTY");
                            $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                            $lete2 = $lete + 1;
                            $sheet->cell($arrayLetter[$lete2].$row, "Rate");
                            $sheet->getStyle($arrayLetter[$lete2].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                             $last = $lete;
                            $lete2 = $lete + 2;
                            $row++;
                            $lete = $lete+2;
                            $chester = $arrayLetter[$lete];
                        }
                        $lete++;
                        $row = 6;
                        $sheet->setColumnFormat(array($arrayLetter[$lete] => '0' ));
                        $sheet->cell($arrayLetter[$lete].$row, "OVERALL");
                        $ovc1 =$arrayLetter[$lete].$row;
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ovc2 = $arrayLetter[$l].$row;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                        $colorStart = $arrayLetter[$lete].$row;
                        $row = $row+2;
                        $Start = 'C'.$row;
                        $end = $arrayLetter[$last].$row;
                        $sheet->cell($arrayLetter[$lete].$row, "=SUM($Start:$end)");
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                        $row++;
                        $Start = 'C'.$row;
                        $end = $arrayLetter[$last].$row;
                        $sheet->cell($arrayLetter[$lete].$row, "=SUM($Start:$end)");
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                        $row++;
                        $Start = 'C'.$row;
                        $end = $arrayLetter[$last].$row;
                        $sheet->cell($arrayLetter[$lete].$row, "=SUM($Start:$end)/$counter");
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->setColumnFormat(array($arrayLetter[$lete].$row => '0%' ));
                        $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                        $row++;
                        $sheet->cell($arrayLetter[$lete].$row, "QTY");
                        $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $leter = $lete+1;
                        $sheet->cell($arrayLetter[$leter].$row, "Rates");
                        $sheet->cells($arrayLetter[$leter].$row, function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->getStyle($arrayLetter[$leter].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $row++;
                       
                         
                     
                        //==================================================================DESIGN===================================================
                        
                       $Start = "A6";
                        $aa = 6;
                        $end = "$chester$aa";
                        $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#00CCFF'); });
                        $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });

                        $Start = "A7";
                        $aa = 7;
                        $end = "$chester$aa";
                        $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#00CCFF'); });
                        $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });

                        $Start = "B8";
                        $aa = 8;
                        $end = "$chester$aa";
                        $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#969696'); });

                        $Start = "B9";
                        $aa = 9;
                        $end = "$chester$aa";
                        $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#969696'); });

                        $Start = "B10";
                        $aa = 10;
                        $end = "$chester$aa";
                        $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#969696'); });
                        $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });

                        $Start = "A11";
                        $aa = 11;
                        $end = "$chester$aa";
                        $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });
                        $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#FFFF00'); });
                       
                        $sheet->cells("A8:A10", function($cells) {$cells->setBackground('#FFCC99'); });


                        //==================================================================DESIGN===================================================
                          $lete = 0;
                         $defe = 12; 
                        foreach ($Outdatass as $key => $val) {
                            $moDD = $val->mod;
                            $letes = $val->pono;
                            $roww = array_search($moDD, $ModHold);
                            $lete = array_search($letes, $ponoHold);
                            $lete = $lete - 3;
                            $sheet->cell($arrayLetter[$lete].$roww, $val->qty);
                            $sheet->getStyle($arrayLetter[$lete].$roww)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                            $lete2 = $lete + 1;
                            $rates = (($val->qty / $val->poqty)*100);
                             
                            $sheet->cell($arrayLetter[$lete2].$roww, $rates/1000);
                            $sheet->getStyle($arrayLetter[$lete2].$roww)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                          
                            $sheet->cell('A'.$defe, $ModHold[$defe]);
                            $defe++; 
                        }
                         //original end}
                         $row = $row+2;
                        
                          $sheet->cell('A'.$defe, "Total Defects");
                          $sheet->cells('A'.$defe, function($cells) {$cells->setFontWeight('bold'); });
                          $sheet->cells('A'.$defe, function($cells) {$cells->setBackground('#99CC00'); });
                          $lete = 0;
                          $row = $row-1;
                          $rowa = $row+1;
                          $rowMaintain =11;
                          $rowss = $rowMaintain + $ModHoldcount;
                          $rows1 = $rowss +1;
                          $ponocount = $ponocount*2;
                          
                          $sheet->cells('B'.$rows1, function($cells) {$cells->setBackground('#99CC00'); });
                          for($x=0;$x<$ponocount;$x++)
                          {
                          $Start = $arrayLetter[$lete].$rowMaintain;
                          $end = $arrayLetter[$lete].$rowss;
                          $sheet->cell($arrayLetter[$lete].$rows1, "=SUM($Start:$end)");
                          $sheet->cells($arrayLetter[$lete].$rows1, function($cells) {$cells->setBackground('#99CC00'); });
                        $sheet->getStyle($arrayLetter[$lete].$rows1)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->cells($arrayLetter[$lete].$rows1, function($cells) {$cells->setFontWeight('bold'); }); 
                        $lete++;
                          }
                        $sheet->cell('A6',"Device Name:");

                        $sheet->cell('A7',"PO Number:");
                        $sheet->cell('A8',"Total Input:");
                        $sheet->cell('A9',"Total Output");
                        $sheet->cell('A10',"Total Yield");
                        $sheet->cell('A11',"Defects:");

                        //=========================================OVERALL TOTAL==========================
                        $tempqty=0;
                        $temprate=0;
                        $s=0;
                        $s1 = 1;
                        $st = 12;
                       
                        for($y=0;$y<$ModHoldcount;$y++){
                            $temprate = 0;
                            $tempqty = 0;
                            $s = 0;
                            $s1 = 1;
                        for($x=0;$x<$counter;$x++)
                        {
                            $temprate += $sheet->getcell($arrayLetter[$s1].$st)->getCalculatedValue();
                            $tempqty += $sheet->getcell($arrayLetter[$s].$st)->getCalculatedValue();
                            $s = $s+2;
                            $s1 = $s1+2;
                        }
                        $s++;
                        $s1++;

                        $sheet->cell($arrayLetter[$s].$st, $tempqty);
                        $sheet->getStyle($arrayLetter[$s].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->cells($arrayLetter[$s].$st, function($cells) {$cells->setFontWeight('bold'); }); 
                        $sheet->cell($arrayLetter[$s1].$st, $temprate);
                        $sheet->getStyle($arrayLetter[$s1].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->cells($arrayLetter[$s1].$st, function($cells) {$cells->setFontWeight('bold'); }); 
                        $sheet->setColumnFormat(array($arrayLetter[$s1].$st => '0%'));
                        $st++;
                    }
                    $os = 11;
                    $minus = $st-1;
                    $Start = $arrayLetter[$s].$os;
                    $end = $arrayLetter[$s].$minus;
                    $Start2 = $arrayLetter[$s1].$os;
                    $end2 = $arrayLetter[$s1].$minus;
                    $st;  
                     $sheet->cell($arrayLetter[$s].$st, "=SUM($Start:$end)");
                     $sheet->cell($arrayLetter[$s1].$st, "=SUM($Start2:$end2)");
                     $l1 = $arrayLetter[$s].$st;
                     $l2 = $arrayLetter[$s1].$st;
                     $sheet->cells("$ovc1:$l1", function($cells) {$cells->setBackground('#99CC00'); });
                     $sheet->cells("$ovc2:$l2", function($cells) {$cells->setBackground('#99CC00'); });
                   

                     $sheet->setColumnFormat(array($arrayLetter[$s1].$st => '0%'));
                     $sheet->getStyle($arrayLetter[$s1].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                     $sheet->cells($arrayLetter[$s1].$st, function($cells) {$cells->setFontWeight('bold'); }); 
                     $sheet->getStyle($arrayLetter[$s].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                     $sheet->cells($arrayLetter[$s].$st, function($cells) {$cells->setFontWeight('bold'); }); 

                     $lete = 0;
                     $nine=9;
                     $twenty = 20;
                     $eight = 8;
                     for($x=0;$x<$ponocount/2;$x++){
                      $g = $sheet->getcell($arrayLetter[$lete].$nine)->getCalculatedValue();
                      $g2 = $sheet->getcell($arrayLetter[$lete].$twenty)->getCalculatedValue();
                     $sheet->cell($arrayLetter[$lete].$eight, $g+$g2);
                     $lete=$lete+2;
                     }
                    });
                })->download('xls');
            } else {
               //no data
            }
            
            
        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
         }
    }
    
    public function summaryREptpdf(Request $request)
     {
       try
        { 
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';
            $datefrom = $request->datefrom;
            $dateto = $request->dateto;
            $prodtype = $request->srprodtype;
            $check = DB::connection($this->mysql)->table("tbl_yielding_performance")->whereBetween('productiondate', [$datefrom, $dateto])->where('prodtype',$prodtype)->count();
            if($check > 0){
            Excel::create('Summary_Report_'.$date, function($excel) use($request)
            {
                $excel->sheet('Sheet1', function($sheet) use($request)
                {
                    $sheet->setAutoSize(true);
                    $datefrom = $request->datefrom;
                    $dateto = $request->dateto;
                    $prodtype = $request->srprodtype;
                    $family = $request->srfamily;
                    $sheet->setCellValue('A1', "$family Yield Summary");
                    $sheet->mergeCells('A1:B1');
                    $sheet->cell('A3',"Inclusive Date");
                    $date = date("Y-m-d");
                    $sheet->cell('A4',$date);
                    $sheet->cell('C3',"Date From");
                    $sheet->cell('D3',$datefrom);
                    $sheet->cell('C4',"Date To");
                    $sheet->cell('D4',$dateto);
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

                     

                      $Outdatass = DB::connection($this->mysql)->table('tbl_yielding_performance')
                        ->select('family','device','yieldingno','pono','twoyield','poqty','mod','toutput','qty','twoyield',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"))
                        ->groupBy('mod')

                         ->whereBetween('productiondate', [$datefrom, $dateto])
                         ->where('prodtype',$prodtype)
                        ->get();

                     $Outdata = DB::connection($this->mysql)->table('tbl_yielding_performance')
                        ->select('family','device','yieldingno','pono','twoyield','poqty','mod','toutput','qty','twoyield',DB::raw("SUM(accumulatedoutput) as accumulatedoutput"))
                        ->groupBy('pono')

                         ->whereBetween('productiondate', [$datefrom, $dateto])
                         ->where('prodtype',$prodtype)
                        ->get();
                            $row = 6;
                            $ModHold = [];
                            $countMH = 12;
                             foreach ($Outdatass as $key => $val) { //GET MOD
                                    $ModHold[$countMH] = $val->mod;
                                    $countMH++;
                             }
                             $ModHoldcount = count($ModHold);
                             $modFixx = array_unique($ModHold);
                             $ponoHold = [];
                             $countpono = 3;
                             foreach ($Outdata as $key => $val) { //GET PONO
                                    $ponoHold[$countpono] = $val->pono;
                                    $countpono = $countpono+2;
                             }
                             $ponocount = count($ponoHold);
                             $ponoFixx = array_unique($ponoHold);
                      $arrayLetter = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ");
                      $con = count($arrayLetter)/3;
                      $r=1;
                      for($x=0;$x<$con;$x++){
                        
                       $sheet->setColumnFormat(array(
                        $arrayLetter[$r] => '0%'
                         ));
                       $r=$r+2;
                      }
                      $lete = 0;
                      $defe = 12; 
                      $rowMaintain = 12;
                        

                      $counter = 0;
                    foreach ($Outdata as $key => $val) {
                        $row = 6;
                        $sheet->cell($arrayLetter[$lete].$row, $val->device);
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $counter++;
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $row++;
                        $sheet->cell($arrayLetter[$lete].$row, $val->pono);
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $row++;
                        $sheet->cell($arrayLetter[$lete].$row, $val->poqty);
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                        $row++;
                        $sheet->cell($arrayLetter[$lete].$row, $val->accumulatedoutput);
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                        $row++;    
                        $sheet->cell($arrayLetter[$lete].$row, $val->twoyield/100);
                        $ov1 = $arrayLetter[$lete].$row;
                        $l = $lete+1;
                        $ov2 = $arrayLetter[$l].$row;
                        $sheet->mergeCells("$ov1:$ov2");
                        $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                        $sheet->setColumnFormat(array($arrayLetter[$lete].$row => '0%' ));
                        $row++;
                        $sheet->cell($arrayLetter[$lete].$row, "QTY");
                        $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                        $lete2 = $lete + 1;
                        $sheet->cell($arrayLetter[$lete2].$row, "Rate");
                        $sheet->getStyle($arrayLetter[$lete2].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                         $last = $lete;
                        $lete2 = $lete + 2;
                        $row++;
                        $lete = $lete+2;
                        $chester = $arrayLetter[$lete];
                    }
                    $lete++;
                    $row = 6;
                    $sheet->setColumnFormat(array($arrayLetter[$lete] => '0' ));
                    $sheet->cell($arrayLetter[$lete].$row, "OVERALL");
                    $ovc1 =$arrayLetter[$lete].$row;
                    $ov1 = $arrayLetter[$lete].$row;
                    $l = $lete+1;
                    $ovc2 = $arrayLetter[$l].$row;
                    $ov2 = $arrayLetter[$l].$row;
                    $sheet->mergeCells("$ov1:$ov2");
                    $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                    $colorStart = $arrayLetter[$lete].$row;
                    $row = $row+2;
                    $Start = 'C'.$row;
                    $end = $arrayLetter[$last].$row;
                    $sheet->cell($arrayLetter[$lete].$row, "=SUM($Start:$end)");
                    $ov1 = $arrayLetter[$lete].$row;
                    $l = $lete+1;
                    $ov2 = $arrayLetter[$l].$row;
                    $sheet->mergeCells("$ov1:$ov2");
                    $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                    $row++;
                    $Start = 'C'.$row;
                    $end = $arrayLetter[$last].$row;
                    $sheet->cell($arrayLetter[$lete].$row, "=SUM($Start:$end)");
                    $ov1 = $arrayLetter[$lete].$row;
                    $l = $lete+1;
                    $ov2 = $arrayLetter[$l].$row;
                    $sheet->mergeCells("$ov1:$ov2");
                    $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                    $row++;
                    $Start = 'C'.$row;
                    $end = $arrayLetter[$last].$row;
                    $sheet->cell($arrayLetter[$lete].$row, "=SUM($Start:$end)/$counter");
                    $ov1 = $arrayLetter[$lete].$row;
                    $l = $lete+1;
                    $ov2 = $arrayLetter[$l].$row;
                    $sheet->mergeCells("$ov1:$ov2");
                    $sheet->getStyle("$ov1:$ov2")->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->setColumnFormat(array($arrayLetter[$lete].$row => '0%' ));
                    $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                    $row++;
                    $sheet->cell($arrayLetter[$lete].$row, "QTY");
                    $sheet->cells($arrayLetter[$lete].$row, function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->getStyle($arrayLetter[$lete].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $leter = $lete+1;
                    $sheet->cell($arrayLetter[$leter].$row, "Rates");
                    $sheet->cells($arrayLetter[$leter].$row, function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->getStyle($arrayLetter[$leter].$row)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $row++;
                   
                     
                 
                    //==================================================================DESIGN===================================================
                    
                   $Start = "A6";
                    $aa = 6;
                    $end = "$chester$aa";
                    $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#00CCFF'); });
                    $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });

                    $Start = "A7";
                    $aa = 7;
                    $end = "$chester$aa";
                    $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#00CCFF'); });
                    $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });

                    $Start = "B8";
                    $aa = 8;
                    $end = "$chester$aa";
                    $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#969696'); });

                    $Start = "B9";
                    $aa = 9;
                    $end = "$chester$aa";
                    $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#969696'); });

                    $Start = "B10";
                    $aa = 10;
                    $end = "$chester$aa";
                    $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#969696'); });
                    $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });

                    $Start = "A11";
                    $aa = 11;
                    $end = "$chester$aa";
                    $sheet->cells("$Start:$end", function($cells) {$cells->setFontWeight('bold'); });
                    $sheet->cells("$Start:$end", function($cells) {$cells->setBackground('#FFFF00'); });
                   
                    $sheet->cells("A8:A10", function($cells) {$cells->setBackground('#FFCC99'); });


                    //==================================================================DESIGN===================================================
                      $lete = 0;
                     $defe = 12; 
                    foreach ($Outdatass as $key => $val) {
                        $moDD = $val->mod;
                        $letes = $val->pono;
                        $roww = array_search($moDD, $ModHold);
                        $lete = array_search($letes, $ponoHold);
                        $lete = $lete - 3;
                        $sheet->cell($arrayLetter[$lete].$roww, $val->qty);
                        $sheet->getStyle($arrayLetter[$lete].$roww)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                        $lete2 = $lete + 1;
                        $rates = (($val->qty / $val->poqty)*100);
                         
                        $sheet->cell($arrayLetter[$lete2].$roww, $rates/1000);
                        $sheet->getStyle($arrayLetter[$lete2].$roww)->getAlignment()->applyFromArray(array('horizontal' => 'center'));  
                      
                        $sheet->cell('A'.$defe, $ModHold[$defe]);
                        $defe++; 
                    }
                     //original end}
                     $row = $row+2;
                    
                      $sheet->cell('A'.$defe, "Total Defects");
                      $sheet->cells('A'.$defe, function($cells) {$cells->setFontWeight('bold'); });
                      $sheet->cells('A'.$defe, function($cells) {$cells->setBackground('#99CC00'); });
                      $lete = 0;
                      $row = $row-1;
                      $rowa = $row+1;
                      $rowMaintain =11;
                      $rowss = $rowMaintain + $ModHoldcount;
                      $rows1 = $rowss +1;
                      $ponocount = $ponocount*2;
                      
                      $sheet->cells('B'.$rows1, function($cells) {$cells->setBackground('#99CC00'); });
                      for($x=0;$x<$ponocount;$x++)
                      {
                      $Start = $arrayLetter[$lete].$rowMaintain;
                      $end = $arrayLetter[$lete].$rowss;
                      $sheet->cell($arrayLetter[$lete].$rows1, "=SUM($Start:$end)");
                      $sheet->cells($arrayLetter[$lete].$rows1, function($cells) {$cells->setBackground('#99CC00'); });
                    $sheet->getStyle($arrayLetter[$lete].$rows1)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells($arrayLetter[$lete].$rows1, function($cells) {$cells->setFontWeight('bold'); }); 
                    $lete++;
                      }
                    $sheet->cell('A6',"Device Name:");

                    $sheet->cell('A7',"PO Number:");
                    $sheet->cell('A8',"Total Input:");
                    $sheet->cell('A9',"Total Output");
                    $sheet->cell('A10',"Total Yield");
                    $sheet->cell('A11',"Defects:");

                    //=========================================OVERALL TOTAL==========================
                    $tempqty=0;
                    $temprate=0;
                    $s=0;
                    $s1 = 1;
                    $st = 12;
                   
                    for($y=0;$y<$ModHoldcount;$y++){
                        $temprate = 0;
                        $tempqty = 0;
                        $s = 0;
                        $s1 = 1;
                    for($x=0;$x<$counter;$x++)
                    {
                        $temprate += $sheet->getcell($arrayLetter[$s1].$st)->getCalculatedValue();
                        $tempqty += $sheet->getcell($arrayLetter[$s].$st)->getCalculatedValue();
                        $s = $s+2;
                        $s1 = $s1+2;
                    }
                    $s++;
                    $s1++;

                    $sheet->cell($arrayLetter[$s].$st, $tempqty);
                    $sheet->getStyle($arrayLetter[$s].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells($arrayLetter[$s].$st, function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->cell($arrayLetter[$s1].$st, $temprate);
                    $sheet->getStyle($arrayLetter[$s1].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                    $sheet->cells($arrayLetter[$s1].$st, function($cells) {$cells->setFontWeight('bold'); }); 
                    $sheet->setColumnFormat(array($arrayLetter[$s1].$st => '0%'));
                    $st++;
                }
                $os = 11;
                $minus = $st-1;
                $Start = $arrayLetter[$s].$os;
                $end = $arrayLetter[$s].$minus;
                $Start2 = $arrayLetter[$s1].$os;
                $end2 = $arrayLetter[$s1].$minus;
                $st;  
                 $sheet->cell($arrayLetter[$s].$st, "=SUM($Start:$end)");
                 $sheet->cell($arrayLetter[$s1].$st, "=SUM($Start2:$end2)");
                 $l1 = $arrayLetter[$s].$st;
                 $l2 = $arrayLetter[$s1].$st;
                 $sheet->cells("$ovc1:$l1", function($cells) {$cells->setBackground('#99CC00'); });
                 $sheet->cells("$ovc2:$l2", function($cells) {$cells->setBackground('#99CC00'); });
               

                 $sheet->setColumnFormat(array($arrayLetter[$s1].$st => '0%'));
                 $sheet->getStyle($arrayLetter[$s1].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                 $sheet->cells($arrayLetter[$s1].$st, function($cells) {$cells->setFontWeight('bold'); }); 
                 $sheet->getStyle($arrayLetter[$s].$st)->getAlignment()->applyFromArray(array('horizontal' => 'center')); 
                 $sheet->cells($arrayLetter[$s].$st, function($cells) {$cells->setFontWeight('bold'); });  

                  
                    //=======================================//
                    $arrayLetterpdf =  array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");
                    $defe = 6;
                    
                    $countpono = 7 + $ModHoldcount;
                    $rowss = $countpono+1;
                    for($x=0;$x<=$countpono;$x++){
                        for($y=0;$y<=$rowss;$y++){
                    $rowww[$defe][$y] = $sheet->getcell($arrayLetterpdf[$y].$defe)->getCalculatedValue();
                         }
                         $defe++;
                    }
                        
                    //$sheet->setCellValue('H26', $rowww[6][0]);
                  
                    $html9 = "";
                    $deviceN = 6;

                     $a = count($rowww)-3;
                     $a = $a;
                     $startc = 12;
                     $ponocount = $ponocount+2;
                    for($x=0;$x<$countpono;$x++){
                        $html9 .= '<tr>';
                        for($y=0;$y<$a;$y++){
                            $html9 .= '<td>'.$rowww[$deviceN][$y].'</td>';
                        }
                        $deviceN++;
                        $html9 .= '</tr>';
                    }
                    //FOR TOTAL
                    $endC = $startc + $ModHoldcount;
                    $totalcontainer = [];
                    $total = 0;
                
                        $dt = Carbon::now();
                        $dompdf = new Dompdf();
                        $date = substr($dt->format('Ymd'), 2);
                        $html = '<div class="container-fluid" style="font-size:10">
                                                        <h2> YIELD SUMMARY</h2>
                                                        <p>Inclusive Date</p>
                                                        </br>
                                                        <p style="padding-left:6em;">Date From:'.$datefrom.'</p>
                                                        <p style="padding-left:6em;">Date From:'.$dateto.'</p>
                                                        <div style="width: 100%;margin-top: 3%;">
                                                            <table border="1" style="font-size:10">
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
       return $dompdf->stream('Summary Report'.Carbon::now().'.pdf');
                });
            });
            }
            else{
                // no DATA
            }

        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
         }
    }
}


