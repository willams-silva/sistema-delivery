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
$total_reg = @count($res);

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
<td style="color:{$classe_linha}"><span class="badge bg-light text-dark border">{$ativo}</span></td>
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
HTML;
} else {
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>


<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela').DataTable({
			"ordering": false,
			"stateSave": true,
            "pageLength": 10,
            "language": {"search":"Buscar mesa:","lengthMenu":"Exibir _MENU_ mesas","info":"Exibindo _START_ a _END_ de _TOTAL_ mesas","zeroRecords":"Nenhuma mesa encontrada","paginate":{"previous":"Anterior","next":"Próximo"}}
		});
		// Mantém o foco do operador; não força a busca a cada atualização.
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

