<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conexao.php"; // precisa devolver $conexao como PDO

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado!");
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conteudo = $_POST['conteudo'];
    
    // Upload de imagem
    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $nomeImagem = time() . "_" . $_FILES['imagem']['name'];
        $caminho = __DIR__ . "/assets/" . $nomeImagem;

        if (!is_dir(__DIR__ . "/assets")) {
            mkdir(__DIR__ . "/assets", 0777, true);
        }

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
            $imagem = $nomeImagem;
        }
    }

    // Agora usando PDO
    $stmt = $conexao->prepare("INSERT INTO posts (usuario_id, conteudo, imagem) VALUES (:usuario_id, :conteudo, :imagem)");
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->bindParam(':conteudo', $conteudo, PDO::PARAM_STR);
    $stmt->bindParam(':imagem', $imagem, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<form method="POST" enctype="multipart/form-data">
    <textarea name="conteudo" placeholder="O que você está pensando?" required></textarea>
    <input type="file" name="imagem" accept="image/*">
    <button type="submit">Postar</button>
</form>


