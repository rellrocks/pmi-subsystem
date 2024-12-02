@extends('layouts.master')

@section('title')
	WBS | Pricon Microelectronics, Inc.
@endsection

@section('content')

	<?php $state = ""; $readonly = ""; ?>
	@foreach ($userProgramAccess as $access)
		@if ($access->program_code == Config::get('constants.MODULE_CODE_WBS'))  <!-- Please update "2001" depending on the corresponding program_code -->
			@if ($access->read_write == "2")
			<?php $state = "disabled"; $readonly = "readonly"; ?>
			@endif
		@endif
	@endforeach
	
	<div class="page-content">

		<!-- BEGIN PAGE CONTENT-->
		<div class="row">
			<div class="col-md-12">
				<!-- BEGIN EXAMPLE TABLE PORTLET-->
				@include('includes.message-block')
				<div class="portlet box blue" >
					<div class="portlet-title">
						<div class="caption">
							<i class="fa fa-navicon"></i>  WBS Material Disposition
						</div>
					</div>
					<div class="portlet-body">
                        <div class="row">
                            <form action="" class="form-horizontal">
                            	<div class="col-md-6">
                            		<div class="form-group row">

	                                    <label class="control-label col-md-3">Transaction No.</label>
	                                    <div class="col-md-9">
	                                        <input type="hidden" class="form-control clear input-sm" id="id" name="id"/>
	                                        <div class="input-group">
	                                            <input type="text" class="form-control input-sm add" id="transaction_code" name="transaction_code">

	                                            <span class="input-group-btn">
	                                                <a href="javascript:navigate('first');" id="btn_min" class="btn blue input-sm"><i class="fa fa-fast-backward"></i></a>
	                                                <a href="javascript:navigate('prev');" id="btn_prv" class="btn blue input-sm"><i class="fa fa-backward"></i></a>
	                                                <a href="javascript:navigate('next');" id="btn_nxt" class="btn blue input-sm"><i class="fa fa-forward"></i></a>
	                                                <a href="javascript:navigate('last');" id="btn_max" class="btn blue input-sm"><i class="fa fa-fast-forward"></i></a>
	                                            </span>
	                                        </div>

	                                        
	                                    </div>
	                                     
	                                </div>
                            		<div class="form-group">
                            			<label for="" class="control-label col-sm-3">Item Code</label>
                            			<div class="col-sm-9">
                                            <div class="input-group">
                                                <input type="text" class="form-control input-sm clear" id="item" name="item" readonly>

                                                <span class="input-group-btn">
                                                    <button type="button" disabled="" id="btn_search_item" class="btn blue input-sm"><i class="fa fa-search"></i></button>
                                                </span>
                                            </div>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label for="" class="control-label col-sm-3">Item Name</label>
                            			<div class="col-sm-9">
                            				<input type="text" class="form-control input-sm clear" id="item_desc" name="item_desc" readonly>
                            			</div>
                            		</div>
                            	</div>
                        		<div class="col-md-6">
                        			<div class="form-group">
                            			<label for="" class="control-label col-sm-3">Created By</label>
                            			<div class="col-sm-9">
                            				<input type="text" class="form-control input-sm" id="createdby" name="createdby" readonly>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label for="" class="control-label col-sm-3">Created Date</label>
                            			<div class="col-sm-9">
                            				<input type="text" class="form-control input-sm" value="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" id="createddate" name="createddate" readonly>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label for="" class="control-label col-sm-3">Updated By</label>
                            			<div class="col-sm-9">
                            				<input type="text" class="form-control input-sm" id="updatedby" name="updatedby" readonly>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label for="" class="control-label col-sm-3">Updated Date</label>
                            			<div class="col-sm-9">
                            				<input type="text" class="form-control input-sm" value="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" id="updateddate" name="updateddate" readonly>
                            			</div>
                            		</div>
                        		</div>
                            </form>
                        </div>

                        <div class="row">
                        	<div class="col-md-12">
                        		<div class="portlet box">
                        			<div class="portlet-body">
                        				<table class="table table-bordered table-hover table-striped table-responsive" id="tbl_items">
                                			<thead>
                                				<tr>
                                                    <td style="width: 5%;">
                                                        <input type="checkbox" class="check_all_items">
                                                    </td>
                                					<td style="width: 8%;"></td>
                                					<td>Item Code</td>
                                					<td>Item Name</td>
                                					<td>Qty</td>
                                					<td>Lot No.</td>
                                					<td>Expiration</td>
                                					<td>Disposition</td>
                                					<td>Remarks</td>
                                				</tr>
                                			</thead>
                                			<tbody id="tbl_items_body">
                                				<!-- content here! -->
                                			</tbody>
                                		</table>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="button" class="btn btn-sm red" id="btn_delete">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>

                                        

                        			</div>
                        		</div>
                        	</div>
                        </div>

                        <div class="row">
                        	<div class="col-md-12 text-center">
								<button type="button" class="btn btn-sm green" id="btn_add">
									<i class="fa fa-plus"></i> Add
								</button>
                                <button type="button"class="btn btn-sm blue" id="btn_save">
                                    <i class="fa fa-floppy-o"></i> Save
                                </button>
                                <button type="button" class="btn btn-sm blue" id="btn_edit">
                                    <i class="fa fa-edit"></i> Edit
                                </button>

                                <button type="button" class="btn btn-sm blue-madison" id="btn_search">
                                    <i class="fa fa-search"></i> Search
                                </button>
								<button type="button" class="btn btn-sm grey-gallery" id="btn_disregard">
									<i class="fa fa-thumbs-o-down"></i> Disregard
								</button>
								<button type="button" class="btn btn-sm purple" id="btn_export">
									<i class="fa fa-print"></i> Export to Excel
								</button>
							</div>
                        </div>

					</div>
				</div>
				<!-- END EXAMPLE TABLE PORTLET-->
			</div>
		</div>
		<!-- END PAGE CONTENT-->
	</div>

    @include('includes.wbs.material_disposition_modal')
    @include('includes.modals')

@endsection

@push('script')
	<script type="text/javascript">
		var token = "{{ Session::token() }}";
        var getItemCodeURL = "{{ url('/matdis-search-item') }}";
        var saveLotNosURL = "{{url('/matdis-save-item')}}";
        var getAllDataURL = "{{url('/matdis-get-data')}}";
        var getSearchedMaterialsURL = "{{url('/matdis-get-searched-materials')}}";
        var exportMaterialURL = "{{url('/matdis-get-data-export')}}";
        var getCurrentQtyURL = "{{url('/matdis-get-current-qty')}}";
        var DeleteItemURL = "{{url('/matdis-delete-item')}}";


	</script>
	<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/wbs/materialdisposition.js').'?v='.date('YmdHis') }}" type="text/javascript"></script>
@endpush