<?php
@session_start();
require_once '../../../conexao.php';
$empresa=(int)($_SESSION['empresa']??0);$operador=(int)($_SESSION['id']??0);
if(!$empresa||!$operador){http_response_code(403);exit('Sessão inválida');}
$q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$operador,':empresa'=>$empresa]);
if($q->fetchColumn()!=='Administrador'){http_response_code(403);exit('Somente administradores podem consultar acessos');}
$q=$pdo->query('SELECT a.id,a.nome,a.chave,a.grupo,a.pagina,COALESCE(g.nome,"Sem grupo") grupo_nome,(SELECT COUNT(DISTINCT up.usuario) FROM usuarios_permissoes up WHERE up.permissao=a.id) total_usuarios,(SELECT COUNT(*) FROM cargos_permissoes cp WHERE cp.permissao_id=a.id) total_cargos FROM acessos a LEFT JOIN grupo_acessos g ON g.id=a.grupo ORDER BY a.id DESC');
$itens=$q->fetchAll(PDO::FETCH_ASSOC);
echo '<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h5 class="mb-1">Catálogo de acessos</h5><small class="text-muted">Definições globais compartilhadas entre empresas. Exclua somente acessos sem vínculos.</small></div><span class="badge bg-primary">'.count($itens).' acessos</span></div>';
if(!$itens){echo '<div class="text-center text-muted py-4">Nenhum acesso cadastrado.</div>';exit;}
echo '<div class="table-responsive"><table class="table table-hover table-striped align-middle" id="tabela"><thead><tr><th>Selecionar</th><th>Nome</th><th>Chave</th><th>Grupo</th><th>Página</th><th>Usuários</th><th>Cargos</th><th>Ações</th></tr></thead><tbody>';
foreach($itens as $a){
 $id=(int)$a['id'];$nome=htmlspecialchars((string)$a['nome'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $chave=htmlspecialchars((string)$a['chave'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $grupo=htmlspecialchars((string)$a['grupo_nome'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $pagina=htmlspecialchars((string)$a['pagina'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $usuarios=(int)$a['total_usuarios'];$cargos=(int)$a['total_cargos'];
 $dados=htmlspecialchars(json_encode([(string)$a['nome'],(string)$a['chave'],(string)$a['grupo'],(string)$a['pagina']],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 echo '<tr><td><input type="checkbox" id="seletor-'.$id.'" onchange="selecionar(\''.$id.'\')" aria-label="Selecionar acesso '.$id.'"></td><td><strong>'.$nome.'</strong></td><td><code>'.$chave.'</code></td><td>'.$grupo.'</td><td>'.$pagina.'</td><td>'.$usuarios.'</td><td>'.$cargos.'</td><td><button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="bcEditarAcesso('.$id.',this)" data-acesso="'.$dados.'" title="Editar acesso"><i class="fa fa-edit"></i></button><button type="button" class="btn btn-outline-danger btn-sm" onclick="bcExcluirAcesso('.$id.','.$usuarios.','.$cargos.')" title="Excluir acesso" '.($usuarios||$cargos?'disabled':'').'><i class="fa fa-trash"></i></button></td></tr>';
}
echo '</tbody></table></div>';
?>
<script>
$(function(){if($.fn.DataTable){$('#tabela').DataTable({pageLength:10,order:[[1,'asc']],language:{search:'Buscar acesso:',lengthMenu:'Mostrar _MENU_',info:'Exibindo _START_ a _END_ de _TOTAL_',zeroRecords:'Nenhum acesso encontrado',paginate:{previous:'Anterior',next:'Próximo'}}});}});
function bcEditarAcesso(id,btn){
 var d=$(btn).attr('data-acesso');try{d=JSON.parse(d);}catch(e){return;}
 $('#mensagem').text('');$('#titulo_inserir').text('Editar acesso');
 $('#id').val(id);$('#nome').val(d[0]);$('#chave').val(d[1]);$('#grupo').val(d[2]);$('#pagina').val(d[3]);$('#modalForm').modal('show');
}
function bcExcluirAcesso(id,usuarios,cargos){
 if(usuarios||cargos){Swal.fire('Acesso em uso','Remova os vínculos antes de excluir.','warning');return;}
 Swal.fire({title:'Excluir acesso?',text:'Essa definição é global e pode ser utilizada por outras empresas.',icon:'warning',showCancelButton:true,confirmButtonText:'Excluir',cancelButtonText:'Cancelar',confirmButtonColor:'#dc3545'}).then(function(r){if(r.isConfirmed)excluir(id);});
}
function limparCampos(){$('#id,#nome,#chave,#ids').val('');$('#grupo').val('0');$('#pagina').val('Sim');$('#btn-deletar').hide();}
</script>