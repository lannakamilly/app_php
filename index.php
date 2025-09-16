<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conexao.php"; // precisa devolver $conexao como PDO

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não logado!");
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar posts
$stmt = $conexao->prepare("
    SELECT p.id, p.usuario_id, p.conteudo, p.imagem, p.criado_em, u.nome 
    FROM posts p
    JOIN users u ON p.usuario_id = u.id
    ORDER BY p.criado_em DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Feed de Posts</h2>

<?php if (count($posts) > 0): ?>
    <?php foreach ($posts as $post): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <p><strong><?php echo htmlspecialchars($post['nome']); ?></strong></p>
            <p><?php echo nl2br(htmlspecialchars($post['conteudo'])); ?></p>
            
            <?php if (!empty($post['imagem'])): ?>
                <img src="assets/<?php echo htmlspecialchars($post['imagem']); ?>" 
                     alt="Imagem do post" 
                     style="max-width:300px; display:block; margin-top:10px;">
            <?php endif; ?>

            <small>Postado em: <?php echo $post['criado_em']; ?></small>

            <!-- Botões de Ação (apenas se o post for do usuário logado) -->
            <?php if ($post['usuario_id'] == $usuario_id): ?>
                <div style="margin-top:10px;">
                    <a href="editar_post.php?id=<?php echo $post['id']; ?>" 
                       style="padding:5px 10px; background:blue; color:white; text-decoration:none; margin-right:5px;">
                       Editar
                    </a>
                    
                    <a href="excluir_post.php?id=<?php echo $post['id']; ?>" 
                       onclick="return confirm('Tem certeza que deseja excluir este post?');"
                       style="padding:5px 10px; background:red; color:white; text-decoration:none;">
                       Excluir
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nenhum post encontrado.</p>
<?php endif; ?>
