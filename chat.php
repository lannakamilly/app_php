<?php
session_start();

// ⚡ Simulação: ID do usuário logado (pega do login de verdade)
$_SESSION['id'] = 1; // altere para o id do usuário logado
$meu_id = $_SESSION['id'];

// Conexão com banco
$host = "localhost";
$db   = "conectatech";
$user = "root";
$pass = "";
$conexao = new mysqli($host, $user, $pass, $db);
if ($conexao->connect_error) {
    die("Conexão falhou: " . $conexao->connect_error);
}

// 📌 Pega conversa atual (exemplo: usuário logado conversando com id=2)
$amigo_id = 2; 

// Verifica se já existe conversa entre os dois
$sql = "SELECT id FROM conversas 
        WHERE (usuario1_id=$meu_id AND usuario2_id=$amigo_id) 
           OR (usuario1_id=$amigo_id AND usuario2_id=$meu_id)";
$result = $conexao->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $conversa_id = $row['id'];
} else {
    $conexao->query("INSERT INTO conversas (usuario1_id, usuario2_id) VALUES ($meu_id, $amigo_id)");
    $conversa_id = $conexao->insert_id;
}

// =====================
// Envio de mensagem
// =====================
if (isset($_POST['mensagem'])) {
    $mensagem = $conexao->real_escape_string($_POST['mensagem']);
    $conexao->query("INSERT INTO mensagens (conversa_id, remetente_id, mensagem) 
                  VALUES ($conversa_id, $meu_id, '$mensagem')");
    exit;
}

// =====================
// Long Polling (recebe mensagens em tempo real)
// =====================
if (isset($_GET['longpoll'])) {
    $ultimo_id = intval($_GET['ultimo'] ?? 0);
    $timeout = 20; // 20 segundos de espera
    $start = time();

    while (time() - $start < $timeout) {
        $sql = "SELECT * FROM mensagens 
                WHERE conversa_id=$conversa_id AND id > $ultimo_id 
                ORDER BY id ASC";
        $res = $conexao->query($sql);

        if ($res->num_rows > 0) {
            $msgs = [];
            while ($row = $res->fetch_assoc()) {
                $msgs[] = $row;
            }
            echo json_encode($msgs);
            exit;
        }
        usleep(500000); // espera 0.5s antes de checar de novo
    }
    echo json_encode([]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat</title>
<style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
    .chat-container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; height: 80vh; }
    .mensagens { flex: 1; overflow-y: auto; padding: 10px; }
    .msg { margin: 5px 0; padding: 10px; border-radius: 8px; max-width: 70%; word-wrap: break-word; }
    .minha { background: #DCF8C6; margin-left: auto; }
    .dele { background: #eee; margin-right: auto; }
    form { display: flex; border-top: 1px solid #ddd; }
    input { flex: 1; padding: 10px; border: none; }
    button { padding: 10px 15px; border: none; background: #25D366; color: #fff; cursor: pointer; }
    button:hover { background: #1ebe5b; }
</style>
</head>
<body>
<div class="chat-container">
    <div class="mensagens" id="mensagens"></div>
    <form id="form-chat">
        <input type="text" name="mensagem" id="mensagem" placeholder="Digite sua mensagem..." autocomplete="off">
        <button type="submit">Enviar</button>
    </form>
</div>

<script>
let ultimoId = 0;

// Função para buscar mensagens em tempo real (long polling)
async function buscarMensagens() {
    try {
        const res = await fetch("chat.php?longpoll=1&ultimo=" + ultimoId);
        const data = await res.json();
        if (data.length > 0) {
            data.forEach(msg => {
                let div = document.createElement("div");
                div.classList.add("msg");
                div.classList.add(msg.remetente_id == <?php echo $meu_id; ?> ? "minha" : "dele");
                div.textContent = msg.mensagem;
                document.getElementById("mensagens").appendChild(div);
                ultimoId = msg.id;
            });
            document.getElementById("mensagens").scrollTop = document.getElementById("mensagens").scrollHeight;
        }
    } catch (e) {
        console.error("Erro:", e);
    } finally {
        buscarMensagens(); // chama de novo para continuar o long polling
    }
}
buscarMensagens();

// Enviar mensagem
document.getElementById("form-chat").addEventListener("submit", async e => {
    e.preventDefault();
    let msg = document.getElementById("mensagem").value;
    if (msg.trim() == "") return;
    await fetch("chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "mensagem=" + encodeURIComponent(msg)
    });
    document.getElementById("mensagem").value = "";
});
</script>
</body>
</html>
