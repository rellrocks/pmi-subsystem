<?php
namespace App\Http\Controllers\QCDB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Auth; #Auth facade
use Dompdf\Dompdf;
use Carbon\Carbon;
use PDF;
use App\IQCInspection;
use Illuminate\Http\Request;
use App\Http\Requests;
use Excel;
use Event;
use App\Events\UpdateIQCInspection;
use Illuminate\Support\Str;
use App\Events\NotifyReceiving;

class IQCInspectionController extends Controller
{
    protected $mysql;
    protected $mssql;
    protected $common;
    protected $com;
    protected $wbs;

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

    public function getIQCInspection(Request $request)
    {
        if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_IQCDB'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {

            return view('qcdb.iqcinspection',[
                        'userProgramAccess' => $userProgramAccess]);
        }
    }

    public function getInvoiceItems(Request $req)
    {
        $results = [];
        $val = (!isset($req->q))? "" : $req->q;
        $id = (!isset($req->id))? "" : $req->id;
        $text = (!isset($req->text))? "" : $req->text;
        $table = (!isset($req->table))? "" : $req->table;
        $condition = (!isset($req->condition))? "" : $req->condition;
        $isDistinct = (!isset($req->isDistinct))? "" : $req->isDistinct;
        $display = (!isset($req->display))? "" : $req->display;
        $addOptionVal = (!isset($req->addOptionVal))? "" : $req->addOptionVal;
        $addOptionText = (!isset($req->addOptionText))? "" : $req->addOptionText;
        $sql_query = (!isset($req->sql_query))? "" : $req->sql_query;
        $orderBy = (!isset($req->orderBy))? "" : $req->orderBy;

        try {
            if ($addOptionVal != "" && $display == "id&text") {
                array_push($results, [
                    'id' => $addOptionVal,
                    'text' => $addOptionText
                ]);
            }

            if ($sql_query == null || $sql_query == "") {
                   $sql_query = "SELECT DISTINCT m.item as id, m.item as `text`
                                FROM tbl_wbs_material_receiving_batch as m
                                WHERE m.not_for_iqc = 0
                                AND m.invoice_no = '".$req->invoiceno."'
                                UNION
                                SELECT DISTINCT l.item as id, l.item as `text`
                                FROM tbl_wbs_local_receiving_batch as l
                                WHERE l.not_for_iqc = 0
                                AND l.invoice_no = '".$req->invoiceno."'"; 
                                //AND (l.judgement is null OR l.judgement = '' OR l.judgement = 'On-going')
            
            }
            
            $db = DB::connection($this->wbs)->select($sql_query);

            foreach ($db as $key => $d) {
                array_push($results, [
                    'id' => $d->id,
                    'text' => $d->text
                ]);
            }

        } catch(\Exception $e) {
            return [
                'success' => false,
                'msessage' => $e->getMessage()
            ];
        }
        
        return $results;
    }

    public function getInvoiceItemLotNo(Request $req)
    {
        $results = [];
        $val = (!isset($req->q))? "" : $req->q;
        $id = (!isset($req->id))? "" : $req->id;
        $text = (!isset($req->text))? "" : $req->text;
        $table = (!isset($req->table))? "" : $req->table;
        $condition = (!isset($req->condition))? "" : $req->condition;
        $isDistinct = (!isset($req->isDistinct))? "" : $req->isDistinct;
        $display = (!isset($req->display))? "" : $req->display;
        $addOptionVal = (!isset($req->addOptionVal))? "" : $req->addOptionVal;
        $addOptionText = (!isset($req->addOptionText))? "" : $req->addOptionText;
        $sql_query = (!isset($req->sql_query))? "" : $req->sql_query;
        $orderBy = (!isset($req->orderBy))? "" : $req->orderBy;
        $invoiceno = (!isset($req->invoiceno))? "" : $req->invoiceno;
        $partcode = (!isset($req->partcode))? "" : $req->partcode;
        $mode = (!isset($req->mode))? "" : $req->mode;
        $iqc_id = (!isset($req->iqc_id))? "" : $req->iqc_id;

        try {
            if ($addOptionVal != "" && $display == "id&text") {
                array_push($results, [
                    'id' => $addOptionVal,
                    'text' => $addOptionText,
                    'mr_id' => '',
                    'inv_id' => '',
                    'qty' => 0
                ]);
            }

            if ($sql_query == null || $sql_query == "") {
                    
            }

            if ($mode == 'disposition') {
                $iqc = DB::connection($this->mysql)->table("iqc_inspections")->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))->where("id", $iqc_id)->select('lot_no')->first();
                $lot = ($iqc->lot_no == null)? "": $iqc->lot_no;
                $arr_lot = explode(',',$lot);

                $iqc_lot_no = "'".implode("','", $arr_lot)."'";


                $sql_query = "select l.lot_no as id,
                                    l.lot_no as `text`,
                                    l.qty as qty,
                                    l.id as mr_id,
                                    (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
                                    'LR' as `source`
                                    from tbl_wbs_local_receiving_batch as l
                                    where l.invoice_no = '" . $invoiceno . "'
                                    and l.item = '" . $partcode . "'
                                    and l.lot_no in(".$iqc_lot_no.")
                                    union
                                    select m.lot_no as id,
                                    m.lot_no as `text`,
                                    m.qty as qty,
                                    m.id as mr_id,
                                    (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id,
                                    'MR' as `source`
                                    from tbl_wbs_material_receiving_batch as m
                                    where m.invoice_no = '" . $invoiceno . "'
                                    and m.item = '" . $partcode . "'
                                    and m.lot_no in(".$iqc_lot_no.")";

                $db = DB::connection($this->wbs)->select($sql_query);
            } else {
                $db = DB::connection($this->wbs)->select($sql_query);
            }

            foreach ($db as $key => $d) {
                array_push($results, [
                    'id' => $d->id,
                    'text' => $d->text,
                    'mr_id' => $d->mr_id,
                    'inv_id' => $d->inv_id,
                    'qty' => $d->qty
                ]);
            }

        } catch(\Exception $e) {
            return [
                'success' => false,
                'msessage' => $e->getMessage()
            ];
        }
        
        return $results;
    }

    public function getInvoiceItemDetails(Request $req)
    {
        // $details = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch as b')
        //         ->join('tbl_wbs_material_receiving as m','m.receive_no','=','b.wbs_mr_id')
        //         ->select('b.item_desc',
        //                 'b.supplier',
        //                 'm.app_time',
        //                 'm.app_date',
        //                 'm.receive_no',
        //                 DB::raw("SUM(qty) as lot_qty"))
        //         ->where('m.invoice_no',$req->invoiceno)
        //         ->where('b.item',$req->item)
        //         ->first();

        $data = [
                'lot' => [],
                'details' => []
            ];

        $iqc_id = $req->iqc_id;

        $details = DB::connection($this->mysql)
                        ->select("SELECT partname as item_desc,
                                        supplier as supplier,
                                        app_time as app_time,
                                        app_date as app_date,
                                        app_no as receive_no,
                                        lot_qty as lot_qty
                                FROM iqc_inspections
                                WHERE id='".$iqc_id."'");

        if (count($details) < 1) {
            $details = DB::connection($this->wbs)
                            ->select("SELECT il.item_desc as item_desc,
                                            il.supplier as supplier,
                                            l.app_time as app_time,
                                            l.app_date as app_date,
                                            l.receive_no as receive_no,
                                            SUM(lb.qty) as lot_qty
                                    FROM tbl_wbs_local_receiving_batch as lb
                                    LEFT JOIN tbl_wbs_local_receiving as l
                                    ON l.receive_no = lb.wbs_loc_id
                                    INNER JOIN tbl_wbs_inventory as il
                                    on il.loc_batch_id = lb.id
                                    WHERE il.invoice_no = '".$req->invoiceno."'
                                    and il.item = '".$req->item."'");
            if (count($details) < 1) {
                $details = DB::connection($this->wbs)
                            ->select("SELECT im.item_desc as item_desc,
                                            im.supplier as supplier,
                                            m.app_time as app_time,
                                            m.app_date as app_date,
                                            m.receive_no as receive_no,
                                            SUM(b.qty) as lot_qty
                                    FROM tbl_wbs_material_receiving_batch as b
                                    INNER JOIN tbl_wbs_material_receiving as m
                                    ON m.receive_no = b.wbs_mr_id
                                    LEFT JOIN tbl_wbs_inventory as im
                                    on im.mat_batch_id = b.id
                                    WHERE im.invoice_no = '".$req->invoiceno."'
                                    and im.item = '".$req->item."'");
            } else {
                if (is_null($details[0]->item_desc)) {
                    $details = DB::connection($this->wbs)
                                ->select("SELECT im.item_desc as item_desc,
                                                im.supplier as supplier,
                                                m.app_time as app_time,
                                                m.app_date as app_date,
                                                m.receive_no as receive_no,
                                                SUM(b.qty) as lot_qty
                                        FROM tbl_wbs_material_receiving_batch as b
                                        INNER JOIN tbl_wbs_material_receiving as m
                                        ON m.receive_no = b.wbs_mr_id
                                        LEFT JOIN tbl_wbs_inventory as im
                                        on im.mat_batch_id = b.id
                                        WHERE im.invoice_no = '".$req->invoiceno."'
                                        and im.item = '".$req->item."'");
                }
            }
        }

        if ($req->modal_mode == 'inspection') {
            $lot = DB::connection($this->wbs)
                    ->select("select l.lot_no as id,
                            l.lot_no as lot_no,
                            l.qty as qty,
                            l.id as mr_id,
                            (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
                            'LR' as `source`
                            from tbl_wbs_local_receiving_batch as l
                            where l.invoice_no = '".$req->invoiceno."'
                            and l.item = '".$req->item."'
                            union
                            select m.lot_no as id,
                            m.lot_no as lot_no,
                            m.qty as qty,
                            m.id as mr_id,
                            (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id,
                            'MR' as `source`
                            from tbl_wbs_material_receiving_batch as m
                            where m.invoice_no = '".$req->invoiceno."'
                            and m.item = '".$req->item."'");
        } else { //on-going
            $lot = DB::connection($this->wbs)
                    ->select("select l.lot_no as id,
                                l.lot_no as lot_no,
                                l.qty as qty,
                                l.id as mr_id,
                                i.id as inv_id,
                                'LR' as `source`,
                                i.iqc_status
                                from tbl_wbs_local_receiving_batch as l
                                join tbl_wbs_inventory as i
                                on i.loc_batch_id = l.id
                                where l.invoice_no = '".$req->invoiceno."'
                                and l.item = '".$req->item."'
                                and i.iqc_status in(3, 0)
                                union
                                select m.lot_no as id,
                                m.lot_no as lot_no,
                                m.qty as qty,
                                m.id as mr_id,
                                i.id as inv_id,
                                'MR' as `source`,
                                i.iqc_status
                                from tbl_wbs_material_receiving_batch as m
                                join tbl_wbs_inventory as i
                                on i.mat_batch_id = m.id
                                where m.invoice_no = '".$req->invoiceno."'
                                and m.item = '".$req->item."'
                                and i.iqc_status in(3, 0)");
        }

        

        return $data = [
                'lot' => $lot,
                'details' => $details
            ];

        // if ($this->checkIfExistObject($db) > 0 && $this->checkIfExistObject($lot) > 0) {
        //     return $data = [
        //         'lot' => $lot,
        //         'details' => $db
        //     ];
        // }
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

    public function saveInspection(Request $req)
    {
        $TEST = $req->all();
        $data = [
            'return_status' => 'failed',
            'msg' => "Saving Failed."
        ];
        $query = false;

        try {
            $lots = $req->lot_no;

            if (is_string($req->lot_no)) {
                $lots = explode(',',$req->lot_no);
                array_pop($lots);
            }

            if ($req->save_status == 'ADD') {
                $this->insertToInspection($req,$lots);
                $this->insertHistory($lots,$req);

                $query = true;

            } else {
                $this->updateInspection($req,$lots);
                $this->insertHistory($lots,$req);

                $query = true;
            }

            if ($query) {
                // Event::fire(new UpdateIQCInspection($this->wbs));
                $data = [
                    'return_status' => 'success',
                    'msg' => "Successfully Saved."
                ];
            }

            return $data;
        }
        catch (\Exception $e) {
            $data = [
                'return_status' => 'error',
                'msg' => $e->getMessage()
            ];

            return $data;
        }
    }

    // private function insertToInspection($req,$lots)
    // {
    //     $test = $req->lot_no_data;
    //     $for_lots;
    //     $lot_nos;
    //     $lot_qty;
    //     $lotQty;
    //     $is_batching = $req->is_batching;
    //     $Arrinv_id;
    //     $Arrmr_id;
    //     $mr_source;

    //     try {
    //         if (is_array($lots)) {
    //             $for_lots = $lots;
    //             $Arrinv_id = explode(',' ,$req->inv_id);
    //             $Arrmr_id = explode(',' ,$req->mr_id);
    //             $lot_qty = explode(',' ,$req->lot_qty);
    //             $lotQty = explode(',' ,$req->lot_qty);
    //             $mr_source = explode(',' ,$req->mr_source);
    //             $lot_nos = implode(',',$lots);
    //         } else {
    //             $for_lots = explode(',',$lots);
    //             $lot_nos = $lots;
    //         }

    //         foreach ($for_lots as $key => $lot) {
    //             $lot_qty = $this->getLotQty($req->invoice_no,$req->partcode,$lot);
    //             $status = 0;
    //             $kitting = 0;

    //             $check_accepted_judgment = ["Sorted", "Reworked", "Accepted", 'Special Accept'];
    //             $check_rejected_judgment = ["RTV", "Rejected"];

    //             if (in_array($req->judgement,$check_accepted_judgment)) {
    //                 $status = 1;
    //                 $kitting = 1;
    //             } 

    //             if (in_array($req->judgement,$check_rejected_judgment)) {
    //                 $status = 2;
    //                 $kitting = 0;
    //             }

    //             $checker = -1;
    //             $MRwhereRaw = "";
    //             $INVwhereRaw = "";
    //             $inv_id = 0;
    //             $mr_id = 0;

    //             if (is_null($req->inv_id) || empty($req->inv_id)) {
    //                 $sql_lot = "select l.lot_no as lot_no,
    //                             l.id as mr_id,
    //                             (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
    //                             'LR' as `source`
    //                             from tbl_wbs_local_receiving_batch as l
    //                             where l.invoice_no = '".$req->invoice_no."'
    //                             and l.item = '".$req->partcode."'
    //                             and l.lot_no = '".$lot."'
    //                             union
    //                             select m.lot_no as lot_no,
    //                             m.id as mr_id,
    //                             (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id,
    //                             'MR' as `source`
    //                             from tbl_wbs_material_receiving_batch as m
    //                             where m.invoice_no = '".$req->invoice_no."'
    //                             and m.item = '".$req->partcode."'
    //                             and m.lot_no = '".$lot."'";

    //                 $db_lot = DB::connection($this->wbs)->select($sql_lot);
    //                 $lot_count = count($db_lot);
                    

    //                 if ($lot_count > 0) {
    //                     if ($db_lot[0]->source == 'MR') {
    //                         $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
    //                                         ->where('id',$db_lot[0]->mr_id)
    //                                         ->count();
    //                         $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
    //                         $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
    //                         $inv_id = $db_lot[0]->inv_id;
    //                         $mr_id = $db_lot[0]->mr_id;
    //                     } else {
    //                         $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
    //                         $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
    //                         $inv_id = $db_lot[0]->inv_id;
    //                         $mr_id = $db_lot[0]->mr_id;
    //                     }
    //                 } else {
    //                     $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
    //                                     ->where('not_for_iqc',0)
    //                                     ->where('invoice_no',$req->invoice_no)
    //                                     ->where('item',$req->partcode)
    //                                     ->where('lot_no',$lot)
    //                                     ->count();
    //                     $MRwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
    //                     $INVwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
    //                 }

                    
    //             } else {
                    
    //                 if (Str::contains($req->app_no, 'MAT') ) {
    //                     $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
    //                                 ->where('id',$Arrmr_id[$key])
    //                                 ->count();
    //                     $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
    //                     $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
    //                     $mr_id = $Arrmr_id[$key];
    //                     $inv_id = $Arrinv_id[$key];
    //                 } else {
    //                     $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
    //                     $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
    //                     $mr_id = $Arrmr_id[$key];
    //                     $inv_id = $Arrinv_id[$key];
    //                 }
    //             }


    //             if ($checker > 0) {
    //                 $table = 'tbl_wbs_material_receiving_batch';
    //             } 

    //             $ngr = $req->ngr;
    //             DB::beginTransaction();
    //             DB::connection($this->wbs)->table($table)
    //                 ->whereRaw(DB::raw($MRwhereRaw))
    //                 ->update([
    //                     'iqc_status' => $status,
    //                     'for_kitting' => $kitting,
    //                     'iqc_result' => $req->remarks,
    //                     'judgement' => $req->judgement,
    //                     'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
    //                     'ins_time' => $req->time_ins_to,
    //                     'ins_by' => $req->inspector,
    //                     'updated_at' => Carbon::now(),
    //                 ]);
                    
    //             DB::connection($this->wbs)->table('tbl_wbs_inventory')
    //                 ->whereRaw(DB::raw($INVwhereRaw))
    //                 ->update([
    //                     'iqc_status' => $status,
    //                     'for_kitting' => $kitting,
    //                     'iqc_result' => $req->remarks,
    //                     'judgement' => $req->judgement,
    //                     'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
    //                     'ins_time' => $req->time_ins_to,
    //                     'ins_by' => $req->inspector,
    //                     'ngr_status' => $ngr['status_NGR'],
    //                     // 'ngr_disposition' => $ngr['disposition_NGR'],
    //                     'ngr_control_no' => $ngr['control_no_NGR'],
    //                     'ngr_issued_date' => $ngr['date_NGR'],
    //                     'iqc_id' => $req->id,
    //                     'updated_at' => Carbon::now(),
    //                 ]);

    //         }

    //         $ngr = $req->ngr;

    //         $query =  DB::connection($this->mysql)->table('iqc_inspections')
    //                 ->insertGetId([
    //                     'invoice_no' => $req->invoice_no,
    //                     'partcode' => $req->partcode,
    //                     'partname' => $req->partname,
    //                     'supplier' => $req->supplier,
    //                     'app_date' => $req->app_date,
    //                     'app_time' => $req->app_time,
    //                     'app_no' => $req->app_no,
    //                     'lot_no' => $lot_nos,
    //                     'lot_qty' => $req->total_lot_qty,
    //                     'type_of_inspection' => $req->type_of_inspection,
    //                     'severity_of_inspection' => $req->severity_of_inspection,
    //                     'inspection_lvl' => $req->inspection_lvl,
    //                     'aql' => $req->aql,
    //                     'accept' => $req->accept,
    //                     'reject' => $req->reject,
    //                     'date_ispected' => $req->date_inspected,
    //                     'ww' => $req->ww,
    //                     'fy' => $req->fy,
    //                     'shift' => $req->shift,
    //                     'time_ins_from' => $req->time_ins_from,
    //                     'time_ins_to' => $req->time_ins_to,
    //                     'inspector' => $req->inspector,
    //                     'submission' => $req->submission,
    //                     'judgement' => $req->judgement,
    //                     'lot_inspected' => $req->lot_inspected,
    //                     'lot_accepted' => $req->lot_accepted,
    //                     'sample_size' => $req->sample_size,
    //                     'no_of_defects' => $req->no_of_defects,
    //                     'remarks' => $req->remarks,
    //                     'classification' => $req->classification,
    //                     'family' => $req->family,
    //                     'dbcon' => Auth::user()->productline,
    //                     'inv_id' => $req->inv_id,
    //                     'mr_id' => $req->mr_id,
    //                     'created_at' => Carbon::now(),
    //                     'updated_at' => Carbon::now(),
    //                     'ngr_status' => $ngr['status_NGR'],
    //                     'ngr_disposition' => $ngr['disposition_NGR'],
    //                     'ngr_control_no' => $ngr['control_no_NGR'],
    //                     'ngr_issued_date' => $ngr['date_NGR'],
    //                     'with_dispo' => 0
    //                 ]);

    //         $mod = $req->mod_of_defects;
    //         if(count($mod) > 0 ){
    //             foreach ($mod as $key => $m) {
    //                 DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
    //                     ->insert([
    //                         'invoice_no' => $req->invoice_no,
    //                         'partcode' => $req->partcode,
    //                         'mod' => $m['mod'],
    //                         'qty' => $m['qty'],
    //                         'created_at' => date('Y-m-d H:i:s'),
    //                         'updated_at' => date('Y-m-d H:i:s'),
    //                         'lot_no' => $m['lot_no'],
    //                         'iqc_id' => $query
    //                 ]);
    //             }
    //         }
            
           
    //         for($i = 0; $i < count($for_lots); $i++){
    //             DB::connection($this->mysql)->table('iqc_lot_no')
    //                 ->insert([
    //                     'iqc_id' => $query,
    //                     'inv_id' =>  $Arrinv_id[$i],
    //                     'mr_id' => $Arrmr_id[$i],
    //                     'mr_source' => $mr_source[$i],
    //                     'qty' => floatval($lotQty[$i]),
    //                     'lot_no' => $for_lots[$i],
    //                     'invoice_no' => $req->invoice_no,
    //                     'item_no' => $req->partcode,
    //                     'created_at' => Carbon::now(),
    //                     'updated_at' => Carbon::now(),
    //                     'create_user' => Auth::user()->user_id,
    //                     'update_user' => Auth::user()->user_id,
    //                     'is_deleted' => 0
    //             ]);
    //         }

    //         //UPDATE SOURCE CODE FOR REMOVE ON-GOING LOT NUMBER IF THEY ADD IN INSPECTION FORM WITH THE SAME LOT NUMBER IN ON-GOING TAB
    //         //JUNE 14, 2022
    //         $lot_isOngoing = [];
    //         $ongoingLotNo = [];

    //         $sql = "SELECT invoice_no, partcode, lot_no FROM iqc_inspections WHERE judgement = 'On-going' AND is_deleted = '0' ORDER BY id DESC";
    //         $sqlLotNoOngoing = DB::Connection($this->mysql)->select($sql);
            
    //         foreach($sqlLotNoOngoing as $key => $lotNo){
    //             $temp =  explode(',',$lotNo->lot_no);
    //             array_push($ongoingLotNo, $temp);
    //         }

    //         $ongoingLotNo = call_user_func_array('array_merge', $ongoingLotNo);

    //         foreach($lots as $key => $l){
    //             for($i = 0; $i < count($ongoingLotNo);){
    //                 if($l == $ongoingLotNo[$i]){
    //                     array_push($lot_isOngoing, $l);
    //                     break;
    //                 }else{
    //                     $i++;
    //                 }
    //             }
    //         }

    //         $condition = "";
    //         foreach($lot_isOngoing as $key => $x){
    //             if(count($lot_isOngoing) > 1){
    //                 $condition = $condition . " OR lot_no LIKE '%" .$x. "%'";
    //             }else{
    //                 $condition = "";
    //             }
    //         }

    //         $sql = "SELECT id FROM iqc_inspections WHERE (lot_no LIKE '%" .$lot_isOngoing[0]. "%'" .$condition. ") AND judgement = 'On-going' AND is_deleted = 0 ";
    //         $getId = DB::Connection($this->mysql)->select($sql);

    //         foreach($getId as $key => $l){
    //             DB::connection($this->mysql)->table('iqc_inspections')
    //                 ->where('id', $l->id)
    //                 ->update([
    //                     'is_deleted' => 1,
    //                     'inspector' => $req->inspector,
    //                     'updated_at' => Carbon::now()
    //                 ]);
    //         }

    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //     }
    // }

    private function insertToInspection($req,$lots)
    {
        $test = $req->lot_no_data;
        $for_lots;
        $lot_nos;
        $lot_qty;
        $lotQty;
        $is_batching = $req->is_batching;
        $Arrinv_id;
        $Arrmr_id;
        $mr_source;

        try {
            if (is_array($lots)) {
                $for_lots = $lots;
                $Arrinv_id = explode(',' ,$req->inv_id);
                $Arrmr_id = explode(',' ,$req->mr_id);
                $lot_qty = explode(',' ,$req->lot_qty);
                $lotQty = explode(',' ,$req->lot_qty);
                $mr_source = explode(',' ,$req->mr_source);
                $lot_nos = implode(',',$lots);
            } else {
                $for_lots = explode(',',$lots);
                $lot_nos = $lots;
            }

            foreach ($for_lots as $key => $lot) {
                $lot_qty = $this->getLotQty($req->invoice_no,$req->partcode,$lot);
                $status = 0;
                $kitting = 0;

                $check_accepted_judgment = ["Sorted", "Reworked", "Accepted", 'Special Accept'];
                $check_rejected_judgment = ["RTV", "Rejected"];

                if (in_array($req->judgement,$check_accepted_judgment)) {
                    $status = 1;
                    $kitting = 1;
                } 

                if (in_array($req->judgement,$check_rejected_judgment)) {
                    $status = 2;
                    $kitting = 0;
                }

                $checker = -1;
                $MRwhereRaw = "";
                $INVwhereRaw = "";
                $inv_id = 0;
                $mr_id = 0;

                if (is_null($req->inv_id) || empty($req->inv_id)) {
                    $sql_lot = "select l.lot_no as lot_no,
                                l.id as mr_id,
                                (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
                                'LR' as `source`
                                from tbl_wbs_local_receiving_batch as l
                                where l.invoice_no = '".$req->invoice_no."'
                                and l.item = '".$req->partcode."'
                                and l.lot_no = '".$lot."'
                                union
                                select m.lot_no as lot_no,
                                m.id as mr_id,
                                (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id,
                                'MR' as `source`
                                from tbl_wbs_material_receiving_batch as m
                                where m.invoice_no = '".$req->invoice_no."'
                                and m.item = '".$req->partcode."'
                                and m.lot_no = '".$lot."'";

                    $db_lot = DB::connection($this->wbs)->select($sql_lot);
                    $lot_count = count($db_lot);
                    

                    if ($lot_count > 0) {
                        if ($db_lot[0]->source == 'MR') {
                            $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                                            ->where('id',$db_lot[0]->mr_id)
                                            ->count();
                            $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
                            $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
                            $inv_id = $db_lot[0]->inv_id;
                            $mr_id = $db_lot[0]->mr_id;
                        } else {
                            $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
                            $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
                            $inv_id = $db_lot[0]->inv_id;
                            $mr_id = $db_lot[0]->mr_id;
                        }
                    } else {
                        $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                                        ->where('not_for_iqc',0)
                                        ->where('invoice_no',$req->invoice_no)
                                        ->where('item',$req->partcode)
                                        ->where('lot_no',$lot)
                                        ->count();
                        $MRwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
                        $INVwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
                    }

                    
                } else {
                    
                    if (Str::contains($req->app_no, 'MAT') ) {
                        $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                                    ->where('id',$Arrmr_id[$key])
                                    ->count();
                        $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
                        $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
                        $mr_id = $Arrmr_id[$key];
                        $inv_id = $Arrinv_id[$key];
                    } else {
                        $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
                        $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
                        $mr_id = $Arrmr_id[$key];
                        $inv_id = $Arrinv_id[$key];
                    }
                }


                if ($checker > 0) {
                    $table = 'tbl_wbs_material_receiving_batch';
                } 

                $ngr = $req->ngr;
                DB::beginTransaction();
                DB::connection($this->wbs)->table($table)
                    ->whereRaw(DB::raw($MRwhereRaw))
                    ->update([
                        'iqc_status' => $status,
                        'for_kitting' => $kitting,
                        'iqc_result' => $req->remarks,
                        'judgement' => $req->judgement,
                        'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                        'ins_time' => $req->time_ins_to,
                        'ins_by' => $req->inspector,
                        'updated_at' => Carbon::now(),
                    ]);
                    
                DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->whereRaw(DB::raw($INVwhereRaw))
                    ->update([
                        'iqc_status' => $status,
                        'for_kitting' => $kitting,
                        'iqc_result' => $req->remarks,
                        'judgement' => $req->judgement,
                        'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                        'ins_time' => $req->time_ins_to,
                        'ins_by' => $req->inspector,
                        'ngr_status' => $ngr['status_NGR'],
                        // 'ngr_disposition' => $ngr['disposition_NGR'],
                        'ngr_control_no' => $ngr['control_no_NGR'],
                        'ngr_issued_date' => $ngr['date_NGR'],
                        'iqc_id' => $req->id,
                        'updated_at' => Carbon::now(),
                    ]);

                    
                // $table = 'tbl_wbs_local_receiving_batch';

                // $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                //                 ->where('not_for_iqc',0)
                //                 ->where('invoice_no',$req->invoice_no)
                //                 ->where('item',$req->partcode)
                //                 ->where('lot_no',$lot)
                //                 ->count();
                // if ($checker > 0) {
                //     $table = 'tbl_wbs_material_receiving_batch';
                // }

                // DB::connection($this->wbs)->table($table)
                //     ->where('not_for_iqc',0)
                //     ->where('invoice_no',$req->invoice_no)
                //     ->where('item',$req->partcode)
                //     ->where('lot_no',$lot)
                //     ->update([
                //         'iqc_status' => $status,
                //         'for_kitting' => $kitting,
                //         'iqc_result' => $req->remarks,
                //         'judgement' => $req->judgement,
                //         'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                //         'ins_time' => $req->time_ins_to,
                //         'ins_by' => $req->inspector,
                //         'updated_at' => Carbon::now(),
                //     ]);
                // DB::connection($this->wbs)->table('tbl_wbs_inventory')
                //     ->where('not_for_iqc',0)
                //     ->where('invoice_no',$req->invoice_no)
                //     ->where('item',$req->partcode)
                //     ->where('lot_no',$lot)
                //     ->update([
                //         'iqc_status' => $status,
                //         'for_kitting' => $kitting,
                //         'iqc_result' => $req->remarks,
                //         'judgement' => $req->judgement,
                //         'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                //         'ins_time' => $req->time_ins_to,
                //         'ins_by' => $req->inspector,
                //         'updated_at' => Carbon::now(),
                //     ]);
            }

            $ngr = $req->ngr;

            $query =  DB::connection($this->mysql)->table('iqc_inspections')
                    ->insertGetId([
                        'invoice_no' => $req->invoice_no,
                        'partcode' => $req->partcode,
                        'partname' => $req->partname,
                        'supplier' => $req->supplier,
                        'app_date' => $req->app_date,
                        'app_time' => $req->app_time,
                        'app_no' => $req->app_no,
                        'lot_no' => $lot_nos,
                        'lot_qty' => $req->total_lot_qty,
                        'type_of_inspection' => $req->type_of_inspection,
                        'severity_of_inspection' => $req->severity_of_inspection,
                        'inspection_lvl' => $req->inspection_lvl,
                        'aql' => $req->aql,
                        'accept' => $req->accept,
                        'reject' => $req->reject,
                        'date_ispected' => $req->date_inspected,
                        'ww' => $req->ww,
                        'fy' => $req->fy,
                        'shift' => $req->shift,
                        'time_ins_from' => $req->time_ins_from,
                        'time_ins_to' => $req->time_ins_to,
                        'inspector' => $req->inspector,
                        'submission' => $req->submission,
                        'judgement' => $req->judgement,
                        'lot_inspected' => $req->lot_inspected,
                        'lot_accepted' => $req->lot_accepted,
                        'sample_size' => $req->sample_size,
                        'no_of_defects' => $req->no_of_defects,
                        'remarks' => $req->remarks,
                        'classification' => $req->classification,
                        'family' => $req->family,
                        'dbcon' => Auth::user()->productline,
                        'inv_id' => $req->inv_id,
                        'mr_id' => $req->mr_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'ngr_status' => $ngr['status_NGR'],
                        'ngr_disposition' => $ngr['disposition_NGR'],
                        'ngr_control_no' => $ngr['control_no_NGR'],
                        'ngr_issued_date' => $ngr['date_NGR'],
                        'with_dispo' => 0
                    ]);

            if (count($req->mode_of_defect) > 0) {

                $countModeOfDefect = count($req->mode_of_defect);

                for($i = 0; $i < $countModeOfDefect; $i++){
                    $insertMod_query = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                        ->insert([
                            'iqc_id' => $query,
                            'invoice_no' => $req->invoice_no,
                            'partcode' => $req->partcode,
                            'mod' => $req->mode_of_defect[$i]["mod"],
                            'qty' => $req->mode_of_defect[$i]["qty"],
                            'lot_no' => $req->mode_of_defect[$i]["lot_no"],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }     
            }
           
            for($i = 0; $i < count($for_lots); $i++){
                DB::connection($this->mysql)->table('iqc_lot_no')
                    ->insert([
                        'iqc_id' => $query,
                        'inv_id' =>  $Arrinv_id[$i],
                        'mr_id' => $Arrmr_id[$i],
                        'mr_source' => $mr_source[$i],
                        'qty' => floatval($lotQty[$i]),
                        'lot_no' => $for_lots[$i],
                        'invoice_no' => $req->invoice_no,
                        'item_no' => $req->partcode,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'create_user' => Auth::user()->user_id,
                        'update_user' => Auth::user()->user_id,
                        'is_deleted' => 0
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }


    
    public function specialAccept(Request $req)
    {
        $judgement = $req->judgement;
        $lots = $req->lot_no;
        $wbs_inventory = [];
        $partcode = (is_null($req->partcodelbl) && empty($req->partcodelbl)) ? $req->partcode : $req->partcodelbl;

        $query = false;
        
        $lot_qty = 0;

        if (is_string($req->lot_no)) {
            $array_lots = explode(',',$lots);
        }

        $data = [
            'return_status' => 'failed',
            'msg' => "Item Already Accepted."
        ];

        $check_duplicate = DB::connection($this->mysql)->table('iqc_inspections')
                            ->where('lot_no', $lots)
                            ->where('invoice_no', $req->invoice_no)
                            ->where('judgement', 'Special Accept')
                            ->count();
        if ($check_duplicate <= 0) {
                
            foreach ($array_lots as $key => $lot) {
                $lot_qty = $lot_qty + $this->getLotQty($req->invoice_no,$partcode,$lot);

                $status = 4;
                $kitting = 1;

                $count = DB::connection($this->wbs)->table('tbl_wbs_inventory')
							->where('id', $req->inv_id)
							->count();
				
				if ($count > 0) {
					$wbs_inventory = DB::connection($this->wbs)->table('tbl_wbs_inventory')
										->where('id', $req->inv_id)
										->first();
				} else {
					$wbs_inventory = DB::connection($this->wbs)->table('tbl_wbs_inventory')
										->where('invoice_no',$req->invoice_no)
										->where('item',$partcode)
										->where('lot_no',$lot)
										->first();
				}

                DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->insert([
                        'app_by' => $wbs_inventory->app_by,
                        'app_date' => $wbs_inventory->app_date,
                        'app_time'  => $wbs_inventory->app_time,
                        'box' => $wbs_inventory->box,
                        'box_qty' => $wbs_inventory->box_qty,
                        'create_pg' => $wbs_inventory->create_pg,
                        'create_user' => $wbs_inventory->create_user,
                        'created_at' => $wbs_inventory->created_at,
                        'deleted' => $wbs_inventory->deleted,
                        'drawing_num' => $wbs_inventory->drawing_num,
                        'for_kitting' => $kitting,
                        'ins_by' => $wbs_inventory->ins_by,
                        'ins_date' => $wbs_inventory->ins_date,
                        'ins_time' => $wbs_inventory->ins_time,
                        'invoice_no' => $wbs_inventory->invoice_no,
                        'iqc_result' => $wbs_inventory->iqc_result,
                        'iqc_status' => $status,
                        'is_printed' => $wbs_inventory->is_printed,
                        'item' => $wbs_inventory->item,
                        'item_desc' => $wbs_inventory->item_desc,
                        'judgement' => "Special Accept",
                        'loc_batch_id' => $wbs_inventory->loc_batch_id,
                        'location' => $wbs_inventory->location,
                        'lot_no' => $wbs_inventory->lot_no,
                        'mat_batch_id' => $wbs_inventory->mat_batch_id,
                        'not_for_iqc' => $wbs_inventory->not_for_iqc,
                        'plating_date' => $wbs_inventory->plating_date,
                        'pressed_date' => $wbs_inventory->pressed_date,
                        'qty' => $wbs_inventory->qty,
                        'received_date' => $wbs_inventory->received_date,
                        'supplier' => $wbs_inventory->supplier,
                        'update_pg' => $wbs_inventory->update_pg,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => Carbon::now(),
                        'wbs_mr_id' => $wbs_inventory->wbs_mr_id
                    ]);
            }
            
            DB::connection($this->mysql)->table('iqc_inspections')
                ->insert([
                    'invoice_no' => $req->invoice_no,
                    'partcode' => $partcode,
                    'partname' => $req->partname,
                    'supplier' => $req->supplier,
                    'app_date' => $req->app_date,
                    'app_time' => $req->app_time,
                    'app_no' => $req->app_no,
                    'lot_no' => $lots,
                    'lot_qty' => $lot_qty,
                    'type_of_inspection' => $req->type_of_inspection,
                    'severity_of_inspection' => $req->severity_of_inspection,
                    'inspection_lvl' => $req->inspection_lvl,
                    'aql' => $req->aql,
                    'accept' => $req->accept,
                    'reject' => $req->reject,
                    'date_ispected' => $req->date_inspected,
                    'ww' => $req->ww,
                    'fy' => $req->fy,
                    'shift' => $req->shift,
                    'time_ins_from' => $req->time_ins_from,
                    'time_ins_to' => $req->time_ins_to,
                    'inspector' => $req->inspector,
                    'submission' => $req->submission,
                    'judgement' => 'Special Accept',
                    'lot_inspected' => $req->lot_inspected,
                    'lot_accepted' => $req->lot_accepted,
                    'sample_size' => $req->sample_size,
                    'no_of_defects' => $req->no_of_defects,
                    'remarks' => $req->remarks,
                    'classification' => $req->classification,
                    'dbcon' => Auth::user()->productline,
                    'updated_at' => Carbon::now(),
                ]);
            $query = true;
        }else {
            $query = false;
            $data = [
                'return_status' => 'failed',
                'msg' => "Item Already Accepted."
            ];
        }
        if ($query) {
            //Event::fire(new UpdateIQCInspection($this->wbs));
            $data = [
                'return_status' => 'success',
                'msg' => "Special Acceptance Success."
            ];
        }

        return $data;
    }

    // private function updateInspection($req,$lots)
    // {
    //     $for_lots = [];
    //     $lot_nos = '';
    //     $is_batching = $req->is_batching;
    //     $Arrinv_id = [];
    //     $Arrmr_id = [];

    //     try {
    //         if (is_array($lots)) {
    //             $for_lots = $lots;
    //             $Arrinv_id = explode(',',$req->inv_id);
    //             $Arrmr_id = explode(',',$req->mr_id);
    //             $lot_nos = implode(',',$lots);
    //         } else {
    //             $for_lots = explode(',',$lots);
    //             $lot_nos = $lots;
    //         }

    //         if ($is_batching == 1 || $is_batching == "1") {

    //         }

    //         foreach ($for_lots as $key => $lot) {
    //             $lot_qty = $this->getLotQty($req->invoice_no,$req->partcode,$lot);
    //             $status = 0;
    //             $kitting = 0;

    //             $check_accepted_judgment = ["Sorted", "Reworked", "Accepted", 'Special Accept'];
    //             $check_rejected_judgment = ["RTV", "Rejected"];

    //             if (in_array($req->judgement,$check_accepted_judgment)) {
    //                 $status = 1;
    //                 $kitting = 1;
    //             } 

    //             if (in_array($req->judgement,$check_rejected_judgment)) {
    //                 $status = 2;
    //                 $kitting = 0;
    //             }

    //             $table = 'tbl_wbs_local_receiving_batch';

    //             $checker = -1;
    //             $MRwhereRaw = "";
    //             $INVwhereRaw = "";
    //             $inv_id = 0;
    //             $mr_id = 0;

    //             DB::beginTransaction();

    //             if (is_null($req->inv_id) || empty($req->inv_id)) {
    //                 $sql_lot = "select l.lot_no as lot_no,
    //                             l.id as mr_id,
    //                             (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
    //                             'LR' as `source`
    //                             from tbl_wbs_local_receiving_batch as l
    //                             where l.invoice_no = '".$req->invoice_no."'
    //                             and l.item = '".$req->partcode."'
    //                             and l.lot_no = '".$lot."'
    //                             union
    //                             select m.lot_no as lot_no,
    //                             m.id as mr_id,
    //                             (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id,
    //                             'MR' as `source`
    //                             from tbl_wbs_material_receiving_batch as m
    //                             where m.invoice_no = '".$req->invoice_no."'
    //                             and m.item = '".$req->partcode."'
    //                             and m.lot_no = '".$lot."'";

    //                 $db_lot = DB::connection($this->wbs)->select($sql_lot);
    //                 $lot_count = count($db_lot);
                    

    //                 if ($lot_count > 0) {
    //                     if ($db_lot[0]->source == 'MR') {
    //                         $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
    //                                         ->where('id',$db_lot[0]->mr_id)
    //                                         ->count();
    //                         $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
    //                         $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
    //                         $inv_id = $db_lot[0]->inv_id;
    //                         $mr_id = $db_lot[0]->mr_id;
    //                     } else {
    //                         $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
    //                         $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
    //                         $inv_id = $db_lot[0]->inv_id;
    //                         $mr_id = $db_lot[0]->mr_id;
    //                     }
    //                 } else {
    //                     $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
    //                                     ->where('not_for_iqc',0)
    //                                     ->where('invoice_no',$req->invoice_no)
    //                                     ->where('item',$req->partcode)
    //                                     ->where('lot_no',$lot)
    //                                     ->count();
    //                     $MRwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
    //                     $INVwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
    //                 }

                    
    //             } else {
                    
    //                 if (Str::contains($req->app_no, 'MAT') ) {
    //                     $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
    //                                 ->where('id',$Arrmr_id[$key])
    //                                 ->count();
    //                     $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
    //                     $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
    //                     $mr_id = $Arrmr_id[$key];
    //                     $inv_id = $Arrinv_id[$key];
    //                 } else {
    //                     $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
    //                     $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
    //                     $mr_id = $Arrmr_id[$key];
    //                     $inv_id = $Arrinv_id[$key];
    //                 }
    //             }


    //             if ($checker > 0) {
    //                 $table = 'tbl_wbs_material_receiving_batch';
    //             } 
    //             // else {
    //             //     $sql_lot = "select l.lot_no as lot_no,
    //             //                 l.id as mr_id,
    //             //                 (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
    //             //                 'LR' as `source`
    //             //                 from tbl_wbs_local_receiving_batch as l
    //             //                 where l.invoice_no = '".$req->invoice_no."'
    //             //                 and l.item = '".$req->partcode."'";

    //             //     $db_lot = DB::connection($this->wbs)->select($sql_lot);
    //             //     $lot_count = count($db_lot);

    //             //     if ($lot_count > 0) {
    //             //         $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
    //             //         $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
    //             //         $inv_id = $db_lot[0]->inv_id;
    //             //         $mr_id = $db_lot[0]->mr_id;
    //             //     } else {
    //             //         $checker = DB::connection($this->wbs)->table($table)
    //             //                         ->where('not_for_iqc',0)
    //             //                         ->where('invoice_no',$req->invoice_no)
    //             //                         ->where('item',$req->partcode)
    //             //                         ->where('lot_no',$req->lot_no)
    //             //                         ->count();
    //             //         $MRwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$req->lot_no."'";
    //             //         $INVwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$req->lot_no."'";
    //             //     }
    //             // }

    //             $ngr = $req->ngr;

    //             DB::connection($this->wbs)->table($table)
    //                 ->whereRaw(DB::raw($MRwhereRaw))
    //                 ->update([
    //                     'iqc_status' => $status,
    //                     'for_kitting' => $kitting,
    //                     'iqc_result' => $req->remarks,
    //                     'judgement' => $req->judgement,
    //                     'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
    //                     'ins_time' => $req->time_ins_to,
    //                     'ins_by' => $req->inspector,
    //                     'updated_at' => Carbon::now(),
    //                 ]);
                    
    //             DB::connection($this->wbs)->table('tbl_wbs_inventory')
    //                 ->whereRaw(DB::raw($INVwhereRaw))
    //                 ->update([
    //                     'iqc_status' => $status,
    //                     'for_kitting' => $kitting,
    //                     'iqc_result' => $req->remarks,
    //                     'judgement' => $req->judgement,
    //                     'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
    //                     'ins_time' => $req->time_ins_to,
    //                     'ins_by' => $req->inspector,
    //                     'ngr_status' => $ngr['status_NGR'],
    //                     // 'ngr_disposition' => $ngr['disposition_NGR'],
    //                     'ngr_control_no' => $ngr['control_no_NGR'],
    //                     'ngr_issued_date' => $ngr['date_NGR'],
    //                     'iqc_id' => $req->id,
    //                     'updated_at' => Carbon::now(),
    //                 ]);
    //         }
            

    //         DB::connection($this->mysql)->table('iqc_inspections')
    //             ->where('id',$req->id)
    //             ->update([
    //                 'partcode' => $req->partcode,
    //                 'partname' => $req->partname,
    //                 'supplier' => $req->supplier,
    //                 'app_date' => $req->app_date,
    //                 'app_time' => $req->app_time,
    //                 'app_no' => $req->app_no,
    //                 'lot_no' => $lot_nos,
    //                 'lot_qty' => $req->lot_qty,
    //                 'type_of_inspection' => $req->type_of_inspection,
    //                 'severity_of_inspection' => $req->severity_of_inspection,
    //                 'inspection_lvl' => $req->inspection_lvl,
    //                 'aql' => $req->aql,
    //                 'accept' => $req->accept,
    //                 'reject' => $req->reject,
    //                 'date_ispected' => $req->date_inspected,
    //                 'ww' => $req->ww,
    //                 'fy' => $req->fy,
    //                 'shift' => $req->shift,
    //                 'time_ins_from' => $req->time_ins_from,
    //                 'time_ins_to' => $req->time_ins_to,
    //                 'inspector' => $req->inspector,
    //                 'submission' => $req->submission,
    //                 'judgement' => $req->judgement,
    //                 'lot_inspected' => $req->lot_inspected,
    //                 'lot_accepted' => $req->lot_accepted,
    //                 'sample_size' => $req->sample_size,
    //                 'no_of_defects' => $req->no_of_defects,
    //                 'remarks' => $req->remarks,
    //                 'classification' => $req->classification,
    //                 'family' => $req->family,
    //                 'dbcon' => Auth::user()->productline,
    //                 'updated_at' => Carbon::now(),
    //                 'ngr_status' => $ngr['status_NGR'],
    //                 // 'ngr_disposition' => $ngr['disposition_NGR'],
    //                 'ngr_control_no' => $ngr['control_no_NGR'],
    //                 'ngr_issued_date' => $ngr['date_NGR'],
    //                 'inv_id' => $inv_id,
    //                 'mr_id' => $mr_id
    //             ]);

    //         $mod = $req->mod_of_defects;
    //         if (count($mod) > 0) {
    //             foreach ($mod as $key => $m){
    //                 if($m['id'] == "-1" || $m['id']== -1){
    //                     DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
    //                         ->insert([
    //                             'invoice_no' => $req->invoice_no,
    //                             'partcode' => $req->partcode,
    //                             'mod' => $m['mod'],
    //                             'qty' => $m['qty'],
    //                             'created_at' => date('Y-m-d H:i:s'),
    //                             'updated_at' => date('Y-m-d H:i:s'),
    //                             'lot_no' => $m['lot_no'],
    //                             'iqc_id' => $req->id
    //                         ]);
    //                 }else{
    //                     DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
    //                     ->where('id', $m['id'])
    //                     ->update([
    //                         'invoice_no' => $req->invoice_no,
    //                         'partcode' => $req->partcode,
    //                         'mod' => $m['mod'],
    //                         'qty' => $m['qty'],
    //                         'updated_at' => date('Y-m-d H:i:s'),
    //                         'lot_no' => $m['lot_no'],
    //                         'iqc_id' => $req->id
    //                     ]); 
    //                 }
    //             }
    //         }
                
    //         if (!is_null($ngr['disposition_NGR']) && !empty($ngr['disposition_NGR'])) {
                
    //             // $check_judgment = ["Sorted", "Reworked", "RTV", 'Special Accept'];

    //             // if (!in_array($req->judgement,$check_judgment)) {
    //             //     $this->disposition($req, $lot_nos, $ngr, $inv_id, $mr_id);
    //             // }

    //             $this->disposition($req, $lot_nos, $ngr, $Arrinv_id, $Arrmr_id);
    //         }

    //         DB::commit();

    //     } 
    //     catch (\Exception $e) {
    //         DB::rollback();
    //     }
    // }

    private function updateInspection($req,$lots)
    {
        $for_lots = [];
        $lot_nos = '';
        $is_batching = $req->is_batching;
        $Arrinv_id = [];
        $Arrmr_id = [];


        try {
            if (is_array($lots)) {
                $for_lots = $lots;
                $Arrinv_id = explode(',',$req->inv_id);
                $Arrmr_id = explode(',',$req->mr_id);
                $lot_nos = implode(',',$lots);
            } else {
                $for_lots = explode(',',$lots);
                $lot_nos = $lots;
            }

            if ($is_batching == 1 || $is_batching == "1") {

            }

            foreach ($for_lots as $key => $lot) {
                $lot_qty = $this->getLotQty($req->invoice_no,$req->partcode,$lot);
                $status = 1;
                $kitting = 0;

                $check_accepted_judgment = ["Sorted", "Reworked", "Accepted", 'Special Accept'];
                $check_rejected_judgment = ["RTV", "Rejected"];

                if (in_array($req->judgement,$check_accepted_judgment)) {
                    $status = 1;
                    $kitting = 1;
                } 

                if (in_array($req->judgement,$check_rejected_judgment)) {
                    $status = 2;
                    $kitting = 0;
                }

                $table = 'tbl_wbs_local_receiving_batch';

                $checker = -1;
                $MRwhereRaw = "";
                $INVwhereRaw = "";
                $inv_id = 0;
                $mr_id = 0;

                DB::beginTransaction();

                if (is_null($req->inv_id) || empty($req->inv_id)) {
                    $sql_lot = "select l.lot_no as lot_no,
                                l.id as mr_id,
                                (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
                                'LR' as `source`
                                from tbl_wbs_local_receiving_batch as l
                                where l.invoice_no = '".$req->invoice_no."'
                                and l.item = '".$req->partcode."'
                                and l.lot_no = '".$lot."'
                                union
                                select m.lot_no as lot_no,
                                m.id as mr_id,
                                (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id,
                                'MR' as `source`
                                from tbl_wbs_material_receiving_batch as m
                                where m.invoice_no = '".$req->invoice_no."'
                                and m.item = '".$req->partcode."'
                                and m.lot_no = '".$lot."'";

                    $db_lot = DB::connection($this->wbs)->select($sql_lot);
                    $lot_count = count($db_lot);
                    

                    if ($lot_count > 0) {
                        if ($db_lot[0]->source == 'MR') {
                            $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                                            ->where('id',$db_lot[0]->mr_id)
                                            ->count();
                            $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
                            $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
                            $inv_id = $db_lot[0]->inv_id;
                            $mr_id = $db_lot[0]->mr_id;
                        } else {
                            $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
                            $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
                            $inv_id = $db_lot[0]->inv_id;
                            $mr_id = $db_lot[0]->mr_id;
                        }
                    } else {
                        $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                                        ->where('not_for_iqc',0)
                                        ->where('invoice_no',$req->invoice_no)
                                        ->where('item',$req->partcode)
                                        ->where('lot_no',$lot)
                                        ->count();
                        $MRwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
                        $INVwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$lot."'";
                    }

                    
                } else {
                    
                    if (Str::contains($req->app_no, 'MAT') ) {
                        $checker = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                                    ->where('id',$Arrmr_id[$key])
                                    ->count();
                        $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
                        $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
                        $mr_id = $Arrmr_id[$key];
                        $inv_id = $Arrinv_id[$key];
                    } else {
                        $MRwhereRaw = "id = '".$Arrmr_id[$key]."'";
                        $INVwhereRaw = "id = '".$Arrinv_id[$key]."'";
                        
                        $mr_id = $Arrmr_id[$key];
                        $inv_id = $Arrinv_id[$key];
                    }
                }


                if ($checker > 0) {
                    $table = 'tbl_wbs_material_receiving_batch';
                } 
                // else {
                //     $sql_lot = "select l.lot_no as lot_no,
                //                 l.id as mr_id,
                //                 (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id,
                //                 'LR' as `source`
                //                 from tbl_wbs_local_receiving_batch as l
                //                 where l.invoice_no = '".$req->invoice_no."'
                //                 and l.item = '".$req->partcode."'";

                //     $db_lot = DB::connection($this->wbs)->select($sql_lot);
                //     $lot_count = count($db_lot);

                //     if ($lot_count > 0) {
                //         $MRwhereRaw = "id = '".$db_lot[0]->mr_id."'";
                //         $INVwhereRaw = "id = '".$db_lot[0]->inv_id."'";
                //         $inv_id = $db_lot[0]->inv_id;
                //         $mr_id = $db_lot[0]->mr_id;
                //     } else {
                //         $checker = DB::connection($this->wbs)->table($table)
                //                         ->where('not_for_iqc',0)
                //                         ->where('invoice_no',$req->invoice_no)
                //                         ->where('item',$req->partcode)
                //                         ->where('lot_no',$req->lot_no)
                //                         ->count();
                //         $MRwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$req->lot_no."'";
                //         $INVwhereRaw = "not_for_iqc=0 AND invoice_no='".$req->invoice_no."' AND item='".$req->partcode."' AND lot_no='".$req->lot_no."'";
                //     }
                // }

                $ngr = $req->ngr;

                DB::connection($this->wbs)->table($table)
                    ->whereRaw(DB::raw($MRwhereRaw))
                    ->update([
                        'iqc_status' => $status,
                        'for_kitting' => $kitting,
                        'iqc_result' => $req->remarks,
                        'judgement' => $req->judgement,
                        'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                        'ins_time' => $req->time_ins_to,
                        'ins_by' => $req->inspector,
                        'updated_at' => Carbon::now(),
                    ]);
                    
                DB::connection($this->wbs)->table('tbl_wbs_inventory')
                    ->whereRaw(DB::raw($INVwhereRaw))
                    ->update([
                        'iqc_status' => $status,
                        'for_kitting' => $kitting,
                        'iqc_result' => $req->remarks,
                        'judgement' => $req->judgement,
                        'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                        'ins_time' => $req->time_ins_to,
                        'ins_by' => $req->inspector,
                        'ngr_status' => $ngr['status_NGR'],
                        // 'ngr_disposition' => $ngr['disposition_NGR'],
                        'ngr_control_no' => $ngr['control_no_NGR'],
                        'ngr_issued_date' => $ngr['date_NGR'],
                        'iqc_id' => $req->id,
                        'updated_at' => Carbon::now(),
                    ]);
            }
            

            DB::connection($this->mysql)->table('iqc_inspections')
                ->where('id',$req->id)
                ->update([
                    'partcode' => $req->partcode,
                    'partname' => $req->partname,
                    'supplier' => $req->supplier,
                    'app_date' => $req->app_date,
                    'app_time' => $req->app_time,
                    'app_no' => $req->app_no,
                    'lot_no' => $lot_nos,
                    'lot_qty' => $req->lot_qty,
                    'type_of_inspection' => $req->type_of_inspection,
                    'severity_of_inspection' => $req->severity_of_inspection,
                    'inspection_lvl' => $req->inspection_lvl,
                    'aql' => $req->aql,
                    'accept' => $req->accept,
                    'reject' => $req->reject,
                    'date_ispected' => $req->date_inspected,
                    'ww' => $req->ww,
                    'fy' => $req->fy,
                    'shift' => $req->shift,
                    'time_ins_from' => $req->time_ins_from,
                    'time_ins_to' => $req->time_ins_to,
                    'inspector' => $req->inspector,
                    'submission' => $req->submission,
                    'judgement' => $req->judgement,
                    'lot_inspected' => $req->lot_inspected,
                    'lot_accepted' => $req->lot_accepted,
                    'sample_size' => $req->sample_size,
                    'no_of_defects' => $req->no_of_defects,
                    'remarks' => $req->remarks,
                    'classification' => $req->classification,
                    'family' => $req->family,
                    'dbcon' => Auth::user()->productline,
                    'updated_at' => Carbon::now(),
                    'ngr_status' => $ngr['status_NGR'],
                    // 'ngr_disposition' => $ngr['disposition_NGR'],
                    'ngr_control_no' => $ngr['control_no_NGR'],
                    'ngr_issued_date' => $ngr['date_NGR'],
                    'inv_id' => $inv_id,
                    'mr_id' => $mr_id
                ]);

            if (count($req->mode_of_defect) > 0) {

                $countModeOfDefect = count($req->mode_of_defect);


                for($i = 0; $i < $countModeOfDefect; $i++){

                    if($req->mode_of_defect[$i]["id"] == "-1"){
                        $insertMod_query = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                            ->insert([
                                'iqc_id' => $req->id,
                                'invoice_no' => $req->invoice_no,
                                'partcode' => $req->partcode,
                                'mod' => $req->mode_of_defect[$i]["mod"],
                                'qty' => $req->mode_of_defect[$i]["qty"],
                                'lot_no' => $req->mode_of_defect[$i]["lot_no"],
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                    }else{
                        $updateMod_query = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                            ->where('id', $req->mode_of_defect[$i]["id"])
                            ->update([
                                'mod' => $req->mode_of_defect[$i]["mod"],
                                'qty' => $req->mode_of_defect[$i]["qty"],
                                'lot_no' => $req->mode_of_defect[$i]["lot_no"],
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                    
                    }           
                
                }     
            }

            if (!is_null($ngr['disposition_NGR']) && !empty($ngr['disposition_NGR'])) {
                
                // $check_judgment = ["Sorted", "Reworked", "RTV", 'Special Accept'];

                // if (!in_array($req->judgement,$check_judgment)) {
                //     $this->disposition($req, $lot_nos, $ngr, $inv_id, $mr_id);
                // }

                $this->disposition($req, $lot_nos, $ngr, $Arrinv_id, $Arrmr_id);
            }

            DB::commit();

        } 
        catch (\Exception $e) {
            DB::rollback();
        }
    }


    private function disposition($req, $lot_nos, $ngr, $Arrinv_id, $Arrmr_id)
    {
        $dispo = '';
        $check_duplicate = 0;

        try {
            if (Str::contains($ngr['disposition_NGR'], 'Sorting')) {
                $dispo = "Sorted";
            }
            elseif (Str::contains($ngr['disposition_NGR'], 'Rework')) {
                $dispo = "Reworked";
            }
            elseif (Str::contains($ngr['disposition_NGR'], 'RTV')) {
                $dispo = "RTV";
            }
            elseif (Str::contains($ngr['disposition_NGR'], 'Special Adoptation')) {
                $dispo = "Special Accept";
            }
            elseif (Str::contains($ngr['disposition_NGR'], 'Accept')) {
                $dispo = "Special Accept";
            }
            elseif (Str::contains($ngr['disposition_NGR'], 'Use as is')) {
                $dispo = "Special Accept";
            }
            elseif (Str::contains($ngr['disposition_NGR'], 'Ok to Use')) {
                $dispo = "Special Accept";
            }


            // check muna if meron nang dispo
            $iqc = DB::connection($this->mysql)->table('iqc_inspections')
                        ->where('id', $req->id)
                        ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                        ->first();


            if ($iqc->judgement == "Rejected") {
                DB::connection($this->mysql)->table('iqc_inspections')
                    ->where('id',$req->id)
                    ->update([
                        // 'judgement' => $dispo,
                        'inspector' => $req->inspector,
                        'updated_at' => Carbon::now(),
                        'ngr_status' => $ngr['status_NGR'],
                        'ngr_disposition' => $ngr['disposition_NGR'],
                        'ngr_control_no' => $ngr['control_no_NGR'],
                        'ngr_issued_date' => $ngr['date_NGR'],
                        'with_dispo' => 1
                    ]);
                DB::connection($this->mysql)->table('iqc_inspections')
                    ->insert([
                        'invoice_no' => $req->invoice_no,
                        'partcode' => $req->partcode,
                        'partname' => $req->partname,
                        'supplier' => $req->supplier,
                        'app_date' => $req->app_date,
                        'app_time' => $req->app_time,
                        'app_no' => $req->app_no,
                        'lot_no' => $lot_nos,
                        'lot_qty' => $req->lot_qty,
                        'type_of_inspection' => $req->type_of_inspection,
                        'severity_of_inspection' => $req->severity_of_inspection,
                        'inspection_lvl' => $req->inspection_lvl,
                        'aql' => $req->aql,
                        'accept' => $req->accept,
                        'reject' => $req->reject,
                        'date_ispected' => $req->date_inspected,
                        'ww' => $req->ww,
                        'fy' => $req->fy,
                        'shift' => $req->shift,
                        'time_ins_from' => $req->time_ins_from,
                        'time_ins_to' => $req->time_ins_to,
                        'inspector' => $req->inspector,
                        'submission' => $req->submission,
                        'judgement' => $dispo,
                        'lot_inspected' => $req->lot_inspected,
                        'lot_accepted' => $req->lot_accepted,
                        'sample_size' => $req->sample_size,
                        'no_of_defects' => $req->no_of_defects,
                        'remarks' => $req->remarks,
                        'classification' => $req->classification,
                        'family' => $req->family,
                        'dbcon' => Auth::user()->productline,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'ngr_status' => $ngr['status_NGR'],
                        'ngr_disposition' => $ngr['disposition_NGR'],
                        'ngr_control_no' => $ngr['control_no_NGR'],
                        'ngr_issued_date' => $ngr['date_NGR'],
                        'inv_id' => implode(',',$Arrinv_id),
                        'mr_id' => implode(',',$Arrmr_id),
                        'with_dispo' => 0,
                        'reject_id' => $req->id
                    ]);
            } else {
                DB::connection($this->mysql)->table('iqc_inspections')
                    ->where('id',$req->id)
                    ->update([
                        'judgement' => $dispo,
                        'inspector' => $req->inspector,
                        'updated_at' => Carbon::now(),
                        'ngr_status' => $ngr['status_NGR'],
                        'ngr_disposition' => $ngr['disposition_NGR'],
                        'ngr_control_no' => $ngr['control_no_NGR'],
                        'ngr_issued_date' => $ngr['date_NGR'],
                    ]);
            }

            foreach ($Arrinv_id as $key => $inv_id) {

                $status = 1;
                $kitting = 1;

                if ($dispo == "RTV") {
                    $status = 2;
                    $kitting = 0;
                }

                $table = "tbl_wbs_material_receiving_batch";

                $check_mr = DB::connection($this->wbs)->table('tbl_wbs_local_receiving_batch')
                                ->where('id',$Arrmr_id[$key])->count();

                if ($check_mr > 0) {
                    $table = "tbl_wbs_local_receiving_batch";
                }

                DB::connection($this->wbs)->table($table)
                    ->where('id',$Arrmr_id[$key])
                    ->update([
                        'iqc_status' => $status,
                        'for_kitting' => $kitting,
                        'iqc_result' => $req->remarks,
                        'judgement' => $dispo,
                        'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                        'ins_time' => $req->time_ins_to,
                        'ins_by' => $req->inspector,
                        'updated_at' => Carbon::now(),
                    ]);

                DB::connection($this->wbs)->table('tbl_wbs_inventory')
                ->where('id',$inv_id)
                ->update([
                    'iqc_status' => $status,
                    'for_kitting' => $kitting,
                    'iqc_result' => $req->remarks,
                    'judgement' => $dispo,
                    'ins_date' => $this->formatDate($req->date_inspected,'m/d/Y'),
                    'ins_time' => $req->time_ins_to,
                    'ins_by' => $req->inspector,
                    'ngr_status' => $ngr['status_NGR'],
                    'ngr_disposition' => $ngr['disposition_NGR'],
                    'ngr_control_no' => $ngr['control_no_NGR'],
                    'ngr_issued_date' => $ngr['date_NGR'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'update_user' => Auth::user()->user_id,
                    'update_pg' => "IQC INSPECTION - NGR Inspection"
                ]);
            }
        } 
        catch (\Exception $e) {
        }

            
    }

    private function insertHistory($lots,$req)
    {
        foreach ($lots as $key => $lot) {
            $lot_qty = $this->getLotQty($req->invoice,$req->partcode,$lot);

            DB::connection($this->mysql)->table('iqc_inspections_history')
                ->insert([
                    'invoice_no' => $req->invoice_no,
                    'partcode' => $req->partcode,
                    'partname' => $req->partname,
                    'supplier' => $req->supplier,
                    'app_date' => $req->app_date,
                    'app_time' => $req->app_time,
                    'app_no' => $req->app_no,
                    'lot_no' => $lot,
                    'lot_qty' => $lot_qty,
                    'type_of_inspection' => $req->type_of_inspection,
                    'severity_of_inspection' => $req->severity_of_inspection,
                    'inspection_lvl' => $req->inspection_lvl,
                    'aql' => $req->aql,
                    'accept' => $req->accept,
                    'reject' => $req->reject,
                    'date_ispected' => $req->date_inspected,
                    'ww' => $req->ww,
                    'fy' => $req->fy,
                    'shift' => $req->shift,
                    'time_ins_from' => $req->time_ins_from,
                    'time_ins_to' => $req->time_ins_to,
                    'inspector' => $req->inspector,
                    'submission' => $req->submission,
                    'judgement' => $req->judgement,
                    'lot_inspected' => $req->lot_inspected,
                    'lot_accepted' => $req->lot_accepted,
                    'sample_size' => $req->sample_size,
                    'no_of_defects' => $req->no_of_defects,
                    'remarks' => $req->remarks,
                    // 'classification' => $req->classification,
                    // 'family' => $req->family,
                    'dbcon' => Auth::user()->productline,
                    'created_at' => Carbon::now(),
                ]);
        }
    }

    private function requalifyInventory($app_no,$partcode,$lot)
    {
        DB::connection($this->wbs)->table('tbl_wbs_inventory')
            ->where('wbs_mr_id', $app_no)
            ->where('item', $partcode)
            ->where('lot_no', $lot)
            ->update([
                'received_date' => date('Y-m-d')
            ]);
    }

    public function getNumOfDefectives(Request $req)
    {
        $db = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                ->where('invoice_no',$req->invoice_no)
                ->where('partcode',$req->partcode)
                ->select(
                    DB::raw("SUM(qty) as no_of_defectives")
                )
                ->groupBy('modid')->first();
        if (count((array)$db) > 0) {
            return $db->no_of_defectives;
        } else {
            return 0;
        }
    }

    public function getShift(Request $req)
    {
        $shift = '';
        $from = Carbon::parse($req->from);
        $to = Carbon::parse($req->to);

        if ($req->from == '7:30 AM' && $req->to == '7:30 PM') {
            $shift = 'Shift A';
        }

        if ($req->from == '7:30 PM' && $req->to == '7:30 AM') {
            $shift = 'Shift B';
        }

        if ($from->hour < $to->hour) {
            $shift = 'Shift A';
        }

        if ($from->hour > $to->hour) {
            $shift = 'Shift B';
        }

        return $shift;
    }

    public function calculateLotQty(Request $req)
    {
        $lot_qty = 0;
        $lot_no = [];

        if (is_array($req->lot_no)) {
            $lot_no = $req->lot_no;
        } else {
            $lot_no = explode(',', $req->lot_no);
        }

        if (empty($req->lot_no) || is_null($req->lot_no)) {
            return $lot_qty;
        } else {
            foreach ($lot_no as $key => $lot) {
                $db = DB::connection($this->wbs)
                        ->select("select m.qty as lot_qty
                                from tbl_wbs_material_receiving_batch as m
                                where m.item = '".$req->item."'
                                and m.invoice_no = '".$req->invoiceno."'
                                and m.lot_no = '".$lot."'
                                and m.not_for_iqc = 0
                                union
                                select l.qty as lot_qty
                                from tbl_wbs_local_receiving_batch as l
                                where l.item = '".$req->item."'
                                and l.invoice_no = '".$req->invoiceno."'
                                and l.lot_no = '".$lot."'
                                and l.not_for_iqc = 0
                                limit 1");

                // $db = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
                //         ->select('qty as lot_qty')
                //         ->where('item',$req->item)
                //         ->where('invoice_no',$req->invoiceno)
                //         ->where('lot_no',$lot)
                //         ->first();
                if (count($db) > 0) {
                    $lot_qty = $lot_qty + $db[0]->lot_qty;
                }
            }
            return $lot_qty;
        }
    }

    private function getLotQty($invoice,$item,$lot)
    {
        $lot_qty = 0;

        $sql = "SELECT DISTINCT m.qty as lot_qty
                FROM tbl_wbs_material_receiving_batch as m
                WHERE m.not_for_iqc = 0
                AND m.invoice_no = '".$invoice."'
                AND m.lot_no = '".$lot."'
                AND m.item = '".$item."'
                and m.not_for_iqc = 0
                UNION
                SELECT l.qty as lot_qty
                FROM tbl_wbs_local_receiving_batch as l
                WHERE l.not_for_iqc = 0
                AND l.invoice_no = '".$invoice."'
                AND l.lot_no = '".$lot."'
                AND l.item = '".$item."'
                and l.not_for_iqc = 0
                limit 1";

        $db = DB::connection($this->wbs)->select($sql);
        $count = count($db);

        if ($count > 0) {
            $lot_qty = $db[0]->lot_qty;
        }

        // $db = DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
        //         ->select('qty as lot_qty')
        //         ->where('item',$item)
        //         ->where('invoice_no',$invoice)
        //         ->where('lot_no',$lot)
        //         ->first();
        // if ($this->checkIfExistObject($db) > 0) {
        //     $lot_qty = $db->lot_qty;
        // }

        return $lot_qty;
    }

    public function SamplingPlan(Request $req)
    {
        $size = 0;
        $accept = 0;
        $reject = 0;

        if ($req->soi == 'Normal') {
            if ($req->il == 'S2') {
                if ($req->aql == 0.65) {
                    if ($req->lot_qty <= 20) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }

                    if ($req->lot_qty > 20) {
                        $size = 20;
                        $accept = 0;
                        $reject = 1;
                    }
                }
            }

            if ($req->il == 'S3') {
                if ($req->aql == 0.40) {
                    if ($req->lot_qty >= 200) {
                        $size = 32;
                        $accept = 0;
                        $reject = 1;
                    }

                    if ($req->lot_qty <= 32) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }
                }

                if ($req->aql == 1.00) {
                    if ($req->lot_qty >= 200) {
                        $size = 50;
                        $accept = 1;
                        $reject = 2;
                    }

                    elseif ($req->lot_qty >= 13 && $req->lot_qty <= 199) {
                        $size = 13;
                        $accept = 0;
                        $reject = 1;
                    }
                    elseif ($req->lot_qty <= 12) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }
                   

                }

                if ($req->aql == 0.25) {
                    if ($req->lot_qty <= 80) {
                        $size = $req->lot_qty;
                    }

                    if ($req->lot_qty > 80) {
                        $size = 100;
                    }
                    $accept = 0;
                    $reject = 1;
                }
            }
            if ($req->il == 'II') {

                if ($req->aql == 0.15) {
                    if ($req->lot_qty <= 80) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 81 && $req->lot_qty <= 3200) {
                        $size = 80;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 3;
                        $reject = 4;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 5;
                        $reject = 6;
                    }
                }

                if ($req->aql == 0.40) {
                    if ($req->lot_qty <= 32) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 33 && $req->lot_qty <= 500) {
                        $size = 32;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 501 && $req->lot_qty <= 3200) {
                        $size = 125;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 10000) {
                        $size = 200;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 10001 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 3;
                        $reject = 4;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 5;
                        $reject = 6;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 7;
                        $reject = 8;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 10;
                        $reject = 11;
                    }
                }

                if ($req->aql == 0.10) {
                    if ($req->lot_qty <= 125) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 126 && $req->lot_qty <= 10000) {
                        $size = 125;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 10001 && $req->lot_qty <= 35000) {
                        $size = 500;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 3;
                        $reject = 4;
                    }
                }

                if ($req->aql == 0.04) {
                    if ($req->lot_qty <= 315) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 316 && $req->lot_qty <= 3200) {
                        $size = 315;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 315;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 1250;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 1;
                        $reject = 2;
                    }
                }
            }
        } elseif($req->soi == 'Reduced') {

            if ($req->il == 'S2') {
                if ($req->aql == 0.65) {
                    if ($req->lot_qty <= 31) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }

                    if ($req->lot_qty > 32) {
                        $size = 32;
                        $accept = 0;
                        $reject = 1;
                    }
                }
            }

            if ($req->il == 'S3') {
                if ($req->aql == 0.40) {
                    if ($req->lot_qty <= 12) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }

                    if ($req->lot_qty >= 13) {
                        $size = 13;
                        $accept = 0;
                        $reject = 1;
                    }
                }
                if ($req->aql == 1.00) {
                    if ($req->lot_qty >= 35000) {
                        $size = 5;
                        $accept = 0;
                        $reject = 1;
                    }

                    elseif ($req->lot_qty < 35000 && $req->lot_qty >= 13) {
                        $size = 20;
                        $accept = 1;
                        $reject = 2;
                    }
                    elseif ($req->lot_qty <= 12) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }
                }

                if ($req->aql == 0.25) {
                    if ($req->lot_qty < 80) {
                        $size = 20;
                    }

                    if ($req->lot_qty > 80) {
                        $size = 20;
                    }
                        $accept = 0;
                        $reject = 1;
                }
                        
            }

            if ($req->il == 'II') {
                if ($req->aql == 0.15) {
                    if ($req->lot_qty <= 125) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 126 && $req->lot_qty <= 3200) {
                        $size = 125;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 3;
                        $reject = 4;
                    }
                }

                if ($req->aql == 0.40) {
                    if ($req->lot_qty <= 50) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 51 && $req->lot_qty <= 500) {
                        $size = 50;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 501 && $req->lot_qty <= 10000) {
                        $size = 200;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 10001 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 3;
                        $reject = 4;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 5;
                        $reject = 6;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 8;
                        $reject = 9;
                    }
                }

                if ($req->aql == 0.10) {
                    if ($req->lot_qty <= 200) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 201 && $req->lot_qty <= 10000) {
                        $size = 200;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 10001 && $req->lot_qty <= 35000) {
                        $size = 800;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 800;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 2;
                        $reject = 3;
                    }
                }

                if ($req->aql == 0.04) {
                    if ($req->lot_qty <= 500) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 501 && $req->lot_qty <= 3200) {
                        $size = 500;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 35000) {
                        $size = 500;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 2000;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 2000;
                        $accept = 1;
                        $reject = 2;
                    }
                }
            }
        }
        if ($req->soi == 'Tightened') {
            if ($req->il == 'S2') {
                if ($req->aql == 0.65) {
                    if ($req->lot_qty <= 31) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }

                    if ($req->lot_qty >= 32) {
                        $size = 32;
                        $accept = 0;
                        $reject = 1;
                    }
                }
            }

            if ($req->il == 'S3') {
                if ($req->aql == 0.40) {
                    if ($req->lot_qty >= 51) {
                        $size = 50;
                        $accept = 0;
                        $reject = 1;
                    }

                    elseif ($req->lot_qty <= 50) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    }
                }

                if ($req->aql == 1.00) {
                    if ($req->lot_qty >= 35000) {
                        $size = 20;
                        $accept = 0;
                        $reject = 1;

                    }

                    elseif ($req->lot_qty < 35000 && $req->lot_qty >= 13) {
                        $size = 80;
                        $accept = 1;
                        $reject = 2;

                    }
                    elseif ($req->lot_qty <= 12) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;

                    }
                    
                }

                if ($req->aql == 0.25) {
                    if ($req->lot_qty < 80) {
                        $size = 80;
                    }

                    if ($req->lot_qty > 80) {
                        $size = 80;
                    }
                        $accept = 0;
                        $reject = 1;

                }
            }
            if ($req->il == 'II') {

                if ($req->aql == 0.15) {
                    if ($req->lot_qty <= 80) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 81 && $req->lot_qty <= 3200) {
                        $size = 80;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 3;
                        $reject = 4;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 5;
                        $reject = 6;
                    }
                }

                if ($req->aql == 0.40) {
                    if ($req->lot_qty <= 32) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 33 && $req->lot_qty <= 500) {
                        $size = 32;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 501 && $req->lot_qty <= 3200) {
                        $size = 125;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 10000) {
                        $size = 200;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 10001 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 3;
                        $reject = 4;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 5;
                        $reject = 6;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 7;
                        $reject = 8;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 10;
                        $reject = 11;
                    }
                }

                if ($req->aql == 0.10) {
                    if ($req->lot_qty <= 125) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 126 && $req->lot_qty <= 10000) {
                        $size = 125;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 10001 && $req->lot_qty <= 35000) {
                        $size = 500;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 500;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 800;
                        $accept = 2;
                        $reject = 3;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 3;
                        $reject = 4;
                    }
                }

                if ($req->aql == 0.04) {
                    if ($req->lot_qty <= 315) {
                        $size = $req->lot_qty;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 316 && $req->lot_qty <= 3200) {
                        $size = 315;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 3201 && $req->lot_qty <= 35000) {
                        $size = 315;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 35001 && $req->lot_qty <= 150000) {
                        $size = 315;
                        $accept = 0;
                        $reject = 1;
                    } elseif ($req->lot_qty >= 150001 && $req->lot_qty <= 500000) {
                        $size = 1250;
                        $accept = 1;
                        $reject = 2;
                    } elseif ($req->lot_qty >= 500001) {
                        $size = 1250;
                        $accept = 1;
                        $reject = 2;
                    }
                }
            }
        } 

        return $data = [
            'sample_size' => $size,
            'accept' => $accept,
            'reject' => $reject,
            'date_inspected' => date('Y-m-d'),
            'inspector' =>Auth::user()->user_id,
            //'workweek' =>$this->getWorkWeek()
        ];
    }

    public function getDropdowns()
    {
        $family = $this->com->getDropdownByNameSelect2('Family');
        $tofinspection = $this->com->getDropdownByNameSelect2('Type of Inspection');
        $sofinspection = $this->com->getDropdownByNameSelect2('Severity of Inspection');
        $inspectionlvl = $this->com->getDropdownByNameSelect2('Inspection Level');
        $aql = $this->com->getDropdownByNameSelect2('AQL');
        $shift = $this->com->getDropdownByNameSelect2('Shift');
        $submission = $this->com->getDropdownByNameSelect2('Submission');
        $shift = $this->com->getDropdownByNameSelect2('Shift');
        $mod = $this->com->getDropdownByNameSelect2('Mode of Defect - IQC Inspection');

        $ngr_status = DB::connection($this->mysql)->table('iqc_ngr_master')
                        ->select('description as id', 'description as text')
                        ->where('category','STATUS')
                        ->get();

        $ngr_disposition = DB::connection($this->mysql)->table('iqc_ngr_master')
                            ->select('description as id', 'description as text')
                                ->where('category','DISPOSITION')
                                ->get();
                                
        return $data = [
                    'family' => $family,
                    'tofinspection' => $tofinspection,
                    'sofinspection' => $sofinspection,
                    'inspectionlvl' => $inspectionlvl,
                    'aql' => $aql,
                    'shift' => $shift,
                    'submission' => $submission,
                    'shift' => $shift,
                    'mod' => $mod,
                    'ngr_status' => $ngr_status,
                    'ngr_disposition' => $ngr_disposition,
                ];
    }

    private function checkIfExistObject($object)
    {
       return count( (array)$object);
    }

    private function array_to_object($array)
    {
        return (object) $array;
    }

    public function getWorkWeek_old()
    {
        $yr = 52;
        $apr = date('Y')."-04-01";
        $aprweek = date("W", strtotime($apr));

        $diff = $yr - $aprweek;
        $date = Carbon::now();
        $weeknow = $date->format("W");

        $workweek = $diff + $weeknow + 1;

        if ($workweek > 53) {
            return $data = ['workweek' => $workweek - 53];
        } else {
            return $data = ['workweek' => $workweek];
        }
        
    }

    public function getWorkWeek()
    {
        $apr_date = date('Y')."-04-01";
        $date = Carbon::now();
        $aprweek = DB::connection($this->mysql)->select('select (week (DATE_FORMAT(now(),"%Y-%m-%d")) - week ("'.$apr_date.'")) + 1 as weeknow');
        $workweek = $aprweek[0]->weeknow;
        return $data = ['workweek' => $workweek];
    }

    public function saveModeOfDefectsInspection(Request $req)
    {
        $data = [
            'return_status' => "failed",
            "msg" => "Mode of Defect saving failed."
        ];

        $total_mod_count = $req->current_count + $req->qty;

        if ($total_mod_count > $req->sample_size) {
            $data = [
                'return_status' => "failed",
                "msg" => "Mode of Defect quantity is more than the Sample Size.",
                "count" => $total_mod_count
            ];
        } else {
            if ($req->status == 'ADD') {
                //$lot_no = (is_array($req->lot_no))? implode(',',$req->lot_no) : $req->lot_no;
                // $query = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                //             ->insert([
                //                 'iqc_id' => $req->iqc_id,
                //                 'invoice_no' => $req->invoiceno,
                //                 'partcode' => $req->item,
                //                 'mod' => $req->mod,
                //                 'qty' => $req->qty,
                //                 'lot_no' => $lot_no,
                //                 'created_at' => date('Y-m-d H:i:s'),
                //                 'updated_at' => date('Y-m-d H:i:s'),
                //             ]);
                DB::beginTransaction();
                try{
                    $values = array();
                    for($i = 0; $i <= count($req->mod_of_defects) - 1; $i++){


                        // $exist =  DB::connection($this->mysql)
                        //             ->table('iqc_lot_no')
                        //             ->where('lot_no', $req->lot_no_data[$i]['lot_no'])
                        //             ->first();

                        // if(!$exist){
                            array_push(
                                $values,
                                array(
                                    'iqc_id' => $req->iqc_id,
                                    'invoice_no' => $req->invoiceno,
                                    'partcode' => $req->item,
                                    'mod' => $req->mod_of_defects[$i]['mode_of_defect'],
                                    'qty' => $req->mod_of_defects[$i]['quantity'],
                                    'lot_no' => $req->mod_of_defects[$i]['selected_lot'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                )
                            );
                        // }                
                    
                    }     
                    $query = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                        ->insert($values);
                    DB::commit();
                }catch(\Exception $err){
                    DB::rollback();
                }           

            } else {
                $query = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                            ->where('id',$req->id)
                            ->update([
                                'mod' => $req->mod,
                                'qty' => $req->qty,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
            }


            if ($query == true) {
                $data = [
                    'return_status' => "success",
                    "msg" => "Mode of Defect successfully saved.",
                    "count" => $total_mod_count
                ];
            }
        } 

        return $data;
    }

    public function deleteModeOfDefectsInspection(Request $req)
    {
        $data = [
            'return_status' => "failed",
            "msg" => "Mode of Defect deleting failed."
        ];

        $query = false;

        foreach ($req->id as $key => $id) {
            $delete = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                        ->where('id',$id)
                        ->delete();
            if ($delete == true) {
                $query = true;
            }
        }


        if ($query == true) {
            $data = [
                'return_status' => "success",
                "msg" => "Mode of Defect successfully deleted."
            ];
        }

        return $data;
    }

    public function getModeOfDefectsInspection(Request $req)
    {
        $test = $req->all();
        try {
			if (is_array($req->lot_no)) {
				$lot_no = implode(',',$req->lot_no);
			} else {
				$lot_no = $req->lot_no;
			}
            
            $db = [];

			$sql = "select distinct m.id as id,
						m.invoice_no as invoice_no,
						m.partcode as partcode,
						m.lot_no as lot_no,
						m.mod,
						m.qty,
						m.created_at,
						m.iqc_id
					from iqc_inspections as i
					join tbl_mod_iqc_inspection as m
					on i.invoice_no = m.invoice_no
					and i.partcode = m.partcode
					and m.iqc_id=".$req->iqc_id;

            $count = DB::connection($this->mysql)->select($sql);

            if (count($count) > 0) {
                $db = DB::connection($this->mysql)->select($sql);
            } else {
				$db = DB::connection($this->mysql)
						->select("select distinct m.id as id,
									i.invoice_no as invoice_no,
									i.partcode as partcode,
									i.lot_no as lot_no,
									m.mod,
									m.qty,
									m.created_at,
									m.iqc_id
								from iqc_inspections as i
								join tbl_mod_iqc_inspection as m
								on i.invoice_no = m.invoice_no
								and i.partcode = m.partcode
								where m.invoice_no = '".$req->invoiceno."'
								and (m.lot_no = '".$lot_no."' or i.lot_no = '".$lot_no."')
								and (i.partcode = '".$req->item."' or m.partcode = '".$req->item."')");

				if (count($db) < 1) {
					$db = DB::connection($this->mysql)
							->select("select distinct m.id as id,
										i.invoice_no as invoice_no,
										i.partcode as partcode,
										i.lot_no as lot_no,
										m.mod,
										m.qty,
										m.created_at,
										m.iqc_id
									from iqc_inspections as i
									join tbl_mod_iqc_inspection as m
									on i.invoice_no = m.invoice_no
									and i.partcode = m.partcode
									where m.invoice_no = '".$req->invoiceno."'
									and (i.partcode = '".$req->item."' or m.partcode = '".$req->item."')");
					if (count($db) < 1) {
						$db = DB::connection($this->mysql)
								->select("select distinct m.id as id,
											i.invoice_no as invoice_no,
											i.partcode as partcode,
											i.lot_no as lot_no,
											m.mod,
											m.qty,
											m.created_at,
											m.iqc_id
										from iqc_inspections as i
										join tbl_mod_iqc_inspection as m
										on i.invoice_no = m.invoice_no
										and i.lot_no = m.lot_no
										where m.invoice_no = '".$req->invoiceno."'
										and m.lot_no = '".$lot_no."'
										and (i.partcode = '".$req->item."' or m.partcode = '".$req->item."')");
					} else {
						$count = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                            ->where('invoice_no',$req->invoiceno)
                            ->where('partcode',$req->item)
                            ->where('lot_no',$lot_no)
                            ->count();

						if ($count > 0) {
							$db = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
									->where('invoice_no',$req->invoiceno)
									->where('partcode',$req->item)
									->where('lot_no',$lot_no)
									->get();
						}
					}
				}
            }

            // if (count($db) > 0) {
            //     foreach ($db as $key => $d) {
			// 		if (is_null($d->iqc_id) && empty($d->iqc_id) && $d->iqc_id == 0) {
			// 			DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
			// 				->where('id',$d->id)
			// 				->update([
			// 					'iqc_id' => $req->iqc_id
			// 				]);
			// 		}

			// 		if (is_null($d->partcode) && empty($d->partcode)) {
			// 			DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
			// 				->where('id',$d->id)
			// 				->update([
			// 					'partcode' => $req->item
			// 				]);
			// 		}
            //     }

			// 	$cnt = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
			// 				->where('iqc_id',$req->iqc_id)->count();

			// 	if ($cnt > 0) {
			// 		$db = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
			// 				->where('iqc_id',$req->iqc_id)->get();

			// 		foreach ($db as $key => $dx) {
			// 			if (is_null($dx->partcode) || empty($dx->partcode)) {
			// 				DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
			// 					->where('id',$dx->id)
			// 					->update([
			// 						'partcode' => $req->item
			// 					]);
			// 			}
			// 		}
			// 	}
            // }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        

        return $db;
    }

    public function getIQCData(Request $req)
    {
        $from_cond = '';
        $to_cond = '';
        $item_cond ='';

        if(empty($req->item))
        {
            $item_cond ='';
        } else {
            $item_cond = " AND i.partcode = '" . $req->item . "'";
        }

        if (!empty($req->from) && !empty($req->to)) {
            $from_cond = "AND i.date_ispected BETWEEN '" . $req->from . "' AND '" . $req->to . "'";
        } else {
            $from_cond = '';
            $to_cond = '';
        }

        try {
            $sql_data = DB::connection($this->mysql)->table(DB::raw('iqc_inspections as i'))
                            ->select([
                                DB::raw("i.id as id"), DB::raw("i.invoice_no as invoice_no"), DB::raw("i.partcode as partcode"), DB::raw("i.partname as partname"), 
                                DB::raw("i.supplier as supplier"), DB::raw("i.app_date as app_date"), DB::raw("i.app_time as app_time"), DB::raw("i.app_no as app_no"), 
                                DB::raw("i.lot_no as lot_no"), DB::raw("i.lot_qty as lot_qty"), DB::raw("i.type_of_inspection as type_of_inspection"), DB::raw("i.severity_of_inspection as severity_of_inspection"), 
                                DB::raw("i.inspection_lvl as inspection_lvl"), DB::raw("i.aql as aql"), DB::raw("i.accept as accept"), DB::raw("i.reject as reject"), DB::raw("i.date_ispected as date_ispected"),
                                DB::raw("i.ww as ww"), DB::raw("i.fy as fy"), DB::raw("i.time_ins_from as time_ins_from"), DB::raw("i.time_ins_to as time_ins_to"), 
                                DB::raw("i.shift as shift"), DB::raw("i.inspector as inspector"), DB::raw("i.submission as submission"), DB::raw("i.judgement as judgement"), 
                                DB::raw("i.classification as classification"), DB::raw("i.family as family"), DB::raw("i.lot_inspected as lot_inspected"), DB::raw("i.lot_accepted as lot_accepted"), DB::raw("i.sample_size as sample_size"), 
                                DB::raw("i.no_of_defects as no_of_defects"), DB::raw("i.remarks as remarks"), DB::raw("ngr.id as ngr_status_id"), DB::raw("ngr.description as ngr_status"), DB::raw("i.ngr_disposition as ngr_disposition"),
                                DB::raw("i.ngr_control_no as ngr_control_no"), DB::raw("DATE_FORMAT(i.ngr_issued_date,'%Y-%m-%d') as ngr_issued_date"), DB::raw("i.inv_id as inv_id"), DB::raw("i.mr_id as mr_id"), DB::raw("i.updated_at as updated_at")
                            ])
                            ->leftJoin('iqc_ngr_master as ngr', 'ngr.id', '=', 'i.ngr_status')
                            // ->whereRaw("i.judgement <> 'On-going' AND i.with_dispo = 0 AND (is_deleted is null or is_deleted = 0) ".$item_cond.$from_cond.$to_cond);
                            ->whereRaw("i.judgement <> 'On-going' AND (is_deleted is null or is_deleted = 0) ".$item_cond.$from_cond.$to_cond);

            return Datatables::of($sql_data)->make(true);

        } catch (\Throwable $th) {
            $json_data = [
                'error' => $th
            ];

            return json_encode($json_data);
        }
    }

    public function getOngoing(Request $req)
    {
        try {

            $sql_data = DB::connection($this->mysql)->table('iqc_inspections')
                            ->select([
                                'id', 'invoice_no', 'partcode', 'partname', 
                                'supplier', 'app_date', 'app_time', 'app_no', 
                                'lot_no', 'lot_qty', 'type_of_inspection', 'severity_of_inspection', 
                                'inspection_lvl', 'aql', 'accept', 'reject', 'date_ispected',
                                'ww', 'fy', 'time_ins_from', 'time_ins_to', 
                                'shift', 'inspector', 'submission', 'judgement', 
                                'classification', 'family', 'lot_inspected', 'lot_accepted', 'sample_size', 
                                'no_of_defects', 'remarks', 'inv_id', 'mr_id', 'created_at'
                            ])
                            ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                            ->where('judgement','=','On-going');
                            

            return Datatables::of($sql_data)->addColumn('action', function ($data) { return ''; })->make(true);
        } catch (\Throwable $th) {
            return json_encode($th);
        }
    }

    public function deleteOnGoing(Request $req)
    {
        $data = [
            'return_status' => "failed",
            "msg" => "On-going data deleting failed."
        ];

        $query = false;
        $inv_ids = [];
        $mr_ids = [];

        try {
            DB::beginTransaction();

            foreach ($req->id as $key => $id) {
                $iqc = DB::connection($this->mysql)->table('iqc_inspections')
                            ->where('id',$id)
                            ->select('id','inv_id','mr_id','app_no','lot_no')
                            ->first();
    
                if (count((array)$iqc) > 0) {
                    //$lot_nos = explode(',', $iqc->lot_no);
                    $inv_ids = explode(',', $iqc->inv_id);
                    $mr_ids = explode(',', $iqc->mr_id);
    
                    $table = "tbl_wbs_local_receiving_batch";
    
                    if (Str::contains($iqc->app_no, 'MAT')) {
                        $table = "tbl_wbs_material_receiving_batch";
                    }
    
                    foreach ($inv_ids as $inv_key => $inv_id) {
                        $checkInv = DB::connection($this->wbs)->table("tbl_wbs_inventory")
                                        ->where('id', $inv_id)
                                        ->update([
                                            'for_kitting' => 0,
                                            'iqc_status' => 0,
                                            'iqc_result' => '',
                                            'judgement' => '',
                                            'ins_date' => '',
                                            'ins_time' => '',
                                            'ins_by' => Auth::user()->user_id,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'update_user' => Auth::user()->user_id,
                                            'update_pg' => "IQC INSPECTION - Delete On-going function",
                                            'qc_remarks' => $req->remarks,
                                            'needed_whs_update' => 1
                                        ]);
                        $checkBatch = DB::connection($this->wbs)->table($table)
                                        ->where('id', $mr_ids[$inv_key])
                                        ->update([
                                            'for_kitting' => 0,
                                            'iqc_status' => 0,
                                            'iqc_result' => '',
                                            'judgement' => '',
                                            'ins_date' => '',
                                            'ins_time' => '',
                                            'ins_by' => Auth::user()->user_id,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'update_user' => Auth::user()->user_id,
                                            // 'qc_remarks' => $req->remarks,
                                            // 'needed_whs_update' => 1
                                        ]);
    
                        $checkBatch = true;
                        
                    }
    
                    // foreach ($lot_nos as $key => $lot) {
                    //     $checkInv = DB::connection($this->wbs)->update(
                    //                         "UPDATE tbl_wbs_inventory SET iqc_status='0'
                    //                         WHERE invoice_no='".$iqc->invoice_no."' AND item='".$iqc->partcode."' AND lot_no='".$lot."'"
                    //                     );
                                        
    
                    //     $checkBatch = DB::connection($this->wbs)->update(
                    //                         "UPDATE tbl_wbs_material_receiving_batch SET iqc_status='0'
                    //                         WHERE invoice_no='".$iqc->invoice_no."' AND item='".$iqc->partcode."' AND lot_no='".$lot."'"
                    //                     );
    
                    //     $checkBatch = DB::connection($this->wbs)->update(
                    //                         "UPDATE tbl_wbs_local_receiving_batch SET iqc_status='0'
                    //                         WHERE invoice_no='".$iqc->invoice_no."' AND item='".$iqc->partcode."' AND lot_no='".$lot."'"
                    //                     );
    
                    //     $checkBatch = true;
                    // }
                    if ($checkBatch == true) {
                        $delete = DB::connection($this->mysql)->table('iqc_inspections')
                                    ->where('id',$id)
                                    ->delete();
                        $query = true;
                    }
                    //$query = true;
                }
            }
    
            if ($query == true) {
                DB::commit();
                // Fire event here
                event(new NotifyReceiving($mr_ids));
                $data = [
                    'return_status' => "success",
                    "msg" => "Inspection data successfully deleted."
                ];
            }
    
        } catch (\Exception $th) {
            DB::rollback();
            $data = [
                'return_status' => "error",
                "msg" => $th->getMessage()
            ];
        }

        
        return $data;
    }

    public function deleteIQCInspection(Request $req)
    {
        $data = [
            'return_status' => "failed",
            "msg" => "Inspection data deleting failed."
        ];

        $query = false;

        foreach ($req->id as $key => $id) {
            $iqc = DB::connection($this->mysql)->table('iqc_inspections')
                        ->where('id',$id)
                        ->select('id','inv_id','mr_id','app_no')
                        ->first();

            if (count((array)$iqc) > 0) {
                //$lot_nos = explode(',', $iqc->lot_no);
                $inv_ids = explode(',', $iqc->inv_id);
                $mr_ids = explode(',', $iqc->mr_id);

                $table = "tbl_wbs_local_receiving_batch";

                if (Str::contains($iqc->app_no, 'MAT')) {
                    $table = "tbl_wbs_material_receiving_batch";
                }

                foreach ($inv_ids as $key => $inv_id) {
                    $checkInv = DB::connection($this->wbs)->table("tbl_wbs_inventory")
                                    ->where('id', $inv_id)
                                    ->update([
                                        'for_kitting' => 0,
                                        'iqc_status' => 0,
                                        'iqc_result' => '',
                                        'judgement' => '',
                                        'ins_date' => '',
                                        'ins_time' => '',
                                        'ins_by' => '',
                                        'updated_at' => date('Y-m-d H:i:s'),
                                        'update_user' => Auth::user()->user_id,
                                        'update_pg' => "IQC INSPECTION - Delete function"
                                    ]);
                    $checkBatch = DB::connection($this->wbs)->table($table)
                                    ->where('id', $mr_ids[$key])
                                    ->update([
                                        'for_kitting' => 0,
                                        'iqc_status' => 0,
                                        'iqc_result' => '',
                                        'judgement' => '',
                                        'ins_date' => '',
                                        'ins_time' => '',
                                        'ins_by' => ''
                                    ]);

                    $checkBatch = true;
                }
                if ($checkBatch == true) {
                    $delete = DB::connection($this->mysql)->table('iqc_inspections')
                                ->where('id',$id)
                                ->update([
                                    'is_deleted' => 1,
                                    'deleted_at' => date('Y-m-d H:i:s'),
                                    'delete_user' => Auth::user()->user_id
                                ]);
                    $query = true;
                }
                //$query = true;
            }

        }

        // foreach ($req->id as $key => $id) {
        //     $delete = DB::connection($this->mysql)->table('iqc_inspections')
        //                 ->where('id',$id)
        //                 ->delete();
        //     if ($delete == true) {
        //         $query = true;
        //     }
        // }


        if ($query == true) {
            $data = [
                'return_status' => "success",
                "msg" => "Inspection data successfully deleted."
            ];
        }

        return $data;
    }

    public function getItemsSearch(Request $req)
    {
        $results = [];
        $val = (!isset($req->q))? "" : $req->q;
        $id = (!isset($req->id))? "" : $req->id;
        $text = (!isset($req->text))? "" : $req->text;
        $table = (!isset($req->table))? "" : $req->table;
        $condition = (!isset($req->condition))? "" : $req->condition;
        $isDistinct = (!isset($req->isDistinct))? "" : $req->isDistinct;
        $display = (!isset($req->display))? "" : $req->display;
        $addOptionVal = (!isset($req->addOptionVal))? "" : $req->addOptionVal;
        $addOptionText = (!isset($req->addOptionText))? "" : $req->addOptionText;
        $sql_query = (!isset($req->sql_query))? "" : $req->sql_query;
        $orderBy = (!isset($req->orderBy))? "" : $req->orderBy;
        $search = "";

        try {
            if ($addOptionVal != "" && $display == "id&text") {
                array_push($results, [
                    'id' => $addOptionVal,
                    'text' => $addOptionText
                ]);
            }

            if ($sql_query == null || $sql_query == "") {
                if (!empty($val) && !is_null($val)) {
                    $search = " AND partcode LIKE '%".$val."%'";
                }
                $sql_query = "SELECT DISTINCT partcode as id, partcode as `text`
                                FROM iqc_inspections WHERE (is_deleted is null or is_deleted = 0) ".$search;
            }
            
            $db = DB::connection($this->mysql)->select($sql_query);

            foreach ($db as $key => $d) {
                array_push($results, [
                    'id' => $d->id,
                    'text' => $d->text
                ]);
            }

        } catch(\Exception $e) {
            return [
                'success' => false,
                'msessage' => $e->getMessage()
            ];
        }
        
        return $results;
    }

    public function searchInspection(Request $req)
    {
        $from_cond = '';
        $to_cond = '';
        $item_cond ='';

        if(empty($req->item))
        {
            $item_cond ='';
        } else {
            $item_cond = " AND partcode = '" . $req->item . "'";
        }

        if (!empty($req->from) && !empty($req->to)) {
            $from_cond = "AND date_ispected BETWEEN '" . $req->from . "' AND '" . $req->to . "'";
        } else {
            $from_cond = '';
            $to_cond = '';
        }

        $data = DB::connection($this->mysql)->table('iqc_inspections')
                    ->whereRaw(" (is_deleted is null or is_deleted = 0) ".$item_cond.$from_cond.$to_cond)
                    ->get();
        return $data;
    }

    public function searchHistory(Request $req)
    {
        $from_cond = '';
        $to_cond = '';
        $item_cond ='';
        $lot_cond = '';
        $judge_cond = '';

        if(empty($req->item))
        {
            $item_cond ='';
        } else {
            $item_cond = " AND partcode = '" . $req->item . "'";
        }

        if (!empty($req->from) && !empty($req->to)) {
            $from_cond = "AND date_ispected BETWEEN '" . $req->from . "' AND '" . $req->to . "'";
        } else {
            $from_cond = '';
            $to_cond = '';
        }

        if(empty($req->lotno))
        {
            $lot_cond ='';
        } else {
            $lot_cond = " AND lot_no = '" . $req->lotno . "'";
        }

        if(empty($req->judgement))
        {
            $judge_cond ='';
        } else {
            $judge_cond = " AND judgement = '" . $req->judgement . "'";
        }

        $data = DB::connection($this->mysql)->table('iqc_inspections_history')
                    ->whereRaw(" 1=1 ".$item_cond.$lot_cond.$judge_cond.$from_cond.$to_cond)
                    ->get();
        return $data;
    }

    //REQUALIFICATION
    public function getItemsRequalification()
    {
        $db = DB::connection($this->mysql)->table('iqc_inspections')
                ->select('partcode as id','partcode as text')
                ->where('judgement','Accepted')
                ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                ->distinct()
                ->get();
        if ($this->checkIfExistObject($db) > 0) {
            return $db;
        }
    }

    public function getAppNoRequalification(Request $req)
    {
        $db = DB::connection($this->mysql)->table('iqc_inspections')
                ->select('app_no as id','app_no as text')
                ->where('judgement','Accepted')
                ->where('partcode',$req->item)
                ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                ->distinct()
                ->get();

        if ($this->checkIfExistObject($db) > 0) {
            return $db;
        }
    }

    public function getDetailsRequalification(Request $req)
    {
        $db = DB::connection($this->mysql)->table('iqc_inspections')
                ->where('judgement','Accepted')
                ->where('partcode',$req->item)
                ->where('app_no',$req->app_no)
                ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                ->select('partname','supplier','app_date','app_time','lot_qty')
                ->distinct()
                ->first();

        $lots = DB::connection($this->mysql)->table('iqc_inspections')
                ->where('judgement','Accepted')
                ->where('partcode',$req->item)
                ->where('app_no',$req->app_no)
                ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                ->select('lot_no')
                ->get();

        if ($this->checkIfExistObject($db) > 0 || $this->checkIfExistObject($lots) > 0) {
            $arr = [];
            foreach ($lots as $key => $lot) {
                $arr = explode(',',$lot->lot_no);
            }
            $lotnos = [];
            $lotval = [];
            foreach ($arr as $key => $x) {
                $object = json_decode(json_encode(['id'=>$x,'text'=>$x]), FALSE);
                array_push($lotnos,$object);
                array_push($lotval,$x);
            }
            return $data = [
                'details' => $db,
                'lots' => $lotnos,
                'lotval' => $lotval
            ];
        }
    }

    public function calculateLotQtyRequalification(Request $req)
    {
        $lot_qty = 0;
        if (empty($req->lot_no)) {
            return $lot_qty;
        } else {
            foreach ($req->lot_no as $key => $lot) {
                $db = DB::connection($this->wbs)->table('tbl_wbs_inventory')
                        ->select('qty as lot_qty')
                        ->where('item',$req->item)
                        ->where('wbs_mr_id',$req->app_no)
                        ->where('lot_no',$lot)
                        ->first();
                if ($this->checkIfExistObject($db) > 0) {
                    $lot_qty = $lot_qty + $db->lot_qty;
                }
            }
            return $lot_qty;
        }
    }

    public function visualInspectionRequalification(Request $req)
    {
        $db = DB::connection($this->mysql)->table('iqc_inspections')
                ->where('judgement','Accepted')
                ->where('partcode',$req->item)
                ->where('app_no',$req->app_no)
                ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                ->get();

        if ($this->checkIfExistObject($db) > 0) {
            return $db;
        }
    }

    public function saveRequalification(Request $req)
    {
        $data = [
            'return_status' => 'failed',
            'msg' => "Saving Failed."
        ];
        $query = false;
        $status = 0;
        $kitting = 0;

        if ($req->judgement == 'Accepted') {
            $status = 1;
            $kitting = 1;
        } else {
            $status = 2;
            $kitting = 0;
        }

        if ($req->save_status == 'ADD') {
            DB::connection($this->mysql)->table('iqc_inspections_rq')
                ->insert([
                    'ctrl_no_rq' => $req->ctrlno,
                    'partcode_rq' => $req->partcode,
                    'partname_rq' => $req->partname,
                    'supplier_rq' => $req->supplier,
                    'app_date_rq' => $req->app_date,
                    'app_time_rq' => $req->app_time,
                    'app_no_rq' => $req->app_no,
                    'lot_no_rq' =>$req->lot_no,
                    'lot_qty_rq' => $req->lot_qty,
                    'date_ispected_rq' => $req->date_inspected,
                    'ww_rq' => $req->ww,
                    'fy_rq' => $req->fy,
                    'shift_rq' => $req->shift,
                    'time_ins_from_rq' => $req->time_ins_from,
                    'time_ins_to_rq' => $req->time_ins_to,
                    'inspector_rq' => $req->inspector,
                    'submission_rq' => $req->submission,
                    'judgement_rq' => $req->judgement,
                    'lot_inspected_rq' => $req->lot_inspected,
                    'lot_accepted_rq' => $req->lot_accepted,
                    'no_of_defects_rq' => $req->no_of_defects,
                    'remarks_rq' => $req->remarks,
                    'dbcon_rq' => Auth::user()->productline,
                    'created_at' => Carbon::now(),
                ]);
            if (is_string($req->lot_no)) {
                $lots = explode(',',$req->lot_no);
                $this->requalifyInventory($req->app_no,$req->partcode,$lots);
            } else {
                $this->requalifyInventory($req->app_no,$req->partcode,$req->lot_no);
            }

            $query = true;

        } else {
            DB::connection($this->mysql)->table('iqc_inspections_rq')
                ->where('id',$req->id)
                ->update([
                    'partcode_rq' => $req->partcode,
                    'partname_rq' => $req->partname,
                    'supplier_rq' => $req->supplier,
                    'app_date_rq' => $req->app_date,
                    'app_time_rq' => $req->app_time,
                    'app_no_rq' => $req->app_no,
                    'lot_no_rq' => $req->lot_no,
                    'lot_qty_rq' => $req->lot_qty,
                    'date_ispected_rq' => $req->date_inspected,
                    'ww_rq' => $req->ww,
                    'fy_rq' => $req->fy,
                    'shift_rq' => $req->shift,
                    'time_ins_from_rq' => $req->time_ins_from,
                    'time_ins_to_rq' => $req->time_ins_to,
                    'inspector_rq' => $req->inspector,
                    'submission_rq' => $req->submission,
                    'judgement_rq' => $req->judgement,
                    'lot_inspected_rq' => $req->lot_inspected,
                    'lot_accepted_rq' => $req->lot_accepted,
                    'no_of_defects_rq' => $req->no_of_defects,
                    'remarks_rq' => $req->remarks,
                    'dbcon_rq' => Auth::user()->productline,
                    'updated_at' => Carbon::now(),
                ]);

                if (is_string($req->lot_no)) {
                    $lots = explode(',',$req->lot_no);
                    $this->requalifyInventory($req->app_no,$req->partcode,$lots);
                } else {
                    $this->requalifyInventory($req->app_no,$req->partcode,$req->lot_no);
                }

            $query = true;
        }

        if ($query) {
            $data = [
                'return_status' => 'success',
                'msg' => "Successfully Saved."
            ];
        }

        return $data;
    }

    public function getRequaliData(Request $req)
    {
        return DB::connection($this->mysql)->table('iqc_inspections_rq')
                    ->take($req->row)
                    ->get();
    }

    public function deleteRequalification(Request $req)
    {
        $data = [
            'return_status' => "failed",
            "msg" => "Re-qualified data deleting failed."
        ];

        $query = false;

        foreach ($req->id as $key => $id) {
            $delete = DB::connection($this->mysql)->table('iqc_inspections_rq')
                        ->where('id',$id)
                        ->delete();
            if ($delete == true) {
                $query = true;
            }
        }


        if ($query == true) {
            $data = [
                'return_status' => "success",
                "msg" => "Re-qualified data successfully deleted."
            ];
        }

        return $data;
    }

    public function getmodeOfDefectsRequaliData(Request $req)
    {
        $db = DB::connection($this->mysql)->table('tbl_mod_iqc_rq')
                ->where('partcode',$req->item)
                ->get();
        if ($this->checkIfExistObject($db) > 0) {
            return $db;
        }
    }

    public function saveModRequalification(Request $req)
    {

        $test = $req->all();
        $data = [
            'return_status' => "failed",
            "msg" => "Mode of Defect saving failed."
        ];

        if ($req->status == 'ADD') {
            $query = DB::connection($this->mysql)->table('tbl_mod_iqc_rq')
                        ->insert([
                            'partcode' => $req->item,
                            'mod' => $req->mod,
                            'qty' => $req->qty,
                            'created_at' => Carbon::now(),
                        ]);
        } else {
            $query = DB::connection($this->mysql)->table('tbl_mod_iqc_rq')
                        ->where('id',$req->id)
                        ->update([
                            'mod' => $req->mod,
                            'qty' => $req->qty,
                            'updated_at' => Carbon::now(),
                        ]);
        }


        if ($query == true) {
            $data = [
                'return_status' => "success",
                "msg" => "Mode of Defect successfully saved."
            ];
        }

        return $data;
    }

    public function deleteModRequalification(Request $req)
    {
        $data = [
            'return_status' => "failed",
            "msg" => "Mode of Defect deleting failed."
        ];

        $query = false;

        foreach ($req->id as $key => $id) {
            $delete = DB::connection($this->mysql)->table('tbl_mod_iqc_rq')
                        ->where('id',$id)
                        ->delete();
            if ($delete == true) {
                $query = true;
            }
        }


        if ($query == true) {
            $data = [
                'return_status' => "success",
                "msg" => "Mode of Defect successfully deleted."
            ];
        }

        return $data;
    }

    //GROUP BY
    public function getGroupbyContent(Request $req)
    {
        if (!empty($req->field)) {
            $db = DB::connection($this->mysql)->table('iqc_inspections')
                    ->select($req->field.' as id',$req->field.' as text')
                    ->whereRaw(DB::raw("(is_deleted is null or is_deleted = 0)"))
                    ->distinct()
                    ->get();
            if ($this->checkIfExistObject($db) > 0) {
                return $db;
            }
        }
    }

    public function getGroupByTable(Request $req)
    {
        return $this->IQCDatatableQuery($req,false);
    }

    public function getInspectionByDate(Request $req)
    {
        $date_inspected = '';

        if (!empty($req->from) && !empty($req->to)) {
            $date_inspected = "date_ispected BETWEEN '".$req->from."' AND '".$req->to."'";
        }

        $db = DB::connection($this->mysql)
                ->select("SELECT *
                        FROM iqc_inspections
                        WHERE (is_deleted is null or is_deleted = 0) ".$date_inspected);

        if ($this->checkIfExistObject($db) > 0) {
            return $db;
        }
    }

    private function IQCDatatableQuery($req,$join)
    {
        $g1 = ''; $g2 = ''; $g3 = '';
        $g1c = ''; $g2c = ''; $g3c = '';
        $date_inspected = '';
        $groupBy = [];

        // wheres
        if (!empty($req->from) && !empty($req->to)) {
            $date_inspected = "date_ispected BETWEEN '".$req->from."' AND '".$req->to."'";
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
                ->select("SELECT SUM(sample_size) AS sample_size,
                                SUM(lot_qty) AS lot_qty,
                                SUM(no_of_defects) AS no_of_defects,
                                SUM(lot_accepted) AS lot_accepted,
                                SUM(lot_inspected) AS lot_inspected,
                                supplier, app_date, date_ispected, judgement,
                                time_ins_from, time_ins_to, app_no, fy, ww, submission,
                                partcode, partname, lot_no, aql
                        FROM iqc_inspections
                        WHERE (is_deleted is null or is_deleted = 0) ".$date_inspected.$g1c.$g2c.$g3c.$grby);
        } else {

            $db = DB::connection($this->mysql)
                ->select("SELECT a.invoice_no,a.partcode,a.partname,a.supplier,a.app_date,
                                a.app_time,a.app_no,a.lot_no,a.lot_qty,a.type_of_inspection,a.severity_of_inspection,
                                a.inspection_lvl,a.aql,a.accept,a.reject,a.date_ispected,a.ww,a.fy,a.shift,
                                a.time_ins_from,a.time_ins_to,a.inspector,a.submission,a.judgement,a.lot_inspected,
                                a.lot_accepted,a.sample_size,a.no_of_defects,a.remarks,b.mod
                        FROM iqc_inspections as a
                        LEFT JOIN tbl_mod_iqc_inspection as b ON a.invoice_no = b.invoice_no
                        WHERE (a.is_deleted is null or a.is_deleted = 0) ".$date_inspected.$g1c.$g2c.$g3c.$grby);
        }
        

        if ($this->checkIfExistObject($db) > 0) {
            return $db;
        }
    }

    //IQC INSPECTION RESULT PDF REPORT - UPDATE SOURCE CODE (JUNE-03-2022)
    public function getIQCreport(Request $req)
    {
        $dt = Carbon::now();
        $company_info = $this->com->getCompanyInfo();
        $date = substr($dt->format('  M j, Y  h:i A '), 2);

        $from = (!isset($req->gfrom) || $req->gfrom == '' || $req->gfrom == null)? '': $this->com->convertDate($req->gfrom,'Y-m-d');
        $to = (!isset($req->gto) || $req->gto == '' || $req->gto == null)? '': $this->com->convertDate($req->gto,'Y-m-d');                                                            

        $header = DB::connection($this->mysql)
                        ->table('iqc_inspection_group')
                        ->select('partcode',
                                'partname',
                                'supplier',
                                'type_of_inspection',
                                'severity_of_inspection',
                                'inspection_lvl',
                                'aql',
                                'accept',
                                'reject')
                        ->distinct()
                        ->get();

        $data = [
            'company_info' => $company_info,
            'header' => $header,
            'conn' => $this->mysql,
            'from' => $from,
            'to' => $to,
            'date' => $date,
        ];

        $pdf = PDF::loadView('pdf.iqc', $data)
                    ->setPaper('A4')
                    ->setOption('margin-top', 10)
                    ->setOption('margin-bottom', 5)
                    ->setOption('margin-left', 1)
                    ->setOption('margin-right', 1)
                    ->setOrientation('landscape');

        return $pdf->inline('IQC_Inspection_'.Carbon::now());
    }


     //IQC INSPECTION RESULT EXCEL REPORT - UPDATE SOURCE CODE (JUNE-03-2022)
    public function getIQCreportexcel(Request $req)
    {

     //   dd($req->all());
        $dt = Carbon::now();
        $date = substr($dt->format('Ymd'), 2); 
        
        
        Excel::create('IQC_Inspection_Report'.$date, function($excel) use($dt, $date, $req)
        {                                        
            
            $com_info = $this->com->getCompanyInfo();
            $from = (!isset($req->gfrom) || $req->gfrom == '' || $req->gfrom == null)? '': $this->com->convertDate($req->gfrom,'Y-m-d');
            $to = (!isset($req->gto) || $req->gto == '' || $req->gto == null)? '': $this->com->convertDate($req->gto,'Y-m-d');                                                            

            $per_sheet = DB::connection($this->mysql)
                        ->table('iqc_inspection_group')
                        ->select('partcode',
                                'partname',
                                'supplier',
                                'type_of_inspection',
                                'severity_of_inspection',
                                'inspection_lvl',
                                'aql',
                                'accept',
                                'reject')
                        ->distinct()
                        ->get();
            
            foreach($per_sheet as $key => $psheet){

                $excel->sheet(($psheet->partcode . "-" . $key), function($sheet) use($com_info, $to, $from, $dt, $psheet, $req){   

                    $sheet->setFreeze('A12');
                    $sheet->setWidth('A', 5);                      
                    $date = substr($dt->format('  M j, Y  h:i A '), 2);

                    $sheet->setHeight(1, 15);
                    $sheet->mergeCells('A1:W1');
                    $sheet->cells('A1:W1', function($cells) use($com_info) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cell('A1',$com_info['name']);

                    $sheet->setHeight(2, 15);
                    $sheet->mergeCells('A2:W2');
                    $sheet->cells('A2:W2', function($cells) use($com_info) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cell('A2',$com_info['address']);

                    $sheet->setHeight(4, 20);
                    $sheet->mergeCells('A4:W4');
                    $sheet->cells('A4:W4', function($cells) {
                        $cells->setAlignment('center');
                        $cells->setFont([
                            'family'     => 'Arial',
                            'size'       => '14',
                            'bold'       =>  true,
                            'underline'  =>  true
                        ]);
                    });
                    
                    $sheet->cell('A4',"INSPECTION RESULT RECORD");

                    //CELL FOR LABELS
                    $sheet->mergeCells('B7:C8');
                    $sheet->cell('B7', function($cell) {
                        $cell->setValue("PART NAME");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });
                    
                    $sheet->mergeCells('B9:C9');
                    $sheet->cell('B9', function($cell) {
                        $cell->setValue("PARTCODE");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('B10:C10');
                    $sheet->cell('B10', function($cell) {
                        $cell->setValue("SUPPLIER");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('I7:K7');
                    $sheet->cell('I7', function($cell) {
                        $cell->setValue("TYPE OF INSPECTION");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('I8:K8');
                    $sheet->cell('I8', function($cell) {
                        $cell->setValue("SEVERITY OF INSPECTION");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('I9:K9');
                    $sheet->cell('I9', function($cell) {
                        $cell->setValue("INSPECTION LEVEL");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('I10:K10');
                    $sheet->cell('I10', function($cell) {
                        $cell->setValue("AQL");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('Q7:R7');
                    $sheet->cell('Q7', function($cell) {
                        $cell->setValue("Ac");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('Q8:R8');
                    $sheet->cell('Q8', function($cell) {
                        $cell->setValue("Re");                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('Q9:R9');
                    $sheet->cell('Q9', function($cell) {                            
                        $cell->setBorder('thin','thin','thin','thin');                            
                    });

                    $sheet->mergeCells('Q10:R10');
                    $sheet->cell('Q10', function($cell) {                            
                        $cell->setBorder('thin','thin','thin','thin');                            
                    });

                    $sheet->mergeCells('S9:W9');
                    $sheet->cell('S9', function($cell) {                            
                        $cell->setBorder('thin','thin','thin','thin');                            
                    });

                    $sheet->mergeCells('S10:W10');
                    $sheet->cell('S10', function($cell) {                            
                        $cell->setBorder('thin','thin','thin','thin');                            
                    });
                    

                    //HEADER
                    $sheet->cell('B11', function($cell) {
                        $cell->setValue("FY-WW");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->cell('C11', function($cell) {
                        $cell->setValue("DATE INSPECTED");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->cell('D11', function($cell) {
                        $cell->setValue("SHIFT");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });
                        
                    $sheet->cell('E11', function($cell) {
                        $cell->setValue("FROM");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->cell('F11', function($cell) {
                        $cell->setValue("TO");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->cell('G11', function($cell) {
                        $cell->setValue("NO. OF SUB");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->mergeCells('H11:I11');
                    $sheet->cell('H11', function($cell) {
                        $cell->setValue("APPLICATION CONTROL NO.");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->mergeCells('J11:K11');
                    $sheet->cell('J11', function($cell) {
                        $cell->setValue("INVOICE NO.");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    }); 
                    
                    $sheet->mergeCells('L11:M11');
                    $sheet->cell('L11', function($cell) {
                        $cell->setValue("LOT NUMBER");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });  

                    $sheet->cell('N11', function($cell) {
                        $cell->setValue("QUANTITY");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });  

                    $sheet->cell('O11', function($cell) {
                        $cell->setValue("LOT SIZE");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    }); 

                    $sheet->cell('P11', function($cell) {
                        $cell->setValue("SAMPLE SIZE( n )");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    }); 

                    $sheet->cell('Q11', function($cell) {
                        $cell->setValue("NO. OF DEFECTIVES");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });    
                    
                    $sheet->mergeCells('R11:S11');
                    $sheet->cell('R11', function($cell) {
                        $cell->setValue("MODE OF DEFECTS");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });
                    
                    $sheet->cell('T11', function($cell) {
                        $cell->setValue("QUANTITY");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });  
                                            
                    $sheet->cell('U11', function($cell) {
                        $cell->setValue("DETERMINATION ON LOT ACCEPTABILITY");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });
                    
                    $sheet->cell('V11', function($cell) {
                        $cell->setValue("INSPECTOR");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });

                    $sheet->cell('W11', function($cell) {
                        $cell->setValue("REMARKS");                        
                        $cell->setValignment('center');  
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '10',
                            'bold'       =>  false,                                
                        ]);                            
                    });  
                        
                    //CELLS POPULATE FROM DATABASE
                    
                    $sheet->mergeCells('D7:H8');
                    $sheet->cell('D7', function($cell) use($psheet){
                        $cell->setValue($psheet->partname);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('D9:H9');
                    $sheet->cell('D9', function($cell) use($psheet){
                        $cell->setValue($psheet->partcode);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('D10:H10');
                    $sheet->cell('D10', function($cell) use($psheet){
                        $cell->setValue($psheet->supplier);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('L7:P7');
                    $sheet->cell('L7', function($cell) use($psheet){
                        $cell->setValue($psheet->type_of_inspection);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('L8:P8');
                    $sheet->cell('L8', function($cell) use($psheet){
                        $cell->setValue($psheet->severity_of_inspection);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('L9:P9');
                    $sheet->cell('L9', function($cell) use($psheet){
                        $cell->setValue($psheet->inspection_lvl);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('L10:P10');
                    $sheet->cell('L10', function($cell) use($psheet){
                        $cell->setValue($psheet->aql);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('S7:W7');
                    $sheet->cell('S7', function($cell) use($psheet){
                        $cell->setValue($psheet->accept);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    });

                    $sheet->mergeCells('S8:W8');
                    $sheet->cell('S8', function($cell) use($psheet){
                        $cell->setValue($psheet->reject);                        
                        $cell->setValignment('center');
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setFont([
                            'family'     => 'Arial',
                            'size'       => '12',
                            'bold'       =>  false,
                        ]);
                    }); 

                    $findMe = "'";
                    $final_supplier = "";
                    $final_partcode = "";
                    $pos1 = strpos($psheet->supplier, $findMe);
                    $pos2 = strpos($psheet->partcode, $findMe);

                    if($pos1 == ""){
                        $final_supplier = $psheet->supplier;
                    }else{
                        $final_supplier = substr($psheet->supplier, 0, $pos1 );
                    }

                    if($pos2 == ""){
                        $final_partcode = $psheet->partcode;
                    }else{
                        $final_partcode = substr($psheet->partcode, 0, $pos2 );
                    }

                    $sql = "SELECT id, fy, partcode, ww, date_ispected, 
                                    shift, time_ins_from, time_ins_to, 
                                    submission, app_no, invoice_no, lot_no, 
                                    lot_qty, sample_size, no_of_defects, 
                                    judgement, inspector, remarks
                            FROM iqc_inspections
                            WHERE supplier LIKE '%" .$final_supplier. "%'
                            AND partcode LIKE '%" .$final_partcode. "%'
                            AND date_ispected BETWEEN '" .$from. "' AND '" .$to. "'
                            AND is_deleted = 0";
                                            
                    $details = DB::connection($this->mysql)->select($sql);

                    $row = 12;
                
                    foreach ($details as $key => $d){

                        $lotRow = $row;
                        $lot_qtyRow = $row;
                        $modeRow = $row;
    
                        $sheet->cell('B'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->fy. ' - ' .$d->ww);
                        });

                        $sheet->cell('C'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->date_ispected);
                        });

                        $sheet->cell('D'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->shift);
                        });

                        $sheet->cell('E'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->time_ins_from);
                        });  

                        $sheet->cell('F'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->time_ins_to);
                        });

                        $sheet->cell('G'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->submission);
                        });

                        $sheet->cell('H'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->app_no);
                        });

                        $sheet->cell('J'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->invoice_no);
                        });

                        $sheet->cell('O'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->lot_qty);
                        });

                        $sheet->cell('P'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->sample_size);
                        });

                        $sheet->cell('Q'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->no_of_defects);
                        });

                        $sheet->cell('U'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->judgement);
                        });  

                        $sheet->cell('V'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->inspector);
                        });

                        $sheet->cell('W'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValue($d->remarks);                     
                        });                                                           
            
                        // SELECT LOT NO

                        $sql = "SELECT lot_no AS lot_no 
                                FROM iqc_inspections
                                WHERE id = '" .$d->id. "'
                                AND is_deleted = 0";

                        $lot_no = DB::connection($this->mysql)->select($sql);
                        
                        $lot_arr = [];
                        // ITERATE LOT NO
                        foreach ($lot_no as $key => $lot){ 
                            $lot_exploded = explode(',' , $lot->lot_no); //magiging array

                            foreach ($lot_exploded as $key => $lot_val) {
                                array_push($lot_arr,$lot_val);
                            }
                            
                        }

                        $lot_arr = array_unique($lot_arr);

                        $lot_qtySql = "SELECT DISTINCT(inv_id), qty
                                        FROM iqc_lot_no 
                                        WHERE iqc_id = '" .$d->id. "' 
                                        AND is_deleted = 0";


                        $lot_qty = DB::connection($this->mysql)->select($lot_qtySql);

                        $qty_count = count($lot_qty);
                        // foreach($lot_qty as $key => $qty_val){
                        //     array_push($lot_qty_arr, $qty_val->qty);
                        // }

                        foreach ($lot_arr as $key => $lv) {
                            $sheet->mergeCells('L'.$lotRow.':M'.$lotRow);                                                                                                           
                            $sheet->cell('L'.$lotRow, function($cell) use($lv){
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValue($lv);
                            });  
                            $lotRow++;
                        }

                        if($qty_count > 0){
                            if($qty_count == count($lot_arr)){
                                foreach ($lot_qty as $key => $qty) {
                                    $sheet->cell('N'.$lot_qtyRow, function($cell) use($qty){
                                        $cell->setValignment('center');
                                        $cell->setAlignment('center');
                                        $cell->setBorder('thin','thin','thin','thin');
                                        $cell->setValue($qty->qty);
                                    });
                                    $lot_qtyRow++;
                                }
                            }else{
                                $sheet->cell('N'.$row, function($cell) use($d){
                                    $cell->setValignment('center');
                                    $cell->setAlignment('center');
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setValue($d->lot_qty);
                                });
                            }
                        }else{
                            $sheet->cell('N'.$row, function($cell) use($d){
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValue($d->lot_qty);
                            });
                        }
                           
                        

                        $mode_of_defects = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                                                                ->select('mod', 'qty')
                                                                ->where('iqc_id', $d->id);

                        $check_count = $mode_of_defects->count();

                        if ($check_count > 0) {
                            foreach($mode_of_defects->get() as $key => $mod){
                                $sheet->cell('R'.$modeRow, function($cell) use($mod){
                                    $cell->setValignment('center');
                                    $cell->setAlignment('center');
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setValue($mod->mod);
                                });                                                                                                   
                                $sheet->cell('T'.$modeRow, function($cell) use($mod){
                                    $cell->setValignment('center');
                                    $cell->setAlignment('center');
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setValue($mod->qty);
                                });
                                $modeRow++;
                            }
                        }else{
                            $mode_of_defects = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                                                ->select('mod','qty')
                                                ->where('invoice_no', $d->invoice_no)
                                                ->where('partcode', $d->partcode)
                                                ->where('lot_no', $d->lot_no)
                                                ->get();
                            if(count($mode_of_defects) == 0){
                                $sheet->cell('R'.$modeRow, function($cell){
                                    $cell->setValignment('center');
                                    $cell->setAlignment('center');
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setValue("NDF");
                                });
                                $sheet->cell('T'.$modeRow, function($cell){
                                    $cell->setValignment('center');
                                    $cell->setAlignment('center');
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setValue("0");
                                });
                                $modeRow++;
                            }else{
                                foreach($mode_of_defects as $key => $mod){
                                    $sheet->cell('R'.$modeRow, function($cell) use($mod){
                                        $cell->setValignment('center');
                                        $cell->setAlignment('center');
                                        $cell->setBorder('thin','thin','thin','thin');
                                        $cell->setValue($mod->mod);
                                    });                                                                                                   
                                    $sheet->cell('T'.$modeRow, function($cell) use($mod){
                                        $cell->setValignment('center');
                                        $cell->setAlignment('center');
                                        $cell->setBorder('thin','thin','thin','thin');
                                        $cell->setValue($mod->qty);
                                    });
                                    $modeRow++;
                                }
                            }
                        }   

                        $finalRow = max([$lotRow, $modeRow, $lot_qtyRow]);
                        $mergeRow = $finalRow - 1;
                        $md = count($mode_of_defects);


                        if($modeRow == $row){

                            $sheet->mergeCells('R'.$row.':S'.$modeRow); 
                            $sheet->mergeCells('T'.$row.':T'.$modeRow); 

                        }else if($modeRow > $row){

                            $sheet->mergeCells('R'.$row.':S'.$mergeRow); 
                            $sheet->mergeCells('T'.$row.':T'.$mergeRow); 

                        }

                        if($finalRow === $row){

                            $row++;

                        }else{

                            $sheet->mergeCells('B'.$row.':B'.$mergeRow);
                            $sheet->mergeCells('C'.$row.':C'.$mergeRow); 
                            $sheet->mergeCells('D'.$row.':D'.$mergeRow); 
                            $sheet->mergeCells('E'.$row.':E'.$mergeRow); 
                            $sheet->mergeCells('F'.$row.':F'.$mergeRow); 
                            $sheet->mergeCells('G'.$row.':G'.$mergeRow); 
                            $sheet->mergeCells('H'.$row.':I'.$mergeRow);  
                            $sheet->mergeCells('J'.$row.':K'.$mergeRow); 
                            if($qty_count != count($lot_arr)){
                                $sheet->mergeCells('N'.$row.':N'.$mergeRow); 
                            }
                            $sheet->mergeCells('O'.$row.':O'.$mergeRow); 
                            $sheet->mergeCells('P'.$row.':P'.$mergeRow); 
                            $sheet->mergeCells('Q'.$row.':Q'.$mergeRow); 
                            $sheet->mergeCells('U'.$row.':U'.$mergeRow); 
                            $sheet->mergeCells('V'.$row.':V'.$mergeRow); 
                            $sheet->mergeCells('W'.$row.':W'.$mergeRow); 

                            $row = $finalRow;
                        }
                    }                       
                });
            }
        })->download('xls');
    }

    //IQC INSPECTION SUMMARY EXCEL REPORT - UPDATE SOURCE CODE (JUNE-03-2022)
    public function getIQCSummaryReportExcel(Request $req){

        $dt = Carbon::now();
        $date = substr($dt->format('Ymd'), 2); 

        Excel::create('IQC_Inspection_Summary_Report'.$date, function($excel) use($dt, $date, $req)
        {                                        
            
            $com_info = $this->com->getCompanyInfo();
            $from = (!isset($req->gfrom) || $req->gfrom == '' || $req->gfrom == null)? '': $this->com->convertDate($req->gfrom,'Y-m-d');
            $to = (!isset($req->gto) || $req->gto == '' || $req->gto == null)? '': $this->com->convertDate($req->gto,'Y-m-d');                                                            

            $excel->sheet(('IQC_INSPECTION_SUMMARY_REPORT'), function($sheet) use($com_info, $to, $from, $dt, $req){   

                $sheet->setFreeze('A7');
                $sheet->setWidth('A', 5);                      
                $date = substr($dt->format('  M j, Y  h:i A '), 2);

                $sheet->setHeight(1, 15);
                $sheet->mergeCells('A1:AC1');
                $sheet->cells('A1:C1', function($cells) use($com_info) {
                    $cells->setAlignment('center');
                    $cells->setValignment('center'); 
                });
                $sheet->cell('A1',$com_info['name']);

                $sheet->setHeight(2, 15);
                $sheet->mergeCells('A2:AC2');
                $sheet->cells('A2:AC2', function($cells) use($com_info) {
                    $cells->setAlignment('center');
                    $cells->setValignment('center'); 
                });
                $sheet->cell('A2',$com_info['address']);

                $sheet->setHeight(4, 20);
                $sheet->mergeCells('A4:AC4');
                $sheet->cells('A4:AC4', function($cells) {
                    $cells->setAlignment('center');
                    $cells->setValignment('center'); 
                    $cells->setFont([
                        'family'     => 'Arial',
                        'size'       => '14',
                        'bold'       =>  true,
                        'underline'  =>  true
                    ]);
                });
                
                $sheet->cell('A4',"IQC INSPECTION SUMMARY");


                //HEADER
                $sheet->cell('B6', function($cell) {
                    $cell->setValue("Invoice No."); 
                    $cell->setAlignment('center');
                    $cell->setValignment('center');
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('C6', function($cell) {
                    $cell->setValue("Part Code");
                    $cell->setAlignment('center');                      
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('D6', function($cell) {
                    $cell->setValue("Part Name");  
                    $cell->setAlignment('center');                      
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });
                    
                $sheet->cell('E6', function($cell) {
                    $cell->setValue("Supplier");        
                    $cell->setAlignment('center');                
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('F6', function($cell) {
                    $cell->setValue("App. Date");   
                    $cell->setAlignment('center');                     
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('G6', function($cell) {
                    $cell->setValue("App. Time"); 
                    $cell->setAlignment('center');                       
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('H6', function($cell) {
                    $cell->setValue("App No.");     
                    $cell->setAlignment('center');                   
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('I6', function($cell) {
                    $cell->setValue("Lot No.");    
                    $cell->setAlignment('center');                   
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                }); 
                
                $sheet->cell('J6', function($cell) {
                    $cell->setValue("Lot Qty.");      
                    $cell->setAlignment('center');                  
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  

                $sheet->cell('K6', function($cell) {
                    $cell->setValue("Type of Inspection");  
                    $cell->setAlignment('center');                      
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  
                
                $sheet->cell('L6', function($cell) {
                    $cell->setValue("Severity of Inspection");     
                    $cell->setAlignment('center');                   
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  

                $sheet->cell('M6', function($cell) {
                    $cell->setValue("Inspection Level");       
                    $cell->setAlignment('center');                 
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                }); 

                $sheet->cell('N6', function($cell) {
                    $cell->setValue("AQL");                    
                    $cell->setAlignment('center');    
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  

                $sheet->cell('O6', function($cell) {
                    $cell->setValue("Accept");                
                    $cell->setAlignment('center');        
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                }); 

                $sheet->cell('P6', function($cell) {
                    $cell->setValue("Reject");               
                    $cell->setAlignment('center');         
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                }); 

                $sheet->cell('Q6', function($cell) {
                    $cell->setValue("Date Inspected");    
                    $cell->setAlignment('center');                    
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });    
                
                $sheet->cell('R6', function($cell) {
                    $cell->setValue("FY-WW");             
                    $cell->setAlignment('center');           
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('S6', function($cell) {
                    $cell->setValue("Shift");              
                    $cell->setAlignment('center');          
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });
                
                $sheet->cell('T6', function($cell) {
                    $cell->setValue("Time Inspected");  
                    $cell->setAlignment('center');                      
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  
                                        
                
                $sheet->cell('U6', function($cell) {
                    $cell->setValue("Inspector");         
                    $cell->setAlignment('center');               
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('V6', function($cell) {
                    $cell->setValue("Submission");         
                    $cell->setAlignment('center');               
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  

                $sheet->cell('W6', function($cell) {
                    $cell->setValue("Judgement");         
                    $cell->setAlignment('center');               
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  

                $sheet->cell('X6', function($cell) {
                    $cell->setValue("Lot Inspected");      
                    $cell->setAlignment('center');                  
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });  

                $sheet->cell('Y6', function($cell) {
                    $cell->setValue("Lot Accepted");      
                    $cell->setAlignment('center');                  
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('Z6', function($cell) {
                    $cell->setValue("Sample Size");                        
                    $cell->setAlignment('center');
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('AA6', function($cell) {
                    $cell->setValue("No. of Defects");        
                    $cell->setAlignment('center');                
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('AB6', function($cell) {
                    $cell->setValue("Remarks");              
                    $cell->setAlignment('center');          
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                $sheet->cell('AC6', function($cell) {
                    $cell->setValue("Classification");        
                    $cell->setAlignment('center');                
                    $cell->setValignment('center');  
                    $cell->setBorder('thin','thin','thin','thin');
                    $cell->setFont([
                        'family'     => 'Arial',
                        'size'       => '10',
                        'bold'       =>  true,                                
                    ]);                            
                });

                //SELECT IQC INSPECTIONS GROUP IN ACCORDANCE TO PART CODE
               $sql_group = "SELECT partcode, partname, supplier, type_of_inspection, severity_of_inspection, 
                                    inspection_lvl, aql, accept, reject
                            FROM iqc_inspection_group 
                            GROUP BY partcode, partname, supplier, type_of_inspection, severity_of_inspection, 
                                    inspection_lvl, aql, accept, reject";

                $inspection_group = DB::connection($this->mysql)->select($sql_group);
                                       

                $row = 7;

                // foreach($inspection_group as $key => $data){

                //     $findMe = "'";
                //     $final_supplier = "";

                //     $pos1 = strpos($data->supplier, $findMe);

                //     if($pos1 == ""){
                //         $final_supplier = $data->supplier;
                //     }else{
                //         $final_supplier = substr($data->supplier, 0 , $pos1);
                //     }

                    //SELECT IQC INSPECTIONS IN ACCORDANCE TO PART CODE
                    $sql = "SELECT invoice_no, partcode, partname, supplier, app_date, app_time, app_no, lot_no, lot_qty, type_of_inspection, 
                                    severity_of_inspection, inspection_lvl, aql, accept, reject, date_ispected, ww, fy, shift,
                                    time_ins_from, time_ins_to, inspector, submission, judgement, lot_inspected, lot_accepted, sample_size, 
                                    no_of_defects, remarks, classification, is_deleted
                            FROM iqc_inspection_group";
                            // WHERE supplier LIKE '%" .$final_supplier. "%'
                            // AND partcode = '" .$data->partcode. "'
                            // AND type_of_inspection = '" .$data->type_of_inspection. "'
                            // AND severity_of_inspection = '" .$data->severity_of_inspection. "'
                            // AND inspection_lvl = '" .$data->inspection_lvl. "'
                            // AND aql = '" .$data->aql. "'
                            // AND accept =  '" .$data->accept. "'
                            // AND reject = '" .$data->reject. "'
                            // AND date_ispected BETWEEN '" .$from. "' AND '" .$to. "'
                            // AND (judgement = 'Accepted' or judgement = 'Rejected') 
                            // AND is_deleted = 0
                            // GROUP BY invoice_no, partcode, partname, supplier, app_date, app_time, app_no, lot_no, lot_qty, type_of_inspection, 
                            // severity_of_inspection, inspection_lvl, aql, accept, reject, date_ispected, ww, fy, shift,
                            // time_ins_from, time_ins_to, inspector, submission, judgement, lot_inspected, lot_accepted, sample_size, 
                            // no_of_defects, remarks, classification, is_deleted";
                                            
                    $details = DB::connection($this->mysql)->select($sql);

                  
                    foreach ($details as $key => $d){

                        $sheet->cell('B'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->invoice_no);
                        });

                        $sheet->cell('C'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->partcode);
                        });

                        $sheet->cell('D'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->partname);
                        });

                        $sheet->cell('E'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->supplier);
                        });  

                        $sheet->cell('F'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->app_date);
                        });

                        $sheet->cell('G'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->app_time);
                        });

                        $sheet->cell('H'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->app_no);
                        });

                        $sheet->cell('I'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->lot_no);
                        });

                        $sheet->cell('J'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->lot_qty);
                        });

                        $sheet->cell('K'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->type_of_inspection);
                        });

                        $sheet->cell('L'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->severity_of_inspection);
                        });

                        $sheet->cell('M'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->inspection_lvl);
                        });

                        $sheet->cell('N'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->aql);
                        });  

                        $sheet->cell('O'.$row, function($cell) use($d){
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->accept);
                        });

                        $sheet->cell('P'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->reject);                     
                        });     
                        
                        $sheet->cell('Q'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->date_ispected);                     
                        });    

                        $sheet->cell('R'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->fy. ' - ' .$d->ww);                     
                        });    

                        $sheet->cell('S'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->shift);                     
                        });    

                        $sheet->cell('T'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->time_ins_from. ' - ' .$d->time_ins_to);                     
                        });    

                        $sheet->cell('U'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->inspector);                     
                        });    

                        $sheet->cell('V'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->submission);                     
                        });    

                        $sheet->cell('W'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->judgement);                     
                        });    

                        $sheet->cell('X'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->lot_inspected);                     
                        });    

                        $sheet->cell('Y'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->lot_accepted);                     
                        });    

                        $sheet->cell('Z'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->sample_size);                     
                        });    

                        $sheet->cell('AA'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->no_of_defects);                     
                        });    

                        $sheet->cell('AB'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->remarks);                     
                        });    

                        $sheet->cell('AC'.$row, function($cell) use($d){                   
                            $cell->setValignment('center');
                            $cell->setAlignment('left');
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setFont([
                                'family'     => 'Calibri',
                                'size'       => '10',
                            ]);
                            $cell->setValue($d->classification);                     
                        });    
            
                        $sheet->setWidth([
                            'A' => 3,
                            'B' => 20,
                            'C' => 10,
                            'D' => 20,
                            'E' => 20,
                            'F' => 10,
                            'G' => 8.5,
                            'H' => 15,
                            'I' => 20,
                            'J' => 9,
                            'K' => 15,
                            'L' => 18,
                            'M' => 13,
                            'N' => 5,
                            'O' => 6,
                            'P' => 6,
                            'Q' => 12,
                            'R' => 8,
                            'S' => 6,
                            'T' => 13,
                            'U' => 12,
                            'V' => 10,
                            'W' => 9,
                            'X' => 11,
                            'Y' => 11,
                            'Z' => 9.5,
                            'AA' => 11.5,
                            'AB' => 50,
                            'AC' => 20,
                            'AD' => 3,
                        ]);

                        $row++;
                        
                    }      
                    
               // }                 
            });
        })->download('xls');

    }
    


    public function uploadfiles(Request $req)
    {
        $inspection_data = $req->file('inspection_data');
        $inspection_mod = $req->file('inspection_mod');
        $requali_data = $req->file('requali_data');
        $requali_mod = $req->file('requali_mod');

        $data = [
            'return_status' => 'failed',
            'msg' => 'Upload was unsuccessful.'
        ];

        $process = false;

        if (isset($inspection_data)) {
            $this->uploadInspection($inspection_data);
            $process = true;
        }

        if (isset($inspection_mod)) {
            $this->uploadInspectionMod($inspection_mod);
            $process = true;
        }

        if (isset($requali_data)) {
            $process = true;
        }

        if (isset($requali_mod)) {
            $process = true;
        }

        if ($process == true) {
            $data = [
                'return_status' => 'success',
                'msg' => 'Data were successfully uploaded.'
            ];
        }

        return $data;
    }

    private function uploadInsepectionInsert($field)
    {
        $status = 0;
        $kitting = 0;

        if ($field['judgement'] == 'Accepted') {
            $status = 1;
            $kitting = 1;
        } else {
            $status = 2;
            $kitting = 0;
        }

        $lot_qty = $this->getLotQty($field['invoice_no'],$field['partcode'],$field['lot_no']);
        
        DB::connection($this->mysql)->table('iqc_inspections')
            ->insert([
                'invoice_no' => $field['invoice_no'],
                'partcode' => $field['partcode'],
                'partname' => $field['partname'],
                'supplier' => $field['supplier'],
                'app_date' => $field['app_date'],
                'app_time' => $field['app_time'],
                'app_no' => $field['app_no'],
                'lot_no' => $field['lot_no'],
                'lot_qty' => $lot_qty,
                'type_of_inspection' => $field['type_of_inspection'],
                'severity_of_inspection' => $field['severity_of_inspection'],
                'inspection_lvl' => $field['inspection_lvl'],
                'aql' => $field['aql'],
                'accept' => $field['accept'],
                'reject' => $field['reject'],
                'date_ispected' => $field['date_inspected'],
                'ww' => $field['ww'],
                'fy' => $field['fy'],
                'shift' => $field['shift'],
                'time_ins_from' => $field['time_inspection_from'],
                'time_ins_to' => $field['time_inspection_to'],
                'inspector' => $field['inspector'],
                'submission' => $field['submission'],
                'judgement' => $field['judgement'],
                'lot_inspected' => $field['lot_inspected'],
                'lot_accepted' => $field['lot_accepted'],
                'sample_size' => $field['sample_size'],
                'no_of_defects' => $field['no_of_defects'],
                'remarks' => $field['remarks'],
                'dbcon' => Auth::user()->productline,
                'created_at' => Carbon::now(),
            ]);

        DB::connection($this->mysql)->table('iqc_inspections_history')
                ->insert([
                    'invoice_no' => $field['invoice_no'],
                    'partcode' => $field['partcode'],
                    'partname' => $field['partname'],
                    'supplier' => $field['supplier'],
                    'app_date' => $field['app_date'],
                    'app_time' => $field['app_time'],
                    'app_no' => $field['app_no'],
                    'lot_no' => $field['lot_no'],
                    'lot_qty' => $lot_qty,
                    'type_of_inspection' => $field['type_of_inspection'],
                    'severity_of_inspection' => $field['severity_of_inspection'],
                    'inspection_lvl' => $field['inspection_lvl'],
                    'aql' => $field['aql'],
                    'accept' => $field['accept'],
                    'reject' => $field['reject'],
                    'date_ispected' => $field['date_inspected'],
                    'ww' => $field['ww'],
                    'fy' => $field['fy'],
                    'shift' => $field['shift'],
                    'time_ins_from' => $field['time_inspection_from'],
                    'time_ins_to' => $field['time_inspection_to'],
                    'inspector' => $field['inspector'],
                    'submission' => $field['submission'],
                    'judgement' => $field['judgement'],
                    'lot_inspected' => $field['lot_inspected'],
                    'lot_accepted' => $field['lot_accepted'],
                    'sample_size' => $field['sample_size'],
                    'no_of_defects' => $field['no_of_defects'],
                    'remarks' => $field['remarks'],
                    'dbcon' => Auth::user()->productline,
                    'created_at' => Carbon::now(),
                ]);

        DB::connection($this->wbs)->table('tbl_wbs_material_receiving_batch')
            ->where('invoice_no', $field['invoice_no'])
            ->where('wbs_mr_id', $field['app_no'])
            ->where('item', $field['partcode'])
            ->where('lot_no', $field['lot_no'])
            ->update([
                'iqc_status' => $status,
                'for_kitting' => $kitting,
                'iqc_result' => $field['remarks']
            ]);

        DB::connection($this->wbs)->table('tbl_wbs_inventory')
            ->where('invoice_no', $field['invoice_no'])
            ->where('wbs_mr_id', $field['app_no'])
            ->where('item', $field['partcode'])
            ->where('lot_no', $field['lot_no'])
            ->update([
                'iqc_status' => $status,
                'for_kitting' => $kitting,
                'iqc_result' => $field['remarks']
            ]);
    }

    private function uploadInspection($inspection_data)
    {
        Excel::load($inspection_data, function($reader) {

            $results = $reader->get();
            $fields = $results->toArray();

            foreach ($fields as $key => $field) {
                if ($this->ItemInspectionExists($field['invoice_no'],$field['partcode'],$field['lot_no']) < 1) {
                    $this->uploadInsepectionInsert($field);
                }
            }
        });
    }

    private function uploadInspectionMod($inspection_mod)
    {
        Excel::load($inspection_mod, function($reader) {

            $results = $reader->get();
            $fields = $results->toArray();

            foreach ($fields as $key => $field) {
                if ($this->ItemInspectionModExists($field['invoice_no'],$field['partcode'],$field['lot_no'],$field['mod']) < 1) {
                    $this->insertInspectionMod($field);
                } else {
                    $this->updateInspectionMod($field);
                }
            }
        });
    }

    private function insertInspectionMod($field)
    {
        DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
            ->insert([
                'invoice_no' => $field['invoice_no'],
                'partcode' => $field['partcode'],
                'mod' => $field['mod'],
                'qty' => $field['qty'],
                'lot_no' => $field['lot_no'],
                'created_at' => Carbon::now(),
            ]);
    }

    private function updateInspectionMod($field)
    {
        $oldmod = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                    ->select('qty')
                    ->where('invoice_no', $field['invoice_no'])
                    ->where('partcode', $field['partcode'])
                    ->where('mod', $field['mod'])
                    ->where('lot_no', $field['lot_no'])
                    ->first();

        $newqty = $oldmod + $field['qty'];

        DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
            ->where('invoice_no', $field['invoice_no'])
            ->where('partcode', $field['partcode'])
            ->where('mod', $field['mod'])
            ->where('lot_no', $field['lot_no'])
            ->update([
                'qty' => $newqty,
                'updated_at' => Carbon::now(),
            ]);
    }

    private function ItemInspectionExists($invoiceno,$partcode,$lotno)
    {
        $cnt = DB::connection($this->mysql)->table('iqc_inspections')
                    ->where('invoice_no',$invoiceno)
                    ->where('partcode',$partcode)
                    ->where('lot_no','like','%'.$lotno.'%')
                    ->count();
        return $cnt;
    }

    private function ItemInspectionModExists($invoiceno,$partcode,$lotno,$mod)
    {
        $cnt = DB::connection($this->mysql)->table('tbl_mod_iqc_inspection')
                    ->where('invoice_no',$invoiceno)
                    ->where('partcode',$partcode)
                    ->where('lot_no',$lotno)
                    ->where('mod',$mod)
                    ->count();
        return $cnt;
    }

    public function postSaveSortingData(Request $req) 
    {
        $data = [
            'status' => 'failed',
            'msg' => 'Saving Sorting data was failed.',
            'sort_data' => []
        ];

        try {
            $data_count = count($req->sorting_data);
            $ok_count = 0;
            $iqc_id = 0;

            foreach ($req->sorting_data as $key => $sorting_data) {
                if ($sorting_data['id'] == '' && $sorting_data['id'] == null) {
                    $query = DB::connection($this->mysql)->table('iqc_disposition_sorting')
                                ->insert([
                                    'lot_no' => $sorting_data['lot_no'],
                                    'total_qty' => $sorting_data['total_qty'],
                                    'good_qty' => $sorting_data['good_qty'],
                                    'ng_qty' => $sorting_data['ng_qty'],
                                    'actual_qty' => $sorting_data['actual_qty'],
                                    'remarks' => $sorting_data['remarks'],
                                    'mr_id' => $sorting_data['mr_id'],
                                    'inv_id' => $sorting_data['inv_id'],
                                    'iqc_id' => $sorting_data['iqc_id'],
                                    'category' => $sorting_data['category'],
                                    'disposal_date' => $sorting_data['disposal_date'],
                                    'disposal_slip_no' => $sorting_data['disposal_slip_no'],
                                    'ngr_control_no' => $sorting_data['ngr_control_no'],
                                    'packinglist_no' => $sorting_data['packinglist_no'],
                                    'create_user' => Auth::user()->user_id,
                                    'update_user' => Auth::user()->user_id,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                    if ($query) {
                        $ok_count++;
                    }
                } else {
                    $query = DB::connection($this->mysql)->table('iqc_disposition_sorting')
                                ->where('id', $sorting_data['id'])
                                ->update([
                                    'lot_no' => $sorting_data['lot_no'],
                                    'total_qty' => $sorting_data['total_qty'],
                                    'good_qty' => $sorting_data['good_qty'],
                                    'ng_qty' => $sorting_data['ng_qty'],
                                    'actual_qty' => $sorting_data['actual_qty'],
                                    'remarks' => $sorting_data['remarks'],
                                    'mr_id' => $sorting_data['mr_id'],
                                    'inv_id' => $sorting_data['inv_id'],
                                    'iqc_id' => $sorting_data['iqc_id'],
                                    'category' => $sorting_data['category'],
                                    'disposal_date' => $sorting_data['disposal_date'],
                                    'disposal_slip_no' => $sorting_data['disposal_slip_no'],
                                    'ngr_control_no' => $sorting_data['ngr_control_no'],
                                    'packinglist_no' => $sorting_data['packinglist_no'],
                                    'update_user' => Auth::user()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                    if ($query) {
                        $ok_count++;
                    }
                }

                $iqc_id = $sorting_data['iqc_id'];
            }

            if ($ok_count == $data_count) {
                    $sort_data = DB::connection($this->mysql)->table('iqc_disposition_sorting')
                                    ->where('iqc_id', $iqc_id)->get();
                    $data = [
                        'status' => 'success',
                        'msg' => 'Saving Sorting data was successful.',
                        'sort_data' => $sort_data
                    ];
                }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'sort_data' => []
            ];
        }

        return $data;
    }

    public function getSortingData(Request $req)
    {
        $query = "";
        $sort_data = [];

        try {
            $query = "SELECT * FROM iqc_disposition_sorting WHERE iqc_id = ".$req->iqc_id;
            $sort_data = DB::connection($this->mysql)->select($query);

            return $sort_data;
        } catch (\Exception $e) {
            return [
                    'status' => 'success',
                    'msg' => 'Saving Sorting data was successful.',
                    'sort_data' => $sort_data
                ];
        }
        
    }

    public function postDeleteSortingData(Request $req)
    {
        $data = [
            'status' => 'failed',
            'msg' => 'Deleting Sorting data was failed.',
            'sort_data' => []
        ];

        try {
            $query = DB::connection($this->mysql)->table('iqc_disposition_sorting')
                        ->whereIn('id', $req->ids)->delete();
            if ($query) {
                $sort_data = DB::connection($this->mysql)->table('iqc_disposition_sorting')
                                ->where('iqc_id', $req->iqc_id)->get();
                $data = [
                    'status' => 'success',
                    'msg' => 'Deleting Sorting data was successful.',
                    'sort_data' => $sort_data
                ];
            }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'sort_data' => []
            ];
        }

        return $data;
    }

    public function postSaveReworkData(Request $req) 
    {
        $data = [
            'status' => 'failed',
            'msg' => 'Saving Rework data was failed.',
            'rework_data' => []
        ];

        try {
            $data_count = count($req->rework_data);
            $ok_count = 0;
            $iqc_id = 0;

            foreach ($req->rework_data as $key => $rework_data) {
                if ($rework_data['id'] == '' && $rework_data['id'] == null) {
                    $query = DB::connection($this->mysql)->table('iqc_disposition_rework')
                                ->insert([
                                    'lot_no' => $rework_data['lot_no'],
                                    'total_qty' => $rework_data['total_qty'],
                                    'good_qty' => $rework_data['good_qty'],
                                    'ng_qty' => $rework_data['ng_qty'],
                                    'actual_qty' => $rework_data['actual_qty'],
                                    'remarks' => $rework_data['remarks'],
                                    'mr_id' => $rework_data['mr_id'],
                                    'inv_id' => $rework_data['inv_id'],
                                    'iqc_id' => $rework_data['iqc_id'],
                                    'category' => $rework_data['category'],
                                    'disposal_date' => $rework_data['disposal_date'],
                                    'disposal_slip_no' => $rework_data['disposal_slip_no'],
                                    'ngr_control_no' => $rework_data['ngr_control_no'],
                                    'packinglist_no' => $rework_data['packinglist_no'],
                                    'create_user' => Auth::user()->user_id,
                                    'update_user' => Auth::user()->user_id,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                    if ($query) {
                        $ok_count++;
                    }
                } else {
                    $query = DB::connection($this->mysql)->table('iqc_disposition_rework')
                                ->where('id', $rework_data['id'])
                                ->update([
                                    'lot_no' => $rework_data['lot_no'],
                                    'total_qty' => $rework_data['total_qty'],
                                    'good_qty' => $rework_data['good_qty'],
                                    'ng_qty' => $rework_data['ng_qty'],
                                    'actual_qty' => $rework_data['actual_qty'],
                                    'remarks' => $rework_data['remarks'],
                                    'mr_id' => $rework_data['mr_id'],
                                    'inv_id' => $rework_data['inv_id'],
                                    'iqc_id' => $rework_data['iqc_id'],
                                    'category' => $rework_data['category'],
                                    'disposal_date' => $rework_data['disposal_date'],
                                    'disposal_slip_no' => $rework_data['disposal_slip_no'],
                                    'ngr_control_no' => $rework_data['ngr_control_no'],
                                    'packinglist_no' => $rework_data['packinglist_no'],
                                    'update_user' => Auth::user()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                    if ($query) {
                        $ok_count++;
                    }
                }

                $iqc_id = $rework_data['iqc_id'];
            }

            if ($ok_count == $data_count) {
                    $rework_data = DB::connection($this->mysql)->table('iqc_disposition_rework')
                                    ->where('iqc_id', $iqc_id)->get();
                    $data = [
                        'status' => 'success',
                        'msg' => 'Saving Rework data was successful.',
                        'rework_data' => $rework_data
                    ];
                }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'rework_data' => []
            ];
        }

        return $data;
    }

    public function getReworkData(Request $req)
    {
        $query = "";
        $rework_data = [];

        try {
            $query = "SELECT * FROM iqc_disposition_rework WHERE iqc_id = ".$req->iqc_id;
            $rework_data = DB::connection($this->mysql)->select($query);

            return $rework_data;
        } catch (\Exception $e) {
            return [
                    'status' => 'success',
                    'msg' => 'Saving Rework data was successful.',
                    'rework_data' => $rework_data
                ];
        }
        
    }

    public function postDeleteReworkData(Request $req)
    {
        $data = [
            'status' => 'failed',
            'msg' => 'Deleting Rework data was failed.',
            'rework_data' => []
        ];

        try {
            $query = DB::connection($this->mysql)->table('iqc_disposition_rework')
                        ->whereIn('id', $req->ids)->delete();
            if ($query) {
                $rework_data = DB::connection($this->mysql)->table('iqc_disposition_rework')
                                ->where('iqc_id', $req->iqc_id)->get();
                $data = [
                    'status' => 'success',
                    'msg' => 'Deleting Rework data was successful.',
                    'rework_data' => $rework_data
                ];
            }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'rework_data' => []
            ];
        }

        return $data;
    }


    public function postSaveRTVData(Request $req) 
    {
        $data = [
            'status' => 'failed',
            'msg' => 'Saving RTV data was failed.',
            'rtv_data' => []
        ];

        try {
            $data_count = count($req->rtv_data);
            $ok_count = 0;
            $iqc_id = 0;

            foreach ($req->rtv_data as $key => $rtv_data) {
                if ($rtv_data['id'] == '' && $rtv_data['id'] == null) {
                    $query = DB::connection($this->mysql)->table('iqc_disposition_rtv')
                                ->insert([
                                    'lot_no' => $rtv_data['lot_no'],
                                    'total_qty' => $rtv_data['total_qty'],
                                    'rtv_qty' => $rtv_data['rtv_qty'],
                                    'category' => $rtv_data['category'],
                                    'disposal_date' => $rtv_data['disposal_date'],
                                    'disposal_slip_no' => $rtv_data['disposal_slip_no'],
                                    'ngr_control_no' => $rtv_data['ngr_control_no'],
                                    'packinglist_no' => $rtv_data['packinglist_no'],
                                    'remarks' => $rtv_data['remarks'],
                                    'mr_id' => $rtv_data['mr_id'],
                                    'inv_id' => $rtv_data['inv_id'],
                                    'iqc_id' => $rtv_data['iqc_id'],
                                    'create_user' => Auth::user()->user_id,
                                    'update_user' => Auth::user()->user_id,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                    if ($query) {
                        $ok_count++;
                    }
                } else {
                    $query = DB::connection($this->mysql)->table('iqc_disposition_rtv')
                                ->where('id', $rtv_data['id'])
                                ->update([
                                    'lot_no' => $rtv_data['lot_no'],
                                    'total_qty' => $rtv_data['total_qty'],
                                    'rtv_qty' => $rtv_data['rtv_qty'],
                                    'category' => $rtv_data['category'],
                                    'disposal_date' => $rtv_data['disposal_date'],
                                    'disposal_slip_no' => $rtv_data['disposal_slip_no'],
                                    'ngr_control_no' => $rtv_data['ngr_control_no'],
                                    'packinglist_no' => $rtv_data['packinglist_no'],
                                    'remarks' => $rtv_data['remarks'],
                                    'mr_id' => $rtv_data['mr_id'],
                                    'inv_id' => $rtv_data['inv_id'],
                                    'iqc_id' => $rtv_data['iqc_id'],
                                    'update_user' => Auth::user()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                    if ($query) {
                        $ok_count++;
                    }
                }

                $iqc_id = $rtv_data['iqc_id'];
            }

            if ($ok_count == $data_count) {
                    $rtv_data = DB::connection($this->mysql)->table('iqc_disposition_rtv')
                                    ->where('iqc_id', $iqc_id)->get();
                    $data = [
                        'status' => 'success',
                        'msg' => 'Saving RTV data was successful.',
                        'rtv_data' => $rtv_data
                    ];
                }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'rtv_data' => []
            ];
        }

        return $data;
    }

    public function getRTVData(Request $req)
    {
        $query = "";
        $rtv_data = [];

        try {
            $query = "SELECT * FROM iqc_disposition_rtv WHERE iqc_id = ".$req->iqc_id;
            $rtv_data = DB::connection($this->mysql)->select($query);

            return $rtv_data;
        } catch (\Exception $e) {
            return [
                    'status' => 'success',
                    'msg' => 'Saving RTV data was successful.',
                    'rtv_data' => $rtv_data
                ];
        }
        
    }

    public function postDeleteRTVData(Request $req)
    {
        $data = [
            'status' => 'failed',
            'msg' => 'Deleting RTV data was failed.',
            'rtv_data' => []
        ];

        try {
            $query = DB::connection($this->mysql)->table('iqc_disposition_rtv')
                        ->whereIn('id', $req->ids)->delete();
            if ($query) {
                $rtv_data = DB::connection($this->mysql)->table('iqc_disposition_rtv')
                                ->where('iqc_id', $req->iqc_id)->get();
                $data = [
                    'status' => 'success',
                    'msg' => 'Deleting RTV data was successful.',
                    'rtv_data' => $rtv_data
                ];
            }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'rtv_data' => []
            ];
        }

        return $data;
    }

    public function getAvailableLotNumbers(Request $req)
    {
        $invoice_no = $req->invoice_no;
        $partcode = $req->partcode;

        $data = [];

        $sql = "select i.id as id,
                        i.item as item,
                        i.item_desc as item_desc,
                        i.supplier as supplier,
                        IFNULL(b.qty,l.qty) as qty,
                        i.lot_no as lot_no,
                        i.drawing_num as drawing_num,
                        i.wbs_mr_id as wbs_mr_id,
                        i.invoice_no as invoice_no,
                        i.iqc_result as iqc_result,
                        i.updated_at as updated_at,
                        i.update_user as update_user,
                        i.iqc_status as iqc_status,
                        i.judgement as judgement,
                        if(b.id = null,'LR','MR') as mr_source,
                        ifnull(l.id,b.id) as mr_id
                from tbl_wbs_inventory as i
                left join tbl_wbs_material_receiving_batch as b
                on i.mat_batch_id = b.id
                left join tbl_wbs_local_receiving_batch as l
                on i.loc_batch_id = l.id
                where i.iqc_status in (0) 
                and i.invoice_no = '".$invoice_no."'
                and i.item = '".$partcode."'"; //i.not_for_iqc in (0) and (i.iqc_status = 0 AND i.judgement is null)

        $data = DB::connection($this->wbs)->select($sql);

        return response()->json($data);
    }

    // public function insertIQCLotNo(Request $req)
    // { 
        
    //     try{
    //         $values = array();
    //         $lot_no_arr = [];
    //         for($i = 0; $i <= count($req->lot_no_data) - 1; $i++){


    //             $exist =  DB::connection($this->mysql)
    //                         ->table('iqc_lot_no')
    //                         ->where('lot_no', $req->lot_no_data[$i]['lot_no'])
    //                         ->first();

    //             if(!$exist){
    //                 array_push(
    //                     $values,
    //                     array(
    //                         'iqc_id' => $req->iqc_id,
    //                         'inv_id' => $req->lot_no_data[$i]['inv_id'],
    //                         'mr_id' => $req->lot_no_data[$i]['mr_id'],
    //                         'mr_source' => $req->lot_no_data[$i]['mr_source'],
    //                         'qty' => $req->lot_no_data[$i]['qty'],
    //                         'lot_no' => $req->lot_no_data[$i]['lot_no'],
    //                         'invoice_no' => $req->invoice_no,
    //                         'item_no' => $req->partcode,
    //                         'created_at' => Carbon::now(),
    //                         'updated_at' => Carbon::now(),
    //                         'create_user' => Auth::user()->user_id,
    //                         'update_user' => Auth::user()->user_id,
    //                         'is_deleted' => 0
    //                     )
    //                 );

    //                 array_push($lot_no_arr,$req->lot_no_data[$i]['lot_no']);
    //             }                
               
    //         }

    //         DB::beginTransaction();
            
    //         $inserted = DB::connection($this->mysql)->table('iqc_lot_no')
    //                         ->insert($values);

    //         if ($inserted) {
    //             $lot_no = implode(',',$lot_no_arr);
    //             DB::connection($this->mysql)->table('iqc_inspections')
    //                 ->where('id',$req->iqc_id)
    //                 ->update([
    //                     'lot_no' => $lot_no
    //                 ]);
    //             DB::commit();
    //         }
            
            
    //     }catch(\Exception $err){
    //         DB::rollback();
    //     }
    // }
      public function insertIQCLotNo(Request $req)
    {        
        $test = $req->all();  
        DB::beginTransaction();
        try{
            $values = array();
            for($i = 0; $i <= count($req->lot_no_data) - 1; $i++){


                $exist =  DB::connection($this->mysql)
                            ->table('iqc_lot_no')
                            ->where('lot_no', $req->lot_no_data[$i]['lot_no'])
                            ->first();

                if(!$exist){
                    array_push(
                        $values,
                        array(
                            'iqc_id' => $req->iqc_id,
                            'inv_id' => $req->lot_no_data[$i]['inv_id'],
                            'mr_id' => $req->lot_no_data[$i]['mr_id'],
                            'mr_source' => $req->lot_no_data[$i]['mr_source'],
                            'qty' => $req->lot_no_data[$i]['qty'],
                            'lot_no' => $req->lot_no_data[$i]['lot_no'],
                            'invoice_no' => $req->invoice_no,
                            'item_no' => $req->partcode,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'create_user' => Auth::user()->user_id,
                            'update_user' => Auth::user()->user_id,
                            'is_deleted' => 0
                        )
                    );
                }                
               
            }     
            DB::connection($this->mysql)->table('iqc_lot_no')
                ->insert($values);
            DB::commit();
        }catch(Exception $err){
            DB::rollback();
        }
    }
}
