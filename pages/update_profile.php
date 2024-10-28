<?php
include('../config/db.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $profile_picture = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);

        // Validate image upload
        if ($_FILES['profile_picture']['size'] > 50000) {
            die("Error: File too large.");
        }
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg'])) {
            die("Error: Only JPG, JPEG, and PNG files are allowed.");
        }

        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = basename($_FILES["profile_picture"]["name"]);
    }

    // Check if email is unique
    $email_check = $conn->prepare("SELECT * FROM users WHERE email = ? AND email != ?");
    $email_check->bind_param("ss", $new_email, $email);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        die("Error: Email already in use.");
    }

    // Prepare update query based on password input
    if (!empty($_POST['password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, profile_picture = ? WHERE email = ?");
        $stmt->bind_param("sssss", $new_username, $new_email, $new_password, $profile_picture, $email);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE email = ?");
        $stmt->bind_param("ssss", $new_username, $new_email, $profile_picture, $email);
    }

    if ($stmt->execute()) {
        $_SESSION['user'] = $new_email;
        $response = ['status' => 'success', 'message' => 'Profile updated successfully!'];
    } else {
        $response = ['status' => 'error', 'message' => 'Error: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();
    echo json_encode($response);
    exit();
}
?>

<?php include('../header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff3e0; /* Soft yellow background */
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .profile-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 600px;
        }

        h2.title {
            color: #ff4081; /* Bright pink */
            font-size: 2.5em;
            text-shadow: 2px 2px #ffcc00; /* Yellow shadow */
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .profile-image img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .btn-update {
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-update:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: #388e3c; /* Darker green */
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .error-message {
            color: #d32f2f; /* Darker red */
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .profile-form input {
                font-size: 14px;
                padding: 8px;
            }

            .btn-update {
                font-size: 14px;
                padding: 8px;
            }

            .profile-image img {
                width: 80px;
                height: 80px;
            }
        }

        @media (max-width: 480px) {
            .profile-container {
                padding: 15px;
            }

            .profile-form input {
                font-size: 12px;
                padding: 6px;
            }

            .btn-update {
                font-size: 12px;
                padding: 6px;
            }

            .profile-image img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <h2 class="title">Update Profile</h2>
            <div id="message"></div>
            <form method="POST" enctype="multipart/form-data" class="profile-form">
                <div class="form-group">
                    <label for="username">New Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">New Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="profile-image">
                    <img src="../images/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                </div>
                <div class="form-group">
                    <label for="profile_picture">New Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture">
                </div>
                <button type="submit" class="btn-update">Update Profile</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.profile-form');
            const messageDiv = document.getElementById('message');

            form.addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent default form submission

                const formData = new FormData(form);

                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        messageDiv.innerHTML = `<p class="success-message">${result.message}</p>`;
                        // Reload the page after 1 second
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        messageDiv.innerHTML = `<p class="error-message">${result.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>
