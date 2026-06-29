<?php
// list.php - Страница со списком всех сохранённых анкет
require_once 'config.php';

// Получаем все анкеты с их языками программирования
$sql = "SELECT a.*, 
        GROUP_CONCAT(pl.name ORDER BY pl.name SEPARATOR ', ') as languages
        FROM applications a
        LEFT JOIN application_languages al ON a.id = al.application_id
        LEFT JOIN programming_languages pl ON al.language_id = pl.id
        GROUP BY a.id
        ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Список сохранённых анкет — Лабораторная работа №3</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1400px;
            width: 95%;
        }
        
        .applications-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 20px;
            overflow-x: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .applications-table th,
        .applications-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #ce93d8;
            vertical-align: top;
        }
        
        .applications-table th {
            background: linear-gradient(135deg, #7b1fa2, #4a148c);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }
        
        .applications-table tr:hover {
            background-color: #f3e5f5;
        }
        
        .applications-table tr:last-child td {
            border-bottom: none;
        }
        
        .badge {
            display: inline-block;
            background-color: #7b1fa2;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            margin: 0.1rem;
            white-space: nowrap;
        }
        
        .languages-cell {
            min-width: 180px;
            max-width: 250px;
        }
        
        .biography-cell {
            max-width: 250px;
            word-break: break-word;
            white-space: normal;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6a1b9a;
            background-color: #f3e5f5;
            border-radius: 20px;
        }
        
        .stats {
            background-color: #f3e5f5;
            padding: 1rem 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: nowrap;
            white-space: nowrap;
        }
        
        .btn-view {
            background-color: #7b1fa2;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: background-color 0.2s;
            white-space: nowrap;
        }
        
        .btn-view:hover {
            background-color: #4a148c;
        }
        
        .btn-delete {
            background-color: #ffebee;
            color: #f44336;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.75rem;
            border: 1px solid #f44336;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .btn-delete:hover {
            background-color: #f44336;
            color: white;
        }
        
        .table-wrapper {
            overflow-x: auto;
            border-radius: 20px;
            margin: 0 -0.5rem;
            padding: 0 0.5rem;
        }
        
       