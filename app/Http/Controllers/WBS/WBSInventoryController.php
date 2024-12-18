<?php

namespace App\Http\Controllers\WBS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use Carbon\Carbon; 
use Datatables;
use Config;
use DB;
use Excel;

class WBSInventoryController extends Controller
{
    protected $mysql;
    protected $wbs;
    protected $mssql;
    protected $common;
    protected $com;

    public function __construct()
    {
        $this->middleware('auth');
        $this->com = new CommonController;

        if (Auth::user() != null) {
            $this->mysql = $this->com->userDBcon(Auth::user()->productline,'mysql');
            $this->wbs = $this->com->userDBcon(Auth::user()->productline,'wbs');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline,'common');
        } else {
            return redirect('/');
        }
    }

    public function index()
    {
        $pgcode = Config::get('constants.MODULE_WBS_INV');

        if(!$this->com->getAccessRights($pgcode, $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            # Render WBS Page.
            // DB::connection($this->wbs)
            //     ->select(DB::raw("CALL spWBSInventory()"));
            return view('wbs.wbsinventory',[
                            'userProgramAccess' => $userProgramAccess,
                            'pgcode' => $pgcode,
                            'pgaccess' => $this->com->getPgAccess($pgcode)
                        ]);
        }
    }

    //2023-07-29 : Armando/
    //Datatable Execute Problem
    public function inventory_list(Request $req) {
        $invoice_cond = '';
        $item_cond = '';
        $lot_cond = '';
        $date_cond = '';
        $iqc_status_cond = "";
        $sql_query = "";

        $columns = [
            0 => 'id',
            1 => 'action',
            2 => 'wbs_mr_id',
            3 => 'invoice_no',
            4 => 'item',
            5 => 'item_desc',
            6 => 'qty',
            7 => 'lot_no',
            8 => 'location',
            9 => 'supplier',
            10 => 'iqc_status',
            11 => 'ngr_status',
            12 => 'ngr_disposition',
            13 => 'ngr_control_no',
            14 => 'create_user',
            15 => 'received_date',
            16 => 'kit_disabled',
            17 => 'update_user',
            18 => 'updated_at',
        ];

        try {
            if(empty($req->srch_invoice))
            {
                $invoice_cond ='';
            } else {
                $invoice_cond = " AND invoice_no = '" . $req->srch_invoice . "' ";
            }
            if(empty($req->srch_item))
            {
                $item_cond ='';
            } else {
                $item_cond = " AND item = '" . $req->srch_item . "' ";
            }
            if(empty($req->srch_lot_no))
            {
                $lot_cond ='';
            } else {
                $lot_cond = " AND lot_no = '" . $req->srch_lot_no . "' ";
            }

            if(empty($req->srch_judgment))
            {
                $iqc_status_cond ='';
            } else {
                $iqc_status_cond = " AND iqc_status = " . $req->srch_judgment . " ";
            }

            if (!empty($req->srch_from) && !empty($req->srch_to)) {
                $date_cond = " AND received_date BETWEEN '" . $req->srch_from . "' AND '" . $req->srch_to . "' ";
            } else {
                $date_cond = '';
            }

            $conn_wbs = "pmi_wbs_".strtolower(Auth::user()->productline); 
            $conn_mysql = "pmi_".strtolower(Auth::user()->productline);
            
            //Datatable inputs
            $search = $req->search['value'];
            $limit = $req->length;
            $start = $req->start;
            $order = $columns[$req->input('order.0.column')];
            $dir = $req->input('order.0.dir');

            $alias = "i.id AS id,
            i.wbs_mr_id AS wbs_mr_id,
            i.invoice_no AS invoice_no,
            i.item AS item,
            i.item_desc AS item_desc,
            i.qty AS qty,
            i.lot_no AS lot_no,
            i.location AS location,
            i.supplier AS supplier,
            i.not_for_iqc AS not_for_iqc,
            i.iqc_status AS iqc_status,
            i.judgement AS judgement,
            i.create_user AS create_user,
            i.received_date as received_date,
            i.update_user AS update_user,
            i.updated_at AS updated_at,
            i.mat_batch_id AS mat_batch_id,
            i.loc_batch_id AS loc_batch_id,
            ngr.description AS ngr_status, 
            i.ngr_disposition AS ngr_disposition, 
            i.ngr_control_no AS ngr_control_no, 
            IF(i.kit_disabled = 1, 'Disabled', '') AS kit_disabled ";

            if(empty($search)) {

                $sql_query_count = "SELECT COUNT(i.id) as maxRow FROM ".$conn_wbs.".tbl_wbs_inventory AS i LEFT JOIN ".$conn_mysql.".iqc_ngr_master AS ngr ON ngr.id = i.ngr_status WHERE i.deleted = 0 AND i.wbs_mr_id <> ''".$invoice_cond.$item_cond.$lot_cond.$iqc_status_cond.$date_cond; 
                
                $sql_query_list = "SELECT  ".$alias." FROM ".$conn_wbs.".tbl_wbs_inventory AS i LEFT JOIN ".$conn_mysql.".iqc_ngr_master AS ngr ON ngr.id = i.ngr_status WHERE i.deleted = 0 AND i.wbs_mr_id <> ''".$invoice_cond.$item_cond.$lot_cond.$iqc_status_cond.$date_cond." order by ".$order." ".$dir." limit ".$limit." offset ".$start;
                
                $execute_query = DB::select($sql_query_count);
                $total_row = ($execute_query[0]->maxRow > 0 ? $execute_query[0]->maxRow : 0);
                $data_list = DB::select($sql_query_list);
            }else {

                $like = "where i.deleted = 0 AND (i.wbs_mr_id LIKE '%".$search."%'
                OR i.invoice_no LIKE '%".$search."%'
                OR i.item LIKE '%".$search."%'
                OR i.item_desc LIKE '%".$search."%'
                OR i.qty LIKE '%".$search."%'
                OR i.lot_no LIKE '%".$search."%'
                OR i.location LIKE '%".$search."%'
                OR i.supplier LIKE '%".$search."%'
                OR i.judgement LIKE '%".$search."%'
                OR i.create_user LIKE '%".$search."%'
                OR i.received_date LIKE '%".$search."%'
                OR i.update_user LIKE '%".$search."%'
                OR ngr.description LIKE '%".$search."%'
                OR i.ngr_disposition LIKE '%".$search."%'
                OR i.ngr_control_no LIKE '%".$search."%')";

                $sql_query_list = "SELECT  ".$alias." FROM ".$conn_wbs.".tbl_wbs_inventory AS i LEFT JOIN ".$conn_mysql.".iqc_ngr_master AS ngr ON ngr.id = i.ngr_status ";
                $sql_query_list .= $like;
                $sql_query_list .= " order by ".$order." ".$dir." limit ".$limit." offset ".$start;

                $sql_query_count = "SELECT COUNT(i.id) as maxRow  FROM ".$conn_wbs.".tbl_wbs_inventory AS i LEFT JOIN ".$conn_mysql.".iqc_ngr_master AS ngr ON ngr.id = i.ngr_status ";
                $sql_query_count .= $like;
                $sql_query_count .= " limit ".$limit." offset ".$start;


                $execute_query = DB::select($sql_query_count);
                $total_row = ($execute_query[0]->maxRow > 0 ? $execute_query[0]->maxRow : 0);
                $data_list = DB::select($sql_query_list);
            }

            $data = [];

            if (!empty($data_list)) {
                $data = $data_list;
            }

            $json_data = [
                'draw' => intval($req->draw),
                'recordsTotal' => intval($total_row),
                'recordsFiltered' => intval($total_row),
                'data' => $data
            ];

            return json_encode($json_data);

        } catch (\Throwable $th) {
        return json_encode($th);
        } 
    }
    public function inventory_list_old(Request $req)
    {
        $invoice_cond = '';
        $item_cond = '';
        $lot_cond = '';
        $date_cond = '';
        $iqc_status_cond = "";
        $sql_query = "";

        try {
            $search = $req->search['value'];
            if(empty($req->srch_invoice))
            {
                $invoice_cond ='';
            } else {
                $invoice_cond = " AND invoice_no = '" . $req->srch_invoice . "' ";
            }

            if(empty($req->srch_item))
            {
                $item_cond ='';
            } else {
                $item_cond = " AND item = '" . $req->srch_item . "' ";
            }

            if(empty($req->srch_lot_no))
            {
                $lot_cond ='';
            } else {
                $lot_cond = " AND lot_no = '" . $req->srch_lot_no . "' ";
            }

            if(empty($req->srch_judgment))
            {
                $iqc_status_cond ='';
            } else {
                $iqc_status_cond = " AND iqc_status = " . $req->srch_judgment . " ";
            }

            if (!empty($req->srch_from) && !empty($req->srch_to)) {
                $date_cond = " AND received_date BETWEEN '" . $req->srch_from . "' AND '" . $req->srch_to . "' ";
            } else {
                $date_cond = '';
            }

            $conn_wbs = "pmi_wbs_".strtolower(Auth::user()->productline); 
            $conn_mysql = "pmi_".strtolower(Auth::user()->productline);

            $sql_query = "select i.id as id,
                                i.wbs_mr_id as wbs_mr_id,
                                i.invoice_no as invoice_no,
                                i.item as item,
                                i.item_desc as item_desc,
                                i.qty as qty,
                                i.lot_no as lot_no,
                                i.location as location,
                                i.supplier as supplier,
                                i.not_for_iqc as not_for_iqc,
                                i.iqc_status as iqc_status,
                                i.judgement as judgement,
                                i.create_user as create_user,
                                i.received_date as received_date,
                                i.update_user as update_user,
                                i.updated_at as updated_at,
                                i.mat_batch_id as mat_batch_id,
                                i.loc_batch_id as loc_batch_id,
                                ngr.description as ngr_status,
                                i.ngr_disposition as ngr_disposition,
                                i.ngr_control_no as ngr_control_no,
                                IF(i.kit_disabled = 1, 'Disabled', '') as kit_disabled
                        from ".$conn_wbs.".tbl_wbs_inventory as i
                        LEFT JOIN ".$conn_mysql.".iqc_ngr_master as ngr
                        on ngr.id = i.ngr_status
                        where i.deleted = 0 AND i.wbs_mr_id <> ''".$invoice_cond.$item_cond.$lot_cond.$iqc_status_cond.$date_cond;

            $execute_query = DB::select($sql_query);
            $totalData = count($execute_query);

            $columns = [
                0 => 'id',
                1 => 'action',
                2 => 'wbs_mr_id',
                3 => 'invoice_no',
                4 => 'item',
                5 => 'item_desc',
                6 => 'qty',
                7 => 'lot_no',
                8 => 'location',
                9 => 'supplier',
                10 => 'iqc_status',
                11 => 'ngr_status',
                12 => 'ngr_disposition',
                13 => 'ngr_control_no',
                14 => 'create_user',
                15 => 'received_date',
                16 => 'kit_disabled',
                17 => 'update_user',
                18 => 'updated_at',
            ];

            $totalFiltered = $totalData;

            $limit = $req->length;
            $start = $req->start;
            $order = $columns[$req->input('order.0.column')];
            $dir = $req->input('order.0.dir');

            $search = $req->input('search.value');

            if ($invoice_cond != "" || $item_cond != "" || $lot_cond != "" || $date_cond != "" || $iqc_status_cond != "") {
                $search = "";
            }

            if (empty($search)) {
                $inv = DB::select($sql_query." order by ".$order." ".$dir." limit ".$limit." offset ".$start);
            } else {
                $sql_query = "select i.id as id,
                                i.wbs_mr_id as wbs_mr_id,
                                i.invoice_no as invoice_no,
                                i.item as item,
                                i.item_desc as item_desc,
                                i.qty as qty,
                                i.lot_no as lot_no,
                                i.location as location,
                                i.supplier as supplier,
                                i.not_for_iqc as not_for_iqc,
                                i.iqc_status as iqc_status,
                                i.judgement as judgement,
                                i.create_user as create_user,
                                i.received_date as received_date,
                                i.update_user as update_user,
                                i.updated_at as updated_at,
                                i.mat_batch_id as mat_batch_id,
                                i.loc_batch_id as loc_batch_id,
                                ngr.description as ngr_status,
                                i.ngr_disposition as ngr_disposition,
                                i.ngr_control_no as ngr_control_no,
                                IF(i.kit_disabled = 1, 'Disabled', '') as kit_disabled
                        from ".$conn_wbs.".tbl_wbs_inventory as i
                        LEFT JOIN ".$conn_mysql.".iqc_ngr_master as ngr
                        on ngr.id = i.ngr_status
                        where i.deleted = 0 AND (i.wbs_mr_id LIKE '%".$search."%'
                            OR i.invoice_no LIKE '%".$search."%'
                            OR i.item LIKE '%".$search."%'
                            OR i.item_desc LIKE '%".$search."%'
                            OR i.qty LIKE '%".$search."%'
                            OR i.lot_no LIKE '%".$search."%'
                            OR i.location LIKE '%".$search."%'
                            OR i.supplier LIKE '%".$search."%'
                            OR i.judgement LIKE '%".$search."%'
                            OR i.create_user LIKE '%".$search."%'
                            OR i.received_date LIKE '%".$search."%'
                            OR i.update_user LIKE '%".$search."%'
                            OR ngr.description LIKE '%".$search."%'
                            OR i.ngr_disposition LIKE '%".$search."%'
                            OR i.ngr_control_no LIKE '%".$search."%') ";

                $inv = DB::select($sql_query." order by ".$order." ".$dir." limit ".$limit." offset ".$start);

                $totalFiltered = count($inv);
            }

            $data = [];

            if (!empty($inv)) {
                $data = $inv;
            }

            $json_data = [
                'draw' => intval($req->draw),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data
            ];

            return json_encode($json_data);
        } catch (\Throwable $th) {
            return json_encode($th);
        }
    }

    private function formatDate($date, $format)
	{
		if(empty($date))
		{
			return null;
		}
		else
		{
			return date($format,strtotime($date));
		}
	}

    public function deleteselected(Request $req)
    {
        $data = [
            'msg' => "Deleting failed.",
            'status' => 'failed'
        ];
        foreach ($req->id as $key => $id) {
            $deleted = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                        ->where('id',$id)
                        ->update([
                        	'deleted' => 1,
                        	'update_user' => Auth::user()->user_id,
                        	'updated_at' => date('Y-m-d h:i:s'),
                        	'update_pg' => 'WBS Inventory - DELETED'

                        ]);

            if ($deleted) {
                $data = [
                    'msg' => "Data were successfully deleted.",
                    'status' => 'success'
                ];
            }
        }

        return $data;
    }

    public function savedata(Request $req)
    {
        $result = [
            'msg' => 'Update was unsuccessful.',
            'status' => 'failed'
        ];
        $NFI = 0;

        $judgement = "Pending";
        switch ($req->status) {
            case 1:
                $judgement = "Accepted";
                $kit = 1;
                break;
            case 2:
                $judgement = "Rejected";
                break;
            case 3:
                $judgement = "On-gping";
                break;
            case 4:
                $judgement = "Special Accept";
                $kit = 1;
                break;
            
            default:
                $judgement = "Pending";
                break;
        }

        if (isset($req->id)) {
            $NFI = (isset($req->nr))?1:0;
            $UP = DB::connection($this->wbs)
                    ->table('tbl_wbs_inventory')
                    ->where('id',$req->id)
                    ->update([
                        'item' => $req->item,
                        'item_desc' => $req->item_desc,
                        'lot_no' => $req->lot_no,
                        'qty' => $req->qty,
                        'not_for_iqc'=> $NFI,
                        'location' => $req->location,
                        'supplier' => $req->supplier,
                        'iqc_status' => $req->status,
                        'for_kitting' => $kit,
                        'judgement' => $judgement,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s'),
                        'update_pg' => 'WBS Inventory - EDITED'
                    ]);
            if ($UP) {
                $result = [
                    'msg' => 'Data was successfully updated.',
                    'status' => 'success'
                ];

                // DB::connection($this->wbs)
                //     ->select(DB::raw("CALL spWBSInventory()"));
            }
            
        }
        return response()->json($result);
    }

    public function inventory_excel()
    {
        $dt = Carbon::now();
        $date = $dt->format('m-d-y');

        $com_info = $this->com->getCompanyInfo();

        $data = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                            ->select(
                                'wbs_mr_id',
                                'item',
                                'item_desc',
                                'qty',
                                'lot_no',
                                'location',
                                'supplier',
                                'iqc_status',
                                'received_date'
                            )->get();

        // return dd($data);
        
        Excel::create('WBS_Inventory_List_'.$date, function($excel) use($com_info,$data)
        {
            $excel->sheet('Inventory', function($sheet) use($com_info,$data)
            {
                $sheet->setHeight(1, 15);
                $sheet->mergeCells('A1:J1');
                $sheet->cells('A1:J1', function($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A1',$com_info['name']);

                $sheet->setHeight(2, 15);
                $sheet->mergeCells('A2:J2');
                $sheet->cells('A2:J2', function($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A2',$com_info['address']);

                $sheet->setHeight(4, 20);
                $sheet->mergeCells('A4:J4');
                $sheet->cells('A4:J4', function($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont([
                        'family'     => 'Calibri',
                        'size'       => '14',
                        'bold'       =>  true,
                        'underline'  =>  true
                    ]);
                });
                $sheet->cell('A4',"WBS INVENTORY LIST");

                $sheet->setHeight(6, 15);
                $sheet->cells('A6:J6', function($cells) {
                    $cells->setFont([
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true,
                    ]);
                    // Set all borders (top, right, bottom, left)
                    $cells->setBorder('thick', 'thick', 'thick', 'thick');
                });
                $sheet->cell('A6', "");
                $sheet->cell('B6', "Control No.");
                $sheet->cell('C6', "Code");
                $sheet->cell('D6', "Description");
                $sheet->cell('E6', "Quantity");
                $sheet->cell('F6', "Lot No.");
                $sheet->cell('G6', "Location");
                $sheet->cell('H6', "Supplier");
                $sheet->cell('I6', "IQC Status");
                $sheet->cell('J6', "Received DAte");

                $row = 7;
                
                //return dd($com_info);

                $count = 1;
                $status = '';

                foreach ($data as $key => $wbs) {
                    $sheet->setHeight($row, 15);
                    $sheet->cell('A'.$row, $count);
                    $sheet->cell('B'.$row, $wbs->wbs_mr_id);
                    $sheet->cell('C'.$row, $wbs->item);
                    $sheet->cell('D'.$row, $wbs->item_desc);
                    $sheet->cell('E'.$row, $wbs->qty);
                    $sheet->cell('F'.$row, $wbs->lot_no);
                    $sheet->cell('G'.$row, $wbs->location);
                    $sheet->cell('H'.$row, $wbs->supplier);

                    switch ($wbs->iqc_status) {
                        case 0:
                            $status = 'Pending';
                            break;

                        case 1:
                            $status = 'Accepted';
                            break;

                        case 2:
                            $status = 'Rejected';
                            break;

                        case 3:
                            $status = 'On-going';
                            break;
                    }

                    $sheet->cell('I'.$row, $status);
                    $sheet->cell('J'.$row, $this->com->convertDate($wbs->received_date,'m/d/Y h:i A'));
                    $row++;
                    $count++;
                }
                
                $sheet->cells('A'.$row.':J'.$row, function($cells) {
                    $cells->setBorder('thick', 'thick', 'thick', 'thick');
                });
            });
        })->download('xls');
    }
}
