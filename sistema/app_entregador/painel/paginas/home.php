<?php 
if(@$home == 'ocultar'){
    echo "<script>window.location='index'</script>";
    exit();
}

//total clientes
$query = $pdo->query("SELECT * from clientes");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = @count($res);

//total clientes mes
$query = $pdo->query("SELECT * from clientes where data_cad >= '$data_inicio_mes' and data_cad <= '$data_final_mes'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes_mes = @count($res);

$total_pagar_vencidas = 0;
$query = $pdo->query("SELECT * from pagar where vencimento < curDate() and pago != 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagar_vencidas = @count($res);
for($i=0; $i<$contas_pagar_vencidas; $i++){
    $valor_1 = $res[$i]['valor'];   
    $total_pagar_vencidas += $valor_1;
}
$total_pagar_vencidasF = @number_format($total_pagar_vencidas, 2, ',', '.');    



$total_receber_vencidas = 0;
$query = $pdo->query("SELECT * from receber where vencimento < curDate() and pago != 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_receber_vencidas = @count($res);
for($i=0; $i<$contas_receber_vencidas; $i++){
    $valor_2 = $res[$i]['valor'];   
    $total_receber_vencidas += $valor_2;
}
$total_receber_vencidasF = @number_format($total_receber_vencidas, 2, ',', '.');


//total recebidas mes
$total_recebidas_mes = 0;
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_recebidas_mes = @count($res);
for($i=0; $i<$contas_recebidas_mes; $i++){
    $valor_3 = $res[$i]['valor'];   
    $total_recebidas_mes += $valor_3;
}
$total_recebidas_mesF = @number_format($total_recebidas_mes, 2, ',', '.');

//total contas pagas mes
$total_pagas_mes = 0;
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagas_mes = @count($res);
for($i=0; $i<$contas_pagas_mes; $i++){
    $valor_4 = $res[$i]['valor'];   
    $total_pagas_mes += $valor_4;
}
$total_pagas_mesF = @number_format($total_pagas_mes, 2, ',', '.');


$total_saldo_mes = $total_recebidas_mes - $total_pagas_mes;
$total_saldo_mesF = @number_format($total_saldo_mes, 2, ',', '.');
if($total_saldo_mes >= 0){
    $classe_saldo = 'green';
    
}else{
    $classe_saldo = 'red';
   
}






//total recebidas dia
$total_recebidas_dia = 0;
$query = $pdo->query("SELECT * from receber where data_pgto = curDate() and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_recebidas_dia = @count($res);
for($i=0; $i<$contas_recebidas_dia; $i++){
    $valor_3 = $res[$i]['valor'];   
    $total_recebidas_dia += $valor_3;
}
$total_recebidas_diaF = @number_format($total_recebidas_dia, 2, ',', '.');

//total contas pagas dia
$total_pagas_dia = 0;
$query = $pdo->query("SELECT * from pagar where data_pgto = curDate() and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagas_dia = @count($res);
for($i=0; $i<$contas_pagas_dia; $i++){
    $valor_4 = $res[$i]['valor'];   
    $total_pagas_dia += $valor_4;
}
$total_pagas_diaF = @number_format($total_pagas_dia, 2, ',', '.');


$total_saldo_dia = $total_recebidas_dia - $total_pagas_dia;
$total_saldo_diaF = @number_format($total_saldo_dia, 2, ',', '.');
if($total_saldo_dia >= 0){
    $classe_saldo_dia = 'green';
    $seta_saldo = 'up3.png';
}else{
    $classe_saldo_dia = 'red';
     $seta_saldo = 'down3.png';
}



 ?>
    <!-- Slider -->
    <div class="swiper-container slidertoolbar">
    	<div class="swiper-wrapper">

    		

             <div class="swiper-slide" style="margin-top:50px; background-image:url(images/slide2.png); ">
            <div class="slider_trans" style="">  


             <div class="swiper-container-team" style="margin-top: 5px">            
            <div class="swiper-wrapper" >
              <div class="swiper-slide " style="width:46%; margin-left:4px; margin-top:3px; margin-right:3px; padding:5px; box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); border-radius:4px; background: #f5f5f5;">
             
                <a href="clientes">
                        
                        <img style="float:right;" src="images/cards/clientes.png" width="40px" />
                        <span style="float:left; color:#363636; margin-left:0px; font-size: 9px">CLIENTES CADASTRADOS</span><br>
                        <span style="font-size: 19px; color:blue; margin-left:0; float:left; "><?php echo $total_clientes ?></span><br>                       

                        <div style="border-top:1px solid #c7c5c5; text-align: right; margin-top: 7px; margin-right: 8px; width:100%; padding:2px">
                            
                            <span style="color:#363636; margin-left:0px; font-size: 10px; padding: 2px;">
                            CADASTRADOS MÊS

                            <img style="margin-left: 0px; margin-left:2px; margin-top:4px;" src="images/cards/up3.png" width="6px" />
                        </span>                     

                        <span style="color: blue; font-size: 14px; margin-left: 5px; margin-right: 8px; "><?php echo $total_clientes_mes ?>

                        </span>     
                        </div>  
                    </a>


              </div>
              <div class="swiper-slide" style="margin-right:3px; margin-top:3px; width:46%; padding:5px; box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); border-radius:4px; background: #f5f5f5;">
             
                <a href="receber" style="  ">
                        
                        <img style="float:right;" src="images/cards/financ.png" width="35px" />
                        <span style="float:left; color:#363636; margin-left:0px; font-size: 9px">SALDO TOTAL MÊS</span><br>
                        <span style="font-size: 19px; margin-left:0; float:left; color:<?php echo $classe_saldo ?>">R$ <?php echo $total_saldo_mesF ?></span><br>                      

                        <div style="border-top:1px solid #c7c5c5; text-align: right; margin-top: 7px; margin-right: 8px; width:100%; padding:2px">
                            
                            <span style="color:#363636; margin-left:0px; font-size: 10px; padding: 2px;">
                            SALDO HOJE 

                            <img style="margin-left: 0px; margin-left:2px; margin-top:4px;" src="images/cards/<?php echo $seta_saldo ?>" width="6px" />
                        </span>                     

                        <span style="font-size: 14px; margin-left: 5px; margin-right: 8px; color:<?php echo $classe_saldo_dia ?>">R$ <?php echo $total_saldo_diaF ?>

                        </span>     
                        </div>  
                    </a>

              </div>
             
            </div>
        </div>





         <div class="swiper-container-team" style="margin-top: -15px">            
            <div class="swiper-wrapper" >
              <div class="swiper-slide " style="width:46%; margin-left:4px; margin-top:3px; margin-right:3px; padding:5px; box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); border-radius:4px; background: #f5f5f5;">
             
                <a href="pagar">
                        
                        <img style="float:right;" src="images/cards/financeiro_negativo.png" width="42px" />
                        <span style="float:left; color:#363636; margin-left:0px; font-size: 9px">DESPESAS VENCIDAS</span><br>
                        <span style="font-size: 19px; color:red; margin-left:0; float:left; ">R$ <?php echo $total_pagar_vencidasF ?></span><br>                       

                        <div style="border-top:1px solid #c7c5c5; text-align: right; margin-top: 7px; margin-right: 8px; width:100%; padding:2px">
                            
                            <span style="color:#363636; margin-left:0px; font-size: 10px; padding: 2px;">
                            PAGAR VENCIDAS

                            <img style="margin-left: 0px; margin-left:2px; margin-top:4px;" src="images/cards/down3.png" width="6px" />
                        </span>                     

                        <span style="color: red; font-size: 14px; margin-left: 5px; margin-right: 8px; "><?php echo $contas_pagar_vencidas ?>

                        </span>     
                        </div>  
                    </a>


              </div>
              <div class="swiper-slide" style="margin-right:3px; margin-top:3px; width:46%; padding:5px; box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); border-radius:4px; background: #f5f5f5;">
             
                <a href="receber" style="  ">
                        
                        <img style="float:right;" src="images/cards/financeiro_positivo.png" width="42px" />
                        <span style="float:left; color:#363636; margin-left:0px; font-size: 9px">RECEBER VENCIDAS</span><br>
                        <span style="font-size: 19px; color:green; margin-left:0; float:left;">R$ <?php echo $total_receber_vencidasF ?></span><br>                      

                        <div style="border-top:1px solid #c7c5c5; text-align: right; margin-top: 7px; margin-right: 8px; width:100%; padding:2px">
                            
                            <span style="color:#363636; margin-left:0px; font-size: 10px; padding: 2px;">
                            RECEBER VENCIDAS

                            <img style="margin-left: 0px; margin-left:2px; margin-top:4px;" src="images/cards/up3.png" width="6px" />
                        </span>                     

                        <span style="font-size: 14px; margin-left: 5px; margin-right: 8px; color:green"><?php echo $contas_receber_vencidas ?>

                        </span>     
                        </div>  
                    </a>

              </div>
             
            </div>
        </div>





         <div class="swiper-container-team" style="margin-top: -15px">            
            <div class="swiper-wrapper" >
              <div class="swiper-slide " style="width:46%; margin-left:4px; margin-top:3px; margin-right:3px; padding:5px; box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); border-radius:4px; background: #f5f5f5;">
             
                <a href="pagar">
                        
                        <img style="float:right;" src="images/cards/up.png" width="25px" />
                        <span style="float:left; color:#363636; margin-left:0px; font-size: 9px">NOVOS DADOS</span><br>
                        <span style="font-size: 19px; color:red; margin-left:0; float:left; ">R$ 50,00</span><br>                       

                        <div style="border-top:1px solid #c7c5c5; text-align: right; margin-top: 7px; margin-right: 8px; width:100%; padding:2px">
                            
                            <span style="color:#363636; margin-left:0px; font-size: 10px; padding: 2px;">
                            DADOS DE EXEMPLO

                            <img style="margin-left: 0px; margin-left:2px; margin-top:4px;" src="images/cards/down3.png" width="6px" />
                        </span>                     

                        <span style="color: red; font-size: 14px; margin-left: 5px; margin-right: 8px; "><?php echo $contas_pagar_vencidas ?>

                        </span>     
                        </div>  
                    </a>


              </div>
              <div class="swiper-slide" style="margin-right:3px; margin-top:3px; width:46%; padding:5px; box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); border-radius:4px; background: #f5f5f5;">
             
                <a href="receber" style="  ">
                        
                        <img style="float:right;" src="images/cards/down.png" width="25px" />
                        <span style="float:left; color:#363636; margin-left:0px; font-size: 9px">NOVOS DADOS</span><br>
                        <span style="font-size: 19px; color:green; margin-left:0; float:left;">R$ 650,00</span><br>                      

                        <div style="border-top:1px solid #c7c5c5; text-align: right; margin-top: 7px; margin-right: 8px; width:100%; padding:2px">
                            
                            <span style="color:#363636; margin-left:0px; font-size: 10px; padding: 2px;">
                            NOVO CARD MODELO

                            <img style="margin-left: 0px; margin-left:2px; margin-top:4px;" src="images/cards/up3.png" width="6px" />
                        </span>                     

                        <span style="font-size: 14px; margin-left: 5px; margin-right: 8px; color:green"><?php echo $contas_receber_vencidas ?>

                        </span>     
                        </div>  
                    </a>

              </div>
             
            </div>
        </div>






</div>
</div>




    		<div class="swiper-slide" style="background-image:url(images/slide2.jpg); margin-top:50px;">
    			<div class="slider_trans">		  
    				<div class="slider-caption">
    					<span class="subtitle" data-swiper-parallax="-60%">MULTIPURPOSE DESIGNS</span>
    					<h2 data-swiper-parallax="-100%">WITH BEAUTIFUL</h2>

    					<p data-swiper-parallax="-30%">You can design and create, and build the most wonderful place in the world. But it takes people to make the dream a reality.</p>
    				</div>	
    			</div>	
    		</div>
    		<div class="swiper-slide" style="background-image:url(images/slide3.jpg); margin-top:50px;">
    			<div class="slider_trans">		  
    				<div class="slider-caption">
    					<span class="subtitle" data-swiper-parallax="-60%">WEB AND NATIVE</span>
    					<h2 data-swiper-parallax="-100%">READY FOR APPS</h2>

    					<p data-swiper-parallax="-30%">You can design and create, and build the most wonderful place in the world. But it takes people to make the dream a reality.</p>
    				</div>
    			</div>
    		</div> 		   
    	</div>
    	<div class="swiper-pagination"></div>
    	<div class="swiper-button-prev"></div>
    	<div class="swiper-button-next"></div>	
    </div>





    <div class="swiper-container-toolbar swiper-toolbar">
    	<div class="swiper-pagination-toolbar"></div>
    	<div class="swiper-wrapper">
    		<div class="swiper-slide toolbar-icon">
    			
    			<a class="<?php echo $clientes ?>" href="clientes"><img src="images/icons/blue/user.png" alt="" title="" /><span>CLIENTES</span></a>
                <a class="<?php echo $receber ?>" href="receber"><img src="images/icons/blue/contas.png" alt="" title="" /><span>RECEBIMENTOS</span></a>
                <a class="<?php echo $pagar ?>" href="pagar"><img src="images/icons/blue/financeiro2.png" alt="" title="" /><span>DESPESAS</span></a>
    			<a class="<?php echo $funcionarios ?>" href="funcionarios"><img src="images/icons/blue/lock.png" alt="" title="" /><span>FUNCIONÁRIOS</span></a>
    			<a class="<?php echo $tarefas ?>" href="tarefas"><img src="images/icons/blue/blog.png" alt="" title="" /><span>TAREFAS</span></a>
                <a class="<?php echo $usuarios ?>" href="usuarios"><img src="images/icons/blue/users.png" alt="" title="" /><span>USUÁRIOS</span></a>
    			
    		</div> 
    		<div class="swiper-slide toolbar-icon">
                <a class="<?php echo $formas_pgto ?>" href="formas_pgto"><img src="images/icons/blue/more.png" alt="" title="" /><span>FORMAS PGTO</span></a>
                <a class="<?php echo $frequencias ?>" href="frequencias"><img src="images/icons/blue/blog.png" alt="" title="" /><span>FREQUÊNCIAS</span></a>
    			<a class="<?php echo $cargos ?>" href="cargos"><img src="images/icons/blue/features.png" alt="" title="" /><span>CARGOS</span></a>
    			<a class="<?php echo $grupo_acessos ?>" href="grupo_acessos"><img src="images/icons/blue/tables.png" alt="" title="" /><span>GRUPOS</span></a>
    			<a class="<?php echo $acessos ?>" href="acessos"><img src="images/icons/blue/lock.png" alt="" title="" /><span>ACESSOS</span></a>
    			<a class="<?php echo $fornecedores ?>" href="fornecedores"><img src="images/icons/blue/bike.png" alt="" title="" /><span>FORNECEDORES</span></a>
    			
    		</div>

    	</div>
    </div>	




     		</div>
          </div>
        </div>

      </div>
    </div>