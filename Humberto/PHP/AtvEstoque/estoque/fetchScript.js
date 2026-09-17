async function atualizarProdutos(term = "")
{
    const formData = new FormData();

    formData.append("txt_Termo", term);

    const response = await fetch("listar.php", 
    {
        method: "POST",
        body: formData
    });

    const products = await response.json();

    const tabela = document.querySelector("#tbInv tbody");

    tabela.innerHTML = "";

    products.forEach(product => {
        tabela.innerHTML += `
            <tr>
                <td>${product.descricao}</td>
                <td>${product.categoria}</td>
                <td>R$ ${product.valorCompra}</td>
                <td>R$ ${product.valorVenda}</td>
                <td>${product.qtEstoque}</td>
                <td><button style="background: #f00; border-radius: 100%; width: 30px; height: 30px; text-align: center; padding: 10px; border: none; color: #fff">X</button></td>
            </tr>
        `;
    });
}

async function atualizarCategorias()
{
    const response = await fetch("categorias.php");
    const products = await response.json();

    const dd = document.querySelector("#dd_catgr");

    dd.innerHTML = "";

    for (let i = 0; i < products.length; i++) 
    {
        dd.innerHTML += `
            <option value="${products[i].idCategoria}">
                ${products[i].nome}
            </option>
        `;
    }
}

function pesquisar()
{
    const term = document.querySelector("#txt_Termo").value;

    atualizarProdutos(term);
}


document
    .querySelector("#btn_Pesquisar")
    .addEventListener("click", pesquisar);

atualizarCategorias();
atualizarProdutos();