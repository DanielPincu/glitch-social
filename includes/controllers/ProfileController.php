<?php
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/User.php';

class ProfileController {
    private $profileModel;
    private $postModel;
    private $userModel;

    public function __construct() {
        $this->profileModel = new Profile();
        $this->postModel = new Post();
        $this->userModel = new User();
    }

    // Get profile info and their posts
    public function showProfile($user_id) {
        $profile = $this->profileModel->getByUserId($user_id);
        $viewer_id = $_SESSION['user_id'] ?? null;

        // Prevent blocked users from viewing each other's profiles
        if ($viewer_id && $this->userModel->isUserBlocked($viewer_id, $user_id)) {
            return [
                'profile' => [
                    'id' => $user_id,
                    'username' => 'Blocked User',
                    'bio' => 'You have blocked this user.',
                    'location' => '',
                    'website' => '',
                    'avatar_url' => null
                ],
                'posts' => []
            ];
        }

        if ($viewer_id && $this->userModel->isUserBlocked($user_id, $viewer_id)) {
            return [
                'profile' => [
                    'id' => $user_id,
                    'username' => 'Blocked User',
                    'bio' => 'This user has blocked you.',
                    'location' => '',
                    'website' => '',
                    'avatar_url' => null
                ],
                'posts' => []
            ];
        }

        $posts = $this->postModel->getPostsByUser($user_id, $viewer_id);
        return ['profile' => $profile, 'posts' => $posts];
    }

    // Update profile for logged-in user
    public function updateProfile($user_id, $bio, $location, $website, $avatarPath = null) {
        // Always delegate saving to the model, including avatar if provided
        return $this->profileModel->save($user_id, $bio, $location, $website, $avatarPath);
    }
    // Follow or unfollow another user (toggle behavior)
    public function toggleFollow($follower_id, $user_id) {
        // Prevent following if blocked
        if ($this->userModel->isUserBlocked($follower_id, $user_id) || $this->userModel->isUserBlocked($user_id, $follower_id)) {
            return false; // cannot follow
        }

        if ($this->profileModel->isFollowing($follower_id, $user_id)) {
            $this->profileModel->unfollowUser($follower_id, $user_id);
            return false; // now unfollowed
        } else {
            $this->profileModel->followUser($follower_id, $user_id);
            return true; // now followed
        }
    }

    public function blockUserAndUnfollow($blocker_id, $blocked_id) {
        $this->userModel->blockUser($blocker_id, $blocked_id);
        $this->profileModel->unfollowUser($blocker_id, $blocked_id);
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