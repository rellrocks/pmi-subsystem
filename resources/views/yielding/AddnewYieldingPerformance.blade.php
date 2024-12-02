@extends('layouts.master')    
@section('title')
     Yield Performance | Pricon Microelectronics, Inc.
@endsection

@push('css')
    <style type="text/css">
        .dataTables_scrollHeadInner{
            width:100% !important;
        }
        .dataTables_scrollHeadInner table{
            width:100% !important;
        }
        .modal-backdrop {
            z-index: -1;
        }
    </style>
@endpush

@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
          @if ($access->program_code == Config::get('constants.MODULE_CODE_NEWTRAN'))  <!-- Please update "2001" depending on the corresponding program_code -->
               @if ($access->read_write == "2")
               <?php $state = "disabled"; $readonly = "readonly"; ?>
               @endif
          @endif
     @endforeach
     
    <div class="page-content">

        <div class="portlet box blue" >
            <div class="portlet-title">
                <div class="caption">
                     <i class="fa fa-navicon"></i>  Add New Yield Performance
                </div>
            </div>

            <div class="portlet-body">
                <div class="row">
                    <div class="col-sm-12">  
                        <form class="form-horizontal">
                            {!! csrf_field() !!}
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label col-sm-3">PO No.</label>
                                    <div class="col-sm-8">
                                        <input type="hidden"  id="id" name="id"/>
                                        <input type="hidden"  id="row" name="row"/>
                                        <input type="text" value="@foreach($msrecords as $msrec){{$msrec->PO}}@endforeach" class="form-control input-sm" id="pono" name="pono"/>
                                        <div id="er1"></div>

                                    </div>
                                                                                            
                                 </div>

                                 <div class="form-group">
                                    <label class="control-label col-sm-3">PO Qty</label>
                                    <div class="col-sm-8">                                    
                                        <input class="form-control input-sm" size="16" type="text" name="poqty" id="poqty" 
                                    disabled="disabled"/> 
                                    <input class="form-control input-sm" size="16" type="hidden" name="hdpoqty" id="hdpoqty"/> 
                                        <div id="error1"></div>  
                                    </div>
                                </div> 

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Device</label>
                                    <div class="col-sm-8">                                        
                                        <input type="text" class="form-control input-sm" id="device" name="device" disabled="disabled"/>
                                        <div id="error2"></div>   
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Family</label>
                                    <div class="col-sm-8">
                                        <Select class="form-control input-sm" id="family" name="family" required>
                                            <option value=""></option>
                                          @foreach($family as $family)
                                                <option value="{{$family->description}}">{{$family->description}}
                                                </option>
                                            @endforeach
                                        </Select>
                                        <div id="er2"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Series</label>
                                    <div class="col-sm-8">
                                        <Select class="form-control input-sm" id="series" name="series" required>
                                            <option value=""></option>
                                            @foreach($series as $series)
                                                <option value="{{$series->description}}">{{$series->description}}
                                                </option>
                                            @endforeach
                                        </Select>
                                        <div id="er3"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">ProductType</label>
                                    <div class="col-sm-8">
                                        <Select class="form-control input-sm" id="prodtype" name="prodtype" required>
                                           {<option value=""></option>
                                            <option value="Test Socket">Test Socket</option>
                                            <option value="Burn In">Burn In</option> 
                                        </Select>
                                        <div id="erprodtype"></div>
                                    </div>
                                </div><br>
                                 <div class="col-sm-3 col-sm-offset-9">
                                        <button type="button" name="search-task"  class="btn btn-circle input-sm green load-task"  id="btnload">
                                           <i class="fa fa-arrow-circle-down"></i> 
                                        </button>
                                    </div>  
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Production Date</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control input-sm" id="productiondate" name="productiondate"/>
                                    </div>
                                      
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Yielding Station</label>
                                    <div class="col-sm-8">
                                        <Select class="form-control input-sm" id="yieldingstation" name="yieldingstation">
                                            <option value=""></option>
                                            @foreach($yieldstation as $ys)
                                                <option value="{{$ys->description}}">{{$ys->description}}
                                                </option>
                                            @endforeach
                                        </Select>
                                        <div id="er6"></div>
                                    </div>
                                </div>

                                 <div class="form-group">
                                    <label class="control-label col-sm-4">Accumulated Output</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="accumulatedoutput" name="accumulatedoutput" />
                                        <div id="er7"></div>
                                    </div> 

                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Classification</label>
                                    <div class="col-sm-8">
                                        <Select class="form-control input-sm" id="classification" name="classification">
                                            <option value=""></option>
                                            <option value="NDF">NDF</option>
                                            <option value="Material NG (MNG)">Material NG (MNG)</option>
                                            <option value="Production NG (PNG)">Production NG (PNG)</option>   
                                        </Select>
                                        <div id="er4"></div>
                                    </div>   
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4" id="select2">Mode of Defect</label>
                                    <div class="col-sm-8">
                                        <Select class="form-control input-sm mod" id="mod" name="mod">
                                            
                                        <option value=" "> </option>
                                            {{-- @foreach($modefect as $modefect)
                                                <option value="{{$modefect->description}}">{{$modefect->description}}
                                                </option>
                                            @endforeach --}}
                                        </Select>
                                        <div id="er5"></div>
                                    </div>   
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Qty</label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control input-sm" id="qty" name="qty" />
                                        <div id="er10"></div>
                                    </div>
                                    
                                 </div>

                                 <div class="form-group">
                                    <label class="control-label col-sm-4">Remarks</label>
                                    <div class="col-sm-8">
                                        <textarea name="remarks" id="remarks" class="form-control input-sm"></textarea>
                                        <div id="er10"></div>
                                    </div>
                                 </div><br>
                                  <div class="col-sm-2 col-sm-offset-10">
                                    <button type="button" onclick="javascript:addpya();" name="search-task"  class="btn btn-circle input-sm green load-task"  id="btnloadpya">
                                    <i class="fa fa-plus"></i> 
                                    </button>
                                </div>   
                            </div>

                            <div class="col-sm-4">
                                 <div class="form-group">
                                    <label class="control-label col-sm-4">Total Input</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="tinput" name="tinput"/>
                                        <input type="hidden" class="form-control input-sm" id="hiddentinput" name="hiddentinput"/>
                                        <div id="er8"></div>
                                      </div>
                                </div>
                                   <div class="form-group">
                                    <label class="control-label col-sm-4">Total Output</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="toutput" name="toutput"/>
                                        <div id="er8"></div>
                                      </div>
                                </div>
      
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total Reject</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="treject" name="treject"/>
                                        <div id="er9"></div>
                                    </div>
                                 </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total MNG</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="tmng" name="tmng"  disabled="disabled" />
                                    </div>
                                 </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total PNG</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="tpng" name="tpng"  disabled="disabled" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">% Yield w/o MNG</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="ywomng" name="ywomng"  disabled="disabled" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total Yield</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="twoyield" name="twoyield"  disabled="disabled" />
                                        <input type="hidden" class="form-control input-sm" id="counter" name="counter"  disabled="disabled" />
                                    </div>
                                 </div>

                            </div>
                        </form>

                    </div>
                </div>  

                <br>

                <div class="form-group pull-right">
                    <label class="control-label col-sm-2">DPPM</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control input-sm" id="dppm" name="dppm" disabled="disabled">
                        <input type="hidden" class="form-control input-sm " name="hdstatus" id="hdstatus"></input>
                    </div>    
                </div>
                <br/>

                <div class="row">
                    <div class="col-sm-12 text-center">
                        <button type="button" style="font-size:12px;" onclick="javascript:addnew();" class="btn green input-sm" id="btnadd">
                           <i class="fa fa-plus"></i> Add New
                        </button>
                        <button type="button" style="font-size:12px;" onclick="javascript: setcontrol('DIS'); " class="btn red-intense input-sm" id="btndiscard">
                           <i class="fa fa-pencil"></i> Discard Changes
                        </button>
                        <button type="button" style="font-size:12px;" class="btn green input-sm" id="btnsave">
                           <i class="fa fa-save"></i> Save
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                        <div class="col-sm-12" > 
                            <table id="tbl_pya" class="table table-striped table-bordered table-hover"style="font-size:10px">
                                <thead id="thead1">
                                    <tr>
                                        <td class="table-checkbox" style="width: 5%">
                                            <input type="checkbox" class="check_all_pya"/>
                                        </td>
                                        <td style="width: 5%">
                                        </td>
                                        <td>Production Date</td>
                                        <td>Yielding Station</td>
                                        <td>Accumulated Output</td>
                                        <td>Classification</td>
                                        <td>Mode of Defects</td>
                                        <td>Quantity</td>
                                        <td>Remarks</td>
                                    </tr>
                                </thead>
                                <tbody id="tbl_pya_body"></tbody>
                            </table>
                       
                        <button style="margin-top: 20px;" type="button" onclick="javascript:deletepya();" name="delete-taskPYA" class="btn btn-sm btn-danger delete-taskPYA" id="delete-taskPYA">Delete
                             <i class="fa fa-trash"></i> 
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>


    @include('includes.yielding-modals')
    @include('includes.modals')

@endsection

@push('script')
<script type="text/javascript">
    var token = "{{ Session::token() }}";
    var saveURL = "{{ url('/save-yield') }}";
    var searchPOURL = "{{ url('/search-pono2') }}";
    var getMODURL = "{{ url('/get_mod') }}";
    var getAutoValueURL = "{{ url('/getautovalue') }}";
    var getPngURL = "{{ url('/getpng') }}";
    var getMngURL = "{{ url('/getmng') }}";
    var searchDisplayPYAURL = "{{ url('/searchdisplaypya') }}";
    var searchDisplayCMQURL = "{{ url('/searchdisplaycmq') }}";
    var searchDisplayDetailsURL = "{{ url('/searchdisplaydetails') }}";
    var searchDisplaySummaryURL = "{{ url('/searchdisplaysummary') }}";
    var deleteAllPOURL = "{{ url('/deleteAll-pono2') }}";
    var searchYieldURL = "{{ url('/search-yieldperformance2') }}";
    var deletePyaURL = "{{ url('/deletepya') }}";
    var deleteCmqURL = "{{ url('/deletecmq') }}";
    var backURL = "{{ url('/yieldperformance') }}";
    var getPODetailsURL = "{{ url('/GetPONumberDetails')}}";
    var getFamilyDropdownURL = "{{ url('/getFamilyDropDown') }}";
    var getProdtypeDropdownURL = "{{ url('/getProdtypeDropdown') }}";
    var GetdeffectsURL = "{{ url('/Getdeffects') }}";
</script>
<script>
    
  $('#mod').select2({
  selectOnClose: true
});
</script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/add_new_yielding_performance.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}"></script>
@endpush