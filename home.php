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
        .curtir-btn, .comentar-btn {
            margin-top: 10px;
        }
        .comentarios {
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .comentario {
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 70%;
            padding: 5px;
        }
        button {
            padding: 5px 10px;
            cursor: pointer;
        }
        .btn-criar {
            margin-bottom: 20px;
            background: green;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Feed Inicial</h2>

    <a href="criar_post.php" class="btn-criar">➕ Criar Publicação</a>

    <?php if (count($posts) === 0): ?>
        <p>Nenhum post de outros usuários ainda.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php
            // Contar curtidas
            $stmt_c = $conexao->prepare("SELECT COUNT(*) FROM curtidas WHERE post_id = ?");
            $stmt_c->execute([$post['id']]);
            $curtidas = $stmt_c->fetchColumn();

            // Verificar se o usuário já curtiu
            $stmt_uc = $conexao->prepare("SELECT id FROM curtidas WHERE post_id = ? AND usuario_id = ?");
            $stmt_uc->execute([$post['id'], $usuario_id]);
            $jaCurtiu = $stmt_uc->rowCount() > 0;

            // Buscar comentários
            $stmt_com = $conexao->prepare("
                SELECT c.comentario, u.nome 
                FROM comentarios c
                JOIN users u ON c.usuario_id = u.id
                WHERE c.post_id = ?
                ORDER BY c.criado_em ASC
            ");
            $stmt_com->execute([$post['id']]);
            $comentarios = $stmt_com->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="post" id="post-<?= $post['id'] ?>">
                <strong><?= htmlspecialchars($post['nome']) ?></strong>
                <div class="conteudo"><?= nl2br(htmlspecialchars($post['conteudo'])) ?></div>
                <?php if (!empty($post['imagem'])): ?>
                    <img src="assets/<?= htmlspecialchars($post['imagem']) ?>" alt="Postagem">
                <?php endif; ?>
                <small>Postado em <?= date('d/m/Y H:i', strtotime($post['criado_em'])) ?></small>

                <!-- Botão Curtir -->
                <button onclick="curtir(<?= $post['id'] ?>)" id="btn-curtir-<?= $post['id'] ?>">
                    <?= $jaCurtiu ? '💔 Descurtir' : '❤️ Curtir' ?> (<span id="qtd-curtidas-<?= $post['id'] ?>"><?= $curtidas ?></span>)
                </button>

                <!-- Formulário de comentário -->
                <div style="margin-top: 10px;">
                    <input type="text" id="comentario-<?= $post['id'] ?>" placeholder="Escreva um comentário">
                    <button onclick="comentar(<?= $post['id'] ?>)">Comentar</button>
                </div>

                <!-- Lista de comentários -->
                <div class="comentarios" id="comentarios-<?= $post['id'] ?>">
                    <?php foreach ($comentarios as $c): ?>
                        <div class="comentario"><strong><?= htmlspecialchars($c['nome']) ?>:</strong> <?= htmlspecialchars($c['comentario']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function curtir(post_id) {
    fetch('curtir_post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + post_id
    })
    .then(response => response.text())
    .then(data => {
        const btn = document.getElementById('btn-curtir-' + post_id);
        const qtd = document.getElementById('qtd-curtidas-' + post_id);
        if(data === 'curtido') {
            btn.innerHTML = '💔 Descurtir (' + (parseInt(qtd.innerText)+1) + ')';
            qtd.innerText = parseInt(qtd.innerText)+1;
        } else if(data === 'descurtido') {
            btn.innerHTML = '❤️ Curtir (' + (parseInt(qtd.innerText)-1) + ')';
            qtd.innerText = parseInt(qtd.innerText)-1;
        }
    });
}

function comentar(post_id) {
    const comentario = document.getElementById('comentario-' + post_id).value;
    if(comentario.trim() === '') return;

    fetch('comentar_post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + post_id + '&comentario=' + encodeURIComponent(comentario)
    })
    .then(() => {
        // Adicionar comentário na lista sem recarregar
        const div = document.getElementById('comentarios-' + post_id);
        div.innerHTML += '<div class="comentario"><strong>Você:</strong> ' + comentario + '</div>';
        document.getElementById('comentario-' + post_id).value = '';
    });
}
</script>

</body>
</html>
