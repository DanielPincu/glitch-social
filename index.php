<?php
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

// 4. Instantiate controllers
$userModel = new User($pdo);
$profileModel = new Profile($pdo);
$postModel = new Post($pdo);
$passwordModel = new Password($pdo);
$notificationModel = new Notification($pdo);
$zionChat = new ZionChat($pdo);
$termsModel = new Terms($pdo);
$profileController = new ProfileController($pdo, $profileModel, $postModel, $userModel, null);
$userController = new UserController($pdo, $userModel, $profileController);
$profileController->setUserController($userController);
$userController->handleFollowAction($session);

$postController = new PostController($pdo, $postModel, $userModel);
$adminController = new AdminController($pdo, $userModel, $profileController);

$ajaxController = new AjaxController($session, $userModel, $postModel, $notificationModel, $zionChat);
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
        $controller = $profileController;
        $controller->handleProfile($session);
        break;

    case 'post':
        // Redirect to home with id parameter
        header("Location: index.php?page=home&id=" . urlencode($_GET['id'] ?? ''));
        exit;

    case 'settings':
        $settingsController = new SettingsController($pdo, $session, $userController, $postController, $adminController);
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
        $passwordController = new PasswordController($pdo, $session, $passwordModel);
        $passwordController->showForgotPassword();
        break;

    case 'reset_password':
        $passwordController = new PasswordController($pdo, $session, $passwordModel);
        $passwordController->showResetPassword();
        break;

    case 'terms':
        $termsController = new TermsController($termsModel, $session);
        $termsController->show();
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
