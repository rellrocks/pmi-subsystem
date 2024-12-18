<?php

namespace App\Http\Controllers\YPICS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use File;
use Carbon\Carbon;
use Config;
use Excel;
use Illuminate\Support\Facades\DB;
use PDF;

class FlexScheduleController extends Controller
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

    public function index()
    {
        if(!$this->com->getAccessRights(Config::get('constants.MODULE_CODE_FLEX'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
        	# Render WBS Page.
            return view('ypics.flex-schedule',['userProgramAccess' => $userProgramAccess]);
        }
    }

    // Inventory Data Start
    public function processInventoryData()
    {
        $return_data = [
            'msg' => 'Processing Inventory data has failed.',
            'status' => 'failed'
        ];
        $generated = false;

        try {
            $inventory_data = $this->getInventoryData();

            if (count($inventory_data) > 0) {
                $formatted_content = $this->generateInventoryFormat($inventory_data);
                $generated = $this->generateCSVFile("StockQuery","InventoryData",$formatted_content);

                if ($generated) {
                    $return_data = [
                        'msg' => 'Inventory data was processed, Please wait to download.',
                        'status' => 'success'
                    ];
                }
            } else {
                $return_data = [
                    'msg' => 'No data was processed.',
                    'status' => 'failed'
                ];
            }

        } catch (\Exception $th) {
            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error'
            ];
        }

        return $return_data;
    }

    private function getInventoryData()
    {
        $data = [];

        // $query = "SELECT z.CODE as [CODE], ";
        // $query .= "     h.[NAME] as [NAME], ";
        // $query .= "     SUM(z.ZAIK) as [STOCK], ";
        // $query .= "     SUM(z.ZAIKK) AS [ZAIKK], ";
        // $query .= "     (SUM(z.ZAIK)+SUM(z.ZAIKK)) AS [TOTAL] ";
        // $query .= "FROM XZAIK as z ";
        // $query .= "JOIN XHEAD as h ";
        // $query .= "ON z.CODE = h.CODE ";
        // $query .= "WHERE z.JYOGAI <> 1 ";
        // $query .= "AND (z.ZAIK+z.ZAIKK) > 0 ";
        // $query .= "AND z.HOKAN IN ('WHS100','WHS102','ASSY100','ASSY102') ";
        // $query .= "GROUP BY z.CODE, h.[NAME]";

        $query = "SELECT [CODE],
                        [NAME],
                        SUM([TOTAL]) AS TOTAL,
                        [ZAIKK],
                        SUM(ISNULL([WHS100],0)) as WHS100,                      
                        SUM(ISNULL([ASSY100],0)) as ASSY100,
                        SUM(ISNULL([ASSY102],0)) as ASSY102,
                        SUM(ISNULL([WHS119],0)) as WHS119
                FROM ( SELECT z.CODE as [CODE],
                        h.[NAME] as [NAME],
                        ISNULL(z.ZAIK,0) as [STOCK],
                        ISNULL(z.ZAIKK,0) AS [ZAIKK],
                        ISNULL(z.ZAIK+z.ZAIKK,0) AS [TOTAL],
                                z.HOKAN 
                FROM XZAIK as z
                JOIN XHEAD as h
                ON z.CODE = h.CODE
                WHERE z.JYOGAI <> 1
                AND (z.ZAIK+z.ZAIKK) > 0
                AND z.HOKAN IN ('WHS100','ASSY100','ASSY102','WHS119')
                GROUP BY z.CODE, h.[NAME], z.ZAIK, z.ZAIKK, z.HOKAN ) src
                PIVOT
                (
                        SUM([STOCK])
                        for HOKAN in ([WHS100],[ASSY100],[ASSY102],[WHS119])
                )piv
                GROUP BY CODE, [NAME], [ZAIKK]";

        $data = DB::connection($this->mssql)->select($query);

        return $data;
    }

    public function generateInventoryFormat($inventory_data)
    {
        $i = '"';
        $o = ',';
        // $content = "Code,Name,Stock Total"."\r\n";
        $content = "Code,Name,Stock Total,WHS100,ASSY100,ASSY102,WHS119"."\r\n";

        foreach ($inventory_data as $key => $data) {
            $content .= preg_replace('/\s+/', ' ', trim(preg_replace('/\t+/','',$data->CODE))).$o.
                        preg_replace('/\s+/', ' ', trim(str_replace(",","",preg_replace('/\t+/','',$data->NAME)))).$o.
                        preg_replace('/\s+/', ' ', trim(preg_replace('/\t+/','',$data->TOTAL))).$o.
                        preg_replace('/\s+/', ' ', trim(preg_replace('/\t+/','',$data->WHS100))).$o.
                        preg_replace('/\s+/', ' ', trim(str_replace('/\t+/','',$data->ASSY100))).$o.
                        preg_replace('/\s+/', ' ', trim(preg_replace('/\t+/','',$data->ASSY102))).$o.
                        preg_replace('/\s+/', ' ', trim(preg_replace('/\t+/','',$data->WHS119)))
                        ."\r\n";
        }
        

        return $content;
    }
    // Inventory Data End

    // Parts Incoming
    public function CheckPPSDeliveryFile(Request $req)
    {
        $return_data = [
            'msg' => "Uploading PPS Delivery file has failed.",
            'status' => 'failed',
        ];

        try {
            $file = $req->file('pps_del_file');
        
            $data = [];
            Excel::selectSheetsByIndex(0)->load($file, function ($reader) use(&$data)
            {
                $data = $reader->toArray();
            });

            $sheet1 = $data;
            $error = 0;
            $end_of_line = 0;
            $row = 2;
            foreach ($sheet1 as $key => $sh) {
                if (!isset($sh['itemcode']) && 
                    !isset($sh['name']) && 
                    !isset($sh['orderno']) &&
                    !isset($sh['schdqty']) &&
                    !isset($sh['ppd_reply'])) {
                    $end_of_line++;
                    break;
                }

                if ($end_of_line == 0) {
                    if (!isset($sh['name'])) {
                        $error++;
                    }

                    if (!isset($sh['orderno'])) {
                        $error++;
                    }

                    if (!isset($sh['schdqty'])) {
                        $error++;
                    }

                    if (!isset($sh['ppd_reply'])) {
                        $error++;
                    }
                }

                if ($error > 0) {
                    $return_data = [
                        'msg' => "Uploading has failed, please check Row ". $row .".",
                        'status' => 'failed',
                    ];

                    break;
                }

                $row++;
            }

            if ($error == 0) {
                $params = [];
                $xslip_data = [];
                $xslip_params = [];
                DB::beginTransaction();

                $xslip_data = DB::connection($this->mssql)
                                ->select("SELECT [PORDER] as orderno,
                                                [NDATE] as deldate,
                                                ([KVOL] - [TJITU]) AS balance
                                        FROM XSLIP
                                        where ([KVOL] - [TJITU]) > 0");

                DB::connection($this->mysql)->table('flex_pps_delivery_xslip')->truncate();

                

                foreach ($xslip_data as $key => $x) {

                    $yy = substr($x->deldate,0,4);
                    $mm = substr($x->deldate,4,2);
                    $dd = substr($x->deldate,6,2);

                    $deldate = $yy.'-'.$mm.'-'.$dd;

                    array_push($xslip_params, [
                        'orderno' => $x->orderno,
                        'balance' => $x->balance,
                        'deldate' => $deldate,
                        'token' => $req->_token
                    ]);
                }

                $xslipBatch = array_chunk($xslip_params, 1000);
                foreach ($xslipBatch as $xslp) {
                    DB::connection($this->mysql)->table('flex_pps_delivery_xslip')->insert($xslp);
                }

                DB::connection($this->mysql)->table('flex_pps_delivery')->truncate();

                foreach ($sheet1 as $key => $sh) {
                    if ((float)$sh['schdqty'] > 0) {
                        array_push($params, [
                            'itemcode' => $sh['itemcode'],
                            'itemname' => $sh['name'],
                            'orderno' => $sh['orderno'],
                            'schdqty' => $sh['schdqty'],
                            'ppd_reply' => $sh['ppd_reply'],
                            'token' => $req->_token
                        ]);
                    }
                }

                $insertBatchs = array_chunk($params, 1000);
                foreach ($insertBatchs as $batch) {
                    DB::connection($this->mysql)->table('flex_pps_delivery')->insert($batch);
                }

                DB::commit();

                $return_data = [
                    'msg' => "",
                    'status' => 'success',
                ];
            }
        } catch (\Exception $th) {
            DB::rollback();

            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
            ];
        }

        return $return_data;
    }

    public function processPPSDeliveryFile(Request $req)
    {
        $return_data = [
            'msg' => "Processing of PPS Delivery data and YPICS data has failed.",
            'status' => 'failed',
        ];

        $parts_incoming_data = [];
        $generated = false;

        try {
            $parts_incoming_data = DB::connection($this->mysql)
                                        ->select("SELECT pps.orderno as pr,
                                                        DATE_FORMAT(pps.ppd_reply,'%y%m%d') as yec_pu,
                                                        pps.itemcode as mcode,
                                                        pps.itemname as mname,
                                                        'PPS'  as vender,
                                                        pps.schdqty as deliqty
                                                FROM flex_pps_delivery as pps
                                                left join flex_pps_delivery_xslip as x
                                                on x.orderno = pps.orderno
                                                where pps.orderno is not null and DATE_FORMAT(pps.ppd_reply,'%y%m%d') is not null
                                                and pps.itemcode is not null and pps.itemname is not null
                                                and pps.schdqty is not null");
            if (count($parts_incoming_data) > 0) {
                $formatted_content = $this->generatePartsIncomingFormat($parts_incoming_data);
                $generated = $this->generateCSVFile("MRP","PartsIncomingPlan",$formatted_content);

                if ($generated) {
                    $return_data = [
                        'msg' => 'Parts Incoming Plan data was processed, Please wait to download.',
                        'status' => 'success'
                    ];
                }
            } else {
                $return_data = [
                    'msg' => 'No data was processed.',
                    'status' => 'failed'
                ];
            }

            
        } catch (\Exception $th) {
            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
            ];
        }

        return $return_data;
    }

    public function generatePartsIncomingFormat($pps_data)
    {
    	$i = '"';
    	$o = ',';
        $content = "PR,YEC PU,MCode,MName,VENDER,DeliQty"."\r\n";

        foreach ($pps_data as $key => $data) {
            $content .= trim(preg_replace('/\t+/','',$data->pr)).$o.
                        trim(preg_replace('/\t+/','',$data->yec_pu)).$o.
                        trim(preg_replace('/\t+/','',$data->mcode)).$o.
                        trim(str_replace(",","",preg_replace('/\t+/','',$data->mname))).$o.
                        trim(preg_replace('/\t+/','',$data->vender)).$o.
                        trim(preg_replace('/\t+/','',$data->deliqty))."\r\n";
        }

    	return $content;
    }
    // Parts Incoming End

    // Prod Balance
    private function transalateHeader($text)
    {
        $text = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "", $text));
        switch ($text) {
            case '購買発注番号':
                $text = 'Haccyuu_No';
                break;

            case '補番':
                $text = 'Haccyuu_Hoban';
                break;

            case '品目コード':
                $text = 'Hinmoku_Code';
                break;

            case '品目テキスト':
                $text = 'Hinmoku_tekisuto';
                break;

            case '保管場所':
                $text = 'Hokanbasyo';
                break;

            case 'MRP管理者':
                $text = 'MRPKanrisya';
                break;

            case '発注日':
                $text = 'Haccyuu_Bi';
                break;

            case '仕入先コード':
                $text = 'Siiresaki_Code';
                break;

            case '仕入先テキスト':
                $text = 'Shiiresaki_Tekisuto';
                break;

            case '発注数量':
                $text = 'Haccyuu_Qty';
                break;

            case '発注残数量':
                $text = 'Haccyuu_Zan_Qty';
                break;

            case '統計関連納入日':
                $text = 'Toukei_kannrenn_nounyuu_Bi';
                break;

            case '回答納期':
                $text = 'Kaitou_Nouki';
                break;

            case '回答時刻':
                $text = 'Kaitou_Jikoku';
                break;

            case '回答数量':
                $text = 'Kaitou_Qty';
                break;

            case '得意先コード':
                $text = 'Tokuisaki_Code';
                break;

            case '得意先名':
                $text = 'Tokuisaki_Mei';
                break;

            case '得意先指定納期':
                $text = 'Tokuisaki_Nouki';
                break;
                
            default:
                $text = $text;
                break;
        }

        return $text;
    }

    public function CheckZYMRFile(Request $req)
    {
        $return_data = [
            'msg' => "Uploading ZYPF5210 file has failed.",
            'status' => 'failed',
        ];

        try {
            $content = $this->getFilePointerUTF8($req->file('zymr_file'));
            $line = [];
            $hd = [];
            $header = [];
            $rawData = [];
            $data = [];

            // $content = fopen($file,'r');

            $count = 0;
            while(!feof($content)) {
                if ($count < 1) {
                    //collect header
                    $hd = explode("\t",fgets($content));
                    foreach ($hd as $key => $h) {
                        array_push($header, $this->transalateHeader($h));
                    }
                } else {
                    $line = explode("\t",fgets($content));

                    for ($index=0; $index < count($header); $index++) {
                        if (isset( $line[$index])) {
                            $assoc = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "", $header[$index]));
                            $assoc = $this->transalateHeader($assoc);
                            $rawData[$count-1][$assoc] = $line[$index];
                        }
                    }
                }
                $count++;
            }

            fclose($content);

            foreach ($rawData as $key => $rd) {
                $check_array = $this->checkIndexes($header,$rd);
                if ($check_array) {
                    array_push($data, [
                        'Haccyuu_No' => $rd['Haccyuu_No'], 
                        'Haccyuu_Hoban' => $rd['Haccyuu_Hoban'], 
                        'Hinmoku_Code' => $rd['Hinmoku_Code'], 
                        'Hinmoku_tekisuto' => $rd['Hinmoku_tekisuto'], 
                        'Hokanbasyo' => $rd['Hokanbasyo'], 
                        'MRPKanrisya' => $rd['MRPKanrisya'], 
                        'Haccyuu_Bi' => $rd['Haccyuu_Bi'], 
                        'Siiresaki_Code' => $rd['Siiresaki_Code'], 
                        'Shiiresaki_Tekisuto' => $rd['Shiiresaki_Tekisuto'], 
                        'Haccyuu_Qty' => $rd['Haccyuu_Qty'], 
                        'Haccyuu_Zan_Qty' => $rd['Haccyuu_Zan_Qty'], 
                        'Toukei_kannrenn_nounyuu_Bi' => $rd['Toukei_kannrenn_nounyuu_Bi'], 
                        'Kaitou_Nouki_Jikoku' => $rd['Kaitou_Nouki'].' '.$rd['Kaitou_Jikoku'], 
                        'Kaitou_Qty' => $rd['Kaitou_Qty'], 
                        'Tokuisaki_Code' => $rd['Tokuisaki_Code'], 
                        'Tokuisaki_Mei' => $rd['Tokuisaki_Mei'], 
                        'Tokuisaki_Nouki' => $rd['Tokuisaki_Nouki'], 
                    ]);
                }
                
            }

            DB::beginTransaction();

            DB::connection($this->mysql)->table('flex_prod_balance_zymr')->truncate();

            $inserted = false;

            $insertBatchs = array_chunk($data, 1000);
            foreach ($insertBatchs as $batch) {
                $inserted = DB::connection($this->mysql)->table('flex_prod_balance_zymr')->insert($batch);
            }

            DB::commit();

            if ($inserted) {
                $return_data = [
                    'msg' => "",
                    'status' => 'success',
                ];
            }

        } catch (\Exception $th) {
            DB::rollback();

            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
            ];
        }

        return $return_data;
    }

    public function processZYMRFile(Request $req)
    {
        $return_data = [
            'msg' => "Processing of Data has failed.",
            'status' => 'failed',
            'errors_data' => []
        ];

        $ypics = [];

        try {
            $ypics = DB::connection($this->mssql)
                        ->select("SELECT r.SORDER as sorder,
                                    s.SEIBAN as seiban,
                                    s.CODE as code,
                                    h.NAME as code_name,
                                    s.KVOL as kvol,
                                    s.TJITU as tjitu,
                                    LEFT(r.CDATE,8) as cdate,
                                    r.CVOL as cvol,
                                    LEFT(r.JDATE,8) as jdate,
                                    r.TJITU as r_tjitu,
                                    C.CUST as cust,
                                    c.CNAME as cname
                                FROM XRECE AS r
                                JOIN XSLIP AS s
                                ON s.SEIBAN = r.SORDER
                                JOIN XHEAD AS h
                                ON h.CODE = r.CODE
                                JOIN XCUST AS c
                                ON c.CUST = r.CUST
                                where (s.KVOL - s.TJITU) > 0
                                AND s.SEIBAN IS NOT NULL
                                AND r.CVOL > r.TJITU");
                                
            $ypics_data = [];
            foreach ($ypics as $key => $yp) {
                array_push($ypics_data, [
                    'sorder' => $yp->sorder,
                    'seiban' => $yp->seiban,
                    'code' => $yp->code,
                    'code_name' => $yp->code_name,
                    'kvol' => $yp->kvol,
                    'tjitu' => $yp->tjitu,
                    'cdate' => $yp->cdate,
                    'cvol' => $yp->cvol,
                    'jdate' => $yp->jdate,
                    'r_tjitu' => $yp->r_tjitu,
                    'cust' => $yp->cust,
                    'cname' => $yp->cname
                ]);
            }

            DB::connection($this->mysql)->table('flex_prod_balance_ypics')->truncate();
            DB::connection($this->mysql)->table('flex_prod_balance_error')->truncate();
            $ypics_inserted = false;
            $batches = array_chunk($ypics_data, 1000);
            foreach ($batches as $btch) {
                $ypics_inserted = DB::connection($this->mysql)->table('flex_prod_balance_ypics')->insert($btch);
            }

            if (!$ypics_inserted) {
                $return_data = [
                    'msg' => "Retrieving data from TPICS was unsuccessful.",
                    'status' => 'failed',
                    'errors_data' => []
                ];
            } else {
                $zymr_ypics = [];

                $zymr_ypics = DB::connection($this->mysql)
                                ->select("SELECT CONCAT(LEFT(y.seiban , 10),'-',z.Haccyuu_Hoban) as po_no,
                                                date_format(z.Kaitou_Nouki_Jikoku,'%Y/%m/%d') as pmi_answer_date,
                                                date_format(z.Kaitou_Nouki_Jikoku,'%H:%i:%s') as pmi_answer_time,
                                                y.`code` as pcode,
                                                y.code_name as pname,
                                                LEFT(y.cdate,8) as requested_delivery_date,
                                                z.MRPKanrisya as mrp_no,
                                                z.Tokuisaki_Code as customer_code,
                                                y.cname as customer_name,
                                                z.Kaitou_Qty as pmi_answer_quantity,
                                                DATE_FORMAT(z.Tokuisaki_Nouki,'%Y/%m%/%d') as customer_let,
                                                y.jdate as jdate,
                                                z.Hinmoku_Code,
                                                z.Hinmoku_tekisuto,
                                                z.Hokanbasyo,
                                                z.MRPKanrisya,
                                                z.Haccyuu_Bi,
                                                z.Siiresaki_Code,
                                                z.Shiiresaki_Tekisuto,
                                                z.Haccyuu_Qty,
                                                z.Toukei_kannrenn_nounyuu_Bi,
                                                z.Kaitou_Nouki_Jikoku,
                                                z.Tokuisaki_Code,
                                                z.Tokuisaki_Mei,
                                                z.Tokuisaki_Nouki,
                                                z.Haccyuu_No,
                                                z.Haccyuu_Hoban,
                                                z.Haccyuu_Zan_Qty,
                                                (y.cvol - y.tjitu) as po_balance,
                                                z.Kaitou_Qty,
                                                (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)  as diff,
                                                zz.sumKaitou_Qty as sumKaitou_Qty,
                                                zzz.po_count as po_count,
                                                if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)<0,1,0) as lack_answer,
                                                if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)>0,1,0) as over_answer,
                                                if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)=0,1,0) as exact_answer,
                                                if((zz.sumKaitou_Qty - z.Haccyuu_Zan_Qty)=0,1,0) as answer,
                                                case 
                                                    
                                                    when (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)=0 and (zz.sumKaitou_Qty - z.Haccyuu_Zan_Qty)=0 and ((y.cvol - y.tjitu) - zz.sumKaitou_Qty)=0 then 11
                                                    when z.Haccyuu_Zan_Qty = zz.sumKaitou_Qty and (y.cvol - y.tjitu) > z.Haccyuu_Zan_Qty then 16
                                                    when zzz.po_count = 1 and (y.cvol - y.tjitu) > (z.Kaitou_Qty - zz.sumKaitou_Qty) then 17
                                                    when zzz.po_count > 1 and (y.cvol - y.tjitu) = zz.sumKaitou_Qty then 12
                                                    when zzz.po_count = 2 and z.Haccyuu_Hoban <> '0001' and (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)>0 then 13
                                                    when zzz.po_count = 1 and z.Kaitou_Qty = zz.sumKaitou_Qty then 14
                                                    when zzz.po_count > 1 and (y.cvol - y.tjitu) = z.Kaitou_Qty then 15
                                                    else 0 
                                                end as included
                                            FROM flex_prod_balance_zymr as z
                                            LEFT JOIN flex_prod_balance_ypics as y
                                            ON z.Haccyuu_No = LEFT(y.seiban , 10)
                                            LEFT JOIN (select Haccyuu_No, 
                                                            Haccyuu_Zan_Qty,
                                                            sum(Kaitou_Qty) as sumKaitou_Qty
                                                        from flex_prod_balance_zymr
                                                        group by Haccyuu_No, Haccyuu_Zan_Qty
                                                    ) as zz
                                            on z.Haccyuu_No = zz.Haccyuu_No
                                            LEFT JOIN (SELECT Haccyuu_No, count(Haccyuu_No) as po_count 
                                                        from flex_prod_balance_zymr
                                                        group by Haccyuu_No) as zzz
                                            on zzz.Haccyuu_No = z.Haccyuu_No");

                if (count($zymr_ypics) > 0) {
                    // create csv format
                    $content = $this->generateProdBalanceFormat($zymr_ypics);
                    //$generated = $this->generateCSVFile("PRODUCTION_PMI_TS","ProdBalance",$formatted_content);

                    if (count($content) > 0) {
                        $errors_data = DB::connection($this->mysql)
                                        ->select("SELECT Haccyuu_No,
                                                        Haccyuu_Hoban,
                                                        Hinmoku_Code,
                                                        Hinmoku_tekisuto,
                                                        Hokanbasyo,
                                                        MRPKanrisya,
                                                        DATE_FORMAT(Haccyuu_Bi,'%Y/%m/%d') as Haccyuu_Bi,
                                                        Siiresaki_Code,
                                                        Shiiresaki_Tekisuto,
                                                        Haccyuu_Qty,
                                                        Haccyuu_Zan_Qty,
                                                        DATE_FORMAT(Toukei_kannrenn_nounyuu_Bi,'%Y/%m/%d') as Toukei_kannrenn_nounyuu_Bi,
                                                        DATE_FORMAT(Kaitou_Nouki_Jikoku,'%Y/%m/%d') as Kaitou_Nouki,
                                                        DATE_FORMAT(Kaitou_Nouki_Jikoku,'%H:%i:%s') as Kaitou_Jikoku,
                                                        Kaitou_Qty,
                                                        Tokuisaki_Code,
                                                        Tokuisaki_Mei,
                                                        DATE_FORMAT(Tokuisaki_Nouki,'%Y/%m/%d') as Tokuisaki_Nouki
                                                FROM flex_prod_balance_error
                                                ORDER BY Haccyuu_No, Haccyuu_Hoban");

                        $this->ErrorsExcelFileCreate($errors_data);

                        $return_data = [
                            'msg' => 'Production Balance & Ship Plan data was processed, Please wait to download.',
                            'status' => 'success',
                            'errors_data' => $errors_data
                        ];
                    } else {
                        $return_data = [
                            'msg' => 'No P.O. got matched with this .txt file.',
                            'status' => 'failed',
                            'errors_data' => []
                        ];
                    }
                } else {
                    $return_data = [
                        'msg' => 'No data was processed.',
                        'status' => 'failed',
                        'errors_data' => []
                    ];
                }
            }

        } catch (\Exception $th) {
            $return_data = [
                'msg' => $th->getMessage(),
                'status' => 'error',
                'errors_data' => []
            ];
        }

        return $return_data;
    }

    private function remove_excess_qty($po,$output_data,$requested_delivery_date,$jdate)
    {
        // foreach ($data_checker as $key => $ch) {
            $data_to_check = DB::connection($this->mysql)
                                ->select("SELECT CONCAT(LEFT(y.seiban , 10),'-',z.Haccyuu_Hoban) as po_no,
                                            date_format(z.Kaitou_Nouki_Jikoku,'%Y/%m/%d') as pmi_answer_date,
                                            date_format(z.Kaitou_Nouki_Jikoku,'%H:%i:%s') as pmi_answer_time,
                                            y.`code` as pcode,
                                            y.code_name as pname,
                                            LEFT(y.cdate,8) as requested_delivery_date,
                                            z.MRPKanrisya as mrp_no,
                                            z.Tokuisaki_Code as customer_code,
                                            y.cname as customer_name,
                                            z.Kaitou_Qty as pmi_answer_quantity,
                                            DATE_FORMAT(z.Tokuisaki_Nouki,'%Y/%m%/%d') as customer_let,
                                            y.jdate as jdate,
                                            z.Hinmoku_Code,
                                            z.Hinmoku_tekisuto,
                                            z.Hokanbasyo,
                                            z.MRPKanrisya,
                                            z.Haccyuu_Bi,
                                            z.Siiresaki_Code,
                                            z.Shiiresaki_Tekisuto,
                                            z.Haccyuu_Qty,
                                            z.Toukei_kannrenn_nounyuu_Bi,
                                            z.Kaitou_Nouki_Jikoku,
                                            z.Tokuisaki_Code,
                                            z.Tokuisaki_Mei,
                                            z.Tokuisaki_Nouki,
                                            z.Haccyuu_No,
                                            z.Haccyuu_Hoban,
                                            z.Haccyuu_Zan_Qty,
                                            (y.cvol - y.tjitu) as po_balance,
                                            z.Kaitou_Qty,
                                            (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)  as diff,
                                            zz.sumKaitou_Qty as sumKaitou_Qty,
                                            zzz.po_count as po_count,
                                            if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)<0,1,0) as lack_answer,
                                            if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)>0,1,0) as over_answer,
                                            if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)=0,1,0) as exact_answer,
                                            if((zz.sumKaitou_Qty - z.Haccyuu_Zan_Qty)=0,1,0) as answer,
                                            case 
                                                
                                                when (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)=0 and (zz.sumKaitou_Qty - z.Haccyuu_Zan_Qty)=0 then 11
                                                when zzz.po_count > 1 and (y.cvol - y.tjitu) = zz.sumKaitou_Qty then 12
                                                when zzz.po_count = 2 and z.Haccyuu_Hoban <> '0001' and (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)>0 then 13
                                                when zzz.po_count = 1 and z.Kaitou_Qty = zz.sumKaitou_Qty then 14
                                                when zzz.po_count > 1 and (y.cvol - y.tjitu) = z.Kaitou_Qty then 15
                                                else 0 
                                            end as included
                                        FROM flex_prod_balance_zymr as z
                                        LEFT JOIN flex_prod_balance_ypics as y
                                        ON z.Haccyuu_No = LEFT(y.seiban , 10)
                                        LEFT JOIN (select Haccyuu_No, 
                                                        Haccyuu_Zan_Qty,
                                                        sum(Kaitou_Qty) as sumKaitou_Qty
                                                    from flex_prod_balance_zymr
                                                    group by Haccyuu_No, Haccyuu_Zan_Qty
                                                ) as zz
                                        on z.Haccyuu_No = zz.Haccyuu_No
                                        LEFT JOIN (SELECT Haccyuu_No, count(Haccyuu_No) as po_count 
                                                    from flex_prod_balance_zymr
                                                    group by Haccyuu_No) as zzz
                                        on zzz.Haccyuu_No = z.Haccyuu_No
                                        where z.Haccyuu_No = '".$po."'"); //where (y.cvol - y.tjitu) <=  z.Kaitou_Qty

            $not_included = [];
            
            $has_complete_balance = false;

            $last_index = count($data_to_check) - 1;

            // // check if there was completed balance
            // if ($data_to_check[$last_index]->po_balance == $data_to_check[$last_index]->pmi_answer_quantity) {
            //     $has_complete_balance = true;
            // }

            // // manipulate data with complete balance
            // if ($has_complete_balance) {
            //     $po_bal = 0;
            //     $pmi_ans_qty = 0;
            //     for($i = count($data_to_check) - 1; $i >= 0; $i--)
            //     {
            //         if ($ch->po_no == $data_to_check[$i]->Haccyuu_No) {
            //             $po_bal = $data_to_check[$i]->po_balance;
            //             $pmi_ans_qty = $data_to_check[$i]->pmi_answer_quantity;

            //             if ($po_bal > $pmi_ans_qty) {
            //                 array_push($not_included, $data_to_check[$i]->id);
            //             }
            //         }
            //     }
            // } 

            // manipulate data with seperated complete balance
            // else {
                $alloted_balance = 0;
                $po_bal = $data_to_check[0]->po_balance;
                $pmi_ans_qty = 0;
                $answers = [];
                for($i = count($data_to_check) - 1; $i >= 0; $i--)
                {
                    if ($po == $data_to_check[$i]->Haccyuu_No) {
                        
                        $pmi_ans_qty += $data_to_check[$i]->pmi_answer_quantity;

                        if ($po_bal >= $pmi_ans_qty) {
                            array_push($answers, [
                                'po_no' => trim(preg_replace('/\t+/','',$data_to_check[$i]->po_no)),
                                'pmi_answer_date' => trim(preg_replace('/\t+/','',$data_to_check[$i]->pmi_answer_date)),
                                'pmi_answer_time' => trim(preg_replace('/\t+/','',$data_to_check[$i]->pmi_answer_time)),
                                'pcode' => trim(preg_replace('/\t+/','',$data_to_check[$i]->pcode)),
                                'pname' => trim(str_replace(",","",preg_replace('/\t+/','',$data_to_check[$i]->pname))),
                                'po_balance' => trim(preg_replace('/\t+/','',$data_to_check[$i]->po_balance)),
                                'requested_delivery_date' => trim(preg_replace('/\t+/','',$requested_delivery_date)),
                                'mrp_no' => trim(preg_replace('/\t+/','',$data_to_check[$i]->mrp_no)),
                                'customer_code' => trim(preg_replace('/\t+/','',$data_to_check[$i]->customer_code)),
                                'customer_name' => trim(str_replace(",","",preg_replace('/\t+/','',$data_to_check[$i]->customer_name))),
                                'pmi_answer_quantity' => trim(preg_replace('/\t+/','',$data_to_check[$i]->pmi_answer_quantity)),
                                'customer_let' => trim(preg_replace('/\t+/','',$data_to_check[$i]->customer_let)),
                                'jdate' => trim(preg_replace('/\t+/','',$jdate)),
                                'Haccyuu_No' => trim(preg_replace('/\t+/','',$data_to_check[$i]->Haccyuu_No)),
                                'Haccyuu_Qty' => trim(preg_replace('/\t+/','',$data_to_check[$i]->Haccyuu_Qty))
                            ]);
                        }

                        // if ($po_bal < $pmi_ans_qty) {
                        //     array_push($not_included, $data_to_check[$i]->id);
                        // }
                    }
                }

                foreach ($answers as $key => $ans) {
                    array_push($output_data, [
                        'po_no' => $ans['po_no'],
                        'pmi_answer_date' => $ans['pmi_answer_date'],
                        'pmi_answer_time' => $ans['pmi_answer_time'],
                        'pcode' => $ans['pcode'],
                        'pname' => $ans['pname'],
                        'po_balance' => $ans['po_balance'],
                        'requested_delivery_date' => $requested_delivery_date,
                        'mrp_no' => $ans['mrp_no'],
                        'customer_code' => $ans['customer_code'],
                        'customer_name' => $ans['customer_name'],
                        'pmi_answer_quantity' => $ans['pmi_answer_quantity'],
                        'customer_let' => $ans['customer_let'],
                        'jdate' => $jdate,
                        'Haccyuu_No' => $ans['Haccyuu_No'],
                        'Haccyuu_Qty' => $ans['Haccyuu_Qty'],
                    ]);
                }
            // }            

            // if ($has_complete_balance) {
            //     foreach ($data_to_check as $key => $dtc) {
            //         if ($dtc->po_balance > $dtc->pmi_answer_quantity) {
            //             array_push($not_included, $dtc->id);
            //         }
            //     }
            // } else {
            //     array_push($not_included, $data_to_check[0]->id);
            // }

            // DB::connection($this->mysql)->table('flex_prod_balance_zymr')
            //     ->whereIn('id',$not_included)->update(['not_included'=>1]);
            
            // $not_included_id = $data_to_check[0]->id;
            // DB::connection($this->mysql)->table('flex_prod_balance_zymr')->where('id',$not_included_id)->update(['not_included'=>1]);
        // }

        return $output_data;
    }

    public function generateProdBalanceFormat($pbalance_data)
    {
        $output_data = [];

        $not_included_pos = DB::connection($this->mysql)
                                ->select("select dd.po_no,dd.po_count, count(dd.po_no) as cnt 
                                        from(SELECT LEFT(y.seiban , 10) as po_no,
                                            y.`code` as pcode,
                                            y.code_name as pname,
                                            z.Kaitou_Qty as pmi_answer_quantity,
                                            z.Haccyuu_Qty,
                                            z.Haccyuu_Zan_Qty,
                                            (y.cvol - y.tjitu) as po_balance,
                                            z.Kaitou_Qty,
                                            (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)  as diff,
                                            zz.sumKaitou_Qty as sumKaitou_Qty,
                                            zzz.po_count as po_count,
                                            if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)<0,1,0) as lack_answer,
                                            if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)>0,1,0) as over_answer,
                                            if((z.Kaitou_Qty - z.Haccyuu_Zan_Qty)=0,1,0) as exact_answer,
                                            if((zz.sumKaitou_Qty - z.Haccyuu_Zan_Qty)=0,1,0) as answer,
                                            case 
                                                
                                                when (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)=0 and (zz.sumKaitou_Qty - z.Haccyuu_Zan_Qty)=0 then 11
                                                when zzz.po_count > 1 and (y.cvol - y.tjitu) = zz.sumKaitou_Qty then 12
                                                when zzz.po_count = 2 and z.Haccyuu_Hoban <> '0001' and (z.Kaitou_Qty - z.Haccyuu_Zan_Qty)>0 then 13
                                                when zzz.po_count = 1 and z.Kaitou_Qty = zz.sumKaitou_Qty then 14
                                                when zzz.po_count > 1 and (y.cvol - y.tjitu) = z.Kaitou_Qty then 15
                                                else 0 
                                            end as included
                                        FROM flex_prod_balance_zymr as z
                                        LEFT JOIN flex_prod_balance_ypics as y
                                        ON z.Haccyuu_No = LEFT(y.seiban , 10)
                                        LEFT JOIN (select Haccyuu_No, 
                                                        Haccyuu_Zan_Qty,
                                                        sum(Kaitou_Qty) as sumKaitou_Qty
                                                    from flex_prod_balance_zymr
                                                    group by Haccyuu_No, Haccyuu_Zan_Qty
                                                ) as zz
                                        on z.Haccyuu_No = zz.Haccyuu_No
                                        LEFT JOIN (SELECT Haccyuu_No, count(Haccyuu_No) as po_count 
                                                    from flex_prod_balance_zymr
                                                    group by Haccyuu_No) as zzz
                                        on zzz.Haccyuu_No = z.Haccyuu_No) as dd
                                        where dd.included = 0
                                        group by dd.po_no,dd.po_count
                                        having po_count = cnt");

        foreach ($pbalance_data as $key => $data) {
            if (is_null($data->po_no)) {
                DB::connection($this->mysql)->table('flex_prod_balance_error')
                    ->insert([
                        'Haccyuu_No' => $data->Haccyuu_No, 
                        'Haccyuu_Hoban' => $data->Haccyuu_Hoban, 
                        'Hinmoku_Code' => $data->Hinmoku_Code, 
                        'Hinmoku_tekisuto' => $data->Hinmoku_tekisuto, 
                        'Hokanbasyo' => $data->Hokanbasyo, 
                        'MRPKanrisya' => $data->MRPKanrisya, 
                        'Haccyuu_Bi' => $data->Haccyuu_Bi, 
                        'Siiresaki_Code' => $data->Siiresaki_Code, 
                        'Shiiresaki_Tekisuto' => $data->Shiiresaki_Tekisuto, 
                        'Haccyuu_Qty' => $data->Haccyuu_Qty, 
                        'Haccyuu_Zan_Qty' => $data->Haccyuu_Zan_Qty, 
                        'Toukei_kannrenn_nounyuu_Bi' => $data->Toukei_kannrenn_nounyuu_Bi, 
                        'Kaitou_Nouki_Jikoku' => $data->Kaitou_Nouki_Jikoku, 
                        'Kaitou_Qty' => $data->Kaitou_Qty, 
                        'Tokuisaki_Code' => $data->Tokuisaki_Code, 
                        'Tokuisaki_Mei' => $data->Tokuisaki_Mei, 
                        'Tokuisaki_Nouki' => $data->Tokuisaki_Nouki, 
                    ]);

            } else {
                $rdd_yy = substr($data->requested_delivery_date,0,4);
                $rdd_mm = substr($data->requested_delivery_date,4,2);
                $rdd_dd = substr($data->requested_delivery_date,6,2);

                $requested_delivery_date = $rdd_yy.'/'.$rdd_mm.'/'.$rdd_dd;

                $jd_yy = substr($data->jdate,0,4);
                $jd_mm = substr($data->jdate,4,2);
                $jd_dd = substr($data->jdate,6,2);

                $jdate = $jd_yy.'/'.$jd_mm.'/'.$jd_dd;

                $arrChecking = [11,12,14,15];
                if (in_array($data->included,$arrChecking)) {
                    array_push($output_data, [
                        'po_no' => trim(preg_replace('/\t+/','',$data->po_no)),
                        'pmi_answer_date' => trim(preg_replace('/\t+/','',$data->pmi_answer_date)),
                        'pmi_answer_time' => trim(preg_replace('/\t+/','',$data->pmi_answer_time)),
                        'pcode' => trim(preg_replace('/\t+/','',$data->pcode)),
                        'pname' => trim(str_replace(",","",preg_replace('/\t+/','',$data->pname))),
                        'po_balance' => trim(preg_replace('/\t+/','',$data->po_balance)),
                        'requested_delivery_date' => trim(preg_replace('/\t+/','',$requested_delivery_date)),
                        'mrp_no' => trim(preg_replace('/\t+/','',$data->mrp_no)),
                        'customer_code' => trim(preg_replace('/\t+/','',$data->customer_code)),
                        'customer_name' => trim(str_replace(",","",preg_replace('/\t+/','',$data->customer_name))),
                        'pmi_answer_quantity' => trim(preg_replace('/\t+/','',$data->pmi_answer_quantity)),
                        'customer_let' => trim(preg_replace('/\t+/','',$data->customer_let)),
                        'jdate' => trim(preg_replace('/\t+/','',$jdate)),
                        'Haccyuu_No' => trim(preg_replace('/\t+/','',$data->Haccyuu_No)),
                        'Haccyuu_Qty' => trim(preg_replace('/\t+/','',$data->Haccyuu_Qty))
                    ]);
                } elseif ($data->included == 13) {
                    array_push($output_data, [
                        'po_no' => trim(preg_replace('/\t+/','',$data->po_no)),
                        'pmi_answer_date' => trim(preg_replace('/\t+/','',$data->pmi_answer_date)),
                        'pmi_answer_time' => trim(preg_replace('/\t+/','',$data->pmi_answer_time)),
                        'pcode' => trim(preg_replace('/\t+/','',$data->pcode)),
                        'pname' => trim(str_replace(",","",preg_replace('/\t+/','',$data->pname))),
                        'po_balance' => trim(preg_replace('/\t+/','',$data->po_balance)),
                        'requested_delivery_date' => trim(preg_replace('/\t+/','',$requested_delivery_date)),
                        'mrp_no' => trim(preg_replace('/\t+/','',$data->mrp_no)),
                        'customer_code' => trim(preg_replace('/\t+/','',$data->customer_code)),
                        'customer_name' => trim(str_replace(",","",preg_replace('/\t+/','',$data->customer_name))),
                        'pmi_answer_quantity' => trim(preg_replace('/\t+/','',$data->po_balance)),
                        'customer_let' => trim(preg_replace('/\t+/','',$data->customer_let)),
                        'jdate' => trim(preg_replace('/\t+/','',$jdate)),
                        'Haccyuu_No' => trim(preg_replace('/\t+/','',$data->Haccyuu_No)),
                        'Haccyuu_Qty' => trim(preg_replace('/\t+/','',$data->Haccyuu_Qty))
                    ]);
                }
                elseif ($data->included == 17) {
                    array_push($output_data, [
                        'po_no' => trim(preg_replace('/\t+/','',$data->po_no)),
                        'pmi_answer_date' => trim(preg_replace('/\t+/','',$data->pmi_answer_date)),
                        'pmi_answer_time' => trim(preg_replace('/\t+/','',$data->pmi_answer_time)),
                        'pcode' => trim(preg_replace('/\t+/','',$data->pcode)),
                        'pname' => trim(str_replace(",","",preg_replace('/\t+/','',$data->pname))),
                        'po_balance' => trim(preg_replace('/\t+/','',$data->po_balance)),
                        'requested_delivery_date' => trim(preg_replace('/\t+/','',$requested_delivery_date)),
                        'mrp_no' => trim(preg_replace('/\t+/','',$data->mrp_no)),
                        'customer_code' => trim(preg_replace('/\t+/','',$data->customer_code)),
                        'customer_name' => trim(str_replace(",","",preg_replace('/\t+/','',$data->customer_name))),
                        'pmi_answer_quantity' => trim(preg_replace('/\t+/','',$data->po_balance)),
                        'customer_let' => trim(preg_replace('/\t+/','',$data->customer_let)),
                        'jdate' => trim(preg_replace('/\t+/','',$jdate)),
                        'Haccyuu_No' => trim(preg_replace('/\t+/','',$data->Haccyuu_No)),
                        'Haccyuu_Qty' => trim(preg_replace('/\t+/','',$data->Haccyuu_Qty))
                    ]);
                }
                elseif ($data->included == 16) {
                    # add new answer
                    $additional_plan = DB::connection($this->mysql)
                                            ->select("SELECT LEFT(y.seiban , 10) as po_no,
                                                        (y.cvol - y.tjitu) as po_balance,
                                                        SUM(z.Kaitou_Qty) as pmi_answer_quantity,
                                                        z.Haccyuu_No
                                                    FROM flex_prod_balance_zymr as z
                                                    LEFT JOIN flex_prod_balance_ypics as y
                                                    ON z.Haccyuu_No = LEFT(y.seiban , 10) 
                                                    where (y.cvol - y.tjitu) >=  z.Kaitou_Qty AND z.not_included = 0
                                                    and LEFT(y.seiban , 10) = '".$data->Haccyuu_No."'
                                                    group by LEFT(y.seiban , 10),
                                                        (y.cvol - y.tjitu),
                                                        z.Haccyuu_No
                                                    having po_balance > SUM(z.Kaitou_Qty)");
                    if (count($additional_plan) > 0) {
                        array_push($output_data, [
                            'po_no' => trim(preg_replace('/\t+/','',$data->po_no)),
                            'pmi_answer_date' => trim(preg_replace('/\t+/','',$data->pmi_answer_date)),
                            'pmi_answer_time' => trim(preg_replace('/\t+/','',$data->pmi_answer_time)),
                            'pcode' => trim(preg_replace('/\t+/','',$data->pcode)),
                            'pname' => trim(str_replace(",","",preg_replace('/\t+/','',$data->pname))),
                            'po_balance' => trim(preg_replace('/\t+/','',$data->po_balance)),
                            'requested_delivery_date' => trim(preg_replace('/\t+/','',$requested_delivery_date)),
                            'mrp_no' => trim(preg_replace('/\t+/','',$data->mrp_no)),
                            'customer_code' => trim(preg_replace('/\t+/','',$data->customer_code)),
                            'customer_name' => trim(str_replace(",","",preg_replace('/\t+/','',$data->customer_name))),
                            'pmi_answer_quantity' => trim(preg_replace('/\t+/','',$data->pmi_answer_quantity)),
                            'customer_let' => trim(preg_replace('/\t+/','',$data->customer_let)),
                            'jdate' => trim(preg_replace('/\t+/','',$jdate)),
                            'Haccyuu_No' => trim(preg_replace('/\t+/','',$data->Haccyuu_No)),
                            'Haccyuu_Qty' => trim(preg_replace('/\t+/','',$data->Haccyuu_Qty))
                        ]);

                        // additional plan
                        foreach ($additional_plan as $key => $add) {
                            $hoban = (int)$data->Haccyuu_Hoban + 1;
                            $hoban_no = str_pad($hoban, 4, '0', STR_PAD_LEFT);
                            $po = $data->Haccyuu_No.'-'.$hoban_no;
                            $pmi_answer_quantity = $add->po_balance - $add->pmi_answer_quantity;

                            array_push($output_data, [
                                'po_no' => trim(preg_replace('/\t+/','',$po)),
                                'pmi_answer_date' => trim(preg_replace('/\t+/','',$data->pmi_answer_date)),
                                'pmi_answer_time' => trim(preg_replace('/\t+/','',$data->pmi_answer_time)),
                                'pcode' => trim(preg_replace('/\t+/','',$data->pcode)),
                                'pname' => trim(str_replace(",","",preg_replace('/\t+/','',$data->pname))),
                                'po_balance' => trim(preg_replace('/\t+/','',$data->po_balance)),
                                'requested_delivery_date' => trim(preg_replace('/\t+/','',$requested_delivery_date)),
                                'mrp_no' => trim(preg_replace('/\t+/','',$data->mrp_no)),
                                'customer_code' => trim(preg_replace('/\t+/','',$data->customer_code)),
                                'customer_name' => trim(str_replace(",","",preg_replace('/\t+/','',$data->customer_name))),
                                'pmi_answer_quantity' => trim(preg_replace('/\t+/','',$pmi_answer_quantity)),
                                'customer_let' => trim(preg_replace('/\t+/','',$data->customer_let)),
                                'jdate' => trim(preg_replace('/\t+/','',$jdate)),
                                'Haccyuu_No' => trim(preg_replace('/\t+/','',$data->Haccyuu_No)),
                                'Haccyuu_Qty' => trim(preg_replace('/\t+/','',$data->Haccyuu_Qty))
                            ]);
                        }
                    }
                } else {
                    foreach ($not_included_pos as $key => $not) {
                        if ($not->po_no == $data->Haccyuu_No) {
                            $output_length = count($output_data) -1;
                            if ($output_length > -1) {
                                $po_output = $output_data[$output_length]['Haccyuu_No'];

                                if ($po_output == $data->Haccyuu_No) {
                                    break;
                                }

                                $output_data = $this->remove_excess_qty($data->Haccyuu_No,$output_data,$requested_delivery_date,$jdate);
                            }
                        }
                    }
                }
            }
        }

        if (count($output_data) > 0) {
            Excel::create("PRODUCTION_PMI_TS", function($excel) use($output_data)
            {
                $excel->sheet("PRODUCTION_PMI_TS", function($sheet) use($output_data)
                {
                    $sheet->setHeight(1, 15);
                    $sheet->cell('A1',"PONo");
                    $sheet->cell('B1',"PMI Answer Date");
                    $sheet->cell('C1',"PMI Answer Time");
                    $sheet->cell('D1',"PCode");
                    $sheet->cell('E1',"PName");
                    $sheet->cell('F1',"POBalance");
                    $sheet->cell('G1',"Requested delivery date");
                    $sheet->cell('H1',"MRP No");
                    $sheet->cell('I1',"Customer code");
                    $sheet->cell('J1',"Customer name");
                    $sheet->cell('K1',"PMI Answer Quantity");
                    $sheet->cell('L1',"Customer let");
                    $sheet->cell('M1',"Order Date");
                    $sheet->cell('N1',"Order Qty");

                    $sheet->setColumnFormat(array(
                        'B' => 'yyyy/mm/dd',
                        'D' => '@',
                        'G' => 'yyyy/mm/dd',
                        'L' => 'yyyy/mm/dd',
                        'M' => 'yyyy/mm/dd'
                    ));

                    $row = 2;
                    foreach ($output_data as $key => $dt) {

                        $sheet->cell('A'.$row, $dt['po_no']);
                        $sheet->cell('B'.$row, $dt['pmi_answer_date']);
                        $sheet->cell('C'.$row, $dt['pmi_answer_time']);
                        $sheet->cell('D'.$row, $dt['pcode']);
                        $sheet->cell('E'.$row, $dt['pname']);
                        $sheet->cell('F'.$row, $dt['po_balance']);
                        $sheet->cell('G'.$row, $dt['requested_delivery_date']);
                        $sheet->cell('H'.$row, $dt['mrp_no']);
                        $sheet->cell('I'.$row, $dt['customer_code']);
                        $sheet->cell('J'.$row, $dt['customer_name']);
                        $sheet->cell('K'.$row, $dt['pmi_answer_quantity']);
                        $sheet->cell('L'.$row, $dt['customer_let']);
                        $sheet->cell('M'.$row, $dt['jdate']);
                        $sheet->cell('N'.$row, $dt['Haccyuu_Qty']);
                        $row++;
                    }

                    $sheet->setColumnFormat(array(
                        'B' => 'yyyy/mm/dd',
                        'D' => '@',
                        'G' => 'yyyy/mm/dd',
                        'L' => 'yyyy/mm/dd',
                        'M' => 'yyyy/mm/dd'
                    ));
                });
            })->store('csv', storage_path('FlexSched/ProdBalance'));
        }

    	return $output_data;
    }

    private function ErrorsExcelFileCreate($data)
    {
        Excel::create("ZYPF5210_PMI_TS", function($excel) use($data)
		{
            $excel->sheet("errors", function($sheet) use($data)
            {
                $sheet->setHeight(1, 15);
                $sheet->cell('A1',"Haccyuu_No");
                $sheet->cell('B1',"Haccyuu_Hoban");
                $sheet->cell('C1',"Hinmoku_Code");
                $sheet->cell('D1',"Hinmoku_tekisuto");
                $sheet->cell('E1',"Hokanbasyo");
                $sheet->cell('F1',"MRPKanrisya");
                $sheet->cell('G1',"Haccyuu_Bi");
                $sheet->cell('H1',"Siiresaki_Code");
                $sheet->cell('I1',"Shiiresaki_Tekisuto");
                $sheet->cell('J1',"Haccyuu_Qty");
                $sheet->cell('K1',"Haccyuu_Zan_Qty");
                $sheet->cell('L1',"Toukei_kannrenn_nounyuu_Bi");
                $sheet->cell('M1',"Kaitou_Nouki");
                $sheet->cell('N1',"Kaitou_Jikoku");
                $sheet->cell('O1',"Kaitou_Qty");
                $sheet->cell('P1',"Tokuisaki_Code");
                $sheet->cell('Q1',"Tokuisaki_Mei");
                $sheet->cell('R1',"Tokuisaki_Nouki");

                $row = 2;
                foreach ($data as $key => $dt) {

                    $sheet->cell('A'.$row, $dt->Haccyuu_No);
                    $sheet->cell('B'.$row, $dt->Haccyuu_Hoban);
                    $sheet->cell('C'.$row, $dt->Hinmoku_Code);
                    $sheet->cell('D'.$row, $dt->Hinmoku_tekisuto);
                    $sheet->cell('E'.$row, $dt->Hokanbasyo);
                    $sheet->cell('F'.$row, $dt->MRPKanrisya);
                    $sheet->cell('G'.$row, $dt->Haccyuu_Bi);
                    $sheet->cell('H'.$row, $dt->Siiresaki_Code);
                    $sheet->cell('I'.$row, $dt->Shiiresaki_Tekisuto);
                    $sheet->cell('J'.$row, $dt->Haccyuu_Qty);
                    $sheet->cell('K'.$row, $dt->Haccyuu_Zan_Qty);
                    $sheet->cell('L'.$row, $dt->Toukei_kannrenn_nounyuu_Bi);
                    $sheet->cell('M'.$row, $dt->Kaitou_Nouki);
                    $sheet->cell('N'.$row, $dt->Kaitou_Jikoku);
                    $sheet->cell('O'.$row, $dt->Kaitou_Qty);
                    $sheet->cell('P'.$row, $dt->Tokuisaki_Code);
                    $sheet->cell('Q'.$row, $dt->Tokuisaki_Mei);
                    $sheet->cell('R'.$row, $dt->Tokuisaki_Nouki);
                    $row++;
                }
            });
		})->store('xls', storage_path('FlexSched/ProdBalance'));
    }
    // Prod Balance

    // global
    private function generateCSVFile($filename,$folder,$content)
    {
        $generated = false;

    	$path = storage_path().'/FlexSched'.'/'.$folder;

        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $file = $path."/".$filename.".csv";

    	$myfile = fopen($file, "w") or die("Unable to open file!");
		fwrite($myfile, $content);
		$generated = fclose($myfile);


        return $generated;
    }

    public function downloadFile(Request $req)
    {
        $myFile = storage_path().'/FlexSched//'.$req->folder.'/'.$req->filename;
    	return response()->download($myFile);//->deleteFileAfterSend(true);
    }

    private function checkIndexes($assoc_arr, $rawData)
    {
        $is_valid = true;
        if (is_array($assoc_arr)) {
            foreach ($assoc_arr as $key => $assoc) {
                $indx = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "", $assoc));
                if (!isset($rawData[$indx])) {
                    $is_valid = false;
                    break;
                    exit;
                }
            }
        }

        return $is_valid;
    }

    private function getFilePointerUTF8($target_file)
    {
        $current_locale = setlocale(LC_ALL, '0'); // Backup current locale.
    
        setlocale(LC_ALL, 'ja_JP.UTF-8');
    
        // Read the file content in SJIS-Win.
        $content = file_get_contents($target_file);
    
        // Convert file content to SJIS-Win.
        $content = mb_convert_encoding($content, "UTF-8", "SJIS-win");
    
        // Save the file as UTF-8 in a temp location.
        $fp = tmpfile();
        fwrite($fp, $content);
        rewind($fp);
    
        setlocale(LC_ALL, $current_locale); // Restore the backed-up locale.
    
        return $fp;
    }
}
