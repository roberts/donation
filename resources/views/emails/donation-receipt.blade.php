<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Donation</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #1a56db;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .highlight {
            font-weight: bold;
        }
        .details {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .details li {
            margin-bottom: 8px;
        }
        .notice {
            background-color: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Thank You for Your Generous Donation!</h1>
    
    <p>Dear {{ $donation->donor->first_name }},</p>
    
    <p>On behalf of <span class="highlight">{{ $donation->school->name }}</span> and the entire IBE Foundation family, we want to express our heartfelt gratitude for your generous donation of <span class="highlight">${{ $donation->amount_dollars }}</span>.</p>
    
    <p>Your contribution makes a real difference in the lives of students and helps us continue our mission to invest in education.</p>
    
    <h2>Donation Details</h2>
    
    <div class="details">
        <ul>
            <li><strong>Receipt Number:</strong> #{{ $donation->id }}</li>
            <li><strong>Date:</strong> {{ $donation->created_at->format('F j, Y') }}</li>
            <li><strong>Amount:</strong> ${{ $donation->amount_dollars }}</li>
            <li><strong>School:</strong> {{ $donation->school->name }}</li>
        </ul>
    </div>
    
    <div class="notice">
        <strong>Your official tax receipt is attached to this email as a PDF.</strong>
        <p style="margin-top: 10px; margin-bottom: 0;">Please keep this receipt for your tax records. IBE Foundation is a 501(c)(3) non-profit organization, and your donation is tax-deductible to the extent allowed by law.</p>
    </div>
    
    <p>If you have any questions about your donation or would like to learn more about how your contribution is making an impact, please don't hesitate to reach out.</p>
    
    <p>With gratitude,<br><strong>IBE Foundation Team</strong></p>
    
    <div class="footer">
        <p>EIN: {{ config('app.ein', '00-0000000') }} | This email was sent to {{ $donation->donor->email }}</p>
    </div>
</body>
</html>
