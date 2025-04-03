<?php
// Include the database connection script
require_once '../inc/config.php';

// Create the SQL query to fetch all rows from the recipes table
//$sql = "SELECT * FROM recipes WHERE Title LIKE '%Keto seafood%'";
$sql = "SELECT DISTINCT Title FROM recipes";


// Execute the query
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    // Display the data in an HTML table
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Content</th>
            <th>Ingredients</th>
            <th>Instructions</th>
            <th>Meal</th>
            <th>Other</th>
          </tr>";

    // Loop through each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Title'], 0, 255)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Content'], 0, 255)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Ingredients'], 0, 255)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Instructions'], 0, 255)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Meal'], 0, 255)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Other'], 0, 255)) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No recipes found in the database.";
}

// Close the database connection
$conn->close();
?>

