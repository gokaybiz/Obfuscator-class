<?php
$p = @$_GET["p4ge"];

switch ($p) {
	case 'test':
		$g = "deneme";
		break;
	
	default:
		$g = 404;
		if (empty($p))
			$g = "home";
		break;
}
if ($g == 404) die(include("partials/pages/err/$g.php"));

$g = "partials/pages/$g.php";
if (!is_file($g)) exit("Sayfa tanimlanmis ama dosya yok");

$g = (new Obfucator($g))->encode();
?>
