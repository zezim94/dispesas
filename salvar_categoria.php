<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "Acesso negado!";
    exit;
}

if (isset($_POST['id'], $_POST['nome'])) {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $user_id = $_SESSION['user_id'];

    // Atualizando a categoria no banco de dados
    $stmt = $pdo->prepare("UPDATE categorias SET nome = :nome WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Executando a consulta
    if ($stmt->execute()) {
        echo "Categoria atualizada com sucesso!";
    } else {
        echo "Erro ao atualizar categoria.";
    }
}
?>
