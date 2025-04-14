<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'];

    // Verificar se a categoria já existe no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE nome = ?");
    $stmt->execute([$categoria]);
    if ($stmt->rowCount() > 0) {
        $erro = "Categoria já existe!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
        $stmt->execute([$categoria]);
        header('Location: adicionar.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Adicionar Categoria</h2>

    <?php if (isset($erro)) { echo "<p class='text-danger'>$erro</p>"; } ?>

    <form method="POST">
        <div class="form-group">
            <label for="categoria">Nome da Categoria</label>
            <input type="text" class="form-control" name="categoria" id="categoria" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Adicionar</button>
    </form>

    <a href="adicionar.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

</body>
</html>
