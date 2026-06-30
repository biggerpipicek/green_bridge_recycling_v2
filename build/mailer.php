<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

define('GBR_ADMIN_EMAIL', 'admin@gbrguh.eu');
define('GBR_ADMIN_NAME',  'GBR Admin');
define('GBR_SITE_URL',    'https://gbrguh.eu');

function sendGBRMail(string $to_email, string $to_name, string $subject, string $html_body): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mailin.endora.cz';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@gbrguh.eu';
        $mail->Password   = "8kl6G1/d=96'";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('admin@gbrguh.eu', 'GBR');
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Subject  = $subject;
        $mail->Body     = wrapGBRTemplate($subject, $html_body);
        $mail->AltBody  = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>'], "\n", $html_body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Wraps content in a consistent GBR-branded HTML email template.
 */
function wrapGBRTemplate(string $title, string $content): string {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <tr>
          <td style="background:#1a6b3a;padding:24px 32px;">
            <span style="color:#ffffff;font-size:22px;font-weight:bold;letter-spacing:1px;">&#9850; Green Bridge Recycling</span>
          </td>
        </tr>
        <tr>
          <td style="padding:32px;">
            <h2 style="margin:0 0 20px;color:#1a1a1a;font-size:20px;">{$title}</h2>
            <div style="color:#444;font-size:15px;line-height:1.7;">
              {$content}
            </div>
          </td>
        </tr>
        <tr>
          <td style="background:#f8f9fa;padding:18px 32px;border-top:1px solid #e9ecef;">
            <p style="margin:0;color:#999;font-size:12px;">
              This is an automated notification from <a href="https://gbrguh.eu" style="color:#1a6b3a;">gbrguh.eu</a>.
              Please do not reply to this email directly.<br>
              &copy; {$year} Green Bridge Recycling
            </p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

// ─────────────────────────────────────────────
//  HELPER BUILDERS — one per trigger event
// ─────────────────────────────────────────────

/**
 * Order created notification (sent to admin).
 */
function mailOrderCreated($conn, int $order_id, array $order_data, string $partner_name): void {
    $order_no  = htmlspecialchars($order_data['order_no'] ?? "#{$order_id}");
    $type      = htmlspecialchars(strtoupper($order_data['type'] ?? ''));
    $date      = htmlspecialchars($order_data['date'] ?? date('Y-m-d'));
    $price     = htmlspecialchars($order_data['price'] ?? '0');
    $currency  = htmlspecialchars($order_data['currency'] ?? 'EUR');
    $partner   = htmlspecialchars($partner_name);
    $track_url = GBR_SITE_URL . '/green_bridge_recycling_v2/pages/public/track_trace.php?id=' . urlencode($order_data['track_id'] ?? '');
    $order_url = GBR_SITE_URL . "/green_bridge_recycling_v2/pages/system/add_guhring_order.php?id={$order_id}";

    $body = <<<HTML
<p>A new order has been created in the system.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;">
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;width:40%;">Order No</td><td style="padding:8px 12px;font-weight:bold;">{$order_no}</td></tr>
  <tr><td style="padding:8px 12px;color:#666;">Type</td><td style="padding:8px 12px;">{$type}</td></tr>
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;">Partner</td><td style="padding:8px 12px;">{$partner}</td></tr>
  <tr><td style="padding:8px 12px;color:#666;">Date</td><td style="padding:8px 12px;">{$date}</td></tr>
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;">Price</td><td style="padding:8px 12px;">{$price} {$currency}</td></tr>
</table>
<br>
<a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#1a6b3a;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;margin-right:10px;">View Order</a>
<a href="{$track_url}" style="display:inline-block;padding:10px 22px;background:#e9ecef;color:#333;border-radius:6px;text-decoration:none;font-weight:bold;">Track &amp; Trace</a>
HTML;

    $sent = sendGBRMail(GBR_ADMIN_EMAIL, GBR_ADMIN_NAME, "New Order Created — {$order_no}", $body);

    if ($sent) {
        $user_id = $_SESSION['user_id'] ?? 0;
        logActivity($conn, $user_id, 'email_sent', 'order', $order_id,
            "Email sent to " . GBR_ADMIN_EMAIL . " — new order {$order_no} created");
    }
}

/**
 * Order updated notification (sent to admin).
 * Sends a specific email for completed/cancelled, a generic diff email otherwise.
 */
function mailOrderUpdated($conn, int $order_id, array $old_data, array $new_data, string $partner_name): void {
    $order_no  = htmlspecialchars($old_data['order_no'] ?? "#{$order_id}");
    $new_status = $new_data['order_status'] ?? '';
    $old_status = $old_data['order_status'] ?? '';
    $order_url  = GBR_SITE_URL . "/green_bridge_recycling_v2/pages/system/add_guhring_order.php?id={$order_id}";
    $view_btn   = "<br><br><a href=\"{$order_url}\" style=\"display:inline-block;padding:10px 22px;background:#1a6b3a;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;\">View Order</a>";

    // --- COMPLETED ---
    if ($new_status === 'completed' && $old_status !== 'completed') {
        $approve = htmlspecialchars($new_data['approve_status'] ?? '');
        $body = <<<HTML
<p>Order <strong>{$order_no}</strong> has been marked as <span style="color:#1a6b3a;font-weight:bold;">Completed</span>.</p>
<p>Approval status: <strong>{$approve}</strong><br>Partner: <strong>{$partner_name}</strong></p>
{$view_btn}
HTML;
        $subject = "Order Completed — {$order_no}";

    // --- CANCELLED ---
    } elseif ($new_status === 'cancelled' && $old_status !== 'cancelled') {
        $body = <<<HTML
<p>Order <strong>{$order_no}</strong> has been marked as <span style="color:#dc3545;font-weight:bold;">Cancelled</span>.</p>
<p>Partner: <strong>{$partner_name}</strong></p>
{$view_btn}
HTML;
        $subject = "Order Cancelled — {$order_no}";

    // --- GENERIC UPDATE ---
    } else {
        $fields_to_watch = ['partner_id', 'type', 'date', 'price', 'currency',
                            'pallet_no', 'brutto_w', 'netto_w', 'approve_status', 'order_status'];
        $rows = '';
        $bg   = false;
        foreach ($fields_to_watch as $field) {
            if (isset($old_data[$field], $new_data[$field]) && (string)$old_data[$field] !== (string)$new_data[$field]) {
                $label   = htmlspecialchars(ucwords(str_replace('_', ' ', $field)));
                $old_val = htmlspecialchars($old_data[$field]);
                $new_val = htmlspecialchars($new_data[$field]);
                $bg_str  = $bg ? 'background:#f8f9fa;' : '';
                $rows   .= "<tr style=\"{$bg_str}\"><td style=\"padding:8px 12px;color:#666;width:35%;\">{$label}</td><td style=\"padding:8px 12px;text-decoration:line-through;color:#999;\">{$old_val}</td><td style=\"padding:8px 12px;font-weight:bold;color:#1a6b3a;\">{$new_val}</td></tr>";
                $bg = !$bg;
            }
        }

        if (empty($rows)) {
            return; // nothing meaningful changed, skip email
        }

        $body = <<<HTML
<p>Order <strong>{$order_no}</strong> was updated.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;">
  <thead>
    <tr style="background:#1a6b3a;color:#fff;">
      <th style="padding:8px 12px;text-align:left;">Field</th>
      <th style="padding:8px 12px;text-align:left;">Old Value</th>
      <th style="padding:8px 12px;text-align:left;">New Value</th>
    </tr>
  </thead>
  <tbody>{$rows}</tbody>
</table>
{$view_btn}
HTML;
        $subject = "Order Updated — {$order_no}";
    }

    $sent = sendGBRMail(GBR_ADMIN_EMAIL, GBR_ADMIN_NAME, $subject, $body);

    if ($sent) {
        $user_id = $_SESSION['user_id'] ?? 0;
        logActivity($conn, $user_id, 'email_sent', 'order', $order_id,
            "Email sent to " . GBR_ADMIN_EMAIL . " — order {$order_no} {$new_status}");
    }
}

/**
 * New ticket created notification (sent to admin).
 */
function mailTicketCreated($conn, int $ticket_id, string $title, string $description, string $priority, string $creator): void {
    $clean_title    = htmlspecialchars($title);
    $clean_desc     = nl2br(htmlspecialchars($description));
    $clean_priority = htmlspecialchars(ucfirst($priority));
    $clean_creator  = htmlspecialchars($creator);
    $ticket_url     = GBR_SITE_URL . "/green_bridge_recycling_v2/pages/system/template/ticket.php?id={$ticket_id}";

    $priority_color = match($priority) {
        'high'   => '#dc3545',
        'medium' => '#fd7e14',
        default  => '#6c757d',
    };

    $body = <<<HTML
<p>A new support ticket has been submitted.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;">
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;width:35%;">Ticket #</td><td style="padding:8px 12px;font-weight:bold;">{$ticket_id}</td></tr>
  <tr><td style="padding:8px 12px;color:#666;">Title</td><td style="padding:8px 12px;font-weight:bold;">{$clean_title}</td></tr>
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;">Priority</td><td style="padding:8px 12px;"><span style="color:{$priority_color};font-weight:bold;">{$clean_priority}</span></td></tr>
  <tr><td style="padding:8px 12px;color:#666;">Created By</td><td style="padding:8px 12px;">{$clean_creator}</td></tr>
</table>
<br>
<p style="color:#555;"><strong>Description:</strong><br>{$clean_desc}</p>
<a href="{$ticket_url}" style="display:inline-block;padding:10px 22px;background:#1a6b3a;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;">Open Ticket</a>
HTML;

    $sent = sendGBRMail(GBR_ADMIN_EMAIL, GBR_ADMIN_NAME, "New Ticket #{$ticket_id} — {$clean_title}", $body);

    if ($sent) {
        $user_id = $_SESSION['user_id'] ?? 0;
        logActivity($conn, $user_id, 'email_sent', 'ticket', $ticket_id,
            "Email sent to " . GBR_ADMIN_EMAIL . " — new ticket #{$ticket_id}: {$title}");
    }
}

/**
 * Ticket status updated notification (sent to admin).
 */
function mailTicketUpdated($conn, int $ticket_id, string $title, string $old_status, string $new_status, string $updater): void {
    $clean_title      = htmlspecialchars($title);
    $clean_old_status = htmlspecialchars(ucfirst(str_replace('_', ' ', $old_status)));
    $clean_new_status = htmlspecialchars(ucfirst(str_replace('_', ' ', $new_status)));
    $clean_updater    = htmlspecialchars($updater);
    $ticket_url       = GBR_SITE_URL . "/green_bridge_recycling_v2/pages/system/template/ticket.php?id={$ticket_id}";

    $status_color = match($new_status) {
        'closed'      => '#198754',
        'in_progress' => '#fd7e14',
        default       => '#dc3545',
    };

    $body = <<<HTML
<p>Ticket <strong>#{$ticket_id} — {$clean_title}</strong> has been updated.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;">
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;width:35%;">Old Status</td><td style="padding:8px 12px;text-decoration:line-through;color:#999;">{$clean_old_status}</td></tr>
  <tr><td style="padding:8px 12px;color:#666;">New Status</td><td style="padding:8px 12px;font-weight:bold;color:{$status_color};">{$clean_new_status}</td></tr>
  <tr style="background:#f8f9fa;"><td style="padding:8px 12px;color:#666;">Updated By</td><td style="padding:8px 12px;">{$clean_updater}</td></tr>
</table>
<br>
<a href="{$ticket_url}" style="display:inline-block;padding:10px 22px;background:#1a6b3a;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;">View Ticket</a>
HTML;

    $sent = sendGBRMail(GBR_ADMIN_EMAIL, GBR_ADMIN_NAME, "Ticket #{$ticket_id} Status Updated — {$clean_new_status}", $body);

    if ($sent) {
        $user_id = $_SESSION['user_id'] ?? 0;
        logActivity($conn, $user_id, 'email_sent', 'ticket', $ticket_id,
            "Email sent to " . GBR_ADMIN_EMAIL . " — ticket #{$ticket_id} status changed to {$new_status}");
    }
}

/**
 * Password changed notification (sent to the user's own email).
 */
function mailPasswordChanged($conn, int $user_id, string $username, string $user_email): void {
    $clean_user = htmlspecialchars($username);
    $time       = date('d.m.Y H:i');

    $body = <<<HTML
<p>Hi <strong>{$clean_user}</strong>,</p>
<p>Your GBR account password was changed successfully on <strong>{$time}</strong>.</p>
<p>If you did not make this change, please contact your system administrator immediately.</p>
<p style="color:#999;font-size:13px;">This notification was sent to the email address associated with your account.</p>
HTML;

    $sent = sendGBRMail($user_email, $clean_user, 'Your GBR Password Was Changed', $body);

    if ($sent) {
        logActivity($conn, $user_id, 'email_sent', 'user', $user_id,
            "Email sent to {$user_email} — password change confirmation for user #{$user_id}");
    }
}

/**
 * Export notification — saves PDF to temp, emails it to the user with admin BCC,
 * then the caller streams it to the browser.
 * $pdf_path    = temp file path of already-generated PDF (Output 'F')
 * $filename    = download filename e.g. GBR_Inventory_Export_20260611.pdf
 * $export_type = human label e.g. "Inventory", "Dashboard", "Gühring Orders"
 */
function mailExportReady($conn, string $pdf_path, string $filename, string $export_type, string $user_email, string $username): void {
    $clean_user = htmlspecialchars($username);
    $time       = date('d.m.Y H:i');

    $body = <<<HTML
<p>Hi <strong>{$clean_user}</strong>,</p>
<p>Your <strong>{$export_type}</strong> export from <strong>{$time}</strong> is attached to this email.</p>
<p style="color:#999;font-size:13px;">This was generated automatically upon your request in GBR.</p>
HTML;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mailin.endora.cz';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@gbrguh.eu';
        $mail->Password   = "8kl6G1/d=96'";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('admin@gbrguh.eu', 'GBR');
        $mail->addAddress($user_email, $clean_user);
        $mail->addBCC(GBR_ADMIN_EMAIL, GBR_ADMIN_NAME);
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Subject  = "GBR Export — {$export_type} ({$time})";
        $mail->Body     = wrapGBRTemplate("GBR Export — {$export_type}", $body);
        $mail->AltBody  = "Your {$export_type} export from {$time} is attached.";
        $mail->addAttachment($pdf_path, $filename);

        $mail->send();

        $user_id = $_SESSION['user_id'] ?? 0;
        logActivity($conn, $user_id, 'email_sent', 'export', $user_id,
            "Export email ({$export_type}) sent to {$user_email} with PDF attachment");

    } catch (Exception $e) {
        error_log("PHPMailer export error: " . $mail->ErrorInfo);
    }
}