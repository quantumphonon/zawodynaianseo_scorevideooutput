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

require_once(dirname(dirname(__FILE__)) . '/config.php');

include 'IanseoVideoGraphics_functions.php';

$tour_name = $_GET['tour'];
$tour_id = get_tour_id($tour_name);

include 'IanseoVideoGraphics_Score.php';
