<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Média Aluno Fatec</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <form method="POST">
            <label>Nota 1:</label>
            <input type="text" name="nota1"><br>
            <label>Nota 2:</label>
            <input type="text" name="nota2"><br>
            <label>Nota Trabalho:</label> 
            <input type="text" name="notaT"><br><br>
            <button type="submit" name="calcular">Calcular</button>
        </form>

        <!-- PHP time, brosky -->

        <?php
            if (isset($_POST['calcular']))
            {
                // Variáveis
                
                $n1 = $_POST['nota1'];
                $n2 = $_POST['nota2'];
                $nt = $_POST['notaT'];

                // Processamento
                
                $media = ($n1*0.35) + ($n2*0.35) + ($nt*0.3);

                if ($media < 6)
                {
                    echo "<p style='color: red;'>Aluno reprovado!</p>";
                    // $situacao = "reprovado";
                }
                else
                {
                    echo "<p style='color: green;'>Aluno aprovado!</p>";
                    // $situacao = "aprovado";
                }

                // Saída de dados

                echo "<h3>";
                echo "Média do Aluno: " . round($media, 1);
                // echo "<br>";
                // echo "Situação do Aluno: " . $situacao;
                echo "</h3>";
            }
        ?>
    </body>
</html>
