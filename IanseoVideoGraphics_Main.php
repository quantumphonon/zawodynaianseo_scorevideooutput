<html>
<head>
<style>
<?php include 'IanseoVideoGraphics_Style.css';?>
</style>
</head>
</body>

<?php
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 1; URL=$url1");

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

include 'IanseoVideoGraphics_functions.php';

$tour_name = $_GET['tour'];
$tour_id = get_tour_id($tour_name);
//$select = "SELECT * FROM ianseographics";
//$rs = safe_r_sql($select);
//if($row = $rs->fetch_assoc()){
    //$graphics_type = $row['outputtype'];
//}
$graphics_type = 'score';
if($graphics_type == 'score'){
    include 'IanseoVideoGraphics_Score.php';
}
if($graphics_type == 'bracket'){
    include 'IanseoVideoGraphics_Bracket.php';
}
if($graphics_type == 'presentation'){
    include 'IanseoVideoGraphics_Presentation.php';
}
if($graphics_type == 'winner'){
    include 'IanseoVideoGraphics_Winner.php';
}
if($graphics_type == 'presentation_reverse'){
    include 'IanseoVideoGraphics_Presentation_reverse.php';
}