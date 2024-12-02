<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Auth; #Auth facade
use Dompdf\Dompdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use Excel;
use PDF;
use DB;
use Config;

class NGRMasterController extends Controller
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

    public function index(Request $request)
    {
        if(!$this->com->getAccessRights(Config::get('constants.MODULE_MGR_MASTER'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {

            return view('master.ngr_master',[
                        'userProgramAccess' => $userProgramAccess]);
        }
    }

    public function save(Request $req)
    {
        $data = [
            'msg' => 'Saving of description was failed.',
            'status' => 'failed'
        ];

        if (is_null($req->id) || $req->id == "") {
            // insert data
            $query = DB::connection($this->mysql)
                        ->table('iqc_ngr_master')
                        ->insert([
                            'description' => $req->description,
                            'category' => $req->category,
                            'create_user' => Auth::user()->user_id,
                            'update_user' => Auth::user()->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

            if ($query) {
                $data = [
                    'msg' => 'Description was successfuly saved.',
                    'status' => 'success'
                ];
            }
        } else {
            // update
            $query = DB::connection($this->mysql)
                        ->table('iqc_ngr_master')
                        ->where('id', $req->id)
                        ->update([
                            'description' => $req->description,
                            'update_user' => Auth::user()->user_id,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

            if ($query) {
                $data = [
                    'msg' => 'Description was successfuly saved.',
                    'status' => 'success'
                ];
            }
        }

        return $data;
    }

    public function delete(Request $req)
    {
        $data = [
            'msg' => 'Deleting of description was failed.',
            'status' => 'failed'
        ];

        $query = DB::connection($this->mysql)
                    ->table('iqc_ngr_master')
                    ->whereIn('id', $req->IDs)
                    ->delete();

        if ($query) {
            $data = [
                'msg' => 'Description was successfuly deleted.',
                'status' => 'success'
            ];
        }

        return $data;
    }

    public function get_list(Request $req)
    {
        $draw = $req->get('draw');
        $start = $req->get("start");
        $rowperpage = $req->get("length"); // Rows display per page

        $columnIndex_arr = $req->get('order');
        $columnName_arr = $req->get('columns');
        $order_arr = $req->get('order');
        $search_arr = $req->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = DB::connection($this->mysql)
                            ->table('iqc_ngr_master')
                            ->where('category', $req->category)
                            ->count();

        $totalRecordswithFilter = DB::connection($this->mysql)
                                    ->table('iqc_ngr_master')
                                    ->where('category', $req->category)
                                    ->where('description', 'like', '%' .$searchValue . '%')->count();

        $query = DB::connection($this->mysql)
                    ->table('iqc_ngr_master')
                    ->where('category', $req->category)
                    ->where('description', 'like', '%' .$searchValue . '%')
                    ->orderBy($columnName,$columnSortOrder)
                    ->skip($start)
                    ->take($rowperpage)
                    ->get();

        $response = array(
            "draw" => (int)$draw,
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $query
        );
        return json_encode($response);
    }
}
