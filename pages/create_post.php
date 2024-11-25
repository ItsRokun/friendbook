<?php 
session_start();
include_once "../php/config.php";

// Check if the user is logged in by verifying the session variable 'unique_id'
if (!isset($_SESSION['unique_id'])) {
    header("location: ../login.php");
    exit();
}

// Fetch the logged-in user's information using 'unique_id'
$unique_id = $_SESSION['unique_id'];
$user_stmt = $conn->prepare("SELECT user_id, fname, email FROM users WHERE unique_id = ?");
$user_stmt->bind_param("s", $unique_id); // Adjusted to string if unique_id is string
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'] ?? null;
$username = $user_data['fname'] ?? null;
$user_stmt->close();

if (!$user_id) {
    echo "Error: User ID not found.";
    exit();
}

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8'); // Sanitize input
    $post_image = null;

    // Handle file upload
    if (!empty($_FILES['post_image']['name'])) {
        $target_dir = "../php/images/";
        $file_name = basename($_FILES["post_image"]["name"]);
        $unique_name = uniqid() . "_" . $file_name; // Generate a unique file name
        $target_file = $target_dir . $unique_name;

        // Basic validation for image upload
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_type, $allowed_types) && $_FILES['post_image']['size'] <= 2 * 1024 * 1024) {
            if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
                $post_image = $unique_name;
            } else {
                echo "Error: File upload failed.";
                exit();
            }
        } else {
            echo "Error: Invalid file type or file size exceeds 2MB.";
            exit();
        }
    }

    // Insert the post into the database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $content, $post_image);

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

<!-- Create Post Container -->
<div class="container mx-auto p-4 bg-white shadow-md rounded-lg mt-10">
    <h2 class="text-2xl font-semibold text-center mb-4">Create Post</h2>
    <p class="text-sm text-center text-gray-600 mb-4">You can't post an image without content</p>
    
    <!-- Post Form -->
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        
        <!-- Content Input -->
        <div>
            <label for="content" class="block text-gray-700 font-medium">Content:</label>
            <textarea name="content" id="content" required class="w-full p-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>
        
        <!-- Post Image Input -->
        <div>
            <label for="post_image" class="block text-gray-700 font-medium">Post Image (Optional):</label>
            <input type="file" name="post_image" id="post_image" accept="image/*" class="w-full p-2 text-sm text-gray-600 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-lg font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Post
            </button>
        </div>
    </form>
</div>

<?php include('../footer.php'); ?>
