<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conexao.php"; // $conexao como PDO

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado!");
}

$usuario_id = $_SESSION['usuario_id'];

// ====== Pegar posts de outros usuários ======
$stmt = $conexao->prepare("
    SELECT p.*, u.nome 
    FROM posts p
    JOIN users u ON p.usuario_id = u.id
    WHERE p.usuario_id != :usuario_id
    ORDER BY p.criado_em DESC
");
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Feed Inicial</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            display: flex;
            justify-content: center;
            margin: 0;
            padding: 20px 0;
        }
        .container {
            width: 500px;
        }
        .post {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 5px #ccc;
            margin-bottom: 20px;
            padding: 15px;
        }
        .post strong {
            display: block;
            margin-bottom: 5px;
        }
        .post img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }
        .conteudo {
            margin-top: 8px;
        }
        small {
            color: #777;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Feed Inicial</h2>

    <?php if (count($posts) === 0): ?>
        <p>Nenhum post de outros usuários ainda.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <strong><?= htmlspecialchars($post['nome']) ?></strong>
                <div class="conteudo"><?= nl2br(htmlspecialchars($post['conteudo'])) ?></div>
                <?php if (!empty($post['imagem'])): ?>
                    <img src="assets/<?= htmlspecialchars($post['imagem']) ?>" alt="Postagem">
                <?php endif; ?>
                <small>Postado em <?= date('d/m/Y H:i', strtotime($post['criado_em'])) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
