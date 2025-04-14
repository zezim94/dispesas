<?php
session_start();

// Inicializa o tabuleiro e os nomes dos jogadores
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['player1'] = 'Jogador 1';
    $_SESSION['player2'] = 'Jogador 2';
    $_SESSION['wins'] = ['Jogador 1' => 0, 'Jogador 2' => 0];
    $_SESSION['current_player'] = 'X';
    $_SESSION['mode'] = 'machine'; // Modo padrão: jogar contra a máquina
}

// Processa o clique no tabuleiro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['index'])) {
        $index = $_POST['index'];
        if ($_SESSION['board'][$index] === '') {
            $_SESSION['board'][$index] = $_SESSION['current_player'];
            if (check_winner($_SESSION['board'], $_SESSION['current_player'])) {
                $winner = $_SESSION['current_player'] === 'X' ? $_SESSION['player1'] : $_SESSION['player2'];
                $_SESSION['wins'][$winner]++;
                reset_game();
            } elseif (!in_array('', $_SESSION['board'])) {
                reset_game();
            } else {
                $_SESSION['current_player'] = $_SESSION['current_player'] === 'X' ? 'O' : 'X';
                if ($_SESSION['mode'] === 'machine' && $_SESSION['current_player'] === 'O') {
                    machine_move();
                    if (check_winner($_SESSION['board'], 'O')) {
                        $_SESSION['wins'][$_SESSION['player2']]++;
                        reset_game();
                    } else {
                        $_SESSION['current_player'] = 'X';
                    }
                }
            }
        }
    }

    // Processa a mudança de modo
    if (isset($_POST['mode'])) {
        $_SESSION['mode'] = $_POST['mode'];
        reset_game();
    }

    // Processa a inserção dos nomes
    if (isset($_POST['player1']) && isset($_POST['player2'])) {
        $_SESSION['player1'] = htmlspecialchars($_POST['player1']);
        $_SESSION['player2'] = htmlspecialchars($_POST['player2']);
    }

    // Processa a limpeza do placar
    if (isset($_POST['reset'])) {
        $_SESSION['wins'] = ['Jogador 1' => 0, 'Jogador 2' => 0]; // Zera o placar
    }

    // Processa o botão de voltar
    if (isset($_POST['back'])) {
        header('Location: index.php'); // Aqui você pode redirecionar para uma página específica
        exit;
    }
}

// Função para verificar o vencedor
function check_winner($board, $player)
{
    $winning_combinations = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8], // Linhas
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8], // Colunas
        [0, 4, 8],
        [2, 4, 6]             // Diagonais
    ];
    foreach ($winning_combinations as $combination) {
        if (
            $board[$combination[0]] === $player &&
            $board[$combination[1]] === $player &&
            $board[$combination[2]] === $player
        ) {
            return true;
        }
    }
    return false;
}

// Função para resetar o jogo
function reset_game()
{
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['current_player'] = 'X';
}

// Função para a jogada da máquina
function machine_move()
{
    $empty_cells = array_keys($_SESSION['board'], '');
    if (!empty($empty_cells)) {
        $random_index = $empty_cells[array_rand($empty_cells)];
        $_SESSION['board'][$random_index] = 'O';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo da Velha</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .cell {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100px;
            font-size: 36px;
            border: 2px solid #ccc;
            cursor: pointer;
        }

        .cell:hover {
            background-color: #f1f1f1;
        }

        .wins-table {
            margin-top: 30px;
        }

        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .buttons button {
            padding: 10px 20px;
            font-size: 16px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container text-center py-5">
        <h1 class="display-4 mb-4">Jogo da Velha</h1>

        <!-- Modo de Jogo -->
        <form method="post" class="form-container">
            <label class="form-check-label">
                <input type="radio" class="form-check-input" name="mode" value="machine" <?= $_SESSION['mode'] === 'machine' ? 'checked' : '' ?> onchange="this.form.submit()"> Jogar contra a Máquina
            </label>
            <label class="form-check-label">
                <input type="radio" class="form-check-input" name="mode" value="person" <?= $_SESSION['mode'] === 'person' ? 'checked' : '' ?> onchange="this.form.submit()"> Jogar contra uma Pessoa
            </label>
        </form>

        <!-- Nomes dos Jogadores -->
        <?php if ($_SESSION['mode'] === 'person'): ?>
            <form method="post" class="form-container">
                <input type="text" class="form-control" name="player1" value="<?= $_SESSION['player1'] ?>" placeholder="Jogador 1 (X)" required>
                <input type="text" class="form-control" name="player2" value="<?= $_SESSION['player2'] ?>" placeholder="Jogador 2 (O)" required>
                <button type="submit" class="btn btn-primary mt-3">Atualizar Nomes</button>
            </form>
        <?php endif; ?>

        <!-- Tabuleiro -->
        <div class="board">
            <?php for ($i = 0; $i < 9; $i++): ?>
                <form method="post" class="cell">
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <button type="submit" class="w-100 h-100 border-0"><?= $_SESSION['board'][$i] ?></button>
                </form>
            <?php endfor; ?>
        </div>

        <!-- Placar -->
        <div class="wins-table">
            <h2 class="h4">Vitórias</h2>
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?= $_SESSION['player1'] ?></th>
                    <th><?= $_SESSION['player2'] ?></th>
                </tr>
                <tr>
                    <td><?= $_SESSION['wins'][$_SESSION['player1']] ?></td>
                    <td><?= $_SESSION['wins'][$_SESSION['player2']] ?></td>
                </tr>
            </table>
        </div>

        <!-- Botões -->
        <div class="buttons">
            <form method="post">
                <button type="submit" name="reset" class="btn btn-danger">Limpar Placar</button>
            </form>
            <form method="post">
                <button type="submit" name="back" class="btn btn-success">Voltar</button>
            </form>
        </div>
    </div>
    <style>
        /* Estilo para o body, garantindo que o conteúdo seja centralizado na tela */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
        }

        .game-container {
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            /* Define uma largura fixa para o contêiner */
        }

        /* Tabuleiro do jogo */
        .board {
            display: grid;
            grid-template-columns: repeat(3, 80px);
            /* Diminuir a largura para 80px */
            gap: 8px;
            margin: 20px 0;
            justify-content: center;
            /* Centraliza a grade horizontalmente */
            align-items: center;
            /* Centraliza a grade verticalmente */
        }

        .cell {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 0;
            padding-top: 100%;
            /* Garante que o campo seja quadrado */
            position: relative;
            font-size: 24px;
            /* Reduz o tamanho da fonte */
            border: 2px solid #ccc;
            cursor: pointer;
        }

        .cell:hover {
            background-color: #f1f1f1;
        }

        .cell button {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: none;
            border: none;
            font-size: 24px;
            /* Reduz o tamanho do texto */
        }

        /* Tabela de vitórias */
        .wins-table {
            margin-top: 20px;
            font-size: 14px;
            /* Reduz o tamanho da fonte para ficarem proporcionais */
        }

        .wins-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .wins-table table,
        .wins-table th,
        .wins-table td {
            border: 1px solid #ccc;
        }

        .wins-table th,
        .wins-table td {
            padding: 8px;
            text-align: center;
        }

        /* Botões */
        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .buttons button {
            padding: 8px 16px;
            /* Ajusta o tamanho do botão */
            font-size: 14px;
            /* Reduz o tamanho da fonte */
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: blue;
            transition: background-color 0.3s;
        }

        .buttons button:hover {
            background-color: #ddd;
        }

        /* Formulários para seleção de jogadores e modo */
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            /* Reduz o tamanho da fonte */
        }

        .form-container input[type="text"] {
            font-size: 14px;
            /* Reduz o tamanho da fonte para os campos de texto */
            padding: 5px;
            width: 150px;
        }

        /* Formulários para seleção de modo de jogo */
        .form-container label {
            font-size: 14px;
        }
    </style>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>