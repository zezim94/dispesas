<?php
require 'conexao.php';

$sql = "SELECT id FROM pedidos WHERE status = 'pendente'";
$result = $conn->query($sql);

$pedidos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}

echo json_encode($pedidos);
