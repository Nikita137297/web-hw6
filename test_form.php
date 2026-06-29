<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['test'])) {
    echo "✅ РАБОТАЕТ! Получено: " . htmlspecialchars($_GET['test']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Тест</title></head>
<body>
    <h1>Тест формы</h1>
    <form action="test_form.php" method="GET">
        <input type="text" name="test" value="hello" style="padding:10px;width:300px;">
        <br><br>
        <button type="submit" style="padding:10px 30px;background:#4caf50;color:white;border:none;border-radius:5px;cursor:pointer;">
            Отправить
        </button>
    </form>
</body>
</html>