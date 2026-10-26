<?php
require_once 'conexao.php';
session_start();

$erro = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha seu e-mail e sua senha.';
    } else {
        $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            $stmt->close();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int)$usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                header('Location: index.php');
                exit();
            }
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — Tecla Nativa</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="cadlogin.css">
</head>
<body class="pagina-acesso">
<header class="acesso-header">
    <a class="marca" href="index.php" aria-label="Tecla Nativa - página inicial">
        <span class="marca-simbolo" aria-hidden="true">tn</span>
        <span class="marca-texto">tecla<span>nativa</span><small>PERSONALIDADE EM CADA TECLA</small></span>
    </a>
    <a class="voltar-loja" href="catalogo.php">Explorar a loja <span aria-hidden="true">↗</span></a>
</header>

<main class="acesso-layout">
    <section class="acesso-lateral">
        <div class="lateral-conteudo">
            <p class="acesso-etiqueta">UM TECLADO, MIL POSSIBILIDADES</p>
            <h1>Seu espaço.<br>Suas regras.</h1>
            <p>Encontre detalhes que transformam o comum em algo só seu. A próxima combinação começa por aqui.</p>
            <div class="teclado-ilustracao" aria-hidden="true">
                <span>esc</span><span>1</span><span>2</span><span>3</span><span>4</span>
                <span>tab</span><span>Q</span><span>W</span><span>E</span><span>R</span>
                <span>shift</span><span>A</span><span>S</span><span>D</span><span>↵</span>
                <span class="tecla-larga">space</span><span>⌘</span><span>fn</span>
            </div>
            <div class="lateral-rodape"><span></span> Pequenos detalhes. Uma identidade inteira.</div>
        </div>
        <div class="forma-decorativa forma-um"></div>
        <div class="forma-decorativa forma-dois"></div>
    </section>

    <section class="acesso-conteudo">
        <div class="formulario-acesso">
            <div class="formulario-simbolo" aria-hidden="true">↗</div>
            <p class="formulario-kicker">BEM-VINDO DE VOLTA</p>
            <h2>Entre na sua conta.</h2>
            <p class="formulario-descricao">Continue de onde sua próxima ideia começa.</p>

            <?php if ($erro !== ''): ?>
                <div class="aviso-formulario" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="form-acesso">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="voce@exemplo.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>

                <div class="label-com-link">
                    <label for="senha">Senha</label>
                </div>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>

                <button type="submit" class="botao-acesso">Entrar na conta <span aria-hidden="true">→</span></button>
            </form>

            <p class="troca-acesso">Ainda não tem conta? <a href="cadastro.php">Crie a sua aqui</a></p>
            <p class="nota-acesso"><span aria-hidden="true">✳</span> Feito para quem presta atenção nos detalhes.</p>
        </div>
    </section>
</main>

<footer class="acesso-footer"><span>© <?= date('Y') ?> Tecla Nativa</span><span>KEYCAPS • PERSONALIDADE EM CADA TECLA</span></footer>
</body>
</html>
