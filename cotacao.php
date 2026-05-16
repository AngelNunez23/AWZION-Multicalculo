<?php require_once "auth.php"; ?>
<?php
$conn = new mysqli("localhost", "root", "", "multicalculo_seguro");

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}

$mensagem = "";
$resultados = [];
$melhorOpcao = null;

$veiculos = $conn->query("
    SELECT v.*, c.nome AS cliente_nome
    FROM veiculos v
    INNER JOIN clientes c ON v.cliente_id = c.id
    ORDER BY v.id DESC
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $veiculo_id = $_POST["veiculo_id"] ?? "";

    if ($veiculo_id != "") {
        $stmt = $conn->prepare("
            SELECT v.*, c.nome AS cliente_nome
            FROM veiculos v
            INNER JOIN clientes c ON v.cliente_id = c.id
            WHERE v.id = ?
        ");
        $stmt->bind_param("i", $veiculo_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $veiculo = $res->fetch_assoc();

            $valorFipe = (float)$veiculo["valor_fipe"];
            $ano = (int)$veiculo["ano"];
            $idadeVeiculo = date("Y") - $ano;
            $marca = strtolower(trim($veiculo["marca"]));

            $seguradoras = [
                ["nome" => "Porto Seguro", "taxa_base" => 4.20],
                ["nome" => "Azul Seguros", "taxa_base" => 3.90],
                ["nome" => "Tokio Marine", "taxa_base" => 4.60],
                ["nome" => "Bradesco Seguros", "taxa_base" => 5.00]
            ];

            foreach ($seguradoras as $seg) {
                $taxa = $seg["taxa_base"];

                // ajuste pelo valor FIPE
                if ($valorFipe > 50000 && $valorFipe <= 100000) {
                    $taxa += 0.50;
                } elseif ($valorFipe > 100000) {
                    $taxa += 1.00;
                }

                // ajuste pela idade do veículo
                if ($idadeVeiculo <= 3) {
                    $taxa += 0.80;
                } elseif ($idadeVeiculo > 10) {
                    $taxa += 1.20;
                }

                // ajuste por marca de maior risco
                if (
                    $marca == "bmw" ||
                    $marca == "audi" ||
                    $marca == "mercedes-benz" ||
                    $marca == "porsche" ||
                    $marca == "land rover"
                ) {
                    $taxa += 1.50;
                }

                $valorSeguro = $valorFipe * ($taxa / 100);

                $resultados[] = [
                    "cliente" => $veiculo["cliente_nome"],
                    "marca" => $veiculo["marca"],
                    "modelo" => $veiculo["modelo"],
                    "ano" => $veiculo["ano"],
                    "placa" => $veiculo["placa"],
                    "valor_fipe" => $valorFipe,
                    "idade" => $idadeVeiculo,
                    "seguradora" => $seg["nome"],
                    "taxa" => $taxa,
                    "valor_seguro" => $valorSeguro
                ];
            }

            // ordenar pelo menor valor
            usort($resultados, function ($a, $b) {
                return $a["valor_seguro"] <=> $b["valor_seguro"];
            });

            if (count($resultados) > 0) {
                $melhorOpcao = $resultados[0];
            }

            // salvar todas as cotações no banco
            $insert = $conn->prepare("
                INSERT INTO cotacoes (veiculo_id, seguradora, taxa_aplicada, valor_seguro)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($resultados as $r) {
                $insert->bind_param(
                    "isdd",
                    $veiculo_id,
                    $r["seguradora"],
                    $r["taxa"],
                    $r["valor_seguro"]
                );
                $insert->execute();
            }

            $insert->close();
            $mensagem = "Multicálculo realizado e salvo com sucesso!";
        } else {
            $mensagem = "Veículo não encontrado.";
        }

        $stmt->close();
    } else {
        $mensagem = "Selecione um veículo.";
    }
}

$historico = $conn->query("
    SELECT 
        cotacoes.id,
        cotacoes.seguradora,
        cotacoes.taxa_aplicada,
        cotacoes.valor_seguro,
        cotacoes.created_at,
        c.nome AS cliente_nome,
        v.marca,
        v.modelo,
        v.placa
    FROM cotacoes
    INNER JOIN veiculos v ON cotacoes.veiculo_id = v.id
    INNER JOIN clientes c ON v.cliente_id = c.id
    ORDER BY cotacoes.id DESC
");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multicálculo de Seguros - Cotação</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Cotação de Seguro</h1>
    <p class="subtitulo">Simulação automática com várias seguradoras</p>

    <div class="nav-links">
    <a href="dashboard.php" class="btn-acao editar">Dashboard</a>
    <a href="index.php" class="btn-acao editar">Voltar para Clientes</a>
    <a href="veiculo.php" class="btn-acao editar">Ir para Veículos</a>
    <a href="relatorio.php" class="btn-acao editar">Relatório PDF</a>
    <a href="logout.php" class="btn-acao excluir">Sair</a>
</div>
</div>

    <div class="card">
        <h2>Selecionar Veículo</h2>

        <?php if ($mensagem != ""): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="full">
                <label for="veiculo_id">Veículo</label>
                <select name="veiculo_id" id="veiculo_id" required>
                    <option value="">Selecione</option>
                    <?php if ($veiculos && $veiculos->num_rows > 0): ?>
                        <?php while($v = $veiculos->fetch_assoc()): ?>
                            <option value="<?php echo $v["id"]; ?>">
                                <?php echo htmlspecialchars($v["cliente_nome"] . " - " . $v["marca"] . " " . $v["modelo"] . " (" . $v["ano"] . ")"); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="full">
                <button type="submit">Calcular Multicálculo</button>
            </div>
        </form>
    </div>

    <?php if (!empty($resultados)): ?>
        <div class="card">
            <h2>Resultado da Cotação</h2>

            <div class="resultado-box">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($resultados[0]["cliente"]); ?></p>
                <p><strong>Veículo:</strong> <?php echo htmlspecialchars($resultados[0]["marca"] . " " . $resultados[0]["modelo"]); ?></p>
                <p><strong>Ano:</strong> <?php echo htmlspecialchars($resultados[0]["ano"]); ?></p>
                <p><strong>Placa:</strong> <?php echo htmlspecialchars($resultados[0]["placa"]); ?></p>
                <p><strong>Idade do veículo:</strong> <?php echo htmlspecialchars($resultados[0]["idade"]); ?> ano(s)</p>
                <p><strong>Valor FIPE:</strong> R$ <?php echo number_format($resultados[0]["valor_fipe"], 2, ",", "."); ?></p>
            </div>

            <div class="table-wrap" style="margin-top: 20px;">
                <table>
                    <thead>
                        <tr>
                            <th>Seguradora</th>
                            <th>Taxa Aplicada</th>
                            <th>Valor do Seguro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $r): ?>
                            <tr <?php echo ($melhorOpcao && $r["seguradora"] == $melhorOpcao["seguradora"]) ? 'class="linha-melhor"' : ''; ?>>
                                <td><?php echo htmlspecialchars($r["seguradora"]); ?></td>
                                <td><?php echo number_format($r["taxa"], 2, ",", "."); ?>%</td>
                                <td>R$ <?php echo number_format($r["valor_seguro"], 2, ",", "."); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($melhorOpcao): ?>
                <p class="valor-final" style="margin-top: 18px;">
                    Melhor opção: <?php echo htmlspecialchars($melhorOpcao["seguradora"]); ?> — 
                    R$ <?php echo number_format($melhorOpcao["valor_seguro"], 2, ",", "."); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Histórico de Cotações</h2>

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
                    <?php if ($historico && $historico->num_rows > 0): ?>
                        <?php while($h = $historico->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $h["id"]; ?></td>
                                <td><?php echo htmlspecialchars($h["cliente_nome"]); ?></td>
                                <td><?php echo htmlspecialchars($h["marca"] . " " . $h["modelo"]); ?></td>
                                <td><?php echo htmlspecialchars($h["placa"]); ?></td>
                                <td><?php echo htmlspecialchars($h["seguradora"]); ?></td>
                                <td><?php echo number_format($h["taxa_aplicada"], 2, ",", "."); ?>%</td>
                                <td>R$ <?php echo number_format($h["valor_seguro"], 2, ",", "."); ?></td>
                                <td><?php echo htmlspecialchars($h["created_at"]); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Nenhuma cotação registrada ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>