<div id="inventory_modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div class="row">
					<div class="col-md-6">
						<h3 class="modal-title" style="font-weight: bold;">Inventory Entry</h3>
					</div>

					<div class="col-md-6 text-right">
						<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
					</div>
				</div>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group" id="div_inventory_date">
								<label class="control-label col-sm-3">Inventory Date:</label>
								<div class="col-sm-9">
									<input type="hidden" class="clear" id="inventory_id" name="inventory_id">
									<input type="hidden" id="state" name="state" value="ADD">
                                    <input type="date" class="form-control input-sm clear validate-input" id="inventory_date" name="inventory_date">
									<small id="err_inventory_date"></small>
								</div>
							</div>

							<div class="form-group" id="div_po_no">
								<label class="control-label col-sm-3">P.O. No.:</label>
								<div class="col-sm-9">
									<input type="text" id="po_no" name="po_no" class="form-control input-sm clear validate-input">
									<small id="err_po_no"></small>
								</div>
							</div>

                            <div class="form-group" id="div_series_name">
								<label class="control-label col-sm-3">Series Name:</label>
								<div class="col-sm-9">
									<input type="text" id="series_name" name="series_name" class="form-control input-sm clear" readonly>
									<small id="err_series_name"></small>
								</div>
							</div>

							<div class="form-group" id="div_quantity">
								<label class="control-label col-sm-3">Quantity:</label>
								<div class="col-sm-9">
									<input type="number" id="quantity" name="quantity" class="form-control input-sm clear validate-input">
									<small id="err_quantity"></small>
								</div>
							</div>

                            <div class="form-group" id="div_total_no_of_lots">
								<label class="control-label col-sm-3">Total No. of Lots:</label>
								<div class="col-sm-9">
									<input type="number" id="total_no_of_lots" name="total_no_of_lots" class="form-control input-sm clear validate-input">
									<small id="err_total_no_of_lots"></small>
								</div>
							</div>
						</div>

                        <div class="col-md-6">
                            <div class="form-group" id="div_lot_date">
								<label class="control-label col-sm-3">Lot App. Date:</label>
								<div class="col-sm-9">
                                    <input type="date" class="form-control input-sm clear validate-input" id="lot_date" name="lot_date">
									<small id="err_lot_date"></small>
								</div>
							</div>

                            <div class="form-group" id="div_lot_time">
								<label class="control-label col-sm-3">Lot App. Time:</label>
								<div class="col-sm-9">
                                    <input autocomplete="off" type="text" class="form-control input-sm clear validate-input timepicker timepicker-no-seconds" name="lot_time" id="lot_time"/>
                                    <small id="err_lot_time"></small>
								</div>
							</div>
                        </div>
					</div>
					<div class="row">
						<div class="col-md-offset-8 col-md-2">
                            <button type="button" class="btn btn-sm grey-gallery btn-block" id="btn_cancel" data-dismiss="modal">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm blue btn-block" id="btn_save">
                                <i class="fa fa-floppy-o"></i> Save
                            </button>
                        </div>
						
					</div>
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
                    <!-- <div class="form-group">
                        <label class="control-label col-sm-3">PO Number</label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm" type="text" value="" name="search_po" id="search_po"/>
                        </div>
                    </div> -->

                    <div class="form-group">
                        <label class="control-label col-sm-3">Inventory Date From: </label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm date-picker" type="text" placeholder = "Select Date From" value="" name="search_from" id="search_from" autocomplete="off"/>
                            <div id="er_search_from"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3">Inventory Date To: </label>
                        <div class="col-sm-7">
                            <input class="form-control input-sm date-picker" type="text" placeholder = "Select Date To" value="" name="search_to" id="search_to" autocomplete="off"/>
                            <div id="er_search_to"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <!-- {{-- <button type="button" class="btn btn-success" onclick="javascript:searchInspection();">
                        <i class="fa fa-search"></i> Search
                    </button> --}} -->
                    <button type="button" id="btn_pdf" class="btn btn-primary" onclick="javascript:PDFReport();">
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </button>
					<!-- <button type="button" id="btn_excel" class="btn btn-success" onclick="javascript:ExcelReport();">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button> -->
                    <a href="javascript:ExcelReport();" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <button type="button" data-dismiss="modal" class="btn btn-danger" id="btn_search-close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>