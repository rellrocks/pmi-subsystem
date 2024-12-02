<?php

namespace App\Http\Controllers\WBS;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Config;
use Datatables;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class WBSInventoryController extends Controller
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
            $this->mysql = $this->com->userDBcon(Auth::user()->productline, 'wbs');
            $this->mssql = $this->com->userDBcon(Auth::user()->productline, 'mssql');
            $this->common = $this->com->userDBcon(Auth::user()->productline, 'common');
        } else {
            return redirect('/');
        }
    }

    public function index()
    {
        $pgcode = Config::get('constants.MODULE_WBS_INV');

        if (!$this->com->getAccessRights($pgcode, $userProgramAccess)) {
            return redirect('/home');
        } else {
            # Render WBS Page.
            return view('wbs.wbsinventory', [
                            'userProgramAccess' => $userProgramAccess,
                            'pgcode' => $pgcode,
                            'pgaccess' => $this->com->getPgAccess($pgcode)
                        ]);
        }
    }

    public function inventory_list(Request $req)
    {
        $inv = DB::connection($this->mysql)->table('tbl_wbs_inventory')
            ->orderBy('received_date', 'desc')
            ->where('deleted', 0)
            ->select([
                'id',
                'wbs_mr_id',
                'invoice_no',
                'item',
                'item_desc',
                'qty',
                'lot_no',
                'location',
                'supplier',
                'not_for_iqc',
                'iqc_status',
                'judgement',
                'create_user',
                'received_date',
                'update_user',
                'updated_at',
                'mat_batch_id',
                'loc_batch_id',
            ]);
        return Datatables::of($inv)
            ->editColumn('id', function ($data) {
                return $data->id;
            })
            ->addColumn('action', function ($data) {
                return '<button class="btn btn-sm btn-primary btn_edit" data-id="' . $data->id . '"
                            data-wbs_mr_id="' . $data->wbs_mr_id . '"
                            data-invoice_no="' . $data->invoice_no . '"
                            data-item="' . $data->item . '"
                            data-item_desc="' . $data->item_desc . '"
                            data-qty="' . $data->qty . '"
                            data-lot_no="' . $data->lot_no . '"
                            data-location="' . $data->location . '"
                            data-received_date="' . $data->received_date . '"
                            data-supplier="' . $data->supplier . '"
                            data-not_for_iqc="' . $data->not_for_iqc . '"
                            data-iqc_status="' . $data->iqc_status . '"
                            data-judgement="' . $data->judgement . '"
                            data-mat_batch_id="' . $data->mat_batch_id . '"
                            data-loc_batch_id="' . $data->loc_batch_id . '">
                                <i class="fa fa-edit"></i>
                        </button>';
            })->make(true);
    }
    public function inventory_search(Request $req)
    {
        $wbs_mr_id = $req->s_wbs_mr_id;
        $invoice_no = $req->s_invoice_no;
        $iqc_status = $req->s_iqc_status;
        $item = $req->s_item;
        $lot_no = $req->s_lot_no;
        $from = $req->s_from;
        $to = $req->s_to;

        $inv = DB::connection($this->mysql)->table('tbl_wbs_inventory');
        $inv->orderBy('received_date', 'desc');
        $inv->where('deleted', 0);
        if ($wbs_mr_id != "") {
            $inv->where('wbs_mr_id', $wbs_mr_id);
        }
        if ($invoice_no != "") {
            $inv->where('invoice_no', $invoice_no);
        }
        if ($iqc_status != "") {
            $inv->where('iqc_status', $iqc_status);
        }
        if ($lot_no != "") {
            $inv->where('lot_no', $lot_no);
        }
        if ($from != "" && $to == "") {
            $inv->where('received_date', $from);
        }
        if ($from == "" && $to != "") {
            $inv->where('received_date', $to);
        }
        if ($from != "" && $to != "") {
            $inv->whereBetween('received_date', [$from,$to]);
        }
        $inv->select([
                'id',
                'wbs_mr_id',
                'invoice_no',
                'item',
                'item_desc',
                'qty',
                'lot_no',
                'location',
                'supplier',
                'not_for_iqc',
                'iqc_status',
                'judgement',
                'create_user',
                'received_date',
                'update_user',
                'updated_at',
                'mat_batch_id',
                'loc_batch_id',
            ]);
        $result = $inv->get();
        return response()->json($result);
        // return Datatables::of($inv)
        //     ->editColumn('id', function ($data) {
        //         return $data->id;
        //     })
        //     ->addColumn('action', function ($data) {
        //         return '<button class="btn btn-sm btn-primary btn_edit" data-id="' . $data->id . '"
        //             data-wbs_mr_id="' . $data->wbs_mr_id . '"
        //             data-invoice_no="' . $data->invoice_no . '"
        //             data-item="' . $data->item . '"
        //             data-item_desc="' . $data->item_desc . '"
        //             data-qty="' . $data->qty . '"
        //             data-lot_no="' . $data->lot_no . '"
        //             data-location="' . $data->location . '"
        //             data-received_date="' . $data->received_date . '"
        //             data-supplier="' . $data->supplier . '"
        //             data-not_for_iqc="' . $data->not_for_iqc . '"
        //             data-iqc_status="' . $data->iqc_status . '"
        //             data-judgement="' . $data->judgement . '"
        //             data-mat_batch_id="' . $data->mat_batch_id . '"
        //             data-loc_batch_id="' . $data->loc_batch_id . '">
        //                 <i class="fa fa-edit"></i>
        //             </button>';
        //     })->make(true);
    }

    public function deleteselected(Request $req)
    {
        $data = [
            'msg' => "Deleting failed.",
            'status' => 'failed',
        ];
        foreach ($req->id as $key => $id) {
            $deleted = DB::connection($this->mysql)->table('tbl_wbs_inventory')
                ->where('id', $id)
                ->update(['deleted' => 1]);

            if ($deleted) {
                $data = [
                    'msg' => "Data were successfully deleted.",
                    'status' => 'success',
                ];
            }
        }

        return $data;
    }

    public function savedata(Request $req)
    {
        $result = "";
        $NFI = 0;
        if (isset($req->id)) {
            $NFI = (isset($req->nr)) ? 1 : 0;
            $UP = DB::connection($this->mysql)
                ->table('tbl_wbs_inventory')
                ->where('id', $req->id)
                ->update([
                    'item' => $req->item,
                    'item_desc' => $req->item_desc,
                    'lot_no' => $req->lot_no,
                    'qty' => $req->qty,
                    'not_for_iqc' => $NFI,
                    'location' => $req->location,
                    'supplier' => $req->supplier,
                    'iqc_status' => $req->status,
                    'received_date' => $this->com->convertDate($req->received_date, 'Y-m-d'),
                    'update_user' => Auth::user()->user_id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
            $result = "Updated";
        }
        return response()->json($result);
    }

    public function inventory_excel()
    {
        $dt = Carbon::now();
        $date = $dt->format('m-d-y');

        $com_info = $this->com->getCompanyInfo();

        $data = DB::connection($this->mysql)->table('tbl_wbs_inventory')
            ->select(
                'wbs_mr_id',
                'item',
                'item_desc',
                'qty',
                'lot_no',
                'location',
                'supplier',
                'iqc_status',
                'received_date'
            )->get();

        // return dd($data);

        Excel::create('WBS_Inventory_List_' . $date, function ($excel) use ($com_info, $data) {
            $excel->sheet('Inventory', function ($sheet) use ($com_info, $data) {
                $sheet->setHeight(1, 15);
                $sheet->mergeCells('A1:J1');
                $sheet->cells('A1:J1', function ($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A1', $com_info['name']);

                $sheet->setHeight(2, 15);
                $sheet->mergeCells('A2:J2');
                $sheet->cells('A2:J2', function ($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A2', $com_info['address']);

                $sheet->setHeight(4, 20);
                $sheet->mergeCells('A4:J4');
                $sheet->cells('A4:J4', function ($cells) {
                    $cells->setAlignment('center');
                    $cells->setFont([
                        'family' => 'Calibri',
                        'size' => '14',
                        'bold' => true,
                        'underline' => true,
                    ]);
                });
                $sheet->cell('A4', "WBS INVENTORY LIST");

                $sheet->setHeight(6, 15);
                $sheet->cells('A6:J6', function ($cells) {
                    $cells->setFont([
                        'family' => 'Calibri',
                        'size' => '11',
                        'bold' => true,
                    ]);
                    // Set all borders (top, right, bottom, left)
                    $cells->setBorder('thick', 'thick', 'thick', 'thick');
                });
                $sheet->cell('A6', "");
                $sheet->cell('B6', "Control No.");
                $sheet->cell('C6', "Code");
                $sheet->cell('D6', "Description");
                $sheet->cell('E6', "Quantity");
                $sheet->cell('F6', "Lot No.");
                $sheet->cell('G6', "Location");
                $sheet->cell('H6', "Supplier");
                $sheet->cell('I6', "IQC Status");
                $sheet->cell('J6', "Received DAte");

                $row = 7;

                //return dd($com_info);

                $count = 1;
                $status = '';

                foreach ($data as $key => $wbs) {
                    $sheet->setHeight($row, 15);
                    $sheet->cell('A' . $row, $count);
                    $sheet->cell('B' . $row, $wbs->wbs_mr_id);
                    $sheet->cell('C' . $row, $wbs->item);
                    $sheet->cell('D' . $row, $wbs->item_desc);
                    $sheet->cell('E' . $row, $wbs->qty);
                    $sheet->cell('F' . $row, $wbs->lot_no);
                    $sheet->cell('G' . $row, $wbs->location);
                    $sheet->cell('H' . $row, $wbs->supplier);

                    switch ($wbs->iqc_status) {
                        case 0:
                            $status = 'Pending';
                            break;

                        case 1:
                            $status = 'Accepted';
                            break;

                        case 2:
                            $status = 'Rejected';
                            break;

                        case 3:
                            $status = 'On-going';
                            break;
                        case 4:
                            $status = 'Special Accept';
                              break;        

                    }

                    $sheet->cell('I' . $row, $status);
                    $sheet->cell('J' . $row, $this->com->convertDate($wbs->received_date, 'm/d/Y h:i A'));
                    $row++;
                    $count++;
                }

                $sheet->cells('A' . $row . ':J' . $row, function ($cells) {
                    $cells->setBorder('thick', 'thick', 'thick', 'thick');
                });
            });
        })->download('xls');
    }

    public function refresh_inventory()
    {
        DB::connection($this->mysql)
            ->select("update tbl_wbs_inventory
                    set for_kitting = 1
                    where iqc_status in(1,4)");

        DB::connection($this->mysql)
            ->select("update tbl_wbs_material_receiving_batch
                    set for_kitting = 1
                    where iqc_status in(1,4)");

        DB::connection($this->mysql)
            ->select("update tbl_wbs_local_receiving_batch
                    set for_kitting = 1
                    where iqc_status in(1,4)");


        return $data = [
            'msg' => 'Successfully updated.',
            'status' => 'success'
        ];
    }
}
