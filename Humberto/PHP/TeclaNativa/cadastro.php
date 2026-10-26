<?php
require_once 'conexao.php';
session_start();

$erro = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '' || $confirmar_senha === '') {
        $erro = 'Preencha todos os campos para criar sua conta.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um endereço de e-mail válido.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'Sua senha precisa ter pelo menos 6 caracteres.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($existe) {
                $erro = 'Este e-mail já está cadastrado. Entre na sua conta.';
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $nome, $email, $senha_hash);
                    if ($stmt->execute()) {
                        $stmt->close();
                        header('Location: login.php?cadastro=sucesso');
                        exit();
                    }
                    $stmt->close();
                    $erro = 'Não foi possível criar a conta. Tente novamente.';
                } else {
                    $erro = 'Não foi possível preparar o cadastro.';
                }
            }
        } else {
            $erro = 'Não foi possível verificar o e-mail informado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta — Tecla Nativa</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="cadlogin.css">
</head>
<body class="pagina-acesso pagina-cadastro">
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
            <p class="acesso-etiqueta">COMECE PELA SUA IDENTIDADE</p>
            <h1>Um novo visual<br>em cada tecla.</h1>
            <p>Monte seu cantinho do seu jeito. Escolha suas combinações favoritas e deixe sua mesa com a sua cara.</p>
            <div class="teclado-ilustracao" aria-hidden="true">
                <span>esc</span><span>1</span><span>2</span><span>3</span><span>4</span>
                <span>tab</span><span>Q</span><span>W</span><span>E</span><span>R</span>
                <span>shift</span><span>A</span><span>S</span><span>D</span><span>↵</span>
                <span class="tecla-larga">space</span><span>⌘</span><span>fn</span>
            </div>
            <div class="lateral-rodape"><span></span> Seu teclado também merece personalidade.</div>
        </div>
        <div class="forma-decorativa forma-um"></div>
        <div class="forma-decorativa forma-dois"></div>
    </section>

    <section class="acesso-conteudo">
        <div class="formulario-acesso">
            <div class="formulario-simbolo" aria-hidden="true">＋</div>
            <p class="formulario-kicker">FAÇA PARTE DA TECLA NATIVA</p>
            <h2>Crie sua conta.</h2>
            <p class="formulario-descricao">Só alguns dados para começar.</p>

            <?php if ($erro !== ''): ?>
                <div class="aviso-formulario" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php" class="form-acesso">
                <label for="nome">Como podemos te chamar?</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome" value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="name" maxlength="100" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="voce@exemplo.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>

                <label for="senha">Crie uma senha</label>
                <input type="password" id="senha" name="senha" placeholder="Pelo menos 6 caracteres" autocomplete="new-password" minlength="6" required>

                <label for="confirmar_senha">Confirme sua senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite a senha novamente" autocomplete="new-password" minlength="6" required>

                <button type="submit" class="botao-acesso">Criar minha conta <span aria-hidden="true">→</span></button>
            </form>

            <p class="troca-acesso">Já tem uma conta? <a href="login.php">Entre aqui</a></p>
            <p class="nota-acesso"><span aria-hidden="true">✳</span> Seus dados são usados para acessar sua conta na loja.</p>
        </div>
    </section>
</main>

<footer class="acesso-footer"><span>© <?= date('Y') ?> Tecla Nativa</span><span>KEYCAPS • PERSONALIDADE EM CADA TECLA</span></footer>
</body>
</html>
