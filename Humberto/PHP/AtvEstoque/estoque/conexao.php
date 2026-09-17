<?php

    $servidor = "127.0.0.1";
    $usuario = "root";
    $senha = "usbw";
    $banco = "estoque_dsm2";

    $conecta_db = mysqli_connect($servidor, $usuario, $senha, $banco);

    if (!$conecta_db) 
    {
        die("Erro ao conectar ao banco: " . mysqli_connect_error());
    }

    mysqli_set_charset($conecta_db, "utf8");

?>