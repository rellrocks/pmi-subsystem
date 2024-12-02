<?php
/*******************************************************************************
     Copyright (c) <Company Name> All rights reserved.

     FILE NAME: WBSIqcController.php
     MODULE NAME:  3006 : WBS - IQC Inspection
     CREATED BY: MESPINOSA
     DATE CREATED: 2016.07.05
     REVISION HISTORY :

     VERSION     ROUND    DATE           PIC          DESCRIPTION
     100-00-01   1     2016.07.05     MESPINOSA       Initial Draft
     200-00-01   1     2016.07.01    AK.DELAROSA      Revision
*******************************************************************************/
?>
<?php

namespace App\Http\Controllers\WBS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth; #Auth facade
use Carbon\Carbon;
use Datatables;
use Config;
use DB;
use PDF;
use App;

/**
* IQC Controller
**/
class WBSIqcController extends Controller
{
    /**
    * IQC constructor.
    **/
    protected $wbs;
    protected $mysql;
    protected $mssql;
    protected $common;

    public function __construct()
    {
        $this->middleware('auth');
        $com = new CommonController;

        if (Auth::user() != null) {
            $this->wbs = $com->userDBcon(Auth::user()->productline,'wbs');
            $this->mysql = $com->userDBcon(Auth::user()->productline,'mysql');
            $this->mssql = $com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $com->userDBcon(Auth::user()->productline,'common');
        } else {
            return redirect('/');
        }
    }
    
    public function getWbsIqc(Request $request_data)
    {
    	$common = new CommonController;
        if(!$common->getAccessRights(Config::get('constants.MODULE_CODE_IQCINS'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {

        	# Render WBS Page.
            return view('wbs.iqc',['userProgramAccess' => $userProgramAccess]);
        }
    }

    public function getLoadwbs(Request $req)
    {
        $from = $req->from;
        $to = $req->to;
        $recno = $req->recno;
        $status = $req->status;
        $itemno = $req->itemno;
        $lotno = $req->lotno;
        $invoice_no = $req->invoice_no;

        if(is_null($from) and is_null($to) or $from == '' and $to == '')
        {
            $receivedate_cond = '';
        }
        else
        {
            $receivedate_cond = " AND i.received_date BETWEEN '" . $from . "' AND '" . $to . "'";
        }

        if($itemno == '')
        {
            $item_cond ='';
        }
        else
        {
            $item_cond = " AND i.item = '" . $itemno . "'";
        }

        if($status == '')
        {
            $status_cond = '';
        }
        else
        {
            switch ($status) {
                case '0':
                    $status_cond = " AND (i.iqc_status = 0 AND i.judgement is null)";
                    break;
                case 'Accepted':
                    $status_cond = " AND (i.iqc_status = 1 AND i.judgement = '" . $status . "')";
                    break;
                case 'Rejected':
                    $status_cond = " AND ((i.iqc_status = 2 OR i.iqc_status = 1) AND i.judgement = '" . $status . "')";
                    break;
                case '3':
                    $status_cond = " AND (i.iqc_status = 3)";
                    break;
                case 'Special Accept':
                    $status_cond = " AND (i.iqc_status = 4 OR i.judgement = '" . $status . "')";
                    break;
                case 'RTV':
                    $status_cond = " AND (i.judgement = '" . $status . "')"; //i.iqc_status = 2 AND 
                    break;
                case 'Sorted':
                    $status_cond = " AND ( i.judgement = '" . $status . "')"; //i.iqc_status = 1 OR
                    break;
                case 'Reworked':
                    $status_cond = " AND (i.judgement = '" . $status . "')"; //i.iqc_status = 1 OR 
                    break;
                default:
                    $status_cond = " AND (i.iqc_status = '" . $status . "')";
                    break;
            }
            
        }

        if($lotno == '')
        {
            $lotno_cond = '';
        }
        else
        {
            $lotno_cond = " AND i.lot_no = '" . $lotno . "'";
        }

        if($recno == '')
        {
            $recno_cond = '';
        }
        else
        {
            $recno_cond = " AND i.wbs_mr_id = '" . $recno . "'";
        }

        if($invoice_no == '')
        {
            $invoice_no_cond = '';
        }
        else
        {
            $invoice_no_cond = " AND i.invoice_no = '" . $invoice_no . "'";
        }

        try {
            $search = $req->search['value'];

            $totalData = DB::connection($this->wbs)->table('tbl_wbs_inventory as i')
                            ->leftJoin('tbl_wbs_material_receiving_batch as b','i.mat_batch_id','=','b.id')
                            ->leftJoin('tbl_wbs_local_receiving_batch as l','i.loc_batch_id','=','l.id')
                            ->whereRaw(DB::raw("i.not_for_iqc in (0) ".$receivedate_cond.$item_cond.$status_cond.$lotno_cond.$recno_cond.$invoice_no_cond))
                            ->orderBy('i.created_at','desc')
                            ->select([
                                    DB::raw('i.id as id'),
                                    DB::raw('i.item as item'),
                                    DB::raw('i.item_desc as item_desc'),
                                    DB::raw('i.supplier as supplier'),
                                    DB::raw("IFNULL(b.qty,(SELECT b.qty FROM tbl_wbs_local_receiving_batch as b
                                                            where b.id = i.loc_batch_id)) as qty"),
                                    DB::raw('i.lot_no as lot_no'),
                                    DB::raw('i.drawing_num as drawing_num'),
                                    DB::raw('i.wbs_mr_id as wbs_mr_id'),
                                    DB::raw('i.invoice_no as invoice_no'),
                                    DB::raw('i.iqc_result as iqc_result'),
                                    DB::raw('i.updated_at as updated_at'),
                                    DB::raw('i.update_user as update_user'),
                                    DB::raw('i.iqc_status as iqc_status'),
                                    DB::raw('i.ins_date as ins_date'),
                                    DB::raw('i.ins_time as ins_time'),
                                    DB::raw('i.ins_by as ins_by'),
                                    DB::raw('i.app_date as app_date'),
                                    DB::raw('i.app_time as app_time'),
                                    DB::raw('i.app_by as app_by'),
                                    DB::raw('i.judgement as judgement'),
                                    DB::raw('i.created_at as created_at')
                                ])->count();

            //$totalData = count($sql_data);

            $columns = [
                0 => 'id',
                1 => 'action',
                2 => 'iqc_status',
                3 => 'item',
                4 => 'item_desc',
                5 => 'supplier',
                6 => 'qty',
                7 => 'lot_no',
                8 => 'drawing_num',
                9 => 'wbs_mr_id',
                10 => 'invoice_no',
                11 => 'app_by',
                12 => 'app_date',
                13 => 'ins_by',
                14 => 'ins_date',
                15 => 'iqc_result',
                16 => 'updated_at'
            ];

            $totalFiltered = $totalData;

            $limit = $req->length;
            $start = $req->start;
            $order = $columns[$req->input('order.0.column')];
            $dir = $req->input('order.0.dir');

            if ($receivedate_cond !== '' || $item_cond !== '' || $lotno_cond !== '' || $recno_cond !== '' || $invoice_no_cond !== '') {
                $search = '';
            }

            if (empty($search)) {
                $sql_data = DB::connection($this->wbs)->table('tbl_wbs_inventory as i')
                                ->select([
                                    DB::raw('i.id as id'),
                                    DB::raw('i.item as item'),
                                    DB::raw('i.item_desc as item_desc'),
                                    DB::raw('i.supplier as supplier'),
                                    DB::raw("IFNULL(b.qty,(SELECT b.qty FROM tbl_wbs_local_receiving_batch as b
                                                            where b.id = i.loc_batch_id)) as qty"),
                                    DB::raw('i.lot_no as lot_no'),
                                    DB::raw('i.drawing_num as drawing_num'),
                                    DB::raw('i.wbs_mr_id as wbs_mr_id'),
                                    DB::raw('i.invoice_no as invoice_no'),
                                    DB::raw('i.iqc_result as iqc_result'),
                                    DB::raw('i.updated_at as updated_at'),
                                    DB::raw('i.update_user as update_user'),
                                    DB::raw('i.iqc_status as iqc_status'),
                                    DB::raw('i.ins_date as ins_date'),
                                    DB::raw('i.ins_time as ins_time'),
                                    DB::raw('i.ins_by as ins_by'),
                                    DB::raw('i.app_date as app_date'),
                                    DB::raw('i.app_time as app_time'),
                                    DB::raw('i.judgement as judgement'),
                                    DB::raw('i.app_by as app_by'),
                                ])
                                ->leftJoin('tbl_wbs_material_receiving_batch as b','i.mat_batch_id','=','b.id')
                                ->leftJoin('tbl_wbs_local_receiving_batch as l','i.loc_batch_id','=','l.id')
                                ->whereRaw(DB::raw("i.not_for_iqc in (0) ".$receivedate_cond.$item_cond.$status_cond.$lotno_cond.$recno_cond.$invoice_no_cond))
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();
            } else {
                $sql_data = DB::connection($this->wbs)
                                ->select("select i.id as id,
                                                i.item as item,
                                                i.item_desc as item_desc,
                                                i.supplier as supplier,
                                                IFNULL(b.qty,(SELECT b.qty FROM tbl_wbs_local_receiving_batch as b where b.id = i.loc_batch_id)) as qty,
                                                i.lot_no as lot_no,
                                                i.drawing_num as drawing_num,
                                                i.wbs_mr_id as wbs_mr_id,
                                                i.invoice_no as invoice_no,
                                                i.iqc_result as iqc_result,
                                                i.updated_at as updated_at,
                                                i.update_user as update_user,
                                                i.iqc_status as iqc_status,
                                                i.ins_date as ins_date,
                                                i.ins_time as ins_time,
                                                i.ins_by as ins_by,
                                                i.app_date as app_date,
                                                i.app_time as app_time,
                                                i.judgement as judgement,
                                                i.app_by as app_by
                                        from tbl_wbs_inventory as i
                                        LEFT JOIN tbl_wbs_material_receiving_batch as b
                                        ON i.mat_batch_id = b.id
                                        LEFT JOIN tbl_wbs_local_receiving_batch as l
                                        ON i.loc_batch_id = l.id
                                        WHERE i.not_for_iqc in (0) ".$status_cond."
                                        AND (i.item LIKE '%".$search."%'
                                        OR i.item_desc LIKE '%".$search."%'
                                        OR i.supplier LIKE '%".$search."%'
                                        OR i.lot_no LIKE '%".$search."%'
                                        OR i.drawing_num LIKE '%".$search."%'
                                        OR i.wbs_mr_id LIKE '%".$search."%'
                                        OR i.invoice_no LIKE '%".$search."%'
                                        OR i.app_by LIKE '%".$search."%'
                                        OR i.app_date LIKE '%".$search."%'
                                        OR i.ins_by LIKE '%".$search."%'
                                        OR i.ins_date LIKE '%".$search."%'
                                        OR i.iqc_result LIKE '%".$search."%'
                                        OR i.updated_at LIKE '%".$search."%')
                                        ORDER BY i.".$order." ".$dir."
                                        LIMIT ".$limit." OFFSET ".$start);
                // $sql_data = DB::connection($this->wbs)->table('tbl_wbs_inventory as i')
                //                 ->select([
                //                     DB::raw('i.id as id'),
                //                     DB::raw('i.item as item'),
                //                     DB::raw('i.item_desc as item_desc'),
                //                     DB::raw('i.supplier as supplier'),
                //                     DB::raw("IFNULL(b.qty,(SELECT b.qty FROM tbl_wbs_local_receiving_batch as b
                //                                             where b.id = i.loc_batch_id)) as qty"),
                //                     DB::raw('i.lot_no as lot_no'),
                //                     DB::raw('i.drawing_num as drawing_num'),
                //                     DB::raw('i.wbs_mr_id as wbs_mr_id'),
                //                     DB::raw('i.invoice_no as invoice_no'),
                //                     DB::raw('i.iqc_result as iqc_result'),
                //                     DB::raw('i.updated_at as updated_at'),
                //                     DB::raw('i.update_user as update_user'),
                //                     DB::raw('i.iqc_status as iqc_status'),
                //                     DB::raw('i.ins_date as ins_date'),
                //                     DB::raw('i.ins_time as ins_time'),
                //                     DB::raw('i.ins_by as ins_by'),
                //                     DB::raw('i.app_date as app_date'),
                //                     DB::raw('i.app_time as app_time'),
                //                     DB::raw('i.judgement as judgement'),
                //                     DB::raw('i.app_by as app_by'),
                //                 ])
                //                 ->leftJoin('tbl_wbs_material_receiving_batch as b','i.mat_batch_id','=','b.id')
                //                 ->leftJoin('tbl_wbs_local_receiving_batch as l','i.loc_batch_id','=','l.id')
                //                 ->whereRaw(DB::raw("i.not_for_iqc = 0 ".$status_cond))
                //                 // ->orWhere('i.iqc_status','LIKE','%'.$req->status.'%')
                //                 ->orWhere('i.item','LIKE','%'.$search.'%')
                //                 ->orWhere('i.item_desc','LIKE','%'.$search.'%')
                //                 ->orWhere('i.supplier','LIKE','%'.$search.'%')
                //                 ->orWhere('i.lot_no','LIKE','%'.$search.'%')
                //                 ->orWhere('i.drawing_num','LIKE','%'.$search.'%')
                //                 ->orWhere('i.wbs_mr_id','LIKE','%'.$search.'%')
                //                 ->orWhere('i.invoice_no','LIKE','%'.$search.'%')
                //                 ->orWhere('i.app_by','LIKE','%'.$search.'%')
                //                 ->orWhere('i.app_date','LIKE','%'.$search.'%')
                //                 ->orWhere('i.ins_by','LIKE','%'.$search.'%')
                //                 ->orWhere('i.ins_date','LIKE','%'.$search.'%')
                //                 ->orWhere('i.iqc_result','LIKE','%'.$search.'%')
                //                 ->orWhere('i.updated_at','LIKE','%'.$search.'%')
                //                 ->offset($start)
                //                 ->limit($limit)
                //                 ->orderBy($order, $dir)
                //                 ->get();
                $totalFiltered = count($sql_data);
                // $totalFiltered = DB::connection($this->wbs)->table('tbl_wbs_inventory as i')
                //                     ->leftJoin('tbl_wbs_material_receiving_batch as b','i.mat_batch_id','=','b.id')
                //                     ->leftJoin('tbl_wbs_local_receiving_batch as l','i.loc_batch_id','=','l.id')
                //                     ->whereRaw(DB::raw("i.not_for_iqc = 0 ".$status_cond))
                //                     // ->orWhere('i.iqc_status','LIKE','%'.$req->status.'%')
                //                     ->orWhere('i.item','LIKE','%'.$search.'%')
                //                     ->orWhere('i.item_desc','LIKE','%'.$search.'%')
                //                     ->orWhere('i.supplier','LIKE','%'.$search.'%')
                //                     ->orWhere('i.lot_no','LIKE','%'.$search.'%')
                //                     ->orWhere('i.drawing_num','LIKE','%'.$search.'%')
                //                     ->orWhere('i.wbs_mr_id','LIKE','%'.$search.'%')
                //                     ->orWhere('i.invoice_no','LIKE','%'.$search.'%')
                //                     ->orWhere('i.app_by','LIKE','%'.$search.'%')
                //                     ->orWhere('i.app_date','LIKE','%'.$search.'%')
                //                     ->orWhere('i.ins_by','LIKE','%'.$search.'%')
                //                     ->orWhere('i.ins_date','LIKE','%'.$search.'%')
                //                     ->orWhere('i.iqc_result','LIKE','%'.$search.'%')
                //                     ->orWhere('i.updated_at','LIKE','%'.$search.'%')
                //                     ->count();
            }

            $data = [];

            if (!empty($sql_data)) {
                $data = $sql_data;
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

        // $iqc = DB::connection($this->wbs)->table('tbl_wbs_inventory as i')
        //             ->leftJoin('tbl_wbs_material_receiving_batch as b','i.mat_batch_id','=','b.id')
        //             ->leftJoin('tbl_wbs_local_receiving_batch as l','i.loc_batch_id','=','l.id')
        //             ->where('i.iqc_status',$req->status)
        //             ->where('i.not_for_iqc',0)
        //             // ->where('b.qty','>',0)
        //             ->orderBy('i.created_at','desc')
        //             ->select([
        //                     DB::raw('i.id as id'),
        //                     DB::raw('i.item as item'),
        //                     DB::raw('i.item_desc as item_desc'),
        //                     DB::raw('i.supplier as supplier'),
        //                     DB::raw("IFNULL(b.qty,(SELECT b.qty FROM tbl_wbs_local_receiving_batch as b
        //                                             where b.id = i.loc_batch_id)) as qty"),
        //                     DB::raw('i.lot_no as lot_no'),
        //                     DB::raw('i.drawing_num as drawing_num'),
        //                     DB::raw('i.wbs_mr_id as wbs_mr_id'),
        //                     DB::raw('i.invoice_no as invoice_no'),
        //                     DB::raw('i.iqc_result as iqc_result'),
        //                     DB::raw('i.updated_at as updated_at'),
        //                     DB::raw('i.update_user as update_user'),
        //                     DB::raw('i.iqc_status as iqc_status'),
        //                     DB::raw('i.ins_date as ins_date'),
        //                     DB::raw('i.ins_time as ins_time'),
        //                     DB::raw('i.ins_by as ins_by'),
        //                     DB::raw('i.app_date as app_date'),
        //                     DB::raw('i.app_time as app_time'),
        //                     DB::raw('i.app_by as app_by'),
        //                 ]);

        // return Datatables::of($iqc)
                        // ->editColumn('id', function ($data) {
                        //     return $data->id;
                        // })
                        // ->editColumn('iqc_status', function ($data) {
                        //     if ($data->iqc_status == 0) {
                        //         return "Pending";
                        //     }

                        //     if ($data->iqc_status == 1) {
                        //         return "Accepted";
                        //     }

                        //     if ($data->iqc_status == 2) {
                        //         return "Rejected";
                        //     }

                        //     if ($data->iqc_status == 3) {
                        //         return "On-going";
                        //     }

                        //     if ($data->iqc_status == 4) {
                        //         return "Special Accept";
                        //     }
                        // })
                        // ->editColumn('app_date', function ($data) {
                        //     return $data->app_date.' '.$data->app_time;
                        // })
                        // ->editColumn('app_by', function ($data) {
                        //     return $data->app_by;
                        // })
                        // ->editColumn('ins_date', function ($data) {
                        //     return $data->ins_date.' '.$data->ins_time;
                        // })
                        // ->editColumn('ins_by', function ($data) {
                        //     return $data->ins_by;
                        // })
                        // ->addColumn('action', function ($data) {
                        //     return '<a href="javascript:;" class="updatesinglebtn btn btn-primary btn-sm input-sm" data-id="'.$data->id.
                        //             '" data-app_date="'.$data->app_date.
                        //             '" data-app_time="'.$data->app_time.'"><i class="fa fa-edit"></i></a>';
                        // })
                        // ->make(true);
    }

    public function postUpdateIQCstatus(Request $req)
    {
        $id = $req->id;
        $for_kit = '';
        $judgement = '';
        switch ($req->statusup) {
            case '1':
                $for_kit = '1';
                $judgement = 'Accepted';
                break;
            case '2':
                $for_kit = '0';
                $judgement = 'Rejected';
                break;
            case '3':
                $for_kit = '0';
                $judgement = 'On-going';
                break;

            case '4':
                $for_kit = '1';
                $judgement = 'Special Accept';
                break;

            default:
                $for_kit = '0';
                break;
        }
        $details = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                        ->where('id',$id)->first();

        $app = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                    ->select(
                            'app_date',
                            'app_time',
                            'wbs_mr_id',
                            'id'
                    )
                    ->where('id',$details->mat_batch_id)->first();

        if (count((array)$app) < 1) {
            $app = DB::connection($this->wbs)->table('tbl_wbs_local_receiving_batch')
                        ->select(
                                'app_date',
                                'app_time',
                                DB::raw('wbs_loc_id as wbs_mr_id'),
                                'id'
                        )
                        ->where('id',$details->loc_batch_id)->first();

            DB::connection($this->wbs)->table('tbl_wbs_local_receiving_batch')
                    ->where('id',$details->loc_batch_id)
                    ->update([
                        'iqc_status' => $req->statusup,
                        'iqc_result' => $req->iqcresup,
                        'for_kitting'=> $for_kit,
                        'judgement' => $judgement,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
        }

        DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
            ->where('id',$details->mat_batch_id)
            ->update([
                'iqc_status' => $req->statusup,
                'iqc_result' => $req->iqcresup,
                'for_kitting'=> $for_kit,
                'judgement' => $judgement,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $update = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->where('id',$id)
                    ->update([
                        'iqc_status' => $req->statusup,
                        'iqc_result' => $req->iqcresup,
                        'for_kitting'=> $for_kit,
                        'judgement' => $judgement,
                        'update_pg' => 'WBS IQC - ' . $judgement,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

        $checkIfExistIQC = DB::connection($this->mysql)->table('iqc_inspections')
                                ->where('inv_id',$req->id)
                                ->where('mr_id',$app->id)
                                ->count();

        if ($checkIfExistIQC > 0) {
            DB::connection($this->mysql)->table('iqc_inspections')
                ->where('inv_id',$req->id)
                ->where('mr_id',$app->id)
                ->update([
                    'time_ins_from' => $req->start_time,
                    'inspector' => $req->inspector,
                    'judgement' => $judgement,
                    'updated_at' => Carbon::now()
                ]);
        } else {
             DB::connection($this->mysql)->table('iqc_inspections')
                ->insert([
                    'invoice_no' => $details->invoice_no,
                    'partcode' => $details->item,
                    'partname' => $details->item_desc,
                    'supplier' => $details->supplier,
                    'app_date' => $app->app_date,
                    'app_time' => $app->app_time,
                    'app_no' => $app->wbs_mr_id,
                    'lot_no' => $details->lot_no,
                    'lot_qty' => $details->qty,
                    'time_ins_from' => $req->start_time,
                    'inspector' => $req->inspector,
                    'judgement' => $judgement,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'inv_id' => $id,
                    'mr_id' => $app->id
                ]);
        }

        if($update) {
            return response()->json(array('status'=>1),200);
        } else {
            return response()->json(array('status'=>0),200);
        }
    }

    public function postUpdateIQCstatusBulk(Request $req)
    {
        /*
            This Controller Should no Annealing Process, CN Only
        */
        $success = false;
        try {
            // DB::connection($this->mysql)->beginTransaction();
            // DB::connection($this->wbs)->beginTransaction();
            $statusup = $req->statusup;
            $inspector = $req->inspector;
            $start_time = $req->start_time;
            $iqcresup = $req->iqcresup;
            $time_ins_from = $req->start_time;
            $user_id = Auth::user()->user_id;

            $x = 0;
            $req_array = [];
            $grp_item = [];

            $for_kit = '';
            $judgement = '';
            $isFrom_MR = '';

            switch ($statusup) {
                case '1':
                    $for_kit = '1';
                    $judgement = 'Accepted';
                    break;
                case '2':
                    $for_kit = '0';
                    $judgement = 'Rejected';
                    break;
                case '3':
                    $for_kit = '0';
                    $judgement = 'On-going';
                    break;
    
                case '4':
                    $for_kit = '1';
                    $judgement = 'Special Accept';
                    break;
    
                default:
                    $for_kit = '0';
                    break;
            }

            foreach ($req->id as $key => $id) {
                array_push($req_array,[
                    'inv_id' => $id,
                    'item' => $req->item[$x]
                ]);
                $x++;
            }

            foreach ($req_array as $key) {
                $arr = array_filter($grp_item, function ($r) use ($key) {
                    return $r['item'] == $key['item'];
                });
                if (!empty($arr)) {

                }else {
                    $filter_ = array_filter($req_array, function ($r) use ($key) {
                        return $r['item'] == $key['item'];
                    });

                    $child_item = [];
                    foreach ($filter_ as $j) {
                        array_push($child_item,[
                            'inv_id' => $j['inv_id']
                        ]);
                    }
                    $grp_item[] = [
                        'item' => $key['item'],
                        'child_item' => $child_item,
                    ];
                }
            }

            $idcount = count($req_array);
            $loc_recId = 0;
            $mac_recId = 0;
            $cnt = 0;

            foreach ($grp_item as $i) {
                $lot_qty = 0;
                $iqc_lot_no = [];
                $_item = $i['item'];
                $child_item = $i['child_item'];
                $lot_no = [];
                $inv_id_arr = [];
                $mr_id = [];
                foreach ($child_item as $j) {
                    $inv_id = $j['inv_id'];
                    $iqc = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                        ->where('id',$inv_id)->first();
                    $invoice_no = $iqc->invoice_no;
                    $partcode = $iqc->item;
                    $partname = $iqc->item_desc;
                    $supplier = $iqc->supplier;

                    $app = DB::connection($this->wbs)->table('tbl_wbs_material_receiving')
                        ->where('invoice_no',$invoice_no)->first();
                    //Local Receiving - ID
                    if (count((array)$app) < 1) {
                        $app = DB::connection($this->wbs)->table('tbl_wbs_local_receiving')
                                ->where('invoice_no',$invoice_no)->first();
                        $ress = DB::connection($this->wbs)->table('tbl_wbs_local_receiving_batch')
                            ->where('id',$iqc->loc_batch_id)
                            ->update([
                                'iqc_status' => $statusup,
                                'iqc_result' => $iqcresup,
                                'for_kitting'=> $for_kit,
                                'judgement' => $judgement,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        $loc_recId = $iqc->loc_batch_id;
                    }else {
                    //Material Receiving - ID
                        DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                        ->where('id',$iqc->mat_batch_id)
                        ->update([
                            'iqc_status' => $statusup,
                            'iqc_result' => $iqcresup,
                            'for_kitting'=> $for_kit,
                            'judgement' => $judgement,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $mac_recId = $iqc->mat_batch_id;
                    }

                    $update = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->where('id',$inv_id)
                    ->update([
                        'iqc_status' => $statusup,
                        'iqc_result' => $iqcresup,
                        'for_kitting'=> $for_kit,
                        'judgement' => $judgement,
                        'update_pg' => 'WBS IQC - ' . $judgement,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    array_push($inv_id_arr,$inv_id);
                    array_push($mr_id,($loc_recId == 0 ? $mac_recId : $loc_recId));
                    array_push($lot_no, $iqc->lot_no);
                    array_push($iqc_lot_no,[
                        'inv_id' => $inv_id,
                        'lot_no' => $iqc->lot_no,
                        'qty' => $iqc->qty,
                        'mr_source' => ($loc_recId == 0 ? 'MR' : 'LR'),
                        'mr_id' => ($loc_recId == 0 ? $mac_recId : $loc_recId)
                    ]);

                    $lot_qty = intval($lot_qty) + intval($iqc->qty);
                    $app_date = $app->app_date;
                    $app_time = $app->app_time;
                    $app_no = $app->receive_no;
                    $cnt++;
                }
                //SPLICE LOT NO and INV ID
                $lot = implode(',', $lot_no);
                $inv_id_arr = implode(',', $inv_id_arr);
                $mrid = implode(',', $mr_id);

                $params = [
                    'invoice_no' => $invoice_no,
                    'partcode' => $partcode,
                    'partname' => $partname,
                    'supplier' => $supplier,
                    'app_date' => $app_date,
                    'app_time' => $app_time,
                    'app_no' => $app_no,
                    'lot_no' => $lot,
                    'lot_qty' => $lot_qty,
                    'time_ins_from' => $time_ins_from,
                    'inspector' => $inspector,
                    'judgement' => $judgement,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'inv_id' => $inv_id_arr,
                    'mr_id' => $mrid,
                ];
                //INSERT iqc_inspections
                $iqc_id = DB::connection($this->mysql)->table('iqc_inspections')->insertGetId($params);
                //INSERT iqc_lot_no
                foreach ($iqc_lot_no as $key) {
                    DB::connection($this->mysql)->table('iqc_lot_no')
                    ->insert([
                        'iqc_id' => $iqc_id,
                        'inv_id' =>  $key['inv_id'],
                        'mr_id' => $key['mr_id'],
                        'mr_source' => $key['mr_source'],
                        'qty' => floatval($key['qty']),
                        'lot_no' => $key['lot_no'],
                        'invoice_no' => $invoice_no,
                        'item_no' => $partcode,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'create_user' => $user_id,
                        'update_user' => $user_id,
                        'is_deleted' => 0
                    ]);
                }
            }
            $success = true;
        } catch (\Exception $th) {
            $success = false;
        }

        if($success && ($cnt == $idcount)){
            // DB::connection($this->mysql)->commit();
            // DB::connection($this->wbs)->commit();
            return response()->json(array('status'=>1),200);
        }else {
            // DB::connection($this->mysql)->rollBack();
            // DB::connection($this->wbs)->rollBack();
            return response()->json(array('status'=>0),200);
        }
    }

    public function postUpdateIQCstatusBulk_old(Request $req)
    {
        $cnt = 0;
        $idcount = count($req->id);
        $for_kit = '';
        $judgement = '';
        switch ($req->statusup) {
            case '1':
                $for_kit = '1';
                $judgement = 'Accepted';
                break;
            case '2':
                $for_kit = '0';
                $judgement = 'Rejected';
                break;
            case '3':
                $for_kit = '0';
                $judgement = 'On-going';
                break;

            case '4':
                $for_kit = '1';
                $judgement = 'Special Accept';
                break;

            default:
                $for_kit = '0';
                break;
        }

        $invoice_no = '';
        $partcode = '';
        $partname = '';
        $supplier = '';
        $app_date = '';
        $app_time = '';
        $app_no = '';
        $lot_qty = 0;
        $time_ins_from = '';
        $inspector = '';
        $lot = '';
        $invid = '';
        $mrid = '';

        $mrbatchid = 0;

        $same_item = false;

        $params = [];
        $lot_no = [];
        $inv_id = [];
        $mr_id = [];
        $lot_qty_arr = [];
        $mr_source_arr = [];

        if (count(array_unique($req->item)) === 1) {
             $same_item = true;
        }

        if ($same_item == true) {
            foreach ($req->id as $key => $id) {
                $iqc = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                        ->where('id',$id)->first();

                $app = DB::connection($this->wbs)->table('tbl_wbs_material_receiving')
                        ->where('invoice_no',$iqc->invoice_no)->first();

                if (count((array)$app) < 1) {
                    $app = DB::connection($this->wbs)->table('tbl_wbs_local_receiving')
                                ->where('invoice_no',$iqc->invoice_no)->first();

                    DB::connection($this->wbs)->table('tbl_wbs_local_receiving_batch')
                        ->where('id',$iqc->loc_batch_id)
                        ->update([
                            'iqc_status' => $req->statusup,
                            'iqc_result' => $req->iqcresup,
                            'for_kitting'=> $for_kit,
                            'judgement' => $judgement,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    
                    $mrbatchid = $iqc->loc_batch_id;
                } else {
                    DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                        ->where('id',$iqc->mat_batch_id)
                        ->update([
                            'iqc_status' => $req->statusup,
                            'iqc_result' => $req->iqcresup,
                            'for_kitting'=> $for_kit,
                            'judgement' => $judgement,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    $mrbatchid = $iqc->mat_batch_id;
                }

                array_push($inv_id,$id);
                array_push($mr_id,$mrbatchid);
                array_push($lot_no, $iqc->lot_no);
                array_push($lot_qty_arr, $iqc->qty);
                array_push($mr_source_arr, $id);

                $invoice_no = $iqc->invoice_no;
                $partcode = $iqc->item;
                $partname = $iqc->item_desc;
                $supplier = $iqc->supplier;
                $app_date = $app->app_date;
                $app_time = $app->app_time;
                $app_no = $app->receive_no;
                $lot_qty = intval($lot_qty) + intval($iqc->qty);
                $time_ins_from = $req->start_time;
                $inspector = $req->inspector;

                DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->where('id',$id)
                    ->update([
                        'iqc_status' => $req->statusup,
                        'iqc_result' => $req->iqcresup,
                        'for_kitting'=> $for_kit,
                        'judgement' => $judgement,
                        'update_pg' => 'WBS IQC BULK - ' . $judgement,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $cnt++;
            }

            $lot = implode(',', $lot_no);
            $invid = implode(',', $inv_id);
            $mrid = implode(',', $mr_id);

            $params = [
                'invoice_no' => $invoice_no,
                'partcode' => $partcode,
                'partname' => $partname,
                'supplier' => $supplier,
                'app_date' => $app_date,
                'app_time' => $app_time,
                'app_no' => $app_no,
                'lot_no' => $lot,
                'lot_qty' => $lot_qty,
                'time_ins_from' => $time_ins_from,
                'inspector' => $inspector,
                'judgement' => $judgement,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'inv_id' => $invid,
                'mr_id' => $mrid
            ];

            $query = DB::connection($this->mysql)->table('iqc_inspections')->insertGetId($params);

            $mr_source = '';
            for($i = 0; $i < count($lot_no); $i++){
                if($mr_source_arr[$i] == null || $mrbatchid == ''){
                    $mr_source = 'LR';
                }else{
                    $mr_source = 'MR';
                }
                DB::connection($this->mysql)->table('iqc_lot_no')
                    ->insert([
                        'iqc_id' => $query,
                        'inv_id' =>  $inv_id[$i],
                        'mr_id' => $mr_id[$i],
                        'mr_source' => $mr_source,
                        'qty' => floatval($lot_qty_arr[$i]),
                        'lot_no' => $lot_no[$i],
                        'invoice_no' => $invoice_no,
                        'item_no' => $partcode,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'create_user' => Auth::user()->user_id,
                        'update_user' => Auth::user()->user_id,
                        'is_deleted' => 0
                ]);
            }
        } else {
            foreach ($req->id as $key => $id) {
                $details = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                            ->where('id',$id)->first();

                $app = DB::connection($this->wbs)->table('tbl_wbs_material_receiving')
                                ->where('invoice_no',$details->invoice_no)->first();

                if (count((array)$app) < 1) {
                    $app = DB::connection($this->wbs)->table('tbl_wbs_local_receiving')
                                ->where('invoice_no',$details->invoice_no)->first();

                    DB::connection($this->wbs)->table('tbl_wbs_local_receiving_batch')
                        ->where('id',$details->loc_batch_id)
                        ->update([
                            'iqc_status' => $req->statusup,
                            'iqc_result' => $req->iqcresup,
                            'for_kitting'=> $for_kit,
                            'judgement' => $judgement,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    $mrbatchid = $details->loc_batch_id;
                } else {
                    DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                        ->where('id',$details->mat_batch_id)
                        ->update([
                            'iqc_status' => $req->statusup,
                            'iqc_result' => $req->iqcresup,
                            'for_kitting'=> $for_kit,
                            'judgement' => $judgement,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    $mrbatchid = $details->mat_batch_id;
                }

                DB::connection($this->mysql)->table('iqc_inspections')
                    ->insert([
                        'invoice_no' => $details->invoice_no,
                        'partcode' => $details->item,
                        'partname' => $details->item_desc,
                        'supplier' => $details->supplier,
                        'app_date' => $app->app_date,
                        'app_time' => $app->app_time,
                        'app_no' => $app->receive_no,
                        'lot_no' => $details->lot_no,
                        'lot_qty' => $details->qty,
                        'time_ins_from' => $req->start_time,
                        'inspector' => $req->inspector,
                        'judgement' => $judgement,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'inv_id' => $id,
                        'mr_id' => $mrbatchid
                    ]);

                DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->where('id',$id)
                    ->update([
                        'iqc_status' => $req->statusup,
                        'iqc_result' => $req->iqcresup,
                        'for_kitting'=> $for_kit,
                        'judgement' => $judgement,
                        'update_pg' => 'WBS IQC - BULK ' . $judgement,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $cnt++;
            }
        }

        if($cnt == $idcount) {
            return response()->json(array('status'=>1),200);
        } else {
            return response()->json(array('status'=>0),200);
        }
    }

    public function getSearch(Request $req)
    {
        $from = $req->from;
        $to = $req->to;
        $recno = $req->recno;
        $status = $req->status;
        $itemno = $req->itemno;
        $lotno = $req->lotno;
        $invoice_no = $req->invoice_no;

        if(is_null($from) and is_null($to) or $from == '' and $to == '')
        {
            $receivedate_cond = '';
        }
        else
        {
            $receivedate_cond = " AND i.received_date BETWEEN '" . $from . "' AND '" . $to . "'";
        }

        if($itemno == '')
        {
            $item_cond ='';
        }
        else
        {
            $item_cond = " AND i.item = '" . $itemno . "'";
        }

        if($status == '')
        {
            $status_cond = '';
        }
        else
        {
            $status_cond = " AND i.iqc_status = '" . $status . "'";
        }

        if($lotno == '')
        {
            $lotno_cond = '';
        }
        else
        {
            $lotno_cond = " AND i.lot_no = '" . $lotno . "'";
        }

        if($recno == '')
        {
            $recno_cond = '';
        }
        else
        {
            $recno_cond = " AND i.wbs_mr_id = '" . $recno . "'";
        }

        if($invoice_no == '')
        {
            $invoice_no_cond = '';
        }
        else
        {
            $invoice_no_cond = " AND i.invoice_no = '" . $invoice_no . "'";
        }

        $iqc = DB::connection($this->wbs)->table('tbl_wbs_inventory as i')
                    ->leftJoin('tbl_wbs_material_receiving_batch as b','i.mat_batch_id','=','b.id')
                    ->leftJoin('tbl_wbs_local_receiving_batch as l','i.loc_batch_id','=','l.id')
                    ->whereRaw("1=1"
                            . $receivedate_cond
                            . $item_cond
                            . $status_cond
                            . $lotno_cond
                            . $recno_cond
                            . $invoice_no_cond)
                    ->orderBy('i.created_at','desc')
                    ->select([
                            DB::raw('i.id as id'),
                            DB::raw('i.item as item'),
                            DB::raw('i.item_desc as item_desc'),
                            DB::raw('i.supplier as supplier'),
                            DB::raw("IFNULL(b.qty,(SELECT b.qty FROM tbl_wbs_local_receiving_batch as b
                                                    where b.id = i.loc_batch_id)) as qty"),
                            DB::raw('i.box as box'),
                            DB::raw('i.lot_no as lot_no'),
                            DB::raw('i.drawing_num as drawing_num'),
                            DB::raw('i.wbs_mr_id as wbs_mr_id'),
                            DB::raw('i.invoice_no as invoice_no'),
                            DB::raw('i.iqc_result as iqc_result'),
                            DB::raw('i.updated_at as updated_at'),
                            DB::raw('i.update_user as update_user'),
                            DB::raw('i.iqc_status as iqc_status'),
                            DB::raw('i.ins_date as ins_date'),
                            DB::raw('i.ins_time as ins_time'),
                            DB::raw('i.ins_by as ins_by'),
                            DB::raw('i.app_date as app_date'),
                            DB::raw('i.app_time as app_time'),
                            DB::raw('i.app_by as app_by'),
                        ])
                    ->groupBy(
                            'i.item',
                            'i.item_desc',
                            'i.supplier',
                            'i.lot_no',
                            'i.box',
                            'i.drawing_num',
                            'i.wbs_mr_id',
                            'i.invoice_no',
                            'i.iqc_result',
                            'i.updated_at',
                            'i.update_user',
                            'i.iqc_status',
                            'i.ins_date',
                            'i.ins_time',
                            'i.ins_by',
                            'i.app_date',
                            'i.app_time',
                            'i.app_by'
                        );

        return Datatables::of($iqc)
                        ->editColumn('id', function ($data) {
                            return $data->id;
                        })
                        ->editColumn('iqc_status', function ($data) {
                            if ($data->iqc_status == 0) {
                                return "Pending";
                            }

                            if ($data->iqc_status == 1) {
                                return "Accepted";
                            }

                            if ($data->iqc_status == 2) {
                                return "Rejected";
                            }

                            if ($data->iqc_status == 3) {
                                return "On-going";
                            }

                            if ($data->iqc_status == 4) {
                                return "Special Accept";
                            }
                        })
                        ->addColumn('action', function ($data) {
                            return '<a href="javascript:;" class="updatesinglebtn btn btn-primary btn-sm input-sm" data-id="'.$data->id.
                                    '" data-app_date="'.$data->app_date.
                                    '" data-app_time="'.$data->app_time.'"><i class="fa fa-edit"></i></a>';
                        })
                        ->make(true);
    }

    private function checkIfExistObject($object)
    {
       return count( (array)$object);
    }
}