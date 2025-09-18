<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) exit("Não logado");

$usuario_id = $_SESSION['usuario_id'];
$descricao = $_POST['descricao'];
$imagem = null;

if (!empty($_FILES['imagem']['name'])) {
    $nomeImg = time() . "_" . basename($_FILES['imagem']['name']);
    move_uploaded_file($_FILES['imagem']['tmp_name'], "uploads/".$nomeImg);
    $imagem = $nomeImg;
}

$stmt = $conexao->prepare("INSERT INTO posts (usuario_id, descricao, imagem) VALUES (?, ?, ?)");
$stmt->execute([$usuario_id, $descricao, $imagem]);

header("Location: home.php");
