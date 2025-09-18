<?php
session_start();
require_once "conexao.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$conversa_id = filter_input(INPUT_GET, 'conversa_id', FILTER_VALIDATE_INT);

if (!$conversa_id) {
    echo json_encode([]);
    exit;
}

// Verificar se o usuário pertence à conversa
$stmt = $conexao->prepare("SELECT * FROM conversas WHERE id = ? AND (usuario1_id = ? OR usuario2_id = ?)");
$stmt->execute([$conversa_id, $usuario_id, $usuario_id]);
$conversa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conversa) {
    echo json_encode([]);
    exit;
}

// Buscar mensagens
$stmt = $conexao->prepare("SELECT remetente_id, mensagem, data_envio FROM mensagens WHERE conversa_id = ? ORDER BY data_envio ASC");
$stmt->execute([$conversa_id]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($mensagens);