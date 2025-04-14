<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obter as categorias do banco de dados
$stmt = $pdo->prepare("SELECT * FROM categorias");
$stmt->execute();
$categorias = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];
    $data = $_POST['data'];
    $user_id = $_SESSION['user_id'];

    // Inserir a transação no banco de dados
    $stmt = $pdo->prepare("INSERT INTO transacoes (user_id, descricao, valor, tipo, categoria, data) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $descricao, $valor, $tipo, $categoria, $data]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Transação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Adicionar Transação</h2>

    <form method="POST">
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" class="form-control" name="descricao" id="descricao" required>
        </div>
        <div class="form-group">
            <label for="valor">Valor</label>
            <input type="number" class="form-control" name="valor" id="valor" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="tipo">Tipo</label>
            <select class="form-control" name="tipo" id="tipo" onchange="toggleCategoria()" required>
                <option value="receita">Receita</option>
                <option value="despesa">Despesa</option>
            </select>
        </div>
        <div class="form-group" id="categoria-container">
    <label for="categoria">Categoria</label>
    <select class="form-control" name="categoria" id="categoria">
        <!-- As categorias serão carregadas dinamicamente do banco de dados -->
        <?php foreach ($categorias as $categoria): ?>
            <option value="<?= htmlspecialchars($categoria['nome']) ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
        <?php endforeach; ?>
    </select>
    <a href="adicionar_categoria.php" class="btn btn-link mt-2">Nova Categoria</a> <!-- Botão Nova Categoria -->
</div>

        <div class="form-group">
            <label for="data">Data</label>
            <input type="date" class="form-control" name="data" id="data" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Adicionar</button>
    </form>

    <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
</div>
</body>
</html>
