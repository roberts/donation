<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Receipt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 20px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #1a56db;
            margin-bottom: 5px;
        }
        
        .tagline {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .receipt-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
        }
        
        .receipt-number {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1a56db;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            color: #666;
            font-size: 13px;
        }
        
        .info-value {
            font-weight: 500;
            text-align: right;
        }
        
        .amount-section {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        
        .amount-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .amount-value {
            font-size: 36px;
            font-weight: bold;
            color: #1a56db;
        }
        
        .school-info {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .school-label {
            font-size: 12px;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #92400e;
        }
        
        .tax-notice {
            background-color: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 8px;
            padding: 15px;
            margin: 30px 0;
        }
        
        .tax-notice-title {
            font-weight: bold;
            color: #166534;
            margin-bottom: 5px;
        }
        
        .tax-notice-text {
            font-size: 12px;
            color: #166534;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .footer p {
            margin-bottom: 5px;
        }
        
        .ein {
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table td {
            padding: 5px 0;
            vertical-align: top;
        }
        
        table td:last-child {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">IBE Foundation</div>
        <div class="tagline">Invest Before Educate</div>
    </div>
    
    <div class="receipt-title">Official Donation Receipt</div>
    <div class="receipt-number">Receipt #{{ $donation->id }} | {{ $donation->created_at->format('F j, Y') }}</div>
    
    <div class="section">
        <div class="section-title">Donor Information</div>
        <table>
            <tr>
                <td class="info-label">Name</td>
                <td class="info-value">
                    @if($donation->donor->title){{ $donation->donor->title }} @endif{{ $donation->donor->first_name }} {{ $donation->donor->last_name }}
                </td>
            </tr>
            @if($donation->donor->spouse_title && $donation->donor->spouse_first_name)
            <tr>
                <td class="info-label">Spouse</td>
                <td class="info-value">
                    {{ $donation->donor->spouse_title }} {{ $donation->donor->spouse_first_name }} {{ $donation->donor->spouse_last_name }}
                </td>
            </tr>
            @endif
            <tr>
                <td class="info-label">Address</td>
                <td class="info-value">{{ $donation->donor->email }}</td>
            </tr>
            @if($donation->donor->phone)
            <tr>
                <td class="info-label">Phone</td>
                <td class="info-value">{{ $donation->donor->phone }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    @php
        $billingAddress = $donation->donor->addresses->where('type', 'billing')->first();
    @endphp
    @if($billingAddress)
    <div class="section">
        <div class="section-title">Billing Address</div>
        <p>
            {{ $billingAddress->street }}<br>
            {{ $billingAddress->city }}, {{ $billingAddress->state }} {{ $billingAddress->postal_code }}<br>
            {{ $billingAddress->country ?? 'USA' }}
        </p>
    </div>
    @endif
    
    <div class="school-info">
        <div class="school-label">Donation Designated To</div>
        <div class="school-name">{{ $donation->school->name }}</div>
    </div>
    
    <div class="amount-section">
        <div class="amount-label">Donation Amount</div>
        <div class="amount-value">${{ $donation->amount_dollars }}</div>
    </div>
    
    <div class="section">
        <div class="section-title">Payment Details</div>
        <table>
            <tr>
                <td class="info-label">Payment Date</td>
                <td class="info-value">{{ $donation->created_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
            <tr>
                <td class="info-label">Payment Method</td>
                <td class="info-value">Credit Card (Stripe)</td>
            </tr>
            @if($donation->transactions->first())
            <tr>
                <td class="info-label">Transaction ID</td>
                <td class="info-value">{{ $donation->transactions->first()->stripe_payment_intent_id }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    @if($donation->filing_status || $donation->filing_year)
    <div class="section">
        <div class="section-title">Tax Filing Information</div>
        <table>
            @if($donation->filing_year)
            <tr>
                <td class="info-label">Tax Year</td>
                <td class="info-value">{{ $donation->filing_year }}</td>
            </tr>
            @endif
            @if($donation->filing_status)
            <tr>
                <td class="info-label">Filing Status</td>
                <td class="info-value">{{ $donation->filing_status->label() }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif
    
    <div class="tax-notice">
        <div class="tax-notice-title">Tax Deduction Information</div>
        <div class="tax-notice-text">
            IBE Foundation is a 501(c)(3) non-profit organization. Your donation is tax-deductible to the extent allowed by law. 
            No goods or services were provided in exchange for this contribution. Please retain this receipt for your tax records.
        </div>
    </div>
    
    <div class="footer">
        <p><strong>IBE Foundation</strong></p>
        <p>EIN: <span class="ein">{{ config('app.ein', '00-0000000') }}</span></p>
        <p>{{ config('app.address', 'Phoenix, AZ') }}</p>
        <p>{{ config('app.url') }}</p>
        <p style="margin-top: 15px; font-style: italic;">Thank you for your generous support of education!</p>
    </div>
</body>
</html>
