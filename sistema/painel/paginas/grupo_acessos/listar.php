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
<style>
html body #listar #bc-grupos-tabela tbody td:last-child button.btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn.ocultar{display:none!important}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn.btn-outline-primary,html body #listar #bc-grupos-tabela tbody td:last-child button.btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn.btn-outline-danger{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn i{color:inherit!important;font-size:16px}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #bc-grupos-tabela tbody td:last-child button.btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody td:last-child button.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody td:last-child button.btn{transition:none!important}
#listar #bc-group-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-group-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-group-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-group-cards #bc-grupos-tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-group-cards #bc-grupos-tabela thead{display:none!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-group-cards #bc-grupos-tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-group-cards #bc-grupos-tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-group-cards #bc-grupos-tabela tbody td:nth-child(3)::before{content:'Acessos vinculados: '}
#listar.bc-group-cards #bc-grupos-tabela tbody td:nth-child(4)::before{content:none}
#listar.bc-group-cards #bc-grupos-tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-group-cards #bc-grupos-tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-group-cards #bc-grupos-tabela tbody tr:hover,html body #listar.bc-group-cards #bc-grupos-tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr td:last-child button.btn:hover,html body #listar.bc-group-cards #bc-grupos-tabela tbody tr td:last-child button.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr td:last-child button.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-group-cards #bc-grupos-tabela tbody tr td:last-child button.btn:hover i{color:inherit!important}

html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate .page-link,html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #bc-grupos-tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
#bc-group-pages{display:flex;justify-content:center;gap:6px;flex-wrap:wrap;margin-top:16px}
#bc-group-pages button{border:1px solid #d7c5b7;border-radius:8px;background:white;color:#6c4631;padding:8px 12px;font-weight:600}
#bc-group-pages button[aria-current=page]{background:#914a29;color:white}
#bc-group-pages button:disabled{background:#f4eee8;color:#806e63}
#listar #bc-grupos-tabela tbody tr[hidden]{display:none!important}
</style><div id="bc-group-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
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
<div id="bc-group-pages"></div><div id="mensagem-excluir" role="status"></div>
<script>
(function(){
 var tabela=$('#bc-grupos-tabela');
 var root=document.getElementById('listar'),page=1,size=10;
 function render(){
 var termo=$('#bc-grupo-busca').val().toLocaleLowerCase(),rows=tabela.find('tbody tr'),matches=rows.filter(function(){return $(this).find('.bc-grupo-nome').text().toLocaleLowerCase().includes(termo);}),total=Math.max(1,Math.ceil(matches.length/size));
 page=Math.min(page,total);rows.prop('hidden',true);matches.slice((page-1)*size,page*size).prop('hidden',false);
 var nav=$('#bc-group-pages').empty();
 function add(label,target,disabled,current){var b=$('<button type="button">').text(label).prop('disabled',disabled).on('click',function(){page=target;render();});if(current)b.attr('aria-current','page');nav.append(b);}
 if(matches.length){add('Anterior',page-1,page===1,false);for(var i=1;i<=total;i++)add(i,i,false,i===page);add('Próximo',page+1,page===total,false);}
 bcGrupoAtualizarSelecao();
 }
 function apply(mode){root.dataset.groupView=mode;root.classList.toggle('bc-group-cards',mode==='cards');tabela.toggleClass('table-hover',mode==='table');$('#bc-group-list-view button').each(function(){$(this).attr('aria-pressed',String(this.dataset.view===mode));});if(mode==='cards'){tabela.find('.bc-grupo-check').prop('checked',false);bcGrupoAtualizarSelecao();}}
 $('#bc-group-list-view button').on('click',function(){apply(this.dataset.view);});
 $('#bc-grupo-busca').on('input',function(){page=1;render();});
 apply(root.dataset.groupView||'cards');render();
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
