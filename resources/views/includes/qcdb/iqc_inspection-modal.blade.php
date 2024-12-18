<!-- IQC RESULT MODAL -->
<div id="IQCresultModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">IQC Inspection Result</h4>
			</div>
			<form class=form-horizontal id="frm_iqc_inspection">
				{{ csrf_field() }}
				 <div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group" id="classification_manual" style="display:none">
								<label class="control-label col-sm-3">Classification</label>
								<div class="col-sm-9">
									<select class="form-control input-sm clear" id="classification" name="classification">
										<option value=""></option>
										<option value="Visual Inspection" selected>Visual Inspection (Temporary Invoice)</option>
										<option value="Pkg. & Raw Material">Packaging & Raw Material</option>
										<option value="Material Qualification">Material Qualification</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Invoice No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control required input-sm clear" id="invoice_no" name="invoice_no">
									<input type="hidden" class="form-control input-sm clear" id="iqc_result_id" name="iqc_result_id">
									{{-- <input type="hidden" class="form-control input-sm clear" id="classification" name="classification" value="Visual Inspection"> --}}
									<div id="er_invoice_no" style="color: #f24848; font-weight: 900"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Part Code</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="partcodelbl" name="partcodelbl" style="display: none">
									<select id="partcode" name="partcode" class="form-control input-sm clear partcode clearselect" <?php echo($state);?>>
									</select>
                                    {{-- <input type="text" id="partcode" name="partcode" class="form-control input-sm clear clearselect"> --}}
									<!-- <select class="form-control required select2me input-sm clear" id="partcode" name="partcode">
									</select> -->
									<div id="er_partcode"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Part Name</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="partname" name="partname">
									<div id="er_partname"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Family</label>
								<div class="col-sm-9">
									<select id="family" name="family" class="form-control required input-sm clear family clearselect actual">
									</select>
									<div id="er_family"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Supplier</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="supplier" name="supplier" >
									<div id="er_supplier"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
                                <label class="control-label col-sm-3"></label>
                                <div class="md-checkbox-inline">
                                    <div class="md-checkbox">
                                        <input type="checkbox" id="is_batching" class="md-check" name="is_batching">
                                        <label for="is_batching">
                                        <span></span>
                                        <span class="check"></span>
                                        <span class="box"></span>
                                        Check for Batching </label>
                                    </div>
                                </div>
                            </div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Date</label>
								<div class="col-sm-9">
									<input class="form-control input-sm clear" type="text" name="app_date" id="app_date" value="{{date('m/d/Y')}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Time</label>
								<div class="col-sm-9">
									<input type="text" data-format="h:m A" class="form-control input-sm clear" name="app_time" id="app_time" value="{{date('h:i A')}}">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Ctrl No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control required input-sm clear" id="app_no" name="app_no">
									<div id="er_app_no"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.</label>
								<div class="col-sm-9">
									<button type="button" class="btn btn-sm btn-block btn-info" id="btn_lot_numbers">Lot Numbers</button>
									{{-- <select id="lot_no" name="lot_no[]" multiple class="form-control required input-sm clear lot_no clearselect">
									</select> --}}
									{{-- <input type="text" name="lot_no" id="lot_no" class="form-control required input-sm lot_no clear clearselect"> --}}
									<!-- </select> -->
									<div id="er_lot_no"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Quantity</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="lot_qty" name="lot_qty">
									<div id="er_lot_qty"></div>
								</div>
							</div>
						</div>

					</div>

					<hr>

					<div class="row">
						<div class="col-sm-12">
							<strong>Sampling Plan</strong>
						</div>
					</div>


					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Type of Inspection</label>
								<div class="col-sm-9">
									<select id="type_of_inspection" name="type_of_inspection" class="form-control required input-sm clear type_of_inspection clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required input-sm clearselect show-tick actual" name="type_of_inspection" id="type_of_inspection"> --}}
									<div id="er_type_of_inspection"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Severity of Inspection</label>
								<div class="col-sm-9">
									<select id="severity_of_inspection" name="severity_of_inspection" class="form-control required input-sm clear severity_of_inspection clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required input-sm clearselect show-tick actual" name="severity_of_inspection" id="severity_of_inspection"> --}}
									<div id="er_severity_of_inspection"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Inspection Level</label>
								<div class="col-sm-9">
									<select id="inspection_lvl" name="inspection_lvl" class="form-control required input-sm clear clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required input-sm clearselect show-tick actual" name="inspection_lvl" id="inspection_lvl"> --}}
									<div id="er_inspection_lvl"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">AQL</label>
								<div class="col-sm-9">
									<select id="aql" name="aql" class="form-control required input-sm clear aql clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required input-sm clearselect show-tick actual" name="aql" id="aql"> --}}
									<div id="er_aql"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Accept</label>
								<div class="col-sm-9">
									<input type="number" min="0" max="1" class="form-control input-sm clear actual" id="accept" name="accept">
									<div id="er_accept"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Reject</label>
								<div class="col-sm-9">
									<input type="number" min="0" max="1" class="form-control input-sm clear actual" id="reject" name="reject">
									<div id="er_reject"></div>
								</div>
							</div>
						</div>

					</div>

					<hr>

					<div class="row">
						<div class="col-sm-12">
							<strong>Visual Inspection</strong>
						</div>
					</div>


					<div class="row">

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Date Inspected</label>
								<div class="col-sm-9">
									<input class="form-control required input-sm clear date-picker actual" type="text" name="date_inspected" id="date_inspected" data-date-format='yyyy-mm-dd'/>
									<div id="er_date_ispected"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">WW#</label>
								<div class="col-sm-3">
									<input type="text" class="form-control input-sm clear actual" id="ww" name="ww">
									<div id="er_ww"></div>
								</div>
								<label class="control-label col-sm-3">FY#</label>
								<div class="col-sm-3">
									<input type="text" class="form-control input-sm clear actual" id="fy" name="fy" readonly>
									<div id="er_fy"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Time Inspected</label>
								<div class="col-sm-4">
									<input autocomplete="off" type="text" class="form-control required input-sm timepicker timepicker-no-seconds" name="time_ins_from" id="time_ins_from"/>
									<div id="er_time_ins_from"></div>
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-4">
									<input autocomplete="off" type="text" class="form-control required input-sm timepicker timepicker-no-seconds" name="time_ins_to" id="time_ins_to"/>
									<div id="er_time_ins_to"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Shift</label>
								<div class="col-sm-9">
									<select id="shift" name="shift" class="form-control required input-sm clear shift clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required input-sm clearselect show-tick actual" name="shift" id="shift"> --}}
									<div id="er_shift"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Inspector</label>
								<div class="col-sm-9">
									<input type="text" class="form-control required input-sm actual" id="inspector" name="inspector" value="{{ Auth::user()->user_id }}">
									<div id="er_inspector"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Submission</label>
								<div class="col-sm-9">
									<select id="submission" name="submission" class="form-control required input-sm clear submission clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required input-sm clearselect show-tick actual" name="submission" id="submission"> --}}
									<div id="er_submission"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Judgement</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="judgement" name="judgement" readonly>
									<div id="er_judgement"></div>
									<!-- <label class="text-success" id="msg_special_accept" style="margin-top:10px;" hidden>Special Accept</label> -->
								</div>
							</div>
						</div>

						<div class="col-md-6">

							<div class="form-group">
								<label class="control-label col-sm-3">Lot Inspected</label>
								<div class="col-sm-9">
									<input type="text" class="form-control required input-sm clear actual" id="lot_inspected" name="lot_inspected">
									<div id="er_lot_inspected"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Accepted</label>
								<div class="col-sm-9">
									<input type="text" class="form-control required input-sm clear actual" id="lot_accepted" name="lot_accepted">
									<div id="er_lot_accepted"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Sample Size</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="sample_size" name="sample_size" readonly>
									<div id="er_sample_size"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" id="no_defects_label">No. of Defectives</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="no_of_defects" name="no_of_defects">
									<div id="er_no_of_defects"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Remarks</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="remarks" name="remarks">
									<input type="hidden" class="form-control input-sm clear" id="inspectionstatus" name="inspectionstatus">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" id="mode_defects_label">Mode of Defects</label>
								<div class="col-sm-4">
									<button type="button" class="btn blue btn_mod_ins" id="btn_mod_ins">
                                        <i class="fa fa-plus-circle"></i> Add Mode of Defects
                                    </button>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<input type="hidden" name="save_status" id="save_status">
								</div>
							</div>
						</div>

					</div>

					<hr>

					<div class="row ngr_details">
						<div class="col-sm-12">
							<strong>NGR Details</strong>
						</div>
					</div>

					<div class="row ngr_details">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">NGR Status</label>
								<div class="col-sm-9">
									<select id="status_NGR" name="status_NGR" class="form-control required_ngr input-sm clear status_NGR clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required_ngr input-sm clearselect show-tick actual" name="status_NGR" id="status_NGR"> --}}
									<div id="er_status_NGR"></div>
								</div>
							</div>

							<div class="form-group" id="disposition_ngr_div">
								<label class="control-label col-sm-3">NGR Disposition</label>
								<div class="col-sm-9">
									<select id="disposition_NGR" name="disposition_NGR" class="form-control required_ngr input-sm clear disposition_NGR clearselect actual">
									</select>
									{{-- <input type="text" class="form-control required_ngr input-sm clearselect show-tick actual" name="disposition_NGR" id="disposition_NGR"> --}}
									<div id="er_disposition_NGR"></div>
								</div>
							</div>

						</div>
						
						<div class="col-md-6">
							
							<div class="form-group">
								<label class="control-label col-sm-3" id="no_defects_label">NGR Control No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear required_ngr actual" id="control_no_NGR" name="control_no_NGR">
									<div id="er_control_no_NGR"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">NGR Issued Date</label>
								<div class="col-sm-9">
									<input class="form-control required_ngr input-sm clear date-picker actual" type="text" name="date_NGR" id="date_NGR" data-date-format='yyyy-mm-dd'/>
									<div id="er_date_NGR"></div>
								</div>
							</div>

						</div>	 

					</div>

				</div>
				<div class="modal-footer">
					<input type="hidden" name="inv_id" id="inv_id">
					<input type="hidden" name="mr_id" id="mr_id">

					<button type="button" class="btn btn-primary ngr_buttons" id="btn_special_accept" style="background-color:#00ff00; display: none;"><i class="fa fa-check-circle-o"></i>Special Accept</button>
					<button type="button" class="btn btn-primary ngr_buttons" id="btn_sorting" style="background-color:#ff9933; display: none;"><i class="fa fa-sort"></i>Sorting</button>
					<button type="button" class="btn btn-primary" id="btn_sorting_details" style="background-color:#ff9933; display: none;"><i class="fa fa-sort"></i>Sorting Details</button>
					<button type="button" class="btn btn-primary ngr_buttons" id="btn_rework" style="background-color:#ff33cc; display: none;"><i class="fa fa-recycle"></i>Rework</button>
					<button type="button" class="btn btn-primary" id="btn_rework_details" style="background-color:#ff33cc; display: none;"><i class="fa fa-recycle"></i>Rework Details</button>
					<button type="button" class="btn btn-primary ngr_buttons" id="btn_rtv" style="background-color:#ff0000; display: none;"><i class="fa fa-truck"></i>RTV</button>
					<button type="button" class="btn btn-primary" id="btn_rtv_details" style="background-color:#ff0000; display: none;"><i class="fa fa-truck"></i>RTV</button>
					<button type="button" class="btn btn-success" id="btn_savemodal"><i class="fa fa-floppy-disk-o"></i>Save</button>
					<!-- <button type="button" onclick="javascript:saveInspection();" class="btn btn-success" id="btn_savemodal"><i class="fa fa-floppy-disk-o"></i>Save</button> -->
					<button type="button" class="btn grey-gallery" id="btn_clearmodal"><i class="fa fa-eraser"></i>Clear</button>
					<a href="javascript:;" data-dismiss="modal"  class="btn btn-danger btn_backModal"><i class="fa fa-reply"></i>Back</a>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- IQC RESULT MODAL -->
<div id="ManualModal" class="modal fade" role="dialog" data-backdrop="static" style="overflow:hidden;">
	<div class="modal-dialog gray-gallery modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">IQC Inspection Result</h4>
			</div>
			<form class=form-horizontal>
				 <div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Classification</label>
								<div class="col-sm-9">
									<select class="form-control input-sm clear" id="classification_man" name="classification_man">
										<option value=""></option>
										<option value="Visual Inspection">Visual Inspection (Temporary Invoice)</option>
										<option value="Pkg. & Raw Material">Packaging & Raw Material</option>
										<option value="Material Qualification">Material Qualification</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Invoice No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="invoice_no_man" name="invoice_no_man">
									<input type="hidden" class="form-control input-sm clear" id="iqc_result_id_man" name="iqc_result_id_man">

									<div id="er_invoice_no_man" style="color: #f24848; font-weight: 900"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Part Code</label>
								<div class="col-sm-9">
									<!-- <input type="text" class="form-control input-sm clear" id="partcode" name="partcode"> -->
									<select id="partcode_man" name="partcode_man" class="form-control input-sm clear partcode clearselect" <?php echo($state);?>>
									</select>
                                    {{-- <input type="text" id="partcode_man" name="partcode_man" class="form-control input-sm clear clearselect" > --}}
									<!-- <select class="form-control select2me input-sm clear" id="partcode" name="partcode">
									</select> -->
									<div id="er_partcode"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Part Name</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="partname_man" name="partname_man">
									<div id="er_partname"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Family</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="family_man" name="family_man" >
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Supplier</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="supplier_man" name="supplier_man" >
									<div id="er_supplier"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Application Date</label>
								<div class="col-sm-9">
									<input class="form-control input-sm clear" type="text" name="app_date_man" id="app_date_man" value="{{date('m/d/Y')}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Time</label>
								<div class="col-sm-9">
									<input type="text" data-format="h:m A" class="form-control input-sm clear" name="app_time_man" id="app_time_man" value="{{date('H:i A')}}">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Ctrl No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="app_no_man" name="app_no_man">
									<div id="er_app_no"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.</label>
								<div class="col-sm-9">
									<select id="lot_no_man" name="lot_no_man" class="form-control input-sm clear lot_no clearselect">
									</select>
									{{-- <input type="text" name="lot_no_man" id="lot_no_man" class="form-control input-sm lot_no clear clearselect"> --}}
									<!-- </select> -->
									<div id="er_lot_no"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Quantity</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="lot_qty_man" name="lot_qty_man">
									<div id="er_lot_qty"></div>
								</div>
							</div>
						</div>

					</div>

					<hr>

					<div class="row">
						<div class="col-sm-12">
							<strong>Sampling Plan</strong>
						</div>
					</div>


					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Type of Inspection</label>
								<div class="col-sm-9">
									<select id="type_of_inspection_man" name="type_of_inspection_man" class="form-control input-sm clear type_of_inspection clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clearselect show-tick actual" name="type_of_inspection_man" id="type_of_inspection_man"> --}}
									<div id="er_type_of_inspection"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Severity of Inspection</label>
								<div class="col-sm-9">
									<select id="severity_of_inspection_man" name="severity_of_inspection_man" class="form-control input-sm clear severity_of_inspection clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clearselect show-tick actual" name="severity_of_inspection_man" id="severity_of_inspection_man"> --}}
									<div id="er_severity_of_inspection"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Inspection Level</label>
								<div class="col-sm-9">
									<select id="inspection_lvl_man" name="inspection_lvl_man" class="form-control input-sm clear inspection_lvl clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clearselect show-tick actual" name="inspection_lvl_man" id="inspection_lvl_man"> --}}
									<div id="er_inspection_lvl"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">AQL</label>
								<div class="col-sm-9">
									<select id="aql_man" name="aql_man" class="form-control input-sm clear aql clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clearselect show-tick actual" name="aql_man" id="aql_man"> --}}
									<div id="er_aql"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Accept</label>
								<div class="col-sm-9">
									<input type="number" min="0" max="1" class="form-control input-sm clear actual" id="accept_man" name="accept_man">
									<div id="er_accept"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Reject</label>
								<div class="col-sm-9">
									<input type="number" min="0" max="1" class="form-control input-sm clear actual" id="reject_man" name="reject_man">
									<div id="er_reject"></div>
								</div>
							</div>
						</div>

					</div>

					<hr>

					<div class="row">
						<div class="col-sm-12">
							<strong>Visual Inspection</strong>
						</div>
					</div>


					<div class="row">

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Date Inspected</label>
								<div class="col-sm-9">
									<input class="form-control input-sm clear date-picker actual" type="text" name="date_inspected_man" id="date_inspected_man" data-date-format='yyyy-mm-dd'/>
									<div id="er_date_ispected"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">WW#</label>
								<div class="col-sm-3">
									<input type="text" class="form-control input-sm clear actual" id="ww_man" name="ww_man">
									<div id="er_ww"></div>
								</div>
								<label class="control-label col-sm-3">FY#</label>
								<div class="col-sm-3">
									<input type="text" class="form-control input-sm clear actual" id="fy_man" name="fy_man" readonly>
									<div id="er_fy"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Time Inspected</label>
								<div class="col-sm-4">
									<input autocomplete="off" type="text" data-format="hh:mm A" class="form-control input-sm actual" name="time_ins_from_man" id="time_ins_from_man" value="{{date('h:i A')}}"/> {{-- timepicker timepicker-no-seconds --}}
									<div id="er_time_ins_from"></div>
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-4">
									<input autocomplete="off" type="text" data-format="hh:mm A" class="form-control input-sm actual" name="time_ins_to_man" id="time_ins_to_man"  value="{{date('h:i A')}}"/> {{-- timepicker timepicker-no-seconds --}}
									<div id="er_time_ins_to"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Shift</label>
								<div class="col-sm-9">
									<select id="shift_man" name="shift_man" class="form-control input-sm clear shift clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clearselect show-tick actual" name="shift_man" id="shift_man"> --}}
									<div id="er_shift"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Inspector</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm actual" id="inspector_man" name="inspector_man" value="{{ Auth::user()->user_id }}">
									<div id="er_inspector"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Submission</label>
								<div class="col-sm-9">
									<select id="submission_man" name="submission_man" class="form-control input-sm clear submission clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clearselect show-tick actual" name="submission_man" id="submission_man"> --}}
									<div id="er_submission"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Judgement</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="judgement_man" name="judgement_man" readonly>
									<div id="er_judgement"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">

							<div class="form-group">
								<label class="control-label col-sm-3">Lot Inspected</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="lot_inspected_man" name="lot_inspected_man">
									<div id="er_lot_inspected"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Accepted</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="lot_accepted_man" name="lot_accepted_man">
									<div id="er_lot_accepted"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Sample Size</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="sample_size_man" name="sample_size_man">
									<div id="er_sample_size"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" id="no_defects_label_man">No. of Defectives</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="no_of_defects_man" name="no_of_defects_man">
									<div id="er_no_of_defects"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Remarks</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear actual" id="remarks_man" name="remarks_man">
									<input type="hidden" class="form-control input-sm clear" id="inspectionstatus_man" name="inspectionstatus_man">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" id="mode_defects_label_man">Mode of Defects</label>
								<div class="col-sm-4">
									<button type="button" class="btn blue btn_mod_ins" id="btn_mod_ins_man">
                                        <i class="fa fa-plus-circle"></i> Add Mode of Defects
                                    </button>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<input type="hidden" name="save_status" id="save_status_man">
								</div>
							</div>
						</div>

					</div>

				</div>
				<div class="modal-footer">
					<button type="button" onclick="javascript:saveInspection_man();" class="btn btn-success" id="btn_savemodal_man"><i class="fa fa-floppy-disk-o"></i>Save</button>
					<button type="button" class="btn grey-gallery" id="btn_clearmodal_man"><i class="fa fa-eraser"></i>Clear</button>
					<a href="javascript:;" data-dismiss="modal"  class="btn btn-danger btn_backModal"><i class="fa fa-reply"></i>Back</a>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- REQUALI MODAL -->
<div id="ReQualiModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery modal-xl">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Re-qualification</h4>
			</div>
			<form class=form-horizontal>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="scroller" style="height:200px">
								<table class="table table-striped table-hover table-responsive table-bordered" id="tblrealification">
									<thead>
										<tr>
											<td class="table-checkbox" style="width: 2%">
                                                <input type="checkbox" class="group-checkable checkAllitemsrq" />
                                            </td>
											<td></td>
											<td>Ctrl No.</td>
											<td>Part Code</td>
											<td>Part Name</td>
											<td>Lot No.</td>
											<td>Application Date</td>
											<td>Application Time</td>
											<td>Application Ctrl No.</td>
										</tr>
									</thead>
									<tbody id="rq_inspection_body">
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="row">

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Ctrl No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm" id="ctrl_no_rq" name="ctrl_no_rq">
									<div id="er_ctrl_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Part Code</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear" id="partcode_rq" name="partcode_rq">
									<span id="er_partcode_rq" style="color:red"></span>
									<input type="hidden" class="form-control input-sm clear" id="id_rq" name="id_rq" readonly>
									<input type="hidden" class="form-control input-sm clear" id="save_status_rq" name="save_status_rq" readonly>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Part Name</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="partname_rq" name="partname_rq" readonly>
									<div id="er_partname_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Supplier</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="supplier_rq" name="supplier_rq" readonly>
									<div id="er_supplier_rq"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Application Ctrl No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear" id="app_no_rq" name="app_no_rq">
									<span id="er_app_no_rq" style="color:red"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Date</label>
								<div class="col-sm-9">
									<input class="form-control input-sm clear date-picker" type="text" value="{{date('m/d/Y')}}" name="app_date_rq" id="app_date_rq" readonly />

								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Application Time</label>
								<div class="col-sm-9">
									<input type="text" data-format="hh:mm A" class="form-control input-sm clear clockface_1" value="{{date('h:i A')}}" name="app_time_rq" id="app_time_rq" readonly />

								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.</label>
								<div class="col-sm-9">
									<input type="text" name="lot_no_rq" id="lot_no_rq" class="form-control requiredRequali input-sm lot_no_rq">
									<div id="er_lot_no_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Quantity</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="lot_qty_rq" name="lot_qty_rq" readonly>
									<div id="er_lot_qty_rq"></div>
								</div>
							</div>
						</div>

					</div>

					<hr>

					<div class="row">
						<div class="col-sm-12">
							<strong>Visual Inspection</strong>
						</div>
					</div>


					<div class="row">

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Date Inspected</label>
								<div class="col-sm-9">
									<input class="form-control requiredRequali input-sm clear date-picker" type="text" name="date_ispected_rq" id="date_ispected_rq"/>
									<div id="er_date_ispected_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">WW#</label>
								<div class="col-sm-3">
									<input type="text" class="form-control input-sm clear" id="ww_rq" name="ww_rq">
									<div id="er_ww_rq"></div>
								</div>
								<label class="control-label col-sm-3">FY#</label>
								<div class="col-sm-3">
									<input type="text" class="form-control input-sm clear" id="fy_rq" name="fy_rq" readonly>
									<div id="qr_fy_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Time Inspected</label>
								<div class="col-sm-4">
									<input autocomplete="off" type="text" data-format="hh:mm A" class="form-control requiredRequali input-sm clear clockface_1" name="time_ins_from_rq" id="time_ins_from_rq"/>
									<div id="er_time_ins_from_rq"></div>
								</div>
								<div class="col-sm-1"></div>
								<div class="col-sm-4">
									<input autocomplete="off" type="text" data-format="hh:mm A" class="form-control requiredRequali input-sm clear clockface_1" name="time_ins_to_rq" id="time_ins_to_rq"/>
									<div id="er_time_ins_to_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Shift</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear show-tick" name="shift_rq" id="shift_rq">
									<div id="er_shift_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Inspector</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm" name="inspector_rq" id="inspector_rq"/>
									<div id="er_inspector_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Submission</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear show-tick" name="submission_rq" id="submission_rq">
									<div id="er_submission_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Judgement</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear" id="judgement_rq" name="judgement_rq">
									<div id="er_judgement_rq"></div>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Inspected</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear" id="lot_inspected_rq" name="lot_inspected_rq">
									<div id="er_lot_inspected_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot Accepted</label>
								<div class="col-sm-9">
									<input type="text" class="form-control requiredRequali input-sm clear" id="lot_accepted_rq" name="lot_accepted_rq">
									<div id="er_lot_accepted_rq"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" id="no_defects_label_rq">No. of Defectives</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="no_of_defects_rq" name="no_of_defects_rq">
									<div id="er_no_of_defects"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Remarks</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="remarks_rq" name="remarks_rq">
									<input type="hidden" class="form-control input-sm clear" id="status_rq" name="status_rq">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" id="mode_defects_label_rq">Mode of Defects</label>
								<div class="col-sm-4">
									<button type="button" class="btn blue btn_mod_rq" id="btn_mod_rq">
                                        <i class="fa fa-plus-circle"></i> Add Mode of Defects
                                    </button>
								</div>
							</div>
						</div>

					</div>


				</div>
				<div class="modal-footer">
					<button type="button" onclick="javascript:saveRequalification();" class="btn btn-success" id="btn_savemodal_rq"><i class="fa fa-floppy-disk-o"></i>Save</button>
					<button type="button" id="btn_deleteRequali" class="btn btn-success red"><i class="fa fa-trash"></i>Delete</button>
					<a href="javascript:;" class="btn grey-gallery btn_clearModal" id="btn_clearmodal_rq"><i class="fa fa-eraser"></i>Clear</a>
					<a href="javascript:;" data-dismiss="modal" id="btn_back_rq" class="btn btn-danger btn_backModal"><i class="fa fa-reply"></i>Back</a>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODE OF DEFECTS -->
<div id="mod_inspectionModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Mode of Defect</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">

							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.</label>
								<div class="col-sm-9">
									<select id="selected_lot" name="selected_lot" class="form-control input-sm">
									</select>
									<div id="er_lot"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Mode of Defect</label>
								<div class="col-sm-9">
									<select id="mod_inspection" name="mod_inspection" class="form-control input-sm clear mod_inspection clearselect" <?php echo($state);?>>
									</select>
									<div id="er_mod"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Quantity</label>
								<div class="col-sm-9">
									<input type="text" id="qty_inspection" name="qty_inspection" class="form-control input-sm">
									<input type="hidden" id="status_inspection" name="status_inspection" class="form-control input-sm">
									<input type="hidden" id="mod_id" name="mod_id" class="form-control input-sm">
									<div id="er_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-12">
									<button type="button" id="bt_save_modeofdefectsinspection" class="btn btn-sm green pull-right">Save</button>
									<button type="button" id="bt_delete_modeofdefectsinspection" class="btn btn-sm red pull-right">Delete</button>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-hover table-bordered table-striped" id="tbl_modeofdefect">
									<thead>
										<tr>
											<td class="table-checkbox">
                                                <input type="checkbox" class="group-checkable checkAllitemsinspection" />
                                            </td>
                                            <td></td>											
											<td>Mode of Defects</td>
											<td>Quantity</td>
											<td>Lot No.</td>
										</tr>
									</thead>
									<tbody id="tblformodinspection">
                                    	<!-- table records here -->
                                    </tbody>
								</table>
								<input type="hidden" name="mod_count" id="mod_count">
								<input type="hidden" name="mod_total_qty" id="mod_total_qty">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn btn-danger" id=inspectionmod_close>Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODE OF DEFECTS CH3CKL3V3L -->
<div id="mod_checklevelModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Mode of Defect</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-sm-3">Mode of Defect</label>
								<div class="col-sm-9">
									<select class="form-control input-sm show-tick" name="mod_checklevel" id="mod_checklevel">
										<option value=""></option>
									</select>
									<div id="er_modcl"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Quantity</label>
								<div class="col-sm-9">
									<input type="text" id="qty_checklevel" name="qty_checklevel" class="form-control input-sm">
									<input type="hidden" id="status_checklevel" name="status_checklevel" class="form-control input-sm">
									<input type="hidden" id="id_checklevel" name="id_checklevel" class="form-control input-sm">
									<div id="er_qtycl"></div>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-12">
									<button type="button" onclick="javascript:checklevel_save();" id="btn_checklevel_save" class="btn btn-sm green pull-right">Save</button>
									<button type="button" onclick="javascript:deleteAllcheckedchecklevel();" id="btn_deleteAllcheckedchecklevel" class="btn btn-sm red pull-right">Delete</button>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-hover table-bordered table-striped" id="tbl_modeofdefect">
									<thead>
										<tr>
											<td class="table-checkbox" style="width: 5%">
													<input type="checkbox" class="group-checkable checkAllitemschecklevel" />
											</td>
											<td></td>
											<td>#</td>
											<td>Mode of Defects</td>
											<td>Quantity</td>
										</tr>
									</thead>
									<tbody id="tblformodchecklevel">
                                    	<!-- table records here -->
                                    </tbody>
								</table>
							</div>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" id=checklabelmod_close class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODE OF DEFECTS REQUEALIFICATION -->
<div id="mod_requalificationModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Mode of Defect</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-sm-3">Mode of Defect</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm show-tick" name="mod_rq" id="mod_rq">
									<div id="er_modrq"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Quantity</label>
								<div class="col-sm-9">
									<input type="text" id="qty_rq" name="qty_rq" class="form-control input-sm">
									<input type="hidden" id="status_requalification" name="status_requalification" class="form-control input-sm">
									<input type="hidden" id="id_requalification" name="id_requalification" class="form-control input-sm">
									<div id="er_qtyrq"></div>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-12">
									<button type="button" onclick="javascript:saveModeOfDefectsRequali();" id="btn_saveModeOfDefectsRequali" class="btn btn-sm green pull-right">Save</button>
									<button type="button" id="btn_deletemodrq" class="btn btn-sm red pull-right">Delete</button>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-hover table-bordered table-striped" id="tbl_modeofdefect">
									<thead>
										<tr>
											<td class="table-checkbox" style="width: 5%">
												<input type="checkbox" class="group-checkable checkAllitemsrequalification" />
											</td>
											<td></td>
											<td>#</td>
											<td>Mode of Defects</td>
											<td>Quantity</td>
										</tr>
									</thead>
									<tbody id="tblformodrequalification">
                                    	<!-- table records here -->
                                    </tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" id=rqmod_close class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- GROUP BY MODAL -->
<div id="GroupByModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog modal-lg gray-gallery">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Group Items By:</h4>
            </div>
            <form method="GET" action="{{ url('/iqc-calculate-dppm') }}" id="frm_DPPM">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Date from</span>
                                    <input type="text" class="form-control date-picker input-sm " id="gfrom" name="gfrom" autocomplete="false">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Date to</span>
                                    <input type="text" class="form-control date-picker input-sm " id="gto" name="gto" autocomplete="false">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Group By</span>
                                    <select class="form-control input-sm show-tick" name="field1" id="field1">
                                        <option value=""></option>
										<option value="invoice_no">Invoice No</option>
										<option value="inspector">Inspector</option>
										<option value="date_ispected">Date Inspected</option>
										<option value="time_ins_from">Inspection Time</option>
										<option value="app_no">Application Ctrl No</option>
										<option value="app_date">App Date</option>
										<option value="app_time">App Time</option>
										<option value="fy">FY</option>
										<option value="ww">WW</option>
										<option value="submission">Submission</option>
										<option value="partcode">Part Code</option>
										<option value="partname">Part Name</option>
										<option value="supplier">Supplier</option>
										<option value="lot_no">Lot No</option>
										<option value="aql">AQL</option>
										<option value="lot_qty">Lot Qty</option>
										<option value="type_of_inspection">Types of Inspection</option>
										<option value="severity_of_inspection">Severity of Inspection</option>
										<option value="inspection_lvl">Inspection Level</option>
										<option value="accept">Accept</option>
										<option value="reject">Reject</option>
										<option value="shift">Shift</option>
										<option value="lot_inspected">Lot Inspected</option>
										<option value="lot_accepted">Lot Accepted</option>
										<option value="sample_size">Sample Size</option>
										<option value="no_of_defects">No. of Defects</option>
										<option value="remarks">Remarks</option>
										<option value="classification">Classification</option>
										<option value="judgement">Judgement</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">=</span>
                                    <select class="form-control input-sm show-tick" name="content1" id="content1">
                                        <!-- append here -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Group By</span>
                                    <select class="form-control input-sm show-tick" name="field2" id="field2">
										<option value=""></option>
										<option value="invoice_no">Invoice No</option>
										<option value="inspector">Inspector</option>
										<option value="date_ispected">Date Inspected</option>
										<option value="time_ins_from">Inspection Time</option>
										<option value="app_no">Application Ctrl No</option>
										<option value="app_date">App Date</option>
										<option value="app_time">App Time</option>
										<option value="fy">FY</option>
										<option value="ww">WW</option>
										<option value="submission">Submission</option>
										<option value="partcode">Part Code</option>
										<option value="partname">Part Name</option>
										<option value="supplier">Supplier</option>
										<option value="lot_no">Lot No</option>
										<option value="aql">AQL</option>
										<option value="lot_qty">Lot Qty</option>
										<option value="type_of_inspection">Types of Inspection</option>
										<option value="severity_of_inspection">Severity of Inspection</option>
										<option value="inspection_lvl">Inspection Level</option>
										<option value="accept">Accept</option>
										<option value="reject">Reject</option>
										<option value="shift">Shift</option>
										<option value="lot_inspected">Lot Inspected</option>
										<option value="lot_accepted">Lot Accepted</option>
										<option value="sample_size">Sample Size</option>
										<option value="no_of_defects">No. of Defects</option>
										<option value="remarks">Remarks</option>
										<option value="classification">Classification</option>
										<option value="judgement">Judgement</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">=</span>
                                    <select class="form-control input-sm show-tick" name="content2" id="content2">
                                        <!-- append here -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Group By</span>
                                    <select class="form-control input-sm show-tick" name="field3" id="field3">
										<option value=""></option>
										<option value="invoice_no">Invoice No</option>
										<option value="inspector">Inspector</option>
										<option value="date_ispected">Date Inspected</option>
										<option value="time_ins_from">Inspection Time</option>
										<option value="app_no">Application Ctrl No</option>
										<option value="app_date">App Date</option>
										<option value="app_time">App Time</option>
										<option value="fy">FY</option>
										<option value="ww">WW</option>
										<option value="submission">Submission</option>
										<option value="partcode">Part Code</option>
										<option value="partname">Part Name</option>
										<option value="supplier">Supplier</option>
										<option value="lot_no">Lot No</option>
										<option value="aql">AQL</option>
										<option value="lot_qty">Lot Qty</option>
										<option value="type_of_inspection">Types of Inspection</option>
										<option value="severity_of_inspection">Severity of Inspection</option>
										<option value="inspection_lvl">Inspection Level</option>
										<option value="accept">Accept</option>
										<option value="reject">Reject</option>
										<option value="shift">Shift</option>
										<option value="lot_inspected">Lot Inspected</option>
										<option value="lot_accepted">Lot Accepted</option>
										<option value="sample_size">Sample Size</option>
										<option value="no_of_defects">No. of Defects</option>
										<option value="remarks">Remarks</option>
										<option value="classification">Classification</option>
										<option value="judgement">Judgement</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">=</span>
                                    <select class="form-control input-sm show-tick" name="content3" id="content3">
                                        <!-- append here -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btn_calculate">Calculate</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SEARCH MODAL -->
<div id="SearchModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Search</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-sm-3">Part Code</label>
								<div class="col-sm-7">
									<select id="search_partcode" name="search_partcode" class="form-control input-sm clear search_partcode clearselect">
									</select>
									{{-- <input type="text" class="form-control input-sm clear" id="search_partcode" name="search_partcode"> --}}
									<span id="search_partcode_error" style="color:red"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">From</label>
								<div class="col-sm-7">
									<input class="form-control input-sm date-picker" type="text" data-date-format='yyyy-mm-dd' name="search_from" id="search_from"/>
									<!-- <div id="er_search_from"></div> -->
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">To</label>
								<div class="col-sm-7">
									<input class="form-control input-sm date-picker" type="text" data-date-format='yyyy-mm-dd' name="search_to" id="search_to"/>
									<!-- <div id="er_search_to"></div> -->
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="btn_searchnow" class="btn btn-success">OK</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- HISTORY MODAL -->
<div id="historyModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">History</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">Part Code</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="hs_partcode" name="hs_partcode">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="hs_lotno" name="hs_lotno">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Judgement</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm clear" id="hs_judgement" name="hs_judgement">
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-sm-3">From</label>
								<div class="col-sm-9">
									<input class="form-control input-sm date-picker" type="text" data-date-format='yyyy-mm-dd' name="hs_from" id="hs_from"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">To</label>
								<div class="col-sm-9">
									<input class="form-control input-sm date-picker" type="text" data-date-format='yyyy-mm-dd' name="hs_to" id="hs_to"/>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12 table-responsive">
							<table class="table table-bordered table-striped table-fixedheader" style="font-size: 10px;">
								<thead>
									<tr>
										<td style="width: 11.67%">Invoice No.</td>
										<td style="width: 11.67%">Part Code</td>
										<td style="width: 30.67%">Part Name</td>
										<td style="width: 16.67%">Lot No.</td>
										<td style="width: 12.67%">Lot Qty.</td>
										<td style="width: 16%">Jugdement</td>
									</tr>
								</thead>
								<tbody id="tblhistorybody"></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="btn_searchHistory" class="btn btn-success">OK</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Upload -->
<div id="uploadModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Upload Data File</h4>
			</div>
			<form class="form-horizontal" method="POST" enctype="multipart/form-data" id="frm_upload" action="{{ url('/upload-iqc') }}">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-md-3">Inspection Data</label>
								<div class="col-md-9">
									<input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
									<input type="file" class="filestyle" data-buttonName="btn-primary" name="inspection_data" id="inspection_data" {{$readonly}}>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-md-3">Mode of Defects</label>
								<div class="col-md-9">
									<input type="file" class="filestyle" data-buttonName="btn-primary" name="inspection_mod" id="inspection_mod" {{$readonly}}>
								</div>
							</div>

							<hr/>

							<div class="form-group">
								<label class="control-label col-md-3">Re-qualification Data</label>
								<div class="col-md-9">
									<input type="file" class="filestyle" data-buttonName="btn-primary" name="requali_data" id="requali_data" {{$readonly}}>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-md-3">Mode of Defects</label>
								<div class="col-md-9">
									<input type="file" class="filestyle" data-buttonName="btn-primary" name="requali_mod" id="requali_mod" {{$readonly}}>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="btn_uploadfile" class="btn btn-success">Upload</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="LotNoModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lot Numbers</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-7">
						<div class="form-group row">
							<label class="control-label col-md-2">Invoice No.</label>
							<div class="col-md-9">
								<input type="text" class="form-control input-sm" name="lot_no_invoice_no" id="lot_no_invoice_no" readonly>
							</div>
						</div>

						<div class="form-group row">
							<label class="control-label col-md-2">Part Code</label>
							<div class="col-md-9">
								<input type="text" class="form-control input-sm" name="lot_no_part_code" id="lot_no_part_code" readonly>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="" id ="div_tbl_available_lot">
						<h4 class="text-success"><b>Pending Lot Number</b></h4>
						<table class="table table-striped table-condensed table-bordered" id="tbl_available_lot">
							<thead>
								<td width="5%">
									<input type="checkbox" id="check_all_items">
								</td>
								<td>Judgement</td>
								<td>Part Code</td>
								<td>Description</td>
								<td>Lot No.</td>
								<td>Qty.</td>
								<td>Drawing No.</td>
								<td>Supplier</td>
							</thead>
							<tbody id="tbl_available_lot_body"></tbody>
						</table>		
					</div>
					<div class="" id="div_tbl_lot_no">
						<h4  class="text-primary"><b>Selected Lot Number</b></h4>
						<table class="table table-striped table-condensed table-bordered" id="tbl_lot_no">
							<thead>
								<td width="5%">
									<input type="checkbox" id="check_all_lot_no">
								</td>
								<td>Lot No.</td>
								<td>Qty.</td>
							</thead>
							<tbody id="tbl_lot_no_body"></tbody>
						</table>
						<div class="col-md-12 text-center">
							<button type="button" class="btn red" id="btn_delete_lot_no">
                                    <i class="fa fa-trash"></i> Delete
                            </button>
						</div>
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
        		<button type="button" class="btn btn-success" id='insert_iqc_lot_no'>Save</button>
				<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
			</div>
		</div>
	</div>
</div>





<!-- MSG -->
<div id="confirmDeleteModal" class="modal fade" role="dialog" data-backdrop="static">
	 <div class="modal-dialog modal-sm gray-gallery">
		  <div class="modal-content ">
			   <div class="modal-header">
					<h4 class="modal-title">Delete</h4>
			   </div>
			   <div class="modal-body">
					<p>Are you sure do you want to delete?</p>
					<input type="hidden" name="delete_type" id="delete_type">
			   </div>
			   <div class="modal-footer">
			   		<button type="button" class="btn btn-primary" id="btn_deleteyes">Yes</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">No</button>
			   </div>
		  </div>
	 </div>
</div>

<!-- SORTING MODAL -->
<div id="sorting_Modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<div class="row">
					<div class="col-md-6">
						<h3 class="modal-title" style="font-weight: bold;">Sorting</h3>
					</div>

					<div class="col-md-6 text-right">
						<button type="button" data-dismiss="modal" class="btn btn-danger" id=sorting_close>Close</button>
					</div>
				</div>
			</div>
			
			<div class="modal-body">
				<form class="form-horizontal">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.:</label>
								<div class="col-sm-9">
									<input type="hidden" class="sorting_clear" id="sorting_index" name="sorting_index">
									<input type="hidden" class="sorting_clear" id="sorting_id" name="sorting_id">
									<input type="hidden" class="sorting_clear" id="sorting_mr_id" name="sorting_mr_id">
									<input type="hidden" class="sorting_clear" id="sorting_inv_id" name="sorting_inv_id">
									<input type="hidden" id="sorting_state" name="sorting_state" value="ADD">
									<select class="form-control input-sm lot_no sorting_clear_select2" name="sorting_lot_no" id="sorting_lot_no"></select>									
									<div id="er_sorting_lot_no"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Total Quantity:</label>
								<div class="col-sm-9">
									<input type="number" id="sorting_total_qty" name="sorting_total_qty" class="form-control input-sm sorting_clear">
									<div id="er_ssorting_total_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Good Quantity:</label>
								<div class="col-sm-9">
									<input type="number" id="sorting_good_qty" name="sorting_good_qty" class="form-control input-sm sorting_clear">
									<div id="er_sorting_good_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">NG Quantity:</label>
								<div class="col-sm-9">
									<input type="number" id="sorting_ng_qty" name="sorting_ng_qty" class="form-control input-sm sorting_clear">
									<div id="er_sorting_ng_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Actual Quantity:</label>
								<div class="col-sm-9">
									<input type="number" id="sorting_act_qty" name="sorting_act_qty" class="form-control input-sm sorting_clear">
									<div id="er_sorting_act_qty"></div>
								</div>
							</div>

							
							<div class="form-group">
								<label class="control-label col-sm-3">Category:</label>
								<div class="col-sm-9">
									<select id="sorting_category" name="sorting_category" class="form-control input-sm sorting_clear">
										<option value=""></option>
										<option value="Local Disposal">Local Disposal</option>
										<option value="Return To YEC">Return To YEC</option>
										<option value="Return To Supplier">Return To Supplier</option>
									</select>
									<div id="er_sorting_category"></div>
								</div>
							</div>

							<div class="form-group" id="sorting_disposal_date_div" style="display:none;">
								<label class="control-label col-sm-3" id="sorting_date">Date:</label>
								<div class="col-sm-9">
									<input class="form-control input-sm sorting_clear date-picker actual" type="text" name="sorting_disposal_date" id="sorting_disposal_date" data-date-format='yyyy-mm-dd' autocomplete="off"/>
									<div id="er_sorting_disposal_date"></div>
								</div>
							</div>

							<div class="form-group" id="sorting_disposal_slip_div" style="display:none;">
								<label class="control-label col-sm-3">Disposal Slip No.:</label>
								<div class="col-sm-9">
									<input type="text" id="sorting_disposal_slip_no" name="sorting_disposal_slip_no" class="form-control input-sm sorting_clear">
									<div id="er_sorting_disposal_slip_no"></div>
								</div>
							</div>

							<div class="form-group" id="sorting_ngr_control_no_div" style="display:none;">
								<label class="control-label col-sm-3">NGR Control No.:</label>
								<div class="col-sm-9">
									<input type="text" id="sorting_ngr_control_no" name="sorting_ngr_control_no" class="form-control input-sm sorting_clear">
									<div id="er_sorting_ngr_control_no"></div>
								</div>
							</div>

							<div class="form-group" id="sorting_packinglist_no_div" style="display:none;">
								<label class="control-label col-sm-3">Packing List No.:</label>
								<div class="col-sm-9">
									<input type="text" id="sorting_packinglist_no" name="sorting_packinglist_no" class="form-control input-sm sorting_clear">
									<div id="er_sorting_packinglist_no"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Remarks:</label>
								<div class="col-sm-9">
									<input type="text" id="sorting_remarks" name="sorting_remarks" class="form-control input-sm sorting_clear">
									<div id="er_sorting_remarks"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm green btn-block" id="btn_add_sorting">
								<i class="fa fa-plus"></i> Add
							</button>
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm blue btn-block" id="btn_save_sorting" disabled>
								<i class="fa fa-floppy-o"></i> Save
							</button>
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm grey-gallery btn-block" id="btn_cancel_sorting" disabled>
								<i class="fa fa-times"></i> Cancel
							</button>
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm red btn-block" id="btn_delete_sorting" disabled>
								<i class="fa fa-trash"></i> Delete
							</button>
						</div>
					</div>
				</form>

				<hr>

				<div class="row">
					<div class="col-md-12">
						<table class="table table-hover table-bordered table-striped" id="tbl_sorting">
							<thead>
								<tr>
									<td class="table-checkbox">
										<input type="checkbox" class="group-checkable" id="sorting_check_all"/>
									</td>
									<td></td>
									<td>Lot No.</td>
									<td>Good Qty</td>
									<td>NG Qty</td>
									<td>Actual Qty</td>
									<td>Category</td>
									<td>Date</td>
									<td>Disposal Slip #</td>
									<td>NGR Control #</td>
									<td>Packing List #</td>
									<td>Remarks</td>
								</tr>
							</thead>
							<tbody id="tblforsorting">
								<!-- table records here -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>

<!-- REWORK MODAL -->
<div id="rework_Modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<div class="row">
					<div class="col-md-6">
						<h3 class="modal-title" style="font-weight: bold;">Rework</h3>
					</div>

					<div class="col-md-6 text-right">
						<button type="button" data-dismiss="modal" class="btn btn-danger" id=rework_close>Close</button>
					</div>
				</div>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.:</label>
								<div class="col-sm-9">
									<input type="hidden" class="rework_clear" id="rework_index" name="rework_index">
									<input type="hidden" class="rework_clear" id="rework_id" name="rework_id">
									<input type="hidden" class="rework_clear" id="rework_mr_id" name="rework_mr_id">
									<input type="hidden" class="rework_clear" id="rework_inv_id" name="rework_inv_id">
									<input type="hidden" id="rework_state" name="rework_state" value="ADD">
									<select class="form-control input-sm lot_no rework_clear_select2" name="rework_lot_no" id="rework_lot_no"></select>
									<div id="er_rework_lot_no"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Total Quantity:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_total_qty" name="rework_total_qty" class="form-control input-sm rework_clear">
									<div id="er_rework_total_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Good Quantity:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_good_qty" name="rework_good_qty" class="form-control input-sm rework_clear">
									<div id="er_rework_good_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">NG Quantity:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_ng_qty" name="rework_ng_qty" class="form-control input-sm rework_clear">
									<div id="er_rework_ng_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Actual Quantity:</label>
								<div class="col-sm-9">
									<input type="number" id="rework_act_qty" name="rework_act_qty" class="form-control input-sm rework_clear">
									<div id="er_rework_act_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Category:</label>
								<div class="col-sm-9">
									<select id="rework_category" name="rework_category" class="form-control input-sm rework_clear">
										<option value=""></option>
										<option value="Local Disposal">Local Disposal</option>
										<option value="Return To YEC">Return To YEC</option>
										<option value="Return To Supplier">Return To Supplier</option>
									</select>
									<div id="er_rework_category"></div>
								</div>
							</div>

							<div class="form-group" id="rework_disposal_date_div" style="display:none;">
								<label class="control-label col-sm-3" id="rework_date">Date:</label>
								<div class="col-sm-9">
									<input class="form-control input-sm rework_clear date-picker actual" type="text" name="rework_disposal_date" id="rework_disposal_date" data-date-format='yyyy-mm-dd' autocomplete="off"/>
									<div id="er_rework_disposal_date"></div>
								</div>
							</div>

							<div class="form-group" id="rework_disposal_slip_div" style="display:none;">
								<label class="control-label col-sm-3">Disposal Slip No.:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_disposal_slip_no" name="rework_disposal_slip_no" class="form-control input-sm rework_clear">
									<div id="er_rework_disposal_slip_no"></div>
								</div>
							</div>

							<div class="form-group" id="rework_ngr_control_no_div" style="display:none;">
								<label class="control-label col-sm-3">NGR Control No.:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_ngr_control_no" name="rework_ngr_control_no" class="form-control input-sm rework_clear">
									<div id="er_rework_ngr_control_no"></div>
								</div>
							</div>

							<div class="form-group" id="rework_packinglist_no_div" style="display:none;">
								<label class="control-label col-sm-3">Packing List No.:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_packinglist_no" name="rework_packinglist_no" class="form-control input-sm rework_clear">
									<div id="er_rework_packinglist_no"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Remarks:</label>
								<div class="col-sm-9">
									<input type="text" id="rework_remarks" name="rework_remarks" class="form-control input-sm rework_clear">
									<div id="er_rework_remarks"></div>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm green btn-block" id="btn_add_rework">
								<i class="fa fa-plus"></i> Add
							</button>
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm blue btn-block" id="btn_save_rework" disabled>
								<i class="fa fa-floppy-o"></i> Save
							</button>
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm grey-gallery btn-block" id="btn_cancel_rework" disabled>
								<i class="fa fa-times"></i> Cancel
							</button>
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-sm red btn-block" id="btn_delete_rework" disabled>
								<i class="fa fa-trash"></i> Delete
							</button>
						</div>
					</div>

					<hr>

					<div class="row">
						<div class="col-md-12">
							<table class="table table-hover table-bordered table-striped" id="tbl_rework">
								<thead>
									<tr>
										<td class="table-checkbox">
											<input type="checkbox" class="group-checkable" id="rework_check_all" />
										</td>
										<td></td>
										<td>Lot No.</td>
										<td>Good Qty</td>
										<td>NG Qty</td>
										<td>Actual Qty</td>
										<td>Category</td>
										<td>Date</td>
										<td>Disposal Slip #</td>
										<td>NGR Control #</td>
										<td>Packing List #</td>
										<td>Remarks</td>
									</tr>
								</thead>
								<tbody id="tblforrework">
									<!-- table records here -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- RTV MODAL -->
<div id="rtv_Modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<div class="row">
					<div class="col-md-6">
						<h3 class="modal-title" style="font-weight: bold;">RTV</h3>
					</div>

					<div class="col-md-6 text-right">
						<button type="button" data-dismiss="modal" class="btn btn-danger" id=rtv_close>Close</button>
					</div>
				</div>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label col-sm-3">Lot No.:</label>
								<div class="col-sm-9">
									<input type="hidden" class="rtv_clear" id="rtv_index" name="rtv_index">
									<input type="hidden" class="rtv_clear" id="rtv_id" name="rtv_id">
									<input type="hidden" class="rtv_clear" id="rtv_mr_id" name="rtv_mr_id">
									<input type="hidden" class="rtv_clear" id="rtv_inv_id" name="rtv_inv_id">
									<input type="hidden" id="rtv_state" name="rtv_state" value="ADD">
									<select class="form-control input-sm lot_no rtv_clear_select2" name="rtv_lot_no" id="rtv_lot_no"></select>
									<div id="er_rtv_lot_no"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Total Quantity:</label>
								<div class="col-sm-9">
									<input type="text" id="rtv_total_qty" name="rtv_total_qty" class="form-control input-sm rtv_clear">
									<div id="er_rtv_total_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">RTV Quantity:</label>
								<div class="col-sm-9">
									<input type="text" id="rtv_qty" name="rtv_qty" class="form-control input-sm rtv_clear">
									<div id="er_rtv_qty"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Category:</label>
								<div class="col-sm-9">
									<select id="rtv_category" name="rtv_category" class="form-control input-sm rtv_clear">
										<option value=""></option>
										<option value="Local Disposal">Local Disposal</option>
										<option value="Return To YEC">Return To YEC</option>
										<option value="Return To Supplier">Return To Supplier</option>
									</select>
									<div id="er_rtv_category"></div>
								</div>
							</div>

							<div class="form-group" id="rtv_disposal_date_div" style="display:none;">
								<label class="control-label col-sm-3" id="rtv_date">Date:</label>
								<div class="col-sm-9">
									<input class="form-control input-sm rtv_clear date-picker actual" type="text" name="rtv_disposal_date" id="rtv_disposal_date" data-date-format='yyyy-mm-dd' autocomplete="off"/>
									<div id="er_rtv_disposal_date"></div>
								</div>
							</div>

							<div class="form-group" id="rtv_disposal_slip_div" style="display:none;">
								<label class="control-label col-sm-3">Disposal Slip No.:</label>
								<div class="col-sm-9">
									<input type="text" id="rtv_disposal_slip_no" name="rtv_disposal_slip_no" class="form-control input-sm rtv_clear">
									<div id="er_rtv_disposal_slip_no"></div>
								</div>
							</div>

							<div class="form-group" id="rtv_ngr_control_no_div" style="display:none;">
								<label class="control-label col-sm-3">NGR Control No.:</label>
								<div class="col-sm-9">
									<input type="text" id="rtv_ngr_control_no" name="rtv_ngr_control_no" class="form-control input-sm rtv_clear">
									<div id="er_rtv_ngr_control_no"></div>
								</div>
							</div>

							<div class="form-group" id="rtv_packinglist_no_div" style="display:none;">
								<label class="control-label col-sm-3">Packing List No.:</label>
								<div class="col-sm-9">
									<input type="text" id="rtv_packinglist_no" name="rtv_packinglist_no" class="form-control input-sm rtv_clear">
									<div id="er_rtv_packinglist_no"></div>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3">Remarks:</label>
								<div class="col-sm-9">
									<input type="text" id="rtv_remarks" name="rtv_remarks" class="form-control input-sm rtv_clear">
									<div id="er_rtv_remarks"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<button type="button" class="btn btn-sm green btn-block" id="btn_add_rtv">
								<i class="fa fa-plus"></i> Add
							</button>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-sm blue btn-block" id="btn_save_rtv" disabled>
								<i class="fa fa-floppy-o"></i> Save
							</button>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-sm grey-gallery btn-block" id="btn_cancel_rtv" disabled>
								<i class="fa fa-times"></i> Cancel
							</button>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-sm red btn-block" id="btn_delete_rtv" disabled>
								<i class="fa fa-trash"></i> Delete
							</button>
						</div>
					</div>

					<hr>

					<div class="row">
						<div class="col-md-12">
							<table class="table table-hover table-bordered table-striped" id="tbl_rtv">
								<thead>
									<tr>
										<td class="table-checkbox">
											<input type="checkbox" class="group-checkable" id="rtv_check_all"/>
										</td>
										<td></td>
										<td>Lot No.</td>
										<td>Total Qty.</td>
										<td>RTV Qty.</td>
										<td>Category</td>
										<td>Date</td>
										<td>Disposal Slip #</td>
										<td>NGR Control #</td>
										<td>Packing List #</td>
										<td>Remarks</td>
									</tr>
								</thead>
								<tbody id="tblforrtv">
									<!-- table records here -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>