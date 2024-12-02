<?php
namespace App\Http\Controllers\Nav;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; #Auth facade
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use App\Http\Requests;
use DB;
use Config;
use Carbon\Carbon;
use PDF;
use App;
use Excel;
use File;
class ExtractCSVFileController extends Controller
{
    protected $mysql;
    protected $mssql;
    protected $common;

    
    public function __construct()
    {
        $this->middleware('auth');
        $com = new CommonController;

        if (Auth::user() != null) {
            $this->mysql = $com->userDBcon(Auth::user()->productline,'wbs');
            $this->mssql = $com->userDBcon(Auth::user()->productline,'mssql');
            $this->common = $com->userDBcon(Auth::user()->productline,'common');


                          
        } else {
            return redirect('/');
        }
    }

     public function DataExtract()
    {
    	 $com = new CommonController;

        if(!$com->getAccessRights(Config::get('constants.MODULE_CODE_NAVCSV'), $userProgramAccess))
        {
            return redirect('/home');
        }
        else
        {
            return view('Nav.dataextract',['userProgramAccess' => $userProgramAccess]);
        }
    }



     public function ExportCSV(Request $req)
    {
        $date = carbon::now();
        $com = new CommonController;
        $date = date('Y-m-d');
        $sample = "__BOM_";
        $rawmats = "__RMReceipt_";
        $packinglist = "__PackingList_";
        $BOH = "__BOH_";
        $Class = "__Class_T_and_B_";
        $Dispatch = "__Dispatch_Qty_and_Inventory_Transfer_";
        $Actual = "__Actual_Shipping_Result_";
        $Withdrawal = "__Withdrawal_Result_Entry_";
        $plines = explode(",",$req->productline);
        $pertable = $req->pertable;
        $message = "Downloading success!";
        $error = "Downloading failed";

        foreach ($plines as $key => $pline) 
        {

            if($pline == "TS"){ 
             $path = "/home/administrator/Documents/DataExtractionpoint/NAV/TS_YPICS_Data";
            //  $path = "//var/www/html/pmi-subsystem/public/CSVFILE/TS_YPICS_Data";
           //    $path = "smb://192.168.3.196/dataextractionpoint/NAV/TS_YPICS_Data";
            }elseif ($pline == "CN") {
           //  $path =  "smb://192.168.3.196/dataextractionpoint/NAV/CN_YPICS_Data";
               //$path = "//var/www/html/pmi-subsystem/public/CSVFILE/CN_YPICS_Data";
            $path = "/home/administrator/Documents/DataExtractionpoint/NAV/CN_YPICS_Data";
            }else{
              //$path = "smb://192.168.3.196/dataextractionpoint/NAV/YF_YPICS_Data";
               //$path = "//var/www/html/pmi-subsystem/public/CSVFILE/YF_YPICS_Data";
             $path= "/home/administrator/Documents/DataExtractionpoint/NAV/YF_YPICS_Data";
            }

            if (file_exists($path)) {

                    // dd($from,$to);
                    //  dd(file_exists($path));
                if (strpos($pertable, 'Bom') !== false) 
                {

                    $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$sample.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {

                            $sheet->cell('A1', "PO_Number");
                            $sheet->cell('B1', 'Product_Code');
                            $sheet->cell('C1', "Product_Name");
                            $sheet->cell('D1', "PartCode");
                            $sheet->cell('E1', "PartName");
                            $sheet->cell('F1', "PartType");
                            $sheet->cell('G1', "RequiredQty");
                            $sheet->cell('H1', "UOM");
                            $sheet->cell('I1', "Location");
                            $sheet->cell('J1', "DrawNum");
                            $sheet->cell('K1', "Supplier");
                            $sheet->cell('L1', "WHS100");
                            $sheet->cell('M1', "WHS102");
                            $sheet->cell('N1', "InputDate");
                            // $sheet->cell('N1', "WHS102");

                        
                    
                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'ymd');
                        $to = $com->convertDate($req->to,'ymd');

                        $data = DB::connection($this->mssql)
                                ->select("SELECT r.SORDER as PO_Number, r.CODE as Product_Code, ha.NAME as Product_Name, hk.CODE as PartCode, 
                                                    h.NAME as PartName,
                                                    i.BUNR as PartType, 
                                                    hk.KVOL as RequiredQty, 
                                                    UPPER(h.TANI1) as UOM, 
                                                    x.RACKNO as Location, 
                                                    i.DRAWING_NUM as DrawNum, 
                                                    i.VENDOR as Supplier, 
                                                    x.WHS100 as WHS100, 
                                                    x.WHS102 as WHS102,
                        CAST(LEFT(r.INPUTDATE,6) as date) as InputDate
                                            FROM XRECE r
                                            LEFT JOIN XSLIP s ON r.SORDER = s.SEIBAN
                                            LEFT JOIN XHIKI hk ON s.PORDER = hk.PORDER
                                            LEFT JOIN XITEM i ON i.CODE = hk.CODE
                                            LEFT JOIN XHEAD h ON h.CODE = hk.CODE
                        LEFT JOIN XHEAD ha ON ha.CODE = r.CODE
                                            LEFT JOIN (SELECT z.CODE, 
                                                            ISNULL(z1.ZAIK,0) as WHS100, 
                                                            ISNULL(z2.ZAIK,0) as WHS102, 
                                                            z1.RACKNO FROM XZAIK z
                                                    LEFT JOIN XZAIK z1 ON z1.CODE = z.CODE AND z1.HOKAN = 'WHS100'
                                                    LEFT JOIN XZAIK z2 ON z2.CODE = z.CODE AND z2.HOKAN = 'WHS102'
                                                    WHERE z.RACKNO <> ''
                                                    GROUP BY z.CODE, z1.ZAIK, z2.ZAIK, z1.RACKNO
                                                    ) x ON x.CODE = hk.CODE
                        WHERE hk.CODE is not null AND CAST(LEFT(hk.INPUTDATE,6)as nvarchar) between '".$from."' AND '".$to."' 
                        GROUP BY
                            r.SORDER,
                            r.CODE, 
                            ha.NAME,
                            hk.CODE, 
                            h.NAME, 
                            i.VENDOR, 
                            hk.KVOL,
                            h.TANI1,
                            i.BUNR, 
                            i.DRAWING_NUM, 
                            x.WHS100, 
                            x.WHS102, 
                            x.RACKNO,
                            r.INPUTDATE
                        ORDER BY r.SORDER, CAST(LEFT(r.INPUTDATE,6) as date)");
            
                            foreach ($data as $key => $md) {
                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->PO_Number);
                                $sheet->cell('B'.$row, $md->Product_Code);
                                $sheet->cell('C'.$row, $md->Product_Name);
                                $sheet->cell('D'.$row, $md->PartCode);
                                $sheet->cell('E'.$row, $md->PartName);
                                $sheet->cell('F'.$row, $md->PartType);
                                $sheet->cell('G'.$row, $md->RequiredQty);
                                $sheet->cell('H'.$row, $md->UOM);
                                $sheet->cell('I'.$row, $md->Location);
                                $sheet->cell('J'.$row, $md->DrawNum);
                                $sheet->cell('K'.$row, $md->Supplier);
                                $sheet->cell('L'.$row, $md->WHS100);
                                $sheet->cell('M'.$row, $md->WHS102);
                                $sheet->cell('N'.$row, $md->InputDate);

                                $row++;
                            }  
                        });
                    })->store('csv',$path);
                }

                if(strpos($pertable, 'Raw_Mats') !== false)
                {

                    
                        $this->mssql = $com->userDBcon($pline,'mssql');

                        Excel::create($pline.$rawmats.$date, function($excel) use($req)
                        {

                            $excel->sheet('Report', function($sheet) use($req)
                            {

                                $sheet->cell('A1', "Item_Code");
                                $sheet->cell('B1', 'Item_Name');
                                $sheet->cell('C1', "Quantity");
                                $sheet->cell('D1', "UOM");
                                $sheet->cell('E1', "Price");
                                $sheet->cell('F1', "Currency");
                                $sheet->cell('G1', "Sales_Amount");
                                $sheet->cell('H1', "PartType");
                                $sheet->cell('I1', "Invoice_No");
                                $sheet->cell('J1', "DeliveryDate");
                                $sheet->cell('K1', "Input_By");
                                $sheet->cell('L1', "ClassType");
                                $sheet->cell('M1', "Supplier");

                            $row = 2;
                            $com = new CommonController;
                            $from = $com->convertDate($req->from,'Ymd');
                            $to = $com->convertDate($req->to,'Ymd');
                            // dd($from,$to);    
                            $data = DB::connection($this->mssql)
                                        ->select("
                                                SELECT s.CODE as Item_Code, h.NAME as Item_Name, s.JITU as Quantity, UPPER(h.TANI1) as UOM, s.APRICE as Price, e.CURRE as Currency,
                                                s.KOUNYUUGAKU as Sales_Amount, i.BUNR as PartType, s.INVOICE_NUM as Invoice_No, 
                                                CAST(LEFT(s.FDATE,8) as date) as DeliveryDate, s.INPUTUSER as Input_By, s.AKUBU as ClassType, s.VENDOR as Supplier
                                                FROM XSACT s 
                                                LEFT JOIN XHEAD h ON h.CODE = s.CODE
                                                LEFT JOIN XITEM i ON i.CODE = s.CODE
                                                LEFT JOIN XSECT e ON e.BUMO = s.VENDOR
                                                WHERE CAST(LEFT(s.FDATE,8)as nvarchar) between '".$from."' AND '".$to."' AND s.BUMO = 'PURH100' AND AKUBU = 'J'
                                                AND s.BUMO = 'PURH100' AND AKUBU = 'J'
                                        ");
                                                    
                                foreach ($data as $key => $md) {
                                    $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->Item_Code);
                                    $sheet->cell('B'.$row, $md->Item_Name);
                                    $sheet->cell('C'.$row, $md->Quantity);
                                    $sheet->cell('D'.$row, $md->UOM);
                                    $sheet->cell('E'.$row, $md->Price);
                                    $sheet->cell('F'.$row, $md->Currency);
                                    $sheet->cell('G'.$row, $md->Sales_Amount);
                                    $sheet->cell('H'.$row, $md->PartType);
                                    $sheet->cell('I'.$row, $md->Invoice_No);
                                    $sheet->cell('J'.$row, $md->DeliveryDate);
                                    $sheet->cell('K'.$row, $md->Input_By);
                                    $sheet->cell('L'.$row, $md->ClassType);
                                    $sheet->cell('M'.$row, $md->Supplier);
                    
                                    $row++;
                                }  
                        });
                    })->store('csv',$path); 
                }


                if(strpos($pertable, 'Packinglist') !== false)
                {     
                $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$packinglist.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {

                            $sheet->cell('A1', "PONumber");
                            $sheet->cell('B1', 'Product_Code');
                            $sheet->cell('C1', "Product_Name");
                            $sheet->cell('D1', "Quantity");
                            $sheet->cell('E1', "Price");
                            $sheet->cell('F1', "Sales_Amount");
                            $sheet->cell('G1', "Entry_Date");
                            $sheet->cell('H1', "Actual_Ship_Result_Date");
                            $sheet->cell('I1', "Packing_List_No");
                            $sheet->cell('J1', "PreparedBy");
                    
                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'ymd');
                        $to = $com->convertDate($req->to,'ymd');
                        $data = DB::connection($this->mssql)
                                            ->select("
                                                    SELECT r.SORDER as PONumber, r.CODE as Product_Code, h.NAME as Product_Name,
                                                    r.JITU as Quantity, r.APRICE as Price, r.URIAGEGAKU as Sales_Amount,
                                                    CAST(LEFT(r.IDATE,8) as date) as Entry_Date, CAST(LEFT(r.FDATE,8) as date) as Actual_Ship_Result_Date, r.PACKING_LIST as Packing_List_No, r.INPUTUSER as PreparedBy
                                                    FROM XRACT r 
                                                    LEFT JOIN XHEAD h ON h.CODE = r.CODE
                                                    WHERE CAST(LEFT(r.INPUTDATE,6)as nvarchar) between '".$from."' AND '".$to."' 
                                                    ");

                            foreach ($data as $key => $md) {
                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->PONumber);
                                $sheet->cell('B'.$row, $md->Product_Code);
                                $sheet->cell('C'.$row, $md->Product_Name);
                                $sheet->cell('D'.$row, $md->Quantity);
                                $sheet->cell('E'.$row, $md->Price);
                                $sheet->cell('F'.$row, $md->Sales_Amount);
                                $sheet->cell('G'.$row, $md->Entry_Date);
                                $sheet->cell('H'.$row, $md->Actual_Ship_Result_Date);
                                $sheet->cell('I'.$row, $md->Packing_List_No);
                                $sheet->cell('J'.$row, $md->PreparedBy);
                            

                                $row++;
                            }  
                        });
                    })->store('csv',$path); 
                }

                if(strpos($pertable,'Consumption')!== false)
                {
                    $path .= "/Consumption";    
                    $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$BOH.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {
                        $sheet->setColumnFormat(array(
                        'F' => '0.0000',
                        ));  
                        $sheet->cell('A1', "Part_Code");
                        $sheet->cell('B1', 'Part_Name');
                        $sheet->cell('C1', "BOH");
                        $sheet->cell('D1', "CurrentInventory");
                        $sheet->cell('E1', "UOM");
                        $sheet->cell('F1', "UnitCost");
                        $sheet->cell('G1', "PartType");

                    
                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'ymd');
                        $to = $com->convertDate($req->to,'ymd');

                        $data = DB::connection($this->mssql)
                                    ->select("SELECT z.CODE as Part_Code ,h.NAME as Part_Name, z.ZAIKTANA as BOH,
                                    ISNULL(z1.ZAIK,0) + ISNULL(z2.ZAIK,0) as CurrentInventory, UPPER(h.TANI1) as UOM, ISNULL(t.SPRICE,0) as UnitCost , i.BUNR as PartType
                                    FROM XZAIK z 
                                    LEFT JOIN XZAIK z1 ON z1.CODE = z.CODE AND z1.HOKAN = 'WHS100'
                                    LEFT JOIN XZAIK z2 ON z2.CODE = z.CODE AND z2.HOKAN = 'WHS102'
                                    LEFT JOIN XHEAD h ON h.CODE = z.CODE
                                    LEFT JOIN XITEM i ON i.CODE = z.CODE
                                    LEFT JOIN XTANK t ON t.CODE = z.CODE
                                    WHERE z.RACKNO <> '' AND z.HOKAN IN('WHS100', 'WHS102')
                                    GROUP BY z.CODE, z.ZAIKTANA, h.NAME, z1.ZAIK, z2.ZAIK, h.TANI1, i.BUNR,t.SPRICE
                                    ORDER BY z.CODE 
                                                ");
            
                            foreach ($data as $key => $md) {
                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->Part_Code);
                                $sheet->cell('B'.$row, $md->Part_Name);
                                $sheet->cell('C'.$row, $md->BOH);
                                $sheet->cell('D'.$row, $md->CurrentInventory);
                                $sheet->cell('E'.$row, $md->UOM);
                                $sheet->cell('F'.$row, $md->UnitCost);
                                $sheet->cell('G'.$row, $md->PartType);


                            
                                $row++;
                        }  
                        });
                    })->store('csv',$path);

                    // // dean
                    
                    $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$Class.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {
                        $sheet->setColumnFormat(array(
                        'E' => '0.0000',
                        ));                          

                        $sheet->cell('A1', "Item_Code");
                        $sheet->cell('B1', 'Item_Description');
                        $sheet->cell('C1', "Quantity");
                        $sheet->cell('D1', "UOM");
                        $sheet->cell('E1', "UnitCost");
                        $sheet->cell('F1', "Sales_Amount");
                        $sheet->cell('G1', "ClassType");
                        $sheet->cell('H1', "DeliveryDate");
                        $sheet->cell('I1', "InputBy");
                        $sheet->cell('J1', "Po_Number");

                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'Ymd');
                        $to = $com->convertDate($req->to,'Ymd');

                        $data = DB::connection($this->mssql)
                                    ->select("SELECT s.CODE as Item_Code, h.NAME as Item_Description, 
                                        ISNULL(s.JITU,0) as Quantity, UPPER(h.TANI1) as UOM, s.APRICE as UnitCost, s.KOUNYUUGAKU as Sales_Amount,
                                        s.AKUBU as ClassType,
                                        CAST(LEFT(s.FDATE,8) as date) as DeliveryDate,
                                        s.INPUTUSER as InputBy, s.SEIBAN as Po_Number FROM XSACT s
                                        LEFT JOIN XHEAD h ON h.CODE = s.CODE
                                        WHERE CAST(LEFT(s.FDATE,8 )as nvarchar) between '".$from."' AND '".$to."' 
                                        AND s.BUMO = 'PURH100' AND  AKUBU IN('T','B')
                                        ORDER BY CASE WHEN 
                                        s.AKUBU ='T' THEN '1'
                                        WHEN s.AKUBU = 'B' THEN '2'
                                        END , s.CODE, s.FDATE
                                        ");
            
                            foreach ($data as $key => $md) {
                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->Item_Code);
                                $sheet->cell('B'.$row, $md->Item_Description);
                                $sheet->cell('C'.$row, $md->Quantity);
                                $sheet->cell('D'.$row, $md->UOM);
                                $sheet->cell('E'.$row, $md->UnitCost);
                                $sheet->cell('F'.$row, $md->Sales_Amount);
                                $sheet->cell('G'.$row, $md->ClassType);
                                $sheet->cell('H'.$row, $md->DeliveryDate);
                                $sheet->cell('I'.$row, $md->InputBy);
                                $sheet->cell('J'.$row, $md->Po_Number);
                            
                                $row++;
                            }  
                        });
                    })->store('csv',$path);

                    // //dean2
                    $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$Dispatch.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {
                        $sheet->setColumnFormat(array(
                        'E' => '0.0000',
                        ));  
                        $sheet->cell('A1', "Item_Code");
                        $sheet->cell('B1', 'Item_Description');
                        $sheet->cell('C1', "Qty");
                        $sheet->cell('D1', "UOM");
                        $sheet->cell('E1', "UnitCost");
                        $sheet->cell('F1', "PartType");
                        $sheet->cell('G1', "Transfer_From");
                        $sheet->cell('H1', "Transfer_To");
                        $sheet->cell('I1', "ClassType");
                        $sheet->cell('J1', "ActualIssueDate");
                        $sheet->cell('K1', "Po_Number");
                        

                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'Ymd');
                        $to = $com->convertDate($req->to,'Ymd');

                        $data = DB::connection($this->mssql)
                                    ->select("SELECT p.CODE as Item_Code, h.NAME as Item_Description, 
                                        ISNULL(SUM(p.JITU0),0) as Qty, UPPER(h.TANI1) as UOM, ISNULL(t.SPRICE,0) as UnitCost, i.BUNR as PartType, p.MOTO as Transfer_From, 
                                        p.HOKAN as Transfer_To, p.KUBU as ClassType,
                                        CAST(LEFT(p.FDATE,8) as date) as ActualIssueDate, p.SEIBAN  as Po_Number
                                        FROM XPACT p  
                                        LEFT JOIN XHEAD h ON h.CODE =p.CODE
                                        LEFT JOIN XITEM i ON i.CODE = p.CODE
                                        LEFT JOIN XTANK t ON t.CODE = p.CODE
                                        WHERE CAST(LEFT(p.FDATE,8)as nvarchar) between '".$from."' AND '".$to."'  AND p.KUBU IN('H','Z')
                                        GROUP BY CAST(LEFT(p.FDATE,8) as date), p.CODE, h.NAME, h.TANI1,t.SPRICE, i.BUNR, p.MOTO, p.HOKAN, p.KUBU, p.SEIBAN
                                        ORDER BY p.KUBU, p.CODE, CAST(LEFT(p.FDATE,8) as date)
                                        ");
            
                            foreach ($data as $key => $md) {
                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->Item_Code);
                                $sheet->cell('B'.$row, $md->Item_Description);
                                $sheet->cell('C'.$row, $md->Qty);
                                $sheet->cell('D'.$row, $md->UOM);
                                $sheet->cell('E'.$row, $md->UnitCost);
                                $sheet->cell('F'.$row, $md->PartType);
                                $sheet->cell('G'.$row, $md->Transfer_From);
                                $sheet->cell('H'.$row, $md->Transfer_To);
                                $sheet->cell('I'.$row, $md->ClassType);
                                $sheet->cell('J'.$row, $md->ActualIssueDate);
                                $sheet->cell('K'.$row, $md->Po_Number);

                            
                                $row++;
                            }  
                        });
                    })->store('csv',$path);

                    // //dean3
                    $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$Actual.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {
                            $sheet->setColumnFormat(array(
                        'F1' => '0.0000',
                        ));  
                        $sheet->cell('A1', "PO_Number");
                        $sheet->cell('B1', 'Item_Code');
                        $sheet->cell('C1', "Item_Description");
                        $sheet->cell('D1', "ShipOUt_Qty_PerPO");
                        $sheet->cell('E1', "UOM");
                        $sheet->cell('F1', "UnitCost");
                        $sheet->cell('G1', "Selling_Price");
                        $sheet->cell('H1', "Part_Type");
                        $sheet->cell('I1', "Actual_ShippingDate");
                        $sheet->cell('J1', "Packing_List_No");
                        
                        
                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'Ymd');
                        $to = $com->convertDate($req->to,'Ymd');

                        $data = DB::connection($this->mssql)
                                    ->select("SELECT  r.SORDER as PO_Number, r.CODE as Item_Code, h.NAME as Item_Description, ISNULL(r.JITU,0) as ShipOUt_Qty_PerPO, UPPER(h.TANI1) as UOM,
                                        CASE
                                        WHEN ISNULL(t.SPRICE,0) > 0 THEN ISNULL(t.SPRICE,0) 
                                        WHEN ISNULL(b.PRICE,0) > 0 THEN ISNULL(b.PRICE,0)
                                        ELSE ISNULL (t.SPRICE,0)
                                        END AS UnitCost, r.APRICE as Selling_Price
                                        ,i.BUNR as Part_Type, CAST(LEFT(r.FDATE,8) as date) as Actual_ShippingDate, r.PACKING_LIST as Packing_List_No
                                        FROM XRACT r 
                                        LEFT JOIN XHEAD h ON h.CODE = r.CODE
                                        LEFT JOIN XITEM i ON i.CODE = r.CODE
                                        LEFT JOIN XTANK t on t.CODE = r.CODE
                                        LEFT JOIN XBAIK b on b.CODE = r.CODE
                                        WHERE CAST(LEFT(r.FDATE,8)as nvarchar) between '".$from."' AND '".$to."' 
                                        GROUP BY r.SORDER, r.CODE, h.NAME, r.JITU, t.SPRICE, b.PRICE, r.APRICE, i.BUNR, CAST(LEFT(r.FDATE,8) as date), r.PACKING_LIST, h.TANI1
                                        ORDER BY CAST(LEFT(r.FDATE,8) as date), r.SORDER, r.CODE
                                    ");
            
                            foreach ($data as $key => $md) {
                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->PO_Number);
                                $sheet->cell('B'.$row, $md->Item_Code);
                                $sheet->cell('C'.$row, $md->Item_Description); 
                                $sheet->cell('D'.$row, $md->ShipOUt_Qty_PerPO);
                                $sheet->cell('E'.$row,$md->UOM); 
                                $sheet->cell('F'.$row,$md->UnitCost); 
                                $sheet->cell('G'.$row, $md->Selling_Price);
                                $sheet->cell('H'.$row, $md->Part_Type);
                                $sheet->cell('I'.$row, $md->Actual_ShippingDate);
                                $sheet->cell('J'.$row, $md->Packing_List_No);
                            
                                $row++;
                            }  
                        });
                    })->store('csv',$path);
                    //dean4
                    $this->mssql = $com->userDBcon($pline,'mssql');
                    Excel::create($pline.$Withdrawal.$date, function($excel) use($req)
                    {
                        $excel->sheet('Report', function($sheet) use($req)
                        {
                        // $sheet->setCellValue("E1",sprintf("%0.4f",'UnitCost'),true)
                        // ->getStyle()
                        // ->getNumberFormat()
                        // ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                        // $objPHPExcel->getActiveSheet()->getStyle('E1')
                        // ->getNumberFormat()
                        // ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);   
                        $sheet->setColumnFormat(array(
                        'E' => '0.0000',
                        'F' => '0.0000',
                        ));  
                        $sheet->cell('A1', "Item_Code");
                        $sheet->cell('B1', "Item_Description");
                        $sheet->cell('C1', "ShipOut_Qty_PerBOM");
                        $sheet->cell('D1', "UOM");
                        $sheet->cell('E1', "UnitCost");
                        $sheet->cell('F1', "Withdrawal_Unit_Cost");
                        $sheet->cell('G1', "Part_Type");
                        $sheet->cell('H1', "Withdrawal_Date");
                        $sheet->cell('I1', "PO_Number");
                        $sheet->cell('J1', "Product_Code");    
                        $sheet->cell('K1', "Product_Name");
                        
                        $row = 2;
                        $com = new CommonController;
                        $from = $com->convertDate($req->from,'Ymd');
                        $to = $com->convertDate($req->to,'Ymd');

                        $data = DB::connection($this->mssql)
                                    ->select("SELECT h.CODE as Item_Code, h1.NAME as Item_Description, SUM(h.JITU) as ShipOut_Qty_PerBOM, UPPER(h1.TANI1) as UOM,

                                    CASE
                                    WHEN ISNULL(t.SPRICE,0) > 0 THEN ISNULL(t.SPRICE,0) 
                                    WHEN ISNULL(b.PRICE,0) > 0 THEN ISNULL(b.PRICE,0)
                                    ELSE ISNULL (t.SPRICE,0)
                                    END AS UnitCost,h.HPRICE as Withdrawal_Unit_Cost ,i.BUNR as Part_Type, CAST(LEFT(h.FDATE,8) as date) as Withdrawal_Date, 
                                    ISNULL(s.SEIBAN,'') as PO_Number, h.OYACODE as Product_Code, h2.NAME as Product_Name

                            FROM XHACT h
                                LEFT JOIN (SELECT SEIBAN, CODE FROM XSACT 
                                            WHERE CAST(LEFT(FDATE,8) as nvarchar) between '".$from."' AND '".$to."' AND AKUBU = 'J'
                                            GROUP BY SEIBAN, CODE)s ON h.OYACODE = s.CODE
                                LEFT JOIN XHEAD h1 ON h1.CODE = h.CODE
                                LEFT JOIN XHEAD h2 ON h2.CODE = h.OYACODE
                                LEFT JOIN XITEM i ON i.CODE = h.CODE
                                LEFT JOIN XTANK t ON t.CODE = h.CODE
                                LEFT JOIN XBAIK b ON b.CODE = h.CODE
                            WHERE CAST(LEFT(h.FDATE,8)as nvarchar) between '".$from."' AND '".$to."'
                            GROUP BY h.CODE, h1.NAME, h1.TANI1, CAST(LEFT(h.FDATE,8) as date), i.BUNR, s.SEIBAN, h.OYACODE, h2.NAME, b.PRICE,t.SPRICE,h.HPRICE
                            ");
                            
                            foreach ($data as $key => $md) {
                            // $unitcost = number_format($md->UnitCost, 4, '.', '');
                            // $withdrawal_unit_cost = number_format($md->Withdrawal_Unit_Cost, 4, '.', '');

                                $sheet->setHeight($row, 20);
                                $sheet->cell('A'.$row, $md->Item_Code);
                                $sheet->cell('B'.$row, $md->Item_Description);
                                $sheet->cell('C'.$row, $md->ShipOut_Qty_PerBOM);
                                $sheet->cell('D'.$row, $md->UOM);
                                // $sheet->cell(sprintf('E'.$row, $md->UnitCost,"%0.4f"),true)
                                // ->getStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                                $sheet->cell('E'.$row, $md->UnitCost);
                                $sheet->cell('F'.$row, $md->Withdrawal_Unit_Cost);
                                $sheet->cell('G'.$row, $md->Part_Type);
                                $sheet->cell('H'.$row, $md->Withdrawal_Date);
                                $sheet->cell('I'.$row, $md->PO_Number);
                                $sheet->cell('J'.$row, $md->Product_Code);
                                $sheet->cell('K'.$row, $md->Product_Name);
                            
                            
                                $row++;
                            }  
                            
                        });
                
                    })->store('csv',$path);

                } 

            }else{
              return redirect('/dataextract')->with('failed','true');
            }
        }
        return redirect('/dataextract')->with('success','true');
    }
    public function TimeSetting(Request $req){

        $data = DB::connection($this->common)->table('time_setter')
                    ->insert([
                        'hour'=>$req->hour,
                        'minute'=>$req->minute,
                        'am_pm'=>$req->am_pm   
                    ]);
        return response()->json($data); 
    }
    public function GetTime(Request $req){
        $data = DB::connection($this->common)
                    ->select("SELECT id,hour,minute,am_pm FROM time_setter");

        return response()->json($data); 
    }
    public function UpdateTime(Request $req){
        $data = DB::connection($this->common)->table('time_setter')
                    ->where('id',$req->id)
                    ->update([
                            'hour'=> $req->hour,
                            'minute'=> $req->minute,
                            'am_pm'=> $req->am_pm,
                    ]);
        return response()->json($data); 
    }
}
