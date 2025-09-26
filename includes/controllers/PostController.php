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
}