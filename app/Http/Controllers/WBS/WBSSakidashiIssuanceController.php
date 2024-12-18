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
use Carbon\Carbon;
use Dompdf\Dompdf;
use PDF;
use Excel;
use File;
use App;
use Datatables;

class WBSSakidashiIssuanceController extends Controller
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

    public function index()
    {
    	$common = new CommonController;

        $pgcode = Config::get('constants.MODULE_CODE_SAKIISS');

        if(!$common->getAccessRights($pgcode, $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            return view('wbs.sakidashiissuance',[
                'userProgramAccess' => $userProgramAccess,
                'pgcode' => $pgcode,
                'pgaccess' => $this->com->getPgAccess($pgcode)
            ]);
        }
    }

    public function searchPO(Request $req)
    {
        $data = [
            'msg' => "P.O. number [".$req->po."] doesn't exist.",
            'return_status' => "failed",
            'info' => [],
            'details' => [],
        ];
        $info = DB::connection($this->mssql)
                    ->table('XSLIP as s')
                    ->leftJoin('XHEAD as h', 's.CODE', '=', 'h.CODE')
                    ->leftjoin('XRECE as r', 's.SEIBAN','=','r.SORDER')
                    ->select(DB::raw('s.CODE as code'),
                            DB::raw('h.NAME as prodname'),
                            DB::raw('r.KVOL as POqty'),
                            DB::raw('s.PORDER as porder'),
                            DB::raw('r.SEDA as branch'),
                            DB::raw('s.SEIBAN as po'))
                    ->where('s.SEIBAN',$req->po)
                    ->orderBy('r.SEDA','desc')
                    ->first();
                    
        $date = Carbon::now();
        $dt = $date->addDays(2);
        $return_date = $dt->format('m/d/Y');

        if ($this->com->checkIfExistObject($info) > 0) {
                $ypics = DB::connection($this->mssql)
                                ->select("SELECT hk.CODE as kcode, 
                                                h.NAME as partname, 
                                                hk.KVOL as rqdqty, 
                                                x.ZAIK as actualqty,
                                                x.RACKNO as location, 
                                                i.DRAWING_NUM as drawnum, 
                                                i.VENDOR as supplier, 
                                                x.WHS100 as whs100, 
                                                x.WHS102 as whs102
                                        FROM XSLIP s
                                        LEFT JOIN XHIKI hk ON s.PORDER = hk.PORDER
                                        LEFT JOIN XITEM i ON i.CODE = hk.CODE
                                        LEFT JOIN XHEAD h ON h.CODE = hk.CODE
                                        LEFT JOIN (SELECT z.CODE, 
                                                        ISNULL(z1.ZAIK,0) as WHS100, 
                                                        ISNULL(z2.ZAIK,0) as WHS102, 
                                                        SUM(z.ZAIK) as ZAIK,
                                                        z.RACKNO FROM XZAIK z
                                                   LEFT JOIN XZAIK z1 ON z1.CODE = z.CODE AND z1.HOKAN = 'WHS100'
                                                   LEFT JOIN XZAIK z2 ON z2.CODE = z.CODE AND z2.HOKAN = 'WHS102'
                                                   WHERE z.RACKNO <> ''
                                                   GROUP BY z.CODE, z1.ZAIK, z2.ZAIK, z.RACKNO
                                        ) x ON x.CODE = hk.CODE
                                        WHERE s.SEIBAN = '".$req->po."' AND s.PORDER = '".$info->porder."'
                                        GROUP BY hk.CODE, 
                                                h.NAME, 
                                                i.VENDOR, 
                                                hk.KVOL, 
                                                i.DRAWING_NUM, 
                                                x.WHS100, 
                                                x.WHS102, 
                                                x.RACKNO,
                                                x.ZAIK");

                $details = [];
                foreach ($ypics as $key => $yp) {
                    array_push($details,[
                        'kcode' => $yp->kcode,
                        'partname' => $this->convert_unicodeJIS($yp->partname),
                        'rqdqty' => $yp->rqdqty,
                        'actualqty' => $yp->actualqty,
                        'location' => $this->convert_unicodeJIS($yp->location),
                        'drawnum' => $this->convert_unicodeJIS($yp->drawnum),
                        'supplier' => $this->convert_unicodeJIS($yp->supplier),
                        'whs100' => $yp->whs100,
                        'whs102' => $yp->whs102,
                    ]);
                }

                //$this->utf8_encode_deep($details);

            if ($this->com->checkIfExistObject($details) > 0) {
                $data = [
                    'return_status' => "success",
                    'info' => $info,
                    'details' => $details,
                    'return_date' => date('m/d/Y', strtotime("+2 days"))
                ];
            }

            return json_encode($data);
        }
        return json_encode($data);
        //return json_encode($data);
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

    public function saveReceivedBy(Request $req)
    {
        $success = true;
        $msg = '';
        $status = '';
        try {
            $issuance_no = $req->issuance_no;
            $user_id = Auth::user()->user_id;
            $alreadyReceived = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance')
            ->select('id')
            ->where('issuance_no', $issuance_no)
            ->whereNotNull('received_by')
            ->count();
            if($alreadyReceived == false) {
                DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance')
                ->where('issuance_no', $issuance_no)
                ->update([
                    'received_by' => $user_id,
                    'received_date' => Carbon::now()
                ]);
                $msg = "Received by was Successfully saved.";
                $status = 'success';
            }else {
                $msg = "Received by has already been set by another user.";
                $status = 'failed';
            }
            
        } catch(\Exception $e) {
            $msg = "There's some error while processing.";
            $status = 'failed';
        }
        $data = [
            'status' => $status,
            'msg' => $msg,
        ];
        return $data;
    }

    public function saveRecord(Request $req)
    {
        $details_query = 0;
        $info_query = 0;
        $data = [
            'msg' => 'Data was not saved.',
            'return_status' => 'failed'
        ];
        $info = $req->info;
        $details = $req->details;

        

        if ($info['issuanceno'] == '') {
            #insert info
            $issuanceno = $this->com->getTransCode('SAK_ISS');
            $info_query = $this->InsertToIssuance($info,$issuanceno);
            if ($info_query) {
                #insert details
                $details_query = $this->InsertToIssuanceItem($details,$info,$issuanceno);
            }
        } else {
            #update info
            $info_query = $this->UpdateIssuance($details,$info,$info['issuanceno']);
            if ($info_query) {
                $data = [
                    'msg' => 'Successfully updated.',
                    'return_status' => 'success'
                ];
            }
        }

        if ($details_query) {
            $data = [
                'msg' => 'Data was successfully saved.',
                'return_status' => 'success',
                'issuanceno' => $info['issuanceno']
            ];
        }
        return $data;
    }

    private function InsertToIssuance($info,$issuanceno)
    {
        return DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance')->insert([
                'issuance_no' => $issuanceno,
                'po_no' => $info['po'],
                'device_code' => $info['code'],
                'device_name' => $info['devname'],
                'po_qty' => $info['poqty'],
                'incharge' => $info['incharge'],
                'remarks' => $info['remarks'],
                'status' => 'Open',
                'create_user' => Auth::user()->user_id,
                'update_user' => Auth::user()->user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
    }

    private function InsertToIssuanceItem($detail,$info,$issuanceno)
    {
        $new_qty = $this->getFifoQty($detail['fifoid']) - $detail['issueqty'];

        DB::connection($this->mysql)->table('tbl_wbs_inventory')
            ->where('id',$detail['fifoid'])
            ->update(['qty' => $new_qty]);

        return DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance_item')->insert([
                'issuance_no' => $issuanceno,
                'item' => $detail['partcode'],
                'item_desc' => $detail['partname'],
                'lot_no' => $detail['lotno'],
                'pair_no' => $detail['pairno'],
                'issued_qty' => $detail['issueqty'],
                'required_qty' => $detail['reqqty'],
                'return_qty' => $detail['retqty'],
                'sched_return_date' => $this->convertDate($detail['schedretdate'],'Y-m-d'),
                'remarks' => $info['remarks'],
                'issuance_date' => $this->convertDate(Carbon::now(),'Y-m-d'),
                'create_user' => Auth::user()->user_id,
                'update_user' => Auth::user()->user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
    }

    private function UpdateIssuanceItem($detail,$info,$issuanceno)
    {
    	$checkItem = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance_item')
        				->where('issuance_no',$issuanceno)->count();
        if ($checkItem > 0) {
        	$db = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance_item')
        			->where('issuance_no',$issuanceno)
        			->update([
		                'item' => $detail['partcode'],
		                'item_desc' => $detail['partname'],
		                'lot_no' => $detail['lotno'],
		                'pair_no' => $detail['pairno'],
		                'issued_qty' => $detail['issueqty'],
		                'required_qty' => $detail['reqqty'],
		                'return_qty' => $detail['retqty'],
		                'sched_return_date' => $this->convertDate($detail['schedretdate'],'Y-m-d'),
		                'remarks' => $info['remarks'],
		                'issuance_date' => $this->convertDate(Carbon::now(),'Y-m-d'),
		                'update_user' => Auth::user()->user_id,
		                'updated_at' => Carbon::now()
		            ]);
        } else {
        	$db = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance_item')
        			->insert([
		                'issuance_no' => $issuanceno,
		                'item' => $detail['partcode'],
		                'item_desc' => $detail['partname'],
		                'lot_no' => $detail['lotno'],
		                'pair_no' => $detail['pairno'],
		                'issued_qty' => $detail['issueqty'],
		                'required_qty' => $detail['reqqty'],
		                'return_qty' => $detail['retqty'],
		                'sched_return_date' => $this->convertDate($detail['schedretdate'],'Y-m-d'),
		                'remarks' => $info['remarks'],
		                'issuance_date' => $this->convertDate(Carbon::now(),'Y-m-d'),
		                'create_user' => Auth::user()->user_id,
		                'update_user' => Auth::user()->user_id,
		                'created_at' => Carbon::now(),
		                'updated_at' => Carbon::now()
		            ]);

            if (empty($detail['fifoid']) || $detail['fifoid'] == "") {
            
            } else {
                $new_qty = $this->getFifoQty($detail['fifoid']) - $detail['issueqty'];

                DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('id',$detail['fifoid'])
                    ->update(['qty' => $new_qty]);
            }
        }
        return $db;
    }

    private function getFifoQty($id)
    {
        $check = DB::connection($this->mysql)
                    ->table('tbl_wbs_inventory')
                    ->select('qty')
                    ->where('id',$id)
                    ->where('deleted',0)
                    ->count();

        if ($check > 0) {
            $data = DB::connection($this->mysql)
                        ->table('tbl_wbs_inventory')
                        ->select('qty')
                        ->where('id',$id)
                        ->where('deleted',0)
                        ->first();
            return $data->qty;
        } else {
            return "0";
        }
        
    }

    private function UpdateIssuance($detail,$info,$issuanceno)
    {
        $db = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance')
                ->where('issuance_no',$issuanceno)
                ->update(['updated_at' => Carbon::now(),
                        'update_user' => Auth::user()->user_id
                ]);

            $this->UpdateIssuanceItem($detail,$info,$issuanceno);
        return $db;
    }

    public function getLatest()
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                    ->join('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                    ->select('i.id as id',
                            'i.issuance_no as issuance_no',
                            'i.po_no as po_no',
                            'i.device_code as device_code',
                            'i.device_name as device_name',
                            'i.po_qty as po_qty',
                            'i.incharge as incharge',
                            'i.remarks as remarks',
                            'i.status as status',
                            'i.create_user as create_user',
                            'i.update_user as update_user',
                            DB::raw("DATE_FORMAT(i.created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            DB::raw("DATE_FORMAT(i.updated_at, '%m/%d/%Y %h:%i %p') as updated_at"),
                            'd.item as item',
                            'd.item_desc as item_desc',
                            'd.lot_no as lot_no',
                            'd.pair_no as pair_no',
                            'd.issued_qty as issued_qty',
                            'd.required_qty as req_qty',
                            'd.return_qty as return_qty',
                            DB::raw("IF(IFNULL(i.received_by,'') = '',0,1) as received_taken"),
                            DB::raw("DATE_FORMAT(sched_return_date, '%m/%d/%Y') as return_date"))
                    ->orderBy('i.created_at','desc')->first();
        if ($this->com->checkIfExistObject($data) > 0) {
        	return json_encode($data);
        }
    }

    public function getSakisahiData(Request $req)
    {
    	$data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                    ->join('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                    ->select('i.id as id',
                            'i.issuance_no as issuance_no',
                            'i.po_no as po_no',
                            'i.device_code as device_code',
                            'i.device_name as device_name',
                            'i.po_qty as po_qty',
                            'i.incharge as incharge',
                            'i.remarks as remarks',
                            'i.status as status',
                            'i.create_user as create_user',
                            'i.update_user as update_user',
                            DB::raw("DATE_FORMAT(i.created_at, '%m/%d/%Y %h:%i %p') as created_at"),
                            DB::raw("DATE_FORMAT(i.updated_at, '%m/%d/%Y %h:%i %p') as updated_at"),
                            'd.item as item',
                            'd.item_desc as item_desc',
                            'd.lot_no as lot_no',
                            'd.pair_no as pair_no',
                            'd.issued_qty as issued_qty',
                            'd.required_qty as req_qty',
                            'd.return_qty as return_qty',
                            DB::raw("IF(IFNULL(i.received_by,'') = '',0,1) as received_taken"),
                            DB::raw("DATE_FORMAT(sched_return_date, '%m/%d/%Y') as return_date"))
                    ->where('i.issuance_no',$req->issuanceno)
                    ->orderBy('i.created_at','desc')->first();
        if ($this->com->checkIfExistObject($data) > 0) {
        	return json_encode($data);
        }
    }

    public function getTransCode(Request $req)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                    ->join('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                    ->select('i.id as id',
                            'i.issuance_no as issuance_no',
                            'i.po_no as po_no',
                            'i.device_code as device_code',
                            'i.device_name as device_name',
                            'i.po_qty as po_qty',
                            'i.incharge as incharge',
                            'i.remarks as remarks',
                            'i.status as status',
                            'i.create_user as create_user',
                            'i.update_user as update_user',
                            'i.created_at as created_at',
                            'i.updated_at as updated_at',
                            'd.item as item',
                            'd.item_desc as item_desc',
                            'd.lot_no as lot_no',
                            'd.pair_no as pair_no',
                            'd.issued_qty as issued_qty',
                            'd.required_qty as req_qty',
                            'd.return_qty as return_qty',
                            DB::raw("IF(IFNULL(i.received_by,'') = '',0,1) as received_taken"),
                            DB::raw("DATE_FORMAT(sched_return_date, '%m/%d/%Y') as return_date"))
                    ->where('i.issuance_no',$req->issuanceno)
                    ->orderBy('i.created_at','desc')->first();
        if ($this->com->checkIfExistObject($data) > 0) {
            return json_encode($data);
        }
    }

    public function itemHistory(Request $req)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                    ->join('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                    ->select('i.issuance_no as issuance_no',
                            'd.issued_qty as issued_qty',
                            'd.required_qty as req_qty',
                            'd.return_qty as return_qty',
                            'd.lot_no as lot_no',
                            'd.pair_no as pair_no',
                            'i.remarks as remarks',
                            'd.id as id')
                    ->where('i.po_no',$req->po)
                    ->where('d.item',$req->item)
                    ->orderBy('id','desc')
                    ->get();
        if ($this->com->checkIfExistObject($data) > 0) {
            return json_encode($data);
        } else {
            return $data = [];
        }
    }

    public function cancelPO(Request $req)
    {
        $data = [
            'msg' => "Cancelling P.O. failed.",
            'status' => 'failed'
        ];
        $update = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance')
                    ->where('po_no',$req->po)->update(['status' => 'Cancelled']);
        if ($update) {
            $data = [
                'msg' => "Issuance No. [".$req->po."] was successfully cancelled.",
                'status' => 'success'
         ];
     }
        return $data;
    }

    public function sakiNavigate(Request $req)
    {
        switch ($req->to) {
            case 'nxt':
            case 'prev':
                return $this->navQueryNextPrev($req->to,$req->issuanceno);
                break;

            case 'last':
                return $this->navQueryLastFirst('desc');
                break;

            case 'first':
                return $this->navQueryLastFirst('asc');
                break;

            default:
                return $this->navQueryLastFirst('desc');
                break;
        }
    }

    private function navQueryNextPrev($to,$issuanceno)
    {
        $id = 0;
        if ($this->getID($issuanceno) > 0) {
            $id = $this->getID($issuanceno);
        }

        $newID = $id - 1;

        if ($to == 'nxt') {
            $newID = $id + 1;
        }

        $data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                    ->leftjoin('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                    ->select('i.id as id',
                            'i.issuance_no as issuance_no',
                            'i.po_no as po_no',
                            'i.device_code as device_code',
                            'i.device_name as device_name',
                            'i.po_qty as po_qty',
                            'i.incharge as incharge',
                            'i.remarks as remarks',
                            'i.status as status',
                            'i.create_user as create_user',
                            'i.update_user as update_user',
                            'i.created_at as created_at',
                            'i.updated_at as updated_at',
                            'd.item as item',
                            'd.item_desc as item_desc',
                            'd.lot_no as lot_no',
                            'd.pair_no as pair_no',
                            'd.issued_qty as issued_qty',
                            'd.required_qty as req_qty',
                            'd.return_qty as return_qty',
                            DB::raw("IF(IFNULL(i.received_by,'') = '',0,1) as received_taken"),
                            DB::raw("DATE_FORMAT(sched_return_date, '%m/%d/%Y') as return_date"))
                    ->where('i.id',$newID)
                    ->first();
        if ($this->com->checkIfExistObject($data) > 0) {
            return json_encode($data);
        }
    }

    private function navQueryLastFirst($order)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                    ->join('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                    ->select('i.id as id',
                            'i.issuance_no as issuance_no',
                            'i.po_no as po_no',
                            'i.device_code as device_code',
                            'i.device_name as device_name',
                            'i.po_qty as po_qty',
                            'i.incharge as incharge',
                            'i.remarks as remarks',
                            'i.status as status',
                            'i.create_user as create_user',
                            'i.update_user as update_user',
                            'i.created_at as created_at',
                            'i.updated_at as updated_at',
                            'd.item as item',
                            'd.item_desc as item_desc',
                            'd.lot_no as lot_no',
                            'd.pair_no as pair_no',
                            'd.issued_qty as issued_qty',
                            'd.required_qty as req_qty',
                            'd.return_qty as return_qty',
                            DB::raw("IF(IFNULL(i.received_by,'') = '',0,1) as received_taken"),
                            DB::raw("DATE_FORMAT(sched_return_date, '%m/%d/%Y') as return_date"))
                    ->orderBy('i.id',$order)
                    ->get();
        if ($this->com->checkIfExistObject($data) > 0) {
            return json_encode($data[0]);
        }
    }

    private function getID($issuanceno)
    {
        $data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance')
                    ->where('issuance_no',$issuanceno)
                    ->select('id')
                    ->first();
        return $data->id;
    }

    private function convertDate($date,$format)
    {
        $time = strtotime($date);
        $newdate = date($format,$time);
        return $newdate;
    }

    /**
    * Search Sakidashi Data.
    **/
    public function searchSiWbsData(Request $request_data)
    {
        $condition = $request_data['condition_arr'];
        $ctr = 0;
        $value = null;
        $result = null;

        $pono_cond = '';
        $devicecode_cond = '';
        $itemcode_cond = '';
        $incharge_cond = '';
        $status_cond = '';

        try
        {
            # Create PO No. Condition
            if(empty($condition['srch_pono']))
            {
                $pono_cond ='';
            }
            else
            {
                $pono_cond = " AND S.po_no = '" . $condition['srch_pono'] . "'";
            }

            # Create Device Code Condition
            if(empty($condition['srch_devicecode']))
            {
                $devicecode_cond = '';
            }
            else
            {
                $devicecode_cond = " AND S.device_code = '" . $condition['srch_devicecode'] . "'";
            }

            # Create Item Code Condition
            if(empty($condition['srch_itemcode']))
            {
                $itemcode_cond = '';
            }
            else
            {
                $itemcode_cond = " AND SI.item = '" . $condition['srch_itemcode'] . "'";
            }

            # Create Incharge Condition
            if(empty($condition['srch_incharge']))
            {
                $incharge_cond = '';
            }
            else
            {
                $incharge_cond = " AND S.incharge = '" . $condition['srch_incharge'] . "'";
            }


            # Create Status Condition
            if($condition['srch_open'] > 0 || $condition['srch_close'] > 0 || $condition['srch_cancelled'] > 0)
            {
                if($condition['srch_open'] == 1)
                {
                    $open = "'Open'";
                }
                else
                {
                    $open = "''";
                }

                if($condition['srch_close'] == 1)
                {
                    $close = "'Close'";
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

                $status_cond = " AND S.status IN (". $open .", ". $close .",". $cancelled.")";
            }


            # Retrieve Data using the generated conditions.
            $si_details_data = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as S')
                        ->join(DB::raw( "(SELECT issuance_no, item, item_desc
                                            FROM tbl_wbs_sakidashi_issuance_item
                                            GROUP BY issuance_no, item, item_desc) as SI"), 'SI.issuance_no', '=', 'S.issuance_no')
                        ->select( 'S.id'
                            , 'S.issuance_no'
                            , 'S.po_no'
                            , 'S.device_code'
                            , 'S.device_name'
                            , 'S.incharge'
                            , 'SI.item'
                            , 'SI.item_desc'
                            , 'S.status'
                            , 'S.create_user'
                            , DB::raw("(CASE S.created_at
                                WHEN '0000-00-00' THEN NULL
                                ELSE DATE_FORMAT(S.created_at, '%m/%d/%Y %h:%i %p')
                               END) AS created_at")
                            , 'S.update_user'
                            , DB::raw("(CASE S.updated_at
                                WHEN '0000-00-00' THEN NULL
                                ELSE DATE_FORMAT(S.updated_at, '%m/%d/%Y %h:%i %p')
                               END) AS updated_at"))
                        ->whereRaw(" 1=1 "
                            . $pono_cond
                            . $devicecode_cond
                            . $itemcode_cond
                            . $incharge_cond
                            . $status_cond);
                        //->get();

            return Datatables::of($si_details_data)->make(true);
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }

        return json_encode($si_details_data);
    }

    /**
    * Generate Parts Report.
    **/
    public function printSiReport_old(Request $req)
    {
        $id = trim($req->id);
        $cur_id = '';
        $issuance_no = '';
        $max_id = '';
        $max_id = '';

        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);

        $sakidashi = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as S')
                        ->join(DB::raw( "(SELECT issuance_no, item, item_desc, lot_no, issued_qty, pair_no,
                                                required_qty, return_qty, sched_return_date, updated_at
                                    FROM tbl_wbs_sakidashi_issuance_item
                                    GROUP BY issuance_no, item, item_desc
                                    ORDER BY issuance_no, updated_at DESC) as SI"), 'SI.issuance_no', '=', 'S.issuance_no')
                        ->select( 'S.issuance_no'
                            , 'S.po_no'
                            , 'S.device_code'
                            , 'S.device_name'
                            , 'S.incharge'
                            , 'SI.item'
                            , 'SI.item_desc'
                            , 'SI.lot_no'
                            , 'SI.pair_no'
                            , DB::RAW('FORMAT(SI.issued_qty, 0) AS issued_qty')
                            , DB::RAW('FORMAT(SI.required_qty, 0) AS required_qty')
                            , DB::RAW('FORMAT(SI.return_qty, 0) AS return_qty')
                            , DB::raw("(CASE SI.sched_return_date
                                WHEN '0000-00-00 00:00' THEN NULL
                                ELSE DATE_FORMAT(SI.sched_return_date, '%M %d, %Y')
                               END) AS sched_return_date")
                            , 'S.status'
                            , DB::raw("(CASE SI.updated_at
                                WHEN '0000-00-00 00:00' THEN NULL
                                ELSE DATE_FORMAT(SI.updated_at, '%m/%d/%Y %h:%i %p')
                               END) AS updated_at"))
                        ->where('S.id', '=', $id)
                        ->first();

        if($this->com->checkIfExistObject($sakidashi) > 0)
        {
            $issuanceno      = $sakidashi->issuance_no;
            $issuancedate    = $sakidashi->updated_at;
            $item            = $sakidashi->item;
            $itemdesc        = $sakidashi->item_desc;
            $lotno           = $sakidashi->lot_no;
            $pairno          = $sakidashi->pair_no;
            $pono            = $sakidashi->po_no;
            $devicecode      = $sakidashi->device_code;
            $devicename      = $sakidashi->device_name;
            $issuedqty       = $sakidashi->issued_qty;
            $requiredqty     = $sakidashi->required_qty;
            $returnqty       = $sakidashi->return_qty;
            $schedreturndate = $sakidashi->sched_return_date;
            $incharge        = $sakidashi->incharge;

                /*$si_details_data = DB::connection($this->mysql)->table()
                            ->join()
                            ->join()
                            ->leftJoin()
                            ->whereRaw()
                            ->select()
                            ->orderBy()
                            ->get();*/
        }
        else
        {
            $issuanceno      = '';
            $issuancedate    = '';
            $item            = '';
            $itemdesc        = '';
            $lotno           = '';
            $pairno          = '';
            $pono            = '';
            $devicecode      = '';
            $devicename      = '';
            $issuedqty       = '';
            $requiredqty     = '';
            $returnqty       = '';
            $schedreturndate = '';
            $incharge        = '';
        }

        $html1 = '<!DOCTYPE html>
                    <html>

                    <head>
                        <style type="text/css">
                            @page
                            {
                                margin-top: 0.0em;
                                margin-bottom: 0.0em;
                                margin-left: 0.0em;
                                margin-right: 0.0em;
                            }
                            @page :first
                            {
                                margin-top: 0px;
                            }
                            html {
                                height: 100%;
                            }
                            body
                            {
                                height: 50%;
                                margin: 3px, 3px, 3px, 3px;
                                padding: 0px;
                                min-height: 100vh;
                            }
                            .rTable {
                                display: table;
                                width: 100%;
                            }

                            .rTableRow {
                                display: table-row;
                            }

                            .rTableHeading {
                                background-color: #ddd;
                                display: table-header-group;
                            }

                            .rTableCell,
                            .rTableHead {
                                display: table-cell;
                                padding: 6px 10px;
                                border: 0px solid #999999;
                            }

                            .rTableCell-bordered,
                            .rTableHead-bordered {
                                display: table-cell;
                                padding: 3px 10px;
                                border: 1px solid black;
                            }

                            .rTableCell-half{
                                display: table-cell;
                                padding: 3px 10px;
                                border: 2px solid black;
                                width:50%;
                            }

                            .width-10 {
                                width:10%;
                            }
                            .width-20 {
                                width:20%;
                            }
                            .width-30 {
                                width:30%;
                            }
                            .width-35 {
                                width:35%;
                            }
                            .width-40 {
                                width:40%;
                            }
                            .width-50 {
                                width:50%;
                            }
                            .width-70 {
                                width:70%;
                            }
                            .width-60 {
                                width:60%;
                            }

                            .height-30 {
                                height:500px;
                            }

                            .center{
                                text-align: center;
                            }

                            .right{
                                text-align: right;
                            }

                            .largeText{
                                font-size: 16px;
                            }

                            .large1Text{
                                font-size: 15px;
                            }

                            .mediumText{
                                font-size: 14px;
                            }

                            .smallText{
                                font-size: 13px;
                            }

                            .smallestText{
                                font-size: 10px;
                            }

                            .fontArial
                            {
                                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                            }

                            .rTableHeading {
                                display: table-header-group;
                                background-color: #ddd;
                                font-weight: bold;
                            }

                            .rTableFoot {
                                display: table-footer-group;
                                font-weight: bold;
                                background-color: #ddd;
                            }

                            .rTableBody {
                                display: table-row-group;
                            }
                            .rBorder-1 {
                                border: 1px solid black;
                            }
                            .rBorder-2 {
                                border: 3px solid black;
                            }
                        </style>
                    </head>

                    <body>
                        <div id="s7" class="rTable fontArial" style="max-height:3000px;overflow:auto;">
                            <div class="rTableBody">
                                <div class="rTableRow" style="height:100%">
                                    <div class="rTableCell-half rBorder-2">
                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell mediumText">WAREHOUSE COPY</div>
                                                    <div class="rTableCell mediumText right">CTRL No.: '. $issuanceno .'</div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell mediumText">(WHS100)</div>
                                                    <div class="rTableCell"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell center largeText"><strong>SAKIDASHI ISSUANCE SLIP</strong></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell right mediumText">Issuance Date: '. $issuancedate .'</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow rBorder-2">
                                                                    <div class="rTableCell-bordered width-30 smallText">ITEM CODE</div>
                                                                    <div class="rTableCell-bordered width-70 "><strong class="smallText">'. $item .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow rBorder-2">
                                                                    <div class="rTableCell-bordered width-30 smallText">CONTACT TYPE</div>
                                                                    <div class="rTableCell-bordered width-70 "><strong class="smallText">'. $itemdesc .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow rBorder-2">
                                                                    <div class="rTableCell-bordered width-30 smallText">LOT # / PAIR #</div>
                                                                    <div class="rTableCell-bordered width-70">
                                                                        <div class="rTable">
                                                                            <div class="rTableBody">
                                                                                <div class="rTableRow">
                                                                                    <div class="rTableCell"><strong class="smallText">'. $lotno .'</strong></div>
                                                                                    <div class="rTableCell"><strong class="smallText">'. $pairno .'</strong></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">PO #</div>
                                                                    <div class="rTableCell-bordered width-70"><strong class="smallText">'. $pono .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">DEVICE CODE</div>
                                                                    <div class="rTableCell-bordered width-70 smallText">'. $devicecode .'</div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">DEVICE NAME</div>
                                                                    <div class="rTableCell-bordered width-70 smallText">'. $devicename .'</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-40 smallText">REQ ISSUANCE QTY</div>
                                                                    <div class="rTableCell-bordered width-60 center"><strong class="smallText">'. $requiredqty .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-40 smallText">REEL QTY</div>
                                                                    <div class="rTableCell-bordered width-60 center"><strong class="smallText">'. $issuedqty .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-40 smallText">QTY FOR RETURN</div>
                                                                    <div class="rTableCell-bordered width-60 center"><strong class="smallText">'. $returnqty .'</strong></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell mediumText">SCHEDULED DATE FOR RETURN</div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell center mediumText"><strong>'. $schedreturndate .'</strong>
                                                        <hr style="display: block; border-style: inset; border-width: 3px;" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell smallText">ISSUED BY:</div>
                                                    <div class="rTableCell smallText">RECEIVED BY:</div>
                                                    <div class="rTableCell smallText">DATE:</div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell smallText">'. $incharge .'</div>
                                                    <div class="rTableCell smallText">_______________</div>
                                                    <div class="rTableCell smallText">_______________</div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                    <div class="rTableCell-half rBorder-2">
                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell mediumText">PRODUCTION COPY</div>
                                                    <div class="rTableCell mediumText right">CTRL No.: '. $issuanceno .'</div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell mediumText">(ASSY100)</div>
                                                    <div class="rTableCell"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell center largeText"><strong>SAKIDASHI RETURN SLIP</strong></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell right mediumText"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">ITEM CODE</div>
                                                                    <div class="rTableCell-bordered width-70"><strong class="smallText">'. $item .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">CONTACT TYPE</div>
                                                                    <div class="rTableCell-bordered width-70"><strong class="smallText">'. $itemdesc .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">LOT # / PAIR #</div>
                                                                    <div class="rTableCell-bordered width-70">
                                                                    	<div class="rTable">
                                                                            <div class="rTableBody">
                                                                                <div class="rTableRow">
                                                                                    <div class="rTableCell"><strong class="smallText">'. $lotno .'</strong></div>
                                                                                    <div class="rTableCell"><strong class="smallText">'. $pairno .'</strong></div>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">PO #</div>
                                                                    <div class="rTableCell-bordered width-70"><strong class="smallText">'. $pono .'</strong></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-30 smallText">Device Name</div>
                                                                    <div class="rTableCell-bordered width-70"><strong class="smallText">'. $devicename .'</strong></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-35 smallText">REQ ISSUANCE QT</div>
                                                                    <div class="rTableCell-bordered width-35 smallText">REEL QTY</div>
                                                                    <div class="rTableCell-bordered width-30 smallText">ACTUAL CUT</div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-35 right"><strong class="smallText">'. $requiredqty .'</strong></div>
                                                                    <div class="rTableCell-bordered width-35 right"><strong class="smallText">'. $issuedqty .'</strong></div>
                                                                    <div class="rTableCell-bordered width-30 right"><strong class="smallText"></strong></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-50 smallText">NEEDED QTY FOR RETURN</div>
                                                                    <div class="rTableCell-bordered width-50 smallText">'. $returnqty .'</div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-50 smallText">ACTUAL QTY FOR RETURN</div>
                                                                    <div class="rTableCell-bordered width-50 smallText"></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-50 smallText">DATE FOR RETURN</div>
                                                                    <div class="rTableCell-bordered width-50"><em><strong class="smallText" style="text-align: center;">'. $schedreturndate .'</strong></em></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable smallText rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                	<div class="rTableCell width-30"><span>TYPE OF RETURN</span></div>
                                                                	<div class="rTableCell width-20">
                                                                        <span>REEL    </span><input name="reel" type="checkbox" value="reel" />
                                                                    </div>
                                                                    <div class="rTableCell width-20">
                                                                        <span>STRIPS    </span><input name="reel" type="checkbox" value="strips" />
                                                                    </div>
                                                                    <div class="rTableCell width-20">
                                                                        <span>CUT    </span><input name="reel" type="checkbox" value="cut" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable smallestText rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell">REASON OF DIFFERENCE</div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell right">REASON CODE:</div>
                                                                    <div class="rTableCell">A.) USE FOR REWORK</div>
                                                                    <div class="rTableCell">C.) DISCREPANCY</div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell"></div>
                                                                    <div class="rTableCell">B.) MATERIAL NG</div>
                                                                    <div class="rTableCell">D.) PRODUCTION NG</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rTableRow">
                                                    <div class="rTableCell">
                                                        <div class="rTable rBorder-1">
                                                            <div class="rTableBody">
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-20" style="font-size:11px">CODE</div>
                                                                    <div class="rTableCell-bordered width-20" style="font-size:11px">QTY</div>
                                                                    <div class="rTableCell-bordered width-20" style="font-size:11px">CODE</div>
                                                                    <div class="rTableCell-bordered width-20" style="font-size:11px">QTY</div>
                                                                    <div class="rTableCell-bordered width-20" style="font-size:11px">TOTAL DIFF</div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"><p style="color: white;">.</p></div>
                                                                </div>
                                                                <div class="rTableRow">
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"></div>
                                                                    <div class="rTableCell-bordered width-20" style="padding:0px"><p style="color: white;">.</p></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rTable">
                                            <div class="rTableBody">
                                                <div class="rTableRow">
                                                    <div class="rTableCell mediumText">RETURNED BY:</div>
                                                    <div class="rTableCell mediumText">____________</div>
                                                    <div class="rTableCell mediumText">DATE:</div>
                                                    <div class="rTableCell mediumText">____________</div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </body>

                    </html> ';
        // echo $html;

        # gather all html parts.
        $html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $html1;

        # apply snappy pdf wrapper
        $pdf = App::make('snappy.pdf.wrapper');
        # transform html to pdf format.
        $pdf->loadHTML($html)
            ->setPaper('A4')
            ->setOption('margin-top', 6)
            ->setOption('margin-right', 3)
            ->setOption('margin-left', 3)
            ->setOption('margin-bottom', 3)
            ->setOrientation('portrait');
        # display PDF report to response.
        return $pdf->inline();
    }

    public function printSiReport(Request $req)
    {
        $id = trim($req->id);
        $cur_id = '';
        $issuance_no = '';
        $max_id = '';
        $max_id = '';

        $dt = Carbon::now();
        $date = substr($dt->format('  M j, Y A'), 2);

        $sakidashi = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as S')
                        ->join(DB::raw( "(SELECT issuance_no, item, item_desc, lot_no, issued_qty, pair_no,
                                                required_qty, return_qty, sched_return_date, updated_at
                                    FROM tbl_wbs_sakidashi_issuance_item
                                    GROUP BY issuance_no, item, item_desc
                                    ORDER BY issuance_no, updated_at DESC) as SI"), 'SI.issuance_no', '=', 'S.issuance_no')
                        ->select( 'S.issuance_no'
                            , 'S.po_no'
                            , 'S.device_code'
                            , 'S.device_name'
                            , 'S.incharge'
                            , 'SI.item'
                            , 'SI.item_desc'
                            , 'SI.lot_no'
                            , 'SI.pair_no'
                            , DB::RAW('FORMAT(SI.issued_qty, 0) AS issued_qty')
                            , DB::RAW('FORMAT(SI.required_qty, 0) AS required_qty')
                            , DB::RAW('FORMAT(SI.return_qty, 0) AS return_qty')
                            , DB::raw("(CASE SI.sched_return_date
                                WHEN '0000-00-00 00:00' THEN NULL
                                ELSE DATE_FORMAT(SI.sched_return_date, '%M %d, %Y')
                               END) AS sched_return_date")
                            , 'S.status'
                            , DB::raw("(CASE SI.updated_at
                                WHEN '0000-00-00 00:00' THEN NULL
                                ELSE DATE_FORMAT(SI.updated_at, '%Y/%m/%d')
                               END) AS updated_at")
                            , DB::raw("IF(S.received_date = '0000-00-00 00:00:00','',DATE_FORMAT(S.received_date, '%Y-%m-%d')) as received_date")
                            , 'S.received_by'
                               )
                        ->where('S.id', '=', $id)
                        ->first();

        if($this->com->checkIfExistObject($sakidashi) > 0)
        {
            $issuanceno      = $sakidashi->issuance_no;
            $issuancedate    = Carbon::createFromFormat('Y/m/d',$sakidashi->updated_at)->format('mdy');
            $item            = $sakidashi->item;
            $itemdesc        = $sakidashi->item_desc;
            $lotno           = $sakidashi->lot_no;
            $pairno          = $sakidashi->pair_no;
            $pono            = $sakidashi->po_no;
            $devicecode      = $sakidashi->device_code;
            $devicename      = $sakidashi->device_name;
            $issuedqty       = $sakidashi->issued_qty;
            $requiredqty     = $sakidashi->required_qty;
            $returnqty       = $sakidashi->return_qty;
            $schedreturndate = $sakidashi->sched_return_date;
            $incharge        = preg_replace('/[^A-Za-z\-]/s', '', $sakidashi->incharge);
            $receivedby = $sakidashi->received_by;
            $receiveddate = $sakidashi->received_date;
                /*$si_details_data = DB::connection($this->mysql)->table()
                            ->join()
                            ->join()
                            ->leftJoin()
                            ->whereRaw()
                            ->select()
                            ->orderBy()
                            ->get();*/
            if(!empty($receiveddate)){
                $receiveddate = Carbon::createFromFormat('Y-m-d', $receiveddate)->format('mdy');
            }
        }
        else
        {
            $issuanceno      = '';
            $issuancedate    = '';
            $item            = '';
            $itemdesc        = '';
            $lotno           = '';
            $pairno          = '';
            $pono            = '';
            $devicecode      = '';
            $devicename      = '';
            $issuedqty       = '';
            $requiredqty     = '';
            $returnqty       = '';
            $schedreturndate = '';
            $incharge        = '';
            $receivedby = '';
            $receiveddate = '';
        }

        $data = [
            'issuanceno' => $issuanceno,
            'item' => $item,
            'itemdesc' => $itemdesc,
            'lotno' => $lotno,
            'pairno' => $pairno,
            'pono' => $pono,
            'requiredqty' => $requiredqty,
            'issuedqty' => $issuedqty,
            'returnqty' => $returnqty,
            'schedreturndate' => $schedreturndate,
            'issuancedate' => $issuancedate,
            'devicecode' => $devicecode,
            'devicename' => $devicename,
            'receivedby' => $receivedby,
            'receiveddate' => $receiveddate,
            'incharge' => $incharge,
        ];
        $pdf = App::make('snappy.pdf.wrapper');
        $pdf = PDF::loadView('wbs.sakidashiissuance-pdf', $data)
         ->setPaper('A4')
         ->setOption('margin-top', 2)
         ->setOption('margin-left', 2)
         ->setOption('margin-right', 2)
         ->setOption('margin-bottom', 2)
         ->setOrientation('portrait');
        return $pdf->inline('Material_Issuance_Sheet_.pdf');
    }

    public function sakiExportToExcel(Request $array)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('Ymd'), 2);

        Excel::create('WBS_Sakidashi_Issuance'.$date, function($excel) use($array)
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
                $sheet->cell('L1',"REMARKS");
                $sheet->cell('M1',"PAIRNO");

                $issuanceno = $array->issuanceno;
                $partcode = $array->partcode;

                 $field = DB::connection($this->mysql)->table('tbl_wbs_sakidashi_issuance as i')
                            ->join('tbl_wbs_sakidashi_issuance_item as d','i.issuance_no','=','d.issuance_no')
                            ->select('i.issuance_no as issuance_no',
                                    'd.issued_qty as issued_qty',
                                    'd.required_qty as req_qty',
                                    'd.lot_no as lot_no',
                                    'i.remarks as remarks',
                                    'i.po_no as po_no',
                                    'd.item as item',
                                    'd.created_at as created_at',
                                    'd.item_desc as item_desc',
                                    'd.pair_no as pair_no')
                            ->where('i.issuance_no',$issuanceno)
                            ->where('d.item',$partcode)
                            ->get();

                $row = 2;
                foreach ($field as $key => $val) {
                    $sheet->cell('A'.$row,"");
                    $sheet->cell('B'.$row,$val->item);
                    $sheet->cell('C'.$row,"WHS100");
                    $sheet->cell('D'.$row,"ASSY100");
                    $sheet->cell('E'.$row, $val->po_no);
                    $sheet->cell('F'.$row, 1);
                    $sheet->cell('G'.$row, $val->issued_qty);
                    $sheet->cell('H'.$row, $val->lot_no);
                    $sheet->cell('I'.$row, $this->formatDate($val->created_at));
                    $sheet->cell('J'.$row, substr($val->issuance_no,3));
                    $sheet->cell('K'.$row, $val->item_desc);
                    $sheet->cell('L'.$row, $val->remarks);
                    $sheet->cell('M'.$row, $val->pair_no);
                    $row++;
                }
            });

        })->download('xls');
    }

    private function formatDate($time)
    {
        $old_date = date($time);
        $old_date_timestamp = strtotime($old_date);
        $new_date = date('Ymd', $old_date_timestamp);

        return $new_date;
    }

    public function getFifoTable(Request $req)
    {
        $data = DB::connection($this->mysql)->table(DB::raw('tbl_wbs_inventory, (SELECT @rownum := 0) i'))
                    ->whereRaw("qty > 0 AND iqc_status='1' AND for_kitting='1' AND item='".$req->code."'")
                    ->where('deleted',0)
                    ->orderBy('received_date','asc')
                    ->distinct()
                    ->select([DB::raw("@rownum:=@rownum+1 as rn"),
                            DB::raw('id as id'),
                            DB::raw('item as item'),
                            DB::raw('item_desc as item_desc'),
                            DB::raw('qty as received_qty'),
                            DB::raw('qty as qty'),
                            DB::raw("IFNULL(lot_no,'') as lot_no"),
                            DB::raw('location as location'),
                            DB::raw('received_date as received_date')
                            //DB::raw("DATE_FORMAT(received_date, '%m/%d/%Y') as received_date")
                        ]);
        if (count((array)$data) < 1) {
            $data = DB::connection($this->mysql)->table(DB::raw('tbl_wbs_inventory, (SELECT @rownum := 0) i'))
                        ->whereRaw("qty > 0 AND iqc_status='1' AND for_kitting='1' AND item='".$req->code."'")
                        ->where('deleted',0)
                        ->orderBy('received_date','asc')
                        ->distinct()
                        ->select([DB::raw("@rownum:=@rownum+1 as rn"),
                            DB::raw('id as id'),
                            DB::raw('item as item'),
                            DB::raw('item_desc as item_desc'),
                            DB::raw('qty as received_qty'),
                            DB::raw('qty as qty'),
                            DB::raw("IFNULL(lot_no,'') as lot_no"),
                            DB::raw('location as location'),
                            DB::raw('received_date as received_date')
                            //DB::raw("DATE_FORMAT(received_date, '%m/%d/%Y') as received_date")
                        ]);
        }

        return Datatables::of($data)
                            ->addColumn('action', function($data) {
                                return '<a href="javascript:;" class="btn btn-primary btn_select_lot btn-sm" data-id="'.$data->id.'" data-item="'.$data->item.'" data-item_desc="'.$data->item_desc.'" data-rowcount="'.$data->rn.'" data-lotno="'.$data->lot_no.'" data-qty="'.$data->qty.'">
                                    <i class="fa fa-pencil"></i>
                                </a>';
                            })
                            ->make(true);
    }

    public function checkInPO(Request $req)
    {
        $details = DB::connection($this->mssql)
                    ->select("SELECT hk.CODE as item
                                FROM XSLIP s
                                LEFT JOIN XHIKI hk ON s.PORDER = hk.PORDER
                                LEFT JOIN XITEM i ON i.CODE = hk.CODE
                                LEFT JOIN XHEAD h ON h.CODE = hk.CODE
                                LEFT JOIN (SELECT z.CODE, 
                                            ISNULL(z1.ZAIK,0) as WHS100, 
                                            ISNULL(z2.ZAIK,0) as WHS102, 
                                            z.RACKNO FROM XZAIK z
                                            LEFT JOIN XZAIK z1 ON z1.CODE = z.CODE AND z1.HOKAN = z.HOKAN
                                            LEFT JOIN XZAIK z2 ON z2.CODE = z.CODE AND z2.HOKAN = z.HOKAN
                                            WHERE z.RACKNO <> ''
                                            GROUP BY z.CODE, z1.ZAIK, z2.ZAIK, z.RACKNO
                                        ) x ON x.CODE = hk.CODE
                                        WHERE s.SEIBAN = '".$req->po."' AND hk.CODE = '".$req->item."'
                                        GROUP BY hk.CODE, 
                                                h.NAME, 
                                                i.VENDOR, 
                                                hk.KVOL, 
                                                i.DRAWING_NUM, 
                                                x.WHS100, 
                                                x.WHS102, 
                                                x.RACKNO");
                    
        return json_encode($details);
    }

    public function checkInFIFO(Request $req)
    {
        $data =  DB::connection($this->mysql)->table('tbl_wbs_inventory')
                    ->where('deleted',0)
                    ->where('item',$req->item)
                    ->where('lot_no',$req->lot)
                    ->where('qty','>',0)
                    ->where('for_kitting',1)
                    ->count();
        return json_encode($data);
    }

    public function BrCodePrint_honeywell(Request $req)
    {
        $data = DB::connection($this->mysql)
                    ->table('tbl_wbs_sakidashi_issuance_item as d')
                    ->join('tbl_wbs_sakidashi_issuance as i','d.issuance_no','=','i.issuance_no')
                    ->where('d.id',$req->id)
                    ->first();

        $path = storage_path().'/brcodesakidashi';
                        if (!File::exists($path)) {
                            File::makeDirectory($path, 0777, true, true);
                        }
        $filename = $data->issuance_no.'_'.$data->po_no.'_'.$data->item.'.prn';

        $brIssuance = explode('-', $data->issuance_no);

        $content = 'CLIP ON'."\r\n";
        $content .= 'CLIP BARCODE ON'."\r\n";
        $content .= 'DIR2'."\r\n";
        $content .= 'PP310,766:AN7'."\r\n";
        $content .= 'DIR2'."\r\n";
        $content .= 'FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 10'."\r\n";
        $content .= 'PP60,776:FT "Swiss 721 Bold BT",20,0,78'."\r\n";
        $content .= 'PP290,540:BARSET "CODE128",2,1,3,51'."\r\n";
        $content .= 'PB "'.$data->po_no.'"'."\r\n";
        $content .= 'PP240,460:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 8'."\r\n";
        $content .= 'PT "'.$data->po_no.'"'."\r\n";
        $content .= 'PP290,120:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "PAIR # '.$data->pair_no.'"'."\r\n";
        $content .= 'PP260,120:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "ACTUAL RETURN"'."\r\n";
        $content .= 'PP210,540:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "ISSUED QTY."'."\r\n";
        $content .= 'PP210,450:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "'.$data->issued_qty.''."\r\n";
        $content .= 'PP210,360:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "REQUIRED QTY"'."\r\n";
        $content .= 'PP210,250:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "'.$data->required_qty.'"'."\r\n";
        $content .= 'PP210,190:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "FOR RETURN"'."\r\n";
        $content .= 'PP210,80:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 5'."\r\n";
        $content .= 'PT "'.$data->return_qty.'"'."\r\n";
        $content .= 'PP210,560:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 6'."\r\n";
        // $content .= 'PT "CTRL #"'."\r\n";
        $content .= 'PP175,540:BARSET "CODE128",2,1,3,30'."\r\n";
        $content .= 'PB "'.$data->issuance_no.'"'."\r\n";
        $content .= 'PP145,380:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 6'."\r\n";
        $content .= 'PT "'.$data->issuance_no.'"'."\r\n";
        $content .= 'PP145,540:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 6'."\r\n";
        $content .= 'PT "Lot No:"'."\r\n";
        $content .= 'PP125,540:BARSET "CODE128",2,1,3,30'."\r\n";
        $content .= 'PB "'.$data->lot_no.'"'."\r\n";
        $content .= 'PP95,380:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 7'."\r\n";
        $content .= 'PT "'.$data->lot_no.'"'."\r\n";
        $content .= 'PP80,380:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 7'."\r\n";
        $content .= 'PT ""'."\r\n";
        $content .= 'PP63,540:BARSET "CODE128",2,1,3,30'."\r\n";
        $content .= 'PB "'.$data->item.'"'."\r\n";
        $content .= 'PP30,380:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 7'."\r\n";
        $content .= 'PT "'.$data->item.' / '.$data->item_desc.'"'."\r\n";
        $content .= 'PP60,190:FT "Swiss 721 BT"'."\r\n";

        $content .= 'FONTSIZE 4'."\r\n";
        $content .= 'PT "PREPARED BY: '.$data->incharge.'"'."\r\n";
        $content .= 'PP150,779:AN7'."\r\n";
        $content .= 'PF'."\r\n";


        $myfile = fopen($path."/".$filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);

        $headers = [
                        'Content-type'=>'text/plain',
                        'Content-Disposition'=>sprintf('attachment; filename="%s"', $filename)
                    ];

        return \Response::download($path.'/'.$filename, $filename, $headers);
    }
    public function BrCodePrint(Request $req)
    {
        $data = DB::connection($this->mysql)
                    ->table('tbl_wbs_sakidashi_issuance_item as d')
                    ->join('tbl_wbs_sakidashi_issuance as i','d.issuance_no','=','i.issuance_no')
                    ->where('d.id',$req->id)
                    ->first();

        $path = storage_path().'/brcodesakidashi';
                        if (!File::exists($path)) {
                            File::makeDirectory($path, 0777, true, true);
                        }
        $filename = $data->issuance_no.'_'.$data->po_no.'_'.$data->item.'.prn';

        
        $content = '^XA'."\r\n"; 
        $content .= '^FO60,776^A0N,40,40^FDPO #: '.$data->po_no.'^FS'."\r\n"; 
        $content .= '^FO290,540^BCN,100,Y,N,N^FD'.$data->po_no.'^FS'."\r\n"; 
        $content .= '^FO240,460^A0N,30,30^FDPAIR # '.$data->pair_no.'^FS'."\r\n"; 

        $content .= '^FO210,450^A0N,30,30^FDISSUED QTY: '.$data->issued_qty.'^FS'."\r\n"; 
        $content .= '^FO210,360^A0N,30,30^FDREQUIRED QTY: '.$data->required_qty.'^FS'."\r\n"; 
        $content .= '^FO210,250^A0N,30,30^FDFOR RETURN: '.$data->return_qty.'^FS'."\r\n"; 

        $content .= '^FO175,540^BCN,60,Y,N,N^FD'.$data->issuance_no.'^FS'."\r\n"; 
        $content .= '^FO145,380^A0N,30,30^FDCTRL #: '.$data->issuance_no.'^FS'."\r\n";

        $content .= '^FO125,540^BCN,60,Y,N,N^FD'.$data->lot_no.'^FS'."\r\n"; 
        $content .= '^FO95,380^A0N,30,30^FDLot No: '.$data->lot_no.'^FS'."\r\n"; 

        $content .= '^FO63,540^BCN,60,Y,N,N^FD'.$data->item.'^FS'."\r\n"; 
        $content .= '^FO30,380^A0N,30,30^FDItem: '.$data->item.' / '.$data->item_desc.'^FS'."\r\n"; 
        $content .= '^FO150,779^A0N,30,30^FDPrepared by: '.$data->incharge.'^FS'."\r\n"; 
        $content .= '^XZ'."\r\n"; 

        $myfile = fopen($path."/".$filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);

        $headers = [
            'Content-type'=>'text/plain',
            'Content-Disposition'=>sprintf('attachment; filename="%s"', $filename)
        ];

        return \Response::download($path.'/'.$filename, $filename, $headers);
    }


    public function fifoReason(Request $req)
    {
        $data = [
                'return_status' => 'failed',
            ];
        $insert = DB::connection($this->mysql)->table('tbl_wbs_fiforeason')
                    ->insert([
                        'item' => $req->item,
                        'lotno' => $req->lotno,
                        'issuanceno' => $req->issuanceno,
                        'reason' => $req->reason,
                        'created_at' => Carbon::now()
                    ]);
        if ($insert) {
            $data = [
                'return_status' => 'success',
            ];
        }

        return $data;
    }

    public function fifoReasonExcel(Request $req)
    {
        $dt = Carbon::now();
        $date = substr($dt->format('Ymd'), 2);
        $issuanceno = $req->issuanceno;

        Excel::create('FIFO_REASONS_'.$req->issuanceno.'_'.$date, function($excel) use($issuanceno)
        {
            $excel->sheet('Sheet1', function($sheet) use($issuanceno)
            {
                $sheet->cell('A1', "ISSUANCE NO");
                $sheet->cell('B1', "ITEM");
                $sheet->cell('C1', "LOTNO");
                $sheet->cell('D1', "REASON");
                $sheet->cell('E1', "DATE");

                $row = 2;
                $data = DB::connection($this->mysql)->table('tbl_wbs_fiforeason')
                            ->where('issuanceno',$issuanceno)
                            ->get();

                foreach ($data as $key => $mk) {
                    $sheet->cell('A'.$row, $mk->issuanceno);
                    $sheet->cell('B'.$row, $mk->item);
                    $sheet->cell('C'.$row, $mk->lotno);
                    $sheet->cell('D'.$row, $mk->reason);
                    $sheet->cell('E'.$row, $mk->created_at);
                    $row++;
                }
            });

        })->download('xls');
    }
}
