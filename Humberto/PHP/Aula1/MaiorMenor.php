<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Maior e Menor</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <form method="POST">
            <label>Número 1:</label>
            <input type="text" name="num1"><br>
            <label>Número 2:</label>
            <input type="text" name="num2"><br>
            <label>Número 3:</label>
            <input type="text" name="num3"><br>
            <button type="submit" name="mostrar">Mostrar</button>
        </form>

        <!-- PHP time, brosky -->

        <?php
            if (isset($_POST['mostrar']))
            {
                // Variáveis
                
                $n1 = $_POST['num1'];
                $n2 = $_POST['num2'];
                $n3 = $_POST['num3'];

                // Processamento

                $menor = 99999;
                $maior = -99999;

                if ($n1 < $menor)
                {
                    $menor = $n1;
                }
                if ($n2 < $menor)
                {
                    $menor = $n2;
                }
                if ($n3 < $menor)
                {
                    $menor = $n3;
                }

                if ($n1 > $maior)
                {
                    $maior = $n1;
                }
                if ($n2 > $maior)
                {
                    $maior = $n2;
                }
                if ($n3 > $maior)
                {
                    $maior = $n3;
                }

                // Saída de dados

                echo "<h3>";
                echo "Maior: " . $maior;
                echo "<br>";
                echo "Menor: " . $menor;
                echo "</h3>";
            }
        ?>
    </body>
</html>
