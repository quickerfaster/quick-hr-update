<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip['number'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-info { flex: 2; }
        .payslip-info { flex: 1; text-align: right; }
        .section { margin: 15px 0; }
        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .subtotal-row td {
            border-top: 2px solid #333;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .net-pay {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #333;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            @if(file_exists($company['logo_path']))
                <img src="{{ $company['logo_path'] }}" style="height: 40px; margin-bottom: 10px;">
            @endif
            <h2>{{ $company['name'] }}</h2>
            @if($company['address'])
                <div>{{ $company['address'] }}</div>
            @endif
            @if($company['phone'])
                <div>Phone: {{ $company['phone'] }}</div>
            @endif
            @if($company['email'])
                <div>Email: {{ $company['email'] }}</div>
            @endif
        </div>
        <div class="payslip-info">
            <h3>PAYSLIP</h3>
            <div><strong>Number:</strong> {{ $payslip['number'] }}</div>
            <div><strong>Date:</strong> {{ $payslip['paid_at']?->format('M j, Y') ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Employee & Pay Period -->
    <div class="section">
        <div><strong>Employee:</strong> {{ $employee['name'] }} ({{ $employee['id'] }})</div>
        <div><strong>Address:</strong> {{ $employee['address'] }}</div>
        @if($payroll_run['title'])
            <div><strong>Payroll Run:</strong> #{{ $payroll_run['id'] }} — {{ $payroll_run['title'] }}</div>
        @endif
        <div><strong>Pay Period:</strong> {{ $payroll_run['period_start'] }} to {{ $payroll_run['period_end'] }}</div>
        @if($payroll_run['payment_date'])
            <div><strong>Payment Date:</strong> {{ $payroll_run['payment_date'] }}</div>
        @endif
    </div>

    <!-- Earnings -->
    <div class="section">
        <div class="section-title">EARNINGS</div>
        @if($payslip['earnings']->isNotEmpty())
            <table>
                @foreach($payslip['earnings'] as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td>Gross Pay</td>
                    <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($payslip['gross_pay'], 2) }}</td>
                </tr>
            </table>
        @else
            <table>
                <tr class="subtotal-row">
                    <td>Gross Pay</td>
                    <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($payslip['gross_pay'], 2) }}</td>
                </tr>
            </table>
        @endif
    </div>

    <!-- Deductions -->
    <div class="section">
        <div class="section-title">DEDUCTIONS</div>
        @if($payslip['deductions']->isNotEmpty())
            <table>
                @foreach($payslip['deductions'] as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td>Total Deductions</td>
                    <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($payslip['total_deductions'], 2) }}</td>
                </tr>
            </table>
        @else
            <table>
                <tr>
                    <td>No deductions this period</td>
                    <td class="text-right">-</td>
                </tr>
                <tr class="subtotal-row">
                    <td>Total Deductions</td>
                    <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($payslip['total_deductions'], 2) }}</td>
                </tr>
            </table>
        @endif
    </div>

    <!-- Employer Contributions (if any) -->
    @if($payslip['employer_contributions']->isNotEmpty())
        <div class="section">
            <div class="section-title">EMPLOYER CONTRIBUTIONS</div>
            <table>
                @foreach($payslip['employer_contributions'] as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td class="text-right">{{ $payslip['currency_symbol'] }}{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <!-- Net Pay -->
    <div class="net-pay">
        NET PAY: {{ $payslip['currency_symbol'] }}{{ number_format($payslip['net_pay'], 2) }}
    </div>

    <!-- Signatories -->
    <div class="section">
        <div class="section-title">AUTHORIZED SIGNATURES</div>
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div>Prepared By: {{ $payroll_run['prepared_by'] ?: '______________________' }}</div>
                    <div style="margin-top: 40px; border-top: 1px solid #333;">Signature</div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div>Approved By: {{ $payroll_run['approved_by'] ?: '______________________' }}</div>
                    <div style="margin-top: 40px; border-top: 1px solid #333;">Signature</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>This is a computer-generated payslip. No signature required.</div>
        <div>Confidential: For employee eyes only.</div>
    </div>
</body>
</html>
