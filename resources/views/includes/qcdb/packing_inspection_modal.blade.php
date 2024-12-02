<!-- ADD NEW MODAL -->
<div id="inspection_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery modal-lg">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Packing Inspection Result</h4>
            </div>
            <form class=form-horizontal method="post" id="frm_inspection">
                <div class="modal-body">
                    {{ csrf_field() }}
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label col-sm-4">P.O. No.</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="po_num" name="po_num">
                                    <input type="hidden" id="id" class="clear enter" name="id">
                                    <div id="er_po_no"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Inspection Date</label>
                                <div class="col-sm-8">
                                    <input class="form-control input-sm clear enter date-picker" type="text" value="" name="date_inspected" id="date_inspected" autocomplete="off"/>
                                    <div id="er_insp_date"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Shipment Date</label>
                                <div class="col-sm-8">
                                    <input class="form-control input-sm clear enter date-picker" type="text" value="" name="shipment_date" id="shipment_date" autocomplete="off"/>
                                    <div id="er_ship_date"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Device Name</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="device_name" name="device_name" readonly />
                                    <div id="er_series_name"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Inspector</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="inspector" name="inspector">
                                    <div id="er_inspector"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Packing Type</label>
                                <div class="col-sm-8">
                                    <select class="form-control input-sm clear enter show-tick" name="packing_type" id="packing_type">
                                        <option value=""></option>
                                    </select>
                                    <div id="er_packing_type"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Unit Condition</label>
                                <div class="col-sm-8">
                                    <select class="form-control input-sm clear enter" name="unit_condition" id="unit_condition">
                                        <option value=""></option>
                                    </select>
                                    <div id="er_unit_condition"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Packing Operator</label>
                                <div class="col-sm-8">
                                    <select class="form-control input-sm clear enter" name="packing_operator" id="packing_operator">
                                        <option value=""></option>
                                    </select>
                                    <div id="er_packing_operator"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Remarks</label>
                                <div class="col-sm-8">
                                    <textarea name="remarks" id="remarks" class="form-control input-sm clear enter" style="resize:none"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label col-sm-4">Packing Code<small>(per Series)</small></label>
                                <div class="col-sm-8">
                                    <select class="form-control input-sm clear enter" name="packing_code_series" id="packing_code_series">
                                        <option value=""></option>
                                    </select>
                                    <div id="er_packing_code_series"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Carton No.</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="carton_num" name="carton_num">
                                    <div id="er_carton_no"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Packing Code.</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="packing_code" name="packing_code">
                                    <div id="er_pack_code"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Runcard</label>
                                <div class="col-sm-8">
                                    <button type="button" class="btn btn-block green" id="btn_runcard"> Lot Number</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Judgement</label>
                                <div class="col-sm-8">
                                    <select class="form-control input-sm clear enter" name="judgement" id="judgement">
                                        <option value=""></option>
                                        <option value="Accept">Accept</option>
                                        <option value="Reject">Reject</option>   
                                    </select>
                                    <div id="er_judgement"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-4">Total Qty</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="total_qty" name="total_qty" readonly>
                                    <div id="er_total_qty"></div>
                                </div>
                            </div>
                            <div class="form-group" id="no_defects_div">
                                <label class="control-label col-sm-4">No. of Defectives</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm clear enter" id="no_of_defects" name="no_of_defects" readonly>
                                </div>
                            </div>
                            <div class="form-group" id="mode_defects_div">
                                <label class="control-label col-sm-4">Mode of Defects</label>
                                <div class="col-sm-8">
                                    <button type="button" class="btn blue" id="btn_mode_of_defects">
                                        <i class="fa fa-plus-circle"></i> Add Mode of Defects
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="btn_save">Save</button>
                        <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<!-- RUNCARD MODAL -->
<div id="runcard_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Runcard Specification</h4>
            </div>
            <form class="form-horizontal" method="post" action="{{ url('/packinginspection/save-runcard') }}" id="frm_runcard">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label col-sm-3">Lot No.</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control input-sm" name="runcard_no" id="runcard_no">
                                    <input type="hidden" name="runcard_id" id="runcard_id">
                                    <input type="hidden" name="runcard_po" id="runcard_po">
                                    <input type="hidden" name="runcard_id_inspection" id="runcard_id_inspection">
                                    <input type="hidden" name="runcard_carton_no" id="runcard_carton_no">
                                    <div id="er_rc_no"></div>
                                </div>
                                <label class="control-label col-sm-1">Qty</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control input-sm" name="runcard_qty" id="runcard_qty">
                                    <div id="er_rc_qty"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-3">Remarks</label>
                                <div class="col-sm-7">
                                    <textarea name="runcard_remarks" id="runcard_remarks" class="form-control input-sm" style="resize:none"></textarea>
                                    <div id="er_rc_remarks"></div>
                                </div>
                            
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-hover table-bordered table-striped" id="tbl_runcard" style="font-size: 10px; white-space: nowrap" width="100%" cellspacing="0" data-page-length="33" data-scroll-x="true" scroll-collapse="false">
                                <thead>
                                    <tr>
                                        <td class="table-checkbox">
                                            <input type="checkbox" class="group-checkable check_all_runcard" />
                                        </td>
                                        <td></td>
                                        <td>P.O #</td>
                                        <td>Runcard #</td>
                                        <td>Qty</td>
                                        <td>Remarks</td>
                                    </tr>
                                </thead>
                                <tbody id="tbl_runcard_body"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn grey-gallery" id="btn_delete_runcard">Delete Record</button>
                    <button type="submit" class="btn btn-success" id="btn_save_runcard">Save</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODE OF DEFECTS -->
<div id="mode_of_defects_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Mode of Defect</h4>
            </div>
            <form class="form-horizontal" method="post" action="{{ url('/packinginspection/save-mod') }}" id="frm_mode_of_defects">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label col-sm-3">Mode of Defect</label>
                                <div class="col-sm-9">
                                    <select class="form-control input-sm" name="mod" id="mod">
                                        <option value="">select one</option>
                                    </select>
                                    <div id="er_mod"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-3">Quantity</label>
                                <div class="col-sm-9">
                                    <input type="text" id="mod_qty" name="mod_qty" class="form-control input-sm">
                                    <input type="hidden" id="mod_id" name="mod_id">
                                    <input type="hidden" id="mod_po_inspection" name="po_inspection">
                                    <input type="hidden" id="mod_id_inspection" name="id_inspection">
                                    <div id="er_qty"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="button" id="btn_delete_mod" class="btn btn-sm red pull-right">Delete</button>
                                    <button type="submit" id="btn_save_mod" class="btn btn-sm green pull-right">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-hover table-bordered table-striped" id="tbl_mode_of_defects" style="font-size: 10px; white-space: nowrap" width="100%" cellspacing="0" data-page-length="33" data-scroll-x="true" scroll-collapse="false">
                                <thead>
                                    <tr>
                                        <td class="table-checkbox" style="width: 5%">
                                            <input type="checkbox" class="group-checkable check_all_mod" />
                                        </td>
                                        <td></td>
                                        <td>Mode of Defects</td>
                                        <td>Quantity</td>
                                    </tr>
                                </thead>
                                <tbody id="tbl_mode_of_defects_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SEARCH MODAL -->
<div id="search_modal" class="modal fade" role="dialog" data-backdrop="static">
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
                                <label class="control-label col-sm-3">PO Number</label>
                                <div class="col-sm-7">
                                    <input class="form-control input-sm" type="text" value="" name="search_po" id="search_po"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3">From</label>
                                <div class="col-sm-7">
                                    <input class="form-control input-sm date-picker" type="text" value="" name="search_from" id="search_from" autocomplete="off"/>
                                    <div id="er_search_from"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-3">To</label>
                                <div class="col-sm-7">
                                    <input class="form-control input-sm date-picker" type="text" value="" name="search_to" id="search_to" autocomplete="off"/>
                                    <div id="er_search_to"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm purple" id="pdf_search">PDF</button>
                    <button type="button" class="btn btn-sm green" id="excel_search">EXCEL</button>
                    <button type="button" class="btn btn-sm blue" id="btn_search_data">Search</button>
                    <button type="button" data-dismiss="modal" class="btn btn-sm red" id="btn_search-close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- GROUP BY MODAL -->
<div id="group_by_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-md gray-gallery">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Group Items By:</h4>
            </div>
            <form class="form-horizontal" method="post" id="frm_dppm" action="{{ url('/packinginspection/calculate-dppm') }}">
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label col-sm-2">Date From</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control date-picker input-sm " id="gfrom" name="gfrom">     
                            </div>
                            <div class="col-sm-5">
                                    <input type="text" class="form-control date-picker input-sm " id="gto" name="gto">
                            </div>
                        </div>
                    </div>
                    <br>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label col-sm-2">Group #1</label>
                            <div class="col-sm-5">
                                <select class="form-control input-sm" name="field1" id="field1">
                                    <option value=""></option>
                                    <option value="id">ID</option>
                                    <option value="date_inspected">Inspection Date</option>
                                    <option value="shipment_date">Shipment Date</option>
                                    <option value="device_name">Series Name</option>
                                    <option value="po_num">PO #</option>
                                    <option value="packing_operator">Packing Operator</option>  
                                    <option value="inspector">Inspector</option>
                                    <option value="packing_type">Packing Type</option>
                                    <option value="unit_condition">Unit Condition</option>
                                    <option value="packing_code_series">Packing Code(Per Series)</option>
                                    <option value="carton_num">Carton #</option>
                                    <option value="packing_code">Packing Code</option>
                                    <option value="total_qty">Qty</option>
                                    <option value="judgement">Judgement</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <select class="form-control input-sm" name="content1" id="content1">
                                <!-- append here -->
                                </select>
                            </div>
                        </div>  
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label col-sm-2">Group #2</label>
                            <div class="col-sm-5">
                                <select class="form-control input-sm" name="field2" id="field2">
                                    <option value=""></option>
                                    <option value="id">ID</option>
                                    <option value="date_inspected">Inspection Date</option>
                                    <option value="shipment_date">Shipment Date</option>
                                    <option value="device_name">Series Name</option>
                                    <option value="po_num">PO #</option>
                                    <option value="packing_operator">Packing Operator</option>  
                                    <option value="inspector">Inspector</option>
                                    <option value="packing_type">Packing Type</option>
                                    <option value="unit_condition">Unit Condition</option>
                                    <option value="packing_code_series">Packing Code(Per Series)</option>
                                    <option value="carton_num">Carton #</option>
                                    <option value="packing_code">Packing Code</option>
                                    <option value="total_qty">Qty</option>
                                    <option value="judgement">Judgement</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <select class="form-control input-sm" name="content2" id="content2">
                                <!-- append here -->
                                <option value=""></option>  
                                </select>
                            </div>
                        </div>  
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label col-sm-2">Group #3</label>
                            <div class="col-sm-5">
                                <select class="form-control input-sm" name="field3" id="field3">
                                    <option value=""></option>
                                    <option value="id">ID</option>
                                    <option value="date_inspected">Inspection Date</option>
                                    <option value="shipment_date">Shipment Date</option>
                                    <option value="device_name">Series Name</option>
                                    <option value="po_num">PO #</option>
                                    <option value="packing_operator">Packing Operator</option>  
                                    <option value="inspector">Inspector</option>
                                    <option value="packing_type">Packing Type</option>
                                    <option value="unit_condition">Unit Condition</option>
                                    <option value="packing_code_series">Packing Code(Per Series)</option>
                                    <option value="carton_num">Carton #</option>
                                    <option value="packing_code">Packing Code</option>
                                    <option value="total_qty">Qty</option>
                                    <option value="judgement">Judgement</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <select class="form-control input-sm" name="content3" id="content3">
                                <!-- append here -->
                                </select>
                            </div>
                        </div>  
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success">Calculate</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>