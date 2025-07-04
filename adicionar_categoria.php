<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$erro = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = trim($_POST['categoria']);
    $user_id = $_SESSION['user_id'];

    // Verificar se a categoria já existe para o usuário logado
    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nome = :nome AND user_id = :user_id");
    $stmt->bindParam(':nome', $categoria);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $erro = "Categoria já existe!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, user_id) VALUES (:nome, :user_id)");
        $stmt->bindParam(':nome', $categoria);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        header('Location: adicionar.php');
        exit();
    }
}

// Buscar categorias para exibição
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Adicionar Categoria</h2>

    <?php if (!empty($erro)) { echo "<p class='text-danger'>$erro</p>"; } ?>

    <form method="POST">
        <div class="form-group">
            <label for="categoria">Nome da Categoria</label>
            <input type="text" class="form-control" name="categoria" id="categoria" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Adicionar</button>
    </form>

    <h3 class="mt-4">Categorias Cadastradas</h3>
    <table class="table table-bordered mt-2">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?= htmlspecialchars($categoria['nome']) ?></td>
                    <td>
                        <a href="editar_categoria.php?id=<?= $categoria['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="excluir_categoria.php?id=<?= $categoria['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="adicionar.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

</body>
</html>
