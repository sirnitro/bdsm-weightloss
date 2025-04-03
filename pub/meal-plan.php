<?php
// Database connection
$host = 'localhost'; // Database host
$dbname = 'bdsmweightloss'; // Database name
$username = 'root'; // Database username
$password = 'Old.No.7'; // Database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}

// Fetch recipes grouped by meal type
$query = "
    SELECT RecipeID, Title, Meal, Ingredients, Instructions, HealthInfo, Source 
    FROM Recipes 
    WHERE Meal IN ('Breakfast', 'Lunch', 'Dinner', 'Snack') 
    ORDER BY Meal, RAND()";
$stmt = $pdo->prepare($query);
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize recipes by meal type
$meals = ['Breakfast' => [], 'Lunch' => [], 'Dinner' => [], 'Snack' => []];
foreach ($recipes as $recipe) {
    if (isset($meals[$recipe['Meal']])) {
        $meals[$recipe['Meal']][] = $recipe;
    }
}

// Generate a 14-day planner
$planner = [];
for ($day = 1; $day <= 14; $day++) {
    $planner[$day] = [
        'Breakfast' => array_shift($meals['Breakfast']),
        'Lunch' => array_shift($meals['Lunch']),
        'Dinner' => array_shift($meals['Dinner']),
        'Snack' => array_shift($meals['Snack']),
    ];
}

// Handle search
$searchResults = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = "
        SELECT RecipeID, Title, Meal, Ingredients, Instructions, HealthInfo, Source 
        FROM Recipes 
        WHERE Title LIKE :search OR Ingredients LIKE :search";
    $searchStmt = $pdo->prepare($searchQuery);
    $searchStmt->execute(['search' => "%$searchTerm%"]);
    $searchResults = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Generate HTML for the planner
?>
<?php include_once "inc/header.php"; ?>

    <h2 class="main-title-h1">Keto 14-Day Meal Planner</h2>
    <div class="calendar">
        <?php foreach ($planner as $day => $dayMeals): ?>
            <div class="day">
                <h3>Day <?= $day ?></h3>
                <?php foreach ($dayMeals as $meal => $recipe): ?>
                    <div class="recipe" onclick='showPopup(<?= json_encode($recipe) ?>)'>
                        <?= $meal ?>: <?= $recipe['Title'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="overlay" onclick="closePopup()"></div>
    <div id="popup" class="popup">
        <span class="close" onclick="closePopup()">&#10005;</span>
        <h2 id="popup-title"></h2>
        <p><strong>Ingredients:</strong> <span id="popup-ingredients"></span></p>
        <p><strong>Instructions:</strong> <span id="popup-instructions"></span></p>
        <p><strong>Health Info:</strong> <span id="popup-healthinfo"></span></p>
        <p id="popup-source"></p>
    </div>

    <div class="search-bar">
        <form method="GET">
            <input type="text" name="search" placeholder="Search for recipes..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if (!empty($searchResults)): ?>
        <div class="search-results">
            <h2>Search Results</h2>
            <ul>
                <?php foreach ($searchResults as $result): ?>
                    <li class="recipe" onclick='showPopup(<?= json_encode($result) ?>)'>
                        <?= $result['Title'] ?> (<?= $result['Meal'] ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php include_once "inc/footer.php"; ?>

