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
$query = $pdo->query("SELECT * from $tabela where sessao = '$id_usuario' and pedido = '0' and (mesa = '$id_ab_mesa' OR ( '$id_ab_mesa' = '' AND (mesa = '0' OR mesa IS NULL))) and empresa = '$id_empresa' order by id asc");
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


$query = $pdo->query("SELECT * from $tabela where sessao = '$id_usuario' and pedido = '0' and (mesa = '$id_ab_mesa' OR ( '$id_ab_mesa' = '' AND (mesa = '0' OR mesa IS NULL))) and empresa = '$id_empresa' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
echo '<div class="pdv-comanda-lista">';
if ($linhas === 0) {
    echo '<div class="pdv-comanda-vazia" role="status">
        <span class="pdv-comanda-vazia-icone"><i class="fa fa-shopping-basket" aria-hidden="true"></i></span>
        <strong>Comanda vazia</strong>
        <span>Selecione um produto ou utilize o leitor de código de barras para começar.</span>
    </div>';
}
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



	echo '<div class="row g-0 pdv-comanda-item">'; // cada produto tem espaco proprio e nao fica sob a barra de rolagem
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
  echo '<div class="col-9 col-md-9 position-relative pdv-comanda-detalhes">';
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
        <input type="text" inputmode="decimal" class="pdv-quantidade-input" value="'.$quantidadeF.'" id="input_quant_'.$id.'" aria-label="Quantidade do produto. Enter confirma e Escape cancela" onkeydown="return teclaQuantidadePdv(event, '.$id.', '.$quantidadeF.')" onblur="alterarQuantidade('.$id.', '.$quantidadeF.')">
        <button type="button" class="pdv-quantidade-btn" title="Aumentar quantidade" aria-label="Aumentar quantidade" onclick="aumentar('.$id.', '.$quantidadeF.')"><i class="fa fa-plus" aria-hidden="true"></i></button>
      </div>
      <span class="pdv-quantidade-unidade">'.$sigla_unidade.'</span>
      <span class="pdv-total-item">Total Item: <strong>R$ '.$totalF.'</strong></span>
      ';

      // Ícone remover no canto — mesmo visual, mas sem depender de margem negativa
      echo '<div class="pdv-remover-area">
        <button type="button" class="pdv-remover-item" title="Remover item" aria-label="Remover item" data-nome="'.htmlspecialchars($nome_produto, ENT_QUOTES, 'UTF-8').'" onclick="excluirAlert('.$id.', this)"><i class="fa fa-trash" aria-hidden="true"></i></button>
      </div>';

    echo '</div>';
  echo '</div>';
echo '</div>';

	
	}
}
echo '</div>';

$total_finalF = @number_format($total_final, 2, ',', '.');
$quantidadeTotalItens = 0;
foreach ($res as $itemResumo) {
    $quantidadeTotalItens += (float)($itemResumo['quantidade'] ?? 0);
}
$quantidadeTotalFormatada = rtrim(rtrim(number_format($quantidadeTotalItens, 3, ',', '.'), '0'), ',');
$nomeMesaSeguro = htmlspecialchars((string)$nome_mesa, ENT_QUOTES, 'UTF-8');
echo '<div class="pdv-resumo-comanda" role="status" aria-label="Resumo da comanda">';
if ($nomeMesaSeguro !== '') {
    echo '<div class="pdv-resumo-mesa">'.$nomeMesaSeguro.'</div>';
}
echo '<div class="pdv-resumo-linha">';
echo '<div class="pdv-resumo-itens"><span>Produtos na comanda</span><strong>'.(int)$linhas.' <small>tipos</small> · '.$quantidadeTotalFormatada.' <small>unidades</small></strong></div>';
echo '<div class="pdv-resumo-total"><span>Total a pagar</span><strong>R$ '.$total_finalF.'</strong></div>';
echo '</div>';
// Campos de conferencia atualizados no navegador quando o operador informa dinheiro.
echo '<div class="pdv-resumo-recebido" id="pdv-resumo-recebido" style="display:none"><span>Valor recebido</span><strong id="pdv-resumo-recebido-valor">R$ 0,00</strong></div>';
echo '<div class="pdv-resumo-troco" id="pdv-resumo-troco" style="display:none"><span>Troco a devolver</span><strong id="pdv-resumo-troco-valor">R$ 0,00</strong></div>';
echo '<div class="pdv-resumo-faltante" id="pdv-resumo-faltante" style="display:none" role="status"><span><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Falta receber</span><strong id="pdv-resumo-faltante-valor">R$ 0,00</strong></div>';
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
function excluirAlert(id, botao) {
    if (window.pdvExclusaoEmAndamento && window.pdvExclusaoEmAndamento[id]) return;
    var nome = botao ? String($(botao).attr('data-nome') || 'este produto') : 'este produto';
    var nomeSeguro = $('<div>').text(nome).html();
    Swal.fire({
        title: 'Remover da comanda?',
        html: '<div class="pdv-exclusao-conteudo"><div class="pdv-exclusao-produto">' + nomeSeguro + '</div><p>O item e seus adicionais serão retirados da comanda.</p></div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-trash" aria-hidden="true"></i> Remover item',
        cancelButtonText: 'Manter item',
        confirmButtonColor: '#512c24',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
        customClass: {popup: 'pdv-exclusao-popup'}
    }).then(function(resultado) {
        if (!resultado.isConfirmed) return;
        window.pdvExclusaoEmAndamento = window.pdvExclusaoEmAndamento || {};
        if (window.pdvExclusaoEmAndamento[id]) return;
        window.pdvExclusaoEmAndamento[id] = true;
        if (botao) $(botao).prop('disabled', true);
        $.ajax({
            url: 'paginas/' + pag + '/excluir-item.php',
            method: 'POST',
            data: {id: id},
            dataType: 'html'
        }).done(function(mensagem) {
            if (String(mensagem).trim() === 'Excluído com Sucesso') {
                listarVendas();
                if (typeof buscar === 'function') buscar();
                Swal.fire({title: 'Item removido', icon: 'success', timer: 1100, showConfirmButton: false, customClass: {popup: 'pdv-exclusao-popup'}});
            } else {
                Swal.fire({title: 'Não foi possível remover', text: String(mensagem), icon: 'error', confirmButtonColor: '#512c24'});
            }
        }).fail(function(xhr, status) {
            // Erros HTTP do servidor nao sao necessariamente falhas de conexao.
            // Mostrar a mensagem real, sem expor HTML ou detalhes internos do PHP.
            var codigo = Number(xhr && xhr.status) || 0;
            var mensagemServidor = String(xhr && xhr.responseText || '').replace(/<[^>]*>/g, ' ').replace(/\\s+/g, ' ').trim();
            var mensagem = '';
            var titulo = 'Não foi possível remover';
            if (codigo === 403) {
                mensagem = 'Sua sessão pode ter expirado. Entre novamente no sistema antes de excluir.';
            } else if (codigo === 404) {
                mensagem = 'Este item não está mais na comanda atual. A lista será atualizada.';
            } else if (codigo === 400) {
                mensagem = 'O item selecionado é inválido. Atualize a comanda e tente novamente.';
            } else if (codigo === 0 || status === 'timeout') {
                titulo = 'Falha na conexão';
                mensagem = 'Não foi possível confirmar a exclusão. Confira a comanda atualizada antes de tentar novamente.';
            } else {
                mensagem = 'O servidor não conseguiu excluir o item. Confira a comanda e tente novamente.';
            }
            Swal.fire({
                title: titulo,
                text: mensagem,
                icon: 'error',
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#512c24',
                customClass: {popup: 'pdv-exclusao-popup'}
            });
            listarVendas();
        }).always(function() {
            delete window.pdvExclusaoEmAndamento[id];
            if (botao) $(botao).prop('disabled', false);
        });
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
        grupo.addClass('pdv-quantidade-carregando').attr('aria-busy', 'true');
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
            grupo.removeClass('pdv-quantidade-carregando').removeAttr('aria-busy');
            grupo.find('button, input').prop('disabled', false);
        });
    }
    // Se houver digitacao pendente, aplicar o +/- sobre o numero visivel,
    // sem disparar duas requisicoes concorrentes (blur + clique).
    function ajustarQuantidadePdv(id, quantidadeOriginal, passo) {
        if (window.pdvQuantidadeEmAndamento[id]) return;
        var campo = $('#input_quant_' + id);
        var digitado = String(campo.val() || '').trim();
        var original = Number(String(quantidadeOriginal).replace(',', '.'));
        var pendente = digitado !== String(quantidadeOriginal);
        var atual = Number(digitado.replace(',', '.'));
        if (pendente && (!/^[0-9]+(?:[.,][0-9]{1,3})?$/.test(digitado) || !Number.isFinite(atual) || atual <= 0)) {
            // Evitar salvar o texto invalido no blur ao clicar no botao.
            campo.val(String(quantidadeOriginal));
            mostrarErroQuantidadePdv('Informe uma quantidade válida antes de usar os botões de aumentar ou diminuir.');
            return;
        }
        if (!pendente) {
            requisitarQuantidadePdv(id, passo > 0 ? 'aumentar' : 'diminuir', quantidadeOriginal);
            return;
        }
        var proxima = Math.round((atual + passo) * 1000) / 1000;
        if (proxima <= 0) {
            campo.val(String(quantidadeOriginal));
            mostrarErroQuantidadePdv('A quantidade deve ser maior que zero. Para remover o item, utilize a opção de excluir.');
            return;
        }
        // O onblur deve ver o valor original e nao iniciar outro salvamento.
        campo.val(String(quantidadeOriginal));
        requisitarQuantidadePdv(id, 'alterar_quantidade', String(proxima), quantidadeOriginal);
    }
    function diminuir(id, quantidade) {
        ajustarQuantidadePdv(id, quantidade, -1);
    }
    function aumentar(id, quantidade) {
        ajustarQuantidadePdv(id, quantidade, 1);
    }
    function teclaQuantidadePdv(event, id, anterior) {
        if (event.key === 'Enter') {
            event.preventDefault();
            event.target.blur(); // onblur valida e grava uma unica vez
            return false;
        }
        if (event.key === 'Escape' || event.key === 'Esc' || event.keyCode === 27) {
            event.preventDefault();
            event.stopPropagation();
            // Marcar cancelamento ANTES do blur: o blur nao pode enviar AJAX.
            event.target.dataset.pdvCancelarEdicao = '1';
            event.target.value = String(anterior);
            event.target.blur();
            return false;
        }
        return true;
    }
    function alterarQuantidade(id, quant_enviada) {
        var campo = $('#input_quant_' + id);
        if (campo[0] && campo[0].dataset.pdvCancelarEdicao === '1') {
            delete campo[0].dataset.pdvCancelarEdicao;
            campo.val(String(quant_enviada));
            return;
        }
        if (window.pdvQuantidadeEmAndamento && window.pdvQuantidadeEmAndamento[id]) return;
        var quantidade = String(campo.val() || '').trim();
        if (quantidade === String(quant_enviada)) return;
        if (!/^[0-9]+(?:[.,][0-9]{1,3})?$/.test(quantidade) || Number(quantidade.replace(',', '.')) <= 0) {
            mostrarErroQuantidadePdv('Informe uma quantidade maior que zero, com até três casas decimais. Para remover o item, utilize a opção de excluir.');
            campo.val(quant_enviada);
            return;
        }
        requisitarQuantidadePdv(id, 'alterar_quantidade', quantidade, quant_enviada);
    }


function verGrade(id){
    $('#modalVerGrade').modal('show');
    listarItensGrades(id);
}
	
</script>