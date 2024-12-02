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
                                        device_code,
                                        device_name,
                                        family,
                                        prod_type,
                                        series
                                FROM tbl_poregistration
                                where pono='".$req->po."'");
            if (count((array)$data) > 0) {
                return response()->json($data[0]);
            } else {
                $data = DB::connection($this->mssql)
                            ->SELECT("SELECT r.SORDER as pono, r.CODE as device_code, h.NAME as device_name, r.KVOL as po_qty, SUBSTRING(h.NAME, 1, CHARINDEX('-',h.NAME) - 1) as  series,
                                UPPER(i.BUNR) as prodtype, h.NOTE as family 
                                FROM XRECE r 
                                     LEFT JOIN XITEM i ON i.CODE = r.CODE
                                     LEFT JOIN XHEAD h ON h.CODE = r.CODE
                                WHERE i.BUNR IN('Burn-In','Test Sockets') AND r.SORDER = '$req->po'
                                GROUP BY r.SORDER, r.CODE, h.NAME, r.KVOL, i.BUNR, h.NOTE
                                ORDER BY i.BUNR, r.CODE");
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
                                        where p.mod <> ''".$date_cond.
                                        $ptype_cond.
                                        $family_cond.
                                        $series_cond.
                                        $device_cond.
                                        $po_cond."
                                        group by date_format(p.productiondate,'%d-%m')
                                        order by p.productiondate");


                foreach ($prod as $key => $prd) {
                    array_push($dates, $prd->dates);
                }

                $sheet->setAutoSize(true);
                $sheet->setCellValue('A1', 'Yield Performance Report');
                $sheet->mergeCells('A1:C1');

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
                $sheet->mergeCells('B4:C4');
                $sheet->cell('B4',$datefrom);

                $sheet->cell('A5', function($cell) {
                    $cell->setValue("To:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(5,20);
                $sheet->mergeCells('B5:C5');
                $sheet->cell('B5',$dateto);

                $sheet->cell('A6', function($cell) {
                    $cell->setValue("Product Type:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(6,20);
                $sheet->cell('B6',$req->ptype);

                $sheet->cell('A7', function($cell) {
                    $cell->setValue("Family:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(7,20);
                $sheet->cell('B7',$req->family);

                $sheet->cell('A8', function($cell) {
                    $cell->setValue("Series Name:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(8,20);
                $sheet->cell('B8',$req->series);

                $sheet->cell('A9', function($cell) {
                    $cell->setValue("Device Name:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(9,20);
                $sheet->cell('B9',$req->device);

                $sheet->cell('A10', function($cell) {
                    $cell->setValue("PO Number:");
                    $cell->setFont([
                        'italic'       =>  true,
                    ]);
                });
                $sheet->setHeight(10,20);
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

                $defect_data = DB::connection($this->mysql)
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
                                );
                // dd($defect_data);

             // $pya = DB::connection($this->mysql)->table('tbl_yielding_pya')
             //            ->where('pono','=',$req->po)
             //            ->select('remarks')
             //            ->orderBY('productiondate','DESC')
             //            ->get();

                $defects = json_decode(json_encode($defect_data), true);
                // dd($defects);

                 // $rework = json_decode(json_encode($rewok_ng), true);
                
                $row = 13;
                $defect_names = [];

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


                    
                   
                    foreach ($defects as $key => $df) 
                    {
                          // $countcol = count($df);

                          // echo "<bt>";
                          // echo "$countcol";
                       // dd($defects);
                        $totals = $df['mod'];
                        if ($totals == 'Input' || $totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)'  || $totals == 'TotalYield(%)2ndPassed') {
                            if($totals != 'TotalYield(%)2ndPassed'){

                                $sheet->mergeCells($dt[0].$rows[$key].':'.$dt[1].$rows[$key]);

                                $sheet->cell('A'.$rows[$key], function($cell) use($totals){
                                    $cell->setValue($totals);
                                    $cell->setBackground('#fff9ae');
                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                   // $sheet->cell($nextCol[0].$rows[$key],$df['TOTAL_PNG'] + $df['TOTAL_MNG']);
                                    // $sheet->cell($nextCol[1].$rows[$key],$df['REWORK']);
                                });


                                $sheet->cells($dt[0].$rows[$key].':'.$dt[1].$rows[$key], function($cells) {
                                    $cells->setBackground('#FFFFFF');
                                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                });


                                $sheet->cells($dt[0].$rows[$key], function($cells) {
                                    $cells->setBackground('#FFFFFF');
                                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                });

                                $sheet->cell($dt[0].$rows[$key],($df[$dates[$datekey].'_PNG'] == 0)? '0.00' : $df[$dates[$datekey].'_PNG']
                                // $sheet->cell($nextCol[0].$rows[$key],$df[$dates][$datekey]['REWORK']);  

                            );   
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
                            $sheet->cell($dt[0].$rows[$key],($df[$dates[$datekey].'_PNG'] == 0)? '0.00' : $df[$dates[$datekey].'_PNG']);
                            $sheet->cell($dt[1].$rows[$key],($df[$dates[$datekey].'_MNG'] == 0)? '0.00' : $df[$dates[$datekey].'_MNG']);
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


                $sheet->cell($nextCol[0].'11','      ');
                $sheet->cell($nextCol[1].'11','TOTAL');
                $sheet->cell($dateColums[$currIndex+1][0].'11','AFTER REWORK');

                $sheet->cell($nextCol[0].'11','      ');  
                 $sheet->cell($nextCol[0].'16','TOTAL:');    
                $sheet->cell($nextCol[1].'12','NG');
                $sheet->cell($dateColums[$currIndex+1][0].'12','NG');
                $sheet->setHeight(11,20);
                $sheet->setHeight(12,20);


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






                // dd($defects);
                foreach ($defects as $key => $df) {
                    $totals = $defect_names[$rows[$key]];
                    if ($totals == 'Input'  || $totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)' || $totals == 'TotalYield(%)2ndPassed' ) {

                            if(!  ($totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)' || $totals == 'TotalYield(%)2ndPassed' )){ 

                            // $sheet->cells($nextCol[0].':'.$nextCol[1]);    
                            // $sheet->cells($nextCol[0].$rows[$key].':'.$nextCol[1].$rows[$key], function($cells) {

                            // $sheet->cell($nextCol[0].$rows[$key],'=SUM(B26,B27)');
                            // $sheet->cell($nextCol[1].$rows[$key],$df['REWORK']);
                            // $cells->setBorder('thin', 'thin', 'thin', 'thin');

                            // // dd($sheet);                                               
                            // });                         
                             // $sheet->cell('P1','=SUM(P4,P10)');                           
                             // $sheet->setCellValue('H2','=SUM(G2*F2)');
                             $currIndex =  array_search($nextCol, $dateColums);
                            //echo "<br>nextCol[0] : $nextCol[0].rows[key] : $rows[$key] ---- nextCol[1]: $nextCol[1].rows[key]: $rows[$key]";
                             $sheet->cells($nextCol[0].$rows[$key].':'.$nextCol[1].$rows[$key], function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                            });

                              $sheet->cell($nextCol[1].$rows[$key],'=SUM(B26,B27)');


                            $sheet->cells($dateColums[$currIndex+1][0].$rows[$key], function($cells) {
                            $cells->setBorder('thin', 'thin', 'thin', 'thin');
                            });


                              $sheet->cell($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK']);


                             }

                    } else {
                        // echo "<br>";
                        // echo "$rows[$key]";
                        $currIndex =  array_search($nextCol, $dateColums);
                        $sheet->cells($nextCol[0].$rows[$key].':'.$nextCol[1].$rows[$key], function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                         $sheet->cell($nextCol[1].$rows[$key],$df['TOTAL_PNG'] + $df['TOTAL_MNG']);

                          $sheet->cells($dateColums[$currIndex+1][0].$rows[$key], function($cells) {
                             $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                         $sheet->cell($dateColums[$currIndex+1][0].$rows[$key],$df['REWORK']);
                         // $sheet->cell($nextCol[1].$rows[$key],($df['TOTAL_MNG'] == 0)? '0.00' : );
                         
                    }
                    $sheet->setHeight($rows[$key],20);
                }





                $lastRow = count($defects)+1+3+10;
                 // echo "<br>";
                 // echo "$lastRow";
                $sheet->cell('A'.$lastRow,"Last Row A");
                $sheet->cell('B'.$lastRow,"Last Row B");
                // $h = "hello";
                $total_text = array("Input"=>"Total Input","Output"=>"Total Output","Production - NG"=>"Total PNG","Material - NG"=>"Total MNG","Yield w/o MNG(%)"=>"Total Yield w/o MNG(%)","TotalYield(%)"=>"TotalYield(%)(1st Passed)","TotalYield(%)2ndPassed"=>"TotalYield(%)(2nd Passed)");
                // echo "$h";
                foreach ($defects as $key => $df) {
                    $totals = $defect_names[$rows[$key]];


                    if ($totals == 'Input' || $totals == 'Output' || $totals == 'Production - NG' || $totals == 'Material - NG' || $totals == 'Yield w/o MNG(%)' || $totals == 'TotalYield(%)' || $totals == 'TotalYield(%)2ndPassed') {


                        $sheet->mergeCells('B'.$lastRow.':'.'C'.$lastRow);
                        $sheet->cells('B'.$lastRow.':'.'C'.$lastRow, function($cells) {
                            $cells->setBackground('#ffffff');
                            $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                            $sheet->cell('A'.$lastRow,$total_text[$totals]);
                            $sheet->cells('A'.$lastRow, function($cells) {
                            $cells->setBackground('#AFFFA9');
                            });

                         $sheet->cell('B'.$lastRow,($df['TOTAL_PNG'] == 0)? '0.00' : $df['TOTAL_PNG']);
                         $sheet->setBorder('A'.$lastRow, 'thin');
                         $sheet->setBorder('B'.$lastRow, 'thin');

                         $lastRow++;
                         
                    } 

                    $sheet->setHeight($lastRow,20);
                }

            });
          })->download('xls');
        // });
    }
































































    public function defectSummary(Request $req)
    {
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
                                        where p.mod <> ''".$date_cond.
                                        $ptype_cond.
                                        $family_cond.
                                        $series_cond.
                                        $device_cond.
                                        $po_cond."
                                        group by y.family");


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

                $defects = json_decode(json_encode($defect_data), true);

                $row = 7;
                $defect_names = [];

                foreach ($defects as $key => $df) {
                    $defect_names[$row] = $df['mod'];
                    $row++;
                }

                $rows = array_keys($defect_names);

                
                foreach ($fams as $famkey => $family) {
                    $sheet->cell($cols[$famkey].'6',$family);

                    $no = 1;
                    foreach ($defects as $key => $df) {
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

                foreach ($defects as $key => $df) {
                    $sheet->cell($nextCol.$rows[$key], function($cell) use($df) {
                        $cell->setValue($df['TOTAL']);
                        $cell->setFont([
                            'bold'       =>  true,
                        ]);
                    });
                }


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

                    $sheet->cell('A13', function($cell) {
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

                    $totals = json_decode(json_encode($data), true);

                    $row = 7;
                    $total_names = [];

                    foreach ($totals as $key => $tn) {
                        $total_names[$row] = $tn['Input'];
                        $row++;
                    }

                    $rows = array_keys($total_names);

                    
                    foreach ($fams as $famkey => $family) {
                        $sheet->cell($cols[$famkey].'6', function($cell) use($family) {
                            $cell->setValue($family);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        foreach ($totals as $key => $tl) {
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

                            if ($total_names[$rows[$key]] == 'Yield w/o MNG' || $total_names[$rows[$key]] == 'Total Yield(%)') {
                                $sheet->cell($cols[$famkey].$rows[$key], function($cell) use($tl,$rows,$key,$family){
                                    $cell->setValue(($tl[$family] == 0)? '0.00%' : number_format($tl[$family],2).'%');
                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                });
                                $sheet->setHeight($rows[$key],20);
                            } else {
                                $sheet->cell($cols[$famkey].$rows[$key], function($cell) use($tl,$rows,$key,$family){
                                    $cell->setValue(($tl[$family] == 0)? '0.00' : $tl[$family]);
                                    $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                });
                                $sheet->setHeight($rows[$key],20);
                            }
                        }

                        $sheet->cell($cols[$famkey].'13', number_format($req->target_yield,2).'%');

                        $lastColKey = $famkey;
                    }

                    $nextCol = $cols[$lastColKey+1];

                    $sheet->cell($nextCol.'6','TOTAL');

                    foreach ($totals as $key => $tl) {
                        if ($total_names[$rows[$key]] == 'Yield w/o MNG' || $total_names[$rows[$key]] == 'Total Yield(%)') {

                            $sheet->cell($nextCol.$rows[$key], function($cell) use($tl) {
                                $cell->setValue(number_format($tl['TOTAL'],2) . '%');
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

                    $sheet->cells('A13:'.$nextCol.'13', function($cells) {
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

                    $cols = ["B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP"];
                    $colsd = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","AB","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP"];

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
                        array_push($fams, $fam->family);
                    }

                    DB::connection($this->mysql)
                        ->select(
                            DB::raw(
                                "CALL GetYieldSummaryPerFamily(
                                    '".$datefrom."',
                                    '".$dateto."',
                                    '".$req->prodtype."',
                                    '".$req->family."')"
                                )
                        );

                    $yieldsummary_query = DB::connection($this->mysql)->select("SELECT * FROM YieldSummaryPerFamily");
                    $defects_query = DB::connection($this->mysql)->select("SELECT * FROM DefectList");

                    $yield_arr = json_decode(json_encode($yieldsummary_query), true);
                    $defects_arr = json_decode(json_encode($defects_query), true);

                    $device = [];
                    $po = [];
                    $tinput = [];
                    $toutput = [];
                    $totalyield = [];
                    $defects = [];
                    $defects_all = [];
                    $defectrows = [];
                    $defect_rows = [];
                    $rows = 9;

                    foreach ($yield_arr as $key => $y) {
                        array_push($device, $y['device']);
                        array_push($po, $y['pono']);
                        array_push($tinput, $y['tinput']);
                        array_push($toutput, $y['toutput']);
                        array_push($totalyield, $y['totalyield']);

                        foreach ($defects_arr as $key => $d) {
                            $count = 1;
                            if (isset($y[$d['DefectID']])) {

                                if ($d['Defect'] !== '') {
                                    array_push($defects, [
                                        'defect' => $d['Defect'],
                                        'qty' => ($y[$d['DefectID']] == 0)? '0.00' : $y[$d['DefectID']],
                                        'rate' => floatval(($y[$d['DefectID']]/$y['tinput'])*100),
                                        'po' => $y['pono']
                                    ]);

                                    array_push($defectrows, $d['Defect']);
                                }
                            }
                             
                        }
                    }

                    foreach (array_unique($defectrows) as $key => $defect) {
                        $defect_rows[$rows] = $defect;
                        $rows++;
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
                        $sheet->mergeCells($cols[$colcount].'3:'.$cols[$colcount+1].'3');
                        $sheet->cell($cols[$colcount].'3', function($cell) use($dv) {
                            $cell->setValue($dv);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#baddff');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $colcount = $colcount+2;
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
                        $sheet->mergeCells($cols[$colcount].'4:'.$cols[$colcount+1].'4');
                        $sheet->cell($cols[$colcount].'4', function($cell) use($p) {
                            $cell->setValue($p);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#baddff');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $colcount = $colcount+2;
                    }

                    $sheet->mergeCells($cols[$colcount].'3:'.$cols[$colcount+1].'4');

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

                    $sheet->cell('A5', function($cell) {
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
                    $total_tinput = 0;
                    foreach ($tinput as $key => $ti) {
                        $sheet->mergeCells($cols[$colcount].'5:'.$cols[$colcount+1].'5');
                        $sheet->cell($cols[$colcount].'5', function($cell) use($ti) {
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
                        $colcount = $colcount+2;
                        $total_tinput += intval($ti);
                    }

                    $sheet->mergeCells($cols[$colcount].'5:'.$cols[$colcount+1].'5');

                    $sheet->cell($cols[$colcount].'5', function($cell) use($total_tinput) {
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

                    $sheet->cell('A6', function($cell) {
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
                        $colcount = $colcount+2;
                        $total_toutput += intval($to);
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

                    $sheet->cell('A7', function($cell) {
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

                    foreach ($totalyield as $key => $ty) {
                        $sheet->mergeCells($cols[$colcount].'7:'.$cols[$colcount+1].'7');
                        $sheet->cell($cols[$colcount].'7', function($cell) use($ty){
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

                        $sheet->cell($cols[$colcount].'8', function($cell) use($ty){
                            $cell->setValue("Qty");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#f4ebab');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        $sheet->cell($cols[$colcount+1].'8', function($cell) use($ty){
                            $cell->setValue("Rate");
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#f4ebab');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $colcount = $colcount+2;
                    }

                    $total_totalyield = number_format((intval($total_tinput) / intval($total_toutput))*100,2).'%';

                    $sheet->mergeCells($cols[$colcount].'7:'.$cols[$colcount+1].'7');

                    $sheet->cell($cols[$colcount].'7', function($cell) use($total_totalyield) {
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

                    $sheet->cell($cols[$colcount].'8', function($cell) use($ty){
                        $cell->setValue("Qty");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->cell($cols[$colcount+1].'8', function($cell) use($ty){
                        $cell->setValue("Rate");
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $sheet->cell('A8', function($cell) {
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
                    $row = 9;
                    $total_right_qty_arr = [];
                    $total_down_qty_arr = [];
                    $po_qty = 0;

                    foreach ($po as $key => $p) {
                        foreach ($defect_rows as $rkey => $dfr) {
                            foreach ($defects as $key => $df) {
                                if ($dfr == $df['defect'] && $p == $df['po']) {
                                    $total_right_qty_arr[$rkey] = (isset($total_right_qty_arr[$rkey]))? $total_right_qty_arr[$rkey] + $df['qty']: 0 + $df['qty'];

                                    $sheet->cell($cols[$colcount].$rkey, function($cell) use($df) {
                                        $cell->setValue($df['qty']);
                                        $cell->setFont([
                                            'family'     => 'Calibri',
                                            'size'       => '11',
                                            'bold'       =>  false,
                                        ]);
                                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                                    });

                                    $sheet->cell($cols[$colcount+1].$rkey, function($cell) use($df) {
                                        $cell->setValue(number_format(floatval($df['rate']),2).'%');
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
                            
                            $row++;
                        }

                        $colcount = $colcount+2;
                    }

                    foreach ($defect_rows as $rkey => $dfr) {
                        $sheet->cell($cols[$colcount].$rkey, function($cell) use($total_right_qty_arr,$rkey) {
                            $cell->setValue($total_right_qty_arr[$rkey]);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        $sheet->cell($cols[$colcount+1].$rkey, function($cell) use($total_right_qty_arr,$total_tinput,$rkey) {
                            $rate = ($total_right_qty_arr[$rkey]/$total_tinput)*100;
                            $cell->setValue(number_format($rate,2).'%');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                    }

                    $overall_total_qty = 0;
                    $rows = 9;
                    foreach ($total_right_qty_arr as $key => $qty) {
                        $overall_total_qty += $qty;
                        $rows++;
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

                    $sheet->cell($cols[$colcount+1].$rows, function($cell) use($overall_total_qty,$total_tinput) {
                        $rate = ($overall_total_qty/$total_tinput)*100;
                        $cell->setValue(number_format($rate,2).'%');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                        $cell->setBackground('#87d7b2');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

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
                    foreach ($po as $key => $p) {
                        $sheet->cell($cols[$colcount].$rows, function($cell) use($total_down_qty_arr,$p) {
                            $cell->setValue($total_down_qty_arr[$p]);
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        $sheet->cell($cols[$colcount+1].$rows, function($cell) use($total_down_qty_arr,$p,$total_tinput) {
                            $rate = ($total_down_qty_arr[$p]/$total_tinput)*100;
                            $cell->setValue(number_format($rate,2).'%');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            $cell->setBackground('#87d7b2');
                            $cell->setBorder('thin', 'thin', 'thin', 'thin');
                        });

                        $colcount = $colcount + 2;
                    }
                });
            })->download('xls');
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
