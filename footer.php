<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparent Footer</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f9f9f9;
        }

        footer {
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            padding: 10px 15px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            color: #fff;
            font-size: 0.9rem;
            box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px); /* Optional: adds a blur effect for a frosted glass look */
        }

        .end-message {
            margin: 0;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            footer {
                font-size: 0.85rem;
                padding: 8px 12px;
            }
        }

        @media (max-width: 480px) {
            footer {
                font-size: 0.75rem; /* Smaller font size */
                padding: 6px 8px;   /* Reduced padding */
            }
        }

        @media (max-width: 320px) {
            footer {
                font-size: 0.7rem;  /* Even smaller font size */
                padding: 4px 6px;   /* Further reduced padding */
            }
        }
    </style>
</head>
<body>
    <!-- Main content can go here -->

    <footer>
        <p class="end-message">Thank You for Visiting!</p>
    </footer>
</body>
</html>
