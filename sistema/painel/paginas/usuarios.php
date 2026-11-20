<?php
$pag = 'usuarios';

if (@$usuarios == 'ocultar') {
	echo "<script>window.location='index'</script>";
	exit();
}

?>

<div class="breadcrumb-header justify-content-between">
	<div class="left-content mt-2">
		<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-user-plus me-1"></i>
			Adicionar <?php echo ucfirst($pag); ?></a>




		<button type="button" class="btn btn-outline-secondary" onclick="bcAbrirAuditoria()"><i class="fa-solid fa-clock-rotate-left me-1"></i>Histórico de permissões</button>

		<a class="btn btn-danger" href="#" onclick="deletarSel()" title="Excluir" id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>

	</div>

</div>


<div class="row row-sm">
	<div class="col-lg-12">
		<div class="card custom-card">
			<div class="card-body" id="listar">

			</div>
		</div>
	</div>
</div>

<input type="hidden" id="ids">

<!-- Modal  -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
				<div class="modal-body">


					<div class="row needs-validation was-validated">
						<div class="col-md-6 mb-2">
							<label>Nome <span class="text-danger" style="font-size: 9px">(Obrigatório)</span> </label>
							<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
						</div>

						<div class="col-md-6 mb-2">
							<label>Email <span class="text-danger" style="font-size: 9px">(Obrigatório)</span></label>
							<input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
						</div>


					</div>


					<div class="row">

						<div class="col-md-3 mb-2 col-6 needs-validation was-validated">
							<label>Telefone <span class="text-danger" style="font-size: 9px">(Obrigatório)</span></label>
							<input type="text" class="form-control" id="telefone" name="telefone" placeholder="Telefone" required>
						</div>


						<div class="col-md-3 mb-2 col-6">
							<label>Nível</label>
							<select class="form-select" name="nivel" id="nivel">
								<?php
								$query = $pdo->query("SELECT * from cargos where empresa = '$id_empresa' order by id desc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if ($linhas > 0) {
									for ($i = 0; $i < $linhas; $i++) { ?>
										<option value="<?php echo $res[$i]['nome'] ?>"><?php echo $res[$i]['nome'] ?></option>
								<?php }
								} ?>
							</select>
						</div>


						<div class="col-md-3 mb-2">
							<label>Nascimento</label>
							<input type="date" class="form-control" id="data_nasc" name="data_nasc" placeholder="">
						</div>


						<div class="col-md-3 mb-2 col-6">
							<label>CPF</label>
							<input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF">
						</div>

					</div>

					<div class="row">



						<div class="col-md-3 mb-2">
							<label>Tipo de Chave</label>
							<select class="form-select" id="tipo_chave" name="tipo_chave">
								<option value="">Selecionar Chave</option>
								<option value="Telefone">Telefone</option>
								<option value="CPF">CPF</option>
								<option value="CNPJ">CNPJ</option>
								<option value="Código">Código</option>
								<option value="Email">Email</option>

							</select>
						</div>

						<div class="col-md-6 mb-2 ">
							<label>Chave Pix</label>
							<input type="text" class="form-control" id="pix" name="pix" placeholder="Chave Pix">
						</div>

						<div class="col-md-3 mb-2">
							<label>CEP</label>
							<input type="text" class="form-control" id="cep" name="cep" placeholder="CEP"
								onblur="pesquisacep(this.value);">
						</div>



					</div>

					<div class="row">


						<div class="col-md-9 mb-2">
							<label>Rua</label>
							<input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua">
						</div>

						<div class="col-md-3 mb-2">
							<label>Número</label>
							<input type="text" class="form-control" id="numero" name="numero" placeholder="Número">
						</div>




					</div>


					<div class="row">

						<div class="col-md-3 mb-2">
							<label>Complemento</label>
							<input type="text" class="form-control" id="complemento" name="complemento" placeholder="AP 100">
						</div>

						<div class="col-md-3 mb-2">
							<label>Bairro</label>
							<input type="text" class="form-control" id="bairro" name="bairro" placeholder="Bairro">
						</div>

						<div class="col-md-3 mb-2">
							<label>Cidade</label>
							<input type="text" class="form-control" id="cidade" name="cidade" placeholder="Cidade">
						</div>

						<div class="col-md-3 mb-2">
							<label>Estado</label>
							<select class="form-select" id="estado" name="estado">
								<option value="">Selecionar</option>
								<option value="AC">Acre</option>
								<option value="AL">Alagoas</option>
								<option value="AP">Amapá</option>
								<option value="AM">Amazonas</option>
								<option value="BA">Bahia</option>
								<option value="CE">Ceará</option>
								<option value="DF">Distrito Federal</option>
								<option value="ES">Espírito Santo</option>
								<option value="GO">Goiás</option>
								<option value="MA">Maranhão</option>
								<option value="MT">Mato Grosso</option>
								<option value="MS">Mato Grosso do Sul</option>
								<option value="MG">Minas Gerais</option>
								<option value="PA">Pará</option>
								<option value="PB">Paraíba</option>
								<option value="PR">Paraná</option>
								<option value="PE">Pernambuco</option>
								<option value="PI">Piauí</option>
								<option value="RJ">Rio de Janeiro</option>
								<option value="RN">Rio Grande do Norte</option>
								<option value="RS">Rio Grande do Sul</option>
								<option value="RO">Rondônia</option>
								<option value="RR">Roraima</option>
								<option value="SC">Santa Catarina</option>
								<option value="SP">São Paulo</option>
								<option value="SE">Sergipe</option>
								<option value="TO">Tocantins</option>
								<option value="EX">Estrangeiro</option>
							</select>
						</div>


					</div>


					<input type="hidden" class="form-control" id="id" name="id">

					<br>
					<small>
						<div id="mensagem" align="center"></div>
					</small>
				</div>
				<div class="modal-footer">
					<button id="btn_salvar" type="submit" class="btn btn-primary">Salvar<i class="fa fa-check ms-2"></i></button>

					<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
						<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...
					</button>
				</div>
			</form>
		</div>
	</div>
</div>






<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_dados"></span></h4>
				<button id="btn-fechar-dados" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">


				<div class="row">


					<div class="col-md-12">
						<div class="tile">
							<div class="table-responsive">
								<table id="" class="text-left table table-bordered">
									<tr>
										<td style="width: 20%" class="bg-primary text-white">Telefone</td>
										<td><span id="telefone_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Email</td>
										<td><span id="email_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">CPF</td>
										<td><span id="cpf_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Nível</td>
										<td><span id="nivel_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Ativo</td>
										<td><span id="ativo_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Data Cadastro</td>
										<td><span id="data_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Data Nascimento</td>
										<td><span id="data_nasc_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Chave Pix</td>
										<td><span id="tipo_chave_dados"></span></td>
									</tr>

									<tr>
										<td style="width: 25%" class="bg-primary text-white w_150">Rua</td>
										<td><span id="endereco_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Número</td>
										<td><span id="numero_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Complemento</td>
										<td><span id="complemento_dados"></span></td>
									</tr>


									<tr>
										<td class="bg-primary text-white w_150">Bairro</td>
										<td><span id="bairro_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Cidade</td>
										<td><span id="cidade_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Estado</td>
										<td><span id="estado_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">CEP</td>
										<td><span id="cep_dados"></span></td>
									</tr>




								</table>
							</div>
						</div>
					</div>



					<div class="col-md-12">
						<div class="tile">
							<div class="table-responsive">
								<table id="" class="text-left table table-bordered">

									<tr>
										<td align="center"><img src="" id="foto_dados" width="200px"></td>
									</tr>

								</table>
							</div>
						</div>
					</div>

				</div>





			</div>

		</div>
	</div>
</div>




<!-- Histórico de alterações de permissões -->
<div class="modal fade" id="modalAuditoriaPermissoes" tabindex="-1" aria-labelledby="tituloAuditoriaPermissoes" aria-hidden="true">
 <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
 <div class="modal-header"><h5 class="modal-title" id="tituloAuditoriaPermissoes"><i class="fa-solid fa-clock-rotate-left me-2"></i>Histórico de permissões</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
 <div class="modal-body">
 <div class="row g-2 mb-3">
 <div class="col-md-3"><label for="bc-audit-usuario" class="form-label">ID do usuário</label><input id="bc-audit-usuario" type="number" min="1" class="form-control" placeholder="Todos"></div>
 <div class="col-md-3"><label for="bc-audit-acao" class="form-label">Operação</label><select id="bc-audit-acao" class="form-select"><option value="">Todas</option><option value="conceder">Conceder acesso</option><option value="remover">Remover acesso</option><option value="conceder_todos">Conceder todos</option><option value="remover_todos">Remover todos</option></select></div>
 <div class="col-md-2"><label for="bc-audit-inicio" class="form-label">De</label><input id="bc-audit-inicio" type="date" class="form-control"></div>
 <div class="col-md-2"><label for="bc-audit-fim" class="form-label">Até</label><input id="bc-audit-fim" type="date" class="form-control"></div>
 <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-primary w-100" onclick="bcCarregarAuditoria()">Filtrar</button></div>
 </div>
 <p class="small text-muted">Últimos 100 registros correspondentes aos filtros. Visível apenas para administradores.</p>
 <div id="bc-audit-status" role="status" class="small mb-2"></div>
 <div class="table-responsive"><table class="table table-sm table-striped align-middle"><thead><tr><th>Data/hora</th><th>Administrador</th><th>Usuário</th><th>Operação</th><th>Acesso</th><th>Detalhes</th></tr></thead><tbody id="bc-audit-linhas"></tbody></table></div>
 </div></div></div>
</div>

<!-- Modal Permissoes -->
<div class="modal fade" id="modalPermissoes" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_permissoes"></span>

					<span class="d-inline-flex align-items-center gap-2 ms-3">
						<input class="form-check-input mt-0" type="checkbox" id="input-todos" onchange="marcarTodos()" aria-label="Conceder ou remover todas as permissões">
						<label for="input-todos" class="small mb-0">Todos os acessos</label>
					</span>

				</h4>
				<button style="" id="btn-fechar-arquivos" aria-label="Close" class="btn-close" data-bs-dismiss="modal"
					type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<div id="listar_permissoes">

				</div>

				<br>
				<input type="hidden" name="id" id="id_permissoes">
				<small>
					<div id="mensagem_permissao" align="center" class="mt-3"></div>
				</small>
			</div>

		</div>
	</div>
</div>




<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>



<script type="text/javascript">
	function bcErroPermissao(xhr) {
		var msg=(xhr.responseText||'Não foi possível alterar as permissões.').replace(/<[^>]*>/g,'').trim();
		$('#mensagem_permissao').addClass('text-danger').text(msg.slice(0,250));
		$('#input-todos').prop('disabled',false);
	}
	function listarPermissoes(id) {
		$.ajax({
			url: 'paginas/' + pag + "/listar_permissoes.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar_permissoes").html(result);
				$('#mensagem_permissao').text('');
			}
		});
	}

	function adicionarPermissao(id, usuario) {
		$.ajax({
			url: 'paginas/' + pag + "/add_permissao.php",
			method: 'POST',
			data: {
				id,
				usuario
			},
			dataType: "html",

			success: function(result) {
				listarPermissoes(usuario);
			},error: function(xhr){bcErroPermissao(xhr);listarPermissoes(usuario);}
		});
	}


	function marcarTodos() {
		var checkbox=$('#input-todos'),usuario=$('#id_permissoes').val(),conceder=checkbox.is(':checked');
		checkbox.prop('disabled',true);
		Swal.fire({
			icon:'warning',
			title:conceder?'Conceder todos os acessos?':'Remover todos os acessos?',
			text:conceder?'O usuário terá acesso a todas as funções cadastradas, inclusive administrativas.':'O usuário perderá todas as permissões configuradas.',
			showCancelButton:true,confirmButtonText:conceder?'Conceder todos':'Remover todos',cancelButtonText:'Cancelar',
			confirmButtonColor:conceder?'#d97706':'#dc3545'
		}).then(function(r){
			if(!r.isConfirmed){listarPermissoes(usuario);checkbox.prop('disabled',false);return;}
			var endpoint=conceder?'add_permissoes.php':'limpar_permissoes.php';
			$.ajax({url:'paginas/usuarios/'+endpoint,method:'POST',data:{id_usuario:usuario},dataType:'text'})
			 .done(function(){listarPermissoes(usuario);})
			 .fail(function(xhr){bcErroPermissao(xhr);listarPermissoes(usuario);})
			 .always(function(){checkbox.prop('disabled',false);});
		});
	}

	function adicionarPermissoes(id_usuario) {

		$.ajax({
			url: 'paginas/' + pag + "/add_permissoes.php",
			method: 'POST',
			data: {
				id_usuario
			},
			dataType: "html",

			success: function(result) {
				listarPermissoes(id_usuario);
			},error: function(xhr){bcErroPermissao(xhr);listarPermissoes(id_usuario);}
		});
	}


	function limparPermissoes(id_usuario) {

		$.ajax({
			url: 'paginas/' + pag + "/limpar_permissoes.php",
			method: 'POST',
			data: {
				id_usuario
			},
			dataType: "html",

			success: function(result) {
				listarPermissoes(id_usuario);
			},error: function(xhr){bcErroPermissao(xhr);listarPermissoes(id_usuario);}
		});
	}
</script>

<script type="text/javascript">
	function limpa_formulário_cep() {
		//Limpa valores do formulário de cep.
		document.getElementById('endereco').value = ("");
		document.getElementById('bairro').value = ("");
		document.getElementById('cidade').value = ("");
		document.getElementById('estado').value = ("");
		//document.getElementById('ibge').value=("");
	}

	function meu_callback(conteudo) {
		if (!("erro" in conteudo)) {
			//Atualiza os campos com os valores.
			document.getElementById('endereco').value = (conteudo.logradouro);
			document.getElementById('bairro').value = (conteudo.bairro);
			document.getElementById('cidade').value = (conteudo.localidade);
			document.getElementById('estado').value = (conteudo.uf);
			//document.getElementById('ibge').value=(conteudo.ibge);
		} //end if.
		else {
			//CEP não Encontrado.
			limpa_formulário_cep();
			alert("CEP não encontrado.");
		}
	}


	function pesquisacep(valor) {

		//Nova variável "cep" somente com dígitos.
		var cep = valor.replace(/\D/g, '');

		//Verifica se campo cep possui valor informado.
		if (cep != "") {

			//Expressão regular para validar o CEP.
			var validacep = /^[0-9]{8}$/;

			//Valida o formato do CEP.
			if (validacep.test(cep)) {

				//Preenche os campos com "..." enquanto consulta webservice.
				document.getElementById('endereco').value = "...";
				document.getElementById('bairro').value = "...";
				document.getElementById('cidade').value = "...";
				document.getElementById('estado').value = "...";
				//document.getElementById('ibge').value="...";

				//Cria um elemento javascript.
				var script = document.createElement('script');

				//Sincroniza com o callback.
				script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

				//Insere script no documento e carrega o conteúdo.
				document.body.appendChild(script);

			} //end if.
			else {
				//cep é inválido.
				limpa_formulário_cep();
				alert("Formato de CEP inválido.");
			}
		} //end if.
		else {
			//cep sem valor, limpa formulário.
			limpa_formulário_cep();
		}
	};
</script>
<script>
function bcAbrirAuditoria(){
 $('#bc-audit-status').text('');
 $('#modalAuditoriaPermissoes').modal('show');
 bcCarregarAuditoria();
}
function bcCarregarAuditoria(){
 var status=$('#bc-audit-status'),corpo=$('#bc-audit-linhas');
 status.removeClass('text-danger').text('Carregando histórico...');
 corpo.empty();
 $.ajax({url:'paginas/usuarios/auditoria_listar.php',method:'POST',dataType:'json',data:{
  usuario:$('#bc-audit-usuario').val(),acao:$('#bc-audit-acao').val(),
  inicio:$('#bc-audit-inicio').val(),fim:$('#bc-audit-fim').val()
 }}).done(function(d){
  var itens=d.registros||[];
  status.text(itens.length?itens.length+' registro(s) encontrado(s).':'Nenhum registro encontrado.');
  var nomes={conceder:'Concedeu acesso',remover:'Removeu acesso',conceder_todos:'Concedeu todos',remover_todos:'Removeu todos'};
  itens.forEach(function(x){
   var tr=$('<tr>');
   [x.criado_em,x.operador_nome,x.alvo,nomes[x.acao]||x.acao,x.acesso_nome||'—',x.detalhes||'—'].forEach(function(v){$('<td>').text(v==null?'—':String(v)).appendTo(tr);});
   corpo.append(tr);
  });
 }).fail(function(xhr){status.addClass('text-danger').text(xhr.responseJSON&&xhr.responseJSON.erro||'Não foi possível consultar o histórico.');});
}
</script>
