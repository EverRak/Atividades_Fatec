<?php

include "conexao.php";

$descricao = $_POST["txt_desc"];
$categoria = $_POST["slc_ctgr"];
$valorCompra = $_POST["txt_vlrC"];
$valorVenda = $_POST["txt_vlrV"];
$qtdEstoque = $_POST["txt_qtdE"];

$sql = "INSERT INTO Produtos 
        (descricao, idCategoria, valorCompra, valorVenda, qtEstoque)
        VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conecta_db, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "siddi",
    $descricao,
    $categoria,
    $valorCompra,
    $valorVenda,
    $qtdEstoque
);

mysqli_stmt_execute($stmt);

echo "<h2>";
echo "Produto cadastrado com sucesso!";
echo "</h2>";

?>