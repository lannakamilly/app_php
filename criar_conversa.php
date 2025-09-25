<?php
session_start();
require_once "conexao.php";
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) { echo json_encode([]); exit; }

$usuario1_id = $_SESSION['usuario_id'];
$usuario2_id = filter_input(INPUT_POST, 'usuario2_id', FILTER_VALIDATE_INT);
if (!$usuario2_id || $usuario1_id == $usuario2_id) { echo json_encode([]); exit; }

// Verifica se já existe conversa
$stmt = $conexao->prepare("SELECT id FROM conversas WHERE (usuario1_id=? AND usuario2_id=?) OR (usuario1_id=? AND usuario2_id=?) LIMIT 1");
$stmt->execute([$usuario1_id, $usuario2_id, $usuario2_id, $usuario1_id]);
$conversa = $stmt->fetch(PDO::FETCH_ASSOC);

if ($conversa) {
    echo json_encode(['conversa_id' => $conversa['id']]);
    exit;
}

// Cria nova conversa
$stmt = $conexao->prepare("INSERT INTO conversas (usuario1_id, usuario2_id) VALUES (?, ?)");
if ($stmt->execute([$usuario1_id, $usuario2_id])) {
    echo json_encode(['conversa_id' => $conexao->lastInsertId()]);
} else {
    echo json_encode([]);
}
