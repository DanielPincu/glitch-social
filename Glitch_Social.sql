-- reset database
DROP DATABASE IF EXISTS Glitch_Social;
CREATE DATABASE Glitch_Social CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
USE Glitch_Social;

SET default_storage_engine=INNODB;

-- users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, -- stored as a hash; 255 allows future algorithms beyond bcrypt's 60 chars
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  is_blocked TINYINT(1) NOT NULL DEFAULT 0,
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expires DATETIME DEFAULT NULL
);

-- profiles
CREATE TABLE profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  bio VARCHAR(255) DEFAULT NULL,
  avatar_url VARCHAR(255) DEFAULT NULL,
  location VARCHAR(50) DEFAULT NULL,
  website VARCHAR(50) DEFAULT NULL,
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
  content VARCHAR(500) DEFAULT NULL,
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
  content VARCHAR(255) NOT NULL,
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
  content VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_zion_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_zion_created (created_at),
  INDEX idx_zion_id (id)
);

-- terms acceptance
CREATE TABLE terms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content VARCHAR(1000) NOT NULL,
  updated_by INT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO terms (content, updated_by) VALUES
('Welcome to Glitch Social. By using this site, you agree to follow our community guidelines, respect others, and avoid harmful content. Your continued use of the platform constitutes acceptance of these terms.', NULL);

-- about website
CREATE TABLE about (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content VARCHAR(1000) NOT NULL,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO about (content, updated_by)
VALUES (
'Welcome to our platform. This page provides background information about our mission, goals, and the principles our community is built on.

We strive to create a safe, inclusive, and supportive environment where users can share, learn, and connect with one another.

Thank you for being part of our journey.',
NULL
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

CREATE TRIGGER after_follow_insert_notification
AFTER INSERT ON followers
FOR EACH ROW
BEGIN
  -- Prevent duplicate follow notifications
  IF NOT EXISTS (
      SELECT 1 FROM notifications
      WHERE user_id = NEW.user_id
        AND actor_id = NEW.follower_id
        AND type = 'follow'
  ) THEN
      INSERT INTO notifications (user_id, actor_id, post_id, type)
      VALUES (NEW.user_id, NEW.follower_id, NULL, 'follow');
  END IF;
END //

CREATE TRIGGER after_follow_delete_notification
AFTER DELETE ON followers
FOR EACH ROW
BEGIN
  DELETE FROM notifications
  WHERE user_id = OLD.user_id
    AND actor_id = OLD.follower_id
    AND type = 'follow';
END //

CREATE TRIGGER after_blocked_users_insert_delete_likes
AFTER INSERT ON blocked_users
FOR EACH ROW
BEGIN
  -- Delete any likes made by the blocker on the blocked user's posts
  DELETE FROM likes 
  WHERE user_id = NEW.blocker_id 
    AND post_id IN (SELECT id FROM posts WHERE user_id = NEW.blocked_id);

  -- Delete any likes made by the blocked user on the blocker's posts
  DELETE FROM likes 
  WHERE user_id = NEW.blocked_id 
    AND post_id IN (SELECT id FROM posts WHERE user_id = NEW.blocker_id);
END //

CREATE TRIGGER after_blocked_users_insert_delete_comments
AFTER INSERT ON blocked_users
FOR EACH ROW
BEGIN
  -- Delete comments made by the blocker on the blocked user's posts
  DELETE FROM comments 
  WHERE user_id = NEW.blocker_id 
    AND post_id IN (SELECT id FROM posts WHERE user_id = NEW.blocked_id);

  -- Delete comments made by the blocked user on the blocker's posts
  DELETE FROM comments 
  WHERE user_id = NEW.blocked_id 
    AND post_id IN (SELECT id FROM posts WHERE user_id = NEW.blocker_id);
END //
DELIMITER ;

-- VIEWS
-- Count of total users and total posts

CREATE OR REPLACE VIEW view_total_users_posts AS
SELECT
  (SELECT COUNT(*) FROM users) AS total_users,
  (SELECT COUNT(*) FROM posts) AS total_posts;


-- Count of total likes and total comments

CREATE OR REPLACE VIEW view_total_likes_comments AS
SELECT
  (SELECT COUNT(*) FROM likes) AS total_likes,
  (SELECT COUNT(*) FROM comments) AS total_comments;


-- Top 3 most active users based on posts, comments, and likes given

CREATE OR REPLACE VIEW view_top3_active_users AS
SELECT
    u.id AS user_id,
    u.username,
    pr.avatar_url,
    COUNT(DISTINCT p.id) AS posts,
    COUNT(DISTINCT c.id) AS comments,
    COUNT(DISTINCT l.id) AS likes_given,
    (
        COUNT(DISTINCT p.id) * 2 +
        COUNT(DISTINCT c.id) +
        COUNT(DISTINCT l.id) * 0.5
    ) AS activity_score
FROM users u
LEFT JOIN profiles pr ON pr.user_id = u.id
LEFT JOIN posts p ON p.user_id = u.id
LEFT JOIN comments c ON c.user_id = u.id
LEFT JOIN likes l ON l.user_id = u.id
GROUP BY u.id, pr.avatar_url
ORDER BY activity_score DESC
LIMIT 3;


-- Seed initial admin users Søren and Kim with relations and activity

-- Create users with automatic IDs
INSERT INTO users (username, email, password, is_admin)
VALUES
  ('Søren', 'smsj@easv.dk', '$2y$10$Nja1F6D0kR3zHcjAniZ.qOzTwHAlhljd.60DsFgBlQSfuRYTnWjTS', 1),
  ('Kim',   'kt@easv.dk', '$2y$10$qyk0iOv5t1Y.dly1TWqN7uTrlpFAFTBzObiP3HVo90jhVciwbjjLK', 1);

-- Capture user IDs
SET @soren_id = (SELECT id FROM users WHERE username = 'Søren' LIMIT 1);
SET @kim_id   = (SELECT id FROM users WHERE username = 'Kim'   LIMIT 1);

-- Make each user follow the other
INSERT INTO followers (user_id, follower_id)
VALUES
  (@soren_id, @kim_id),
  (@kim_id, @soren_id);

-- Each user posts a message in chat (zion_messages)
INSERT INTO zion_messages (user_id, content)
VALUES
  (@soren_id, 'Hi, I am Søren in the chat.'),
  (@kim_id,   'Hi, I am Kim in the chat.');

-- Each user creates a text-only post
INSERT INTO posts (user_id, content)
VALUES (@soren_id, 'Hello from Søren. This is my first post.');
SET @soren_post_id = LAST_INSERT_ID();

INSERT INTO posts (user_id, content)
VALUES (@kim_id, 'Hello from Kim. This is my first post.');
SET @kim_post_id = LAST_INSERT_ID();

-- Each user likes both their own post and the other user post
INSERT INTO likes (user_id, post_id)
VALUES
  (@soren_id, @soren_post_id),
  (@soren_id, @kim_post_id),
  (@kim_id,   @kim_post_id),
  (@kim_id,   @soren_post_id);

-- Each user comments on both posts
INSERT INTO comments (post_id, user_id, content)
VALUES
  (@soren_post_id, @soren_id, 'Comment by Søren on his own post.'),
  (@soren_post_id, @kim_id,   'Comment by Kim on Søren''s post.'),
  (@kim_post_id,   @kim_id,   'Comment by Kim on his own post.'),
  (@kim_post_id,   @soren_id, 'Comment by Søren on Kim''s post.');
