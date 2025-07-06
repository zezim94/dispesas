<?php
session_start();
require 'config.php'; // Conexão com banco de dados

// Verifica se o usuário é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['nivel'] !== 'adm') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query_usuario = "SELECT * FROM usuarios WHERE id = :id";
    $stmt_usuario = $pdo->prepare($query_usuario);
    $stmt_usuario->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_usuario->execute();
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado!";
        exit;
    }
}

// Atualização dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel = $_POST['nivel'];
    $senha = $_POST['senha'];

    $senha = !empty($senha) ? password_hash($senha, PASSWORD_BCRYPT) : $usuario['senha'];
    $img_perfil = $usuario['img_perfil']; // padrão: manter imagem

    // Processar nova imagem de perfil se enviada
    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['img_perfil']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['img_perfil']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_exts)) {
            // Cloudinary API
            $cloud_name = 'ddurjywqx';
            $api_key = '292648758627711';
            $api_secret = 'KJEYTdqQDh_0QFphlQnrBbW1_mM';
            $timestamp = time();

            // Gera a assinatura
            $params_to_sign = "timestamp=$timestamp" . $api_secret;
            $signature = sha1($params_to_sign);

            $post_fields = [
                'file' => new CURLFile($file_tmp),
                'api_key' => $api_key,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ];

            $url = "https://api.cloudinary.com/v1_1/$cloud_name/image/upload";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // opcional para local/dev

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (isset($result['secure_url'])) {
                $img_perfil = $result['secure_url'];
            } else {
                echo "Erro ao enviar a imagem para Cloudinary!";
                exit;
            }
        } else {
            echo "Formato de imagem não permitido!";
            exit;
        }
    }

    // Atualizar no banco
    $query_update = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, nivel = :nivel, img_perfil = :img_perfil WHERE id = :id";
    $stmt_update = $pdo->prepare($query_update);
    $stmt_update->bindParam(':nome', $nome);
    $stmt_update->bindParam(':email', $email);
    $stmt_update->bindParam(':senha', $senha);
    $stmt_update->bindParam(':nivel', $nivel);
    $stmt_update->bindParam(':img_perfil', $img_perfil);
    $stmt_update->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_update->execute();

    header('Location: adm.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>

    <div class="container mt-5">
        <h2>Editar Usuário</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha (deixe em branco para não alterar)</label>
                <input type="password" class="form-control" id="senha" name="senha">
            </div>
            <div class="form-group">
                <label for="nivel">Nível</label>
                <select class="form-control" id="nivel" name="nivel" required>
                    <option value="adm" <?= ($usuario['nivel'] === 'adm') ? 'selected' : '' ?>>Administrador</option>
                    <option value="user" <?= ($usuario['nivel'] === 'user') ? 'selected' : '' ?>>Usuário</option>
                </select>
            </div>
            <div class="form-group">
                <label for="img_perfil">Imagem de Perfil</label>
                <input type="file" class="form-control" id="img_perfil" name="img_perfil">
                <small class="form-text text-muted">Deixe em branco para manter a imagem atual</small>
                <br>
                <?php if ($usuario['img_perfil']): ?>
                    <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Imagem de Perfil" style="width: 100px; height: 100px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>

</html>
