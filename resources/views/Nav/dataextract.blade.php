@extends('layouts.master')

@section('title')
	WBS | Pricon Microelectronics, Inc.
@endsection

@push('css')
	<style type="text/css">
        table.table-fixedheader {
            width: 100%;
        }
        table.table-fixedheader, table.table-fixedheader>thead, table.table-fixedheader>tbody, table.table-fixedheader>thead>tr, table.table-fixedheader>tbody>tr, table.table-fixedheader>thead>tr>td, table.table-fixedheader>tbody>td {
            display: block;
        }
        table.table-fixedheader>thead>tr:after, table.table-fixedheader>tbody>tr:after {
            content:' ';
            display: block;
            visibility: hidden;
            clear: both;
        }
        table.table-fixedheader>tbody {
            overflow-y: scroll;
            height: 200px;
        }
        table.table-fixedheader>thead {
            overflow-y: scroll;
        }
        table.table-fixedheader>thead::-webkit-scrollbar {
            background-color: inherit;
        }

        table.table-fixedheader>thead>tr>td:after, table.table-fixedheader>tbody>tr>td:after {
            content:' ';
            display: table-cell;
            visibility: hidden;
            clear: both;
        }

        table.table-fixedheader>thead tr td, table.table-fixedheader>tbody tr td {
            float: left;
            word-wrap:break-word;
            height: 40px;
        }

        table.table-fifo>tbody {
            overflow-y: scroll;
            height: 375px;
        }
       /* #hd_barcode {
        	position: absolute;
		    z-index: -1;
        }*/
        .table-striped > tbody > .selected{
            background-color: #a6b4cd!important;
        }
    </style>
@endpush

@section('content')
	<?php $state = ""; $readonly = ""; ?>
	@foreach ($userProgramAccess as $access)
		@if ($access->program_code == Config::get('constants.MODULE_CODE_NAVCSV'))  <!-- Please update "2001" depending on the corresponding program_code -->
			@if ($access->read_write == "2")
			<?php $state = "disabled"; $readonly = "readonly"; ?>
			@endif
		@endif
	@endforeach
    
    <div class="page-content">
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                @include('includes.message-block')
                <div class="portlet box blue" >
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-navicon"></i>CSV
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">                                                                           
                             <div class="col-md-6 checkall_container">
                            </div> 
                                <div class="col-md-6">
                                   {{--  <label>Date :</label> --}}
                                    <div class="input-group input-large date-picker input-daterange" data-date="<?php echo date("Ymd"); ?>" data-date-format="yyyy-mm-dd">
                                          <span class="input-group-addon">Date from:</span>
                                        <input type="text" class="form-control input-md reset" name="date_from" id="date_from" placeholder="yyyy-mm-dd" />
                                        <span class="input-group-addon">to:</span>
                                        <input type="text" class="form-control input-md reset" name="date_to" id="date_to" placeholder="yyyy-mm-dd" />
                                    </div>
                                  {{--   <label>Set Date: </label> --}}
                                </div>
                        </div>
                        <br>
                            <style>
                            .btn {
                             width: 200px;
                             }
                             </style>
                        <div class="row">
                            <div class="col-md-6 extract">


                                <div class="col-md-12 checkall_container">
                                     <input type="checkbox" name="all" id="checkall" checked="true"/>
                                     <label>ALL TABLES</label>
                                </div> 
                                <div class="col-md-12">
                                    <input type="checkbox" class="test" checked="true"  value="Raw_Mats" id="rwmats" />
                                     <label>SALES LINE</label>     
                                </div>
                                <div class="col-md-12">
                                    <input type="checkbox" class="test" checked="true"  value="Sales_header" id="rwmats" />
                                     <label>SALES HEADER</label>     
                                </div>
                                <div class="col-md-12 extract">
                                    <input type="checkbox" class="test" checked="true" value="Bom" id="bom" />
                                    <label>BOM</label>
                                </div>
                                <div class="col-md-12 extract">
                                     <input type="checkbox" class="test" checked="true" value="Consumption" id="cnsmptn" />
                                     <label>CONSUMPTION</label>
                                </div>
                                <div class="col-md-12 extract ">
                                    <input type="checkbox" class="test" checked="true" value="Packinglist" id="pcklist" />
                                    <label>PACKING LIST</label>
                                </div>
                            </div>                                                   
                            <div class="col-md-6">
                                 <div class="col-md-12 check_productline">
                                    <label>PRODUCT LINE</label>
                                    <input type="checkbox" checked="true" class="pline" value="TS" id="bu2_v4"/>
                                    <label>TS</label>
                                    <input type="checkbox" checked="true" class="pline" value="CN" id="cn_v4"/>
                                    <label>CN</label>
                                    <input type="checkbox" checked="true" class="pline" value="YF" id="yf_v4"/>
                                    <label>YF</label>
                                </div> 
                                {{-- <br> --}}
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success input-md" id="btn_force_dl" name=""> Download </button><br>
                                    <button type="button" class="btn btn-danger input-md" id="btn_cancel_dl" name=""> Cancel </button>
                                </div>
                            </div> 
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('includes.modals')
@endsection
@push('script')
<script>
    var token = "{{ Session::token() }}";
    var ExportCSVURL = "{{ url('/export-csv') }}";
    var TimeSettingURL = "{{url('/time-setting')}}";
    var GetTimeURL = "{{url('/get-time')}}"
    var UpdateTimeURL = "{{url('/update-time')}}"

</script>
   @if(Session::has('success'))
    <script type="text/javascript">
    $(function() {
       msg('Data extract successfully!','success');
    });
    </script>
    @endif
       @if(Session::has('failed'))
    <script type="text/javascript">
    $(function() {
       msg('Data extract failed!','failed');
    });
    </script>
    @endif
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/extractcsv.js') }}" type="text/javascript"></script>
@endpush