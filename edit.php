<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $transacao = $stmt->fetch();

    if (!$transacao) {
        header('Location: index.php');
        exit;
    }
}

// Recuperar todas as categorias do banco de dados
$stmt_categorias = $pdo->prepare("SELECT * FROM categorias");
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];
    $data = $_POST['data'];

    $stmt = $pdo->prepare("UPDATE transacoes SET descricao = ?, valor = ?, tipo = ?, categoria = ?, data = ? WHERE id = ?");
    $stmt->execute([$descricao, $valor, $tipo, $categoria, $data, $id]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Transação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <h2 class="text-center">Editar Transação</h2>

        <form method="POST">
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" class="form-control" name="descricao" id="descricao" value="<?= htmlspecialchars($transacao['descricao']) ?>" required>
            </div>
            <div class="form-group">
                <label for="valor">Valor</label>
                <input type="number" class="form-control" name="valor" id="valor" value="<?= htmlspecialchars($transacao['valor']) ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select class="form-control" name="tipo" id="tipo" onchange="toggleCategoria()" required>
                    <option value="receita" <?= $transacao['tipo'] == 'receita' ? 'selected' : '' ?>>Receita</option>
                    <option value="despesa" <?= $transacao['tipo'] == 'despesa' ? 'selected' : '' ?>>Despesa</option>
                </select>
            </div>
            <div class="form-group" id="categoria-container">
                <label for="categoria">Categoria</label>
                <select class="form-control" name="categoria" id="categoria" required>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= $transacao['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="data">Data</label>
                <input type="date" class="form-control" name="data" id="data" value="<?= htmlspecialchars($transacao['data']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Salvar</button>
        </form>

        <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
    </div>

</body>

</html>