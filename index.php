<?php
// index.php - Форма с валидацией, Cookies и авторизацией
session_start();
require_once 'config.php';

// Функция для получения значения из Cookies или GET
function getValue($fieldName, $default = '') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET[$fieldName]) && $_GET[$fieldName] !== '') {
        return htmlspecialchars(trim($_GET[$fieldName]));
    }
    if (isset($_COOKIE['form_' . $fieldName])) {
        return htmlspecialchars($_COOKIE['form_' . $fieldName]);
    }
    return $default;
}

// Функция для получения ошибки из Cookies
function getError($fieldName) {
    if (isset($_COOKIE['error_' . $fieldName])) {
        return $_COOKIE['error_' . $fieldName];
    }
    return '';
}

// Функция для проверки наличия ошибки
function hasError($fieldName) {
    return isset($_COOKIE['error_' . $fieldName]);
}

// Если пользователь авторизован — загружаем его данные из БД
$userData = null;
$userLanguages = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $userData = $stmt->fetch();
    
    if ($userData) {
        // Загружаем языки пользователя
        $langStmt = $pdo->prepare("
            SELECT pl.name 
            FROM application_languages al
            JOIN programming_languages pl ON al.language_id = pl.id
            WHERE al.application_id = :id
        ");
        $langStmt->execute([':id' => $_SESSION['user_id']]);
        $userLanguages = $langStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Получаем значения полей (приоритет: GET > Cookie > БД)
$full_name = getValue('full_name', $userData['full_name'] ?? '');
$phone = getValue('phone', $userData['phone'] ?? '');
$email = getValue('email', $userData['email'] ?? '');
$birth_date = getValue('birth_date', $userData['birth_date'] ?? '');
$gender = getValue('gender', $userData['gender'] ?? '');

// Языки: из Cookie или БД
$languages = [];
if (isset($_COOKIE['form_languages']) && $_COOKIE['form_languages'] !== '') {
    $languages = explode(',', $_COOKIE['form_languages']);
} elseif (!empty($userLanguages)) {
    $languages = $userLanguages;
}
if (isset($_GET['languages']) && is_array($_GET['languages'])) {
    $languages = $_GET['languages'];
}

$biography = getValue('biography', $userData['biography'] ?? '');
$contract_accepted = (isset($_COOKIE['form_contract_accepted']) && $_COOKIE['form_contract_accepted'] == '1') ||
                     (isset($_GET['contract_accepted']) && $_GET['contract_accepted'] == '1') ||
                     ($userData && $userData['contract_accepted'] == 1);

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №5 — Форма с авторизацией</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-border {
            border: 2px solid #f44336 !important;
            background-color: #ffebee !important;
        }
        .field-error {
            color: #f44336;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: block;
        }
        .error-summary {
            background-color: #ffebee;
            border-left: 5px solid #f44336;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }
        .user-info {
            background-color: #e8f5e9;
            border-left: 5px solid #4caf50;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .user-info .logout-btn {
            background: #f44336;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .user-info .logout-btn:hover {
            background: #c62828;
        }
        .credentials-box {
            background: #f3e5f5;
            border: 2px dashed #7b1fa2;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .credentials-box .login-cred {
            font-weight: bold;
            color: #4a148c;
            font-size: 1.1rem;
        }
        header { background: linear-gradient(135deg, #7b1fa2, #4a148c); border-bottom: 5px solid #4caf50; }
        footer { background: #7b1fa2; border-top: 1px solid #4caf50; }
        .submit-btn { background: linear-gradient(135deg, #4caf50, #2e7d32); }
        .submit-btn:hover { background: linear-gradient(135deg, #388e3c, #1b5e20); }
        .action-btn { background: linear-gradient(135deg, #7b1fa2, #4a148c); }
        .action-btn:hover { background: linear-gradient(135deg, #6a1b9a, #38006b); }
        .action-btn.secondary { background: linear-gradient(135deg, #4caf50, #2e7d32); }
        .action-btn.secondary:hover { background: linear-gradient(135deg, #388e3c, #1b5e20); }
        .action-btn.admin { background: linear-gradient(135deg, #7b1fa2, #4a148c); }
        .action-btn.admin:hover { box-shadow: 0 6px 14px rgba(123, 31, 162, 0.4); }
        .form-group label { color: #4a148c; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #7b1fa2;
            box-shadow: 0 0 0 3px rgba(123, 31, 162, 0.2);
        }
        .required { color: #f44336; }
        .intro { border-left: 8px solid #7b1fa2; background-color: #f3e5f5; }
        .application-form { box-shadow: 0 8px 20px rgba(123, 31, 162, 0.1); }
        .login-container h2 { color: #4a148c; }
        .login-btn { background: linear-gradient(135deg, #7b1fa2, #4a148c); }
        .login-btn:hover { background: linear-gradient(135deg, #6a1b9a, #38006b); }
        .register-link a { color: #7b1fa2; }
    </style>
</head>
<body>
    

    <main class="container">
        <section class="intro">
            <p>Заполните форму ниже. При первой отправке генерируются логин и пароль. <br>
            <?php if (isset($_SESSION['user_id'])): ?>
                Вы авторизованы как <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
            <?php else: ?>
                <a href="login.php">Войдите</a>, чтобы