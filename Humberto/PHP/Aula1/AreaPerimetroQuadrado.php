<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Área e Perímetro do Quadrado</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <form method="POST">
            <label>Lado:</label>
            <input type="text" name="lado"><br>
            <button type="submit" name="calcular">Calcular</button>
        </form>

        <!-- PHP time, brosky -->

        <?php
            if (isset($_POST['calcular']))
            {
                // Variáveis
                
                $lad = $_POST['lado'];

                // Processamento

                $area = pow($lad, 2);
                $perim = $lad * 4; 

                // Saída de dados

                echo "<h3>";
                echo "Lado: " . $lad;
                echo "<br>";
                echo "Perímetro: " . $perim;
                echo "<br>";
                echo "Área: " . $area;
                echo "</h3>";
            }
        ?>
    </body>
</html>