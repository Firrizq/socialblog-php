<?php

declare(strict_types=1);

/**
 * Default Home Controller
 */
class Home extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'CCIT Social Blog - Home',
            'message' => 'Welcome to the Social Blogging Platform!'
        ];

        $this->view('home/index', $data);
    }
}
