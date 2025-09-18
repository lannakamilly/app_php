<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$outro_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

if (!$outro_id || $outro_id == $usuario_id) {
    header("Location: perfil.php");
    exit;
}

// Garantir que o menor ID vai para usuario1_id
$usuario1_id = min($usuario_id, $outro_id);
$usuario2_id = max($usuario_id, $outro_id);

// Verificar se conversa já existe
$stmt = $conexao->prepare("
    SELECT id FROM conversas 
    WHERE usuario1_id = ? AND usuario2_id = ?
");
$stmt->execute([$usuario1_id, $usuario2_id]);
$conversa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conversa) {
    // Criar conversa
    $stmt = $conexao->prepare("INSERT INTO conversas (usuario1_id, usuario2_id) VALUES (?, ?)");
    $stmt->execute([$usuario1_id, $usuario2_id]);
    $conversa_id = $conexao->lastInsertId();
} else {
    $conversa_id = $conversa['id'];
}

// Redirecionar para a página de chat
header("Location: chat.php?conversa_id=" . $conversa_id);
exit;
