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

class WBSLocalMaterialReceivingController extends Controller
{
    protected $mysql;
    protected $mssql;
    protected $common;
    protected $barcode;
    protected $com;

    public function __construct()
    {
        $this->middleware('auth');
        $this->com = new CommonController;

        if (Auth::user() != null) {
            $this->mysql = $this->com->userDBcon(Auth::user()->productline,'wbs');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline,'common');
            $this->barcode = $this->com->userDBcon(Auth::user()->productline,'barcode');
            $this->main_mysql = $this->com->userDBcon(Auth::user()->productline,'mysql');
        } else {
            return redirect('/');
        }
    }

    public function index()
    {
        $pgcode = Config::get('constants.MODULE_CODE_lOCMAT');

        if(!$this->com->getAccessRights($pgcode, $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            return view('wbs.materialreceivinglocal', [
                        'userProgramAccess' => $userProgramAccess,
                        'pgcode' => $pgcode,
                        'pgaccess' => $this->com->getPgAccess($pgcode)
                    ]);
        }
    }

    public function postSaveLocRec(Request $req)
    {
        $data = [
            'msg' => 'Saving failed.',
            'status' => 'failed'
        ];

        if ($req->save_type == "ADD") {
            $receive_no = $this->com->getTransCode('MAT_LOC');
            $insert = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                        ->insert([
                            'receive_no' => $receive_no,
                            'receive_date' => $this->convertDate($req->receivingdate,'Y-m-d'),
                            'invoice_date' => $this->convertDate($req->invoicedate,'Y-m-d'),
                            'invoice_no' => $req->invoice_no,
                            'create_user' => Auth::user()->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'update_user' => Auth::user()->user_id,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
            if ($insert) {
                $data = [
                    'msg' => 'Successfully saved.',
                    'status' => 'success'
                ];
            }
        } else {
            $update = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                        ->where('id',$req->id)
                        ->update([
                            'receive_date' => $this->convertDate($req->receivingdate,'Y-m-d'),
                            'invoice_date' => $this->convertDate($req->invoicedate,'Y-m-d'),
                            'invoice_no' => $req->invoice_no,
                            'orig_invoice_no' => $req->orig_invoice,
                            'update_user' => Auth::user()->user_id,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
            if ($update) {
                DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                    ->where('wbs_loc_id',$req->receive_no)
                    ->update([
                        'invoice_no' => $req->invoice_no,
                        'orig_invoice_no' => $req->orig_invoice,
                        'received_date' => $this->convertDate($req->receivingdate,'Y-m-d'),
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $data = [
                    'msg' => 'Successfully saved.',
                    'status' => 'success'
                ];
            }
        }

        

        return $data;
    }

    public function getLocalMaterialData(Request $req)
    {
        if (empty($req->to) && !empty($req->id)) {
            $localinfo = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                            ->select('id',
                                'receive_no',
                                'invoice_no',
                                'orig_invoice_no',
                                DB::raw("DATE_FORMAT(invoice_date, '%m/%d/%Y') as invoice_date"),
                                DB::raw("DATE_FORMAT(receive_date, '%m/%d/%Y') as receive_date"),
                                'create_user',
                                DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                                'update_user',
                                DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                            ->where('id',$req->id)
                            ->first();

            if ($this->com->checkIfExistObject($localinfo) > 0) {
                $localbatch = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                                ->select('id',
                                    DB::raw('IFNULL(item,"") as item'),
                                    DB::raw('IFNULL(item_desc,"") as item_desc'),
                                    DB::raw('IFNULL(qty,"") as qty'),
                                    DB::raw('IFNULL(box,"") as box'),
                                    DB::raw('IFNULL(box_qty,"") as box_qty'),
                                    DB::raw('IFNULL(lot_no,"") as lot_no'),
                                    DB::raw('IFNULL(location,"") as location'),
                                    DB::raw('IFNULL(supplier,"") as supplier'),
                                    DB::raw('IFNULL(drawing_num,"") as drawing_num'),
                                    'not_for_iqc',
                                    'is_printed')
                                ->where('wbs_loc_id',$localinfo->receive_no)
                                ->where('invoice_no',$localinfo->invoice_no)
                                ->get();
            } else {
                $localinfo = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                            ->select('id',
                                    'receive_no',
                                    'invoice_no',
                                    'orig_invoice_no',
                                    DB::raw("DATE_FORMAT(invoice_date, '%m/%d/%Y') as invoice_date"),
                                    DB::raw("DATE_FORMAT(receive_date, '%m/%d/%Y') as receive_date"),
                                    'create_user',
                                    DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                                    'update_user',
                                    DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                                ->where('receive_no',$req->id)
                                ->first();

                $localbatch = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                                ->select('id',
                                    DB::raw('IFNULL(item,"") as item'),
                                    DB::raw('IFNULL(item_desc,"") as item_desc'),
                                    DB::raw('IFNULL(qty,"") as qty'),
                                    DB::raw('IFNULL(box,"") as box'),
                                    DB::raw('IFNULL(box_qty,"") as box_qty'),
                                    DB::raw('IFNULL(lot_no,"") as lot_no'),
                                    DB::raw('IFNULL(location,"") as location'),
                                    DB::raw('IFNULL(supplier,"") as supplier'),
                                    DB::raw('IFNULL(drawing_num,"") as drawing_num'),
                                    'not_for_iqc',
                                    'is_printed')
                                ->where('wbs_loc_id',$localinfo->receive_no)
                                ->where('invoice_no',$localinfo->invoice_no)
                                ->get();
            }

            return $data = [
                        'localinfo' => $localinfo,
                        'localbatch' => $localbatch,
                    ];
        }

        if (!empty($req->to) && !empty($req->id)) {
            return $this->navigate($req->to,$req->id);
        }
        if (empty($req->to) && empty($req->id)) {
            return $this->last();
        }
    }

    public function ExtractExcelFile(Request $req)
    {
        // $check_invoice = DB::connection($this->mysql)->('tbl_wbs_local_receiving')
        //                  ->select('receive_no')
        //                  ->where()


        $file = $req->file('localitems');
        $fields; 
        $errCount = 0;

        $data = [
            'msg' => "Uploading Failed.",
            'status' => 'failed'
        ];

        Excel::load($file, function($reader) use(&$fields){
            $fields = $reader->toArray();
            $data = [
                'msg' => "Data was successfully saved.",
                'status' => 'success'
            ];
        });
       
        // dd($fields);
         foreach ($fields as $key => $field) {

            // $check_invoice = DB::connection($this->mysql)
//DEAN
            if (!is_null($field['invoiceno'])) {
                if($field['invoiceno'] != $req->invoice_num || $field['receivingno'] != $req->control_num ){
                    $errCount = $errCount +1;
                };  
            }
        }
         // dd($fields,$errCount);
        if ($errCount == 0) {
             foreach ($fields as $key => $field) {

            // $check_invoice = DB::connection($this->mysql)
//DEAN
             if (!is_null($field['invoiceno'])) {
                 $this->saveToBatch($field);
             }
            }
            $data = [
                'msg' => "Data was successfully saved.",
                'status' => 'success'
            ];
        }else{
            $data = [
                'msg' => "Control number or invoice number are incorrect.",
                'status' => 'failed'
            ];
        }
        return json_encode($data);
        // return json_encode($)
    }

public function ExtractExcelFile_new(Request $req){
        $file = $req->file('localitems');
        $fields; 
        $errCount = 0;
        $data = [
            'msg' => "Uploading Failed.",
            'status' => 'failed'
        ];

        Excel::load($file, function($reader) use(&$fields){
            $fields = $reader->toArray();
            $data = [
                'msg' => "Data was successfully saved.",
                'status' => 'success'
            ];
        });

        foreach ($fields as $key => $field) {
            if (!is_null($field['invoiceno'])) {
                if($field['invoiceno'] != $req->invoice_num || $field['receivingno'] != $req->control_num ){
                    $errCount = $errCount +1;
                };  
            }
        }

        if($errCount == 0) {
            $grp = [];
            foreach ($fields as $key) {
                $arr = array_filter($grp, function ($r) use ($key) {
                    return $r['lotno'] == $key['lotno'] && $r['item'] == $key['item'];
                });

                if (!empty($arr)) {
                    // Have
                } else {
                    // Null
                    $filter_ = array_filter($fields, function ($r) use ($key) {
                        return $r['lotno'] == $key['lotno'] && $r['item'] == $key['item'];
                    });
                    $fllDetails = [];
                    foreach ($filter_ as $key) {
                        array_push($fllDetails,$key);
                    }
                    $grp[] = [
                        'lotno' => $key['lotno'],
                        'item' => $key['item'],
                        'group' => $fllDetails
                    ];
                }
            }
            $new_data = [];
            for ($x = 0; $x < count($grp); $x++) {
                $d = $grp[$x];
                $inc = 1;
                for ($j = 0; $j < count($d['group']); $j++)  {
                    if(count($d['group']) > 0 && count($d['group']) != 1) {
                        $_concat = $d['group'][$j]['lotno']." - ".$inc."/".count($d['group']);
                        $grp[$x]['group'][$j]['lotno'] = $_concat;
                        $inc++;
                        array_push($new_data,$grp[$x]['group'][$j]);
                    }else {
                        array_push($new_data,$grp[$x]['group'][$j]);
                    }  
                }
            }
            foreach ($new_data as $key => $field) {
                if (!is_null($field['invoiceno'])) {
                    $this->saveToBatch($field);
                }
            }
            $ww = 0;
            $data = [
                'msg' => "Data was successfully saved.",
                'status' => 'success'
            ];
        }else {
            $data = [
                'msg' => "Control number or invoice number are incorrect.",
                'status' => 'failed'
            ];
        }
        return json_encode($data);
    }




    private function checkNR($item)
    {
        $nr = DB::connection($this->main_mysql)->table('tbl_iqc_matrix')
                        ->select('item')
                        ->where('item',$item)
                        ->count();
        if ($nr > 0) {
            return true;
        }

        return false;
    }

    private function saveToBatch($data)
    {
        if ($this->checkItemIfExist($data) > 0) {
            # code...
        } else {
            if ($this->checkNR($this->getItemCode($data['item']))) {
                $iqc_status = 1;
                $for_kitting = 1;
                $not_req = 1;
            } else {
                $iqc_status = 0;
                $for_kitting = 0;
                $not_req = 0;
            }

            if ($data['not_required_iqc'] == 1) {
                $iqc_status = 1;
                $for_kitting = 1;
                $not_req = 1;
            } else {
                $iqc_status = 0;
                $for_kitting = 0;
                $not_req = 0;
            }

            if ($this->getItemCode($data['item']) == '' || $this->getItemCode($data['item']) == null) {

                # code...
            } else {
                $loc_batch_id = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')->insertGetId([
                    'wbs_loc_id' => $data['receivingno'],
                    'invoice_no' => $data['invoiceno'],
                    'item' => $this->getItemCode($data['item']),
                    'item_desc' => $this->getItemName($data['item']),
                    'qty' => str_replace(',','',$data['qty']),
                    'box' => $data['package_category'],
                    'box_qty' => str_replace(',','',$data['package_qty']),
                    'lot_no' => $data['lotno'],
                    'location' => $this->getLocation($data['item']),
                    'supplier' => strtoupper($data['supplier']),
                    'drawing_num' => $this->getDrawingNum($data['item']),
                    'iqc_status' => $iqc_status,
                    'is_printed' => 0,
                    'for_kitting' => $for_kitting,
                    'not_for_iqc' => $not_req,
                    'iqc_result' => '',
                    'received_date' => $this->getReceivedDate($data['receivingno']),
                    'create_user' => Auth::user()->user_id,
                    'created_at' =>  date('Y-m-d H:i:s'),
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                DB::connection($this->mysql)->table('tbl_wbs_inventory')->insert([
                    'wbs_mr_id' => $data['receivingno'],
                    'invoice_no' => $data['invoiceno'],
                    'item' => $this->getItemCode($data['item']),
                    'item_desc' => $this->getItemName($data['item']),
                    'qty' => str_replace(',','',$data['qty']),
                    'box' => $data['package_category'],
                    'box_qty' => str_replace(',','',$data['package_qty']),
                    'lot_no' => $data['lotno'],
                    'location' => $this->getLocation($data['item']),
                    'supplier' => strtoupper($data['supplier']),
                    'drawing_num' => $this->getDrawingNum($data['item']),
                    'iqc_status' => $iqc_status,
                    'is_printed' => 0,
                    'for_kitting' => $for_kitting,
                    'not_for_iqc' => $not_req,
                    'iqc_result' => '',
                    'received_date' => $this->getReceivedDate($data['receivingno']),
                    'create_user' => Auth::user()->user_id,
                    'created_at' =>  date('Y-m-d H:i:s'),
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'loc_batch_id' => $loc_batch_id
                ]);
            }

            DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                ->where('receive_no',$data['receivingno'])->update([
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }
    }




    public function updateBatchItem(Request $req)
    {
        $data = [
            'msg' => 'Saving failed.',
            'status' => 'failed'
        ];

        $iqc_status = 0;
        $for_kitting = 0;
        $not_req = 0;

        if ($this->checkNR($this->getItemCode($req->item))) {
            $iqc_status = 1;
            $for_kitting = 1;
            $not_req = 1;
        } else {
            $iqc_status = 0;
            $for_kitting = 0;
            $not_req = 0;
        }

        if ($req->nr == 1) {
            $iqc_status = 1;
            $for_kitting = 1;
            $not_req = 1;
        }

        if ($req->save_status == 'EDIT') {
            $update = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                        ->where('id',$req->id)
                        ->update([
                            'qty' => $req->qty,
                            'box' => $req->box,
                            'box_qty' => $req->box_qty,
                            'lot_no' => $req->lot_no,
                            'supplier' => $req->supplier,
                            'iqc_status' => $iqc_status,
                            'for_kitting' => $for_kitting,
                            'not_for_iqc' => $not_req,
                        ]);
            if ($update) {
                DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                    ->where('receive_no',$req->controlno)
                    ->update([
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);
                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('loc_batch_id',$req->id)
                    ->update([
                        'qty' => $req->qty,
                        'box' => $req->box,
                        'box_qty' => $req->box_qty,
                        'lot_no' => $req->lot_no,
                        'supplier' => $req->supplier,
                        'iqc_status' => $iqc_status,
                        'for_kitting' => $for_kitting,
                        'not_for_iqc' => $not_req,
                    ]);

                $data = [
                    'msg' => 'Successfully updated.',
                    'status' => 'success'
                ];
            }
        } else {
            $loc_batch_id = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')->insertGetId([
                'wbs_loc_id' => $req->controlno,
                'invoice_no' => $req->invoice_no,
                'item' => $this->getItemCode($req->item),
                'item_desc' => $this->getItemName($req->item),
                'qty' => str_replace(',','',$req->qty),
                'box' => $req->box,
                'box_qty' => str_replace(',','',$req->box_qty),
                'lot_no' => $req->lot_no,
                'location' => $this->getLocation($req->item),
                'supplier' => strtoupper($req->supplier),
                'drawing_num' => $this->getDrawingNum($req->item),
                'iqc_status' => $iqc_status,
                'is_printed' => 0,
                'for_kitting' => $for_kitting,
                'not_for_iqc' => $not_req,
                'iqc_result' => '',
                'received_date' => $this->getReceivedDate($req->controlno),
                'create_user' => Auth::user()->user_id,
                'created_at' =>  date('Y-m-d h:i:s a'),
                'update_user' => Auth::user()->user_id,
                'updated_at' => date('Y-m-d h:i:s a')
            ]);

            DB::connection($this->mysql)->table('tbl_wbs_inventory')->insert([
                'wbs_mr_id' => $req->controlno,
                'invoice_no' => $req->invoice_no,
                'item' => $this->getItemCode($req->item),
                'item_desc' => $this->getItemName($req->item),
                'qty' => str_replace(',','',$req->qty),
                'box' => $req->box,
                'box_qty' => str_replace(',','',$req->box_qty),
                'lot_no' => $req->lot_no,
                'location' => $this->getLocation($req->item),
                'supplier' => strtoupper($req->supplier),
                'drawing_num' => $this->getDrawingNum($req->item),
                'iqc_status' => $iqc_status,
                'is_printed' => 0,
                'for_kitting' => $for_kitting,
                'not_for_iqc' => $not_req,
                'iqc_result' => '',
                'received_date' => $this->getReceivedDate($req->controlno),
                'create_user' => Auth::user()->user_id,
                'created_at' =>  date('Y-m-d h:i:s a'),
                'update_user' => Auth::user()->user_id,
                'updated_at' => date('Y-m-d h:i:s a'),
                'loc_batch_id' => $loc_batch_id
            ]);

            if ($loc_batch_id) {
                DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                    ->where('receive_no',$req->controlno)
                    ->update([
                        'update_user' => Auth::user()->user_id,
                        'updated_at' => date('Y-m-d h:i:s a')
                    ]);

                $data = [
                    'msg' => 'Successfully Added.',
                    'status' => 'success'
                ];
            }
        }

        

        return $data;
    }

    private function convertDate($date,$format)
    {
        $time = strtotime($date);
        $newdate = date($format,$time);
        return $newdate;
    }

    private function navigate($to,$id)
    {
        switch ($to) {
            case 'next':
                return $this->next($id);
                break;

            case 'prev':
                return $this->prev($id);
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

    private function next($id) 
    {
        $data = [];
        $nxt = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                        ->where('id',$id)
                        ->select('id')->first();

        if ($this->com->checkIfExistObject($nxt) > 0) {
            $localinfo = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                            ->select('id',
                                'receive_no',
                                'invoice_no',
                                'orig_invoice_no',
                                DB::raw("DATE_FORMAT(invoice_date, '%m/%d/%Y') as invoice_date"),
                                DB::raw("DATE_FORMAT(receive_date, '%m/%d/%Y') as receive_date"),
                                'create_user',
                                DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                                'update_user',
                                DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                            ->where("id",">",$nxt->id)
                            ->orderBy("id")
                            ->first();

            if ($this->com->checkIfExistObject($localinfo) > 0) {
                $localbatch = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                                ->select('id',
                                        DB::raw('IFNULL(item,"") as item'),
                                        DB::raw('IFNULL(item_desc,"") as item_desc'),
                                        DB::raw('IFNULL(qty,"") as qty'),
                                        DB::raw('IFNULL(box,"") as box'),
                                        DB::raw('IFNULL(box_qty,"") as box_qty'),
                                        DB::raw('IFNULL(lot_no,"") as lot_no'),
                                        DB::raw('IFNULL(location,"") as location'),
                                        DB::raw('IFNULL(supplier,"") as supplier'),
                                        DB::raw('IFNULL(drawing_num,"") as drawing_num'),
                                        'not_for_iqc',
                                        'is_printed')
                                ->where('wbs_loc_id',$localinfo->receive_no)
                                ->where('invoice_no',$localinfo->invoice_no)
                                ->get();

                return $data = [
                                'localinfo' => $localinfo,
                                'localbatch' => $localbatch,
                            ];
            } else {
                return $this->last();
            }
        } else {
            $data = [
                'msg' => "You've reached the last Local Material Number.",
                'status' => 'failed'
            ];
        }
        return $data;
    }

    private function last()
    {
        $data = [];

        $localinfo = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                        ->select('id',
                            'receive_no',
                            'invoice_no',
                            'orig_invoice_no',
                            DB::raw("DATE_FORMAT(invoice_date, '%m/%d/%Y') as invoice_date"),
                            DB::raw("DATE_FORMAT(receive_date, '%m/%d/%Y') as receive_date"),
                            'create_user',
                            DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            'update_user',
                            DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                        ->where("id", "=", function ($query) {
                            $query->select(DB::raw(" MAX(id)"))
                                ->from('tbl_wbs_local_receiving');
                        })
                        ->first();

        if ($this->com->checkIfExistObject($localinfo) > 0) {
            $localbatch = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                            ->select('id',
                                    DB::raw('IFNULL(item,"") as item'),
                                    DB::raw('IFNULL(item_desc,"") as item_desc'),
                                    DB::raw('IFNULL(qty,"") as qty'),
                                    DB::raw('IFNULL(box,"") as box'),
                                    DB::raw('IFNULL(box_qty,"") as box_qty'),
                                    DB::raw('IFNULL(lot_no,"") as lot_no'),
                                    DB::raw('IFNULL(location,"") as location'),
                                    DB::raw('IFNULL(supplier,"") as supplier'),
                                    DB::raw('IFNULL(drawing_num,"") as drawing_num'),
                                    'not_for_iqc',
                                    'is_printed')
                            ->where('wbs_loc_id',$localinfo->receive_no)
                            ->where('invoice_no',$localinfo->invoice_no)
                            ->get();

            return $data = [
                            'localinfo' => $localinfo,
                            'localbatch' => $localbatch,
                        ];
            }

        return $data;
    }

    private function prev($id)
    {
        $data = [];
        $nxt = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                        ->where('id',$id)
                        ->select('id')->first();

        if ($this->com->checkIfExistObject($nxt) > 0) {
            $localinfo = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                            ->select('id',
                                'receive_no',
                                'invoice_no',
                                'orig_invoice_no',
                                DB::raw("DATE_FORMAT(invoice_date, '%m/%d/%Y') as invoice_date"),
                                DB::raw("DATE_FORMAT(receive_date, '%m/%d/%Y') as receive_date"),
                                'create_user',
                                DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                                'update_user',
                                DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                            ->where("id","<",$nxt->id)
                            ->orderBy("id","DESC")
                            ->first();

            if ($this->com->checkIfExistObject($localinfo) > 0) {
                $localbatch = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                                ->select('id',
                                        DB::raw('IFNULL(item,"") as item'),
                                        DB::raw('IFNULL(item_desc,"") as item_desc'),
                                        DB::raw('IFNULL(qty,"") as qty'),
                                        DB::raw('IFNULL(box,"") as box'),
                                        DB::raw('IFNULL(box_qty,"") as box_qty'),
                                        DB::raw('IFNULL(lot_no,"") as lot_no'),
                                        DB::raw('IFNULL(location,"") as location'),
                                        DB::raw('IFNULL(supplier,"") as supplier'),
                                        DB::raw('IFNULL(drawing_num,"") as drawing_num'),
                                        'not_for_iqc',
                                        'is_printed')
                                ->where('wbs_loc_id',$localinfo->receive_no)
                                ->where('invoice_no',$localinfo->invoice_no)
                                ->get();

                return $data = [
                                'localinfo' => $localinfo,
                                'localbatch' => $localbatch,
                            ];
            } else {
                return $this->first();
            }
        } else {
            $data = [
                'msg' => "You've reached the first Local Material Number.",
                'status' => 'failed'
            ];
        }
        return $data;
    }

    private function first()
    {
        $data = [];

        $localinfo = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                        ->select('id',
                            'receive_no',
                            'invoice_no',
                            'orig_invoice_no',
                            DB::raw("DATE_FORMAT(invoice_date, '%m/%d/%Y') as invoice_date"),
                            DB::raw("DATE_FORMAT(receive_date, '%m/%d/%Y') as receive_date"),
                            'create_user',
                            DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            'update_user',
                            DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                        ->where("id", "=", function ($query) {
                            $query->select(DB::raw(" MIN(id)"))
                                ->from('tbl_wbs_local_receiving');
                        })
                        ->first();

        if ($this->com->checkIfExistObject($localinfo) > 0) {
            $localbatch = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                            ->select('id',
                                    DB::raw('IFNULL(item,"") as item'),
                                    DB::raw('IFNULL(item_desc,"") as item_desc'),
                                    DB::raw('IFNULL(qty,"") as qty'),
                                    DB::raw('IFNULL(box,"") as box'),
                                    DB::raw('IFNULL(box_qty,"") as box_qty'),
                                    DB::raw('IFNULL(lot_no,"") as lot_no'),
                                    DB::raw('IFNULL(location,"") as location'),
                                    DB::raw('IFNULL(supplier,"") as supplier'),
                                    DB::raw('IFNULL(drawing_num,"") as drawing_num'),
                                    'not_for_iqc',
                                    'is_printed')
                            ->where('wbs_loc_id',$localinfo->receive_no)
                            ->where('invoice_no',$localinfo->invoice_no)
                            ->get();

            return $data = [
                            'localinfo' => $localinfo,
                            'localbatch' => $localbatch,
                        ];
            }

        return $data;
    }

    private function getDrawingNum($code)
    {
        $db = DB::connection($this->mssql)->table('XITEM')
                ->select('DRAWING_NUM')
                ->where('CODE',$code)
                ->first();
        if ($this->com->checkIfExistObject($db) > 0) {
            return $db->DRAWING_NUM;
        }
    }

    private function getLocation($code)
    {
        $db = DB::connection($this->mssql)->table('XZAIK')
                ->select('RACKNO')
                ->where('CODE',$code)
                ->where('RACKNO','<>','')
                ->first();
        if ($this->com->checkIfExistObject($db) > 0) {
            return $db->RACKNO;
        }
    }

    private function getItemName($code)
    {
        $db = DB::connection($this->mssql)->table('XHEAD')
                ->select('NAME')
                ->where('CODE',$code)
                ->first();
        if ($this->com->checkIfExistObject($db) < 1) {
            $db = DB::connection($this->mssql)->table('XHEAD')
                    ->select('NAME')
                    ->where('NAME',$code)
                    ->first();
        }

        if ($this->com->checkIfExistObject($db) > 0) {
            return $db->NAME;
        }
    }

    private function getItemCode($name)
    {
        $db = DB::connection($this->mssql)->table('XHEAD')
                ->select('CODE')
                ->where('NAME',$name)
                ->first();

        if ($this->com->checkIfExistObject($db) < 1) {
            $db = DB::connection($this->mssql)->table('XHEAD')
                ->select('CODE')
                ->where('CODE',$name)
                ->first();
        }

        if ($this->com->checkIfExistObject($db) > 0 ) {
            return $db->CODE;
        }
    }

    private function getReceivedDate($receive_no)
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                ->select('receive_date')
                ->where('receive_no',$receive_no)
                ->first();
        if ($this->com->checkIfExistObject($db) > 0) {
            return $db->receive_date;
        }
    }

    private function checkItemIfExist($data)
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                ->where('wbs_loc_id',$data['receivingno'])
                ->where('item',$this->getItemCode($data['item']))
                ->where('lot_no',$data['lotno'])
                ->count();
        return $db;
    }

    public function summaryReport(Request $req)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('Ymd'), 2);
        $com_info = $this->com->getCompanyInfo();
        $from = $this->convertDate($req->from,'Y-m-d');
        $to = $this->convertDate($req->to,'Y-m-d');

        $data = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                    ->select('receive_date',
                            'invoice_no',
                            'orig_invoice_no',
                            'create_user',
                            'update_user',
                            'receive_no',
                            'invoice_date')
                    ->whereRaw("receive_date BETWEEN '" . $from . "' AND '" . $to . "'")
                    ->orderBy('receive_date','asc')
                    ->get();

        //return dd($data);

        Excel::create('Local_Receiving_Summary_Report_'.$date, function($excel) use($from,$to,$data,$com_info)
        {
            $excel->sheet('Summary', function($sheet) use($from,$to,$data,$com_info)
            {
                $sheet->setHeight(1, 20);
                $sheet->mergeCells('A1:G1');
                $sheet->cells('A1:G1', function($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A1',$com_info['name']);

                $sheet->setHeight(2, 20);
                $sheet->mergeCells('A2:G2');
                $sheet->cells('A2:G2', function($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A2',$com_info['address']);

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
                $sheet->cell('A4',Auth::user()->productline." MATERIALS INCOMING INVOICE MONITORING");

                $sheet->cell('A5', function($cell) {
                    $cell->setFont([
                        'family'     => 'Calibri',
                        'size'       => '14',
                        'bold'       =>  true,
                    ]);
                });

                $sheet->setHeight(5, 20);
                $sheet->cell('A5', 'Month: ');
                $sheet->cell('B5', $this->convertDate($from,'F Y'));

                $sheet->setHeight(8, 20);
                $sheet->cells('A8:G8', function($cells) {
                    $cells->setFont([
                        'family'     => 'Calibri',
                        'size'       => '12',
                        'bold'       =>  true,
                    ]);
                    // Set all borders (top, right, bottom, left)
                    $cells->setBorder('solid', 'solid', 'solid', 'solid');
                });
                $sheet->cell('A8', "Control No.");
                $sheet->cell('B8', "Invoice No.");
                $sheet->cell('C8', "Date Received");
                $sheet->cell('D8', "Invoice Date");
                $sheet->cell('E8', "Total Qty.");
                $sheet->cell('F8', "CORRESPONDING INVOICE NUMBER");
                $sheet->cell('G8', "Created By");
                $sheet->cell('H8', "Updated By");

                $row = 9;

                foreach ($data as $key => $ml) {
                    $qty = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                            ->where('wbs_loc_id',$ml->receive_no)
                            ->where('invoice_no',$ml->invoice_no)
                            ->sum('qty');
                    $sheet->setHeight($row, 20);
                    $sheet->cell('A'.$row, $ml->receive_no);
                    $sheet->cell('B'.$row, $ml->invoice_no);
                    $sheet->cell('C'.$row, $ml->receive_date);
                    $sheet->cell('D'.$row, $ml->invoice_date);
                    $sheet->cell('E'.$row, $qty);
                    $sheet->cell('F'.$row, $ml->orig_invoice_no);
                    $sheet->cell('G'.$row, $ml->create_user);
                    $sheet->cell('H'.$row, $ml->update_user);
                    $row++;
                }
                
                $sheet->cells('A6:G'.$row, function($cells) {
                    $cells->setBorder('solid', 'solid', 'solid', 'solid');
                });
            });

            foreach ($data as $key => $ml) {
                $excel->sheet($ml->receive_no, function($sheet) use($from,$to,$ml,$com_info)
                {
                    $qty = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                        ->where('wbs_loc_id',$ml->receive_no)
                        ->where('invoice_no',$ml->invoice_no)
                            ->sum('qty');

                    $sheet->setHeight(1, 20);
                    $sheet->mergeCells('A1:G1');
                    $sheet->cells('A1:G1', function($cells) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cell('A1',$com_info['name']);

                    $sheet->setHeight(2, 20);
                    $sheet->mergeCells('A2:G2');
                    $sheet->cells('A2:G2', function($cells) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cell('A2',$com_info['address']);

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
                    $sheet->cell('A4',"WBS LOCAL MATERIAL RECEIVING");

                    $sheet->cell('A5', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('A6', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('C5', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('C6', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('E5', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('E6', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('G5', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->cell('G5', function($cell) {
                        $cell->setFont([
                            'family'     => 'Calibri',
                            'size'       => '14',
                            'bold'       =>  true,
                        ]);
                    });

                    $sheet->setHeight(5, 20);
                    $sheet->cell('A5', 'Control No.: ');
                    $sheet->cell('B5', $ml->receive_no);

                    $sheet->setHeight(6, 20);
                    $sheet->cell('A6', 'Invoice No.: ');
                    $sheet->cell('B6', $ml->invoice_no);

                    $sheet->setHeight(5, 20);
                    $sheet->cell('C5', 'Received Date: ');
                    $sheet->cell('D5', $ml->receive_date);

                    $sheet->setHeight(6, 20);
                    $sheet->cell('C6', 'Invoice Date: ');
                    $sheet->cell('D6', $ml->invoice_date);

                    $sheet->setHeight(5, 20);
                    $sheet->cell('E5', 'Corr. Invoice: ');
                    $sheet->cell('F5', $ml->orig_invoice_no);

                    $sheet->setHeight(6, 20);
                    $sheet->cell('E6', 'Total Qty.: ');
                    $sheet->cell('F6', $qty);

                    $sheet->setHeight(5, 20);
                    $sheet->cell('G5', 'Created By: ');
                    $sheet->cell('H5', $ml->create_user);

                    $sheet->setHeight(6, 20);
                    $sheet->cell('G6', 'Updated By: ');
                    $sheet->cell('H6', $ml->update_user);

                    $sheet->setHeight(8, 20);
                    $sheet->cells('A8:G8', function($cells) {
                        $cells->setFont([
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true,
                        ]);
                        // Set all borders (top, right, bottom, left)
                        $cells->setBorder('solid', 'solid', 'solid', 'solid');
                    });
                    $sheet->cell('A8', "Item Code");
                    $sheet->cell('B8', "Description");
                    $sheet->cell('C8', "Lot Number");
                    $sheet->cell('D8', "Quantity");
                    $sheet->cell('E8', "Packaging");
                    $sheet->cell('F8', "Pckg Qty.");
                    $sheet->cell('G8', "Supplier");

                    $row = 9;

                    $locs = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                                ->select('item',
                                        'item_desc',
                                        'qty',
                                        'box',
                                        'box_qty',
                                        'lot_no',
                                        'supplier')
                                ->where('wbs_loc_id',$ml->receive_no)
                                ->where('invoice_no',$ml->invoice_no)
                                ->orderBy('wbs_loc_id','asc')
                                ->get();

                    foreach ($locs as $key => $loc) {
                        $sheet->setHeight($row, 20);
                        $sheet->cell('A'.$row, $loc->item);
                        $sheet->cell('B'.$row, $loc->item_desc);
                        $sheet->cell('C'.$row, $loc->lot_no);
                        $sheet->cell('D'.$row, $loc->qty);
                        $sheet->cell('E'.$row, $loc->box);
                        $sheet->cell('F'.$row, $loc->box_qty);
                        $sheet->cell('G'.$row, $loc->supplier);
                        $row++;
                    }
                    
                    $sheet->cells('A6:G'.$row, function($cells) {
                        $cells->setBorder('solid', 'solid', 'solid', 'solid');
                    });
                });
            }

        })->download('xls');
    }

    public function printBarcode(Request $req)
    {
        $mr_data = [];
        $printed = [];
        $mat_code = DB::connection($this->mysql)->table('tbl_transaction')
                        ->where('description','Local Material Receiving')
                        ->select('code')
                        ->get();
        if ($req->state == 'bulk') {
            $mr_data = DB::connection($this->mysql)->table('tbl_wbs_local_receiving as r')
                ->join('tbl_wbs_local_receiving_batch as b','r.receive_no','=','b.wbs_loc_id')
                ->where('r.receive_no',$req->receivingno)
                ->get();

            if ($this->checkIfExistObject($mr_data) > 0) {
                foreach ($mr_data as $key => $mr) {
                    $recdate = str_replace('-', '', $mr->invoice_date);
                    $receivingdate = substr($recdate, 2);
                    DB::connection($this->barcode) //Config::get('constants.DB_SQLSRV_BARCODE')
                        ->table('barcode_print')
                        ->insert(['printdate' => date('Y-m-d H:i:s')
                            ,'txnno'     => $mr->invoice_no
                            ,'txndate'   => $this->convertDate($mr->invoice_date,'Y-m-d')
                            ,'itemno'    => $mr->item
                            ,'itemdesc'  => $mr->item_desc
                            ,'qty'       => $mr->qty
                            ,'bcodeqty'  => $mr->box_qty
                            ,'lotno'     => $mr->lot_no
                            ,'location'  => $mr->location
                            ,'barcode'   => $mr->item.$receivingdate
                            ,'printedby' => Auth::user()->user_id
                            ,'trancode'  => 'MAT_RCV'
                            ,'printerid' => 0
                        ]);

                //     DB::connection($this->barcode) //Config::get('constants.DB_SQLSRV_BARCODE')
                //         ->table('barcode_print_mobile')
                //         ->insert(['printdate' => date('Y-m-d H:i:s')
                //             ,'txnno'     => $mr->invoice_no
                //             ,'txndate'   => $invdate
                //             ,'itemno'    => $mr->item
                //             ,'itemdesc'  => $mr->item_desc
                //             ,'qty'       => $mr->qty
                //             ,'bcodeqty'  => $mr->box_qty
                //             ,'lotno'     => $mr->lot_no
                //             ,'location'  => $mr->location
                //             ,'barcode'   => $mr->item.$receivingdate
                //             ,'printedby' => Auth::user()->user_id
                //             ,'trancode'  => (isset($mat_code[0]->code))? $mat_code[0]->code : 'MAT_RCV'
                //             ,'printerid' => 0
                //         ]);
                }

                DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                        ->where('wbs_loc_id',$req->receivingno)
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

            $br = DB::connection($this->barcode)
                    ->table('barcode_print')
                    ->insert(['printdate' => date('Y-m-d H:i:s')
                        ,'txnno'     => $req->txnno
                        ,'txndate'   => $this->convertDate($req->txndate,'Y-m-d')
                        ,'itemno'    => $req->itemno
                        ,'itemdesc'  => $req->itemdesc
                        ,'qty'       => $req->qty
                        ,'bcodeqty'  => $req->bcodeqty
                        ,'lotno'     => $req->lotno
                        ,'location'  => $req->location
                        ,'barcode'   => $req->itemno.$receivingdate
                        ,'printedby' => Auth::user()->user_id
                        ,'trancode'  => 'MAT_RCV'
                        ,'printerid' => 0
                    ]);

            // $br_mobile = DB::connection($this->barcode)
            //         ->table('barcode_print_mobile')
            //         ->insert(['printdate' => date('Y-m-d H:i:s')
            //             ,'txnno'     => $req->txnno
            //             ,'txndate'   => $req->txndate
            //             ,'itemno'    => $req->itemno
            //             ,'itemdesc'  => $req->itemdesc
            //             ,'qty'       => $req->qty
            //             ,'bcodeqty'  => $req->bcodeqty
            //             ,'lotno'     => $req->lotno
            //             ,'location'  => $req->location
            //             ,'barcode'   => $req->itemno.$receivingdate
            //             ,'printedby' => Auth::user()->user_id
            //             ,'trancode'  => 'MAT_RCV'
            //             ,'printerid' => 0
            //         ]);

            if (isset($req->id)) {
                DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                    ->where('wbs_loc_id',$req->receivingno)
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

    public function ApplicationForIQC(Request $req)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);
        $app_date = $dt->format('m/d/Y');
        $app_time = $dt->format('H:i A');
        $company_info = $this->com->getCompanyInfo();

        $cnt = DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                    ->where('receive_no',$req->receivingno)
                    ->count();


        $iqc_data = '';
        if($cnt > 0)
        {
            $receiveno = $req->receivingno;
            $iqc_data = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch as b')
                            ->join('tbl_wbs_local_receiving as r','b.wbs_loc_id','=','r.receive_no')
                            ->where('b.wbs_loc_id', $receiveno)
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
            if ($this->com->checkIfExistObject($iqc_data) > 0) {
                DB::connection($this->mysql)->table('tbl_wbs_local_receiving')
                    ->where('receive_no',$req->receivingno)
                    ->update([
                        'app_date' => $app_date,
                        'app_time' => $app_time,
                        'app_by' => Auth::user()->user_id
                    ]);

                DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                    ->where('wbs_loc_id',$req->receivingno)
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
                        $sheet->cell('B8', $iqc_data[0]->invoice_no);
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
                })->download('xls');
            } else {
                $message = "Please batch Invoice Items first.";
                return redirect('/wbslocmat')->with(['err_message' => $message]);
                // return $req->receivingno;
            }
        }
    }

    public function getPackage()
    {
        return DB::connection($this->mysql)->table('tbl_package_category')
                    ->select('description as id', 'description as text')
                    ->get();
    }

    public function postDeleteBatchItem(Request $req)
    {
        $data = [
            'msg' => "Deleting failed.",
            'status' => 'failed'
        ];

        $deleted = false;
        foreach ($req->ids as $key => $id) {
            $deleted = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                        ->where('id',$id)
                        ->delete();

                        DB::connection($this->mysql)->table('tbl_wbs_inventory')
                        ->where('loc_batch_id',$id)
                        ->delete();
        }

        if ($deleted) {
            $data = [
                'msg' => "Item/s was/were successfully deleted.",
                'status' => 'success'
            ];
        }

        return $data;
    }

    public function getTotal(Request $req)
    {
        $data = ['total' => 0];
        $db = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
            ->where('wbs_loc_id',$req->receiveno)
            ->where('invoice_no',$req->invoice_no)
                ->select(DB::raw('IFNULL(SUM(qty),0) as total'))->get();
        if (count((array)$db) > 0) {
            $data = ['total' => $db[0]->total];
        }

        return $data;
    }

    public function Search(Request $req)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_local_receiving_batch')
                    ->where('item',$req->srch_item)
                    ->get();
        return response()->json($data);
    }
}
