<?php
require_once __DIR__.'/sistema/conexao.php';
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-store');
$slug=$_GET['loja'] ?? '';
if(!is_string($slug)||!preg_match('/^[a-zA-Z0-9_-]+$/D',$slug)){http_response_code(404);exit;}
$q=$pdo->prepare('SELECT nome, url_site, icone, logo FROM config WHERE url_site = ? LIMIT 1');
$q->execute([$slug]);$loja=$q->fetch(PDO::FETCH_ASSOC);
if(!$loja){http_response_code(404);exit;}
$nome=$slug==='rainhadastapiocas'?'Rainha das Tapiocas':($loja['nome'] ?: $slug);
$icons=[];
foreach([192,512] as $size){$icons[]=['src'=>'/pwa-icon.php?loja='.rawurlencode($slug) .'&size='.$size.'&v=3','sizes'=>$size.'x'.$size,'type'=>'image/png','purpose'=>'any'];}
echo json_encode(['id'=>'/site/'.$slug,'name'=>$nome,'short_name'=>$nome,'start_url'=>'/site/'.$slug,'scope'=>'/','display'=>'standalone','orientation'=>'portrait','background_color'=>'#fffaf5','theme_color'=>'#512c24','icons'=>$icons],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
