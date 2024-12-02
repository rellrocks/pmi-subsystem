<!-- Defect Summary Report -->
    <div id="defectsummaryrpt_Modal" class="modal fade" role="dialog" data-backdrop="static">
          <div class="modal-dialog gray-gallery">
               <div class="modal-content ">
                    <div class="modal-header">
                         <h4 class="defectsummaryrpt-title"></h4>
                    </div>
                    <div class="modal-body">
                         <div class="row">
                              <div class="col-sm-12">
                                   <form class="form-horizontal">
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">From</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm date-picker" autocomplete="off" name="dsr-datefrom" id="dsr-datefrom">
                                             </div>     
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">To</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm date-picker" autocomplete="off" name="dsr-dateto" id="dsr-dateto">
                                             </div>
                                        </div>
                                        <hr>
                                        <div class="form-group hidden">
                                             <label class="control-label col-sm-3">P.O.</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" name="dsr-po" id="dsr-po">
                                             </div>
                                        </div>

                                        <div class="form-group ">
                                             <label class="control-label col-sm-3">Product Type</label>
                                             <div class="col-sm-9">
                                                  <select class="form-control input-sm" name="dsr-ptype" id="dsr-ptype">
                                                       <option value=""></option>
                                                       <option value="TEST SOCKET">TEST SOCKET</option>
                                                       <option value="BURN-IN">BURN-IN</option>     
                                                  </select>
                                             </div>
                                        </div>

                                        <div class="form-group hidden">
                                             <label class="control-label col-sm-3">Family</label>
                                             <div class="col-sm-9">
                                                  <select class="form-control input-sm" name="dsr-family" id="dsr-family">
                                                       <option value=""></option>
                                                       @foreach($family as $family)
                                                            <option value="{{$family->description}}">{{$family->description}}</option>
                                                       @endforeach
                                                  </select>
                                             </div>
                                        </div>

                                        <div class="form-group hidden">
                                             <label class="control-label col-sm-3">Series Name</label>
                                             <div class="col-sm-9">
                                                  <select class="form-control input-sm" name="dsr-series" id="dsr-series">
                                                       <option value=""></option>
                                                       @foreach($series as $series)
                                                          <option value="{{$series->description}}">{{$series->description}}</option>
                                                       @endforeach
                                                  </select>
                                             </div>
                                        </div>

                                        <div class="form-group hidden">
                                             <label class="control-label col-sm-3">Device</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" name="dsr-device" id="dsr-device">
                                             </div>
                                        </div>

                                        
                                        <br>
                                        <div class="form-group pull-right">
                                             <div class="col-sm-12">
                                                  <button type="button" id="btn_defect_export"class="btn green-jungle input-sm">Export to Excel</button>
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