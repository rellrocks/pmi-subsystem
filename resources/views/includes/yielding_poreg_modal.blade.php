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
                                    <option value="1">Accepted</option>
                                    <option value="2">Reject</option>
                                    <option value="3">On-going</option>
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

     <div id="poreg_Modal" class="modal fade" role="dialog" data-backdrop="static">
          <div class="modal-dialog gray-gallery modal-xl">
               <div class="modal-content ">
                    <div class="modal-header">
                         <h4 class="poreg_modal-title"> PO Details</h4>
                    </div>
                    <div class="modal-body">
                         <div class="row">
                              <div class="col-sm-5">
                                   <form class="form-horizontal">
                                       <!--  {!! csrf_field() !!} -->
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">PO Number</label>

                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm validate" id="pono" name="pono" maxlength="17">
                                                  <div id="pono_feedback"></div>
                                             </div>     
                                        </div>
                                        <div class="form-group" style="display: none">
                                             <label class="control-label col-sm-3">Device Code</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm validate" id="device_code" name="device_code" maxlength="45" >
                                                  <div id="device_code_feedback"></div>
                                             </div>
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">Device Name</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm validate" id="device_name" name="device_name" maxlength="40" >
                                                  <div id="device_name_feedback"></div>
                                             </div>
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">PO Quantity</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm validate" id="poqty" name="poqty" maxlength="40" >
                                                  <input type="hidden" class="form-control input-sm" id="poregstatus" name="poregstatus" maxlength="40" >
                                                  <input type="hidden" class="form-control input-sm" id="id" name="id" maxlength="40">
                                                  <div id="poqty_feedback"></div>
                                             </div>
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">Family</label>
                                             <div class="col-sm-9">
                                                  <!-- <Select class="form-control input-sm" id="devicefamily" name="devicefamily" tabindex="3">
                                                       <option value=""></option>
                                                         {{--   @foreach ($family as $fam)
                                                          <option value="{{$fam->family}}">{{$fam->family}}</option>
                                                                @endforeach --}}
                                                 </Select> -->
                                                 <input type="text" class="form-control required input-sm clearselect show-tick actual select-validate" name="family" id="family">
                                                  <div id="family_feedback"></div>
                                             </div>
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">Series</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control required input-sm clearselect show-tick actual select-validate" name="series" id="series">
                                                  <div id="series_feedback"></div>
                                             </div>
                                        </div> 
                                        <div class="form-group row">
                                             <label class="control-label col-sm-3">Product Type</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control required input-sm clearselect show-tick actual select-validate" name="prod_type" id="prod_type">
                                                  <div id="prod_type_feedback"></div>
                                             </div>
                                        </div>

                                        <div class="form-group pull-right">
                                             <div class="col-sm-12">
                                                 <!--  <button type="button" onclick="javascript:saveporeg();" id="btn_save_po_reg" class="btn btn-success">Save</button> 
                                                  <button type="button" data-dismiss="modal" class="btn btn-danger btn-sm">Close</button> -->
                                                  <button type="button" id="btn_save_po_reg" class="btn btn-success">Save</button> 
                                                  <!-- <button type="button" id='btnporegclear' class="btn btn-danger">Clear</button> -->
                                             </div>  
                                        </div>
                                   </form>   
                              </div>
                              <div class="col-sm-7">
                                   <table id="tbl_poregistration" class="table table-striped table-bordered table-hover"style="font-size:10px">
                                        <thead id="thead1">
                                             <tr>
                                                 <td class="table-checkbox" style="width: 5%">
                                                       <input type="checkbox" class="check_all_po_reg"/>   
                                                  </td>
                                                  <td></td>
                                                  <td>PO Number</td>
                                                  <td>Device Code</td>
                                                  <td>Device Name</td>
                                                  <td>PO Quantity</td>
                                                  <td>Family</td>
                                                  <td>Series</td>
                                                  <td>Product Type</td>    
                                             </tr>
                                        </thead>
                                        <tbody id="tbl_poregistration_body"></tbody>
                                   </table>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" id='btn_remove_po_reg' class="btn red">Remove</button>
                         <button type="button" data-dismiss="modal" class="btn btn-danger" id="btn_poclose">Close</button>
                    </div>
            </div>
        </div>
    </div>