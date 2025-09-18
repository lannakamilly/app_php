<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) exit("Não logado");

$usuario_id = $_SESSION['usuario_id'];
$post_id = $_POST['post_id'];
$comentario = $_POST['comentario'];

$stmt = $conexao->prepare("INSERT INTO comentarios (usuario_id,post_id,comentario) VALUES (?,?,?)");
$stmt->execute([$usuario_id,$post_id,$comentario]);

// Buscar nome do usuário
$stmtU = $conexao->prepare("SELECT nome FROM users WHERE id=?");
$stmtU->execute([$usuario_id]);
$u=$stmtU->fetch(PDO::FETCH_ASSOC);

echo "<p><b>".htmlspecialchars($u['nome']).":</b> ".htmlspecialchars($comentario)."</p>";
