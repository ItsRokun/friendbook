<?php
session_start();
include_once "../php/config.php";

// Set the timezone to Bangladesh
date_default_timezone_set('Asia/Dhaka');

// Check if the user is logged in by verifying the session variable 'unique_id'
if (!isset($_SESSION['unique_id'])) {
    header("location: ../login.php");
    exit();
}

// Fetch the logged-in user's information using 'unique_id'
$unique_id = $_SESSION['unique_id'];
$user_stmt = $conn->prepare("SELECT user_id, fname, lname, email FROM users WHERE unique_id = ?");
$user_stmt->bind_param("s", $unique_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Check if user data is found
if ($user_data) {
    $user_id = $user_data['user_id'];
    $username = htmlspecialchars($user_data['fname'] . ' ' . $user_data['lname']);
    $email = htmlspecialchars($user_data['email']);
} else {
    echo "<p>User ID not found.</p>";
    exit();
}
$user_stmt->close();

// Determine the current hour of the day
$current_hour = date('H');

// Set the greeting based on the time of day
$greeting = ($current_hour >= 5 && $current_hour < 12) ? "Good Morning" :
            (($current_hour >= 12 && $current_hour < 17) ? "Good Afternoon" : "Good Night");

// Function to handle Likes
function toggleLike($conn, $user_id, $post_id) {
    // Check if the user has already liked the post
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $liked = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($liked) {
        // Unlike the post
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
        return ['status' => 'unliked'];
    } else {
        // Like the post
        $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
        return ['status' => 'liked'];
    }
}

// Handle Like/Unlike
if (isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];

    if (!$user_id || !$post_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit();
    }

    $like_status = toggleLike($conn, $user_id, $post_id);

    // Get updated like count
    $stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $like_count = $stmt->get_result()->fetch_assoc()['like_count'];
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'new_status' => $like_status['status'],
        'new_like_count' => $like_count
    ]);
    exit();
}

// Fetch all posts with associated user information
$sql = "
    SELECT posts.*, CONCAT(users.fname, ' ', users.lname) AS username, users.img AS profile_picture
    FROM posts
    JOIN users ON posts.user_id = users.user_id
    ORDER BY posts.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body class="bg-gray-50 text-gray-800">

<?php include('../header.php'); ?>

<div class="container mx-auto p-4">

    <!-- Dynamic Greeting Message -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-semibold text-pink-500"><?php echo $greeting . ", " . $username; ?>!</h2>
    </div>

    <!-- Create Post Button -->
    <div class="text-center mb-8">
        <a href="create_post.php" class="inline-block bg-yellow-400 text-gray-800 font-bold py-3 px-6 rounded-lg text-lg shadow-md transition-transform transform hover:bg-yellow-500 hover:translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-pink-400 focus:ring-opacity-50"><i class="fas fa-pencil-alt"></i>Create Post
        </a>
    </div>

    <!-- Post List -->
    <div class="space-y-8">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $post_id = $row['post_id'];
                $username = htmlspecialchars($row['username']);
                $profile_picture = htmlspecialchars($row['profile_picture']);
                $post_content = htmlspecialchars($row['content']);
                $profile_picture_path = '../php/images/' . $profile_picture;

                // Count likes and comments
                $like_count_stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
                $like_count_stmt->bind_param("i", $post_id);
                $like_count_stmt->execute();
                $like_count = $like_count_stmt->get_result()->fetch_assoc()['like_count'];
                $like_count_stmt->close();

                $comment_count_stmt = $conn->prepare("SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = ?");
                $comment_count_stmt->bind_param("i", $post_id);
                $comment_count_stmt->execute();
                $comment_count = $comment_count_stmt->get_result()->fetch_assoc()['comment_count'];
                $comment_count_stmt->close();

                // Check if the current user has liked the post
                $like_status_stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
                $like_status_stmt->bind_param("ii", $user_id, $post_id);
                $like_status_stmt->execute();
                $liked = $like_status_stmt->get_result()->num_rows > 0;
                $like_status_stmt->close();

                // Determine the like button text
                $like_button_text = $liked ? "Unlike" : "Like";

                echo "<div class='bg-white p-6 rounded-lg shadow-md space-y-4' data-post-id='$post_id'>";

                // Display user profile picture
                echo "<div class='flex items-center space-x-4'>";
                echo "<img src='" . (file_exists($profile_picture_path) ? $profile_picture_path : '../php/images/default-logo.png') . "' class='w-12 h-12 rounded-full' alt='Profile Picture'>";
                echo "<h3 class='text-xl font-semibold'>" . $username . "</h3>";
                echo "</div>";

                // Display post content
                echo "<p class='text-lg text-gray-700'>" . $post_content . "</p>";

                // Display post image if it exists
                if (!empty($row['image'])) {
                    echo "<img src='../php/images/" . htmlspecialchars($row['image']) . "' class='w-full rounded-lg mt-4' alt='Post Image'>";
                }

                // Display like and comment count
                echo "<p class='mt-2'>Likes: <span class='font-semibold'>" . $like_count . "</span> | Comments: <span class='font-semibold'>" . $comment_count . "</span></p>";

                // Like/Unlike form
                echo "<form method='POST' class='like-form mt-4' data-post-id='" . $post_id . "'>";
                echo "<input type='hidden' name='like_post_id' value='" . $post_id . "'>";
                echo "<button type='submit' class='bg-pink-500 text-white py-2 px-4 rounded-lg hover:bg-pink-600 transition-colors'>$like_button_text</button>";
                echo "</form>";

                // Comment form
                echo "<form method='POST' class='comment-form mt-4' data-post-id='" . $post_id . "'>";
                echo "<input type='hidden' name='comment_post_id' value='" . $post_id . "'>";
                echo "<textarea name='comment' class='w-full p-2 border rounded-lg' placeholder='Add a comment...'></textarea>";
                echo "<button type='submit' class='bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition-colors mt-2'>Comment</button>";
                echo "</form>";

                // View button
                echo "<div class='flex justify-end mt-4'>";
                echo "<a href='view.php?post_id=" . $post_id . "' class='bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors'>View</a>";
                echo "</div>";
                echo "</div>"; // End of post div

            }
        } else {
            echo "<p>No posts available.</p>";
        }
        ?>
    </div>

</div>

<script src="../js/home.js"></script>
</body>
</html>
