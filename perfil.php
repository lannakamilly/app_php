<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Atualizar perfil
if (isset($_POST['editar'])) {
    $nome = $_POST['nome'];
    $bio = $_POST['bio'];

    // Upload foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . '.' . $ext;
        $destino = 'uploads/perfis/' . $nomeArquivo;

        if (!is_dir('uploads/perfis')) {
            mkdir('uploads/perfis', 0755, true);
        }

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
            // Atualizar foto no banco
            $stmt = $conexao->prepare("UPDATE users SET foto_perfil = ? WHERE id = ?");
            $stmt->execute([$nomeArquivo, $usuario_id]);
        }
    }

    $stmt = $conexao->prepare("UPDATE users SET nome=?, bio=? WHERE id=?");
    $stmt->execute([$nome, $bio, $usuario_id]);

    header("Location: perfil.php");
    exit;
}

// Buscar dados do usuário logado
$stmt = $conexao->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar posts do usuário
$stmt = $conexao->prepare("SELECT * FROM posts WHERE usuario_id = ? ORDER BY data_postagem DESC");
$stmt->execute([$usuario_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Listar amigos
$stmt = $conexao->prepare("
    SELECT u.* FROM users u
    JOIN amizades a ON 
        (a.usuario_id = ? AND a.amigo_id = u.id AND a.status='aceito')
        OR (a.amigo_id = ? AND a.usuario_id = u.id AND a.status='aceito')
");
$stmt->execute([$usuario_id, $usuario_id]);
$amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pesquisa de usuários
$pesquisa = "";
$resultados = [];
if (isset($_GET['search'])) {
    $pesquisa = $_GET['search'];
    $stmt = $conexao->prepare("SELECT * FROM users WHERE nome LIKE ? AND id!=?");
    $stmt->execute(["%$pesquisa%", $usuario_id]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Enviar pedido de amizade
if (isset($_GET['add'])) {
    $add_id = $_GET['add'];
    $stmt = $conexao->prepare("INSERT IGNORE INTO amizades (usuario_id, amigo_id) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $add_id]);
    header("Location: perfil.php");
    exit;
}

// Aceitar pedido de amizade
if (isset($_GET['aceitar'])) {
    $aceitar_id = $_GET['aceitar'];
    $stmt = $conexao->prepare("UPDATE amizades SET status='aceito' WHERE usuario_id=? AND amigo_id=?");
    $stmt->execute([$aceitar_id, $usuario_id]);
    header("Location: perfil.php");
    exit;
}

// Pedidos pendentes recebidos
$stmt = $conexao->prepare("
    SELECT u.* FROM users u
    JOIN amizades a ON a.usuario_id = u.id
    WHERE a.amigo_id=? AND a.status='pendente'
");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Perfil</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background: #f0f2f5;
    min-height: 100vh;
    display: flex;
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

/* CONTAINER PRINCIPAL */
.container {
    margin-left: 90px;
    max-width: 900px;
    width: 100%;
    padding: 30px 25px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    min-height: 90vh;
    display: flex;
    flex-direction: column;
    gap: 40px;
}

/* SEÇÃO DE PERFIL */
.profile-header {
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
}

.profile-photo {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #1877f2;
    box-shadow: 0 4px 15px rgba(24,119,242,0.4);
    background: #ddd;
}

.profile-info {
    flex: 1;
    min-width: 250px;
}

.profile-info h2 {
    font-size: 32px;
    color: #1877f2;
    margin-bottom: 10px;
}

.profile-info p.bio {
    font-size: 16px;
    color: #555;
    white-space: pre-wrap;
    line-height: 1.5;
}

/* FORMULÁRIO DE EDIÇÃO */
form#editarPerfil {
    max-width: 600px;
    margin-top: 20px;
}

form#editarPerfil input[type="text"],
form#editarPerfil textarea {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 15px;
    border: 1.5px solid #ccd0d5;
    border-radius: 12px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    resize: vertical;
}

form#editarPerfil input[type="text"]:focus,
form#editarPerfil textarea:focus {
    border-color: #1877f2;
    outline: none;
}

form#editarPerfil input[type="file"] {
    margin-bottom: 15px;
}

form#editarPerfil button {
    background: #1877f2;
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 700;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(24,119,242,0.6);
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

form#editarPerfil button:hover {
    background: #145db2;
    box-shadow: 0 6px 18px rgba(21,93,178,0.8);
}

/* SEÇÕES */
section {
    max-width: 900px;
}

h3 {
    color: #1877f2;
    font-size: 24px;
    margin-bottom: 20px;
    border-bottom: 2px solid #1877f2;
    padding-bottom: 6px;
}

/* LISTAS */
ul {
    list-style: none;
    padding: 0;
    max-width: 600px;
    margin: 0 auto;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    background: #f7f9fc;
}

li {
    padding: 15px 20px;
    border-bottom: 1px solid #e1e4e8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 16px;
    color: #1c1e21;
}

li:last-child {
    border-bottom: none;
}

ul li a {
    background: #1877f2;
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: background 0.3s ease;
}

ul li a:hover {
    background: #145db2;
}

/* POSTS DO USUÁRIO */
.posts-list {
    max-width: 700px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.post {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 20px;
    transition: box-shadow 0.3s ease;
}

.post:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.post strong {
    color: #1877f2;
    font-size: 18px;
    display: block;
    margin-bottom: 12px;
    font-weight: 700;
}

.post .conteudo {
    font-size: 16px;
    line-height: 1.5;
    white-space: pre-wrap;
    color: #1c1e21;
    margin-bottom: 12px;
}

.post img {
    max-width: 100%;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    object-fit: cover;
}

/* Data */
.post small {
    color: #606770;
    font-size: 13px;
}

/* RESPONSIVO */
@media screen and (max-width: 900px) {
    .container {
        margin-left: 80px;
        padding: 20px 15px;
    }
}

@media screen and (max-width: 600px) {
    body {
        display: block;
        padding: 10px 0;
    }
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        flex-direction: row;
        padding: 10px 0;
        box-shadow: none;
    }
    .sidebar:hover {
        width: 100%;
    }
    .sidebar a {
        justify-content: center;
        padding: 10px 15px;
        font-size: 14px;
    }
    .sidebar i {
        min-width: auto;
        margin-right: 8px;
    }
    .sidebar span {
        opacity: 1 !important;
        margin-left: 0;
    }
    .container {
        margin-left: 0;
        max-width: 100%;
        border-radius: 0;
        box-shadow: none;
        padding: 15px 10px;
    }
    .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .profile-info {
        min-width: auto;
    }
    .posts-list {
        max-width: 100%;
    }
    ul {
        max-width: 100%;
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

<!-- CONTEÚDO PRINCIPAL -->
<div class="container" role="main" aria-label="Perfil do usuário">

    <!-- Cabeçalho do perfil -->
    <div class="profile-header">
        <img src="<?= !empty($usuario['foto_perfil']) ? 'uploads/perfis/' . htmlspecialchars($usuario['foto_perfil']) : 'https://via.placeholder.com/140?text=Sem+Foto' ?>" alt="Foto de perfil de <?= htmlspecialchars($usuario['nome']) ?>" class="profile-photo" />
        <div class="profile-info">
            <h2><?= htmlspecialchars($usuario['nome']) ?></h2>
            <p class="bio"><?= nl2br(htmlspecialchars($usuario['bio'])) ?></p>
        </div>
    </div>

    <!-- Formulário para editar perfil -->
    <form id="editarPerfil" method="post" enctype="multipart/form-data" aria-label="Formulário para editar perfil">
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" placeholder="Nome" aria-required="true" />
        <textarea name="bio" placeholder="Bio" rows="4" aria-label="Biografia do usuário"><?= htmlspecialchars($usuario['bio']) ?></textarea>
        <label for="foto_perfil" style="display:block; margin-bottom:8px; font-weight:600; color:#1877f2; cursor:pointer;">
            <i class="fas fa-camera"></i> Alterar foto de perfil
        </label>
        <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" style="display:none;" />
        <button type="submit" name="editar" aria-label="Salvar alterações no perfil">Salvar</button>
    </form>

    <!-- Amigos -->
    <section aria-label="Lista de amigos">
        <h3>Amigos</h3>
        <?php if (count($amigos) === 0): ?>
            <p style="text-align:center; color:#606770;">Você não tem amigos adicionados.</p>
        <?php else: ?>
            <ul>
                <?php foreach($amigos as $a): ?>
                    <li><?= htmlspecialchars($a['nome']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Pedidos de amizade -->
    <section aria-label="Pedidos de amizade pendentes">
        <h3>Pedidos de amizade</h3>
        <?php if (count($pedidos) === 0): ?>
            <p style="text-align:center; color:#606770;">Nenhum pedido pendente.</p>
        <?php else: ?>
            <ul>
                <?php foreach($pedidos as $p): ?>
                    <li>
                        <?= htmlspecialchars($p['nome']) ?>
                        <a href="?aceitar=<?= $p['id'] ?>" aria-label="Aceitar pedido de amizade de <?= htmlspecialchars($p['nome']) ?>">Aceitar</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Pesquisa de usuários -->
    <section aria-label="Pesquisar usuários">
        <h3>Procurar usuários</h3>
        <form method="get" aria-label="Formulário de pesquisa de usuários" style="max-width:600px; margin:0 auto 20px;">
            <input type="text" name="search" value="<?= htmlspecialchars($pesquisa) ?>" placeholder="Digite o nome" aria-label="Campo para pesquisa de usuários" />
            <button type="submit" aria-label="Pesquisar usuários">Pesquisar</button>
        </form>

        <?php if (count($resultados) === 0 && $pesquisa !== ''): ?>
            <p style="text-align:center; color:#606770;">Nenhum usuário encontrado para "<?= htmlspecialchars($pesquisa) ?>"</p>
        <?php elseif (count($resultados) > 0): ?>
            <ul style="max-width:600px; margin:0 auto;">
                <?php foreach($resultados as $r): ?>
                    <li>
                        <?= htmlspecialchars($r['nome']) ?>
                        <a href="?add=<?= $r['id'] ?>" aria-label="Enviar pedido de amizade para <?= htmlspecialchars($r['nome']) ?>">Adicionar</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Posts do usuário -->
    <section aria-label="Posts do usuário">
        <h3>Meus Posts</h3>
        <?php if (count($posts) === 0): ?>
            <p style="text-align:center; color:#606770;">Você ainda não publicou nenhum post.</p>
        <?php else: ?>
            <div class="posts-list">
                <?php foreach ($posts as $post): ?>
                    <article class="post" aria-label="Post publicado em <?= date('d/m/Y H:i', strtotime($post['data_postagem'])) ?>">
                        <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                        <div class="conteudo"><?= nl2br(htmlspecialchars($post['descricao'])) ?></div>
                        <?php if (!empty($post['imagem'])): ?>
                            <img src="uploads/<?= htmlspecialchars($post['imagem']) ?>" alt="Imagem do post" />
                        <?php endif; ?>
                        <small>Postado em <?= date('d/m/Y H:i', strtotime($post['data_postagem'])) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>