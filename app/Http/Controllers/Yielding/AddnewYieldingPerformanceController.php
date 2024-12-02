<?php
namespace App\Http\Controllers\Yielding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth; #Auth facade

class AddnewYieldingPerformanceController extends Controller
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


    public function getYieldPerformance(Request $request)
    {
        if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_NEWTRAN'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        { 
        
            $msrecords = DB::connection($this->mssql)
                // ->table('XHIKI as d')
                // ->join('XHEAD as h','d.OYACODE','=','h.CODE')
                // ->select(DB::raw("d.SEIBAN as PO")
                //     , DB::raw("d.OYACODE as devicecode")
                //     , DB::raw("h.NAME as devicename")
                //     , DB::raw("SUM(d.KVOL) as POqty"))
                // ->where('d.SEIBAN',$request->pono)
                // ->groupBy('d.SEIBAN','d.OYACODE','h.NAME')
                // ->get();

                ->table('XSLIP as s')
                ->leftJoin('XHEAD as h', 's.CODE', '=', 'h.CODE')
                ->leftjoin('XRECE as r', 's.SEIBAN','=','r.SORDER')
                ->select(DB::raw('s.SEIBAN as PO'),
                                DB::raw('s.CODE as devicecode'),
                                DB::raw('h.NAME as devicename'),
                                DB::raw('r.KVOL as POqty'),
                                DB::raw('r.SEDA as branch'))
                ->where('s.SEIBAN',$request->pono)
                ->orderBy('r.SEDA','desc')
                // ->first()
                ->get();

            $datenow = date('Y/m/d');
            $count = DB::connection($this->mysql)->table("tbl_yielding_performance")->first();

            $countpya = DB::connection($this->mysql)->table("tbl_yielding_performance")->count(); 
            $countcmq = DB::connection($this->mysql)->table("tbl_yielding_performance")->count(); 
            //$family = DB::connection($this->mysql)->table("tbl_seriesregistration")->select('family')->distinct()->get();
            $modefect = $this->com->getDropdownByName('Mode of Defect - Yield Performance');
            $family = $this->com->getDropdownByName('Family');
            $series = $this->com->getDropdownByName('Series');
            //$devreg = DB::connection($this->mysql)->table('tbl_deviceregistration')->get();
            $ys = $this->com->getDropdownByName('Yielding Station');

            return view('yielding.AddnewYieldingPerformance',['userProgramAccess' => $userProgramAccess,'family' => $family,'modefect' => $modefect,'yieldstation' => $ys,'yieldingno'=>$count,'series'=>$series, 'msrecords'=>$msrecords, 'count'=>$count,'countpya'=> $countpya,'countcmq'=> $countcmq]); 
        }
    }

    public function checkdetails(Request $request)
    {
        $pono = $request->pono;
        $tablecount = DB::connection($this->mysql)->table('tbl_yielding_performance')->where('pono','=',$pono)->count();

        return $tablecount;
    }

    //displaying the records for production date,yielding station and accumulated output
    private function displaypya($pono)
    {
        $table = "tbl_yielding_performance";
        $dataexist = DB::connection($this->mysql)->table($table)
                    ->where('pono','=',$pono)
                    ->select('id','yieldingno','productiondate','yieldingstation','accumulatedoutput','remarks')
                    ->groupBy('yieldingno')
                    ->orderBY('productiondate','DESC')
                    ->get();

        return $dataexist; 
    }

    //display records for classification, mod and quantity
    private function displaycmq($pono)
    { 
        $table = "tbl_yielding_performance";  
        $dataexist = DB::connection($this->mysql)->table($table)
                    ->where('pono','=',$pono)
                    ->select('id','pono','classification','mod','qty','remarks')
                    ->orderBY('productiondate','DESC')
                    ->get();
        return $dataexist; 
    }

    public function searchdisplaypya(Request $request)
    {
        $table = "tbl_yielding_performance";
        $pono = $request->pono;  
        $dataexist = DB::connection($this->mysql)->table($table)
                    ->where('pono','=',$pono)
                    ->select('id','yieldingno','pono','productiondate','yieldingstation','accumulatedoutput','remarks')
                    ->groupBy('yieldingno')
                    ->orderBY('productiondate','DESC')
                    ->get();
                    
       return $dataexist;
    }

    public function getautovalue(Request $request)
    {
        $pono = $request->pono;
        $table = DB::connection($this->mysql)->table('tbl_yielding_performance as y')
                        ->join('tbl_yielding_pya as p','y.pono','=','p.pono')
                        ->join('tbl_yielding_cmq as c','y.pono','=','c.pono')
                        ->select(DB::raw("SUM(distinct(p.accumulatedoutput)) AS toutput"),DB::raw("SUM(distinct(c.qty)) AS treject"))
                        ->where('y.pono','=',$pono)
                        ->groupBy('y.pono')
                        ->orderBy('y.id','DESC')
                        ->get();
        return $table;
    }

    public function getpng(Request $request)
    {
        $pono = $request->pono;
        $table = DB::connection($this->mysql)->table('tbl_yielding_performance')
                        ->select(DB::raw("SUM(tpng) AS tpng"))
                        ->where('pono','=',$pono)
                        ->where('classification','=','Production NG (PNG)')
                        ->get();
        return $table;
    }

    public function getmng(Request $request)
    {
        $pono = $request->pono;
        $table = DB::connection($this->mysql)->table('tbl_yielding_performance')
                        ->select(DB::raw("SUM(tmng) AS tmng"))
                        ->where('pono','=',$pono)
                        ->where('classification','=','Material NG (MNG)')
                        ->get();
        return $table;
    }

    public function searchdisplaycmq(Request $request)
    {
        $table = "tbl_yielding_performance";
        $pono = $request->pono;
        $dataexist = DB::connection($this->mysql)->table($table)
                    ->where('pono','=',$pono)
                    ->select('id','pono','yieldingno','classification','mod','qty','remarks')
                    ->orderBY('productiondate','DESC')
                    ->get();

        return $dataexist;
    }

    public function searchdisplaydetails(Request $request)
    {
        $table = "tbl_yielding_performance";
        $pono = $request->pono;
        $dataexist = DB::connection($this->mysql)->table($table)
                    ->where('pono','=',$pono)
                    ->orderBy('productiondate','DESC')
                    ->get();

        return $dataexist; 
    }

    public function searchdisplaysummary(Request $request)
    {
        $table = "tbl_yielding_performance";
        $pono = $request->pono;
        $dataexist = DB::connection($this->mysql)->table($table)
                    ->where('pono','=',$pono)
                    ->orderBy('productiondate','DESC')
                    ->get();

        return $dataexist; 
    }

    public function addYieldperformance(Request $req)
    {
        if(isset($req->newyieldingstation)){

            $status = $req->status;
            $classification = $req->classification;
            $x = 0;
            if ($req->id !== '' || !empty($req->id)) {
                $updated = DB::connection($this->mysql)->table("tbl_yielding_performance")
                            ->where('id',$req->id)
                            ->update([
                                'pono' => $req->pono,
                                'poqty' => $req->poqty,
                                'device' => $req->device,
                                'family' => $req->family,
                                'series' => $req->series,
                                'prodtype' => $req->prodtype,
                                'tinput' => $req->tinput,
                                'toutput' => $req->toutput,
                                'treject' => $req->treject,
                                'tmng' => $req->tmng,
                                'tpng' => $req->tpng,
                                'ywomng' => $req->ywomng,
                                'twoyield' => $req->twoyield,
                                'create_user' => Auth::user()->user_id,
                                'update_user' => Auth::user()->user_id,
                                'updated_at' => date('Y-m-d h:i:s')
                            ]);

                DB::connection($this->mysql)->table('tbl_yielding_pya')
                    ->where('yield_id',$req->id)
                    ->delete();

                foreach ($req->newyieldingstation as $key => $rec) {
                    DB::connection($this->mysql)->table('tbl_yielding_pya')
                                ->insert([
                                    'yield_id' => $req->id,
                                    'pono' => $req->pono,
                                    'productiondate' => $req->newproductiondate[$key],
                                    'yieldingstation' => $rec,
                                    'accumulatedoutput' => $req->newaccumulatedoutput[$key],
                                    'mod' => $req->newmod[$key],
                                    'classification' => $req->newclassification[$key],
                                    'qty' => $req->newqty[$key],
                                    'remarks' => $req->remarks[$key],
                                    'update_user' => Auth::user()->user_id,
                                    'updated_at' => date('Y-m-d h:i:s')
                                ]);
                }
            }else {
                $inserted = DB::connection($this->mysql)->table("tbl_yielding_performance")
                                ->insert([
                                    'pono' => $req->pono,
                                    'poqty' => $req->poqty,
                                    'device' => $req->device,
                                    'family' => $req->family,
                                    'series' => $req->series,
                                    'prodtype' => $req->prodtype,
                                    'tinput' => $req->tinput,
                                    'toutput' => $req->toutput,
                                    'treject' => $req->treject,
                                    'tmng' => $req->tmng,
                                    'tpng' => $req->tpng,
                                    'ywomng' => $req->ywomng,
                                    'twoyield' => $req->twoyield,
                                    'create_user' => Auth::user()->user_id,
                                    'update_user' => Auth::user()->user_id,
                                    'created_at' => date('Y-m-d h:i:s'),
                                    'updated_at' => date('Y-m-d h:i:s')
                                ]);

                $last_insert = DB::connection($this->mysql)->table("tbl_yielding_performance")
                        ->select('id')
                        ->orderBy('id','desc')
                        ->first();

                // foreach ($req->newmod as $key => $rec) {
                //     DB::connection($this->mysql)->table('tbl_yielding_cmq')
                //         ->insert([
                //             'yield_id' => $id,
                //             'pono' => $req->pono,
                //             'productiondate' => $req->productiondate,
                //             'mod' => $rec,
                //             'classification' => $req->newclassification[$key],
                //             'qty' => $req->newqty[$key],
                //             'remarks' => $req->remarks[$key],
                //             'created_at' => date('Y-m-d h:i:s'),
                //             'updated_at' => date('Y-m-d h:i:s')
                //         ]);
                // }

                foreach ($req->newyieldingstation as $key => $rec) {
                    DB::connection($this->mysql)->table('tbl_yielding_pya')
                                ->insert([
                                    'yield_id' => $last_insert->id,
                                    'pono' => $req->pono,
                                    'productiondate' => $req->newproductiondate[$key],
                                    'yieldingstation' => $rec,
                                    'accumulatedoutput' => $req->newaccumulatedoutput[$key],
                                    'mod' => $req->newmod[$key],
                                    'classification' =>$req->newclassification[$key],
                                    'qty' => $req->newqty[$key],
                                    'remarks' => $req->remarks[$key],
                                       // 'create_user' => Auth::user()->user_id,
                                    'update_user' => Auth::user()->user_id,
                                    'created_at' => date('Y-m-d h:i:s'),
                                    'updated_at' => date('Y-m-d h:i:s')
                                ]);
                }
                    

            }
            return response()->json(['msg' => 'Successfully saved.','status' => 'success']);
        }
         return response()->json( ['msg' => "Insert some data on the table.",'status' => 'failed']);
    } 

    public function searchPO(Request $request)
    {    
        $search = $request->data;
        $find = $search['pono'];
        
        $countrow = DB::connection($this->mysql)->table('tbl_yielding_performance')
                    ->where('pono',$find)->count();
     
        $table = DB::connection($this->mysql)->table('tbl_deviceregistration as d')
                ->join('tbl_poregistration as p','d.pono','=','p.pono')
                ->where('d.pono',$find)
                ->select('d.pono as pono','d.devicename as devicename','d.family as family','d.series as series','d.ptype as ptype','p.poqty as poqty')
                ->get();    
      
        return $table;
    }

    public function search(Request $request)
    {    
        $search = $request->data;
        $find = $search['search'];
       /* return $find;*/
        
        $ok =DB::connection($this->mysql)->table('tbl_yielding_performance')
        ->where('pono', $find)
        ->get();

        return $ok;

        $dataexist = DB::connection($this->mysql)->table('tbl_yielding_performance')
        ->where('pono',$find)
        ->count();
        /*return $dataField['treject'];*/

        if($dataexist == 0){
            return $dataexist;
        }
    }  

    public function deleteAllPost(Request $request)
    {      
        $tray = $request->tray;
        $traycount = $request->traycount;

       /* return $tray;*/
        if($traycount > 0){
            $ok = DB::connection($this->mysql)->table('tbl_yielding_performance')->wherein('id',$tray)->delete();
        
            if ($ok) {
                $msg = "Successfully deleted selected records.";
                return redirect('/addnewYieldperformance')->with(['message'=>$msg]);
            } else {
                $msg = "No Record Exists.";
                return redirect('/addnewYieldperformance')->with(['err_message'=>$msg]);
            }
        }
    }

    public function deletecmq(Request $request)
    {      
        $tray = $request->tray;
        $traycount = $request->traycount;
        $yieldingno = $request->yieldingno;
        $count = DB::connection($this->mysql)->table('tbl_yielding_cmq')->where('yieldingno',$yieldingno)->count();
        
        if($traycount > 0){
            DB::connection($this->mysql)->table('tbl_yielding_performance')->wherein('id',$tray)->delete();
            DB::connection($this->mysql)->table('tbl_yielding_cmq')->wherein('id',$tray)->delete();
        }
        if($count == 1){
            DB::connection($this->mysql)->table('tbl_yielding_pya')->where('yieldingno',$yieldingno)->delete();
        }
        return $count;
    }

    public function deletepya(Request $request)
    {
        $tray = $request->tray;
        $traycount = $request->traycount;
        $yield_id = $request->id;
        $table = DB::connection($this->mysql)->table("tbl_yielding_pya")->select('yield_id')->orderBy('id','desc')->first();

        if($traycount > 0){
            DB::connection($this->mysql)->table('tbl_yielding_performance')->where('id',$yield_id)->delete();
            DB::connection($this->mysql)->table('tbl_yielding_pya')->where('yield_id',$yield_id)->delete();
        }
        return $table->yieldingno;
    }

    public function multiSearch(Request $request)
    {
        $mSearch = $request->data;
        $mSearchtype1 = $request->data['mSearchtype1'];
        $mSearchval1 = $request->data['mSearchval1'];
        $fixedtbl =DB::connection($this->mysql)->table("tbl_yielding_performance")->get();
        if($mSearchtype1 == 1){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('yieldingno')->get();
            $field = "yieldingno";
        }
        else if($mSearchtype1 == 2){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('pono')->get();
            $field = "pono";
        }
        else if($mSearchtype1 == 3){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('poqty')->get();
            $field = "poqty";
        }
        else if($mSearchtype1 == 4){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('device')->get();
            $field = "device";
        }
        else if($mSearchtype1 == 5){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('family')->get();
            $field = "family";
        }
        else if($mSearchtype1 == 6){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('series')->get();
            $field = "series";
        }
        else if($mSearchtype1 == 7){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('classification')->get();
            $field = "classification";
        }
        else if($mSearchtype1 == 8){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('mod')->get();
            $field = "mod";
        }
        else if($mSearchtype1 == 9){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('qty')->get();
            $field = "qty";
        }
        else if($mSearchtype1 == 10){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('productiondate')->get();
            $field = "productiondate";
        }
        else if($mSearchtype1 == 11){
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('yieldingstation')->get();
            $field = "yieldingstation";
        }
        else {
            $table =DB::connection($this->mysql)->table("tbl_yielding_performance")->select('accumulatedoutput')->get();
            $field = "accumulatedoutput";
        }
   

        return $table;
    }

    public function multiSearchDisplay(Request $request)
    {
        $search = $request->data;
        $columnfield = $search['mSearchtype1'];
        $columnvalue = $search['mSearchval1'];
      
        if($columnfield == 1){
            $field = 'yieldingno';
        }   else if($columnfield == 2){
            $field = 'pono';
        }   else if($columnfield == 3){
            $field = 'poqty';
        }    else if($columnfield == 4){
            $field = 'device';
        }    else if($columnfield == 5){
            $field = 'family';
        }    else if($columnfield == 6){
            $field = 'series';
        }    else if($columnfield == 7){
            $field = 'classification';
        }    else if($columnfield == 8){
            $field = 'mod';
        }    else if($columnfield == 9){
            $field = 'qty';
        }    else if($columnfield == 10){
            $field = 'productiondate';
        }    else if($columnfield == 11){
            $field = 'yieldingstation';
        }    else{
            $field = 'accumulatedoutput';
        }
       /* return $columnvalue;*/
        $ok = DB::connection($this->mysql)->table("tbl_yielding_performance")
            ->where($field, 'LIKE' ,$columnvalue)
            ->get();

        $count = DB::connection($this->mysql)->table("tbl_yielding_performance")
            ->where($field,$columnvalue)
            ->count();

        return $ok;
        /*if(count($ok)>0) {
            $msg = "Record/s found";
            return redirect('/yieldperformance2')->with(['message'=>$msg,'records'=>$ok]);
        } else {
            $msg = "No Record/s found.";
            return redirect('/yieldperformance2')->with(['err_message'=>$msg]);
        }*/
    }

    public function getsummarylist()
    {
        $data = Summary::all();
        return Datatables::of($data)->make(true);
    }

    public function devreg_get_series(Request $request)
    {
        $data = $request->data;
        $table = DB::connection($this->mysql)->table('tbl_dropdown_series')->select($data['family'])->get();
        return $table;
    }
    //  public function get_mod(Request $request){
    //     $prodtype = $request->prodtype;
    //     $table = DB::connection($this->mysql)->table('tbl_modregistration')->select('mod')->where('family',$prodtype)->get();
    //     return $table;
    // }

    //CER
    public function GetPONumberDetails(Request $req)
    {
        $podetails = '';
        $pya = '';
        $cmq = '';
        $save_details = DB::connection($this->mysql)
                            ->table('tbl_yielding_performance')
                            ->where('pono',$req->po)
                            ->select(
                                'id',
                                'pono',
                                'poqty',
                                'device',
                                'family',
                                'series',
                                'prodtype',
                                'classification',
                                'mod',
                                'qty',
                                'tinput',
                                'toutput',
                                'treject',
                                'tmng',
                                'tpng',
                                'ywomng',
                                'twoyield',
                                'created_at',
                                'updated_at'
                            )->get();
        $effect ="2";

        if (count((array)$save_details) < 1) {
            $localpo = DB::connection($this->mysql)
                                ->table('tbl_poregistration')
                                ->where('pono',$req->po)
                                ->select(DB::raw('pono as pono'),
                                        DB::raw('device_name as device_name'),
                                        DB::raw('poqty as po_qty'),
                                        DB::raw('family as family'),
                                        DB::raw('series as series'),
                                        DB::raw('prod_type as prodtype'))
                                ->first();

            $podetails = $localpo;
             
            $effect ="1";
                 
            if(count((array)$podetails) == 0)
            {
                $effect = "0";
                $ypics = DB::connection($this->mssql)
                                ->SELECT("SELECT r.SORDER as pono, r.CODE as device_code, h.NAME as device_name, r.KVOL as po_qty, SUBSTRING(h.NAME, 1, CHARINDEX('-',h.NAME) - 1) as  series,
                                    UPPER(i.BUNR) as prodtype, h.NOTE as family 
                                    FROM XRECE r 
                                         LEFT JOIN XITEM i ON i.CODE = r.CODE
                                         LEFT JOIN XHEAD h ON h.CODE = r.CODE
                                    WHERE i.BUNR IN('Burn-In','Test Sockets') AND r.SORDER = '$req->po'
                                    GROUP BY r.SORDER, r.CODE, h.NAME, r.KVOL, i.BUNR, h.NOTE
                                    ORDER BY i.BUNR, r.CODE");

                if (count((array)$ypics) > 0) {
                    if ($ypics[0]->pono !== null)
                    {
                        $podetails = $ypics[0];
                    }
                }
            }
        } else {
            $pya = DB::connection($this->mysql)->table('tbl_yielding_pya')
                        ->where('pono','=',$req->po)
                        ->select('id','yield_id','productiondate','yieldingstation','accumulatedoutput','classification','mod','qty','remarks')
                        ->orderBY('productiondate','DESC')
                        ->get();

            $cmq = DB::connection($this->mysql)->table('tbl_yielding_cmq')
                        ->where('pono','=',$req->po)
                        ->select('id','pono','classification','mod','qty','remarks')
                        ->orderBY('productiondate','DESC')
                        ->get();
        }

        // if ($effect !== "2"){
            

        $result = [
            'po_details' => $podetails,
            'effect' => $effect,
            'yield_data' => $save_details,
            'pya' => $pya,
            'cmq' => $cmq
        ];

     // }
       
        return $result;
    }

    public function getRelatedseries(Request $req)
    {
            $dropdownlist = DB::connection($this->mysql)
                            ->table('tbl_seriesregistration')->where('family', '=', $req->Family)->get();
            return $dropdownlist;
    }


          public function Getdeffects(Request $req){

        $dropdownlist = DB::connection($this->common)
                    ->table('tbl_mdropdowns')
                    ->where('category', '=', 10)
                    // ->select('category')
                    ->get();                   
        return $dropdownlist;
    
    }




    //


    
}