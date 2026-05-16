
<?php
$conn = new mysqli("localhost", "root", "", "multicalculo_seguro");

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}

$msg = "";

// SALVAR VEÍCULO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $placa = $_POST['placa'] ?? '';
    $valor_fipe = $_POST['valor_fipe'] ?? '';

    if ($cliente_id != "" && $marca != "" && $modelo != "" && $ano != "" && $placa != "" && $valor_fipe != "") {
        $stmt = $conn->prepare("INSERT INTO veiculos (cliente_id, marca, modelo, ano, placa, valor_fipe) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issisd", $cliente_id, $marca, $modelo, $ano, $placa, $valor_fipe);

        if ($stmt->execute()) {
            $msg = "Veículo cadastrado com sucesso!";
        } else {
            $msg = "Erro ao cadastrar veículo: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $msg = "Preencha todos os campos.";
    }
}

// LISTAR CLIENTES
$clientes = $conn->query("SELECT * FROM clientes ORDER BY nome ASC");

// LISTAR VEÍCULOS
$veiculos = $conn->query("
    SELECT v.*, c.nome AS cliente_nome
    FROM veiculos v
    JOIN clientes c ON v.cliente_id = c.id
    ORDER BY v.id DESC
");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multicálculo de Seguros - Veículos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Cadastro de Veículos</h1>
    <p class="subtitulo">Associe veículos aos clientes cadastrados</p>

    <div class="nav-links">
    <a href="dashboard.php" class="btn-acao editar">Dashboard</a>
    <a href="index.php" class="btn-acao editar">Voltar para Clientes</a>
    <a href="cotacao.php" class="btn-acao editar">Ir para Cotação</a>
    <a href="relatorio.php" class="btn-acao editar">Relatório PDF</a>
    <a href="logout.php" class="btn-acao excluir">Sair</a>
</div>
</div>

    <div class="card">
        <h2>Cadastrar Veículo</h2>

        <?php if ($msg != ""): ?>
            <div class="mensagem"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label for="cliente_id">Cliente</label>
                <select name="cliente_id" id="cliente_id" required>
                    <option value="">Selecione</option>
                    <?php if ($clientes && $clientes->num_rows > 0): ?>
                        <?php while ($c = $clientes->fetch_assoc()) { ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['nome']); ?>
                            </option>
                        <?php } ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label for="marca">Marca</label>
                <select name="marca" id="marca" required>
                    <option value="">Selecione a marca</option>
                    <option value="Toyota">Toyota</option>
                    <option value="Honda">Honda</option>
                    <option value="Ford">Ford</option>
                    <option value="Chevrolet">Chevrolet</option>
                    <option value="Volkswagen">Volkswagen</option>
                    <option value="Fiat">Fiat</option>
                    <option value="Hyundai">Hyundai</option>
                    <option value="Kia">Kia</option>
                    <option value="Renault">Renault</option>
                    <option value="Nissan">Nissan</option>
                    <option value="Jeep">Jeep</option>
                    <option value="BMW">BMW</option>
                    <option value="Mercedes-Benz">Mercedes-Benz</option>
                    <option value="Audi">Audi</option>
                    <option value="Peugeot">Peugeot</option>
                    <option value="Citroën">Citroën</option>
                    <option value="Mitsubishi">Mitsubishi</option>
                    <option value="Volvo">Volvo</option>
                    <option value="Land Rover">Land Rover</option>
                    <option value="Porsche">Porsche</option>
                    <option value="BYD">BYD</option>
                    <option value="Chery">Chery</option>
                </select>
            </div>

            <div>
                <label for="modelo">Modelo</label>
                <input type="text" name="modelo" id="modelo" placeholder="Digite o modelo" required>
            </div>

            <div>
                <label for="ano">Ano</label>
                <input type="number" name="ano" id="ano" placeholder="Digite o ano" required>
            </div>

            <div>
                <label for="placa">Placa</label>
                <input type="text" name="placa" id="placa" placeholder="Digite a placa" required>
            </div>

            <div>
                <label for="valor_fipe">Valor FIPE</label>
                <input type="number" step="0.01" name="valor_fipe" id="valor_fipe" placeholder="Digite o valor FIPE" required>
            </div>

            <div class="full">
                <button type="submit">Salvar Veículo</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Veículos Cadastrados</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Ano</th>
                        <th>Placa</th>
                        <th>FIPE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($veiculos && $veiculos->num_rows > 0): ?>
                        <?php while ($v = $veiculos->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $v['id']; ?></td>
                                <td><?php echo htmlspecialchars($v['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($v['marca']); ?></td>
                                <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($v['ano']); ?></td>
                                <td><?php echo htmlspecialchars($v['placa']); ?></td>
                                <td>R$ <?php echo number_format($v['valor_fipe'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Nenhum veículo cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>