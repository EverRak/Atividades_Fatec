<?php

    include "conexao.php";

    header("Content-Type: application/json; charset=utf-8");

    $sql = mysqli_query(
        $conecta_db,
        "SELECT * FROM Categorias"
    );

    $produtos = array();

    while ($produto = mysqli_fetch_assoc($sql))
    {
        $produtos[] = $produto;
    }

    echo json_encode($produtos);

?>