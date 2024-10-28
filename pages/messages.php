<?php
session_start();
include('../config/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the user's ID
$email = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if (!$user_data) {
    echo "User not found.";
    exit();
}

$user_id = $user_data['id'];

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';

    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, message, created_at) VALUES (?, ?, NOW())");
    if (!$stmt) {
        die("SQL prepare error: " . $conn->error);
    }
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
    exit(); // Stop further script execution after sending the message
}

// Fetch all messages
$stmt = $conn->prepare("SELECT m.id, m.sender_id, m.message, m.created_at, u.username 
                         FROM messages m 
                         JOIN users u ON m.sender_id = u.id 
                         ORDER BY m.created_at ASC");
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}

$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include('../header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMQ/6aGtxvEX8h6ogYkJ9fBr18nY5f62N/zF8W" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .chat-container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden; /* Ensure content doesn't overflow */
        }
        .refresh-icon {
            position: fixed; /* Fixed positioning for popup effect */
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
            z-index: 1000; /* Ensure it stays above other content */
        }
        .refresh-icon:hover {
            background-color: #0056b3;
        }
        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 15px;
            background: #e9e9e9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-message {
            padding: 10px;
            border-radius: 5px;
            background: #f1f1f1;
            position: relative;
        }
        .chat-message strong {
            display: block;
            color: #007bff;
        }
        .chat-message small {
            color: #888;
            font-size: 0.8em;
            position: absolute;
            bottom: 5px;
            right: 10px;
        }
        .chat-form {
            padding: 10px;
            background: #f8f8f8;
            display: flex;
        }
        .chat-form input[type="text"] {
            flex: 1;
            min-width: 200px; /* Minimum width for mobile devices */
            margin-right: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }
        .chat-form button {
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .chat-form button:hover {
            background: #0056b3;
        }
        @media (max-width: 600px) {
            .chat-container {
                width: 95%;
                margin: 20px auto;
            }
            .chat-messages {
                height: 300px;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="chat-container">
    <div id="chatMessages" class="chat-messages">
        <?php foreach ($messages as $message): ?>
            <div class="chat-message">
                <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                <small><?php echo date('Y-m-d H:i:s', strtotime($message['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <form id="chatForm" class="chat-form">
        <input type="text" id="messageInput" name="message" placeholder="Type a message" required>
        <button type="submit">Send</button>
    </form>

    <button class="refresh-icon" onclick="refreshPage()">
        <i class="fas fa-sync-alt"></i>
    </button>
</div>

<script>
function refreshPage() {
    location.reload(); // Reload the current page
}

$(document).ready(function() {
    // Handle sending a message
    $('#chatForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'messages.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function() {
                $('#messageInput').val('');
                fetchMessages(); // Refresh messages after sending
            },
            error: function(xhr, status, error) {
                console.error("Message send error: ", error);
            }
        });
    });

    // Fetch messages every 2 seconds
    function fetchMessages() {
        $.ajax({
            url: 'messages.php', // Using the same file for fetching
            method: 'GET',
            success: function(data) {
                const messages = JSON.parse(data);
                $('#chatMessages').empty();
                messages.forEach(message => {
                    $('#chatMessages').append(`
                        <div class="chat-message">
                            <strong>${message.username}:</strong>
                            <p>${message.message.replace(/\\n/g, '<br>')}</p>
                            <small>${new Date(message.created_at).toLocaleString()}</small>
                        </div>
                    `);
                });
                $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight); // Auto scroll
            },
            error: function(xhr, status, error) {
                console.error("Fetch messages error: ", error);
            }
        });
    }

    setInterval(fetchMessages, 2000); // Fetch messages every 2 seconds
    fetchMessages(); // Initial fetch
});
</script>

<?php include('../footer.php'); ?>
