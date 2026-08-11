<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Folha de Pagamento</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <form method="POST">
            <label>Número de salários mínimos recebidos (ref: R$1621,00):</label>
            <br>
            <input type="text" name="numero">
            <br>
            <button type="submit" name="calcular">Calcular</button>
        </form>

        <!-- PHP time, brosky -->

        <?php
            if (isset($_POST['calcular']))
            {
                // Variáveis
                
                $bruto = 1621 * $_POST['numero'];
                $INSS;

                // Processamento

                if ($bruto < 1621)
                {
                    $INSS = 0;
                }
                elseif ($bruto <= 2430)
                {
                    $INSS = 0.075 * $bruto;
                }
                elseif ($bruto <= 3240)
                {
                    $INSS = 0.09 * $bruto;
                }
                elseif ($bruto <= 4860)
                {
                    $INSS = 0.12 * $bruto;
                }
                else
                {
                    $INSS = 0.14 * $bruto;
                }

                // R$ 1621,00 até R$ 2430,00 - Alíquota de 7,5% 
                // R$ 2430,01 até R$ 3240,00 - Alíquota de 9% 
                // R$ 3240,01 até R$ 4860,00 - Alíquota de 12% 
                // R$ 4860,01 em diante - Alíquota de 14% 

                // Saída de dados

                echo "<h3>";
                echo "Salário bruto: " . $bruto;
                echo "<br>";
                echo "Desconto INSS: " . $INSS;
                echo "<br>";
                echo "Salário líquido: " . ($bruto - $INSS);
                echo "</h3>";
            }
        ?>
    </body>
</html>
