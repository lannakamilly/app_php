<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar conversas do usuário
$stmt = $conexao->prepare("
    SELECT c.id, 
           u1.nome AS nome1, u1.id AS id1,
           u2.nome AS nome2, u2.id AS id2
    FROM conversas c
    JOIN users u1 ON c.usuario1_id = u1.id
    JOIN users u2 ON c.usuario2_id = u2.id
    WHERE c.usuario1_id = ? OR c.usuario2_id = ?
    ORDER BY c.id DESC
");
$stmt->execute([$usuario_id, $usuario_id]);
$conversas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para pegar o nome do outro usuário na conversa
function nomeOutroUsuario($conversa, $usuario_id) {
    if ($conversa['id1'] == $usuario_id) return $conversa['nome2'];
    return $conversa['nome1'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Chat</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<style>
/* ====== SEU ESTILO EXISTENTE ====== */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
body { background: #e9ebee; min-height: 100vh; display: flex;}
.sidebar {position: fixed; left: 0; top: 0; width: 70px; height: 100%; background: #1877f2; display: flex; flex-direction: column; align-items: center; padding-top: 20px; transition: width 0.3s ease; overflow: hidden; box-shadow: 2px 0 8px rgba(0,0,0,0.1); z-index: 1000;}
.sidebar:hover { width: 220px; }
.sidebar a { color: #fff; text-decoration: none; width: 100%; padding: 15px 25px; display: flex; align-items: center; transition: background 0.2s ease; border-radius: 0 25px 25px 0; margin-bottom: 8px; font-weight: 600; font-size: 16px;}
.sidebar a:hover { background: rgba(255,255,255,0.2); }
.sidebar i { min-width: 30px; font-size: 22px; text-align: center;}
.sidebar span { margin-left: 15px; opacity: 0; transition: opacity 0.3s ease; white-space: nowrap;}
.sidebar:hover span { opacity: 1; }
.container { margin-left: 90px; flex: 1; display: flex; height: 100vh; max-width: 1200px; background: #fff; border-radius: 15px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); overflow: hidden;}
.conversas-list { width: 320px; border-right: 1px solid #ddd; overflow-y: auto; background: #f7f9fc; }
.conversa-item { padding: 15px 20px; border-bottom: 1px solid #ddd; cursor: pointer; transition: background 0.2s ease;}
.conversa-item:hover, .conversa-item.active { background: #e7f3ff; }
.conversa-item span { font-weight: 600; color: #1877f2; }
.chat-area { flex: 1; display: flex; flex-direction: column; }
.chat-header { padding: 15px 20px; border-bottom: 1px solid #ddd; font-weight: 700; font-size: 20px; color: #1877f2; background: #f7f9fc; }
.chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #f0f2f5; }
.message { max-width: 70%; margin-bottom: 15px; padding: 12px 18px; border-radius: 20px; font-size: 15px; line-height: 1.4; word-wrap: break-word; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
.message.sent { background: #1877f2; color: #fff; margin-left: auto; border-bottom-right-radius: 4px;}
.message.received { background: #e4e6eb; color: #050505; margin-right: auto; border-bottom-left-radius: 4px;}
.chat-input-area { padding: 15px 20px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: #f7f9fc;}
.chat-input-area textarea { flex: 1; resize: none; padding: 12px 15px; border-radius: 30px; border: 1.5px solid #ccd0d5; font-size: 16px; min-height: 50px;}
.chat-input-area textarea:focus { border-color: #1877f2; outline: none; }
.chat-input-area button { background: #1877f2; border: none; color: #fff; padding: 0 25px; border-radius: 30px; font-weight: 700; font-size: 18px; cursor: pointer; box-shadow: 0 4px 12px rgba(24,119,242,0.6); transition: background 0.3s ease, box-shadow 0.3s ease;}
.chat-input-area button:hover { background: #145db2; box-shadow: 0 6px 18px rgba(21,93,178,0.8);}
.conversas-list::-webkit-scrollbar, .chat-messages::-webkit-scrollbar { width: 6px;}
.conversas-list::-webkit-scrollbar-thumb, .chat-messages::-webkit-scrollbar-thumb { background-color: #ccd0d5; border-radius: 3px; }

/* Pesquisa de usuários */
.buscar-usuarios { padding: 10px 20px; border-bottom: 1px solid #ddd; }
.buscar-usuarios input { width: 100%; padding: 10px; border-radius: 20px; border: 1px solid #ccc; }
.buscar-usuarios-list { max-height: 200px; overflow-y: auto; background: #f7f9fc; }
.buscar-usuarios-list div { padding: 10px; border-bottom: 1px solid #ddd; cursor: pointer; transition: background 0.2s ease;}
.buscar-usuarios-list div:hover { background: #e7f3ff; }

/* Responsivo */
@media screen and (max-width: 900px) { .container { margin-left: 80px; max-width: 100%; height: 100vh; border-radius: 0; } .conversas-list { width: 250px; } }
@media screen and (max-width: 600px) { body { display: block; padding: 10px 0; } .sidebar { position: relative; width: 100%; height: auto; flex-direction: row; padding: 10px 0; box-shadow: none; } .sidebar:hover { width: 100%; } .sidebar a { justify-content: center; padding: 10px 15px; font-size: 14px; } .sidebar span { opacity: 1 !important; margin-left: 0;} .container { margin-left: 0; height: auto; flex-direction: column; } .conversas-list { width: 100%; height: 200px; border-right: none; border-bottom: 1px solid #ddd; } .chat-area { height: 400px; } }
</style>
</head>
<body>

<div class="sidebar">
    <a href="home.php" title="Home"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="perfil.php" title="Perfil"><i class="fas fa-user"></i><span>Perfil</span></a>
    <a href="chat.php" title="Chat"><i class="fas fa-comments"></i><span>Chat</span></a>
    <a href="index.php" title="Sair"><i class="fas fa-sign-out-alt"></i><span>Sair</span></a>
</div>

<div class="container">
    <div class="conversas-list" id="conversasList">
        <div class="buscar-usuarios">
            <input type="text" id="buscarUsuario" placeholder="Buscar usuários..." />
        </div>
        <div class="buscar-usuarios-list" id="buscarUsuariosList"></div>

        <?php if (count($conversas) === 0): ?>
            <p style="padding:20px; color:#606770;">Nenhuma conversa encontrada.</p>
        <?php else: ?>
            <?php foreach ($conversas as $c): ?>
                <div class="conversa-item" data-id="<?= $c['id'] ?>">
                    <span><?= htmlspecialchars(nomeOutroUsuario($c, $usuario_id)) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="chat-area">
        <div class="chat-header" id="chatHeader">Selecione uma conversa</div>
        <div class="chat-messages" id="chatMessages"></div>
        <form id="chatForm" class="chat-input-area" style="display:none;">
            <textarea id="msgInput" placeholder="Digite sua mensagem..."></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<script>
const usuarioId = <?= $usuario_id ?>;
let conversaAtivaId = null;
let fetchInterval = null;

const conversasList = document.getElementById('conversasList');
const chatMessages = document.getElementById('chatMessages');
const chatHeader = document.getElementById('chatHeader');
const chatForm = document.getElementById('chatForm');
const msgInput = document.getElementById('msgInput');
const buscarInput = document.getElementById('buscarUsuario');
const buscarList = document.getElementById('buscarUsuariosList');

// Selecionar conversa
function selecionarConversa(item) {
    if (conversaAtivaId === item.dataset.id) return;

    conversasList.querySelectorAll('.conversa-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');

    conversaAtivaId = item.dataset.id;
    chatHeader.textContent = item.textContent.trim();
    chatForm.style.display = 'flex';
    carregarMensagens(conversaAtivaId);

    if (fetchInterval) clearInterval(fetchInterval);
    fetchInterval = setInterval(() => { carregarMensagens(conversaAtivaId); }, 3000);
}

conversasList.querySelectorAll('.conversa-item').forEach(item => {
    item.addEventListener('click', () => selecionarConversa(item));
});

// Carregar mensagens
function carregarMensagens(conversaId) {
    fetch('chat_mensagens.php?conversa_id=' + conversaId)
    .then(res => res.json())
    .then(data => {
        chatMessages.innerHTML = '';
        data.forEach(msg => {
            const div = document.createElement('div');
            div.classList.add('message');
            div.classList.add(msg.remetente_id == usuarioId ? 'sent' : 'received');
            div.textContent = msg.mensagem;
            chatMessages.appendChild(div);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });
}

// Enviar mensagem
chatForm.addEventListener('submit', e => {
    e.preventDefault();
    const mensagem = msgInput.value.trim();
    if (!mensagem || !conversaAtivaId) return;

    fetch('chat_enviar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'conversa_id=' + conversaAtivaId + '&mensagem=' + encodeURIComponent(mensagem)
    }).then(res => res.text())
    .then(resp => {
        if (resp === 'sucesso') { msgInput.value = ''; carregarMensagens(conversaAtivaId); }
        else { alert('Erro ao enviar mensagem.'); }
    });
});

// Buscar usuários
buscarInput.addEventListener('input', () => {
    const termo = buscarInput.value.trim();
    if (!termo) { buscarList.innerHTML = ''; return; }

    fetch('buscar_usuario.php?nome=' + encodeURIComponent(termo))
    .then(res => res.json())
    .then(data => {
        buscarList.innerHTML = '';
        data.forEach(u => {
            const div = document.createElement('div');
            div.textContent = u.nome;
            div.dataset.id = u.id;
            div.addEventListener('click', () => iniciarConversa(u.id, u.nome));
            buscarList.appendChild(div);
        });
    });
});

// Iniciar conversa
function iniciarConversa(outroId, outroNome) {
    fetch('criar_conversa.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'usuario2_id=' + outroId
    }).then(res => res.json())
    .then(data => {
        if (data.conversa_id) {
            location.reload(); // recarrega para atualizar a lista de conversas
        } else {
            alert('Erro ao iniciar conversa.');
        }
    });
}
</script>

</body>
</html>
