@extends('layouts.master')

@section('title')
    OQC Inventory | Pricon Microelectronics, Inc.
@endsection

@push('css')
<style>
    .form-horizontal .radio, .form-horizontal .checkbox, .form-horizontal .radio-inline, .form-horizontal .checkbox-inline {
        padding-top: 2px;
        margin-top: 0;
        margin-bottom: 0;
    }
    .form-horizontal .control-label {
        padding-top: 3px;
        margin-bottom: 0;
        text-align: right;
    }
    td {
        white-space: nowrap;
    }
    td.lot {
        white-space: normal;
    }
    table thead tr td {
        text-align: center;
    }
</style>
@endpush

@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
        @if ($access->program_code == Config::get('constants.MODULE_CODE_OQCINV'))  <!-- Please update "2001" depending on the corresponding program_code -->
            @if ($access->read_write == "2")
            <?php $state = "disabled"; $readonly = "readonly"; ?>
            @endif
        @endif
    @endforeach

    <div class="page-content">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right">
                    <a href="javascript:;" class="btn green" id="btn_addnew" >
                        <i class="fa fa-plus"></i> Add New
                    </a>
                    <!-- <button type="button" class="btn blue" id="btn_groupby">
                        <i class="fa fa-group"></i> Group By
                    </button> -->
                    <button type="button" class="btn red" id="btn_delete" > {{-- @if($is_supervisor == 0) {{ 'disabled' }}@endif --}}
                        <i class="fa fa-trash"></i> Delete
                    </button>
                    <a href="javascript:;" class="btn purple" id="btn_search">
                        <i class="fa fa-search"></i> Search
                    </a>
                </div>
            </div>
        </div>
        <hr>
        
        <div class="row">
            <div class="col-md-12" id="main_pane">

                <table class="table table-hover table-bordered table-striped table-condensed" id="tbl_oqc_inventory" style="font-size: 11px;">
                    <thead>
                        <tr>
                            <td class="table-checkbox">
                                <input type="checkbox" class="group-checkable" />
                            </td>
                            <td width="5%"></td>
                            <td>Inventory Date</td>
                            <td>Lot App. Date</td>
                            <td>Lot App. Time</td>
                            <td>P.O. No.</td>
                            <td>Series Name</td>
                            <td>Quantity</td>
                            <td>Total No. of Lots</td>
                            <td>Updated By</td>
                            <td>Update Date</td>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="col-md-12" id="group_by_pane"></div>
        </div>

    </div>

    @include('includes.qcdb.oqc_inventory-modal')
    @include('includes.modals')

@endsection

@push('script')

<script type="text/javascript">
    var token = "{{ Session::token() }}";
    var author = "{{ Auth::user()->firstname }}";
    var OQCInventoryDataTableURL = "{{ url('/oqc-inventory-data') }}";
    var PODetailsURL = "{{ url('/oqc-inventory-po-details') }}";
    var SaveInventorysURL = "{{ url('/oqc-inventory-save') }}";
    var DeleteInventoryURL = "{{ url('/oqc-inventory-delete') }}";
    var PDFReportURL = "{{ url('/oqc-inventory-pdf') }}";
    var ExcelReportURL = "{{ url('/oqc-inventory-excel') }}";

    var checkDataReport = "{{ url('/oqc-inventory-report-check') }}";
    
</script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/qcdb/oqc_inventory.js') }}" type="text/javascript"></script>
@endpush
