<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 5px 0;
            line-height: 1.5;
        }
        .info-box strong {
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead {
            background-color: #333;
            color: white;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            font-weight: bold;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .summary table {
            margin-bottom: 0;
        }
        .summary table td {
            padding: 5px 10px;
        }
        .summary .total-row {
            background-color: #333;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 50px;
        }
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 10px;
        }
        .signature-box p {
            margin: 5px 0;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        .notes {
            margin-top: 20px;
            padding: 10px;
            background-color: #fffacd;
            border: 1px solid #f0e68c;
        }
        .notes h4 {
            margin: 0 0 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PURCHASE ORDER</h1>
        <h2>{{ config('app.name', 'Your Company Name') }}</h2>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-column">
                <div class="info-box">
                    <h3>Vendor Information</h3>
                    <p><strong>Vendor Name:</strong> {{ $po->vendor->name }}</p>
                    <p><strong>Contact Person:</strong> {{ $po->vendor->contact_person }}</p>
                    <p><strong>Phone:</strong> {{ $po->vendor->phone }}</p>
                    <p><strong>Email:</strong> {{ $po->vendor->email }}</p>
                    <p><strong>Address:</strong> {{ $po->vendor->address }}</p>
                </div>
            </div>
            <div class="info-column" style="padding-left: 10px;">
                <div class="info-box">
                    <h3>Order Information</h3>
                    <p><strong>PO Number:</strong> {{ $po->po_number }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</p>
                    <p><strong>Expected Date:</strong> {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y') }}</p>
                    <p><strong>Status:</strong> {{ $po->status }}</p>
                    @if($po->location)
                    <p><strong>Delivery To:</strong> {{ $po->location->name }}</p>
                    <p><strong>Location:</strong> {{ $po->location->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 40%;">Product Name</th>
                <th style="width: 15%;" class="text-center">Quantity</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 25%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item->product->name }}
                    @if($item->notes)
                    <br><small style="color: #666;">Note: {{ $item->notes }}</small>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($po->tax_amount > 0)
            <tr>
                <td><strong>Tax:</strong></td>
                <td class="text-right">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($po->discount_amount > 0)
            <tr>
                <td><strong>Discount:</strong></td>
                <td class="text-right">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($po->shipping_cost > 0)
            <tr>
                <td><strong>Shipping:</strong></td>
                <td class="text-right">Rp {{ number_format($po->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($po->total, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    @if($po->notes)
    <div class="notes">
        <h4>Notes:</h4>
        <p>{{ $po->notes }}</p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Prepared By</strong></p>
            <div class="signature-line">
                <p>{{ $po->createdBy->name ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="signature-box">
            <p><strong>Approved By</strong></p>
            <div class="signature-line">
                <p>{{ $po->approvedBy->name ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="signature-box">
            <p><strong>Vendor Acknowledgment</strong></p>
            <div class="signature-line">
                <p>_____________________</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p style="text-align: center; font-size: 10px; color: #999;">
            This is a computer-generated document. No signature is required.<br>
            Generated on {{ \Carbon\Carbon::now()->format('d M Y H:i') }}
        </p>
    </div>
</body>
</html>
