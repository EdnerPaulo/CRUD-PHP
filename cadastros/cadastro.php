<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrado com Sucesso</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
$host = 'localhost';
$banco = 'cadastro';
$usuario = 'root';
$senha = '';

try {
    // Conexão com MySQL procedural para criar o banco
    $mysqli = new mysqli($host, $usuario, $senha);
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }

    // Criar banco se não existir
    if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS $banco")) {
        throw new Exception("Erro ao criar banco: " . $mysqli->error);
    }

    // Conectar ao banco com PDO
    $pdo = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar a tabela "usuarios" se não existir
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        senha VARCHAR(255)
    )";
    $pdo->exec($sql);

    // Verificar se todos os campos foram preenchidos
    if (
        empty($_POST["nome"]) ||
        empty($_POST["email"]) ||
        empty($_POST["senha"]) ||
        empty($_POST["senhai"])
    ) {
        echo "<div style='color:red;'>Por favor, preencha todos os campos.</div>";
    } elseif ($_POST["senha"] != $_POST["senhai"]) {
        echo "<div style='color:red;'>As senhas não coincidem.</div>";
    } else {
        // Filtrar dados do formulário
        $nome = filter_var($_POST["nome"], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $senha = $_POST["senha"];
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Verificar se o e-mail já existe
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetchColumn();

        if ($result > 0) {
            echo "<div style='color:red;'>O e-mail $email já está cadastrado. Por favor, use outro e-mail.</div>";
        } else {
            // Inserir o novo usuário no banco
            $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senhaHash);

            if ($stmt->execute()) {
                echo "<h1>Cadastrado com sucesso!</h1>";
                echo "<p>É um prazer te conhecer, <strong>$nome</strong>.</p>";
            } else {
                echo "<div style='color:red;'>Erro ao cadastrar usuário.</div>";
            }
        }
    }

} catch (Exception $e) {
    echo "<div style='color:red;'>Erro: " . $e->getMessage() . "</div>";
}
?>
    <br>
    <p><a href="index.html">Voltar ao formulário</a></p>
</body>
</html>
