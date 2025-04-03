<?php
function render_program_progress($programs, $pdo, $user_id) {
    if (empty($programs)) {
        echo "<p>You haven’t enrolled in any programs yet.</p>";
        echo '<a href="' . BASE_URL . 'pages/programs.php" class="btn-primary">Browse Programs</a>';
        return;
    }

    echo '<div class="programs-grid">';
    foreach ($programs as $program) {
        // Count total challenges
        $totalStmt = $pdo->prepare("
            SELECT COUNT(*) FROM challenges c
            JOIN challenge_groups g ON c.group_id = g.id
            WHERE g.program_id = ?
        ");
        $totalStmt->execute([$program['id']]);
        $totalChallenges = $totalStmt->fetchColumn();

        // Count completed challenges
        $completedStmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_challenges uc
            JOIN challenges c ON uc.challenge_id = c.id
            JOIN challenge_groups g ON c.group_id = g.id
            WHERE uc.user_id = ? AND g.program_id = ? AND uc.completed_at IS NOT NULL
        ");
        $completedStmt->execute([$user_id, $program['id']]);
        $completedChallenges = $completedStmt->fetchColumn();

        $isComplete = ($totalChallenges > 0 && $completedChallenges >= $totalChallenges);
        $slug = htmlspecialchars($program['slug']);
        $name = htmlspecialchars($program['name']);
        $style = htmlspecialchars($program['style']);

        echo "<div class='program-card'>";
        echo "<h3>{$name}";
        if ($isComplete) {
            echo ' <span class="badge-complete">🏅 Completed</span>';
        }
        echo "</h3>";
        echo "<p class='style-label'>{$style}</p>";
        echo "<p>Enrolled: " . date("F j, Y", strtotime($program['enrolled_at'])) . "</p>";
        echo '<a href="' . BASE_URL . 'members/my_program.php?slug=' . urlencode($slug) . '" class="btn-primary">';
        echo $isComplete ? 'Review Program' : 'Continue Program';
        echo '</a>';

        echo render_progress_bar($completedChallenges, $totalChallenges);

        echo "</div>";
    }
    echo '</div>';
}
?>

