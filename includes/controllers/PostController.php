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
    public function getLikeCount($post_id) {
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
    // Get all posts (alias for admin use)
    public function getAllPosts() {
        return $this->getPosts();
    }
    // Update a post’s content and optionally its image
    public function updatePostContent($post_id, $new_content, $user_id, $new_image_path = null, $remove_image = false) {
        return $this->post->updateContent($post_id, $new_content, $user_id, $new_image_path, $remove_image);
    }
    // Get posts from users the current user follows
    public function getPostsFromFollowing($user_id) {
        return $this->post->getPostsFromFollowing($user_id);
    }

    // Add a comment to a post
    public function addComment($post_id, $user_id, $content) {
        return $this->post->addComment($post_id, $user_id, $content);
    }

    // Get comments for a post
    public function getComments($post_id) {
        return $this->post->getComments($post_id);
    }

    public function updateComment($comment_id, $user_id, $new_content) {
    return $this->post->updateComment($comment_id, $user_id, $new_content);
    }

    public function deleteComment($comment_id, $user_id) {
    return $this->post->deleteComment($comment_id, $user_id);
}
}

