<!--Yield Performance Summary Report -->
     <div id="yieldpsrpt_Modal" class="modal fade" role="dialog" data-backdrop="static">
          <div class="modal-dialog gray-gallery">
               <div class="modal-content ">
                    <div class="modal-header">
                         <h4>Yield Performance Summary Report</h4>
                    </div>
                    <div class="modal-body">
                         <div class="row">
                              <div class="col-sm-12">
                                   <form class="form-horizontal">
                                        {!! csrf_field() !!}
                                        
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">From</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm date-picker" autocomplete="off" name="ypsr-datefrom" id="ypsr-datefrom">
                                             </div>     
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-3">To</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm date-picker" autocomplete="off" name="ypsr-dateto" id="ypsr-dateto">
                                             </div>
                                        </div>

                                        <div class="form-group">
                                             <label class="control-label col-sm-3">Product Type</label>
                                             <div class="col-sm-9">
                                                  <Select class="form-control input-sm " id="ypsr-prodtype" name="ypsr-prodtype">
                                                       <option value=""></option>
                                                       <option value="TEST SOCKET">TEST SOCKET</option>
                                                       <option value="BURN-IN">BURN-IN</option>
                                                 </Select>
                                             </div>
                                        </div>
                                        
                                        <hr>

                                        <div class="form-group">
                                             <label class="control-label col-sm-3">Target Yield</label>
                                             <div class="col-sm-9">
                                                  <input type="text" class="form-control input-sm" name="ypsr-targetyield" id="ypsr-targetyield">
                                             </div>
                                        </div>
                                        
                                        <div class="form-group pull-right">
                                             <div class="col-sm-12">
                                                  <button type="button" id="btn_export_yield_performance_summary"  class="btn green-jungle input-sm">Export to Excel</button>
                                                  {{-- <button type="button" onclick="javascript:yieldsumRptpdf();" class="btn yellow-gold input-sm" >Export to PDF</button>  --}}
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