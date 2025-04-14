<?php
session_start();

// Verifica se a lista de jogadores já existe na sessão. Se não, inicializa como array vazio.
if (!isset($_SESSION['jogadores'])) {
    $_SESSION['jogadores'] = [];
}

// Inicializa as variáveis de sessão para o jogador atual
if (!isset($_SESSION['nome'])) {
    $_SESSION['nome'] = '';
    $_SESSION['acertos'] = 0;
    $_SESSION['erros'] = 0;
    $_SESSION['historico'] = [];
}

// Função para gerar uma pergunta aleatória
function gerarPergunta()
{
    $operacoes = ['+', '-', '*', '/', '**', 'sqrt'];
    $operacao = $operacoes[array_rand($operacoes)];

    $numero1 = rand(1, 10);
    $numero2 = rand(1, 10);

    // Exceções para operações
    if ($operacao === '+' || $operacao === '-' || $operacao === '*') {
        return gerarOperacaoSimples($operacao, $numero1, $numero2);
    } elseif ($operacao === '/') {
        $numero1 = $numero2 * rand(1, 10); // Garante que não haverá divisão por zero
        return gerarOperacaoSimples($operacao, $numero1, $numero2);
    } elseif ($operacao === '**') {
        return gerarExponenciacao($numero1, $numero2);
    } elseif ($operacao === 'sqrt') {
        return gerarRadiciacao($numero1);
    }
}

// Função para gerar uma operação simples
function gerarOperacaoSimples($operacao, $numero1, $numero2)
{
    switch ($operacao) {
        case '+':
            $resposta = $numero1 + $numero2;
            break;
        case '-':
            $resposta = $numero1 - $numero2;
            break;
        case '*':
            $resposta = $numero1 * $numero2;
            break;
        case '/':
            $resposta = $numero1 / $numero2;
            break;
    }

    return [
        'pergunta' => "$numero1 $operacao $numero2 = ?",
        'resposta' => round($resposta, 3) // Limita a resposta para 3 casas decimais
    ];
}

// Função para gerar exponenciação
function gerarExponenciacao($numero1, $numero2)
{
    $resposta = pow($numero1, $numero2);
    return [
        'pergunta' => "$numero1 ^ $numero2 = ?",
        'resposta' => round($resposta, 3) // Limita a resposta para 3 casas decimais
    ];
}

// Função para gerar radiciação
function gerarRadiciacao($numero1)
{
    $resposta = sqrt($numero1);
    return [
        'pergunta' => "√$numero1 = ?",
        'resposta' => round($resposta, 3) // Limita a resposta para 3 casas decimais
    ];
}

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cadastro de novo jogador
    if (isset($_POST['novo_nome']) && $_POST['novo_nome'] !== '') {
        $novoJogador = $_POST['novo_nome'];

        // Verifica se o nome do jogador já existe
        if (array_key_exists($novoJogador, $_SESSION['jogadores'])) {
            $mensagemErro = "Jogador com esse nome já existe! Escolha outro nome.";
        } else {
            // Adiciona o novo jogador à lista de jogadores
            $_SESSION['jogadores'][$novoJogador] = [
                'acertos' => 0,
                'erros' => 0,
                'historico' => []
            ];

            // Configura o jogador atual para o novo jogador
            $_SESSION['nome'] = $novoJogador;
        }
    }

    // Se o jogador escolher um existente
    if (isset($_POST['nome_jogador']) && $_POST['nome_jogador'] !== '') {
        // Seleciona o jogador, sem alterar o histórico
        $_SESSION['nome'] = $_POST['nome_jogador'];
    }

    // Resposta do jogador
    if (isset($_POST['resposta_usuario'])) {
        $respostaUsuario = $_POST['resposta_usuario'];
        $respostaCorreta = $_SESSION['resposta'];

        // Verificar se a resposta está correta
        if (floatval($respostaUsuario) == floatval($respostaCorreta)) {
            $_SESSION['jogadores'][$_SESSION['nome']]['acertos']++;
            $mensagem = "Resposta correta!";
            $acertou = true;
        } else {
            $_SESSION['jogadores'][$_SESSION['nome']]['erros']++;
            $mensagem = "Resposta errada! A resposta correta era: $respostaCorreta";
            $acertou = false;
        }

        // Armazenar no histórico
        $historico = [
            'nome' => $_SESSION['nome'],
            'pergunta' => $_SESSION['pergunta'],
            'resposta_usuario' => $respostaUsuario,
            'resposta_correta' => $respostaCorreta,
            'acertou' => $acertou ? 'Acertou' : 'Errou'
        ];
        $_SESSION['jogadores'][$_SESSION['nome']]['historico'][] = $historico;

        // Gerar nova pergunta
        $pergunta = gerarPergunta();
        $_SESSION['pergunta'] = $pergunta['pergunta'];
        $_SESSION['resposta'] = $pergunta['resposta'];
    }
} else {
    // Gerar a primeira pergunta
    $pergunta = gerarPergunta();
    $_SESSION['pergunta'] = $pergunta['pergunta'];
    $_SESSION['resposta'] = $pergunta['resposta'];
}

// Exibir a interface
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo de Perguntas Matemáticas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin-top: 50px;
        }

        .btn {
            font-size: 18px;
            padding: 10px 20px;
        }

        .stat {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .card-body h4 {
            font-size: 1.5rem;
        }

        .alert {
            font-size: 16px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Jogo de Perguntas Matemáticas</h1>
        <p class="lead">Responda corretamente as perguntas para ganhar pontos.</p>

            <!-- Se o jogador já foi selecionado, mostra a pergunta -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"><?= $_SESSION['pergunta'] ?></h4>
                    <form method="POST">
                        <div class="form-group">
                            <label for="resposta_usuario">Sua Resposta:</label>
                            <input type="text" class="form-control" id="resposta_usuario" name="resposta_usuario" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Responder</button>
                    </form>
                </div>
            </div>

            <?php if (isset($mensagem)): ?>
                <div class="alert alert-info mt-3">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <div class="stat">
                <p><strong>Jogador: </strong><?= $_SESSION['nome'] ?></p>
                <p><strong>Acertos: </strong><?= isset($_SESSION['jogadores'][$_SESSION['nome']]['acertos']) ? $_SESSION['jogadores'][$_SESSION['nome']]['acertos'] : 0 ?></p>
                <p><strong>Erros: </strong><?= isset($_SESSION['jogadores'][$_SESSION['nome']]['erros']) ? $_SESSION['jogadores'][$_SESSION['nome']]['erros'] : 0 ?></p>
            </div>
            <div class="card mt-4">
            <div class="card-body">
    <h4 class="card-title">Escolher um Jogador Existente</h4>
    <form method="POST">
        <select name="nome_jogador" class="form-control" onchange="this.form.submit()">
            <option value="">Selecione um jogador</option>
            <?php foreach ($_SESSION['jogadores'] as $jogador => $dados): ?>
                <option value="<?= $jogador ?>" <?= $jogador === $_SESSION['nome'] ? 'selected' : '' ?>><?= $jogador ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

            <!-- Formulário para cadastrar um novo jogador -->
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title">Criar um novo jogador</h4>
                    <form method="POST">
                        <div class="form-group">
                            <label for="novo_nome">Nome do Jogador:</label>
                            <input type="text" class="form-control" id="novo_nome" name="novo_nome" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar Jogador</button>
                    </form>
                    <?php if (isset($mensagemErro)): ?>
                        <div class="alert alert-danger mt-3">
                            <?= $mensagemErro ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <a href="mat_historico.php" class="btn btn-secondary mt-3">Ver Histórico</a>
            <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
        
    </div>

</body>

</html>
