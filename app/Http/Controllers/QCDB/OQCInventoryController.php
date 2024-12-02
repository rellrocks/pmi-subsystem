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

class OQCInventoryController extends Controller
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
            $this->mysql = $this->com->userDBcon(Auth::user()->productline,'mysql');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline,'common');
        } else {
            return redirect('/');
        }
    }

    public function index()
    {
        $module_code = Config::get('constants.MODULE_CODE_OQCINV');
        if(!$this->com->getAccessRights($module_code, $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            $is_supervisor = Auth::user()->is_supervisor;
            return view('qcdb.oqc_inventory',['userProgramAccess' => $userProgramAccess, 'is_supervisor' => $is_supervisor]);
        }
    }

    public function getInventory(Request $req)
    {
        $output = '';
        $po = '';
        $date = '';
        $select = [
            'id',
            DB::raw("DATE_FORMAT(inventory_date,'%Y-%m-%d') as inventory_date"),
            DB::raw("DATE_FORMAT(lot_app_date,'%Y-%m-%d') as lot_date"),
            DB::raw("DATE_FORMAT(lot_app_date,'%H:%i:%S') as lot_time"),
            'po_no',
            'series_name',
            'quantity',
            'total_no_of_lots',
            'deleted',
            'create_user',
            'update_user',
            'created_at',
            DB::raw("DATE_FORMAT(updated_at,'%Y-%m-%d %H:%i:%S') as updated_at")
        ];

        if ($req->type == 'search') {
            if (!empty($req->search_po)) {
                $po = " AND po_no = '".$req->search_po."'";
            }

            if (($req->search_from !== '' || !empty($req->search_from)) && ($req->search_to !== '' || !empty($req->search_to))) {
                $date = " AND inventory_date BETWEEN '".$this->com->convertDate($req->search_from,'Y-m-d').
                        "' AND '".$this->com->convertDate($req->search_to,'Y-m-d')."'";
            }

            $where = $po.$date;

            $query = DB::connection($this->mysql)->table('oqc_inventory')
                        ->whereRaw("deleted=0 ".$where)
                        ->orderBy('id','desc')
                        ->select($select);
        } else {
            $query = DB::connection($this->mysql)->table('oqc_inventory')
                    ->orderBy('id','desc')
                    ->where('deleted',0)
                    ->select($select);
        }

        return Datatables::of($query)
                        ->editColumn('id', function($data) {
                            return $data->id;
                        })
                        ->addColumn('action', function($data) {
                            return '<button type="button" class="btn btn-sm btn-primary btn_edit_inventory">'.
                                        '<i class="fa fa-edit"></i> '.
                                    '</button>';
                        })
                        ->make(true);
    }

    public function PODetails(Request $req)
    {
        $return_data = [
            'msg' => 'Getting P.O. Details has failed.',
            'status' => 'failed',
            'data' => []
        ];

        $query = [];

        try {
            if (!empty($req->po)) {
                if ($req->is_probe > 0) {
                    $query = DB::connection($this->mssql)
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
                    $query = DB::connection($this->mssql)
                                ->table('XRECE as R')
                                ->leftJoin('XHEAD as H','R.CODE','=','H.CODE')
                                ->leftJoin('XCUST as C','R.CUST','=','C.CUST')
                                ->where('R.SORDER','like',$req->po."%")
                                ->select(DB::raw('R.SORDER as po'),
                                        DB::raw('R.CODE as device_code'),
                                        DB::raw('H.NAME as device_name'),
                                        DB::raw('R.CUST as customer_code'),
                                        DB::raw('C.CNAME as customer_name'),
                                        DB::raw('SUM(R.KVOL) as po_qty'))
                                ->groupBy('R.SORDER',
                                        'R.CODE',
                                        'H.NAME',
                                        'R.CUST',
                                        'C.CNAME')
                                ->get();

                }

                if (count($query)) {
                    $return_data = [
                        'msg' => '',
                        'status' => 'success',
                        'data' => $query
                    ];
                }
            } else {
                $return_data = [
                    'msg' => '',
                    'status' => 'success',
                    'data' => $query
                ];
            }
        } catch (\Exception $th) {
            $return_data = [
                        'msg' => $th->getMessage(),
                        'status' => 'error',
                        'data' => []
                    ];
        }

        return $return_data;
    }

    public function SaveInventory(Request $req)
    {
        $return_data = [
            'msg' => 'Saving data has failed.',
            'status' => 'failed',
            'data' => []
        ];

        $inserted = 0;

        try {

            $lot_app_date = $this->com->convertDate($req->lot_date. ' ' .$req->lot_time,'Y-m-d H:i:s');

            if (is_null($req->inventory_id) || $req->inventory_id == "") {
                $inserted = DB::connection($this->mysql)->table('oqc_inventory')
                                ->insert([
                                    'inventory_date' => $req->inventory_date,
                                    'lot_app_date' => $lot_app_date,
                                    'po_no' => $req->po_no,
                                    'series_name' => $req->series_name,
                                    'quantity' => $req->quantity,
                                    'total_no_of_lots' => $req->total_no_of_lots,
                                    'deleted' => 0,
                                    'create_user' => Auth::user()->user_id,
                                    'update_user' => Auth::user()->user_id,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
            } else {
                $inserted = DB::connection($this->mysql)->table('oqc_inventory')
                                ->where('id', $req->inventory_id)
                                ->update([
                                    'inventory_date' => $req->inventory_date,
                                    'lot_app_date' => $lot_app_date,
                                    'po_no' => $req->po_no,
                                    'series_name' => $req->series_name,
                                    'quantity' => $req->quantity,
                                    'total_no_of_lots' => $req->total_no_of_lots,
                                    'deleted' => 0,
                                    'update_user' => Auth::user()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
            }
            

            if ($inserted) {
                $return_data = [
                    'msg' => 'Data has successfully saved.',
                    'status' => 'success',
                    'data' => []
                ];
            }
        } catch (\Exception $th) {
            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
                'data' => []
            ];
        }

        return $return_data;
    }

    public function DeleteInventory(Request $req)
    {
        $return_data = [
            'msg' => 'Deleting data has failed.',
            'status' => 'failed',
            'data' => []
        ];

        $deleted = 0;

        try {
            $deleted = DB::connection($this->mysql)->table('oqc_inventory')
                            ->whereIn('id', $req->ids)
                            ->update([
                                'deleted' => 1,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'update_user' => Auth::user()->user_id
                            ]);

            if ($deleted) {
                $return_data = [
                    'msg' => 'Data has successfully deleted.',
                    'status' => 'success',
                    'data' => []
                ];
            }

        } catch (\Exception $th) {
            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
                'data' => []
            ];
        }

        return $return_data;
    }
    public function ExcelReport(Request $req)
    {
        $dt = Carbon::now();
        $dates = substr($dt->format('Ymd'), 2);

        Excel::create('OQC_Inventory_Report'.$dates, function($excel) use($dt,$req)
        {
            $com_info = $this->com->getCompanyInfo();
            $date_today = substr($dt->format('  M j, Y  h:i A '), 2);
            $dates = '';
            $from = explode('-', $req->from);
            $fromFormat = $from[2] . '-' . $from[0] . '-' . $from[1];
            $to = '';

            if ($req->from !== '' || !empty($req->from)) {
                if($req->to === '' || empty($req->to)){
                    $dates = " BETWEEN '".$fromFormat. "' AND '".$fromFormat."'";
                }else{
                    $to = explode('-', $req->to);
                    $toFormat = $to[2] . '-' . $to[0] . '-' . $to[1];
                    $dates = " BETWEEN '".$fromFormat. "' AND '".$toFormat."'";
                }
            }

            $sql = "SELECT LEFT(inventory_date,10) AS inventory_date,
                            LEFT(lot_app_date,10) AS lot_app_date, 
                            RIGHT(lot_app_date,8) AS lot_app_time, 
                            po_no, 
                            series_name, 
                            quantity, 
                            total_no_of_lots
                    FROM oqc_inventory
                    WHERE LEFT(inventory_date,10) ".$dates. "
                    AND deleted = 0
                    ORDER BY LEFT(inventory_date,10) DESC";

            $sqlData = DB::connection($this->mysql)->select($sql);
           
            foreach ($sqlData as $key => $inv) {
                $excel->sheet($fromFormat. ' - ' .$inv->inventory_date, function($sheet) use($com_info, $dates)
                {
                    
                    $sqlInv = "SELECT LEFT(inventory_date,10) AS inventory_date,
                            LEFT(lot_app_date,10) AS lot_app_date, 
                            RIGHT(lot_app_date,8) AS lot_app_time, 
                                po_no, 
                                series_name, 
                                quantity, 
                                total_no_of_lots
                            FROM oqc_inventory
                            WHERE LEFT(inventory_date,10) ".$dates. "
                            AND deleted = 0
                            ORDER BY LEFT(inventory_date,10) DESC";

                    $sqlInventory = DB::connection($this->mysql)->select($sqlInv);


                    $sheet->setFreeze('A8');

                    $sheet->setHeight(1, 15);
                    $sheet->mergeCells('A1:G1');
                    $sheet->cells('A1:G1', function($cells) {
                        $cells->setAlignment('center');
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12'
                        ]);
                    });
                    $sheet->cell('A1',$com_info['name']);

                    $sheet->setHeight(2, 15);
                    $sheet->mergeCells('A2:G2');
                    $sheet->cells('A2:G2', function($cells) {
                        $cells->setAlignment('center');
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12'
                        ]);
                    });
                    $sheet->cell('A2',$com_info['address']);

                    $sheet->setHeight(4, 20);
                    $sheet->mergeCells('A4:G5');
                    $sheet->cells('A4:G5', function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                            'underline'  =>  true
                        ]);
                    });
                    $sheet->cell('A4',"TS FOR OQC INVENTORY");


                    $sheet->mergeCells('A6:A7');
                    $sheet->cell('A6', function($cell) {
                        $cell->setValue("Inventory Date");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->mergeCells('B6:C6');
                    $sheet->cell('B6', function($cell) {
                        $cell->setValue("Lot Application");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('B7', function($cell) {
                        $cell->setValue("Date");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });
                    
                    $sheet->cell('C7', function($cell) {
                        $cell->setValue("Time");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->mergeCells('D6:D7');
                    $sheet->cell('D6', function($cell) {
                        $cell->setValue("P.O. Number");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->mergeCells('E6:E7');
                    $sheet->cell('E6', function($cell) {
                        $cell->setValue("Series Name");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->mergeCells('F6:F7');
                    $sheet->cell('F6', function($cell) {
                        $cell->setValue("Quantity");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->mergeCells('G6:G7');
                    $sheet->cell('G6', function($cell) {
                        $cell->setValue("Total No. of Lots");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true,
                        ]);
                    });

                 
                    $sheet->setHeight(10, 15);
                    $row = 8;

                    $totalQuantity = 0;
                    $sumOfTotalNoOfLots = 0;

                    foreach ($sqlInventory as $key => $inv) {
                            
                        $sheet->cells('A'.$row.':G'.$row, function($cells) {
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
        
                        $sheet->cell('A'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($inv->inventory_date);
                        });
        
                        $sheet->cell('B'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($inv->lot_app_date);
                        });
        
                        $sheet->cell('C'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($inv->lot_app_time);
                        });
        
                        $sheet->cell('D'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($inv->po_no);
                        });
        
                        $sheet->cell('E'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue($inv->series_name);
                        });
        
                        $sheet->cell('F'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue(number_format($inv->quantity));
                        });
        
                        $sheet->cell('G'.$row, function($cell) use($inv) {
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setValignment('center');
                            $cell->setValue(number_format($inv->total_no_of_lots));
                        });
    
                        $sheet->setWidth([
                            'A' => 30,
                            'B' => 30,
                            'C' => 30,
                            'D' => 40,
                            'E' => 50,
                            'F' => 30,
                            'G' => 30
                        ]);
    
                        $totalQuantity =  $totalQuantity + $inv->quantity;
                        $sumOfTotalNoOfLots = $sumOfTotalNoOfLots + $inv->total_no_of_lots;
                        $row++;
                    
                    }

                    $finalRow = $row;


                    
                    $sheet->cell('E'.$finalRow, function($cell) {
                        $cell->setValue("TOTAL");
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBackground('#fcbf49');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                            
                        ]);
                    });

                    $sheet->cell('F'.$finalRow, function($cell) use($totalQuantity){
                        $cell->setValue(number_format($totalQuantity));
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBackground('#fcbf49');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('G'.$finalRow, function($cell) use($sumOfTotalNoOfLots){
                        $cell->setValue(number_format($sumOfTotalNoOfLots));
                        $cell->setAlignment('center');
                        $cell->setValignment('center');
                        $cell->setBackground('#fcbf49');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                    });
                    
                });
                break;
            }

            

            

        })->download('xlsx');
    }
    public function PDFReport(Request $req)
	{
		$dates = '';
        $from = explode('-', $req->from);
        $fromFormat = $from[2] . '-' . $from[0] . '-' . $from[1];
        $to = '';

        if ($req->from !== '' || !empty($req->from)) {
            if($req->to === '' || empty($req->to)){
                    $dates = " BETWEEN '".$fromFormat. "' AND '".$fromFormat."'";
                }else{
                    $to = explode('-', $req->to);
                    $toFormat = $to[2] . '-' . $to[0] . '-' . $to[1];
                    $dates = " BETWEEN '".$fromFormat. "' AND '".$toFormat."'";
                }
        }
		
		
		if ($req->po !== '' || !empty($req->po)) {
			$po = " AND a.po_no = '".$req->po."'";
		}

        $sqlInv = "SELECT LEFT(inventory_date,10) AS inventory_date,
                    LEFT(lot_app_date,10) AS lot_app_date, 
                    RIGHT(lot_app_date,8) AS lot_app_time, 
                        po_no, 
                        series_name, 
                        quantity, 
                        total_no_of_lots
                    FROM oqc_inventory
                    WHERE LEFT(inventory_date,10) ".$dates. "
                    AND deleted = 0
                    ORDER BY LEFT(inventory_date,10) DESC";

        $details = DB::connection($this->mysql)->select($sqlInv);
		

        $dt = Carbon::now();
        $company_info = $this->com->getCompanyInfo();
        $date = substr($dt->format('  M j, Y  h:i A '), 2);

        $html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <html>
        <head>
            <style>
                .header,
                .footer {
                    width: 100%;
                    text-align: center;
                    position: fixed;
                }
                .header {
                    top: 0px;
                }
                .footer {
                    bottom: 0px;
                }
                .pagenum:before {
                    content: counter(page);
                }
                .fontArial
                {
                    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                }
            </style>
        </head>
        <body>
            <div class="footer fontArial">
                <hr />
                <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-size:12px;">
                    <tbody>
                        <tr>
                            <td align="left">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td>Date:</td>
                                        <td>'. $date .'</td>
                                    </tr>
                                </tbody>
                            </table>
                            </td>
                            <td align="right">
                            <table align="right" border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td>Page:</td>
                                        <td><span class="pagenum"></span></td>
                                    </tr>
                                </tbody>
                            </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <table class="fontArial" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                <tbody>
                    <tr>
                        <td align="center">
                        <h4>'. $company_info['name'] .'</h4>
                        <p style="line-height: 1.8px; font-size:12px; ">'. $company_info['address'] .'</p>
                        <h2><ins>TS FOR OQC INVENTORY</ins></h2>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="fontArial"  style="border: 2px solid black; border-collapse: collapse; width:100%; cellspacing:0; cellpadding:0; font-size:12px;">
                <thead style="border: 2px solid black; text-align: center;">
                    <tr>
                        <th style="border-right: 1px solid black;" scope="col"><strong>Inventory Date</strong></th>
                        <th style="border-right: 1px solid black;" scope="col"><strong>
                            <table style="width: 100%; margin:auto; padding : 0;">
                                <tr>
                                    <th colspan = "2" style = "border-bottom: 1px solid black; width : 100%; margin:auto; padding:0; text-align: center">Lot Application</th>
                                </tr>
                                <tr>
                                    <th style= "border-right: 1px solid black; width : 50%; margin:auto; padding:0; text-align: center"> Date </th>
                                    <th style= "width : 50%; margin:auto; padding:0; text-align: center"> Time </th>
                                </tr>
                            </table>
                        </strong></th>
                        <th style="border-right: 1px solid black;" scope="col"><strong>P.O. Number</strong></th>
                        <th style="border-right: 1px solid black;" scope="col"><strong>Series Name</strong></th>
                        <th style="border-right: 1px solid black;" scope="col"><strong>Quantity</strong></th>
                        <th scope="col"><strong>Total No. of Lots</strong></th>
                    </tr>
                </thead>
                <tbody>';

        	
            $html2 = '';
            $html3 = '';
            $html4 = '';
            $html5 = '';
            $totalQuantity = 0;
            $totalLots = 0;
            $inventoryDate = '';
            foreach ($details as $key => $row)
            {
         
                $inventoryDate = $row->inventory_date;
				$html2 = $html4 . '
					<tr>
						<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->inventory_date .'</td>
						<td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">
                            <table style="width: 100%; margin:auto; padding : 0;">';

                $html3 = $html2 .
                        '<tr>
                            <td style= "border-right: 1px solid black; width : 50%; margin:auto; padding:0; text-align: center">' . $row->lot_app_date . '</td>
                            <td style= "width : 50%; margin:auto; padding:0; text-align: center"> '. $row->lot_app_time .' </td>
                        </tr>';

                $html4 = $html3 . 
                            '</table>
                        </td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->po_no .'</td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. $row->series_name .'</td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. number_format($row->quantity) .'</td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center;">'. number_format($row->total_no_of_lots) .'</td>
                    </tr>';
					
               $totalQuantity = $totalQuantity + $row->quantity;
               $totalLots = $totalLots + $row->total_no_of_lots;

		
            }

            $html5 = '<tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center; background-color: #fcbf49;">TOTAL</td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center; background-color: #fcbf49;">'. number_format($totalQuantity) .'</td>
                        <td style="border-bottom: 1px solid black; border-right: 1px solid black; text-align: center; background-color: #fcbf49;">'. number_format($totalLots) .'</td>
                    </tr>
                </tbody>
            </table>
            </body>
            </html>';

			$drawHtml = $html .  $html4 . $html5;
			
			$dompdf = new Dompdf();
			$dompdf->loadHTML($drawHtml);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
       
        return $dompdf->stream('OQC_Inventory_'.$inventoryDate.'_'.Carbon::now().'.pdf');

	}

    public function ReportDataCheck(Request $req){

        $data = [
            'return_status' => 0
        ];

        $dates = '';
        $from = explode('-', $req->from);
        $fromFormat = $from[2] . '-' . $from[0] . '-' . $from[1];
        $to = '';

        if ($req->from !== '' || !empty($req->from)) {
            if($req->to === '' || empty($req->to)){
                $dates = " BETWEEN '".$fromFormat. "' AND '".$fromFormat."'";
            }else{
                $to = explode('-', $req->to);
                $toFormat = $to[2] . '-' . $to[0] . '-' . $to[1];
                $dates = " BETWEEN '".$fromFormat. "' AND '".$toFormat."'";
            }
        }
		
		if ($req->po !== '' || !empty($req->po)) {
			$po = " AND a.po_no = '".$req->po."'";
		}

        $sqlInv = "SELECT LEFT(inventory_date,10) AS inventory_date,
                    LEFT(lot_app_date,10) AS lot_app_date, 
                    RIGHT(lot_app_date,8) AS lot_app_time, 
                        po_no, 
                        series_name, 
                        quantity, 
                        total_no_of_lots
                    FROM oqc_inventory
                    WHERE LEFT(inventory_date,10) ".$dates. "
                    ORDER BY LEFT(inventory_date,10) DESC";

        $details = DB::connection($this->mysql)->select($sqlInv);

        if(count($details) == 0){
            return $data;
        }else{
            return $data = [
                'return_status' => 1
            ];
        }
    }

}
