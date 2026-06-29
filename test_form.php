<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['test'])) {
    echo "✅ ФОРМА РАБОТАЕТ! Получено: " . htmlspecialchars($_GET['test']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Тест</title></head>
<body>
    <form method="GET">
        <input type="text" name="test" value="hello">
        <button type="submit">Отправить</button>
    </form>
</body>
</html>