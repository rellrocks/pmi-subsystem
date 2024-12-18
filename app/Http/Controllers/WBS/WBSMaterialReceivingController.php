<?php
/*******************************************************************************
     Copyright (c) <Company Name> All rights reserved.

     FILE NAME: WBSMaterialReceivingController.php
     MODULE NAME:  3006 : WBS - Material Receiving
     CREATED BY: MESPINOSA
     DATE CREATED: 2016.07.05
     REVISION HISTORY :

     VERSION     ROUND    DATE           PIC          DESCRIPTION
     100-00-01   1     2016.07.05     MESPINOSA       Initial Draft
     200-00-01   1     2016.12.20     AKDELAROSA      2ND VERSION
*******************************************************************************/
?>
<?php

namespace App\Http\Controllers\WBS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use Config;
use DB;
use Illuminate\Support\Facades\Auth; #Auth facade
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Dompdf\Dompdf;
use PDF;
use App;
use Excel;

/**
* Material Receiving Controller
**/
class WBSMaterialReceivingController extends Controller
{
    /**
    * Material Receiving constructor.
    **/
    protected $mysql;
    protected $mssql;
    protected $common;
    protected $barcode;
    protected $ppscon;
    protected $com;

    protected $error;
    protected $errMatno;
    protected $errItem;
    protected $errInvoice;

    public function __construct()
    {
        $this->error = false;
        $this->errMatno = '';
        $this->errItem = '';
        $this->errItem = '';
        $this->errInvoice = '';

        $this->middleware('auth');
        $this->com = new CommonController;

        if (Auth::user() != null) {
            $this->mysql = $this->com->userDBcon(Auth::user()->productline,'wbs');
            $this->main_mysql = $this->com->userDBcon(Auth::user()->productline,'mysql');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline,'common');
            $this->barcode = $this->com->userDBcon(Auth::user()->productline,'barcode');
            $this->ppscon = $this->com->userDBcon(Auth::user()->productline,'pps_invoice');
        } else {
            return redirect('/');
        }
    }

    public function getWBSMaterialReceiving(Request $request_data)
    {
        $pgcode = Config::get('constants.MODULE_CODE_MATRVC');

        if(!$this->com->getAccessRights($pgcode, $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            //$packages = $this->com->getDropdownById(86);
            $packages = DB::connection($this->mysql)->select("SELECT `description` FROM tbl_package_category");
            $for_modification = DB::connection($this->mysql)
                                    ->select("select i.id as id,
                                                i.wbs_mr_id as receiving_no,
                                                i.invoice_no as invoice_no,
                                                i.item as item,
                                                i.item_desc as item_desc,
                                                i.lot_no as lot_no,
                                                i.qty as qty,
                                                i.supplier as supplier,
                                                i.received_date as received_date,
                                                i.location as location,
                                                ifnull(i.mat_batch_id,i.loc_batch_id) as mr_id,
                                                case when i.mat_batch_id is null then 'LR'
                                                else 'MR'
                                                end as mr_source,
                                                i.deleted as deleted,
                                                i.qc_remarks as qc_remarks
                                            from tbl_wbs_inventory as i
                                            left join tbl_wbs_material_receiving_batch as m
                                            on i.mat_batch_id = m.id
                                            left join tbl_wbs_local_receiving_batch as l
                                            on i.loc_batch_id = l.id
                                            where i.qc_remarks is not null
                                            and i.needed_whs_update <> 0");
            $count_for_modification = count($for_modification);

            #Render WBS Page.
            return view('wbs.materialreceiving', [
                        'userProgramAccess' => $userProgramAccess,
                        'packages' => $packages,
                        'pgcode' => $pgcode,
                        'pgaccess' => $this->com->getPgAccess($pgcode),
                        'count_for_modification' => $count_for_modification
                    ]);
        }
    }

    public function postInvoiceNo(Request $req)
    {
        return $this->getInvoiceData($req->invoiceno);
    }

    private function getInvoiceData($invoiceno)
    {
        $data = [];
        $invoice = [];
        try {
            if ($this->CheckInvoiceNo($invoiceno) > 0) {
                return $data = [
                        'msg' => 'This Invoice No. ['.$invoiceno.'] was already received. Please refer to this Receiving Number ['.$this->getMatRecNum($invoiceno).'].',
                        'request_status' => 'failed'
                    ];
            } else {
                # Retreive Material Receive Invoice Data
                $mr_data = DB::connection($this->mssql)->table('XSACT')
                            ->select(DB::raw("1 AS id")
                                , 'INVOICE_NUM as invoice_no'
                                // , DB::raw("CONCAT(SUBSTRING(IDATE, 5,2), '/', SUBSTRING(IDATE, 7,2), '/', SUBSTRING(IDATE, 1,4)) AS invoice_date")
                                , DB::raw('ROUND(SUM(JITU), 2) as total_qty')
                                , DB::raw('ROUND(SUM(KOUNYUUGAKU), 2) as total_amt')
                                , DB::raw("null as  status")
                                , DB::raw(" '". Auth::user()->user_id ."' as create_user")
                                , DB::raw("CONVERT(varchar, GETDATE(), 101) as created_at")
                                , DB::raw(" '". Auth::user()->user_id ."' as update_user")
                                , DB::raw("CONVERT(varchar, GETDATE(), 101) as updated_at")
                                )
                            ->where('INVOICE_NUM', $invoiceno)
                            ->groupBy('INVOICE_NUM')
                            ->first();

                $invoice_date = DB::connection($this->mssql)->table('XSACT')
                                    ->select(DB::raw("FDATE AS invoice_date")) //CONCAT(SUBSTRING(IDATE, 5,2), '/', SUBSTRING(IDATE, 7,2), '/', SUBSTRING(IDATE, 1,4))
                                    ->where('INVOICE_NUM', $invoiceno)
                                    ->first();
                $variance = DB::connection($this->mssql)
                            ->table('XSACT AS S')
                            ->join('XHEAD AS H', 'H.CODE' ,'=','S.CODE')
                            ->select(DB::raw("CAST(ROUND(SUM(S.JITU) - SUM(S.HVOL), 4) AS VARCHAR) as variance"))
                            ->where('S.INVOICE_NUM', $invoiceno)
                            ->groupBy('S.INVOICE_NUM')
                            ->first();

                if (count((array)$mr_data) > 0) {
                    $dt = Carbon::now();
                    $date = $dt->format('m/d/Y');

                    $receive_no = '';

                    $invyr = substr($invoice_date->invoice_date,0,8);
                    $invm = substr($invoice_date->invoice_date,4,4);
                    $invd = substr($invoice_date->invoice_date,5,5);

                    $invdate = substr($invoice_date->invoice_date,0,8);

                    $invoice = [
                        'receiving_no' => $receive_no,
                        'receiving_date' => $date,
                        'invoiceno' => $mr_data->invoice_no,
                        'invoice_date' => $this->formatDate($invdate,'m/d/Y'),
                        'total_qty' => number_format($mr_data->total_qty),
                        'total_var' => number_format($variance->variance),
                        'total_amt' => $mr_data->total_amt,
                        'status' => 'Open',
                        'created_by' => Auth::user()->user_id,
                        'created_date' => date("m/d/Y h:i A"),
                        'updated_by' => Auth::user()->user_id,
                        'updated_date' => date("m/d/Y h:i A")
                    ];

                    $details = $this->getDetails($mr_data->invoice_no);
                    $summary = $this->getSummaryInvoice($mr_data->invoice_no);
                    $data = [
                        'invoicedata' => $invoice,
                        'detailsdata' => $details,
                        'summarydata' => $summary,
                        'request_status' => 'success'
                    ];
                    return response()->json($data);
                } else {
                    return $data = [
                        'msg' => 'This Invoice Number does not exist.',
                        'request_status' => 'failed'
                    ];
                }
            }
        } catch (throwable $th) {
            return $th;
        }
        
    }


    public function postSaveMaterialReceiving(Request $req)
    {
        $success = false;
        if ($req->savestate == 'ADD') {
            try {
                DB::connection($this->mysql)->beginTransaction();
                $mrdata = json_decode($req->mrdata);
                $detailsdata = $this->getDetails($mrdata->invoice_no);
                $summarydata = json_decode($req->summarydata);
                $notForIQC = json_decode($req->notForIQC);
                $receive_no = $this->com->getTransCode('MAT_RCV');

                DB::connection($this->mysql)->table('tbl_wbs_material_receiving')->insert([
                    'receive_no' => $receive_no,
                    'receive_date' => $this->formatDate($mrdata->receive_date, 'Y-m-d'),
                    'invoice_no' => $mrdata->invoice_no,
                    'invoice_date' => $this->formatDate($mrdata->invoice_date, 'Y-m-d'),
                    'pallet_no' => $mrdata->pallet_no,
                    'total_qty' => $this->number_unformat($mrdata->total_qty),
                    'total_var' => $this->number_unformat($mrdata->total_var),
                    'total_amt' => $this->number_unformat($mrdata->total_amt),
                    'status' => $mrdata->status,
                    'create_user' => Auth::user()->user_id,
                    'create_pg' => date('Y-m-d H:i:s'),
                    'created_at' =>  date('Y-m-d H:i:s'),
                    'update_pg' => date('Y-m-d H:i:s'),
                    'updated_at' =>  date('Y-m-d H:i:s')

                ]);

                $wbs_mr_id = $this->lastinsertctrlno();
                
                foreach ($detailsdata as $key => $details) {
                    DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->insert([
                        'wbs_mr_id' => $wbs_mr_id,
                        'item' => $details['item'],
                        'item_desc' => $details['description'],
                        'qty' => $details['qty'],
                        'pr_no' => $details['pr'],
                        'unit_price' => $details['price'],
                        'amount' => $details['amount'],
                        'create_user' => Auth::user()->user_id,
                        'created_at' =>  date('Y-m-d H:i:s'),
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

            
                foreach ($summarydata->item as $key => $item) {
                    DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->insert([
                        'wbs_mr_id' => $wbs_mr_id,
                        'item' => $item,
                        'item_desc' => $summarydata->description[$key],
                        'qty' => $summarydata->qty[$key],
                        'received_qty' => $summarydata->r_qty[$key],
                        'variance' => $summarydata->variance[$key],
                        'not_for_iqc' => (in_array($item, (array)$notForIQC))? 1: 0,
                        'for_kitting' => (in_array($item, (array)$notForIQC))? 1: 0,
                        'create_user' => Auth::user()->user_id,
                        'created_at' =>  date('Y-m-d H:i:s'),
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $success = true;
            } catch (\Exception $th) {
                $success = false;
            }
            if($success) {
                DB::connection($this->mysql)->commit();
                return [
                    'msg' => "You've successfully saved Invoice Number [".$mrdata->invoice_no."] in Material Receiving Number [".$wbs_mr_id."]",
                    'request_status' => 'success'
                ];
            }else {
                DB::connection($this->mysql)->rollBack();
                return [
                    'msg' => "Saving Failed."
                ];
            }
        } elseif ($req->savestate == 'EDIT') {
            $msgError = "Saving Failed.";
            try {
                $mrdata = json_decode($req->mrdata);
                $summarydata = json_decode($req->summarydata);
                $batchdata = json_decode($req->batchdata);
                $notForIQC = json_decode($req->notForIQC);
                $notForIQCbatch = json_decode($req->notForIQCbatch);
                $IsPrinted = json_decode($req->IsPrinted);
                $status = 'O';
                $iqcstatus = 0;
                $user_id = Auth::user()->user_id;
                DB::connection($this->mysql)->beginTransaction();
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$mrdata->receive_no)
                    ->update([
                    'receive_date' => $this->formatDate($mrdata->receive_date, 'Y-m-d'),
                    'update_pg' => date('Y-m-d H:i:s'),
                    'update_user' => $user_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                #region SUMMARY
                if ($this->checkIfExistObject($summarydata->item) < 1) {
                    //for iqc checking
                    foreach ($summarydata->itemall as $key => $itemall) {
                        if ($itemall != '') {
                            $checkiqc = 0;
                            if (isset($notForIQC)) {
                                $checkiqc = (in_array($itemall, $notForIQC))? 1: 0;
                                $iqcstatus = (in_array($itemall, $notForIQC))? 1: 0;
                            } else {
                                $checkiqc = 0;
                            }
    
                            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                                ->where('id',$summarydata->id[$key])
                                ->update([
                                    'not_for_iqc' => $checkiqc,
                                    'for_kitting' => $checkiqc,
                                    'update_user' => $user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            $this->checkIfJudged($itemall,$mrdata->receive_no,$checkiqc,$iqcstatus);
                        }
                    }
                } else {
                    foreach ($summarydata->item as $key => $item) {
                        if ($item != '') {
                            $checkiqc = 0;
                            if (isset($notForIQC)) {
                                $checkiqc = (in_array($item, $notForIQC))? 1: 0;
                            } else {
                                $checkiqc = 0;
                            }
    
                            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                                ->where('id',$summarydata->id[$key])
                                ->update([
                                    'not_for_iqc' => $checkiqc,
                                    'for_kitting' => $checkiqc,
                                    'update_user' => $user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                        }
                    }
                }
                #endregion

                #region BATCH
                if (count((array)$batchdata) > 0) {
                    foreach ($batchdata->item as $key => $item) {
                        if($batchdata->location[$key] == "" || $batchdata->location[$key] == null) {
                            $success = false;
                            $msgError = "Location cannot be empty.";
                            break;
                        }
                        if ($this->checkIfBatchExist($batchdata->id[$key])) {
                            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                                ->where('id',$batchdata->id[$key])
                                ->update([
                                    'qty' => str_replace(',','', $batchdata->batch_qty[$key]),
                                    'box' => $batchdata->box[$key],
                                    'box_qty' => str_replace(',','', $batchdata->box_qty[$key]),
                                    'lot_no' => $batchdata->lot_no[$key],
                                    'supplier' => strtoupper($batchdata->supplier[$key]),
                                    'update_user' => $user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            if ($batchdata->qty[$key] != $batchdata->batch_qty[$key]) {
                                $newqty = $batchdata->qty[$key];
                               
                                if($newqty < 0){
                                    $converted_qty = abs($newqty);
                                    DB::connection($this->mysql)->table('tbl_wbs_inventory')
                                        ->where('mat_batch_id',$batchdata->id[$key])
                                        ->decrement('qty',$converted_qty);
                                
                                } else if($newqty > 0) {
                                    DB::connection($this->mysql)->table('tbl_wbs_inventory')
                                        ->where('mat_batch_id',$batchdata->id[$key])
                                        ->increment('qty',$newqty);
                                }
    
                                 DB::connection($this->mysql)->table('tbl_wbs_inventory')
                                        ->where('mat_batch_id',$batchdata->id[$key])
                                       ->update([
                                           'box' => $batchdata->box[$key],
                                           'box_qty' => str_replace(',','',$batchdata->box_qty[$key]),
                                           'lot_no' => $batchdata->lot_no[$key],
                                           'supplier' => strtoupper($batchdata->supplier[$key]),
                                           'update_pg' => date('Y-m-d H:i:s'),
                                           'update_user' => $user_id,
                                           'updated_at' => date('Y-m-d H:i:s')
                                        ]);
    
                                }else{
                                       DB::connection($this->mysql)->table('tbl_wbs_inventory')
                                        ->where('mat_batch_id',$batchdata->id[$key])
                                       ->update([
                                           'box' => $batchdata->box[$key],
                                           'box_qty' => str_replace(',','',$batchdata->box_qty[$key]),
                                           'lot_no' => $batchdata->lot_no[$key],
                                           'supplier' => strtoupper($batchdata->supplier[$key]),
                                           'update_pg' => date('Y-m-d H:i:s'),
                                           'update_user' => $user_id,
                                           'updated_at' => date('Y-m-d H:i:s')
                                        ]);
                                }
    
                             $this->UpdateCalculateQty($mrdata->receive_no,$item,str_replace(',','',$batchdata->qty[$key]));
                        } else {
                            $check = 0; 
                            $printed = 0;
    
                            if (isset($notForIQCbatch)) {
                                $check = (in_array($item, $notForIQCbatch))? 1: 0;
                            } else {
                                $check = 0;
                            }
    
                            if (isset($IsPrinted)) {
                                $printed = (in_array($item, $IsPrinted))? 1: 0;
                            }
                            $mat_batch_id = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                                            ->insertGetId([
                                                'wbs_mr_id' => $mrdata->receive_no,
                                                'invoice_no' => $mrdata->invoice_no,
                                                'item' => $item,
                                                'item_desc' => $batchdata->item_desc[$key],
                                                'qty' => str_replace(',','',$batchdata->qty[$key]),
                                                'box' => $batchdata->box[$key],
                                                'box_qty' => str_replace(',','',$batchdata->box_qty[$key]),
                                                'lot_no' => $batchdata->lot_no[$key],
                                                'location' => $batchdata->location[$key],
                                                'supplier' => strtoupper($batchdata->supplier[$key]),
                                                'drawing_num' => $this->getDrawingNum($item),
                                                'iqc_status' => $check,
                                                'is_printed' => $printed,
                                                'for_kitting' => $check,
                                                'not_for_iqc' => $check,
                                                'iqc_result' => '',
                                                'received_date' => $mrdata->receive_date,
                                                'create_user' => $user_id,
                                                'created_at' =>  date('Y-m-d H:i:s'),
                                                'update_user' => $user_id,
                                                'updated_at' => date('Y-m-d H:i:s')
                                            ]);
    
                            DB::connection($this->mysql)->table('tbl_wbs_inventory')->insert([
                                'wbs_mr_id' => $mrdata->receive_no,
                                'invoice_no' => $mrdata->invoice_no,
                                'item' => $item,
                                'item_desc' => $batchdata->item_desc[$key],
                                'qty' => str_replace(',','',$batchdata->qty[$key]),
                                'box' => $batchdata->box[$key],
                                'box_qty' => str_replace(',','',$batchdata->box_qty[$key]),
                                'lot_no' => $batchdata->lot_no[$key],
                                'location' => $batchdata->location[$key],
                                'supplier' => strtoupper($batchdata->supplier[$key]),
                                'drawing_num' => $this->getDrawingNum($item),
                                'iqc_status' => $check,
                                'is_printed' => $printed,
                                'for_kitting' => $check,
                                'not_for_iqc' => $check,
                                'iqc_result' => '',
                                'received_date' => $mrdata->receive_date,
                                'create_pg' => date('Y-m-d H:i:s'),
                                'create_user' => $user_id,
                                'created_at' =>  date('Y-m-d H:i:s'),
                                'update_pg' => date('Y-m-d H:i:s'),
                                'update_user' => $user_id,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'mat_batch_id' => $mat_batch_id
                            ]);
    
                            $this->calculateQty($mrdata->receive_no,$item,str_replace(',','',$batchdata->qty[$key]));
                            $success = true;
                        }
                    }
                }
                #endregion

                if($success) {
                    $total_var = $this->getVariance($mrdata->receive_no,$this->getTotalQty($mrdata->receive_no));
                    if ($total_var < 1) {
                        $status = 'X';
                    }

                    DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$mrdata->receive_no)->update([
                        'total_var' => $total_var,
                        'status' => $status,
                        'pallet_no' => $mrdata->pallet_no,
                        'total_qty' => $this->number_unformat($mrdata->total_qty),
                        'total_var' => $this->number_unformat($total_var),
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'update_pg' => date('Y-m-d H:i:s')
                    ]);
                    $success = true;
                }
            } catch (\Exception $th) {
                $success = false;
            }

            if($success) {
                DB::connection($this->mysql)->commit();
                return [
                    'msg' => "You've successfully saved your changes in Material Receiving Number [".$mrdata->receive_no."]",
                    'request_status' => 'success'
                ];
            }else {
                DB::connection($this->mysql)->rollBack();
                return [
                    'msg' => $msgError
                ];
            }
        } else {
            return [
                    'msg' => "Saving Failed."
                ];
        }
    }

    private function CheckInvoiceNo($invoiceno)
    {
        $invoice = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('invoice_no',$invoiceno)
                        ->where('status','O')
                        ->count();
        return $invoice;
    }

    private function getMatRecNum($invoiceno)
    {
        $matrec = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->select('receive_no')
                        ->where('invoice_no',$invoiceno)
                        ->first();
        return $matrec->receive_no;
    }

    public function wbsCancel()
    {
        $this->com->getWbsPrevCode('MAT_RCV');
    }

    public function getLatestMR()
    {
        $data = [];
        $mr_data = [];
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')->orderBy('id','desc')->count();
        if ($cnt > 0) {
            $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')->orderBy('id','desc')->first();

            $mrdata = [
                'receive_no' => $mr->receive_no,
                'receive_date' => $mr->receive_date,
                'invoice_no' => $mr->invoice_no,
                'invoice_date' => $mr->invoice_date,
                'pallet_no' => $mr->pallet_no,
                'total_qty' => number_format($mr->total_qty),
                'total_var' => number_format($this->getVariance($mr->receive_no,$mr->total_qty)),
                'status' => $this->getStatus($mr->receive_no,$mr->status),
                'create_user' => $mr->create_user,
                'created_at' => $mr->create_pg,
                'update_user' => $mr->update_user,
                'updated_at' => $mr->updated_at
            ];

            $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
            $summarydata = $this->DisplaySummary($mr->receive_no);
            // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
            $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

            return $data = [
                        'invoicedata' => $mrdata,
                        'detailsdata' => $detailsdata,
                        'summarydata' => $summarydata,
                        'batchdata' => $batchdata,
                        'request_status' => 'success'
                    ];
        } else {
            return $data = [
                        'msg' => "No Data found.",
                        'request_status' => 'failed'
                    ];
        }
    }

    private function truncate($tbl)
    {
        DB::connection($this->mysql)->table($tbl)->truncate();
    }

    public function getWBSNavigate(Request $req)
    {
        switch ($req->to) {
            case 'next':
                return $this->next($req->receivingno);
                break;

            case 'prev':
                return $this->prev($req->receivingno);
                break;

            case 'last':
                return $this->last();
                break;

            case 'first':
                return $this->first();
                break;

            default:
                return $this->last();
                break;
        }
    }

    private function next($receivingno)
    {
        $data = [];
        $check = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$receivingno)
                    ->select('id')->count();
        if ($check > 0) {
            $nxt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('receive_no',$receivingno)
                        ->select('id')->first();
            $nxtid = $nxt->id + 1;

            $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('id',$nxtid)
                        ->count();

            if ($cnt > 0) {
                $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('id',$nxtid)->first();

                $mrdata = [
                    'receive_no' => $mr->receive_no,
                    'receive_date' => $mr->receive_date,
                    'invoice_no' => $mr->invoice_no,
                    'invoice_date' => $mr->invoice_date,
                    'pallet_no' => $mr->pallet_no,
                    'total_qty' => number_format($mr->total_qty),
                    'total_var' => number_format($this->getVariance($mr->receive_no,$mr->total_qty)),
                    'status' => $this->getStatus($mr->receive_no,$mr->status),
                    'create_user' => $mr->create_user,
                    'created_at' => $mr->create_pg,
                    'update_user' => $mr->update_user,
                    'updated_at' => $mr->updated_at
                ];

                $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
                $summarydata = $this->DisplaySummary($mr->receive_no);
                // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
                $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

                return $data = [
                            'invoicedata' => $mrdata,
                            'detailsdata' => $detailsdata,
                            'summarydata' => $summarydata,
                            'batchdata' => $batchdata,
                            'request_status' => 'success'
                        ];
            } else {
                return $this->last();
            }
        } else {
            return $data = [
                        'msg' => "You've reached the last Material Receiving Number",
                        'request_status' => 'failed'
                    ];
        }
    }

    private function prev($receivingno)
    {
        $data = [];
        $check = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$receivingno)
                    ->count();
        if ($check > 0) {
            $prev = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('receive_no',$receivingno)
                        ->select('id')->first();
            $previd = $prev->id - 1;

            $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('id',$previd)
                        ->count();
            if ($cnt > 0) {
                $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('id',$previd)->first();

                $mrdata = [
                    'receive_no' => $mr->receive_no,
                    'receive_date' => $mr->receive_date,
                    'invoice_no' => $mr->invoice_no,
                    'invoice_date' => $mr->invoice_date,
                    'pallet_no' => $mr->pallet_no,
                    'total_qty' => number_format($mr->total_qty),
                    'total_var' => number_format($this->getVariance($mr->receive_no,$mr->total_qty)),
                    'status' => $this->getStatus($mr->receive_no,$mr->status),
                    'create_user' => $mr->create_user,
                    'created_at' => $mr->create_pg,
                    'update_user' => $mr->update_user,
                    'updated_at' => $mr->updated_at
                ];

                $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
                $summarydata = $this->DisplaySummary($mr->receive_no);
                // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
                $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

                return $data = [
                            'invoicedata' => $mrdata,
                            'detailsdata' => $detailsdata,
                            'summarydata' => $summarydata,
                            'batchdata' => $batchdata,
                            'request_status' => 'success'
                        ];
            } else {
                return $this->first();
            }
        } else {
            return $data = [
                        'msg' => "You've reached the last Material Receiving Number",
                        'request_status' => 'failed'
                    ];
        }
    }

    private function last()
    {
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->orderBy('id','desc')
                    ->count();
        if ($cnt > 0) {
            $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->orderBy('id','desc')
                    ->first();
            $mrdata = [
                'receive_no' => $mr->receive_no,
                'receive_date' => $mr->receive_date,
                'invoice_no' => $mr->invoice_no,
                'invoice_date' => $mr->invoice_date,
                'pallet_no' => $mr->pallet_no,
                'total_qty' => number_format($mr->total_qty),
                'total_var' => number_format($this->getVariance($mr->receive_no,$mr->total_qty)),
                'status' => $this->getStatus($mr->receive_no,$mr->status),
                'create_user' => $mr->create_user,
                'created_at' => $mr->create_pg,
                'update_user' => $mr->update_user,
                'updated_at' => $mr->updated_at
            ];

            $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
            $summarydata = $this->DisplaySummary($mr->receive_no);
            // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
            $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

            return $data = [
                        'invoicedata' => $mrdata,
                        'detailsdata' => $detailsdata,
                        'summarydata' => $summarydata,
                        'batchdata' => $batchdata,
                        'request_status' => 'success'
                    ];
        }
    }

    private function first()
    {
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->orderBy('id','asc')
                    ->count();
        if ($cnt > 0) {
            $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->orderBy('id','asc')
                    ->first();
            $mrdata = [
                'receive_no' => $mr->receive_no,
                'receive_date' => $mr->receive_date,
                'invoice_no' => $mr->invoice_no,
                'invoice_date' => $mr->invoice_date,
                'pallet_no' => $mr->pallet_no,
                'total_qty' => number_format($mr->total_qty),
                'total_var' => number_format($this->getVariance($mr->receive_no,$mr->total_qty)),
                'status' => $this->getStatus($mr->receive_no,$mr->status),
                'create_user' => $mr->create_user,
                'created_at' => $mr->create_pg,
                'update_user' => $mr->update_user,
                'updated_at' => $mr->updated_at
            ];

            $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
            $summarydata = $this->DisplaySummary($mr->receive_no);
            // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
            $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

            return $data = [
                        'invoicedata' => $mrdata,
                        'detailsdata' => $detailsdata,
                        'summarydata' => $summarydata,
                        'batchdata' => $batchdata,
                        'request_status' => 'success'
                    ];
        }
    }

    public function getWBSMRnumber(Request $req)
    {
        return $this->getMRdata($req->receivingno);
    }

    public function getMRdata($receivingno)
    {
        $data = [];
        $mr_data = [];
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$receivingno)
                    ->count();
        if ($cnt > 0) {
            $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$receivingno)->first();

            $mrdata = [
                'receive_no' => $mr->receive_no,
                'receive_date' => $mr->receive_date,
                'invoice_no' => $mr->invoice_no,
                'invoice_date' => $mr->invoice_date,
                'pallet_no' => $mr->pallet_no,
                'total_qty' => number_format($mr->total_qty),
                'total_var' => number_format($this->getVariance($mr->receive_no,$mr->total_qty)),
                'status' => $this->getStatus($mr->receive_no,$mr->status),
                'create_user' => $mr->create_user,
                'created_at' => $mr->create_pg,
                'update_user' => $mr->update_user,
                'updated_at' => $mr->updated_at
            ];

            $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
            $summarydata = $this->DisplaySummary($mr->receive_no);
            // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
            $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

            return $data = [
                        'invoicedata' => $mrdata,
                        'detailsdata' => $detailsdata,
                        'summarydata' => $summarydata,
                        'batchdata' => $batchdata,
                        'request_status' => 'success'
                    ];
        }else {
            return $data = [
                'request_status' => 'failed'
            ];
        }
    }

    private function checkIfExistObject($object)
    {
       return count((array)$object);
    }

    private function getVariance($wbs_mr_id,$qty)
    {
        $variance = 0;
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->where('wbs_mr_id',$wbs_mr_id)->count();
        if ($cnt > 0) {
            $mrs = DB::connection($this->mysql)
                ->select("SELECT IFNULL(mrs.variance,s.qty) AS variance
                        FROM tbl_wbs_material_receiving_summary s
                        LEFT JOIN
                        (SELECT rs.wbs_mr_id, rs.item, SUM(b.qty) as received_qty, (rs.qty - SUM(b.qty)) as variance
                        FROM tbl_wbs_material_receiving_summary rs
                            LEFT JOIN tbl_wbs_material_receiving_batch b
                            ON b.wbs_mr_id = rs.wbs_mr_id AND b.item = rs.item
                        WHERE b.wbs_mr_id = '".$wbs_mr_id."'
                        GROUP BY rs.item)mrs ON s.wbs_mr_id = mrs.wbs_mr_id
                        AND s.item = mrs.item
                        WHERE s.wbs_mr_id = '".$wbs_mr_id."'");
            foreach ($mrs as $key => $mr) {
                $variance += $mr->variance;
            }
            return $variance;
        }
    }

    private function getStatus($wbs_mr_id,$status)
    {
        $variance = 0;
        $stats = 'O';
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->where('wbs_mr_id',$wbs_mr_id)->count();
        if ($cnt > 0) {
            $mrs = DB::connection($this->mysql)
                ->select("SELECT IFNULL(mrs.variance,s.qty) AS variance
                        FROM tbl_wbs_material_receiving_summary s
                        LEFT JOIN
                        (SELECT rs.wbs_mr_id, rs.item, SUM(b.qty) as received_qty, (rs.qty - SUM(b.qty)) as variance
                        FROM tbl_wbs_material_receiving_summary rs
                            LEFT JOIN tbl_wbs_material_receiving_batch b
                            ON b.wbs_mr_id = rs.wbs_mr_id AND b.item = rs.item
                        WHERE b.wbs_mr_id = '".$wbs_mr_id."'
                        GROUP BY rs.item)mrs ON s.wbs_mr_id = mrs.wbs_mr_id
                        AND s.item = mrs.item
                        WHERE s.wbs_mr_id = '".$wbs_mr_id."'");
            foreach ($mrs as $key => $mr) {
                $variance += $mr->variance;
            }

            if ($variance < 1 && $status != 'C') {
                $stats = 'X';
            }

            if ($status == 'C') {
                $stats = 'C';
            }
        }

        return $stats;
    }

    public function getWBSMRsearch(Request $req)
    {
        $from = $this->formatDate($req->from, 'Y-m-d');
        $to = $this->formatDate($req->to, 'Y-m-d');
        $invoiceno = $req->invoiceno;
        $invfrom = $this->formatDate($req->invfrom, 'Y-m-d');
        $invto = $this->formatDate($req->invto, 'Y-m-d');
        $open = $req->open;
        $close = $req->close;
        $cancelled = $req->cancelled;
        $item = $req->item;

        $receivedate_cond = '';
        $invoiceno_cond = '';
        $invoicedate_cond = '';
        $status_cond = '';
        $item_cond = '';

        # Create Receive Date Condition.
            if(is_null($from) and is_null($to))
            {
                $receivedate_cond = '';
            }
            else
            {
                $receivedate_cond = " AND b.received_date BETWEEN '" . $from . "' AND '" . $to . "'";
            }

            # Create Invoice No. Condition
            if(empty($invoiceno))
            {
                $invoiceno_cond ='';
            }
            else
            {
                $invoiceno_cond = " AND b.invoice_no = '" . $invoiceno . "'";
            }

            # Create Invoice No. Condition
            if(empty($item))
            {
                $item_cond ='';
            }
            else
            {
                $item_cond = " AND b.item = '" . $item . "'";
            }

            # Create Invoice Date. Condition
            if(is_null($invfrom) and is_null($invto))
            {
                $invoicedate_cond = '';
            }
            else
            {
                $invoicedate_cond = " AND r.invoice_date BETWEEN '" . $invfrom . "' AND '" . $invto . "'";
            }

            # Create Status Condition
            if($open > 0 || $close > 0 || $cancelled > 0)
            {
                if($open == 1)
                {
                    $open = "'O'";
                }
                else
                {
                    $open = "''";
                }

                if($close == 1)
                {
                    $close = "'X'";
                }
                else
                {
                    $close = "''";
                }

                if($cancelled == 1)
                {
                    $cancelled = "'C'";
                }
                else
                {
                    $cancelled = "''";
                }

                $status_cond = " AND `status` IN (". $open .", ". $close .",". $cancelled.")";
            }

        $data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving as r')
                    ->leftJoin('tbl_wbs_material_receiving_batch as b','r.receive_no','=','b.wbs_mr_id')
                    ->whereRaw(" 1=1 "
                            . $receivedate_cond
                            . $invoiceno_cond
                            . $invoicedate_cond
                            . $status_cond
                            . $item_cond)
                    ->select('r.id',
                            'r.receive_no',
                            'b.received_date',
                            'b.invoice_no',
                            'r.invoice_date',
                            'b.item',
                            'b.lot_no',
                            'b.qty',
                            'r.status',
                            'b.iqc_status',
                            'r.create_user',
                            'r.created_at',
                            'r.update_user',
                            'r.updated_at')
                    ->groupBy('r.id',
                            'r.receive_no',
                            'b.received_date',
                            'b.invoice_no',
                            'r.invoice_date',
                            'b.item',
                            'b.lot_no',
                            'b.qty',
                            'r.status',
                            'b.iqc_status')
                    ->orderBy('r.id','desc')
                    ->get();
        if ($this->checkIfExistObject($data) > 0) {
            return $data;
        }
    }

    public function getWBSMRlookItem(Request $req)
    {
        $data = [];
        $mr_data = [];
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('id',$req->id)
                    ->count();
        if ($cnt > 0) {
            $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('id',$req->id)->first();

            $mrdata = [
                'receive_no' => $mr->receive_no,
                'receive_date' => $mr->receive_date,
                'invoice_no' => $mr->invoice_no,
                'invoice_date' => $mr->invoice_date,
                'pallet_no' => $mr->pallet_no,
                'total_qty' => number_format($mr->total_qty),
                'total_var' => $this->getVariance($mr->receive_no,$mr->total_qty),
                'status' => $this->getStatus($mr->receive_no,$mr->status),
                'create_user' => $mr->create_user,
                'created_at' => $mr->created_at,
                'update_user' => $mr->update_user,
                'updated_at' => $mr->updated_at
            ];

            $detailsdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')->where('wbs_mr_id',$mr->receive_no)->get();
            $summarydata = $this->DisplaySummary($mr->receive_no);
            // DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->where('wbs_mr_id',$mr->receive_no)->get();
            $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$mr->receive_no)->get();

            return $data = [
                        'invoicedata' => $mrdata,
                        'detailsdata' => $detailsdata,
                        'summarydata' => $summarydata,
                        'batchdata' => $batchdata,
                        'request_status' => 'success'
                    ];
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

    public function printBarcode(Request $req)
    {
        $mr_data = [];
        $printed = [];
        $mat_code = DB::connection($this->mysql)->table('tbl_transaction')
                        ->where('description','Material Receiving')
                        ->select('code')
                        ->get();
        if ($req->state == 'bulk') {
            $mr_data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving as r')
                ->join('tbl_wbs_material_receiving_batch as b','r.receive_no','=','b.wbs_mr_id')
                ->where('r.receive_no',$req->receivingno)
                ->get();

            if ($this->checkIfExistObject($mr_data) > 0) {
                foreach ($mr_data as $key => $mr) {
                    $recdate = str_replace('-', '', $mr->receive_date);
                    $receivingdate = substr($recdate, 2);
                    
                    $user_id = Auth::user()->user_id;
                    $trancode = (isset($mat_code[0]->code))? $mat_code[0]->code : 'MAT_RCV'; //old
                    //$trancode = ($user_id == 'santos' ? 'MAT_RCV_F3' : 'MAT_RCV'); //temporary
                    DB::connection($this->barcode) //Config::get('constants.DB_SQLSRV_BARCODE')
                        ->table('barcode_print')
                        ->insert(['printdate' => date('Y-m-d H:i:s')
                            ,'txnno'     => $mr->invoice_no
                            ,'txndate'   => $invdate
                            ,'itemno'    => $mr->item
                            ,'itemdesc'  => $mr->item_desc
                            ,'qty'       => $mr->qty
                            ,'bcodeqty'  => $mr->box_qty
                            ,'lotno'     => $mr->lot_no
                            ,'location'  => $mr->location
                            ,'barcode'   => $mr->item.$receivingdate
                            ,'printedby' => Auth::user()->user_id
                            ,'trancode'  => (isset($mat_code[0]->code))? $mat_code[0]->code : 'MAT_RCV'
                            ,'printerid' => 0
                        ]);

                    DB::connection($this->barcode) //Config::get('constants.DB_SQLSRV_BARCODE')
                        ->table('barcode_print_mobile')
                        ->insert(['printdate' => date('Y-m-d H:i:s')
                            ,'txnno'     => $mr->invoice_no
                            ,'txndate'   => $invdate
                            ,'itemno'    => $mr->item
                            ,'itemdesc'  => $mr->item_desc
                            ,'qty'       => $mr->qty
                            ,'bcodeqty'  => $mr->box_qty
                            ,'lotno'     => $mr->lot_no
                            ,'location'  => $mr->location
                            ,'barcode'   => $mr->item.$receivingdate
                            ,'printedby' => Auth::user()->user_id
                            ,'trancode'  => (isset($mat_code[0]->code))? $mat_code[0]->code : 'MAT_RCV'
                            ,'printerid' => 0
                        ]);
                }

                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                        ->where('wbs_mr_id',$req->receivingno)
                        ->update([
                            'is_printed' => 1
                        ]);
                return $data = [
                        'msg' => "You've successfully printed the barcodes.",
                        'request_status' => 'success'
                    ];

            } else {
                return $data = [
                        'msg' => "Barcode printing failed.",
                        'request_status' => 'failed'
                    ];
            }
        } else {
            $recdate = str_replace('-', '', $req->receivingdate);
            $receivingdate = substr($recdate, 2);
            $user_id = Auth::user()->user_id;
            $trancode = (isset($mat_code[0]->code))? $mat_code[0]->code : 'MAT_RCV';//old
            //$trancode = ($user_id == 'santos' ? 'MAT_RCV_F3' : 'MAT_RCV'); //temporary
            $br = DB::connection($this->barcode)
                    ->table('barcode_print')
                    ->insert(['printdate' => date('Y-m-d H:i:s')
                        ,'txnno'     => $req->txnno
                        ,'txndate'   => $req->txndate
                        ,'itemno'    => $req->itemno
                        ,'itemdesc'  => $req->itemdesc
                        ,'qty'       => $req->qty
                        ,'bcodeqty'  => $req->bcodeqty
                        ,'lotno'     => $req->lotno
                        ,'location'  => $req->location
                        ,'barcode'   => $req->itemno.$receivingdate
                        ,'printedby' => Auth::user()->user_id
                        ,'trancode'  => $trancode
                        ,'printerid' => 0
                    ]);

            $br_mobile = DB::connection($this->barcode)
                    ->table('barcode_print_mobile')
                    ->insert(['printdate' => date('Y-m-d H:i:s')
                        ,'txnno'     => $req->txnno
                        ,'txndate'   => $req->txndate
                        ,'itemno'    => $req->itemno
                        ,'itemdesc'  => $req->itemdesc
                        ,'qty'       => $req->qty
                        ,'bcodeqty'  => $req->bcodeqty
                        ,'lotno'     => $req->lotno
                        ,'location'  => $req->location
                        ,'barcode'   => $req->itemno.$receivingdate
                        ,'printedby' => Auth::user()->user_id
                        ,'trancode'  => $trancode
                        ,'printerid' => 0
                    ]);

            if (isset($req->id)) {
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('wbs_mr_id',$req->receivingno)
                    ->where('id',$req->id)
                    ->update([
                        'is_printed' => 1
                    ]);
            } else {

            }


            if ($br) {
                return $data = [
                        'msg' => "You've successfully printed the barcodes.",
                        'request_status' => 'success'
                    ];
            } else {
                return $data = [
                        'msg' => "Barcode printing failed.",
                        'request_status' => 'failed'
                    ];
            }


        }
    }

    public function getUpdateIsPrintedview(Request $req)
    {
        return $this->getBatchItem($req->receivingno);
    }

    public function printForIQC(Request $req)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);
        $app_date = $dt->format('m/d/Y');
        $app_time = $dt->format('H:i A');

        $company_info = $this->com->getCompanyInfo();

        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$req->receivingno)
                    ->count();


        $iqc_data = '';
        if($cnt > 0)
        {
            $receiveno = $req->receivingno;
            $iqc_data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch as b')
                            ->join('tbl_wbs_material_receiving as r','b.wbs_mr_id','=','r.receive_no')
                            ->where('b.wbs_mr_id', $receiveno)
                            ->where('b.for_kitting', '0')
                            ->where('b.iqc_status', '0')
                            ->select('r.receive_no as receive_no',
                                    'r.invoice_no as invoice_no',
                                    'b.item as item',
                                    'b.item_desc as item_desc',
                                    'b.supplier as supplier',
                                    'b.qty as qty',
                                    'b.lot_no as lot_no',
                                    'b.drawing_num as drawing_num',
                                    'b.judgement as judgement')
                            ->get();
            if ($this->checkIfExistObject($iqc_data) > 0) {
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$req->receivingno)
                    ->update([
                        'app_date' => $app_date,
                        'app_time' => $app_time,
                        'app_by' => Auth::user()->user_id
                    ]);

                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('wbs_mr_id',$req->receivingno)
                    ->where('app_date',null)
                    ->where('app_time',null)
                    ->where('app_by',null)
                    ->update([
                        'app_date' => $app_date,
                        'app_time' => $app_time,
                        'app_by' => Auth::user()->user_id
                    ]);

                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('wbs_mr_id',$req->receivingno)
                    ->where('app_date',null)
                    ->where('app_time',null)
                    ->where('app_by',null)
                    ->update([
                        'app_date' => $app_date,
                        'app_time' => $app_time,
                        'app_by' => Auth::user()->user_id
                    ]);

                Excel::create('Application_for_IQC_'.$app_date, function($excel) use($iqc_data,$company_info,$app_date,$app_time)
                {
                    $excel->sheet('Report', function($sheet) use($iqc_data,$company_info,$app_date,$app_time)
                    {
                        $sheet->setHeight(1, 15);
                        $sheet->mergeCells('A1:G1');
                        $sheet->cells('A1:G1', function($cells) {
                            $cells->setAlignment('center');
                        });
                        $sheet->cell('A1',$company_info['name']);

                        $sheet->setHeight(2, 15);
                        $sheet->mergeCells('A2:G2');
                        $sheet->cells('A2:G2', function($cells) {
                            $cells->setAlignment('center');
                        });
                        $sheet->cell('A2',$company_info['address']);

                        $sheet->setHeight(4, 20);
                        $sheet->mergeCells('A4:G4');
                        $sheet->cells('A4:G4', function($cells) {
                            $cells->setAlignment('center');
                            $cells->setFont([
                                'family'     => 'Calibri',
                                'size'       => '14',
                                'bold'       =>  true,
                                'underline'  =>  true
                            ]);
                        });
                        $sheet->cell('A4',"APPLICATION FOR IQC INSPECTION");

                        $sheet->setHeight(6, 15);
                        $sheet->cells('A12:G12', function($cells) {
                            $cells->setFont([
                                'family'     => 'Calibri',
                                'size'       => '11',
                                'bold'       =>  true,
                            ]);
                            // Set all borders (top, right, bottom, left)
                            $cells->setBorder('solid', 'solid', 'solid', 'solid');
                        });
                        $sheet->cell('A6', "CONTROL:");
                        $sheet->cell('B6', $iqc_data[0]->receive_no);
                        $sheet->cell('E6', "DATE:");
                        $sheet->cell('F6', $app_date);

                        $sheet->cell('A8', "INVOICE #:");
                        $sheet->cell('B8', $iqc_data[0]->receive_no);
                        $sheet->cell('E8', "TIME:");
                        $sheet->cell('F8', $app_time);

                        $sheet->cell('A10', "SERIES NAME / P.O. #");

                        $sheet->cell('A12', "Part Code");
                        $sheet->cell('B12', "Material Name");
                        $sheet->cell('C12', "Supplier / Vendo");
                        $sheet->cell('D12', "Quantity");
                        $sheet->cell('E12', "Lot #");
                        $sheet->cell('F12', "Drawing No.");
                        $sheet->cell('G12', "IQC Result");

                        $row = 13;

                        foreach ($iqc_data as $key => $iqc) {
                            $sheet->setHeight($row, 15);
                            $sheet->cell('A'.$row, $iqc->item);
                            $sheet->cell('B'.$row, $iqc->item_desc);
                            $sheet->cell('C'.$row, $iqc->supplier);
                            $sheet->cell('D'.$row, $iqc->qty);
                            $sheet->cell('E'.$row, $iqc->lot_no);
                            $sheet->cell('F'.$row, $iqc->drawing_num);
                            $sheet->cell('G'.$row, $iqc->judgement);
                            $row++;
                        }

                        $sheet->cells('A12:G'.$row, function($cells) {
                            $cells->setBorder('solid', 'solid', 'solid', 'solid');
                        });

                        $row = $row+2;

                        $sheet->cell('A'.$row, "PREPARED BY:");
                        $sheet->cell('B'.$row, "");
                        $sheet->cell('E'.$row, "RECEIVED BY:");
                        $sheet->cell('F'.$row, "");
                    });
                })->download('xlsx');
            } else {
                $message = "Please batch Invoice Items first.";
                return redirect('/materialreceiving')->with(['err_message' => $message]);
            }
        }
    }

    public function printMaterialReceive(Request $req)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);

        $company_info = $this->com->getCompanyInfo();

        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$req->receivingno)
                    ->count();
        if ($cnt > 0) {
            $mr = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$req->receivingno)->first();

            $mrdata = [
                'receive_no' => $mr->receive_no,
                'receive_date' => $mr->receive_date,
                'invoice_no' => $mr->invoice_no,
                'invoice_date' => $mr->invoice_date,
                'pallet_no' => $mr->pallet_no,
                'total_qty' => number_format($mr->total_qty),
                'total_var' => $this->getVariance($mr->receive_no,$mr->total_qty),
                'total_amt' => $mr->total_amt,
                'status' => $this->getStatus($mr->receive_no,$mr->status),
            ];
            $receiveno   = $mrdata['receive_no'];
            $receivedate = $mrdata['receive_date'];
            $invoiceno   = $mrdata['invoice_no'];
            $invoicedate = $mrdata['invoice_date'];
            $palletno    = $mrdata['pallet_no'];
            $totalqty    = $mrdata['total_qty'];
            $totalamt    = $mrdata['total_amt'];
            $status      = $mrdata['status'];

            $mr_details_data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving as r')
                            ->join('tbl_wbs_material_receiving_details as d', 'd.wbs_mr_id', '=', 'r.receive_no')
                            ->join('tbl_wbs_material_receiving_summary as s', 's.item', '=', 'd.item')
                            ->leftJoin(DB::raw("(SELECT wbs_mr_id, item,
                                                GROUP_CONCAT(id SEPARATOR ', ') as id,
                                                GROUP_CONCAT(FORMAT(qty,2) SEPARATOR ', ') as qty,
                                                GROUP_CONCAT(FORMAT(box_qty,2) SEPARATOR ', ') as box_qty,
                                                GROUP_CONCAT(lot_no SEPARATOR ', ') as lot_no,
                                                GROUP_CONCAT(location SEPARATOR ', ') as location
                                            FROM tbl_wbs_material_receiving_batch
                                            GROUP BY wbs_mr_id, item) as B"), function ($join) use($receiveno)
                                            {
                                                $join->on('B.item', '=', 'd.item')
                                                     ->where('B.wbs_mr_id', '=', $receiveno);
                                            })
                            ->whereRaw("r.receive_no ='" . $receiveno . "'
                                        AND d.wbs_mr_id = r.receive_no
                                        AND s.wbs_mr_id = r.receive_no")
                            ->select('d.wbs_mr_id'
                                    ,'d.item'
                                    , 'd.item_desc'
                                    , DB::raw('FORMAT(d.qty,2) AS qty')
                                    , DB::raw('FORMAT(s.received_qty,2) AS received_qty')
                                    , DB::raw('FORMAT(s.variance,2) AS variance')
                                    , 'B.id AS batch_id'
                                    , 'B.qty AS batch_qty'
                                    , 'B.box_qty AS box_qty'
                                    , 'B.lot_no'
                                    , 'B.location')
                            ->orderBy('d.item')
                            ->get();
        }
        else
        {
            $receiveno = '';
            $receivedate = '';
            $invoiceno = '';
            $invoicedate = '';
            $palletno = '';
            $totalqty = '';
            $totalamt = '';
            $status = '';
            $mr_details_data = [];
        }

        $html = '
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
                            <p style="line-height: 1.8px; font-size:12px; "> '. $company_info['tel1'] . ' ' . $company_info['tel2'] .'</p>
                            <h2><ins>MATERIAL RECEIVING</ins></h2>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="fontArial" border="0" cellpadding="3" cellspacing="3" style="width: 100%;  font-size:12px;">
                    <tbody>
                        <tr>
                            <td style="width: 80px;">Receive No. :</td>
                            <td colspan="2">'. $receiveno .'</td>
                            <td>&nbsp;</td>
                            <td style="width: 80px;">Pallet No, :</td>
                            <td colspan="2">'. $palletno .'</td>
                        </tr>
                        <tr>
                            <td style="width: 80px;">Receive Date :</td>
                            <td colspan="2">'. $receivedate .'</td>
                            <td>&nbsp;</td>
                            <td style="width: 80px;">Total Qty. :</td>
                            <td colspan="2">'. $totalqty .'</td>
                        </tr>
                        <tr>
                            <td style="width: 80px;">Invoice No. :</td>
                            <td colspan="2">'. $invoiceno .'</td>
                            <td>&nbsp;</td>
                            <td style="width: 80px;">Total Amt. :</td>
                            <td colspan="2">'. $totalamt .'</td>
                        </tr>
                        <tr>
                            <td style="width: 80px;">Invoice Date :</td>
                            <td colspan="2">'. $invoicedate .'</td>
                            <td>&nbsp;</td>
                            <td style="width: 80px;">Status :</td>
                            <td colspan="2">'. $status .'</td>
                        </tr>
                    </tbody>
                </table>
                <br/>
                <table class="fontArial"  style="border: 2px solid black ; border-collapse: collapse; width:100%; cellspacing:0; cellpadding:0; font-size:12px;">
                    <thead style="border: 2px solid black;">
                        <tr>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Item No.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Item Description</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Invoice Qty.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Receive Qty.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Variance</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Batch ID</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Qty.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Box Qty.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Lot No.</strong></th>
                            <th scope="col"><strong>Location</strong></th>
                        </tr>
                    </thead>
                    <tbody>';

            $html2 = '';
            foreach ($mr_details_data as $key => $row)
            {
            // var_dump($row);
                // if(empty($row->batch_id))
                // {
                //     $html2 = $html2 . '<tr>
                //                 <td style="border-bottom: 1px solid black;">'. $row->item .'</td>
                //                 <td style="border-bottom: 1px solid black;" >'. $row->item_desc .'</td>
                //                 <td style="border-bottom: 1px solid black; text-align: center;">'. $row->qty .'</td>
                //                 <td style="border-bottom: 1px solid black; text-align: center;">'. $row->received_qty .'</td>
                //                 <td style="border-bottom: 1px solid black; text-align: center;">'. $row->variance .'</td>
                //                 </tr>';
                // }
                // else
                // {
                    $html2 = $html2 .'<tr>
                                <td style="border-bottom: 1px solid black; text-align: center;">'. $row->item .'</td>
                                <td style="border-bottom: 1px solid black; text-align: left;" >'. $row->item_desc .'</td>
                                <td style="border-bottom: 1px solid black; text-align: right;">'. $row->qty .'</td>
                                <td style="border-bottom: 1px solid black; text-align: right;">'. $row->received_qty .'</td>
                                <td style="border-bottom: 1px solid black; border-right: 1px solid black;text-align: right;">'. $row->variance .'</td>
                                <td style="border-bottom: 1px solid black; text-align: center;">'. $row->batch_id .'</td>
                                <td style="border-bottom: 1px solid black; text-align: right;">'. $row->batch_qty .'</td>
                                <td style="border-bottom: 1px solid black; text-align: right;">'. $row->box_qty .'</td>
                                <td style="border-bottom: 1px solid black; text-align: left;">'. $row->lot_no .'</td>
                                <td style="border-bottom: 1px solid black; text-align: left;">'. $row->location .'</td>
                            </tr>';
                // }
            }

            $html3 = '</tbody>
                </table>
          </body>
        </html>';
        // echo $html;

        # gather all html parts.
        $html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $html . $html2 . $html3;

        $dompdf = new Dompdf();
        $dompdf->loadHTML($html);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();
        return $dompdf->stream('Material_Receiving_'.$req->receivingno.'_'.Carbon::now().'.pdf');

        // $pdf = PDF::loadHTML($html)->setPaper('letter', 'landscape');
        // return $pdf->stream('Material Receiveing'.Carbon::now().'.pdf');

      /*  # apply snappy pdf wrapper
        $pdf = App::make('snappy.pdf.wrapper');
        # transform html to pdf format.
        $pdf->loadHTML($html)->setPaper('A4')->setOrientation('landscape');
        # display PDF report to response.
        return $pdf->inline();*/
    }

    private function checkIfIQCbatchExist($receiveno)
    {
       $iqc_data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch as b')
                            ->join('tbl_wbs_material_receiving as r','b.wbs_mr_id','=','r.receive_no')
                            ->where('b.wbs_mr_id', $receiveno)
                            ->where('b.for_kitting', '0')
                            ->where('b.iqc_status', '0')
                            ->count();
        return $iqc_data;
    }

    private function getBatchItem($receivingno)
    {
        $batchdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->where('wbs_mr_id',$receivingno)->get();

        return $data = [
                    'batchdata' => $batchdata,
                    'request_status' => 'success'
                ];
    }

    public function postDeleteBatchItem_old(Request $req)
    {
        $count_id = count($req->ids);
        $cnt = 0;
        foreach ($req->ids as $key => $id) {
            $cnt++;
            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                ->where('wbs_mr_id',$req->receivingno)
                ->where('id',$id)
                ->delete();
            DB::connection($this->mysql)->table('tbl_wbs_inventory')
                ->where('wbs_mr_id',$req->receivingno)
                ->where('mat_batch_id',$id)
                ->delete();
            $summarydata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                            ->where('wbs_mr_id',$req->receivingno)
                            ->where('item',$req->items[$key])
                            ->select('received_qty')
                            ->first();

            $deducttoreceivedqty = $summarydata->received_qty - $req->qtys[$key];

            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                ->where('wbs_mr_id',$req->receivingno)
                ->where('item',$req->items[$key])
                ->update([
                    'variance' => $req->qtys[$key],
                    'received_qty' => $deducttoreceivedqty
                ]);
        }

        if ($count_id == $cnt) {
            return $this->getMRdata($req->receivingno);
        } else {
            return $data = [
                    'msg' => 'Deleting Batch Item failed.',
                    'request_status' => 'failed'
                ];
        }
    }

    //armando 2024-01-23
    public function postDeleteBatchItem(Request $req)
    {
        $params = $req->params;
        $receivingno = $params['receivingno'];
        $delete_batch_arr = $params['delete_batch_arr'];
        $isSuccess = false;
        $count_id = count($req->ids);
        $cnt = 0;
        DB::connection($this->mysql)->beginTransaction();
        try {
            foreach ($delete_batch_arr as $key) {
                $cnt++;
                $batch_id = (int)$key['batch_id'];
                $qty = (int)$key['qty'];
                $item = $key['item'];
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('wbs_mr_id',$receivingno)
                    ->where('id',$batch_id)
                    ->delete();
                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('wbs_mr_id',$receivingno)
                    ->where('mat_batch_id',$batch_id)
                    ->delete();
                $summarydata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                                ->where('wbs_mr_id',$receivingno)
                                ->where('item',$item)
                                ->select('received_qty')
                                ->first();
                $deducttoreceivedqty = $summarydata->received_qty - $qty;
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->where('wbs_mr_id',$receivingno)
                    ->where('item',$req->items[$key])
                    ->update([
                        'variance' => $req->qtys[$key],
                        'received_qty' => $deducttoreceivedqty
                    ]);
            }
            $isSuccess = true;
        } catch (\Exception $th) {
            $isSuccess = false;
        }
        if($isSuccess) {
            DB::connection($this->mysql)->commit();
            return $this->getMRdata($receivingno);
        }else {
            DB::connection($this->mysql)->rollBack();
            return [
                'msg' => 'Deleting Batch Item failed.',
                'request_status' => 'failed'
            ];
        }
    }

    public function getItems(Request $req)
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
        $invoice_no = (!isset($req->invoice_no))? "" : $req->invoice_no;

        try {
            if ($addOptionVal != "" && $display == "id&text") {
                array_push($results, [
                    'id' => $addOptionVal,
                    'text' => $addOptionText
                ]);
            }

            if ($sql_query == null || $sql_query == "") {
                    $sql_query = "SELECT DISTINCT S.CODE AS id, 
                                                CONCAT(S.CODE, ' | ', H.NAME) AS [text], 
                                                Z.RACKNO AS [location]
                                FROM XSACT as S
                                LEFT JOIN (SELECT DISTINCT z.CODE, z.RACKNO 
                                            FROM XZAIK as z 
                                            WHERE z.JYOGAI = 0
                                            AND z.RACKNO <> '') AS Z
                                on Z.CODE = S.CODE
                                JOIN XHEAD as H
                                on H.CODE = S.CODE
                                WHERE S.INVOICE_NUM <> ''
                                AND S.INVOICE_NUM = '".$invoice_no."'";
            }
            
            $db = DB::connection($this->mysql)->select($sql_query);

            foreach ($db as $key => $d) {
                array_push($results, [
                    'id' => $d->id,
                    'text' => $d->text,
                    // 'location' => $d->location
                ]);
            }

        } catch(\Exemption $e) {
            return [
                'success' => false,
                'msessage' => $e->getMessage()
            ];
        }
        
        return $results;
    }

    public function getItemData(Request $req)
    {
        $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw("(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0 and HOKAN = 'WHS100') AS Z"), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $req->invoice_no)
                ->where('S.CODE', '=', $req->itemcode)
                ->where('Z.RACKNO','<>','')
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->get();

        if ($this->checkIfExistObject($data) > 0) {
            return $data;
        } else {
            $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw("(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0 and HOKAN = 'WHS100') AS Z"), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $req->invoice_no)
                ->where('S.CODE', '=', $req->itemcode)
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->get();

            return $data;
        }
    }

    public function getPackage(Request $req)
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
        $invoice_no = (!isset($req->invoice_no))? "" : $req->invoice_no;

        try {
            if ($addOptionVal != "" && $display == "id&text") {
                array_push($results, [
                    'id' => $addOptionVal,
                    'text' => $addOptionText
                ]);
            }

            if ($sql_query == null || $sql_query == "") {
                    $sql_query = "SELECT description as id, description as `text` FROM tbl_package_category";
            }
            
            $db = DB::connection($this->mysql)->select($sql_query);

            foreach ($db as $key => $d) {
                array_push($results, [
                    'id' => $d->id,
                    'text' => $d->text
                ]);
            }

        } catch(\Exemption $e) {
            return [
                'success' => false,
                'msessage' => $e->getMessage()
            ];
        }
        
        return $results;
        // return DB::connection($this->mysql)->table('tbl_package_category')
        //             ->select('description as id', 'description as text')
        //             ->get();
    }

    public function getSingleBatchItem(Request $req)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('id',$req->id)
                    ->select('item',
                            'item_desc',
                            'qty',
                            'box',
                            'box_qty',
                            'lot_no',
                            'location',
                            'supplier')
                    ->get();
        if ($this->checkIfExistObject($data) > 0) {
            return $data;
        }
    }

    private function getDetails($invoice_no)
    {
        $ypics = DB::connection($this->mssql)
                    ->table('XSACT AS S')
                    ->join('XHEAD AS H', 'H.CODE' ,'=','S.CODE')
                    ->where('S.INVOICE_NUM', $invoice_no)
                    ->select('S.INVOICE_NUM as invoiceno'
                        , 'S.CODE as item'
                        , 'H.NAME as description'
                        , DB::raw('CAST(S.JITU AS VARCHAR) as qty')
                        , 'S.PORDER as pr'
                        , DB::raw('CAST(S.APRICE AS VARCHAR) as price')
                        , 'S.KOUNYUUGAKU as amount')
                    ->get();
        $this->utf8_encode_deep($ypics);

        $data = [];

        foreach ($ypics as $key => $yp) {
            array_push($data, [
                    'item' => $yp->item,
                    'description' => $this->convert_unicodeJIS($yp->description),
                    'qty' => $yp->qty,
                    'pr' => $yp->pr,
                    'price' => $yp->price,
                    'amount' => $yp->amount
                ]);
        }
        return $data;
    }

    private function getSummary($invoice_no)
    {
        $data = DB::connection($this->mssql)
                    ->table('XSACT AS S')
                    ->leftJoin('XHEAD AS H', 'H.CODE' ,'=','S.CODE')
                    ->select(DB::raw("'' AS id")
                        , 'S.CODE as item'
                        , 'H.NAME as description'
                        , DB::raw("CAST(SUM(S.JITU) AS VARCHAR) as qty")
                        , DB::raw("CAST(SUM(S.HVOL) AS VARCHAR) as r_qty")
                        , DB::raw("CAST(SUM(S.JITU) - SUM(S.HVOL) AS VARCHAR) as variance")
                        , 'S.VENDOR as vendor'
                        )
                    ->where('S.INVOICE_NUM', $invoice_no)
                    ->groupBy('S.CODE', 'H.NAME','S.VENDOR')
                    ->get();
        $this->utf8_encode_deep($data);
        return $data;
    }

    public function getSummaryInvoice($invoice_no)
    {
        $ypics = DB::connection($this->mssql)
                    ->table('XSACT AS S')
                    ->leftJoin('XHEAD AS H', 'H.CODE' ,'=','S.CODE')
                    ->select(DB::raw("'' AS id")
                        , 'S.CODE as item'
                        , 'H.NAME as description'
                        , DB::raw("CAST(SUM(S.JITU) AS VARCHAR) as qty")
                        , DB::raw("CAST(SUM(S.HVOL) AS VARCHAR) as r_qty")
                        , DB::raw("CAST(SUM(S.JITU) - SUM(S.HVOL) AS VARCHAR) as variance")
                        , 'S.VENDOR as vendor'
                        )
                    ->where('S.INVOICE_NUM', $invoice_no)

                    ->groupBy('S.CODE', 'H.NAME','S.VENDOR')
                    ->get();
        

         // $refresh_iqc_status = DB::connection($this->mssql)->select("SELECT S.CODE as item, H.NAME as description, S.VENDOR as vendor
         //        FROM [XSACT] AS S LEFT JOIN XHEAD AS H ON H.CODE = S.CODE where S.INVOICE_NUM = '". $invoiceno."' AND S.VENDOR IN ('PPD','PPS','PCC')
         //        GROUP BY S.CODE,H.NAME,S.VENDOR
         //        ");   

       // $ypics = DB::connection($this->mssql)
       //          ->select("SELECT '' AS id, S.CODE as item, H.NAME as description,
       //                   CAST(SUM(S.JITU) AS VARCHAR) AS qty,
       //                   CAST(SUM(S.HVOL) AS VARCHAR) AS r_qty,
       //                   CAST(SUM(S.JITU) - SUM(S.HVOL) AS VARCHAR) as variance,
       //                   S.VENDOR as vendor,
       //                   FROM XSACT AS S LEFT JOIN XHEAD AS H ON H.CODE = S.CODE where S.INVOICE_NUM = '". $invoiceno."' AND S.VENDOR IN ('PPD','PPS','PCC')
       //                   GROUP BY S.CODE,H.NAME,S.VENDOR");

        $this->utf8_encode_deep($ypics);

        $data = [];

        // dd($ypics);
        foreach ($ypics as $key => $yp) {
            $checkNR = DB::connection($this->main_mysql)->table('tbl_iqc_matrix')
                        ->select('item')
                        ->where('item',$yp->item)
        
                        ->count();
       // dd($checkNR                 
            if ($checkNR > 0) {
                array_push($data, [
                    'id' => $yp->id,
                    'item' => $yp->item,
                    'description' => $this->convert_unicodeJIS($yp->description),
                    'qty' => $yp->qty,
                    'r_qty' => $yp->r_qty,
                    'variance' => $yp->variance,
                    'vendor' => $yp->vendor,
                    'nr' => 1
                ]);
            } else {
                array_push($data, [
                    'id' => $yp->id,
                    'item' => $yp->item,
                    'description' => $this->convert_unicodeJIS($yp->description),
                    'qty' => $yp->qty,
                    'r_qty' => $yp->r_qty,
                    'variance' => $yp->variance,
                    'vendor' => $yp->vendor,
                    'nr' => 0
                ]);
            }
        }
        return $data;
    }

    private function convert_unicodeJIS($str)
    {
        /*if (mb_detect_encoding($str, 'UTF-8', true) === false) {
            $str = mb_convert_encoding($str, 'UTF-8', 'SJIS');
        }*/
        $str = mb_convert_encoding($str, 'UTF-8','SJIS');
        
        return $str;
    }

    private function utf8_encode_deep(&$input) 
    {
        if (is_string($input)) {
            //$input = utf8_encode($input);
            mb_convert_encoding($input,"UTF-8","SJIS");
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                if (is_object($value)) {
                    $vals = array_keys(get_object_vars($value));

                    foreach ($vals as $val) {
                        mb_convert_encoding($val,"UTF-8","SJIS");
                    }
                } else {
                    mb_convert_encoding($value,"UTF-8","SJIS");
                }

            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                mb_convert_encoding($var,"UTF-8","SJIS");
            }
        }
    }

    public function notForIQC(Request $req)
    {
        $check = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->where('wbs_mr_id',$req->receivingno)
                    ->where('item',$req->item)
                    ->where('not_for_iqc',1)
                    ->count();
        if ($check > 0) {
            return $data = [
                        'check' => '1',
                    ];
        } else {
            return $data = [
                        'check' => '',
                    ];
        }
    }

    private function getDrawingNum($item)
    {
        $db = DB::connection($this->mssql)
                ->table('XITEM')
                ->select('DRAWING_NUM as drawing_num')
                ->where('CODE',$item)
                ->first();
        return $db->drawing_num;
    }

    private function checkIfBatchExist($id)
    {
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('id',$id)
                    ->count();
        if ($cnt > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function UpdateSummaryReceivedQty($mr,$item,$qty)
    {
        DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
            ->where('wbs_mr_id',$mr)
            ->where('item',$item)
            ->update(['received_qty' => $qty]);
    }

    private function number_unformat($number, $force_number = true, $dec_point = '.', $thousands_sep = ',')
    {
        if ($force_number) {
            $number = preg_replace('/^[^\d]+/', '', $number);
        } else if (preg_match('/^[^\d]+/', $number)) {
            return false;
        }
        $type = (strpos($number, $dec_point) === false) ? 'int' : 'float';
        $number = str_replace(array($dec_point, $thousands_sep), array('.', ''), $number);
        settype($number, $type);
        return $number;
    }

    private function getTotalQty($receivingno)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                ->where('receive_no',$receivingno)
                ->select('total_qty')
                ->first();
        return $data->total_qty;
    }

    public function postCancelMr(Request $req)
    {
        $query = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('receive_no',$req->receivingno)->update([
                        'status' => 'C',
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('m/d/Y h:i A')
                    ]);

        if ($query) {
            DB::connection($this->mysql)->table('tbl_wbs_inventory')
                ->where('wbs_mr_id',$req->receivingno)
                ->delete();

            return $this->getMRdata($req->receivingno);
        } else {
            return $data = [
                    'msg' => 'Cancelling Invoice failed.',
                    'request_status' => 'failed'
                ];
        }
    }

    public function BatchItemExcel(Request $req)
    {
        $file = $req->file('batchfiles');
        $errMatno = '';
        $errInvoice = '';
        $errItem = '';
        $msg = '';
        $value = '';

        $error = '';

        Excel::load($file, function($reader) use($errMatno, $errInvoice, $errItem, $error, $msg){


            $results = $reader->get();
            $fields = $results->toArray();
            $iqc_status = 0;
            $for_kitting = 0;

            foreach ($fields as $key => $field) {
                //if (array_key_exists('receivingno', $field)) {
                    if ($this->checkItemIfExist($field['receivingno'],$field['item'],$field['lotno']) > 0) {

                    } else {
                        if ($field['not_required_iqc'] == 1) {
                            $iqc_status = 1;
                            $for_kitting = 1;
                        }

                        // filter
                        if ($this->checkMatNumber($field['receivingno']) < 1) {
                            $error = true;
                            $errMatno = $field['receivingno'];
                            break;
                        }

                        if ($this->checkInvoice($field['invoiceno']) < 1) {
                            $error = true;
                            $errInvoice = $field['invoiceno'];
                            break;
                        }

                        if ($this->checkItem($field['invoiceno'],$field['item']) < 1) {
                            $error = true;
                            $errItem = $field['item'];
                            break;
                        }

                        if ($error == false) {
                            $this->saveToBatch($field['receivingno'],
                                        $field['invoiceno'],
                                        $field['item'],
                                        $field['qty'],
                                        $field['package_category'],
                                        $field['package_qty'],
                                        $field['lotno'],
                                        $field['supplier'],
                                        $field['not_required_iqc'],
                                        $iqc_status,
                                        $for_kitting,
                                        $field['received_date']);
                        }
                    }
                // } else {
                //     $error = true;
                //     $msg = "Invalid upload format.";
                //     break;
                // }
            }
        });

        if ($error == true) {
            $data = [
                'return_status' => 'failed',
                'receivingno' => $errMatno,
                'item' => $errItem,
                'invoice' => $errInvoice,
                'msg' => $msg
            ];
        }
        if ($error == false) {
            $data = [
                'return_status' => 'success',
                'receivingno' => $this->lastinsertmr(),
                'vals' => $value
            ];
        }

        return json_encode($data);
    }

    public function BatchItemExcel_new(Request $req) {
        $file = $req->file('batchfiles');
        $receiveNoupload = $req->receiveNoupload;
        $errMatno = '';
        $errInvoice = '';
        $errItem = '';
        $msg = '';
        $value = '';
        $error = false;

        Excel::load($file, function ($reader) use (&$errMatno, &$errInvoice, &$errItem, &$msg,&$value,&$receiveNoupload,&$error) {
            $r = $reader->get();
            $fields = $r->toArray();
            $result = [];
            foreach ($fields as $key => $field) {
                if(($receiveNoupload == $field['receivingno']) && ($receiveNoupload != null && $field['item'] != null && $field['lotno'] != null)) {
                    $iqc_status = 0;
                    $for_kitting = 0;
                    if (trim((int)$field['not_required_iqc']) == 1) {
                        $iqc_status = 1;
                        $for_kitting = 1;
                    }

                    if ($this->checkMatNumber($field['receivingno']) < 1) {
                        $msg = 'Invalid Receiving No. :'.$field['receivingno'];
                        $errMatno = $field['receivingno'];
                        $error = true;
                        break;
                    }
                    //YPICS Invoice
                    else if($this->checkInvoice($field['invoiceno']) < 1) {
                        $msg = 'Invoice No not found :'.$field['invoiceno'];
                        $errInvoice = $field['invoiceno'];
                        $error = true;
                        break;
                    }
                    //YPICS Items
                    else if($this->checkItem($field['invoiceno'],$field['item']) < 1) {
                        $msg = 'Item not found :'.$field['item'];
                        $errItem = $field['item'];
                        $error = true;
                        break;
                    }

                    array_push($result,[
                        'receivingno' => trim($receiveNoupload),
                        'item' => trim($field['item']),
                        'lotno' => trim($field['lotno']),
                        'not_required_iqc' => trim((int)$field['not_required_iqc']),
                        'invoiceno' => trim($field['invoiceno']),
                        'qty' => (int)trim($field['qty']),
                        'package_category' => trim($field['package_category']),
                        'package_qty' => (int)trim($field['package_qty']),
                        'supplier' => trim($field['supplier']),
                        'received_date' => trim($field['received_date']),
                        'for_kitting' => $for_kitting,
                        'iqc_status' => $iqc_status,
                    ]);
                    $error = false;
                }else {
                    $msg = 'Invalid Receiving No. :'.$field['receivingno'];
                    $errMatno = $field['receivingno'];
                    $error = true;
                    break;
                }
            }
            if($error == false) {
                $grp = [];
                foreach ($result as $details) {
                    $arr = array_filter($grp, function ($r) use ($details) {
                        return $r['lotno'] == $details['lotno'] && $r['item'] == $details['item'];
                    });
                
                    if (empty($arr)) {
                        $filter_ = array_filter($result, function ($r) use ($details) {
                            return $r['lotno'] == $details['lotno'] && $r['item'] == $details['item'];
                        });
                        $grp[] = [
                            'lotno' => $details['lotno'],
                            'item' => $details['item'],
                            'invoiceno' => $details['invoiceno'],
                        ];
                    }
                }
                $temp_data = [];
                $xIndex = 0;
                try {
                    DB::connection($this->mysql)->beginTransaction();
                    foreach ($grp as $key) {
                        $lotno = $key['lotno'];
                        $item = $key['item'];

                        //$item_desc = 'test-item_desc';
                        //$location = 'test-location';
                        $details = $this->getItemsDetails($key['invoiceno'],$key['item']);
                        $item_desc = $details->name;
                        $location = $details->rackno;
                        
                        //$drawing_num = 'test-drawing_num';
                        $drawing_num = $this->getDrawingNum($item);

                        $arr = array_filter($result, function ($r) use ($key) {
                            return $r['lotno'] == $key['lotno'] && $r['item'] == $key['item'];
                        });
                        if(count($arr) > 1) {
                            $noLot = 1;
                            foreach ($arr as $c) {
                                array_push($temp_data,[
                                    'wbs_mr_id' => $c['receivingno'],
                                    'item' => $c['item'],
                                    'item_desc' => $item_desc,
                                    'lot_no' => $c['lotno'].'.'.$noLot.'/'.count($arr),
                                    'location' => $location,
                                    'not_required_iqc' => $c['not_required_iqc'],
                                    'invoiceno' => $c['invoiceno'],
                                    'qty' => $c['qty'],
                                    'package_category' => $c['package_category'],
                                    'package_qty' => $c['package_qty'],
                                    'supplier' => $c['supplier'],
                                    'drawing_num' => $drawing_num,
                                    'received_date' => $c['received_date'],
                                    'for_kitting' => $c['for_kitting'],
                                    'iqc_status' => $c['iqc_status'],
                                    'is_printed' => 0,
                                    'iqc_result' => '',
                                    'not_for_iqc' => $c['not_required_iqc'],
                                    'create_user' => Auth::user()->user_id,
                                    'created_at' =>  date('Y-m-d H:i:s'),
                                    'update_user' => Auth::user()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                                $noLot++;
                                $xIndex++;
                            }
                        }else {
                            $d = $arr[$xIndex];
                            array_push($temp_data,[
                                'wbs_mr_id' => $d['receivingno'],
                                'item' => $d['item'],
                                'item_desc' => $item_desc,
                                'lot_no' => $d['lotno'],
                                'location' => $location,
                                'not_required_iqc' => $d['not_required_iqc'],
                                'invoiceno' => $d['invoiceno'],
                                'qty' => $d['qty'],
                                'package_category' => $d['package_category'],
                                'package_qty' => $d['package_qty'],
                                'supplier' => $d['supplier'],
                                'drawing_num' => $drawing_num,
                                'received_date' => $d['received_date'],
                                'for_kitting' => $d['for_kitting'],
                                'iqc_status' => $d['iqc_status'],
                                'is_printed' => 0,
                                'iqc_result' => '',
                                'not_for_iqc' => $d['not_required_iqc'],
                                'create_user' => Auth::user()->user_id,
                                'created_at' =>  date('Y-m-d H:i:s'),
                                'update_user' => Auth::user()->user_id,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            $xIndex++;
                        }
                    }

                    foreach ($temp_data as $v) {
                        $qty = str_replace(',','',$v['qty']);
                        $package_qty = str_replace(',','',$v['package_qty']);
                        $supplier = strtoupper($v['supplier']);
                        $receivingno = $v['wbs_mr_id'];
                        $item = $v['item'];
                        $mat_batch_id = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->insertGetId([
                            'wbs_mr_id' => $receivingno,
                            'invoice_no' => $v['invoiceno'],
                            'item' => $item,
                            'item_desc' => $v['item_desc'],
                            'qty' => $qty,
                            'box' => $v['package_category'],
                            'box_qty' => $package_qty,
                            'lot_no' => $v['lot_no'],
                            'location' => $v['location'],
                            'supplier' => $supplier,
                            'drawing_num' => $v['drawing_num'],
                            'iqc_status' => $v['iqc_status'],
                            'is_printed' => $v['is_printed'],
                            'for_kitting' => $v['for_kitting'],
                            'not_for_iqc' => $v['not_for_iqc'],
                            'iqc_result' => $v['iqc_result'],
                            'received_date' => $v['received_date'],
                            'create_user' => $v['create_user'],
                            'created_at' =>  date('Y-m-d H:i:s'),
                            'update_user' => $v['update_user'],
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        DB::connection($this->mysql)->table('tbl_wbs_inventory')->insert([
                            'wbs_mr_id' => $receivingno,
                            'invoice_no' => $v['invoiceno'],
                            'item' => $item,
                            'item_desc' => $v['item_desc'],
                            'qty' => $qty,
                            'box' => $v['package_category'],
                            'box_qty' => $package_qty,
                            'lot_no' => $v['lot_no'],
                            'location' => $v['location'],
                            'supplier' => $supplier,
                            'drawing_num' => $v['drawing_num'],
                            'iqc_status' => $v['iqc_status'],
                            'is_printed' => $v['is_printed'],
                            'for_kitting' => $v['for_kitting'],
                            'not_for_iqc' => $v['not_for_iqc'],
                            'iqc_result' => $v['iqc_result'],
                            'received_date' => $v['received_date'],
                            'create_user' => $v['create_user'],
                            'created_at' =>  date('Y-m-d H:i:s'),
                            'update_user' => $v['update_user'],
                            'updated_at' => date('Y-m-d H:i:s'),
                            'mat_batch_id' => $mat_batch_id
                        ]);
                        $this->calculateQty($receivingno,$item,$qty);
                        $status = 'O';
                        $total_var = $this->getVariance($receivingno,$this->getTotalQty($receivingno));
                        if ($total_var < 1) {
                            $status = 'X';
                        }
                        DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->where('receive_no',$receivingno)->update([
                            'total_var' => $total_var,
                            'status' => $status,
                            'update_user' => Auth::user()->user_id,
                            'updated_at' => Carbon::now()
                        ]);
                    }

                    $error = false;
                } catch (\Exception $th) {
                    $error = true;
                }
            }
            if($error){
                DB::connection($this->mysql)->rollBack();
            }else {
                DB::connection($this->mysql)->commit();
            }
        });

        if($error){
            $data = [
                'return_status' => 'failed',
                'item' => $errItem,
                'invoice' => $errInvoice,
                'receivingno' => $errMatno,
                'msg' => $msg
            ];
        }else {
            $data = [
                'return_status' => 'success',
                'receivingno' => $this->lastinsertmr(),
                'vals' => $value
            ];
        }
        return json_encode($data);
    }



    private function checkMatNumber($receivingno)
    {
        $count = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->select('receive_no')->where('receive_no',$receivingno)
                    ->count();
        return $count;
    }

    private function saveToBatch($receivingno,$invoiceno,$item,$qty,$package,$package_qty,$lotno,$supplier,$not_req,$iqc_status,$for_kitting,$received_date)
    {
        $details = $this->getItemsDetails($invoiceno,$item);
        $mat_batch_id = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->insertGetId([
            'wbs_mr_id' => $receivingno,
            'invoice_no' => $invoiceno,
            'item' => $item,
            'item_desc' => $details->name,
            'qty' => str_replace(',','',$qty),
            'box' => $package,
            'box_qty' => str_replace(',','',$package_qty),
            'lot_no' => $lotno,
            'location' => $details->rackno,
            'supplier' => strtoupper($supplier),
            'drawing_num' => $this->getDrawingNum($item),
            'iqc_status' => $iqc_status,
            'is_printed' => 0,
            'for_kitting' => $for_kitting,
            'not_for_iqc' => $not_req,
            'iqc_result' => '',
            'received_date' => $received_date,
            'create_user' => Auth::user()->user_id,
            'created_at' =>  date('Y-m-d H:i:s'),
            'update_user' => Auth::user()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        DB::connection($this->mysql)->table('tbl_wbs_inventory')->insert([
            'wbs_mr_id' => $receivingno,
            'invoice_no' => $invoiceno,
            'item' => $item,
            'item_desc' => $details->name,
            'qty' => str_replace(',','',$qty),
            'box' => $package,
            'box_qty' => str_replace(',','',$package_qty),
            'lot_no' => $lotno,
            'location' => $details->rackno,
            'supplier' => strtoupper($supplier),
            'drawing_num' => $this->getDrawingNum($item),
            'iqc_status' => $iqc_status,
            'is_printed' => 0,
            'for_kitting' => $for_kitting,
            'not_for_iqc' => $not_req,
            'iqc_result' => '',
            'received_date' => $received_date,
            'create_user' => Auth::user()->user_id,
            'created_at' =>  date('Y-m-d H:i:s'),
            'update_user' => Auth::user()->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
            'mat_batch_id' => $mat_batch_id
        ]);

        $this->calculateQty($receivingno,$item,str_replace(',','',$qty));

        $status = 'O';

        $total_var = $this->getVariance($receivingno,$this->getTotalQty($receivingno));
        if ($total_var < 1) {
            $status = 'X';
        }

        DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
            ->where('receive_no',$receivingno)->update([
                'total_var' => $total_var,
                'status' => $status,
                'update_user' => Auth::user()->user_id,
                'updated_at' => Carbon::now()
            ]);
        // DB::connection($this->mysql)->table('tbl_wbs_inventory')
        //     ->where('wbs_mr_id',$receivingno)
        //     ->update([
        //         'status' => $status,
        //         'update_user' => Auth::user()->user_id,
        //         'updated_at' => Carbon::now()
        //     ]);
    }


    private function getItemsDetails($invoice,$item)
    {
        $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw("(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0 AND HOKAN = 'WHS100') AS Z"), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $invoice)
                ->where('S.CODE', '=', $item)
                ->where('Z.RACKNO','<>','')
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->first();

        if ($this->checkIfExistObject($data) > 0) {
            return $data;
        } else {
            $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw("(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0 AND HOKAN = 'WHS100') AS Z"), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $invoice)
                ->where('S.CODE', '=', $item)
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->first();

            return $data;
        }
    }

    private function checkInvoice($invoice)
    {
        $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw('(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0) AS Z'), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $invoice)
                ->where('Z.RACKNO','<>','')
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->count();

        if ($data > 0) {
            return $data;
        } else {
            $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw('(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0) AS Z'), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $invoice)
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->count();

            return $data;
        }
    }

    private function checkItem($invoice,$item)
    {
        $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw('(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0) AS Z'), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $invoice)
                ->where('S.CODE', '=', $item)
                ->where('Z.RACKNO','<>','')
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->count();

        if ($data > 0) {
            return $data;
        } else {
            $data = DB::connection($this->mssql)
                ->table('XSACT as S')
                ->join('XHEAD as H', 'H.CODE', '=','S.CODE')
                ->leftJoin(DB::raw('(SELECT z.CODE, z.RACKNO FROM XZAIK z WHERE z.JYOGAI = 0) AS Z'), 'Z.CODE', '=','S.CODE')
                ->select('S.CODE as code','H.NAME as name', 'Z.RACKNO as rackno')
                ->where('S.INVOICE_NUM', '=', $invoice)
                ->where('S.CODE', '=', $item)
                ->groupBy('S.CODE','H.NAME', 'Z.RACKNO')
                ->count();

            return $data;
        }
    }

    private function deleteBatch($receiveno)
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                ->where('wbs_mr_id',$receiveno)
                ->delete();
    }

    private function lastinsertmr()
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                ->select('wbs_mr_id')
                ->orderBy('id','desc')->first();
        return $db->wbs_mr_id;
    }

    private function lastinsertctrlno()
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                ->select('receive_no')
                ->orderBy('id','desc')->first();
        return $db->receive_no;
    }

    private function calculateQty($receiveno,$item,$qty)
    {
        $old =  DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->select('qty','received_qty','variance')
                    ->where('wbs_mr_id',$receiveno)
                    ->where('item',$item)
                    ->first();

        $newreceivedqty = $qty + $old->received_qty;
        $newvariance = $old->qty - $qty;

        DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
            ->where('wbs_mr_id',$receiveno)
            ->where('item',$item)
            ->update([
                'received_qty' => $newreceivedqty,
                'variance' => $newvariance
            ]);
    }

    private function DisplaySummary($receiveno)
    {
        $db = DB::connection($this->mysql)
                ->select("SELECT s.id,s.wbs_mr_id, s.item, s.item_desc,
                                s.qty, IFNULL(mrs.received_qty,0.0000) AS received_qty,
                                IFNULL(mrs.variance,s.qty) AS variance,
                                s.not_for_iqc,
                                s.for_kitting
                        FROM tbl_wbs_material_receiving_summary s
                        LEFT JOIN
                        (SELECT rs.wbs_mr_id, rs.item, SUM(b.qty) as received_qty, (rs.qty - SUM(b.qty)) as variance
                        FROM tbl_wbs_material_receiving_summary rs
                            LEFT JOIN tbl_wbs_material_receiving_batch b
                            ON b.wbs_mr_id = rs.wbs_mr_id AND b.item = rs.item
                        WHERE b.wbs_mr_id = '".$receiveno."'
                        GROUP BY rs.item)mrs ON s.wbs_mr_id = mrs.wbs_mr_id
                        AND s.item = mrs.item
                        WHERE s.wbs_mr_id = '".$receiveno."'");

        if (count((array)$db) > 0) {
            $db = DB::connection($this->mysql)
                ->select("SELECT s.id,s.wbs_mr_id, s.item, s.item_desc,
                                s.qty, IFNULL(mrs.received_qty,0.0000) AS received_qty,
                                IFNULL(mrs.variance,s.qty) AS variance,
                                s.not_for_iqc,
                                s.for_kitting
                        FROM tbl_wbs_material_receiving_summary s
                        LEFT JOIN
                        (SELECT rs.wbs_mr_id, rs.item, SUM(b.qty) as received_qty, (rs.qty - SUM(b.qty)) as variance
                        FROM tbl_wbs_material_receiving_summary rs
                            LEFT JOIN tbl_wbs_material_receiving_batch b
                            ON b.wbs_mr_id = rs.wbs_mr_id AND b.item = rs.item
                        WHERE b.wbs_mr_id = '".$receiveno."'
                        GROUP BY rs.item)mrs ON s.wbs_mr_id = mrs.wbs_mr_id
                        AND s.item = mrs.item
                        WHERE s.wbs_mr_id = '".$receiveno."'");
        } else {
            $db = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->where('wbs_mr_id',$receiveno)->get();
        }

        return $db;
    }

    private function UpdateCalculateQty($receiveno,$item,$qty)
    {
            $old =  DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                        ->select('qty','received_qty','variance')
                        ->where('wbs_mr_id',$receiveno)
                        ->where('item',$item)
                        ->first();

            if (count((array)$old)) {
                $variance = $old->qty - $qty;

                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                    ->where('wbs_mr_id',$receiveno)
                    ->where('item',$item)
                    ->update([
                        'received_qty' => $qty,
                        'variance' => $variance
                    ]);
            }
    }

    private function checkItemIfExist($receivingno,$item,$lotno)
    {
        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('wbs_mr_id',$receivingno)
                    ->where('item',$item)
                    ->where('lot_no',$lotno)
                    ->count();
        return $cnt;
    }

    private function receivedAllDetails($invoiceno,$item)
    {
        $details = DB::connection($this->ppscon)->table('vw_pps_invoice')
                        ->select('LotNo')
                        ->where('InvoiceNo',$invoiceno)
                        ->where('PartCode',$item)
                        ->first();
        if ($this->checkIfExistObject($details) > 0) {
            return $details->LotNo;
        } else {
            return '';
        }
    }

    public function receiveAll(Request $req)
    {
        //get MR Details
        $details = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')
                        ->where('wbs_mr_id',$req->receivingno)
                        ->get();
        foreach ($details as $key => $detail) {
            $itemdetails = $this->getItemsDetails($req->invoiceno,$detail->item);
            $lotno = $this->receivedAllDetails($req->invoiceno,$detail->item);
            $mat_batch_id = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')->insertGetId([
                'wbs_mr_id' => $req->receivingno,
                'invoice_no' => $req->invoiceno,
                'item' => $detail->item,
                'item_desc' => $detail->item_desc,
                'qty' => str_replace(',','',$detail->qty),
                'box' => 'Box',
                'box_qty' => str_replace(',','','1'),
                'lot_no' => $lotno,
                'location' => $itemdetails->rackno,
                'supplier' => strtoupper('PPS'),
                'drawing_num' => $this->getDrawingNum($detail->item),
                'iqc_status' => 1,
                'is_printed' => 0,
                'for_kitting' => 1,
                'not_for_iqc' => 0,
                'iqc_result' => '',
                'received_date' => $req->received_date,
                'create_user' => Auth::user()->user_id,
                'created_at' =>  date('m/d/Y h:i A'),
                'update_user' => Auth::user()->user_id,
                'updated_at' => date('m/d/Y h:i A')
            ]);

            DB::connection($this->mysql)->table('tbl_wbs_inventory')->insert([
                'wbs_mr_id' => $req->receivingno,
                'invoice_no' => $req->invoiceno,
                'item' => $detail->item,
                'item_desc' => $detail->item_desc,
                'qty' => str_replace(',','',$detail->qty),
                'box' => 'Box',
                'box_qty' => str_replace(',','','1'),
                'lot_no' => $lotno,
                'location' => $itemdetails->rackno,
                'supplier' => strtoupper('PPS'),
                'drawing_num' => $this->getDrawingNum($detail->item),
                'iqc_status' => 1,
                'is_printed' => 0,
                'for_kitting' => 1,
                'not_for_iqc' => 0,
                'iqc_result' => '',
                'received_date' => $req->received_date,
                'create_user' => Auth::user()->user_id,
                'created_at' =>  date('m/d/Y h:i A'),
                'update_user' => Auth::user()->user_id,
                'updated_at' => date('m/d/Y h:i A'),
                'mat_batch_id' => $mat_batch_id
            ]);

            $this->calculateQty($req->receivingno,$detail->item,str_replace(',','',$detail->qty));
        }
    }

    public function refeshInvoice(Request $req)
    {
        $invoiceno = $req->invoiceno;

        $data = [
            'return_status' => 'failed',
            'msg' => 'Refreshing failed.'
        ];

        $cnt = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                    ->where('invoice_no',$invoiceno)
                    ->count();

        if ($cnt > 0) {
            $mrdata = DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                        ->select('receive_no','total_qty')->where('invoice_no',$invoiceno)->first();
            // dd($mrdata);

            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                ->where('wbs_mr_id',$mrdata->receive_no)
                ->delete();

            DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')
                ->where('wbs_mr_id',$mrdata->receive_no)
                ->delete();

            $detailsdata = $this->getDetails($invoiceno);
            $summarydata = $this->getSummaryInvoice($invoiceno);

            foreach ($detailsdata as $key => $detail) {
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_details')
                    ->insert([
                        'wbs_mr_id' => $mrdata->receive_no,
                        'item' => $detail['item'],
                        'item_desc' => $detail['description'],
                        'qty' => $detail['qty'],
                        'pr_no' => $detail['pr'],
                        'unit_price' => $detail['price'],
                        'amount' => $detail['amount'],
                        'create_user' => Auth::user()->user_id,
                        'created_at' =>  date('Y-m-d h:i:s a'),
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);
            }

            foreach ($summarydata as $key => $summary) {
                $variance = 0;
                $batch = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                            ->select(DB::raw("ifnull(SUM(qty),0) as qty"),
                                    'item',
                                    'wbs_mr_id',
                                    'not_for_iqc',
                                    'for_kitting'
                            )
                            ->where('wbs_mr_id',$mrdata->receive_no)
                            ->where('item',$summary['item'])
                            ->first();

                $receivedqty = $batch->qty;
                $variance = $summary['qty'] - $receivedqty;

                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')->insert([
                    'wbs_mr_id' => $mrdata->receive_no,
                    'item' => $summary['item'],
                    'item_desc' => $summary['description'],
                    'qty' => $summary['qty'],
                    'received_qty' => $receivedqty,
                    'variance' => $summary['qty'],
                    'not_for_iqc' => $batch->not_for_iqc,
                    'for_kitting' => $batch->for_kitting,
                    'create_user' => Auth::user()->user_id,
                    'created_at' =>  date('Y-m-d h:i:s a'),
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date('Y-m-d h:i:s a')
                ]);
            }

            // $cntBatch = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
            //                 ->where('wbs_mr_id',$mrdata->receive_no)->count();
            // if ($cntBatch > 0) {
            //     $insertedsummary = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
            //                         ->where('wbs_mr_id')
            //                         ->where('item')
            //                         ->get();
            //     $variance = 0;
            //     $batch = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
            //                 ->select(DB::raw("ifnull(SUM(qty),0) as qty"),
            //                         'item',
            //                         'wbs_mr_id',
            //                         'not_for_iqc',
            //                         'for_kitting'
            //                 )
            //                 ->where('wbs_mr_id',$mrdata->receive_no)
            //                 ->where('item',$summary->item)
            //                 ->groupBy('wbs_mr_id','item')
            //                 ->first();
            //     $variance = $summary->qty - $batch->qty;

            // }
            $status = '';

            $total_var = $this->getVariance($mrdata->receive_no,$this->getTotalQty($mrdata->receive_no));
            if ($total_var < 1) {
                $status = 'X';
            }

            $mr_data = DB::connection($this->mssql)->table('XSACT')
                        ->select(DB::raw("1 AS id")
                            , 'INVOICE_NUM as invoice_no'
                            // , DB::raw("CONCAT(SUBSTRING(IDATE, 5,2), '/', SUBSTRING(IDATE, 7,2), '/', SUBSTRING(IDATE, 1,4)) AS invoice_date")
                            , DB::raw('ROUND(SUM(JITU), 2) as total_qty')
                            , DB::raw('ROUND(SUM(KOUNYUUGAKU), 2) as total_amt')
                        )
                        ->where('INVOICE_NUM', $invoiceno)
                        ->groupBy('INVOICE_NUM')
                        ->first();
            $variance = DB::connection($this->mssql)
                        ->table('XSACT AS S')
                        ->join('XHEAD AS H', 'H.CODE' ,'=','S.CODE')
                        ->select(DB::raw("CAST(ROUND(SUM(S.JITU) - SUM(S.HVOL), 4) AS VARCHAR) as variance"))
                        ->where('S.INVOICE_NUM', $invoiceno)
                        ->groupBy('S.INVOICE_NUM')
                        ->first();
            // dd($mr_data);
            DB::connection($this->mysql)->table('tbl_wbs_material_receiving')
                ->where('receive_no',$mrdata->receive_no)->update([
                    'status' => $status,
                    'total_qty' => $this->number_unformat($mr_data->total_qty), 
                    'total_var' => $this->number_unformat($variance->variance),
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date('Y-m-d h:i:s a'),
                    'update_pg' => date('Y-m-d h:i:s a')
                ]);

            $new_location =  DB::connection($this->mssql)->select("SELECT CODE, CASE WHEN [RACKNO] <> '' THEN [RACKNO]
                  ELSE 'New Code'
                  END AS location
                  FROM [XZAIK]
                  WHERE HOKAN = 'WHS100'
                  AND CODE IN (SELECT DISTINCT CODE FROM XSACT WHERE INVOICE_NUM = '". $invoiceno ."')
                  GROUP BY CODE,RACKNO");

            foreach ($new_location as $key => $value) {
                 DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('invoice_no',$invoiceno)
                    ->where('item', $value->CODE)
                    ->update([
                        'location' => $value->location
                    ]);
            }

       //               foreach ($ypics as $key => $yp) {
       //      $checkNR = DB::connection($this->main_mysql)->table('tbl_iqc_matrix')
       //                  ->select('item')
       //                  ->where('item',$yp->item)
        
       //                  ->count();
       // // dd($checkNR                 
       //      if ($checkNR > 0) {
       //          array_push($data, [
       //              'id' => $yp->id,
       //              'item' => $yp->item,
       //              'description' => $yp->description,
       //              'qty' => $yp->qty,
       //              'r_qty' => $yp->r_qty,
       //              'variance' => $yp->variance,
       //              'vendor' => $yp->vendor,
       //              'nr' => 1
       //          ]);
       //      } else {
       //          array_push($data, [
       //              'id' => $yp->id,
       //              'item' => $yp->item,
       //              'description' => $yp->description,
       //              'qty' => $yp->qty,
       //              'r_qty' => $yp->r_qty,
       //              'variance' => $yp->variance,
       //              'vendor' => $yp->vendor,
       //              'nr' => 0
       //          ]);
       //      }
       //  }




            $check_ypics = DB::connection($this->mssql)->select("SELECT S.CODE as item, H.NAME as description, S.VENDOR as vendor
                FROM [XSACT] AS S LEFT JOIN XHEAD AS H ON H.CODE = S.CODE where S.INVOICE_NUM = '". $invoiceno."'
                GROUP BY S.CODE,H.NAME,S.VENDOR
                ");
                
          
            foreach ($check_ypics as $key => $value) {
                    // dd($value);
                    $check_iqc_matrix = DB::connection($this->main_mysql)->table('tbl_iqc_matrix')
                                            ->select('item')
                                            ->where('item',$value->item)
                                            ->count();
                                             
                     if ($check_iqc_matrix > 0) {

                        // dd($check_iqc_matrix); 
                         DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
                                        // ->select('item')
                                        //->where('item',$check)
                                        ->where('item',$value->item)
                                          ->update([
                                            'not_for_iqc' => 1
                                        ]);

                          DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                                        // ->select('item')
                                        //->where('item',$check)
                                        ->where('item',$value->item)
                                        ->update([
                                            'not_for_iqc' => 1
                                       ]);                                           
                     }else{


                     }                       
              
            }

         // $countiqc =  DB::connection($this->main_mysql)->table('tbl_iqc_matrix')
         //                                ->select('item')
         //                                ->where('item',$req->item)
         //                                ->get();  

                                     
         //  if ($countiqc > 0) {

         //      $checkiqc = DB::connection($this->main_mysql)->table('tbl_iqc_matrix')
         //                                ->select('item')
         //                                ->where('item',$req->item)
         //                                ->get();  
                         
         //    foreach ($checkiqc as $key => $check) {

              
         //        // $check = DB::connection($this->mysql)->("SELECT COUNT(item) as item WHERE item ='".$item."'");
         //          DB::connection($this->mysql)->table('tbl_wbs_material_receiving_summary')
         //                                ->select('item')
         //                                //->where('item',$check)
         //                                ->where('item',$check->item)
         //                                ->update([
         //                                    'not_for_iqc' => 1
         //                                ]);

         //           DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
         //                                ->select('item')
         //                                //->where('item',$check)
         //                                ->where('item',$check->item)
         //                                ->update([
         //                                    'not_for_iqc' => 1
         //                                ]);
              
         //    }
              
         //  }else{


         //  }
        

            // dd($checkiqc);

            $data = [
                'return_status' => 'success',
                'msg' => 'Invoice data were successfully refreshed.',
                'receivingno' => $mrdata->receive_no,
                // 'value1' => $checkiqc

            ];
        }

        return $data;
    }

    public function getLocation (Request $req)
    {
         $data =  DB::connection($this->mssql)->select("SELECT CASE WHEN [RACKNO] <> '' THEN [RACKNO]
                  ELSE 'New Code'
                  END AS location
                  FROM [XZAIK]
                  WHERE HOKAN = 'WHS100'
                  AND CODE = '". $req->code ."'
                  GROUP BY CODE, RACKNO");

         return $data;
    }

    public function getReceivingSupplier(Request $req)
    {
        $name = $this->com->getDropdownByName($req->name);
        if (count((array)$name) > 0) {
            return response()->json($name);
        } else {
            $id = $this->com->getDropdownById(41);
            return response()->json($id);
        }
    }

    public function checkIfJudged($itemall,$receive_no,$checkiqc,$iqcstatus)
    {
        $mats = DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('item',$itemall)
                    ->where('wbs_mr_id',$receive_no)
                    ->get();

        foreach ($mats as $key => $mat) {
            if (intval($mat->iqc_status) > 0) {
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('item',$itemall)
                    ->where('wbs_mr_id',$receive_no)
                    ->update([
                        'not_for_iqc' => $checkiqc,
                        'for_kitting' => $checkiqc,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);
            } else {
                DB::connection($this->mysql)->table('tbl_wbs_material_receiving_batch')
                    ->where('item',$itemall)
                    ->where('wbs_mr_id',$receive_no)
                    ->update([
                        'not_for_iqc' => $checkiqc,
                        'for_kitting' => $checkiqc,
                        'iqc_status' => $iqcstatus,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);
            }
        }

        $invs = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('item',$itemall)
                    ->where('wbs_mr_id',$receive_no)
                    ->get();

        foreach ($invs as $key => $inv) {
            if (intval($inv->iqc_status) > 0) {
                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('item',$itemall)
                    ->where('wbs_mr_id',$receive_no)
                    ->update([
                        'not_for_iqc' => $checkiqc,
                        'for_kitting' => $checkiqc,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);
            } else {
                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('item',$itemall)
                    ->where('wbs_mr_id',$receive_no)
                    ->update([
                        'not_for_iqc' => $checkiqc,
                        'for_kitting' => $checkiqc,
                        'iqc_status' => $iqcstatus,
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);
            }
        }
    }

    public function showNeedsModificationItem()
    {
        $return_data = [
            'status' => 'failed',
            'msg' => 'No Items to modify.',
            'data' => []
        ];
        try {
            $data = DB::connection($this->mysql)
                        ->select("select i.id as id,
                                    i.wbs_mr_id as receiving_no,
                                    i.invoice_no as invoice_no,
                                    i.item as item,
                                    i.item_desc as item_desc,
                                    i.lot_no as lot_no,
                                    i.qty as qty,
                                    i.supplier as supplier,
                                    i.received_date as received_date,
                                    i.location as location,
                                    ifnull(i.mat_batch_id,i.loc_batch_id) as mr_id,
                                    case when i.mat_batch_id is null then 'LR'
                                    else 'MR'
                                    end as mr_source,
                                    i.deleted as deleted,
                                    i.ins_by as ins_by,
                                    i.qc_remarks as qc_remarks
                                from tbl_wbs_inventory as i
                                left join tbl_wbs_material_receiving_batch as m
                                on i.mat_batch_id = m.id
                                left join tbl_wbs_local_receiving_batch as l
                                on i.loc_batch_id = l.id
                                where i.qc_remarks is not null
                                and i.needed_whs_update <> 0");

            if (count($data) > 0) {
                $return_data = [
                    'status' => 'success',
                    'msg' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $th) {
            $return_data = [
                'status' => 'error',
                'msg' => $th->getMessage(),
                'data' => []
            ];
        }

        return $return_data;
    }

    public function removeModificationItem(Request $req)
    {
        $return_data = [
            'status' => 'failed',
            'msg' => 'Transaction has failed.'
        ];
        try {
            DB::beginTransaction();
            $success = 0;
            
            foreach ($req->ids as $key => $id) {
                $update = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                            ->where('id',$id)
                            ->update([
                                'qc_remarks' => null,
                                'needed_whs_update' => 0
                            ]);

                if ($update) {
                    $success++;
                }
            }
            

            if ($success > 0) {
                DB::commit();
                $return_data = [
                    'status' => 'success',
                    'msg' => 'Transaction was succesful.'
                ];
            }
        } catch (\Exception $th) {
            DB::rollback();
            $return_data = [
                'status' => 'error',
                'msg' => $th->getMessage(),
            ];
        }

        return $return_data;
    }

    public function ModifyItem(Request $req)
    {
        $return_data = [
            'status' => 'failed',
            'msg' => 'Transaction has failed.'
        ];

        $tbl = "tbl_wbs_material_receiving_batch";

        try {
            DB::beginTransaction();
            $success = 0;
            
            $update = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                        ->where('id',$req->id)
                        ->update([
                            'lot_no' => $req->lot_no,
                            'supplier' => $req->supplier,
                            'qc_remarks' => null,
                            'needed_whs_update' => 0,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'update_user' => Auth::user()->user_id,
                            'update_pg' => "WBS Mat. Receiving - QC Request Modification"
                        ]);

            if ($update) {

                if ($req->mr_source == "LR") {
                    $tbl = "tbl_wbs_local_receiving_batch";
                }

                DB::connection($this->mysql)->table($tbl)
                        ->where('id',$req->mr_id)
                        ->update([
                            'lot_no' => $req->lot_no,
                            'supplier' => $req->supplier,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'update_user' => Auth::user()->user_id,
                            // 'update_pg' => "WBS Mat. Receiving - QC Request Modification"
                            // 'qc_remarks' => null,
                            // 'needed_whs_update' => 0
                        ]);

                $success++;
            }
            

            if ($success > 0) {
                DB::commit();
                $return_data = [
                    'status' => 'success',
                    'msg' => 'Transaction was succesful.'
                ];
            }
        } catch (\Exception $th) {
            DB::rollback();
            $return_data = [
                'status' => 'error',
                'msg' => $th->getMessage(),
            ];
        }

        return $return_data;
    }
}
