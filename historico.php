<?php
session_start();
require "config.php"; // Certifique-se de que a configuração de conexão com o banco de dados esteja correta

// Verifica se há um nome do usuário na sessão (caso contrário, redireciona)
$ultimo_nome = $_SESSION['nome_usuario'] ?? 'Desconhecido'; // Aqui você pode pegar o nome do usuário de algum lugar da sessão

// Verifica se é para mostrar todo o histórico
$mostrar_tudo = isset($_GET['tudo']);

// Pesquisa de jogadas
$pesquisa = $_GET['pesquisa'] ?? '';

// Query básica para obter as jogadas
$query = "SELECT * FROM historico_jogadas WHERE nome = :nome";

// Adiciona filtro de pesquisa, se necessário
if (!empty($pesquisa)) {
    $query .= " AND (jogada_usuario ILIKE :pesquisa OR resultado ILIKE :pesquisa)";
}

// Verifica se deve exibir todo o histórico ou apenas o do último usuário
if ($mostrar_tudo) {
    $query = "SELECT * FROM historico_jogadas"; // Mostra todo o histórico
}

// Prepara a query
$stmt = $pdo->prepare($query);

// Liga os parâmetros da consulta
$stmt->bindParam(':nome', $ultimo_nome, PDO::PARAM_STR);

if (!empty($pesquisa)) {
    $pesquisa = "%" . $pesquisa . "%";  // Adiciona os "%" para a busca parcial
    $stmt->bindParam(':pesquisa', $pesquisa, PDO::PARAM_STR);
}

// Executa a consulta
$stmt->execute();

// Busca os resultados
$historico_filtrado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Limpar histórico ao clicar no botão
if (isset($_POST['limpar'])) {
    $stmt_delete = $pdo->prepare("DELETE FROM historico_jogadas WHERE nome = :nome");
    $stmt_delete->bindParam(':nome', $ultimo_nome, PDO::PARAM_STR);
    $stmt_delete->execute();
    header("Location: historico.php"); // Recarrega a página após limpar
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Jogadas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>

    <div class="container">
        <h1 class="mt-5 text-center">Histórico de Jogadas</h1>

        <!-- Campo de pesquisa -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar jogadas..." value="<?= htmlspecialchars($pesquisa) ?>">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
            </div>
        </form>

        <!-- Botões de ação -->
        <div class="mb-3 d-flex flex-wrap justify-content-between">
            <div class="btn-group mb-2 mr-2">
                <a href="historico.php" class="btn btn-secondary">Mostrar apenas minhas jogadas</a>
            </div>
            <div class="btn-group mb-2 mr-2">
                <a href="historico.php?tudo=1" class="btn btn-info">Mostrar todo o histórico</a>
            </div>
            <div class="btn-group mb-2 mr-2">
                <a href="jogo.php" class="btn btn-info">Novo jogo</a>
            </div>
            <div class="btn-group mb-2 mr-2">
                <a href="index.php" class="btn btn-info">Menu</a>
            </div>
            <div class="btn-group mb-2">
                <form method="POST">
                    <button type="submit" name="limpar" class="btn btn-danger">Limpar Histórico</button>
                </form>
            </div>
        </div>

        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Data e Hora</th>
                    <th>Nome</th>
                    <th>Jogada do Usuário</th>
                    <th>Jogada do Computador</th>
                    <th>Resultado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico_filtrado as $jogada): ?>
                    <tr>
                        <td><?= htmlspecialchars($jogada['data_hora']) ?></td>
                        <td><?= htmlspecialchars($jogada['nome']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($jogada['jogada_usuario'])) ?></td>
                        <td><?= ucfirst(htmlspecialchars($jogada['jogada_computador'])) ?></td>
                        <td><?= htmlspecialchars($jogada['resultado']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="jogo.php" class="btn btn-primary mt-3">Voltar ao Jogo</a>
    </div>

</body>

</html>
