<?php
session_start();

// Verifica se o jogador está selecionado
if (!isset($_SESSION['nome']) || $_SESSION['nome'] === '') {
    header('Location: index.php'); // Redireciona se não houver jogador selecionado
    exit();
}

// Função para limpar o histórico do jogador atual
if (isset($_POST['limpar_historico'])) {
    $_SESSION['jogadores'][$_SESSION['nome']]['historico'] = [];
}

// Verifica se o histórico de um jogador específico deve ser mostrado
$jogadorSelecionado = $_SESSION['nome'];
if (isset($_POST['jogador'])) {
    $jogadorSelecionado = $_POST['jogador'];
}
$historico = isset($_SESSION['jogadores'][$jogadorSelecionado]['historico']) ? $_SESSION['jogadores'][$jogadorSelecionado]['historico'] : [];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Perguntas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Histórico de Perguntas de <?= htmlspecialchars($jogadorSelecionado) ?></h1>

        <!-- Formulário para limpar o histórico -->
        <form method="POST" class="mb-3">
            <button type="submit" name="limpar_historico" class="btn btn-danger">Limpar Histórico</button>
        </form>

        <!-- Exibição do histórico -->
        <?php if (empty($historico)): ?>
            <div class="alert alert-warning mt-3">
                Nenhuma pergunta respondida ainda.
            </div>
        <?php else: ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Pergunta</th>
                        <th>Resposta do Jogador</th>
                        <th>Resposta Correta</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['pergunta']) ?></td>
                            <td><?= htmlspecialchars($item['resposta_usuario']) ?></td>
                            <td><?= htmlspecialchars($item['resposta_correta']) ?></td>
                            <td><?= $item['acertou'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Selecione um jogador para ver o histórico -->
        <form method="POST" class="mt-3">
            <h4>Ver Histórico de Outro Jogador</h4>
            <select name="jogador" class="form-control" onchange="this.form.submit()">
                <option value="">Selecione um jogador</option>
                <?php foreach ($_SESSION['jogadores'] as $jogador => $dados): ?>
                    <option value="<?= $jogador ?>" <?= $jogador === $jogadorSelecionado ? 'selected' : '' ?>><?= $jogador ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Botão para ver todos os históricos -->
        <form method="POST" action="ver_todos_historicos.php" class="mt-3">
            <button type="submit" class="btn btn-primary">Ver Todos os Históricos</button>
        </form>

        <a href="mat.php" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</body>

</html>
