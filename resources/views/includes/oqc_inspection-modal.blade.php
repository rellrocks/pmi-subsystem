<div id="inspection_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery modal-xl">
        <div class="modal-content ">
            <form class=form-horizontal method="POST" action="{{ url('/oqc-save-inspection') }}" id="frm_inspection">
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <th colspan="2" class="text-right">
                                {{ csrf_field() }}
                                <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                            </th>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="form-group" id="assembly_line_div">
                                    <label class="control-label col-sm-3">Assembly Line</label>
                                    <div class="col-sm-9">
                                        <select class="form-control enter input-sm validate clear" name="assembly_line" id="assembly_line">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="assembly_line_msg"></strong>
                                        </span>
                                        <input type="hidden" class="form-control enter input-sm validate clear" id="inspection_id" name="inspection_id">
                                    </div>
                                </div>
                                <div class="form-group" id="lot_no_div">
                                    <label class="control-label col-sm-3">Lot No.</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="lot_no" name="lot_no">
                                        <span class="help-block">
                                            <strong id="lot_no_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="app_date_div">
                                    <label class="control-label col-sm-3">Application Date</label>
                                    <div class="col-sm-9">
                                        <input class="form-control enter input-sm validate clear" type="date" name="app_date" id="app_date" />
                                        <span class="help-block">
                                            <strong id="app_date_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="app_time_div">
                                    <label class="control-label col-sm-3">Application Time</label>
                                    <div class="col-sm-9">
                                        <!-- <input type="text" class="form-control enter input-sm validate clear" name="app_time" id="app_time"/> -->
                                        <input type="text" data-format="hh:mm A" class="form-control enter input-sm validate clear timepicker" name="app_time" id="app_time"/>
                                        <span class="help-block">
                                            <strong id="app_time_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="prod_category_div">
                                    <label class="control-label col-sm-3">Product Category</label>
                                    <div class="col-sm-9">
                                        <select class="form-control enter input-sm validate clear" name="prod_category" id="prod_category">
                                            <option value=""></option>
                                            <option value="Automotive">Automotive</option>
                                            <option value="Non-Automotive">Non-Automotive</option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="prod_category_msg"></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group" id="workweek_div">
                                    <label class="control-label col-sm-3">Work Week</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control enter input-sm validate clear" id="workweek" name="workweek" min = 0x>
                                        <span class="help-block">
                                            <strong id="workweek_msg"></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group" id="serial_no_div">
                                    <label class="control-label col-sm-3"></label>
                                    <div class="col-sm-9">
                                        <button type="button" class="btn btn-sm blue btn-block" id="btn_serial_no">Serial No.</button>
                                    </div>
                                </div>

                            </td>

                            <td width="50%">
                                <div class="form-group">
                                    <label class="control-label col-sm-3"></label>
                                    <div class="md-checkbox-inline">
                                        <div class="md-checkbox">
                                            <input type="checkbox" id="is_probe" class="md-check" name="is_probe" value="0">
                                            <label for="is_probe">
                                            <span></span>
                                            <span class="check"></span>
                                            <span class="box"></span>
                                            Check if PROBE </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="po_no_div">
                                    <label class="control-label col-sm-3">P.O. No.</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control enter input-sm validate clear" id="po_no" name="po_no" maxlength="15">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn input-sm green" id="btn_getpodetails">
                                                    <i class="fa fa-arrow-circle-down"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <span class="help-block">
                                            <strong id="po_no_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="series_name_div">
                                    <label class="control-label col-sm-3">Device Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="series_name" name="series_name" readonly>
                                        <input type="hidden" class="form-control enter input-sm validate clear" id="series_code" name="series_code">
                                        <span class="help-block">
                                            <strong id="series_name_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="customer_div">
                                    <label class="control-label col-sm-3">Customer</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="customer" name="customer" readonly>
                                        <input type="hidden" class="form-control enter input-sm validate clear" id="customer_code" name="customer_code">
                                        <span class="help-block">
                                            <strong id="customer_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="po_qty_div">
                                    <label class="control-label col-sm-3">P.O. Qty</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="po_qty" name="po_qty">
                                        <span class="help-block">
                                            <strong id="po_qty_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="family_div">
                                    <label class="control-label col-sm-3">Family</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="family" id="family">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="family_msg"></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group" id="probe_lot_div">
                                    <label class="control-label col-sm-3"></label>
                                    <div class="col-sm-9">
                                        <button type="button" class="btn btn-sm blue btn-block" id="btn_probe_lot">Probe Pin Lot No.</button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2">Sampling Plan</th>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="form-group" id="type_of_inspection_div">
                                    <label class="control-label col-sm-3">Type of Inspection</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="type_of_inspection" id="type_of_inspection">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="type_of_inspection_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="severity_of_inspection_div">
                                    <label class="control-label col-sm-3">Severity of Inspection</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="severity_of_inspection" id="severity_of_inspection">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="severity_of_inspection_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="inspection_lvl_div">
                                    <label class="control-label col-sm-3">Inspection Level</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="inspection_lvl" id="inspection_lvl">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="inspection_lvl_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="lot_qty_div">
                                    <label class="control-label col-sm-3">Lot Quantity</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="lot_qty" name="lot_qty">
                                        <span class="help-block">
                                            <strong id="lot_qty_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td width="50%">
                                <div class="form-group" id="aql_div">
                                    <label class="control-label col-sm-3">AQL</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="aql" id="aql">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="aql_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="sample_size_div">
                                    <label class="control-label col-sm-3">Sample Size</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="sample_size" name="sample_size">
                                        <span class="help-block">
                                            <strong id="sample_size_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Accept</label>
                                    <div class="col-sm-9">
                                        <input type="number" min="0" max="1" class="form-control enter input-sm" id="accept" name="accept" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Reject</label>
                                    <div class="col-sm-9">
                                        <input type="number" min="0" max="1" class="form-control enter input-sm" id="reject" name="reject" readonly>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2">Visual Inspection</th>
                        </tr>

                        <tr>
                            <td width="50%">
                                <div class="form-group" id="date_inspected_div">
                                    <label class="control-label col-sm-3">Date Inspected</label>
                                    <div class="col-sm-9">
                                        <input class="form-control enter input-sm validate clear" type="date" name="date_inspected" id="date_inspected" />
                                        <span class="help-block">
                                            <strong id="date_inspected_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">WW#</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control input-sm clear" id="ww" name="ww" readonly>
                                    </div>
                                    <label class="control-label col-sm-3">FY#</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control input-sm clear" id="fy" name="fy" readonly>
                                    </div>
                                </div>
                                <div class="form-group" id="time_ins_div">
                                    <label class="control-label col-sm-3">Time Inspected</label>
                                    <div class="col-sm-4">
                                        {{-- <input type="text" data-format="hh:mm A" class="form-control enter input-sm validate clear timepicker" name="time_ins_from" id="time_ins_from"/> --}}
                                       <input autocomplete="off" type="text" class="form-control enter input-sm validate clear timepicker timepicker-no-seconds" name="time_ins_from" id="time_ins_from"/>
                                        <span class="help-block">
                                            <strong id="time_ins_msg"></strong>
                                        </span>
                                    </div>
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-4">
                                        {{-- <input type="text" data-format="hh:mm A" class="form-control enter input-sm validate clear timepicker" name="time_ins_to" id="time_ins_to"/> --}}
                                    <input autocomplete="off" type="text" class="form-control enter input-sm validate clear timepicker timepicker-no-seconds" name="time_ins_to" id="time_ins_to"/>
                                        <span class="help-block">
                                            <strong id="time_ins_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="shift_div">
                                    <label class="control-label col-sm-3">Shift</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="shift" id="shift">
                                            <option value=""></option>
                                            <option value="Shift A">Shift A</option>
                                            <option value="Shift B">Shift B</option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="shift_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Inspector</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control input-sm clear" id="inspector" name="inspector" readonly value="{{ Auth::user()->firstname }}" />
                                    </div>
                                </div>
                                <div class="form-group" id="submission_div">
                                    <label class="control-label col-sm-3">Submission</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate" name="submission" id="submission">
                                            <option value=""></option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="submission_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="coc_req_div">
                                    <label class="control-label col-sm-3">COC Requirements</label>
                                    <div class="col-sm-9">
                                        <select class=" form-control enter input-sm validate clear" name="coc_req" id="coc_req">
                                            <option value=""></option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <span class="help-block">
                                            <strong id="coc_req_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="judgement_div">
                                    <label class="control-label col-sm-3">Judgement</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="judgement" name="judgement" readonly>
                                        <span class="help-block">
                                            <strong id="judgement_div"></strong>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td width="50%">
                                <div class="form-group" id="lot_inspected_div">
                                    <label class="control-label col-sm-3">Lot Inspected</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="lot_inspected" name="lot_inspected">
                                        <span class="help-block">
                                            <strong id="lot_inspected_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="lot_accepted_div">
                                    <label class="control-label col-sm-3">Lot Accepted</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="lot_accepted" name="lot_accepted">
                                        <span class="help-block">
                                            <strong id="lot_accepted_msg"></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group" id="no_of_defects_div">
                                    <label class="control-label col-sm-3">No. of Defectives</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control input-sm clear" id="no_of_defects" name="no_of_defects" readonly>
                                    </div>
                                </div>
                                <div class="form-group" id="remarks_div">
                                    <label class="control-label col-sm-3">Remarks</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control enter input-sm validate clear" id="remarks" name="remarks">
                                        <input type="hidden" id="inspection_save_status" name="inspection_save_status">
                                    </div>
                                </div>
                                <div class="form-group" id="mode_of_defects_div">
                                    <label class="control-label col-sm-3">Mode of Defects</label>
                                    <div class="col-sm-4">
                                        <button type="button"  class="btn btn-sm blue btn-block" id="btn_mode_of_defects">
                                            <i class="fa fa-plus-circle"></i> Add Mode of Defects
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group" id="gauge_div">
                                    <label class="control-label col-sm-3">Gauge</label>
                                    <div class="col-sm-3">
                                        <select class=" form-control enter input-sm validate" id="gauge" name="gauge">
                                            <option value="0">Without</option>
                                            <option value="1">With</option>
                                        </select>
                                    </div>

                                    <label class="control-label col-sm-3">Accessory</label>
                                    <div class="col-sm-3">
                                        <select class=" form-control enter input-sm validate" id="accessory" name="accessory">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="yd_label_req_div">
                                    <label class="control-label col-sm-3">YD Label Requirements</label>
                                    <div class="col-sm-3">
                                        <select class=" form-control enter input-sm validate" id="yd_label_req" name="yd_label_req">
                                            <option value="0">Without</option>
                                            <option value="1">With</option>
                                        </select>
                                    </div>

                                    <label class="control-label col-sm-3">CHS Coating</label>
                                    <div class="col-sm-3">
                                        <select class=" form-control enter input-sm validate" id="chs_coating" name="chs_coating">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>


                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-right">
                                <button type="submit" class="btn btn-success" id="btn_savemodal" @if($is_supervisor === 0) {{ 'data-val' }}@endif>Save</button>
                                <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                            </th>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="search_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Search</h4>
            </div>
            <form class="form-horizontal" id="frm_oqc_search">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-sm-3">PO Number</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm" type="text" value="" name="search_po" id="search_po"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">From</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm date-picker" type="text" value="" name="search_from" id="search_from"/>
                            <div id="er_search_from"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">To</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm date-picker" type="text" value="" name="search_to" id="search_to"/>
                            <div id="er_search_to"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">Choose Parameter</label>
                        <div class="col-sm-7">
                            <select name="chosen" id="chosen" class = "form-control input-sm" >
                                <option value="" selected hidden>Workweek / Serial No. / Probe Pin Lot No.</option>
                                <option value="Workweek">Workweek</option>
                                <option value="Serial">Serial No.</option>
                                <option value="Probe">Probe Pin Lot No.</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-success" onclick="javascript:searchInspection();">
                        <i class="fa fa-search"></i> Search
                    </button> --}}
                    <a href="javascript:PDFReport();" class="btn btn-primary"> <!-- target="_tab" -->
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </a>
                    <a href="javascript:ExcelReport();" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <button type="button" data-dismiss="modal" class="btn btn-danger" id="btn_search-close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="report_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reports</h4>
            </div>
            <form class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-sm-3">PO Number</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm" type="text" name="rpt_po" id="rpt_po"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">From</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm date-picker" type="text" name="rpt_from" id="rpt_from"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">To</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm date-picker" type="text" name="rpt_to" id="rpt_to"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">Submission</label>
                        <div class="col-sm-7">
                            <select class="form-control input-sm" name="rpt_sub" id="rpt_sub">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="javascript:PDFReport();" class="btn btn-primary">
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </button>
                    <button type="button" onclick="javascript:ExcelReport();" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger" id="btn_report-close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="groupby_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg gray-gallery">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Group Items By:</h4>
            </div>
            <form method="POST" action="{{ url('/oqc-calculate-dppm') }}" id="frm_DPPM">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Date from</span>
                                    <input type="text" class="form-control date-picker input-sm grpfield " id="gfrom" name="gfrom" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Date to</span>
                                    <input type="text" class="form-control date-picker input-sm grpfield " id="gto" name="gto" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Group By</span>
                                    <select class="form-control input-sm grpfield show-tick" name="field1" id="field1">
                                        <option value=""></option>
                                        <option value="id">ID</option>
                                        <option value="date_inspected">Date Inspected</option>
                                        <option value="time_ins_from">Inspection Time</option>
                                        <option value="fy">FY#</option>
                                        <option value="ww">WW#</option>
                                        <option value="assembly_line">Assembly Line</option>
                                        <option value="submission">Submission</option>
                                        <option value="prod_category">Category</option>
                                        <option value="customer">Customer Name</option>
                                        <option value="family">Family</option>
                                        <option value="device_name">Device Name</option>
                                        <option value="po_no">P.O#</option>
                                        <option value="lot_no">Lot No</option>
                                        <option value="aql">AQL</option>
                                        <option value="lot_accepted">Lot Accepted</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">=</span>
                                    <select class="form-control input-sm grpfield show-tick" name="content1" id="content1" style="width:100%"></select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">Group By</span>
                                    <select class="form-control input-sm grpfield show-tick" name="field2" id="field2">
                                        <option value=""></option>
                                        <option value="id">ID</option>
                                        <option value="date_inspected">Date Inspected</option>
                                        <option value="time_ins_from">Inspection Time</option>
                                        <option value="fy">FY#</option>
                                        <option value="ww">WW#</option>
                                        <option value="assembly_line">Assembly Line</option>
                                        <option value="submission">Submission</option>
                                        <option value="prod_category">Category</option>
                                        <option value="customer">Customer Name</option>
                                        <option value="family">Family</option>
                                        <option value="device_name">Device Name</option>
                                        <option value="po_no">P.O#</option>
                                        <option value="lot_no">Lot No</option>
                                        <option value="aql">AQL</option>
                                        <option value="lot_accepted">Lot Accepted</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">=</span>
                                    <select class="form-control input-sm grpfield show-tick" name="content2" id="content2">
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
                                    <select class="form-control input-sm grpfield show-tick" name="field3" id="field3">
                                        <option value=""></option>
                                        <option value="id">ID</option>
                                        <option value="date_inspected">Date Inspected</option>
                                        <option value="time_ins_from">Inspection Time</option>
                                        <option value="fy">FY#</option>
                                        <option value="ww">WW#</option>
                                        <option value="assembly_line">Assembly Line</option>
                                        <option value="submission">Submission</option>
                                        <option value="prod_category">Category</option>
                                        <option value="customer">Customer Name</option>
                                        <option value="family">Family</option>
                                        <option value="device_name">Device Name</option>
                                        <option value="po_no">P.O#</option>
                                        <option value="lot_no">Lot No</option>
                                        <option value="aql">AQL</option>
                                        <option value="lot_accepted">Lot Accepted</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">=</span>
                                    <select class="form-control input-sm grpfield show-tick" name="content3" id="content3">
                                        <!-- append here -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="calID">Calculate</button>
                    <button type="button" class="btn grey-gallery" id="btn_clear_grpby">Clear</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="probe_item_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg gray-gallery">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Select Probe Items</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-condensed table-bordered table-striped" id="tbl_probe" nowrap>
                            <thead>
                                <tr>
                                    <td></td>
                                    <td>Device Code</td>
                                    <td>Device Name</td>
                                    <td>Customer Code</td>
                                    <td>Customer Name</td>
                                    <td>BUNR</td>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="serial_no_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery modal-lg">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Serial No</h4>
            </div>

            <div class="modal-body">
                <form method="POST" method="POST" action="{{ url('/oqc-upload-serial-no') }}" accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal" id="frmSerialUpload">
                    {{ csrf_field() }}

                    <div class="form-group row">
                        <label class="control-label col-md-3">Excel Serial No.</label>
                        <div class="col-md-7">
                            <input type="file" class="filestyle" data-buttonName="btn-primary" name="serial_nos" id="serial_nos" accept=".xlsx, .xls, .XLSX, XLS" {{$readonly}}>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block" id="btn_upload_serial">Upload File</button>
                        </div>
                    </div>
                </form>

                <div class="form-horizontal" id="frm_serial_no">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" id="serial_no_name_div">
                                <label class="control-label col-sm-2">Serial No.</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control input-sm validateSerialNo clear_serial_no" name="serial_no" id="serial_no" autocomplete="off">
                                    <input type="hidden" id="serial_save_status" name="serial_save_status" class="">
                                    <input type="hidden" id="serial_id" name="serial_id" class="clear_serial_no">
                                    <input type="hidden" id="ins_id" name="ins_id" class="">
                                    <span class="help-block">
                                        <strong id="serial_no_msg"></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-offset-4 col-md-2">
                                    <button type="button" class="btn btn-sm green btn-block" id="btn_add_serial_no">Add</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm red btn-block" id="btn_remove_serial_no">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <br/>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-hover table-bordered table-striped table-condensed" id="tbl_serial_no">
                            <thead>
                                <tr>
                                    <td class="table-checkbox">
                                        <input type="checkbox" class="group-checkable-serial" />
                                    </td>
                                    <td>Edit</td>
                                    <td>Serial No.</td>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="mode_of_defects_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Mode of Defect</h4>
            </div>

            <div class="modal-body">
                <form class="form-horizontal" id="frm_mode_of_defects">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" id="mode_of_defects_name_div">
                                <label class="control-label col-sm-3">Mode of Defect</label>
                                <div class="col-sm-9">
                                    <select class="form-control input-sm validateModeOfDefects clear_mod" name="mode_of_defects_name" id="mode_of_defects_name">
                                       <option value=""></option>
                                    </select>
                                    <span class="help-block">
                                        <strong id="mode_of_defects_name_msg"></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group" id="mod_qty_div">
                                <label class="control-label col-sm-3">Quantity</label>
                                <div class="col-sm-9">
                                    <input type="number" name="mod_qty" id="mod_qty" class="form-control input-sm validateModeOfDefects clear_mod">
                                    <input type="hidden" id="mode_save_status" name="mode_save_status" class="">
                                    <input type="hidden" id="mod_po" name="mod_po" class="clear_mod">
                                    <input type="hidden" id="mod_device" name="mod_device" class="clear_mod">
                                    <input type="hidden" id="mod_lotno" name="mod_lotno" class="clear_mod">
                                    <input type="hidden" id="mod_submission" name="mod_submission" class="clear_mod">
                                    <input type="hidden" id="mod_id" name="mod_id" class="clear_mod">
                                    <input type="hidden" id="ins_id" name="ins_id" class="clear_mod">
                                    <span class="help-block">
                                        <strong id="mod_qty_msg"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-8 col-md-2">
                                    <button type="button" class="btn btn-sm green btn-block" id="btn_add_mod">Add</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm red btn-block" id="btn_remove_mod">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-striped table-condensed" id="tbl_mode_of_defects">
                                <thead>
                                    <tr>
                                        <td class="table-checkbox" width="5%">
                                            <input type="checkbox" class="group-checkable-mod" />
                                        </td>
                                        <td width="8%">Edit</td>
                                        <td>Mode of Defects</td>
                                        <td>Quantity</td>
                                    </tr>
                                </thead>
                                <tbody id="tbl_mode_of_defects_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="probe_lot_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Probe Pin Lot Numbers</h4>
            </div>

            <div class="modal-body">
                <div class="form-horizontal" id="frm_probe_lot">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" id="probe_lot_name_div">
                                <label class="control-label col-sm-3">Probe Pin Lot No.</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control input-sm validateProbeLot clear_probe_lot" name="probe_lot" id="probe_lot" autocomplete="off">
                                    <input type="hidden" id="probe_lot_save_status" name="probe_lot_save_status" class="">
                                    <input type="hidden" id="probe_lot_id" name="probe_lot_id" class="clear_probe_lot">
                                    <input type="hidden" id="ins_id" name="ins_id" class="">
                                    <span class="help-block">
                                        <strong id="probe_lot_msg"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group" id="probe_qty_div">
                                <label class="control-label col-sm-3">Qty.</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control input-sm validateProbeLot clear_qty" name="probe_qty" id="probe_qty" autocomplete="off">
                                    <span class="help-block">
                                        <strong id="qty_msg"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-8 col-md-2">
                                    <button type="button" class="btn btn-sm green btn-block" id="btn_add_probe_lot">Add</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm red btn-block" id="btn_remove_probe_lot">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-striped table-condensed" id="tbl_probe_lot">
                                <thead>
                                    <tr>
                                        <td class="table-checkbox">
                                            <input type="checkbox" class="group-checkable-probe" />
                                        </td>
                                        <td>Edit</td>
                                        <td>Probe Pin Lot No.</td>
                                        <td>Qty</td>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="file_checking_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm gray-gallery">
        <div class="modal-content ">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5 id="file_checking_msg"></h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <img src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/images/ajax-loader.gif') }}" class="img-responsive">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
