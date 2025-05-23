<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Fornecedor</title>
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
            throw new Exception("Erro de conexão (MySQLi): " . $mysqli->connect_error);
        }

        // Criar banco se não existir
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS $banco")) {
            throw new Exception("Erro ao criar banco (MySQLi): " . $mysqli->error);
        }

        // Conectar ao banco com PDO
        $pdo = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Criar a tabela "fornecedores" se não existir
        $sql = "CREATE TABLE IF NOT EXISTS fornecedores (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome_fornecedor VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            nome_produto_fornecido VARCHAR(255) NOT NULL
        )";
        $pdo->exec($sql);

        // Verificar se todos os campos foram preenchidos
        if (empty($_POST["fornecedor"]) || empty($_POST["username"]) || empty($_POST["telefone"]) || empty($_POST["produtosf"])) {
            echo "<div class='result-message' style='color:red;'>Por favor, preencha todos os campos.</div>";
        } else {
            // Filtrar dados do formulário
            $nome_fornecedor = filter_var($_POST["fornecedor"], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST["username"], FILTER_SANITIZE_EMAIL);
            $telefone = filter_var($_POST["telefone"], FILTER_SANITIZE_STRING);
            $nome_produto_fornecido = filter_var($_POST["produtosf"], FILTER_SANITIZE_STRING);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='result-message' style='color:red;'>Por favor, insira um e-mail válido.</div>";
            } else {
                // Verificar se o e-mail já existe
                $sql = "SELECT COUNT(*) FROM fornecedores WHERE email = :email";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    echo "<div class='result-message' style='color:red;'>O e-mail $email já está cadastrado. Por favor, use outro e-mail.</div>";
                } else {
                    // Inserir o novo fornecedor no banco
                    $sql = "INSERT INTO fornecedores (nome_fornecedor, email, telefone, nome_produto_fornecido) VALUES (:nome_fornecedor, :email, :telefone, :nome_produto_fornecido)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':nome_fornecedor', $nome_fornecedor);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':telefone', $telefone);
                    $stmt->bindParam(':nome_produto_fornecido', $nome_produto_fornecido);

                    if ($stmt->execute()) {
                        echo "<div class='result-message'><h1>Fornecedor cadastrado com sucesso!</h1><p>Fornecedor <strong>$nome_fornecedor</strong> cadastrado.</p></div>";
                    } else {
                        echo "<div class='result-message' style='color:red;'>Erro ao cadastrar fornecedor.</div>";
                    }
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