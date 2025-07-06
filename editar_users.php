<?php
session_start();
require 'config.php';  // Configuração do banco

// Função para upload no Imgur
function uploadToImgur($file_tmp) {
    $client_id = 'SEU_CLIENT_ID_AQUI'; // Substitua pelo seu Client ID correto do Imgur

    $image_data = base64_encode(file_get_contents($file_tmp));

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.imgur.com/3/image",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Client-ID $client_id"
        ],
        CURLOPT_POSTFIELDS => [
            'image' => $image_data,
            'type' => 'base64'
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($response, true);

    if (isset($json['data']['link'])) {
        return $json['data']['link']; // URL da imagem hospedada no Imgur
    } else {
        return false; // Falha no upload
    }
}

// Verifica se está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['nivel'] !== 'adm') {
    header('Location: index.php');
    exit;
}

// Pega o id do usuário a ser editado
if (!isset($_GET['id'])) {
    echo "ID do usuário não especificado!";
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel = $_POST['nivel'];
    $senha = $_POST['senha'];

    if (!empty($senha)) {
        $senha = password_hash($senha, PASSWORD_BCRYPT);
    } else {
        $senha = $usuario['senha'];
    }

    $img_perfil = $usuario['img_perfil'];

    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['img_perfil']['tmp_name'];
        $file_name = $_FILES['img_perfil']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_exts)) {
            $url_img = uploadToImgur($file_tmp);

            if ($url_img !== false) {
                $img_perfil = $url_img;
            } else {
                echo "Erro ao enviar a imagem para Imgur!";
                exit;
            }
        } else {
            echo "Formato de imagem não permitido!";
            exit;
        }
    }

    $query_update = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, nivel = :nivel, img_perfil = :img_perfil WHERE id = :id";
    $stmt_update = $pdo->prepare($query_update);
    $stmt_update->bindParam(':nome', $nome);
    $stmt_update->bindParam(':email', $email);
    $stmt_update->bindParam(':senha', $senha);
    $stmt_update->bindParam(':nivel', $nivel);
    $stmt_update->bindParam(':img_perfil', $img_perfil);
    $stmt_update->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_update->execute();

    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
</head>

<body>
    <div class="container mt-5">
        <h2>Editar Usuário</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required />
            </div>
            <div class="form-group">
                <label for="senha">Senha (deixe em branco para não alterar)</label>
                <input type="password" class="form-control" id="senha" name="senha" />
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
                <input type="file" class="form-control" id="img_perfil" name="img_perfil" />
                <small class="form-text text-muted">Deixe em branco para manter a imagem atual</small>
                <br />
                <?php if ($usuario['img_perfil']): ?>
                    <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Imagem de Perfil" style="width: 100px; height: 100px;" />
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
        </form>
    </div>
</body>

</html>
