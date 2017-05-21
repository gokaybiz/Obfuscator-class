<?php
$p = @$_GET["p4ge"];
if (!empty($p)) {
	switch ($p) {
		case 'test':
			$g = "deneme";
			break;
		
		default:
			$g = 404;
			break;
	}
	if ($g == 404) die(include("partials/pages/$g.php"));
	$g = "partials/pages/$g.php";
	if (!is_file($g)) exit("Sayfa tanimlanmis ama dosya yok");
}else
	$g = "partials/pages/home.php";
$g = (new Obfucator($g))->encode();
?>