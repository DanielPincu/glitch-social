<?php
require_once __DIR__ . '/autoload.php';

$session = new Session();
$page = $_GET['page'] ?? 'home';
$title = '';

$userController = new UserController();
$postController = new PostController();

$ajaxController = new AjaxController($session, $userController, $postController);
$ajaxController->handleRequest();



// Handle follow/unfollow actions (now handled by UserController)
$userController->handleFollowAction($session);

switch ($page) {
    case 'login':
        // If already logged in, redirect to home
        if ($session->isLoggedIn()) {
            header("Location: index.php");
            exit;
        }
        $userController->handleLogin();
        break;

    case 'register':
        $userController->handleRegister();
        break;

    case 'logout':
        $session->logout();
        header("Location: index.php?page=login");
        exit;

    case 'profile':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        $controller = new ProfileController();
        $controller->handleProfile($session);
        break;

    case 'post':
        // Redirect to home with id parameter
        header("Location: index.php?page=home&id=" . urlencode($_GET['id'] ?? ''));
        exit;

    case 'settings':
        $settingsController = new SettingsController();
        $settingsController->show();
        break;

    case 'search':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        $searchResults = $userController->handleSearch();
        $title = "Search";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/search_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case '404':
        $title = "Page Not Found";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/404_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'home':
        $homeController = new HomeController();
        $homeController->showHome();
        break;

    case 'forgot_password':
        require_once __DIR__ . '/includes/controllers/PasswordController.php';
        $passwordController = new PasswordController($pdo);
        $title = "Forgot Password";
        $content = $passwordController->forgotPassword();
        require __DIR__ . '/includes/views/header.php';
        echo $content;
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'reset_password':
        require_once __DIR__ . '/includes/controllers/PasswordController.php';
        $passwordController = new PasswordController($pdo);
        $title = "Reset Password";
        $content = $passwordController->resetPassword();
        require __DIR__ . '/includes/views/header.php';
        echo $content;
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'terms':
        $title = "Terms and Regulations";
        require __DIR__ . '/includes/views/header.php';
        $termsController = new TermsController();
        $termsController->showTerms();
        require __DIR__ . '/includes/views/footer.php';
        break;

    default:
        header("Location: index.php?page=404");
        exit;
}
