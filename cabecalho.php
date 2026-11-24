<?php
@session_start();
require_once("sistema/conexao.php");

$url_site = $_SESSION['url_site'];


$query2 = $pdo->query("SELECT * FROM config where url_site = '$url_site'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$id_empresa = @$res2[0]['empresa'];
$_SESSION['empresa'] = $id_empresa;
require_once("sistema/painel/buscar_config.php");

// Garante que os contatos exibidos na vitrine sejam sempre os da loja acessada,
// sem depender de variáveis globais previamente carregadas pelo cadastro SaaS.
$stmt_contatos_loja = $pdo->prepare("SELECT telefone, instagram FROM config WHERE url_site = :url_site LIMIT 1");
$stmt_contatos_loja->execute([':url_site' => $_SESSION['url_site']]);
$config_contatos_loja = $stmt_contatos_loja->fetch(PDO::FETCH_ASSOC);

if ($config_contatos_loja) {
	$telefone_sistema = $config_contatos_loja['telefone'] ?? '';
	$whatsapp_sistema = $telefone_sistema;
	$tel_whats = '55' . preg_replace('/\\D+/', '', $telefone_sistema);
	$instagram_sistema = $config_contatos_loja['instagram'] ?? '';
}


?>
<!DOCTYPE html>
<html>

<head>
	<title><?php echo $nome_sistema ?></title>

	<base href="<?php echo $url_sistema ?>">

	 <meta property="og:description" content="<?php echo $nome_sistema ?>" />
  	<meta property="og:image" content="sistema/img/logo.png">
    <meta property="og:image:type" content="image/jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630"> 

	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<!-- Site Metas -->
	<meta name="keywords" content="delivery interativo, pedir sanduíche" />
	<meta name="description" content="Temos sanduíches de qualidade.." />
	<meta name="author" content="Hugo Vasconcelos" />

	<link rel="shortcut icon" href="sistema/img/icone.png" type="image/x-icon">
	<link rel="manifest" href="/manifest.php?loja=<?php echo rawurlencode($url_site ?? '') ?>&amp;v=2">
<link rel="apple-touch-icon" href="/pwa-icon.php?loja=<?php echo rawurlencode($url_site ?? '') ?>&amp;size=192">


	<!-- CSS here -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/animate.css">
	<link rel="stylesheet" href="assets/css/swiper-bundle.css">
	<link rel="stylesheet" href="assets/css/slick.css">
	<link rel="stylesheet" href="assets/css/magnific-popup.css">
	<link rel="stylesheet" href="assets/css/spacing.css">
	<link rel="stylesheet" href="assets/css/meanmenu.css">
	<link rel="stylesheet" href="assets/css/nice-select.css">
	<link rel="stylesheet" href="assets/css/fontawesome.min.css">
	<link rel="stylesheet" href="assets/css/icon-dukamarket.css">
	<link rel="stylesheet" href="assets/css/main.css">
	<link rel="stylesheet" href="assets/css/style.css">


	<!-- CSS only -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">

	<!-- JavaScript Bundle with Popper -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">

	<link rel="stylesheet" type="text/css" href="css/style.css">

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>


	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>



	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<!-- SweetAlert2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




<style>
/* Avisos e pop-ups das páginas públicas: padrão do cardápio. */
.swal2-popup {
  background:#fffdf9 !important;
  color:#503827 !important;
  border:1px solid #efddc9 !important;
  border-radius:20px !important;
  box-shadow:0 18px 55px rgba(78,43,20,.18) !important;
  padding:22px !important;
  font-family:inherit !important;
}
.swal2-title { color:#753d1e !important; font-weight:750 !important; }
.swal2-html-container { color:#604837 !important; line-height:1.6 !important; }
.swal2-confirm { background:#a85b24 !important; border:0 !important; border-radius:10px !important; padding:11px 23px !important; font-weight:700 !important; box-shadow:none !important; }
.swal2-cancel,.swal2-deny { background:#fff2e4 !important; color:#82451f !important; border:1px solid #e8c9a9 !important; border-radius:10px !important; padding:11px 20px !important; font-weight:650 !important; }
.swal2-actions { gap:9px !important; }
.swal2-icon.swal2-info { border-color:#b87b48 !important; color:#a85b24 !important; }
.swal2-icon.swal2-warning { border-color:#d99d46 !important; color:#b57a20 !important; }
.swal2-input,.swal2-textarea,.swal2-select { border:1px solid #ddc4ad !important; border-radius:10px !important; background:#fff !important; color:#503827 !important; }
.modal-content { background:#fffdf9; color:#503827; border:1px solid #efddc9; border-radius:18px; box-shadow:0 16px 45px rgba(78,43,20,.14); overflow:hidden; }
.modal-header { background:#fff2e4; color:#753d1e; border-bottom:1px solid #efddc9; padding:16px 20px; }
.modal-title { font-weight:750; }
.modal-body { padding:20px; }
.modal-footer { border-top:1px solid #efddc9; padding:14px 20px; }
.modal-content .btn-primary,.modal-content .btn-success,.modal-content .btn-warning { background:#a85b24 !important; color:#fff !important; border-color:#a85b24 !important; border-radius:10px; font-weight:700; }
.modal-content .btn-secondary { background:#fff2e4; color:#82451f; border-color:#e8c9a9; border-radius:10px; }
.modal-content .form-control:focus,.modal-content .form-select:focus { border-color:#c38a57; box-shadow:0 0 0 .2rem rgba(168,91,36,.12); }
@media(max-width:600px) {
 .swal2-popup { width:calc(100% - 28px) !important; padding:18px 14px !important; }
 .swal2-title { font-size:1.35rem !important; }
 .modal-dialog { margin:12px; }
 .modal-body { padding:15px; }
}
</style>


</head>

<body>