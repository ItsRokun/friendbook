<?php
session_start();
include_once "config.php";

if (isset($_SESSION['unique_id'])) {
    $logout_id = $_SESSION['unique_id']; // Get logout ID from session

    // Check if logout_id is set before using it
    if (isset($logout_id)) {
        // Secure the logout_id
        $logout_id = mysqli_real_escape_string($conn, $logout_id);

        // Execute the query to update the user status
        $sql = "UPDATE users SET status = 'Offline' WHERE unique_id = '{$logout_id}'";
        if (mysqli_query($conn, $sql)) {
            // If query executed successfully, destroy session and redirect to login page
            session_destroy();
            header("Location: ../login.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        // Handle the case where logout_id is not set
        echo "Error: No logout ID provided.";
    }
} else {
    // Redirect to login if session is not set
    header("Location: ../login.php");
    exit();
}
?>
