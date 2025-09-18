<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) exit("Não logado");

$usuario_id = $_SESSION['usuario_id'];
$post_id = $_POST['post_id'];

// Verificar se já curtiu
$stmt = $conexao->prepare("SELECT * FROM curtidas WHERE usuario_id=? AND post_id=?");
$stmt->execute([$usuario_id,$post_id]);

if ($stmt->rowCount()>0){
    // remover curtida
    $conexao->prepare("DELETE FROM curtidas WHERE usuario_id=? AND post_id=?")->execute([$usuario_id,$post_id]);
    echo "Curtida removida";
} else {
    // adicionar curtida
    $conexao->prepare("INSERT INTO curtidas (usuario_id,post_id) VALUES (?,?)")->execute([$usuario_id,$post_id]);
    echo "Curtida adicionada";
}
