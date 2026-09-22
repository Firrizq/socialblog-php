<?php

declare(strict_types=1);

/**
 * Default Home Controller
 * Displays the main timeline feed of posts.
 */
class Home extends Controller
{
    private object $postModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post_model');
    }

    public function index(): void
    {
        $posts = $this->postModel->getFeedPosts();

        $data = [
            'title' => 'Timeline Feed - Social Blog',
            'posts' => $posts
        ];

        $this->view('home/index', $data);
    }
}
