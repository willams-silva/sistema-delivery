<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$id_usuario = @$_SESSION['id'];
$tabela = 'pagar';



$data_hoje = date('Y-m-d');
$data_atual = date('Y-m-d');
$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual . "-" . $mes_atual . "-01";
$data_inicio_ano = $ano_atual . "-01-01";

$data_ontem = date('Y-m-d', @strtotime("-1 days", @strtotime($data_atual)));
$data_amanha = date('Y-m-d', @strtotime("+1 days", @strtotime($data_atual)));


if ($mes_atual == '04' || $mes_atual == '06' || $mes_atual == '09' || $mes_atual == '11') {
	$data_final_mes = $ano_atual . '-' . $mes_atual . '-30';
} else if ($mes_atual == '02') {
	$bissexto = date('L', @mktime(0, 0, 0, 1, 1, $ano_atual));
	if ($bissexto == 1) {
		$data_final_mes = $ano_atual . '-' . $mes_atual . '-29';
	} else {
		$data_final_mes = $ano_atual . '-' . $mes_atual . '-28';
	}
} else {
	$data_final_mes = $ano_atual . '-' . $mes_atual . '-31';
}


$total_pago = 0;
$total_pendentes = 0;

$total_pagoF = 0;
$total_pendentesF = 0;

$dataInicial = @$_POST['p2'];
$dataFinal = @$_POST['p3'];
$filtro = @$_POST['p1'];
$tipo_data = @$_POST['p4'];

if ($tipo_data == "") {
	$tipo_data = 'vencimento';
}

if ($dataInicial == "") {
	$dataInicial = $data_inicio_mes;
}

if ($dataFinal == "") {
	$dataFinal = $data_final_mes;
}


$total_valor = 0;
$total_valorF = 0;
$total_total = 0;
$total_totalF = 0;
$total_vencidas = 0;
$total_vencidasF = 0;
$total_hoje = 0;
$total_hojeF = 0;
$total_amanha = 0;
$total_amanhaF = 0;
$total_recebidas = 0;
$total_recebidasF = 0;



//PEGAR O TOTAL DAS CONTAS A PAGAR PENDENTES
$query = $pdo->query("SELECT * from $tabela where pago = 'Não' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$total_valor += $res[$i]['valor'];
		$total_valorF = @number_format($total_valor, 2, ',', '.');
	}
}

//PEGAR O TOTAL DAS CONTAS A PAGAR

$query = $pdo->query("SELECT * from $tabela where empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$total_total += $res[$i]['valor'];
		$total_totalF = @number_format($total_total, 2, ',', '.');
	}
}


//PEGAR O TOTAL DAS CONTAS A PAGAR PEDENTES
$query = $pdo->query("SELECT * from $tabela where vencimento < curDate() and pago = 'Não' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$total_vencidas += $res[$i]['valor'];
		$total_vencidasF = @number_format($total_vencidas, 2, ',', '.');
	}
}

//PEGAR O TOTAL DAS CONTAS A PAGAR QUE VENCE HOJE
$query = $pdo->query("SELECT * from $tabela where vencimento = curDate() and pago = 'Não' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_am = @count($res);
if ($total_am > 0) {
	for ($i = 0; $i < $total_am; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$total_hoje += $res[$i]['valor'];
		$total_hojeF = @number_format($total_hoje, 2, ',', '.');
	}
}


//PEGAR O TOTAL DAS CONTAS A PAGAR RECEBIDAS
$query = $pdo->query("SELECT * from $tabela where pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_pg = @count($res);
if ($total_pg > 0) {
	for ($i = 0; $i < $total_pg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$total_recebidas += $res[$i]['valor'];
		$total_recebidasF = @number_format($total_recebidas, 2, ',', '.');
	}
}


$data_hoje = date('Y-m-d');
$data_amanha = date('Y/m/d', @strtotime("+1 days", @strtotime($data_hoje)));


//PEGAR O TOTAL DAS CONTAS A PAGAR QUE VENCE AMANHÃ
$query = $pdo->query("SELECT * from $tabela where vencimento = '$data_amanha' and pago = 'Não' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_am = @count($res);
if ($total_am > 0) {
	for ($i = 0; $i < $total_am; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$total_amanha += $res[$i]['valor'];
		$total_amanhaF = @number_format($total_amanha, 2, ',', '.');
	}
}


if ($filtro == 'Vencidas') {
	$query = $pdo->query("SELECT * from $tabela where $tipo_data < curDate() and pago = 'Não' and empresa = '$id_empresa' order by id desc ");
} else if ($filtro == 'Proximos7') {
    // Mantém a listagem sincronizada com a antecedência definida por empresa.
    $diasAlerta = 7;
    try {
        $configAlerta = $pdo->prepare("SELECT dias_antecedencia FROM alertas_vencimento_pagar WHERE empresa = :empresa");
        $configAlerta->execute([':empresa'=>(int)$id_empresa]);
        $diasConfigurados = $configAlerta->fetchColumn();
        if ($diasConfigurados !== false) {
            $diasAlerta = max(1, min(30, (int)$diasConfigurados));
        }
    } catch (Throwable $erroConfig) {
        error_log('Filtro vencimentos pagar: '.$erroConfig->getMessage());
    }
    $query = $pdo->prepare("SELECT * FROM pagar WHERE empresa = :empresa AND (pago IS NULL OR pago <> 'Sim') AND vencimento > CURDATE() AND vencimento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY) ORDER BY vencimento ASC, id DESC");
    $query->bindValue(':empresa',(int)$id_empresa,PDO::PARAM_INT);
    $query->bindValue(':dias',$diasAlerta,PDO::PARAM_INT);
    $query->execute();
} else if ($filtro == 'Recebidas') {
	$query = $pdo->query("SELECT * from $tabela where pago = 'Sim' and empresa = '$id_empresa' order by id desc ");
} else if ($filtro == 'Hoje') {
	$query = $pdo->query("SELECT * from $tabela where $tipo_data = curDate() and pago = 'Não' and empresa = '$id_empresa' order by id desc ");
} else if ($filtro == 'Amanha') {
	$query = $pdo->query("SELECT * from $tabela where $tipo_data = '$data_amanha' and pago = 'Não' and empresa = '$id_empresa' order by id desc ");
} else if ($filtro == 'Todas') {
	$query = $pdo->query("SELECT * from $tabela where empresa = '$id_empresa' order by id desc ");
} else if ($filtro == 'Pedentes') {
	$query = $pdo->query("SELECT * from $tabela where pago = 'Não' and empresa = '$id_empresa' order by id desc ");
} else {
	$query = $pdo->query("SELECT * from $tabela WHERE $tipo_data >= '$dataInicial' and vencimento <= '$dataFinal' and empresa = '$id_empresa' order by id desc ");
}



$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
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
html body #listar.bc-pay-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-pay-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-pay-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-pay-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-pay-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-pay-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-pay-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-pay-cards #tabela thead{display:none!important}
html body #listar.bc-pay-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-pay-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-pay-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-pay-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-pay-cards #tabela tbody td:nth-child(3)::before{content:'Favorecido: '}
#listar.bc-pay-cards #tabela tbody td:nth-child(4)::before{content:'Valor: '}
#listar.bc-pay-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-pay-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-pay-cards #tabela tbody tr:hover,html body #listar.bc-pay-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-pay-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-pay-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-pay-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-pay-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-pay-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-pay-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-pay-cards #tabela tbody td:nth-child(5)::before{content:'Vencimento: '}
html body #listar.bc-pay-cards #tabela tbody td:nth-child(6)::before{content:'Pagamento: '}
html body #listar.bc-pay-cards #tabela tbody td:nth-child(7)::before{content:'Arquivo: ';margin-right:8px}
html body #listar.bc-pay-cards #tabela tbody td:nth-child(7){text-align:left!important}
html body #listar.bc-pay-cards #tabela tbody td:nth-child(4){font-size:20px;font-weight:700;color:#63351e}
html body #listar #tabela tbody td:last-child :is(a,button){display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;margin:3px!important;padding:0!important;border:1px solid #d7c5b7!important;border-radius:9px!important;background:#fff!important;color:#914a29!important}
html body #listar #tabela tbody td:last-child :is(a,button).ocultar{display:none!important}
html body #listar.bc-pay-cards #tabela tbody td:last-child :is(a,button):hover{background:#fff!important;transform:none!important;box-shadow:none!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item.disabled{opacity:1!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item .page-link::before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item .page-link::after{content:none!important}
</style>
<div id="bc-pay-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-pay-list-view');
 if(!root||!bar)return;
 
 function apply(mode){if(mode==='cards'){root.querySelectorAll('input[id^=seletor-]').forEach(function(x){x.checked=false;});jQuery('#ids').val('');jQuery('#btn-deletar,#btn-baixar').hide();}root.dataset.payView=mode;root.classList.toggle('bc-pay-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.payView||'cards');
})();
</script>
BCVIEW;
if ($linhas > 0) {
	echo <<<HTML
	<table class="table table-bordered text-nowrap border-bottom" id="tabela">
	<thead> 
	<tr> 
	<th align="center" width="5%" class="text-center">Selecionar</th>
	<th>Descrição</th>
	<th>Favorecido</th>
	<th>Valor</th>	
	<th>Vencimento</th>	
	<th>Pagamento</th>	
	<th class="text-center">Arquivo</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>
HTML;


	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$descricao = $res[$i]['descricao'];
		$fornecedor = $res[$i]['fornecedor'];
		$funcionario = $res[$i]['funcionario'];
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
		$subtotal = $res[$i]['subtotal'];
		$usuario_lanc = $res[$i]['usuario_lanc'];
		$usuario_pgto = $res[$i]['usuario_pgto'];
		$pago = $res[$i]['pago'];



		$vencimentoF = implode('/', array_reverse(@explode('-', $vencimento)));
		$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
		$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));



		$valorF = @number_format($valor, 2, ',', '.');
		$multaF = @number_format($multa, 2, ',', '.');
		$jurosF = @number_format($juros, 2, ',', '.');
		$descontoF = @number_format($desconto, 2, ',', '.');
		$subtotalF = @number_format($subtotal, 2, ',', '.');

		if ($pago == "Sim") {
			$valor_finalF = @number_format($subtotal, 2, ',', '.');
		} else {
			$valor_finalF = @number_format($valor, 2, ',', '.');
		}



		//extensão do arquivo
		$ext = pathinfo($arquivo, PATHINFO_EXTENSION);
		if ($ext == 'pdf') {
			$tumb_arquivo = 'pdf.png';
		} else if ($ext == 'rar' || $ext == 'zip' || $ext == 'RAR' || $ext == 'ZIP') {
			$tumb_arquivo = 'rar.png';
		} else if ($ext == 'doc' || $ext == 'docx' || $ext == 'txt' || $ext == 'DOC' || $ext == 'DOCX' || $ext == 'TXT') {
			$tumb_arquivo = 'word.png';
		} else if ($ext == 'xlsx' || $ext == 'xlsm' || $ext == 'xls' || $ext == 'XLSX' || $ext == 'XLSM' || $ext == 'XLS') {
			$tumb_arquivo = 'excel.png';
		} else if ($ext == 'xml' || $ext == 'XML') {
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
			$nome_frequencia = 'Única';
		}

		$query2 = $pdo->query("SELECT * FROM formas_pgto where id = '$forma_pgto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_pgto = $res2[0]['nome'];
		} else {
			$nome_pgto = 'Sem Registro';
		}



		if ($pago == 'Sim') {
			$classe_pago = 'verde';
			$classe_pago2 = '#ddffe1';
			$ocultar = 'ocultar';
			$ocultar_pendentes = '';
			$total_pago += $subtotal;
		} else if ($pago == 'Não' and $vencimento > $data_atual) {
			$classe_pago2 = '';
			$classe_pago = 'text-danger';
			$ocultar = '';
			$total_pago += $valor;
			$ocultar_pendentes = 'ocultar';
		} else if ($pago == 'Não' and $vencimento == $data_atual) {
			$classe_pago2 = '#fff8dd';
			$classe_pago = 'text-danger';
			$ocultar = '';
			$total_pago += $valor;
			$ocultar_pendentes = 'ocultar';
		} else {
			$classe_pago = 'text-danger';
			$classe_pago2 = '#ffdddd';
			$ocultar = '';
			$total_pendentes += $valor;
			$ocultar_pendentes = 'ocultar';
		}

		$valor_multa = 0;
		$valor_juros = 0;
		$classe_venc = '';
		if (@strtotime($vencimento) < @strtotime($data_hoje)) {
			$classe_venc = 'text-danger';
			$valor_multa = $multa_atraso;

			//pegar a quantidade de dias que o pagamento está atrasado
			$dif = @strtotime($data_hoje) - @strtotime($vencimento);
			$dias_vencidos = floor($dif / (60 * 60 * 24));

			$valor_juros = ($valor * $juros_atraso / 100) * $dias_vencidos;
		}

		$total_pendentesF = @number_format($total_pendentes, 2, ',', '.');
		$total_pagoF = @number_format($total_pago, 2, ',', '.');





		//PEGAR RESIDUOS DA CONTA
		$total_resid = 0;
		$valor_com_residuos = 0;
		$query2 = $pdo->query("SELECT * FROM $tabela WHERE id_ref = '$id' and residuo = 'Sim'");
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


		$nome_pessoa = '';
		$telefone_pessoa = '';
		$pix_pessoa = '';
		$tipo_pessoa = 'Pessoa';
		if ($fornecedor != 0 || $funcionario != 0) {
			if ($fornecedor != 0) {
				$tab = 'fornecedores';
				$id_pessoa = $fornecedor;
				$tipo_pessoa = 'Fornecedor';
			}

			if ($funcionario != 0) {
				$tab = 'usuarios';
				$id_pessoa = $funcionario;
				$tipo_pessoa = 'Funcionário';
			}

			//nome pessoa
			$query2 = $pdo->query("SELECT * FROM $tab where id = '$id_pessoa'");
			$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
			$total_reg2 = @count($res2);
			if ($total_reg2 > 0) {
				$nome_pessoa = $res2[0]['nome'];
				$telefone_pessoa = $res2[0]['telefone'];
				$pix_pessoa = $res2[0]['pix'];
			} else {
				$nome_pessoa = 'Nenhum';
				$telefone_pessoa = '';
				$pix_pessoa = '';
			}
		}

		if ($valor_finalF < $subtotal) {
			$valor_finalF = $subtotal;
		}



		echo <<<HTML
<tr style="background: {$classe_pago2}">
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td><i class="fa fa-square {$classe_pago} mr-1"></i> {$descricao}</td>
<td> {$nome_pessoa}</td>
<td class="">R$ {$valor_finalF} <small><a href="#" onclick="mostrarResiduos('{$id}')" class="text-danger" title="Ver Resíduos">{$vlr_antigo_conta}</a></small></td>	
<td class="esc {$classe_venc}">{$vencimentoF}</td>
<td class="esc">{$data_pgtoF}</td>

<td class="text-center"><a href="images/contas/{$arquivo}" target="_blank">
	<img class="rounded-circle" src="images/contas/{$tumb_arquivo}" width="30px" height="30px"></a>
	</td>

<td>
	<big><a class="{$ocultar}" href="#" onclick="editar('{$id}','{$descricao}','{$valor}','{$fornecedor}','{$funcionario}','{$vencimento}','{$data_pgto}','{$forma_pgto}','{$frequencia}','{$obs}','{$tumb_arquivo}')" title="Editar Dados"><i class="fa fa-edit text-info"></i></a></big>



	<big><a class="{$ocultar}" href="#" onclick="excluirConta('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>

<big><a href="#" onclick="mostrar('{$descricao}','{$valorF}','{$nome_pessoa}','{$vencimentoF}','{$data_pgtoF}','{$nome_pgto}','{$nome_frequencia}','{$obs}','{$tumb_arquivo}','{$multaF}','{$jurosF}','{$descontoF}','{$subtotalF}','{$nome_usu_lanc}','{$nome_usu_pgto}', '{$pago}', '{$arquivo}','{$telefone_pessoa}','{$pix_pessoa}','{$tipo_pessoa}')" title="Mostrar Dados"><i class="fa fa-info-circle text-primary"></i></a></big>


<big><a class="{$ocultar}" href="#" onclick="baixar('{$id}', '{$valor}', '{$descricao}', '{$forma_pgto}', '{$valor_multa}', '{$valor_juros}')" title="Baixar Conta"><i class="fa fa-check-square" style="color:#079934"></i></a></big>

	<big><a class="{$ocultar}" href="#" onclick="parcelar('{$id}', '{$valor}', '{$descricao}')" title="Parcelar Conta"><i class="fa fa-calendar-o" style="color:#7f7f7f"></i></a></big>

		<big><a href="#" onclick="arquivo('{$id}', '{$descricao}')" title="Inserir / Ver Arquivos"><i class="fa fa-file-o taxt-danger"></i></a></big>


				<form method="POST" action="rel/recibo_conta_pagar_class.php" target="_blank" style="display:inline-block">
					<input type="hidden" name="id" value="{$id}">
					<big><button class="{$ocultar_pendentes} icones_mobile" title="Imprimir Recibo 80mmm" style="background:transparent; border:none; margin:0; padding:0"><i class="fa fa-file-pdf-o text-danger"></i></button></big>
					</form>

					<form method="POST" action="rel/imp_recibo_pagar.php" target="_blank" style="display:inline-block">
					<input type="hidden" name="id" value="{$id}">
					<big><button class="{$ocultar_pendentes} icones_mobile" title="Imprimir Recibo 80mmm" style="background:transparent; border:none; margin:0; padding:0"><i class="fa fa-print " style="color:#666464"></i></button></big>
					</form>



</td>
</tr>
HTML;
	}


	echo <<<HTML
</tbody>
</table>
<div align="center" id="mensagem-excluir"></div>
<br>



			<p align="right" style="margin-top: -10px">
				<span style="margin-right: 10px">Total Pendentes  <span style="color:red">R$ {$total_pendentesF} </span></span>
				<span>Total Pago  <span style="color:green">R$ {$total_pagoF} </span></span>
			</p>

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



		$('#total_itens').text('R$ <?= $total_valorF ?>');
		$('#total_total').text('R$ <?= $total_totalF ?>');
		$('#total_vencidas').text('R$ <?= $total_vencidasF ?>');
		$('#total_hoje').text('R$ <?= $total_hojeF ?>');
		$('#total_amanha').text('R$ <?= $total_amanhaF ?>');
		$('#total_recebidas').text('R$ <?= $total_recebidasF ?>');
		$('#total_pedentes').text('R$ <?= $total_valorF ?>');
	});
</script>


<script type="text/javascript">
	function editar(id, descricao, valor, fornecedor, funcionario, vencimento, data_pgto, forma_pgto, frequencia, obs, arquivo) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#id').val(id);
		$('#descricao').val(descricao);
		$('#valor').val(valor);
		$('#fornecedor').val(fornecedor).change();
		$('#funcionario').val(funcionario).change();
		$('#vencimento').val(vencimento);
		$('#data_pgto').val(data_pgto);
		$('#forma_pgto').val(forma_pgto).change();
		$('#frequencia').val(frequencia).change();
		$('#obs').val(obs);

		$('#arquivo').val('');
		$('#target').attr('src', 'images/contas/' + arquivo);

		$('#modalForm').modal('show');
	}


	function mostrar(descricao, valor, pessoa, vencimento, data_pgto, nome_pgto, frequencia, obs, arquivo, multa, juros, desconto, total, usu_lanc, usu_pgto, pago, arq, telefone, pix, tipo_pessoa) {

		if (data_pgto == "") {
			data_pgto = 'Pendente';
		}

		if (pessoa == "") {
			pessoa = "Sem Registro";
			$('#dados_pessoa').hide();
		} else {
			$('#dados_pessoa').show();
		}

		if (telefone == "") {
			telefone = "Sem Registro";
		}

		if (pix == "") {
			pix = "Sem Registro";
		}

		$('#titulo_dados').text(descricao);
		$('#valor_dados').text(valor);
		$('#cliente_dados').text(pessoa);
		$('#vencimento_dados').text(vencimento);
		$('#data_pgto_dados').text(data_pgto);
		$('#nome_pgto_dados').text(nome_pgto);
		$('#frequencia_dados').text(frequencia);
		$('#obs_dados').text(obs);

		$('#multa_dados').text(multa);
		$('#juros_dados').text(juros);
		$('#desconto_dados').text(desconto);
		$('#total_dados').text(total);
		$('#usu_lanc_dados').text(usu_lanc);
		$('#usu_pgto_dados').text(usu_pgto);
		$('#telefone_dados').text(telefone);
		$('#pix_dados').text(pix);
		$('#tipo_pessoa').text(tipo_pessoa);

		$('#pago_dados').text(pago);
		$('#target_dados').attr("src", "images/contas/" + arquivo);
		$('#target_link_dados').attr("href", "images/contas/" + arq);

		$('#modalDados').modal('show');
	}

	function limparCampos() {
		$('#id').val('');
		$('#descricao').val('');
		$('#valor').val('');
		$('#vencimento').val("<?= $data_atual ?>");
		$('#data_pgto').val('');
		$('#obs').val('');
		$('#arquivo').val('');
		$('#fornecedor').val(0).change();
		$('#funcionario').val(0).change();
		$('#frequencia').val(0).change();
		$('#arquivo').val('');

		$('#target').attr("src", "images/contas/sem-foto.png");

		$('#ids').val('');
		$('#btn-deletar').hide();
		$('#btn-baixar').hide();
	}


	function permissoes(id, nome) {

		$('#id_permissoes').val(id);
		$('#nome_permissoes').text(nome);

		$('#modalPermissoes').modal('show');
		listarPermissoes(id);
	}


	function parcelar(id, valor, nome) {
		$('#id-parcelar').val(id);
		$('#valor-parcelar').val(valor);
		$('#qtd-parcelar').val('');
		$('#nome-parcelar').text(nome);
		$('#nome-input-parcelar').val(nome);
		$('#modalParcelar').modal('show');
		$('#mensagem-parcelar').text('');
	}


	function baixar(id, valor, descricao, pgto, multa, juros) {

		$('#id-baixar').val(id);
		$('#descricao-baixar').text(descricao);
		$('#valor-baixar').val(valor);
		$('#saida-baixar').val(pgto).change();
		$('#subtotal').val(valor);


		$('#valor-juros').val(juros);
		$('#valor-desconto').val('');
		$('#valor-multa').val(multa);

		totalizar()

		$('#modalBaixar').modal('show');
		$('#mensagem-baixar').text('');
	}


	function mostrarResiduos(id) {

		$.ajax({
			url: 'paginas/' + pag + "/listar-residuos.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar-residuos").html(result);
			}
		});
		$('#modalResiduos').modal('show');


	}

	function arquivo(id, nome) {
		$('#id-arquivo').val(id);
		$('#nome-arquivo').text(nome);
		$('#modalArquivos').modal('show');
		$('#mensagem-arquivo').text('');
		$('#arquivo_conta').val('');
		listarArquivos();
	}
</script>




<script type="text/javascript">
	function deletarSelBaixar() {
		var ids = $('#ids').val();
		var id = ids.split("-");

		for (i = 0; i < id.length - 1; i++) {
			var novo_id = id[i];
			$.ajax({
				url: 'paginas/' + pag + "/baixar_multiplas.php",
				method: 'POST',
				data: {
					novo_id
				},
				dataType: "html",

				success: function(result) {
					//alert(result)

				}
			});
		}

		setTimeout(() => {
			buscar();
			limparCampos();
		}, 1000);


	}



	function excluirConta(id) {
		//$('#mensagem-excluir').text('Excluindo...')


		$('body').removeClass('timer-alert');
		Swal.fire({
			title: "Deseja Excluir?",
			text: "Você não conseguirá recuperá-lo novamente!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33', // Cor do botão de confirmação (vermelho)
			cancelButtonColor: '#3085d6', // Cor do botão de cancelamento (azul)
			confirmButtonText: "Sim, Excluir!",
			cancelButtonText: "Cancel",
			reverseButtons: true
		}).then((result) => {
			if (result.isConfirmed) {


				$.ajax({
					url: 'paginas/' + pag + "/excluir.php",
					method: 'POST',
					data: {
						id
					},
					dataType: "html",

					success: function(mensagem) {
						if (mensagem.trim() == "Excluído com Sucesso") {

							// Ação de exclusão aqui
							Swal.fire({
								title: 'Excluido com Sucesso!',
								text: 'Fecharei em 1 segundo.',
								icon: "success",
								timer: 1000
							})
							//excluido();
							buscar();
							limparCampos();


						} else {
							$('#mensagem-excluir').addClass('text-danger')
							$('#mensagem-excluir').text(mensagem)
						}
					}
				});

			}
		});

	}


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
			$('#btn-baixar').hide();
		} else {
			$('#btn-deletar').show();
			$('#btn-baixar').show();
		}
	}

	function deletarSel(id) {
		//$('#mensagem-excluir').text('Excluindo...')

		$('body').removeClass('timer-alert');
		Swal.fire({
			title: "Deseja Excluir?",
			text: "Você não conseguirá recuperá-lo novamente!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33', // Cor do botão de confirmação (vermelho)
			cancelButtonColor: '#3085d6', // Cor do botão de cancelamento (azul)
			confirmButtonText: "Sim, Excluir!",
			cancelButtonText: "Cancel",
			reverseButtons: true
		}).then((result) => {
			if (result.isConfirmed) {




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

					buscar();
				}, 1000);

				limparCampos();


			}
		});


	};
</script>