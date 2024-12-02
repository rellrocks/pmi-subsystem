
<?php
/*******************************************************************************
     Copyright (c) Company Nam All rights reserved.

     FILE NAME: poregistration.blade.php
     MODULE NAME:  6002 : PO Registration
     CREATED BY: dax
     DATE CREATED: 2018.08.08
     REVISION HISTORY :

     
*******************************************************************************/
?>

@extends('layouts.master')

@section('title')
PO Registration | Pricon Microelectronics, Inc.
@endsection


@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
        @if ($access->program_code == Config::get('constants.MODULE_CODE_POREG'))
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
                            <i class="fa fa-navicon"></i>  P.O. Registration
                        </div>
                    </div>
                    <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-right">
                                        {{-- <form class="" action="{{ url('/updatedevice') }}" method="post" id="frm">
                                                        {{ csrf_field() }} --}}
                                           <button type="button" id="add" class="btn btn-sm green input-sm"  {{$state}}> 
                                                <i class="fa fa-plus"></i> ADD  PO DETAILS
                                           </button>
                                           <button type="button" id="update" class="btn btn-sm blue input-sm"  {{$state}}> 
                                                <i class="fa fa-star"></i> UPDATE DEVICE FROM YPICS
                                            </button>
                                        {{-- </form> --}}
                                    </div>
                                </div>
                            </div>
                                  <br/>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tbl_device" style="font-size:10px">
                                            <thead>
                                                <tr>
                                                    <td>PO NUmber</td>
                                                    <td>Device Code</td>
                                                    <td>Device Name</td>
                                                    <td>PO Qty</td>
                                                    <td>Family</td>
                                                    <td>Series</td>
                                                    <td>Product Type</td>
                                                    <td>Update</td>
                                                </tr>
                                            </thead>
                                            <tbody id="tbl_device_body"></tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->

            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>

    @include('includes.yielding_poreg_modal')
    @include('includes.modals')

        

@endsection

@push('script')
<script type="text/javascript">
    var token = "{{ Session::token() }}";
    var loadporegdevice = "{{url('/getdevice')}}";
    var displayporegitem = "{{url('/displayItem')}}";
    var loadfamilylist = "{{url('/getFamilyDropDown')}}"
    var loadserieslist = "{{url('/getSeriesDropdown')}}"
    var getdropdownlang = "{{url('/yielddropdowns')}}";
    var getpoypics = "{{ url('/CheckYpicsPO') }}";
    var addpodata = "{{ url('/add-poregistration') }}";
    var loadypicsdevice = "{{url('/updatedevice')}}";
    var getPOregistration = "{{ url('/get-poregistration')}}";
    var displayporeg = "{{ url('/displayporeg')}}";
    var deleteporeg = "{{ url('/deleteporeg')}}";
</script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
<script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/yielding_poreg.js') }}" type="text/javascript"></script>
@endpush


