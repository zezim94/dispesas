<?php
require 'conexao.php'; // Conexão com o banco de dados

// Consulta para selecionar os pedidos com status 'pendente'
$sql = "SELECT id FROM pedidos WHERE status = 'pendente'";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Resultados
$pedidos = [];
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pedidos[] = $row;
    }
}

// Retorna os pedidos como um JSON
echo json_encode($pedidos);
?>
