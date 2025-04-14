<?php
require 'config.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die('Token inválido.');
}

// Verifica se o token existe e ainda está válido
$stmt = $pdo->prepare("SELECT usuario_id, expira_em FROM tokens_reset WHERE token = ?");
$stmt->execute([$token]);
$token_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$token_info) {
    die("Token inválido ou expirado.");
}

$usuario_id = $token_info['usuario_id'];
$expira_em = $token_info['expira_em'];

if (strtotime($expira_em) < time()) {
    die("Token expirado.");
}

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);

    // Atualiza a senha do usuário
    $stmt2 = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt2->execute([$nova_senha, $usuario_id]);

    // Invalida o token
    $stmt3 = $pdo->prepare("DELETE FROM tokens_reset WHERE token = ?");
    $stmt3->execute([$token]);

    echo "Senha alterada com sucesso!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Redefinir Senha</h2>
        <form method="POST" class="p-4 border rounded bg-white">
            <div class="mb-3">
                <label for="senha" class="form-label">Nova senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar nova senha</button>
        </form>
    </div>
</body>

</html>