<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
header('Content-Type: application/json');

require_once("sistema/conexao.php");
require_once("sistema/painel/buscar_config.php");

// Monta os caminhos completos para os ícones, usando pasta "images/"
$icon_path_192 = "/sistema/img/icone_192_".$id_empresa.".png";
$icon_path_512 = "/sistema/img/icone_512_".$id_empresa.".png";

$manifest = [
    "name" => $nome_sistema,
    "short_name" => $nome_sistema,
    "start_url" => "/site/".$url_site,
    "scope" => "/site/",
    "display" => "standalone",
    "orientation" => "portrait",
    "background_color" => "#ffffff",
    "theme_color" => "#ffffff",
    "icons" => [
        [
            "src" => $icon_path_192,
            "sizes" => "192x192",
            "type" => "image/png"
        ],
        [
            "src" => $icon_path_512,
            "sizes" => "512x512",
            "type" => "image/png"
        ]
    ]
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
