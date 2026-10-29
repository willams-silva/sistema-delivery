<?php 
$tabela = 'usuarios';
require_once("../../../../conexao.php");

$busca = @$_POST['p1'];

$query = $pdo->query("SELECT * from $tabela where nome like '%$busca%' or telefone like '%$busca%' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
for($i=0; $i<$linhas; $i++){
	$id = $res[$i]['id'];
	$nome = $res[$i]['nome'];
	$telefone = $res[$i]['telefone'];
	$email = $res[$i]['email'];
	$senha = $res[$i]['senha'];
	$foto = $res[$i]['foto'];
	$nivel = $res[$i]['nivel'];
	$endereco = $res[$i]['endereco'];
	$ativo = $res[$i]['ativo'];
	$data = $res[$i]['data'];

	$dataF = implode('/', array_reverse(@explode('-', $data)));

	if($ativo == 'Sim'){
	$icone = 'fa-check-square';
	$titulo_link = 'Desativar Usuário';
	$acao = 'Não';
	$classe_ativo = '';
	$foto_ativo = '';
	}else{
		$icone = 'fa-square-o';
		$titulo_link = 'Ativar Usuário';
		$acao = 'Sim';
		$classe_ativo = '#c4c4c4';
		$foto_ativo = '.5';
	}

	$mostrar_adm = '';
	if($nivel == 'Administrador'){
		$senha = '******';
		$mostrar_adm = 'ocultar';
		
	}


echo <<<HTML
<li class="swipeout">
						   <div class="swiper-wrapper">		
								  <div class="swiper-slide swipeout-content item-content" style="color:{$classe_ativo} ">
										<div class="post_entry" onclick="mostrar('{$nome}','{$email}','{$telefone}','{$endereco}','{$ativo}','{$dataF}', '{$senha}', '{$nivel}', '{$foto}')">
											<div class="post_thumb"><img src="images/perfil/{$foto}" alt="" title="" style="opacity: {$foto_ativo}"/></div>
											<div class="post_details">
											  <div class="post_category textos_list">{$nome}</div>				
												<p>{$nivel}</p>
												<p class="subtitulo_list">{$telefone} </p>
											  </div>
											<div class="post_swipe"><img src="images/swipe_more.png" alt="" title="" /></div>
										</div>
								  </div>
								  <div class="swiper-slide swipeout-actions-right">
										<a style="width: 25%; background: #3c6cb5" href="#" class="action1"><img src="images/icons/white/edit.png" alt="" title="" /></a>
										<a style="width: 25%; background: #d4412a" href="#" class="action1 open-popup" data-popup=""><img src="images/icons/white/trash.png" alt="" title="" /></a>
										<a style="width: 25%; background: #37de56" href="#" class="action1 open-popup" data-popup=""><img src="images/icons/white/square-check.png" alt="" title="" /></a>
										<a style="width: 25%; background: #dba81a" href="#" class="action1 open-popup" data-popup=".popup-social"><img src="images/icons/white/lock.png" alt="" title="" /></a>
								  </div>
						  </div>
					</li>	
HTML;

}

}else{
	echo 'Nenhum Registro Encontrado!';
}
?>


<script type="text/javascript">
	function editar(id, nome, email, telefone, endereco, nivel){
		$('#mensagem').text('');
    	$('#titulo_inserir').text('Editar Registro');

    	$('#id').val(id);
    	$('#nome').val(nome);
    	$('#email').val(email);
    	$('#telefone').val(telefone);
    	$('#endereco').val(endereco);
    	$('#nivel').val(nivel).change();

    	$('#modalForm').modal('show');
	}


	function mostrar(nome, email, telefone, endereco, ativo, data, senha, nivel, foto){
		    	
    	$('#titulo_dados').text(nome);
    	$('#email_dados').text(email);
    	$('#telefone_dados').text(telefone);
    	$('#endereco_dados').text(endereco);
    	$('#ativo_dados').text(ativo);
    	$('#data_dados').text(data);
    	
    	$('#nivel_dados').text(nivel);
    	$('#foto_dados').attr("src", "images/perfil/" + foto);    	

    	$('#btn_mostrar').click();
	}

	function limparCampos(){
		$('#id').val('');
    	$('#nome').val('');
    	$('#email').val('');
    	$('#telefone').val('');
    	$('#endereco').val('');

    	$('#ids').val('');
    	$('#btn-deletar').hide();	
	}

	

	function permissoes(id, nome){
		    	
    	$('#id_permissoes').val(id);
    	$('#nome_permissoes').text(nome);    	

    	$('#modalPermissoes').modal('show');
    	listarPermissoes(id);
	}

	


	
</script>

