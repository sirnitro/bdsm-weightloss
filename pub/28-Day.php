<?php
// Include the configuration and common files
include('inc/config.php');
include('header.php');

// Get the current page from the URL parameter, default to 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Number of posts to display per page
$postsPerPage = 1;

// Calculate the offset
$offset = ($page - 1) * $postsPerPage;

// SQL query to fetch one post from the "28 Day Challenge" category
$sql = "SELECT Title, Content FROM posts 
        WHERE Category = '28 Day Challenge' AND Status = 'publish' 
        LIMIT $offset, $postsPerPage";
$result = $conn->query($sql);

// SQL query to count the total number of posts in the "28 Day Challenge" category
$totalPostsSql = "SELECT COUNT(*) as total FROM posts WHERE Category = '28 Day Challenge' AND Status = 'publish'";
$totalPostsResult = $conn->query($totalPostsSql);
$totalPosts = $totalPostsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalPages = ceil($totalPosts / $postsPerPage);

// Inline CSS for better styling
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
        color: #333;
    }
    .container {
        max-width: 900px;
        margin: 20px auto;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .header {
        text-align: center;
        background-color: #f77f00;
        padding: 10px;
        color: #fff;
        font-size: 24px;
        border-radius: 8px 8px 0 0;
    }
    .post-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .post-meta {
        font-size: 14px;
        color: #777;
        margin-bottom: 20px;
    }
    .content-section {
        margin-bottom: 20px;
    }
    .content-section h2 {
        font-size: 18px;
        border-bottom: 2px solid #f77f00;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }
    .content-section table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .content-section table th, .content-section table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .content-section table th {
        background-color: #f77f00;
        color: #fff;
    }
    .pagination {
        text-align: center;
        margin-top: 20px;
    }
    .pagination a {
        display: inline-block;
        margin: 0 5px;
        padding: 8px 12px;
        text-decoration: none;
        background-color: #f77f00;
        color: #fff;
        border-radius: 5px;
    }
    .pagination a:hover {
        background-color: #d06900;
    }
    .pagination a.disabled {
        background-color: #ddd;
        color: #aaa;
        pointer-events: none;
    }
</style>
";

// Display the content
echo "<div class='container'>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='header'>{$row['Title']}</div>";
        echo "<div class='post-meta'>January 28, 2021 | Master Stephen | 0 Comments</div>";
        echo "<div class='content-section'>
                <h2>Daily Meals</h2>
                <table>
                    <tr>
                        <th>Breakfast</th>
                        <th>Lunch</th>
                        <th>Supper</th>
                    </tr>
                    <tr>
                        <td><a href='#'>Egg Muffins</a></td>
                        <td><a href='#'>BTLA Lettuce Wraps</a></td>
                        <td><a href='#'>Chicken Curry Bell Pepper Sandwich</a></td>
                    </tr>
                </table>
              </div>";
        echo "<div class='content-section'>
                <h2>Daily Basic Exercises</h2>
                <table>
                    <tr>
                        <th>Exercise 1</th>
                        <th>Exercise 2</th>
                        <th>Exercise 3</th>
                    </tr>
                    <tr>
                        <td>Jumping Jacks</td>
                        <td>Squats</td>
                        <td>Planks</td>
                    </tr>
                </table>
              </div>";
        echo "<div class='content-section'>
                <h2>Daily BDSM Tasks</h2>
                <table>
                    <tr>
                        <th>Task 1</th>
                        <th>Task 2</th>
                        <th>Task 3</th>
                    </tr>
                    <tr>
                        <td><a href='#'>Anal Training</a></td>
                        <td><a href='#'>BJ Training</a></td>
                        <td><a href='#'>Orgasm Control</a></td>
                    </tr>
                </table>
              </div>";
        echo "<div class='content-section'>
                <h2>Daily Journal</h2>
                <p>{$row['Content']}</p>
              </div>";
    }
} else {
    echo "<p>No posts found in the '28 Day Challenge' category.</p>";
}

// Pagination controls
echo "<div class='pagination'>";
if ($page > 1) {
    echo "<a href='28-Day.php?page=" . ($page - 1) . "' class='prev'>Previous</a>";
} else {
    echo "<a class='prev disabled'>Previous</a>";
}
if ($page < $totalPages) {
    echo "<a href='28-Day.php?page=" . ($page + 1) . "' class='next'>Next</a>";
} else {
    echo "<a class='next disabled'>Next</a>";
}
echo "</div>";

echo "</div>"; // End of container

// Include the footer
include('footer.php');

// Close the connection
$conn->close();
?>

