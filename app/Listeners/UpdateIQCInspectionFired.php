<?php

namespace App\Listeners;

use App\Events\UpdateIQCInspection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;

class UpdateIQCInspectionFired
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UpdateIQCInspection  $event
     * @return void
     */
    public function handle(UpdateIQCInspection $event)
    {
        DB::connection($event->con)->table('tbl_wbs_inventory')
            ->where('invoice_no','like','PPS%')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_inventory')
            ->whereIn('iqc_status',[0,3])
            ->where('judgement','Accepted')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_inventory')
            ->whereIn('iqc_status',[0,3])
            ->where('judgement','Rejected')
            ->update([
                'iqc_status' => 2,
                'for_kitting' => 0
            ]);

        DB::connection($event->con)->table('tbl_wbs_inventory')
            ->whereIn('iqc_status',[0,4])
            ->where('judgement','Rejected')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_inventory')
            ->whereIn('iqc_status',[0,4])
            ->where('judgement','Accepted')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_material_receiving_batch')
            ->where('invoice_no','like','PPS%')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_material_receiving_batch')
            ->whereIn('iqc_status',[0,3])
            ->where('judgement','Accepted')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_material_receiving_batch')
            ->whereIn('iqc_status',[0,3])
            ->where('judgement','Rejected')
            ->update([
                'iqc_status' => 2,
                'for_kitting' => 0
            ]);

        DB::connection($event->con)->table('tbl_wbs_local_receiving_batch')
            ->whereIn('iqc_status',[0,3])
            ->where('judgement','Accepted')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_local_receiving_batch')
            ->whereIn('iqc_status',[0,3])
            ->where('judgement','Rejected')
            ->update([
                'iqc_status' => 2,
                'for_kitting' => 0
            ]);







        DB::connection($event->con)->table('tbl_wbs_material_receiving_batch')
            ->whereIn('iqc_status',[0,4])
            ->where('judgement','Accepted')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_material_receiving_batch')
            ->whereIn('iqc_status',[0,4])
            ->where('judgement','Rejected')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_local_receiving_batch')
            ->whereIn('iqc_status',[0,4])
            ->where('judgement','Accepted')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        DB::connection($event->con)->table('tbl_wbs_local_receiving_batch')
            ->whereIn('iqc_status',[0,4])
            ->where('judgement','Rejected')
            ->update([
                'iqc_status' => 1,
                'for_kitting' => 1
            ]);

        \Log::info('IQC Inspection Updated at '.date('Y-m-d g:i:s a'));
    }
}
