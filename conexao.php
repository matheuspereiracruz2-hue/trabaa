<?php
/**
 * conexao.php - Cria a conexão PDO com o MySQL.
 * Ajuste usuário e senha conforme o seu ambiente (XAMPP padrão: root / senha vazia).
 */
$host    = 'localhost';
$banco   = 'stardew_wiki';
$usuario = 'root';
$senha   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            // Lança exceções em caso de erro (facilita encontrar problemas)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Retorna arrays associativos por padrão
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Usa prepared statements reais do servidor (proteção extra contra SQL Injection)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Não exibimos detalhes técnicos ao visitante; gravamos no log do servidor.
    error_log('Erro de conexão: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Verifique o arquivo conexao.php.');
}
