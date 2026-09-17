<?php

include "conexao.php";

header("Content-Type: application/json; charset=utf-8");

$term = isset($_POST["txt_Termo"]) ? $_POST["txt_Termo"] : "";

$sql = mysqli_query(
    $conecta_db,
    "SELECT 
        Produtos.*,
        Categorias.nome AS categoria
    FROM Produtos
    INNER JOIN Categorias 
        ON Produtos.idCategoria = Categorias.idCategoria
    WHERE Produtos.descricao LIKE '%$term%' OR
        Categorias.nome LIKE '%$term%'"
    );

$produtos = array();

while ($produto = mysqli_fetch_assoc($sql))
{
    $produtos[] = $produto;
}

echo json_encode($produtos);

?>