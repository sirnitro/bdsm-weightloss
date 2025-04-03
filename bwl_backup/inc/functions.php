<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $subject, $body, $altBody = '')
{
    global $pdo;

    $mail = new PHPMailer(true);
    $status = 'sent';
    $errorMsg = null;

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
    } catch (Exception $e) {
        $status = 'failed';
        $errorMsg = $mail->ErrorInfo;
        if (DEBUG_MODE) {
            error_log("Mailer Error: $errorMsg");
        }
    }

    // Log email to DB
    $stmt = $pdo->prepare("INSERT INTO email_logs (to_address, subject, body, status, error_message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$to, $subject, $body, $status, $errorMsg]);

    return $status === 'sent';
}

function get_email_template($slug)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT subject, body FROM email_templates WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function replace_placeholders($text, $data = [])
{
    foreach ($data as $key => $value) {
        $text = str_replace("{{{$key}}}", $value, $text);
    }
    return $text;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_program_slug($program_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT slug FROM programs WHERE id = ?");
    $stmt->execute([$program_id]);
    return $stmt->fetchColumn();
}

function url($path) {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function render_progress_bar(int $completed, int $total, bool $showPercentText = true): string {
    $percent = ($total > 0) ? round(($completed / $total) * 100) : 0;
    $tooltip = "$completed of $total completed";

    $label = $showPercentText ? "<span class='progress-label'>{$percent}%</span>" : '';

    $completeMsg = $percent === 100
        ? "<div class='progress-complete-msg'>🏁 Challenge Group Complete!</div>"
        : '';

    return <<<HTML
<div class="progress-bar-container" title="{$tooltip}">
    <div class="progress-bar-track">
        <div class="progress-bar-fill" style="width: {$percent}%">
            {$label}
        </div>
    </div>
    {$completeMsg}
</div>
HTML;
}

function get_domme_info(PDO $pdo, int $user_id): ?array {
    $stmt = $pdo->prepare("SELECT domme_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $domme_id = $stmt->fetchColumn();

    if ($domme_id) {
        $stmt = $pdo->prepare("SELECT display_name, title FROM users WHERE id = ?");
        $stmt->execute([$domme_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    return null;
}

function render_challenge_block(string $title, ?string $content): string {
    if (empty(trim($content))) {
        return '';
    }

    $safeContent = nl2br(htmlspecialchars(trim($content)));

    return <<<HTML
    <div class="challenge-block">
        <h3>{$title}</h3>
        <p>{$safeContent}</p>
    </div>
    HTML;
}

function render_reflection_prompt(): string {
    return <<<HTML
    <div class="challenge-block reflection-block">
        <h3>Reflection / Journal (Required):</h3>
        <textarea name="reflection" rows="6" placeholder="What did you experience? How did it feel? What did you learn?"></textarea>
    </div>
    HTML;
}

function get_active_orders_for_sub($sub_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM dominant_orders WHERE submissive_id = ? AND status = 'pending'");
    $stmt->execute([$sub_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

