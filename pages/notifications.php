<?php
include('../config/db.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_id = $user_stmt->get_result()->fetch_assoc()['id'];

// Mark notifications as read
if (isset($_GET['mark_as_read'])) {
    $notification_id = $_GET['mark_as_read'];
    $update_stmt = $conn->prepare("UPDATE notifications SET read_status = TRUE WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("ii", $notification_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Fetch notifications
$notification_stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notification_stmt->bind_param("i", $user_id);
$notification_stmt->execute();
$notifications = $notification_stmt->get_result();
?>

<?php include('../header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fce4ec; /* Soft pink background */
            margin: 0;
            padding: 0;
        }

        h2 {
            color: #ff4081; /* Bright pink */
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
            text-shadow: 2px 2px #ffd740; /* Yellow shadow */
        }

        .notification {
            background-color: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); /* Softer shadow */
            max-width: 600px;
            border-left: 10px solid #ffd740; /* Bright yellow left border */
            transition: transform 0.3s;
        }

        .notification:hover {
            transform: scale(1.02); /* Slight zoom on hover */
        }

        .notification p {
            margin: 10px 0;
            color: #333;
        }

        .notification a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff4081; /* Bright pink */
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1); /* Button shadow */
        }

        .notification a:hover {
            background-color: #f50057; /* Darker pink */
            transform: translateY(-2px); /* Slight lift on hover */
        }
    </style>
</head>
<body>

<h2>Notifications</h2>
<?php
while ($notification = $notifications->fetch_assoc()) {
    echo "<div class='notification'>";
    echo "<p>" . htmlspecialchars($notification['message']) . "</p>";
    echo "<p>Status: " . ($notification['read_status'] ? 'Read' : 'Unread') . "</p>";
    echo "<p>Received on: " . $notification['created_at'] . "</p>";
    if (!$notification['read_status']) {
        echo "<a href='?mark_as_read=" . $notification['id'] . "'>Mark as Read</a>";
    }
    echo "</div>";
}
?>

<?php
$notification_stmt->close();
$conn->close();
?>

<?php include('../footer.php'); ?>
</body>
</html>
