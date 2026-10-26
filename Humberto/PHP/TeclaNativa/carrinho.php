<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = (int) $_SESSION['usuario_id'];
$mensagem = '';
$mensagem_tipo = 'erro';

function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function dinheiro($valor) {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/* Adicionar uma unidade de um produto ao carrinho. */
if (isset($_GET['add'])) {
    $produto_id = filter_input(INPUT_GET, 'add', FILTER_VALIDATE_INT);

    if ($produto_id && $produto_id > 0) {
        $stmt = $conn->prepare("SELECT id, estoque FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($produto && (int)$produto['estoque'] > 0) {
            $stmt = $conn->prepare("SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND produto_id = ?");
            $stmt->bind_param("ii", $usuario_id, $produto_id);
            $stmt->execute();
            $existente = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existente) {
                $nova_qtd = min((int)$existente['quantidade'] + 1, (int)$produto['estoque']);
                $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
                $stmt->bind_param("iii", $nova_qtd, $existente['id'], $usuario_id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, 1)");
                $stmt->bind_param("ii", $usuario_id, $produto_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: carrinho.php");
    exit();
}

/* Remover um item específico. */
if (isset($_GET['remover'])) {
    $carrinho_id = filter_input(INPUT_GET, 'remover', FILTER_VALIDATE_INT);

    if ($carrinho_id && $carrinho_id > 0) {
        $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $carrinho_id, $usuario_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: carrinho.php");
    exit();
}

/* Alterar quantidade. */
if (isset($_GET['acao'], $_GET['id']) && in_array($_GET['acao'], ['mais', 'menos'], true)) {
    $carrinho_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($carrinho_id && $carrinho_id > 0) {
        $stmt = $conn->prepare(
            "SELECT c.quantidade, c.produto_id, p.estoque
             FROM carrinho c
             JOIN produtos p ON p.id = c.produto_id
             WHERE c.id = ? AND c.usuario_id = ?"
        );
        $stmt->bind_param("ii", $carrinho_id, $usuario_id);
        $stmt->execute();
        $item_qtd = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($item_qtd) {
            $nova_qtd = (int)$item_qtd['quantidade'];
            $nova_qtd += ($_GET['acao'] === 'mais') ? 1 : -1;

            if ($nova_qtd <= 0) {
                $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
                $stmt->bind_param("ii", $carrinho_id, $usuario_id);
            } else {
                $nova_qtd = min($nova_qtd, (int)$item_qtd['estoque']);
                $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
                $stmt->bind_param("iii", $nova_qtd, $carrinho_id, $usuario_id);
            }
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: carrinho.php");
    exit();
}

/* Buscar os itens atuais e calcular o subtotal no servidor. */
function buscarItensCarrinho($conn, $usuario_id) {
    $sql = "SELECT c.id AS carrinho_id, c.quantidade, p.id AS produto_id,
                   p.nome, p.preco, p.imagem, p.estoque
            FROM carrinho c
            JOIN produtos p ON p.id = c.produto_id
            WHERE c.usuario_id = ?
            ORDER BY c.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $itens = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['quantidade'] = (int)$linha['quantidade'];
        $linha['preco'] = (float)$linha['preco'];
        $linha['estoque'] = (int)$linha['estoque'];
        $linha['subtotal'] = $linha['preco'] * $linha['quantidade'];
        $itens[] = $linha;
    }

    $stmt->close();
    return $itens;
}

/* Finalização: valida pagamento, frete e estoque e recalcula valores no servidor. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
    $pagamento = $_POST['pagamento'] ?? '';
    $frete = $_POST['frete'] ?? '';

    $pagamentos_validos = ['Pix', 'Cartão de Crédito', 'Boleto'];
    $fretes_validos = ['Sedex', 'PAC', 'Retirada'];

    if (!in_array($pagamento, $pagamentos_validos, true) || !in_array($frete, $fretes_validos, true)) {
        $mensagem = 'Selecione uma forma de pagamento e uma opção de entrega válidas.';
    } else {
        $itens_checkout = buscarItensCarrinho($conn, $usuario_id);
        $subtotal_checkout = 0.0;
        $estoque_ok = true;
        $produto_sem_estoque = '';

        if (empty($itens_checkout)) {
            $mensagem = 'Seu carrinho está vazio.';
        } else {
            foreach ($itens_checkout as $item) {
                $subtotal_checkout += $item['subtotal'];

                if ($item['quantidade'] > $item['estoque']) {
                    $estoque_ok = false;
                    $produto_sem_estoque = $item['nome'];
                    break;
                }
            }

            if (!$estoque_ok) {
                $mensagem = 'Estoque insuficiente para: ' . $produto_sem_estoque . '. Atualize a quantidade antes de continuar.';
            } else {
                $valor_frete = ($frete === 'Sedex') ? 15.00 : (($frete === 'PAC') ? 8.00 : 0.00);

                $conn->begin_transaction();

                try {
                    foreach ($itens_checkout as $item) {
                        $stmt = $conn->prepare(
                            "UPDATE produtos SET estoque = estoque - ?
                             WHERE id = ? AND estoque >= ?"
                        );
                        $stmt->bind_param("iii", $item['quantidade'], $item['produto_id'], $item['quantidade']);
                        $stmt->execute();
                        $atualizou = $stmt->affected_rows;
                        $stmt->close();

                        if ($atualizou !== 1) {
                            throw new Exception('O estoque mudou durante a compra. Revise seu carrinho.');
                        }
                    }

                    $stmt = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
                    $stmt->bind_param("i", $usuario_id);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit();

                    $_SESSION['checkout_pagamento'] = $pagamento;
                    $_SESSION['checkout_frete'] = $frete;
                    $_SESSION['checkout_total'] = $subtotal_checkout + $valor_frete;
                    $_SESSION['checkout_valor_frete'] = $valor_frete;
                    $_SESSION['checkout_subtotal'] = $subtotal_checkout;

                    header("Location: finalizar.php");
                    exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    $mensagem = $e->getMessage() ?: 'Não foi possível finalizar a compra. Tente novamente.';
                }
            }
        }
    }
}

$itens = buscarItensCarrinho($conn, $usuario_id);
$total = 0.0;

foreach ($itens as $item) {
    $total += $item['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua sacola — Tecla Nativa</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="carrinho.css">
</head>
<body>
<header>
    <h1>Tecla <span>Nativa</span></h1>
    <nav>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li><a href="catalogo.php">Catálogo</a></li>
            <li><a href="carrinho.php" aria-current="page">Carrinho</a></li>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li class="dropdown" onclick="toggleMenu(event)">
                    <a href="#" aria-haspopup="true" aria-expanded="false">Minha Conta ▾</a>
                    <div class="dropdown-content" id="menuConta">
                        <p class="ola">Olá, <?= h(explode(' ', $_SESSION['usuario_nome'] ?? 'Cliente')[0]) ?>!</p>
                        <a href="logout.php">Sair da conta</a>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="pagina-carrinho">
    <div class="carrinho-cabecalho">
        <div>
            <p class="sobretitulo">TECLA NATIVA / SEU CARRINHO</p>
            <h2>Suas próximas teclas.</h2>
            <p class="texto-intro">Confira sua seleção antes de dar uma nova cara ao seu teclado.</p>
        </div>
        <div class="icone-teclado" aria-hidden="true">⌨</div>
    </div>

    <?php if ($mensagem !== ''): ?>
        <div class="mensagem-carrinho" role="alert"><?= h($mensagem) ?></div>
    <?php endif; ?>

    <?php if (count($itens) > 0): ?>
        <form method="POST" action="carrinho.php" class="checkout-form" id="checkoutForm">
            <input type="hidden" name="finalizar_compra" value="1">

            <div class="carrinho-layout">
                <section class="lista-compras" aria-labelledby="titulo-itens">
                    <div class="secao-titulo">
                        <h3 id="titulo-itens">Sua seleção</h3>
                        <span><?= count($itens) ?> <?= count($itens) === 1 ? 'produto' : 'produtos' ?></span>
                    </div>

                    <div class="lista-produtos">
                        <?php foreach ($itens as $item): ?>
                            <article class="produto-carrinho">
                                <div class="produto-foto">
                                    <img
                                        src="img/<?= h($item['imagem']) ?>"
                                        alt="<?= h($item['nome']) ?>"
                                        loading="lazy"
                                    >
                                </div>

                                <div class="produto-detalhes">
                                    <p class="produto-categoria">KEYCAPS / TECLA NATIVA</p>
                                    <h4><?= h($item['nome']) ?></h4>
                                    <p class="produto-preco-unitario"><?= dinheiro($item['preco']) ?> <span>por conjunto</span></p>

                                    <div class="produto-acoes">
                                        <div class="qtd-container" aria-label="Quantidade">
                                            <a href="carrinho.php?acao=menos&amp;id=<?= (int)$item['carrinho_id'] ?>" class="btn-qtd" aria-label="Diminuir quantidade">−</a>
                                            <span class="qtd-texto"><?= (int)$item['quantidade'] ?></span>
                                            <a href="carrinho.php?acao=mais&amp;id=<?= (int)$item['carrinho_id'] ?>" class="btn-qtd" aria-label="Aumentar quantidade" <?= $item['quantidade'] >= $item['estoque'] ? 'aria-disabled="true" onclick="return false;"' : '' ?>>+</a>
                                        </div>
                                        <a href="carrinho.php?remover=<?= (int)$item['carrinho_id'] ?>" class="btn-remover" onclick="return confirm('Remover este produto da sacola?')">Remover</a>
                                    </div>
                                </div>

                                <div class="produto-subtotal">
                                    <span>Subtotal</span>
                                    <strong><?= dinheiro($item['subtotal']) ?></strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <a href="catalogo.php" class="link-voltar">← Continuar explorando a coleção</a>

                    <section class="opcoes-checkout" aria-labelledby="titulo-pagamento">
                        <div class="secao-titulo">
                            <h3 id="titulo-pagamento">Como você quer pagar?</h3>
                            <span>Escolha uma opção</span>
                        </div>

                        <div class="opcoes-grid">
                            <label class="opcao-selecao">
                                <input type="radio" name="pagamento" value="Pix" required>
                                <span class="opcao-marca"></span>
                                <span class="opcao-icone" aria-hidden="true">◇</span>
                                <span class="opcao-texto"><strong>Pix</strong><small>Aprovação imediata</small></span>
                            </label>
                            <label class="opcao-selecao">
                                <input type="radio" name="pagamento" value="Cartão de Crédito">
                                <span class="opcao-marca"></span>
                                <span class="opcao-icone" aria-hidden="true">▤</span>
                                <span class="opcao-texto"><strong>Cartão de crédito</strong><small>Até 3x sem juros</small></span>
                            </label>
                            <label class="opcao-selecao">
                                <input type="radio" name="pagamento" value="Boleto">
                                <span class="opcao-marca"></span>
                                <span class="opcao-icone" aria-hidden="true">▥</span>
                                <span class="opcao-texto"><strong>Boleto</strong><small>Pagamento bancário</small></span>
                            </label>
                        </div>
                    </section>

                    <section class="opcoes-checkout entrega" aria-labelledby="titulo-entrega">
                        <div class="secao-titulo">
                            <h3 id="titulo-entrega">Como receber?</h3>
                            <span>Entrega demonstrativa</span>
                        </div>

                        <div class="opcoes-grid">
                            <label class="opcao-selecao">
                                <input type="radio" name="frete" value="Sedex" required>
                                <span class="opcao-marca"></span>
                                <span class="opcao-texto"><strong>Sedex</strong><small>3 a 5 dias úteis</small></span>
                                <span class="opcao-valor">R$ 15,00</span>
                            </label>
                            <label class="opcao-selecao">
                                <input type="radio" name="frete" value="PAC">
                                <span class="opcao-marca"></span>
                                <span class="opcao-texto"><strong>PAC</strong><small>7 a 10 dias úteis</small></span>
                                <span class="opcao-valor">R$ 8,00</span>
                            </label>
                            <label class="opcao-selecao">
                                <input type="radio" name="frete" value="Retirada">
                                <span class="opcao-marca"></span>
                                <span class="opcao-texto"><strong>Retirada</strong><small>Buscar na loja</small></span>
                                <span class="opcao-valor gratis">Grátis</span>
                            </label>
                        </div>
                    </section>
                </section>

                <aside class="resumo-compra" aria-labelledby="titulo-resumo">
                    <div class="resumo-topo">
                        <p class="sobretitulo">QUASE SEU</p>
                        <h3 id="titulo-resumo">Resumo da compra</h3>
                    </div>

                    <div class="resumo-linha">
                        <span>Produtos</span>
                        <strong id="subtotal-produtos" data-valor="<?= h(number_format($total, 2, '.', '')) ?>"><?= dinheiro($total) ?></strong>
                    </div>
                    <div class="resumo-linha">
                        <span>Entrega</span>
                        <strong id="valor-frete">A escolher</strong>
                    </div>

                    <div class="resumo-total">
                        <span>Total estimado</span>
                        <strong id="total-geral"><?= dinheiro($total) ?></strong>
                        <small>O total inclui a opção de entrega selecionada.</small>
                    </div>

                    <button type="submit" name="finalizar_compra" value="1" class="btn-finalizar">
                        Confirmar pedido <span aria-hidden="true">→</span>
                    </button>

                    <p class="resumo-nota"><span aria-hidden="true">✓</span> Você poderá revisar os detalhes do pedido na próxima etapa.</p>
                    <div class="resumo-decoracao" aria-hidden="true">
                        <span>ESC</span><span>⌘</span><span>↵</span>
                    </div>
                </aside>
            </div>
        </form>
    <?php else: ?>
        <section class="carrinho-vazio">
            <div class="vazio-icone" aria-hidden="true">⌨</div>
            <p class="sobretitulo">SUA PRÓXIMA IDEIA COMEÇA AQUI</p>
            <h3>A sacola está esperando.</h3>
            <p>Explore a coleção e encontre as teclas que combinam com sua mesa.</p>
            <a href="catalogo.php" class="btn-finalizar">Explorar a coleção <span aria-hidden="true">→</span></a>
        </section>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Tecla Nativa. Pequenos detalhes, uma identidade inteira.</p>
</footer>

<script>
    const subtotalProdutos = Number(document.getElementById('subtotal-produtos')?.dataset.valor || 0);
    const valorFreteEl = document.getElementById('valor-frete');
    const totalGeralEl = document.getElementById('total-geral');

    function formatarReais(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    document.querySelectorAll('input[name="frete"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            const valores = { Sedex: 15, PAC: 8, Retirada: 0 };
            const frete = valores[radio.value] ?? 0;
            if (valorFreteEl) valorFreteEl.textContent = frete === 0 ? 'Grátis' : formatarReais(frete);
            if (totalGeralEl) totalGeralEl.textContent = formatarReais(subtotalProdutos + frete);
        });
    });

    function toggleMenu(event) {
        const link = event.target.closest('a');
        if (link && link.getAttribute('href') === '#') event.preventDefault();

        const menu = document.getElementById('menuConta');
        if (menu) menu.classList.toggle('show');
    }

    window.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-content.show').forEach(menu => menu.classList.remove('show'));
        }
    });
</script>
</body>
</html>
