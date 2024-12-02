<?php
namespace App\Http\Controllers\Yielding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; #Auth facade
use Datatables;
use App\Http\Requests;
use App\Poregistration;
use App\Deviceregistration;
use App\Seriesregistration;
use App\Modregistration;
use Carbon\Carbon;
use Config;
use DB;
use Dompdf\Dompdf;
use Excel;
use PDF;

class PORegistrationController extends Controller
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
    public function getPORegistration(Request $request)
    {
         if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_POREG'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        { 
        
            $msrecords = DB::connection($this->mssql)
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
                ->get();

            $record = DB::connection($this->mysql)->table("tbl_yielding_performance")
                        ->groupBy('pono')
                        ->get();
            $targetyield = DB::connection($this->mysql)->table("tbl_targetregistration")->distinct()->get();
           
            $count = DB::connection($this->mysql)->table("tbl_yielding_performance")->count() + 1;
            $tablepya = DB::connection($this->mysql)->table("tbl_yielding_pya")->get(); 
            $tablecmq = DB::connection($this->mysql)->table("tbl_yielding_cmq")->get(); 
            $classification = $this->com->getDropdownByName('Classification');
            $family = DB::connection($this->mysql)->table("tbl_seriesregistration")->select('family')->distinct()->get();
            $series = $this->com->getDropdownByName('series');
            $ys = $this->com->getDropdownByName('Yielding Station');
            $tableporeg = DB::connection($this->mysql)->table("tbl_poregistration")->orderBy('id','DESC')->get();
            $tabledevicereg = DB::connection($this->mysql)->table("tbl_deviceregistration")->orderBy('id','DESC')->get();
            $tableseriesreg = DB::connection($this->mysql)->table("tbl_seriesregistration")->orderBy('family','asc')->get();
            $tablemodreg = DB::connection($this->mysql)->table("tbl_modregistration")->orderBy('family','asc')->get();
            $target = DB::connection($this->mysql)->table("tbl_targetregistration")->orderBy('datefrom','asc')->get();
            $ptype = $this->com->getDropdownByName('Product Type');
            
            return view('yielding.poregistration',['userProgramAccess' => $userProgramAccess,'category' => $classification,'family' => $family,'series' => $series,'yieldstation' => $ys,'record'=>$record, 'yieldingno'=>$count, 'msrecords'=>$msrecords, 'count'=>$count,'fieldpya'=>$tablepya,'fieldcmq'=>$tablecmq,'tableporeg'=>$tableporeg,'tabledevicereg'=>$tabledevicereg,'tableseriesreg'=>$tableseriesreg,'tablemodreg'=>$tablemodreg,
                'target' => $target,
                'ptype' => $ptype,
                'targetyield' => $targetyield]); 
        }
    }
    public function getLoaddevice(Request $req)
    {
        $db = DB::connection($this->mysql)->table('tbl_poypics')
                    ->orderBy('family')
                    ->select([
                            'id',
                            'pono',
                            'device_code',
                            'device_name',
                            'poqty',
                            'family',
                            'series',
                            'prod_type',
                            // 'created_at',
                            // 'updated_at',
                        ]);
        return Datatables::of($db)
                        ->addColumn('action', function ($data) {
                            return '<a href="javascript:;" class="updatesinglebtn btn btn-primary btn-sm input-sm" data-id="'.$data->id.'"
                                data-pono ="'.$data->pono.'"
                                data-device_code ="'.$data->device_code.'"
                                data-device_name ="'.$data->device_name.'"
                                data-poqty ="'.$data->poqty.'"
                                data-family="'.$data->family.'"
                                data-series="'.$data->series.'"
                                data-prod_type="'.$data->prod_type.'">
                                <i class="fa fa-edit"></i>
                                </a>';
                        })
                        ->make(true);
    }

    public function getponoreg(Request $request){
        $pono = $request->pono;
            $msrecords = DB::connection($this->mssql)
                ->table('XRECE as d')
                ->join('XHEAD as h','d.CODE','=','h.CODE')
                ->select(DB::raw("d.SORDER as PO")
                    , DB::raw("d.CODE as devicecode")
                    , DB::raw("h.NAME as devicename")
                    , DB::raw("SUM(d.KVOL) as POqty"))
                ->where('d.SORDER',$request->pono)
                ->groupBy('d.SORDER','d.CODE','h.NAME')
                ->get();
        return $msrecords;
    }
     public function getDropdownByNameSelect3($name)
    {
        $name = str_replace(" ","",strtolower($name));

        try
        {
            $result = DB::connection($this->common)->table('tbl_mdropdowns')->where('category', '='
                , DB::raw("(SELECT id FROM tbl_mdropdown_category WHERE LOWER(REPLACE(category, ' ', ''))='". $name ."')"))
                ->select('description as id', 'description as text')
                ->get();
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }

        return $result;
    }
    public function getDropdowns()
    {
        $family = $this->getDropdownByNameSelect3('Family');
        $series = $this->getDropdownByNameSelect3('Series');
        $product_type = $this->getDropdownByNameSelect3('ProductType');

        return $data = [
                    'family' => $family,
                    'series' => $series,
                    'product_type' => $product_type,
                    ];
    }

    //YIELDING PER
    public function CheckYpicsPO(Request $req)
    {
        $couns = DB::connection($this->mysql)
                    ->table('tbl_poregistration')
                    ->where('pono',$req->po)
                    ->count();
        return response()->json($couns);
    }
    
    //Update tbl_deviceregistration_copy
    private function truncateTable($table)
    {
        return DB::connection($this->mysql)->table($table)->truncate();
    }

    //dax .... get all device from YPICS 4.0
    public function postUpdatebtn(Request $request)
    {
        ini_set('max_execution_time', 0);
        $this->truncateTable('tbl_poypics');

        $value = [];

        $dbs = DB::connection($this->mssql)
                    ->select(DB::raw("
                        SELECT r.SORDER as pono, r.CODE as device_code, h.NAME as device_name, r.KVOL as poqty, SUBSTRING(h.NAME, 1, CHARINDEX('-',h.NAME) - 1) as  series,
                                UPPER(i.BUNR) as prod_type, h.NOTE as family 
                                FROM XRECE r 
                                     LEFT JOIN XITEM i ON i.CODE = r.CODE
                                     LEFT JOIN XHEAD h ON h.CODE = r.CODE
                                WHERE i.BUNR IN('Burn-In','Test Sockets')
                                GROUP BY r.SORDER, r.CODE, h.NAME, r.KVOL, i.BUNR, h.NOTE
                                ORDER BY i.BUNR, r.CODE
                        "));
        
        foreach ($dbs as $key => $db) {
            
            array_push($value, [
                    'pono' => $db->pono,
                    'device_code' => $db->device_code,
                    'device_name' => $db->device_name,
                    'poqty' => $db->poqty,
                    'family' => $db->family,
                    'series' => $db->series,
                    'prod_type' => $db->prod_type,
                ]);
        }

        $insertBatchs = array_chunk($value, 1000);
        foreach ($insertBatchs as $batch) {
            DB::connection($this->mysql)->table('tbl_poypics')->insert($batch);
        }
    }

   //dax .... open modal and display details from the list
    public function displayListItem(Request $req)
    {
        $data = DB::connection($this->mysql)->table('tbl_poypics')
                    ->where('id',$req->id)
                    ->select('pono',
                            'device_code',
                            'device_name',
                            'poqty',
                            'family',
                            'series',
                            'prod_type')
                    ->get();
        if ($this->checkIfExistObject($data) > 0) {
            return $data;
        }
    }

    //displaying  tbl_poregistration records----------------
    
    public function getYieldPerformanceDT(){
        $markup = DB::connection($this->mysql)->table('tbl_yielding_performance')
                    ->orderBy('id','DESC')
                    ->select([
                        'id',
                        'pono',
                        'poqty',
                        'device',
                        'series',
                        'family',
                        'toutput',
                        'treject',
                        'accumulatedoutput',
                    ]);
        return Datatables::of($markup)
                        ->editColumn('id', function($data) {
                            return $data->id;
                        })
                        ->addColumn('action', function($data) {
                            return '<button type="button" name="edit-yield" class="btn btn-sm btn-primary edit-yield" 
                            data-id="'.$data->id.'" 
                            data-pono="'.$data->pono.'" 
                            data-poqty="'.$data->poqty.'"
                            data-device="'.$data->device.'"
                            data-series="'.$data->series.'"
                            data-family="'.$data->family.'"
                            data-toutput="'.$data->toutput.'"
                            data-treject="'.$data->treject.'"
                            data-accumulatedoutput="'.$data->accumulatedoutput.'">
                                         <i class="fa fa-edit"></i> 
                                    </button>';
                        })
                        ->make(true);
    }


    public function displayporeg(){
        $po_reg = DB::connection($this->mysql)->table('tbl_poregistration')->orderBy('id','DESC')->get();
        $data = [
                'msg' => 'Successfully saved.',
                'status' => 'success',
                'po_reg' => $po_reg
        ];
        return response()->json($data);
    }

    public function getporeg()
    {
        $markup = DB::connection($this->mysql)->table('tbl_poregistration')
                    ->orderBy('Prod_type', 'device_code')
                    ->select([
                        'id',
                        'pono',
                        'device_code',
                        'device_name',
                        'poqty',
                        'Family',
                        'Series',
                        'Prod_type',
                    ]);
        return Datatables::of($markup)
                        ->editColumn('id', function($data) {
                            return $data->id;
                        })
                        ->addColumn('action', function($data) {
                            return '<button type="button" name="edit-poreg" class="btn btn-sm btn-primary edit-poreg" 
                            data-id="'.$data->id.'" 
                            data-pono="'.$data->pono.'" 
                            data-device_code="'.$data->device_code.'"
                            data-device_name="'.$data->device_name.'" 
                            data-poqty="'.$data->poqty.'"
                            data-Family="'.$data->Family.'"
                            data-Series="'.$data->Series.'"
                            data-Prod_type="'.$data->Prod_type.'">
                                         <i class="fa fa-edit"></i> 
                                    </button>';
                        })
                        ->make(true);
    }

    public function poregistration(Request $request){

         $this->validate($request, [
            'pono' => 'required',
            'device_name' => 'required',
            'poqty' => 'required',
            'family' => 'required',
            'series' => 'required',
            'prod_type' => 'required'
        ]);

        $status = $request->status;
        $id = $request->id;

        $data = [
            'msg' => 'Saving failed.',
            'status' => 'failed',
            'po_reg' => ''
        ];

        $insert = false;
     
        if($status == "ADD"){
            $insert = DB::connection($this->mysql)->table('tbl_poregistration')
                        ->insert([
                            'pono'=>$request->pono,
                            'device_code'=>$request->device_code,
                            'device_name'=>$request->device_name,
                            'poqty'=>$request->poqty,
                            'family'=>$request->family,
                            'series'=>$request->series,
                            'prod_type'=>$request->prod_type,
                            'create_user' => Auth::user()->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'update_user' => Auth::user()->user_id,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);   
           
        }

        if($status == "EDIT"){
            $insert = DB::connection($this->mysql)->table('tbl_poregistration')
                        ->where('id','=',$request->id)
                        ->update(array(
                            'pono'=>$request->pono,
                            'device_code'=>$request->device_code,
                            'device_name'=>$request->device_name,
                            'poqty'=>$request->poqty,
                            'family'=>$request->family,
                            'series'=>$request->series,
                            'prod_type'=>$request->prod_type,
                            'update_user' => Auth::user()->user_id,
                            'updated_at' => date('Y-m-d H:i:s')
                        ));
        }

        if ($insert) {
            $po_reg = DB::connection($this->mysql)->table('tbl_poregistration')->orderBy('id','DESC')->get();
            $data = [
                'msg' => 'Successfully saved.',
                'status' => 'success',
                'po_reg' => $po_reg
            ];
        }

        
        return response()->json($data);  
    }
    //Edit records for tbl_poregistration------------------
    public function editporeg(Request $request)
    {    
        $data =DB::connection($this->mysql)->table('tbl_poregistration')
        ->where('id', $search)
        ->get();
        return response()->json($data);
    }
    
    //Delete records for tbl_poregistration------------------
    public function deleteporeg(Request $request){  
        
        $tray = $request->tray;
        $traycount = $request->traycount;  
      /*  return $tray;  */
      try{
            if($traycount > 0){
                DB::connection($this->mysql)->table('tbl_poregistration')->wherein('id',$tray)->delete();  
            } 
            return 1;
        }
      catch(Exception $err){
            return 2;
        }
    }
 
    //displaying  tbl_deviceregistration records----------------
    private function displaydevreg(){
        $table = DB::connection($this->mysql)->table('tbl_deviceregistration')->orderBy('id','DESC')->get();
        return $table;
    }
    public function displaydeviceregistration(){
        $table = DB::connection($this->mysql)->table('tbl_deviceregistration')->orderBy('id','DESC')->get();
        return $table;
    }
    //Add and Update Device Registration------------------------
    public function deviceregistration(Request $request){
        $status = $request->status;
        $id = $request->id;

        $count = DB::connection($this->mysql)->table('tbl_deviceregistration')->where('pono',$request->pono)->count();
        if($count > 0){
            return $count;
        }else{
            if($status == "ADD"){
               DB::connection($this->mysql)->table('tbl_deviceregistration')
                    ->insert([
                        'pono'=>$request->pono,
                        'device_name'=>$request->devicename,
                        'family'=>$request->family,
                        'series'=>$request->series,
                        'ptype'=>$request->ptype
                    ]);    
            }
            if($status == "EDIT"){
                DB::connection($this->mysql)->table('tbl_deviceregistration')
                    ->where('id','=',$id)
                    ->update(array(
                        'pono'=>$request->pono,
                        'device_name'=>$request->devicename,
                        'family'=>$request->family,
                        'series'=>$request->series,
                        'ptype'=>$request->ptype   
                    ));
            }
            return $this->displaydevreg();  
        }
    }
    //Edit records for tbl_deviceregistration------------------
    public function editdevicereg(Request $request)
    {    
        $search = $request->editsearch;   
        $ok =DB::connection($this->mysql)->table('tbl_deviceregistration')
        ->where('id', $search)
        ->get();
        return $ok;
    }
    //Delete records for tbl_deviceregistration------------------
    public function deletedevicereg(Request $request){  
        $tray = $request->tray;
        $traycount = $request->traycount;  
      /*  return $tray;  */
        if($traycount > 0){
            DB::connection($this->mysql)->table('tbl_deviceregistration')->wherein('id',$tray)->delete();  
        } 
        return $this->displaydevreg();
    }

    //displaying  tbl_Series Registration records----------------
    private function displayseriesreg(){
        $table = DB::connection($this->mysql)->table('tbl_seriesregistration')->get();
        return $table;
    }

    public function getseriesreg(){
        $markup = DB::connection($this->mysql)->table('tbl_seriesregistration')
                    ->orderBy('id','DESC')
                    ->select([
                        'id',
                        'family',
                        'series',
                    ]);
        return Datatables::of($markup)
                        ->editColumn('id', function($data) {
                            return $data->id;
                        })
                        ->addColumn('action', function($data) {
                            return '<button type="button" name="edit-seriesreg" class="btn btn-sm btn-primary edit-seriesreg" 
                            data-id="'.$data->id.'" 
                            data-family="'.$data->family.'" 
                            data-series="'.$data->series.'">
                                         <i class="fa fa-edit"></i> 
                                    </button>';
                        })
                        ->make(true);
    }
    //Add and Update PO Registration------------------------
    public function seriesregistration(Request $request){
        $status = $request->status;
        $id = $request->id;

        if($status == "ADD"){
           DB::connection($this->mysql)->table('tbl_seriesregistration')
                ->insert([
                    'family'=>$request->family,
                    'series'=>$request->series
                 
                ]);    
        }
        if($status == "EDIT"){
            DB::connection($this->mysql)->table('tbl_seriesregistration')
                ->where('id','=',$id)
                ->update(array(
                    'family'=>$request->family,
                    'series'=>$request->series
                ));
        }
        return $this->displayseriesreg();  
    }
    //Edit records for tbl_seriesregistration------------------
    public function editseriesreg(Request $request)
    {    
        $search = $request->editsearch;
        $ok =DB::connection($this->mysql)->table('tbl_seriesregistration')
        ->where('id', $search)
        ->get();
        return $ok;
    }
    //Delete records for tbl_seriesregistration------------------
    public function deleteseriesreg(Request $request){  
        $tray = $request->tray;
        $traycount = $request->traycount;  

        if($traycount > 0){
            DB::connection($this->mysql)->table('tbl_seriesregistration')->wherein('id',$tray)->delete();  
        } 
        return $this->displayseriesreg();
    }
  
    //displaying  tbl_Mod Registration records----------------
    private function displaymodreg(){
        $table = DB::connection($this->mysql)->table('tbl_modregistration')->get();
        return $table;
    }

    public function getModofDef(){
         $markup = DB::connection($this->mysql)->table('tbl_modregistration')
                    ->orderBy('id','DESC')
                    ->select([
                        'id',
                        'family',
                        'mod',
                    ]);
        return Datatables::of($markup)
                        ->editColumn('id', function($data) {
                            return $data->id;
                        })
                        ->addColumn('action', function($data) {
                            return '<button type="button" name="edit-modedit" class="btn btn-sm btn-primary edit-modedit" 
                            data-id="'.$data->id.'" 
                            data-family="'.$data->family.'" 
                            data-mod="'.$data->mod.'">
                                         <i class="fa fa-edit"></i> 
                                    </button>';
                        })
                        ->make(true);
    }
    //Add and Update PO Registration------------------------
    public function modregistration(Request $request){
        $status = $request->status;
        $id = $request->id;
        if($status == "ADD"){
           DB::connection($this->mysql)->table('tbl_modregistration')
                ->insert([
                    'mod'=>$request->mod,
                    'family'=>$request->family
                 
                ]);    
        }
        if($status == "EDIT"){
            DB::connection($this->mysql)->table('tbl_modregistration')
                ->where('id','=',$id)
                ->update(array(
                    'mod'=>$request->mod,
                    'family'=>$request->family
                ));
        }
        return $this->displaymodreg();  
    }
    //Edit records for tbl_modregistration------------------
    public function editmodreg(Request $request)
    {    
        $search = $request->editsearch;
        $ok =DB::connection($this->mysql)->table('tbl_modregistration')
        ->where('id', $search)
        ->get();
        return $ok;
    }
    //Delete records for tbl_modregistration------------------
    public function deletemodreg(Request $request){  
        $tray = $request->tray;
        $traycount = $request->traycount;  

        if($traycount > 0){
            DB::connection($this->mysql)->table('tbl_modregistration')->wherein('id',$tray)->delete();  
        } 
        return $this->displaymodreg();
    }


    public function deleteYIELD(Request $request){  
        $tray = $request->tray;
        $traycount = $request->traycount;  
      /*  return $tray;  */
      try{
            if($traycount > 0){
                DB::connection($this->mysql)->table('tbl_yielding_performance')->wherein('id',$tray)->delete();  
            } 
            return 1;
        }
      catch(Exception $err){
            return 2;
        }
    }
     //---------------------------------------------------------
    //----------------------------------------------------------
    //displaying  tbl_target Registration records----------------
    private function displaytargetreg(){
        $table = DB::connection($this->mysql)->table('tbl_targetregistration')->get();
        return $table;
    }

    public function getTargetYield(){
         $markup = DB::connection($this->mysql)->table('tbl_targetregistration')
                    ->orderBy('id','DESC')
                    ->select([
                        'id',
                        'datefrom',
                        'dateto',
                        'yield',
                        'dppm',
                        'ptype',
                    ]);
        return Datatables::of($markup)
                        ->editColumn('id', function($data) {
                            return $data->id;
                        })
                        ->addColumn('action', function($data) {
                            return '<button type="button" name="edit-target" class="btn btn-sm btn-primary edit-target" 
                            data-id="'.$data->id.'" 
                            data-datefrom="'.$data->datefrom.'" 
                            data-dateto="'.$data->dateto.'"
                            data-yield="'.$data->yield.'"
                            data-dppm="'.$data->dppm.'"
                            data-ptype="'.$data->ptype.'">
                                         <i class="fa fa-edit"></i> 
                                    </button>';
                        })
                        ->make(true);
    }

    //Add and Update PO Registration------------------------
    public function targetregistration(Request $request){
        $status = $request->status;
        $id = $request->id;
        if($status == "ADD"){
           DB::connection($this->mysql)->table('tbl_targetregistration')
                ->insert([
                    'datefrom'=>$request->datefrom,
                    'dateto'=>$request->dateto,
                    'yield'=>$request->yielding,
                    'dppm'=>$request->dppm,
                    'ptype'=>$request->ptype
                ]);    
        }
        if($status == "EDIT"){
            DB::connection($this->mysql)->table('tbl_targetregistration')
                ->where('id','=',$id)
                ->update(array(
                    'datefrom'=>$request->datefrom,
                    'dateto'=>$request->dateto,
                    'yield'=>$request->yielding,
                    'dppm'=>$request->dppm,
                    'ptype'=>$request->ptype
                ));
        }
        return $this->displaytargetreg();  
    }
    //Edit records for tbl_targetregistration------------------
    public function edittargetreg(Request $request)
    {    
        $search = $request->editsearch;
        $ok =DB::connection($this->mysql)->table('tbl_targetregistration')
        ->where('id', $search)
        ->get();
        return $ok;
    }
    //Delete records for tbl_targetregistration------------------
    public function deletetargetreg(Request $request){  
        $tray = $request->tray;
        $traycount = $request->traycount;  

        if($traycount > 0){
            DB::connection($this->mysql)->table('tbl_targetregistration')->wherein('id',$tray)->delete();  
        } 
        return $this->displaytargetreg();
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
                return redirect('/yieldperformance')->with(['message'=>$msg]);
            } else {
                $msg = "No Record Exists.";
                return redirect('/yieldperformance')->with(['err_message'=>$msg]);
            }
        } else {
            $ok = DB::connection($this->mysql)->table('tbl_yielding_performance')->delete();
        
            if ($ok) {
                $msg = "Successfully deleted all records.";
                return redirect('/yieldperformance')->with(['message'=>$msg]);
            } else {
                $msg = "No Record Exists.";
                return redirect('/yieldperformance')->with(['err_message'=>$msg]);
            }
        }
       
    }

    public function deleteAll(Request $request)
    {
       
        $tray = $request->tray;
        $traycount = $request->traycount;
       /* return $tray;*/
        if($traycount > 0){
            $ok = DB::connection($this->mysql)->table('tbl_yielding_performance')->wherein('id',$tray)->delete();
        
            if ($ok) {
                $msg = "Successfully deleted selected records.";
                return redirect('/yieldperformance')->with(['message'=>$msg]);
            } else {
                $msg = "No Record Exists.";
                return redirect('/yieldperformance')->with(['err_message'=>$msg]);
            }
        } else {
             $ok = DB::connection($this->mysql)->table('tbl_wbssetting')->delete();
        
            if ($ok) {
                $msg = "Successfully deleted all records.";
                return redirect('/yieldperformance')->with(['message'=>$msg]);
            } else {
                $msg = "No Record Exists.";
                return redirect('/yieldperformance')->with(['err_message'=>$msg]);
            }
        }
       
    } 

 
    public function udpateyieldsummary(Request $request)
    {
        $table = "tbl_yielding_performance";
        $exist = $request->data;

  
        $ok = DB::connection($this->mysql)->table($table)
            ->where('id', $exist['masterid'])
            ->update(array('pono'=>$exist['pono'],'poqty' =>$exist['poqty'],'device' =>$exist['device'],'series' =>$exist['series'],'family' =>$exist['family'],'toutput' =>$exist['toutput'],'treject' =>$exist['treject'],'twoyield' =>$exist['twoyield'] ));
      
        if ($ok) {
            $msg = "Successfully saved.";
            return redirect('/yieldperformance')->with(['message'=>$msg]);
        } else {
             $msg = "Saving Failed.";
            return redirect('/yieldperformance')->with(['err_message'=>$msg]);
        }
    }

    function summarychart(){
        $ok =DB::connection($this->mysql)->table('tbl_yielding_performance')->get();
        return $ok;
    }

    public function exportToexcel(Request $request)
    {
        try
        {
            $dt = Carbon::now();
            $date = substr($dt->format('Ymd'), 2);
            $path = public_path().'/Yielding_Performance_Data_Check/export';

            Excel::create('Summary_Records_'.$date, function($excel)
            {
                $excel->sheet('Sheet1', function($sheet)
                {
                    $sheet->cell('A1', "PO NO");
                    $sheet->cell('B1', "PO QUANTITY");
                    $sheet->cell('C1', "DEVICE NAME");
                    $sheet->cell('D1', "SERIES");
                    $sheet->cell('E1', "FAMILY");
                    $sheet->cell('F1', "TOTAL OUTPUT");
                    $sheet->cell('G1', "TOTAL REJECT");
                    $sheet->cell('H1', "TOTAL YIELD");

                    $sheet->row(4, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setBackground('##ADD8E6');
                        $row->setFontSize(10);
                        $row->setAlignment('center');
                    });
                    $sheet->row(1, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setBackground('##ADD8E6');
                        $row->setFontSize(10);
                        $row->setAlignment('center');
                    });
                    $sheet->row(3, function ($row) {
                        $row->setFontFamily('Calibri');
                        $row->setFontSize(10);
                        $row->setAlignment('center');
                    });
                    $sheet->setStyle(array(
                        'font' => array(
                            'name'      =>  'Calibri',
                            'size'      =>  10,
                        )
                    ));
                    $row = 2;
                    $data = DB::connection($this->mysql)->table('tbl_yielding_performance')->get();
                    foreach ($data as $key => $val) {
                        $sheet->cell('A'.$row, $val->pono);
                        $sheet->cell('B'.$row, $val->poqty);
                        $sheet->cell('C'.$row, $val->device);
                        $sheet->cell('D'.$row, $val->series);
                        $sheet->cell('E'.$row, $val->family);
                        $sheet->cell('F'.$row, $val->accumulatedoutput);
                        $sheet->cell('G'.$row, $val->qty);
                        $sheet->cell('H'.$row, $val->twoyield);
                        $row++;
                    }

                });

            })->download('xls');


        } catch (Exception $e) {
            return redirect(url('/yieldperformance'))->with(['err_message' => $e]);
        }
    }

    public function exportTopdf(Request $request)
    {
        $field = DB::connection($this->mysql)->table('tbl_yielding_performance')->get();
            $html1 = '<style>
                        #data {
                          border-collapse: collapse;
                          width: 100%;
                          font-size:10px
                        }

                        #data thead td {
                          border: 1px solid black;
                          text-align: center;
                        }

                        #data tbody td {
                          border-bottom: 1px solid black;
                          alignment:center;
                        }

                        #info {
                          width: 100%;
                          font-size:10px;
                        }

                        #info thead td {
                          text-align: center;
                        }
                        #date{
                            text-align:right;
                        }

                      </style>
                      <table id="info">
                        <thead>
                          <tr>
                            <td colspan="5">
                              <h2>YIELDING PERFORMANCE</h2>
                            </td>
                          </tr>
                          <tr>
                            <td colspan="5">
                              
                            </td>
                          </tr>
                          <tr>
                            <td id="date" colspan="6" style="margin-right:20%">
                              '.date("m/d/Y").'
                            </td>
                          </tr>
                        </thead>
                      </table>
                      <table id="data">
                        <thead>
                          <tr>
                            <td>P.O #</td>
                            <td>P.O QUANTITY</td>
                            <td>DEVICE NAME</td>
                            <td>SERIES</td>
                            <td>FAMILY</td>
                            <td>TOTAL OUTPUT</td>
                            <td>TOTAL REJECT</td>
                            <td>TOTAL YIELD</td>

                          </tr>
                        </thead>
                        <tbody>';

            $html3 = '</tbody>
                      </table>';
           
            $html2 = '';
            foreach ($field as $key => $v) {
               $html2 .= '<tr>
                    <td>'.$v->pono.'</td>
                    <td>'.$v->poqty.'</td>
                    <td>'.$v->device.'</td>
                    <td>'.$v->series.'</td>
                    <td>'.$v->family.'</td>
                    <td>'.$v->accumulatedoutput.'</td>
                    <td>'.$v->qty.'</td>
                    <td>'.$v->twoyield.'</td>
                   
                </tr>';
            }

            $html_final = $html1.$html2.$html3;
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html_final);
            $dompdf->setPaper('letter', 'landscape');
            $dompdf->render();
            $dompdf->stream('Yielding_Performance'.Carbon::now().'.pdf');    
    }

   

 

    public function loadchart(Request $request){
        $df = $request->datefroms;
        $dt = $request->datetos;
        $pieces = explode("/", $df);
        $pieces2 = explode("/", $dt);
        $fixeddf = $pieces[2]."-".$pieces[0]."-".$pieces[1];
        $fixeddt = $pieces2[2]."-".$pieces2[0]."-".$pieces2[1];
       // var_dump($fixeddf);
        $table = DB::connection($this->mysql)->table('tbl_yielding_performance')
                    ->select('family',DB::raw("SUM(toutput) as toutput"),DB::raw("SUM(qty) as qty"))
                    ->groupBy('family')
                    ->orderBy('family')
                    ->whereBetween('productiondate', [$fixeddf, $fixeddt])
                    ->get();
       return $table;
    }

    public function devreg_get_series(Request $request){
        $family = $request->family;
        $table = DB::connection($this->mysql)->table('tbl_seriesregistration')->select('series')->where('family',$family)->get();
        return $table;
    }

    public function getFamilyDropDown(){
        $dropdownlist = DB::connection($this->common)
                    ->table('tbl_mdropdowns')->where('category', '=', 8)->get();
        return $dropdownlist;
    }

    public function getSeriesDropdown(){
        $dropdownlist = DB::connection($this->common)
                    ->table('tbl_mdropdowns')->where('category', '=', 9)->get();
        return $dropdownlist;
    }

    public function getProdtypeDropdown(){
        $dropdownlist = DB::connection($this->common)
                    ->table('tbl_mdropdowns')->where('category', '=', 25)->get();
        return $dropdownlist;
    }


}
