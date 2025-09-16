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

// Pega os dados do post
$stmt = $conexao->prepare("SELECT * FROM posts WHERE id = :id AND usuario_id = :usuario_id");
$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Post não encontrado ou você não tem permissão para editar.");
}

// Atualizar post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conteudo = $_POST['conteudo'];
    $imagem = $post['imagem']; // mantém a imagem antiga

    // Se enviou uma nova imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $nomeImagem = time() . "_" . $_FILES['imagem']['name'];
        $caminho = __DIR__ . "/assets/" . $nomeImagem;

        if (!is_dir(__DIR__ . "/assets")) {
            mkdir(__DIR__ . "/assets", 0777, true);
        }

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
            // exclui a imagem antiga
            if (!empty($imagem) && file_exists(__DIR__ . "/assets/" . $imagem)) {
                unlink(__DIR__ . "/assets/" . $imagem);
            }
            $imagem = $nomeImagem;
        }
    }

    // Atualiza no banco
    $stmt = $conexao->prepare("UPDATE posts SET conteudo = :conteudo, imagem = :imagem WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':conteudo', $conteudo, PDO::PARAM_STR);
    $stmt->bindParam(':imagem', $imagem, PDO::PARAM_STR);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<h2>Editar Post</h2>

<form method="POST" enctype="multipart/form-data">
    <textarea name="conteudo" required><?php echo htmlspecialchars($post['conteudo']); ?></textarea>
    <br><br>
    <input type="file" name="imagem" accept="image/*">
    <?php if (!empty($post['imagem'])): ?>
        <p>Imagem atual:</p>
        <img src="assets/<?php echo htmlspecialchars($post['imagem']); ?>" style="max-width:300px;">
    <?php endif; ?>
    <br><br>
    <button type="submit">Atualizar</button>
</form>
