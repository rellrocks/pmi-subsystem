     <div id="updateyield_Modal" class="modal fade" role="dialog" data-backdrop="static">
          <div class="modal-dialog gray-gallery">
               <div class="modal-content ">
                    <div class="modal-header">
                         <h4 class="updatetitle"></h4>
                    </div>
                    <div class="modal-body">
                         <div class="row">
                              <div class="col-sm-12">
                                   <form class="form-horizontal">
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">PO Number</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="pono2" name="pono2" disabled="disabled">
                                                  <input type="hidden" class="form-control input-sm" id="masterid" name="masterid">
                                             </div>     
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">PO Quantity</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="poqty2" name="poqty2" maxlength="40" disabled="disabled" >
                                                  <div id="er1"></div>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                             <label class="control-label col-sm-3" style="font-size:12px">Device</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="device2" name="device2" value="@foreach($msrecords as $msrec){{$msrec->devicename}}@endforeach" disabled="disabled" />
                                                  
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                             <label class="control-label col-sm-3" style="font-size:12px">Series</label>
                                             <div class="col-sm-9">
                                                  <Select class="form-control input-sm" id="series2" name="series2">
                                                  @foreach ($series as $serie)
                                                  <option value="{{$serie->description}}">{{$serie->description}}
                                                  </option>
                                                  @endforeach
                                                  </Select>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                             <label class="control-label col-sm-3" style="font-size:12px">Family</label>
                                             <div class="col-sm-9">
                                                  <Select class="form-control input-sm" id="family2" name="family2">
                                                 
                                                  @foreach ($family as $fam)
                                                  <option value="{{$fam->description}}">{{$fam->description}}</option>
                                                  @endforeach
                                                  </Select>
                                             </div>
                                        </div>
                                         <div class="form-group row">
                                             <label class="control-label col-sm-3" style="font-size:12px">Total Output</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="toutput2" name="toutput2"/>
                                                  <div id="erd2"></div>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                             <label class="control-label col-sm-3" style="font-size:12px">Total Reject</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="treject2" name="treject2" />
                                                  <div id="erd3"></div>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                             <label class="control-label col-sm-3" style="font-size:12px">Total w/o Yield</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" id="twoyield2" name="twoyield2" disabled="disabled"/>
                                                   
                                             </div>
                                        </div>    
                                   </form>   
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" id='modalsaveUpdate' onclick="javascript:update();"  class="btn btn-success">Update</button> 
                         <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                    </div>
               </div>    
          </div>
     </div>