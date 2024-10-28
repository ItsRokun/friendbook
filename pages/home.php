<?php
// Start the session
session_start();
include('../config/db.php');

// Check if the user is logged in and email is stored in the session
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's email and ID
$email = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id, username, profile_picture FROM users WHERE email = ?");
$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['id'];
$username = $user_data['username'];
$user_stmt->close();

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found.']);
    exit();
}

// Handle like button submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];

    // Check if the user has already liked this post
    $check_stmt = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already liked this post.']);
    } else {
        $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Post liked!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
        }

        $stmt->close();
    }
    exit();
}

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_post_id'])) {
    $post_id = $_POST['comment_post_id'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Comment added!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/home.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        /* Back to Top Button */
        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: none;
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            padding: 15px;
            font-size: 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            z-index: 1000;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        #backToTop:hover {
            background: linear-gradient(45deg, #ff4b2b, #ff416c);
            transform: scale(1.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .feedback-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
        }

        .create-post-button {
            display: inline-block;
            background-color: #ff4b2b;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        /* Modal Styles */
        #notificationModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            z-index: 1001;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }

        #notificationModal button {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include('../header.php'); ?>

<div class="welcome-message">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
</div>

<div class="create-post-container">
    <a href="create_post.php" class="create-post-button">Create Post</a>
</div>

<div class="post-list">
    <?php
    // Display all posts with username and profile picture
    $result = $conn->query("
        SELECT posts.*, users.username, users.profile_picture 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC
    ");

    while ($row = $result->fetch_assoc()) {
        $post_id = $row['id'];

        // Count likes
        $like_count_stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
        $like_count_stmt->bind_param("i", $post_id);
        $like_count_stmt->execute();
        $like_count_result = $like_count_stmt->get_result();
        $like_count = $like_count_result->fetch_assoc()['like_count'];
        $like_count_stmt->close();

        // Count comments
        $comment_count_stmt = $conn->prepare("SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = ?");
        $comment_count_stmt->bind_param("i", $post_id);
        $comment_count_stmt->execute();
        $comment_count_result = $comment_count_stmt->get_result();
        $comment_count = $comment_count_result->fetch_assoc()['comment_count'];
        $comment_count_stmt->close();

        echo "<div class='post'>";

        // Display user profile picture
        $profile_picture = htmlspecialchars($row['profile_picture']);
        echo "<img src='../images/" . $profile_picture . "' alt='Profile Picture' class='user-logo'>";

        // Display username and post content
        echo "<h3>" . htmlspecialchars($row['username']) . "</h3>";
        echo "<p>" . htmlspecialchars($row['content']) . "</p>";

        // Display post image if any
        if (!empty($row['image'])) {
            echo "<img src='../images/" . htmlspecialchars($row['image']) . "' class='post-image'>";
        }

        // Display like and comment count
        echo "<p>Likes: " . htmlspecialchars($like_count) . "</p>";
        echo "<p>Comments: " . htmlspecialchars($comment_count) . "</p>";

        // Like and comment forms
        echo "<form method='POST' class='like-form'>";
        echo "<input type='hidden' name='like_post_id' value='" . htmlspecialchars($post_id) . "'>";
        echo "<button type='submit' class='like-button'>Like</button>";
        echo "</form>";

        echo "<form method='POST' class='comment-form'>";
        echo "<input type='hidden' name='comment_post_id' value='" . htmlspecialchars($post_id) . "'>";
        echo "<textarea name='comment' required></textarea>";
        echo "<button type='submit' class='comment-button'>Comment</button>";
        echo "</form>";

        echo "<a href='view.php?post_id=" . htmlspecialchars($post_id) . "' class='view-button'>View</a>";
        echo "</div>";
    }
    ?>
</div>

<!-- Modal for notifications -->
<div id="notificationModal">
    <span id="modalMessage"></span>
    <button onclick="document.getElementById('notificationModal').style.display='none';">Close</button>
</div>

<?php include('../footer.php'); ?>

<button id="backToTop">↑</button>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function handleFormSubmission(form, action) {
        const formData = new FormData(form);

        fetch('home.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            handleSuccessResponse(action, form, data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    document.querySelectorAll('.like-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            handleFormSubmission(form, 'like');
        });
    });

    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            handleFormSubmission(form, 'comment');
        });
    });

    // Back to top functionality
    const backToTopButton = document.getElementById('backToTop');
    window.onscroll = function() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    };

    backToTopButton.onclick = function() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    };
});

function handleSuccessResponse(action, form, data) {
    if (data.status === 'success') {
        document.getElementById('modalMessage').innerText = data.message;
        document.getElementById('notificationModal').style.display = 'block';
        // Reload the page after a successful like or comment
        setTimeout(() => {
            location.reload();
        }, 2000); // Reload after 2 seconds
    } else {
        document.getElementById('modalMessage').innerText = data.message;
        document.getElementById('notificationModal').style.display = 'block';
    }
}
</script>
</body>
</html>
