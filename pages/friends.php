<?php
session_start();
include('../config/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$email = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$user_stmt) {
    die("Database prepare error: " . $conn->error);
}

$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user_data) {
    echo "<p>User not found.</p>";
    exit();
}

$user_id = $user_data['id'];

// Pagination setup
$limit = 10; // Number of users to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for SQL query

// Fetch all users except the logged-in user with pagination
$users_stmt = $conn->prepare("SELECT id, username, profile_picture FROM users WHERE id != ? LIMIT ?, ?");
if (!$users_stmt) {
    die("Database prepare error: " . $conn->error);
}

$users_stmt->bind_param("iii", $user_id, $offset, $limit);
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users_stmt->close();

// Get the total number of users for pagination
$total_users_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE id != ?");
$total_users_stmt->bind_param("i", $user_id);
$total_users_stmt->execute();
$total_users = $total_users_stmt->get_result()->fetch_assoc();
$total_users_stmt->close();

$total = $total_users['total'];
$total_pages = ceil($total / $limit); // Total pages calculation

// Define an array of colors for the user cards
$colors = ['#ffebee', '#e3f2fd', '#e8f5e9', '#fff3e0', '#ede7f6', '#fce4ec', '#e1f5fe', '#f3e5f5', '#fffde7', '#f1f8e9'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
    <link rel="stylesheet" href="../css/friends.css"> <!-- External CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px; /* Increase width for better horizontal layout */
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #ff4081;
            margin-bottom: 20px;
            font-size: 2.5em;
            text-shadow: 1px 1px rgba(255, 255, 255, 0.8);
        }

        .user-list {
            display: flex; /* Use flexbox for layout */
            flex-wrap: wrap; /* Allow wrapping to new lines */
            list-style: none;
            padding: 0;
            justify-content: space-between; /* Space items evenly */
        }

        .user-item {
            background: #fff; /* Default background */
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            width: calc(33.333% - 20px); /* Three cards per row with spacing */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            cursor: pointer;
            position: relative; /* For positioning the image */
        }

        .user-item:hover {
            transform: translateY(-3px); /* Elevate on hover */
        }

        .user-item img {
            width: 60px; /* Fixed width for profile picture */
            height: 60px; /* Fixed height for profile picture */
            border-radius: 50%; /* Make the image round */
            object-fit: cover;
            border: 2px solid #ff4081;
            position: absolute; /* Position the image */
            top: 15px; /* Space from the top */
            left: 15px; /* Space from the left */
        }

        .user-item h3 {
            margin: 10px 0 5px;
            color: #007bff;
            font-size: 1.5em;
            margin-left: 85px; /* Space for the image */
        }

        .load-more {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .load-more:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .user-item {
                width: calc(50% - 20px); /* Two cards per row on medium devices */
            }
        }

        @media (max-width: 480px) {
            .user-item {
                width: 100%; /* One card per row on small devices */
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<?php include('../header.php'); ?>

<div class="container">
    <h2>All Users</h2>
</div>

<div class="container">
    <?php if ($users_result->num_rows > 0): ?>
        <ul class="user-list">
            <?php $colorIndex = 0; ?>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <li class="user-item" style="background-color: <?php echo $colors[$colorIndex]; ?>;" onclick="location.href='user_profile.php?user_id=<?php echo $user['id']; ?>'">
                    <img src="<?php echo '../images/' . htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                </li>
                <?php 
                    $colorIndex = ($colorIndex + 1) % count($colors); // Cycle through the colors
                ?>
            <?php endwhile; ?>
        </ul>

        <?php if ($page < $total_pages): ?>
            <div class="load-more" onclick="loadMore(<?php echo $page + 1; ?>)">Load More</div>
        <?php endif; ?>

    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</div>

<?php include('../footer.php'); ?>

<script>
    function loadMore(page) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'friends.php?page=' + page, true);
        xhr.onload = function () {
            if (this.status === 200) {
                const container = document.querySelector('.user-list');
                const response = this.responseText;
                container.innerHTML += response; // Append new users to the list
                if (page >= <?php echo $total_pages; ?>) {
                    document.querySelector('.load-more').style.display = 'none'; // Hide load more button
                }
            }
        };
        xhr.send();
    }
</script>

</body>
</html>
