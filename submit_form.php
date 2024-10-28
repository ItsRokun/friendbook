<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration file
include 'config/db.php';

// Initialize response array
$response = array('status' => 'error', 'message' => 'Something went wrong.');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if required fields are set and not empty
    if (isset($_POST['name']) && !empty($_POST['name']) &&
        isset($_POST['email']) && !empty($_POST['email']) &&
        isset($_POST['message']) && !empty($_POST['message'])) {
        
        // Retrieve and sanitize form data
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $message = htmlspecialchars(trim($_POST['message']));

        // Check if the $conn variable is set
        if (!$conn) {
            $response['message'] = "Database connection not established.";
            echo json_encode($response);
            exit();
        }

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO contact_form (name, email, message) VALUES (?, ?, ?)");

        if ($stmt === false) {
            $response['message'] = "Error preparing statement: " . $conn->error;
            echo json_encode($response);
            exit();
        }

        // Bind parameters
        $stmt->bind_param("sss", $name, $email, $message);

        // Execute the statement
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = "Message sent successfully!";
        } else {
            $response['message'] = "Error: " . $stmt->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        $response['message'] = "All fields are required.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Output the response as JSON
echo json_encode($response);
?>
