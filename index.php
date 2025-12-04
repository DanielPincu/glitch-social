<?php
// Fix: Redirect index.php/ (with trailing slash) to index.php
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('#index\.php/+$#', $requestUri)) {
    header("Location: /index.php");
    exit;
}
require_once __DIR__ . '/autoload.php';

// 1. Start session early
$session = new Session();

// 2. Initialize database connection early
$database = new Database();
$pdo = $database->connect();

// 3. Resolve page and title
$page = $_GET['page'] ?? 'home';

// Skip notifications and UI on password reset pages
if ($page === 'reset_password' || $page === 'forgot_password') {
    $skipNotifications = true;
    $skipUI = true;
}
$title = '';

// 3. Instantiate models and controllers

// ==== MODELS ====
$userModel = new User($pdo);
$profileModel = new Profile($pdo);
$postModel = new Post($pdo);
$resetModel = new Reset($pdo);
$notificationModel = new Notification($pdo);
$zionChat = new ZionChat($pdo);
$termsModel = new Terms($pdo);

// ==== CONTROLLERS ====
$profileController = new ProfileController($pdo, $profileModel, $postModel, $userModel);
$userController = new UserController($pdo, $userModel, $profileController, $session);
$postController = new PostController($pdo, $postModel, $userModel);
$adminController = new AdminController($pdo, $userModel, $profileController, $session);
$ajaxController = new AjaxController($session, $userModel, $postModel, $notificationModel, $zionChat);

// Intercepts all AJAX actions (likes, comments, chat) and it must run before page router. 
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
        if ($session->isLoggedIn()) {
            header("Location: index.php");
            exit;
        }
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
        $controller = $profileController;
        $controller->handleProfile($session);
        break;

    case 'post':
        // Redirect to home with id parameter
        header("Location: index.php?page=home&id=" . urlencode($_GET['id'] ?? ''));
        exit;

case 'settings':
        $termsModel = new Terms($pdo);
        $termsController = new TermsController($termsModel, $session);
        $aboutModel = new About($pdo);
        $aboutController = new AboutController($aboutModel, $session, $userModel);
        $settingsController = new SettingsController(
            $pdo,
            $session,
            $userController,
            $profileController,
            $postController,
            $adminController,
            $termsController,
            $aboutController
        );
        $settingsController->show();
        break;

    case 'search':
        $userController->showSearchPage();
        break;

    case '404':
        $errorController = new ErrorController($pdo, $session);
        $errorController->show404();
        break;

    case 'home':
        $homeController = new HomeController($pdo, $session, $userController, $postController, $profileController);
        $homeController->showHome();
        break;

    case 'forgot_password':
        $resetController = new ResetController($pdo, $session, $resetModel);
        $resetController->showForgotPassword();
        break;

    case 'reset_password':
        $resetController = new ResetController($pdo, $session, $resetModel);
        $resetController->showResetPassword();
        break;

    case 'terms':
        $termsController = new TermsController($termsModel, $session);
        $termsController->show();
        break;

    case 'about':
        $aboutModel = new About($pdo);
        $aboutController = new AboutController($aboutModel, $session, $userModel);
        $aboutController->show();
        break;

    case 'notifications':
        $notificationController = new NotificationController($notificationModel);
        $notificationController->handleActions();
        break;

    case 'statistics':
        $statisticsModel = new Statistics($pdo);
        $controller = new StatisticsController($pdo);
        $controller->index();
        break;

    default:
        header("Location: index.php?page=404");
        exit;
}
