<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Report</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #484b51;
            background: #fff;
        }
        
        .page-header {
            border-bottom: 2px solid #e2e2e2;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4076d4;
        }

        /* 让公司信息和发票信息严格对齐 */
        .info-table {
            width: 100%;
        }

        .info-table td {
            vertical-align: top;
            padding: 5px;
        }

        .info-right {
            text-align: right;
        }

        /* 表格样式 */
        .table-container {
            margin-top: 30px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .data-table th, .data-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .data-table th {
            background-color: #f3f8fa;
            font-weight: bold;
        }

        .total-row {
            background: #e9f6ff;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <!-- Invoice 标题 -->
    <div class="page-header">
        <h1 class="invoice-title">INVOICE</h1>
    </div>

    <!-- 公司 & 发票信息 -->
    <table class="info-table">
        <tr>
            <td>
                <p><strong>COMPANY A</strong></p>
                <p><strong>CLIENT / DESTINATION</strong></p>
                <p>TEL: 01358838822 &nbsp;&nbsp;&nbsp; FAX: 082-2344332</p>
            </td>
            <td class="info-right">
                <p><strong>No. : I-2411-13</strong></p>
                <p>Your Ref. : Y13827442</p>
                <p>Our D/O No. : 123</p>
                <p>Terms : C.O.D.</p>
                <p>Date : 30/11/2024</p>
                <p>Page : 1 of 6</p>
            </td>
        </tr>
    </table>

    <hr>

    <!-- 发票表格 -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Consignment Note</th>
                    <th>Delivery Date</th>
                    <th>Qty</th>
                    <th>UOM</th>
                    <th>U/ Price RM</th>
                    <th>Disc.</th>
                    <th>Total RM</th>
                </tr>
            </thead>
            <tbody>
                @php $totalAmount = 0; @endphp
                @foreach($manifests as $index => $manifest)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $manifest->from }} - {{ $manifest->to }}</td>
                    <td>{{ $manifest->cn_no }}</td>
                    <td>{{ $manifest->date }}</td>
                    <td>{{ $manifest->pcs }}</td>
                    <td>KG</td>
                    <td>{{ number_format($manifest->price_per_kg, 2) }}</td>
                    <td>0.00</td>
                    <td>{{ number_format($manifest->total_price, 2) }}</td>
                </tr>
                @php $totalAmount += $manifest->total_price; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="8" class="text-end">Total Price:</td>
                    <td>{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Thank you for your business! If you have any questions about this invoice, please contact us.
    </div>

</body>
</html>
