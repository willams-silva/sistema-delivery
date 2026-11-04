<?php
@session_start();
header('Content-Type: application/json');

require_once("sistema/conexao.php");

// Monta os caminhos completos para os ícones, usando pasta "images/"
$icon_path_192 = "/sistema/img/icone_192.png";
$icon_path_512 = "/sistema/img/icone_512.png";

$manifest = [
    "name" => $nome_sistema,
    "short_name" => $nome_sistema,
    "start_url" => "/",
    "scope" => "/",
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
