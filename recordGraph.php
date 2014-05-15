<?php

// db config.php
require("dbconfig.php");

// Standard inclusions 
include("jpgraph/src/jpgraph.php");  
include("jpgraph/src/jpgraph_line.php");
include("jpgraph/src/jpgraph_date.php");
include("jpgraph/src/jpgraph_utils.inc.php");

//Get parameters from URL or establish defaults
//Graph Title
if(isset($_GET['title']) && $_GET['title']!=""){
	$Title = $_GET['title'];
} else {
	//default to company name.
	$Title = "";
}
//Device to graph temperatures for
if(isset($_GET['device']) && $_GET['device']!=""){
	$Device = $_GET['device'];
} else {
	if(isset($_POST['Device']) && $_POST['Device']!=""){
		$Device = $_POST['Device'];
	} else {
		//default to large process "Grieve" oven
		$Device = "grieve";
	}
}

// connect to the MySQL database server
$mysqli = new mysqli($dbhost, $dbuser, $dbpassword, $database);
if($mysqli->connect_errno){
    die("Connection Error: " . $mysqli->connect_errno);
}
// the actual query for the select data
$SQL = "SELECT * FROM record_details WHERE id='$Device'";
$result = $mysqli->query( $SQL );
if (!$result) {
    die("Couldn't execute query.".$mysqli->error);
}
$row = $result->fetch_array(MYSQL_ASSOC);
if(!$row){
    echo "No record_detail found for id = $id.";
    die("No record_detail found for id = $id.");
}
$record = $row["record_type_name"];
$name = $row["name"];
$description = $row["description"];
$unit_name = $row["unit_name"];
$unit_description = $row["unit_description"];
$graph_y_lower = $row["graph_y_lower"];
$graph_y_upper = $row["graph_y_upper"];


//round time to nearest 15 minutes
$time = floor(time()/(15*60))*15*60;
//Get starting, ending time
if(isset($_GET['beg']) && $_GET['beg']!=""){
	$BegTime = $_GET['beg'];
} else {
	if(isset($_POST['BegTime']) && $_POST['BegTime']!=""){
		$BegTime = $_POST['BegTime'];
	} else {
		//default to the last ?? hours to graph
		$BegTime = strftime("%Y-%m-%d %H:%M:%S",$time-(12*60*60));
	}
}
if(isset($_GET['end']) && $_GET['end']!=""){
	$EndTime = $_GET['end'];
} else {
	if(isset($_POST['EndTime']) && $_POST['EndTime']!=""){
		$EndTime = $_POST['EndTime'];
	} else {
		//default to current time
		$EndTime = strftime("%Y-%m-%d %H:%M:%S",$time);
	}
}

$timeBeg = strtotime($BegTime);
$timeEnd = strtotime($EndTime);
$BegTime = strftime("%Y/%m/%d %H:%M:%S",$timeBeg-30);
$EndTime = strftime("%Y/%m/%d %H:%M:%S",$timeEnd+30);

$TotalTime=$timeEnd-$timeBeg;
//Error check too much or too little data
if ($TotalTime>35*24*60*60){
	echo 'Can not graph more than 35 days<br>';
	die('Can not graph more than 35 days');
}
if ($TotalTime<15*60){
	//echo 'Can not graph less than 15 minutes<br>';
	//die('Can not graph less than 15 minutes');
	$timeBeg = $timeEnd-15*60;
        $BegTime = strftime("%Y/%m/%d %H:%M:%S",$timeBeg-30);
	$TotalTime = $timeEnd-$timeBeg;
}

$num = $TotalTime / 60; //number of data points on graph
$TxtEndTime = strftime("%Y/%m/%d %I:%M%p",$timeEnd);
$TxtBegTime = strftime("%Y/%m/%d %I:%M%p",$timeBeg);

//read mysql table
$records = $mysqli->query(
	"SELECT $record, created_at FROM $database.$record ".
	"WHERE created_at > '$BegTime'".
	"AND created_at < '$EndTime'".
	"AND record_id = '$Device'".
	"ORDER BY created_at;"
        );
if (!$records){
    echo 'Query not valid: ' . mysql_error();
    die('Query not valid: ' . mysql_error());
}

$mysqli->close();
        
if($num>1){
    $maxRecVal = 0;
    $recVal = null;
    $row = $records->fetch_array(MYSQL_ASSOC);
    $rowTime = strtotime($row["created_at"]);
    for ($i=0; $i<=$num && $row; $i++){
        $recVal = $row[$record];
        if ($recVal>$maxRecVal) $maxRecVal = $recVal;
        while(($timeBeg+$i*60)>$rowTime && $row){
            $row = $records->fetch_array(MYSQL_ASSOC);
            $rowTime = strtotime($row["created_at"]);
        }
        if (!$row) $rowTime = $timeBeg+$i*60;
        if(($timeBeg+$i*60)<=$rowTime){
            $recVals[$i] = $recVal;
            $dayOfMonth = strftime("%e",$timeBeg+$i*60);
            switch ($dayOfMonth) {
                case ($dayOfMonth=="1")||($dayOfMonth=="21")||($dayOfMonth=="31"):
                      $dayOfMonth .= "st";
                    break;
                case ($dayOfMonth=="2")||($dayOfMonth=="22"):
                      $dayOfMonth .= "nd";
                    break;
                case ($dayOfMonth=="3")||($dayOfMonth=="23"):
                      $dayOfMonth .= "rd";
                    break;
                default:
                      $dayOfMonth .= "th";
                    break;
            }
            $Labels[$i] = $dayOfMonth.strftime(" %k:%M",$timeBeg+$i*60);
        }
    }
    if ($i>=1) {
        if ($maxRecVal>$graph_y_upper){
            $maxRecVal = $graph_y_upper;
        } else {
            $maxRecVal *= 1.2;
        }
        unlink("TempChart.jpg");
        // Initialise the graph  
        $graph = new Graph(1000,700); 
        $graph->SetScale('datlin',$graph_y_lower,$maxRecVal);
        $lineplot = new LinePlot($recVals);
        $graph->SetMargin(80,30,60,100);
        $graph->title->Set($Title);
        $graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
        $graph->subtitle->Set("Record for ".$name." - ".$description,"middle");
        $graph->subtitle->SetFont(FF_ARIAL,FS_BOLD,10);
        $graph->subsubtitle->Set("from $TxtBegTime to $TxtEndTime","middle");
        $graph->subsubtitle->SetFont(FF_ARIAL,FS_NORMAL,10);
        $graph->xaxis->SetTitleMargin(60);
        $graph->xaxis->SetTitle("Time Recorded","middle");
        $graph->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
        $graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
        $graph->xaxis->SetTickLabels($Labels);
        $graph->xaxis->SetLabelAngle(90);
        $graph->xaxis->HideFirstLastLabel();
        $graph->yaxis->SetTitleMargin(40);
        if($record=="temperature"){
            $graph->yaxis->SetTitle("$record ".chr(176)."$unit_name","middle");
        } else {
            $graph->yaxis->SetTitle("$unit_description","middle");
        }
        $graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
        $graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,9);
        $graph->ygrid->Show();
        $graph->xgrid->Show();
        $graph->Add($lineplot);
        $lineplot->SetColor('red');
        $graph->Stroke("TempChart.jpg");
        echo '<img src="TempChart.jpg">';
    }
} else {
    echo "No temperatures recorded for ".$Device." in the time between ".$BegTime." and ".$EndTime." for '".$Device."'.";
    die("No temperatures recorded for ".$Device." in the time between ".$BegTime." and ".$EndTime." for '".$Device."'.");
}
function setGraph(){
    global $graphTime;
    global $recordTime;
    global $recVal;
    global $recVals;
    global $Labels;
    global $i;
    $graphTime += 60;
    $recVals[$i] = $recVal;
    $Labels[$i] = strftime("%m-%d  %H:%M",$recordTime);
}
?>
