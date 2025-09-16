<?php
include 'conexao.php';

try {
    $stmt = $conn->query("SELECT NOW()");
    $data = $stmt->fetch();
    echo "Conexão OK - hora do servidor: " . $data[0];
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
