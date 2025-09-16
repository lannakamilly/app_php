<?php
// Conexão com o banco
$host = "localhost";
$db   = "conectatech";
$user = "root";
$pass = "";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Login
$mensagem = "";
if (isset($_POST['entrar'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $user = $resultado->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            header("Location: ../../"); 
        } else {
            $mensagem = "Senha incorreta!";
        }
    } else {
        $mensagem = "E-mail não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login | ConectaTech</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { width: 100%; padding: 10px; background: #28a745; border: none; color: #fff; border-radius: 5px; cursor: pointer; }
        button:hover { background: #218838; }
        .mensagem { text-align: center; margin: 10px 0; color: red; }
        a { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if($mensagem) echo "<div class='mensagem'>$mensagem</div>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" name="entrar">Entrar</button>
        </form>
        <a href="register.php">Não tem conta? Cadastre-se</a>
    </div>
</body>
</html>
