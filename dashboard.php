<?php require_once "auth.php"; ?>
<?php require_once "auth.php"; ?>
<?php
$conn = new mysqli("localhost", "root", "", "multicalculo_seguro");

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}

/* CONTADORES */
$totalClientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];
$totalVeiculos = $conn->query("SELECT COUNT(*) as total FROM veiculos")->fetch_assoc()['total'];
$totalCotacoes = $conn->query("SELECT COUNT(*) as total FROM cotacoes")->fetch_assoc()['total'];

/* SEGURADORA MAIS USADA */
$seguradora = "Nenhuma";
$res = $conn->query("
    SELECT seguradora, COUNT(*) as total 
    FROM cotacoes 
    GROUP BY seguradora 
    ORDER BY total DESC 
    LIMIT 1
");

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $seguradora = $row['seguradora'] . " (" . $row['total'] . ")";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">

<h1>Dashboard</h1>
<p class="subtitulo">Visão geral do sistema</p>

<div class="nav-links">
    <a href="index.php" class="btn-acao editar">Clientes</a>
    <a href="veiculo.php" class="btn-acao editar">Veículos</a>
    <a href="cotacao.php" class="btn-acao editar">Cotações</a>
    <a href="relatorio.php" class="btn-acao editar">Relatório</a>
    <a href="logout.php" class="btn-acao excluir">Sair</a>
</div>

<div class="dashboard-grid">

    <div class="dashboard-card">
        <span class="dashboard-label">Clientes</span>
        <h3><?php echo $totalClientes; ?></h3>
    </div>

    <div class="dashboard-card">
        <span class="dashboard-label">Veículos</span>
        <h3><?php echo $totalVeiculos; ?></h3>
    </div>

    <div class="dashboard-card">
        <span class="dashboard-label">Cotações</span>
        <h3><?php echo $totalCotacoes; ?></h3>
    </div>

    <div class="dashboard-card">
        <span class="dashboard-label">Mais usada</span>
        <h3><?php echo $seguradora; ?></h3>
    </div>

</div>

</div>
</body>
</html>