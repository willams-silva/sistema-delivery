<?php
@session_start();
require_once '../../../conexao.php';
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id']??0);
if (!$empresa || !$usuario) {http_response_code(403);exit('Sessão inválida');}
$q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:usuario AND empresa=:empresa LIMIT 1');
$q->execute([':usuario'=>$usuario,':empresa'=>$empresa]);
$nivel=$q->fetchColumn();
if ($nivel===false) {http_response_code(403);exit('Acesso negado');}
if ($nivel!=='Administrador') {
 $q=$pdo->prepare('SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id=up.permissao WHERE up.usuario=:usuario AND a.chave=:chave LIMIT 1');
 $q->execute([':usuario'=>$usuario,':chave'=>'grupo_acessos']);
 if (!$q->fetchColumn()) {http_response_code(403);exit('Sem permissão');}
}
$q=$pdo->query('SELECT g.id,g.nome,COUNT(a.id) AS total_acessos FROM grupo_acessos g LEFT JOIN acessos a ON a.grupo=g.id GROUP BY g.id,g.nome ORDER BY g.id DESC');
$grupos=$q->fetchAll(PDO::FETCH_ASSOC);
function bcEsc($v) {return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
 <div><strong><?=count($grupos)?> grupos cadastrados</strong><div class="small text-muted">Os grupos organizam os acessos disponíveis no sistema.</div></div>
 <input type="search" id="bc-grupo-busca" class="form-control" style="max-width:280px" placeholder="Pesquisar grupos..." aria-label="Pesquisar grupos">
</div>
<div class="table-responsive">
<table class="table table-hover align-middle" id="bc-grupos-tabela">
<thead><tr><th><input type="checkbox" id="bc-grupo-todos" aria-label="Selecionar todos"></th><th>Grupo</th><th>Acessos</th><th>Ações</th></tr></thead>
<tbody>
<?php foreach($grupos as $g): $id=(int)$g['id']; $nome=(string)$g['nome']; ?>
<tr>
<td><input type="checkbox" class="bc-grupo-check" value="<?=$id?>" id="seletor-<?=$id?>" onchange="bcGrupoAtualizarSelecao()" aria-label="Selecionar <?=bcEsc($nome)?>"></td>
<td class="bc-grupo-nome"><?=bcEsc($nome)?></td>
<td><span class="badge bg-secondary"><?= (int)$g['total_acessos'] ?></span></td>
<td>
<button type="button" class="btn btn-sm btn-outline-primary bc-grupo-editar" data-id="<?=$id?>" data-nome="<?=bcEsc($nome)?>" aria-label="Editar grupo <?=bcEsc($nome)?>"><i class="fa-solid fa-pen-to-square"></i></button>
<button type="button" class="btn btn-sm btn-outline-danger bc-grupo-excluir" data-id="<?=$id?>" data-nome="<?=bcEsc($nome)?>" data-total="<?=(int)$g['total_acessos']?>" aria-label="Excluir grupo <?=bcEsc($nome)?>"><i class="fa-solid fa-trash-can"></i></button>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php if(!$grupos): ?><p class="text-muted">Nenhum grupo cadastrado.</p><?php endif; ?>
</div>
<div id="mensagem-excluir" role="status"></div>
<script>
(function(){
 var tabela=$('#bc-grupos-tabela');
 $('#bc-grupo-busca').on('input',function(){
  var termo=this.value.toLocaleLowerCase();
  tabela.find('tbody tr').each(function(){
   $(this).toggle($(this).find('.bc-grupo-nome').text().toLocaleLowerCase().includes(termo));
  });
 });
 $('#bc-grupo-todos').on('change',function(){
  tabela.find('tbody tr:visible .bc-grupo-check').prop('checked',this.checked);bcGrupoAtualizarSelecao();
 });
 tabela.on('click','.bc-grupo-editar',function(){
  $('#mensagem').text('');$('#titulo_inserir').text('Editar grupo');
  $('#id').val($(this).attr('data-id'));$('#nome').val($(this).attr('data-nome'));
  $('#modalForm').modal('show');
 });
 tabela.on('click','.bc-grupo-excluir',function(){
  var id=$(this).attr('data-id'),nome=$(this).attr('data-nome'),total=Number($(this).attr('data-total'));
  if(total>0){Swal.fire({icon:'warning',title:'Grupo com acessos vinculados',text:'Remova os '+total+' acessos vinculados antes de excluir '+nome+'.'});return;}
  Swal.fire({icon:'warning',title:'Excluir grupo?',text:'Deseja excluir '+nome+'?',showCancelButton:true,confirmButtonText:'Excluir',cancelButtonText:'Cancelar',confirmButtonColor:'#dc3545'}).then(function(r){if(r.isConfirmed){$.post('paginas/grupo_acessos/excluir.php',{id:id},function(msg){if(String(msg).trim()==='Excluído com Sucesso'){listar();Swal.fire({icon:'success',title:'Grupo excluído',timer:1200,showConfirmButton:false});}else{Swal.fire({icon:'warning',text:String(msg)});}},'text').fail(function(xhr){Swal.fire({icon:'error',title:'Não foi possível excluir',text:(xhr.responseText||'Verifique suas permissões.').replace(/<[^>]*>/g,'').slice(0,250)});});}});
 });
})();
function bcGrupoAtualizarSelecao(){
 var ids=$('#bc-grupos-tabela .bc-grupo-check:checked').map(function(){return this.value;}).get();
 $('#ids').val(ids.length?ids.join('-')+'-':'');
 $('#btn-deletar').toggle(ids.length>0);
 var todas=$('#bc-grupos-tabela tbody tr:visible .bc-grupo-check');
 $('#bc-grupo-todos').prop('checked',todas.length>0 && todas.filter(':checked').length===todas.length);
}
function limparCampos(){
 $('#id').val('');$('#nome').val('');$('#ids').val('');
 $('#btn-deletar').hide();$('#bc-grupo-todos').prop('checked',false);
}
</script>
