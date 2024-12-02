@extends('layouts.master')    
@section('title')
     Yield Performance | Pricon Microelectronics, Inc.
@endsection

@section('content')

     <?php $state = ""; $readonly = ""; ?>
     @foreach ($userProgramAccess as $access)
          @if ($access->program_code == Config::get('constants.MODULE_CODE_REP'))  <!-- Please update "2001" depending on the corresponding program_code -->
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
                                   <i class="fa fa-area-chart"></i>  Yield Performance Report
                              </div>
                         </div>
                         <div class="portlet-body">

                              <div class="row">

                                   <div class="col-sm-12">

                                        <table id="tbl_reports" class="table table-striped table-bordered table-hover"style="font-size:10px">
                                             <thead >
                                                  <tr>
                                                       <td>PO Number</td>
                                                       <td>PO Qty</td>
                                                       <td>Device Name</td>
                                                       <td>Series</td>
                                                       <td>Family</td>
                                                       <td>Total Input</td>
                                                       <td>Total Output</td>
                                                       <td>Total Reject</td>
                                                       <td>Total Yield</td>
                                                  </tr>
                                             </thead>
                                             <tbody></tbody> 
                                        </table>


                                        <br>
                                        <div class="form-group pull-right">
                                             <label class="control-label col-sm-2">DPPM</label>
                                             <div class="col-sm-10">
                                                  <input type="text" class="form-control input-sm" id="dppm" name="dppm">
                                             </div> 
                                        </div>
                                        <div class="col-sm-2">
                                             <input type="text" class="form-control input-sm date-picker" id="datefroms" name="datefroms" > Date From
                                        </div>
                                         <div class="col-sm-2">
                                             <input type="text" class="form-control input-sm date-picker" id="datetos" name="datetos" > Date To
                                        </div>
                                   </div>
                              </div>
                              <br>
                              <br>
                              <div class="row col-sm-offset-1 col-sm-10">
                                  <div id="chartContainer" style="height: 300px;"></div>
                              </div>
                              <div class="row">
                                   <div class="col-sm-12 text-center" style="margin-top:40px;">
                                        <a href="{{ url('/export-to-excel') }}" type="button" style="font-size:12px;" class="btn green-jungle input-sm" id="btnXexcel">
                                        <i class="fa fa-file-excel-o"></i> Export Summary to Excel
                                        </a>
                                        <a href="{{ url('/export-to-pdf') }}" type="button" style="font-size:12px;" class="btn yellow-gold input-sm" id="btnXpdf">
                                             <i class="fa fa-file-pdf-o"></i> Export Summary to Pdf
                                        </a>
                                        <button  type="button" style="font-size:12px;" class="btn blue-soft input-sm" id="btnxport">
                                             <i class="fa fa-share"></i>Export Files
                                        </button>
                                        <button  type="button" style="font-size:12px;" class="btn blue-soft input-sm" onclick="javascript:loadchart();" id="btnloadchart">
                                             <i class="fa fa-share"></i>Load Chart
                                        </button>
                                       
                                   </div>
                              </div>              
                         </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->
               </div>
          </div>
          <!-- END PAGE CONTENT-->
     </div>
     
     @include('includes.yieldEmptyField_modal')
     @include('includes.yieldReport_modal')
     @include('includes.yieldReport_Exports_modal')
     @include('includes.yieldReport_Summary_modal')
     @include('includes.yieldReport_DefectSummary_modal')
     @include('includes.yieldReport_YieldPerformanceSummary_modal')
     @include('includes.yieldReport_YieldSummary_modal')
     @include('includes.modals')
 
@endsection

@push('script')
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/plugins/canvasjs.min.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/common.js') }}"></script>


<script type="text/javascript">
     var token = "{{ Session::token() }}";
     var ReportYieldPerformance = "{{ url('/ReportYieldPerformance') }}";
     var loadchartURL = "{{ url('/loadchart') }}";
     var updateyieldsummary = "{{ url('/update-yieldsummary') }}";
     var reportRecordsURL = "{{ url('/report-records') }}";
     var summaryREptURL = "{{ url('/summaryREpt') }}";
     var checkDataExistsURL = "{{ url('/checkDataExists') }}";
     var CheckDefectSummaryURL = "{{ url('/checKdeffectsummary') }}";
     var CheckYieldPerformanceSummaryURL = "{{ url('/checkyieldperformancesummary') }}";
     var CheckYieldSummaryPerFamilyURL =  "{{ url('/checkyieldperformancesummaryperfamily') }}";
     var defectsummaryRptURL = "{{ url('/defectsummaryRpt') }}";
     var yieldsumfamRptURL = "{{ url('/yieldsumfamRpt') }}";
     var yieldsumRptURL = "{{ url('/yieldsumRpt') }}";

     var searchPOdetailsURL = "{{ url('/searchPOdetails') }}";

     var getYieldTargetURL = "{{ url('/getYieldTargetForReport') }}";
</script>
<script type="text/javascript"src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/yielding_performance_report.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/YieldReportsJS/YPDefectSummaryReport.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/YieldReportsJS/YPSummaryFamilyReport.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/YieldReportsJS/YPSummaryReport.js') }}"></script>
<script type="text/javascript" src="{{ asset(Config::get('constants.PUBLIC_PATH').'assets/global/scripts/YieldReportsJS/YPYieldSummaryReport.js') }}"></script>


@endpush