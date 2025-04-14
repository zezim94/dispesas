<?php

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletando os dados do formulário (pedido)
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $status = 'Pendente';
    $total = 0.00;

    // Inserindo o pedido diretamente na tabela de pedidos
    $stmt = $pdo->prepare("INSERT INTO pedidos (nome, telefone, email, cpf, cep, endereco, numero, complemento, bairro, cidade, estado, status, total) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$nome, $telefone, $email, $cpf, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $status, $total])) {
        header('Location: login.php');
        exit;
    } else {
        $erro = "Erro ao registrar pedido.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .register-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-custom {
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .register-container {
                max-width: 90%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="register-container">
        <h2 class="text-center">Criar Pedido</h2>
        <?php if (isset($erro)) echo "<p class='text-danger text-center'>$erro</p>"; ?>
        <form method="POST" onsubmit="return validarFormulario()">
            <div class="row">
                <div class="col-12">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" name="nome" id="nome" required placeholder="Digite seu nome">
                </div>
                <div class="col-12">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" required placeholder="Digite seu email">
                </div>
                <div class="col-12">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" name="telefone" id="telefone" required placeholder="Digite seu telefone">
                </div>
                <div class="col-12">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" class="form-control" name="cpf" id="cpf" required placeholder="Digite seu CPF">
                </div>
                <div class="col-12">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control" name="cep" id="cep" required placeholder="Digite seu CEP">
                </div>
                <div class="col-12">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" name="endereco" id="endereco" required placeholder="Rua, Avenida...">
                </div>
                <div class="col-md-6 col-12">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" class="form-control" name="numero" id="numero" required placeholder="Número da residência">
                </div>
                <div class="col-md-6 col-12">
                    <label for="complemento" class="form-label">Complemento</label>
                    <input type="text" class="form-control" name="complemento" id="complemento" placeholder="Ex: Apt 203, Bloco B">
                </div>
                <div class="col-md-6 col-12">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" name="bairro" id="bairro" required placeholder="Bairro">
                </div>
                <div class="col-md-6 col-12">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" name="cidade" id="cidade" required placeholder="Cidade">
                </div>
                <div class="col-12">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" class="form-control" name="estado" id="estado" required placeholder="Estado">
                </div>
            </div>
            <button type="submit" class="btn btn-custom mt-3">Enviar Pedido</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Já tem conta? Faça login</a>
        </div>
         <div class="text-center mt-3">
            <a href="politica_privacidade.php">Politica de privacidade</a>
        </div>
    </div>
</div>

<script>
    document.getElementById("cep").addEventListener("blur", function() {
        let cep = this.value.replace(/\D/g, "");
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById("endereco").value = data.logradouro;
                        document.getElementById("bairro").value = data.bairro;
                        document.getElementById("cidade").value = data.localidade;
                        document.getElementById("estado").value = data.uf;
                    } else {
                        alert("CEP não encontrado.");
                    }
                })
                .catch(error => console.error("Erro ao buscar o CEP: ", error));
        }
    });

    function validarFormulario() {
        let cpf = document.getElementById("cpf").value;
        if (!validarCPF(cpf)) {
            alert("CPF inválido. Por favor, insira um CPF válido.");
            return false; // Impede o envio do formulário
        }
        return true; // Permite o envio do formulário
    }

    function validarCPF(cpf) {
        cpf = cpf.replace(/[^\d]+/g, ''); // Remove caracteres não numéricos

        if (cpf.length !== 11 || /^(.)\1{10}$/.test(cpf)) return false; // Verifica se o CPF tem 11 dígitos e se não é uma sequência repetida

        let soma = 0;
        let resto;

        // Validação do primeiro dígito verificador
        for (let i = 1; i <= 9; i++) {
            soma += parseInt(cpf.charAt(i - 1)) * (11 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(9))) return false;

        soma = 0;
        // Validação do segundo dígito verificador
        for (let i = 1; i <= 10; i++) {
            soma += parseInt(cpf.charAt(i - 1)) * (12 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(10))) return false;

        return true;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
