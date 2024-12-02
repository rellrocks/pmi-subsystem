<!-- Success Message Modal -->
<div id="confirmModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm gray-gallery">
            <!-- Modal content-->
        <form class="form-horizontal" id="confirmForm" role="form" method="POST">
            <div class="modal-content ">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="deleteAll-title" id="modalTitle"></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {!! csrf_field() !!}
                        <div class="col-sm-12">
                            <label for="confirmMessage" id="confirmMessage" class="col-sm-12 control-label text-center"></label>
                        </div>    
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-success" id="confirmOk" ><i class="fa fa-save"></i>OK</a>
                </div>
            </div>
        </form>
    </div>
</div>

 <!--delete all modal-->
<div id="deleteAllModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm gray-gallery">
    <!-- Modal content-->
        <form class="form-horizontal" id="deleteAllform" role="form" method="POST">
            <div class="modal-content ">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="deleteAll-title">Delete Yield Performance Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {!! csrf_field() !!}
                        <div class="col-sm-12">
                            <label for="inputname" class="col-sm-12 control-label text-center">
                            Are you sure you want to delete record/s?
                            </label>
                            <input type="hidden" value="" name="deleteAllmaster" id="deleteAllmaster" />
                        </div>    
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success" id="modaldelete" ><i class="fa fa-save"></i> Yes</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> No</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

 <!-- Existing Invoice Load Pop-message-->
<div id="multisearchModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="multisearch-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {!! csrf_field() !!}
                    <div class="form-group row">
                        <div class="col-sm-5 col-sm-offset-1">
                            <label class="control-label col-sm-4">Type</label>  
                        </div>
                        <div class="col-sm-5">
                            <label class="control-label col-sm-4">Values</label>  
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-5 col-sm-offset-1">
                            <Select class="form-control" id="mSearchtype1" name="mSearchtype1">
                            <option>Select One..</option>
                            <option value="1">Yielding No</option>
                            <option value="2">PO No.</option>
                            <option value="3">PO Qty.</option>
                            <option value="4">Device</option>
                            <option value="5">Family</option>
                            <option value="6">Series</option>
                            <option value="7">Classification</option>
                            <option value="8">Mode of Defect</option>
                            <option value="9">Quantity</option>
                            <option value="10">Production Date</option>
                            <option value="11">Yielding Station</option>
                            <option value="12">Accumulated Output</option>
                            </Select>
                        </div>
                        <div class="col-sm-5">
                            <Select class="form-control mSearchval1" id="mSearchval1" name="mSearchval1"></Select>  
                        </div>
                    </div>    
                </div>
            </div>
            <div class="modal-footer">
                 <button type="button" onclick="javascript:multiSearchDisplay();" class="btn btn-success" id="btnmultiSearch" ><i class="fa fa-save"></i>Search</button>
                 <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Existing Invoice Load Pop-message-->
<div id="searchpoModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm blue">
        <div class="modal-content ">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="searchpo-title"></h4>
            </div>
            <div class="modal-body">
                <h4 id="po-message"></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="btnok">OK</button>
            </div>
        </div>
    </div>
</div>