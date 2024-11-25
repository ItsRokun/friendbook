<?php 
session_start();
include_once "../php/config.php";

// Check if the user is logged in by verifying the session variable 'unique_id'
if (!isset($_SESSION['unique_id'])) {
    header("location: ../login.php");
    exit();
}

// Fetch current user data
$unique_id = $_SESSION['unique_id'];
$user_stmt = $conn->prepare("SELECT user_id, fname, lname, email, img FROM users WHERE unique_id = ?");
$user_stmt->bind_param("i", $unique_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

if (!$user_data) {
    echo "<p>User ID not found.</p>";
    exit();
}

$user_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_fname = htmlspecialchars(trim($_POST['fname']), ENT_QUOTES, 'UTF-8');
    $new_lname = htmlspecialchars(trim($_POST['lname']), ENT_QUOTES, 'UTF-8');
    $new_email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $profile_picture = $user_data['img'];

    // Validate names
    if (!preg_match("/^[a-zA-Z\s]+$/", $new_fname) || !preg_match("/^[a-zA-Z\s]+$/", $new_lname)) {
        die(json_encode(['status' => 'error', 'message' => 'First and last names can only contain letters and spaces.']));
    }

    // Validate profile picture upload if a new one is provided
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "../php/images/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);

        // Validate image file size and type
        if ($_FILES['profile_picture']['size'] > 500000) {
            die(json_encode(['status' => 'error', 'message' => 'Error: File too large. Maximum file size is 500KB.']));
        }
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
            die(json_encode(['status' => 'error', 'message' => 'Error: Only JPG, JPEG, and PNG files are allowed.']));
        }

        // Move uploaded file to target directory
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = basename($_FILES["profile_picture"]["name"]); // Update the profile picture name
        } else {
            die(json_encode(['status' => 'error', 'message' => 'Error: Unable to upload file.']));
        }
    }

    // Check if the new email is unique
    $email_check = $conn->prepare("SELECT * FROM users WHERE email = ? AND email != ?");
    $email_check->bind_param("ss", $new_email, $user_data['email']);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        die(json_encode(['status' => 'error', 'message' => 'Error: Email already in use.']));
    }

    // Prepare the update query
    $stmt = !empty($_POST['password']) ? 
        $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ?, password = ?, img = ? WHERE email = ?") : 
        $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ?, img = ? WHERE email = ?");

    if (!empty($_POST['password'])) {
        $new_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $stmt->bind_param("ssssss", $new_fname, $new_lname, $new_email, $new_password, $profile_picture, $user_data['email']);
    } else {
        $stmt->bind_param("sssss", $new_fname, $new_lname, $new_email, $profile_picture, $user_data['email']);
    }

    // Execute the statement and provide feedback
    if ($stmt->execute()) {
        $_SESSION['user'] = $new_email; // Update session email
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<?php include_once "../header.php"; ?>

<style>
/* Base Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

.update-profile-container {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: auto;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"],
button {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

button {
    background-color: #5cb85c;
    color: white;
    cursor: pointer;
}

button:hover {
    background-color: #4cae4c;
}

/* Image Styles */
img {
    border-radius: 5px;
}

/* Responsive Styles */
@media (max-width: 600px) {
    .update-profile-container {
        padding: 15px;
    }

    h2 {
        font-size: 1.5em;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"],
    button {
        font-size: 14px;
    }
}
</style>

<div class="update-profile-container">
    <h2>Update Profile</h2>
    <form id="updateProfileForm" action="" method="POST" enctype="multipart/form-data">
        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($user_data['fname'], ENT_QUOTES, 'UTF-8'); ?>" required>
        
        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($user_data['lname'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="profile_picture">Current Profile Picture:</label>
        <?php if ($user_data['img']): ?>
            <img src="../images/<?php echo htmlspecialchars($user_data['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" style="width:100px;height:auto;">
        <?php endif; ?>
        <input type="file" id="profile_picture" name="profile_picture">

        <label for="password">New Password (leave blank if not changing):</label>
        <input type="password" id="password" name="password">
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password">
        <div id="responseMessage"></div>
        <button type="submit">Update Profile</button>
    </form>
    
</div>

<script>
    document.getElementById("updateProfileForm").onsubmit = function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        fetch("", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById("responseMessage");
            if (data.status === 'success') {
                messageDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
            } else {
                messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
        });
    };
</script>

<?php include_once "../footer.php"; ?>
