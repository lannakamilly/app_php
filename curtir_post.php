<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) die("Usuário não logado!");
if (!isset($_POST['post_id'])) die("Post inválido");

$usuario_id = $_SESSION['usuario_id'];
$post_id = (int)$_POST['post_id'];

// Verificar se já curtiu
$stmt = $conexao->prepare("SELECT id FROM curtidas WHERE post_id = ? AND usuario_id = ?");
$stmt->execute([$post_id, $usuario_id]);

if ($stmt->rowCount() > 0) {
    // Descurtir
    $stmt = $conexao->prepare("DELETE FROM curtidas WHERE post_id = ? AND usuario_id = ?");
    $stmt->execute([$post_id, $usuario_id]);
    echo "descurtido";
} else {
    // Curtir
    $stmt = $conexao->prepare("INSERT INTO curtidas (post_id, usuario_id) VALUES (?, ?)");
    $stmt->execute([$post_id, $usuario_id]);
    echo "curtido";
}
?>
