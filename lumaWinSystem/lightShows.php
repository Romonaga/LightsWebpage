<?php

include('commonFunctions.php');

$_SESSION["Delay"] = 10;
$_SESSION["Minutes"] = 1;
$_SESSION["Width"] = 1;
$_SESSION["ColorEvery"] = 2;
$_SESSION["DesignerEditMode"] = 0;

$conn = getDatabaseConnection();

if($_SESSION['authorized'] == 0)
{
  header("Location: registration.php");
  exit();
}



if(!empty($_REQUEST))
{
    $sendArray['UserID'] = $_SESSION['UserID'];
    if(!empty($_POST['SystemName']))
        $_SESSION["LightSystemID"]  = $_POST['SystemName'];


    if(!empty($_POST['Brightness']))
        $_SESSION["Brightness"] = $_POST['Brightness'];

    if(!empty($_POST['Delay']))
        $_SESSION["Delay"] = $_POST['Delay'];

    if(!empty($_POST['Minutes']))
        $_SESSION["Minutes"] = $_POST['Minutes'];

    if(!empty($_POST['Width']))
        $_SESSION["Width"] = $_POST['Width'];

    if(!empty($_POST['ColorEvery']))
        $_SESSION["ColorEvery"] = $_POST['ColorEvery'];

    if(!empty($_POST['ChgBrightness']))
        $_SESSION["ChgBrightness"] = $_POST['ChgBrightness'];

    if(!empty($_POST['ShowName']))
        $_SESSION["ShowName"] = $_POST['ShowName'];

}

if(isset($_REQUEST['Power']))
{

    $onoff = "ON";
    if (empty($_POST['lights']))
      $onoff = "OFF";

    $sendArray['state'] = $onoff;
    sendMQTT(getServerHostName($_SESSION["LightSystemID"]), json_encode($sendArray));

}


if(isset($_REQUEST['btnChgBrightness']))
{

	if(!empty($_POST['ChgBrightness']))
	{

		$_SESSION['ChgBrightness'] = $_POST['ChgBrightness'];
		$sendArray['chgBrightness'] = $_POST['ChgBrightness'];

		sendMQTT(getServerHostName($_SESSION["LightSystemID"]), json_encode($sendArray));
	}

}




if(isset($_REQUEST['ClearQueue']))
{

    $sendArray['clearQueue'] = 1;
    sendMQTT(getServerHostName($_SESSION["LightSystemID"]), json_encode($sendArray));

}




if(isset($_REQUEST['LightShow']))
{

    $r = 5;
    $g = 3;
    $b = 12;


    $sendArray['brightness'] = $_SESSION["Brightness"];

    if(!empty($_POST['ShowName']))
    {


			
        if(isset($_POST['color_1']))
        {
            if($hex != '#000000')
            {
                $hex = $_POST['color_1'];
                list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");


                $color["r"] = $r;
                $color["g"] = $g;
                $color["b"] = $b;
                $sendColors['color1'] = $color;
            }

        }

        if(isset($_POST['color_2']))
        {
            $hex = $_POST['color_2'];
            if($hex != '#000000')
            {
                list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");


                $color["r"] = $r;
                $color["g"] = $g;
                $color["b"] = $b;
                $sendColors['color2'] = $color;
            }

        }

        if(isset($_POST['color_3']))
        {
            $hex = $_POST['color_3'];
            if($hex != '#000000')
            {
                list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");


                $color["r"] = $r;
                $color["g"] = $g;
                $color["b"] = $b;
                $sendColors['color3'] = $color;
            }

        }

       if(isset($_POST['color_4']))
       {
           $hex = $_POST['color_4'];
           if($hex != '#000000')
           {
              list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
               $color["r"] = $r;
               $color["g"] = $g;
               $color["b"] = $b;
               $sendColors['color4'] = $color;
            }

       }

        if(!empty($_POST['ShowName']))
            $sendArray['show'] =  $_POST['ShowName'];

        if(!empty($_POST['Delay']))
            $sendArray['delay'] = $_SESSION['Delay'];

        if(!empty($_POST['Minutes']))
            $sendArray['minutes'] = $_SESSION['Minutes'];

        if(count($sendColors) > 0)
           $sendArray['colors'] = $sendColors;
        
        if (!empty($_POST['clearStart']))
            $sendArray['clearStart'] = 1;

        if (!empty($_POST['Width']))
            $sendArray['width'] = $_POST['Width'];

        if (!empty($_POST['clearFinish']))
            $sendArray['clearFinish'] = 1;
            
        if (!empty($_POST['gammaCorrection']))
			$sendArray['gammaCorrection'] = 1;

        if (!empty($_POST['powerOn']))
           $sendArray['powerOn'] = "OFF";
           
        if (!empty($_POST['ColorEvery']))
           $sendArray['colorEvery'] = $_SESSION["ColorEvery"];
           
        if(!empty($_POST['hasText']))
			$sendArray['matrixText'] = $_POST['hasText'];
			
		if(!empty($_POST['matrixData']))
			$sendArray['pixles'] = json_decode($_POST['matrixData']);
		
        $sendArray['systemId'] = $_SESSION["LightSystemID"];

    }
    
    sendMQTT(getServerHostName($_SESSION["LightSystemID"]), json_encode($sendArray));

}



if(isset($_REQUEST['btnPlaylist']))
{

	if(!empty($_POST['Playlist']))
	{
		$sendArray['playPlaylist'] = 1;
		$sendArray['playlistName'] = intval($_POST['Playlist']);
		$sendArray['UserID'] = intval($_SESSION['UserID']);
		$displayStrip = mysqli_query($conn,"SELECT serverHostName FROM lightSystems WHERE ID = ".$_SESSION["LightSystemID"] );
		$query_data = mysqli_fetch_array($displayStrip);
		sendMQTT($query_data['serverHostName'], json_encode($sendArray));
	}
}






$playlistoption = '';
$results = mysqli_query($conn,"SELECT ID, playlistName FROM userPlaylist where userId =" . $_SESSION['UserID'] . " or userId =1");
if(mysqli_num_rows($results) > 0)
{
    while($row = mysqli_fetch_array($results))
      $playlistoption .="<option value = '".$row['ID']."'>".$row['playlistName']."</option>";

}


$_SESSION['systemlistoption'] = '';
$_SESSION['lightSystemsScript'] = '';

$systemResults = mysqli_query($conn,"SELECT * FROM lightSystems where userId =" . $_SESSION['UserID'] . " or userId = 1");
if(mysqli_num_rows($systemResults) > 0)
{
    $_SESSION['lightSystemsScript'] = "let systemsMap = new Map();\r\n";
    while($systemRow = mysqli_fetch_array($systemResults))
    {
		if($_SESSION['LightSystemID'] == -1)
			$_SESSION['LightSystemID'] = $systemRow['ID'];
		
		$_SESSION['lightSystemsScript'] .= "var system = new Object(); \r";

        $_SESSION['lightSystemsScript'] .= "    system.id = " . $systemRow['ID'] . ";\r";
        $_SESSION['lightSystemsScript'] .= "    system.systemName = '" . $systemRow['systemName'] . "';\r";
        $_SESSION['lightSystemsScript'] .= "    system.serverHostName = '" . $systemRow['serverHostName'] . "';\r";
        $_SESSION['lightSystemsScript'] .= "    system.enabled = " . $systemRow['enabled'] . ";\r";
        $_SESSION['lightSystemsScript'] .= "    system.userId = " . $systemRow['userId'] . ";\r";
        $_SESSION['lightSystemsScript'] .= "    system.twitchSupport = " . $systemRow['twitchSupport'] . ";\r";
		$_SESSION['lightSystemsScript'] .= "    system.mqttRetries = " . $systemRow['mqttRetries'] . ";\r";
		$_SESSION['lightSystemsScript'] .= "    system.mqttRetryDelay = " . $systemRow['mqttRetryDelay']   .";\r";
		$_SESSION['lightSystemsScript'] .= "    system.twitchMqttQueue = '" . $systemRow['twitchMqttQueue'] ."';\r";
		$_SESSION['lightSystemsScript'] .= "    system.channelsMap = new Map();\r";
		$_SESSION['lightSystemsScript'] .= "    system.featuresMap = new Map();\r";
		
		$channelResults = mysqli_query($conn,"SELECT * FROM lightSystemChannels where lightSystemId = " . $systemRow['ID'] . ";");
		
		if(mysqli_num_rows($channelResults) > 0)
		{
			
			while($channelRow = mysqli_fetch_array($channelResults))
			{
				$_SESSION['lightSystemsScript'] .= "var channel = new Object(); \r";
				$_SESSION['lightSystemsScript'] .= "    channel.channelId = " . $channelRow['channelId'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.stripType = " . $channelRow['stripType'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.stripColumns = " . $channelRow['stripColumns'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.stripRows = " . $channelRow['stripRows'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.dma = " . $channelRow['dma'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.gpio = " . $channelRow['gpio'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.brightness = " . $channelRow['brightness'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.gamma = " . $channelRow['gamma'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    channel.enabled = " . $channelRow['enabled'] .";\r";
				$_SESSION['lightSystemsScript'] .= "system.channelsMap.set(" . $channelRow['channelId'] . ", channel);\r";
				
			}
		}
		
		$featureResults = mysqli_query($conn,"SELECT * FROM lightSystemFeatures where lightSystemId = " . $systemRow['ID'] . ";");
		if(mysqli_num_rows($featureResults) > 0)
		{
			while($featureRow = mysqli_fetch_array($featureResults))
			{
				$_SESSION['lightSystemsScript'] .= "var lightFeature = new Object(); \r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.featureId = " . $featureRow['featureId'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.featureGpio = " . $featureRow['featureGpio'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.featurePlayList = " . $featureRow['featurePlayList'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.motionDelayOff = " . $featureRow['motionDelayOff'] .";\r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.timeFeatureStart = '" . $featureRow['timeFeatureStart'] ."';\r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.timeFeatureEnd = '" . $featureRow['timeFeatureEnd'] ."';\r";
				$_SESSION['lightSystemsScript'] .= "    lightFeature.luxThreshHold = " . $featureRow['luxThreshHold'] .";\r";
				$_SESSION['lightSystemsScript'] .= "system.featuresMap.set(" . $featureRow['featureId']  . ", lightFeature);\r";
				
			}
		}
		

	
        $_SESSION['lightSystemsScript'] .= "systemsMap.set(" . $systemRow['ID'] . ", system);\r";

        if($systemRow['ID'] == $_SESSION["LightSystemID"] )
            $_SESSION['systemlistoption'] .="<option value = '".$systemRow['ID']."' selected>". $systemRow['systemName']."</option>";
        else
            $_SESSION['systemlistoption'] .="<option value = '".$systemRow['ID']."'>". $systemRow['systemName']."</option>";

    }
    
   
}


$conn->close();

?>

<!doctype html>
<?php 
include('header.php'); 
?>


<body onload="initShowSystem();">
<?php include("nav.php");  ?>


<script>



<?php echo $_SESSION['lightSystemsScript'];?>

	function initShowSystem()
	{
		setSystemSettings();
		setShowSettings();
		
	}

    function setSystemSettings()
    {
		
        var widthId  = document.getElementById("WidthId");
        var widthOutput = document.getElementById("WidthValue");
        var chgBrightnessId = document.getElementById("ChgBrightnessId");
        var brightness = document.getElementById("Brightness");


        var systemNameId = document.getElementById("SystemNameId");
        var index = parseInt(systemNameId.value);
        var system = systemsMap.get(index);
        
        var numLeds = system.channelsMap.get(1).stripRows * system.channelsMap.get(1).stripColumns;
        if(widthId.value > numLeds)
        {
            widthId.setAttribute('value', numLeds);
            widthId.value = numLeds;
            widthOutput.innerHTML = numLeds;

        }

        widthId.setAttribute('max', numLeds);
        widthId.max = numLeds;

		chgBrightnessId.value = system.channelsMap.get(1).brightness;
		brightness.value = system.channelsMap.get(1).brightness;
		//setShowSettings();

    }


</script>

<div class="clearfix">
<div class="column twenty-five">
	
	

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<center><img src="System-Control.png" id="systemControlPic" alt="System Control"/></center>
    <p><label for="SystemName">System Name:</label><br />
    <select id="SystemNameId" name="SystemName" onChange="setSystemSettings();">
        <?php echo $_SESSION['systemlistoption'];?>
        </select>
    </p>
    <p><label for="ChgBrightness">Change Brightness:</label>
        <input type="number" value="<?php echo $_SESSION["ChgBrightness"];?>" id="ChgBrightnessId" name="ChgBrightness" min="1" max="255">
        <button type="submit" name="btnChgBrightness">Change</button>
    </p>
        <label for="On">On</label>
    <input type="checkbox" name="lights"  value="ON" checked><button type="submit" name="Power">Power</button>
		<p>

	<script>

	function setPlaylistName()
	{
		var playlistName = document.getElementById("PlayListNameId");
		var playListId = document.getElementById("PlayListId");
		var selectedText = playListId.options[playListId.selectedIndex].text;
		playlistName.value = selectedText;


	}
	</script>
	<label>Playlist</label>
	<select id="PlayListId"  name="Playlist" onChange="setPlaylistName();"><?php echo $playlistoption;?></select>
	<p><button type="submit" name="btnPlaylist">Play</button>
	<button onclick="location.href='editShows.php'; return false" name="btnEditist">Editor</button></p>
			
		</p>
		
	</div>
		





<?php include_once('showDesigner.php'); ?>

 </form>
    
    
	<?php include('footer.php'); ?>
	
	
	
</body>
</html>

