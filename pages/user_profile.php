<?php
session_start();

// Initialize the game
if (!isset($_SESSION['number_to_guess'])) {
    $_SESSION['number_to_guess'] = rand(1, 100); // Random number between 1 and 100
    $_SESSION['attempts'] = 0; // Initialize attempts
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guess = (int)$_POST['guess'];
    $_SESSION['attempts']++;

    if ($guess < $_SESSION['number_to_guess']) {
        $message = "Too low! Try again.";
    } elseif ($guess > $_SESSION['number_to_guess']) {
        $message = "Too high! Try again.";
    } else {
        $message = "Congratulations! You've guessed the number {$_SESSION['number_to_guess']} in {$_SESSION['attempts']} attempts.";
        // Reset the game
        unset($_SESSION['number_to_guess']);
        unset($_SESSION['attempts']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guess the Number Game</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e0f7fa;
            color: #333;
            text-align: center;
            padding: 50px;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full height to center the game vertically */
            box-sizing: border-box;
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #00796b;
            text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.1);
        }

        p {
            font-size: 1.2em;
            color: #555;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        input[type="number"] {
            padding: 15px;
            width: 80px;
            font-size: 1.5em;
            border: 2px solid #00796b;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus {
            border-color: #004d40; /* Darker shade on focus */
        }

        button {
            padding: 15px 30px;
            font-size: 1.5em;
            color: white;
            background-color: #00796b;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #004d40;
            transform: translateY(-2px); /* Slight lift on hover */
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.2em;
            transition: opacity 0.3s;
        }

        /* Back button styles */
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1.2em;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #c62828; /* Darker shade on hover */
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            h1 {
                font-size: 2em;
            }

            input[type="number"], button, .back-button {
                font-size: 1.2em;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.5em;
            }

            input[type="number"], button, .back-button {
                width: 100%; /* Full width on small devices */
            }

            button, .back-button {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<h1>Guess the Number Game</h1>
<p>Guess a number between 1 and 100</p>

<form method="POST">
    <input type="number" name="guess" required>
    <button type="submit">Submit Guess</button>
</form>

<div class="message"><?php echo $message; ?></div>

<!-- Back button -->
<a href="friends.php" class="back-button">Back</a>

</body>
</html>
