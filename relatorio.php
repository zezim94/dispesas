<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Variáveis para o filtro de mês e ano
$ano_selecionado = $_POST['ano'] ?? date('Y');
$mes_selecionado = $_POST['mes'] ?? date('m');

// Construindo o intervalo de datas baseado no mês e ano selecionados
$data_inicio = $ano_selecionado . '-' . str_pad($mes_selecionado, 2, '0', STR_PAD_LEFT) . '-01';
$data_fim = $ano_selecionado . '-' . str_pad($mes_selecionado, 2, '0', STR_PAD_LEFT) . '-31';

// Consultando as transações de receitas e despesas para o mês e ano selecionados
$query = "
    SELECT t.*, c.nome AS categoria_nome
FROM transacoes t
LEFT JOIN categorias c ON t.categoria_id = c.id
WHERE t.user_id = ?
AND t.data BETWEEN ? AND ?
ORDER BY t.data DESC

";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $data_inicio, $data_fim]);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculando o total de receitas e despesas
$total_receitas = 0;
$total_despesas = 0;
$categorias_despesa = [];
$categorias_receita = [];

foreach ($transacoes as $transacao) {
    $nome_categoria = $transacao['categoria_nome'] ?? 'Sem categoria';

    if ($transacao['tipo'] === 'receita') {
        $total_receitas += $transacao['valor'];
        if (!isset($categorias_receita[$nome_categoria])) {
            $categorias_receita[$nome_categoria] = 0;
        }
        $categorias_receita[$nome_categoria] += $transacao['valor'];
        $tipos['receita'] += $transacao['valor'];
    } elseif ($transacao['tipo'] === 'despesa') {
        $total_despesas += $transacao['valor'];
        if (!isset($categorias_despesa[$nome_categoria])) {
            $categorias_despesa[$nome_categoria] = 0;
        }
        $categorias_despesa[$nome_categoria] += $transacao['valor'];
        $tipos['despesa'] += $transacao['valor'];
    }
}


// Preparando os dados para os gráficos
$labels_tipos = array_keys($tipos);
$dados_tipos = array_values($tipos);

$labels_despesas = array_keys($categorias_despesa);
$dados_despesas = array_values($categorias_despesa);

$labels_receitas = array_keys($categorias_receita);
$dados_receitas = array_values($categorias_receita);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Receitas e Despesas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabela-transacoes').DataTable({
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

            // Gráfico de Pizza (Tipos de transação)
            var ctxTipos = document.getElementById('graficoTipos').getContext('2d');
            var graficoTipos = new Chart(ctxTipos, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($labels_tipos) ?>,
                    datasets: [{
                        label: 'Tipos de Transações',
                        data: <?= json_encode($dados_tipos) ?>,
                        backgroundColor: [
                            '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#f9c74f',
                            '#9966ff', '#ff9f40', '#00a8cc', '#ff6f61', '#8dd3c7',
                            '#d4a5a5', '#c2c2f0', '#fdc086', '#a6cee3', '#b2df8a',
                            '#fb9a99', '#fdb462', '#80b1d3', '#bebada', '#bc80bd',
                            '#fccde5', '#d9d9d9', '#b3de69', '#ccebc5', '#ffed6f',
                            '#9b59b6', '#34495e', '#e67e22', '#1abc9c', '#2ecc71',
                            '#f1c40f', '#e74c3c', '#3498db', '#7f8c8d', '#95a5a6',
                            '#16a085', '#f39c12', '#c0392b', '#2980b9', '#bdc3c7',
                            '#ec7063', '#af7ac5', '#7dcea0', '#f8c471', '#73c6b6',
                            '#f1948a', '#aab7b8', '#5dade2', '#76d7c4', '#d7bde2'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // Gráfico de Pizza (Categorias de Despesas)
            var ctxDespesas = document.getElementById('graficoDespesas').getContext('2d');
            var graficoDespesas = new Chart(ctxDespesas, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($labels_despesas) ?>,
                    datasets: [{
                        label: 'Categorias de Despesas',
                        data: <?= json_encode($dados_despesas) ?>,
                        backgroundColor: [
                            '#ffd1dc', '#a0ced9', '#f6c6ea', '#c5dedd', '#ffdfba',
                            '#e2f0cb', '#c7ceea', '#f5d5cb', '#f9f9f9', '#b5ead7',
                            '#ffb3c1', '#ffcccb', '#ffe0ac', '#fdd9a0', '#f6f1d1',
                            '#fcf8e8', '#d5f0dc', '#b2ebf2', '#eec9f2', '#f5d5ff',
                            '#f6e1dc', '#ece2d0', '#d5c9c0', '#f7f48b', '#f0c987',
                            '#f79d65', '#f4845f', '#e5d3f2', '#fff1f9', '#ffdede',
                            '#c1b7a1', '#e8e8e4', '#fcd5ce', '#fae1dd', '#f8edeb',
                            '#b5d6b2', '#d0efb1', '#a9cbb7', '#c9e4ca', '#f0efeb',
                            '#ffe5ec', '#e3f2fd', '#d1cfe2', '#e7f2f8', '#fde2e2',
                            '#dee2ff', '#bde0fe', '#d8e2dc', '#e2ece9', '#bdb2ff'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // Gráfico de Pizza (Categorias de Receitas)
            var ctxReceitas = document.getElementById('graficoReceitas').getContext('2d');
            var graficoReceitas = new Chart(ctxReceitas, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($labels_receitas) ?>,
                    datasets: [{
                        label: 'Categorias de Receitas',
                        data: <?= json_encode($dados_receitas) ?>,
                        backgroundColor: [
                            '#f94144', '#f3722c', '#f8961e', '#f9844a', '#f9c74f',
                            '#90be6d', '#43aa8b', '#577590', '#277da1', '#4d908e',
                            '#f4a261', '#e76f51', '#8ecae6', '#219ebc', '#023047',
                            '#ff6b6b', '#6a4c93', '#3a0ca3', '#7209b7', '#b5179e',
                            '#ffb703', '#fb8500', '#6b9080', '#a4c3b2', '#cb997e',
                            '#e9c46a', '#e76f51', '#2a9d8f', '#264653', '#b5838d',
                            '#ffcdb2', '#ffb4a2', '#e5989b', '#b5ead7', '#d0f4de',
                            '#ffcad4', '#cdb4db', '#a2d2ff', '#caffbf', '#bdfcc9',
                            '#ffe066', '#ff9f1c', '#ff6f61', '#f95738', '#ee6c4d',
                            '#70c1b3', '#247ba0', '#50514f', '#b0bec5', '#607d8b'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .card {
            border-radius: 15px;
        }

        h2,
        h4 {
            color: #007bff;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center">Relatório de Receitas e Despesas</h2>

            <!-- Formulário de Filtro -->
            <form method="POST" class="mb-3">
                <div class="row">
                    <div class="col-md-5">
                        <label for="ano" class="form-label">Ano</label>
                        <input type="number" id="ano" name="ano" class="form-control" value="<?= $ano_selecionado ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="mes" class="form-label">Mês</label>
                        <select name="mes" id="mes" class="form-control" required>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $mes_selecionado == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                    <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Gerar Relatório</button>
                    </div>
                </div>
            </form>

            <h4>Relatório do Mês <?= $mes_selecionado ?>/<?= $ano_selecionado ?>:</h4>
            <p><strong>Total de Receitas:</strong> R$ <?= number_format($total_receitas, 2, ',', '.') ?></p>
            <p><strong>Total de Despesas:</strong> R$ <?= number_format($total_despesas, 2, ',', '.') ?></p>
            <p><strong>Saldo do Mês:</strong> R$ <?= number_format($total_receitas - $total_despesas, 2, ',', '.') ?></p>

            <div class="table-responsive">
                <table id="tabela-transacoes" class="table table-striped table-hover">
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
                        <?php foreach ($transacoes as $transacao): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transacao['data'])) ?></td>
                                <td><?= htmlspecialchars($transacao['categoria_nome'] ?? 'Sem categoria') ?></td>
                                <td><?= htmlspecialchars($transacao['descricao']) ?></td>
                                <td>R$ <?= number_format($transacao['valor'], 2, ',', '.') ?></td>
                                <td><?= ucfirst($transacao['tipo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Gráficos de Pizza -->
            <div class="mt-5">
                <h4>Distribuição por Tipo de Transação</h4>
                <canvas id="graficoTipos"></canvas>
            </div>
            <div class="mt-5">
                <h4>Distribuição por Categorias de Despesas</h4>
                <canvas id="graficoDespesas"></canvas>
            </div>
            <div class="mt-5">
                <h4>Distribuição por Categorias de Receitas</h4>
                <canvas id="graficoReceitas"></canvas>
            </div>

            <!-- Botão Voltar -->
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</body>

</html>
