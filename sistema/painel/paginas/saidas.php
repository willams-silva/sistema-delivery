<?php 
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

if(@$saidas == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}

$pag = 'saidas';

//verificar se ele tem a permissão de estar nessa página
if(@$saidas == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}

?>

<div class="card mb-3" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Gestão de Saídas</h2><span style="font-size:12px;color:#795038">Acompanhe as saídas de produtos e o histórico de movimentações.</span></div></div>
<div class="breadcrumb-header justify-content-between">
    <div class="left-content mt-2">


</div>
</div>


<div class="bs-example widget-shadow" style="padding:15px" id="listar">
	
</div>



<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>

