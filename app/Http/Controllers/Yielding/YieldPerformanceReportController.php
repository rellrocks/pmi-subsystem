<?php
namespace App\Http\Controllers\Yielding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth; #Auth facade
use Excel;
use PDF;
use Carbon\Carbon;
use Dompdf\Dompdf;

class YieldPerformanceReportController extends Controller
{
    protected $mysql;
    protected $mssql;
    protected $common;
    protected $com;
    

    public function __construct()
    {
        $this->middleware('auth');
        $this->com = new CommonController;

        if (Auth::user() != null) {
            $this->mysql = $this->com->userDBcon(Auth::user()->productline,'yielding');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline,'common');
        } else {
            return redirect('/');
        }
    }


    public function getYieldPerformanceReport(Request $request)
    {
        if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_REP'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        { 
            $msrecords = DB::connection($this->mssql)
                            ->table('XSLIP as s')
                            ->leftJoin('XHEAD as h', 's.CODE', '=', 'h.CODE')
                            ->leftjoin('XRECE as r', 's.SEIBAN','=','r.SORDER')
                            ->select(DB::raw('s.SEIBAN as PO'),
                                            DB::raw('s.CODE as devicecode'),
                                            DB::raw('h.NAME as devicename'),
                                            DB::raw('r.KVOL as POqty'),
                                            DB::raw('r.SEDA as branch'))
                            ->where('s.SEIBAN',$request->pono)
                            ->orderBy('r.SEDA','desc')
                            // ->first()
                            ->get();

            $datenow = date('Y/m/d');
            $count = DB::connection($this->mysql)->table("tbl_yielding_pya")->orderBy('id','desc')->first();
            $targetyield = DB::connection($this->mysql)->table("tbl_targetregistration")->distinct()->get();
            $countpya = DB::connection($this->mysql)->table("tbl_yielding_performance")->count(); 
            $countcmq = DB::connection($this->mysql)->table("tbl_yielding_performance")->count(); 
            //$family = DB::connection($this->mysql)->table("tbl_seriesregistration")->select('family')->distinct()->get();
            $modefect = $this->com->getDropdownByName('Mode of Defect - Yield Performance');
            $family = $this->com->getDropdownByName('Family');
            $series = $this->com->getDropdownByName('Series');
            $target = DB::connection($this->mysql)->table("tbl_targetregistration")->orderBy('datefrom','asc')->get();
            $ys = $this->com->getDropdownByName('Yielding Station');
            $record = DB::connection($this->mysql)->table("tbl_yielding_performance")
                        ->groupBy('pono')
                        ->get();

            return view('yielding.YieldPerformanceReport',[
                'userProgramAccess' => $userProgramAccess,
                'family' => $family,
                'modefect' => $modefect,
                'yieldstation' => $ys,
                'yieldingno'=>$count,
                'series'=>$series,
                'msrecords'=>$msrecords, 
                'target' => $target,
                'count'=> $count,
                'countpya'=> $countpya,
                'countcmq'=> $countcmq,
                'targetyield' => $targetyield
            ]); 
        }
    }

    public function searchPOdetails(Request $req)
    {
        if (isset($req->po)) {
           $data = DB::connection($this->mysql)
                            ->select("SELECT pono,
                                        device,
                                        family,
                                        prodtype,
                                        series
                                FROM tbl_yielding_performance
                                where pono='".$req->po."'");
            if (count((array)$data) > 0) {
                return response()->json($data[0]);
            } else {
                $data = DB::connection($this->mysql)
                            ->select("SELECT pono,
                                        device,
                                        family,
                                        prodtype,
                                        series
                                FROM tbl_yielding_performance
                                where pono='".$req->po."'");
                if (count((array)$data) > 0) {
                    return response()->json($data[0]);
                }
            }
        }
    }

    public function records()
    {
        $records = DB::connection($this->mysql)->table("tbl_yielding_performance as y")
                        ->leftJoin('tbl_yielding_pya as p','y.pono','=','p.pono')
                        ->select(
                                DB::raw('y.id as id'),
                                DB::raw('y.pono as pono'),
                                DB::raw('y.poqty as poqty'),
                                DB::raw('y.device as device'),
                                DB::raw('y.series as series'),
                                DB::raw('y.family as family'),
                                DB::raw('y.tinput as tinput'),
                                DB::raw('y.toutput as toutput'),
                                DB::raw('y.treject as treject'),
                                DB::raw("IFNULL(SUM(p.qty),0) as qty")
                            )
                        ->groupBy('y.id',
                                'y.pono',
                                'y.poqty',
                                'y.device',
                                'y.series',
                                'y.family',
                                'y.toutput',
                                'y.treject')
                        ->get();

        return response()->json($records);
    }

    public function summarychart()
    {
        $ok =DB::connection($this->mysql)->table('tbl_yielding_performance')->get();
        return $ok;
    }

    public function exportToexcel(Request $request)
    {
        try
        {
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';

            Excel::create('Summary_Records_'.$date, function($excel)
            {
                $excel->sheet('Sheet1', function($sheet)
                {
                    $sheet->cell('A1', "PO NO");
                    $sheet->cell('B1', "PO QUANTITY");
                    $sheet->cell('C1', "DEVICE NAME");
                    $sheet->cell('D1', "SERIES");
                    $sheet->cell('E1', "FAMILY");
                    $sheet->cell('F1', "TOTAL OUTPUT");
                    $sheet->cell('G1', "TOTAL REJECT");
                    $sheet->cell('H1', "TOTAL YIELD");

                    // $sheet->row(4, function ($row) {
                    //     $row->setFontFamily('Calibri');
                    //     $row->setBackground('##ADD8E6');
                    //     $row->setFontSize(10);
                    //     $row->setAlignment('center');
                    // });

                    $sheet->row(1, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setBackground('##ADD8E6');
                        $row->setFontSize(10);
                        $row->setAlignment('center');
                    });
                    // $sheet->row(3, function ($row) {
                    //     $row->setFontFamily('Calibri');
                    //     $row->setFontSize(10);
                    //     $row->setAlignment('center');
                    // });
                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Calibri',
                            'size'      =>  10,
                        )
                    ));
                    $row = 2;
                    $data = DB::connection($this->mysql)->table('tbl_yielding_performance')
                                ->select(
                                    'pono',
                                    'poqty',
                                    'device',
                                    'series',
                                    'family',
                                    'toutput',
                                    'treject',
                                    'twoyield'
                                )
                                ->get();

                    foreach ($data as $key => $val) {
                        $sheet->cell('A'.$row, $val->pono);
                        $sheet->cell('B'.$row, $val->poqty);
                        $sheet->cell('C'.$row, $val->device);
                        $sheet->cell('D'.$row, $val->series);
                        $sheet->cell('E'.$row, $val->family);
                        $sheet->cell('F'.$row, $val->toutput);
                        $sheet->cell('G'.$row, $val->treject);
                        $sheet->cell('H'.$row, $val->twoyield);
                        $row++;
                    }

                });

            })->download('xls');


        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
        }
    }

    public function exportTopdf(Request $request)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);

        $company_info = $this->com->getCompanyInfo();

        $yield_performance = DB::connection($this->mysql)->table('tbl_yielding_performance')
                                ->select(
                                    'pono',
                                    'poqty',
                                    'device',
                                    'series',
                                    'family',
                                    'toutput',
                                    'treject',
                                    'twoyield'
                                    )
                                    ->get();

        $data = [
                'date' => $date,
                'company_info' => $company_info,
                'yield_performance' => $yield_performance
            ];

        $pdf = PDF::loadView('pdf.yielding_performance', $data)
                    ->setPaper('A4')
                    ->setOption('margin-top', 10)->setOption('margin-bottom', 5)
                    ->setOrientation('landscape');

        return $pdf->inline('Yielding_Performance_'.date('Y-m-d'));
    }









    public function summaryReport(Request $req)
    {
        Excel::create('Yield_Performance_Report', function($excel) use($req)
        {
            $excel->sheet('Sheet1', function($sheet) use($req)
            {

                $date_cond = '';
                $ptype_cond = '';
                $family_cond = '';
                $series_cond = '';
                $device_cond = '';
                $po_cond = '';
                $datefrom = '';
                $dateto = '';
                $dates = [];

                if ($req->datefrom !== '') {
                    $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
                    $dateto = $this->com->convertDate($req->dateto,'Y-m-d');

                    $date_cond = " AND p.productiondate BETWEEN '".$datefrom."' AND '".$dateto."'";
                }

                if ($req->ptype !== '') {
                    $ptype_cond = " AND y.prodtype='".$req->ptype."'";
                }

                if ($req->family !== '') {
                    $family_cond = " AND y.family='".$req->family."'";
                }

                if ($req->series !== '') {
                    $series_cond = " AND y.series='".$req->series."'";
                }

                if ($req->device !== '') {
                    $device_cond = " AND y.device like '%".$req->device."%'";
                }

                if ($req->po !== '') {
                    $po_cond = " AND y.pono='".$req->po."'";
                }

                $prod = DB::connection($this->mysql)
                                ->select("select date_format(p.productiondate,'%d-%m') as dates
                                        from tbl_yielding_performance as y
                                        inner join tbl_yielding_pya as p
                                        on y.pono = p.pono
                                        ".$date_cond.
                                        $ptype_cond.
                                        $family_cond.
                                        $series_cond.
                                        $device_cond.
                                        $po_cond."
                                        group by date_format(p.productiondate,'%d-%m')
                                        order by p.productiondate");

                // dd($prod);
                foreach ($prod as $key => $prd) {
                    array_push($dates, $prd->dates);
                }

                $sheet->setAutoSize(true);
                $sheet->setCellValue('A1', 'Yield Performance Report');
                $sheet->mergeCells('A1:B1');

                $sheet->setHeight(1,30);
                $sheet->row(1, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(15);
                });

                $sheet->cell('A3', function($cell) {
                    $cell->setValue("Inclusive Date");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(3,20);

                $sheet->cell('A4', function($cell) {
                    $cell->setValue("From:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(4,20);
                $sheet->mergeCells('B4:F4');
                $sheet->cell('B4',$datefrom);

                $sheet->cell('A5', function($cell) {
                    $cell->setValue("To:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(5,20);
                $sheet->mergeCells('B5:F5');
                $sheet->cell('B5',$dateto);

                $sheet->cell('A6', function($cell) {
                    $cell->setValue("Product Type:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(6,20);
                $sheet->mergeCells('B6:F6');
                $sheet->cell('B6',$req->ptype);

                $sheet->cell('A7', function($cell) {
                    $cell->setValue("Family:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(7,20);
                $sheet->mergeCells('B7:F7');
                $sheet->cell('B7',$req->family);

                $sheet->cell('A8', function($cell) {
                    $cell->setValue("Series Name:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(8,20);
                $sheet->mergeCells('B8:F8');
                $sheet->cell('B8',$req->series);


                $sheet->cell('A9', function($cell) {
                    $cell->setValue("Device Name:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(9,20);
                $sheet->mergeCells('B9:F9');
                $sheet->cell('B9',$req->device);

                $sheet->cell('A10', function($cell) {
                    $cell->setValue("PO Number:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(10,20);

                $sheet->mergeCells('B10:F10');
                $sheet->cell('B10',$req->po);

                $sheet->setMergeColumn([
                    'columns' => ['A'],
                    'rows' => [
                        [11,12]
                    ]
                ]);

                $sheet->cell('A11', function($cell) {
                    $cell->setValue('Defects');
                    $cell->setBackground('#63ace5');
                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $dateColums = [
                    ['B','C'],['D','E'],['F','G'],['H','I'],['J','K'],["L","M"],["N","O"],
                    ["P","Q"],["R","S"],["T","U"],["V","W"],["X","Y"],["Z","AA"],["AB","AC"],
                    ["AD","AE"],["AF","AG"],["AH","AI"],["AJ","AK"],["AL","AM"],["AN","AO"],
                    ["AP","AQ"],["AR","AS"],["AT","AU"],["AV","AW"],["AX","AY"],["AZ","BA"],
                    ["AB","BB"],["BC","BD"],["BE","BF"],["BG","BH"],["BI","BJ"],["BK","BL"],
                    ["BM","BN"],["BO","BP"]
                ];

                $covereddates = [];

                foreach ($dates as $key => $st) {
                    array_push($covereddates, $dateColums[$key]);
                }

                /*$defect_data = DB::connection($this->mysql)
                                ->select(
                                    DB::raw(
                                        "CALL GetYieldPerformanceReport(
                                            '".$datefrom."',
                                            '".$dateto."',
                                            '".$req->po."',
                                            '".$req->family."',
                                            '".$req->ptype."',
                                            '".$req->series."',                            
                                            '".$req->device."')"

                                        )
                                );*/
                $defect_data = DB::connection($this->mysql)
                                ->select("CALL GetYieldPerformanceReport(?,?,?,?,?,?,?,".DB::raw('@variable').")" , array(
                                            $datefrom,
                                            $dateto,
                                            $req->po,
                                            $req->family,
                                            $req->ptype,
                                            $req->series,                            
                                            $req->device
                                            )
                                );

                 $secondpassed = DB::connection($this->mysql)
                                ->select("SELECT @variable as sp");

                 // dd($defect_data);
                  // dd($secondpassed);


             // $pya = DB::connection($this->mysql)->table('tbl_yielding_pya')
             //            ->where('pono','=',$req->po)
             //            ->select('remarks')
             //            ->orderBY('productiondate','DESC')
             //            ->get();

                $defects = json_decode(json_encode($defect_data), true);
             

                 // $rework = json_decode(json_encode($rewok_ng), true);
                
                $row = 13;
                $defect_names = [];
                   // dd($defects);   
                foreach ($defects as $key => $df) {
                    $defect_names[$row] = $df['mod'];

                   
                    $row++;
                }

                $rows = array_keys($defect_names);
                $lastColKey = 0;


            
                foreach ($covereddates as $datekey => $dt) {

                     // $lastRow = count($dt);
                     // echo "<br>";
                     // echo "$lastRow";

                    $rowCounter = 0;
                    $sheet->cells($dt[0].'11:'.$dt[1].'11', function($cells) {
                        $cells->setBackground('#63ace5');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->cells($dt[0].'12:'.$dt[1].'12', function($cells) {
                        $cells->setBackground('#63ace5');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->mergeCells($dt[0].'11:'.$dt[1].'11');
                    $sheet->cell($dt[0].'11',$dates[$datekey]);
                    $sheet->cell($dt[1].'11','');
                    $sheet->cell($dt[0].'12','PNG ');
                    $sheet->cell($dt[1].'12','MNG ');
                    $sheet->setHeight(11,20);
                    $sheet->setHeight(12,20);


                    
                   // dd($defects)
                   // 
                   $percent = "";
                   // dd($defects);
                    foreach ($defects as $key => $df) 
                    {
                        // dd($df)
                          // $countcol = count($df);

                          // echo "<bt>";
                          // echo "$countcol";
                       // dd($defects);
                        $totals = $df['mod'];
                        if ($totals == 'Input' || $totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)'  || $totals == 'TotalYield(%)2nd Passed') {
                            if($totals != 'TotalYield(%)2nd Passed'){

                                $sheet->mergeCells($dt[0].$rows[$key].':'.$dt[1].$rows[$key]);

                                $sheet->cell('A'.$rows[$key], function($cell) use($totals){
                                    $cell->setValue($totals);
                                    $cell->setBackground('#fff9ae');
                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                   // $sheet->cell($nextCol[0].$rows[$key],$df['TOTAL_PNG'] + $df['TOTAL_MNG']);
                                    // $sheet->cell($nextCol[1].$rows[$key],$df['REWORK']);
                                });


                                $sheet->cells($dt[0].$rows[$key].':'.$dt[1].$rows[$key], function($cells) {
                                    $cells->setBackground('#fff9ae');
                                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                });


                                $sheet->cells($dt[0].$rows[$key], function($cells) {
                                    $cells->setBackground('#FFFFFF');
                                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                });

                                if ($df['mod'] == 'Yield w/o MNG(%)' || $df['mod'] == 'TotalYield(%)') {
                             $percent = "%";
                                 }else{
                             $percent = "";
                        }

                                $sheet->cell($dt[0].$rows[$key],(number_format($df[$dates[$datekey].'_PNG'],2) == 0)? '0.00'.$percent : number_format($df[$dates[$datekey].'_PNG'],2).$percent);
                                // $sheet->cell($nextCol[0].$rows[$key],$df[$dates][$datekey]['REWORK']);  
                             // $sheet->cell('B'.$lastRow,(number_format($df['TOTAL_PNG'],2) == 0)? '0.00'.$percent : number_format($df['TOTAL_PNG'],2).$percent);

                            
                            }

                        } else {
                            // echo "$totals<br>";
                            $sheet->cell('A'.$rows[$key], function($cell) use($totals){
                                $cell->setValue($totals);
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });

                            $sheet->cells($dt[0].$rows[$key].':'.$dt[1].$rows[$key], function($cells) {
                                $cells->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                            $sheet->cell($dt[0].$rows[$key],($df[$dates[$datekey].'_PNG'] == 0)? '' : $df[$dates[$datekey].'_PNG']);
                            $sheet->cell($dt[1].$rows[$key],($df[$dates[$datekey].'_MNG'] == 0)? '' : $df[$dates[$datekey].'_MNG']);
                        }
                        
                        $sheet->setHeight($rows[$key],20);
                    }
                    

                    $lastColKey = $datekey;
                }


                
                 $nextCol = $dateColums[$lastColKey+1];
                 // $countcol = count($nextCol);

                 // echo "<bt>";
                 //  echo "$countcol";
                
                // $test = count($nextCol)+2;
                // $sheet->cell($nextCol[0].'11','A');
                // $sheet->cell('B'.$lastRow,"Last Row B");

                // $sheet->cell($nextCol[0].'11','sads');
                //  $sheet->cell($nextCol[1].'11','sads');
                $currIndex =  array_search($nextCol, $dateColums);

                 // $sheet->mergeCells($dt[0].'11:'.$dt[1].'11');
                 //    $sheet->cell($dt[0].'11',$dates[$datekey]);
                 //    $sheet->cell($dt[1].'11','');

                $sheet->mergeCells($nextCol[0].'11:'.$nextCol[1].'11');    
                $sheet->cell($nextCol[0].'11','TOTAL');
                $sheet->cell($nextCol[1].'11','');
                    $sheet->cells($nextCol[0].'11:'.$nextCol[1].'11', function($cells) {
                    $cells->setAlignment('center');
                       
                    });

                $sheet->cell($dateColums[$currIndex+1][0].'11',' AFTER REWORK ');
                $sheet->cells($dateColums[$currIndex+1][0].'11', function($cells) {
                $cells->setAlignment('center');                  
                });
                // $sheet->cell($nextCol[0].'11','      ');  
                 // $sheet->cell($nextCol[0].'16','TOTAL:');  
                //              $sheet->mergeCells('A2:I2');
                // $sheet->cells('A2:I2', function($cells) {
                //     $cells->setAlignment('center');
                //     $cells->setFont([
                //         'family'     => 'Calibri',
                //         'size'       => '14',
                //         'bold'       =>  true,
                //         'underline'  =>  true
                //     ]);

                // }); 
                // 
                //  
                //   
                $sheet->mergeCells($nextCol[0].'12:'.$nextCol[1].'12');     
                $sheet->cell($nextCol[0].'12','NG');
                $sheet->cell($nextCol[1].'12','');
                $sheet->cells($nextCol[0].'12:'.$nextCol[1].'12', function($cells) {
                $cells->setAlignment('center');                  
                });

                $sheet->cell($dateColums[$currIndex+1][0].'12','NG');
                $sheet->cells($dateColums[$currIndex+1][0].'12', function($cells) {
                $cells->setAlignment('center');                  
                });
                // $sheet->setHeight(11,20);
                // $sheet->setHeight(12,20);


                 $sheet->cell($nextCol[0].'11',function($cells){
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    // $cells->setBackground('#63ace5');
                 }); 

                  $sheet->cell($nextCol[0].'12',function($cells){
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    // $cells->setBackground('#63ace5');
                 }); 


                 $sheet->cell($dateColums[$currIndex+1][0],function($cells){

                 }); 

                $sheet->cells($nextCol[0].'11:'.$nextCol[1].'11', function($cells) {
                    $cells->setBackground('#FFFF00');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->cells($nextCol[0].'12:'.$nextCol[1].'12', function($cells) {
                    $cells->setBackground('##FFFF00');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });



                $sheet->cells($dateColums[$currIndex+1][0].'11', function($cells) {
                    $cells->setBackground('##FFFF00');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                 });


                $sheet->cells($dateColums[$currIndex+1][0].'12', function($cells) {
                    $cells->setBackground('##FFFF00');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                 });


                // $norework = "";
                // 
                // dd($defects);
                foreach ($defects as $key => $df) {
                    $totals = $defect_names[$rows[$key]];
                    if ($totals == 'Input'  || $totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)' || $totals == 'TotalYield(%)2nd Passed' ) {

                            if(!  ($totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)' || $totals == 'TotalYield(%)2nd Passed' || $totals =='NG' || $totals == 'REWORK' )){ 

                          
                             $currIndex =  array_search($nextCol, $dateColums);
        
                          $sheet->cells($nextCol[0].$rows[$key].':'.$nextCol[0].$rows[$key], function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                             

                            });   

                            $sheet->cell($nextCol[0].$rows[$key],'TOTAL'); 
  
                             // $sheet->set($nextCol[1].$rows[$key], ''); 

                             $sheet->cells($nextCol[0].$rows[$key].':'.$nextCol[1].$rows[$key], function($cells) {
                             // $cells->setWidth($nextCol[1].$rows[$key] => 50);
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                             $cells->setBackground('#FFFF00');
                             $cells->setFontColor('#E80000');


                            });   


                              
                               $sheet->cell($nextCol[1].$rows[$key],$df['NG']);


                            $sheet->cells($dateColums[$currIndex+1][0].$rows[$key], function($cells) {
                            $cells->setBorder('thin', 'thin', 'thin', 'thin');
                            $cells->setBackground('##FFFF00');
                            $cells->setFontColor('#E80000');
                            });

                                if ($df['REWORK'] > 0) {

                                    $sheet->cell($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK']);
                                }else{

                                     $sheet->cell($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK']."0");

                                }

                             
                              // $sheet->cell(($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK'] == 0)? '0');


                                // $cell->setValue(($tl[$family] == 0)? '0.00%' : $tl[$family],2);



                             }

                    } else {
                        // echo "<br>";
                        // echo "$rows[$key]";
                        $currIndex =  array_search($nextCol, $dateColums);

                        $sheet->mergeCells($nextCol[0].$rows[$key].':'.$nextCol[1].$rows[$key]);
                        $sheet->cells($nextCol[0].$rows[$key].':'.$nextCol[1].$rows[$key], function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                             $cells->setAlignment('center'); 
                        });

                        $sheet->cell($nextCol[0].$rows[$key],$df['TOTAL_PNG'] + $df['TOTAL_MNG']);
                        $sheet->cell($nextCol[1].$rows[$key]);




                          $sheet->cells($dateColums[$currIndex+1][0].$rows[$key], function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                          $norework = "0";
                          if ($df['REWORK'] > 0) {

                             $sheet->cell($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK']);
                          }else{

                              $sheet->cell($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK'].$norework);
                          }

                      
                         // $sheet->cell($nextCol[1].$rows[$key],($df['TOTAL_MNG'] == 0)? '0.00' : );
                         
                    }
                    $sheet->setHeight($rows[$key],20);
                }





                $lastRow = count($defects)+1+3+10;
                $percent = "";
                 // echo "<br>";
                 // echo "$lastRow";
                $sheet->cell('A'.$lastRow,"Last Row A");
                $sheet->cell('B'.$lastRow,"Last Row B");
                // $h = "hello";
                $total_text = array("Input"=>"Total Input","Output"=>"Total Output","Production - NG"=>"Total PNG","Material - NG"=>"Total MNG","Yield w/o MNG(%)"=>"Total Yield w/o MNG(%)","TotalYield(%)"=>"TotalYield(%)(1st Passed)","TotalYield(%)2nd Passed"=>"TotalYield(%)2nd Passed");
                // echo "$h";
                // dd($defects);
                foreach ($defects as $key => $df) {
                    $totals = $defect_names[$rows[$key]];


                    if ($totals == 'Input' || $totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)' || $totals == 'TotalYield(%)2nd Passed' || $totals == 'NG' || $totals == 'REWORK') {


                        $sheet->mergeCells('B'.$lastRow.':'.'C'.$lastRow);
                        $sheet->cells('B'.$lastRow.':'.'C'.$lastRow, function($cells) {
                            $cells->setBackground('#ffffff');
                            $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                            $sheet->cell('A'.$lastRow,$total_text[$totals]);
                            $sheet->cells('A'.$lastRow, function($cells) {
                            $cells->setBackground('#AFFFA9');
                            });

                        if ($df['mod'] == 'Yield w/o MNG(%)' || $df['mod'] == 'TotalYield(%)') {
                             $percent = "%";
                        }else{
                             $percent = "";
                        }
                        // dd($df['TOTAL_PNG']);
                         $sheet->cell('B'.$lastRow,(number_format($df['TOTAL_PNG'],2) == 0)? '0.00'.$percent: number_format($df['TOTAL_PNG'],2).$percent);
                         $sheet->setBorder('A'.$lastRow, 'thin');
                         $sheet->setBorder('B'.$lastRow, 'thin');

                         $lastRow++;
                         
                    } 

                    $sheet->setHeight($lastRow,20);
                }

               
                $second = count($defects)+4+6+10;

                // echo "<br>";
                // echo "$secondpassed";

                $sheet->cell('A'.$second,"TotalYield(%)(2nd passed)");
                foreach ($secondpassed as $key => $value) {


                             $sheet->cells('A'.$second, function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                             $cells->setBackground('#AFFFA9');


                            }); 

                             $sheet->cells('B'.$second, function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                           

                            }); 

                         $sheet->mergeCells('B'.$second.':'.'C'.$second);
                          $sheet->cell('B'.$second,strval(number_format($value->sp,2).'%'));

                            // $sheet->cell($cols[$famkey].'15', number_format($req->target_yield,2).'%');
                          // HELLO
               }
             


            });

           })->download('xls');
         // });
    }
    public function checkDataExists(Request $req){
        // dd($check);
         $datefrom = $this->com->convertDate($req->srdatefrom,'Y-m-d');
         $dateto = $this->com->convertDate($req->srdateto,'Y-m-d');
        $check = DB::connection($this->mysql)
                 ->select("SELECT count(*) AS rowcount from tbl_yielding_pya where pono = '".$req->srpo."' AND productiondate between '".$datefrom."' and '".$dateto."'");

          return $check;    
    } 

    public function checkdeffectsummary(Request $req){
        $datefrom = $this->com->convertDate($req->dsrdatefrom,'Y-m-d');
        $dateto = $this->com->convertDate($req->dsrdateto,'Y-m-d');
        $check = DB::connection($this->mysql)
                 ->select("SELECT count(*) AS rowcount, p.family,y.mod from tbl_yielding_performance as p JOIN 
                    tbl_yielding_pya as y ON p.pono = y.pono WHERE 
              productiondate between '".$datefrom."' and '".$dateto."' AND y.mod <>''");
        return $check;     

    }

    public function checkyieldperformancesummary(Request $req){
        $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
        $dateto = $this->com->convertDate($req->dateto,'Y-m-d');
        $prodtype = $req->prodtype;
        $check = DB::connection($this->mysql)
                 ->select("SELECT count(*) AS rowcount from tbl_yielding_performance as p JOIN
                 tbl_yielding_pya as y on p.id = y.yield_id and 
                  y.productiondate between '".$datefrom."' and '".$dateto."' and p.prodtype = '".$prodtype."'");
        return $check;    
         
    }
    public function checkyieldperformancesummaryperfamily(Request $req){
        $datefrom = $this->com->convertDate($req->ysfdatefrom,'Y-m-d');
        $dateto = $this->com->convertDate($req->ysfdateto,'Y-m-d');
        $ptype = $req->ptype;
        $family = $req->family;
        $check = DB::connection($this->mysql)
                 ->select("SELECT count(*) AS rowcount from tbl_yielding_performance as p JOIN
                 tbl_yielding_pya as y on p.id = y.yield_id and 
                  y.productiondate between '".$datefrom."' and '".$dateto."' and p.prodtype = '".$ptype."' and p.family ='".$family."'");
                 
        return $check; 
    }
    public function defectSummary(Request $req)
    {
        $that = $this;
        Excel::create('Defect_Summary_Report', function($excel) use($req)
        {
            $excel->sheet('Sheet1', function($sheet) use($req)
            {

                $date_cond = '';
                $ptype_cond = '';
                $family_cond = '';
                $series_cond = '';
                $device_cond = '';
                $po_cond = '';
                $datefrom = '';
                $dateto = '';
                $fams = [];

                if ($req->datefrom !== '') {
                    $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
                    $dateto = $this->com->convertDate($req->dateto,'Y-m-d');

                    $date_cond = " AND p.productiondate BETWEEN '".$datefrom."' AND '".$dateto."'";
                }

                if ($req->ptype !== '') {
                    $ptype_cond = " AND y.prodtype='".$req->ptype."'";
                }

                if ($req->family !== '') {
                    $family_cond = " AND y.family='".$req->family."'";
                }

                if ($req->series !== '') {
                    $series_cond = " AND y.series='".$req->series."'";
                }

                if ($req->device !== '') {
                    $device_cond = " AND y.device like '%".$req->device."%'";
                }

                if ($req->po !== '') {
                    $po_cond = " AND y.pono='".$req->po."'";
                }

                $families = DB::connection($this->mysql)
                                ->select("select y.family
                                        from tbl_yielding_performance as y
                                        inner join tbl_yielding_pya as p
                                        on y.pono = p.pono 
                                        WHERE p.mod <> ''".$date_cond.
                                        $ptype_cond.
                                        $family_cond.
                                        $series_cond.
                                        $device_cond.
                                        $po_cond."
                                        group by y.family");

                if($families == [])
                {
                     $that = $this;
                     Excel::create('Defect_Summary_Report', function($excel) use($req)
                    {
                      $excel->sheet('Sheet1', function($sheet) use($req)
                         {

                            $sheet->setAutoSize(true);
                            $sheet->setCellValue('A1', 'Defect Summary Per Family');
                            $sheet->mergeCells('A1:D1');

                            $sheet->setHeight(1,30);
                            $sheet->row(1, function ($row) {
                                $row->setFontFamily('Calibri');
                                $row->setFontSize(15);
                            });

                            $sheet->cell('A3',"DATE");

                            $sheet->cell('C3',"From");
                            $sheet->cell('D3',$req->datefrom);
                            $sheet->cell('C4',"To");
                            $sheet->cell('D4',$req->dateto);

                            $sheet->setHeight(3,20);
                            $sheet->setHeight(4,20);

                            $sheet->cell('A6',"No.");
                            $sheet->cell('B6',"Defectives");
                            $sheet->cell('C6',"TOTAL");
                            $sheet->cell('D6',"2NDPASSEDEFECTS");


                        });

                    })->download('xls');                    
                }
                foreach ($families as $key => $fam) {
                    array_push($fams, $fam->family);
                }

                $sheet->setAutoSize(true);
                $sheet->setCellValue('A1', 'Defect Summary Per Family');
                $sheet->mergeCells('A1:D1');

                $sheet->setHeight(1,30);
                $sheet->row(1, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(15);
                });

                $sheet->cell('A3',"DATE");

                $sheet->cell('C3',"From");
                $sheet->cell('D3',$datefrom);
                $sheet->cell('C4',"To");
                $sheet->cell('D4',$dateto);

                $sheet->setHeight(3,20);
                $sheet->setHeight(4,20);

                $sheet->cell('A6',"No.");
                $sheet->cell('B6',"Defectives");

                $cols = array("C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP");

                $lastColKey = '';
                $nextCol = '';

                $defect_data = DB::connection($this->mysql)
                                ->select(
                                    DB::raw(
                                        "CALL GetDefectSummary(
                                            '".$datefrom."',
                                            '".$dateto."',
                                            '".$req->po."',
                                            '".$req->family."',
                                            '".$req->ptype."',
                                            '".$req->series."',
                                            '".$req->device."')"
                                        )
                                );
                 // dd($defect_data);
                $defects = json_decode(json_encode($defect_data), true);

                $row = 7;
                $defect_names = [];

                // dd($defects);
                foreach ($defects as $key => $df) {
                    $defect_names[$row] = $df['mod'];
                    $row++;
                }

                // dd($defect_names);
                $rows = array_keys($defect_names);

                // dd($fams);
                // dd($rows);
            
                foreach ($fams as $famkey => $family) {
                    // dd($family);
                    if($family == ''){

                        $family = 'NoFamily';

                    }
                    // dd($family);
                      // $sheet->cells('6'.$rows, function($cells) {
                      //        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                            
                      //       }); 
                    $sheet->cell($cols[$famkey].'6',$family);
                      // $sheet->cell($cols[$famkey].'7',$family);


                    $no = 1;
                    // dd($df[$family]);
                    // dd($defects);
                    // dd($famkey);
                    foreach ($defects as $key => $df) {
                        // dd($cols[$famkey],$rows[$key],$df[$family]);
                        $sheet->cell('A'.$rows[$key],$no);
                        $sheet->cell('B'.$rows[$key],$defect_names[$rows[$key]]);
                        $sheet->cell($cols[$famkey].$rows[$key],($df[$family] == 0)? '0.00' : $df[$family]);
                        $sheet->setHeight($rows[$key],20);
                        $no++;
                    }

                    $lastColKey = $famkey;

                }


                $nextCol = $cols[$lastColKey+1];
                $sheet->cell($nextCol.'6','TOTAL');
           
                $reworkcount = $cols[$lastColKey+2];
                $sheet->cell($reworkcount.'6','2NDPASSEDEFECTS');

                 // dd($defects);
                foreach ($defects as $key => $df) {
                    $sheet->cell($nextCol.$rows[$key], function($cell) use($df) {         
                        $cell->setValue($df['TOTAL']);
                        $cell->setFont([
                            'bold'       =>  true,

                        ]);
                    });

                     // dd($df);
                     $sheet->cell($reworkcount.$rows[$key], function($cell) use($df) {
                        $cell->setValue($df['2NDPASSEDEFECTS']);
                        $cell->setFont([
                            'bold'       =>  true,
                        ]);
                    });              
                }

// $rework = [];
// array_push($fams, $fam->family);
                $sheet->cells('A6:'.$nextCol.'6', function($cells) {
                    $cells->setFont([
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true,
                    ]);
                    // Set all borders (top, right, bottom, left)
                    $cells->setBorder('solid', 'solid', 'solid', 'solid');
                });

                $sheet->setHeight(6, 20);
            });
        })->download('xls');
    }
























    public function yieldsumRpt(Request $req)
    {
        $date_cond = '';
        $ptype_cond = '';
        $fams = [];

        if ($req->datefrom !== '') {
            $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
            $dateto = $this->com->convertDate($req->dateto,'Y-m-d');

            $date_cond = " AND p.productiondate BETWEEN '".$datefrom."' AND '".$dateto."'";
        }

        if ($req->prodtype !== '') {
            $ptype_cond = " AND y.prodtype='".$req->prodtype."'";
        }

        $families = DB::connection($this->mysql)
                        ->select("select y.family
                                from tbl_yielding_performance as y
                                inner join tbl_yielding_pya as p
                                on y.pono = p.pono
                                where 1=1".$date_cond.
                                $ptype_cond."
                                group by y.family");

        if (count((array)$families) > 0) {
            Excel::create('Yield_Performance_Summary_Report', function($excel) use($req)
            {
                $excel->sheet('Sheet1', function($sheet) use($req)
                {

                    $date_cond = '';
                    $ptype_cond = '';
                    $fams = [];

                    if ($req->datefrom !== '') {
                        $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
                        $dateto = $this->com->convertDate($req->dateto,'Y-m-d');

                        $date_cond = " AND p.productiondate BETWEEN '".$datefrom."' AND '".$dateto."'";
                    }

                    if ($req->prodtype !== '') {
                        $ptype_cond = " AND y.prodtype='".$req->prodtype."'";
                    }

                    $sheet->setAutoSize(true);
                    $sheet->setCellValue('A1', 'Yield Performance Summary per Family - '.$req->prodtype);
                    $sheet->mergeCells('A1:G1');

                    $sheet->setHeight(1,30);
                    $sheet->row(1, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setFontSize(15);
                    });

                    $sheet->cell('A3',"DATE");

                    $sheet->cell('B3',"From");
                    $sheet->cell('C3',$datefrom);
                    $sheet->cell('B4',"To");
                    $sheet->cell('C4',$dateto);

                    $sheet->setHeight(3,20);
                    $sheet->setHeight(4,20);

                    $sheet->cell('A6',"Family");

                    $sheet->cell('A15', function($cell) {
                        $cell->setValue("Target Yield");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#fff9ae');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $cols = ["B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP"];

                    $lastColKey = 0;
                    $nextCol = '';

                    $families = DB::connection($this->mysql)
                                    ->select("select y.family
                                            from tbl_yielding_performance as y
                                            inner join tbl_yielding_pya as p
                                            on y.pono = p.pono
                                            where 1=1".$date_cond.
                                            $ptype_cond."
                                            group by y.family");


                    foreach ($families as $key => $fam) {
                        array_push($fams, $fam->family);
                    }

                    $data = DB::connection($this->mysql)
                                    ->select(
                                        DB::raw(
                                            "CALL GetYieldPerformanceSummary(
                                                '".$datefrom."',
                                                '".$dateto."',
                                                '".$req->prodtype."')"
                                            )
                                    );
                    //  echo "total: $data ";                    
                    $totals = json_decode(json_encode($data), true);

                    $row = 7;
                    $total_names = [];
                    // dd($totals);
                    foreach ($totals as $key => $tn) {
                      
                        $total_names[$row] = $tn['Input'];
                        $row++;
                    }

                    $rows = array_keys($total_names);

                    // dd($fams);
                    foreach ($fams as $famkey => $family) {
                        if($family == ''){

                            $family = 'NoFamily';
                        }
                        $sheet->cell($cols[$famkey].'6', function($cell) use($family) {
                            $cell->setValue($family);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        // dd($totals);
                        foreach ($totals as $key => $tl) {
                            // dd($tl[$family]);
                            $sheet->cell('A'.$rows[$key], function($cell) use($total_names,$rows,$key){
                                $cell->setValue($total_names[$rows[$key]]);
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#fff9ae');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });

                            $percent = "";

                            if ($total_names[$rows[$key]] == 'Yield w/o MNG' || $total_names[$rows[$key]] == 'Total Yield(%)1stPassed'|| $total_names[$rows[$key]] == 'After Rework' ||$total_names[$rows[$key]] == 'Total Yield(%)2ndPassed') {

                                $sheet->cell($cols[$famkey].$rows[$key], function($cell) use($tl,$rows,$key,$family){

                                    if ($tl['Input'] == 'Total Yield(%)2ndPassed' || $tl['Input'] == 'Total Yield(%)1stPassed' || $tl['Input'] == 'Yield w/o MNG' ) {

                                        $percent = "%";
                                    }else{
                                        $percent = "";
                                    }
                                     $cell->setValue((number_format($tl[$family],2) == 0)? '100.00'.$percent: number_format($tl[$family],2).$percent);

                                    //TEST
                                        // $sheet->cell($dt[0].$rows[$key],(number_format($df[$dates[$datekey].'_PNG'],2) == 0)? '0.00'.$percent : number_format($df[$dates[$datekey].'_PNG'],2).$percent);
                             


                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                });
                                $sheet->setHeight($rows[$key],20);
                            } else {
                                
                                $sheet->cell($cols[$famkey].$rows[$key], function($cell) use($tl,$rows,$key,$family){
                                    $cell->setValue(($tl[$family] == 0)? '0.00' : $tl[$family],2);
                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                });
                                $sheet->setHeight($rows[$key],20);
                            }
                        }

                        $sheet->cell($cols[$famkey].'15', number_format($req->target_yield,2).'%');

                        $lastColKey = $famkey;
                    }

                    $nextCol = $cols[$lastColKey+1];

                    $sheet->cell($nextCol.'6','TOTAL');

                       // dd($totals);
                     $percent = "";
                      
                    foreach ($totals as $key => $tl) {
                        // dd($tl['After']);
                        if ($total_names[$rows[$key]] == 'Yield w/o MNG' || $total_names[$rows[$key]] == 'Total Yield(%)1stPassed'|| $total_names[$rows[$key]] == 'After Rework'|| $total_names[$rows[$key]] == 'Total Yield(%)2ndPassed') {

                            $sheet->cell($nextCol.$rows[$key], function($cell) use($tl) {
                                // dd($tl['Input']);
                                if ($tl['Input'] == 'After Rework') {

                                    $percent = "";

                                }else{

                                    $percent = "%";

                                }
                                   $cell->setValue(number_format($tl['TOTAL'],2).$percent);
                                   

                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#77ab59');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                        } else {
                            $sheet->cell($nextCol.$rows[$key], function($cell) use($tl) {
                                $cell->setValue($tl['TOTAL']);
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#77ab59');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                        }
                    }
                    $sheet->cells('A6:'.$nextCol.'6', function($cells) {
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cells->setBackground('#63ace5');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setHeight(6, 20);

                    $sheet->cells('A15:'.$nextCol.'15', function($cells) {
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cells->setFontColor('#940000');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setHeight(13, 20);
                });
            })->download('xls');
        } else {
        }
    }






    public function yieldsumfamRpt(Request $req)
    {
        $date_cond = '';
        $ptype_cond = '';
        $fams = [];

        if ($req->datefrom !== '') {
            $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
            $dateto = $this->com->convertDate($req->dateto,'Y-m-d');

            $date_cond = " AND p.productiondate BETWEEN '".$datefrom."' AND '".$dateto."'";
        }

        if ($req->prodtype !== '') {
            $ptype_cond = " AND y.prodtype='".$req->prodtype."'";
        }

        if ($req->family !== '') {
            $family_cond = " AND y.family='".$req->family."'";
        }

        $families = DB::connection($this->mysql)
                        ->select("select y.family
                                from tbl_yielding_performance as y
                                inner join tbl_yielding_pya as p
                                on y.pono = p.pono
                                where 1=1".$date_cond.
                                $ptype_cond.$family_cond."
                                group by y.family");

        if (count((array)$families) > 0) {
            Excel::create('Summary_per_Family_Report', function($excel) use($req)
            {
                $excel->sheet('Sheet1', function($sheet) use($req)
                {

                    $date_cond = '';
                    $ptype_cond = '';
                    $fams = [];

                    if ($req->datefrom !== '') {
                        $datefrom = $this->com->convertDate($req->datefrom,'Y-m-d');
                        $dateto = $this->com->convertDate($req->dateto,'Y-m-d');

                        $date_cond = " AND p.productiondate BETWEEN '".$datefrom."' AND '".$dateto."'";
                    }

                    if ($req->prodtype !== '') {
                        $ptype_cond = " AND y.prodtype='".$req->prodtype."'";
                    }

                    if ($req->family !== '') {
                        $family_cond = " AND y.family='".$req->family."'";
                    }

                    $sheet->setAutoSize(true);
                    $sheet->mergeCells('A1:G1');
                    $sheet->cell('A1', function($cell) use($req) {
                        $cell->setValue($req->family.' YIELD SUMMARY - '.date('Ymd'));
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '15',
                            'bold'       =>  true,
                            'italic'     =>  true
                        ]);
                    });

                    $cols = ["B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ","EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ","FA","FB","FC","FD","FE","FF","FG","FH","FI","FJ","FK","FL","FM","FN","FO","FP","FQ","FR","FS","FT","FU","FV","FW","FX","FY","FZ","GA","GB","GC","GD","GE","GF","GG","GH","GI","GJ","GK","GL","GM","GN","GO","GP","GQ","GR","GS","GT","GU","GV","GW","GX","GY","GZ","HA","HB","HC","HD","HE","HF","HG","HH","HI","HJ","HK","HL","HM","HN","HO","HP","HQ","HR","HS","HT","HU","HV","HW","HX","HY","HZ","IA","IB","IC","ID","IE","IF","IG","IH","II","IJ","IK","IL","IM","IN","IO","IP","IQ","IR","IS","IT","IU","IV","IW","IX","IY","IZ","JA","JB","JC","JD","JE","JF","JG","JH","JI","JJ","JK","JL","JM","JN","JO","JP","JQ","JR","JS","JT","JU","JV","JW","JX","JY","JZ","KA","KB","KC","KD","KE","KF","KG","KH","KI","KJ","KK","KL","KM","KN","KO","KP","KQ","KR","KS","KT","KU","KV","KW","KX","KY","KZ","LA","LB","LC","LD","LE","LF","LG","LH","LI","LJ","LK","LL","LM","LN","LO","LP","LQ","LR","LS","LT","LU","LV","LW","LX","LY","LZ","MA","MB","MC","MD","ME","MF","MG","MH","MI","MJ","MK","ML","MM","MN","MO","MP","MQ","MR","MS","MT","MU","MV","MW","MX","MY","MZ","NA","NB","NC","ND","NE","NF","NG","NH","NI","NJ","NK","NL","NM","NN","NO","NP","NQ","NR","NS","NT","NU","NV","NW","NX","NY","NZ","OA","OB","OC","OD","OE","OF","OG","OH","OI","OJ","OK","OL","OM","ON","OO","OP","OQ","OR","OS","OT","OU","OV","OW","OX","OY","OZ","PA","PB","PC","PD","PE","PF","PG","PH","PI","PJ","PK","PL","PM","PN","PO","PP","PQ","PR","PS","PT","PU","PV","PW","PX","PY","PZ","QA","QB","QC","QD","QE","QF","QG","QH","QI","QJ","QK","QL","QM","QN","QO","QP","QQ","QR","QS","QT","QU","QV","QW","QX","QY","QZ","RA","RB","RC","RD","RE","RF","RG","RH","RI","RJ","RK","RL","RM","RN","RO","RP","RQ","RR","RS","RT","RU","RV","RW","RX","RY","RZ","SA","SB","SC","SD","SE","SF","SG","SH","SI","SJ","SK","SL","SM","SN","SO","SP","SQ","SR","SS","ST","SU","SV","SW","SX","SY","SZ","TA","TB","TC","TD","TE","TF","TG","TH","TI","TJ","TK","TL","TM","TN","TO","TP","TQ","TR","TS","TT","TU","TV","TW","TX","TY","TZ","UA","UB","UC","UD","UE","UF","UG","UH","UI","UJ","UK","UL","UM","UN","UO","UP","UQ","UR","US","UT","UU","UV","UW","UX","UY","UZ","VA","VB","VC","VD","VE","VF","VG","VH","VI","VJ","VK","VL","VM","VN","VO","VP","VQ","VR","VS","VT","VU","VV","VW","VX","VY","VZ","WA","WB","WC","WD","WE","WF","WG","WH","WI","WJ","WK","WL","WM","WN","WO","WP","WQ","WR","WS","WT","WU","WV","WW","WX","WY","WZ","XA","XB","XC","XD","XE","XF","XG","XH","XI","XJ","XK","XL","XM","XN","XO","XP","XQ","XR","XS","XT","XU","XV","XW","XX","XY","XZ","YA","YB","YC","YD","YE","YF","YG","YH","YI","YJ","YK","YL","YM","YN","YO","YP","YQ","YR","YS","YT","YU","YV","YW","YX","YY","YZ","ZA","ZB","ZC","ZD","ZE","ZF","ZG","ZH","ZI","ZJ","ZK","ZL","ZM","ZN","ZO","ZP","ZQ","ZR","ZS","ZT","ZU","ZV","ZW","ZX","ZY","ZZ","AAA","AAB","AAC","AAD","AAE","AAF","AAG","AAH","AAI","AAJ","AAK","AAL","AAM","AAN","AAO","AAP","AAQ","AAR","AAS","AAT","AAU","AAV","AAW","AAX","AAY","AAZ","ABA","ABB","ABC","ABD","ABE","ABF","ABG","ABH","ABI","ABJ","ABK","ABL","ABM","ABN","ABO","ABP","ABQ","ABR","ABS","ABT","ABU","ABV","ABW","ABX","ABY","ABZ","ACA","ACB","ACC","ACD","ACE","ACF","ACG","ACH","ACI","ACJ","ACK","ACL","ACM","ACN","ACO","ACP","ACQ","ACR","ACS","ACT","ACU","ACV","ACW","ACX","ACY","ACZ","ADA","ADB","ADC","ADD","ADE","ADF","ADG","ADH","ADI","ADJ","ADK","ADL","ADM","ADN","ADO","ADP","ADQ","ADR","ADS","ADT","ADU","ADV","ADW","ADX","ADY","ADZ","AEA","AEB","AEC","AED","AEE","AEF","AEG","AEH","AEI","AEJ","AEK","AEL","AEM","AEN","AEO","AEP","AEQ","AER","AES","AET","AEU","AEV","AEW","AEX","AEY","AEZ","AFA","AFB","AFC","AFD","AFE","AFF","AFG","AFH","AFI","AFJ","AFK","AFL","AFM","AFN","AFO","AFP","AFQ","AFR","AFS","AFT","AFU","AFV","AFW","AFX","AFY","AFZ","AGA","AGB","AGC","AGD","AGE","AGF","AGG","AGH","AGI","AGJ","AGK","AGL","AGM","AGN","AGO","AGP","AGQ","AGR","AGS","AGT","AGU","AGV","AGW","AGX","AGY","AGZ","AHA","AHB","AHC","AHD","AHE","AHF","AHG","AHH","AHI","AHJ","AHK","AHL","AHM","AHN","AHO","AHP","AHQ","AHR","AHS","AHT","AHU","AHV","AHW","AHX","AHY","AHZ","AIA","AIB","AIC","AID","AIE","AIF","AIG","AIH","AII","AIJ","AIK","AIL","AIM","AIN","AIO","AIP","AIQ","AIR","AIS","AIT","AIU","AIV","AIW","AIX","AIY","AIZ","AJA","AJB","AJC","AJD","AJE","AJF","AJG","AJH","AJI","AJJ","AJK","AJL","AJM","AJN","AJO","AJP","AJQ","AJR","AJS","AJT","AJU","AJV","AJW","AJX","AJY","AJZ","AKA","AKB","AKC","AKD","AKE","AKF","AKG","AKH","AKI","AKJ","AKK","AKL","AKM","AKN","AKO","AKP","AKQ","AKR","AKS","AKT","AKU","AKV","AKW","AKX","AKY","AKZ","ALA","ALB","ALC","ALD","ALE","ALF","ALG","ALH","ALI","ALJ","ALK","ALL"];
;


                    $colsd = ["B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ","EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ","FA","FB","FC","FD","FE","FF","FG","FH","FI","FJ","FK","FL","FM","FN","FO","FP","FQ","FR","FS","FT","FU","FV","FW","FX","FY","FZ","GA","GB","GC","GD","GE","GF","GG","GH","GI","GJ","GK","GL","GM","GN","GO","GP","GQ","GR","GS","GT","GU","GV","GW","GX","GY","GZ","HA","HB","HC","HD","HE","HF","HG","HH","HI","HJ","HK","HL","HM","HN","HO","HP","HQ","HR","HS","HT","HU","HV","HW","HX","HY","HZ","IA","IB","IC","ID","IE","IF","IG","IH","II","IJ","IK","IL","IM","IN","IO","IP","IQ","IR","IS","IT","IU","IV","IW","IX","IY","IZ","JA","JB","JC","JD","JE","JF","JG","JH","JI","JJ","JK","JL","JM","JN","JO","JP","JQ","JR","JS","JT","JU","JV","JW","JX","JY","JZ","KA","KB","KC","KD","KE","KF","KG","KH","KI","KJ","KK","KL","KM","KN","KO","KP","KQ","KR","KS","KT","KU","KV","KW","KX","KY","KZ","LA","LB","LC","LD","LE","LF","LG","LH","LI","LJ","LK","LL","LM","LN","LO","LP","LQ","LR","LS","LT","LU","LV","LW","LX","LY","LZ","MA","MB","MC","MD","ME","MF","MG","MH","MI","MJ","MK","ML","MM","MN","MO","MP","MQ","MR","MS","MT","MU","MV","MW","MX","MY","MZ","NA","NB","NC","ND","NE","NF","NG","NH","NI","NJ","NK","NL","NM","NN","NO","NP","NQ","NR","NS","NT","NU","NV","NW","NX","NY","NZ","OA","OB","OC","OD","OE","OF","OG","OH","OI","OJ","OK","OL","OM","ON","OO","OP","OQ","OR","OS","OT","OU","OV","OW","OX","OY","OZ","PA","PB","PC","PD","PE","PF","PG","PH","PI","PJ","PK","PL","PM","PN","PO","PP","PQ","PR","PS","PT","PU","PV","PW","PX","PY","PZ","QA","QB","QC","QD","QE","QF","QG","QH","QI","QJ","QK","QL","QM","QN","QO","QP","QQ","QR","QS","QT","QU","QV","QW","QX","QY","QZ","RA","RB","RC","RD","RE","RF","RG","RH","RI","RJ","RK","RL","RM","RN","RO","RP","RQ","RR","RS","RT","RU","RV","RW","RX","RY","RZ","SA","SB","SC","SD","SE","SF","SG","SH","SI","SJ","SK","SL","SM","SN","SO","SP","SQ","SR","SS","ST","SU","SV","SW","SX","SY","SZ","TA","TB","TC","TD","TE","TF","TG","TH","TI","TJ","TK","TL","TM","TN","TO","TP","TQ","TR","TS","TT","TU","TV","TW","TX","TY","TZ","UA","UB","UC","UD","UE","UF","UG","UH","UI","UJ","UK","UL","UM","UN","UO","UP","UQ","UR","US","UT","UU","UV","UW","UX","UY","UZ","VA","VB","VC","VD","VE","VF","VG","VH","VI","VJ","VK","VL","VM","VN","VO","VP","VQ","VR","VS","VT","VU","VV","VW","VX","VY","VZ","WA","WB","WC","WD","WE","WF","WG","WH","WI","WJ","WK","WL","WM","WN","WO","WP","WQ","WR","WS","WT","WU","WV","WW","WX","WY","WZ","XA","XB","XC","XD","XE","XF","XG","XH","XI","XJ","XK","XL","XM","XN","XO","XP","XQ","XR","XS","XT","XU","XV","XW","XX","XY","XZ","YA","YB","YC","YD","YE","YF","YG","YH","YI","YJ","YK","YL","YM","YN","YO","YP","YQ","YR","YS","YT","YU","YV","YW","YX","YY","YZ","ZA","ZB","ZC","ZD","ZE","ZF","ZG","ZH","ZI","ZJ","ZK","ZL","ZM","ZN","ZO","ZP","ZQ","ZR","ZS","ZT","ZU","ZV","ZW","ZX","ZY","ZZ","AAA","AAB","AAC","AAD","AAE","AAF","AAG","AAH","AAI","AAJ","AAK","AAL","AAM","AAN","AAO","AAP","AAQ","AAR","AAS","AAT","AAU","AAV","AAW","AAX","AAY","AAZ","ABA","ABB","ABC","ABD","ABE","ABF","ABG","ABH","ABI","ABJ","ABK","ABL","ABM","ABN","ABO","ABP","ABQ","ABR","ABS","ABT","ABU","ABV","ABW","ABX","ABY","ABZ","ACA","ACB","ACC","ACD","ACE","ACF","ACG","ACH","ACI","ACJ","ACK","ACL","ACM","ACN","ACO","ACP","ACQ","ACR","ACS","ACT","ACU","ACV","ACW","ACX","ACY","ACZ","ADA","ADB","ADC","ADD","ADE","ADF","ADG","ADH","ADI","ADJ","ADK","ADL","ADM","ADN","ADO","ADP","ADQ","ADR","ADS","ADT","ADU","ADV","ADW","ADX","ADY","ADZ","AEA","AEB","AEC","AED","AEE","AEF","AEG","AEH","AEI","AEJ","AEK","AEL","AEM","AEN","AEO","AEP","AEQ","AER","AES","AET","AEU","AEV","AEW","AEX","AEY","AEZ","AFA","AFB","AFC","AFD","AFE","AFF","AFG","AFH","AFI","AFJ","AFK","AFL","AFM","AFN","AFO","AFP","AFQ","AFR","AFS","AFT","AFU","AFV","AFW","AFX","AFY","AFZ","AGA","AGB","AGC","AGD","AGE","AGF","AGG","AGH","AGI","AGJ","AGK","AGL","AGM","AGN","AGO","AGP","AGQ","AGR","AGS","AGT","AGU","AGV","AGW","AGX","AGY","AGZ","AHA","AHB","AHC","AHD","AHE","AHF","AHG","AHH","AHI","AHJ","AHK","AHL","AHM","AHN","AHO","AHP","AHQ","AHR","AHS","AHT","AHU","AHV","AHW","AHX","AHY","AHZ","AIA","AIB","AIC","AID","AIE","AIF","AIG","AIH","AII","AIJ","AIK","AIL","AIM","AIN","AIO","AIP","AIQ","AIR","AIS","AIT","AIU","AIV","AIW","AIX","AIY","AIZ","AJA","AJB","AJC","AJD","AJE","AJF","AJG","AJH","AJI","AJJ","AJK","AJL","AJM","AJN","AJO","AJP","AJQ","AJR","AJS","AJT","AJU","AJV","AJW","AJX","AJY","AJZ","AKA","AKB","AKC","AKD","AKE","AKF","AKG","AKH","AKI","AKJ","AKK","AKL","AKM","AKN","AKO","AKP","AKQ","AKR","AKS","AKT","AKU","AKV","AKW","AKX","AKY","AKZ","ALA","ALB","ALC","ALD","ALE","ALF","ALG","ALH","ALI","ALJ","ALK","ALL"];
;

                    $lastColKey = 0;
                    $nextCol = '';

                    $families = DB::connection($this->mysql)
                                    ->select("select y.family
                                            from tbl_yielding_performance as y
                                            inner join tbl_yielding_pya as p
                                            on y.pono = p.pono
                                            where 1=1".$date_cond.
                                            $ptype_cond.$family_cond."
                                            group by y.family");


                    foreach ($families as $key => $fam) {
                           // dd($fam);
                        array_push($fams, $fam->family);
                    }

                $data = DB::connection($this->mysql)
                        ->select(
                            DB::raw(
                                "CALL GetYieldSummaryPerFamily(
                                    '".$datefrom."',
                                    '".$dateto."',
                                    '".$req->prodtype."',
                                    '".$req->family."')"
                                )
                        );
                // dd($data);
                    $yieldsummary_query = DB::connection($this->mysql)->select("SELECT * FROM YieldSummaryPerFamily");
                     // dd($yieldsummary_query);
                    $yieldsummary_query2 = DB::connection($this->mysql)->select("SELECT * FROM SecondYieldSummaryPerFamily");                  
                    $defects_query = DB::connection($this->mysql)->select("SELECT * FROM DefectList");

                    $yield_arr = json_decode(json_encode($yieldsummary_query), true);
                     // dd($yield_arr );
                    $yield_arr2 = json_decode(json_encode($yieldsummary_query2), true);
                    $defects_arr = json_decode(json_encode($defects_query), true);

                    $device = [];
                    $po = [];
                    $tinput = [];
                    $toutput = [];
                    $totalyield = [];

                    $device2 = [];
                    $po2 = [];
                    $tinput2 = [];
                    $toutput2 = [];
                    $totalyield2 = [];

                    $defects = [];
                    $defects2 = [];
                    $defects_all = [];
                    $defectrows = [];
                    $defectrows2 = [];
                    $defect_rows = [];
                    $defect_rows2 = [];
                    $rows = 10;
                    $rows2 = 10;
                    $sample = "";


                    foreach ($yield_arr as $key => $y) {
                       // dd($y['device'],$y['pono'],$y['tinput'],$y['toutput'],$y['totalyield']);
                        array_push($device, $y['device']);
                        array_push($po, $y['pono']);
                        array_push($tinput, $y['tinput']);
                        array_push($toutput, $y['toutput']);
                        array_push($totalyield, $y['totalyield']);

                        // dd($defects_arr);
                        // dd($defects_arr);
                        foreach ($defects_arr as $key => $d) {
                                //   $sample = $y[$d['DefectID']];
                                // echo "<br> $sample ";

                            $count = 1;               
                            if (isset($y[$d['DefectID']])) {
                                // dd(isset($y[$d['DefectID']]));

                                if ($d['Defect'] == '') {
                                    // dd('sadsa');
                                    if($y['tinput'] <=0){
                                        $total = 0;
                                    }else{

                                        $total = floatval(($y[$d['DefectID']]/$y['tinput'])*100);  
                                        // dd($total); 
                                    }
                                    
                                    array_push($defects, [
                                        'defect' => $d['Defect'],
                                        'qty' => ($y[$d['DefectID']] == 0)? '0' : $y[$d['DefectID']],
                                        'rate' => $total,
                                        'po' => $y['pono']
                                    ]);

                                     array_push($defectrows, $d['Defect']);
                                }else
                                {
                                     if ($d['Defect'] !== '') {
                                    // dd('sadsa');
                                    if($y['tinput'] <=0){
                                        $total = 0;
                                    }else{

                                        $total = floatval(($y[$d['DefectID']]/$y['tinput'])*100);  
                                        // dd($total); 
                                    }
                                    
                                    array_push($defects, [
                                        'defect' => $d['Defect'],
                                        'qty' => ($y[$d['DefectID']] == 0)? '0' : $y[$d['DefectID']],
                                        'rate' => $total,
                                        'po' => $y['pono']
                                    ]);

                                     array_push($defectrows, $d['Defect']);
                                }

                                } 
                            }
                             
                        }
                    }
                     foreach ($yield_arr2 as $key => $y) {
                        array_push($device2, $y['device']);
                        array_push($po2, $y['pono']);
                        array_push($tinput2, $y['tinput']);
                        array_push($toutput2, $y['toutput']);
                        array_push($totalyield2, $y['totalyield']);


                        foreach ($defects_arr as $key => $d) {
                            $count = 1;
                            if (isset($y[$d['DefectID']])) {

                                if ($d['Defect'] !== '') {
                                    array_push($defects2, [
                                        'defect2' => $d['Defect'],
                                        'qty2' => ($y[$d['DefectID']] == 0)? '0.00' : $y[$d['DefectID']],
                                        'rate2' => floatval(($y[$d['DefectID']]/$y['tinput'])*100),
                                        'po2' => $y['pono']
                                    ]);

                                     array_push($defectrows2, $d['Defect']);
                                }
                            }
                             
                        }
                    }

                    foreach (array_unique($defectrows) as $key => $defect) {
                        $defect_rows[$rows] = $defect;
                        $rows++;
                    }
                     foreach (array_unique($defectrows2) as $key => $defect) {
                        $defect_rows2[$rows2] = $defect;
                        $rows2++;
                    }

                    foreach ($defect_rows as $key => $dr) {
                        foreach ($defects as $key => $df) {
                            if ($dr == $df['defect']) {
                                array_push($defects_all, [
                                    $df['defect'] => [
                                        'po' => $df['po'],
                                        'qty' => $df['qty'],
                                        'rate' => $df['rate']
                                    ]
                                ]);
                            }
                        }
                    }
                    $sheet->cell('A3', function($cell) {
                        $cell->setValue("Device Name");
                        $cell->setFont([

                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);

                        $cell->setBackground('#baddff');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $colcount = 0;
                    foreach ($device as $key => $dv) {
                        $sheet->mergeCells($cols[$colcount].'3:'.$cols[$colcount+3].'3');
                        $sheet->cell($cols[$colcount].'3', function($cell) use($dv) {
                            $cell->setValue($dv);
                            $cell->setAlignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,

                                // 'setAutoSize'=>  true,
                            ]);
                            $cell->setBackground('#baddff');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $colcount = $colcount+4;
                    }


                    $sheet->cell('A4', function($cell) {
                        $cell->setValue("P.O. Number");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#baddff');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $colcount = 0;
                    foreach ($po as $key => $p) {
                        $sheet->mergeCells($cols[$colcount].'4:'.$cols[$colcount+3].'4');
                        $sheet->cell($cols[$colcount].'4', function($cell) use($p) {
                            $cell->setValue($p);
                            $cell->setAlignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#baddff');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                                        
                        $colcount = $colcount+4;

                    }

                    $sheet->mergeCells($cols[$colcount].'3:'.$cols[$colcount+3].'4');

                    // dd($cols[$colcount]);    
                    $sheet->cell($cols[$colcount].'3', function($cell) {
                        $cell->setValue("OVERALL");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    // A5
                               
                    $sheet->cell('A5', function($cell) {
                        $cell->setValue("");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  false,
                        ]);

                        $cell->setBackground('#baddff');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');

                    });

                         $colcount = 0;   
                        foreach ($po as $key => $p) {

                                // $nxtcol = ($cols[$colcount+2]);
                                // dd($nxtcol);

                                // dd($cols[$colcount]);
                                 // $sheet->mergeCells($cols[$colcount+2].'5:'.$cols[$colcount+1].'5');
                                 $sheet->mergeCells($cols[$colcount].'5:'.$cols[$colcount+1].'5');
                                 $sheet->cell($cols[$colcount].'5', function($cell) use($p) {

                                 $cell->setValue("1st Passed");
                                    $cell->setAlignment('center');
                                 // $cell->setValue($cols[$colcount+2]."Second Passed");

                                 $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                               ]);
                                 $cell->setBackground('#baddff');
                                 $cell->setBorder('thin', 'thin', 'thin', 'thin');


                        });

                                 $sheet->mergeCells($cols[$colcount+2].'5:'.$cols[$colcount+3].'5');    

                                  $sheet->cell($cols[$colcount+2].'5', function($cell) use($p) {

                                 $cell->setValue("2nd Passed");
                                    $cell->setAlignment('center');
                                 // $cell->setValue($cols[$colcount+2]."Second Passed");

                                 $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                               ]);
                                 $cell->setBackground('#baddff');
                                 $cell->setBorder('thin', 'thin', 'thin', 'thin');


                        });   





                                 $colcount = $colcount+4;
                            # code...
                        }

                    $sheet->mergeCells($cols[$colcount].'5:'.$cols[$colcount+1].'5');

                     // dd($cols[$colcount+2]);    
                    $sheet->cell($cols[$colcount].'5', function($cell) {
                        $cell->setValue("1st Passed");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                     $sheet->mergeCells($cols[$colcount+2].'5:'.$cols[$colcount+3].'5');

                     // dd($cols[$colcount+2]);    
                    $sheet->cell($cols[$colcount+2].'5', function($cell) {
                        $cell->setValue("2nd Passed");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });



                   







                    // A5                                                                                                                                                                                                                                                                                                                  
                    $sheet->cell('A7', function($cell) {
                        $cell->setValue("Total Output");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  false,
                        ]);
                        $cell->setBackground('#FFCC99');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });






                    $colcount = 0;
                    $total_tinput = 0;


                    foreach ($tinput as $key => $ti) {
                        // dd($tinput);
                        $sheet->mergeCells($cols[$colcount].'7:'.$cols[$colcount+1].'7');
                        $sheet->cell($cols[$colcount].'7', function($cell) use($ti) {
                            $cell->setValue($ti);
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                         $sheet->mergeCells($cols[$colcount+2].'7:'.$cols[$colcount+3].'7');
                        $sheet->cell($cols[$colcount+2].'7', function($cell) use($ti) {
                            $cell->setValue("0");
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                        //  $sheet->mergeCells($cols[$colcount+3].'6:'.$cols[$colcount+1].'6');
                        // $sheet->cell($cols[$colcount+3].'6', function($cell) use($ti) {
                        //     $cell->setValue($ti);
                        //     $cell->setAlignment('center');
                        //     $cell->setValignment('center');
                        //     $cell->setFont([
                        //         'family'     => 'Calibri',
                        //         'size'       => '11',
                        //         'bold'       =>  false,
                        //     ]);
                        //     $cell->setBackground('#a1a1a1');
                        //     $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        // });   



                        $colcount = $colcount+4;
                        $total_tinput = $total_tinput + intval($ti);
                    }
                    $colcount2 = 0;
                    $total_tinput2 = 0;
                     foreach ($tinput2 as $key => $ti) {
                        // dd($tinput);

                        $sheet->mergeCells($cols[$colcount2+2].'7:'.$cols[$colcount2+3].'7');
                        $sheet->cell($cols[$colcount2+2].'7', function($cell) use($ti) {
                            $cell->setValue($ti);
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        $colcount2 = $colcount2+4;
                        $total_tinput2 = $total_tinput2 + intval($ti);
                    }

                    $sheet->mergeCells($cols[$colcount].'7:'.$cols[$colcount+1].'7');

                    $sheet->cell($cols[$colcount].'7', function($cell) use($total_tinput) {
                        $cell->setValue($total_tinput);
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $sheet->mergeCells($cols[$colcount+2].'7:'.$cols[$colcount+3].'7');

                    $sheet->cell($cols[$colcount+2].'7', function($cell) use($total_tinput2) {
                        $cell->setValue($total_tinput2);
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->cell('A6', function($cell) {
                        $cell->setValue("Total Input");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  false,
                        ]);
                        $cell->setBackground('#FFCC99');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $colcount = 0;
                    $total_toutput = 0;

                    foreach ($toutput as $key => $to) {
                        $sheet->mergeCells($cols[$colcount].'6:'.$cols[$colcount+1].'6');
                        $sheet->cell($cols[$colcount].'6', function($cell) use($to) {
                            $cell->setValue($to);
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                         $sheet->mergeCells($cols[$colcount+2].'6:'.$cols[$colcount+3].'6');
                        $sheet->cell($cols[$colcount+2].'6', function($cell) use($to) {
                            $cell->setValue("0");
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });   



                        $colcount = $colcount+4;
                        $total_toutput += intval($to);
                    }

                    $colcount2 = 0;
                    $total_toutput2 = 0;

                    foreach ($toutput2 as $key => $to) {
                        $sheet->mergeCells($cols[$colcount2+2].'6:'.$cols[$colcount2+3].'6');
                        $sheet->cell($cols[$colcount2+2].'6', function($cell) use($to) {
                            $cell->setValue($to);
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });   



                        $colcount2 = $colcount2+4;
                        $total_toutput2 += intval($to);
                    }

                    $sheet->mergeCells($cols[$colcount].'6:'.$cols[$colcount+1].'6');

                    $sheet->cell($cols[$colcount].'6', function($cell) use($total_toutput) {
                        $cell->setValue($total_toutput);
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                      $sheet->mergeCells($cols[$colcount+2].'6:'.$cols[$colcount+3].'6');

                    $sheet->cell($cols[$colcount+2].'6', function($cell) use($total_toutput2) {
                        $cell->setValue($total_toutput2);
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->cell('A8', function($cell) {
                        $cell->setValue("Total Yield");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  false,
                        ]);
                        $cell->setBackground('#FFCC99');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $colcount = 0;
                    // dd($totalyield);
                    foreach ($totalyield as $key => $ty) {
                    
                        $sheet->mergeCells($cols[$colcount].'8:'.$cols[$colcount+1].'8');
                        $sheet->cell($cols[$colcount].'8', function($cell) use($ty){
                            $cell->setValue(number_format($ty,2).'%');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        $sheet->mergeCells($cols[$colcount+2].'8:'.$cols[$colcount+3].'8');
                        $sheet->cell($cols[$colcount+2].'8', function($cell) use($ty){
                            $cell->setValue(number_format("0").'%');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                        $sheet->cell($cols[$colcount].'9', function($cell) use($ty){
                            $cell->setValue("Qty");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#f4ebab');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                        $sheet->cell($cols[$colcount+2].'9', function($cell) use($ty){
                            $cell->setValue("Qty");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#f4ebab');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });





                        $sheet->cell($cols[$colcount+1].'9', function($cell) use($ty){
                            $cell->setValue("Rate");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#f4ebab');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                         $sheet->cell($cols[$colcount+3].'9', function($cell) use($ty){
                            $cell->setValue("Rate");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#f4ebab');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                        $colcount = $colcount+4;
                    }
                      $colcount2 = 0;

                      foreach ($totalyield2 as $key => $ty) {
                         // dd($ty);
                            $sheet->mergeCells($cols[$colcount2+2].'8:'.$cols[$colcount2+3].'8');
                            $sheet->cell($cols[$colcount2+2].'8', function($cell) use($ty){
                            $cell->setValue(number_format($ty,2).'%');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#a1a1a1');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $colcount2 = $colcount2+4;
                      }
                    if($total_toutput == 0){

                        $total_totalyield = $total_tinput;
                    }else{
                        $total_totalyield = number_format((intval($total_tinput) / intval($total_toutput))*100,2).'%';  
                    }
                  
                    $sheet->mergeCells($cols[$colcount].'8:'.$cols[$colcount+1].'8');

                    $sheet->cell($cols[$colcount].'8', function($cell) use($total_totalyield) {
                        $cell->setValue($total_totalyield);
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                
                    $sheet->mergeCells($cols[$colcount+2].'8:'.$cols[$colcount+3].'8');

                    if ($yieldsummary_query2 <> []) {
                         $total_totalyield2 = number_format((intval($total_tinput2) / intval($total_toutput2))*100,2).'%';
                    }else{
                        $total_totalyield2 = 0;
                    }           
                    $sheet->cell($cols[$colcount+2].'8', function($cell) use($total_totalyield2) {
                        $cell->setValue($total_totalyield2);
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->cell($cols[$colcount].'9', function($cell) use($ty){
                        $cell->setValue("Qty");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->cell($cols[$colcount+2].'9', function($cell) use($ty){
                        $cell->setValue("Qty");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $sheet->cell($cols[$colcount+1].'9', function($cell) use($ty){
                        $cell->setValue("Rate");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $sheet->cell($cols[$colcount+3].'9', function($cell) use($ty){
                        $cell->setValue("Rate");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });



                    $sheet->cell('A9', function($cell) {
                        $cell->setValue("Defects");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#f4ebab');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $lastdefect = '';



                    // dd($defect_rows);                            
                    foreach ($defect_rows as $key => $dfr) {

                        $sheet->cell('A'.$key, function($cell) use($dfr) {
                            $cell->setValue($dfr);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  false,
                            ]);
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                    }

                    $colcount = 0;
                    $colcount2 = 0;
                    $row = 10;
                    $total_right_qty_arr = [];
                    $total_down_qty_arr = [];
                    $total_right_qty_arr2 = [];
                    $total_down_qty_arr2 = [];
                    $po_qty = 0;

                    foreach ($po as $key => $p) {
                                 // dd($defect_rows);
                        foreach ($defect_rows as $rkey => $dfr) {
                             // dd('sadsa');
                            foreach ($defects as $key => $df) {
                                // dd($df);
                                if ($dfr == $df['defect'] && $p == $df['po']) {
                                     // $test = $df['qty'];
                                     // echo "<br>deffects: $test";
                                     //DEAN//
                                    $total_right_qty_arr[$rkey] = (isset($total_right_qty_arr[$rkey]))? $total_right_qty_arr[$rkey] + $df['qty']: 0 + $df['qty'];

                                    // dd($df);
                                    $sheet->cell($cols[$colcount].$rkey, function($cell) use($df) {
                                        if($df['qty'] == 0) {
                                            $defect_qty = "";

                                        }else{
                                            $defect_qty = $df['qty'];
                                        }
                                        $cell->setValue($defect_qty);
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });

                                     $sheet->cell($cols[$colcount+2].$rkey, function($cell) use($df) {
                                        $cell->setValue("");
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });

                                    $sheet->cell($cols[$colcount+1].$rkey, function($cell) use($df) {
                                        // dd($df['rate']);
                                        if($df['rate'] == 0){
                                            $defect_rate = "";
                                        }else{
                                            // dd('dsasa');
                                             $defect_rate = (number_format(floatval($df['rate']),2).'%');
                                        }
                                        $cell->setValue($defect_rate);
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });


                                    $sheet->cell($cols[$colcount+3].$rkey, function($cell) use($df) {
                                        $cell->setValue("");
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });


                                    $total_down_qty_arr[$df['po']] = (isset($total_down_qty_arr[$df['po']]))? $total_down_qty_arr[$df['po']] + $df['qty']: 0 + $df['qty'];
                                }
                            }
                                   // dd($defects2);
                            foreach ($defects2 as $key => $df) {

                                        // dd($df['qty2']);
                                if ($dfr == $df['defect2'] && $p == $df['po2']) {
                                     $total_right_qty_arr2[$rkey] = (isset($total_right_qty_arr2[$rkey]))? $total_right_qty_arr2[$rkey] + $df['qty2']: 0 + $df['qty2'];

                                     $sheet->cell($cols[$colcount+2].$rkey, function($cell) use($df) {
                                        if($df['qty2'] == 0){
                                                
                                            $defect_qty2 = "";
                                        }else{

                                             $defect_qty2 = $df['qty2'];          
                                        }
                                        $cell->setValue($defect_qty2);
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });



                                    $sheet->cell($cols[$colcount+3].$rkey, function($cell) use($df) {
                                        if($df['rate2'] == 0){
                                            $deffect2 = "";     
                                        }else{
                                            $deffect2 = (number_format(floatval($df['rate2']),2).'%');
                                        }
                                        $cell->setValue($deffect2);
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });

                                     $total_down_qty_arr2[$df['po2']] = (isset($total_down_qty_arr2[$df['po2']]))? $total_down_qty_arr2[$df['po2']] + $df['qty2']: 0 + $df['qty2'];
                                }
                            }
                            
                            $row++;
                        }

                        $colcount = $colcount+4;
                    }

                    // dd($total_right_qty_arr2);
                     // dd($defect_rows2);
                    foreach ($defect_rows as $rkey => $dfr) {

                        // dd($total_right_qty_arr[$rkey]);

                        $sheet->cell($cols[$colcount].$rkey, function($cell) use($total_right_qty_arr,$rkey) {
                            // dd();
                            if($total_right_qty_arr[$rkey] == 0){
                                $testing = "";
                            }else{
                                $testing = $total_right_qty_arr[$rkey];
                            }
                            $cell->setValue($testing);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                         $sheet->cell($cols[$colcount+2].$rkey, function($cell) use($total_right_qty_arr,$rkey) {
                            $cell->setValue("");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                        $sheet->cell($cols[$colcount+1].$rkey, function($cell) use($total_right_qty_arr,$total_tinput,$rkey) {    
                          // dd($total_tinput);   
                            if($total_tinput != 0)
                            {
                                
                                $rate = ($total_right_qty_arr[$rkey]/$total_tinput)*100;
                                if($rate == 0){
                                 
                                    $defectrate = "";
                                }else{
                               
                                    $defectrate = (number_format($rate,2).'%');
                                }
                            }else{
                                $defectrate = "";
                            }
                           
                            $cell->setValue($defectrate);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });



                        $sheet->cell($cols[$colcount+3].$rkey, function($cell) use($total_right_qty_arr,$total_tinput2,$rkey) {
                            // $rate2 = ($total_right_qty_arr2[$rkey]/$total_tinput2)*100;
                            $cell->setValue("");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                    }
                      // dd($defect_rows2);
                    foreach ($defect_rows2 as $rkey => $dfr) {

                         if ($yieldsummary_query2 <> []) {
                            $sheet->cell($cols[$colcount+2].$rkey, function($cell) use($total_right_qty_arr2,$rkey) {
                                // dd($total_right_qty_arr2[$rkey]);
                                if($total_right_qty_arr2[$rkey] == 0){
                                    $defectqty2 = "";
                                }else{
                                    $defectqty2 = $total_right_qty_arr2[$rkey];
                                }
                                $cell->setValue($defectqty2);
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#87d7b2');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                        }else{
                             $sheet->cell($cols[$colcount+2].$rkey, function($cell) use($total_right_qty_arr,$rkey) {
                                // dd($total_right_qty_arr[$rkey]);
                                $cell->setValue("");
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#87d7b2');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                        }


                        if ($yieldsummary_query2 <> []) {
                            // dd($yieldsummary_query2);
                            $sheet->cell($cols[$colcount+3].$rkey, function($cell) use($total_right_qty_arr2,$total_tinput2,$rkey) {
                                $rate2 = ($total_right_qty_arr2[$rkey]/$total_tinput2)*100;
                                if($rate2 == 0){
                                    $rate2nd = "";
                                }else{
                                   $rate2nd = (number_format($rate2,2).'%');
                                }
                                $cell->setValue($rate2nd);
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                     'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#87d7b2');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                        }else{
                            $sheet->cell($cols[$colcount+3].$rkey, function($cell) use($total_right_qty_arr,$total_tinput2,$rkey) {
                                // $rate2 = ($total_right_qty_arr2[$rkey]/$total_tinput2)*100;
                                $cell->setValue(number_format(0,2).'%');
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#87d7b2');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                        }   
                    }




                    $overall_total_qty = 0;
                    $overall_total_qty2 = 0;
                    $rows = 10;
                    foreach ($total_right_qty_arr as $key => $qty) {
                        $overall_total_qty += $qty;
                        $rows++;
                    }
                    foreach ($total_right_qty_arr2 as $key => $qty) {
                        $overall_total_qty2 += $qty;
                    }

                    $sheet->cell($cols[$colcount].$rows, function($cell) use($overall_total_qty) {
                        $cell->setValue($overall_total_qty);
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                     $sheet->cell($cols[$colcount+2].$rows, function($cell) use($overall_total_qty2) {
                        if($overall_total_qty2 == 0){
                            $qty_overall = "";
                        }else{
                              $qty_overall = $overall_total_qty2;
                        }
                        $cell->setValue($qty_overall);
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });




                    $sheet->cell($cols[$colcount+1].$rows, function($cell) use($overall_total_qty,$total_tinput) {
                        if($total_tinput != 0 ){
                            $rate = ($overall_total_qty/$total_tinput)*100;
                              $cell->setValue(number_format($rate,2).'%');
                            
                        }else{
                            $rate = "";
                              $cell->setValue($rate);
                        }
                      
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                       
                    });

                    if ($yieldsummary_query2 <> []) {
                        $sheet->cell($cols[$colcount+3].$rows, function($cell) use($overall_total_qty2,$total_tinput2) {
                            $rate2 = ($overall_total_qty2/$total_tinput2)*100;
                            $cell->setValue(number_format($rate2,2).'%');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                    }else{
                        $sheet->cell($cols[$colcount+3].$rows, function($cell) use($overall_total_qty,$total_tinput2) {
                            // $rate2 = ($overall_total_qty2/$total_tinput2)*100;
                            $cell->setValue("");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                    }

                    $sheet->cell('A'.$rows, function($cell) {
                        $cell->setValue("Total Defects");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $colcount = 0;
                     // dd($po);
                    foreach ($po as $key => $p) {
                        // dd($p);
                        $sheet->cell($cols[$colcount].$rows, function($cell) use($total_down_qty_arr,$p) {
                            if($total_down_qty_arr[$p] == 0){
                                // dd('w');
                                $total_defect_qty = "";
                            }else{

                                $total_defect_qty = $total_down_qty_arr[$p];
                            }
                            $cell->setValue($total_down_qty_arr[$p]);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $sheet->cell($cols[$colcount+1].$rows, function($cell) use($total_down_qty_arr,$p,$key,$tinput) {
                            // echo "<br>po:[$p] qty:$total_down_qty_arr[$p] / tinput:$tinput[$key])*100 ";
                        
                           if($total_down_qty_arr[$p] == 0){
                             $defect_rate = ""; 
                            }else if($total_down_qty_arr[$p] && $tinput[$key] == 0 ){
                                 $defect_rate = "";
                            
                            }else{
                                 $rate = ($total_down_qty_arr[$p]/$tinput[$key])*100;                 
                                 $defect_rate = (number_format($rate,2).'%'); 
                            }                                   
                            $cell->setValue($defect_rate);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                         $sheet->cell($cols[$colcount+2].$rows, function($cell) use($total_down_qty_arr2,$p) {
                            $cell->setValue("");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });


                        $sheet->cell($cols[$colcount+3].$rows, function($cell) use($total_down_qty_arr2,$p,$key,$tinput2) {
                            // $rate = ($total_down_qty_arr2[$p]/$tinput2[$key])*100;
                            $cell->setValue("");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        
                        foreach ($po2 as $key2 => $p2) {
                            if ( $p2 == $p) {
                                $sheet->cell($cols[$colcount+2].$rows, function($cell) use($total_down_qty_arr2,$p2) {
                                $cell->setValue($total_down_qty_arr2[$p2]);
                                $cell->setFont([
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true,
                                ]);
                                $cell->setBackground('#87d7b2');
                                $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                });


                                $sheet->cell($cols[$colcount+3].$rows, function($cell) use($total_down_qty_arr2,$p2,$key2,$tinput2) {
                                    $rate2 = ($total_down_qty_arr2[$p2]/$tinput2[$key2])*100;
                                    $cell->setValue(number_format($rate2,2).'%');
                                    $cell->setFont([
                                        'family'     => 'Calibri',
                                        'size'       => '11',
                                        'bold'       =>  true,
                                    ]);
                                    $cell->setBackground('#87d7b2');
                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                });    
                            }   
                        }
                        $colcount = $colcount + 4;
                    }
                   
                });
                  })->download('xlsx');
    // });
        } else {

        }
    }

    



    public function loadchart(Request $request)
    {
        $df = $request->datefroms;
        $dt = $request->datetos;
       // var_dump($fixeddf);
        $table = DB::connection($this->mysql)->table('tbl_yielding_performance as y')
                    ->join('tbl_yielding_pya as pya','pya.pono','=','y.pono')
                    ->select(
                        DB::raw('y.family as family'),
                        DB::raw("SUM(y.toutput) as toutput"),
                        DB::raw("SUM(pya.qty) as qty"))
                    ->groupBy('y.family')
                    ->orderBy('y.family')
                    ->whereBetween('pya.productiondate', [
                        $this->com->convertDate($df,'Y-m-d'),
                        $this->com->convertDate($dt,'Y-m-d')
                    ])
                    ->get();
       return $table;
    }

    public function devreg_get_series(Request $request)
    {
        $family = $request->family;
        $table = DB::connection($this->mysql)->table('tbl_seriesregistration')->select('series')->where('family',$family)->get();
        return $table;
    }

    public function getYieldTargetForReport(Request $req)
    {
        $data = [];

        $datefrom = $this->com->convertDate($req->date_from,'Y-m-d');
        $dateto = $this->com->convertDate($req->date_to,'Y-m-d');

        $target = DB::connection($this->mysql)
                    ->select("SELECT yield FROM tbl_targetregistration
                            where '".$datefrom."' between datefrom and dateto
                            and '".$dateto."' between datefrom and dateto
                            and ptype = '".$req->prod_type."'");
        if (count((array)$target) > 0) {
            $data = [
                'target_yield' => $target[0]->yield
            ];
        } else {
            $data = [
                'target_yield' => 0.00
            ];
        }

        return response()->json($data);
    }

}
