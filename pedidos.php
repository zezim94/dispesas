<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['NIVEL'] !== 'adm') {
    header('Location: login.php');  // Redireciona para a página de login ou página normal
    exit;
}
require 'config.php';

// Consultar os pedidos no banco de dados
$stmt = $pdo->prepare("SELECT * FROM pedidos");
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <!-- Link para o Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link para o DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Link para os ícones do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            margin-top: 30px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-activate {
            background-color: #28a745;
            color: white;
        }

        .btn-activate:hover {
            background-color: #218838;
        }

        .editable-cell {
            padding: 5px;
        }

        /* Cor no cabeçalho da tabela */
        .table th {
            background-color: #007bff;
            color: white;
        }

        /* Destacar linha quando o mouse passar por cima */
        tr:hover {
            background-color: #f1f1f1;
        }

        /* Botão de Voltar */
        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="adm.php" class="btn btn-back mb-3"><i class="bi bi-arrow-left-circle"></i> Voltar</a>
        <h2 class="text-center">Lista de Pedidos</h2>

        <div class="table-responsive">
            <table id="pedidosTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Endereço</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Senha</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr data-id="<?= $pedido['id'] ?>">
                            <td><?= htmlspecialchars($pedido['id']) ?></td>
                            <td><?= htmlspecialchars($pedido['nome']) ?></td>
                            <td><?= htmlspecialchars($pedido['telefone']) ?></td>
                            <td><?= htmlspecialchars($pedido['email']) ?></td>
                            <td><?= htmlspecialchars($pedido['cpf']) ?></td>
                            <td><?= htmlspecialchars($pedido['endereco']) . ', ' . htmlspecialchars($pedido['numero']) . ' ' . htmlspecialchars($pedido['complemento']) . ' - ' . htmlspecialchars($pedido['bairro']) . ', ' . htmlspecialchars($pedido['cidade']) . ' - ' . htmlspecialchars($pedido['estado']) ?></td>
                            <td><?= htmlspecialchars($pedido['status']) ?></td>
                            <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                            <td>
                                <input type="text" class="editable-cell form-control" value="<?= htmlspecialchars($pedido['senha'] ?? '') ?>" id="senha_<?= $pedido['id'] ?>">
                            </td>
                            <td>
                                <button class="btn btn-activate" onclick="ativarUsuario(<?= $pedido['id'] ?>)">
                                    <i class="bi bi-check-circle"></i> Ativar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts do jQuery, DataTables e Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializa o DataTables para a tabela de pedidos
            $('#pedidosTable').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/Portuguese.json'
                }
            });
        });

        function ativarUsuario(pedidoId) {
            var nome = $('#pedidosTable').find('tr[data-id="' + pedidoId + '"] td').eq(1).text();
            var email = $('#pedidosTable').find('tr[data-id="' + pedidoId + '"] td').eq(3).text();
            var senha = $('#senha_' + pedidoId).val();

            $.ajax({
                url: 'ativar_usuario.php',
                method: 'POST',
                data: {
                    nome: nome,
                    email: email,
                    senha: senha,
                    pedido_id: pedidoId
                },
                success: function(response) {
                    alert('Usuário ativado com sucesso!');
                    $('#pedidosTable').find('tr[data-id="' + pedidoId + '"] td').eq(6).text('Ativo');
                },
                error: function(xhr, status, error) {
                    alert('Erro ao ativar usuário.');
                }
            });
        }
    </script>

</body>

</html>