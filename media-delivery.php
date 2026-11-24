<?php
// Serve only raster images from persistent storage, with legacy fallback.
$types = ['produtos'=>'sistema/painel/images/produtos','categorias'=>'sistema/painel/images/categorias','banners'=>'sistema/painel/images/banner_rotativo','logos'=>'sistema/img'];
$type = $_GET['tipo'] ?? '';
$name = $_GET['arquivo'] ?? '';
if (!is_string($type) || !isset($types[$type]) || !is_string($name) || $name !== basename($name) || !preg_match('/^[^\\/\\\\\x00-\x1f]+\.(jpe?g|png|gif|webp|avif)$/iuD', $name)) { http_response_code(404); exit; }
$roots = [dirname(__DIR__).'/upload_delivery/'.$type, __DIR__.'/'.$types[$type]];
foreach ($roots as $root) {
    $file = $root.'/'.$name;
    if (!is_file($file) || !is_readable($file)) continue;
    $info = @getimagesize($file);
    $mime = $info['mime'] ?? '';
    if (!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp','image/avif'], true)) continue;
    header('Content-Type: '.$mime);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=3600');
    header('Content-Length: '.filesize($file));
    readfile($file);
    exit;
}
http_response_code(404);
