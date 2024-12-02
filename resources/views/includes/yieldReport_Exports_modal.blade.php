 <!-- Exports Modal -->
     <div id="Export-Modal" class="modal fade" role="dialog">
          <div class="modal-dialog modal-md">
               <div class="modal-content">
                     <div class="modal-header">
                         <button type="button" class="close" data-dismiss="modal">&times;</button>
                         <h4 class="mAndr-title">Export</h4>
                    </div>
                    <div class="modal-body">
                         <div class="row">
                              {!! csrf_field() !!}

                              <div class="form-group row">
                                   <div class="col-sm-12 col-sm-offset-3">
                                        <button type="button" class="btn col-sm-6 grey-cascade" id="btnxport-defectsummaryrpt" ><i class="fa fa-chain-broken"></i>Defect Summary</button>
                                   </div>
                              </div>

                              <div class="form-group row">
                                   <div class="col-sm-12 col-sm-offset-3">
                                        <button type="button" class="btn col-sm-6 blue-hoki" id="btnxport-summaryrpt" ><i class="fa fa-list-ul"></i>Yield Performance Report</button>
                                   </div>
                              </div>

                              <div class="form-group row">
                                   <div class="col-sm-12 col-sm-offset-3">
                                        <button type="button" class="btn col-sm-6 red-sunglo" id="btnxport-yieldpsrpt" ><i class="fa fa-list-alt"></i>Yield Performance Summary</button>
                                   </div>
                              </div>
                              
                              <div class="form-group row">
                                   <div class="col-sm-12 col-sm-offset-3">
                                        <button type="button" class="btn col-sm-6 purple-plum" id="btnxport-yieldsfrpt" ><i class="fa fa-align-justify"></i>Yield Summary per Family</button>
                                   </div>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-danger" data-dismiss="modal" id="btn_cancel"><i class="fa fa-times"></i>Cancel</button>
                    </div>
               </div>
          </div>
     </div>