<?php

define('INSTALLER_MAX_STEP', 6);

define('INSTALLER_TYPE_CREATE', 0);
define('INSTALLER_TYPE_MAP', 1);

$installerStepTitles = array(
	'Requirements',
	'Database connection',
	'Database schema',
	'Your first admin user',
	'General settings',
	'Optional features',
	'Finish installation',
);

$installerStepMapping = array(
	0 => 0,
	1 => 1,
	2 => 2,
	3 => 2,
	4 => 3,
	5 => 4,
	6 => 5,
	7 => 6,
);

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

function installer_prev($thisStep, $stepSize = 1)
{
	$s = ($thisStep < 0) ? 0 : ($thisStep - $stepSize);

	$_SESSION['installer']['lastStep'] = $thisStep;
	$_SESSION['installer']['step'] = $s;

	Router::redirect('/?step='.$s);
}

function installer_next($thisStep, $stepSize = 1)
{
	$s = ($thisStep > 8) ? 8 : ($thisStep + $stepSize);

	$_SESSION['installer']['lastStep'] = $thisStep;
	$_SESSION['installer']['step'] = $s;

	Router::redirect('/?step='.$s);
}

if(!isset($_SESSION['installer'])){
	installer_reset();
}

$step = (isset($_GET['step']) && is_numeric($_GET['step'])) ? intval($_GET['step']) : 0;

echo '<h1>Installation of WebMUM</h1>';

if($step > 0){
?>
<ol style="font-size: 1.1em;">
<?php for($s = 1; $s <= INSTALLER_MAX_STEP; $s++): ?>
	<li>
	<?php if(isset($installerStepMapping[$step]) && $s < $installerStepMapping[$step]): ?>
		<span style="color: #999;"><?php echo $installerStepTitles[$s]; ?></span>
	<?php elseif(isset($installerStepMapping[$step]) && $s === $installerStepMapping[$step]): ?>
		<strong><?php echo $installerStepTitles[$s]; ?></strong>
	<?php else: ?>
		<?php echo $installerStepTitles[$s]; ?>
	<?php endif; ?>
	</li>
<?php endfor; ?>
</ol>
<?php
}

try{
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
