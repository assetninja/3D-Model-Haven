<?php
include ($_SERVER['DOCUMENT_ROOT'].'/php/functions.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Schedule / History</title>
    <link href='/css/style.css' rel='stylesheet' type='text/css' />
    <link href='/css/admin.css' rel='stylesheet' type='text/css' />
    <link href="https://fonts.googleapis.com/css?family=PT+Mono" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>

    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
</head>
<body>

<div id="product-list">
<div id="page-wrapper" style="text-align: center">

<h1>Schedule / History</h1>

<?php
echo "<h2>Today: ".date("D d M Y", time())."</h2>";

// $db = get_from_db("date_published", "all", "all", "all", NULL, 0);
$sql = "SELECT * FROM models ORDER BY date_published DESC";
$conn = db_conn_read_only();
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo ("Could not select the data : " . mysqli_errno($conn));
}
$array = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $array[$row['name']] = $row;
    }
}

$current_month = "";
foreach ($array as $info){
    $m = date("m Y", strtotime($info['date_published']));
    if ($m != $current_month){
        if ($current_month != ""){
            echo "</table>\n";
        }
        echo "\n<table cellspacing=0 style='width: 800px; margin-left: auto; margin-right: auto'>";
    }
    echo "<tr>";

    echo "<td";
    if (strtotime($info['date_published']) > time()){
        echo " style='background-color: rgb(255, 150, 150)'";
    }
    if (date("Y-m-d", strtotime($info['date_published'])) == date("Y-m-d", time())){
        echo " style='background-color: rgb(150, 150, 255)'";
    }
    echo ">";
    echo date("D d M Y", strtotime($info['date_published']));
    echo "</td>";

    echo "<td>";
    $img_url = "/files/tex_images/thumbnails/".$info['slug'].".jpg";
    $local_image_path = join_paths($GLOBALS['SYSTEM_ROOT'], $img_url);
    if ($GLOBALS['WORKING_LOCALLY'] and !file_exists($local_image_path)){
        $img_url = "http://3dmodelhaven.com".$img_url;
    }
    echo "<img class='thumbnail' src='".$img_url."'>";
    echo "</td>";

    echo "<td>";
    echo "<a href='/tex/?t=".$info['slug']."'>";
    echo $info['name'];
    echo "</a>";
    echo "<br><span style='font-size: 70%; opacity: 0.4'>";
    echo $info['download_count'];
    echo " (".round($info['download_count']/((time() - strtotime($info['date_published']))/86400)).")</span>";
    echo "</td>";

    // $files = [];
    // foreach (array_keys($GLOBALS['STANDARD_RESOLUTIONS']) as $r){
    //     $local_file = join_paths($GLOBALS['SYSTEM_ROOT'], "files", "tex", $info['slug'].'_'.$r.'.'.$info['ext']);
    //     if (file_exists($local_file)){
    //         array_push($files, $r);
    //     }
    // }
    // echo "<td";
    // echo " style='font-size: 70%;";
    // if (sizeof($files) < 5){  // 1k 2k 4k 8k 16k
    //     echo " background-color: rgb(255, 60, 60);";
    // }
    // echo "'>";
    // if (sizeof($files) > 0){
    //     echo implode(' ', $files);
    // }else{
    //     echo "No files!";
    // }
    // echo "</td>";

    echo "<td style='font-size: 70%'>";
    echo $info['author'];
    echo "</td>";

    echo "</tr>\n";
    $current_month = $m;
}
echo "</table>\n";
?>

</div>
</div>

</body>
</html>
