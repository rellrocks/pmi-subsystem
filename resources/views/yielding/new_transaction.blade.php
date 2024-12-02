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
                     <i class="fa fa-navicon"></i>  New Yield Performance
                </div>
            </div>

            <div class="portlet-body">
                <div class="row">
                    <div class="col-sm-12">  
                        <form class="form-horizontal">
                            {!! csrf_field() !!}
                            <input type="hidden"  id="id" name="id"/>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label col-sm-3">PO No.</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control input-sm clear" id="po" name="po" size="16"/>
                                    </div>
                                    <div class="col-sm-3">
                                        <button type="button" class="btn btn-circle input-sm green" id="btn_load_po">
                                           <i class="fa fa-arrow-circle-down"></i> 
                                        </button>
                                    </div>                                                           
                                 </div>

                                 <div class="form-group">
                                    <label class="control-label col-sm-3">PO Qty</label>
                                    <div class="col-sm-6">                                    
                                        <input class="form-control input-sm clear" size="16" type="number" name="po_qty" id="po_qty"/>
                                    </div>
                                </div> 

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Device</label>
                                    <div class="col-sm-6">                                        
                                        <input type="text" class="form-control input-sm clear" id="device" name="device"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Family</label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm clear" id="family" name="family" required></select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-3">Series</label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm clear" id="series" name="series" required></select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Product Type</label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm clear" id="prod_type" name="prod_type" required>
                                            <option value=""></option>
                                            <option value="Test Socket">Test Socket</option>
                                            <option value="Burn In">Burn In</option> 
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Production Date</label>
                                    <div class="col-sm-6">
                                        <input type="date" class="form-control input-sm clear" id="production_date"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Yielding Station</label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm clear" id="yielding_station"></select>
                                    </div>
                                </div>

                                 <div class="form-group">
                                    <label class="control-label col-sm-4">Accumulated Output</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="accumulated_output"/>
                                    </div> 
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Classification</label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm clear" id="classification">
                                            <option value=""></option>
                                            <option value="NDF">NDF</option>
                                            <option value="Material NG (MNG)">Material NG (MNG)</option>
                                            <option value="Production NG (PNG)">Production NG (PNG)</option>   
                                        </select>
                                    </div>   
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Mode of Defect</label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm clear mod" id="mode_of_defect"></select>
                                    </div>   
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Qty</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="defect_qty"/>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-circle input-sm green"  id="btn_add_details">
                                            <i class="fa fa-plus"></i> 
                                        </button>
                                    </div>       
                                 </div>

                                 <div class="form-group">
                                    <label class="control-label col-sm-4">Remarks</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control input-sm clear remarks" id="remarks"></textarea>
                                    </div>   
                                </div>

                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total Input</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="total_input" name="total_input"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total Output</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="total_output" name="total_output"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total Reject</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="total_reject" name="total_reject"/>
                                    </div>
                                 </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total MNG</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="total_mng" name="total_mng"/>
                                    </div>
                                 </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total PNG</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control input-sm clear" id="total_png" name="total_png"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">% Yield w/o MNG</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control input-sm clear" id="yield_wo_mng" name="yield_wo_mng"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Total Yield</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control input-sm clear" id="total_yield" name="total_yield"/>
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
                        <input type="text" class="form-control input-sm clear" id="dppm" name="dppm">
                    </div>    
                </div>
                <br/>

                <div class="row">
                    <div class="col-sm-12 text-center" style="font-size:12px;">
                        <button type="button" onclick="javascript:new_transaction();" class="btn green input-sm" id="btn_add_new">
                           <i class="fa fa-plus"></i> Add New
                        </button>
                        <button type="button" onclick="javascript: discard(); " class="btn red-intense input-sm" id="btn_discard">
                           <i class="fa fa-pencil"></i> Discard Changes
                        </button>
                        <button type="button" class="btn green input-sm" id="btn_save">
                           <i class="fa fa-save"></i> Save
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-sm-12" > 
                        <table id="tbl_details" class="table table-striped table-bordered table-hover"style="font-size:10px">
                            <thead id="thead1">
                                <tr>
                                    <td class="table-checkbox" style="width: 5%">
                                        <input type="checkbox" class="check_all_details"/>
                                    </td>
                                    <td style="width: 5%">
                                    </td>
                                    <td>Production Date</td>
                                    <td>Yielding Station</td>
                                    <td>Accumulated Output</td>
                                    <td>Classification</td>
                                    <td>Mode of Defects</td>
                                    <td>Quantity</td>
                                    <td>remarks</td>
                                </tr>
                            </thead>
                            <tbody id="tbl_details_body"></tbody>
                        </table>
                       
                        <button type="button" class="btn btn-sm btn-danger delete_details">
                             <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @include('includes.modals')

@endsection

@push('script')
<script type="text/javascript">
    var token = "{{ Session::token() }}";
    var DropdownsURL = "{{ url('/new-transaction-dropdowns') }}";
    var getPODetailsURL = "{{ url('/get-po-details')}}";



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
    
    var getFamilyDropdownURL = "{{ url('/getFamilyDropDown') }}";
    var getProdtypeDropdownURL = "{{ url('/getProdtypeDropdown') }}";
</script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/yield_new_transaction.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}"></script>
@endpush