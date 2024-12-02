<div id="kitqtyModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Add Kit Qty.</h4>
			</div>
			<form class="form-horizontal" id="kitqtyForm">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							{{ csrf_field() }}
							<div class="form-group">
								<label class="control-label col-sm-2">Kit Qty.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm mask_kitqty" id="getkitQty" name="kitqty" autofocus>
								</div>
							</div>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="updateKityQty">OK</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="addIssuanceDetailsModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog modal-full gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Add Issuance</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-5">
						<form class="form-horizontal" id="addIssuanceDetails_form">
							{{ csrf_field() }}
							<input type="hidden" name="fifoid" id="fifoid">
							<div class="form-group">
								<div class="col-sm-12">
								   <p>
									   Item/Part No., Issued Qty., and Location fields are required.
								   </p>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Item/Part No.</label>
								<div class="col-sm-8">
									<input type="text" class="form-control iss_clear input-sm" id="iss_item" name="iss_item" autofocus>
									<input type="hidden" class="form-control iss_clear input-sm" id="iss_item_desc" name="iss_item_desc">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Lot. No</label>
								<div class="col-sm-8">
								   <input type="text" class="form-control iss_clear input-sm" id="iss_lotno" name="iss_lotno">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Kit Qty.</label>
								<div class="col-sm-8">
									<input type="text" class="form-control iss_clear input-sm" id="iss_kitqty" name="iss_kitqty" readonly>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Issued Qty.</label>
								<div class="col-sm-8">
									<input type="text" class="form-control iss_clear input-sm" id="iss_qty" name="iss_qty">
									<input type="hidden" class="form-control iss_clear input-sm" id="iss_selected_qty" name="iss_selected_qty">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Location</label>
								<div class="col-sm-8">
									<input type="text" class="form-control iss_clear input-sm" id="iss_location" name="iss_location">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">Remarks</label>
								<div class="col-sm-8">
									<textarea class="form-control iss_clear input-sm" id="iss_remarks" style="resize:none" name="iss_remarks"></textarea>
								</div>
							</div>
							<input type="hidden" name="iss_save_status" id="iss_save_status">
							<input type="hidden" name="iss_detail_id" id="iss_detail_id">
							<input type="hidden" name="iss_id" id="iss_id">

						</form>
					</div>
					<div class="col-md-7">
						<table class="table table-bordered table-fixedheader table-striped table-fifo" style="font-size:10px" id="tbl_fifo">
							<thead>
								<tr>
									<td width="7.28%"></td>
									<td width="18.28%">Item Code</td>
									<td width="21.28%">Description</td>
									<td width="7.28%">Qty</td>
									<td width="17.28%">Lot</td>
									<td width="14.28%">Location</td>
									<td width="14.28%">Received Date</td>
								</tr>
							</thead>
							<tbody id="tbl_fifo_body"></tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-success" id="btn_add_issuance">OK</a>
				<button type="button" data-dismiss="modal" class="btn btn-danger iss_edit_close" id="iss_add_close">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="fifoReasonModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 id="tit" class="modal-title">FIFO Alert</h4>
			</div>
			<div class="modal-body">
				<p>FIFO is recommended, but you can specify your reason for using this Lot number.</p>
				<input type="hidden" name="frid" id="frid">
				<input type="hidden" name="fritem" id="fritem">
				<input type="hidden" name="fritemdesc" id="fritemdesc">
				<input type="hidden" name="frqty" id="frqty">
				<input type="hidden" name="frlotno" id="frlotno">
				<input type="hidden" name="frlocation" id="frlocation">
				<input type="hidden" name="frkitqty" id="frkitqty">
				<textarea class="form-control" id="fiforeason"></textarea>
			</div>
			<div class="modal-footer">
				<a href="javascript:;" id="btn_fiforeason" class="btn btn-success">OK</a>
				<button type="button" data-dismiss="modal" class="btn btn-danger" id="err_msg_close">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="searchModal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-full">

		<!-- Modal content-->
		<form class="form-horizontal" role="form" method="POST" action="{{ url('/material-kitting') }}">
			{!! csrf_field() !!}
			<div class="modal-content blue">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Search</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="inputname" class="col-md-3 control-label" style="font-size:12px">Po No.</label>
								<div class="col-md-9">
									<input type="text" class="form-control input-sm search_reset" id="srch_pono" placeholder="Po No" name="srch_pono" autofocus />
								</div>
							</div>
							<div class="form-group">
								<label for="inputname" class="col-md-3 control-label" style="font-size:12px">Kit No.</label>
								<div class="col-md-9">
									<input type="text" class="form-control input-sm search_reset" id="srch_kitno" placeholder="Kit No" name="srch_kitno" />
								</div>
							</div>
							<div class="form-group">
								<label for="inputname" class="col-md-3 control-label" style="font-size:12px">Prepared By</label>
								<div class="col-md-9">
									<input type="text" class="form-control input-sm search_reset" id="srch_preparedby" placeholder="Prepared By" name="srch_preparedby" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="inputname" class="col-md-3 control-label" style="font-size:12px">Slip No</label>
								<div class="col-md-8">
									<input type="text" class="form-control input-sm search_reset" id="srch_slipno" placeholder="Slip No" name="srch_slipno" />
								</div>
							</div>
							<div class="form-group">
								<label for="inputname" class="col-md-3 control-label" style="font-size:12px">Status</label>
								<div class="col-md-8">
									<label><input type="checkbox" class="srch_status" style="font-size:12px" value="O" id="srch_open" name="srch_status[]"/>Open</label>
									<label><input type="checkbox" class="srch_status" style="font-size:12px" value="X" id="srch_close" name="srch_status[]"/>Close</label>
									<label><input type="checkbox" class="srch_status" style="font-size:12px" value="C" id="srch_cancelled" name="srch_status[]"/>Cancelled</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<table class="table table-striped table-bordered table-hover table-fixedheader" style="font-size:10px" id="tbl_search">
								<thead>
									<tr>
										<td width="8.3%"></td>
										<td width="8.3%">Transaction No.</td>
										<td width="8.3%">Po No.</td>
										<td width="8.3%">Device Code</td>
										<td width="8.3%">Device name</td>
										<td width="8.3%">Kit No.</td>
										<td width="8.3%">Prepared By</td>
										<td width="8.3%">Slip No.</td>
										<td width="8.3%">Created By</td>
										<td width="8.3%">Created Date</td>
										<td width="8.3%">Updated By</td>
										<td width="8.3%">Updated Date</td>
									</tr>
								</thead>
								<tbody id="tbl_search_body"></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" class="form-control input-sm" id="editId" name="editId">
					<button type="button" style="font-size:12px" onclick="javascript: filterSearch(); " class="btn blue-madison" ><i class="glyphicon glyphicon-filter"></i> Filter</button>
					<button type="button" style="font-size:12px" onclick="javascript: searchReset(); " class="btn green" ><i class="glyphicon glyphicon-repeat"></i> Reset</button>
					<button type="button" style="font-size:12px" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
				</div>
			</div>
		</form>
	</div>
</div>

<div id="kittingListModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Search P.O. Number</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							{{ csrf_field() }}
							<div class="form-group">
								<label class="control-label col-sm-2">P.O.</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm mask_kitqty" id="kittinglist_po" name="kittinglist_po">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2">Kit QTY</label>
								<div class="col-sm-9">
									<input type="text" class="form-control input-sm mask_kitqty" id="kittinglist_kitqty" name="kittinglist_kitqty">
								</div>
							</div>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					{{-- <a class="btn btn-success" target="_blank">Search</a> --}}
					<button type="button" id="btn_search_kitting" class="btn btn-success" target="_blank">Search</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="print_modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog gray-gallery">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Print Labels</h4>
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							{{ csrf_field() }}
							<div class="form-group">
								<label class="control-label col-sm-2">Printer</label>
								<div class="col-sm-9">
									<select class="form-control input-sm" id="printer" name="printer">
										<option value="">Intermec PB22</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2">Print Qty</label>
								<div class="col-sm-9">
									<input type="number" class="form-control input-sm" id="print_qty" name="print_qty" minlength="1">
								</div>
							</div>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<a href="javascript:;" class="btn btn-success" target="_blank">Print</a>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>


<div id="check_issuance_details" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog modal-lg gray-gallery">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title">Check Issuance Details</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<table id="tbl_check_details_modal" class="table table-striped table-bordered" style="width:100%">
				        <thead>
				            <tr>
				           		 {{-- <th> <input type="checkbox" name="select_all" value="1" id="example-select-all"></th> --}}
				           		 {{-- <th> <input name="select_all" value="1" id="example-select-all" type="checkbox" /></th>  --}} 
				           		 <th><input type="checkbox" class="check_all_items"></th>
				                 <th>ID</th>	
				                 <th>Detail ID</th>				           
				                 <th>Issue No</th>				               
				                 <th>Item</th>
				           	     <th>Item Description</th>
				                 <th>Location</th>     
				               	 <th>Lot No.</th>				                       
				            </tr>
				        </thead>
				    </table>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				{{-- <a href="javascript:;" class="btn btn-danger" id="btn_add_issuance"><i class="fa fa-trash"></i>Delete</a> --}}
				<button type="button" class="btn btn-danger" id="iss_delete_detail_id"><i class="fa fa-trash"></i>Delete</button>
				<button type="button" class="btn btn-danger" id="iss_edit_close_modal"><i class="fa fa-close"></i>Close</button>
			</div>
		</div>
	</div>
</div>