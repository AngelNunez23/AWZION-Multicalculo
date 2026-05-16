<?php require_once "auth.php"; ?>
<?php
$conn = new mysqli("localhost", "root", "", "multicalculo_seguro");

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

$resClientes = $conn->query("SELECT * FROM clientes ORDER BY id DESC");
$resVeiculos = $conn->query("
    SELECT v.*, c.nome AS cliente_nome
    FROM veiculos v
    INNER JOIN clientes c ON v.cliente_id = c.id
    ORDER BY v.id DESC
");
$resCotacoes = $conn->query("
    SELECT 
        cotacoes.*,
        c.nome AS cliente_nome,
        v.marca,
        v.modelo,
        v.placa
    FROM cotacoes
    INNER JOIN veiculos v ON cotacoes.veiculo_id = v.id
    INNER JOIN clientes c ON v.cliente_id = c.id
    ORDER BY cotacoes.id DESC
");

$totalClientes = $conn->query("SELECT COUNT(*) AS total FROM clientes")->fetch_assoc()["total"];
$totalVeiculos = $conn->query("SELECT COUNT(*) AS total FROM veiculos")->fetch_assoc()["total"];
$totalCotacoes = $conn->query("SELECT COUNT(*) AS total FROM cotacoes")->fetch_assoc()["total"];

$seguradoraMaisUsada = "Nenhuma";
$qSeg = $conn->query("
    SELECT seguradora, COUNT(*) AS total
    FROM cotacoes
    GROUP BY seguradora
    ORDER BY total DESC
    LIMIT 1
");
if ($qSeg && $qSeg->num_rows > 0) {
    $seg = $qSeg->fetch_assoc();
    $seguradoraMaisUsada = $seg["seguradora"] . " (" . $seg["total"] . ")";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral - Multicálculo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container relatorio">
    <h1>Relatório Geral</h1>
    <p class="subtitulo">Multicálculo de Seguros</p>
   
    <div class="nav-links no-print">
    <a href="dashboard.php" class="btn-acao editar">Dashboard</a>
    <a href="index.php" class="btn-acao editar">Clientes</a>
    <a href="veiculo.php" class="btn-acao editar">Veículos</a>
    <a href="cotacao.php" class="btn-acao editar">Cotações</a>
    <button onclick="window.print()">Imprimir / Salvar em PDF</button>
    <a href="logout.php" class="btn-acao excluir">Sair</a>
</div>
    
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <span class="dashboard-label">Total de Clientes</span>
            <h3><?php echo $totalClientes; ?></h3>
        </div>
        <div class="dashboard-card">
            <span class="dashboard-label">Total de Veículos</span>
            <h3><?php echo $totalVeiculos; ?></h3>
        </div>
        <div class="dashboard-card">
            <span class="dashboard-label">Total de Cotações</span>
            <h3><?php echo $totalCotacoes; ?></h3>
        </div>
        <div class="dashboard-card">
            <span class="dashboard-label">Seguradora Mais Usada</span>
            <h3><?php echo htmlspecialchars($seguradoraMaisUsada); ?></h3>
        </div>
    </div>

    <div class="card">
        <h2>Clientes</h2>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resClientes && $resClientes->num_rows > 0): ?>
                        <?php while($c = $resClientes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $c["id"]; ?></td>
                                <td><?php echo htmlspecialchars($c["nome"]); ?></td>
                                <td><?php echo htmlspecialchars($c["email"]); ?></td>
                                <td><?php echo htmlspecialchars($c["telefone"]); ?></td>
                                <td><?php echo htmlspecialchars($c["cpf"]); ?></td>
                                <td><?php echo htmlspecialchars($c["created_at"]); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">Nenhum cliente cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Veículos</h2>
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
                    <?php if ($resVeiculos && $resVeiculos->num_rows > 0): ?>
                        <?php while($v = $resVeiculos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $v["id"]; ?></td>
                                <td><?php echo htmlspecialchars($v["cliente_nome"]); ?></td>
                                <td><?php echo htmlspecialchars($v["marca"]); ?></td>
                                <td><?php echo htmlspecialchars($v["modelo"]); ?></td>
                                <td><?php echo htmlspecialchars($v["ano"]); ?></td>
                                <td><?php echo htmlspecialchars($v["placa"]); ?></td>
                                <td>R$ <?php echo number_format($v["valor_fipe"], 2, ",", "."); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Nenhum veículo cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Cotações</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Placa</th>
                        <th>Seguradora</th>
                        <th>Taxa</th>
                        <th>Valor</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resCotacoes && $resCotacoes->num_rows > 0): ?>
                        <?php while($ct = $resCotacoes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $ct["id"]; ?></td>
                                <td><?php echo htmlspecialchars($ct["cliente_nome"]); ?></td>
                                <td><?php echo htmlspecialchars($ct["marca"] . " " . $ct["modelo"]); ?></td>
                                <td><?php echo htmlspecialchars($ct["placa"]); ?></td>
                                <td><?php echo htmlspecialchars($ct["seguradora"]); ?></td>
                                <td><?php echo number_format($ct["taxa_aplicada"], 2, ",", "."); ?>%</td>
                                <td>R$ <?php echo number_format($ct["valor_seguro"], 2, ",", "."); ?></td>
                                <td><?php echo htmlspecialchars($ct["created_at"]); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">Nenhuma cotação cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>