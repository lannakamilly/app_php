<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar posts
$stmt = $conexao->query("
    SELECT p.*, u.nome 
    FROM posts p
    JOIN users u ON p.usuario_id = u.id
    ORDER BY p.data_postagem DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Feed Inicial</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    scroll-behavior: smooth;
}

body {
    background: #e9ebee;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    padding: 20px 0;
}

/* NAVBAR LATERAL */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 70px;
    height: 100%;
    background: #1877f2;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 20px;
    transition: width 0.3s ease;
    overflow: hidden;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    z-index: 1000;
}

.sidebar:hover {
    width: 220px;
}

.sidebar a {
    color: #fff;
    text-decoration: none;
    width: 100%;
    padding: 15px 25px;
    display: flex;
    align-items: center;
    transition: background 0.2s ease;
    border-radius: 0 25px 25px 0;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 16px;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.2);
}

.sidebar i {
    min-width: 30px;
    font-size: 22px;
    text-align: center;
}

.sidebar span {
    margin-left: 15px;
    opacity: 0;
    transition: opacity 0.3s ease;
    white-space: nowrap;
}

.sidebar:hover span {
    opacity: 1;
}

/* CONTAINER FEED */
.container {
    margin-left: 90px;
    max-width: 700px;
    width: 100%;
    padding: 0 15px 30px;
}

/* TITULO */
h2 {
    color: #1877f2;
    margin-bottom: 25px;
    font-weight: 700;
    font-size: 28px;
    text-align: center;
}

/* FORM NOVO POST */
.card-postar {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 20px 25px;
    margin-bottom: 30px;
    transition: box-shadow 0.3s ease;
}

.card-postar:hover {
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
}

.card-postar textarea {
    width: 100%;
    resize: none;
    padding: 15px 20px;
    border-radius: 15px;
    border: 1.5px solid #ccd0d5;
    font-size: 16px;
    min-height: 80px;
    transition: border-color 0.3s ease;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card-postar textarea:focus {
    border-color: #1877f2;
    outline: none;
}

.card-postar input[type="file"] {
    margin-top: 15px;
    font-size: 14px;
    cursor: pointer;
}

.card-postar button {
    background: #1877f2;
    color: #fff;
    border: none;
    padding: 12px 25px;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 700;
    font-size: 16px;
    margin-top: 15px;
    box-shadow: 0 4px 12px rgba(24,119,242,0.6);
    transition: background 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-postar button:hover {
    background: #155db2;
    box-shadow: 0 6px 18px rgba(21,93,178,0.8);
}

.card-postar button i {
    font-size: 20px;
}

/* Preview da imagem */
#preview-img {
    margin-top: 15px;
    max-width: 100%;
    max-height: 300px;
    border-radius: 15px;
    object-fit: cover;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: none;
}

/* POST */
.post {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    padding: 20px 25px;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
}

.post:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.post strong {
    color: #1877f2;
    font-size: 18px;
    display: block;
    margin-bottom: 12px;
    font-weight: 700;
}

.post .conteudo {
    margin: 12px 0;
    font-size: 16px;
    line-height: 1.5;
    white-space: pre-wrap;
    color: #1c1e21;
}

.post img {
    max-width: 100%;
    border-radius: 20px;
    margin-top: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    object-fit: cover;
}

/* Data */
small {
    color: #606770;
    display: block;
    margin-top: 10px;
    font-size: 13px;
}

/* CURTIR E COMENTAR */
.post-actions {
    margin-top: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.post-actions button {
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #606770;
    font-weight: 600;
    padding: 8px 15px;
    border-radius: 30px;
    transition: background 0.3s ease, color 0.3s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.post-actions button:hover {
    background: #e7f3ff;
    color: #1877f2;
    box-shadow: 0 4px 12px rgba(24,119,242,0.3);
}

.post-actions button i {
    font-size: 20px;
}

/* Input comentário */
.comentario-input {
    flex: 1;
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.comentario-input input[type="text"] {
    flex-grow: 1;
    padding: 10px 15px;
    border-radius: 30px;
    border: 1.5px solid #ccd0d5;
    font-size: 15px;
    transition: border-color 0.3s ease;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.comentario-input input[type="text"]:focus {
    border-color: #1877f2;
    outline: none;
}

.comentario-input button {
    background: #1877f2;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 700;
    font-size: 15px;
    box-shadow: 0 4px 12px rgba(24,119,242,0.6);
    transition: background 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.comentario-input button:hover {
    background: #155db2;
    box-shadow: 0 6px 18px rgba(21,93,178,0.8);
}

.comentario-input button i {
    font-size: 18px;
}

/* COMENTÁRIOS */
.comentarios {
    margin-top: 20px;
    border-top: 1px solid #dddfe2;
    padding-top: 15px;
    max-height: 200px;
    overflow-y: auto;
}

.comentario {
    margin-bottom: 12px;
    font-size: 14.5px;
    color: #1c1e21;
    line-height: 1.3;
}

.comentario strong {
    color: #1877f2;
    margin-right: 6px;
}

/* Scrollbar para comentários */
.comentarios::-webkit-scrollbar {
    width: 6px;
}

.comentarios::-webkit-scrollbar-thumb {
    background-color: #ccd0d5;
    border-radius: 3px;
}

/* RESPONSIVO */
@media screen and (max-width: 700px) {
    .container {
        margin-left: 80px;
        padding: 0 10px 30px;
    }
}

@media screen and (max-width: 480px) {
    body {
        padding: 10px 0;
    }
    .sidebar {
        width: 50px;
    }
    .sidebar:hover {
        width: 150px;
    }
    .sidebar span {
        display: none;
    }
    .container {
        margin-left: 60px;
        padding: 0 10px 20px;
        max-width: 100%;
    }
    .post-actions {
        flex-direction: column;
        align-items: flex-start;
    }
    .comentario-input {
        flex-direction: column;
    }
    .comentario-input input[type="text"] {
        width: 100%;
        margin-bottom: 8px;
    }
    .comentario-input button {
        width: 100%;
    }
}
</style>
</head>
<body>

<!-- NAVBAR LATERAL -->
<div class="sidebar" aria-label="Menu lateral">
    <a href="home.php" title="Home"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="perfil.php" title="Perfil"><i class="fas fa-user"></i><span>Perfil</span></a>
    <a href="chat.php" title="Chat"><i class="fas fa-comments"></i><span>Chat</span></a>
    <a href="index.php" title="Sair"><i class="fas fa-sign-out-alt"></i><span>Sair</span></a>
</div>

<!-- FEED -->
<div class="container" role="main">
    <h2>Feed Inicial</h2>

    <!-- FORM DE PUBLICAR -->
    <div class="card-postar" aria-label="Formulário para novo post">
        <form id="formPost" enctype="multipart/form-data" method="post" action="salvar_post.php">
            <textarea name="descricao" placeholder="Escreva algo..." required aria-label="Descrição do post"></textarea>
            <input type="file" name="imagem" accept="image/*" id="inputImagem" aria-label="Selecionar imagem para postar" required>
            <img id="preview-img" alt="Pré-visualização da imagem selecionada">
            <button type="submit"><i class="fas fa-plus"></i> Adicionar novo post</button>
        </form>
    </div>

    <!-- POSTS -->
  <!-- POSTS -->
<?php foreach ($posts as $post): ?>
    <?php
    // Curtidas
    $stmt_c = $conexao->prepare("SELECT COUNT(*) FROM curtidas WHERE post_id = ?");
    $stmt_c->execute([$post['id']]);
    $curtidas = $stmt_c->fetchColumn();

    $stmt_uc = $conexao->prepare("SELECT id FROM curtidas WHERE post_id = ? AND usuario_id = ?");
    $stmt_uc->execute([$post['id'], $usuario_id]);
    $jaCurtiu = $stmt_uc->rowCount() > 0;

    // Comentários
    $stmt_com = $conexao->prepare("
        SELECT c.comentario, u.nome 
        FROM comentarios c
        JOIN users u ON c.usuario_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.data_comentario ASC
    ");
    $stmt_com->execute([$post['id']]);
    $comentarios = $stmt_com->fetchAll(PDO::FETCH_ASSOC);

    $meu_post = ($post['usuario_id'] == $usuario_id); // Verifica se é do usuário logado
    ?>
    <article class="post" id="post-<?= $post['id'] ?>" aria-label="Post de <?= htmlspecialchars($post['nome']) ?>">
        <strong><?= htmlspecialchars($post['nome']) ?></strong>
        <div class="conteudo"><?= nl2br(htmlspecialchars($post['descricao'])) ?></div>
        <?php if (!empty($post['imagem'])): ?>
            <img src="uploads/<?= htmlspecialchars($post['imagem']) ?>" alt="Imagem do post">
        <?php endif; ?>

        <small>Postado em <?= date('d/m/Y H:i', strtotime($post['data_postagem'])) ?></small>

        <div class="post-actions">
            <button onclick="curtir(<?= $post['id'] ?>)" id="btn-curtir-<?= $post['id'] ?>" aria-pressed="<?= $jaCurtiu ? 'true' : 'false' ?>" aria-label="<?= $jaCurtiu ? 'Descurtir post' : 'Curtir post' ?>">
                <i class="fa-solid fa-heart" style="color: <?= $jaCurtiu ? '#e0245e' : '#606770' ?>;"></i>
                <span id="qtd-curtidas-<?= $post['id'] ?>"><?= $curtidas ?></span>
            </button>

            <?php if($meu_post): ?>
                <a href="editar_post.php?id=<?= $post['id'] ?>" style="color:#1877f2; text-decoration:none; font-weight:600;"><i class="fas fa-edit"></i> Editar</a>
                <a href="excluir_post.php?id=<?= $post['id'] ?>" style="color:#e0245e; text-decoration:none; font-weight:600;" onclick="return confirm('Tem certeza que deseja excluir este post?');"><i class="fas fa-trash-alt"></i> Excluir</a>
            <?php endif; ?>
        </div>

        <div class="comentario-input">
            <input type="text" id="comentario-<?= $post['id'] ?>" placeholder="Escreva um comentário" aria-label="Campo para escrever comentário">
            <button onclick="comentar(<?= $post['id'] ?>)" aria-label="Enviar comentário"><i class="fas fa-paper-plane"></i></button>
        </div>

        <div class="comentarios" id="comentarios-<?= $post['id'] ?>" aria-live="polite" aria-relevant="additions">
            <?php foreach ($comentarios as $c): ?>
                <div class="comentario"><strong><?= htmlspecialchars($c['nome']) ?>:</strong> <?= htmlspecialchars($c['comentario']) ?></div>
            <?php endforeach; ?>
        </div>
    </article>
<?php endforeach; ?>
</div>

<script>
// Preview da imagem antes de postar
document.getElementById('inputImagem').addEventListener('change', function(event) {
    const preview = document.getElementById('preview-img');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }
});

function curtir(post_id) {
    fetch('curtir.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + post_id
    })
    .then(response => response.text())
    .then(data => {
        const btn = document.getElementById('btn-curtir-' + post_id);
        const qtd = document.getElementById('qtd-curtidas-' + post_id);
        if(data === 'curtido') {
            btn.setAttribute('aria-pressed', 'true');
            btn.querySelector('i').style.color = '#e0245e';
            qtd.innerText = parseInt(qtd.innerText) + 1;
        } else if(data === 'descurtido') {
            btn.setAttribute('aria-pressed', 'false');
            btn.querySelector('i').style.color = '#606770';
            qtd.innerText = parseInt(qtd.innerText) - 1;
        }
    });
}

function comentar(post_id) {
    const input = document.getElementById('comentario-' + post_id);
    const comentario = input.value.trim();
    if(comentario === '') return;

    fetch('comentar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + post_id + '&comentario=' + encodeURIComponent(comentario)
    })
    .then(response => response.text())
    .then(() => {
        const div = document.getElementById('comentarios-' + post_id);
        const novoComentario = document.createElement('div');
        novoComentario.classList.add('comentario');
        novoComentario.innerHTML = '<strong>Você:</strong> ' + comentario;
        div.appendChild(novoComentario);
        input.value = '';
        // Scroll para o último comentário
        div.scrollTop = div.scrollHeight;
    });
}
</script>
</body>
</html>