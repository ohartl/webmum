<?php

function installer_reset()
{
	global $_SESSION;

	$_SESSION['installer'] = array(
		'lastStep' => 0,
		'step' => 0,
		'config' => array(),
	);
}

function installer_message($setMessage = null)
{
	global $_SESSION;

	if(!is_null($setMessage)){
		$_SESSION['installer']['message'] = $setMessage;
	}
	elseif(isset($_SESSION['installer']['message'])){
		$m = '<div class="notification notification-success">'.$_SESSION['installer']['message'].'</div>';
		unset($_SESSION['installer']['message']);

		return $m;
	}

	return $setMessage;
}

function installer_prev($thisStep)
{
	$s = ($thisStep < 0) ? 0 : ($thisStep - 1);

	$_SESSION['installer']['lastStep'] = $thisStep;
	$_SESSION['installer']['step'] = $s;

	Router::redirect('/?step='.$s);
}

function installer_next($thisStep)
{
	$s = ($thisStep > 8) ? 8 : ($thisStep + 1);

	$_SESSION['installer']['lastStep'] = $thisStep;
	$_SESSION['installer']['step'] = $s;

	Router::redirect('/?step='.$s);
}

if(!isset($_SESSION['installer'])){
	installer_reset();
}

?>
<h1>Installation of WebMUM</h1>
<?php

try{
	$step = (isset($_GET['step']) && is_numeric($_GET['step'])) ? intval($_GET['step']) : 0;

	$stepFile = __DIR__.'/step'.$step.'.php';
	if(file_exists($stepFile)){
		include_once $stepFile;
	}
	else{
		installer_reset();
		echo 'Wizard step '.$step.' is missing.';
	}
}
catch(Exception $e){
	echo $e->getMessage();
}
