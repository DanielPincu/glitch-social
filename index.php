<?php
require_once __DIR__ . '/autoload.php';

// 1. Start session early
$session = new Session();

// 2. Initialize database connection early
$database = new Database();
$pdo = $database->connect();

// 3. Resolve page and title
$page = $_GET['page'] ?? 'home';
$title = '';

// 4. Instantiate controllers
$userController = new UserController($pdo);
$userController->handleFollowAction($session);

$postController = new PostController($pdo);

$ajaxController = new AjaxController($session, $userController, $postController, $pdo);
$ajaxController->handleRequest();





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
        $controller = new ProfileController($pdo);
        $controller->handleProfile($session);
        break;

    case 'post':
        // Redirect to home with id parameter
        header("Location: index.php?page=home&id=" . urlencode($_GET['id'] ?? ''));
        exit;

    case 'settings':
        $settingsController = new SettingsController($pdo);
        $settingsController->show();
        break;

    case 'search':
        $userController->showSearchPage();
        break;

    case '404':
        $errorController = new ErrorController();
        $errorController->show404();
        break;

    case 'home':
        $homeController = new HomeController($pdo);
        $homeController->showHome();
        break;

    case 'forgot_password':
        $passwordController = new PasswordController($session);
        $passwordController->showForgotPassword();
        break;

    case 'reset_password':
        $passwordController = new PasswordController($session);
        $passwordController->showResetPassword();
        break;

    case 'terms':
        $termsController = new TermsController($pdo);
        $termsController->show();
        break;

    case 'notifications':
        $notificationController = new NotificationController($pdo);
        $notificationController->handleActions();
        break;

    default:
        header("Location: index.php?page=404");
        exit;
}
