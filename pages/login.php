<?php
include('../config/db.php');
session_start();

function generateUniqueId($conn) {
    do {
        $id = rand(100000, 999999);
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    return $id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password']; // Keep it raw for verification

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p class='error'>Invalid email format!</p>";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Verify the password
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user'] = $email;
                    header("Location: home.php");
                    exit;
                } else {
                    echo "<p class='error'>Invalid email or password!</p>";
                }
            } else {
                echo "<p class='error'>Invalid email or password!</p>";
            }

            $stmt->close();
        }
    } elseif (isset($_POST['register'])) {
        $id = generateUniqueId($conn);
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8'); // Updated sanitization
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p class='error'>Invalid email format!</p>";
        } else {
            $email_check = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $email_check->bind_param("s", $email);
            $email_check->execute();
            $result = $email_check->get_result();

            if ($result->num_rows > 0) {
                echo "<p class='error'>Email already registered!</p>";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (id, username, email, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $id, $username, $email, $password);

                if ($stmt->execute()) {
                    echo "<p class='success'>Registration successful! You can now <a href='#' id='showLogin'>login</a>.</p>";
                } else {
                    echo "<p class='error'>Error: " . $stmt->error . "</p>";
                }

                $stmt->close();
            }

            $email_check->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container" id="container">
        <!-- Sign In Form -->
        <div class="form-container sign-in-container">
            <form method="POST">
                <h1>Sign In</h1>
                <span>or use your account</span>
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <a href="reset_password.php">Forgot your password?</a>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>

        <!-- Sign Up Form -->
        <div class="form-container sign-up-container">
            <form method="POST">
                <h1>Create Account</h1>
                <input type="text" name="username" placeholder="Name" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="register">Sign Up</button>
            </form>
        </div>

        <!-- Overlay -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us, please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start your journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
