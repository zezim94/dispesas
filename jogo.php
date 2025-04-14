<?php
session_start();

// Verifica se há histórico de jogadas
if (!isset($_SESSION['vitorias'])) {
    $_SESSION['vitorias'] = 0;
    $_SESSION['empates'] = 0;
    $_SESSION['derrotas'] = 0;
}

// Função para gerar a jogada do computador
function jogadaComputador() {
    $opcoes = ['pedra', 'papel', 'tesoura'];
    return $opcoes[array_rand($opcoes)];
}

// Variáveis para armazenar o resultado
$usuario = '';
$computador = '';
$mensagem = '';
$nomeUsuario = '';
$erroNome = '';

// Inicializa a lista de nomes de jogadores se ainda não existir
if (!isset($_SESSION['nomes_jogadores'])) {
    $_SESSION['nomes_jogadores'] = [];
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nome'])) {
        $nomeDigitado = trim($_POST['nome']);

        // Verifica se o nome já está em uso
        if (in_array(strtolower($nomeDigitado), array_map('strtolower', $_SESSION['nomes_jogadores']))) {
            $erroNome = "Este nome já está em uso. Escolha outro.";
        } else {
            $_SESSION['nome'] = $nomeDigitado;
            $_SESSION['nomes_jogadores'][] = $nomeDigitado; // Adiciona à lista de nomes
        }
    }

    // Obtém a jogada do usuário
    if (isset($_POST['jogada']) && isset($_SESSION['nome'])) {
        $usuario = $_POST['jogada'];
        $nomeUsuario = $_SESSION['nome'];

        // O computador escolhe aleatoriamente
        $computador = jogadaComputador();

        // Compara as jogadas para determinar o vencedor
        if ($usuario === $computador) {
            $mensagem = 'Empate! Ambos escolheram ' . ucfirst($usuario) . '.';
            $_SESSION['empates']++;
        } elseif (
            ($usuario === 'pedra' && $computador === 'tesoura') ||
            ($usuario === 'papel' && $computador === 'pedra') ||
            ($usuario === 'tesoura' && $computador === 'papel')
        ) {
            $mensagem = 'Você ganhou! ' . ucfirst($usuario) . ' vence ' . ucfirst($computador) . '.';
            $_SESSION['vitorias']++;
        } else {
            $mensagem = 'Você perdeu! ' . ucfirst($computador) . ' vence ' . ucfirst($usuario) . '.';
            $_SESSION['derrotas']++;
        }

        // Armazenar jogada no histórico com data e hora
        $historico = [
            'nome' => $nomeUsuario,
            'jogada_usuario' => $usuario,
            'jogada_computador' => $computador,
            'resultado' => $mensagem,
            'data_hora' => date('d/m/Y H:i:s')
        ];

        $_SESSION['historico'][] = $historico;

        // Verifica se o usuário atingiu 10 vitórias e redireciona para o histórico
        if ($_SESSION['vitorias'] >= 10) {
            $_SESSION['vitorias'] = 0;
            $_SESSION['empates'] = 0;
            $_SESSION['derrotas'] = 0;

            // Remove o nome do usuário da sessão após 10 vitórias
            unset($_SESSION['nome']);

            header("Location: historico.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedra, Papel e Tesoura</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .jogo-container {
            text-align: center;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
        }
        .btn {
            font-size: 20px;
            padding: 10px 20px;
            margin: 10px;
        }
        .resultado {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .img-opcao {
            width: 100px;
            height: 100px;
            margin: 10px;
            cursor: pointer;
        }
        .stat {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="jogo-container">
    <?php if (!isset($_SESSION['nome'])): ?>
        <h1>Pedra, Papel e Tesoura</h1>
        <form method="POST">
            <div class="form-group">
                <label for="nome">Digite seu nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
                <?php if (!empty($erroNome)): ?>
                    <p class="text-danger"><?= htmlspecialchars($erroNome) ?></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Jogo</button>
        </form>
    <?php else: ?>
        <h1>Olá, <?= htmlspecialchars($_SESSION['nome']) ?>! Jogue agora</h1>

        <div>
            <img src="pedra.webp" alt="Pedra" class="img-opcao" onclick="enviarJogada('pedra')">
            <img src="papel.webp" alt="Papel" class="img-opcao" onclick="enviarJogada('papel')">
            <img src="tesoura.webp" alt="Tesoura" class="img-opcao" onclick="enviarJogada('tesoura')">
        </div>

        <form method="POST" id="jogoForm">
            <input type="hidden" id="jogada" name="jogada">
        </form>

        <?php if ($usuario && $computador): ?>
            <div class="resultado">
                <p><strong>Sua escolha:</strong> <?= ucfirst(htmlspecialchars($usuario)) ?></p>
                <p><strong>Escolha do computador:</strong> <?= ucfirst(htmlspecialchars($computador)) ?></p>
                <p><?= htmlspecialchars($mensagem) ?></p>
            </div>
        <?php endif; ?>

        <div class="stat">
            <p>Vitórias: <?= $_SESSION['vitorias'] ?></p>
            <p>Empates: <?= $_SESSION['empates'] ?></p>
            <p>Derrotas: <?= $_SESSION['derrotas'] ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
    function enviarJogada(jogada) {
        document.getElementById('jogada').value = jogada;
        document.getElementById('jogoForm').submit();
    }
</script>

</body>
</html>
