<?php
// GBR EXPORT EMAIL DEBUGGER
// DROP THIS IN pages/system/, HIT IT IN BROWSER, READ THE OUTPUT, THEN DELETE IT.

require "../../build/auth.php";
require "../../build/functions.php";
require "../../build/mailer.php";

echo "<pre style='font-family:monospace;font-size:13px;line-height:1.8;padding:20px;'>";
echo "<b>GBR Export Email Debug</b>\n";
echo str_repeat("─", 60) . "\n\n";

// ── 1. SESSION ────────────────────────────────────────────
echo "<b>1. SESSION</b>\n";
echo "  user_id : " . ($_SESSION['user_id'] ?? '❌ NOT SET') . "\n";
echo "  user    : " . ($_SESSION['user']    ?? '❌ NOT SET') . "\n";
echo "  email   : " . ($_SESSION['email']   ?? '❌ NOT SET — this is why no email is sent') . "\n\n";

// ── 2. TMP DIRECTORY ──────────────────────────────────────
echo "<b>2. TMP DIRECTORY</b>\n";
$sys_tmp = sys_get_temp_dir();
$own_tmp = __DIR__ . '/../../uploads/tmp/';

echo "  sys_get_temp_dir() : $sys_tmp\n";
echo "  sys tmp writable   : " . (is_writable($sys_tmp) ? '✅ yes' : '❌ NO') . "\n";
echo "  uploads/tmp/ path  : " . realpath($own_tmp) ?: $own_tmp . "\n";

if (!is_dir($own_tmp)) mkdir($own_tmp, 0755, true);
echo "  uploads/tmp/ exists: " . (is_dir($own_tmp)    ? '✅ yes' : '❌ NO — mkdir failed') . "\n";
echo "  uploads/tmp/ write : " . (is_writable($own_tmp) ? '✅ yes' : '❌ NO — not writable') . "\n\n";

// ── 3. WRITE TEST FILE ────────────────────────────────────
echo "<b>3. WRITE TEST</b>\n";
$test_file = $own_tmp . 'gbr_write_test_' . time() . '.txt';
$written   = file_put_contents($test_file, 'GBR write test OK');
echo "  Write test         : " . ($written !== false ? '✅ OK (' . $written . ' bytes)' : '❌ FAILED') . "\n";
if ($written !== false) {
    echo "  Read back          : " . (file_get_contents($test_file) === 'GBR write test OK' ? '✅ OK' : '❌ FAILED') . "\n";
    @unlink($test_file);
}
echo "\n";

// ── 4. FPDF LOAD ──────────────────────────────────────────
echo "<b>4. FPDF</b>\n";
$fpdf_path = __DIR__ . '/../../build/fpdf.php';
echo "  fpdf.php exists    : " . (file_exists($fpdf_path) ? '✅ yes' : '❌ NOT FOUND') . "\n";
if (file_exists($fpdf_path)) {
    require_once $fpdf_path;
    echo "  FPDF class         : " . (class_exists('FPDF') ? '✅ loaded' : '❌ not loaded') . "\n";
}
echo "\n";

// ── 5. DUMMY PDF WRITE ────────────────────────────────────
echo "<b>5. DUMMY PDF WRITE</b>\n";
if (class_exists('FPDF')) {
    $test_pdf  = new FPDF();
    $test_pdf->AddPage();
    $test_pdf->SetFont('Arial', '', 12);
    $test_pdf->Cell(0, 10, 'GBR Test PDF');
    $test_pdf_path = $own_tmp . 'gbr_test_' . time() . '.pdf';
    $test_pdf->Output('F', $test_pdf_path);
    echo "  PDF created        : " . (file_exists($test_pdf_path) ? '✅ yes' : '❌ NO — Output F failed') . "\n";
    if (file_exists($test_pdf_path)) {
        echo "  PDF size           : " . filesize($test_pdf_path) . " bytes\n";
        @unlink($test_pdf_path);
    }
} else {
    echo "  ⚠️  Skipped — FPDF not loaded\n";
}
echo "\n";

// ── 6. PHPMAILER SMTP TEST ────────────────────────────────
echo "<b>6. PHPMAILER SMTP CONNECTION TEST</b>\n";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);
$mail->SMTPDebug  = SMTP::DEBUG_SERVER;
$mail->Debugoutput = function($str, $level) {
    echo "  [SMTP] " . htmlspecialchars(trim($str)) . "\n";
};
$mail->isSMTP();
$mail->Host       = 'mailin.endora.cz';
$mail->SMTPAuth   = true;
$mail->Username   = 'admin@gbrguh.eu';
$mail->Password   = "8kl6G1/d=96'";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
$mail->Timeout    = 10;

echo "  Attempting connection to mailin.endora.cz:587...\n";
try {
    $smtp_ok = $mail->smtpConnect();
    echo "  SMTP connect       : " . ($smtp_ok ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    if ($smtp_ok) $mail->smtpClose();
} catch (Exception $e) {
    echo "  SMTP connect       : ❌ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "\n";
}
echo "\n";

// ── 7. SEND TEST EMAIL ────────────────────────────────────
echo "<b>7. SEND TEST EMAIL</b>\n";
$test_email = $_SESSION['email'] ?? '';
if (empty($test_email)) {
    echo "  ⚠️  Skipped — no email in session (\$_SESSION['email'] is empty)\n";
    echo "  Fix: make sure your login sets \$_SESSION['email']\n";
} else {
    echo "  Sending to: $test_email\n";
    $result = sendGBRMail(
        $test_email,
        $_SESSION['user'] ?? 'Test User',
        'GBR Debug Test Email — ' . date('d.m.Y H:i'),
        '<p>This is a test email from the GBR debug script. If you see this, email sending works.</p>'
    );
    echo "  sendGBRMail()      : " . ($result ? '✅ SENT' : '❌ FAILED — check PHP error log') . "\n";
}

echo "\n" . str_repeat("─", 60) . "\n";
echo "Done. Delete this file from the server after reading.\n";
echo "</pre>";
