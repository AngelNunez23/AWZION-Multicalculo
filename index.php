<?php require_once "auth.php"; ?>
<?php
$conn = new mysqli("localhost", "root", "", "multicalculo_seguro");

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

$mensagem = "";
$editando = false;
$clienteEditar = [
    "id" => "",
    "nome" => "",
    "email" => "",
    "telefone" => "",
    "cpf" => ""
];

/* EXCLUIR CLIENTE */
if (isset($_GET["excluir"])) {
    $idExcluir = (int) $_GET["excluir"];

    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $idExcluir);

    if ($stmt->execute()) {
        $mensagem = "Cliente excluído com sucesso!";
    } else {
        $mensagem = "Erro ao excluir cliente.";
    }

    $stmt->close();
}

/* CARREGAR CLIENTE PARA EDITAR */
if (isset($_GET["editar"])) {
    $idEditar = (int) $_GET["editar"];

    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $idEditar);
    $stmt->execute();
    $resultadoEditar = $stmt->get_result();

    if ($resultadoEditar->num_rows > 0) {
        $clienteEditar = $resultadoEditar->fetch_assoc();
        $editando = true;
    }

    $stmt->close();
}

/* SALVAR OU ATUALIZAR CLIENTE */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"] ?? "";
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $cpf = trim($_POST["cpf"] ?? "");

    if ($nome === "" || $email === "" || $telefone === "" || $cpf === "") {
        $mensagem = "Preencha todos os campos.";
    } else {
        if ($id !== "") {
            $stmt = $conn->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, cpf = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nome, $email, $telefone, $cpf, $id);

            if ($stmt->execute()) {
                $mensagem = "Cliente atualizado com sucesso!";
            } else {
                $mensagem = "Erro ao atualizar cliente.";
            }

            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone, cpf) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $telefone, $cpf);

            if ($stmt->execute()) {
                $mensagem = "Cliente cadastrado com sucesso!";
            } else {
                $mensagem = "Erro ao cadastrar cliente.";
            }

            $stmt->close();
        }
    }
}

$clientes = $conn->query("SELECT * FROM clientes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multicálculo de Seguros - Clientes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Multicálculo de Seguros</h1>
    <p class="subtitulo">Sistema de cadastro e controle de clientes para cotação de seguros</p>

    <div class="nav-links">
    <a href="dashboard.php" class="btn-acao editar">Dashboard</a>
    <a href="veiculo.php" class="btn-acao editar">Ir para Veículos</a>
    <a href="cotacao.php" class="btn-acao editar">Ir para Cotação</a>
    <a href="relatorio.php" class="btn-acao editar">Relatório PDF</a>
    <a href="logout.php" class="btn-acao excluir">Sair</a>
</div>
</div>

    <div class="card">
        <h2><?php echo $editando ? "Editar Cliente" : "Cadastrar Cliente"; ?></h2>

        <?php if ($mensagem != ""): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($clienteEditar["id"]); ?>">

            <div>
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome do cliente" required
                       value="<?php echo htmlspecialchars($clienteEditar["nome"]); ?>">
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Digite o email" required
                       value="<?php echo htmlspecialchars($clienteEditar["email"]); ?>">
            </div>

            <div>
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" placeholder="Digite o telefone" required
                       value="<?php echo htmlspecialchars($clienteEditar["telefone"]); ?>">
            </div>

            <div>
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite o CPF" required
                       value="<?php echo htmlspecialchars($clienteEditar["cpf"]); ?>">
            </div>

            <div class="full">
                <button type="submit">
                    <?php echo $editando ? "Atualizar Cliente" : "Salvar Cliente"; ?>
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Clientes Cadastrados</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>CPF</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($clientes && $clientes->num_rows > 0): ?>
                    <?php while ($cliente = $clientes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente["id"]); ?></td>
                            <td><?php echo htmlspecialchars($cliente["nome"]); ?></td>
                            <td><?php echo htmlspecialchars($cliente["email"]); ?></td>
                            <td><?php echo htmlspecialchars($cliente["telefone"]); ?></td>
                            <td><?php echo htmlspecialchars($cliente["cpf"]); ?></td>
                            <td><?php echo htmlspecialchars($cliente["created_at"]); ?></td>
                            <td>
                                <a href="?editar=<?php echo $cliente["id"]; ?>" class="btn-acao editar">Editar</a>
                                <a href="?excluir=<?php echo $cliente["id"]; ?>" class="btn-acao excluir"
                                   onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhum cliente cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>