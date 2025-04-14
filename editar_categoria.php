<?php
session_start();
require 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obtém o ID do usuário da sessão
$user_id = $_SESSION['user_id'];

// Consulta para obter as categorias associadas ao usuário logado
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE user_id = :user_id ORDER BY nome");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Armazena as categorias obtidas
$categorias = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabela-categorias').DataTable({
                "responsive": true,
                "language": {
                    "sEmptyTable": "Nenhuma categoria cadastrada",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                    "sLengthMenu": "Mostrar _MENU_ registros por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sSearch": "Buscar:",
                    "sZeroRecords": "Nenhuma categoria encontrada",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sLast": "Último",
                        "sNext": "Próximo",
                        "sPrevious": "Anterior"
                    }
                }
            });
        });

        function salvarCategoria(id) {
            var nome = $('#nome_' + id).text();
            $.post('salvar_categoria.php', {
                id: id,
                nome: nome
            }, function(response) {
                alert(response);
            });
        }
    </script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .btn i {
            margin-right: 5px;
        }

        .editable {
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card shadow p-4">
            <h2 class="text-center text-primary">Gerenciar Categorias</h2>

            <div class="d-flex justify-content-between mb-3">
                <a href="adicionar_categoria.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nova Categoria
                </a>
            </div>

            <div class="table-responsive">
                <table id="tabela-categorias" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= $categoria['id'] ?></td>
                                <td contenteditable="true" class="editable" id="nome_<?= $categoria['id'] ?>">
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </td>
                                <td>
                                    <button onclick="salvarCategoria(<?= $categoria['id'] ?>)" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save"></i> Salvar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>