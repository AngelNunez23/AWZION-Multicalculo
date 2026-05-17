<?php
session_start();

if (isset($_SESSION["admin_logado"])) {
    header("Location: dashboard.php");
    exit();
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"] ?? "";
    $senha = $_POST["senha"] ?? "";

    $usuario_correto = "admin";
    $senha_correta = "123456";

    if ($usuario === $usuario_correto && $senha === $senha_correta) {
        $_SESSION["admin_logado"] = true;
        $_SESSION["admin_usuario"] = $usuario;
        header("Location: dashboard.php");
        exit();
    } else {
        $mensagem = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Multicálculo de Seguros</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container login-container">
    <div class="card login-card">
        <h1>Login Admin</h1>
        <p class="subtitulo">Acesse o sistema de multicálculo</p>

        <?php if ($mensagem != ""): ?>
            <div class="mensagem erro-login"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="full">
                <label for="usuario">Usuário</label>
                <input type="text" name="usuario" id="usuario" placeholder="Digite o usuário" required>
            </div>

            <div class="full">
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" placeholder="Digite a senha" required>
            </div>

            <div class="full">
                <button type="submit">Entrar</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
