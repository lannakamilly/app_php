<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$outro_id = filter_input(INPUT_GET, 'usuario_id', FILTER_VALIDATE_INT);

if (!$outro_id) {
    echo "Usuário inválido.";
    exit;
}

// Verifica se já existe conversa
$stmt = $conexao->prepare("SELECT id FROM conversas WHERE (usuario1_id = ? AND usuario2_id = ?) OR (usuario1_id = ? AND usuario2_id = ?)");
$stmt->execute([$usuario_id, $outro_id, $outro_id, $usuario_id]);
$conversa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conversa) {
    // Cria nova conversa
    $stmt = $conexao->prepare("INSERT INTO conversas (usuario1_id, usuario2_id) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $outro_id]);
    $conversa_id = $conexao->lastInsertId();
} else {
    $conversa_id = $conversa['id'];
}

// Redireciona para o chat já com a conversa ativa
header("Location: chat.php?conversa_id=" . $conversa_id);
exit;
