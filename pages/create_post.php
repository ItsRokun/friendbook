<?php
session_start();
include('../config/db.php');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$user_email = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_id = $user_result->fetch_assoc()['id'];
$user_stmt->close();

if (!$user_id) {
    echo "Error: User ID not found.";
    exit();
}

// Handle post creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    $content = $_POST['content'];

    // Handle image upload
    $post_image = null;
    if (!empty($_FILES['post_image']['name'])) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["post_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file types (only allow JPEG, PNG)
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo "Error: Only JPG, JPEG, and PNG files are allowed.";
            exit();
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
            $post_image = basename($_FILES["post_image"]["name"]);
        } else {
            echo "Error moving uploaded file.";
            exit();
        }
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("iss", $user_id, $content, $post_image);

    // Execute and check for success
    if ($stmt->execute()) {
        header("Location: home.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<?php include('../header.php'); ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="stylesheet" href="../css/create_post.css"> <!-- Updated CSS file for this page -->
</head>

<div class="create-post-container">
    <h2>Create Post</h2>
    <form method="POST" enctype="multipart/form-data" class="post-form">
        <label>Content:</label>
        <textarea name="content" required></textarea>
        <label>Post Image:</label>
        <input type="file" name="post_image">
        <button type="submit">Post</button>
    </form>
</div>

<?php include('../footer.php'); ?>
