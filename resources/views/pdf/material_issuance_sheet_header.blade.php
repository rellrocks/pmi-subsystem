@extends('pdf.material_issuace_sheet_layout')
@section('content')
    {{-- Warehouse Copy --}}
    <div class="col-xs-6" style="border-right: solid 1px">
        <div class="row">
            <br>
        	<div class="col-xs-12">
        		<table class="table table-borderless table-condensed">
                    <thead>
                        <tr>
                            <th colspan="3">
                                <h4><ins>MATERIAL ISSUANCE SHEET</ins></h4>
                            </th>
                            <td style="font-size: 9px">Warehouse Copy<br>
                                {{-- <span class=".pagenum">Page 1</span> --}}
                            </td>
                        </tr>
                        <tr>
                            <th>PO:</th>
                            <td>{{ $pono }}</td>
                            <th></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>DEVICE NAME:</th>
                            <td colspan="2">{{ $devicename }}</td>
                            <th></th>
                        </tr>
                        <tr>
                            <th>ORDER QTY:</th>
                            <td>{{ $poqty }}</td>
                            <th>Transfer to:</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>KIT QTY:</th>
                            <td>{{ $kitqty }}</td>
                            <th>A. Kanban House</th>
                            <th>__________</th>
                        </tr>
                        <tr>
                            <th>KIT NUMBER:</th>
                            <td>{{ $kitno }}</td>
                            <th>B. Warehouse</th>
                            <th>__________</th>
                        </tr>
                        <tr>
                            <th>PREPARED DT:</th>
                            <td>{{ $createdat }}</td>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Production Copy --}}
    <div class="col-xs-6">
        <div class="row">
            <br>
            <div class="col-xs-12">
                <table class="table table-borderless table-condensed">
                    <thead>
                        <tr>
                            <th colspan="3">
                                <h4><ins>MATERIAL ISSUANCE SHEET</ins></h4>
                            </th>
                            <td style="font-size: 9px">Production Copy<br>
                                {{-- <span class=".pagenum">Page 1</span> --}}
                            </td>
                        </tr>
                        <tr>
                            <th>PO:</th>
                            <td>{{ $pono }}</td>
                            <th></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>DEVICE NAME:</th>
                            <td colspan="2">{{ $devicename }}</td>
                            <th></th>
                        </tr>
                        <tr>
                            <th>ORDER QTY:</th>
                            <td>{{ $poqty }}</td>
                            <th>Transfer to:</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>KIT QTY:</th>
                            <td>{{ $kitqty }}</td>
                            <th>A. Kanban House</th>
                            <th>__________</th>
                        </tr>
                        <tr>
                            <th>KIT NUMBER:</th>
                            <td>{{ $kitno }}</td>
                            <th>B. Warehouse</th>
                            <th>__________</th>
                        </tr>
                        <tr>
                            <th>PREPARED DT:</th>
                            <td>{{ $createdat }}</td>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection