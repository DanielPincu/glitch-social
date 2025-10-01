<?php
require_once __DIR__ . '/../models/Post.php';

class PostController {
    private $post;

    public function __construct() {
        $this->post = new Post();
    }

    // Create a new post with optional image file
    public function createPost($user_id, $content, $file = null) {
        return $this->post->create($user_id, $content, $file);
    }

    // Fetch all posts for displaying
    public function getPosts() {
        return $this->post->fetchAll();
    }
    // Like a post
    public function likePost($post_id, $user_id) {
        return $this->post->like($post_id, $user_id);
    }

    // Unlike a post
    public function unlikePost($post_id, $user_id) {
        return $this->post->unlike($post_id, $user_id);
    }

    // Check if user has liked a post
    public function hasLikedPost($post_id, $user_id) {
        return $this->post->hasLiked($post_id, $user_id);
    }

    // Get the number of likes for a post
    public function getPostLikes($post_id) {
        return $this->post->getLikeCount($post_id);
    }

    // Delete a post
    public function deletePost($post_id) {
        return $this->post->delete($post_id);
    }

    // Get all posts by a specific user
    public function getPostsByUser($user_id) {
        return $this->post->getPostsByUser($user_id);
    }

    // Delete a post by a specific user
    public function deletePostByUser($post_id, $user_id) {
        return $this->post->deleteByUser($post_id, $user_id);
    }
}