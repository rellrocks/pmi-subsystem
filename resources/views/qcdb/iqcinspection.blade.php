@extends('layouts.master')

@section('title')
	QC Database | Pricon Microelectronics, Inc.
@endsection

@push('css')
	<link href="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/css/table-fixedheader.css')}}" rel="stylesheet" type="text/css"/>

    <style rel="stylesheet" type="text/css">
        .select2-selection{
            border: 1px solid #e5e5e5;
        }
        .select2-container--bootstrap .select2-selection--single {
            height: 27px;
            line-height: 0.9;
        }
        .select2 .select2-container--bootstrap .select2-selection--single .select2-selection__placeholder {
            font-size: 11px;
        }
        td {
            white-space: nowrap;
        }
        td.lot {
            white-space: normal;
        }
        .select2-results__option[aria-selected=true] {
            display: none;
        }
        .form-control {
            border: 1px solid #a1a1a1;
        }
        .bootbox-checkbox-list > .checkbox > label, .form-horizontal .checkbox > label {
            padding-left: 20px;
        }
    </style>
@endpush

@section('content')

	<?php $state = ""; $readonly = ""; ?>
	@foreach ($userProgramAccess as $access)
		@if ($access->program_code == Config::get('constants.MODULE_CODE_IQCDB'))  <!-- Please update "2001" depending on the corresponding program_code -->
			@if ($access->read_write == "2")
			<?php $state = "disabled"; $readonly = "readonly"; ?>
			@endif
		@endif
	@endforeach

	<div class="page-content">

		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="btn-group pull-right">
							<a href="javascript:;" class="btn btn-sm green" id="btn_upload">
                                <i class="fa fa-upload"></i> Upload Data
                            </a>
                            <a href="javascript:;" class="btn btn-sm grey-gallery" id="btn_groupby">
                                <i class="fa fa-group"></i> Group By
                            </a>
                            
                            <a href="javascript:;" class="btn btn-sm purple" id="btn_search">
                                <i class="fa fa-search"></i> Search
                            </a>
							<a href="javascript:;" class="btn btn-sm yellow-gold" id="btn_pdf">
                                <i class="fa fa-file-text-o"></i> Export to Pdf
                            </a>
                            <a href="javascript:;" class="btn btn-sm green-jungle" id="btn_excel">
                                <i class="fa fa-file-text-o"></i> Export to Excel
                            </a>

                            <a href="javascript:;" class="btn btn-sm blue" id="btn_history">
                                <i class="fa fa-book"></i> Item History
                            </a>
						</div>
					</div>
				</div>

				<hr>

				<div class="row col-sm-offset-3">
					<div class="col-sm-3">
						<a href="javascript:;" class="btn green btn-block" id="btn_iqcresult">
							<i class="fa fa-search"></i> Inspection
						</a>
					</div>

					<div class="col-sm-3">
						<a href="javascript:;" class="btn green btn-block" id="btn_iqcresult_man">
							<i class="fa fa-search"></i> Manual Input
						</a>
					</div>

					<div class="col-sm-3">
						<a href="javascript:;" class="btn green btn-block" id="btn_requali">
							<i class="fa fa-history"></i> Re-qualification
						</a>
					</div>
				</div>

                <br>

                <div class="row">
                    <div class="col-sm-12" id="main_pane">
                    	<div class="tabbable-custom">
                        	<ul class="nav nav-tabs" role="tablist">
                        		<li role="presentation"  class="active"><a href="#on-going" aria-controls="on-going" role="tab" data-toggle="tab">On-Going</a></li>
                                <li role="presentation"><a href="#inspected" aria-controls="inspected" role="tab" data-toggle="tab">Inspected</a></li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="on-going">
                                	<table class="table table-hover table-bordered table-striped table-condensed" id="on-going-inspection" style="font-size: 10px;">
                                        <thead>
                                            <tr>
                                            	<td class="table-checkbox">
                                                    <input type="checkbox" class="group-checkable ongoing_checkall" />
                                                </td>
                                                <td></td>
                                                <td>Judgement</td>
                                                <td>Invoice No.</td>
                                                <td>Inspector</td>
                                                <td>Inspection Date</td>
                                                <td>Inspection Times</td> 
                                                <td>Application Ctrl No.</td>
                                                <td>FY#</td>
                                                <td>WW#</td>
                                                <td>Sub</td>
                                                <td>Part Code</td>
                                                <td>Part Name</td>
                                                <td>Supplier</td>
                                                <td>Lot No.</td>
                                                <td>Lot Qty.</td>
												<td>Date Created</td>
                                            </tr>
                                        </thead>
                                        <tbody id="tblforongoing">
                                        </tbody>
                                    </table>
                                    <div class="row">
                                    	<div class="col-md-12 text-center">
                                    		<button type="button" class="btn red" id="btn_delete_ongoing">
                                    			<i class="fa fa-trash"></i> Delete
                                    		</button>
                                    	</div>
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="inspected">
									<table class="table table-hover table-bordered table-striped table-condensed" id="iqcdatatable" style="font-size: 10px;">
										<thead>
											<tr>
												<td class="table-checkbox">
													<input type="checkbox" class="group-checkable iqc_checkall" />
												</td>
												<td></td>
												<td>Judgement</td>
												<td>NGR Status</td>
                                                <td>NGR Disposition</td>
                                              	<td>NGR Control No.</td>
												<td>Invoice No.</td>
												<td>Inspector</td>
												<td>Date Inspected</td>
												<td>Inspection Time</td>
												<td>App. No.</td>
												<td>App. Date</td>
												<td>App time</td>
												<td>FY</td>
												<td>WW</td>
												<td>Submission</td>
												<td>Part Code</td>
												<td>Part Name</td>
												<td>Supplier</td>
												<td>Lot No.</td>
												<td>Lot Qty</td>
												<td>Type of Inspection</td>
												<td>Severity of Inspection</td>
												<td>Inspection Level</td>
												<td>Accept</td>
												<td>Reject</td>
												<td>Shift</td>
												<td>Lot Inspected</td>
												<td>Lot Accepted</td>
												<td>Sample Size</td>
												<td>No. of Defects</td>
												<td>Remarks</td>
												<td>Classification</td>
                                                <td>Date Updated</td>
											</tr>
										</thead>
										<tbody id="tblforiqcinspection">
										</tbody>
									</table>
	                                	
                                    <div class="row">
                                    	<div class="col-md-12 text-center">
                                    		<button type="button" class="btn red" id="btn_delete_inspected">
                                    			<i class="fa fa-trash"></i> Delete
                                    		</button>
                                    	</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12" id="group_by_pane"></div>
                </div>

			</div>
		</div>

	</div>


	@include('includes.qcdb.iqc_inspection-modal')
	@include('includes.modals')

@endsection

@push('script')
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>

<script type="text/javascript">
	var token = "{{ Session::token() }}";
    var GetIQCInspectionData = "{{url('/iqcdbgetiqcdata')}}";
    var GetIQCInspectionPartCode = "{{url('iqcdbgetitems')}}";
    var GetIQCInspectionLotNo = "{{url('/iqcdbgetlotno')}}";
    var GetIQCSelect2Data = "{{url('/iqc-getSelect2')}}";
    var PostSpecialAccept = "{{url('/iqcspecialaccept')}}";
    var GetIQCInspectionHistory = "{{url('/iqcdbgethistory')}}";
    var GetIQCOnGoing = "{{url('/iqcdbgetongoing')}}";
    var PostSaveIQCInspection = "{{url('/iqcsaveinspection')}}";
    var GetSamplingPlan = "{{url('/iqcsamplingplan')}}";
    var GetIQCDropdowns = "{{url('/iqcgetdropdowns')}}";
    var GetIQCItemDetails = "{{url('/iqcdbgetitemdetails')}}";
    var GetIQCLotQty = "{{url('/iqccalculatelotqty')}}";
    var PostSaveModeOfDefects = "{{url('/iqcdbsavemodeofdefects')}}";
    var GetModeOfDefects = "{{url('/iqcdbgetmodeofdefectsinspection')}}";
    var GetIQCItemSearch = "{{url('/iqcdbgetitemsearch')}}";
    var GetIQCInspectionSearch = "{{url('/iqcdbsearchinspection')}}";
    var PostIQCInspectionDelete = "{{url('/iqcdbdeleteinspection')}}";
    var PostIQCDeleteModeOfDefects = "{{url('/iqcdbdeletemodeofdefects')}}";
    var PostIQCDeleteOnGoing = "{{url('/iqcdbdeleteongoing')}}";
    var GetWorkWeek = "{{url('/iqcgetworkweek')}}";

    var GetIQCRequaliItemData = "{{url('/iqcdbgetitemrequali')}}";
    var GetIQCRequaliAppNo = "{{url('/iqcdbgetappnorequali')}}";
    var GetIQCRequaliItemDetails = "{{url('/iqcdbgetdetailsrequali')}}";
    var GetIQCRequaliLotQty = "{{url('/iqccalculatelotqtyrequali')}}";
    var GetIQCRequaliVisualInspection = "{{url('/iqcdbvisualinspectionrequali')}}";
    var GetIQCRequaliDropdowns = "{{url('/iqcgetdropdownsrequali')}}";
    var PostIQCRequalSaveInspection = "{{url('/iqcsaverequali')}}";
    var GetIQCRequaliInspectionData = "{{url('/iqcdbgetrequalidata')}}";
    var GetIQCRequaliModeOfDefects = "{{url('/iqcdbgetmodeofdefectsrequali')}}";
    var PostIQCRequaliModeOfDefects = "{{url('/iqcdbsavemodeofdefectsrq')}}";
    var PostIQCRequaliDelete = "{{url('/iqcdbdeleterequali')}}";
    var PostIQCRequaliDeleteModeOfDefects = "{{url('/iqcdbdeletemodeofdefectsrequali')}}";

    // SORTING
    var PostSaveSortingData = "{{url('/iqcsavesortingdata')}}";
    var GetSortingData = "{{url('/iqcgetsortingdata')}}";
    var PostDeleteSortingData = "{{url('/iqcdeletesortingdata')}}";

    // REWORK
    var PostSaveReworkData = "{{url('/iqcsavereworkdata')}}";
    var GetReworkData = "{{url('/iqcgetreworkdata')}}";
    var PostDeleteReworkData = "{{url('/iqcdeletereworkdata')}}";

    // REWORK
    var PostSavertvData = "{{url('/iqcsavertvdata')}}";
    var GetrtvData = "{{url('/iqcgetrtvdata')}}";
    var PostDeletertvData = "{{url('/iqcdeletertvdata')}}";

	var GroupByURL = "{{ url('/iqc-groupby-values') }}";
	var GetSingleGroupByURL = "{{ url('/iqc-groupby-dppmgroup1') }}";
    var GetdoubleGroupByURL = "{{ url('/iqc-groupby-dppmgroup2') }}";
    var GettripleGroupByURL = "{{ url('/iqc-groupby-dppmgroup3') }}";
    var GetdoubleGroupByURLdetails = "{{ url('/iqc-groupby-dppmgroup2_Details') }}";
    var GettripleGroupByURLdetails = "{{ url('/iqc-groupby-dppmgroup3_Details') }}";
    
    var pdfURL = "{{ url('/iqcprintreport') }}";
    var excelURL = "{{ url('/iqcprintreportexcel')  }}";
    var excelSummaryURL = "{{ url('/iqcinspectionsummaryexcel')}}";

    var GetAvailableLotNumbersURL = "{{ url('/iqc-available-lot-numbers')  }}";
    var url_insertIQCLotNo = "{{ url('/insert-iqc-lot-no')  }}";
</script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/qcdb/iqc_inspection.js') }}" type="text/javascript"></script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/qcdb/iqc_inspection_groupby.js')."?v=".date('YmdHis') }}" type="text/javascript"></script>
@endpush
