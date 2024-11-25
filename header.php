<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media</title>
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Custom styles */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        main {
            padding-top: 60px; /* Adjust based on your header height */
        }
    </style>
</head>
<body>
<header class="bg-black bg-opacity-50 text-white p-2 fixed w-full top-0 left-0 z-50 shadow transition duration-300 ease-in-out">
    <nav class="flex justify-between items-center">
        <ul class="flex space-x-4">
            <li><a href="/pages/home.php" class="block text-white p-2 hover:bg-gray-600"><i class="fas fa-newspaper"></i></a></li>
            <li><a href="/users.php" class="block text-white p-2 hover:bg-gray-600"><i class="fa-solid fa-comments"></i></a></li>
            <li><a href="/pages/settings.php" class="block text-white p-2 hover:bg-gray-600"><i class="fas fa-bars"></i></a></li>
            <li><a href="/php/logout.php" class="block text-white p-2 hover:bg-gray-600"> <i class="fas fa-sign-out-alt"></i></a></li>
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
            header.classList.add('bg-opacity-80'); // Darker background on scroll
        } else {
            header.classList.remove('bg-opacity-80');
        }
    });

    // Toggle menu visibility and icon
    document.getElementById('menuToggle').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default link behavior

        var menu = document.querySelector('.dropdown-menu');
        var menuIcon = document.getElementById('menuIcon');
        var closeIcon = document.getElementById('closeIcon');

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menuIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        }
    });
</script>
</body>
</html>
