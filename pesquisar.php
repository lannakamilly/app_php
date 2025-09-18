<?php
require_once "conexao.php";

$q = $_GET['q'] ?? '';

if ($q == '') {
    echo json_encode([]);
    exit;
}

$stmt = $conexao->prepare("SELECT nome FROM users WHERE nome LIKE ? LIMIT 5");
$stmt->execute(['%' . $q . '%']);
$usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($usuarios);
