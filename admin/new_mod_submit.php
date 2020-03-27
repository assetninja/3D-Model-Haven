<?php
include ($_SERVER['DOCUMENT_ROOT'].'/php/functions.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Model | Submit</title>
    <link href='/css/style.css' rel='stylesheet' type='text/css' />
    <link href='/css/admin.css' rel='stylesheet' type='text/css' />
    <link href="https://fonts.googleapis.com/css?family=PT+Mono" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="new_mod.js"></script>

    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
</head>
<body>

<?php include ($_SERVER['DOCUMENT_ROOT'].'/admin/header.php'); ?>
<div id="page-wrapper">
<div id="page" class="center-all">

<?php

$conn = db_conn_read_write();  // Create Database connection first so we can use `mysqli_real_escape_string`

$name = mysqli_real_escape_string($conn, $_POST["name"]);
$author = mysqli_real_escape_string($conn, $_POST["author"]);
$slug = mysqli_real_escape_string($conn, $_POST["slug"]);
debug_console($slug);


// Render
$target_dir = join_paths($GLOBALS['SYSTEM_ROOT'], "files", "mod_images", "renders");
qmkdir($target_dir);
$f = $_FILES['main_render']['name'];
$tmp_file = $_FILES['main_render']['tmp_name'];
$file_hash = random_hash(8);
$file_name = basename($f);
$target_file = join_paths($target_dir, $slug.".png");
$uploadOk = 1;
$error = "";
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($tmp_file);
    if($check == false) {
        $error = "File is not an image.";
        $uploadOk = 0;
    }
}
// Allow certain file formats
$ext = strtolower(pathinfo(basename($f),PATHINFO_EXTENSION));
if ($ext != 'png'){
    $error = "Render must be a PNG";
    $uploadOk = 0;
}
if ($uploadOk == 1) {
    if (move_uploaded_file($tmp_file, $target_file)) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
        $error = "There was an unknown error uploading your file. Please contact Greg and provide the files that aren't working, along with this path: <pre>".$target_file."</pre>";
    }
}
if ($uploadOk == 0) {
    echo $error;
    // header("Location: /admin/new_mod.php?error=".$error);
    die();
}
// Make JPG with correct background color
if (!$GLOBALS['WORKING_LOCALLY']){
    // Meta iamge
    $size = 640;
    $jpg_file = join_paths($target_dir, $slug.".jpg");
    $img = new imagick();
    $img->newImage($size, $size, "rgb(45, 45, 45)");
    $tmp_img = new imagick($target_file);
    $tmp_img->resizeImage($size, $size, imagick::FILTER_BOX, 1, true);
    $img->compositeimage($tmp_img, Imagick::COMPOSITE_OVER, 0, 0);
    $img->setImageFormat('jpg');
    $img->setImageCompression(Imagick::COMPRESSION_JPEG);
    $img->setImageCompressionQuality(90);
    $img->writeImage($jpg_file);
}



// Database stuff
$sql_fields = [];
$sql_fields['name'] = $name;
$sql_fields['author'] = $author;
$sql_fields['slug'] = $slug;
function format_tagcat($s, $conn){
    $s = trim(str_replace(",", ";", str_replace(", ", ";", $s)), ",");
    return mysqli_real_escape_string($conn, $s);
}
$categories = format_tagcat($_POST["cats"], $conn);
$sql_fields['categories'] = $categories;
$sql_fields['tags'] = format_tagcat($_POST["tags"], $conn);

$date_published = $_POST["date_published"];
if ($date_published){
    $sql_fields['date_published'] = $_POST["date_published"];
}

// XXX
// echo "<pre>";
// print_r($sql_fields);
// echo "</pre>";

foreach (array_keys($sql_fields) as $k){
    $sql_fields[$k] = "'".$sql_fields[$k]."'";
}
$sql_value_str = implode(", ", array_values($sql_fields));
$sql_field_str = implode(", ", array_keys($sql_fields));

$sql = "INSERT INTO models (".$sql_field_str.") VALUES (".$sql_value_str.")";

// XXX
// echo "<br>";
// echo "<br>";
// echo "<br>";
// echo $sql;
$result = mysqli_query($conn, $sql);

if ($result == 1){
    echo "<h1>Success!</h1>";
    echo "<p>";
    echo "<em>".$_POST["name"]."</em> ";  // Use GET instead of $name since $name with apostrophy will show Apostro\'phy instead of Apostro'phy
    echo "successfully added to the database.";
    echo "</p>";
    echo "<p>If you need to edit or update this model, you can do so from the <a href='TODO'>phpMyAdmin interface</a>.</p>";

    echo '<a href="/admin" class="no-underline">';
    echo '<div class="button"><i class="fa fa-home" aria-hidden="true"></i> Admin Home</div>';
    echo '</a> ';
    echo '<a href="/admin/new_mod.php" class="no-underline">';
    echo '<div class="button"><i class="fa fa-plus" aria-hidden="true"></i> Add Another</div>';
    echo '</a> ';
    echo '<a href="/model/?m='.$slug.'" class="no-underline">';
    echo '<div class="button"><i class="fa fa-eye" aria-hidden="true"></i> View This Model</div>';
    echo '</a> ';

    // Social Media
    $vars = [
        "name" => $name,
        "link" => "https://3dmodelhaven.com/model/?m=".$slug,
    ];
    function format_vars($str, $vars){
        foreach (array_keys($vars) as $v){
            $str = str_replace("##".$v."##", $vars[$v], $str);
        }
        return str_replace("  ", " ", $str);
    }
    $sql_fields = [];
    $sql_fields['twitface'] = format_vars(mysqli_real_escape_string($conn, $_POST["twitface"]), $vars);
    $sql_fields['reddit'] = format_vars(mysqli_real_escape_string($conn, $_POST["reddit"]), $vars);
    $sql_fields['link'] = "https://3dmodelhaven.com/model/?m=".$slug;
    $sql_fields['image'] = "https://3dmodelhaven.com/files/mod_images/renders/".$slug.".jpg";
    $sql_fields['post_datetime'] = date("Y-m-d H:i:s", strtotime('+7 hours', strtotime($date_published)));
    foreach (array_keys($sql_fields) as $k){
        $sql_fields[$k] = "'".$sql_fields[$k]."'";
    }
    $sql_value_str = implode(", ", array_values($sql_fields));
    $sql_field_str = implode(", ", array_keys($sql_fields));
    $sql = "INSERT INTO social_media (".$sql_field_str.") VALUES (".$sql_value_str.")";
    $result = mysqli_query($conn, $sql);

}else{
    echo "<h1>Submission Failed.</h1>";

    // Check for existing
    $existing_sql = "SELECT * from models WHERE slug='".$slug."'";
    $existing_result = mysqli_query($conn, $existing_sql);
    if (mysqli_num_rows($existing_result) > 0){
        echo "<p>There is already a model with the slug <em>".$slug."</em></p>";
        echo "<p>Either choose a different slug, or manually remove the existing one from the database.</p>";
    }else{
        echo "<p>Looks like something went wrong :(<br>Here is the generated SQL query to help you figure out the problem:</p>";
        echo "<p>".$sql."</p> ";
    }

    echo '<a href="javascript:history.back()" class="no-underline">';
    echo '<div class="button"><i class="fa fa-chevron-left" aria-hidden="true"></i> Back</div>';
    echo '</a> ';
    echo '<a href="javascript:window.location.href=window.location.href" class="no-underline">';
    echo '<div class="button"><i class="fa fa-refresh" aria-hidden="true"></i> Try Again</div>';
    echo '</a> ';
}

?>

</div>
</div>

</body>
</html>
