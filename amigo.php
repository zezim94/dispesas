<?php
session_start();

// Verifica se os participantes foram cadastrados
if (!isset($_SESSION['participants'])) {
    $_SESSION['participants'] = [];
}

$participants = $_SESSION['participants'];  // A lista de participantes

// Verifica se o formulário foi enviado para adicionar participante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adiciona os participantes no array
    if (isset($_POST['add_participant']) && !empty($_POST['participants'])) {
        $new_participants = array_map('trim', $_POST['participants']);

        // Verifica se os participantes são únicos e não estão vazios
        foreach ($new_participants as $participant) {
            if (!empty($participant) && !in_array($participant, $participants)) {
                $participants[] = $participant;
            }
        }

        $_SESSION['participants'] = $participants;  // Atualiza a sessão
    }

    // Realizar sorteio
    if (isset($_POST['sorteio'])) {
        // Verifica se o número de participantes é par
        if (count($participants) % 2 !== 0) {
            echo '<script>alert("O número de participantes precisa ser par para realizar o sorteio.");</script>';
        } else {
            $amigos_secretos = [];
            $available_participants = $participants;

            foreach ($participants as $participant) {
                // Embaralha os participantes disponíveis
                shuffle($available_participants);

                // Verifica se o participante não é sorteado para ele mesmo
                foreach ($available_participants as $key => $potential_secret_friend) {
                    if ($potential_secret_friend !== $participant) {
                        // Atribui o amigo secreto e remove a pessoa sorteada da lista
                        $amigos_secretos[$participant] = $potential_secret_friend;
                        unset($available_participants[$key]);
                        break;
                    }
                }
            }

            $_SESSION['amigos_secretos'] = $amigos_secretos;
            header('Location: resultado.php');
            exit();
        }
    }

    // Limpar todos os participantes
    if (isset($_POST['clear_participants'])) {
        unset($_SESSION['participants']);
        $participants = [];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo de Amigo Secreto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            margin-top: 50px;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px;
        }

        button {
            border-radius: 10px;
            padding: 12px 20px;
        }

        .participant-list {
            margin: 20px 0;
        }

        .participant-list ul {
            list-style-type: none;
            padding: 0;
        }

        .participant-list li {
            padding: 10px;
            background-color: #e3f2fd;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .btn-custom {
            width: 200px;
            padding: 12px 20px;
            font-size: 16px;
            margin: 10px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .participant-inputs {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container text-center">
        <h1>Jogo de Amigo Secreto</h1>

        <?php if (empty($participants)): ?>
            <div class="container form-container">
                <h3>Cadastre os participantes</h3>
                <form method="POST" id="participantsForm">
                    <div class="participant-inputs">
                        <input type="text" name="participants[]" class="form-control" placeholder="Nome do participante" required>
                    </div>
                    <button type="button" id="addParticipant" class="btn btn-info btn-custom">
                        <i class="fas fa-plus"></i> Adicionar Mais
                    </button>
                    <button type="submit" name="add_participant" class="btn btn-success btn-custom">Ir para sorteio</button>
                </form>
            </div>
        <?php else: ?>
            <div class="participant-list">
                <h3>Participantes cadastrados:</h3>
                <ul>
                    <?php foreach ($participants as $participant): ?>
                        <li><?= htmlspecialchars($participant) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="POST">
                <button type="submit" name="sorteio" class="btn btn-primary btn-custom">Realizar Sorteio</button>
            </form>
        <?php endif; ?>

        <!-- Formulário para limpar a lista de participantes -->
        <form method="POST">
            <button type="submit" name="clear_participants" class="btn btn-danger btn-custom">Limpar Participantes</button>
        </form>

        <!-- Botão Voltar -->
        <a href="index.php" class="btn btn-secondary btn-custom">Voltar</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Adiciona novo campo de participante
        document.getElementById('addParticipant').addEventListener('click', function() {
            var newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.name = 'participants[]';
            newInput.classList.add('form-control');
            newInput.classList.add('mt-2');
            newInput.placeholder = 'Nome do participante';
            document.querySelector('.participant-inputs').appendChild(newInput);
        });

        // Impede o envio se o número de participantes for ímpar
        document.getElementById('participantsForm').addEventListener('submit', function(event) {
            var participants = document.querySelectorAll('input[name="participants[]"]');
            if (participants.length % 2 !== 0) {
                alert('O número de participantes precisa ser par para realizar o sorteio.');
                event.preventDefault();
            }
        });
    </script>
</body>

</html>