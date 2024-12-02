@extends('pdf.material_issuace_sheet_layout')
@section('content')
    {{-- Warehouse Copy --}}
    <div class="col-xs-6" style="border-right: solid 1px">
        <div class="row">
        	<div class="col-xs-12">
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Part Name / Code</th>
                            <th>USG</th>
                            <th>RQD</th>
                            <th>QTY</th>
                            <th>LOT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($mk_details_data as $key => $value)
                        {
                        ?>
                            <tr>
                                <td>{{ $value->item }}</td>
                                <td>{{ $value->item_desc }}</td>
                                <td>{{ $value->usage }}</td>
                                <td>{{ $value->rqd_qty }}</td>
                                <td>{{ $value->issued_qty }}</td>
                                <td>{{ $value->lot_no }}</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Production Copy --}}
    <div class="col-xs-6">
        <div class="row">
        	<div class="col-xs-12">
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Part Name / Code</th>
                            <th>USG</th>
                            <th>RQD</th>
                            <th>QTY</th>
                            <th>LOT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($mk_details_data as $key => $value)
                        {
                        ?>
                            <tr>
                                <td>{{ $value->item }}</td>
                                <td>{{ $value->item_desc }}</td>
                                <td>{{ $value->usage }}</td>
                                <td>{{ $value->rqd_qty }}</td>
                                <td>{{ $value->issued_qty }}</td>
                                <td>{{ $value->lot_no }}</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection