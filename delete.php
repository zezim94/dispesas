<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Verificando a transação no banco
    $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
    $transacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transacao) {
        echo "Transação não encontrada.";
        exit;
    }

    // Caso o usuário já tenha confirmado a exclusão
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Excluindo a transação
        $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);

        header('Location: index.php');
        exit;
    }
} else {
    echo "ID inválido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Exclusão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .confirmation-container {
            max-width: 500px;
            margin: 50px auto;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .confirmation-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .btn-custom {
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h2>Confirmar Exclusão</h2>

        <p>Tem certeza de que deseja excluir a transação "<?php echo htmlspecialchars($transacao['descricao']); ?>"?</p>

        <form method="POST">
            <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
        </form>

        <div class="back-link">
            <a href="index.php">Cancelar</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
