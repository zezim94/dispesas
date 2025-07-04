<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Função para formatar a data para o formato 'Y-m'
function formatDate($month, $year) {
    return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
}

// Data atual
$currentMonth = date('m');
$currentYear = date('Y');
$currentMonthStart = formatDate($currentMonth, $currentYear) . '-01';
$currentMonthEnd = formatDate($currentMonth, $currentYear) . '-31';

// Buscar transações do mês atual
$query = "SELECT * FROM transacoes WHERE user_id = :user_id AND data BETWEEN :start_date AND :end_date ORDER BY data DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id, 'start_date' => $currentMonthStart, 'end_date' => $currentMonthEnd]);
$transacoesAtual = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se foi enviado um mês selecionado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mes'])) {
    $mesSelecionado = $_POST['mes'];
    $anoSelecionado = $_POST['ano'];
    $selectedMonthStart = formatDate($mesSelecionado, $anoSelecionado) . '-01';
    $selectedMonthEnd = formatDate($mesSelecionado, $anoSelecionado) . '-31';

    // Buscar transações do mês selecionado
    $stmt->execute(['user_id' => $user_id, 'start_date' => $selectedMonthStart, 'end_date' => $selectedMonthEnd]);
    $transacoesSelecionado = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Caso não tenha sido selecionado, utilizar o mês atual como padrão
    $transacoesSelecionado = $transacoesAtual;
    $mesSelecionado = $currentMonth;
    $anoSelecionado = $currentYear;
}

// Função para calcular total de receitas e despesas
function getTotal($transacoes, $tipo) {
    $total = 0;
    foreach ($transacoes as $transacao) {
        if ($transacao['tipo'] === $tipo) {
            $total += $transacao['valor'];
        }
    }
    return $total;
}

$totalAtualReceita = getTotal($transacoesAtual, 'receita');
$totalAtualDespesa = getTotal($transacoesAtual, 'despesa');
$totalSelecionadoReceita = getTotal($transacoesSelecionado, 'receita');
$totalSelecionadoDespesa = getTotal($transacoesSelecionado, 'despesa');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparação de Mês</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#receitas').DataTable({
                "responsive": true,
                "language": {
                    "sEmptyTable": "Nenhum dado disponível na tabela",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                    "sLengthMenu": "Mostrar _MENU_ registros por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sSearch": "Buscar:",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sLast": "Último",
                        "sNext": "Próximo",
                        "sPrevious": "Anterior"
                    }
                }
            });
            $('#transacoes').DataTable({
                "responsive": true,
                "language": {
                    "sEmptyTable": "Nenhum dado disponível na tabela",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                    "sLengthMenu": "Mostrar _MENU_ registros por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sSearch": "Buscar:",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sLast": "Último",
                        "sNext": "Próximo",
                        "sPrevious": "Anterior"
                    }
                }
            });
        });
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center">Comparação entre Meses</h2>
            <div class="mb-4">
                <form method="POST">
                    <label for="mes">Selecione o Mês:</label>
                    <select name="mes" id="mes" class="form-control d-inline-block" style="width: auto;" required>
                        <option value="01" <?= $mesSelecionado == '01' ? 'selected' : '' ?>>Janeiro</option>
                        <option value="02" <?= $mesSelecionado == '02' ? 'selected' : '' ?>>Fevereiro</option>
                        <option value="03" <?= $mesSelecionado == '03' ? 'selected' : '' ?>>Março</option>
                        <option value="04" <?= $mesSelecionado == '04' ? 'selected' : '' ?>>Abril</option>
                        <option value="05" <?= $mesSelecionado == '05' ? 'selected' : '' ?>>Maio</option>
                        <option value="06" <?= $mesSelecionado == '06' ? 'selected' : '' ?>>Junho</option>
                        <option value="07" <?= $mesSelecionado == '07' ? 'selected' : '' ?>>Julho</option>
                        <option value="08" <?= $mesSelecionado == '08' ? 'selected' : '' ?>>Agosto</option>
                        <option value="09" <?= $mesSelecionado == '09' ? 'selected' : '' ?>>Setembro</option>
                        <option value="10" <?= $mesSelecionado == '10' ? 'selected' : '' ?>>Outubro</option>
                        <option value="11" <?= $mesSelecionado == '11' ? 'selected' : '' ?>>Novembro</option>
                        <option value="12" <?= $mesSelecionado == '12' ? 'selected' : '' ?>>Dezembro</option>
                    </select>
                    <input type="number" name="ano" value="<?= $anoSelecionado ?>" class="form-control d-inline-block" style="width: auto;" required>
                    <button type="submit" class="btn btn-primary mt-3">Comparar</button>
                </form>
            </div>

            <div class="mb-4">
                <h4>Mês Atual (<?= date('m/Y') ?>):</h4>
                <p>Receitas: R$ <?= number_format($totalAtualReceita, 2, ',', '.') ?></p>
                <p>Despesas: R$ <?= number_format($totalAtualDespesa, 2, ',', '.') ?></p>

                <h4>Mês Selecionado (<?= $mesSelecionado . '/' . $anoSelecionado ?>):</h4>
                <p>Receitas: R$ <?= number_format($totalSelecionadoReceita, 2, ',', '.') ?></p>
                <p>Despesas: R$ <?= number_format($totalSelecionadoDespesa, 2, ',', '.') ?></p>
            </div>

            <div class="table-responsive">
                <h4>Transações do Mês Atual:</h4>
                <table id="transacoes" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transacoesAtual as $transacao): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transacao['data'])) ?></td>
                                <td><?= htmlspecialchars($transacao['categoria_id']) ?></td>
                                <td><?= htmlspecialchars($transacao['descricao']) ?></td>
                                <td>R$ <?= number_format($transacao['valor'], 2, ',', '.') ?></td>
                                <td><?= ucfirst($transacao['tipo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4>Transações do Mês Selecionado:</h4>
                <table id="receitas" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transacoesSelecionado as $transacao): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transacao['data'])) ?></td>
                                <td><?= htmlspecialchars($transacao['categoria_id']) ?></td>
                                <td><?= htmlspecialchars($transacao['descricao']) ?></td>
                                <td>R$ <?= number_format($transacao['valor'], 2, ',', '.') ?></td>
                                <td><?= ucfirst($transacao['tipo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center">
                <a href="index.php" class="btn btn-secondary">Voltar</a>
            </div>

        </div>
    </div>
</body>
</html>
