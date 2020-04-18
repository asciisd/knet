<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invoice</title>

    <style>
        body {
            background: #fff none;
            font-size: 12px;
        }

        h2 {
            font-size: 28px;
            color: #ccc;
        }

        .container {
            padding-top: 30px;
        }

        .invoice-head td {
            padding: 0 8px;
        }

        .table th {
            vertical-align: bottom;
            font-weight: bold;
            padding: 8px;
            line-height: 20px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            margin-right: auto;
            margin-left: auto;
        }

        .table tr.row td {
            border-bottom: 1px solid #ddd;
        }

        .table td {
            padding: 8px;
            line-height: 20px;
            text-align: left;
            vertical-align: top;
        }
    </style>
</head>
<body>
<div class="container">
    <table style="align-items: center" width="550">
        <tr>
            <!-- Organization Name / Image -->
            <td style="font-size: 28px">
                <strong>{{ $header ?? $vendor }}</strong>
                <br>
            </td>

            <td style="font-size: 28px; color: #ccc; text-align: right">
                Receipt
                <br>
            </td>
        </tr>
        <tr>
            <!-- Organization Details -->
            <td>
                @if (isset($street))
                    {{ $street }}<br>
                @endif

                @if (isset($location))
                    {{ $location }}<br>
                @endif

                @if (isset($phone))
                    {{ $phone }}<br>
                @endif

                @if (isset($email))
                    {{ $email }}<br>
                @endif

                @if (isset($url))
                    <a href="{{ $url }}">{{ $url }}</a>
                @endif
            </td>

            <td>
                <!-- Invoice Info -->
                <table align="right" width="200">
                    <tr>
                        <td style="text-align: left">Receipt number</td>
                        <td style="text-align: right"><strong>{{ $invoice->receiptNo() }}</strong></td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Invoice number</td>
                        <td style="text-align: right"><strong>{{ $invoice->referenceNo() }}</strong></td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Date paid</td>
                        <td style="text-align: right"><strong>{{ $invoice->date()->toFormattedDateString() }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Payment method</td>
                        <td style="text-align: right"><strong>{{ $invoice->paymentMethod() }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <br><strong>Paid by</strong><br>
                {{ $owner->name }}<br>
                {{ $owner->email }}<br>
            </td>
        </tr>
        <tr style="font-size: 24px;" height="66">
            <td>
                <br>{{ $invoice->amount() }} paid on {{ $invoice->date()->toFormattedDateString() }}

                <br><br>
            </td>
        </tr>
        <tr>

            <!-- Invoice Table -->
            <table class="table" border="0" width="550">
                <tr>
                    <th align="left">Description</th>
                    <th align="right">Date</th>
                    <th align="right">Amount</th>
                </tr>

                <!-- Display The Invoice Items -->

                <tr class="row">
                    <td colspan="2">item description</td>


                    <td>item total</td>
                </tr>

                <!-- Display The Final Total -->
                <tr>
                    <td colspan="2" style="text-align: right;">
                        <strong>Total</strong>
                    </td>
                    <td>
                        <strong>{{ $invoice->amount() }}</strong>
                    </td>
                </tr>
            </table>

        </tr>
    </table>
</div>
</body>
</html>
