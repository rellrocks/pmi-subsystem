@extends('layouts.master')

@section('title')
    QC Database | Pricon Microelectronics, Inc.
@endsection

@push('css')
    {{-- <link rel="stylesheet" href="{{ asset(config('constants.PUBLIC_PATH').'assets/global/css/datatable-fixedheader.css') }}"> //Commented to show scrollbar-y -RHEGIE --}}
    <style type="text/css">
        .dataTables_scrollHeadInner{
            width:100% !important;
        }
        .dataTables_scrollHeadInner table{
            width:100% !important;
        }
        .modal-backdrop {
            z-index: -1;
        }
    </style>
@endpush

@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
        @if ($access->program_code == Config::get('constants.MODULE_CODE_PCKNGDB'))  <!-- Please update "2001" depending on the corresponding program_code -->
            @if ($access->read_write == "2")
            <?php $state = "disabled"; $readonly = "readonly"; ?>
            @endif
        @endif
    @endforeach
    
    <div class="page-content">
        <div class="row">
            <div class="col-md-6">
                <h3>Packing Inspection</h3>
            </div>
            <div class="col-md-6">
                <div class="btn-group pull-right">
                    <button type="button" class="btn green" id="btn_add">
                        <i class="fa fa-plus"></i> Add New
                    </button>
                    <button type="button" class="btn blue" id="btn_groupby">
                        <i class="fa fa-group"></i> Group By
                    </button>
                    <button type="button" class="btn red" id="btn_delete">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                    <button class="btn purple" id="btn_search">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>

        <hr>
        
        <div class="row">
            <div class="col-md-12" id="main_pane">
                <table class="table table-hover table-bordered table-striped" id="tbl_packing_inspection" style="font-size: 10px; white-space: nowrap;">
                    <thead>
                        <tr>
                            <td class="table-checkbox">
                                <input type="checkbox" class="group-checkable check_all" />
                            </td>
                            <td></td>
                            <td>Date Inspected</td>
                            <td>Shipment Date</td>
                            <td>Series Name</td>
                            <td>P.O. #</td>
                            <td>Packing Operator</td>
                            <td>Inspector</td>
                            <td>Packing Type</td>
                            <td>Unit Condition</td>
                            <td>Packing Code(per Series)</td>
                            <td>Carton #</td>
                            <td>Packing Code</td>
                            <td>Qty</td>
                            <td>Judgement</td>
                            <td>Remarks</td>
                        </tr>
                    </thead>
                    <tbody id="tbl_packing_inspection_body"></tbody>
                </table>
            </div>

            <div class="col-md-12" id="group_by_pane"></div>
        </div>

    </div>

    
    @include('includes.qcdb.packing_inspection_modal')
    @include('includes.modals')
    
@endsection

@push('script')
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        var getStampCodeURL = "{{ url('packinginspection/stamp-code') }}";
        var token = "{{ Session::token() }}";
        var getDataInspectedURL = "{{ url('/packinginspection/get-data-inspected') }}";
        var saveURL = "{{ url('/packinginspection/save') }}";
        var initdataURL = "{{url('/packinginspection/initdata')}}";
        var getRuncardURL = "{{ url('/packinginspection/get-runcard') }}";
        var getMODURL = "{{ url('/packinginspection/get-mod') }}";
        var deleteInspectionURL = "{{ url('/packinginspection/delete-inspection') }}";
        var deleteRuncardURL = "{{ url('/packinginspection/delete-runcard') }}";
        var deleteMODURL = "{{ url('/packinginspection/delete-mod') }}";
        var getPOdetailsURL = "{{ url('/packinginspection/po-details') }}";
        var current_user = "{{ Auth::user()->firstname }}";
        var searchPdfURL = "{{ url('/packinginspection/search-pdf') }}";
        var searchExcelURL = "{{ url('/packinginspection/search-excel') }}";
        var searchDataURL = "{{ url('/packinginspection/search-data') }}";
        var GroupByURL = "{{ url('/packinginspection/groupby-values') }}";
        var ReportDataCheckURL = "{{ url('/packinginspection/report-data-check') }}";
        var saveFormURL = "{{ url('/packinginspection/save-inspection') }}";
        var PDFGroupByReportURL = "";
    </script>
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/qcdb/packing_inspection.js').'?v='.date('YmdHis') }}" type="text/javascript"></script>
@endpush