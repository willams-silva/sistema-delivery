<?php
require_once __DIR__.'/permissoes_guard.php';
$q=$pdo->prepare('SELECT permissao FROM usuarios_permissoes WHERE usuario=:usuario');
$q->execute([':usuario'=>$bcUsuario]);
$marcadas=array_fill_keys(array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN)),true);
$q=$pdo->query('SELECT id,nome,grupo FROM acessos ORDER BY grupo,id');
$acessos=$q->fetchAll(PDO::FETCH_ASSOC);
$q=$pdo->query('SELECT id,nome FROM grupo_acessos ORDER BY id');
$grupos=[0=>'Sem grupo'];
foreach($q->fetchAll(PDO::FETCH_ASSOC) as $g) $grupos[(int)$g['id']]=$g['nome'];
$porGrupo=[];
foreach($acessos as $a) $porGrupo[(int)$a['grupo']][]=$a;
function bcPermEsc($s): string {return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?>
<div class="small text-muted mb-3">Selecione os acessos permitidos para este usuário. As alterações são aplicadas individualmente.</div>
<?php foreach($grupos as $grupoId=>$grupoNome): if(empty($porGrupo[$grupoId]))continue; ?>
<section class="mb-3">
<h6 class="fw-bold border-bottom pb-2"><?=bcPermEsc($grupoNome)?> <span class="badge bg-secondary"><?=count($porGrupo[$grupoId])?></span></h6>
<div class="row g-2">
<?php foreach($porGrupo[$grupoId] as $a): $id=(int)$a['id']; ?>
<div class="col-md-4 col-sm-6"><div class="form-check">
<input class="form-check-input bc-permissao-check" type="checkbox" id="bc-permissao-<?=$id?>" <?=isset($marcadas[$id])?'checked':''?> onchange="adicionarPermissao(<?=$id?>,<?=$bcUsuario?>)">
<label class="form-check-label" for="bc-permissao-<?=$id?>"><?=bcPermEsc($a['nome'])?></label>
</div></div>
<?php endforeach; ?>
</div></section>
<?php endforeach; ?>
<?php if (!$acessos): ?><p class="text-muted">Nenhum acesso cadastrado.</p><?php endif; ?>
<script>
$('#input-todos').prop('checked',$('.bc-permissao-check').length>0 && $('.bc-permissao-check:checked').length===$('.bc-permissao-check').length);
</script>
