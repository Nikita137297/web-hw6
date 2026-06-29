<?php
session_start();
require_once 'config.php';

// Получаем данные пользователя если авторизован
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

// Значения полей
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

// Ошибки из cookies
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f3e5f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        header { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: white; padding: 30px 0; text-align: center; border-bottom: 5px solid #4caf50; }
        header h1 { font-size: 2rem; }
        .subtitle { background: rgba(255,255,255,0.2); padding: 5px 20px; border-radius: 20px; display: inline-block; margin-top: 10px; }
        footer { background: #7b1fa2; color: white; text-align: center; padding: 15px 0; margin-top: 30px; border-top: 5px solid #4caf50; }
        .form-box { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 30px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #4a148c; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 15px; border: 2px solid #ce93d8; border-radius: 10px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #7b1fa2; outline: none; }
        .required { color: red; }
        .radio-group { display: flex; gap: 30px; padding: 10px 0; }
        .radio-group label { font-weight: normal; color: #333; }
        .checkbox-label { display: flex; align-items: center; gap: 10px; font-weight: normal !important; }
        .checkbox-label input { width: 20px; height: 20px; }
        select[multiple] { min-height: 150px; }
        select[multiple] option:checked { background: #7b1fa2; color: white; }
        .btn-submit { background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; border: none; padding: 15px; font-size: 18px; font-weight: bold; border-radius: 40px; cursor: pointer; width: 100%; }
        .btn-submit:hover { opacity: 0.9; transform: scale(1.02); }
        .error-text { color: red; font-size: 14px; margin-top: 5px; }
        .error-border { border-color: red !important; }
        .error-summary { background: #ffebee; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid red; }
        .error-summary ul { margin-left: 20px; }
        .user-info { background: #e8f5e9; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .logout-btn { background: red; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; }
        .logout-btn:hover { background: darkred; }
        .credentials-box { background: #f3e5f5; border: 2px dashed #7b1fa2; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .credentials-box strong { color: #4a148c; }
        .action-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
        .action-btn { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: white; padding: 10px 25px; border-radius: 40px; text-decoration: none; font-weight: bold; }
        .action-btn:hover { opacity: 0.9; transform: scale(1.05); }
        small { color: #666; display: block; margin-top: 5px; }
        .intro { background: #f3e5f5; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #7b1fa2; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <div class="subtitle">Лабораторная работа №6 — Форма</div>
        </div>
    </header>

    <main class="container">
        <div class="intro">
            <p>Заполните форму. При первой отправке будут сгенерированы логин и пароль.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>✅ Вы авторизованы как <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
            <?php else: ?>
                <p>🔐 <a href="login.php">Войдите</a>, чтобы редактировать свои данные</p>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-info">
            <span>👤 <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">🚪 Выйти</a>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['new_login']) && isset($_GET['new_password'])): ?>
        <div class="credentials-box">
            <p><strong>✅ Данные сохранены!</strong></p>
            <p>🔑 Логин: <strong><?php echo htmlspecialchars($_GET['new_login']); ?></strong></p>
            <p>🔒 Пароль: <strong><?php echo htmlspecialchars($_GET['new_password']); ?></strong></p>
            <p style="font-size: 13px; color: #666;">* Сохраните их!</p>
        </div>
        <?php endif; ?>

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

        <div class="form-box">
            <form action="process.php" method="GET">
                <input type="hidden" name="edit_id" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">

                <div class="form-group">
                    <label>ФИО <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" class="<?php echo isset($errors['full_name']) ? 'error-border' : ''; ?>">
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="error-text"><?php echo $errors['full_name']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Телефон <span class="required">*</span></label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="<?php echo isset($errors['phone']) ? 'error-border' : ''; ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-text"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="<?php echo isset($errors['email']) ? 'error-border' : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-text"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Дата рождения <span class="required">*</span></label>
                    <input type="date" name="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>" class="<?php echo isset($errors['birth_date']) ? 'error-border' : ''; ?>">
                    <?php if (isset($errors['birth_date'])): ?>
                        <div class="error-text"><?php echo $errors['birth_date']; ?></div>
                    <?php endif; ?>
                </div>

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

                <div class="form-group">
                    <label>Любимый язык программирования <span class="required">*</span></label>
                    <select name="languages[]" multiple size="6" class="<?php echo isset($errors['languages']) ? 'error-border' : ''; ?>">
                        <?php foreach ($allowed_languages as $lang): ?>
                            <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $languages) ? 'selected' : ''; ?>>
                                <?php echo $lang; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Зажмите Ctrl (Cmd на Mac) для выбора нескольких</small>
                    <?php if (isset($errors['languages'])): ?>
                        <div class="error-text"><?php echo $errors['languages']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Биография</label>
                    <textarea name="biography" rows="5"><?php echo htmlspecialchars($biography); ?></textarea>
                    <small>Не более 5000 символов</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="contract_accepted" value="1" <?php echo $contract_accepted ? 'checked' : ''; ?>>
                        С контрактом ознакомлен(а) <span class="required">*</span>
                    </label>
                    <?php if (isset($errors['contract_accepted'])): ?>
                        <div class="error-text"><?php echo $errors['contract_accepted']; ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">✅ Сохранить</button>
            </form>
        </div>

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