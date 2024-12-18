<?php

namespace App\Http\Controllers\QCDB;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
use DB;
use Config;

class IQCGroupByController extends Controller
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

    public function CalculateDPPM(Request $req)
    {
        $g1 = (!isset($req->field1) || $req->field1 == '' || $req->field1 == null)? '': $req->field1;
        $g2 = (!isset($req->field2) || $req->field2 == '' || $req->field2 == null)? '': $req->field2;
        $g3 = (!isset($req->field3) || $req->field3 == '' || $req->field3 == null)? '': $req->field3;
        $content1 = (!isset($req->content1) || $req->content1 == '' || $req->content1 == null)? '%': $req->content1;
        $content2 = (!isset($req->content2) || $req->content2 == '' || $req->content2 == null)? '%': $req->content2;
        $content3 = (!isset($req->content3) || $req->content3 == '' || $req->content3 == null)? '%': $req->content3;

        $findMe = "'";
        $pos1 = strpos($content1, $findMe);
        $pos2 = strpos($content2, $findMe);
        $pos3 = strpos($content3, $findMe);

        if($pos1 == ""){
            $content1 = $content1;
        }else{
            $content1 = substr($content1, 0 , $pos1 );
        }

        if($pos2 == ""){
            $content2 = $content2;
        }else{
            $content2 = substr($content2, 0 , $pos2 );
        }

        if($pos3 == ""){
            $content3 = $content3;
        }else{
            $content3 = substr($content3, 0 , $pos3 );
        }


        $groupby = DB::connection($this->mysql)
                ->select(
                    DB::raw(
                        "CALL GetIQCGroupBy(
                        '".$this->com->convertDate($req->gfrom,'Y-m-d')."',
                        '".$this->com->convertDate($req->gto,'Y-m-d')."',
                        '".$g1."',
                        '".$content1."',
                        '".$g2."',
                        '".$content2."',
                        '".$g3."',
                        '".$content3."')"
                    )
                );

        $data = [];
        $node1 = [];
        $node2 = [];
        $node3 = [];
        $details = [];

        if ($g1 !== '') {
            $grp1_query = DB::connection($this->mysql)->table('iqc_inspection_group')
                            ->select('g1','L1','DPPM1')
                            ->groupBy($g1)
                            ->orderBy('g1')
                            ->get();
            
            foreach ($grp1_query as $key => $gr1) {
                if ($g2 == '') {
                    $details_query =  DB::connection($this->mysql)->table(DB::raw('iqc_inspection_group as i'))
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
                                    ->where('i.g1',$gr1->g1)
                                    ->get();

                    array_push($node1, [
                        'group' => $gr1->g1,
                        'LAR' => $gr1->L1,
                        'DPPM' => $gr1->DPPM1,
                        'field' => $g1,
                        'details' => $details_query
                    ]);
                } else {

                    $grp2_query = DB::connection($this->mysql)->table('iqc_inspection_group')
                                    ->select('g1','g2','L2','DPPM2')
                                    ->where('g1',$gr1->g1)
                                    ->groupBy($g2)
                                    ->orderBy('g2')
                                    ->get();

                    foreach ($grp2_query as $key => $gr2) {
                        if ($g3 == '') {
                            $details_query = DB::connection($this->mysql)->table(DB::raw('iqc_inspection_group as i'))
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
                                                ->where('i.g1',$gr1->g1)
                                                ->where('i.g2',$gr2->g2)
                                                ->get();
                            array_push($node2, [
                                'g1' => $gr1->g1,
                                'group' => $gr2->g2,
                                'LAR' => $gr2->L2,
                                'DPPM' => $gr2->DPPM2,
                                'field' => $g2,
                                'details' => $details_query
                            ]);
                        } else {

                           $grp3_query = DB::connection($this->mysql)->table('iqc_inspection_group')
                                            ->select('g1','g2','g3','L3','DPPM3')
                                            ->where('g1',$gr1->g1)
                                            ->where('g2',$gr2->g2)
                                            ->groupBy($g3)
                                            ->orderBy('g3')
                                            ->get();

                            foreach ($grp3_query as $key => $gr3) {
                                $details_query = DB::connection($this->mysql)->table(DB::raw('iqc_inspection_group as i'))
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
                                                    ->where('i.g1',$gr1->g1)
                                                    ->where('i.g2',$gr2->g2)
                                                    ->where('i.g3',$gr3->g3)
                                                    ->get();
                                array_push($node3, [
                                    'g1' => $gr1->g1,
                                    'g2' => $gr2->g2,
                                    'group' => $gr3->g3,
                                    'LAR' => $gr3->L3,
                                    'DPPM' => $gr3->DPPM3,
                                    'field' => $g3,
                                    'details' => $details_query
                                ]);
                            }

                            array_push($node2, [
                                'g1' => $gr1->g1,
                                'group' => $gr2->g2,
                                'LAR' => $gr2->L2,
                                'DPPM' => $gr2->DPPM2,
                                'field' => $g2,
                                'details' => []
                            ]);
                        }
                    }

                    array_push($node1, [
                        'group' => $gr1->g1,
                        'LAR' => $gr1->L1,
                        'DPPM' => $gr1->DPPM1,
                        'field' => $g1,
                        'details' => []
                    ]);
                }
            }
        }

        $data = [
            'node1' => $node1,
            'node2' => $node2,
            'node3' => $node3
        ];
        
        
        return response()->json($data);
    }

    public function GroupByValues(Request $req)
    {
        $data = DB::connection($this->mysql)->table('iqc_inspections')
                ->select($req->field.' as field')
                ->distinct()
                ->get();

        return $data;
    }
}
