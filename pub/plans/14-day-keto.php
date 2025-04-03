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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keto 14-Day Meal Planner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .day {
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            text-align: left;
        }
        .day h3 {
            margin-top: 0;
        }
        .recipe {
            color: #007BFF;
            cursor: pointer;
            text-decoration: underline;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 1000;
        }
        .popup h2 {
            margin-top: 0;
        }
        .popup .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .search-bar {
            margin-top: 30px;
            text-align: center;
        }
        .search-bar input {
            padding: 10px;
            width: 300px;
            margin-right: 10px;
        }
        .search-bar button {
            padding: 10px 20px;
        }
        .search-results {
            margin-top: 20px;
        }
        .search-results h2 {
            text-align: center;
        }
    </style>
    <script>
        function showPopup(recipe) {
            const popup = document.getElementById('popup');
            document.getElementById('popup-title').innerText = recipe.Title;
            document.getElementById('popup-ingredients').innerText = recipe.Ingredients;
            document.getElementById('popup-instructions').innerText = recipe.Instructions;
            document.getElementById('popup-healthinfo').innerText = recipe.HealthInfo || 'N/A';
            document.getElementById('popup-source').innerHTML = `<a href="${recipe.Source}" target="_blank">Source</a>`;
            document.querySelector('.overlay').style.display = 'block';
            popup.style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }
    </script>
</head>
<body>
    <h1>Keto 14-Day Meal Planner</h1>
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
</body>
</html>

