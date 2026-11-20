<?php
@session_start();
$id_empresa = (int)($_SESSION['empresa'] ?? 0);
if ($id_empresa <= 0 || empty($_SESSION['id'])) { http_response_code(403); exit('Sessão inválida'); }
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'mesas';

$query = $pdo->prepare("SELECT * FROM mesas WHERE empresa = :empresa ORDER BY CAST(nome AS UNSIGNED), id");
$query->execute([':empresa'=>$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = count($res);
// Dados operacionais somente leitura: não altera pedidos nem movimenta comandas.
$atendimentos=[];
try {
 $qAt=$pdo->prepare("SELECT mesa, COUNT(*) AS qtd, MIN(data) AS primeira_data, MIN(horario_abertura) AS primeiro_horario, SUM(COALESCE(subtotal,0)) AS subtotal FROM abertura_mesa WHERE empresa=:empresa AND status='Aberta' GROUP BY mesa");
 $qAt->execute([':empresa'=>$id_empresa]);
 foreach($qAt->fetchAll(PDO::FETCH_ASSOC) as $at) $atendimentos[(int)$at['mesa']]=$at;
} catch(Throwable $e) {
 error_log('mesas/atendimentos: '.$e->getMessage());
}


$totalAbertas=0; $totalAtivas=0;
foreach($res as $mesaResumo){ if($mesaResumo['status']==='Aberta')$totalAbertas++; if($mesaResumo['ativo']==='Sim')$totalAtivas++; }
$totalLivres=0;
foreach($res as $mesaResumo){ if($mesaResumo['ativo']==='Sim' && $mesaResumo['status']==='Fechada')$totalLivres++; }
echo '<div class="row g-3 mb-3">';
foreach([['Total de mesas',$total_reg,'primary'],['Disponíveis',$totalLivres,'success'],['Abertas',$totalAbertas,'warning'],['Desativadas',$total_reg-$totalAtivas,'secondary']] as $kpi){
 echo '<div class="col-6 col-xl-3"><div class="card border shadow-sm h-100"><div class="card-body py-3"><div class="text-muted small">'.htmlspecialchars($kpi[0]).'</div><div class="h3 mb-0 text-'.$kpi[2].'">'.$kpi[1].'</div></div></div></div>';
}
echo '</div>';
if ($total_reg > 0) {
 echo '<div class="bc-mesas-cards row g-3 mb-3" id="bc-mesas-cards">';
 foreach($res as $mesaCard){
  $idCard=(int)$mesaCard['id'];
  $nomeCard=htmlspecialchars((string)$mesaCard['nome'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
  $ativoCard=$mesaCard['ativo']==='Sim';
  $abertaCard=$mesaCard['status']==='Aberta';
  $tipoCard=!$ativoCard?'inativa':($abertaCard?'ocupada':'livre');
  $rotuloCard=!$ativoCard?'Desativada':($abertaCard?'Ocupada':'Livre');
  $corCard=!$ativoCard?'secondary':($abertaCard?'danger':'success');
  echo '<div class="col-6 col-md-4 col-xl-3 bc-mesa-item" data-tipo="'.$tipoCard.'"><div class="bc-mesa-card h-100 border rounded-3 p-3 text-center">';
  echo '<i class="fa fa-chair fa-2x text-'.$corCard.' mb-2" aria-hidden="true"></i><div class="fw-bold mb-1">Mesa '.$nomeCard.'</div>';
  echo '<span class="badge bg-'.$corCard.' mb-2">'.$rotuloCard.'</span>';
  if($abertaCard){
   $at=$atendimentos[$idCard]??null;
   if($at){
    $qtd=(int)$at['qtd'];
    $valor=number_format((float)$at['subtotal'],2,',','.');
    $dataAt=(string)$at['primeira_data'];
    $horaAt=substr((string)$at['primeiro_horario'],0,5);
    $dataLegivel=DateTimeImmutable::createFromFormat('Y-m-d',$dataAt);
    $inicio=$dataLegivel?$dataLegivel->format('d/m').' '.$horaAt:$horaAt;
    echo '<div class="small text-muted mb-2">'.$qtd.' atendimento(s) aberto(s)<div>Desde '.htmlspecialchars($inicio,ENT_QUOTES,'UTF-8').'</div><div>Subtotal registrado: R$ '.$valor.'</div></div>';
   } else {
    echo '<div class="small text-muted mb-2">Sem atendimento aberto localizado</div>';
   }
  }
  echo '<div class="d-flex gap-2 justify-content-center flex-wrap">';
  echo '<button type="button" class="btn btn-sm btn-outline-primary" onclick="editar('.$idCard.', '.htmlspecialchars(json_encode((string)$mesaCard['nome']),ENT_QUOTES,'UTF-8').')" title="Editar mesa"><i class="fa fa-edit"></i></button>';
  echo '<button type="button" class="btn btn-sm btn-outline-dark" onclick="gerarQrCode('.$idCard.', '.htmlspecialchars(json_encode((string)$mesaCard['nome']),ENT_QUOTES,'UTF-8').')" title="QR Code"><i class="fa fa-qrcode"></i></button>';
  if($abertaCard) echo '<a class="btn btn-sm btn-outline-success" href="lista_pedidos_mesas?mesa='.$idCard.'" title="Consultar histórico da mesa"><i class="fa fa-list-alt"></i> Pedidos</a>';
  echo '</div></div></div>';
 }
 echo '</div><div id="bc-mesas-tabela">';
 
	echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
		<th align="center" width="5%" class="text-center">Selecionar</th>
	<th class="width60">Nome</th>			
	
	<th>Disponibilidade</th>
	<th>Status</th>
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$nome = htmlspecialchars((string)$res[$i]['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$ativo = $res[$i]['ativo'];
		$status = $res[$i]['status'];
        $tipoCard=$ativo!=='Sim'?'inativa':($status==='Aberta'?'ocupada':'livre');

		if ($ativo == 'Sim') {
			$icone = 'fa-check-square';
			$titulo_link = 'Tornar Indisponível';
			$acao = 'Não';
			$classe_linha = '';
		} else {
			$icone = 'fa-square-o';
			$titulo_link = 'Mesa Disponível';
			$acao = 'Sim';
			$classe_linha = '#c4c4c4';
		}


		$selected_aberta = '';
		if($status == 'Aberta'){
			$selected_aberta = 'selected';
		}

		$selected_fechada = '';
		if($status == 'Fechada'){
			$selected_fechada = 'selected';
		}

		echo <<<HTML
<tr>
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td style="color:{$classe_linha}">{$nome}</td>
<td style="color:{$classe_linha}"><span class="badge bg-light text-dark border">{$ativo}</span><span class="d-none bc-mesa-tipo">{$tipoCard}</span></td>
<td style="color:{$classe_linha}">
<select id="status_{$id}" style="background:transparent; border:none; border-bottom:1px solid #000; outline:none" onchange="editarStatus('{$id}')">
	<option value="Aberta" {$selected_aberta}>Aberta</option>
	<option value="Fechada" {$selected_fechada}>Fechada</option>
</select>
</td>
<td>
			<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$nome}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

		
<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>

			<a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone}"></i></a>

			<a class="btn btn-dark btn-sm" href="#" onclick="gerarQrCode('{$id}', '{$nome}')" title="QR Code"><i class="fa fa-qrcode"></i></a>

		</td>
</tr>
HTML;
	}

	echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>
</div>
HTML;
} else {
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>


<script type="text/javascript">
	$(document).ready(function() {
		if (!$('#tabela').length) return;
		$('#tabela').DataTable({
			"ordering": false,
			"stateSave": true,
            "pageLength": 10,
            "language": {"search":"Buscar mesa:","lengthMenu":"Exibir _MENU_ mesas","info":"Exibindo _START_ a _END_ de _TOTAL_ mesas","zeroRecords":"Nenhuma mesa encontrada","paginate":{"previous":"Anterior","next":"Próximo"}}
		});
		if (typeof window.bcAplicarVisaoMesas === 'function') window.bcAplicarVisaoMesas();
	});
</script>


<script type="text/javascript">
	function editar(id, nome) {
		$('#id').val(id);
		$('#nome').val(nome);
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
	}

	function limparCampos() {
		$('#nome').val('');
		$('#id').val('');
	}


	function editarStatus(id){
		var status = $('#status_'+id).val();		

		$.ajax({
        url: 'paginas/' + pag + "/editar_status.php",
        method: 'POST',
        data: {id, status},
        dataType: "html",

        success:function(result){
        	if(result.trim() === 'Editado'){
        		listar();
        	}else{
        		Swal.fire({icon:'warning',title:'Não foi possível alterar',text:result})
        	}
            
        },
        error:function(xhr){ Swal.fire({icon:'error',title:'Erro ao atualizar',text:xhr.responseText || 'Tente novamente'}); listar(); }
    });
	}


	function gerarQrCode(id, nome){	


		$('#titulo_qrcode').text(nome);
		$('#modalQRCode').modal('show');	

		$.ajax({
        url: 'paginas/' + pag + "/gerar_qrcode.php",
        method: 'POST',
        data: {id, nome},
        dataType: "html",

        success:function(result){
        	$('#gerar_qrcode').html(result);	           
        }
    });
	}
</script>

