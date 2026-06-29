<?php
session_start();
require_once 'config.php';

// Если пользователь авторизован — загружаем его данные
$userData = null;
$userLanguages = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $userData = $stmt->fetch();
    
    if ($userData) {
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

// Получаем значения полей
$full_name = $_GET['full_name'] ?? $userData['full_name'] ?? '';
$phone = $_GET['phone'] ?? $userData['phone'] ?? '';
$email = $_GET['email'] ?? $userData['email'] ?? '';
$birth_date = $_GET['birth_date'] ?? $userData['birth_date'] ?? '';
$gender = $_GET['gender'] ?? $userData['gender'] ?? '';
$biography = $_GET['biography'] ?? $userData['biography'] ?? '';
$contract_accepted = isset($_GET['contract_accepted']) || ($userData && $userData['contract_accepted'] == 1);

// Языки
$languages = [];
if (isset($_GET['languages']) && is_array($_GET['languages'])) {
    $languages = $_GET['languages'];
} elseif (!empty($userLanguages)) {
    $languages = $userLanguages;
}

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

// Проверяем ошибки из cookies
$errors = [];
$error_fields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'languages', 'contract_accepted'];
foreach ($error_fields as $field) {
    if (isset($_COOKIE['error_' . $field])) {
        $errors[$field] = $_COOKIE['error_' . $field];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error { border: 2px solid red !important; }
        .error-text { color: red; font-size: 0.8rem; }
        .success { background: #e8f5e9; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; }
        .error-summary { background: #ffebee; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; border-left: 4px solid red; }
        .credentials-box { background: #f3e5f5; border: 2px dashed #7b1fa2; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        .user-info { background: #e8f5e9; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .logout-btn { background: red; color: white; padding: 0.5rem 1rem; border-radius: 20px; text-decoration: none; }
        header { background: linear-gradient(135deg, #7b1fa2, #4a148c); border-bottom: 5px solid #4caf50; padding: 2rem 0; color: white; text-align: center; }
        footer { background: #7b1fa2; color: white; text-align: center; padding: 1rem 0; margin-top: 2rem; border-top: 5px solid #4caf50; }
        .container { max-width: 800px; margin: 0 auto; padding: 0 20px; }
        .application-form { background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 0.5rem; color: #4a148c; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #ce93d8; border-radius: 10px; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #7b1fa2; outline: none; }
        .required { color: red; }
        .radio-group { display: flex; gap: 2rem; padding: 0.5rem 0; }
        .checkbox-label { display: flex; align-items: center; gap: 0.5rem; }
        .submit-btn { background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; border: none; padding: 1rem 2rem; font-size: 1.1rem; border-radius: 40px; cursor: pointer; width: 100%; font-weight: bold; }
        .submit-btn:hover { transform: scale(1.02); }
        .action-buttons { display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap; }
        .action-btn { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: white; padding: 0.75rem 1.5rem; border-radius: 40px; text-decoration: none; font-weight: bold; }
        .action-btn:hover { transform: scale(1.05); }
        select[multiple] { min-height: 150px; }
        select[multiple] option:checked { background: #7b1fa2; color: white; }
        .student-info { background: rgba(255,255,255,0.2); display: inline-block; padding: 0.4rem 1.2rem; border-radius: 30px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="student-info">Лабораторная работа №6</p>
        </div>
    </header>

    <main class="container">
        <!-- Информация о пользователе -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-info">
            <span>👤 Вы вошли как <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">🚪 Выйти</a>
        </div>
        <?php endif; ?>

        <!-- Логин/пароль для нового пользователя -->
        <?php if (isset($_GET['new_login']) && isset($_GET['new_password'])): ?>
        <div class="credentials-box">
            <p><strong>✅ Ваши данные для входа сохранены!</strong></p>
            <p>🔑 Логин: <strong><?php echo htmlspecialchars($_GET['new_login']); ?></strong></p>
            <p>🔒 Пароль: <strong><?php echo htmlspecialchars($_GET['new_password']); ?></strong></p>
            <p style="font-size: 0.8rem; color: #666;">* Сохраните их!</p>
        </div>
        <?php endif; ?>

        <!-- Ошибки -->
        <?php if (!empty($errors)): ?>
        <div class="error-summary">
            <strong>❌ Исправьте ошибки:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- ФОРМА -->
        <form action="process.php" method="GET" class="application-form">
            <input type="hidden" name="edit_id" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">

            <!-- 1. ФИО -->
            <div class="form-group">
                <label>ФИО <span class="required">*</span></label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" class="<?php echo isset($errors['full_name']) ? 'error' : ''; ?>">
                <?php if (isset($errors['full_name'])): ?>
                    <div class="error-text"><?php echo $errors['full_name']; ?></div>
                <?php endif; ?>
            </div>

            <!-- 2. Телефон -->
            <div class="form-group">
                <label>Телефон <span class="required">*</span></label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="<?php echo isset($errors['phone']) ? 'error' : ''; ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="error-text"><?php echo $errors['phone']; ?></div>
                <?php endif; ?>
            </div>

            <!-- 3. Email -->
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="<?php echo isset($errors['email']) ? 'error' : ''; ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-text"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>

            <!-- 4. Дата рождения -->
            <div class="form-group">
                <label>Дата рождения <span class="required">*</span></label>
                <input type="date" name="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>" class="<?php echo isset($errors['birth_date']) ? 'error' : ''; ?>">
                <?php if (isset($errors['birth_date'])): ?>
                    <div class="error-text"><?php echo $errors['birth_date']; ?></div>
                <?php endif; ?>
            </div>

            <!-- 5. Пол -->
            <div class="form-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" <?php echo $gender == 'male' ? 'checked' : ''; ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo $gender == 'female' ? 'checked' : ''; ?>> Женский</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <div class="error-text"><?php echo $errors['gender']; ?></div>
                <?php endif; ?>
            </div>

            <!-- 6. Языки -->
            <div class="form-group">
                <label>Любимый язык программирования <span class="required">*</span></label>
                <select name="languages[]" multiple size="6" class="<?php echo isset($errors['languages']) ? 'error' : ''; ?>">
                    <?php foreach ($allowed_languages as $lang): ?>
                        <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $languages) ? 'selected' : ''; ?>>
                            <?php echo $lang; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Зажмите Ctrl для выбора нескольких</small>
                <?php if (isset($errors['languages'])): ?>
                    <div class="error-text"><?php echo $errors['languages']; ?></div>
                <?php endif; ?>
            </div>

            <!-- 7. Биография -->
            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="5"><?php echo htmlspecialchars($biography); ?></textarea>
            </div>

            <!-- 8. Чекбокс -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1" <?php echo $contract_accepted ? 'checked' : ''; ?>>
                    С контрактом ознакомлен(а) <span class="required">*</span>
                </label>
                <?php if (isset($errors['contract_accepted'])): ?>
                    <div class="error-text"><?php echo $errors['contract_accepted']; ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">✅ Сохранить</button>
        </form>

        <!-- Кнопки -->
        <div class="action-buttons">
            <a href="list.php" class="action-btn">📋 Анкеты</a>
            <a href="admin.php" class="action-btn">👑 Админ-панель</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №6 | Май 2026</p>
        </div>
    </footer>
</body>
</html>