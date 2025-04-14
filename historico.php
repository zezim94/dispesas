<?php
session_start();

// Verifica se o histórico está vazio
if (!isset($_SESSION['historico']) || empty($_SESSION['historico'])) {
    echo "<p class='text-center mt-5'>Nenhuma jogada registrada ainda.</p>";
    exit();
}

// Nome do jogador (pegar da última jogada)
$ultimo_nome = $_SESSION['historico'][count($_SESSION['historico']) - 1]['nome'] ?? 'Desconhecido';

// Verifica se é para mostrar todo o histórico
$mostrar_tudo = isset($_GET['tudo']);

// Filtra o histórico para exibir apenas as jogadas do usuário (caso não tenha ativado "Mostrar Tudo")
$historico_filtrado = $mostrar_tudo ? $_SESSION['historico'] : array_filter($_SESSION['historico'], function ($jogada) use ($ultimo_nome) {
    return $jogada['nome'] === $ultimo_nome;
});

// Pesquisa de jogadas
$pesquisa = $_GET['pesquisa'] ?? '';
if (!empty($pesquisa)) {
    $historico_filtrado = array_filter($historico_filtrado, function ($jogada) use ($pesquisa) {
        return stripos($jogada['jogada_usuario'], $pesquisa) !== false || stripos($jogada['resultado'], $pesquisa) !== false;
    });
}

// Limpar histórico ao clicar no botão
if (isset($_POST['limpar'])) {
    unset($_SESSION['historico']);
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
                <button type="submit" name="limpar" class="btn btn-danger">Limpar Histórico</button>
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
                        <td><?= htmlspecialchars($jogada['data_hora'] ?? '') ?></td>
                        <td><?= htmlspecialchars($jogada['nome'] ?? '') ?></td>
                        <td><?= ucfirst(htmlspecialchars($jogada['jogada_usuario'] ?? '')) ?></td>
                        <td><?= ucfirst(htmlspecialchars($jogada['jogada_computador'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($jogada['resultado'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="jogo.php" class="btn btn-primary mt-3">Voltar ao Jogo</a>
    </div>

</body>

</html>