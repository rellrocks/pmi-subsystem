	 <!--Summary Report -->
	<div id="summaryrpt_Modal" class="modal fade" role="dialog" data-backdrop="static">
		  <div class="modal-dialog " gray-gallery">
			   <div class="modal-content ">
					<div class="modal-header">
						 <h4>Yield Performance Report</h4>
					</div>
					<div class="modal-body">
						 <div class="row">
							  <div class="col-sm-12">
								   <form class="form-horizontal">
										{!! csrf_field() !!}
										<div class="form-group">
											 <label class="control-label col-sm-3">From</label>
											 <div class="col-sm-9">
												  <input type="text" class="form-control input-sm date-picker" autocomplete="off" name="srdatefrom" id="srdatefrom">
											 </div>     
										</div>
										<div class="form-group">
											 <label class="control-label col-sm-3">To</label>
											 <div class="col-sm-9">
												  <input type="text" class="form-control input-sm date-picker auto" autocomplete="off" name="srdateto" id="srdateto">
											 </div>
										</div>
										<hr>
										<div class="form-group">
											 <label class="control-label col-sm-3">P.O.</label>
											 <div class="col-sm-9">
												  <input type="text" class="form-control input-sm" name="srpo" id="srpo">
											 </div>
										</div>
										<div class="form-group">
											 <label class="control-label col-sm-3">Production Type</label>
											 <div class="col-sm-9">
												  <Select class="form-control input-sm" id="srprodtype" name="srprodtype">
													 <option value=""></option>
													 <option value="TEST SOCKET">TEST SOCKET</option>
													  <option value="BURN-IN">BURN-IN</option>
												 </Select>
											 </div>
										</div>
										<div class="form-group">
											 <label class="control-label col-sm-3">Family</label>
											 <div class="col-sm-9">
												  <Select class="form-control input-sm" id="srfamily" name="srfamily">
												  	<option value=""></option>
                                                       @foreach($family as $family)
                                                            <option value="{{$family->description}}">{{$family->description}}</option>
                                                       @endforeach
												  </Select>
											 </div>
										</div>
										<div class="form-group">
											 <label class="control-label col-sm-3">Series Name</label>
											 <div class="col-sm-9">
												  <Select class="form-control input-sm" id="srseries" name="srseries">
												  	<option value=""></option>
                                                       @foreach($series as $series)
                                                          <option value="{{$series->description}}">{{$series->description}}</option>
                                                       @endforeach
												  </Select>
											 </div>
										</div>
										<div class="form-group">
											 <label class="control-label col-sm-3">Device</label>
											 <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="srdevice" name="srdevice">
                                             </div>
										</div>
										
										<div class="form-group pull-right">
											 <div class="col-sm-12">
												  <button type="button" id="export_btn" class="btn green-jungle input-sm">Export to Excel</button>
												  <!-- <button type="button"  onclick="javascript:summaryREptpdf();" class="btn yellow-gold input-sm" >Export to PDF</button>  -->
												   <button type="button" data-dismiss="modal" class="btn btn-danger input-sm">Close</button>
											 </div>  
										</div>
								   </form>   
							  </div>
						 </div>
					</div>
			   </div>    
		  </div>
	 </div>