@extends('pdf.material_issuace_sheet_layout')
@section('content')
    {{-- Warehouse Copy --}}
    <div class="col-xs-6" style="border-right: solid 1px">
        <div class="row">
            <div class="col-xs-12">
                <table class="tabl table-borderless table-condensed">
                    <tr>
                        <td>Prepared By:</td>
                        <td>{{$preparedby}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Issued By:</td>
                        <td>__________________</td>
                        <td></td>
                        <td>Date: __________________</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Received By:</td>
                        <td>__________________</td>
                        <td></td>
                        <td>Date: __________________</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Transfer Slip:</td>
                        <td>{{$issuanceno}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <br>

    {{-- Production Copy --}}
    <div class="col-xs-6">
        <div class="row">
            <div class="col-xs-12">
                <table class="tabl table-borderless table-condensed">
                    <tr>
                        <td>Prepared By:</td>
                        <td>{{$preparedby}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Issued By:</td>
                        <td>__________________</td>
                        <td></td>
                        <td>Date: __________________</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Received By:</td>
                        <td>__________________</td>
                        <td></td>
                        <td>Date: __________________</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Transfer Slip:</td>
                        <td>{{$issuanceno}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection