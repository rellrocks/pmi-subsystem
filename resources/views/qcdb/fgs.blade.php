@extends('layouts.master')

@section('title')
	QC Database | Pricon Microelectronics, Inc.
@endsection

@section('content')

	<?php $state = ""; $readonly = ""; ?>
	@foreach ($userProgramAccess as $access)
		@if ($access->program_code == Config::get('constants.MODULE_CODE_FGSDB'))  <!-- Please update "2001" depending on the corresponding program_code -->
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
					<div class="portlet-body">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="portlet box grey-gallery" >
                							<div class="portlet-title">
                								<div class="caption">
                									<i class="fa fa-line-chart"></i> FGS
                								</div>
                                                <div class="actions">
                                                    <div class="btn-group">
                                                        <a href="javascript:;" class="btn green" id="btn_add">
                                                            <i class="fa fa-plus"></i> Add New
                                                        </a>
                                                        <a href="javascript:;" class="btn blue" id="btn_groupby">
                                                            <i class="fa fa-group"></i> Group By
                                                        </a>
                                                        <button type="button" onclick="javascript:deleteAllchecked();" class="btn red" id="btn_delete">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                        <a href="javascript:;" class="btn purple" id="btn_search">
                                                            <i class="fa fa-search"></i> Search
                                                        </a>
                                                        <a href="javascript:;" class="btn yellow-gold" id="btn_pdf">
                                                            <i class="fa fa-file-text-o"></i> Print to Pdf
                                                        </a>
                                                        <a href="javascript:;" class="btn green-jungle" id="btn_excel">
                                                            <i class="fa fa-file-text-o"></i> Print to Excel
                                                        </a>
                                                    </div>
                                                </div>
                							</div>
                							<div class="portlet-body">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <table class="table table-hover table-bordered table-striped" id="fgsdatatable">
                                                            <thead>
                                                                <tr>
                                                                	<td width="4.28%" class="table-checkbox">
                                                                        <input type="checkbox" class="group-checkable checkAllitems" />
                                                                    </td>
                                                                    <td width="5.28%"></td>
                                                                    <td width="14.28%">Date Inspection</td>
                                                                    <td width="20.28%">P.O. #</td>
                                                                    <td width="27.28%">Series Name</td>
                                                                    <td width="14.28%">Quantity</td>
                                                                    <td width="14.28%">Total No. of Lots</td>

                                                                </tr>
                                                            </thead>
                                                            <tbody id="tblforfgs">
                                                        
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <input class="form-control input-sm" type="hidden" value="" name="hd_report_status" id="hd_report_status"/>
                                </div>

							</div>

					</div>
				<!-- END EXAMPLE TABLE PORTLET-->
			</div>
		</div>
		<!-- END PAGE CONTENT-->
	</div>


	@include('includes.qcdb.fgs-modal')
	@include('includes.modals')
@endsection

@push('script')
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    var token = "{{Session::token()}}";
    var GetFGSdata = "{{ url('/FGSgetrows') }}";
    var dbcon = "{{Auth::user()->productline}}";
    var GetFGSYPICSRecordsPO = "{{ url('/getfgsYPICSrecords') }}";
    var GetFGSPrintReport = "{{ url('/fgsprintreport') }}";
    var GetFGSPrintReportExcel = "{{ url('/fgsprintreportexcel')  }}";
    var PostFGSSave = "{{ url('/fgsSave') }}";
    var PostFGSDelete = "{{ url('/fgsDelete') }}";
    var GetFGSPage = "{{ url('/fgs') }}";
</script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/qcdb/fgs.js')."?v=".date('YmdHis') }}" type="text/javascript"></script>
@endpush
