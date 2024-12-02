<div id="lot_no_modal" class="modal fade" role="dialog" data-backdrop="static">
   
    <div class="modal-dialog modal-lg">

        <div class="modal-content blue">
            <div class="modal-header">
                <button type="button" class="close"data-dismiss="modal">&times;</button>
                <h5 class="modal-title">Lot Numbers</h5>
            </div>
            <div class="modal-body">
                <table class="table table-condensed table-bordered table-checkable" id="tbl_lotno">
                    <thead>
                        <td width="5%">
                            <input type="checkbox" class="check_all">
                        </td>
                        <td>Item Code</td>
                        <td>Item Name</td>
                        <td>Lot No.</td>
                        <td>Qty</td>
                    </thead>
                    <tbody id="tbl_lotno_body"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                    {{-- <button type="submit" class="btn btn-success" {{ $state }}><i class="fa fa-save"></i> Save</button> --}}
                    <button type="button" class="btn btn-success btn-sm" id="btn_select_lot">Select</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
            
    </div>
</div>


<div id="edit_lot_no_modal" class="modal fade" role="dialog" data-backdrop="static">

    <div class="modal-dialog">

        <div class="modal-content blue">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title">Lot Numbers</h5>
            </div>
            <form class="form-horizontal">
                <div class="modal-body">
                    <input type="hidden" class="form-control input-sm" id="lot_id" name="lot_id"/>

                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Item Code</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_item" name="edit_item" readonly>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Item Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_item_desc" name="edit_item_desc" readonly>
                        </div>
                    </div>

                      <div class="form-group">
                        <label for="" class="control-label col-sm-3">Current Qty</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_current_qty" name="edit_current_qty" readonly>
                        </div>
                    </div> 


                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Qty</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_qty" name="edit_qty">
                        </div>
                    </div>  

                   


                     {{--   <div class="form-group">
                        <label for="" class="control-label col-sm-3">Qty</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_qty" name="edit_qty"  >
                        </div>
                    </div>  --}}


                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Lot No</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_lot_no" name="edit_lot_no" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Exp Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-sm clear" id="edit_exp_date" name="edit_exp_date">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Disposition</label>
                        <div class="col-sm-9">
                            <select class="form-control input-sm clear" id="edit_disposition" name="edit_disposition">
                                <option value=""></option>
                                @foreach($dispositions as $status)
                                    <option value="{{ $status->description }}">{{ $status->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="" class="control-label col-sm-3">Remarks</label>
                        <div class="col-sm-9">
                            <textarea class="form-control input-sm clear" id="edit_remarks" name="edit_remarks" style="resize: none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- <button type="submit" class="btn btn-success" {{ $state }}><i class="fa fa-save"></i> Save</button> --}}
                    <button type="button" class="btn btn-success btn-sm" id="btn_save_edited">save</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn_close_edited" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                </div>
            </form>
                
        </div>

    </div>
</div>


<!-- Add Batch Modal -->
<div id="batchItemModal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <!-- Modal content-->
        <div class="modal-content blue">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Batch</h4>
            </div>
            <div class="modal-body">
                <p>All the fields are required.</p>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="inputcode" class="col-md-3 control-label">*Batch ID</label>
                        <div class="col-md-9">
                            <input type="text" id="add_invoice_no" name="id" hidden="true" />
                            <input type="text" class="form-control input-sm clearbatch" id="add_inputBatchId" placeholder="Batch ID" name="batchid" readonly />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="col-md-3 control-label">*Item No</label>
                        <div class="col-md-9">
                            <input type="text" id="add_inputItemNo" class="form-control input-sm clearbatch" name="itemno" <?php echo($state);?>>
                            <input type="hidden" id="add_inputItemNoHidden" class="clearbatch">
                            <input type="hidden" id="add_inputItemDesc" class="clearbatch">
                            <input type="hidden" id="add_notForIqc" class="clearbatch">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="col-md-3 control-label">*Quantity</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-sm clearbatch" id="add_inputQty" placeholder="Quantity" name="qty" <?php echo($readonly); ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-3" style="text-align: right;">
                            <label for="inputname" class="control-label">*Package Category</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="add_inputBox" class="form-control input-sm clearbatch" name="itemno" <?php echo($state);?>>
                        </div>
                        <div class="col-md-3" style="text-align: right;">
                            <label for="inputname" class="control-label">*Package Qty</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control input-sm clearbatch" id="add_inputBoxQty" placeholder="Box Qty" name="boxqty" <?php echo($readonly); ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="control-label col-md-3" >*Lot No</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-sm clearbatch" id="add_inputLotNo" placeholder="Lot No" name="lotno" <?php echo($readonly); ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="control-label col-md-3">Location</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-sm clearbatch" id="add_inputLocation" placeholder="Location" name="location" disabled="disabled" <?php echo($readonly); ?> value=""/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="control-label col-md-3">Supplier</label>
                        <div class="col-md-9">
                            <select class="form-control input-sm clearbatch" id="add_inputSupplier" placeholder="Supplier" name="supplier" <?php echo($readonly); ?>></select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_add_batch_modal" class="btn btn-success" <?php echo($state); ?>><i class="fa fa-plus"></i> Add</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Batch Modal -->
<div id="EditbatchItemModal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <!-- Modal content-->
        <div class="modal-content blue">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Batch</h4>
            </div>
            <div class="modal-body">
                <p>All the fields are required.</p>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="inputcode" class="col-md-3 control-label">*Batch ID</label>
                        <div class="col-md-9">
                            <input type="hidden" id="edit_invoice_no" name="id"/>
                            <input type="text" class="form-control input-sm clearbatch" id="edit_inputBatchId" placeholder="Batch ID" name="batchid" readonly />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="col-md-3 control-label">*Item No</label>
                        <div class="col-md-9">
                            <input type="text" id="edit_inputItemNo" class="form-control input-sm clearbatch" name="itemno" <?php echo($state);?>>
                            <input type="hidden" id="edit_inputItemNoHidden" class="clearbatch">
                            <input type="hidden" id="edit_inputItemDesc" class="clearbatch">
                            <input type="hidden" id="edit_notForIqc" class="clearbatch">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="col-md-3 control-label">*Quantity</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-sm clearbatch" id="edit_inputQty" placeholder="Quantity" name="qty"  />
                            <input type="hidden" name="edit_inputQtyHidden" id="edit_inputQtyHidden">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-3" style="text-align: right;">
                            <label for="inputname" class="control-label">*Package Category</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="edit_inputBox" class="form-control input-sm clearbatch" name="itemno" <?php echo($state);?>>
                        </div>
                        <div class="col-md-3" style="text-align: right;">
                            <label for="inputname" class="control-label">*Package Qty</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control input-sm clearbatch" id="edit_inputBoxQty" placeholder="Box Qty" name="boxqty" <?php echo($readonly); ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="control-label col-md-3">*Lot No</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-sm clearbatch" id="edit_inputLotNo" placeholder="Lot No" name="lotno" <?php echo($readonly); ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="control-label col-md-3">Location</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-sm clearbatch" id="edit_inputLocation" placeholder="Location" name="location" disabled="disabled" <?php echo($readonly); ?> value=""/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputname" class="control-label col-md-3">Supplier</label>
                        <div class="col-md-9">
                            <select class="form-control input-sm clearbatch" id="edit_inputSupplier" placeholder="Supplier" name="supplier" <?php echo($readonly); ?>></select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_edit_batch_modal" class="btn btn-success" <?php echo($state); ?>><i class="fa fa-check"></i> Update</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div id="modal_search" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-full">
        <!-- Modal content-->
        <div class="modal-content blue">
            <div class="modal-header">
               
                <h4 class="modal-title">Search</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <form class="form-horizontal">

                                      
                            
                         <div class="form-group">
                                <label for="inputcode" class="col-md-3 control-label">Transaction Date</label>
                                <div class="col-md-7">
                                    <div class="input-group input-large date-picker input-daterange" data-date="<?php echo date("m/d/Y"); ?>" data-date-format="mm/dd/yyyy">
                                        <input type="text" class="form-control input-sm reset" name="srch_from" id="srch_from"/>
                                        <span class="input-group-addon">to </span>
                                        <input type="text" class="form-control input-sm reset" name="srch_to" id="srch_to"/>
                                    </div>
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <input type="hidden" name="id" id="id">
                                <label for="inputname" class="col-md-3 control-label">Item Code</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control input-sm reset" id="srch_item"  name="srch_item"> 
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    <div class="col-md-7" style="height:400px; overflow: auto;">
                        <table class="table table-striped table-bordered table-hover table-responsive sortable" id="tbl_search">
                            <thead>
                                <tr>
                                    <td></td>
                                    <td>Transaction No</td>
                                    <td>Item Code</td>
                                    <td>Item Name</td>
                                    <td>Created By</td>
                                    <td>Created Date</td>
                                    <td>Updated By</td>
                                    <td>Updated Date</td>
                              
                
                                </tr>
                            </thead>
                            <tbody id="tbl_search_body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn blue-madison input-sm" id="btn_filter"><i class="glyphicon glyphicon-filter"></i> Filter</a>
                <a href="javascript:;" class="btn green input-sm" id="btn_reset"><i class="glyphicon glyphicon-repeat"></i> Reset</a>
                <a href="javascript:;" class="btn btn-danger input-sm" id="close_btn" data-dismiss="modal"><i class="fa fa-times"></i> Close</a>
            </div>
        </div>
    </div>
</div>

<!-- AJAX LOADER -->
<div id="loading" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm gray-gallery">
        <div class="modal-content ">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <img src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/images/ajax-loader.gif') }}" class="img-responsive">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MSG -->
<div id="msg" class="modal fade" role="dialog" data-backdrop="static">
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
</div>

<!-- Confirm -->
<div id="confirm" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm gray-gallery">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">WBS Material Receiving</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure?</p>
                <input type="hidden" name="confirm_status" id="confirm_status">
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" id="confirmyes" class="btn btn-success">Yes</button>
                <button type="button" data-dismiss="modal" id="confirmno" class="btn btn-danger">No</button>
            </div>
        </div>
    </div>
</div>




<div id="export_data_modal" class="modal fade" role="dialog" data-backdrop="static">

    <div class="modal-dialog">

        <div class="modal-content blue">
            <div class="modal-header">
            
                <h5 class="modal-title">Export Data</h5>
            </div>
            <form class="form-horizontal">
                <div class="modal-body">
                    <input type="hidden" class="form-control input-sm" id="lot_id" name="lot_id"/>

                    <div class="form-group">
                        <label for="" class="control-label col-sm-2">From:</label>
                        <div class="col-sm-10">
                            <input class="form-control input-sm " type="text" name="from" id="from">
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="" class="control-label col-sm-2 ">To:</label>
                        <div class="col-sm-10">
                             <input class="form-control input-sm " type="text" name="to" id="to">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- <button type="submit" class="btn btn-success" {{ $state }}><i class="fa fa-save"></i> Save</button> --}}
                   <button type="button" id="btn_excel" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                    <button type="button" id="btn_close" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                </div>
            </form>
                
        </div>

    </div>
</div>