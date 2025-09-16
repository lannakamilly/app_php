<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: feed.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conteudo = $_POST['conteudo'];
    
    // Upload de imagem
    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $nomeImagem = time() . "_" . $_FILES['imagem']['name'];
        move_uploaded_file($_FILES['imagem']['tmp_name'], "../assets/" . $nomeImagem);
        $imagem = $nomeImagem;
    }

    $stmt = $conexao->prepare("INSERT INTO posts (usuario_id, conteudo, imagem) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['usuario_id'], $conteudo, $imagem]);
    header("Location: index.php");
    exit;
}
?>

<form method="POST" enctype="multipart/form-data">
    <textarea name="conteudo" placeholder="O que você está pensando?" required></textarea>
    <input type="file" name="imagem" accept="image/*">
    <button type="submit">Postar</button>
</form>
