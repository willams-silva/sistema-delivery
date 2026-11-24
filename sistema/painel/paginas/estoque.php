<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

//verificar se ele tem a permissão de estar nessa página
if (@$estoque == 'ocultar') {
    echo "<script>window.location='../index.php'</script>";
    exit();
}

$pag = 'estoque';
//verificar se ele tem a permissão de estar nessa página
if (@$estoque == 'ocultar') {
    echo "<script>window.location='../index.php'</script>";
    exit();
}

?>

<div class="card mb-3" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-boxes-stacked me-2"></i>Gestão de Estoque</h2><span style="font-size:12px;color:#795038">Acompanhe os produtos que atingiram o nível mínimo de estoque.</span></div></div>
<div class="breadcrumb-header justify-content-between">
    <div class="left-content mt-2">


    </div>
</div>

<div class="bs-example widget-shadow" style="padding:15px" id="listar">

</div>




<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span><span id="titulo_dados"></span></h4>
                <button id="btn-fechar-dados" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">

                <div class="row">


                    <div class="col-md-8">
                        <div class="tile">
                            <div class="table-responsive">
                                <table id="" class="text-left table table-bordered">


                                    <tr>
                                        <td width="45%" class="bg-primary text-white">Categoria</td>
                                        <td><span id="categoria_dados"></span></td>
                                    </tr>

                                    <tr>
                                        <td class="bg-primary text-white">Valor Compra</td>
                                        <td><span id="valor_compra_dados"></span></td>
                                    </tr>

                                    <tr>
                                        <td class="bg-primary text-white">Valor Venda</td>
                                        <td><span id="valor_venda_dados"></span></td>
                                    </tr>

                                    <tr>
                                        <td class="bg-primary text-white">Estoque</td>
                                        <td><span id="estoque_dados"></span></td>
                                    </tr>

                                    <tr>
                                        <td class="bg-primary text-white">Alerta Nível Mínimo Estoque</td>
                                        <td><span id="nivel_estoque_dados"></span></td>
                                    </tr>






                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="tile">
                            <div class="table-responsive">
                                <table id="" class="text-left table table-bordered">

                                    <tr>
                                        <td align="center"><img src="" id="target_mostrar" width="200px"></td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>




                    <div class="col-md-12">
                        <div class="tile">
                            <div class="table-responsive">
                                <table id="" class="text-left table table-bordered">

                                    <tr>
                                        <td width="30$" class="bg-primary text-white">Descrição</td>
                                        <td><span id="descricao_dados"></span></td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>





                </div>










            </div>

        </div>
    </div>
</div>





<script type="text/javascript">
    var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $('.sel2').select2({
            dropdownParent: $('#modalForm')
        });
    });
</script>


<script type="text/javascript">
    function carregarImg() {
        var target = document.getElementById('target');
        var file = document.querySelector("#foto").files[0];

        var reader = new FileReader();

        reader.onloadend = function() {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>