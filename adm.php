<?php
session_start();
require 'config.php';  // Aqui você inclui a configuração do seu banco de dados

if (!isset($_SESSION['user_id']) || $_SESSION['NIVEL'] !== 'adm') {
    header('Location: index.php');
    exit;
}

// Consulta para obter o nome do usuário logado
$query_usuario_logado = "SELECT nome FROM usuarios WHERE id = $1";
$stmt_usuario_logado = $pdo->prepare($query_usuario_logado);
$stmt_usuario_logado->execute([$_SESSION['user_id']]);
$usuario_logado = $stmt_usuario_logado->fetch(PDO::FETCH_ASSOC);

// Consulta todos os usuários
$query_usuarios = "SELECT * FROM usuarios";
$stmt_usuarios = $pdo->prepare($query_usuarios);
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

// Função para verificar usuários logados
function getLoggedUsers()
{
    $loggedUsers = [];
    // Verifica se existem sessões ativas
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'user_id_') === 0) {
            $loggedUsers[] = $value;
        }
    }
    return $loggedUsers;
}

$logados = getLoggedUsers();

// Consulta para obter usuários com status 'online'
$query_logados = "SELECT * FROM usuarios WHERE status = 'online'";
$stmt_logados = $pdo->prepare($query_logados);
$stmt_logados->execute();
$usuarios_logados = $stmt_logados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <!-- Link para o CSS do Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Link para o CSS do DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        /* Aumentando a largura do dropdown */
        .navbar-nav .dropdown-menu {
            width: 250px;
            /* Aumente a largura conforme necessário */
        }

        /* Garantindo que o dropdown se expanda dentro da página */
        .navbar-nav .dropdown-menu {
            left: 0;
            right: 0;
            top: 100%;
            /* Faz com que o dropdown abra para baixo */
        }

        /* Estilos para o nome do usuário */
        .navbar-nav .dropdown-toggle {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        /* Estilo do menu dropdown */
        .dropdown-menu {
            background-color: #343a40;
        }

        .dropdown-item {
            color: #fff;
        }

        .dropdown-item:hover {
            background-color: #007bff;
            color: #fff;
        }

        /* Garantindo que o dropdown não ultrapasse o limite da tela */
        .navbar-nav .dropdown-menu {
            position: absolute;
            max-height: 400px;
            overflow-y: auto;
            /* Caso tenha muitos itens */
        }

        /* Ajustes para responsividade */
        .table-responsive {
            overflow-x: auto;
            /* Permite a rolagem horizontal */
        }

        /* Ajustes para dispositivos móveis */
        @media (max-width: 768px) {
            table {
                font-size: 12px;
                /* Menor fonte em telas pequenas */
            }

            td,
            th {
                padding: 5px;
                /* Menos padding em telas pequenas */
            }

            .navbar-nav .dropdown-menu {
                width: 200px;
                /* Largura menor para o menu em telas pequenas */
            }

            .navbar-brand {
                font-size: 18px;
                /* Tamanho menor para o nome da marca */
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Painel Administrativo</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Controle financeiro</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pedidos.php">Pedidos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pedidos.php">
                        <i class="fas fa-bell"></i>
                        <span id="notificacao-pedidos" class="badge bg-danger" style="display: none; margin-left: 5px;"></span>
                    </a>
                </li>

                <!-- Menu Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo htmlspecialchars($usuario_logado['nome']); ?> <!-- Exibindo o nome do usuário logado -->
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="logout.php">Sair</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h3 class="mt-5">Usuários Logados</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Imagem de Perfil</th>
                        <th>Nível</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($usuarios_logados)) {
                        echo "<tr><td colspan='5'>Nenhum usuário logado no momento</td></tr>";
                    } else {
                        foreach ($usuarios_logados as $logado) {
                            echo "<tr>
                                <td>" . htmlspecialchars($logado['nome']) . "</td>
                                <td>" . htmlspecialchars($logado['email']) . "</td>
                                <td>" . htmlspecialchars($logado['status']) . "</td>
                                <td><img src='" . htmlspecialchars($logado['img_perfil']) . "' alt='Imagem de Perfil' style='width: 50px; height: 50px;'></td>
                                <td>" . htmlspecialchars($logado['NIVEL']) . "</td>
                              </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <h2>Usuários Registrados</h2>
        <div class="table-responsive">
            <table id="usuarios" class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Senha</th>
                        <th>Criado em</th>
                        <th>Imagem de Perfil</th>
                        <th>Nível</th>
                        <th>Ações</th> <!-- Coluna para os botões -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nome']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars($usuario['senha']) ?></td>
                            <td><?= htmlspecialchars($usuario['criado_em']) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($usuario['img_perfil'] ?? 'uploads/default.jpg') ?>" alt="Imagem de Perfil" style="width: 50px; height: 50px;">
                            </td>

                            <td><?= htmlspecialchars($usuario['NIVEL']) ?></td>
                            <td>
                                <!-- Botão Editar -->
                                <a href="editar_users.php?id=<?= $usuario['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>

                                <!-- Botão Excluir -->
                                <a href="excluir_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Você tem certeza que deseja excluir este usuário?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Scripts do DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Usando a versão completa do jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Script para o DataTables -->
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#usuarios').DataTable({
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

    <script>
        $(document).ready(function() {
            function verificarPedidos() {
                $.ajax({
                    url: 'buscar_pedidos.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let notificacaoBadge = $("#notificacao-pedidos");

                        if (data.length > 0) {
                            notificacaoBadge.text(data.length).show();
                        } else {
                            notificacaoBadge.hide();
                        }
                    }
                });
            }

            // Redireciona apenas se clicar na notificação (badge vermelho)
            $("#notificacao-pedidos").on("click", function(event) {
                event.stopPropagation(); // Impede que o clique se propague para o ícone
                window.location.href = "pedidos.php";
            });

            // Impede o clique no ícone do sino (não deve redirecionar)
            $(".fa-bell").on("click", function(event) {
                event.preventDefault(); // Evita que o clique no sino faça algo
            });

            // Atualiza os pedidos a cada 10 segundos
            setInterval(verificarPedidos, 10000);
            verificarPedidos(); // Chamada inicial
        });
    </script>

</body>

</html>
