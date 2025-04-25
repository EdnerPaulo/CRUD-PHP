<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto</title>
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

        // Criar a tabela "produtos" se não existir (Adapte o nome e os campos)
        $sql = "CREATE TABLE IF NOT EXISTS produtos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome_produto VARCHAR(255) NOT NULL,
            valor_produto DECIMAL(10, 2) NOT NULL, // Use DECIMAL para valores monetários
            quantidade_produto INT NOT NULL,
            fornecedor_produto VARCHAR(255) NOT NULL
        )";
        $pdo->exec($sql);

        // Verificar se todos os campos foram preenchidos (Adapte os nomes)
        if (empty($_POST["produtos"]) || empty($_POST["valor"]) || empty($_POST["quantidade"]) || empty($_POST["fornecedor"])) {
            echo "<div class='result-message' style='color:red;'>Por favor, preencha todos os campos.</div>"; // Alterado
        } else {
            // Filtrar dados do formulário (Adapte os nomes)
            $nome_produto = filter_var($_POST["produtos"], FILTER_SANITIZE_STRING);
            $valor_produto = filter_var($_POST["valor"], FILTER_SANITIZE_STRING); // Pode precisar de mais validação
            $quantidade_produto = filter_var($_POST["quantidade"], FILTER_SANITIZE_NUMBER_INT); // Validar como inteiro
            $fornecedor_produto = filter_var($_POST["fornecedor"], FILTER_SANITIZE_STRING);

            // Inserir o novo produto no banco (Adapte os nomes da tabela e dos campos)
            $sql = "INSERT INTO produtos (nome_produto, valor_produto, quantidade_produto, fornecedor_produto) VALUES (:nome_produto, :valor_produto, :quantidade_produto, :fornecedor_produto)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome_produto', $nome_produto);
            $stmt->bindParam(':valor_produto', $valor_produto);
            $stmt->bindParam(':quantidade_produto', $quantidade_produto);
            $stmt->bindParam(':fornecedor_produto', $fornecedor_produto);

            if ($stmt->execute()) {
                echo "<div class='result-message'><h1>Produto cadastrado com sucesso!</h1><p>Produto <strong>$nome_produto</strong> cadastrado.</p></div>"; // Alterado
            } else {
                echo "<div class='result-message' style='color:red;'>Erro ao cadastrar produto.</div>"; // Alterado
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