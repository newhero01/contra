<?php
// ==========================================
// CONFIGURATION
// ==========================================
$FIXED_APR = 4.99;
$FIXED_FEES = 15.00;

$contract_html = "";

// ==========================================
// LOGIC (Runs when form is submitted)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $amount = floatval($_POST['amount']);
    $time_str = htmlspecialchars($_POST['time']);
    $lender_name = htmlspecialchars($_POST['lender_name']);
    $cfo = htmlspecialchars($_POST['cfo']);
    $phone = htmlspecialchars($_POST['phone']);

    // Parse months
    $months = 12;
    preg_match('/[\d.]+/', $time_str, $matches);
    if (!empty($matches)) {
        $val = floatval($matches[0]);
        $months = (stripos($time_str, 'y') !== false) ? intval($val * 12) : intval($val);
    }

    // Calculate EMI
    $r = ($FIXED_APR / 100.0) / 12.0;
    $emi = 0;
    if ($months > 0 && $amount > 0) {
        $emi = $amount * $r * pow(1 + $r, $months) / (pow(1 + $r, $months) - 1);
    }

    // Generate Amortization Table
    $schedule_html = '<table class="standard-table amortization-table"><thead><tr><th>Pmt #</th><th>Payment Amount</th><th>Principal Applied</th><th>Interest Applied</th><th>Remaining Balance</th></tr></thead><tbody>';
    $balance = $amount;
    for ($i = 1; $i <= $months; $i++) {
        $interest_pmt = $balance * $r;
        $principal_pmt = $emi - $interest_pmt;
        $balance -= $principal_pmt;
        if ($i == $months || $balance < 0) $balance = 0.0;
        
        $schedule_html .= "<tr><td>{$i}</td><td>$" . number_format($emi, 2) . "</td><td>$" . number_format($principal_pmt, 2) . "</td><td>$" . number_format($interest_pmt, 2) . "</td><td>$" . number_format($balance, 2) . "</td></tr>";
    }
    $schedule_html .= '</tbody></table>';

    // Build the final contract
    // (You would paste your full CSS and HTML here, replacing variables)
    $contract_html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>{$customer_name}_Contract</title>
        <style>
            /* PASTE YOUR FULL CSS HERE */
            body { font-family: sans-serif; padding: 40px; }
            .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 130px; color: rgba(0, 139, 116, 0.08); z-index: -1; }
            @media print { body { padding: 0; } }
        </style>
    </head>
    <body onload='window.print()'>
        <div class='watermark'>CONFIDENTIAL</div>
        <h1>Personal Loan Agreement</h1>
        <p>Dear <strong>{$customer_name}</strong>,</p>
        <p>Loan Amount: $" . number_format($amount, 2) . "</p>
        <p>Term: {$months} months</p>
        <p>Monthly Payment (EMI): $" . number_format($emi, 2) . "</p>
        <p>Lender: {$lender_name}</p>
        <p>Authorized Rep: {$cfo}</p>
        <p>Phone: {$phone}</p>
        
        <h2>Repayment Schedule</h2>
        {$schedule_html}
    </body>
    </html>";
}
?>

<!-- ==========================================
// WEB FORM (Shows if not submitted)
// ========================================== -->
<?php if (empty($contract_html)): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Contract Generator</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 40px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 8px 0 20px; box-sizing: border-box; }
        button { width: 100%; background: #008B74; color: white; padding: 12px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Generate Loan Contract</h2>
        <form method="post">
            <label>Borrower Name:</label>
            <input type="text" name="customer_name" required>
            
            <label>Loan Amount ($):</label>
            <input type="number" step="0.01" name="amount" required>
            
            <label>Term (e.g., 12 months):</label>
            <input type="text" name="time" required>
            
            <label>Lender Company:</label>
            <input type="text" name="lender_name" required>
            
            <label>Authorized Rep (CFO):</label>
            <input type="text" name="cfo" required>
            
            <label>Phone:</label>
            <input type="text" name="phone" required>
            
            <button type="submit">Generate Contract</button>
        </form>
    </div>
</body>
</html>
<?php else: ?>
    <!-- If submitted, output the contract HTML which will auto-print -->
    <?php echo $contract_html; ?>
<?php endif; ?>
