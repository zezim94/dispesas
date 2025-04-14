<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'] ?? 0;

    // Buscar nome e e-mail do cliente direto da tabela pedidos
    $stmt = $pdo->prepare("SELECT nome, email FROM pedidos WHERE id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        http_response_code(400);
        echo "Pedido não encontrado.";
        exit;
    }

    $nome = $pedido['nome'];
    $email = $pedido['email'];

    // Verifica se já existe um usuário com esse e-mail
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Cria usuário com senha genérica (será redefinida depois)
        $senha_hash = password_hash('temporario', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$nome, $email, $senha_hash]);
        $usuario_id = $pdo->lastInsertId();
    } else {
        $usuario_id = $usuario['id'];
    }

    // Atualiza o pedido para "Ativo"
    $stmt = $pdo->prepare("UPDATE pedidos SET status = 'Ativo' WHERE id = ?");
    $stmt->execute([$pedido_id]);

    // Gera token de redefinição de senha
    $token = bin2hex(random_bytes(32));
    $expira_em = date('Y-m-d H:i:s', time() + 3600); // 1 hora

    $stmt = $pdo->prepare("INSERT INTO tokens_reset (usuario_id, token, expira_em) VALUES (?, ?, ?)");
    $stmt->execute([$usuario_id, $token, $expira_em]);

    // Monta o link de redefinição
    $apiUrl = "https://php-py-xwtc.onrender.com/api/enviar_email";
    $link = "localhost/novo_dispesas/redefinir_senha.php?token=$token";
    $mensagem = "Olá <strong>$nome</strong>,<br><br>Sua conta foi ativada com sucesso!<br>
    Clique no link abaixo para definir sua senha de acesso:<br><br>
    <a href='$link'>$link</a><br><br>Este link expira em 1 hora.";

    $postData = [
        'destinatarios' => $email,
        'assunto' => "Defina sua senha de acesso",
        'mensagem' => $mensagem
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $resposta = curl_exec($ch);
    curl_close($ch);

    echo "Usuário ativado e link de redefinição de senha enviado para: $email";
}
