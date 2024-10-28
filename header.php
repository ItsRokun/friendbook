<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media</title>
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        header {
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            color: #fff;
            padding: 2px 10px; /* Reduced padding */
            height: 30px; /* Reduced height */
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease; /* Smooth transition for background color */
        }

        header.scrolled {
            background: rgba(0, 0, 0, 0.8); /* Darker background on scroll */
        }

        .logo a {
            color: #fff;
            text-decoration: none;
            font-size: 0.8em; /* Reduced font size */
            font-weight: bold;
        }

        nav {
            display: flex;
            align-items: center;
        }

        ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        li {
            position: relative;
            margin-left: 15px;
        }

        a {
            color: #fff;
            text-decoration: none;
            font-size: 0.7em; /* Reduced font size */
            line-height: 30px; /* Center items vertically */
            display: flex;
            align-items: center;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #444;
            padding: 2px 0; /* Reduced padding */
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .dropdown-menu a {
            display: block;
            color: #fff;
            padding: 2px 10px; /* Reduced padding */
            text-decoration: none;
        }

        .dropdown-menu a:hover {
            background: #555;
        }

        .dropdown-menu.show {
            display: block;
        }

        #menuIcon, #closeIcon {
            font-size: 12px; /* Smaller icon size */
            transition: opacity 0.3s ease; /* Smooth transition for icon change */
        }

        #closeIcon {
            display: none; /* Hide the close icon initially */
        }

        main {
            padding-top: 40px; /* Adjust based on your header height */
        }

        footer {
            background-color: #343a40; /* Dark background color */
            padding: 12px 0; /* Increased padding */
            border-top: 2px solid #495057; /* Darker border for separation */
        }

        footer nav {
            text-align: center;
        }

        .footer-nav {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center; /* Center align items horizontally */
            gap: 16px; /* Increased space between items */
        }

        .footer-nav li {
            display: inline;
        }

        .footer-nav a {
            text-decoration: none;
            color: #f8f9fa; /* Light text color */
            font-size: 16px; /* Larger font size */
            display: flex;
            align-items: center;
            gap: 10px; /* Space between icon and text */
            padding: 8px 16px; /* Increased padding */
            border-radius: 6px; /* More rounded corners */
            transition: background-color 0.3s, color 0.3s, transform 0.3s; /* Smooth transition with animation */
        }

        .footer-nav a:hover {
            color: #ffc107; /* Gold text color on hover */
            background-color: #495057; /* Darker background color on hover */
            transform: scale(1.08); /* Slightly larger scale on hover */
        }

        .footer-nav a i {
            transition: transform 0.3s;
        }

        .footer-nav a:hover i {
            transform: rotate(10deg); /* Slight rotation on hover */
        }
    </style>
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="/pages/home.php"><i class="fas fa-house-user"></i> Home</a></li>
            <li><a href="/pages/messages.php"><i class="fas fa-pencil-alt"></i>Messages</a></li>
            <li><a href="/pages/friends.php"><i class="fas fa-user-friends"></i>Friends</a></li>
            <li><a href="/pages/settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <li class="dropdown">
                <a href="#" id="menuToggle">
                    <i class="fas fa-bars" id="menuIcon"></i>
                    <i class="fas fa-times" id="closeIcon"></i> Menu
                </a>
                <div class="dropdown-menu">
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </li>
        </ul>
    </nav>
</header>

<main>
    <!-- Your page content goes here -->
</main>
<script>
    // Change header background on scroll
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Toggle menu visibility and icon
    document.getElementById('menuToggle').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default link behavior

        var menu = document.querySelector('.dropdown-menu');
        var menuIcon = document.getElementById('menuIcon');
        var closeIcon = document.getElementById('closeIcon');

        if (menu.classList.contains('show')) {
            menu.classList.remove('show');
            menuIcon.style.display = 'inline';
            closeIcon.style.display = 'none';
        } else {
            menu.classList.add('show');
            menuIcon.style.display = 'none';
            closeIcon.style.display = 'inline';
        }
    });
</script>
</body>
</html>
