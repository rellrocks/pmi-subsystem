@extends('layouts.master')

@section('title')
YPICS | Pricon Microelectronics, Inc.
@endsection

@push('css')
<style>
    .form-control {
        height: 36px;
    }
</style>
@endpush


@section('content')

    <?php $state = ""; $readonly = ""; ?>
    @foreach ($userProgramAccess as $access)
        @if ($access->program_code == Config::get('constants.MODULE_CODE_FLEX'))
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
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="portlet box blue" >
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-cubes"></i>  Inventory Data
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row text-center">
                            <div class="col-md-12">
                                <h4>Click download button to download inventory data.</h4>
                                <hr/>
                                <button type="button" class="btn btn-block blue" id="btn_inventory_data_download">Download Inventory Data</button>
                            </div>
                                
                        </div> 
                    </div>
                </div>

            </div>

            <div class="col-md-7">
                <div class="portlet box blue" >
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-line-chart"></i>  Parts Incoming Plan
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row text-center">
                            <div class="col-md-12">
                                <h4>Upload PPS Delivery File</h4>
                                <form method="POST" action="{{ url('/flex-parts-incoming-upload') }}" accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal" id="frmPartsIncoming">
                                    {{ csrf_field() }}

                                    <div class="form-group row">
                                        <label class="control-label col-md-3">PPS Delivery File</label>
                                        <div class="col-md-7">
                                            <input type="file" class="filestyle" data-buttonName="btn-primary" name="pps_del_file" id="pps_del_file" accept=".xlsx, .xls, .XLSX, XLS" {{$readonly}}>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-offset-4 col-md-4">
                                            <button type="submit" class="btn btn-primary btn-block" id="btn_upload_pps_del">Upload File</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                                
                        </div> 
                    </div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="portlet box blue" >
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-navicon"></i>  Production Balance & Ship Plan
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row text-center">
                            <div class="col-md-12">
                                <h4>Upload ZYPF5210 File</h4>
                                <form method="POST" method="POST" action="{{ url('/flex-prod-balance-upload') }}" accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal" id="frmProdBalance">
                                    {{ csrf_field() }}

                                    <div class="form-group row">
                                        <label class="control-label col-md-3">ZYPF5210 File</label>
                                        <div class="col-md-7">
                                            <input type="file" class="filestyle" data-buttonName="btn-primary" name="zymr_file" id="zymr_file" accept="text/plain" {{$readonly}}>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-offset-2 col-md-4">
                                            <button type="submit" class="btn btn-primary btn-block" id="btn_upload_zymr">Upload File</button>
                                        </div>

                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-danger btn-block" id="btn_show_error">Show Errors <span class="badge" id="error_count">0</span></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                                
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('includes.modals')

    <div id="file_checking_modal" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm gray-gallery">
            <div class="modal-content ">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5 id="file_checking_msg">Checking of PPS Delivery file..</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <img src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/images/ajax-loader.gif') }}" class="img-responsive">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="errors_modal" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-xl gray-gallery">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Production Balance & Ship Plan Errors</h4>
                </div>
    
                <div class="modal-body">
    
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-hover table-bordered table-striped table-condensed nowrap" id="tbl_errors">
                                <thead>
                                    <tr>
                                        <td>Haccyuu_No</td>
                                        <td>Haccyuu_Hoban</td>
                                        <td>Hinmoku_Code</td>
                                        <td>Hinmoku_tekisuto</td>
                                        <td>Hokanbasyo</td>
                                        <td>MRPKanrisya</td>
                                        <td>Haccyuu_Bi</td>
                                        <td>Siiresaki_Code</td>
                                        <td>Shiiresaki_Tekisuto</td>
                                        <td>Haccyuu_Qty</td>
                                        <td>Haccyuu_Zan_Qty</td>
                                        <td>Toukei_kannrenn_nounyuu_Bi</td>
                                        <td>Kaitou_Nouki</td>
                                        <td>Kaitou_Jikoku</td>
                                        <td>Kaitou_Qty</td>
                                        <td>Tokuisaki_Code</td>
                                        <td>Tokuisaki_Mei</td>
                                        <td>Tokuisaki_Nouki</td>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
    
                <div class="modal-footer">
                    <button type="button" id="btn_download_errors" class="btn btn-success">Download</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        var token = "{{ Session::token() }}";
        var inventory_data_url = "{{ url('/flex-process-inventory-data') }}";
        var downloadInventoryURL = "{{ url('/flex-download-inventory-data') }}";

        var processPPSDeliveryFileURL = "{{ url('/flex-parts-incoming-process') }}";
        var downloadPPSdeliveryURL = "{{ url('/flex-parts-incoming-download') }}";

        var processZYMRFileURL = "{{ url('/flex-prod-balance-process') }}";
        var downloadZYMRURL = "{{ url('/flex-prod-balance-download') }}";
    </script>
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}" type="text/javascript"></script>
    <script src="{{ asset(config('constants.PUBLIC_PATH').'assets/global/scripts/ypics/flex-sched.js') }}" type="text/javascript"></script>
@endpush