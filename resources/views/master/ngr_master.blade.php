
@extends('layouts.master')

@section('title')
	NGR Master | Pricon Microelectronics, Inc.
@endsection

@section('content')

	<?php $state = ""; $readonly = ""; ?>
	@foreach ($userProgramAccess as $access)
		@if ($access->program_code == Config::get('constants.MODULE_MGR_MASTER'))
			@if ($access->read_write == "2")
				<?php $state = "disabled"; $readonly = "readonly"; ?>
			@endif
		@endif
	@endforeach

	<div class="page-content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                @include('includes.message-block')

                <div class="portlet box blue" >
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-navicon"></i>  NGR Master
                        </div>
                    </div>
                    <div class="portlet-body">

                        <div class="tabbable-custom">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation"  class="active"><a href="#ngr_status_tab" aria-controls="ngr_status" role="tab" data-toggle="tab" id="ngr_status_link">NGR Status</a></li>
                                <li role="presentation"><a href="#ngr_disposition_tab" aria-controls="ngr_disposition" role="tab" data-toggle="tab" id="ngr_disposition_link">NGR Disposition</a></li>
                            </ul>

                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="ngr_status_tab">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <!--form-->
                                            <form class="form-horizontal" id="frm_ngr_status">
                                                <div class="form-group row">
                                                    <label for="ngr_status_description" class="col-md-12">NGR Status</label>
                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control input-sm clear" id="ngr_status_description" name="description" disabled>
                                                    </div>
                                                    <input type="hidden" class="form-control input-sm clear" id="ngr_status_id" name="id">
                                                    <input type="hidden" class="form-control input-sm" id="ngr_status_category" name="category" value="STATUS">
                                                    <div id="ngr_status_description_err" style="color: hsl(0, 87%, 62%); font-weight: 900"></div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm green btn-block" id="btn_add_status">
                                                <i class="fa fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm blue btn-block" id="btn_save_status" disabled>
                                                <i class="fa fa-floppy-o"></i> Save
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm grey-gallery btn-block" id="btn_cancel_status" disabled>
                                                <i class="fa fa-times"></i> Cancel
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm red btn-block" id="btn_delete_status" disabled>
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>

                                    <hr/>

                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <table class="table table-striped table-hover table-condensed table-bordered" id="tbl_ngr_status" style="font-size: 10px; white-space: nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            <input type="checkbox" name="check_all_status" class="check_all_status" id="check_all_status">
                                                        </th>
                                                        <th></th>
                                                        <th>Description</th>
                                                        <th>Updated By</th>
                                                        <th>Update Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="ngr_disposition_tab">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <!--form-->
                                            <form class="form-horizontal" id="frm_ngr_disposition">
                                                <div class="form-group row">
                                                    <label for="ngr_disposition_description" class="col-md-12">NGR Disposition</label>
                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control input-sm clear" id="ngr_disposition_description" name="description" disabled>
                                                    </div>
                                                    <input type="hidden" class="form-control input-sm clear" id="ngr_disposition_id" name="id">
                                                    <input type="hidden" class="form-control input-sm" id="ngr_disposition_category" name="category" value="DISPOSITION">
                                                    <div id="ngr_disposition_description_err" style="color: #f24848; font-weight: 900"></div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm green btn-block" id="btn_add_disposition">
                                                <i class="fa fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm blue btn-block" id="btn_save_disposition" disabled>
                                                <i class="fa fa-floppy-o"></i> Save
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm grey-gallery btn-block" id="btn_cancel_disposition" disabled>
                                                <i class="fa fa-times"></i> Cancel
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm red btn-block" id="btn_delete_disposition" disabled>
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>

                                    <hr/>

                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <table class="table table-striped table-hover table-condensed table-bordered" id="tbl_ngr_disposition" style="font-size: 10px; white-space: nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            <input type="checkbox" name="check_all_disposition" class="check_all_disposition" id="check_all_disposition">
                                                        </th>
                                                        <th></th>
                                                        <th>Description</th>
                                                        <th>Updated By</th>
                                                        <th>Update Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

            </div>
        </div>
    </div>

	@include('includes.modals')


@endsection

@push('script')
	<script>
		var token = '{{ Session::token() }}';
	</script>
	<script src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
	<script src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/ngr_master.js') }}" type="text/javascript"></script>
@endpush
