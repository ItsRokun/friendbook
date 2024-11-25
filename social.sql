SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Table structure for `messages`
CREATE TABLE IF NOT EXISTS `messages` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `incoming_msg_id` int(255) NOT NULL,
  `outgoing_msg_id` int(255) NOT NULL,
  `msg` varchar(1000) NOT NULL,
  PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `users`
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `posts`
CREATE TABLE posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);


-- Table structure for `likes`
CREATE TABLE IF NOT EXISTS `likes` (
  `like_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `post_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`post_id`) ON DELETE CASCADE,
  UNIQUE KEY (`user_id`, `post_id`)  -- Ensure a user can like a post only once
);

-- Table structure for `comments`
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `post_id` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`post_id`) ON DELETE CASCADE
);

-- Optional: Create an index on frequently searched fields
CREATE INDEX idx_user_email ON users (email);
CREATE INDEX idx_post_user ON posts (user_id);
CREATE INDEX idx_like_user_post ON likes (user_id, post_id);
CREATE INDEX idx_comment_post ON comments (post_id);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
COMMIT;
