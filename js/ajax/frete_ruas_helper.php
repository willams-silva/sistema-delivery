<?php
function bcFreteRua(PDO $pdo,int $empresa,string $bairro,string $rua,string $entrega): ?float {
 if ($entrega !== 'Delivery') return 0.0;
 $q=$pdo->prepare("SELECT id,valor FROM bairros WHERE empresa=? AND nome=? LIMIT 1");
 $q->execute([$empresa,trim($bairro)]); $b=$q->fetch(PDO::FETCH_ASSOC);
 if (!$b) return null;
 if (trim($rua)!=='') {
 $q=$pdo->prepare("SELECT valor FROM bc_frete_ruas WHERE empresa=? AND bairro_id=? AND rua=? LIMIT 1");
 $q->execute([$empresa,$b['id'],trim($rua)]); $v=$q->fetchColumn();
 if ($v!==false) return (float)$v;
 // A specific street fee takes precedence over a zone fee.
 $q=$pdo->prepare("SELECT z.valor FROM bc_frete_zona_ruas zr JOIN bc_frete_zonas z ON z.id=zr.zona_id AND z.empresa=zr.empresa AND z.bairro_id=zr.bairro_id WHERE zr.empresa=? AND zr.bairro_id=? AND zr.rua=? LIMIT 1");
 $q->execute([$empresa,$b['id'],trim($rua)]); $v=$q->fetchColumn();
 if ($v!==false) return (float)$v;
 }
 return (float)$b['valor'];
}
