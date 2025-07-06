<?php
session_start();
require 'config.php';  // Aqui você inclui a configuração do seu banco de dados

if (!isset($_SESSION['user_id']) || $_SESSION['nivel'] !== 'adm') {
    header('Location: index.php');  // Redireciona para a página de login ou página normal
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Consulta para pegar os dados do usuário
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

    // Se a senha foi alterada, criamos o hash
    if (!empty($senha)) {
        $senha = password_hash($senha, PASSWORD_BCRYPT);  // Cria o hash da senha
    } else {
        // Se a senha não foi modificada, mantemos a senha atual
        $senha = $usuario['senha'];  // Mantém a senha atual no banco de dados
    }

    // Processamento da foto de perfil
    $img_perfil = $usuario['img_perfil']; // Mantém a imagem atual por padrão
    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {
        // Verificar se a imagem é válida
        $file_tmp = $_FILES['img_perfil']['tmp_name'];
        $file_name = $_FILES['img_perfil']['name'];
        $file_size = $_FILES['img_perfil']['size'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Defina as extensões permitidas
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        // Verifique se a extensão do arquivo é permitida
        if (in_array(strtolower($file_ext), $allowed_exts)) {
            // Defina o diretório onde a imagem será salva
            $upload_dir = 'uploads/';
            $new_file_name = uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;

            // Mova o arquivo para a pasta de uploads
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Atualize o caminho da imagem no banco de dados
                $img_perfil = $file_path;
            } else {
                echo "Erro ao enviar a imagem!";
                exit;
            }
        } else {
            echo "Formato de imagem não permitido!";
            exit;
        }
    }

    // Atualiza as informações no banco
    $query_update = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, NIVEL = :nivel, img_perfil = :img_perfil WHERE id = :id";
    $stmt_update = $pdo->prepare($query_update);

    // Bind dos parâmetros para a query de atualização
    $stmt_update->bindParam(':nome', $nome);
    $stmt_update->bindParam(':email', $email);
    $stmt_update->bindParam(':senha', $senha);
    $stmt_update->bindParam(':nivel', $nivel);
    $stmt_update->bindParam(':img_perfil', $img_perfil);
    $stmt_update->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt_update->execute();

    header('Location: admin.php');  // Redireciona para a página de administração
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
                    <option value="adm" <?= ($usuario['NIVEL'] === 'adm') ? 'selected' : '' ?>>Administrador</option>
                    <option value="user" <?= ($usuario['NIVEL'] === 'user') ? 'selected' : '' ?>>Usuário</option>
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
