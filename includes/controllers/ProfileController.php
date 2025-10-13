<?php
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../models/Post.php';

class ProfileController {
    private $profileModel;
    private $postModel;

    public function __construct() {
        $this->profileModel = new Profile();
        $this->postModel = new Post();
    }

    // Get profile info and their posts
    public function showProfile($user_id) {
        $profile = $this->profileModel->getByUserId($user_id);
        $posts = $this->postModel->getPostsByUser($user_id);
        return ['profile' => $profile, 'posts' => $posts];
    }

    // Update profile for logged-in user
    public function updateProfile($user_id, $bio, $location, $website, $avatarFile = null) {
        $avatarPath = null;

        // Handle avatar upload
        if ($avatarFile && isset($avatarFile['error']) && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($avatarFile['type'], $allowedTypes)) {
                $uploadDir = __DIR__ . '/../../img/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $filename = time() . '_' . basename($avatarFile['name']);
                $destination = $uploadDir . $filename;
                if (move_uploaded_file($avatarFile['tmp_name'], $destination)) {
                    $avatarPath = 'img/avatars/' . $filename;
                }
            }
        }

        return $this->profileModel->save($user_id, $bio, $location, $website, $avatarPath);
    }
    // Follow or unfollow another user (toggle behavior)
    public function toggleFollow($follower_id, $user_id) {
        if ($this->profileModel->isFollowing($follower_id, $user_id)) {
            $this->profileModel->unfollowUser($follower_id, $user_id);
            return false; // now unfollowed
        } else {
            $this->profileModel->followUser($follower_id, $user_id);
            return true; // now followed
        }
    }

    // Check if a user is following another
    public function isFollowing($follower_id, $user_id) {
        return $this->profileModel->isFollowing($follower_id, $user_id);
    }

    // Get follower and following counts for a user
    public function getFollowCounts($user_id) {
        $followers = $this->profileModel->countFollowers($user_id);
        $following = $this->profileModel->countFollowing($user_id);
        return ['followers' => $followers, 'following' => $following];
    }

    public function getFollowingList($user_id) {
    return $this->profileModel->getFollowingList($user_id);
    }
}