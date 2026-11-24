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
$total=count($itens);$emUso=0;$comCargos=0;$disponiveis=0;
foreach($itens as $item){
 $temUsuario=(int)$item['total_usuarios']>0;
 $temCargo=(int)$item['total_cargos']>0;
 if($temUsuario||$temCargo)$emUso++;else $disponiveis++;
 if($temCargo)$comCargos++;
}
echo '<div class="row g-3 mb-3">';
foreach([['Total de acessos',$total,'fa-shield-halved','primary'],['Em uso',$emUso,'fa-user-lock','warning'],['Disponíveis',$disponiveis,'fa-circle-check','success'],['Em modelos de cargos',$comCargos,'fa-users-gear','info']] as $kpi){
 echo '<div class="col-6 col-xl-3"><div class="card border shadow-none h-100 mb-0"><div class="card-body py-3"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-muted">'.$kpi[0].'</div><div class="fs-4 fw-bold">'.(int)$kpi[1].'</div></div><i class="fa-solid '.$kpi[2].' text-'.$kpi[3].' fs-4"></i></div></div></div></div>';
}
echo '</div>';
echo '<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h5 class="mb-1">Catálogo de acessos</h5><small class="text-muted">Definições globais compartilhadas entre empresas. Exclua somente acessos sem vínculos.</small></div><span class="badge bg-primary">'.count($itens).' acessos</span></div>';
if(!$itens){echo '<div class="text-center text-muted py-4">Nenhum acesso cadastrado.</div>';exit;}
echo <<<'BCVIEW'
<style>
html body #listar #tabela tbody td:last-child button.btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #tabela tbody td:last-child button.btn.ocultar{display:none!important}
html body #listar #tabela tbody td:last-child button.btn.btn-outline-primary,html body #listar #tabela tbody td:last-child button.btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child button.btn.btn-outline-danger{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child button.btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #tabela tbody td:last-child button.btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #tabela tbody td:last-child button.btn i{color:inherit!important;font-size:16px}
html body #listar #tabela tbody td:last-child button.btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #tabela tbody td:last-child button.btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-access-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-access-cards #tabela tbody td:last-child button.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-access-cards #tabela tbody td:last-child button.btn{transition:none!important}
#listar #bc-access-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-access-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-access-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-access-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-access-cards #tabela thead{display:none!important}
html body #listar.bc-access-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-access-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-access-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-access-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-access-cards #tabela tbody td:nth-child(3)::before{content:'Chave: '}
#listar.bc-access-cards #tabela tbody td:nth-child(4)::before{content:'Grupo: '}
#listar.bc-access-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-access-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-access-cards #tabela tbody tr:hover,html body #listar.bc-access-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-access-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-access-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-access-cards #tabela tbody tr td:last-child button.btn:hover,html body #listar.bc-access-cards #tabela tbody tr td:last-child button.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-access-cards #tabela tbody tr td:last-child button.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-access-cards #tabela tbody tr td:last-child button.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
#listar.bc-access-cards #tabela tbody td:nth-child(5)::before{content:'Página: '}
#listar.bc-access-cards #tabela tbody td:nth-child(6)::before{content:'Usuários: '}
#listar.bc-access-cards #tabela tbody td:nth-child(7)::before{content:'Cargos: '}
html body #listar #tabela tbody td:last-child button.btn:disabled{opacity:.45!important;cursor:not-allowed}
</style>
<div id="bc-access-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-access-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.accessView=mode;if(mode==='cards'){jQuery(root).find('input[type=checkbox]').prop('checked',false);jQuery('#ids').val('');jQuery('#btn-deletar').hide();}root.classList.toggle('bc-access-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable&&jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.accessView||'cards');
})();
</script>
BCVIEW;
echo '<div class="table-responsive"><table class="table align-middle" id="tabela"><thead><tr><th>Selecionar</th><th>Nome</th><th>Chave</th><th>Grupo</th><th>Página</th><th>Usuários</th><th>Cargos</th><th>Ações</th></tr></thead><tbody>';
foreach($itens as $a){
 $id=(int)$a['id'];$nome=htmlspecialchars((string)$a['nome'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $chave=htmlspecialchars((string)$a['chave'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $grupo=htmlspecialchars((string)$a['grupo_nome'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $pagina=htmlspecialchars((string)$a['pagina'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 $usuarios=(int)$a['total_usuarios'];$cargos=(int)$a['total_cargos'];
 $dados=htmlspecialchars(json_encode([(string)$a['nome'],(string)$a['chave'],(string)$a['grupo'],(string)$a['pagina']],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
 echo '<tr><td><input type="checkbox" id="seletor-'.$id.'" onchange="bcSelecionarAcesso('.$id.')" aria-label="Selecionar acesso '.$id.'"></td><td><strong>'.$nome.'</strong></td><td><code>'.$chave.'</code></td><td>'.$grupo.'</td><td>'.$pagina.'</td><td>'.$usuarios.'</td><td>'.$cargos.'</td><td><button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="bcEditarAcesso('.$id.',this)" data-acesso="'.$dados.'" title="Editar acesso"><i class="fa fa-edit"></i></button><button type="button" class="btn btn-outline-danger btn-sm" onclick="bcExcluirAcesso('.$id.','.$usuarios.','.$cargos.')" title="Excluir acesso" '.($usuarios||$cargos?'disabled':'').'><i class="fa fa-trash"></i></button></td></tr>';
}
echo '</tbody></table></div>';
?>
<script>
$(function(){if($.fn.DataTable){$('#tabela').DataTable({pageLength:10,responsive:false,order:[[1,'asc']],language:{search:'Buscar acesso:',lengthMenu:'Mostrar _MENU_',info:'Exibindo _START_ a _END_ de _TOTAL_',zeroRecords:'Nenhum acesso encontrado',paginate:{previous:'Anterior',next:'Próximo'}}});}});
function bcEditarAcesso(id,btn){
 var d=$(btn).attr('data-acesso');try{d=JSON.parse(d);}catch(e){return;}
 $('#mensagem').text('');$('#titulo_inserir').text('Editar acesso');
 $('#id').val(id);$('#nome').val(d[0]);$('#chave').val(d[1]).prop('readonly',true).addClass('bg-light');$('#grupo').val(d[2]);$('#pagina').val(d[3]);$('#modalForm').modal('show');
}
function bcExcluirAcesso(id,usuarios,cargos){
 if(usuarios||cargos){Swal.fire('Acesso em uso','Remova os vínculos antes de excluir.','warning');return;}
 Swal.fire({title:'Excluir acesso?',text:'Essa definição é global e pode ser utilizada por outras empresas.',icon:'warning',showCancelButton:true,confirmButtonText:'Excluir',cancelButtonText:'Cancelar',confirmButtonColor:'#dc3545'}).then(function(r){if(r.isConfirmed)bcExcluirAcessoDireto(id);});
}
function bcExcluirAcessoDireto(id){
 $.ajax({url:'paginas/acessos/excluir.php',method:'POST',data:{id:id},dataType:'text'})
 .done(function(res){if(res.trim()==='Excluído com Sucesso'){listar();$('#ids').val('');$('#btn-deletar').hide();Swal.fire('Excluído','Acesso excluído com sucesso.','success');}else{Swal.fire('Não foi possível excluir',res,'warning');}})
 .fail(function(xhr){Swal.fire('Exclusão bloqueada',(xhr.responseText||'Falha ao excluir.').slice(0,250),'warning');});
}
function bcSelecionarAcesso(id){
 var ids=($('#ids').val()||'').split('-').filter(function(v){return /^[1-9][0-9]*$/.test(v);});
 ids=ids.filter(function(v){return v!==String(id);});
 if($('#seletor-'+id).is(':checked'))ids.push(String(id));
 $('#ids').val(ids.length?ids.join('-')+'-':'');
 $('#btn-deletar').toggle(ids.length>0);
}
function limparCampos(){$('#id,#nome,#chave,#ids').val('');$('#chave').prop('readonly',false).removeClass('bg-light');$('#grupo').val('0');$('#pagina').val('Sim');$('#btn-deletar').hide();}
</script>