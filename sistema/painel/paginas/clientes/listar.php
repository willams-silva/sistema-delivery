<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_operador = (int) ($_SESSION['id'] ?? 0);
if ($id_empresa <= 0 || $id_operador <= 0) {
    http_response_code(403);
    exit('Sessão inválida');
}
require_once("../../../conexao.php");
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $id_operador, ':empresa' => $id_empresa]);
$nivel_operador = $operador->fetchColumn();
if ($nivel_operador === false) {
    http_response_code(403);
    exit('Operador não autorizado');
}
if ($nivel_operador !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'clientes' LIMIT 1");
    $permissao->execute([':usuario' => $id_operador, ':empresa' => $id_empresa]);
    if (!$permissao->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão para consultar clientes');
    }
}

require_once("../../buscar_config.php");
$mostrar_registros = @$_SESSION['registros'];
$id_usuario = $id_operador;

$tabela = 'clientes';


if ($mostrar_registros == 'Não') {
	$query = $pdo->prepare("SELECT * FROM clientes WHERE usuario = :usuario AND empresa = :empresa ORDER BY id DESC");
    $query->execute([':usuario' => $id_usuario, ':empresa' => $id_empresa]);
} else {
	$query = $pdo->prepare("SELECT * FROM clientes WHERE empresa = :empresa ORDER BY id DESC");
    $query->execute([':empresa' => $id_empresa]);
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
#listar #bc-client-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-client-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-client-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-client-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-client-cards #tabela thead{display:none!important}
html body #listar.bc-client-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-client-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-client-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-client-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-client-cards #tabela tbody td:nth-child(3)::before{content:'Telefone: '}
#listar.bc-client-cards #tabela tbody td:nth-child(4)::before{content:'E-mail: '}
#listar.bc-client-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-client-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}
</style>
<div id="bc-client-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-client-list-view');
 if(!root||!bar)return;
 var old=document.getElementById('bc-client-view');if(old)old.style.display='none';
 function apply(mode){root.dataset.clientView=mode;root.classList.toggle('bc-client-cards',mode==='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.clientView||'cards');
})();
</script>
BCVIEW;

if ($linhas > 0) {
	echo <<<HTML
<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead>
	<tr>
	<th align="center" width="5%" class="text-center">Selecionar</th>
	<th class="width40">Nome</th>
	<th >Telefone</th>
	<th >Email</th>
	<th>Ações</th>
	</tr>
	</thead>
	<tbody>
HTML;

	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
		$telefone = $res[$i]['telefone'];
		$email = $res[$i]['email'];
		$endereco = $res[$i]['endereco'];
		$cpf = $res[$i]['cpf'];
		$numero = $res[$i]['numero'];
		$bairro = $res[$i]['bairro'];
		$cidade = $res[$i]['cidade'];
		$estado = $res[$i]['estado'];
		$cep = $res[$i]['cep'];
		$data_cad = $res[$i]['data_cad'];
		$data_nasc = $res[$i]['data_nasc'];
		$complemento = $res[$i]['complemento'];
		$marketing = $res[$i]['marketing'];
		$cartoes = $res[$i]['cartoes'];

		$data_cadF = implode('/', array_reverse(@explode('-', $data_cad)));
		$data_nascF = implode('/', array_reverse(@explode('-', $data_nasc)));

		$tel_whatsF = '55' . preg_replace('/[ ()-]+/', '', $telefone);

		//verificar débitos cliente
		$query2 = $pdo->prepare("SELECT 1 FROM receber WHERE cliente = :cliente AND empresa = :empresa AND vencimento < CURDATE() AND pago != 'Sim' LIMIT 1");
        $query2->execute([':cliente' => $id, ':empresa' => $id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$linhas2 = @count($res2);
		if ($linhas2 > 0) {
			$debito2 = 'table-danger';
			$debito = 'text-danger';
		} else {
			$debito2 = '';
			$debito = '';
		}

		if ($telefone == '' || $telefone == "Sem Registro") {
			$ocultar_whats = 'ocultar';
		} else {
			$ocultar_whats = '';
		}

		if ($marketing == 'Não') {
			$ocultar_mark = 'ocultar';
		} else {
			$ocultar_mark = '';
		}

		$nomeF = rawurlencode($nome);
		$enderecoF = rawurlencode($endereco);

		$mostrar_cartao = '';
		if($total_cartoes_config == 0){
			$mostrar_cartao = 'ocultar';
		}


        // Codifica dados para HTML e argumentos JavaScript separadamente.
        $html_id = htmlspecialchars((string) $id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_id = htmlspecialchars(json_encode((string) $id, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_nome = htmlspecialchars((string) $nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_nome = htmlspecialchars(json_encode((string) $nome, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_nomeF = htmlspecialchars((string) $nomeF, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_nomeF = htmlspecialchars(json_encode((string) $nomeF, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_telefone = htmlspecialchars((string) $telefone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_telefone = htmlspecialchars(json_encode((string) $telefone, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_email = htmlspecialchars((string) $email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_email = htmlspecialchars(json_encode((string) $email, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_endereco = htmlspecialchars((string) $endereco, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_endereco = htmlspecialchars(json_encode((string) $endereco, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_enderecoF = htmlspecialchars((string) $enderecoF, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_enderecoF = htmlspecialchars(json_encode((string) $enderecoF, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cpf = htmlspecialchars((string) $cpf, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cpf = htmlspecialchars(json_encode((string) $cpf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_numero = htmlspecialchars((string) $numero, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_numero = htmlspecialchars(json_encode((string) $numero, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_bairro = htmlspecialchars((string) $bairro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_bairro = htmlspecialchars(json_encode((string) $bairro, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cidade = htmlspecialchars((string) $cidade, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cidade = htmlspecialchars(json_encode((string) $cidade, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_estado = htmlspecialchars((string) $estado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_estado = htmlspecialchars(json_encode((string) $estado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cep = htmlspecialchars((string) $cep, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cep = htmlspecialchars(json_encode((string) $cep, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_complemento = htmlspecialchars((string) $complemento, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_complemento = htmlspecialchars(json_encode((string) $complemento, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_data_cadF = htmlspecialchars((string) $data_cadF, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_data_cadF = htmlspecialchars(json_encode((string) $data_cadF, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_data_nasc = htmlspecialchars((string) $data_nasc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_data_nasc = htmlspecialchars(json_encode((string) $data_nasc, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_data_nascF = htmlspecialchars((string) $data_nascF, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_data_nascF = htmlspecialchars(json_encode((string) $data_nascF, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cartoes = htmlspecialchars((string) $cartoes, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cartoes = htmlspecialchars(json_encode((string) $cartoes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_tel_whatsF = htmlspecialchars((string) $tel_whatsF, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_tel_whatsF = htmlspecialchars(json_encode((string) $tel_whatsF, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

		echo <<<HTML
<tr class="{$debito2}">
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$html_id}" onchange="selecionar({$js_id})">
<label for="seletor-{$html_id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td class="{$debito}"> {$html_nome}</td>
<td>{$html_telefone}</td>
<td>{$html_email}</td>
<td>
<a class="btn btn-info-light btn-sm" href="#" onclick="editar({$js_id},{$js_nomeF},{$js_email},{$js_telefone},{$js_enderecoF},{$js_cpf},{$js_data_nasc},{$js_numero},{$js_bairro},{$js_cidade},{$js_estado},{$js_cep},{$js_complemento})" title="Editar Dados"><i class="fa fa-edit"></i></a>

<big><a class="btn btn-danger-light btn-sm" href="#" onclick="excluir({$js_id})" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>

<a class="btn btn-primary-light btn-sm" href="#" onclick="mostrar({$js_nome},{$js_email},{$js_telefone},{$js_endereco}, {$js_data_cadF},{$js_cpf},{$js_data_nascF},{$js_numero},{$js_bairro},{$js_cidade},{$js_estado},{$js_cep},{$js_complemento},{$js_cartoes})" title="Mostrar Dados"><i class="fa fa-info-circle"></i></a>

<a class="btn btn-dark-light btn-sm" href="#" onclick="arquivo({$js_id}, {$js_nome})" title="Inserir / Ver Arquivos"><i class="fa fa-file-o "></i></a>

<a class="btn btn-success-light btn-sm" href="#" onclick="mostrarContas({$js_nome},{$js_id})" title="Mostrar Contas"><i class="fa fa-money"></i></a>

<a class="btn btn-success-light btn-sm {$ocultar_whats}" href="http://api.whatsapp.com/send?1=pt_BR&phone={$html_tel_whatsF}" title="Whatsapp" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>

<a class="btn btn-info btn-sm {$mostrar_cartao}" href="#" title="Mensagem Cartões" onclick="mensagemMarketing({$js_id}, {$js_nome}, {$js_cartoes}, {$js_telefone})"><i class="fa fa-id-card"></i></a>

<big><a class="btn btn-danger-light btn-sm {$ocultar_mark}" href="#" onclick="excluirMarketing({$js_id})" title="Remover do Marketing Whatsapp"><i class="fa fa-ban text-danger"></i></a></big>

</td>
</tr>
HTML;
	}
} else {
	echo 'Não possui nenhum cadastro!';
}

echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
HTML;
?>


<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela').DataTable({
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
	function editar(id, nome, email, telefone, endereco, cpf, data_nasc, numero, bairro, cidade, estado, cep, complemento) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#id').val(id);
		$('#nome').val(decodeURIComponent(nome));
		$('#email').val(email);
		$('#telefone').val(telefone);
		$('#endereco').val(decodeURIComponent(endereco));
		$('#cpf').val(cpf);
		$('#data_nasc').val(data_nasc);
		$('#numero').val(numero);
		$('#bairro').val(bairro);
		$('#cidade').val(cidade);
		$('#estado').val(estado).change();
		$('#cep').val(cep);
		$('#complemento').val(complemento);

		$('#modalForm').modal('show');
	}

	function mostrar(nome, email, telefone, endereco, data_cad, cpf, data_nasc, numero, bairro, cidade, estado, cep, complemento, cartoes) {

		$('#titulo_dados').text(decodeURIComponent(nome));
		$('#email_dados').text(email);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(decodeURIComponent(endereco));
		$('#cpf_dados').text(cpf);
		$('#data_dados').text(data_cad);
		$('#data_nasc_dados').text(data_nasc);

		$('#numero_dados').text(numero);
		$('#bairro_dados').text(bairro);
		$('#cidade_dados').text(cidade);
		$('#estado_dados').text(estado);
		$('#cep_dados').text(cep);
		$('#complemento_dados').text(complemento);
		$('#cartoes_dados').text(cartoes);

		$('#modalDados').modal('show');
	}

	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#email').val('');
		$('#telefone').val('');
		$('#endereco').val('');
		$('#cpf').val('');
		$('#data_nasc').val('');
		$('#numero').val('');
		$('#bairro').val('');
		$('#cidade').val('');
		$('#estado').val('').change();
		$('#cep').val('');
		$('#complemento').val('');

		$('#ids').val('');
		$('#btn-deletar').hide();
	}


	function arquivo(id, nome) {
		$('#id-arquivo').val(id);
		$('#nome-arquivo').text(nome);
		$('#modalArquivos').modal('show');
		$('#mensagem-arquivo').text('');
		$('#arquivo_conta').val('');
		listarArquivos();
	}



	function mostrarContas(nome, id) {

		$('#titulo_contas').text(nome);
		$('#id_contas').val(id);

		$('#modalContas').modal('show');
		listarDebitos(id);

	}


	function listarDebitos(id) {

		$.ajax({
			url: 'paginas/' + pag + "/listar_debitos.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar_debitos").html(result);
			}
		});
	}





function excluirMarketing(id) {
    //$('#mensagem-excluir').text('Excluindo...')

    $('body').removeClass('timer-alert');
    Swal.fire({
        title: "Excluir do Marketing?",
        text: "Ele não receberá mais mensagens!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33', // Cor do botão de confirmação (vermelho)
        cancelButtonColor: '#3085d6', // Cor do botão de cancelamento (azul)
        confirmButtonText: "Sim, Excluir!",
        cancelButtonText: "Cancelar",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {


            $.ajax({
                url: 'paginas/' + pag + "/excluir_marketing.php",
                method: 'POST',
                data: { id },
                dataType: "html",

                success: function (mensagem) {
                    if (mensagem.trim() == "Excluído com Sucesso") {

                        // Ação de exclusão aqui
                        Swal.fire({
                            title: 'Excluido com Sucesso!',
                            text: 'Fecharei em 1 segundo.',
                            icon: "success",
                            timer: 1000
                        })
                        //excluido();
                        listar();
                        limparCampos();


                    } else {
                        $('#mensagem-excluir').addClass('text-danger')
                        $('#mensagem-excluir').text(mensagem)
                    }
                }
            });

        }
    });


};



function mensagemMarketing(id, nome, cartoes, telefone) {

		$.ajax({
			url: 'paginas/' + pag + "/mensagem_marketing.php",
			method: 'POST',
			data: {
				id, nome, cartoes, telefone
			},
			dataType: "html",

			success: function(result) {
				
				alertsucesso('Mensagem Enviada!');
			}
		});
	}

</script>