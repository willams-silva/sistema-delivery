<?php 
@session_start();
if(@$_SESSION['id'] == ""){
	echo "<script>window.location='../index.php'</script>";
	exit();
}


if(@$_SESSION['aut_token_8952'] != "fdsfdsafda885222"){
	echo "<script>window.location='../index.php'</script>";
	exit();
}

 ?>