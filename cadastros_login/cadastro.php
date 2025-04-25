<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    $host = 'localhost';
    $banco = 'cadastro';
    $usuario = 'root';
    $senha = '';

    try {
        // Conexão com MySQL procedural para criar o banco (Manter, usar PDO para o resto)
        $mysqli = new mysqli($host, $usuario, $senha);
        if ($mysqli->connect_error) {
            throw new Exception("Erro de conexão (MySQLi): " . $mysqli->connect_error);
        }

        // Criar banco se não existir (Manter, usar PDO para o resto)
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS $banco")) {
            throw new Exception("Erro ao criar banco (MySQLi): " . $mysqli->error);
        }

        // Conectar ao banco com PDO (Usar apenas PDO daqui para frente)
        $pdo = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Melhor prática

        // Criar a tabela "clientes" se não existir
        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL
        )";
        $pdo->exec($sql);

        // Verificar se todos os campos foram preenchidos
        if (empty($_POST["name"]) || empty($_POST["username"]) || empty($_POST["password1"]) || empty($_POST["password2"])) {
            echo "<div class='result-message' style='color:red;'>Por favor, preencha todos os campos.</div>"; // Alterado
        } elseif ($_POST["password1"] != $_POST["password2"]) {
            echo "<div class='result-message' style='color:red;'>As senhas não coincidem.</div>"; // Alterado
        } else {
            // Filtrar dados do formulário
            $nome = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST["username"], FILTER_SANITIZE_EMAIL);
            $senha = $_POST["password1"];
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Verificar se o e-mail já existe
            $sql = "SELECT COUNT(*) FROM clientes WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo "<div class='result-message' style='color:red;'>O e-mail $email já está cadastrado. Por favor, use outro e-mail.</div>"; // Alterado
            } else {
                // Inserir o novo usuário no banco
                $sql = "INSERT INTO clientes (nome, email, senha) VALUES (:nome, :email, :senha)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senhaHash);

                if ($stmt->execute()) {
                    echo "<div class='result-message'><h1>Cadastrado com sucesso!</h1><p>É um prazer te conhecer, <strong>$nome</strong>.</p></div>"; // Alterado
                } else {
                    echo "<div class='result-message' style='color:red;'>Erro ao cadastrar usuário.</div>"; // Alterado
                }
            }
        }
    } catch (PDOException $e) {
        echo "<div class='result-message' style='color:red;'>Erro de banco de dados: " . $e->getMessage() . "</div>";
    } catch (Exception $e) {
        echo "<div class='result-message' style='color:red;'>Erro: " . $e->getMessage() . "</div>";
    } finally {
        if ($mysqli) {
            $mysqli->close();
        }
    }
    ?>
    <br>
    <p><a href="index.html">Voltar ao formulário</a></p>
</body>

</html>