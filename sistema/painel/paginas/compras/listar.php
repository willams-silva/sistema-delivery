<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'pagar';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'];
$dataFinal = @$_POST['dataFinal'];
$status = '%' . @$_POST['status'] . '%';


$total_pago = 0;
$total_a_pagar = 0;




$query = $pdo->query("SELECT * FROM $tabela where vencimento >= '$dataInicial' and vencimento <= '$dataFinal' and pago LIKE '$status' and produto != 0 and empresa = '$id_empresa' ORDER BY pago asc, vencimento asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
echo <<<'BCVIEW'
<style>
html body #listar #tabela tbody td:last-child a.btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #tabela tbody td:last-child a.btn.ocultar{display:none!important}
html body #listar #tabela tbody td:last-child a.btn.btn-info-light,html body #listar #tabela tbody td:last-child a.btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn.btn-danger-light{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child a.btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #tabela tbody td:last-child a.btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important;font-size:16px}
html body #listar #tabela tbody td:last-child a.btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #tabela tbody td:last-child a.btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-purchases-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-purchases-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-purchases-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-purchases-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-purchases-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-purchases-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-purchases-cards #tabela thead{display:none!important}
html body #listar.bc-purchases-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-purchases-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-purchases-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-purchases-cards #tabela tbody td:nth-child(1){font-weight:700;color:#63351e}
#listar.bc-purchases-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-purchases-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-purchases-cards #tabela tbody tr:hover,html body #listar.bc-purchases-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-purchases-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-purchases-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-purchases-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-purchases-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-purchases-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-purchases-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-purchases-cards #tabela tbody td:nth-child(2){font-size:20px;color:#63351e;font-weight:600}
html body #listar.bc-purchases-cards #tabela tbody td:last-child::before{content:none!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela thead th{background:#f4e9df!important;color:#63351e!important}
#listar.bc-purchases-cards #tabela tbody td:nth-child(2)::before{content:'Valor: '}
#listar.bc-purchases-cards #tabela tbody td:nth-child(3)::before{content:'Vencimento: '}
#listar.bc-purchases-cards #tabela tbody td:nth-child(4)::before{content:'Pagamento: '}
#listar.bc-purchases-cards #tabela tbody td:nth-child(5)::before{content:'Fornecedor: '}
#listar.bc-purchases-cards #tabela tbody td:nth-child(6)::before{content:'Arquivo: '}
html body #listar #tabela .bc-no-attachment{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #e5d4c6;border-radius:8px;background:#f8efe6;color:#795038;font-size:12px}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
</style>
<div id="bc-purchases-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-purchases-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.purchasesView=mode;root.classList.toggle('bc-purchases-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.purchasesView||'cards');
})();
</script>
BCVIEW;
if ($total_reg > 0) {

	echo <<<HTML
	<table class="table table-bordered text-nowrap border-bottom" id="tabela">
	<thead> 
	<tr> 
	<th>Produto</th>	
	<th>Valor</th> 	
	<th>Vencimento</th> 	
	<th>Data PGTO</th> 
	<th>Fornecedor</th>	
	<th>Arquivo</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$descricao = $res[$i]['descricao'];
		$tipo = $res[$i]['tipo'];
		$valor = $res[$i]['valor'];
		$data_lanc = $res[$i]['data_lanc'];
		$data_pgto = $res[$i]['data_pgto'];
		$data_venc = $res[$i]['vencimento'];
		$usuario_lanc = $res[$i]['usuario_lanc'];
		$usuario_baixa = $res[$i]['usuario_pgto'];
		$foto = $res[$i]['foto'];
		$pessoa = $res[$i]['pessoa'];
		$produto = $res[$i]['produto'];
		$pago = $res[$i]['pago'];
		$obs = $res[$i]['obs'];
		$forma_pgto = $res[$i]['forma_pgto'];

		$valorF = number_format($valor, 2, ',', '.');
		$data_lancF = implode('/', array_reverse(explode('-', $data_lanc)));
		$data_pgtoF = implode('/', array_reverse(explode('-', $data_pgto)));
		$data_vencF = implode('/', array_reverse(explode('-', $data_venc)));


		$query2 = $pdo->query("SELECT * FROM fornecedores where id = '$pessoa'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_pessoa = $res2[0]['nome'];
			$telefone_pessoa = $res2[0]['telefone'];
		} else {
			$nome_pessoa = 'Nenhum!';
			$telefone_pessoa = 'Nenhum';
		}


		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_usuario_pgto = $res2[0]['nome'];
		} else {
			$nome_usuario_pgto = 'Nenhum!';
		}



		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_usuario_lanc = $res2[0]['nome'];
		} else {
			$nome_usuario_lanc = 'Sem Referência!';
		}
		$query2 = $pdo->query("SELECT * FROM formas_pgto where id = '$forma_pgto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_pgto = $res2[0]['nome'];
		} else {
			$nome_pgto = 'Sem Registro';
		}


		if ($data_pgto == '0000-00-00') {
			$classe_alerta = 'text-danger';
			$data_pgtoF = 'Pendente';
			$visivel = '';
			$total_a_pagar += $valor;
		} else {
			$classe_alerta = 'text-verde';
			$visivel = 'ocultar';
			$total_pago += $valor;
		}


		//extensão do arquivo
		$ext = pathinfo($foto, PATHINFO_EXTENSION);
		if ($ext == 'pdf') {
			$tumb_arquivo = 'pdf.png';
		} else if ($ext == 'rar' || $ext == 'zip') {
			$tumb_arquivo = 'rar.png';
		} else {
			$tumb_arquivo = $foto;
		}

		if ($data_venc < $data_hoje and $pago != 'Sim') {
			$classe_debito = 'vermelho-escuro';
		} else {
			$classe_debito = '';
		}

		$bcArquivoHtml = in_array(strtolower(trim((string)$foto)), ['', 'sem-foto.jpg', 'sem-foto.png', 'sem-arquivo.png'], true) ? '<span class="bc-no-attachment"><i class="fa fa-file-o"></i> Sem anexo</span>' : '<a href="images/contas/'.htmlspecialchars($foto, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener"><i class="fa fa-file-o"></i> Ver arquivo</a>';
		echo <<<HTML
<tr class="{$classe_debito}">
<td><i class="fa fa-square {$classe_alerta}"></i> {$descricao}</td>
<td>R$ {$valorF}</td>
<td>{$data_vencF}</td>
<td>{$data_pgtoF}</td>

<td>{$nome_pessoa}</td>
<td class="text-center">{$bcArquivoHtml}</td>




<td>

<a class="btn btn-primary-light btn-sm" href="#" onclick="mostrar('{$descricao}', '{$valorF}', '{$data_lancF}', '{$data_vencF}',  '{$data_pgtoF}', '{$nome_usuario_lanc}', '{$nome_usuario_pgto}', '{$tumb_arquivo}', '{$nome_pessoa}', '{$foto}', '{$telefone_pessoa}', '{$obs}', '{$nome_pgto}', '{$pago}')" title="Ver Dados"><i class="fa fa-info-circle"></i></a>


<big><a href="#" class="btn btn-danger-light btn-sm {$visivel}" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>


<big><a href="#" class="btn btn-success-light btn-sm {$visivel}" onclick="baixarConta('{$id}')" title="Baixar Conta"><i class="bi bi-check-square-fill"></i></a></big>


		</td>
</tr>
HTML;

	}

	$total_pagoF = number_format($total_pago, 2, ',', '.');
	$total_a_pagarF = number_format($total_a_pagar, 2, ',', '.');

	echo <<<HTML
</tbody>
</table>
<div align="center" id="mensagem-excluir"></div>

<br>	
<div align="right">Total Pago: <span class="text-verde">R$ {$total_pagoF}</span> </div>
<div align="right">Total à Pagar: <span class="text-danger">R$ {$total_a_pagarF}</span> </div>

HTML;


} else {
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready(function () {
		if ($('#tabela').length) $('#tabela').DataTable({
			"ordering": false,
 "responsive": false,
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
	});
</script>


<script type="text/javascript">
	function editar(id, produto, pessoa, valor, data_venc, data_pgto, foto, obs, forma_pgto) {
		$('#id').val(id);
		$('#produto').val(produto).change();
		$('#pessoa').val(pessoa).change();
		$('#valor').val(valor);
		$('#data_venc').val(data_venc);
		$('#data_pgto').val(data_pgto);
		$('#obs').val(obs);
		$('#forma_pgto').val(dataforma_pgto_pgto);

		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');

		$('#target').attr('src', 'images/contas/' + foto);
	}

	function limparCampos() {
		$('#id').val('');
		$('#pessoa').val(0).change();
		$('#forma_pgto').val('').change();
		$('#valor').val('');
		$('#data_pgto').val('');
		$('#data_venc').val('<?= $data_hoje ?>');
		$('#foto').val('');
		$('#quantidade').val('1');
		$('#target').attr('src', 'images/contas/sem-foto.jpg');
	}
</script>

<script type="text/javascript">
	function mostrar(descricao, valor, data_lanc, data_venc, data_pgto, usuario_lanc, usuario_pgto, foto, pessoa, link, telefone, obs, forma_pgto, pago) {

		$('#nome_dados').text(descricao);
		$('#valor_dados').text(valor);
		$('#data_lanc_dados').text(data_lanc);
		$('#data_venc_dados').text(data_venc);
		$('#data_pgto_dados').text(data_pgto);
		$('#usuario_lanc_dados').text(usuario_lanc);
		$('#usuario_baixa_dados').text(usuario_pgto);
		$('#pessoa_dados').text(pessoa);
		$('#telefone_dados').text(telefone);
		$('#forma_pgto_dados').text(forma_pgto);
		$('#pago_dados').text(pago);
		$('#obs_dados').text(obs);

		$('#link_mostrar').attr('href', 'images/contas/' + link);
		$('#target_mostrar').attr('src', 'images/contas/' + foto);

		$('#modalDados').modal('show');
	}
</script>




<script type="text/javascript">
	function saida(id, nome, estoque) {

		$('#nome_saida').text(nome);
		$('#estoque_saida').val(estoque);
		$('#id_saida').val(id);

		$('#modalSaida').modal('show');
	}
</script>


<script type="text/javascript">
	function entrada(id, nome, estoque) {

		$('#nome_entrada').text(nome);
		$('#estoque_entrada').val(estoque);
		$('#id_entrada').val(id);

		$('#modalEntrada').modal('show');
	}
</script>