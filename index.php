<?php

require 'vendor/autoload.php';

use App\AuthService;
use App\View;

session_start();

$auth = new AuthService();
$action = $_GET['action'] ?? '';

//проверка автоматической авторизации по cookie remember_token
if (empty($_SESSION['user_id']) && !empty($_COOKIE[AuthService::REMEMBER_COOKIE])) {
    $rememberedUser = $auth->loginByRememberToken($_COOKIE[AuthService::REMEMBER_COOKIE]);
    if ($rememberedUser) {
        $_SESSION['user_id'] = $rememberedUser['id'];
        $auth->sendSettingsCookies($rememberedUser);
    }
}

$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $currentUser = $auth->findUserById((int)$_SESSION['user_id']);
}

//получение настроек оформления из cookie или значения по умолчанию
$bgColor = $_COOKIE[AuthService::BG_COLOR_COOKIE] ?? '#20324a';
$fontColor = $_COOKIE[AuthService::FONT_COLOR_COOKIE] ?? '#ffffff';

//вспомогательная функция флеш-сообщений
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

//маршрутизация действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $login = trim($_POST['login'] ?? '');
        $pwd = $_POST['password'] ?? '';
        $pwdConfirm = $_POST['password_confirm'] ?? '';
        $bg = $_POST['bg_color'] ?? '#20324a';
        $font = $_POST['font_color'] ?? '#ffffff';

        if (strlen($login) < 3 || strlen($pwd) < 6 || $pwd !== $pwdConfirm) {
            $_SESSION['flash_error'] = 'Проверьте данные (пароль от 6 симв., пароли должны совпадать).';
            header('Location: ?action=register');
            exit;
        }

        try {
            $user = $auth->register($login, $pwd, $bg, $font);
            $_SESSION['user_id'] = $user['id'];
            $auth->sendSettingsCookies($user);
            $auth->issueRememberToken((int)$user['id']);
            header('Location: /');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ?action=register');
            exit;
        }
    }

    if ($action === 'login') {
        $login = trim($_POST['login'] ?? '');
        $pwd = $_POST['password'] ?? '';
        $user = $auth->authenticate($login, $pwd);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $auth->sendSettingsCookies($user);
            $auth->issueRememberToken((int)$user['id']);
            header('Location: /');
            exit;
        }

        $_SESSION['flash_error'] = 'Неверный логин или пароль';
        header('Location: ?action=login');
        exit;
    }

    if ($action === 'update_settings' && $currentUser) {
        $bg = $_POST['bg_color'] ?? '#20324a';
        $font = $_POST['font_color'] ?? '#ffffff';
        $auth->updateSettings((int)$currentUser['id'], $bg, $font);
        $auth->sendSettingsCookies($currentUser);
        header('Location: /');
        exit;
    }

    if ($action === 'logout') {
        if (!empty($_COOKIE[AuthService::REMEMBER_COOKIE])) {
            $auth->revokeRememberToken($_COOKIE[AuthService::REMEMBER_COOKIE]);
        }
        $auth->clearAuthCookies();
        session_destroy();
        header('Location: ?action=login');
        exit;
    }
}

//отображение страниц
if ($currentUser) {
    View::render('profile', [
        'user' => $currentUser,
        'bgColor' => $bgColor,
        'fontColor' => $fontColor,
        'flashError' => $flashError,
        'flashSuccess' => $flashSuccess,
    ]);
} elseif ($action === 'register') {
    View::render('register', [
        'bgColor' => $bgColor,
        'fontColor' => $fontColor,
        'flashError' => $flashError,
    ]);
} else {
    View::render('login', [
        'bgColor' => $bgColor,
        'fontColor' => $fontColor,
        'flashError' => $flashError,
        'flashSuccess' => $flashSuccess,
    ]);
}