<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Postagem estilo Instagram</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap');

/* RESET */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Roboto', sans-serif; }

body {
    display: flex;
    background: #e0f2ff;
    min-height: 100vh;
}

/* NAV LATERAL */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 70px;
    height: 100%;
    background: #007bff;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 20px;
    transition: width 0.3s;
    overflow: hidden;
}

.sidebar:hover { width: 200px; }

.sidebar a {
    color: #fff;
    text-decoration: none;
    width: 100%;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    transition: background 0.2s;
    border-radius: 0 25px 25px 0;
    margin-bottom: 5px;
}

.sidebar a:hover { background: rgba(255,255,255,0.2); }

.sidebar i {
    min-width: 30px;
    font-size: 20px;
    text-align: center;
}

.sidebar span {
    margin-left: 10px;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.3s;
}

.sidebar:hover span { opacity: 1; }

/* CARD POSTAGEM */
.container {
    margin-left: 80px;
    padding: 50px 20px;
    flex: 1;
    display: flex;
    justify-content: center;
}

.card {
    background: #ffffff;
    width: 100%;
    max-width: 500px;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    padding: 20px;
    box-sizing: border-box;
    transition: transform 0.3s;
}

.card:hover { transform: translateY(-5px); }

.card h2 {
    margin-top: 0;
    font-size: 1.6rem;
    color: #007bff;
    margin-bottom: 15px;
    text-align: center;
}

textarea {
    width: 100%;
    resize: none;
    height: 100px;
    padding: 15px;
    border-radius: 15px;
    border: 1px solid #ccc;
    font-size: 1rem;
    box-sizing: border-box;
    margin-bottom: 15px;
    transition: border 0.3s, box-shadow 0.3s;
}

textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.5);
}

input[type="file"] { margin-bottom: 15px; }

button {
    width: 100%;
    background: linear-gradient(135deg, #007bff, #00d4ff);
    color: #fff;
    padding: 12px 0;
    border: none;
    border-radius: 15px;
    font-size: 1.1rem;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s, transform 0.2s;
}

button:hover {
    background: linear-gradient(135deg, #0056b3, #00bfff);
    transform: translateY(-2px);
}

/* RESPONSIVO */
@media (max-width: 480px) {
    .sidebar { width: 50px; }
    .sidebar:hover { width: 150px; }
    .sidebar span { display: none; }
    .container { margin-left: 60px; padding: 20px 10px; }
    textarea { height: 80px; }
}
</style>
</head>
<body>

<!-- NAVBAR LATERAL -->
<div class="sidebar">
    <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="criar_post.php"><i class="fas fa-edit"></i><span>Editar</span></a>
    <a href="index.php"><i class="fas fa-plus-square"></i><span>Publicar</span></a>
    <a href="perfil.php"><i class="fas fa-user"></i><span>Perfil</span></a>
</div>

<!-- CARD POSTAGEM -->
<div class="container">
    <div class="card">
        <h2>Compartilhe algo</h2>
        <form method="POST" enctype="multipart/form-data">
            <textarea name="conteudo" placeholder="O que você está pensando?" required></textarea>
            <input type="file" name="imagem" accept="image/*">
            <button type="submit">Postar</button>
        </form>
    </div>
</div>

</body>
</html>
