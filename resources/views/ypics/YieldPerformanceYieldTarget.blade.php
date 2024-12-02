@extends('layouts.master')    
@section('title')
    Yield Target | Pricon Microelectronics, Inc.
@endsection

@section('content')

     <?php $state = ""; $readonly = ""; ?>
     @foreach ($userProgramAccess as $access)
          @if ($access->program_code == Config::get('constants.MODULE_CODE_YIELDTAR'))  <!-- Please update "2001" depending on the corresponding program_code -->
               @if ($access->read_write == "2")
               <?php $state = "disabled"; $readonly = "readonly"; ?>
               @endif
          @endif
     @endforeach

     <div class="page-content">

          <!-- BEGIN PAGE CONTENT-->
          <div class="row">
               <div class="col-sm-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    @include('includes.message-block')
                    <div class="portlet box blue" >
                         <div class="portlet-title">
                              <div class="caption">
                                   <i class="fa fa-navicon"></i>  Yield Target
                              </div>
                         </div>
                              <!-- Target Registration -->
                    </div>
                         <div class="row">
                               <div class="col-sm-12">
                                   <form class="form-horizontal">
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                             <label class="control-label col-sm-2">From</label>
                                             <div class="col-sm-3">
                                                  <input type="text" class="form-control date-picker input-sm" name="target-datefrom" id="target-datefrom">
                                                  <div id="er_target-datefrom"></div>
                                             </div>   
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-2">To</label>
                                             <div class="col-sm-3">
                                                  <input type="text" class="form-control date-picker input-sm" name="target-dateto" id="target-dateto">
                                                  <div id="er_target-dateto"></div>
                                             </div>   
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-2">Target Yield</label>
                                             <div class="col-sm-6">
                                                  <input type="text" class="form-control input-sm" id="targetyield" name="targetyield">
                                                  <div id="er_targetyield"></div>
                                             </div>     
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-2">Target DPPM</label>
                                             <div class="col-sm-6">
                                                  <input type="text" class="form-control input-sm" id="targetdppm" name="targetdppm">
                                                  <input type="hidden" class="form-control input-sm" id="targetstatus" name="targetstatus" maxlength="40" >
                                                  <input type="hidden" class="form-control input-sm" id="targetid" name="targetid" maxlength="40">
                                                  <div id="er_targetdppm"></div>
                                             </div>     
                                        </div>
                                        <div class="form-group">
                                             <label class="control-label col-sm-2">Product Type</label>
                                             <div class="col-sm-6">
                                                  <select class="form-control input-sm" name="targetptype" id="targetptype">
                                                    <option value=""></option>
                                                       <option value="Test Socket">Test Socket</option>
                                                       <option value="Burn In">Burn In</option>  
                                                  </select>
                                                  <div id="er_targetyield"></div>
                                             </div>     
                                        </div>
                                        <br>
                                        <div class="form-group pull-right">
                                             <div class="col-sm-12">
                                                  <button type="button" id='targetsave'  class="btn btn-success">Save</button>
                                                  <button type="button" id='targetclear' class="btn btn-danger">Clear</button>
                                             </div>
                                        </div>      
                                   </form>       
                              </div>
                         </div>
                         <div class="row">
                              <div class="col-sm-12">
                                   <!-- <div class="scroller" style="height: 300px" id="tablefortargetreg"> -->
                                   <table id="modreg-table" class="table table-striped table-bordered table-hover"style="font-size:10px">
                                        <thead id="thead1">
                                             <tr>
                                                 <td class="table-checkbox" style="width: 5%">
                                                       <input type="checkbox" class="group-checkable checkAllitemstarget" name="checkAllitemtarget"/>   
                                                  </td>
                                                  <td>From</td>
                                                  <td>To</td>
                                                  <td>Target Yield</td>
                                                  <td>Target DPPM</td>
                                                  <td>Product Type</td>

                                             </tr>
                                        </thead>
                                        <tbody id="tblfortarget"></tbody>
                                   </table>
                                       
                              </div>
                         </div>
                    
                         <!-- <button type="button" id='saveTarget' onclick="javascript:removetargetreg();"  class="btn red">Remove</button> -->
                         <button type="button" id='saveTarget' class="btn red">Remove</button>
                         <a href="{{ url('/yieldperformanceReport') }}" class="btn btn-danger" id="btn_targetclose">Close</a>
                  
              
                       
                    <!-- END EXAMPLE TABLE PORTLET-->
               </div>
          </div>
          <!-- END PAGE CONTENT-->
     </div>


    @include('includes.modals')

@endsection

@push('script')

<script type="text/javascript">
     var token = "{{ Session::token() }}";
     var addtarget = "{{ url('/add-targetreg') }}";
     var edittarget = "{{ url('/edittargetreg') }}";
     var displaytarget = "{{ url('/getTargetYield') }}";
     var removetarget = "{{ url('/deleteAlltargetreg') }}";

</script>

<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/targetyield.js') }}" type="text/javascript"></script>



@endpush