<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado!");
}

$usuario_id = $_SESSION['usuario_id'];
$descricao = $_POST['descricao'] ?? '';
$imagem = null;

if (!empty($_FILES['imagem']['name'])) {
    $nomeImagem = time() . "_" . basename($_FILES['imagem']['name']);
    $caminho = "uploads/" . $nomeImagem;
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
        $imagem = $nomeImagem;
    }
}

$stmt = $conexao->prepare("INSERT INTO posts (usuario_id, conteudo, imagem) VALUES (?, ?, ?)");
$stmt->execute([$usuario_id, $descricao, $imagem]);

$id = $conexao->lastInsertId();

// Pegar nome do usuário
$stmt_u = $conexao->prepare("SELECT nome FROM users WHERE id=?");
$stmt_u->execute([$usuario_id]);
$usuario = $stmt_u->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "id" => $id,
    "nome" => $usuario['nome'],
    "conteudo" => $descricao,
    "imagem" => $imagem,
    "criado_em" => date('d/m/Y H:i')
]);
