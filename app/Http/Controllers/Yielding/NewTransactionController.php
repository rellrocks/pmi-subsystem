<?php

namespace App\Http\Controllers\Yielding;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Config;
use DB;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class NewTransactionController extends Controller
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

    public function index()
    {
    	if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_NEWTRAN'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
        	return view('yielding.new_transaction',['userProgramAccess' => $userProgramAccess]);
        }
    }

    public function po_details(Request $req)
    {
    	$data = [
    		'msg' => 'No P.O. number found.',
    		'status' => 'failed',
    		'yield_performance' => '',
    		'yield_performance_details' => ''
    	];

    	$yield_performance = DB::connection($this->mysql)
    							->table('yield_performance')
    							->where('po',$req->po)
    							->get();

    	if (count((array)$yield_performance) > 0) {
    		$yield_performance_details = DB::connection($this->mysql)
    										->table('yield_performance_details')
    										->where('po',$req->po)
    										->get();

    		$data = [
	    		'msg' => 'P.O. has already old transactions',
	    		'status' => 'success',
	    		'yield_performance' => $yield_performance,
	    		'yield_performance_details' => $yield_performance_details
	    	];
    	} else {
    		$yield_performance = DB::connection($this->mysql)
	    							->table('tbl_poregistration')
	                                ->where('pono',$req->po)
	                                ->select(DB::raw('pono as po'),
	                                        DB::raw('device_name as device'),
	                                        DB::raw('poqty as po_qty'),
	                                        DB::raw('family as family'),
	                                        DB::raw('series as series'),
	                                        DB::raw('prod_type as prod_type'))
	                                ->first();
	        if (count((array)$yield_performance) > 0) {
	        	$data = [
		    		'msg' => 'P.O. is registered in P.O. Registration',
		    		'status' => 'success',
		    		'yield_performance' => $yield_performance,
		    		'yield_performance_details' => ''
		    	];
	        } else {
	        	$yield_performance = DB::connection($this->mssql)
	        							->table('XRECE as r')
	        							->leftJoin('XITEM as i','i.CODE','=','r.CODE')
	        							->leftJoin('XHEAD as h','h.CODE','=','r.CODE')
		                                ->select(
		                                		DB::raw("r.SORDER as po"),
		                                		DB::raw("r.CODE as device_code"),
		                                		DB::raw("h.NAME as device"),
		                                		DB::raw("r.KVOL as po_qty"), 
		                                		DB::raw("SUBSTRING(h.NAME, 1, CHARINDEX('-',h.NAME) - 1) as series"),
		                                		DB::raw("UPPER(i.BUNR) as prod_type"),
		                                		DB::raw("h.NOTE as family")
		                                )
		                                ->whereIn('i.BUNR',['Burn-In','Test Sockets'])
		                                ->where('r.SORDER',$req->po)
		                                ->groupBy('r.SORDER','r.CODE','h.NAME','r.KVOL','i.BUNR','h.NOTE')
		                                ->first();

		        if (count((array)$yield_performance) > 0) {
		        	$data = [
			    		'msg' => 'P.O. is found in YPICS',
			    		'status' => 'success',
			    		'yield_performance' => $yield_performance,
			    		'yield_performance_details' => ''
			    	];
		        } else {
		        	$data = [
			    		'msg' => 'No P.O. number found in all databases.',
			    		'status' => 'failed',
			    		'yield_performance' => '',
			    		'yield_performance_details' => ''
			    	];
		        }
	        }
    	}

    	return response()->json($data);
    }

    public function dropdowns()
    {
    	$mode_of_defect = $this->com->getDropdownByName('Mode of Defect - Yield Performance');
        $family = $this->com->getDropdownByName('Family');
        $series = $this->com->getDropdownByName('Series');
        $yielding_station = $this->com->getDropdownByName('Yielding Station');

        $data = [
        	'mode_of_defect' => $mode_of_defect,
        	'family' => $family,
        	'series' => $series,
        	'yielding_station' => $yielding_station
        ];

        return response()->json($data);
    }
}
