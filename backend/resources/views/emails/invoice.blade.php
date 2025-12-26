<!DOCTYPE html>
<html>

<head>
    <title>Invoice from {{ $businessName }}</title>
</head>

<body>
    <p>Dear {{ $customerName }},</p>

    <p>Please find attached the invoice <strong>{{ $invoiceNumber }}</strong> dated {{ $invoiceDate }}.</p>

    <p>Details:<br>
        Amount Due: <strong>{{ $amountDue }}</strong><br>
        Due Date: {{ $dueDate }}</p>

    <p>Thank you for your business!</p>

    <p>Regards,<br>
        {{ $businessName }}</p>
</body>

</html>