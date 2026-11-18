# 🌐 SocialBlog PHP

![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![Docker](https://img.shields.io/badge/Docker-Supported-2496ED?style=for-the-badge&logo=docker)
![Architecture](https://img.shields.io/badge/Architecture-MVC-success?style=for-the-badge)

A full-fledged, custom-built MVC PHP web application that bridges the gap between a traditional blogging platform and a modern social media network. 

**SocialBlog PHP** allows users to create rich-text articles, compress and upload videos natively, interact with other users' content (likes, comments, reposts), explore trending tags, and manage personal reading histories and bookmarks.

---

## 📖 Table of Contents

1. [Project Overview](#-project-overview)
2. [Key Features](#-key-features)
3. [Architecture & Tech Stack](#-architecture--tech-stack)
4. [Project Structure](#-project-structure)
5. [Prerequisites](#-prerequisites)
6. [Installation Guide](#-installation-guide)
    - [Method 1: Docker (Recommended)](#method-1-docker-recommended)
    - [Method 2: Manual Setup (XAMPP/MAMP/LAMP)](#method-2-manual-setup-xamppmamplamp)
7. [Configuration](#-configuration)
8. [Client-Side Utilities](#-client-side-utilities)
9. [Continuous Integration / Deployment](#-continuous-integration--deployment)
10. [Roadmap & Future Development](#-roadmap--future-development)
11. [Contributing](#-contributing)
12. [License](#-license)

---

## 🚀 Project Overview

SocialBlog PHP is built from the ground up using a bespoke **Model-View-Controller (MVC)** framework in PHP. It is designed to be lightweight, fast, and completely independent of bulky third-party backend frameworks like Laravel or CodeIgniter, making it highly customizable and excellent for understanding core web application mechanics.

The application securely routes all traffic through a single point of entry (`public/index.php`) and uses robust `.htaccess` configurations to protect application logic and sensitive files.

---

## ✨ Key Features

### 👤 User Management & Authentication
* **Secure Registration & Login:** Password hashing and secure session management.
* **Profile Management:** Edit profile details, avatars, and bios.
* **Hover Cards:** Interactive user profile hover cards (`hover_card.php`) across the application for quick previews of user stats.

### 📝 Content Creation (Blogging)
* **Custom Editorial Editor:** Includes a robust, custom-built rich-text editor (`editorial-editor.js`) for writing beautifully formatted blog posts.
* **Media Uploads & Video Compression:** Natively supports client-side video compression using a dedicated utility (`video-compressor.js`) before hitting the server to save bandwidth and storage.
* **Edit History/Notes:** Keep track of changes and revisions on posts.

### 💬 Social Interactions
* **Dynamic Interaction System:** Like, comment, and engage with posts.
* **Reposting:** Share other users' posts onto your own timeline (similar to Retweets).
* **Bookmarks:** Save posts privately to read later.
* **Nested Comments:** Detailed comment threading (`comment_detail.php`).

### 🔍 Discovery & Feed
* **Explore Page:** Discover new posts, users, and trending content.
* **Tagging System:** Categorize posts with tags and browse specific tags.
* **Reading History:** Automatically tracks the posts a user has recently read.

### 🔔 Real-time Alerts
* **Notifications Engine:** Get notified when someone likes your post, comments, or reposts your content.

---

## 🛠 Architecture & Tech Stack

* **Backend:** PHP (Vanilla, Custom MVC Architecture)
* **Database:** MySQL / MariaDB (Managed via `app/core/Database.php` PDO wrapper)
* **Frontend:** HTML5, CSS3 (Custom styling/Templates), Vanilla JavaScript
* **Routing:** Apache Mod Rewrite (`.htaccess`)
* **Containerization:** Docker & Docker Compose
* **CI/CD:** GitHub Actions (`deploy.yml`)

---

## 📂 Project Structure

```text
socialblog-php/
├── .github/workflows/     # GitHub Actions CI/CD pipelines (deploy.yml)
├── app/                   # Core Application Directory (Protected)
│   ├── config/            # Database and App configuration files
│   ├── controllers/       # Application logic (Auth, Post, Profile, etc.)
│   ├── core/              # Base MVC classes (App, Controller, Database)
│   ├── models/            # Database interaction layers
│   └── views/             # HTML Templates and UI components
├── public/                # Document Root (Publicly accessible)
│   ├── js/                # Client-side JavaScript (Editor, Compressor)
│   ├── .htaccess          # URL Rewriting to index.php
│   └── index.php          # Application Entry Point
├── Dockerfile             # Docker image configuration
├── docker-compose.yml     # Multi-container orchestration
└── .htaccess              # Root directory protection
```

### Deep Dive into the MVC
* **`app/core/App.php`**: The routing engine that parses the URL and calls the appropriate Controller and Method.
* **`app/core/Controller.php`**: The base controller that handles loading views and models.
* **`app/core/Database.php`**: A robust PDO wrapper for executing secure, parameterized SQL queries.

---

## 📋 Prerequisites

Before you begin, ensure you have met the following requirements:
* **PHP:** Version 7.4 or 8.x
* **Database:** MySQL 5.7+ or MariaDB 10.2+
* **Web Server:** Apache (with `mod_rewrite` enabled) or Nginx
* **Docker:** (Optional but recommended) Docker Desktop or Docker Engine + Docker Compose

---

## ⚙️ Installation Guide

### Method 1: Docker (Recommended)

The easiest way to get the project running locally without messing with your local PHP/Apache setup is via Docker.

1. **Clone the repository:**
   
```bash
   git clone https://github.com/yourusername/socialblog-php.git
   cd socialblog-php
```

2. **Configure Environment:**
   Copy the example config file and update database credentials for Docker (usually host is `db` or `mysql` depending on your `docker-compose.yml`).
   
```bash
   cp app/config/config.example.php app/config/config.php
```

3. **Build and Run the Containers:**
   
```bash
   docker-compose up -d --build
```

4. **Access the application:**
   Open your browser and navigate to `http://localhost:8000` (or the port specified in your `docker-compose.yml`).

### Method 2: Manual Setup (XAMPP/MAMP/LAMP)

1. **Clone the repository** into your web server's document root (e.g., `htdocs` or `/var/www/html/`).
   
```bash
   git clone https://github.com/yourusername/socialblog-php.git
```

2. **Set up the Database:**
   * Open phpMyAdmin or your preferred SQL client.
   * Create a new database named `socialblog_db`.
   * Import the provided `.sql` schema (if available in the root, or run migrations/seeders if applicable).

3. **Configuration:**
   
```bash
   cp app/config/config.example.php app/config/config.php
```
   Open `app/config/config.php` and update the constants:
   
```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'socialblog_db');
   define('BASEURL', 'http://localhost/socialblog-php/public');
```

4. **Enable `mod_rewrite`:**
   Ensure Apache's `mod_rewrite` is enabled. The `.htaccess` files in the root and `public/` folder are critical for routing all requests through `public/index.php`.

5. **Permissions:**
   Ensure the `public/uploads/` directory (or wherever media is stored) has the correct write permissions.

---

## 🛠 Client-Side Utilities

This project features some advanced vanilla JavaScript utilities located in `public/js/`:

### 1. Editorial Editor (`editorial-editor.js`)
A custom WYSIWYG (What You See Is What You Get) editor tailored for blogging. It handles text formatting, blockquotes, inline images, and clean HTML output formatting to ensure that content created by users looks beautiful and consistent on the frontend.

### 2. Video Compressor (`video-compressor.js`)
A unique client-side video processing tool. Instead of sending large raw video files directly to the PHP server (which can cause timeouts and exceed `upload_max_filesize`), this script uses the browser's capabilities to compress and optimize video files before the POST request is ever sent.

---

## 🚢 Continuous Integration / Deployment

This project includes automated deployment capabilities via **GitHub Actions**.

* **Workflow File:** `.github/workflows/deploy.yml`
* **Triggers:** Pushes to the `main` or `master` branch.
* **Process:** The pipeline is configured to automatically test (if tests are defined) and deploy the codebase to a production server via FTP/SSH, ensuring that your live site is always up-to-date with your repository.

To use this, configure your repository secrets in GitHub (`Settings` -> `Secrets and variables` -> `Actions`) based on the variables required in the `deploy.yml` file (e.g., `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`).

---

## 🗺 Roadmap & Future Development

- [ ] **REST API Implementation:** Separate the view logic and build a JSON API to support a future mobile application.
- [ ] **WebSockets for Real-Time Chat:** Integrate Ratchet or a Node.js microservice for real-time direct messaging.
- [ ] **Advanced Moderation Tools:** Add an Admin dashboard to manage reported posts and shadowban malicious users.
- [ ] **OAuth2 Integration:** Allow users to log in using Google, Twitter, or GitHub.

---

## 🤝 Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---
*Built with ❤️ for the love of PHP and clean MVC architecture.*