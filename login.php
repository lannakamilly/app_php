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
<<<<<<< HEAD
            header("Location: feed.php"); 
=======
            header("Location: criar_post.php"); // Página principal após login
>>>>>>> ecdeef637b1deab8ccac4f496e7f18b978108854
            exit;
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login | ConectaTech</title>
<!-- Fonte moderna -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
<style>
  /* Reset */
  * {
    margin: 0; padding: 0; box-sizing: border-box;
  }

  body, html {
    height: 100%;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(270deg, #1e3c72, #2a5298, #1e3c72);
    background-size: 600% 600%;
    animation: gradientBG 15s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
  }

  @keyframes gradientBG {
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
  }

  .container {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 25px;
    box-shadow:
      0 8px 32px 0 rgba(31, 38, 135, 0.37),
      0 0 15px rgba(59, 130, 246, 0.6);
    width: 380px;
    padding: 50px 40px 60px 40px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow 0.3s ease;
  }

  .container:focus-within {
    box-shadow:
      0 12px 40px 0 rgba(59, 130, 246, 0.9),
      0 0 25px rgba(59, 130, 246, 0.9);
  }

  /* Logo com brilho */
  .logo {
    width: 130px;
    height: 130px;
    margin-bottom: 35px;
    background: url('logo.png') no-repeat center center;
    background-size: contain;
    border-radius: 50%;
    box-shadow:
      0 0 15px #3b82f6,
      0 0 30px #60a5fa,
      0 0 45px #93c5fd;
    animation: glow 3s ease-in-out infinite alternate;
  }

  @keyframes glow {
    from {
      box-shadow:
        0 0 15px #3b82f6,
        0 0 30px #60a5fa,
        0 0 45px #93c5fd;
    }
    to {
      box-shadow:
        0 0 25px #2563eb,
        0 0 40px #3b82f6,
        0 0 60px #60a5fa;
    }
  }

  h2 {
    font-weight: 600;
    font-size: 2.4rem;
    margin-bottom: 30px;
    letter-spacing: 1.2px;
    text-shadow: 0 2px 6px rgba(0,0,0,0.4);
  }

  form {
    width: 100%;
  }

  /* Float label style */
  .input-group {
    position: relative;
    margin-bottom: 30px;
  }

  input {
    width: 100%;
    padding: 16px 16px 16px 16px;
    font-size: 1rem;
    border-radius: 12px;
    border: 2px solid transparent;
    background: rgba(255, 255, 255, 0.15);
    color: #e0e7ff;
    font-weight: 500;
    outline: none;
    transition: border-color 0.3s ease, background-color 0.3s ease;
  }

  input::placeholder {
    color: transparent;
  }

  input:focus {
    background: rgba(255, 255, 255, 0.3);
    border-color: #3b82f6;
    box-shadow: 0 0 8px #3b82f6;
  }

  label {
    position: absolute;
    top: 50%;
    left: 16px;
    color: #a5b4fc;
    font-weight: 500;
    font-size: 1rem;
    pointer-events: none;
    transform: translateY(-50%);
    transition: 0.3s ease all;
    user-select: none;
  }

  input:focus + label,
  input:not(:placeholder-shown) + label {
    top: -8px;
    left: 12px;
    font-size: 0.8rem;
    color: #60a5fa;
    background: rgba(30, 60, 114, 0.8);
    padding: 0 6px;
    border-radius: 6px;
    text-shadow: 0 0 5px rgba(96, 165, 250, 0.8);
  }

  button {
    width: 100%;
    padding: 16px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(45deg, #3b82f6, #2563eb);
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow:
      0 4px 15px rgba(37, 99, 235, 0.7);
    transition: background 0.4s ease, box-shadow 0.4s ease;
    position: relative;
    overflow: hidden;
  }

  button::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -25%;
    width: 150%;
    height: 200%;
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(25deg);
    transition: all 0.5s ease;
  }

  button:hover::before {
    left: 125%;
  }

  button:hover {
    background: linear-gradient(45deg, #2563eb, #1e40af);
    box-shadow:
      0 6px 25px rgba(37, 99, 235, 0.9);
  }

  .mensagem {
    margin-bottom: 20px;
    color: #ff6b6b;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: shake 0.3s ease;
    text-shadow: 0 0 5px #ff6b6b;
  }

  .mensagem::before {
    content: "⚠️";
    margin-right: 8px;
    font-size: 1.3rem;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-8px); }
    40%, 80% { transform: translateX(8px); }
  }

  a {
    display: block;
    margin-top: 25px;
    text-align: center;
    color: #a5b4fc;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  a:hover {
    color: #dbeafe;
    text-decoration: underline;
  }

  /* Responsividade */
  @media (max-width: 420px) {
    .container {
      width: 90%;
      padding: 40px 30px 50px 30px;
    }
    .logo {
      width: 110px;
      height: 110px;
      margin-bottom: 30px;
    }
    h2 {
      font-size: 2rem;
    }
    button {
      font-size: 1rem;
      padding: 14px;
    }
  }
</style>
</head>
<body>
  <div class="container" role="main" aria-label="Tela de login ConectaTech">
    <div class="logo" aria-label="Logo ConectaTech"></div>
    <h2>Login</h2>
    <?php if($mensagem) echo "<div class='mensagem' role='alert'>$mensagem</div>"; ?>
    <form method="post" autocomplete="off" novalidate>
      <div class="input-group">
        <input type="email" id="email" name="email" placeholder=" " required autocomplete="email" />
        <label for="email">E-mail</label>
      </div>
      <div class="input-group">
        <input type="password" id="senha" name="senha" placeholder=" " required autocomplete="current-password" />
        <label for="senha">Senha</label>
      </div>
      <button type="submit" name="entrar" aria-label="Entrar no sistema">Entrar</button>
    </form>
    <a href="register.php">Não tem conta? Cadastre-se</a>
  </div>
</body>
</html>