<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$pagamento = isset($_SESSION['checkout_pagamento']) ? $_SESSION['checkout_pagamento'] : "Não informado";
$frete = isset($_SESSION['checkout_frete']) ? $_SESSION['checkout_frete'] : "Não informado";
$total_final = isset($_SESSION['checkout_total']) ? $_SESSION['checkout_total'] : 0;
$valor_frete = isset($_SESSION['checkout_valor_frete']) ? $_SESSION['checkout_valor_frete'] : 0;

unset($_SESSION['checkout_pagamento']);
unset($_SESSION['checkout_frete']);
unset($_SESSION['checkout_total']);
unset($_SESSION['checkout_valor_frete']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado — Tecla Nativa</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="menu.css">
</head>
<body>

    <header>
        <h1>Tecla <span>Nativa</span></h1>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="carrinho.php">Carrinho</a></li>
                
                <?php if(isset($_SESSION['usuario_id'])): ?>
                    <li class="dropdown" onclick="toggleMenu(event)">
                        <a href="#">Minha Conta ▾</a>
                        <div class="dropdown-content" id="menuConta">
                            <p class="ola">Olá, <?php echo explode(' ', $_SESSION['usuario_nome'])[0]; ?>!</p>
                            <!-- <a href="logout.php" style="border-top: 1px solid #333;">Sair</a> -->
                            <a href="logout.php" style="border-top: 1px solid #333;" onclick="window.location.href='logout.php'; return false;">Sair</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="sucesso-container">
            <h1>Pedido confirmado!</h1>

            <p>
                Obrigado por escolher a Tecla Nativa.
                Seu pedido foi registrado no nosso ambiente de demonstração.
            </p>
            
            <div class="resumo-box">
                <p><span>Forma de Pagamento:</span> <strong><?php echo $pagamento; ?></strong></p>
                <p><span>Opção de Frete:</span> <strong><?php echo $frete; ?></strong></p>
                <p><span>Valor do Frete:</span> <strong>R$ <?php echo number_format($valor_frete, 2, ',', '.'); ?></strong></p>
                
                <p class="total-final"><span>Total Pago:</span> <span style="color: #4d7958;">R$ <?php echo number_format($total_final, 2, ',', '.'); ?></span></p>
                
                <p style="font-size: 12px; color: #666; margin-top: 15px; display: block;">(Este é um ambiente de simulação acadêmica)</p>
            </div>
            
            <a href="catalogo.php" class="btn">Voltar ao Catálogo</a>
        </div>
    </main>

    <!-- <footer>
        <p>&copy; <?php echo date('Y'); ?> Gothic Store - Todos os direitos reservados. 🦇</p>
    </footer> -->

</body>
</html>