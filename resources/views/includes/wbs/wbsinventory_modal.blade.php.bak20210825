<!-- SEARCH MODAL -->
<div id="searchmodal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog gray-gallery">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Search</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="frm_search">
                    <div class="form-group">
                        <label class="control-label col-sm-3">From</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm datepicker" id="s_from" name="s_from"
                                placeholder="From" data-date-format="yyyy-mm-dd" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">To</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm datepicker" id="s_to" name="s_to"
                                placeholder="To" data-date-format="yyyy-mm-dd" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Receiving Control No.</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm" id="s_wbs_mr_id" name="s_wbs_mr_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Invoice No.</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm" id="s_invoice_no" name="s_invoice_no">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Status</label>
                        <div class="col-sm-9">
                            <select class="form-control input-sm" name="s_iqc_status" id="s_iqc_status">
                                <option value=""></option>
                                <option value="0">Pending</option>
                                <option value="1">Accepted</option>
                                <option value="2">Reject</option>
                                <option value="3">On-going</option>
                                <option value="4">Special Accept</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Item/Part Code</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm" id="s_item" name="s_item" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Lot No.</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm" id="s_lot_no" name="s_lot_no">
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" id="gobtn" class="btn btn-primary btn-sm">Go</button>
                <button type="button" data-dismiss="modal" class="btn btn-danger btn-sm">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="form_inventory_modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog">

        <div class="modal-content blue">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title">ADD\EDIT Item</h3>
            </div>
            <form class="form-horizontal" role="form" method="POST" action="{{ url('/wbs-inventory-save') }}"
                id="frm_inventory">
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <input type="hidden" id="id" name="id">

                    <div class="form-group" id="item_code_div">
                        <label for="inputcode" class="col-md-3 control-label">Item Code</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="item" name="item">
                            <span class="help-block">
                                <strong id="item_code_msg"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" id="item_desc_div">
                        <label for="inputcode" class="col-md-3 control-label">Description</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="item_desc" name="item_desc" autofocus>
                            <span class="help-block">
                                <strong id="item_desc_msg"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" id="lot_no_div">
                        <label for="inputcode" class="col-md-3 control-label">Lot No.</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="lot_no" name="lot_no">
                            <span class="help-block">
                                <strong id="lot_no_msg"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" id="qty_div">
                        <label for="inputcode" class="col-md-3 control-label">Qty.</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="qty" name="qty">
                            <span class="help-block">
                                <strong id="qty_msg"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" id="location_div">
                        <label for="inputcode" class="col-md-3 control-label">Location</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="location" name="location">
                            <span class="help-block">
                                <strong id="location_msg"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" id="supplier_div">
                        <label for="inputcode" class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="supplier" name="supplier">
                            <span class="help-block">
                                <strong id="supplier_msg"></strong>
                            </span>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Recieved Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm date-picker clear"
                                data-date-format="yyyy-mm-dd" id="received_date" name="received_date">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputcode" class="col-md-3 control-label">Updated By</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control validate" id="update_user" name="update_user">
                            <span class="help-block">
                                <strong id="item_code_msg"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputcode" class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <label><input type="checkbox" id="nr" name="nr"> Not for IQC</label>
                        </div>
                    </div>

                    <div class="form-group" id="status_div">
                        <label for="inputname" class="col-md-3 control-label">Status</label>
                        <div class="col-md-9">
                            <select class="form-control validate" id="status" name="status">
                                <option value="0">Pending</option>
                                <option value="1">Accept</option>
                                <option value="2">Reject</option>
                                <option value="3">On-going</option>
                                <option value="4">Special Accept</option>
                            </select>
                            <span class="help-block">
                                <strong id="status_msg"></strong>
                            </span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    {{-- <button type="submit" class="btn btn-success" {{ $state }}><i class="fa fa-save"></i>
                    Save</button> --}}
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                        Close</button>
                </div>
            </form>
        </div>

    </div>
</div>