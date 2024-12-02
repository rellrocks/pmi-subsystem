<?php

namespace App\Http\Controllers\WBS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth; #Auth facade
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Config;
use Carbon\Carbon;
use PDF;
use Excel;
use Datatables;
use Event;
use App\Events\WHSCheckRequest;

class WBSWhsMatIssueanceController extends Controller
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
            $this->mysql = $this->com->userDBcon(Auth::user()->productline,'wbs');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline,'common');
        } else {
            return redirect('/');
        }
    }

    public function getWarehouse(Request $request_data)
    {
        if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_WHSMATISS'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            $active_tab = '0';

            return view('wbs.warehousematerialissuance',[
                'userProgramAccess' => $userProgramAccess,
                // 'summaries' => $reqSummary,
                // 'wm_data' => $wm_data,
                // 'wm_details_data' => $wm_details_data,
                // 'ismax' => $ismax,
                'active_tab' => $active_tab,
                // 'action' => $action
            ]);
        }
    }

    public function viewDetails(Request $request)
    {
        $db = DB::connection($this->mysql)->table('tbl_request_detail')->where('transno',$request->transno)->get();
        return $db;
    }

    public function postNewWhsMatIssuance(Request $request_data)
    {
        $common = new CommonController;
        if(!$common->getAccessRights(Config::get('constants.MODULE_CODE_WBS'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            $result = [];
            $reqSummary = [];
            $wm_details_data = [];

            try
            {
                $issuance_arr['id']          = null;
                $issuance_arr['issuance_no'] = null;
                $issuance_arr['request_no']  = $request_data->transno;
                $issuance_arr['status']      = 'Accepted';
                $issuance_arr['create_user'] = Auth::user()->user_id;
                $issuance_arr['update_user'] = Auth::user()->user_id;
                $issuance_arr['created_at']  = date("Y/m/d H:i:sa");
                $issuance_arr['updated_at']  = date("Y/m/d H:i:sa");


                $itemarr['detail_id']    = $request_data->reqdetid1;//issueDetID1
                $itemarr['item']         = $request_data->itemnodet1;
                $itemarr['item_desc']    = $request_data->itemnodet1;
                $itemarr['request_qty']  = $request_data->reqqtydet1;
                $itemarr['issued_qty_o'] = $request_data->servedqtydet1;
                $itemarr['issued_qty_t'] = $request_data->issqtydet;
                $itemarr['lot_no']       = $request_data->lotnodet;
                $itemarr['location']     = $request_data->locdet1;

                $wm_data = (object) $issuance_arr;
                $wm_details_data = (object) $itemarr;

                $action = 'ADD';
                $ismax = false;
            }
            catch (Exception $e)
            {
                Log::error($e->getMessage());
            }
            return view('warehousematerialissuance',[
                    'userProgramAccess' => $userProgramAccess,
                    'summaries' => $reqSummary,
                    'wm_data' => $wm_data,
                    'wm_details_data' => $wm_details_data,
                    'ismax' => false,
                    'action' => 'ADD',
                    'active_tab' => '1',
                ]);
        }
    }

    public function getMassAlert()
    {
        $datatable = DB::connection($this->mysql)->table('tbl_request_summary')
                        ->where('status','<>','Cancelled')
                        ->where('status','<>','Closed')
                        ->orderBy('id','desc')
                        ->select(['id',
                                DB::raw("ifnull(lastservedby,'') as lastservedby"),
                                DB::raw("ifnull(lastserveddate,'') as lastserveddate"),
                                DB::raw("ifnull(transno,'') as transno"),
                                DB::raw("ifnull(DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p'),'') as created_at"),
                                DB::raw("ifnull(pono,'') as pono"),
                                DB::raw("ifnull(destination,'') as destination"),
                                DB::raw("ifnull(line,'') as line"),
                                DB::raw("ifnull(status,'') as status"),
                                DB::raw("ifnull(requestedby,'') as requestedby"),
                            ]);
        return Datatables::of($datatable)
                        ->addColumn('action', function($data) {
                            return '<a href="javascript:;" class="btn btn-circle btn-primary btn-sm viewdetails" data-transno="'.$data->transno.'" data-status="'.$data->status.'">
                                    <i class="fa fa-search"></i>
                                </a>';
                        })
                        ->setRowClass(function($data) {
                            if ($data->status == 'Serving') {
                                return 'alert-info';
                            }

                            if ($data->status == 'Alert') {
                                return 'alert-danger';
                            }
                        })
                        ->make(true);
    }

    /**
    * Collate related arrays into 1 array.
    */
    private function mergeArray($item_arr)
    {
        $arr_sum = null;
        $cnt = 0;
        foreach ($item_arr as $itemkey => $item)
        {
            $ctr = 0;
            foreach ($item as $valuekey => $value)
            {
                $arr_sum[$ctr][$cnt] = $value;
                $ctr++;
            }
            $cnt++;
        }

        return $arr_sum;
    }

    public function postSaveRequest(Request $request_data)
    {
        $data        = $request_data['wmi_arr'];
        $details_arr = $request_data['detail_arr'];

        try
        {
            if(is_null($details_arr))
            {
                $wmi_batch_data = null;
            }
            else
            {
                $wmi_batch_data = $this->mergeArray($details_arr);
            }

            $result = $this->insertWmi($data, $wmi_batch_data);
        // return $result;
            // $result = 0;
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }

        return $result;
    }

    /**
    * Insert Parts Receive, Details, Summary and Batch Details.
    **/
    private function insertWmi($wmi_data, $wmi_batch_data)
    {
        $result=false;
        $variancesum = 0;
        $inventory_no = 0;

        #array index of inventory data
        $idx_issuancenowhs = 0;
        $idx_reqno         = 1;
        $idx_statuswhs     = 2;
        $idx_createdbywhs  = 3;
        $idx_updatedbywhs  = 4;


        #array index of inventory details data
        $idx_detailid_td   = 0;
        $idx_code_td       = 1;
        $idx_name_td       = 2;
        $idx_issuedqty_td  = 3;
        $idx_servedqty_td  = 4;
        $idx_lot_no_td     = 5;
        $idx_location_td   = 6;

        try
        {
            if(empty($wmi_data[$idx_reqno]))
            {
                $common = new CommonController();
                $nextTransNo = $common->getWbsNextCode('WAR_ISS');
                $transNo = $nextTransNo['new_code'];

                DB::connection($this->mysql)->table("tbl_wbs_warehouse_mat_issuance")
                        ->insert([
                            'issuance_no'  => $transNo
                            ,'request_no'  => $wmi_data[$idx_reqno]
                            ,'status'      => $wmi_data[$idx_statuswhs]
                            ,'create_user' => $wmi_data[$idx_createdbywhs]
                            ,'update_user' => $wmi_data[$idx_updatedbywhs]
                            ,'created_at'  => date("Y/m/d H:i:sa")
                            ,'updated_at'  => date("Y/m/d H:i:sa")
                            ]);
            }
            else
            {
                $transNo = $wmi_data[$idx_reqno];
                DB::connection($this->mysql)->table("tbl_wbs_warehouse_mat_issuance")
                        ->where('issuance_no', '=', $transNo)
                        ->update([
                            'request_no'  => $wmi_data[$idx_reqno]
                            ,'status'      => $wmi_data[$idx_statuswhs]
                            ,'update_user' => $wmi_data[$idx_updatedbywhs]
                            ,'updated_at'  => date("Y/m/d H:i:sa")
                            ]);

                DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')->where('issuance_no', '=', $transNo)->delete();
            }

                # insert all added MR Batch Data.
                if(isset($wmi_batch_data))
                {
                    foreach ($wmi_batch_data as $key => $value)
                    {
                        $result = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                                ->insert(
                                    ['issuance_no'  => $transNo
                                    ,'request_no'   => $wmi_data[$idx_reqno]
                                    ,'detail_id'    => $value[$idx_detailid_td]
                                    ,'item'         => $value[$idx_code_td]
                                    ,'item_desc'    => $value[$idx_name_td]
                                    ,'request_qty'  => 0
                                    ,'issued_qty_o' => $value[$idx_issuedqty_td]
                                    ,'issued_qty_t' => $value[$idx_servedqty_td]
                                    ,'lot_no'       => $value[$idx_lot_no_td]
                                    ,'location'     => $value[$idx_location_td]
                                    ,'status'       => $value[$idx_statuswhs]
                                    ,'create_user'  => Auth::user()->user_id
                                    ,'update_user'  => Auth::user()->user_id
                                    ,'created_at'   => date("Y/m/d H:i:sa")
                                    ,'updated_at'   => date("Y/m/d H:i:sa")
                                    ]);
                    }
                }
            $result = 0;
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
        return $result;
    }

    public function getIssuanceno()
    {
        $common = new CommonController();
        $nextTransNo = $common->getWbsNextCode('WAR_ISS');
        $transNo = $nextTransNo['new_code'];
        return $transno;
    }

    public function postSaveIssuance(Request $req)
    {
        if ($req->statuswhs == 'Alert') {
            foreach ($req->newentry as $key => $id) {
                //if ($req->qtyiss[$key] != '') {
                    $this->insertDetails($req,$key,$id);
                //}
            }


            $status = $this->checkStatus($req->issuancenowhs,$req->totreqqty);

            DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                ->insert([
                    'issuance_no' => $req->issuancenowhs,
                    'request_no' =>$req->reqno,
                    'status' => $status,
                    'total_req_qty' => $req->totreqqty,
                    'create_user' => Auth::user()->user_id,
                    'update_user' => Auth::user()->user_id,
                    'created_at' => date("Y/m/d H:i:sa"),
                    'updated_at' => date("Y/m/d H:i:sa")
                ]);

            $ok = DB::connection($this->mysql)->table('tbl_request_summary')
                    ->where('transno',$req->reqno)->update([
                        'status' => $status,
                        'lastservedby' => Auth::user()->user_id,
                        'lastserveddate' => date("Y/m/d H:i:sa")
                    ]);


            Event::fire(new WHSCheckRequest($this->mysql,$req->reqno));

            if ($ok) {
                $e['msg'] = "Issuance Number [".$req->issuancenowhs."] was successfully saved.";
                return $e;
            }
        } else {
            if (isset($req->newentry)) {
                foreach ($req->newentry as $key => $id) {
                    //if ($req->qtyiss[$key] != '') {
                        $this->insertDetails($req,$key,$id);
                    //}
                }
            } else {
                $this->updateDetails($req);
            }

            $status = $this->checkStatus($req->issuancenowhs,$req->totreqqty);

            $ok = DB::connection($this->mysql)->table('tbl_request_summary')
                    ->where('transno',$req->reqno)->update([
                        'status' => $status,
                        'lastservedby' => Auth::user()->user_id,
                        'lastserveddate' => date("Y/m/d H:i:sa")
                    ]);

            DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                ->where('issuance_no',$req->issuancenowhs)->update([
                    'status' => $status,
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date("Y/m/d H:i:sa")
                ]);

            Event::fire(new WHSCheckRequest($this->mysql,$req->reqno));

            if ($ok) {
                $e['msg'] = "Issuance Number [".$req->issuancenowhs."] was successfully updated";
                return $e;
            }
        }
    }

    private function checkStatus($issuance_no,$total_req_qty)
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
            ->where('issuance_no',$issuance_no)
            ->select(DB::raw("SUM(issued_qty_t) as issued_qty_t"))
            ->first();
        if ($total_req_qty <= $db->issued_qty_t) {
            return 'Closed';
        } else {
            return 'Serving';
        }
    }

    private function insertDetails($req,$key,$id)
    {
        $checkItem = DB::connection($this->mysql)
                        ->table('tbl_wbs_warehouse_mat_issuance_details')
                        ->where('issuance_no',$req->issuancenowhs)
                        ->where('request_no',$req->reqno)
                        ->where('item',$req->itemiss[$key])
                        ->where('item_desc',$req->itemdesciss[$key])
                        ->where('request_qty',$req->requestqty[$key])
                        ->where('issued_qty_o',$req->issuedkit[$key])
                        ->where('issued_qty_t',$req->qtyiss[$key])
                        ->where('lot_no',$req->lotiss[$key])
                        ->where('location',$req->lociss[$key])
                        ->count();

        if ($checkItem > 0) {
            # code...
        } else {
            $status = "";
            if ($req->requestqty[$key] == $req->qtyiss[$key]) {
                $status = "Closed";
            } else {
                $status = "Serving";
            }
            
            DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                ->insert([
                    'issuance_no' => $req->issuancenowhs,
                    'request_no' => $req->reqno,
                    'pmr_detail_id' => $id,
                    'detail_id' => $req->detid[$key],
                    'item' => $req->itemiss[$key],
                    'item_desc' => $req->itemdesciss[$key],
                    'request_qty' => $req->requestqty[$key],
                    'issued_qty_o' => $req->issuedkit[$key],
                    'issued_qty_t' => $req->qtyiss[$key],
                    'issued_date' => date('Y-m-d'),
                    'lot_no' => $req->lotiss[$key],
                    'location' => $req->lociss[$key],
                    'status' => $status,
                    'create_user' => Auth::user()->user_id,
                    'update_user' => Auth::user()->user_id,
                    'created_at' => date("Y/m/d H:i:sa"),
                    'updated_at' => date("Y/m/d H:i:sa")
                ]);

            if (isset($req->fifoid[$key])) {
                $fifoqty = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                            ->select('id','qty')
                            ->where('id',$req->fifoid[$key])
                            ->first();

                $updateqtyfifo = $fifoqty->qty - $req->qtyiss[$key];

                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('id',$req->fifoid[$key])
                    ->update(['qty' => $updateqtyfifo]);

                $pmr = DB::connection($this->mysql)->table('tbl_request_detail')
                                ->where('id',$id)
                                ->select('requestqty','servedqty')
                                ->first();

                if ($pmr->servedqty != 0) {
                    if ($pmr->requestqty > $pmr->servedqty) {
                        $served = $pmr->servedqty + $req->qtyiss[$key];
                        DB::connection($this->mysql)->table('tbl_request_detail')->where('id',$id)->update([
                            'servedqty' => $served,
                            'last_served_by' => Auth::user()->user_id,
                            'last_served_date' => date("Y/m/d H:i:sa")
                        ]);
                    }
                } else {
                    DB::connection($this->mysql)->table('tbl_request_detail')->where('id',$id)->update([
                        'servedqty' => $req->qtyiss[$key],
                        'last_served_by' => Auth::user()->user_id,
                        'last_served_date' => date("Y/m/d H:i:sa")
                    ]);
                }
            }
        }
            
    }

    private function updateDetails($req)
    {
        foreach ($req->db_id as $key => $id) {
            $status = "";
            if ($req->requestqty[$key] == $req->qtyiss[$key]) {
                $status = "Closed";
            } else {
                $status = "Serving";
            }
            
            DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                ->where('id',$id)
                ->update([
                    'detail_id' => $req->detid[$key],
                    'item' => $req->itemiss[$key],
                    'item_desc' => $req->itemdesciss[$key],
                    'request_qty' => $req->requestqty[$key],
                    'issued_qty_o' => $req->issuedkit[$key],
                    'issued_qty_t' => $req->qtyiss[$key],
                    'issued_date' => date('Y-m-d'),
                    'lot_no' => $req->lotiss[$key],
                    'location' => $req->lociss[$key],
                    'status' => $status,
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

            if (isset($req->fifoid[$key])) {
                $checkFifoID = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                                ->select('id','qty')
                                ->where('id',$req->fifoid[$key])
                                ->count();

                if ($checkFifoID > 0) {
                    $fifoqty = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                                ->select('id','qty')
                                ->where('id',$req->fifoid[$key])
                                ->first();

                    $updateqtyfifo = $fifoqty->qty - $req->qtyiss[$key];

                    DB::connection($this->mysql)->table('tbl_wbs_inventory')
                        ->where('id',$req->fifoid[$key])
                        ->update(['qty' => $updateqtyfifo]);
                }
                

                $pmr = DB::connection($this->mysql)->table('tbl_request_detail')
                                ->where('id',$req->pmr_detail_id[$key])
                                ->select('requestqty','servedqty')
                                ->first();

                if ($pmr->servedqty != 0) {
                    if ($pmr->requestqty > $pmr->servedqty) {
                        $served = $pmr->servedqty + $req->qtyiss[$key];
                        DB::connection($this->mysql)->table('tbl_request_detail')->where('id',$req->pmr_detail_id[$key])->update([
                            'servedqty' => $served,
                            'last_served_by' => Auth::user()->user_id,
                            'last_served_date' => date("Y/m/d H:i:sa")
                        ]);
                    }
                } else {
                    DB::connection($this->mysql)->table('tbl_request_detail')->where('id',$req->pmr_detail_id[$key])->update([
                        'servedqty' => $req->qtyiss[$key],
                        'last_served_by' => Auth::user()->user_id,
                        'last_served_date' => date("Y/m/d H:i:sa")
                    ]);
                }
            }
        }   
    }

    private function checkIfCompleted($cnt, $reqno)
    {
        $match = DB::connection($this->mysql)->table('tbl_request_detail')->whereRaw("requestqty = servedqty")->where('transno',$reqno)->count();

        if ($match == $cnt) {
            return true;
        } else {
            return false;
        }
    }

    private function navigate($to,$issuanceno)
    {
        switch ($to) {
            case 'next':
                return $this->next($issuanceno);
                break;

            case 'prev':
                return $this->prev($issuanceno);
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

    private function next($issuancenowhs)
    {
        $data = [];
        $nxt = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                    ->where('issuance_no',$issuancenowhs)
                    ->select('id')->first();

        if ($this->com->checkIfExistObject($nxt) > 0) {
            $whsinfo = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                        ->select('id',
                            'issuance_no',
                            'request_no',
                            'status',
                            'create_user',
                            DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            'total_req_qty',
                            'update_user',
                            DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                        ->where("id",">",$nxt->id)
                        ->orderBy("id","ASC")
                        ->first();
            if ($this->com->checkIfExistObject($whsinfo) > 0) {
                $whsdetails = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                                ->select(DB::raw('id as id'),
                                    DB::raw("issuance_no as issuance_no"),
                                    DB::raw("request_no as request_no"),
                                    DB::raw("pmr_detail_id as pmr_detail_id"),
                                    DB::raw("detail_id as detail_id"),
                                    DB::raw("item as item"),
                                    DB::raw("item_desc as item_desc"),
                                    DB::raw("request_qty as request_qty"),
                                    DB::raw("issued_qty_o as issued_qty_o"),
                                    DB::raw("issued_qty_t as issued_qty_t"),
                                    DB::raw("IFNULL(lot_no,'') as lot_no"),
                                    DB::raw('location as location'),
                                    DB::raw("status as status"))
                                ->where('issuance_no',$whsinfo->issuance_no)
                                ->get();
                return $data = [
                                'issuance' => $whsinfo,
                                'details' => $whsdetails,
                                'status' => 'success'
                            ];
            } else {
                return $this->last();
            }
        } else {
            $data = [
                    'msg' => "You've reached the last Issuance Number",
                    'status' => 'failed'
                ];
        }

        return $data;
    }

    private function prev($issuancenowhs)
    {
        $data = [];
        $prev = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                    ->where('issuance_no',$issuancenowhs)
                    ->select('id')->first();

        if ($this->com->checkIfExistObject($prev) > 0) {
            $whsinfo = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                        ->select('id',
                            'issuance_no',
                            'request_no',
                            'status',
                            'create_user',
                            DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            'total_req_qty',
                            'update_user',
                            DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                        ->where("id","<",$prev->id)
                        ->orderBy("id","DESC")
                        ->first();
            if ($this->com->checkIfExistObject($whsinfo) > 0) {
                $whsdetails = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                                ->select(DB::raw('id as id'),
                                    DB::raw("issuance_no as issuance_no"),
                                    DB::raw("request_no as request_no"),
                                    DB::raw("pmr_detail_id as pmr_detail_id"),
                                    DB::raw("detail_id as detail_id"),
                                    DB::raw("item as item"),
                                    DB::raw("item_desc as item_desc"),
                                    DB::raw("request_qty as request_qty"),
                                    DB::raw("issued_qty_o as issued_qty_o"),
                                    DB::raw("issued_qty_t as issued_qty_t"),
                                    DB::raw("IFNULL(lot_no,'') as lot_no"),
                                    DB::raw('location as location'),
                                    DB::raw("status as status"))
                                ->where('issuance_no',$whsinfo->issuance_no)
                                ->get();
                return $data = [
                                'issuance' => $whsinfo,
                                'details' => $whsdetails,
                                'status' => 'success'
                            ];
            } else {
                return $this->first();
            }
        } else {
            $data = [
                    'msg' => "You've reached the first Issuance Number",
                    'status' => 'failed'
                ];
        }

        return $data;
    }

    private function last()
    {
        $data = [];
        $whsinfo = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                    ->select('id',
                        'issuance_no',
                        'request_no',
                        'status',
                        'create_user',
                        DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                        'total_req_qty',
                        'update_user',
                        DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                    ->where("id", "=", function ($query) {
                        $query->select(DB::raw(" MAX(id)"))
                        ->from('tbl_wbs_warehouse_mat_issuance');
                      })
                    ->first();
        if ($this->com->checkIfExistObject($whsinfo) > 0) {

           $whsdetails = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                            ->select(DB::raw('id as id'),
                                    DB::raw("issuance_no as issuance_no"),
                                    DB::raw("request_no as request_no"),
                                    DB::raw("pmr_detail_id as pmr_detail_id"),
                                    DB::raw("detail_id as detail_id"),
                                    DB::raw("item as item"),
                                    DB::raw("item_desc as item_desc"),
                                    DB::raw("request_qty as request_qty"),
                                    DB::raw("issued_qty_o as issued_qty_o"),
                                    DB::raw("issued_qty_t as issued_qty_t"),
                                    DB::raw("IFNULL(lot_no,'') as lot_no"),
                                    DB::raw('location as location'),
                                    DB::raw("status as status"))
                            ->where('issuance_no',$whsinfo->issuance_no)
                            ->get();

            $data = [
                    'issuance' => $whsinfo,
                    'details' => $whsdetails,
                    'status' => 'success'
                ];
        }

        return $data;
    }

    private function first()
    {
        $data = [];
        $whsinfo = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                    ->select('id',
                        'issuance_no',
                        'request_no',
                        'status',
                        'create_user',
                        DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                        'total_req_qty',
                        'update_user',
                        DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                    ->where("id", "=", function ($query) {
                        $query->select(DB::raw(" MIN(id)"))
                        ->from('tbl_wbs_warehouse_mat_issuance');
                      })
                    ->first();
        if ($this->com->checkIfExistObject($whsinfo) > 0) {

           $whsdetails = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                            ->select(DB::raw('id as id'),
                                    DB::raw("issuance_no as issuance_no"),
                                    DB::raw("request_no as request_no"),
                                    DB::raw("pmr_detail_id as pmr_detail_id"),
                                    DB::raw("detail_id as detail_id"),
                                    DB::raw("item as item"),
                                    DB::raw("item_desc as item_desc"),
                                    DB::raw("request_qty as request_qty"),
                                    DB::raw("issued_qty_o as issued_qty_o"),
                                    DB::raw("issued_qty_t as issued_qty_t"),
                                    DB::raw("IFNULL(lot_no,'') as lot_no"),
                                    DB::raw('location as location'),
                                    DB::raw("status as status"))
                            ->where('issuance_no',$whsinfo->issuance_no)
                            ->get();

            $data = [
                    'issuance' => $whsinfo,
                    'details' => $whsdetails,
                    'status' => 'success'
                ];
        }

        return $data;
    }

    public function getLatest(Request $req)
    {
        if (empty($req->to) && !empty($req->issuanceno)) {
            $iss = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                    ->select(DB::raw('issuance_no as issuance_no'),
                            DB::raw('request_no as request_no'),
                            DB::raw('status as status'),
                            DB::raw('total_req_qty as total_req_qty'),
                            DB::raw('create_user as create_user'),
                            DB::raw('update_user as update_user'),
                            DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                    ->where('issuance_no',$req->issuanceno)
                    ->orderBy('created_at','desc')
                    ->count();
            if ($iss > 0) {
                 $iss = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                            ->select(DB::raw('issuance_no as issuance_no'),
                                    DB::raw('request_no as request_no'),
                                    DB::raw('status as status'),
                                    DB::raw('total_req_qty as total_req_qty'),
                                    DB::raw('create_user as create_user'),
                                    DB::raw('update_user as update_user'),
                                    DB::raw("DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                                    DB::raw("DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                            ->where('issuance_no',$req->issuanceno)
                            ->orderBy('created_at','desc')
                            ->first();
                $details = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                                ->select(DB::raw('id as id'),
                                        DB::raw("issuance_no as issuance_no"),
                                        DB::raw("request_no as request_no"),
                                        DB::raw("pmr_detail_id as pmr_detail_id"),
                                        DB::raw("detail_id as detail_id"),
                                        DB::raw("item as item"),
                                        DB::raw("item_desc as item_desc"),
                                        DB::raw("request_qty as request_qty"),
                                        DB::raw("issued_qty_o as issued_qty_o"),
                                        DB::raw("issued_qty_t as issued_qty_t"),
                                        DB::raw("IFNULL(lot_no,'') as lot_no"),
                                        DB::raw('location as location'),
                                        DB::raw("status as status"))
                                ->where('issuance_no',$iss->issuance_no)
                                ->get();

                
                return $data = [
                            'issuance' => $iss,
                            'details' => $details,
                            'status' => 'success'
                        ];
            }

            return $data = [
                'status' => 'failed',
                'msg' => 'No data found.'
            ];
        }

        if (!empty($req->to) && !empty($req->issuanceno)) {
            return $this->navigate($req->to,$req->issuanceno);
        }
        if (empty($req->to) && empty($req->issuanceno)) {
            return $this->last();
        }
    }

    public function postCancelIssuance(Request $req)
    {
        $e = ['msg' => 'Cancelling failed.'];
        $iss = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                ->where('issuance_no',$req->issuancenowhs)
                ->update([
                    'status' => 'Cancelled',
                    'update_user' => Auth::user()->user_id
                ]);

        if ($iss) {

             DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                ->where('issuance_no',$req->issuancenowhs)
                ->where('request_no',$req->reqno)->update([
                    'status' => 'Cancelled',
                    'update_user' => Auth::user()->user_id
                ]);
            
            $e['msg'] = "Issuance Number [".$req->issuancenowhs."] was successfully cancelled.";
        }
        return $e;
    }

    public function postCancelRequest(Request $request_data)
    {
        $result = 0;
        try
        {
            $recid = $request_data['id'];
            $result = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                    ->where('id', $recid)
                    ->update([
                        'status'       => 'Cancelled'
                        ,'update_user' => Auth::user()->user_id
                        ,'updated_at'  => date("Y/m/d H:i:sa")]);

            $message = "Selected transaction successfully cancelled.";
            $output = redirect(url('/wbswhsmatissuance?page=CUR&id='. $recid))
                        ->with(['message' => $message, 'active_tab'=>'1', 'action'=>'VIEW']);
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
        return $output;
    }

    public function postSearchRequest(Request $request_data)
    {
        $condition = $request_data['condition_arr'];
        $ctr = 0;
        $value = null;
        $result = null;

        $issuance_cond = '';
        $transno_cond = '';
        $status_cond = '';
        $date_cond = '';

        try
        {
            # Create Location. Condition
            if(empty($condition['srch_issuanceno']))
            {
                $issuance_cond ='';
            }
            else
            {
                $issuance_cond = " AND issuance_no like '%" . $condition['srch_issuanceno'] . "%'";
            }

            # Create Pallet No. Condition
            if(empty($condition['srch_transno']))
            {
                $transno_cond = '';
            }
            else
            {
                $transno_cond = " AND request_no = '" . $condition['srch_transno'] . "'";
            }

            if (empty($condition['srch_from']) || empty($condition['srch_to']))
            {
                $date_cond = '';
            }
            else
            {
                $date_cond = "AND issued_date BETWEEN '" . $condition['srch_from'] . "' AND '" . $condition['srch_to']. "'";
            }

            # Create Status Condition
            if($condition['srch_serving'] > 0 || $condition['srch_closed'] > 0 || $condition['srch_cancelled'] > 0)
            {
                if($condition['srch_serving'] == 1)
                {
                    $alert = "'Serving'";
                }
                else
                {
                    $alert = "''";
                }

                if($condition['srch_closed'] == 1)
                {
                    $close = "'Closed'";
                }
                else
                {
                    $close = "''";
                }

                if($condition['srch_cancelled'] == 1)
                {
                    $cancelled = "'Cancelled'";
                }
                else
                {
                    $cancelled = "''";
                }

                $status_cond = " AND `status` IN (". $alert .", ". $close .",". $cancelled.")";
            }

            # Retrieve Data using the generated conditions.
            $wmi_details_data = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance')
                        ->select( 'id'
                            , 'issuance_no'
                            , 'request_no'
                            , 'status'
                            , 'create_user'
                            , DB::raw("(CASE created_at
                                WHEN '0000-00-00' THEN NULL
                                ELSE DATE_FORMAT(created_at, '%m/%d/%Y %h:%i %p')
                               END) AS created_at")
                            , 'update_user'
                            , DB::raw("(CASE updated_at
                                WHEN '0000-00-00' THEN NULL
                                ELSE DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p')
                               END) AS updated_at"))
                        ->whereRaw(" 1=1 "
                            . $issuance_cond
                            . $transno_cond
                            . $status_cond
                            . $date_cond)
                        ->get();
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }

        return json_encode($wmi_details_data);
    }

    public function getPrintRequest(Request $request_data)
    {
        $id = trim($request_data['id']);
        $cur_id = '';
        $inventory_no = '';
        $max_id = '';
        $max_id = '';

        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);

        $common = new CommonController;
        $company_info = $common->getCompanyInfo();

        $wmi_data = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance as WM')
                       ->join('tbl_request_summary as RS', 'RS.transno', '=', 'WM.request_no')
                       ->select('WM.id'
                            , 'WM.issuance_no'
                            , 'WM.request_no'
                            , 'RS.pono'
                            , 'WM.status'
                            , 'RS.requestedby'
                            , DB::raw("DATE_FORMAT(RS.updated_at, '%m/%d/%Y %h:%i %p') as issued_date")
                            , 'WM.create_user'
                            , DB::raw("DATE_FORMAT(WM.created_at, '%m/%d/%Y %h:%i %p') as created_at")
                            , 'WM.update_user'
                            , DB::raw("DATE_FORMAT(WM.updated_at, '%m/%d/%Y %h:%i %p') as updated_at"))
                       ->where("WM.id", "=", $id)
                        ->get();


        if(count($wmi_data) > 0)
        {
            $issuance_no = $wmi_data[0]->issuance_no;
            $request_no  = $wmi_data[0]->request_no;
            $po_no       = $wmi_data[0]->pono;
            $issued_by   = $wmi_data[0]->requestedby;
            $issued_date = $wmi_data[0]->issued_date;
            $status      = $wmi_data[0]->status;
            $create_user = $wmi_data[0]->create_user;
            $created_at  = $wmi_data[0]->created_at;
            $update_user = $wmi_data[0]->update_user;
            $updated_at  = $wmi_data[0]->updated_at;

            $wmi_details_data = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                            ->where('issuance_no','=', $issuance_no)
                            ->select('id'
                                    , 'detail_id'
                                    ,'item as code'
                                    , 'item_desc as name'
                                    , DB::raw('FORMAT(issued_qty_o,2) AS issuedqty')
                                    , DB::raw('FORMAT(request_qty,2) AS requestqty')
                                    , DB::raw('FORMAT(issued_qty_t,2) AS servedqty')
                                    , DB::raw("IFNULL(lot_no,'') as lot_no")
                                    , 'location'
                                    , DB::raw("'' as remarks"))
                            ->orderBy('item')
                            ->get();
        }
        else
        {
            $issuance_no = "";
            $request_no  = "";
            $po_no       = "";
            $issued_by   = "";
            $issued_date = "";
            $status      = "";
            $create_user = "";
            $created_at  = "";
            $update_user = "";
            $updated_at  = "";
            $wmi_details_data = [];
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
                    bottom: 20px;
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
                <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-size:12px;">
                    <tbody>
                        <tr>
                            <td style="width: 100px;">____________________</td>
                            <td style="width: 100px;">____________________</td>
                            <td style="width: 100px;">____________________</td>
                            <td style="width: 100px;">____________________</td>
                            <td style="width: 100px;">____________________</td>
                        </tr>
                        <tr>
                            <td style="width: 100px; padding-left: 30px;">Prepared By: </td>
                            <td style="width: 100px; padding-left: 30px;">Issued By: </td>
                            <td style="width: 100px; padding-left: 30px;">Received By: </td>
                            <td style="width: 100px; padding-left: 30px;">Chedked By: </td>
                            <td style="width: 100px; padding-left: 30px;">Encoded By: </td>
                        </tr>
                    </tbody>
                </table>

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
                            <p style="line-height: 1.8px; font-size:12px; ">'. $company_info['tel1'] . ' ' . $company_info['tel2'] .'</p>
                            <h2><ins>MATERIAL REQUEST</ins></h2>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="fontArial" border="0" cellpadding="3" cellspacing="3" style="width: 100%;  font-size:12px;">
                    <tbody>
                        <tr>
                            <td style="width: 150px;">Issuance No. :</td>
                            <td colspan="2">'. $issuance_no .'</td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Request No. :</td>
                            <td colspan="2">'. $request_no .'</td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">PO No.     :</td>
                            <td colspan="2">'. $po_no .'</td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Issued By:</td>
                            <td colspan="2">'. $issued_by .'</td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Date Issued :</td>
                            <td colspan="2">'. $issued_date .'</td>
                        </tr>
                    </tbody>
                </table>
                <br/>
                <table class="fontArial"  style="border: 2px solid black ; border-collapse: collapse; width:100%; cellspacing:0; cellpadding:0; font-size:10px;">
                    <thead style="border: 2px solid black;">
                        <tr>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Line#.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Item No.</strong></th>
                            <th style="border-right: 1px solid black; width:200px;" scope="col"><strong>Item Description</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Lot No.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Request Qty.</strong></th>
                            <th style="border-right: 1px solid black; width:100px;" scope="col"><strong>Issued Qty.</strong></th>
                            <th style="border-right: 1px solid black;" scope="col"><strong>Remarks</strong></th>
                        </tr>
                    </thead>
                    <tbody>';

            $html2 = '';

            foreach ($wmi_details_data as $key => $row)
            {
                $html2 = $html2 .'<tr>
                        <td style="border-right: 1px solid black; text-align: left;">'. $row->detail_id .'</td>
                        <td style="border-right: 1px solid black; text-align: left;">'. $row->code .'</td>
                        <td style="border-right: 1px solid black; text-align: left; width:200px;" >'. $row->name .'</td>
                        <td style="border-right: 1px solid black; text-align: left;">'. $row->lot_no .'</td>
                        <td style="border-right: 1px solid black; text-align: right;">'. $row->requestqty .'</td>
                        <td style="border-right: 1px solid black; text-align: right;">'. $row->issuedqty .'</td>
                        <td style="border-right: 1px solid black; text-align: left;">'. $row->remarks .'</td>
                        </tr>';
            }

            $html3 = '</tbody>
                </table>
          </body>
        </html>';
        // echo $html;

        # gather all html parts.
        $html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $html . $html2 . $html3;

        $pdf = PDF::loadHTML($html)->setPaper('letter', 'landscape');
        return $pdf->stream('Parts Receiveing'.Carbon::now().'.pdf');

        // # apply snappy pdf wrapper
        // $pdf = App::make('snappy.pdf.wrapper');
        // # transform html to pdf format.
        // $pdf->loadHTML($html)->setPaper('A4')->setOrientation('landscape');
        // # display PDF report to response.
        // return $pdf->inline();
    }

    public function getFifoTable(Request $req)
    {
        // $data = DB::connection($this->mysql)->table('tbl_wbs_inventory')
        //             ->where('item',$req->code)
        //             ->where('qty','>',0)
        //             ->where('for_kitting',1)
        //             ->select('id','item','item_desc','qty','lot_no','received_date')//received_date
        //             ->orderBy('received_date','asc')
        //             ->get();
        // if (count((array)$data) > 0) {
            $datatable = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                            ->where('item',$req->code)
                            ->where('qty','>',0)
                            ->where('for_kitting',1)
                            ->orderBy('received_date','asc')
                            ->select(['id','item','item_desc','qty','lot_no','received_date']);//received_date;

            return Datatables::of($datatable)
                        ->addColumn('action', function($data) {
                            return '<a href="javascript:;" class="btn btn-primary btn_select_lot input-sm" data-id="'.$data->id.'" data-lotno="'.$data->lot_no.'" data-qty="'.$data->qty.'">
                                <i class="fa fa-pencil"></i>
                                </a>';
                        })
                        ->make(true);
        // }
    }

    public function getFifoTablebc(Request $req)
    {
        $count = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details as a')
                    ->leftJoin('tbl_wbs_warehouse_mat_issuance as b','a.issuance_no','=','b.issuance_no')
                    ->where('a.issuance_no',$req->issuance)
                    ->where('a.item',$req->code)
                    ->count();
        if($count > 0){
            return DB::connection($this->mysql)->table('tbl_wbs_inventory')
                ->where('item',$req->code)
                ->where('qty','>',0)
                ->where('for_kitting',1)
                ->select('id','item','item_desc','qty','lot_no','created_at')//received_date
                ->orderBy('received_date','asc')
                ->get();        
        }else{
            return "norecord";
        }
    }

    private function checkIfExistObject($object)
    {
       return count( (array)$object);
    }

    public function getIfNotClose(Request $req)
    {
        $db = DB::connection($this->mysql)->table('tbl_request_summary')
                    ->where('transno',$req->transno)
                    ->select('status')
                    ->first();
        if ($this->checkIfExistObject($db)) {
            return $db->status;
        }
    }

    public function getWhsServing(Request $req)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                ->where('issuance_no',$req->whstransno)
                ->where('request_no',$req->reqtransno)
                ->get();
        return $data;
    }

    public function wbsWhsReport_Excel(Request $array)
    {
        try
        { 
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            
            Excel::create('WBS_Material_Issuance_Report'.$date, function($excel) use($array)
            {
                $excel->sheet('Sheet1', function($sheet) use($array)
                {
                    $dt = Carbon::now();
                    $date = $dt->format('m/d/Y');
                    $sheet->cell('A1',"PORDER");
                    $sheet->cell('B1',"CODE");
                    $sheet->cell('C1',"MOTO");
                    $sheet->cell('D1',"HOKAN");
                    $sheet->cell('E1',"SEIBAN");
                    $sheet->cell('F1',"PEDA");
                    $sheet->cell('G1',"JITUO");
                    $sheet->cell('H1',"LOTNAME");
                    $sheet->cell('I1',"FDATE");
                    $sheet->cell('J1',"TSLIP_NUM");
                    $sheet->cell('K1',"NAME");
                  
             
                   /* $sheet->setHeight(1,20);
                    $sheet->row(1, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setBackground('#ADD8E6');
                        $row->setFontSize(12);
                        $row->setAlignment('center');
                    });
                   
                    $sheet->setStyle(array(
                        'font' => array(
                            'name'  =>  'Calibri',
                            'size'  =>  15
                        )
                    ));*/

                    $issuanceno = $array->issuanceno;

                    $field = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details as a')
                                ->leftJoin('tbl_request_summary as b','a.request_no','=','b.transno')
                                ->select('b.pono','a.item','a.issued_qty_t',DB::raw("IFNULL(a.lot_no,'') as lot_no"),'a.created_at','a.request_no','a.item_desc','a.issuance_no')
                                ->where('a.issuance_no',$issuanceno)
                                ->get();
                    
                    $row = 2;
                    foreach ($field as $key => $val) {
                        $sheet->cell('A'.$row,"");
                        $sheet->cell('B'.$row,$val->item);
                        $sheet->cell('C'.$row,"WHS100");
                        $sheet->cell('D'.$row,"ASSY100");
                        $sheet->cell('E'.$row, $val->pono);
                        $sheet->cell('F'.$row, 1);
                        $sheet->cell('G'.$row, $val->issued_qty_t);
                        $sheet->cell('H'.$row, $val->lot_no);
                        $sheet->cell('I'.$row, $this->convertDate($val->created_at,'Ymd'));
                        $sheet->cell('J'.$row, substr($val->issuance_no,4));
                        $sheet->cell('K'.$row, $val->item_desc);
                       
                     /*   $sheet->row($row, function ($row) {
                            $row->setFontFamily('Calibri');
                            $row->setFontSize(10);
                            $row->setAlignment('center');
                        });
                        $sheet->setHeight($row,20);*/
                        $row++;
                    }
                });

            })->download('xls');
        } catch (Exception $e) {
            return redirect(url('/wbsphysicalinventory'))->with(['err_message' => $e]);
        }
    }

    private function formatDate($time)
    {
        $old_date = date($time);             
        $old_date_timestamp = strtotime($old_date);
        $new_date = date('Ymd', $old_date_timestamp); 

        return $new_date;
    }

    public function getsearch_viewDetails(Request $request)
    {
        $issuanceno = $request->issuanceno;
        $table = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                    ->select(DB::raw('detail_id as detail_id'),
                        DB::raw('item as item'),
                        DB::raw('item_desc as item_desc'),
                        DB::raw('request_qty as request_qty'),
                        DB::raw('issued_qty_o as issued_qty_o'),
                        DB::raw('issued_qty_t as issued_qty_t'),
                        DB::raw("IFNULL(lot_no,'') as lot_no"),
                        DB::raw('location as location'))
                    ->where('issuance_no',$issuanceno)
                    ->get();
        return $table;
    }

    public function getmatbarcode(Request $input)
    {
        $barcodeno = $input->barcode;
        $tablefields = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                ->select('id','item','item_desc','request_qty',DB::raw("SUM(issued_qty_o) as issued_qty_o"),DB::raw("SUM(issued_qty_t) as issued_qty_t"),DB::raw("IFNULL(lot_no,'') as lot_no"),'location')
                ->where('item',$barcodeno)
                ->get();
       
        if ($this->checkIfExistObject($tablefields) > 0) {
            return json_encode($tablefields[0]);
        }
    }

    public function getmatlotno(Request $request)
    {
        $item = $request->item;
        $lotno = $request->lotno;
        $pono = $request->pono; 

        $ok = DB::connection($this->mysql)->table('tbl_wbs_material_kitting_details as a')
                    ->join('tbl_wbs_inventory as b','a.item','=','b.item')
                    ->where('a.po',$pono)
                    ->where('a.item',$item)
                    ->where('b.lot_no',$lotno)
                    ->get();
       
        if($ok){
            return "Matched";
        }else{
            return "NM";
        }
    }

    public function materialRequestPDF(Request $req)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);

        $issuanceno = $req->issuanceno;

        $summary = DB::connection($this->mysql)->table('tbl_request_summary')
                        ->where('whstransno',$issuanceno)->first();

        $details = DB::connection($this->mysql)->table('tbl_request_detail')
                    ->where('whstransno',$issuanceno)
                    ->select('detailid',
                            'code',
                            'name',
                            'classification',
                            'issuedqty',
                            'requestqty',
                            'servedqty',
                            'location',
                            'lot_no',
                            'requestedby',
                            'last_served_by',
                            'last_served_date')
                    ->get();
        $data = [
            'summary' => $summary,
            'details' => $details
        ];

        $pdf = PDF::loadView('pdf.wbs_material_request', $data)
                    ->setPaper('A4')
                    ->setOption('margin-top', 6)
                    ->setOption('margin-left', 3)
                    ->setOption('margin-right', 3)
                    ->setOption('margin-bottom', 5)
                    ->setOrientation('portrait');
        return $pdf->inline('Material_Request_'.$date);
    }

    public function getBalance(Request $req)
    {
        $servedqty = 0;

        $request = DB::connection($this->mysql)->table('tbl_request_detail')
                        ->select(DB::raw("SUM(requestqty) as total_qty"))
                        ->where('whstransno',$req->issuanceno)
                        ->first();
        $issuance = DB::connection($this->mysql)->table('tbl_wbs_warehouse_mat_issuance_details')
                        ->select(DB::raw("SUM(issued_qty_t) as total_served"))
                        ->where('issuance_no',$req->issuanceno)
                        ->first();
        if (count((array)$issuance) > 0) {
            $servedqty = $issuance->total_served;
        }

        $balance = $request->total_qty - $servedqty;

        return $balance;
    }
}
