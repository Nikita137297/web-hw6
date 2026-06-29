<?php
echo "1. PHP работает<br>";
require_once 'config.php';
echo "2. Config подключён<br>";

try {
    $stmt = $pdo->query("SELECT 1");
    echo "3. БД работает<br>";
} catch (PDOException $e) {
    echo "Ошибка БД: " . $e->getMessage() . "<br>";
}

$tables = $pdo->query("SHOW TABLES")->fetchAll();
echo "4. Таблицы в БД:<br>";
foreach ($tables as $row) {
    echo "- " . implode(' ', $row) . "<br>";
}
?>