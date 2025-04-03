<?php
include 'inc/config.php';

// Fetch current day from URL or default to 1
$day = isset($_GET['day']) ? (int)$_GET['day'] : 1;
if ($day < 1 || $day > 28) {
    $day = 1;
}

// Fetch data for the current day
$sql = "SELECT * FROM fitness_plan WHERE day = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $day);
$stmt->execute();
$result = $stmt->get_result();
$dayData = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>28-Day Fitness Program - Day <?php echo $day; ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>28-Day Fitness Program</h1>
        <h2>Day <?php echo $day; ?></h2>
        <div class="content">
            <h3>Workout</h3>
            <button onclick="showModal('workout', '<?php echo addslashes($dayData['workout']); ?>')">View Workout</button>

            <h3>Keto Meal</h3>
            <button onclick="showModal('meal', '<?php echo addslashes($dayData['meal']); ?>')">View Recipe</button>

            <h3>BDSM Tasks</h3>
            <ul>
                <li><?php echo htmlspecialchars($dayData['bdsm_task1']); ?></li>
                <li><?php echo htmlspecialchars($dayData['bdsm_task2']); ?></li>
                <li><?php echo htmlspecialchars($dayData['bdsm_task3']); ?></li>
            </ul>

            <h3>Journal</h3>
            <form action="save_journal.php" method="POST">
                <textarea name="journal" rows="5" placeholder="Write your thoughts..."><?php echo htmlspecialchars($dayData['journal']); ?></textarea>
                <input type="hidden" name="day" value="<?php echo $day; ?>">
                <button type="submit">Save Journal</button>
            </form>
        </div>
        <div class="pagination">
            <?php if ($day > 1): ?>
                <a href="?day=<?php echo $day - 1; ?>">Previous</a>
            <?php endif; ?>
            <?php if ($day < 28): ?>
                <a href="?day=<?php echo $day + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modal-text"></p>
        </div>
    </div>

    <script>
        function showModal(type, content) {
            $('#modal-text').text(content);
            $('#modal').show();
        }
        function closeModal() {
            $('#modal').hide();
        }
    </script>
</body>
</html>

