<?php
$host = "SEU_ENDPOINT_DO_RDS"; // copiado do RDS
$user = "admin";               // usuário mestre
$pass = "SUA_SENHA";
$db   = "NOME_DO_SEU_BANCO";

$conexao = new mysqli($host, $user, $pass, $db);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
