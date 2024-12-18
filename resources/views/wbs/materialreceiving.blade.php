<?php
/*******************************************************************************
    Copyright (c) Company Nam All rights reserved.

    FILE NAME: materialreceiving.blade.php
    MODULE NAME:  3006 : WBS - Material Receiving
    CREATED BY: AK.DELAROSA
    DATE CREATED: 2016.07.01
    REVISION HISTORY :

    VERSION     ROUND    DATE           PIC          DESCRIPTION
    100-00-01   1     2016.07.01    AK.DELAROSA      Initial Draft
    100-00-02   1     2016.07.05    MESPINOSA        Material Receving Implementation.
    200-00-01   1     2016.07.01    AK.DELAROSA      2ND VERSION
*******************************************************************************/
?>

@extends('layouts.master')

@section('title')
    WBS | Pricon Microelectronics, Inc.
@endsection

@push('css')
<style type="text/css">
    table.table-fixedheader {
        width: 100%;
    }
    table.table-fixedheader, table.table-fixedheader>thead, table.table-fixedheader>tbody, table.table-fixedheader>thead>tr, table.table-fixedheader>tbody>tr, table.table-fixedheader>thead>tr>td, table.table-fixedheader>tbody>td {
        display: block;
    }
    table.table-fixedheader>thead>tr:after, table.table-fixedheader>tbody>tr:after {
        content:' ';
        display: block;
        visibility: hidden;
        clear: both;
    }
    table.table-fixedheader>tbody {
        overflow-y: scroll;
        height: 200px;
    }
    table.table-fixedheader>thead {
        overflow-y: scroll;
    }
    table.table-fixedheader>thead::-webkit-scrollbar {
        background-color: inherit;
    }


    table.table-fixedheader>thead>tr>td:after, table.table-fixedheader>tbody>tr>td:after {
        content:' ';
        display: table-cell;
        visibility: hidden;
        clear: both;
    }

    table.table-fixedheader>thead tr td, table.table-fixedheader>tbody tr td {
        float: left;
        word-wrap:break-word;
        height: 40px;
    }
</style>
@endpush

@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
        @if ($access->program_code == Config::get('constants.MODULE_CODE_WBS'))  <!-- Please update "2001" depending on the corresponding program_code -->
            @if ($access->read_write == "2")
                <?php $state = "disabled"; $readonly = "readonly"; ?>
            @endif
        @endif
    @endforeach

    <div class="page-content">

        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                @include('includes.message-block')
                <div class="portlet box blue" >
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-navicon"></i>  WBS Material Receiving
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row" id="mat_info">

                            <div class="col-md-5">
                                <div class="form-group row">
                                    <label class="control-label col-md-3">Receiving No.</label>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control clear clearinv input-sm" id="receivingno" name="receivingno" />
                                            <span class="input-group-btn">
                                                <a href="javascript:navigate('first');" id="btn_min" class="btn blue input-sm"><i class="fa fa-fast-backward"></i></a>
                                                <a href="javascript:navigate('prev');" id="btn_prv" class="btn blue input-sm"><i class="fa fa-backward"></i></a>
                                                <a href="javascript:navigate('next');" id="btn_nxt" class="btn blue input-sm"><i class="fa fa-forward"></i></a>
                                                <a href="javascript:navigate('last');" id="btn_max" class="btn blue input-sm"><i class="fa fa-fast-forward"></i></a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-md-3">Receiving Date.</label>
                                    <div class="col-md-4">
                                        <input class="form-control clear clearinv input-sm date-picker" size="16" type="text" name="receivingdate" id="receivingdate"/>
                                    </div>
                                    <div class="col-md-5"></div>
                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-md-3">Invoice No.</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input type="text" class="form-control clear input-sm" id="invoiceno" name="invoiceno" readonly/>
                                            <input type="hidden" class="form-control clear clearinv input-sm" id="hdninvoiceno" name="hdninvoiceno"/>
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn green input-sm" id="btn_checkinv">
                                                    <i class="fa fa-arrow-circle-down"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-md-3">Invoice Date</label>
                                    <div class="col-md-4">
                                        <input class="form-control clear clearinv input-sm date-picker" size="16" type="text" name="invoicedate" id="invoicedate" disabled="disable"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group row">
                                    <label class="control-label col-md-5">Pallet No.</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control clear clearinv input-sm" id="palletno" name="palletno" readonly/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-md-5">Total Qty.</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control clear clearinv input-sm" id="totalqty" name="totalqty" disabled="disable"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-md-5">Total Variance</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control clear clearinv input-sm" id="totalvar" name="totalvar" disabled="disable"/>
                                        <input type="hidden" id="totalamt" name="totalamt"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-md-5">Status</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control clear clearinv input-sm" id="status" name="status" disabled="disable"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="control-label col-md-4">Created By</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control clear clearinv input-sm" id="createdby" name="createdby" disabled="disable"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-md-4">Created Date</label>
                                    <div class="col-md-8">
                                        <input class="form-control clear clearinv input-sm date-picker" size="50" type="text" name="createddate" id="createddate" disabled="disable"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-md-4">Updated By</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control clear clearinv input-sm" id="updatedby" name="updatedby" disabled="disable"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-md-4">Updated Date</label>
                                    <div class="col-md-8">
                                        <input class="form-control clear clearinv input-sm date-picker" size="50" type="text" name="updateddate" id="updateddate" disabled="disable"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-offset-2 col-md-8">
                                <div class="row">
                                    <div class="col-sm-offset-2 col-sm-8">
                                        <form class="form-horizontal" method="POST" enctype="multipart/form-data" id="uploadbatchfiles" action="{{ url('/wbsuploadbatchitems') }}">
                                            <div class="form-group">
                                                <label class="control-label col-sm-4">Upload Batch Items</label>
                                                <div class="col-sm-6">
                                                    {{ csrf_field() }}
                                                    <input type="file" class="filestyle" data-buttonName="btn-primary" name="batchfiles" id="batchfiles" {{$readonly}}>
                                                    {{-- batchfiles --}}
                                                    <input type="hidden" name="receiveNoupload" id="receiveNoupload" value="">
                                                </div>
                                                <div class="col-sm-2">
                                                    <button type="submit" id="btn_upload" class="btn btn-success" <?php echo($state); ?>>
                                                        <i class="fa fa-upload"></i> Upload
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="tabbable-custom">
                                    <ul class="nav nav-tabs nav-tabs-lg" id="tabslist" role="tablist">
                                        <li class="active">
                                            <a href="#details" data-toggle="tab" data-toggle="tab" aria-expanded="true">Details</a>
                                        </li>
                                        <li>
                                            <a href="#summary" data-toggle="tab" data-toggle="tab" aria-expanded="true">Summary</a>
                                        </li>
                                        <li>
                                            <a href="#batch" data-toggle="tab" data-toggle="tab" aria-expanded="true">Batch Details</a>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="tab-subcontents">
                                        <!-- Details Tab -->
                                        <div class="tab-pane fade in active" id="details">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="table-responsive table-area" id="div_tbl_details" >

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Summary Tab -->
                                        <div class="tab-pane fade" id="summary">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="table-responsive" id="div_tbl_summary">

                                                    </div>
                                                    <!-- <p>Count: <span id="summarycount"></span></p> -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Batch Details Tab -->
                                        <div class="tab-pane fade" id="batch">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="table-responsive" id="div_tbl_batch">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <button type="button"  class="btn green input-sm" id="btn_add_batch" <?php echo($state); ?> >
                                                        <i class="fa fa-plus"></i> Add Batch Item
                                                    </button>
                                                    <button type="button"  class="btn red input-sm" id="btn_delete_batch">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                    <input type="hidden" class="form-control input-sm" id="item_count" placeholder="Lower Limit" name="item_count"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-success input-sm" id="btn_addnew" <?php echo($state); ?>>
                                    <i class="fa fa-plus"></i> Add New
                                </button>
                                <button type="button" class="btn blue-madison input-sm" id="btn_save" <?php echo($state); ?> >
                                    <i class="fa fa-pencil"></i> Save
                                </button>
                                <button type="button" class="btn blue-madison input-sm" id="btn_edit" >
                                    <i class="fa fa-pencil"></i> Edit
                                </button>
                                <button type="button" class="btn red input-sm" id="btn_cancel" <?php echo($state); ?> >
                                    <i class="fa fa-trash"></i> Cancel
                                </button>
                                <button type="button" class="btn red-intense input-sm" id="btn_discard" <?php echo($state); ?> >
                                    <i class="fa fa-pencil"></i> Discard Changes
                                </button>
                                <button type="button" class="btn blue-steel input-sm" id="btn_search">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button type="button" class="btn red input-sm" id="btn_cancel_mr" <?php echo($state); ?> >
                                    <i class="fa fa-trash"></i> Cancel Invoice
                                </button>
                                <button type="submit" class="btn purple-plum input-sm" id="btn_print">
                                    <i class="fa fa-print"></i> Print
                                </button>
                                <button type="submit" class="btn blue input-sm" id="btn_print_iqc">
                                    <i class="fa fa-print"></i> Apply to IQC
                                </button>
                                <button type="button" class="btn grey-gallery input-sm" id="btn_refresh">
                                    <i class="fa fa-refresh"></i> Refresh Invoice
                                </button>
                                <button type="button" class="btn green btn-sm" id="btn_for_details_update_qc">
                                    <i class="fa fa-refresh"></i> For Details Update from QC<span class="badge badge-danger" style="background-color: #F3565D; color: #eee">{{ $count_for_modification }}</span>
                                </button>
                                <input type="hidden" name="savestate" id="savestate">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes.wbs.materialreceiving-modal')

@endsection


@push('script')
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    var access_state = "{{ $pgaccess }}";
    var pcode = "{{ $pgcode }}";
    var token = "{{ Session::token() }}";
    var PostSearchInvoiceNo = "{{url('/wbsmrpostinvoicenum')}}";
    var CancelTransaction = "{{url('/materialreceiving')}}";
    var GetReceivingSearch = "{{url('/wbsmrsearch')}}";
    var GetReceivingViewItem = "{{url('/wbsmrlookitem')}}";
    var PostReceivingPrintBarcode = "{{url('/wbsmrprintbarcode')}}";
    var GetReceivingPrintLabel = "{{url('/wbsmrprintmr')}}";
    var GetReceivingIQCList = "{{url('/wbsmrprintiqc')}}";
    var GetReceivingLocation = "{{url('/getlocation')}}";
    var PostReceivingBatchReceiveAll = "{{ url('/wbsmrreceiveall') }}";
    var GetReceivingLatestData = "{{url('/wbsgetlatestmr')}}";
    var GetReceivingNavigation = "{{url('/wbsnavigate')}}";
    var GetReceivingItems = "{{url('/wbsmrgetitems')}}";
    var GetReceivingItemData = "{{url('/wbsmrgetitemdata')}}";
    var GetReceivingPackage = "{{url('/wbsmrgetpackage')}}";
    var GetReceivingSingleBatchItem = "{{url('/wbsmrsinglebatchitem')}}";
    var GetReceivingNotForIQC = "{{url('/wbsmrcheckifnotforiqc')}}";
    var PostReceivingSaveData = "{{url('/wbsmrsave')}}";
    var GetReceivingNumber = "{{url('/wbsmrnumber')}}";
    var PostReceivingDeleteBatch = "{{url('/wbsmrdeletebatch')}}";
    var PostReceivingCancelInvoice = "{{url('/wbsmrcanvelinvoice')}}";
    var GetReceivingRefreshInvoice = "{{ url('/wbsmr-refresh') }}";
    var GetReceivingSupplier = "{{url('/getReceivingSupplier')}}";
    var showNeedsModificationItemURL = "{{ url('/wbsmr-needs-modify-item') }}";
    var removeNeedsModificationItemURL = "{{ url('/wbsmr-no-need-modify-item') }}";
    var ModifyItemURL = "{{ url('/wbsmr-modify-item') }}";
</script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/wbs/material_receiving.js')."?v=".date('YmdHis') }}" type="text/javascript"></script>
@endpush
