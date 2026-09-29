<?php

declare(strict_types=1);

/**
 * Bookmarks Controller
 * Displays the list of saved/bookmarked posts for the authenticated user.
 */
class Bookmarks extends Controller
{
    private object $postModel;
    private object $interactionModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post_model');
        $this->interactionModel = $this->model('Interaction_model');
    }

    /**
     * Display all bookmarked posts of the logged-in user
     * Redirects to the unified History / Library Hub
     */
    public function index(): void
    {
        // Enforce authentication
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        header('Location: ' . BASEURL . '/history?tab=bookmarks');
        exit;
    }
}
