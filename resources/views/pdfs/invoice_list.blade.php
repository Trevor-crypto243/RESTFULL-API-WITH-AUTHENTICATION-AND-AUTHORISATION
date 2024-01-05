<style>
    thead {
        display: table-header-group;
    }

    .table {
        border-collapse: collapse !important;
    }

    .table td,
    .table th {
        background-color: #fff !important;
    }

    .table-bordered th,
    .table-bordered td {
    }

    th {
        text-align: left;
    }

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }

    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tfoot > tr > th,
    .table > thead > tr > td,
    .table > tbody > tr > td,
    .table > tfoot > tr > td {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
    }

    .table > thead > tr > th {
        vertical-align: bottom;
    }

    .table > caption + thead > tr:first-child > th,
    .table > colgroup + thead > tr:first-child > th,
    .table > thead:first-child > tr:first-child > th,
    .table > caption + thead > tr:first-child > td,
    .table > colgroup + thead > tr:first-child > td,
    .table > thead:first-child > tr:first-child > td {
        border-top: 0;
    }

    .table > tbody + tbody {
    }

    .table .table {
        background-color: #fff;
    }

    .table-condensed > thead > tr > th,
    .table-condensed > tbody > tr > th,
    .table-condensed > tfoot > tr > th,
    .table-condensed > thead > tr > td,
    .table-condensed > tbody > tr > td,
    .table-condensed > tfoot > tr > td {
        padding: 5px;
    }

    .table-bordered {
        border: 1px solid #ddd;
    }

    .table-bordered > thead > tr > th,
    .table-bordered > tbody > tr > th,
    .table-bordered > tfoot > tr > th,
    .table-bordered > thead > tr > td,
    .table-bordered > tbody > tr > td,
    .table-bordered > tfoot > tr > td {
        border: 1px solid #ddd;
    }

    .table-bordered > thead > tr > th,
    .table-bordered > thead > tr > td {
        border-bottom-width: 2px;
    }

    .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-hover > tbody > tr:hover {
        background-color: #f5f5f5;
    }

    table col[class*="col-"] {
        position: static;
        display: table-column;
        float: none;
    }

    table td[class*="col-"],
    table th[class*="col-"] {
        position: static;
        display: table-cell;
        float: none;
    }

    .table > thead > tr > td.active,
    .table > tbody > tr > td.active,
    .table > tfoot > tr > td.active,
    .table > thead > tr > th.active,
    .table > tbody > tr > th.active,
    .table > tfoot > tr > th.active,
    .table > thead > tr.active > td,
    .table > tbody > tr.active > td,
    .table > tfoot > tr.active > td,
    .table > thead > tr.active > th,
    .table > tbody > tr.active > th,
    .table > tfoot > tr.active > th {
        background-color: #f5f5f5;
    }

    .table-hover > tbody > tr > td.active:hover,
    .table-hover > tbody > tr > th.active:hover,
    .table-hover > tbody > tr.active:hover > td,
    .table-hover > tbody > tr:hover > .active,
    .table-hover > tbody > tr.active:hover > th {
        background-color: #e8e8e8;
    }

    .table > thead > tr > td.success,
    .table > tbody > tr > td.success,
    .table > tfoot > tr > td.success,
    .table > thead > tr > th.success,
    .table > tbody > tr > th.success,
    .table > tfoot > tr > th.success,
    .table > thead > tr.success > td,
    .table > tbody > tr.success > td,
    .table > tfoot > tr.success > td,
    .table > thead > tr.success > th,
    .table > tbody > tr.success > th,
    .table > tfoot > tr.success > th {
        background-color: #dff0d8;
    }

    .table-hover > tbody > tr > td.success:hover,
    .table-hover > tbody > tr > th.success:hover,
    .table-hover > tbody > tr.success:hover > td,
    .table-hover > tbody > tr:hover > .success,
    .table-hover > tbody > tr.success:hover > th {
        background-color: #d0e9c6;
    }

    .table > thead > tr > td.info,
    .table > tbody > tr > td.info,
    .table > tfoot > tr > td.info,
    .table > thead > tr > th.info,
    .table > tbody > tr > th.info,
    .table > tfoot > tr > th.info,
    .table > thead > tr.info > td,
    .table > tbody > tr.info > td,
    .table > tfoot > tr.info > td,
    .table > thead > tr.info > th,
    .table > tbody > tr.info > th,
    .table > tfoot > tr.info > th {
        background-color: #d9edf7;
    }

    .table-hover > tbody > tr > td.info:hover,
    .table-hover > tbody > tr > th.info:hover,
    .table-hover > tbody > tr.info:hover > td,
    .table-hover > tbody > tr:hover > .info,
    .table-hover > tbody > tr.info:hover > th {
        background-color: #c4e3f3;
    }

    .table > thead > tr > td.warning,
    .table > tbody > tr > td.warning,
    .table > tfoot > tr > td.warning,
    .table > thead > tr > th.warning,
    .table > tbody > tr > th.warning,
    .table > tfoot > tr > th.warning,
    .table > thead > tr.warning > td,
    .table > tbody > tr.warning > td,
    .table > tfoot > tr.warning > td,
    .table > thead > tr.warning > th,
    .table > tbody > tr.warning > th,
    .table > tfoot > tr.warning > th {
        background-color: #fcf8e3;
    }

    .table-hover > tbody > tr > td.warning:hover,
    .table-hover > tbody > tr > th.warning:hover,
    .table-hover > tbody > tr.warning:hover > td,
    .table-hover > tbody > tr:hover > .warning,
    .table-hover > tbody > tr.warning:hover > th {
        background-color: #faf2cc;
    }

    .table > thead > tr > td.danger,
    .table > tbody > tr > td.danger,
    .table > tfoot > tr > td.danger,
    .table > thead > tr > th.danger,
    .table > tbody > tr > th.danger,
    .table > tfoot > tr > th.danger,
    .table > thead > tr.danger > td,
    .table > tbody > tr.danger > td,
    .table > tfoot > tr.danger > td,
    .table > thead > tr.danger > th,
    .table > tbody > tr.danger > th,
    .table > tfoot > tr.danger > th {
        background-color: #f2dede;
    }

    .table-hover > tbody > tr > td.danger:hover,
    .table-hover > tbody > tr > th.danger:hover,
    .table-hover > tbody > tr.danger:hover > td,
    .table-hover > tbody > tr:hover > .danger,
    .table-hover > tbody > tr.danger:hover > th {
        background-color: #ebcccc;
    }

    .table-responsive {
        min-height: .01%;
        overflow-x: auto;
    }

    .row {
        margin-right: -15px;
        margin-left: -15px;
        clear: both;
    }

    .col-md-6 {
        width: 50%;
        position: relative;
        min-height: 1px;
        padding-right: 15px;
        padding-left: 15px;
    }

    .well {
        min-height: 20px;
        padding: 19px;
        margin-bottom: 20px;
        background-color: #f5f5f5;
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
    }

    tbody:before, tbody:after {
        display: none;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .text-justify {
        text-align: justify;
    }

    .pull-right {
        float: right !important;
    }
</style>


<div>
    {{--<h3 class="text-center">--}}
        {{--<b>LastMile</b>--}}
    {{--</h3>--}}

    <div class="text-left mx-auto"  style="width: 980px;margin-left: auto;margin-right: auto;margin-top: 50px">
       <img src="https://inua.Quicksavacredit.africa/assets/img/logo.png" class="img-responsive mx-auto" style=" max-height: 140px;margin-top: -15px;margin-bottom: -10px; margin: auto"/>
    </div>


    <table style="width: 100%;margin-left: auto;margin-right: auto;margin-top: 30px; border-bottom: solid thin rgba(2, 180, 209, 0.44);"
           class="table table-condensed ">
        <tbody>
            <tr>
                <td>
                    <p class="text-left mx-auto"><b>Invoice List</b></p>
                    <p class="text-left mx-auto"><b>Total Invoices: {{$totalInvoices}} </b></p>
                    <p class="text-left mx-auto"><b>Total Invoice Amount: Ksh. {{number_format($totalInvoicesValue,2)}} </b></p>
                </td>
                <td>
                    <p>
                        Company: <b>{{$company->business_name}}</b>
                    </p>
                </td>
            </tr>

            <tr>
                <td>
                    <p class="text-left mx-auto" >
                        List as of: <b>{{$today}} </b>
                    </p>
                </td>
                <td>
                    <p>
                        Applied By: <b>{{optional($invoiceDiscount->creator)->name}}</b>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>




{{--    <table style="width: 980px;margin-left: auto;margin-right: auto;margin-top: 30px"--}}
{{--           class="table table-condensed ">--}}

{{--        <thead>--}}
{{--            <tr>--}}
{{--                <th>--}}
{{--                    From Date--}}
{{--                </th>--}}
{{--                <th>--}}
{{--                    To Date--}}
{{--                </th>--}}
{{--                <th>--}}
{{--                    Opening Balance--}}
{{--                </th>--}}
{{--                <th>--}}
{{--                    Closing Balance--}}
{{--                </th>--}}
{{--            </tr>--}}
{{--        </thead>--}}

{{--        <tbody>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <p>{{$from}}</p>--}}
{{--                </td>--}}

{{--                <td>--}}
{{--                    <p>{{$to}}</p>--}}
{{--                </td>--}}

{{--                <td>--}}
{{--                    <p>UGX {{number_format($openingBalance,2)}}</p>--}}
{{--                </td>--}}

{{--                <td>--}}
{{--                    <p>UGX {{number_format($balance,2)}}</p>--}}
{{--                </td>--}}
{{--            </tr>--}}

{{--        </tbody>--}}
{{--    </table>--}}



    <div style="width: 100%;margin-left: auto;margin-right: auto;padding: 20px;text-transform: capitalize;">
{{--        <h3 class="text-center"><b>Transactions</b></h3>--}}
        @if(count($invoices)>0)
            <table class="table table-condensed table-bordered table-striped">
                <tbody>
                <tr>
                    <th>Amount</th>

                    <th>Invoice #</th>
                    <th>Branch</th>
                    <th>GRN No.</th>
                    <th>LPO No.</th>
                    <th>Delivery Note No.</th>

                    <th>Invoice Date</th>

                    <th>Status</th>

                    <th>Expected payment Date</th>
                </tr>
                <tbody>
                @foreach($invoices as $key)
                    <tr>
                        <td>{{'KSH '. number_format($key->invoice_amount,2) }}</td>

                        <td>{{$key->invoice_number}}</td>
                        <td>{{$key->invoice_branch}}</td>
                        <td>{{$key->grn_no}}</td>
                        <td>{{$key->lpo_no}}</td>
                        <td>{{$key->delivery_note_no}}</td>

                        <td>{{\Carbon\Carbon::parse($key->invoice_date)->isoFormat('MMM Do YYYY')}}</td>

                        <td>{{$key->approval_status}}</td>

                        <td></td>

{{--                        <td>{{\Carbon\Carbon::parse($key->created_at)->isoFormat('MMM Do YYYY')}}</td>--}}

                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <h5>No invoices found</h5>
        @endif
    </div>
</div>

{{--<script>--}}
    {{--window.onload = function () {--}}
        {{--window.print();--}}
    {{--}--}}
{{--</script>--}}
