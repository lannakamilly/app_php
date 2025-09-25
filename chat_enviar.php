<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) { echo 'erro'; exit; }

$usuario_id = $_SESSION['usuario_id'];
$conversa_id = filter_input(INPUT_POST, 'conversa_id', FILTER_VALIDATE_INT);
$mensagem = trim($_POST['mensagem'] ?? '');

if (!$conversa_id || $mensagem === '') { echo 'erro'; exit; }

// Verifica se o usuário participa da conversa
$stmt = $conexao->prepare("SELECT * FROM conversas WHERE id=? AND (usuario1_id=? OR usuario2_id=?)");
$stmt->execute([$conversa_id, $usuario_id, $usuario_id]);
if (!$stmt->fetch()) { echo 'erro'; exit; }

// Inserir mensagem
$stmt = $conexao->prepare("INSERT INTO mensagens (conversa_id, remetente_id, mensagem) VALUES (?, ?, ?)");
echo $stmt->execute([$conversa_id, $usuario_id, $mensagem]) ? 'sucesso' : 'erro';
