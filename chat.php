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
/* RESET e sidebar igual páginas anteriores */
* {
    margin: 0; padding: 0; box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body {
    background: #e9ebee;
    min-height: 100vh;
    display: flex;
}
.sidebar {
    position: fixed; left: 0; top: 0;
    width: 70px; height: 100%;
    background: #1877f2;
    display: flex; flex-direction: column; align-items: center;
    padding-top: 20px;
    transition: width 0.3s ease;
    overflow: hidden;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    z-index: 1000;
}
.sidebar:hover { width: 220px; }
.sidebar a {
    color: #fff; text-decoration: none;
    width: 100%; padding: 15px 25px;
    display: flex; align-items: center;
    transition: background 0.2s ease;
    border-radius: 0 25px 25px 0;
    margin-bottom: 8px;
    font-weight: 600; font-size: 16px;
}
.sidebar a:hover { background: rgba(255,255,255,0.2); }
.sidebar i {
    min-width: 30px; font-size: 22px; text-align: center;
}
.sidebar span {
    margin-left: 15px; opacity: 0;
    transition: opacity 0.3s ease; white-space: nowrap;
}
.sidebar:hover span { opacity: 1; }

/* Container principal */
.container {
    margin-left: 90px;
    flex: 1;
    display: flex;
    height: 100vh;
    max-width: 1200px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Lista de conversas */
.conversas-list {
    width: 320px;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    background: #f7f9fc;
}

.conversa-item {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
    transition: background 0.2s ease;
}

.conversa-item:hover,
.conversa-item.active {
    background: #e7f3ff;
}

.conversa-item span {
    font-weight: 600;
    color: #1877f2;
}

/* Área de mensagens */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    font-weight: 700;
    font-size: 20px;
    color: #1877f2;
    background: #f7f9fc;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f0f2f5;
}

.message {
    max-width: 70%;
    margin-bottom: 15px;
    padding: 12px 18px;
    border-radius: 20px;
    font-size: 15px;
    line-height: 1.4;
    word-wrap: break-word;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message.sent {
    background: #1877f2;
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.message.received {
    background: #e4e6eb;
    color: #050505;
    margin-right: auto;
    border-bottom-left-radius: 4px;
}

.chat-input-area {
    padding: 15px 20px;
    border-top: 1px solid #ddd;
    display: flex;
    gap: 10px;
    background: #f7f9fc;
}

.chat-input-area textarea {
    flex: 1;
    resize: none;
    padding: 12px 15px;
    border-radius: 30px;
    border: 1.5px solid #ccd0d5;
    font-size: 16px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    transition: border-color 0.3s ease;
    min-height: 50px;
}

.chat-input-area textarea:focus {
    border-color: #1877f2;
    outline: none;
}

.chat-input-area button {
    background: #1877f2;
    border: none;
    color: #fff;
    padding: 0 25px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 18px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(24,119,242,0.6);
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

.chat-input-area button:hover {
    background: #145db2;
    box-shadow: 0 6px 18px rgba(21,93,178,0.8);
}

/* Scrollbar */
.conversas-list::-webkit-scrollbar,
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.conversas-list::-webkit-scrollbar-thumb,
.chat-messages::-webkit-scrollbar-thumb {
    background-color: #ccd0d5;
    border-radius: 3px;
}

/* Responsivo */
@media screen and (max-width: 900px) {
    .container {
        margin-left: 80px;
        max-width: 100%;
        height: 100vh;
        border-radius: 0;
    }
    .conversas-list {
        width: 250px;
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
        height: auto;
        flex-direction: column;
    }
    .conversas-list {
        width: 100%;
        height: 200px;
        border-right: none;
        border-bottom: 1px solid #ddd;
    }
    .chat-area {
        height: 400px;
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

<div class="container" role="main" aria-label="Área de chat">

    <div class="conversas-list" id="conversasList" aria-label="Lista de conversas">
        <?php if (count($conversas) === 0): ?>
            <p style="padding:20px; color:#606770;">Nenhuma conversa encontrada. Inicie uma nova conversa no perfil de algum usuário.</p>
        <?php else: ?>
            <?php foreach ($conversas as $c): ?>
                <div class="conversa-item" data-id="<?= $c['id'] ?>" tabindex="0" role="button" aria-pressed="false">
                    <span><?= htmlspecialchars(nomeOutroUsuario($c, $usuario_id)) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="chat-area" aria-live="polite" aria-atomic="false">
        <div class="chat-header" id="chatHeader" aria-label="Cabeçalho da conversa">
            Selecione uma conversa
        </div>
        <div class="chat-messages" id="chatMessages" aria-label="Mensagens da conversa" tabindex="0" style="overflow-y:auto;">

        </div>
        <form id="chatForm" class="chat-input-area" style="display:none;" aria-label="Formulário para enviar mensagem">
            <textarea id="msgInput" placeholder="Digite sua mensagem..." aria-label="Campo para digitar mensagem" required></textarea>
            <button type="submit" aria-label="Enviar mensagem"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>

</div>

<script>
const conversasList = document.getElementById('conversasList');
const chatMessages = document.getElementById('chatMessages');
const chatHeader = document.getElementById('chatHeader');
const chatForm = document.getElementById('chatForm');
const msgInput = document.getElementById('msgInput');

let conversaAtivaId = null;
let fetchInterval = null;

// Função para carregar mensagens da conversa
function carregarMensagens(conversaId) {
    fetch('chat_mensagens.php?conversa_id=' + conversaId)
    .then(res => res.json())
    .then(data => {
        chatMessages.innerHTML = '';
        data.forEach(msg => {
            const div = document.createElement('div');
            div.classList.add('message');
            div.classList.add(msg.remetente_id == <?= $usuario_id ?> ? 'sent' : 'received');
            div.textContent = msg.mensagem;
            chatMessages.appendChild(div);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });
}

// Selecionar conversa
conversasList.querySelectorAll('.conversa-item').forEach(item => {
    item.addEventListener('click', () => {
        if (conversaAtivaId === item.dataset.id) return;

        // Remove active de todos
        conversasList.querySelectorAll('.conversa-item').forEach(i => {
            i.classList.remove('active');
            i.setAttribute('aria-pressed', 'false');
        });
        item.classList.add('active');
        item.setAttribute('aria-pressed', 'true');

        conversaAtivaId = item.dataset.id;
        chatHeader.textContent = item.textContent.trim();
        chatForm.style.display = 'flex';
        carregarMensagens(conversaAtivaId);

        // Limpa intervalo anterior
        if (fetchInterval) clearInterval(fetchInterval);
        fetchInterval = setInterval(() => {
            carregarMensagens(conversaAtivaId);
        }, 3000);
    });
});

// Enviar mensagem
chatForm.addEventListener('submit', e => {
    e.preventDefault();
    const mensagem = msgInput.value.trim();
    if (!mensagem || !conversaAtivaId) return;

    fetch('chat_enviar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'conversa_id=' + conversaAtivaId + '&mensagem=' + encodeURIComponent(mensagem)
    })
    .then(res => res.text())
    .then(resp => {
        if (resp === 'sucesso') {
            msgInput.value = '';
            carregarMensagens(conversaAtivaId);
        } else {
            alert('Erro ao enviar mensagem.');
        }
    });
});
</script>

</body>
</html>