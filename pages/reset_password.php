<?php
include('../config/db.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email and new password from the form
    $email = $_POST['email'];
    $new_password = md5($_POST['password']); // Using md5 for password encryption

    // Check if the email exists in the users table
    $email_check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $email_check->bind_param("s", $email);
    $email_check->execute();
    $result = $email_check->get_result();

    if ($result->num_rows > 0) {
        // If the email exists, update the password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);

        if ($stmt->execute()) {
            echo "<p class='message'>Password reset successfully!</p>";
        } else {
            echo "<p class='message'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        // If the email does not exist, show an error message
        echo "<p class='message'>Email not found!</p>";
    }

    $email_check->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,800">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #f6f5f7;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            margin: -20px 0 50px;
        }

        h1 {
            font-weight: bold;
            margin: 0;
        }

        p.message {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 20px 0;
        }

        a {
            color: #FF4B2B;
            font-size: 14px;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        button {
            border-radius: 20px;
            border: 1px solid #FF4B2B;
            background-color: #FF4B2B;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
        }

        button:active {
            transform: scale(0.95);
        }

        button:focus {
            outline: none;
        }

        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            width: 100%;
            max-width: 500px;
        }

        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Password Reset Form -->
    <form method="POST">
        <h1>Password Reset</h1>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required>
        
        <button type="submit">Reset Password</button>
        
        <p class="message">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</body>
</html>
