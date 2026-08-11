<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Antecessor e Sucessor</title>
        <link rel="stylesheet" href="style.css">-
    </head>
    <body>
        <form method="POST">
            <label>Número:</label>
            <input type="text" name="numero"><br>
            <button type="submit" name="mostrar">Mostrar</button>
        </form>

        <!-- PHP time, brosky -->

        <?php
            if (isset($_POST['mostrar']))
            {
                // Variáveis
                
                $num = $_POST['numero'];

                // Processamento

                $ant = $num - 1;
                $suc = $num + 1;

                // Saída de dados

                echo "<h3>";
                echo "Número: " . $num;
                echo "<br>";
                echo "Antecessor: " . $ant;
                echo "<br>";
                echo "Sucessor: " . $suc;
                echo "</h3>";
            }
        ?>
    </body>
</html>
