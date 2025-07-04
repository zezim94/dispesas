<?php
require_once __DIR__ . '/vendor/autoload.php';

// Carrega variáveis do .env apenas se o arquivo existir
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Usa variáveis de ambiente do .env ou definidas no Render
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$db = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
$user = $_ENV['DB_USER'] ?? getenv('DB_USER');
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT'); // A porta padrão do PostgreSQL é 5432

// String de Conexão (DSN) para PostgreSQL
$dsn = "pgsql:host=$host;dbname=$db;port=$port";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Cria a conexão com o banco de dados
    $pdo = new PDO($dsn, $user, $pass, $options);
    // No caso de sucesso, a conexão é feita sem "echo"
    // Não envia nenhuma mensagem para o navegador ou logs, para evitar problemas com headers.
    $conn = $pdo;
} catch (\PDOException $e) {
    // Captura o erro caso a conexão falhe
    // Aqui, você pode registrar o erro em um log de erro e não exibi-lo diretamente
    error_log("Erro na conexão com o banco de dados: " . $e->getMessage()); // Registra no log de erros do servidor
    echo "Erro na conexão com o banco de dados."; // Mensagem genérica para o usuário
    exit;
}
