<?php 
require_once("../../../../conexao.php");
$pagina = 'arquivos';
$id = $_POST['id'];

echo <<<HTML
<small>
HTML;
$query = $pdo->query("SELECT * FROM $pagina where id_reg = '$id' and registro = 'Conta à Pagar' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
echo <<<HTML
	<li class="table_row">
        <div class="table_section_small" style="width:55%">Nome</div>
        <div class="table_section" style="width:20%">Arquivo</div>
        <div class="table_section" style="width:15%">Excluir</div> 
    </li>
HTML;
for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
$id = $res[$i]['id'];
$nome = $res[$i]['nome'];
$data_cad = $res[$i]['data_cad'];
$arquivo = $res[$i]['arquivo'];

$nomeF = mb_strimwidth($nome, 0, 23, "...");

//extensão do arquivo
$ext = pathinfo($arquivo, PATHINFO_EXTENSION);
if($ext == 'pdf' || $ext == 'PDF'){
	$tumb_arquivo = 'pdf.png';
}else if($ext == 'rar' || $ext == 'zip' || $ext == 'RAR' || $ext == 'ZIP'){
	$tumb_arquivo = 'rar.png';
}else if($ext == 'doc' || $ext == 'docx' || $ext == 'DOC' || $ext == 'DOCX'){
	$tumb_arquivo = 'word.png';
}else if($ext == 'xlsx' || $ext == 'xlsm' || $ext == 'xls'){
	$tumb_arquivo = 'excel.png';
}else if($ext == 'xml'){
	$tumb_arquivo = 'xml.png';
}else{
	$tumb_arquivo = $arquivo;
}

$data_cadF = implode('/', array_reverse(@explode('-', $data_cad)));

echo <<<HTML
			<li class="table_row celula_tabela" style="font-size:12px; margin-top: -1px; padding-top: 3px">
                        <div class="table_section_small" style="width:55%">{$nomeF}</div>
                        <div class="table_section" style="width:20%"><a href="images/arquivos/{$arquivo}" target="_blank" ><img src="images/arquivos/{$tumb_arquivo}" width="20px" height="20px"></a></div>
                        <div class="table_section" style="width:15%"><a href="#" onclick="excluir_arq('{$id}', '{$nome}')" ><img src="images/icons/black/trash.png" width="18px"></a></div> 
             </li>			
HTML;
}
}else{
	echo '';
}

?>



