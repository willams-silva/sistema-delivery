<?php
require_once __DIR__.'/sistema/conexao.php';
$slug=$_GET['loja']??'';$size=(int)($_GET['size']??192);
if(!is_string($slug)||!preg_match('/^[a-zA-Z0-9_-]+$/D',$slug)||!in_array($size,[192,512],true)){http_response_code(404);exit;}
$q=$pdo->prepare('SELECT icone, logo FROM config WHERE url_site=? LIMIT 1');$q->execute([$slug]);$row=$q->fetch(PDO::FETCH_ASSOC);
if(!$row){http_response_code(404);exit;}
foreach([$row['icone'],$row['logo']] as $name){
if(!$name||basename($name)!==$name)continue;
foreach([dirname(__DIR__).'/upload_delivery/logos/',__DIR__.'/sistema/img/'] as $root){
$file=$root.$name;if(!is_file($file)||!is_readable($file))continue;
$info=@getimagesize($file);if(!$info||$info[0]>6000||$info[1]>6000)continue;
$im=@imagecreatefromstring(file_get_contents($file));if(!$im)continue;
$out=imagecreatetruecolor($size,$size);imagealphablending($out,false);imagesavealpha($out,true);imagefill($out,0,0,imagecolorallocatealpha($out,0,0,0,127));
$scale=min($size/imagesx($im),$size/imagesy($im));$w=(int)round(imagesx($im)*$scale);$h=(int)round(imagesy($im)*$scale);
imagecopyresampled($out,$im,(int)(($size-$w)/2),(int)(($size-$h)/2),0,0,$w,$h,imagesx($im),imagesy($im));
if($slug==='rainhadastapiocas'){
$transparent=imagecolorallocatealpha($out,0,0,0,127);
$center=($size-1)/2;$radius=$size*.46;
for($y=0;$y<$size;$y++){for($x=0;$x<$size;$x++){
if(($x-$center)**2+($y-$center)**2>$radius**2)imagesetpixel($out,$x,$y,$transparent);
}}
}
header('Content-Type: image/png');header('Cache-Control: no-cache');imagepng($out);exit;
}}
http_response_code(404);
