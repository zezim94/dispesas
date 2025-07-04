<?php
session_start();
require 'config.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Consultar as receitas
$query_receitas = "SELECT * FROM transacoes WHERE user_id = ? AND tipo = 'receita' ORDER BY data DESC";
$stmt_receitas = $pdo->prepare($query_receitas);
$stmt_receitas->execute([$user_id]);
$receitas = $stmt_receitas->fetchAll(PDO::FETCH_ASSOC);

// Consultar as despesas
$query_despesas = "SELECT * FROM transacoes WHERE user_id = ? AND tipo = 'despesa' ORDER BY data DESC";
$stmt_despesas = $pdo->prepare($query_despesas);
$stmt_despesas->execute([$user_id]);
$despesas = $stmt_despesas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receitas e Despesas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabela-receitas').DataTable({
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

            $('#tabela-despesas').DataTable({
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
            <h2 class="text-center">Receitas e Despesas</h2>

            <!-- Tabela de Receitas -->
            <h4 class="mt-4">Receitas</h4>
            <div class="table-responsive">
                <table id="tabela-receitas" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receitas as $receita): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($receita['data'])) ?></td>
                                <td><?= htmlspecialchars($receita['categoria_id']) ?></td>
                                <td><?= htmlspecialchars($receita['descricao']) ?></td>
                                <td>R$ <?= number_format($receita['valor'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabela de Despesas -->
            <h4 class="mt-4">Despesas</h4>
            <div class="table-responsive">
                <table id="tabela-despesas" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($despesas as $despesa): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($despesa['data'])) ?></td>
                                <td><?= htmlspecialchars($despesa['categoria_id']) ?></td>
                                <td><?= htmlspecialchars($despesa['descricao']) ?></td>
                                <td>R$ <?= number_format($despesa['valor'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botão Voltar -->
            <div class="d-flex justify-content-center mt-4">
                <a href="index.php" class="btn btn-secondary btn-lg">Voltar</a>
            </div>

        </div>
    </div>
</body>
</html>
