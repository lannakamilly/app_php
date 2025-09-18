<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) die("Usuário não logado!");
if (!isset($_POST['post_id']) || empty(trim($_POST['comentario']))) die("Comentário inválido");

$usuario_id = $_SESSION['usuario_id'];
$post_id = (int)$_POST['post_id'];
$comentario = trim($_POST['comentario']);

$stmt = $conexao->prepare("INSERT INTO comentarios (post_id, usuario_id, comentario) VALUES (?, ?, ?)");
$stmt->execute([$post_id, $usuario_id, $comentario]);

header("Location: feed.php"); // Redireciona de volta ao feed
