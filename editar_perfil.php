<?php
session_start();
require_once 'config.php'; // Arquivo com a conexão ao banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redireciona para a página inicial se não estiver logado
    exit();
}
// Busca os dados do usuário no banco ao abrir a página
try {
    $stmt = $pdo->prepare("SELECT nome, email, img_perfil FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['foto'] = $usuario['img_perfil'];
    }
} catch (PDOException $e) {
    echo "Erro ao buscar os dados: " . $e->getMessage();
    exit;
}
// Variáveis para armazenar mensagens de erro
$erroNome = $erroEmail = $erroSenha = $erroConfirmarSenha = $erroFoto = '';

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação e atualização do nome
    if (!empty($_POST['nome'])) {
        $_SESSION['nome'] = trim($_POST['nome']);
    } else {
        $erroNome = "Nome não pode estar vazio.";
    }

    // Validação e atualização do email
    if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['email'] = trim($_POST['email']);
    } elseif (!empty($_POST['email'])) {
        $erroEmail = "Email inválido.";
    }

    // Validação e atualização da senha
    if (!empty($_POST['senha']) && strlen($_POST['senha']) >= 6) {
        if ($_POST['senha'] === $_POST['confirmar_senha']) {
            $_SESSION['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
        } else {
            $erroConfirmarSenha = "As senhas não coincidem.";
        }
    } elseif (!empty($_POST['senha'])) {
        $erroSenha = "A senha deve ter no mínimo 6 caracteres.";
    }

    // Validação e upload da foto de perfil
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoTemp = $_FILES['foto']['tmp_name'];
        $fotoNome = $_FILES['foto']['name'];
        $fotoTipo = $_FILES['foto']['type'];
        $fotoTamanho = $_FILES['foto']['size'];

        // Verifica se o arquivo é uma imagem
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (in_array($fotoTipo, $tiposPermitidos)) {
            // Verifica o tamanho da imagem (máximo de 10MB)
            if ($fotoTamanho <= 10 * 1024 * 1024) {
                $pastaUploads = 'uploads/';
                $fotoCaminho = $pastaUploads . basename($fotoNome);

                // Move a foto para a pasta 'uploads'
                if (move_uploaded_file($fotoTemp, $fotoCaminho)) {
                    $_SESSION['foto'] = $fotoCaminho; // Armazena o caminho da foto na sessão
                } else {
                    $erroFoto = "Erro ao mover o arquivo para a pasta de uploads.";
                }
            } else {
                $erroFoto = "A imagem deve ter no máximo 10MB.";
            }
        } else {
            $erroFoto = "Apenas imagens JPEG, PNG e GIF são permitidas.";
        }
    }

    // Atualiza os dados no banco de dados
    if (empty($erroNome) && empty($erroEmail) && empty($erroSenha) && empty($erroConfirmarSenha) && empty($erroFoto)) {
        try {
            // Prepara a query para atualizar os dados do usuário
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, img_perfil = :img_perfil WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            // Bind dos parâmetros
            $stmt->bindParam(':nome', $_SESSION['nome']);
            $stmt->bindParam(':email', $_SESSION['email']);
            $stmt->bindParam(':senha', $_SESSION['senha']);
            $stmt->bindParam(':img_perfil', $_SESSION['foto']);
            $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);

            // Executa a query
            $stmt->execute();

            // Redireciona para a página de perfil após a atualização
            header("Location: editar_perfil.php");
            exit();
        } catch (PDOException $e) {
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .form-container h1 {
            color: #007bff;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 1.1rem;
            font-weight: bold;
            color: #495057;
        }

        .form-group input {
            font-size: 1rem;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 100%;
        }

        .form-group input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn {
            width: 100%;
            padding: 12px;
            font-size: 1.1rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .text-danger {
            color: red;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .foto-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .foto-container img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .back-btn {
            text-align: center;
            margin-top: 20px;
        }

        .back-btn a {
            font-size: 1rem;
            color: #007bff;
            text-decoration: none;
        }

        .back-btn a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h1>Editar Perfil</h1>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?= isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : '' ?>" required>
                <?php if (!empty($erroNome)): ?>
                    <p class="text-danger"><?= $erroNome ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>" required>
                <?php if (!empty($erroEmail)): ?>
                    <p class="text-danger"><?= $erroEmail ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="senha">Nova Senha (mínimo 6 caracteres):</label>
                <input type="password" id="senha" name="senha">
                <?php if (!empty($erroSenha)): ?>
                    <p class="text-danger"><?= $erroSenha ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha">
                <?php if (!empty($erroConfirmarSenha)): ?>
                    <p class="text-danger"><?= $erroConfirmarSenha ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group foto-container">
                <label for="foto">Foto de Perfil:</label>
                <input type="file" id="foto" name="foto" accept="image/*">
                <?php if (!empty($_SESSION['foto'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['foto']) ?>" alt="Foto de Perfil">
                <?php endif; ?>
                <?php if (!empty($erroFoto)): ?>
                    <p class="text-danger"><?= $erroFoto ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn">Atualizar Perfil</button>
        </form>

        <div class="back-btn">
            <a href="index.php">Voltar</a>
        </div>
    </div>

</body>

</html>
