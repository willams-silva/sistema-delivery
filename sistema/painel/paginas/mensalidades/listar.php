<?php
@session_start();
$id_empresa = @$_SESSION['empresa'];

$tabela = 'receber_sas';
require_once("../../../conexao.php");
require_once("../../buscar_config.php");

//trazer as configurações
$query = $pdo->query("SELECT * from config where empresa = '0' or empresa is null");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {	
	$dados_pagamento_sas = $res[0]['dados_pagamento'];	
	$access_token_mp_sas = $res[0]['access_token'];	
	$public_key_mp_sas = $res[0]['public_key'];	
	$multa_atraso = $res[0]['multa_atraso'];
	$juros_atraso = $res[0]['juros_atraso'];	
}else{
	$dados_pagamento_sas = "";
	$access_token_mp_sas = "";
	$public_key_mp_sas = "";
	$multa_atraso = "";
	$juros_atraso = "";

}

$total_pago = 0;
$total_pendentes = 0;

$total_pagoF = 0;
$total_pendentesF = 0;

$subtotal_com_juros = 0;

$query = $pdo->query("SELECT * from $tabela where cliente = '$id_empresa' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
echo <<<'BCVIEW'
<style>
html body #listar #tabela tbody td:last-child :is(a,button).btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #tabela tbody td:last-child :is(a,button).btn.ocultar{display:none!important}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-info-light,html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-danger-light{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #tabela tbody td:last-child :is(a,button).btn i{color:inherit!important;font-size:16px}
html body #listar #tabela tbody td:last-child :is(a,button).btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #tabela tbody td:last-child :is(a,button).btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-subscription-cards #tabela tbody td:last-child :is(a,button).btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-subscription-cards #tabela tbody td:last-child :is(a,button).btn{transition:none!important}
#listar #bc-subscription-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-subscription-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-subscription-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-subscription-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-subscription-cards #tabela thead{display:none!important}
html body #listar.bc-subscription-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-subscription-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-subscription-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-subscription-cards #tabela tbody td:nth-child(1){font-weight:700;color:#63351e}
#listar.bc-subscription-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-subscription-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-subscription-cards #tabela tbody tr:hover,html body #listar.bc-subscription-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-subscription-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-subscription-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-subscription-cards #tabela tbody tr td:last-child :is(a,button).btn:hover,html body #listar.bc-subscription-cards #tabela tbody tr td:last-child :is(a,button).btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-subscription-cards #tabela tbody tr td:last-child :is(a,button).btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-subscription-cards #tabela tbody tr td:last-child :is(a,button).btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-subscription-cards #tabela tbody td:nth-child(2){font-size:20px;color:#63351e;font-weight:600}
html body #listar.bc-subscription-cards #tabela tbody td:last-child::before{content:none!important}
html body #listar #tabela tbody td:last-child :is(a,button).btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela thead th{background:#f4e9df!important;color:#63351e!important}
html body #listar #tabela .bc-no-attachment{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #e5d4c6;border-radius:8px;background:#f8efe6;color:#795038;font-size:12px}
html body #listar #tabela tbody td:last-child :is(a,button).btn i{color:inherit!important}
html body #listar #tabela tbody td:last-child :is(a,button).btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
#listar.bc-subscription-cards #tabela tbody td:nth-child(2)::before{content:'Valor: '}
#listar.bc-subscription-cards #tabela tbody td:nth-child(3)::before{content:'Subtotal: '}
#listar.bc-subscription-cards #tabela tbody td:nth-child(4)::before{content:'Vencimento: '}
#listar.bc-subscription-cards #tabela tbody td:nth-child(5)::before{content:'Pagamento: '}
html body #listar #tabela tbody td:last-child :is(a,button).btn-primary{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child :is(a,button).btn-danger{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child :is(a,button).btn-success{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar.bc-subscription-cards #tabela tbody tr,html body #listar.bc-subscription-cards #tabela tbody td{overflow:visible!important}
#listar.bc-subscription-cards #tabela tbody td:nth-child(6)::before{content:'Arquivo: '}
html body #listar #tabela tbody td:last-child .ocultar{display:none!important}
html body #listar #tabela .bc-subscription-rec{color:#795038!important}
html body #listar #tabela .bc-subscription-status{font-size:12px;font-weight:600;margin-left:6px}
</style>
<div id="bc-subscription-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-subscription-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.subscriptionView=mode;root.classList.toggle('bc-subscription-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.subscriptionView||'cards');
})();
</script>
BCVIEW;
if ($linhas > 0) {
	echo <<<HTML
	<table class="table table-bordered text-nowrap border-bottom" id="tabela">
	<thead> 
	<tr> 	
	<th>Descrição</th>	
	<th class="">Valor</th>
	<th>Subtotal</th>	
	<th>Vencimento</th>	
	<th>Pagamento</th>		
	<th class="text-center">Arquivo</th>	
	<th>Pagar / Recibo</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;


	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$descricao = $res[$i]['descricao'];
		$cliente = $res[$i]['cliente'];
		$valor = $res[$i]['valor'];
		$vencimento = $res[$i]['vencimento'];
		$data_pgto = $res[$i]['data_pgto'];
		$data_lanc = $res[$i]['data_lanc'];
		$forma_pgto = $res[$i]['forma_pgto'];
		$frequencia = $res[$i]['frequencia'];
		$obs = $res[$i]['obs'];
		$arquivo = $res[$i]['arquivo'];
		$referencia = $res[$i]['referencia'];
		$id_ref = $res[$i]['id_ref'];
		$multa = $res[$i]['multa'];
		$juros = $res[$i]['juros'];
		$desconto = $res[$i]['desconto'];
		$taxa = $res[$i]['taxa'];
		$subtotal = $res[$i]['subtotal'];
		$usuario_lanc = $res[$i]['usuario_lanc'];
		$usuario_pgto = $res[$i]['usuario_pgto'];
		$pago = $res[$i]['pago'];
		$quant_recorrencia = $res[$i]['quant_recorrencia'];
		$id_recorrencia = $res[$i]['id_recorrencia'];

		$vencimentoF = implode('/', array_reverse(@explode('-', $vencimento)));
		$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
		$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));



		$valorF = @number_format($valor, 2, ',', '.');
		$multaF = @number_format($multa, 2, ',', '.');
		$jurosF = @number_format($juros, 2, ',', '.');
		$descontoF = @number_format($desconto, 2, ',', '.');
		$taxaF = @number_format($taxa, 2, ',', '.');
		$subtotalF = @number_format($subtotal, 2, ',', '.');



		//extensão do arquivo
		$ext = pathinfo($arquivo, PATHINFO_EXTENSION);
		if ($ext == 'pdf' || $ext == 'PDF') {
			$tumb_arquivo = 'pdf.png';
		} else if ($ext == 'rar' || $ext == 'zip' || $ext == 'RAR' || $ext == 'ZIP') {
			$tumb_arquivo = 'rar.png';
		} else if ($ext == 'doc' || $ext == 'docx' || $ext == 'DOC' || $ext == 'DOCX') {
			$tumb_arquivo = 'word.png';
		} else if ($ext == 'xlsx' || $ext == 'xlsm' || $ext == 'xls') {
			$tumb_arquivo = 'excel.png';
		} else if ($ext == 'xml') {
			$tumb_arquivo = 'xml.png';
		} else {
			$tumb_arquivo = $arquivo;
		}



		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_usu_lanc = $res2[0]['nome'];
		} else {
			$nome_usu_lanc = 'Sem Usuário';
		}


		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_pgto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_usu_pgto = $res2[0]['nome'];
		} else {
			$nome_usu_pgto = 'Sem Usuário';
		}


		$query2 = $pdo->query("SELECT * FROM frequencias where dias = '$frequencia'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_frequencia = $res2[0]['frequencia'];
		} else {
			$nome_frequencia = 'Sem Registro';
		}

		$query2 = $pdo->query("SELECT * FROM formas_pgto where id = '$forma_pgto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_pgto = $res2[0]['nome'];
			$taxa_pgto = $res2[0]['taxa'];
		} else {
			$nome_pgto = 'Sem Registro';
			$taxa_pgto = 0;
		}


		$query2 = $pdo->query("SELECT * FROM empresas where id = '$cliente'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_cliente = $res2[0]['nome'];
		} else {
			$nome_cliente = 'Sem Registro';
		}


		if ($pago == 'Sim') {
			$classe_pago = 'verde';
			$classe_pago2 = '#ddffe1';
			$ocultar = 'ocultar';
			$ocultar_pendentes = '';
			$total_pago += $subtotal;
		} else if ($pago == 'Não' and @strtotime($vencimento) > @strtotime($data_atual)) {
			$classe_pago2 = '';
			$classe_pago = 'text-danger';
			$ocultar = '';
			$total_pago += $valor;
			$ocultar_pendentes = 'ocultar';
		} else if ($pago == 'Não' and @strtotime($vencimento) == @strtotime($data_hoje)) {
			$classe_pago2 = '#fff8dd';
			$classe_pago = 'text-danger';
			$ocultar = '';
			$total_pago += $valor;
			$ocultar_pendentes = 'ocultar';
		} else {
			$classe_pago = 'text-danger';
			$classe_pago2 = '#ffdddd';
			$ocultar = '';
			
			$ocultar_pendentes = 'ocultar';
		}


		if ($pago == 'Sim') {
			$ocultar_checkbox = 'ocultar';	
		}else{
			$ocultar_checkbox = '';
			$total_pendentes += $valor;
		}


		$valor_multa = 0;
		$valor_juros = 0;
		$classe_venc = '';
		$classe_venc = '';
		$data_hoje = date('Y-m-d');
		if (@strtotime($vencimento) < @strtotime($data_hoje) and $pago != 'Sim') {
			$classe_venc = 'text-danger';
			$valor_multa = $multa_atraso;


			//pegar a quantidade de dias que o pagamento está atrasado
			$dif = @strtotime($data_hoje) - @strtotime($vencimento);
			$dias_vencidos = floor($dif / (60 * 60 * 24));

			$valor_juros = ($valor * $juros_atraso / 100) * $dias_vencidos;
		}

		$valor_jurosF = @number_format($valor_juros, 2, ',', '.');


		$total_pendentesF = @number_format($total_pendentes, 2, ',', '.');
		$total_pagoF = @number_format($total_pago, 2, ',', '.');

		$taxa_conta = $taxa_pgto * $valor / 100;

		

		$taxa_contaF = @number_format($taxa_conta, 2, ',', '.');


		//PEGAR RESIDUOS DA CONTA
		$total_resid = 0;
		$valor_com_residuos = 0;
		$query2 = $pdo->query("SELECT * FROM receber_sas WHERE id_ref = '$id' and residuo = 'Sim'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {

			$descricao = '(Resíduo) - ' . $descricao;

			for ($i2 = 0; $i2 < @count($res2); $i2++) {
				foreach ($res2[$i2] as $key => $value) {
				}
				$id_res = $res2[$i2]['id'];
				$valor_resid = $res2[$i2]['valor'];
				$total_resid += $valor_resid - $res2[$i2]['desconto'];
			}


			$valor_com_residuos = $valor + $total_resid;
		}
		if ($valor_com_residuos > 0) {
			$vlr_antigo_conta = '(' . $valor_com_residuos . ')';
			$descricao_link = '';
			$descricao_texto = 'd-none';
		} else {
			$vlr_antigo_conta = '';
			$descricao_link = 'd-none';
			$descricao_texto = '';
		}


		if ($api_whatsapp != 'Não' and $cliente != "" and $cliente != "0") {
			$ocultar_cobranca = '';
		} else {
			$ocultar_cobranca = 'ocultar';
		}

		$classe_rec = '';
		if ($quant_recorrencia > 0) {
			$classe_rec = '<span class="bc-subscription-rec">(Recorrência Múltipla)</span>';
		}

		$ocultar_rec = 'ocultar';
		if (($quant_recorrencia > 0 or $id_recorrencia > 0) and $id_recorrencia != '') {
			if ($pago != 'Sim') {
				$ocultar_rec = '';
			}
		}

		$subtotal_com_juros = $valor + $valor_juros + $valor_multa;
		$subtotal_com_jurosF = @number_format($subtotal_com_juros, 2, ',', '.');

		if($subtotal_com_juros > $valor and $pago != 'Sim'){
			$texto_juros = '(Juros)';
		}else{
			$texto_juros = '';
		}


		$bcArquivoHtml = in_array(strtolower(trim((string)$arquivo)), ['', 'sem-foto.png', 'sem-foto.jpg', 'sem-arquivo.png'], true) ? '<span class="bc-no-attachment"><i class="fa fa-file-o"></i> Sem anexo</span>' : '<a href="images/contas/'.htmlspecialchars($arquivo, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener"><i class="fa fa-file-o"></i> Ver arquivo</a>';
        $bcStatus = $pago == 'Sim' ? 'Pago' : 'Pendente';
        echo <<<HTML
<tr>

<td><i class="fa fa-square {$classe_pago} mr-1"></i> {$descricao} {$classe_rec} <span class="bc-subscription-status {$classe_pago}">{$bcStatus}</span></td>
<td class="">R$ {$valorF} <small><a href="#" onclick="mostrarResiduos('{$id}')" class="text-danger" title="Ver Resíduos">{$vlr_antigo_conta}</a></small></td>	
<td>R$ {$subtotal_com_jurosF} <span style="color:red">{$texto_juros}</span></td>

<td class="esc {$classe_venc}">{$vencimentoF}</td>
<td>{$data_pgtoF}</td>

<td>{$bcArquivoHtml}</td>

<td>

	<span class="{$ocultar_pendentes} ">
				<form   method="POST" action="../sas/rel/recibo_conta_class.php" target="_blank" style="display:inline-block">
					<input type="hidden" name="id" value="{$id}">
					<button class="{$ocultar_pendentes} btn btn-danger btn-sm" title="PDF do Recibo Conta" ><i class="fa fa-file-pdf-o"></i></button>
					</form>
			</span>

			<span class="{$ocultar} ">	
					<a href="#" onclick="pagar('{$id}', '{$dados_pagamento_sas}')" class="{$ocultar} btn btn-success btn-sm" title="Efetuar Pagamento" ><i class="fa fa-money"></i></a>
			</span>
	
</td>
</tr>
HTML;
	}


	echo <<<HTML
</tbody>
</table>
<div align="center" id="mensagem-excluir"></div>
HTML;
} else {
	echo '<small>Nenhum Registro Encontrado!</small>';
}

?>



<script type="text/javascript">
	$(document).ready(function() {
		if ($('#tabela').length) $('#tabela').DataTable({
			"language": {
				//"url" : '//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-BR.json'
			},
			"ordering": false,
 "responsive": false,
			"stateSave": true
		});
	});
</script>

<script type="text/javascript">
	function editar(id, nome) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#id').val(id);
		$('#nome').val(nome);

		$('#modalForm').modal('show');
	}



	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');


		$('#ids').val('');
		$('#btn-deletar').hide();
	}
</script>


<script>
	function selecionar(id) {

		var ids = $('#ids').val();

		if ($('#seletor-' + id).is(":checked") == true) {
			var novo_id = ids + id + '-';
			$('#ids').val(novo_id);
		} else {
			var retirar = ids.replace(id + '-', '');
			$('#ids').val(retirar);
		}

		var ids_final = $('#ids').val();
		if (ids_final == "") {
			$('#btn-deletar').hide();
		} else {
			$('#btn-deletar').show();
		}
	}


		// ALERT EXCLUIR #######################################
		function deletarSel(id) {
		const swalWithBootstrapButtons = Swal.mixin({
			customClass: {
				confirmButton: "btn btn-success", // Adiciona margem à direita do botão "Sim, Excluir!"
				cancelButton: "btn btn-danger me-1"
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
				var ids = $('#ids').val();
				var id = ids.split("-");
				for (i = 0; i < id.length - 1; i++) {
					excluirMultiplos(id[i]);
				}
				setTimeout(() => {
					// Ação de exclusão aqui
					Swal.fire({
						title: 'Excluido com Sucesso!',
						text: 'Fecharei em 1 segundo.',
						icon: "success",
						timer: 1000
					})
					listar();
				}, 1000);
				limparCampos();
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



	function pagar(id, dados_pagamento){
		if(dados_pagamento == ""){
			window.open("../../pagamento/"+id);
		}else{
			mensagem_pagar(dados_pagamento)
		}
	}





// MODAL PAGAMENTO #######################################
function mensagem_pagar(dados_pagamento) {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            copyButton: "btn btn-info me-2"
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: "Dados para o Pagamento",
        text: dados_pagamento,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ok",
        cancelButtonText: '<i class="bi bi-clipboard"></i> Copiar Código',
        customClass: {
            confirmButton: "btn btn-success ms-2",
            cancelButton: "btn bc-subscription-copy me-2"
        },
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Usuário clicou em Ok
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Usuário clicou em Copiar Código
            copiarCodigo(dados_pagamento);
        }
    });
}

// FUNÇÃO PARA COPIAR CÓDIGO
function copiarCodigo(texto) {
    navigator.clipboard.writeText(texto).then(function() {
        // Mostra mensagem de sucesso
        Swal.fire({
            title: "Copiado!",
            text: "Código copiado para a área de transferência",
            icon: "success",
            timer: 1500,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }).catch(function(err) {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement("textarea");
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        Swal.fire({
            title: "Copiado!",
            text: "Código copiado para a área de transferência",
            icon: "success",
            timer: 1500,
            timerProgressBar: true,
            showConfirmButton: false
        });
    });
}
</script>