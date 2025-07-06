<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtendo nome, imagem do usuário e nível (admin ou não)
$query_usuario = "SELECT * FROM usuarios WHERE id = :user_id";
$stmt_usuario = $pdo->prepare($query_usuario);
$stmt_usuario->execute(['user_id' => $user_id]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

$nome_usuario = $usuario['nome'] ?? 'Usuário';
$img_perfil = $usuario['img_perfil'] ?? null;
$nivel_usuario = $usuario['nivel'] ?? 'user';

// Atualizando o status do usuário para "online" ao fazer login
$query_login = "UPDATE usuarios SET status = 'online' WHERE id = :user_id";
$stmt_login = $pdo->prepare($query_login);
$stmt_login->execute(['user_id' => $user_id]);

// Variáveis para filtrar
$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim = $_POST['data_fim'] ?? '';

// Construindo a cláusula WHERE dependendo dos filtros de data
$query = "SELECT t.*, c.nome AS categoria_nome 
          FROM transacoes t 
          LEFT JOIN categorias c ON t.categoria_id = c.id
          WHERE t.user_id = :user_id";

$params = ['user_id' => $user_id];

if ($data_inicio) {
    $query .= " AND t.data >= :data_inicio";
    $params['data_inicio'] = $data_inicio;
}
if ($data_fim) {
    $query .= " AND t.data <= :data_fim";
    $params['data_fim'] = $data_fim;
}

$query .= " ORDER BY t.data DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query para calcular o saldo
$query_saldo = "SELECT SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) AS saldo 
                FROM transacoes 
                WHERE user_id = :user_id";
$stmt_saldo = $pdo->prepare($query_saldo);
$stmt_saldo->execute(['user_id' => $user_id]);
$saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Contas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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

            // Função para atualizar a data e hora em tempo real
            function atualizarHora() {
                var now = new Date();
                var dia = now.getDate().toString().padStart(2, '0');
                var mes = (now.getMonth() + 1).toString().padStart(2, '0');
                var ano = now.getFullYear();
                var hora = now.getHours().toString().padStart(2, '0');
                var minuto = now.getMinutes().toString().padStart(2, '0');
                var segundo = now.getSeconds().toString().padStart(2, '0');
                var dataHora = dia + '/' + mes + '/' + ano + ' ' + hora + ':' + minuto + ':' + segundo;
                document.getElementById("dataHoraAtual").innerHTML = dataHora;
            }

            setInterval(atualizarHora, 1000); // Atualiza a cada 1 segundo
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

        .table-responsive {
            margin-top: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }

        /* Ajuste no cabeçalho */
        .navbar-nav {
            display: flex;
            align-items: center;
        }

        .navbar-nav .nav-item {
            margin-left: 20px;
        }

        .foto-perfil {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
        }

        .foto-perfil-large {
            width: 80px;
            /* Ajuste do tamanho da foto */
            height: 80px;
            border-radius: 50%;
        }

        .topo-conteudo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .table-responsive {
            margin-top: 20px;
            width: 100%;
        }

        #dataHoraAtual {
            font-size: 14px;
            color: #007bff;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .navbar-nav {
                flex-direction: column;
                align-items: center;
            }

            .foto-perfil {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>

</head>

<body>

    <!-- MENU RESPONSIVO -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Minhas Finanças</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="receitas_despesas.php">Receitas & Despesas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comparacao.php">Comparação</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorio.php">Relatório</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Jogos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="amigo.php">Amigo secreto</a></li>
                            <li><a class="dropdown-item" href="jogo.php">Pedra, Papel, Tesoura</a></li>
                            <li><a class="dropdown-item" href="memoria.php">Jogo da velha</a></li>
                            <li><a class="dropdown-item" href="mat.php">Matemática</a></li>
                        </ul>
                    </li>

                    <!-- MENU DROPDOWN PARA USUÁRIO -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($nome_usuario) ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="editar_perfil.php">Editar Perfil</a></li>
                            <?php if ($nivel_usuario === 'adm'): ?>
                                <li><a class="dropdown-item" href="adm.php">Admin</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </li>

                    <!-- Foto de perfil -->
                    <li class="nav-item">
                        <img src="<?= !empty($img_perfil) ? htmlspecialchars($img_perfil) : '1.jpg' ?>" class="foto-perfil-large" alt="Foto de perfil">
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container mt-5">
        <div class="topo-conteudo d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column align-items-start">
                <h2>Gerenciamento de Contas Pessoais</h2>
                <h4 class="text-success">Saldo Atual: R$ <?= number_format($saldo, 2, ',', '.') ?></h4>
                <p class="text-muted">Bem-vindo(a), <?= htmlspecialchars($nome_usuario) ?>!</p>
            </div>
            <!-- Foto de Perfil -->
            <div>
                <img src="<?= !empty($img_perfil) ? htmlspecialchars($img_perfil) : '1.jpg' ?>" class="foto-perfil-large" alt="Foto de perfil">
            </div>
        </div>

        <p id="dataHoraAtual"></p> <!-- Exibindo a data e hora -->

        <div class="card shadow p-4">
            <!-- Formulário de Filtro -->
            <form method="POST" class="mb-3">
                <div class="row">
                    <div class="col-md-5">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?= $data_fim ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between mb-3">
                <a href="adicionar.php" class="btn btn-primary">Adicionar Transação</a>
            </div>

            <div class="table-responsive">
                <table id="tabela-transacoes" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>CATEGORIA</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transacoes as $transacao): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transacao['data'])) ?></td>
                                <td><?= htmlspecialchars($transacao['categoria_nome'] ?? 'Sem Categoria') ?></td>
                                <td><?= htmlspecialchars($transacao['descricao']) ?></td>
                                <td>R$ <?= number_format($transacao['valor'], 2, ',', '.') ?></td>
                                <td><?= ucfirst($transacao['tipo']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $transacao['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?= $transacao['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta transação?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Script para Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
