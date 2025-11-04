-- reset database
DROP DATABASE IF EXISTS Glitch_Social;
CREATE DATABASE Glitch_Social CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
USE Glitch_Social;

SET default_storage_engine=INNODB;

-- users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  is_blocked TINYINT(1) NOT NULL DEFAULT 0,
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expires DATETIME DEFAULT NULL
);

-- profiles
CREATE TABLE profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  bio TEXT DEFAULT NULL,
  avatar_url VARCHAR(255) DEFAULT NULL,
  location VARCHAR(100) DEFAULT NULL,
  website VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- followers
CREATE TABLE followers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  follower_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY user_follower (user_id, follower_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_followers_user_follower (user_id, follower_id)
);

-- posts
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  content TEXT DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  visibility ENUM('public','private','followers') NOT NULL DEFAULT 'public',
  is_pinned TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_posts_user_pinned_created_at (user_id, is_pinned, created_at)
);

-- comments
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_comments_post_created_at (post_id, created_at)
);

-- likes
CREATE TABLE likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY user_post (user_id, post_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  INDEX idx_likes_user_post (user_id, post_id)
);

-- blocked users
CREATE TABLE blocked_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  blocker_id INT NOT NULL,
  blocked_id INT NOT NULL,
  FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_block_pair (blocker_id, blocked_id),
  INDEX idx_blocked_users_blocker_blocked (blocker_id, blocked_id)
);

-- notifications
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,    -- recipient
  actor_id INT NOT NULL,   -- who triggered it
  post_id INT DEFAULT NULL,
  type ENUM('post','follow') NOT NULL DEFAULT 'post',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  INDEX idx_notifications_user_id (user_id),
  INDEX idx_notifications_actor_id (actor_id),
  INDEX idx_notifications_post_id (post_id),
  INDEX idx_notifications_type (type)
);


CREATE TABLE zion_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_zion_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_zion_created (created_at),
  INDEX idx_zion_id (id)
);

-- triggers
DELIMITER //
CREATE TRIGGER after_post_insert_notification
AFTER INSERT ON posts
FOR EACH ROW
BEGIN
  -- Only notify followers if visibility allows (public or followers)
  IF NEW.visibility IN ('public', 'followers') THEN
    INSERT INTO notifications (user_id, actor_id, post_id, type)
    SELECT f.follower_id, NEW.user_id, NEW.id, 'post'
    FROM followers f
    WHERE f.user_id = NEW.user_id;
  END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER after_follow_insert_notification
AFTER INSERT ON followers
FOR EACH ROW
BEGIN
  INSERT INTO notifications (user_id, actor_id, post_id, type)
  VALUES (NEW.user_id, NEW.follower_id, NULL, 'follow');
END //
DELIMITER ;

-- views
CREATE OR REPLACE VIEW view_post_notifications AS
SELECT 
  n.id            AS notification_id,
  u.username      AS recipient,
  a.username      AS actor,
  n.type,
  p.content       AS post_content,
  p.visibility    AS post_visibility,
  n.post_id,
  n.user_id,
  n.actor_id
FROM notifications n
JOIN users u ON n.user_id = u.id
JOIN users a ON n.actor_id = a.id
LEFT JOIN posts p ON n.post_id = p.id
WHERE n.type = 'post'
ORDER BY n.id DESC;

CREATE OR REPLACE VIEW view_follow_notifications AS
SELECT 
  n.id       AS notification_id,
  u.username AS recipient,
  a.username AS actor,
  n.type,
  n.user_id,
  n.actor_id
FROM notifications n
JOIN users u ON n.user_id = u.id
JOIN users a ON n.actor_id = a.id
WHERE n.type = 'follow'
ORDER BY n.id DESC;

CREATE OR REPLACE VIEW view_all_posts AS
SELECT 
  p.id          AS post_id,
  p.user_id,
  u.username,
  p.content,
  p.image_path,
  p.visibility,
  p.created_at
FROM posts p
JOIN users u   ON u.id = p.user_id
LEFT JOIN profiles pr ON pr.user_id = p.user_id
ORDER BY p.created_at DESC;