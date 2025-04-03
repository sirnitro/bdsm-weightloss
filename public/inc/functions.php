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
    $stmt = $pdo->prepare("SELECT dom_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $dom_id = $stmt->fetchColumn();

    if ($dom_id) {
        $stmt = $pdo->prepare("SELECT display_name, title FROM users WHERE id = ?");
        $stmt->execute([$dom_id]);
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

function render_sub_dashboard(PDO $pdo, int $user_id): void {
    // Fetch Domme info
    $stmt = $pdo->prepare("SELECT u.display_name, u.title FROM users u
                           JOIN users s ON u.id = s.dom_id
                           WHERE s.id = ?");
    $stmt->execute([$user_id]);
    $domData = $stmt->fetch();

    if ($domData) {
        $display = $domData['title'] ? "{$domData['title']} {$domData['display_name']}" : $domData['display_name'];
        echo "<div class='dom-banner'>You are the property of <strong>{$display}</strong>.</div>";
    } else {
        echo "<div class='info'>You are not linked to a Dominant. <a href='" . BASE_URL . "auth/link_submissive.php'>Use an invite code</a>.</div>";
    }

    // Fetch and show progress
    require_once __DIR__ . '/program_progress_block.php';
    
    echo "<hr>";

    // Message area
    echo "<a href='" . BASE_URL . "members/messages.php' class='btn-primary'>📩 Message Your Dominant</a>";
}

function render_dom_dashboard(PDO $pdo, int $user_id): void {
    echo "<h2>Your Submissives</h2>";

    $stmt = $pdo->prepare("SELECT id, display_name, email FROM users WHERE dom_id = ?");
    $stmt->execute([$user_id]);
    $subs = $stmt->fetchAll();

    if (!$subs) {
        echo "<p>You have no submissives... yet.</p>";
    } else {
        echo "<ul class='challenge-list'>";
        foreach ($subs as $sub) {
            echo "<li><strong>" . htmlspecialchars($sub['display_name']) . "</strong>
                <small>(" . htmlspecialchars($sub['email']) . ")</small>
                <a href='messages.php?user_id={$sub['id']}' class='btn-small'>Message</a>
                <form method='post' action='unlink_sub.php' style='display:inline;'>
                    <input type='hidden' name='sub_id' value='{$sub['id']}'>
                    <button class='btn-small btn-danger' onclick='return confirm(\"Unlink this submissive?\")'>Unlink</button>
                </form></li>";
        }
        echo "</ul>";
    }

    echo "<hr><h3>Create Invite Link</h3>
        <form method='post' action='generate_invite.php'>
            <label>Max Uses:</label>
            <input type='number' name='max_uses' value='1' min='1'>
            <button class='btn-small' type='submit'>Generate</button>
        </form>";

    echo "<hr><h3>Your Active Invites</h3>";
    $stmt = $pdo->prepare("SELECT * FROM dom_invites WHERE dom_id = ? AND is_revoked = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $invites = $stmt->fetchAll();

    if ($invites) {
        echo "<ul class='challenge-list'>";
        foreach ($invites as $invite) {
            $used = $invite['used_by'] ? 1 : 0;
            $status = $invite['is_revoked'] ? 'Revoked' : ($used >= $invite['max_uses'] ? 'Used' : 'Active');
            $link = BASE_URL . "auth/link_submissive.php?code=" . $invite['code'];

            echo "<li><code>{$invite['code']}</code> — 
                <a href='{$link}' target='_blank'>Invite Link</a> 
                (Status: {$status}) 
                <form method='post' action='revoke_invite.php' style='display:inline;'>
                    <input type='hidden' name='invite_id' value='{$invite['id']}'>
                    <button class='btn-small btn-danger'>Revoke</button>
                </form></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No active invites.</p>";
    }

    echo "<hr><a href='messages.php' class='btn-primary'>📬 View Messages</a>";
}

<?php

function fetch_unread_messages(int \$user_id, PDO \$pdo): array {
    \$stmt = \$pdo->prepare("SELECT * FROM messages WHERE receiver_id = ? AND seen = 0 ORDER BY created_at DESC");
    \$stmt->execute([\$user_id]);
    return \$stmt->fetchAll(PDO::FETCH_ASSOC);
}

function render_unread_messages(array \$messages): void {
    if (empty(\$messages)) return;
    echo "<div class='message-center'><h3>📩 Unread Messages</h3><ul>";
    foreach (\$messages as \$msg) {
        echo "<li><strong>" . ucfirst(\$msg['type']) . ":</strong> " . htmlspecialchars(\$msg['message']) . "</li>";
    }
    echo "</ul></div><hr/>";
}

function fetch_enrolled_programs(int \$user_id, PDO \$pdo): array {
    \$stmt = \$pdo->prepare("SELECT p.*, up.enrolled_at FROM user_programs up JOIN programs p ON p.id = up.program_id WHERE up.user_id = ? ORDER BY up.enrolled_at DESC");
    \$stmt->execute([\$user_id]);
    return \$stmt->fetchAll(PDO::FETCH_ASSOC);
}

function render_program_progress(array \$programs, int \$user_id, PDO \$pdo): void {
    if (empty(\$programs)) {
        echo "<p>You haven’t enrolled in any programs yet.</p><a href='" . BASE_URL . "pages/programs.php' class='btn-primary'>Browse Programs</a>";
        return;
    }

    echo "<div class='programs-grid'>";
    foreach (\$programs as \$program) {
        \$totalStmt = \$pdo->prepare("SELECT COUNT(*) FROM challenges c JOIN challenge_groups g ON c.group_id = g.id WHERE g.program_id = ?");
        \$totalStmt->execute([\$program['id']]);
        \$totalChallenges = \$totalStmt->fetchColumn();

        \$completedStmt = \$pdo->prepare("SELECT COUNT(*) FROM user_challenges uc JOIN challenges c ON uc.challenge_id = c.id JOIN challenge_groups g ON c.group_id = g.id WHERE uc.user_id = ? AND g.program_id = ? AND uc.completed_at IS NOT NULL");
        \$completedStmt->execute([\$user_id, \$program['id']]);
        \$completedChallenges = \$completedStmt->fetchColumn();

        \$isComplete = (\$totalChallenges > 0 && \$completedChallenges >= \$totalChallenges);

        echo "<div class='program-card'>";
        echo "<h3>" . htmlspecialchars(\$program['name']);
        if (\$isComplete) echo " <span class='badge-complete'>🏅 Completed</span>";
        echo "</h3>";
        echo "<p class='style-label'>" . htmlspecialchars(\$program['style']) . "</p>";
        echo "<p>Enrolled: " . date("F j, Y", strtotime(\$program['enrolled_at'])) . "</p>";
        echo "<a href='" . url("members/my_program.php?slug=" . urlencode(\$program['slug'])) . "' class='btn-primary'>" . (\$isComplete ? 'Review Program' : 'Continue Program') . "</a>";
        echo render_progress_bar(\$completedChallenges, \$totalChallenges);
        echo "</div>";
    }
    echo "</div>";
}

function fetch_dominant_info(int \$sub_id, PDO \$pdo): ?array {
    \$stmt = \$pdo->prepare("SELECT u.display_name, u.title FROM users u WHERE u.id = (SELECT dom_id FROM users WHERE id = ?)");
    \$stmt->execute([\$sub_id]);
    \$data = \$stmt->fetch(PDO::FETCH_ASSOC);
    return \$data ?: null;
}

function fetch_submissives(int \$dom_id, PDO \$pdo): array {
    \$stmt = \$pdo->prepare("SELECT id, display_name, email FROM users WHERE dom_id = ?");
    \$stmt->execute([\$dom_id]);
    return \$stmt->fetchAll(PDO::FETCH_ASSOC);
}

