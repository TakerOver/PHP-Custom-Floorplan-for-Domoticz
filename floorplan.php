<?php $start=microtime(true);require "secure/settings.php";require "secure/functions.php";if(authenticated){
error_reporting(E_ALL);
ini_set("display_errors","on");
echo '
<html>
	<head>
		<title>Floorplan</title>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
		<meta name="HandheldFriendly" content="true"/>
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="viewport" content="width=device-width,height=device-height,user-scalable=yes,minimal-ui"/>
		<meta name="msapplication-TileColor" content="#000000">
		<meta name="msapplication-TileImage" content="images/domoticzphp48.png">
		<meta name="msapplication-config" content="/browserconfig.xml">
		<link rel="manifest" href="/manifests/floorplan.json">
		<meta name="theme-color" content="#000000">
		<link rel="icon" type="image/png" href="images/domoticzphp48.png"/>
		<link rel="shortcut icon" href="images/domoticzphp48.png"/>
		<link rel="apple-touch-startup-image" href="images/domoticzphp450.png"/>
		<link rel="apple-touch-icon" href="images/domoticzphp48.png"/>
		<link rel="stylesheet" type="text/css" href="/styles/floorplan.php">
	</head>
	<body class="floorplan">
		';
if(isset($_POST['Schakel']))
	Schakel($_POST['Schakel'],$_POST['Actie'],$_POST['Naam']);
elseif(isset($_POST['Udevice']))
	Udevice($_POST['Udevice'],$_POST['Naam']);
elseif(isset($_POST['dimmer']))
{
	if(isset($_POST['dimlevelon_x']))
	{
		Dim($_POST['dimmer'],100,$_POST['Naam']);
		cset('timedimmer'.$_POST['Naam'],$time);
		cset('dimmer'.$_POST['Naam'],0);
	}
	elseif(isset($_POST['dimleveloff_x']))
	{
		Dim($_POST['dimmer'],0,$_POST['Naam']);
		cset('timedimmer'.$_POST['Naam'],$time);
		cset('dimmer'.$_POST['Naam'],0);
	}
	elseif(isset($_POST['dimsleep_x']))
	{
		lg('>>> '.$user.' activated dimmer sleep for '.$_POST['Naam']);
		cset('dimmer'.$_POST['Naam'],1);
	}
	elseif(isset($_POST['dimwake_x']))
	{
		lg('>>> '.$user.' activated dimmer wake for '.$_POST['Naam']);
		Dim($_POST['dimmer'],$_POST['dimwakelevel']+1,$_POST['Naam']);
		cset('dimmer'.$_POST['Naam'],2);
	}
	else
	{
		Dim($_POST['dimmer'],$_POST['dimlevel'],$_POST['Naam']);
		cset('dimmer'.$_POST['Naam'],0);
	}
}
elseif(isset($_POST['Naam']))
{
	switch($_POST['Naam'])
	{
		case 'radioluisteren':
			Udevice(193,0,'On','radio luisteren');
			break;
		case 'tvkijken':
			Udevice(191,0,'On','tv kijken');
			break;
		case 'kodikijken':
			Udevice(192,0,'On','kodi kijken');
			break;
	}
	//if(Schakel($idx,'On',$_POST['Naam'])=='ERROR')echo '<div class="message" class="balloon">Scene '.$_POST['Naam'].' activeren'.'<br/>ERROR</div>';
}
$weer=unserialize(cget('weer'));
$domostart=microtime(true);
$domoticz=json_decode(curl('http://127.0.0.1:8084/json.htm?type=devices&plan=2',true,$ctx),true);
$domotime=microtime(true)-$domostart;
if($domoticz){
	foreach($domoticz['result'] as $dom)
	{
		$name=$dom['Name'];
		isset($dom['SwitchType'])
			?$SwitchType=$dom['SwitchType']
			:'none';
		if($SwitchType=='Dimmer')
		{
			${'DI'.$name}=$dom['idx'];
			$dom['Status']=='Off'?${'D'.$name}='Off':${'D'.$name}='On';
			$dom['Status']=='Off'?${'Dlevel'.$name}=0:${'Dlevel'.$name}=$dom['Level'];
		}
		else
		{
			${'S'.$name}=$dom['Data'];
			${'SI'.$name}=$dom['idx'];
			${'ST'.$name}=strtotime($dom['LastUpdate']);
			if($name=='achterdeur')
				$Sachterdeur=='Open'
					?$Sachterdeur='Closed'
					:$Sachterdeur='Open';
		}
	}
if(isset($_POST['Schakel']))
{
	if($_POST['Schakel']==1||$_POST['Schakel']==2)
	{
		if($Sraamliving=='Open')
			echo '<script language="javascript">alert("WARNING:Raam living open!")</script>';
		if($Sachterdeur=='Open')
			echo '<script language="javascript">alert("WARNING:Achterdeur open!")</script>';
		if($Spoort=='Open')
			echo '<script language="javascript">alert("WARNING:Poort open!")</script>';
	}
}
echo '
	<div class="fix clock">
		<a href=\'javascript:navigator_Go("floorplan.php");\'>'.strftime("%k:%M:%S",$time).'</a>
	</div>
	<div class="fix buiten_temp">';
$temp=$weer['buiten_temp'];
$hoogte=$temp*3;
if($hoogte>88)
	$hoogte=88;
elseif($hoogte<20)
	$hoogte=20;
$top=88-$hoogte;
if($top<0)
	$top=0;
$top=$top+5;
switch($temp)
{
	case $temp>=22:
		$tcolor='F00';
		$dcolor='55F';
		break;
	case $temp>=20:
		$tcolor='D12';
		$dcolor='44F';
		break;
	case $temp>=18:
		$tcolor='B24';
		$dcolor='33F';
		break;
	case $temp>=15:
		$tcolor='93B';
		$dcolor='22F';
		break;
	case $temp>=10:
		$tcolor='64D';
		$dcolor='11F';
		break;
	default:
		$tcolor='55F';
		$dcolor='00F';
}
echo '
		<div class="fix z" style="top:0px;left:20px;">
			<div class="fix tmpbg" style="top:'.number_format($top,0).'px;left:8px;width:26px;height:'.number_format($hoogte,0).'px;background:linear-gradient(to bottom, #'.$tcolor.', #'.$dcolor.');">
			</div>
			<a href=\'javascript:navigator_Go("temp.php?sensor=999");\'>
				<input type="image" src="/images/temp.png" height="100px" width="auto"/>
			</a>
			<div class="fix center" style="top:73px;left:5px;width:32px;">
				'.$temp.'
			</div>
		</div>
	</div>
	<div class="fix center zon">
		'.number_format((filter_var($Szon, FILTER_SANITIZE_NUMBER_INT)/100),0,'.','.').' Watt<br/>
		Regen:<br>';
		$regen=$weer['buien'];if($regen==0)echo 0;elseif($regen>=10)echo number_format($regen,1);else echo number_format($regen,2);
		echo ' mm/u<br/>
		'.number_format($weer['wind'],0).'kph '.$weer['wind_dir'].'
	</div>
	<div class="fix weather">
		<a href=\'javascript:navigator_Go("http://www.wunderground.com/global/stations/06414.html");\'>
			<img src="'.$weer['icon'].'"/>
		</a>
	</div>
	<div class="fix denonicon">
		<a href=\'javascript:navigator_Go("denon.php");\'>
			<img src="/images/denon.png" class="i48">
		</a>
	</div>
	<div class="fix radioluisteren">
		<form method="POST">
			<input type="hidden" name="Naam" value="radioluisteren">
			<input type="image" src="/images/Amp_';echo $Sdenon=='On'?'On':'Off';echo '.png" class="i70">
		</form>
	</div>
	<div class="fix tvkijken">
		<form method="POST">
			<input type="hidden" name="Naam" value="tvkijken">
			<input type="image" src="/images/TV_';echo $Stv=='On'?'On':'Off';echo '.png" class="i60">
		</form>
	</div>
	<div class="fix kodikijken">';
	echo $Skodi=='Off'?'
		<form method="POST">
			<input type="hidden" name="Naam" value="kodikijken">
			<input type="image" src="/images/Kodi_Off.png" class="i48">
		</form>'
	:'
	<form action="kodi.php">
		<input type="image" src="/images/Kodi_On.png" class="i48">
	</form>
	';
	echo '</div>
	<div class="fix heatingicon">
		<a href=\'javascript:navigator_Go("heating.php");\'>
			<img src="/images/Fire_';echo $Sbrander=='On'?'On':'Off';echo '.png" class="i48">
		</a>
	</div>
	<div class="fix floorplan2icon">
		<a href=\'javascript:navigator_Go("floorplan2.php");\' onclick="toggle_visibility(\'Plus\');">
			<img src="/images/plus.png" class="i60"/>
		</a>
	</div>';
Dimmer('tobi');
Dimmer('zithoek');
Dimmer('eettafel');
Dimmer('kamer');
Dimmer('alex');
Schakelaar('tvled','Light');
Schakelaar('kristal','Light');
Schakelaar('bureel','Light');
Schakelaar('inkom','Light');
Schakelaar('keuken','Light');
Schakelaar('wasbak','Light');
Schakelaar('kookplaat','Light');
Schakelaar('werkblad','Light');
Schakelaar('lichtbadkamer1','Light');
Schakelaar('lichtbadkamer2','Light');
Schakelaar('voordeur','Light');
Schakelaar('hall','Light');
Schakelaar('garage','Light');
Schakelaar('zolderg','Light');
Schakelaar('weg','Home');
Schakelaar('slapen','Sleepy');
Schakelaar('terras','Light');
Schakelaar('tuin','Light');
Schakelaar('zolder','Light');
Schakelaar('bureeltobi','Plug');
Schakelaar('badkamervuur','Plug');
Schakelaar('kerstboom','Kerstboom');
Thermometer('living_temp');
Thermometer('badkamer_temp');
Thermometer('kamer_temp');
Thermometer('tobi_temp');
Thermometer('alex_temp');
Thermometer('zolder_temp');
Blinds('zoldertrap');
if($Sweg=='On'||$Sslapen=='On'){Secured('zliving');Secured('zkeuken');Secured('zinkom');Secured('zgarage');}
if($Sweg=='On'){Secured('zhalla');Secured('zhallb');}
if($Spirliving!='Off'||$Spirliving!='Off')Motion('zliving');
if($Spirkeuken!='Off')Motion('zkeuken');
if($Spirinkom!='Off')Motion('zinkom');
if($Spirgarage!='Off')Motion('zgarage');
if($Spirhall!='Off'){Motion('zhalla');Motion('zhallb');}
if($STbelknop>$eendag)Timestamp('belknop',270);
if($STpirgarage>$eendag)Timestamp('pirgarage',0);
if($STpirliving>$eendag)Timestamp('pirliving',0);
if($STpirlivingR>$eendag)Timestamp('pirlivingR',0);
if($STpirkeuken>$eendag)Timestamp('pirkeuken',0);
if($STpirinkom>$eendag)Timestamp('pirinkom',0);
if($STpirhall>$eendag)Timestamp('pirhall',0);
if($STachterdeur>$eendag)Timestamp('achterdeur',270);
if($STpoort>$eendag)Timestamp('poort',90);
if($STraamliving>$eendag)Timestamp('raamliving',270);
if($STraamtobi>$eendag)Timestamp('raamtobi',270);
if($STraamalex>$eendag)Timestamp('raamalex',270);
if($STraamkamer>$eendag)Timestamp('raamkamer',90);
if($STdeurbadkamer>$eendag)Timestamp('deurbadkamer',90);
if($Spoort!='Closed')
	echo '
	<div class="fix poort">
	</div>';
if($Sachterdeur!='Closed')
	echo '
	<div class="fix achterdeur">
	</div>';
if($Sraamliving!='Closed')
	echo '
	<div class="fix raamliving">
	</div>';
if($Sraamtobi!='Closed')
	echo '
	<div class="fix raamtobi">
	</div>';
if($Sraamalex!='Closed')
	echo '
	<div class="fix raamalex">
	</div>';
if($Sraamkamer!='Closed')
	echo '
	<div class="fix raamkamer">
	</div>';
if($Sdeurbadkamer!='Closed')
	echo '
	<div class="fix deurbadkamer">
	</div>';
$total=microtime(true)-$start;
echo '
	<div class="fix floorplanstats">
		'.$udevice.' | D  '.number_format(($domotime*1000),1).' | P  '.number_format((($total-$domotime)*1000),1).' | T  '.number_format(($total*1000),1).'
	</div>';
if(isset($_REQUEST['setdimmer']))
{
	$name=$_REQUEST['setdimmer'];
	echo '
	<div id="D'.$name.'" class="fix dimmer" >
		<form method="POST" action="floorplan.php" oninput="level.value = dimlevel.valueAsNumber">
				<div class="fix z" style="top:15px;left:90px;">
					<h2>'.ucwords($name).': '.round(${'Dlevel'.$name},0).'%</h2>
					<input type="hidden" name="Naam" value="'.$name.'">
					<input type="hidden" name="dimmer" value="'.${'DI'.$name}.'">
				</div>
				<div class="fix z" style="top:100px;left:30px;">
					<input type="image" name="dimleveloff" value ="0" src="images/Light_Off.png" class="i90"/>
				</div>
				<div class="fix z" style="top:100px;left:150px;">
					<input type="image" name="dimsleep" value ="100" src="images/Sleepy.png" class="i90"/>
				</div>
				<div class="fix z" style="top:100px;left:265px;">
					<input type="image" name="dimwake" value="100" src="images/Wakeup.png" style="height:90px;width:90px"/>
					<input type="hidden" name="dimwakelevel" value="'.${'Dlevel'.$name}.'">
				</div>
				<div class="fix z" style="top:100px;left:385px;">
					<input type="image" name="dimlevelon" value ="100" src="images/Light_On.png" class="i90"/>
				</div>
				<div class="fix z" style="top:210px;left:10px;">';
			$levels=array(1,2,3,4,5,6,7,8,9,10,12,14,16,18,20,22,24,26,28,30,32,35,40,45,50,55,60,65,70,75,80,85,90,95,100);
			foreach($levels as $level)
			{
				if(${'Dlevel'.$name}==$level)
					echo '
					<input type="submit" name="dimlevel" value="'.$level.'"/ class="dimlevel dimlevela">';
				else
					echo '
					<input type="submit" name="dimlevel" value="'.$level.'" class="dimlevel"/>';
			}
			echo '
				</div>
			</form>
			<div class="fix z" style="top:5px;left:5px;">
				<a href=\'javascript:navigator_Go("floorplan.php");\'>
					<img src="/images/close.png" width="72px" height="72px"/>
				</a>
			</div>
		</div>
	</body>
	<script type="text/javascript">
		function navigator_Go(url){
			window.location.assign(url);
		}
	</script>
</html>';
		exit;
	}
}else
	echo '<div><br/><br/><br/><a href=""><h1>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Geen verbinding met Domoticz</h1></a></div>';
}
else
{
	header("Location: index.php");
	die("Redirecting to: index.php");
}
?>

		<script type="text/javascript">
			function toggle_visibility(id){
				var e=document.getElementById(id);
				if(e.style.display=='inherit') e.style.display='none';
				else e.style.display='inherit';
			}
			setTimeout('window.location.href=window.location.href;',4963);
			function navigator_Go(url){
				window.location.assign(url);
			}
		</script>
	</body>
</html>
