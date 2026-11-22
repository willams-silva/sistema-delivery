<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'carrinho';


$id_ab_mesa = $_SESSION['id_ab_mesa'];

$nome_mesa = $_SESSION['nome_mesa'];

$id_usuario = $_SESSION['id'];
$desconto = @$_POST['desconto'];
$troco = @$_POST['troco'];
$tipo_desconto = @$_POST['tipo_desconto'];
$frete = @$_POST['frete'];
$frete = @str_replace(',', '.', $frete);

if($frete == ""){
	$frete = 0;
}

if($desconto == ""){
	$desconto = 0;
}

$total_troco = 0;
$total_trocoF = 0;

$total_v = 0;

//buscar o total da venda
$query = $pdo->query("SELECT * from $tabela where sessao = '$id_usuario' and pedido = '0' and mesa = '$id_ab_mesa' and empresa = '$id_empresa' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){	
		$total_das_vendas = $res[$i]['total_item'];
		$total_v += $total_das_vendas;
	}
}

if($tipo_desconto == '%'){
	if($desconto > 0 and $total_v > 0){
		$total_final = -($total_v * $desconto / 100);
	}else{
		$total_final = 0;
	}
	
}else{
	$total_final = -$desconto;
}

$total_final = $total_final + $frete;


$query = $pdo->query("SELECT * from $tabela where sessao = '$id_usuario' and pedido = '0' and mesa = '$id_ab_mesa' and empresa = '$id_empresa' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
echo '<div style="overflow:auto; max-height:200px; width:100%; scrollbar-width: thin;">';
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
	$id = $res[$i]['id'];
	$produto = $res[$i]['produto'];
	$valor = $res[$i]['valor_unitario'];
	$quantidade = $res[$i]['quantidade'];
	$total = $res[$i]['total_item'];
	$nome_produto_tab = $res[$i]['nome_produto'];
    $sabores = $res[$i]['sabores'];

   


	$total_final += $total;
	$total_finalF = @number_format($total_final, 2, ',', '.');
	$valorF = @number_format($valor, 2, ',', '.');
	$totalF = @number_format($total, 2, ',', '.');
	
	if($troco > 0){
		$total_troco = $troco - $total_final;
		$total_trocoF = @number_format($total_troco, 2, ',', '.');
	}
	

	$query2 = $pdo->query("SELECT * from produtos where id = '$produto'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if(@count($res2) > 0){
        $nome_produto = $res2[0]['nome'];
        $foto_produto = $res2[0]['foto'];
        $unidade = $res2[0]['unidade'];
    }else{
        $nome_produto = $nome_produto_tab;
        $foto_produto = "";
        $unidade = "";
    }


     if($sabores > 0){           
            $nome_produto = $nome_produto_tab;            
        }
	
   

	$query3 = $pdo->query("SELECT * FROM unidade_medida where id = '$unidade'");
	$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res3) > 0){
		$nome_unidade = $res3[0]['nome'];
	}else{
		$nome_unidade = 'Sem Unidade';
	}

	$ocultar_quantidades = '';
	$sigla_unidade = '';
	if($nome_unidade == 'Quilogramas' or $nome_unidade == 'Quilo' or $nome_unidade == 'Quilograma' or $nome_unidade == 'KG'){
		$sigla_unidade = ' (KG)';
		$ocultar_quantidades = 'ocultar';
	}

	if($nome_unidade == 'Metros' or $nome_unidade == 'Metro' or $nome_unidade == 'M' or $nome_unidade == 'm'){
		$sigla_unidade = ' (m)';
		$ocultar_quantidades = 'ocultar';
	}

	if($nome_unidade == 'Litro' or $nome_unidade == 'Litros' or $nome_unidade == 'L'){
		$sigla_unidade = ' (L)';
		$ocultar_quantidades = 'ocultar';
	}

	// O catalogo do PDV vende tapiocas por unidade; manter o mesmo criterio na comanda.
	// Nao modifica a unidade cadastrada nem afeta produtos de outras categorias.
	$stmtCategoriaPdv = $pdo->prepare("SELECT c.nome FROM produtos p INNER JOIN categorias c ON c.id = p.categoria AND c.empresa = p.empresa WHERE p.id = ? AND p.empresa = ? LIMIT 1");
	$stmtCategoriaPdv->execute([(int)$produto, (int)$id_empresa]);
	$nomeCategoriaPdv = (string)$stmtCategoriaPdv->fetchColumn();
	if (mb_stripos($nomeCategoriaPdv, 'tapioca') !== false) {
		$sigla_unidade = ' (Un.)';
		$ocultar_quantidades = '';
	}

	//tratamento separa string
	$qt = explode(".", $quantidade);
	if($qt[1] > 0){
		$quantidadeF = $quantidade;		
	}else{
		$quantidadeF = $qt[0];
	}

	$nome_produtoF = mb_strimwidth($nome_produto, 0, 40, "..."); 




    $query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id' and tabela = 'Variação' order by id asc limit 1");
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        $id_do_item = @$res2[0]['id_item'];

        $query2 = $pdo->query("SELECT * FROM itens_grade where id = '$id_do_item'");
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        if(@$res2[0]['texto'] != ""){
            $sigla_grade = '<small> ('.@$res2[0]['texto'].')</small>';
        }else{
            $sigla_grade = '';
        }


        $ocultar_temp = 'ocultar';
        $query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id' order by id asc limit 1");
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        if(@count($res2) > 0){
            $ocultar_temp = '';
        }



	echo '<div class="row g-0">'; // g-0 remove o “gutter” padrão sem precisar de margens negativas
  echo '<div class="col-3 col-md-3 pe-1">';
    // Evitar imagem quebrada quando o arquivo nao foi publicado na hospedagem.
    $fotoArquivo = basename((string)$foto_produto);
    $fotoCaminho = __DIR__ . '/../../images/produtos/' . $fotoArquivo;
    if ($fotoArquivo !== '' && is_file($fotoCaminho)) {
        echo '<img src="images/produtos/'.rawurlencode($fotoArquivo).'" width="45" height="45" style="object-fit:contain" alt="Foto do produto" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\'">';
        echo '<span title="Imagem indisponível" style="display:none;width:45px;height:45px;align-items:center;justify-content:center;background:#f3f4f6;color:#777;border-radius:5px"><i class="fa fa-image"></i></span>';
    } else {
        echo '<span title="Produto sem imagem cadastrada ou arquivo ausente" style="display:inline-flex;width:45px;height:45px;align-items:center;justify-content:center;background:#f3f4f6;color:#777;border-radius:5px"><i class="fa fa-image"></i></span>';
    }
  echo '</div>';

  // Torna este bloco "relative" para o X absoluto ancorar corretamente
  echo '<div class="col-9 col-md-9 position-relative">'; // antes tinha margin-left:-15px
    echo '<span class="text-dark" style="font-size:13px;">'; // mantém 13px (sem margin-left negativo)
      echo '<span class="'.$ocultar_quantidades.'">'.$quantidadeF.'</span> '.$nome_produtoF.' '.$sigla_grade;
    echo '</span>
    <span class="'.$ocultar_temp.'"><a title="Ver Grade" onclick="verGrade('.$id.')"><i class="fa fa-file text-info"></i></a></span>
    <br>';

    // Mostrar personalizações efetivamente vinculadas a este item da comanda.
    // A consulta inclui empresa e carrinho para não misturar extras de outros pedidos.
    $stmtExtras = $pdo->prepare("SELECT a.nome, a.tipo, t.quantidade, t.valor_item
        FROM temp t INNER JOIN adicionais_cat a ON a.id = t.id_item AND a.empresa = t.empresa
        WHERE t.carrinho = ? AND t.empresa = ? AND t.tabela = 'adicionais'
        ORDER BY a.tipo, a.nome");
    $stmtExtras->execute([(int)$id, (int)$id_empresa]);
    $extrasDaComanda = $stmtExtras->fetchAll(PDO::FETCH_ASSOC);
    if ($extrasDaComanda) {
        echo '<div class="small mt-1 pdv-extras-comanda" style="line-height:1.5;color:#512c24 !important;font-weight:600">';
        foreach ($extrasDaComanda as $extra) {
            $qtdExtra = (int)$extra['quantidade'];
            $nomeExtra = htmlspecialchars((string)$extra['nome'], ENT_QUOTES, 'UTF-8');
            $rotulo = $extra['tipo'] === 'opcional' ? 'Opcional' : ($extra['tipo'] === 'proteina' ? 'Proteína extra' : 'Adicional');
            $precoExtra = (float)$extra['valor_item'] * $qtdExtra;
            echo '<div>+ ' . $qtdExtra . '× ' . $nomeExtra . ' (' . $rotulo . ')';
            if ($precoExtra > 0) echo ' — R$ ' . number_format($precoExtra, 2, ',', '.');
            echo '</div>';
        }
        echo '</div>';
    }

    if (mb_stripos($nomeCategoriaPdv, 'tapioca') !== false) {
        echo '<button type="button" class="btn btn-sm btn-outline-secondary mt-1 mb-1" style="font-size:11px;color:#512c24!important;border-color:#b87542!important" onclick="return window.editarAdicionaisPdv ? window.editarAdicionaisPdv('.(int)$id.') : (alert(\'A edição de adicionais não foi carregada. Atualize o PDV com Ctrl+Shift+R.\'), false)"><i class="fa fa-pencil"></i> Editar adicionais</button>';
    }

    echo '<div class="pdv-comanda-controles text-dark">';
      echo '<div class="pdv-quantidade-grupo '.$ocultar_quantidades.'" role="group" aria-label="Quantidade do produto">
        <button type="button" class="pdv-quantidade-btn" title="Diminuir quantidade" aria-label="Diminuir quantidade" onclick="diminuir('.$id.', '.$quantidadeF.')"><i class="fa fa-minus" aria-hidden="true"></i></button>
        <input type="text" inputmode="decimal" class="pdv-quantidade-input" value="'.$quantidadeF.'" id="input_quant_'.$id.'" aria-label="Quantidade do produto" onblur="alterarQuantidade('.$id.', '.$quantidadeF.')">
        <button type="button" class="pdv-quantidade-btn" title="Aumentar quantidade" aria-label="Aumentar quantidade" onclick="aumentar('.$id.', '.$quantidadeF.')"><i class="fa fa-plus" aria-hidden="true"></i></button>
      </div>
      <span class="pdv-quantidade-unidade">'.$sigla_unidade.'</span>
      <span class="pdv-total-item">Total Item: <strong>R$ '.$totalF.'</strong></span>
      ';

      // Ícone remover no canto — mesmo visual, mas sem depender de margem negativa
      echo '<div class="dropdown head-dpdn2" style="position:absolute; top:0; right:10px;">
        <a title="Remover Item" href="#" onclick="excluirAlert('.$id.')"><big><i class="fa fa-times" style="color:#7d1107"></i></big></a>
      </div>';

    echo '</div>';
  echo '</div>';
echo '</div>';

	
	}
}
echo '</div>';

$total_finalF = @number_format($total_final, 2, ',', '.');
echo '<div align="right" style="margin-top:10px; font-size:14px; border-top:1px solid #8f8f8f;" >';
echo '<br>';
echo '<span style="margin-right:10px;">'.$nome_mesa.'</span>';
echo '<span style="margin-right:40px;">Itens: <b>('.$linhas.')</b></span>';
echo '<span>Total: </span>';
echo '<span style="font-weight:bold"> R$ ';
echo $total_finalF;
echo '</span>';
if($troco > 0){
echo '<br><span>Troco: </span>';
echo '<span style="font-weight:bold"> R$ ';
echo $total_trocoF;
echo '</span>';
}
echo '</div>';


?>

<script type="text/javascript">
	var itens = "<?=$linhas?>";
	// O valor recebido em dinheiro é informado pelo operador.
	$('#subtotal_venda').val('<?=$total_final?>');
    if (typeof atualizarPagamentoPdv === 'function') atualizarPagamentoPdv();
	if(itens > 0){
		$("#btn_limpar").show();
		$("#btn_venda").show();
	}else{
		$("#btn_limpar").hide();
		$("#btn_venda").hide();
	}
	



// ALERT EXCLUIR #######################################
function excluirAlert(id) {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success", // Adiciona margem à direita do botão "Sim, Excluir!"
            cancelButton: "btn btn-danger me-1",
            container: 'swal-whatsapp-container'
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: "Deseja Excluir?",
        text: "Você não conseguirá recuperá-lo novamente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, Excluir!",
        cancelButtonText: "Não, Cancelar!",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Realiza a requisição AJAX para excluir o item
            $.ajax({
                url: 'paginas/' + pag + "/excluir-item.php",
                method: 'POST',
                data: { id },
                dataType: "html",
                success: function (mensagem) {
                    if (mensagem.trim() == "Excluído com Sucesso") {
                        // Exibe mensagem de sucesso após a exclusão
                        swalWithBootstrapButtons.fire({
                            title: mensagem,
                            text: 'Fecharei em 1 segundo.',
                            icon: "success",
                            timer: 1000,
                            timerProgressBar: true,
                            confirmButtonText: 'OK',
                            customClass: {
                             container: 'swal-whatsapp-container'
                             }
                        });                        
                        limparCampos();
                        listarVendas();
                        buscar();
                    } else {
                        // Exibe mensagem de erro se a requisição falhar
                        swalWithBootstrapButtons.fire({
                            title: "Opss!",
                            text: mensagem,
                            icon: "error",
                            confirmButtonText: 'OK',
                            customClass: {
                             container: 'swal-whatsapp-container'
                             }
                        });
                    }
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire({
                title: "Cancelado",
                text: "Fecharei em 1 segundo.",
                icon: "error",
                timer: 1000,
                timerProgressBar: true,
            });
        }
    });
}


    function mostrarErroQuantidadePdv(mensagem) {
        var texto = String(mensagem || 'Não foi possível atualizar a quantidade.').replace(/<[^>]*>/g, ' ').trim();
        // O servidor devolve dados em texto. Escapar cada campo antes de montar o HTML.
        var escapar = function(valor) {
            return $('<div>').text(String(valor || '')).html();
        };
        var partes = texto.match(/^Estoque insuficiente para (.+?)\. Disponível: ([0-9.,]+); solicitado: ([0-9.,]+)\./i);
        var conteudo = partes
            ? '<div class="pdv-estoque-alerta">' +
                '<div class="pdv-estoque-produto">' + escapar(partes[1]) + '</div>' +
                '<div class="pdv-estoque-valores">' +
                  '<div class="pdv-estoque-valor"><span>Disponível</span><strong>' + escapar(partes[2]) + '</strong></div>' +
                  '<div class="pdv-estoque-valor"><span>Solicitado</span><strong>' + escapar(partes[3]) + '</strong></div>' +
                '</div>' +
                '<p>Ajuste a quantidade para continuar. Os demais itens da comanda foram preservados.</p>' +
              '</div>'
            : '<p class="pdv-estoque-erro">' + escapar(texto) + '</p>';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Estoque insuficiente',
                html: conteudo,
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#512c24',
                customClass: {popup: 'pdv-estoque-popup'}
            });
        } else {
            alert(texto);
        }
    }


    // Uma requisicao por item por vez. Evita duplo clique e corrida com onblur.
    window.pdvQuantidadeEmAndamento = window.pdvQuantidadeEmAndamento || {};
    function requisitarQuantidadePdv(id, operacao, quantidade, anterior) {
        if (window.pdvQuantidadeEmAndamento[id]) return;
        window.pdvQuantidadeEmAndamento[id] = true;
        var grupo = $('#input_quant_' + id).closest('.pdv-quantidade-grupo');
        grupo.find('button, input').prop('disabled', true);
        $.ajax({
            url: 'paginas/' + pag + '/' + operacao + '.php',
            method: 'POST',
            data: operacao === 'alterar_quantidade' ? {id:id, quantidade:quantidade} : {id:id, quantidade:quantidade},
            dataType: 'html'
        }).done(function(mensagem) {
            if (String(mensagem).trim() === 'Excluído com Sucesso') {
                listarVendas();
            } else {
                mostrarErroQuantidadePdv(mensagem);
                if (anterior !== undefined) $('#input_quant_' + id).val(anterior);
                listarVendas();
            }
        }).fail(function(xhr) {
            mostrarErroQuantidadePdv(xhr.responseText);
            if (anterior !== undefined) $('#input_quant_' + id).val(anterior);
            listarVendas();
        }).always(function() {
            delete window.pdvQuantidadeEmAndamento[id];
            grupo.find('button, input').prop('disabled', false);
        });
    }
    function diminuir(id, quantidade) {
        requisitarQuantidadePdv(id, 'diminuir', quantidade);
    }
    function aumentar(id, quantidade) {
        requisitarQuantidadePdv(id, 'aumentar', quantidade);
    }
    function alterarQuantidade(id, quant_enviada) {
        if (window.pdvQuantidadeEmAndamento && window.pdvQuantidadeEmAndamento[id]) return;
        var campo = $('#input_quant_' + id);
        var quantidade = String(campo.val() || '').trim();
        if (quantidade === String(quant_enviada)) return;
        requisitarQuantidadePdv(id, 'alterar_quantidade', quantidade, quant_enviada);
    }


function verGrade(id){
    $('#modalVerGrade').modal('show');
    listarItensGrades(id);
}
	
</script>