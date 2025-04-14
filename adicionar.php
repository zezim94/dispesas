<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Obter apenas as categorias do usuário autenticado
$stmt = $pdo->prepare("SELECT id, nome FROM categorias WHERE user_id = ? ORDER BY nome");
$stmt->execute([$user_id]);
$categorias = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria_id'];
    $data = $_POST['data'];
    $meses = (int) $_POST['quantidade_meses'];

    $dataAtual = new DateTime($data);
    
    for ($i = 0; $i < $meses; $i++) {
        $stmt = $pdo->prepare("INSERT INTO transacoes (user_id, descricao, valor, tipo, categoria_id, data) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $descricao, $valor, $tipo, $categoria, $dataAtual->format('Y-m-d')]);
        $dataAtual->modify('+1 month');
    }

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
            <select class="form-control" name="tipo" id="tipo" required>
                <option value="receita">Receita</option>
                <option value="despesa">Despesa</option>
            </select>
        </div>
        <div class="form-group">
            <label for="categoria">Categoria</label>
            <select class="form-control" name="categoria_id" id="categoria">
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= htmlspecialchars($categoria['id']) ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <a href="adicionar_categoria.php" class="btn btn-success btn-sm mt-2">
                <i class="fas fa-plus"></i> Nova Categoria
            </a>
        </div>
        <div class="form-group">
            <label for="data">Data Inicial</label>
            <input type="date" class="form-control" name="data" id="data" required>
        </div>
        <div class="form-group">
    <label for="quantidade_meses">Inserir por quantos meses?</label>
    <input type="number" class="form-control" name="quantidade_meses" id="quantidade_meses" min="1" value="1" required>
</div>

        <button type="submit" class="btn btn-primary mt-3">Adicionar</button>
    </form>

    <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
</div>
</body>
</html>
