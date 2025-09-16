<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado!");
}

$usuario_id = $_SESSION['usuario_id'];

// Verifica se o ID do post foi passado
if (!isset($_GET['id'])) {
    die("ID do post não informado.");
}

$post_id = (int) $_GET['id'];

// Verifica se o post pertence ao usuário logado
$stmt = $conexao->prepare("SELECT usuario_id, imagem FROM posts WHERE id = :id");
$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Post não encontrado.");
}

if ($post['usuario_id'] != $usuario_id) {
    die("Você não tem permissão para excluir este post.");
}

// Exclui a imagem do servidor, se existir
if (!empty($post['imagem']) && file_exists(__DIR__ . "/assets/" . $post['imagem'])) {
    unlink(__DIR__ . "/assets/" . $post['imagem']);
}

// Exclui o post do banco
$stmt = $conexao->prepare("DELETE FROM posts WHERE id = :id");
$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
$stmt->execute();

// Redireciona para o feed
header("Location: index.php");
exit;
?>
