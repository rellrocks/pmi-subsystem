
<?php
/*******************************************************************************
     Copyright (c) Company Nam All rights reserved.

     FILE NAME: iqc.blade.php
     MODULE NAME:  3006 : WBS - IQC Inspection
     CREATED BY: AK.DELAROSA
     DATE CREATED: 2016.07.01
     REVISION HISTORY :

     VERSION     ROUND    DATE           PIC          DESCRIPTION
     100-00-01   1     2016.07.01    AK.DELAROSA      Initial Draft
     100-00-02   1     2016.07.27    MESPINOSA        IQC Inspection Implementation.
     200-00-01   1     2016.07.01    AK.DELAROSA      Version 2.0
*******************************************************************************/
?>

@extends('layouts.master')

@section('title')
WBS | Pricon Microelectronics, Inc.
@endsection


@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
        @if ($access->program_code == Config::get('constants.MODULE_CODE_IQCINS'))
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
                            <i class="fa fa-navicon"></i>  WBS IQC Inspection
                        </div>
                    </div>
                    <div class="portlet-body">
							<div class="row">
                                <div class="col-md-12">
                                	<div class="pull-right">
                                		<a href="javascript:;" id="searchbtn" class="btn btn-sm blue input-sm">
                                            <i class="fa fa-search"></i> Search
                                        </a>
                                		      <a href="javascript:;" id="statusbtn" class="btn btn-sm btn-success input-sm" <?php echo($state); ?> >
                                            <i class="fa fa-ellipsis-v"></i> Update Status Bulk
                                        </a>
                                	</div>
                                </div>
                            </div>
                                  <br/>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-striped" id="tbl_iqc" style="font-size:10px; white-space: nowrap; width:100%">
                                        <thead>
                                            <tr>
                                                <td class="table-checkbox">
                                                    <input type="checkbox" id="chk_all" name="chk_all" class="group-checkable"/>
                                                </td>
                                                <td></td>
                                                <td>Status</td>
                                                <td>Item/Part No.</td>
                                                <td>Item Description</td>
                                                <td>Supplier</td>
                                                <td>Quantity</td>
                                                <td>Lot No.</td>
                                                <td>Drawing No.</td>
                                                <td>Receving No.</td>
                                                <td>Invoice No.</td>
                                                <td>Applied By</td>
                                                <td>Date & Time Applied</td>
                                                <td>Inspected By</td>
                                                <td>Date & Time Inspected</td>
                                                <td>IQC Result</td>
                                                <td>Update Date</td>
                                            </tr>
                                        </thead>
                                        <tbody id="tbl_iqc_body"></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->

            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>

    <div id="searchmodal" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog gray-gallery">
            <div class="modal-content ">
            	<div class="modal-header">
                    <h4 class="modal-title">Search</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">
	                    <div class="form-group">
	                    	<label class="control-label col-sm-3">From</label>
	                        <div class="col-sm-9">
	                            <input type="text" class="form-control input-sm datepicker" id="from" name="from" placeholder="From" data-date-format="yyyy-mm-dd" value="">
	                        </div>
	                    </div>
	                    <div class="form-group">
	                    	<label class="control-label col-sm-3">To</label>
	                        <div class="col-sm-9">
	                            <input type="text" class="form-control input-sm datepicker" id="to" name="to" placeholder="To" data-date-format="yyyy-mm-dd" value="">
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="control-label col-sm-3">Receiving No.</label>
	                        <div class="col-sm-9">
	                            <input type="text" class="form-control input-sm" id="recno" name="recno">
	                        </div>
	                    </div>
                        <div class="form-group">
                            <label class="control-label col-sm-3">Invoice No.</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control input-sm" id="invoice_no" name="invoice_no">
                            </div>
                        </div>
	                    <div class="form-group">
	                        <label class="control-label col-sm-3">Status</label>
	                        <div class="col-sm-9">
	                            <select class="form-control input-sm" name="status" id="status">
                                    <option value=""></option>
	                                <option value="0">Pending</option>
	                                <option value="Accepted">Accepted</option>
	                                <option value="Rejected">Rejected</option>
	                                <option value="3">On-going</option>
                                    <option value="Special Accept">Special Accepted</option>
                                    <option value="Sorted">Sorted</option>
                                    <option value="Reworked">Reworked</option>
                                    <option value="RTV">RTV</option>
	                            </select>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="control-label col-sm-3">Item/Part No.</label>
	                        <div class="col-sm-9">
	                            <input type="text" class="form-control input-sm" id="itemno" name="itemno"/>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="control-label col-sm-3">Lot No.</label>
	                        <div class="col-sm-9">
	                            <input type="text" class="form-control input-sm" id="lotno" name="lotno">
	                        </div>
	                    </div>
                    </form>

                </div>
                <div class="modal-footer">
                	<a href="javascript:;" data-dismiss="modal" id="gobtn" class="btn btn-primary btn-sm">Go</a>
                	<button type="button" data-dismiss="modal" class="btn btn-danger btn-sm">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-md gray-gallery">
            <div class="modal-content ">
                <div class="modal-header">
                    <h4 class="modal-title">Update Status for IQC Inspection</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form  class="form-horizontal" id="statusmdl">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Status</label>
                                    <div class="col-sm-8">
                                        <select class="form-control input-sm" name="statusup" id="statusup">
                                            <option value="1">Accepted</option>
                                            <option value="2">Reject</option>
                                            <option value="3" selected="selected">On-going</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Inspector</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="inspector" id="inspector" class="form-control input-sm" value="{{ Auth::user()->user_id }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Start Time</label>
                                    <div class="col-sm-8">
                                        <input type="text" data-format="hh:mm A" class="form-control required input-sm timepicker timepicker-no-seconds" name="start_time" id="start_time"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">IQC Result</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control input-sm" id="iqcresup" style="resize:none" name="iqcresup"  id="iqcresup" ></textarea>
                                    </div>
                                </div>

                                <input type="hidden" name="app_date" id="app_date">
                                <input type="hidden" name="app_time" id="app_time">

                            </form>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" id="selectedid"/>
                    <a href="javascript:;" id="updateIQCstatusbtn" data-dismiss="modal" class="btn btn-success" <?php echo($state); ?>>OK</a>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="bulkupdatemodal" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-md gray-gallery">
            <div class="modal-content ">
                <div class="modal-header">
                    <h4 class="modal-title">Update Status for Bulk IQC Inspection</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form  class="form-horizontal">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Status</label>
                                    <div class="col-sm-8">
                                        <select class="form-control input-sm" name="statusupbulk" id="statusupbulk">
                                            <option value="1">Accepted</option>
                                            <option value="2">Reject</option>
                                            <option value="3" selected="selected">On-going</option>
                                            <option value="4">Special Accept</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Inspector</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="inspectorbulk" id="inspectorbulk" class="form-control input-sm" value="{{ Auth::user()->user_id }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Start Time</label>
                                    <div class="col-sm-8">
                                        <input type="text" data-format="hh:mm A" class="form-control required input-sm timepicker timepicker-no-seconds" name="start_timebulk" id="start_timebulk"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">IQC Result</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control input-sm" id="iqcresupbulk" style="resize:none" name="iqcresupbulk" ></textarea>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <a href="javascript:;" id="updateIQCbulkbtn" data-dismiss="modal" class="btn btn-success" <?php echo($state); ?>>OK</a>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="loading" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm gray-gallery">
            <div class="modal-content ">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <img src="{{ asset('public/assets/images/ajax-loader.gif') }}" class="img-responsive">
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div id="msg" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm gray-gallery">
            <div class="modal-content ">
                <div class="modal-header">
                    <h4 id="title" class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p id="err_msg"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div> --}}

    @include('includes.modals')
@endsection

@push('script')
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
    
	<script type="text/javascript">
		var token = "{{ Session::token() }}";
        var GetWBSIQCdata = "{{url('/getwbsiqc')}}" ;
        var PostWBSIQCSingleUpdate = "{{url('/postwbsiqcsingleupdate')}}";
        var PostWBSIQCBulkUpdate = "{{url('/postwbsiqcupdatebulk')}}";
        var GetWBSIQCSearch = "{{url('/getwbsiqcsearch')}}"
	</script>
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/wbs/iqc.js')."?v=".date('YmdHis') }}" type="text/javascript"></script>
@endpush
