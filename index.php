<?php
// ==========================================
// CONFIGURATION & CONSTANTS
// ==========================================
$FIXED_APR = 4.99;
$FIXED_FEES = 15.00;

$final_html = "";

// ==========================================
// EXACT PYTHON LOGIC TRANSLATED TO PHP
// ==========================================

function parse_time_to_months($time_str) {
    $time_clean = strtolower(trim($time_str));
    if (preg_match('/[\d.]+/', $time_clean, $matches)) {
        $val = floatval($matches[0]);
        return (strpos($time_clean, 'y') !== false || strpos($time_clean, 'year') !== false) ? intval($val * 12) : intval($val);
    }
    return 12;
}

function calculate_emi($principal, $months, $annual_rate) {
    if ($months <= 0 || $principal <= 0) return 0.0;
    $r = ($annual_rate / 100.0) / 12.0;
    if ($r == 0) return $principal / $months;
    return $principal * $r * pow(1 + $r, $months) / (pow(1 + $r, $months) - 1);
}

function generate_amortization_html($principal, $months, $emi, $annual_rate) {
    if ($months <= 0 || $principal <= 0) {
        return "<p><em>No amortization schedule available.</em></p>";
    }

    $html_parts = [
        '<table class="standard-table amortization-table">',
        '<thead><tr>',
        '<th>Pmt #</th>',
        '<th>Payment Amount</th>',
        '<th>Principal Applied</th>',
        '<th>Interest Applied</th>',
        '<th>Remaining Balance</th>',
        '</tr></thead><tbody>'
    ];

    $monthly_rate = ($annual_rate / 100.0) / 12.0;
    $balance = $principal;

    for ($i = 1; $i <= $months; $i++) {
        $interest_pmt = $balance * $monthly_rate;
        $principal_pmt = $emi - $interest_pmt;
        $balance -= $principal_pmt;
        if ($i == $months || $balance < 0) $balance = 0.0;

        $html_parts[] = "<tr>" .
            "<td>{$i}</td>" .
            "<td>$" . number_format($emi, 2) . "</td>" .
            "<td>$" . number_format($principal_pmt, 2) . "</td>" .
            "<td>$" . number_format($interest_pmt, 2) . "</td>" .
            "<td>$" . number_format($balance, 2) . "</td>" .
            "</tr>";
    }
    $html_parts[] = '</tbody></table>';
    return implode("", $html_parts);
}

// ==========================================
// HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get inputs
    $customer_name = trim($_POST['Customer_Name'] ?? '');
    $raw_amount = floatval($_POST['amount'] ?? 0);
    $raw_time = trim($_POST['time'] ?? '');
    $lender_name = trim($_POST['Lender_name'] ?? '');
    $cfo = trim($_POST['CFO'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');

    // Calculations
    $months = parse_time_to_months($raw_time);
    $emi_val = calculate_emi($raw_amount, $months, $FIXED_APR);
    $schedule_html = generate_amortization_html($raw_amount, $months, $emi_val, $FIXED_APR);

    // EXACT HTML TEMPLATE FROM PYTHON CODE
    // (Using NOWDOC <<<'HTML' so PHP does not accidentally modify the $ variables)
    $CONTRACT_HTML = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Loan Agreement</title>
    <!-- Executive Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brand-primary: #008B74; 
            --brand-secondary: #7CB342; 
            --brand-dark: #0f172a;
            --brand-gold: #B8860B; /* Visible Professional Gold */
            --bg-gold: #FFFCF2; /* Soft Gold Background */
            --text-main: #334155;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --bg-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.65;
            color: var(--text-main);
            max-width: 880px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #e2e8f0;
            position: relative;
        }
        
        /* CONFIDENTIAL WATERMARK */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 130px;
            font-family: 'Cinzel', serif;
            font-weight: 700;
            color: rgba(0, 139, 116, 0.08);
            z-index: 9999; 
            pointer-events: none; 
            user-select: none;
            white-space: nowrap;
        }

        .document-container {
            background-color: #ffffff;
            padding: 60px 65px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            position: relative;
        }

        /* Header Layout */
        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 3px solid var(--brand-primary);
        }

        .header-logo { flex-shrink: 0; }
        .header-logo svg { height: 75px; width: auto; display: block; }

        .header-title {
            text-align: right;
            margin-left: auto;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .header-title h1 {
            font-family: 'Cinzel', serif;
            color: var(--brand-dark);
            font-size: 26px;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-title p {
            margin: 4px 0 0 0;
            color: var(--brand-primary);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        h2 {
            font-family: 'Cinzel', serif;
            font-size: 17px;
            color: var(--brand-dark);
            margin-top: 35px;
            margin-bottom: 15px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border-color);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        p { margin-bottom: 14px; text-align: justify; font-size: 14.5px; }
        strong { color: var(--brand-dark); font-weight: 600; }

        /* GOLD HIGHLIGHT CLAUSE */
        .highlight-clause {
            background-color: var(--bg-gold);
            border-left: 6px solid var(--brand-gold);
            border-radius: 6px;
            padding: 20px 25px;
            margin: 35px 0;
            box-shadow: 0 4px 6px -1px rgba(184, 134, 11, 0.15);
        }
        .highlight-clause h2 { 
            margin-top: 0; 
            border-bottom: 1px solid rgba(184, 134, 11, 0.3); 
            color: var(--brand-gold); 
        }

        /* Tables */
        .loan-table, .standard-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0 35px 0;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid var(--brand-primary);
        }
        .loan-table th {
            background-color: var(--brand-primary);
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            padding: 14px 18px;
            text-align: center;
        }
        .loan-table td {
            background-color: var(--bg-light);
            color: var(--brand-dark);
            font-weight: 700;
            font-size: 17px;
            text-align: center;
            padding: 16px 18px;
            border-right: 1px solid var(--border-color);
        }
        .loan-table td:last-child { border-right: none; }
        
        /* Gold Highlight Text */
        .loan-table .highlight { color: var(--brand-gold); font-size: 19px; }

        .standard-table { border: 1px solid var(--border-color); }
        .standard-table th, .standard-table td { padding: 12px 18px; text-align: left; font-size: 14.5px; border-bottom: 1px solid var(--border-color); }
        .standard-table th { background-color: var(--bg-light); color: var(--brand-dark); font-weight: 600; font-size: 12.5px; text-transform: uppercase; }

        .amortization-table { font-family: monospace; font-size: 13.5px; }
        .amortization-table th { background-color: var(--brand-dark); color: white; text-align: center; }
        .amortization-table td { text-align: right; padding: 8px 15px; }
        .amortization-table td:first-child { text-align: center; font-weight: bold; }

        ul, ol { margin-bottom: 20px; padding-left: 25px; font-size: 14.5px; }
        li { margin-bottom: 8px; }

        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; gap: 40px; page-break-inside: avoid; }
        .signature-block { flex: 1; }
        .signature-line { border-top: 1.5px solid var(--brand-dark); margin-top: 60px; margin-bottom: 10px; }
        .signature-block p { margin: 3px 0; font-size: 13.5px; }

        .footer-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 60px;
            font-size: 13px;
            color: var(--text-muted);
            text-align: center;
            border-top: 1px solid var(--border-color);
            padding-top: 30px;
        }
        .footer-logo { margin-bottom: 12px; }
        .footer-logo svg { height: 30px; width: auto; opacity: 0.9; }

        @media print {
            body { background-color: #ffffff; padding: 0; }
            .document-container { box-shadow: none; border: none; padding: 0; }
            @page { size: A4; margin: 15mm; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- FIXED WATERMARK -->
    <div class="watermark">CONFIDENTIAL</div>

    <div class="document-container">
        
        <div class="header-section">
            <div class="header-logo">
                <svg viewBox="0 0 400 90" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="45" cy="45" r="38" fill="#008B74" />
                    <path d="M 78 18 A 38 38 0 0 1 78 72 A 42 42 0 0 0 78 18 Z" fill="#7CB342" />
                    <text x="45" y="62" font-family="Plus Jakarta Sans, sans-serif" font-weight="700" font-size="50" fill="white" text-anchor="middle">$</text>
                    <text x="100" y="42" font-family="Plus Jakarta Sans, sans-serif" font-weight="800" font-size="36" fill="#008B74" letter-spacing="2">SECURE</text>
                    <text x="105" y="76" font-family="Plus Jakarta Sans, sans-serif" font-weight="700" font-size="24" fill="#7CB342" letter-spacing="4">LOANS</text>
                    <text x="235" y="76" font-family="Plus Jakarta Sans, sans-serif" font-weight="600" font-size="18" fill="#008B74" letter-spacing="1">USA</text>
                    <line x1="235" y1="58" x2="280" y2="58" stroke="#008B74" stroke-width="2"/>
                </svg>
            </div>
            <div class="header-title">
                <h1>Personal Loan Agreement</h1>
                <p>Official Contract Document</p>
            </div>
        </div>

        <p>Dear <strong>$Customer_Name</strong>,</p>
        <p>This legally binding Personal Loan Agreement (hereinafter referred to as the "Agreement") establishes the covenants, terms, and financial obligations negotiated and agreed upon between you (the "Borrower") and SECURE LOANS USA (the "Lender"). By executing this document, both parties acknowledge mutual assent to the stipulations detailed herein.</p>

        <!-- Loan Details Summary Table -->
        <h2>Loan Details Summary</h2>
        <table class="loan-table">
            <tr>
                <th>Principal Amount</th>
                <th>Monthly Payment</th>
                <th>Term Duration</th>
                <th>APR</th>
            </tr>
            <tr>
                <td>$amount</td>
                <td class="highlight">$EMI</td>
                <td>$time</td>
                <td>$rate</td>
            </tr>
        </table>

        <h2>I. The Parties</h2>
        <table class="standard-table">
            <tr>
                <th>Role</th>
                <th>Legal Name / Entity</th>
            </tr>
            <tr>
                <td><strong>Borrower</strong></td>
                <td>$Customer_Name</td>
            </tr>
            <tr>
                <td><strong>Lender</strong></td>
                <td>$Lender_name</td>
            </tr>
        </table>
        <p><strong>Lender's Registered Address:</strong> 2014 W Berridge Ln, Phoenix, AZ 85015</p>

        <h2>II. Loan Principal & Indebtedness</h2>
        <p>The Lender hereby agrees to advance, and the Borrower agrees to accept, a total principal sum of <strong>$amount</strong> (the "Borrowed Amount"). The Borrower acknowledges that this sum represents a valid and absolute debt owed to the Lender, subject to compound interest accumulation as outlined in Section III.</p>

        <h2>III. Terms of Agreement</h2>
        <ol>
            <li><strong>Repayment Schedule:</strong>
                <ul>
                    <li>The Borrowed Amount shall be amortized and repaid in consecutive monthly installments over a period of <strong>$time</strong>.</li>
                    <li>Each installment shall be fixed at the exact amount of <strong>$EMI</strong>, barring any previously assessed late fees or adjustments.</li>
                </ul>
            </li>
            <li><strong>APR:</strong>
                <ul>
                    <li>The principal balance shall accrue interest at a fixed Annual Percentage Rate (APR) of <strong>$rate</strong>, compounded monthly based on the outstanding principal balance.</li>
                </ul>
            </li>
            <li><strong>Late Payment Policy:</strong>
                <ul>
                    <li>Any scheduled payment not received within the established grace period will automatically incur a fixed penalty fee of <strong>$Fees</strong>, which shall be added to the total liability.</li>
                </ul>
            </li>
            <li><strong>Prepayment Policy:</strong>
                <ul>
                    <li>The Borrower retains the unilateral right to satisfy the outstanding debt in full prior to maturity, without incurring any prepayment penalties, unearned interest charges, or restrictive fees.</li>
                </ul>
            </li>
        </ol>

        <h2>IV. Dispute Resolution & Arbitration</h2>
        <p>In the event of any controversy, claim, or dispute arising out of or relating to this Agreement, or the breach thereof, both parties agree to resolve the matter solely through binding arbitration. The arbitration shall be conducted by a neutral arbitrator mutually agreed upon, in accordance with applicable federal and state commercial arbitration guidelines. The prevailing party shall be entitled to recover reasonable attorney’s fees and collection costs.</p>
        
        <h2>V. Application of Payments</h2>
        <p>The Borrower covenants to remit payment to the Lender on the <strong>15th day of each calendar month</strong> until the debt is wholly extinguished. All payments tendered by the Borrower shall be applied systematically in the following order of precedence: first to any accrued and unpaid late fees or collection costs, second to accrued and unpaid interest, and finally to the reduction of the outstanding principal balance.</p>

        <div class="page-break"></div>

        <h2>VI. Loan Repayment Schedule</h2>
        <p>The following schedule represents the planned amortization of the loan based on regular, on-time monthly payments. <em>(Note: Deviations in payment dates or amounts may alter this schedule)</em>.</p>
        
        <!-- DYNAMIC AMORTIZATION TABLE GENERATED HERE -->
        $Repayment_Schedule

        <h2>VII. Late Payment, Default, & Acceleration</h2>
        <p>Timely adherence to the repayment schedule is a material condition of this Agreement. A payment is classified as delinquent if it is not received within <strong>3 days</strong> of the specified due date, triggering the immediate application of the late fee.</p>
        
        <p>The Lender reserves the explicit right to declare the entire unpaid Principal Balance, along with any accrued interest and accumulated fees, immediately due and payable (Acceleration) upon the occurrence of any of the following Events of Default:</p>
        <ul>
            <li><strong>a. Severe Delinquency:</strong> Failure to remit any scheduled payment within <strong>15 days</strong> of its designated due date.</li>
            <li><strong>b. Breach of Covenant:</strong> The Borrower's failure to adhere strictly to any condition, warranty, or representation stated in this Agreement.</li>
            <li><strong>c. Asset Transfer:</strong> The unauthorized encumbrance, sale, or transfer of any asset or property explicitly pledged as security or collateral for this financial instrument without the Lender's express written consent.</li>
        </ul>
        <p>In the event of default, the Borrower hereby waives presentment, demand for payment, notice of dishonor, and protest. The Lender shall have the unconditional right to obtain possession of any pledged Collateral in its entirety, utilizing all lawful remedies, without applying any arbitrary discount to the total amount owed.</p>

        <h2>VIII. Credit Reporting & Verification</h2>
        <p>The Borrower acknowledges and grants permission for the Lender to report information about this account to major credit bureaus. Late payments, missed payments, or other defaults on this account may be reflected on the Borrower’s credit report, potentially impacting future creditworthiness.</p>

        <!-- HIGHLIGHTED SECTION (GOLD) -->
        <div class="highlight-clause">
            <h2>IX. Additional Clause (Funds Disbursement)</h2>
            <p>As part of our streamlined loan facilitation process, we are pleased to confirm that upon your successful completion of the mandated Credit Boost Process, the loan funds will be immediately authorized for disbursement. </p>
            <p>The total approved capital will be wired directly via ACH into your authorized checking account ending with <strong>XXXXXX7627 at JPMORGAN CHASE BANK</strong>. This transfer typically clears within 2 to 3 business hours, ensuring a swift, secure, and highly convenient allocation of liquidity for your immediate use.</p>
        </div>

        <h2>X. Severability</h2>
        <p>If any single provision, clause, or subsection of this Agreement is held to be invalid, illegal, or unenforceable by a court of competent jurisdiction, such invalidity shall not affect or render void the remainder of this Agreement. The problematic provision shall be enforced to the maximum extent permissible by law, and the remaining legal provisions will remain in full force and effect, maintaining the original intent of the parties.</p>

        <h2>XI. Governing Law & Jurisdiction</h2>
        <p>This Agreement, and all matters arising out of or relating to it, shall be governed by, construed, and enforced in accordance with the statutory laws of the <strong>State of California</strong>, excluding its conflict of laws principles. Both parties irrevocably consent to the exclusive jurisdiction of the state and federal courts located in California for the adjudication of any legal actions not otherwise resolved through mandatory arbitration.</p>

        <p><em>IN WITNESS WHEREOF, the Borrower and Lender have executed this Agreement as of the day and year first above written, acknowledging complete understanding and willful acceptance of all obligations, warranties, and terms.</em></p>

        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p><strong>Borrower's Signature</strong></p>
                <p>Printed Name: $Customer_Name</p>
                <p>Date: _________________</p>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <p><strong>Authorized Lender Representative</strong></p>
                <p>Printed Name: $CFO</p>
                <p>Title: Senior Loan Processing Officer</p>
                <p>Date: _________________</p>
            </div>
        </div>

        <div class="footer-info">
            <div class="footer-logo">
                <svg viewBox="0 0 400 90" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="45" cy="45" r="38" fill="#008B74" />
                    <path d="M 78 18 A 38 38 0 0 1 78 72 A 42 42 0 0 0 78 18 Z" fill="#7CB342" />
                    <text x="45" y="62" font-family="Plus Jakarta Sans, sans-serif" font-weight="700" font-size="50" fill="white" text-anchor="middle">$</text>
                    <text x="100" y="42" font-family="Plus Jakarta Sans, sans-serif" font-weight="800" font-size="36" fill="#008B74" letter-spacing="2">SECURE</text>
                    <text x="105" y="76" font-family="Plus Jakarta Sans, sans-serif" font-weight="700" font-size="24" fill="#7CB342" letter-spacing="4">LOANS</text>
                    <text x="235" y="76" font-family="Plus Jakarta Sans, sans-serif" font-weight="600" font-size="18" fill="#008B74" letter-spacing="1">USA</text>
                    <line x1="235" y1="58" x2="280" y2="58" stroke="#008B74" stroke-width="2"/>
                </svg>
            </div>
            <p><strong>$Lender_name</strong> • 745 Broadway, San Francisco, CA 94133 • Contact: <strong>$Phone</strong></p>
        </div>

    </div>
</body>
</html>
HTML;

    // Apply exact replacements just like the Python string replacer
    $replacements = [
        '$Customer_Name' => $customer_name,
        '$amount' => "$" . number_format($raw_amount, 2),
        '$time' => $months . " months",
        '$Lender_name' => $lender_name,
        '$CFO' => $cfo,
        '$Phone' => $phone,
        '$EMI' => "$" . number_format($emi_val, 2),
        '$rate' => $FIXED_APR . "%",
        '$Fees' => "$" . number_format($FIXED_FEES, 2),
        '$Repayment_Schedule' => $schedule_html
    ];

    $final_html = str_replace(array_keys($replacements), array_values($replacements), $CONTRACT_HTML);
}
?>

<?php if (empty($final_html)): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Contract Generator</title>
    <style>
        body { font-family: sans-serif; background: #e2e8f0; padding: 40px; color: #334155; }
        .container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #0f172a; }
        label { font-weight: 600; font-size: 14px; margin-bottom: 6px; display: block; color: #008B74; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; background: #008B74; color: white; padding: 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #006b5a; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Generate Loan Contract</h2>
        <form method="post">
            <label>Customer Name:</label>
            <input type="text" name="Customer_Name" required>
            
            <label>Loan Amount ($):</label>
            <input type="number" step="0.01" name="amount" required>
            
            <label>Loan Terms (Months):</label>
            <input type="text" name="time" required>
            
            <label>Lender Company Name:</label>
            <input type="text" name="Lender_name" required>
            
            <label>Authorized Rep (CFO):</label>
            <input type="text" name="CFO" required>
            
            <label>Contact Phone:</label>
            <input type="text" name="Phone" required>
            
            <button type="submit">Generate & Save PDF</button>
        </form>
    </div>
</body>
</html>
<?php else: ?>
    <?php echo $final_html; ?>
<?php endif; ?>
