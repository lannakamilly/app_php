<?php
session_start();
require_once "conexao.php";
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) { echo json_encode([]); exit; }

$usuario_id = $_SESSION['usuario_id'];
$nome = trim($_GET['nome'] ?? '');
if (!$nome) { echo json_encode([]); exit; }

$stmt = $conexao->prepare("SELECT id, nome FROM users WHERE nome LIKE ? AND id != ? LIMIT 10");
$stmt->execute(["%$nome%", $usuario_id]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($usuarios);
