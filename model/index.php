<?php
include ($_SERVER['DOCUMENT_ROOT'].'/php/functions.php');

// Parameters
// Defaults:
$slug = "none";
$category = "all";

// Get params (if they were passed)
if (isset($_GET["t"]) && trim($_GET["t"])){
    $slug = $_GET["t"];
}
if (isset($_GET["c"]) && trim($_GET["c"])){
    $category = $_GET["c"];
}

// Redirect if parameters not received
if (empty($_GET["t"])){
    header("Location: /models/");
}

$slug = htmlspecialchars($slug);
$category = htmlspecialchars($category);

$conn = db_conn_read_only();
$info = get_item_from_db($slug, $conn);

// Redirect to search if the model is not in the DB.
if (sizeof($info) <= 1){
    header("Location: /models/?s=".$slug);
}

$canonical = "https://3dmodelhaven.com/tex/?t=".$slug;
$t1 = [];
$t1 ['name'] = $info['name'];
$t1 ['date_published'] = $info['date_published'];
$t1 ['author'] = $info['author'];
$category_arr = explode(';', $info['categories']);
$tag_arr = explode(';', $info['tags']);
$tags = array_merge($category_arr, $tag_arr);
$t1 ['tags'] = implode(',', array_merge($category_arr, $tag_arr));
include_start_html($info['name'], $slug, $canonical, $t1);
include ($_SERVER['DOCUMENT_ROOT'].'/php/html/header.php');


echo "<div id='item-page'>";
echo "<div id='page-wrapper'>";

echo "<h1>";
echo "<a href='/models/?c=all'>";
echo "3D Models";
echo "</a>";
echo " >";
if ($category != "all"){
    echo " ";
    echo "<a href='/models/?c={$category}'>";
    echo nice_name($category, 'category');
    echo "</a>";
    echo " >";
}
echo "<br><b>{$info['name']}</b></h1>";

$is_published = is_in_the_past($info['date_published']) || $GLOBALS['WORKING_LOCALLY'];
if ($is_published){
    echo "<div id='preview-download'>";
    echo "<div id='item-preview'>";
    echo "<img src=\"/files/tex_images/spheres/".$slug.".jpg\" />";
    echo "<div id='map-preview-img' class='hide'/>";
    echo "<div id='map-preview-zoom-btns' class='hide-mobile'>";
    echo "<div id='map-preview-resolution'>";
    echo "<span id='map-preview-resolution-select' class='button'>640p</span>";
    echo "<ul id='map-preview-resolution-list' class='hidden'>";
    echo "<li>1k</li>";
    echo "<li>2k</li>";
    echo "</ul>";  // #map-preview-resolution-list
    echo "</div>";  // #map-preview-resolution
    echo "<span class='map-preview-zoom' id='map-preview-zoom-out'>-</span>";
    echo "<span class='map-preview-zoom' id='map-preview-zoom-in'>+</span>";
    echo "</div>";  // #zoom-btns
    echo "</div>";  // #map-preview-img
    echo "</div>";  // #item-preview

    echo "<div class='download-buttons'>";
    echo "<h2>Download:</h2>";
    $downloads = [];
    $base_dir = join_paths($GLOBALS['SYSTEM_ROOT'], "files", "models");
    $extensions = array_reverse(listdir($base_dir, "FOLDERS"));  // Reverse so ZIP is first so 'All Maps' is at the top.
    foreach ($extensions as $ext){
        $ext_dir = join_paths($base_dir, $ext);
        $resolutions = listdir($ext_dir, "FOLDERS");
        foreach ($resolutions as $res){
            $res_dir = join_paths($ext_dir, $res);
            if (ends_with($res, 'k')){
                $tex_dir = join_paths($res_dir, $slug);
                if (file_exists($tex_dir)){
                    $files = listdir($tex_dir, "FILES");
                    foreach ($files as $f){
                        $format = $ext;
                        if ($ext == 'zip'){
                            $map_type = 'all';
                            $f_split = explode('_', $f);
                            $format = str_replace('.zip', '', array_pop($f_split));  // Get only 'jpg' from fname
                        }else{
                            $without_ext = pathinfo($f, PATHINFO_FILENAME);
                            $map_type = substr($without_ext, strlen($slug)+1, strlen($res)*-1-1);
                        }
                        $downloads[$map_type][$res][$format] = $f;
                    }
                }
            }
        }
    }

    $preview_zooms = [];
    foreach (array_keys($downloads) as $map_type){
        $map_type_str = nice_name($map_type);
        $map_name_arr = [
            "all" => "<b>All Maps</b>",
            "alb" => "Albedo",
            "diff" => "Diffuse",
            "ao" => "AO",
            "disp" => "Displacement",
            "nor" => "Normal",
            "rough" => "Roughness",
            "spec" => "Specular",
        ];
        foreach (array_keys($map_name_arr) as $m){
            if (strtolower($map_type) == $m){
                $map_type_str = $map_name_arr[$m];
            }
        }

        echo "<div class='map-type'>";
        echo "<div class='map-preview";
        if ($map_type == "all"){
            echo " map-preview-active' id='map-preview-allmaps";
        }
        echo "' map='".$map_type."'><p>";
        echo "<img src='/files/site_images/icons/eye.svg' class='map-preview-icon'>";
        echo "</p></div>";
        echo "<div class='map-download'><p>";
        echo "<img src='/files/site_images/icons/download_white.svg'>";
        echo $map_type_str;
        echo "</p></div>";
        echo "<div class='res-menu hide'>";
        foreach(array_keys($downloads[$map_type]) as $res){
            echo "<div class='res-item'>";
            $i = 0;
            $extensions = array_keys($downloads[$map_type][$res]);
            sort($extensions);
            foreach($extensions as $ext){
                $i += 1;

                if ($ext == 'jpg'){
                    if (array_key_exists($map_type, $preview_zooms)){
                        array_push($preview_zooms[$map_type], $res);
                    }else{
                        $preview_zooms[$map_type] = [$res];
                    }
                }

                $fname = $downloads[$map_type][$res][$ext];
                $format = $ext;
                if ($map_type == 'all'){
                    $format = 'zip';
                }
                $dl_url = join_paths('files', 'models', $format, $res, $slug, $fname);
                $local_path = join_paths($GLOBALS['SYSTEM_ROOT'], $dl_url);
                $filesize = filesize($local_path)/1024/1024;  // size in MB
                if ($filesize > 10){
                    $d = 0;
                }else{
                    $d = 1;
                }
                $filesize = max(0.1, round($filesize, $d));
                $fhash = simple_hash($fname);
                echo "<a href=\"/".$dl_url."\" download=\"".$fname."\" target='_blank'>";
                echo "<div class='dl-btn' id=\"".$info['id']."\" fhash=\"".$fhash."\"";
                $width = 100/sizeof($downloads[$map_type][$res]);
                echo " style='width: calc(".$width."% - 2em)'>";
                if ($i == 1){
                    echo $res." &sdot; ";
                }
                echo strtoupper($ext);
                echo " &sdot; ".$filesize." MB";
                echo "</div>";
                echo "</a>";
            }
            echo "</div>";  // .res-item
        }
        echo "</div>";  // .res-menu
        echo "</div>";  // .map-type
    }
    if (empty($downloads)){
        echo "<p>Model files do not exist :(</p>";
        echo "<p>Please ";
        insert_email("let us know");
        echo " that you're seeing this error.</p>";
    }else{
        echo "<p style='margin: 0.5em; text-align: center;'>License: <a href='/p/license.php'>CC0</a><p>";
    }
    echo "</div>";  // .download-buttons
    echo "</div>";  // #preview-download


    if ($GLOBALS['WORKING_LOCALLY'] && is_in_the_past($info['date_published']) == False){
        echo "<p style='text-align:center;opacity:0.5;'>(working locally on a yet-to-be-published model)</p>";
    }
    echo "<div id='item-info'>";

    echo "<ul class='item-info-list'>";

    echo "<li>";
    echo "<b>Author:</b> <a href=\"/models/?a=".$info['author']."\">".$info['author']."</a>";
    echo "</li>";

    echo "<li>";
    echo "<b>Published:</b> ".date("d F Y", strtotime($info['date_published']))." (".time_ago($info['date_published']).")";
    echo "</li>";

    $downloads_per_day = round($info['download_count']/((time() - strtotime($info['date_published']))/86400));
    echo "<li title=\" (".$downloads_per_day." per day)\">";
    echo "<b>Downloads:</b> ".$info['download_count'];
    echo "</li>";

    echo "<br><li>";
    $category_str = "";
    $category_arr = explode(';', $info['categories']);
    sort($category_arr);
    foreach ($category_arr as $category) {
        $category_str .= '<a href="/models/?c='.$category.'">'.$category.'</a>, ';
    }
    $category_str = substr($category_str, 0, -2);  // Remove ", " at end
    echo "<b>Categories:</b> {$category_str}";
    echo "</li>";

    echo "<li>";
    $tag_str = "";
    $tag_arr = explode(';', $info['tags']);
    sort($tag_arr);
    if ($info['seamless']){
        array_push($tag_arr, "seamless");
    }
    foreach ($tag_arr as $tag) {
        $tag_str .= '<a href="/models/?s='.$tag.'">'.$tag.'</a>, ';
    }
    $tag_str = substr($tag_str, 0, -2);  // Remove ", " at end
    echo "<b>Tags:</b> {$tag_str}";
    echo "</li>";
    echo "</ul>";

    echo "</div>";  // .item-info


    $similar = get_similar($slug, $conn);
    if ($similar){
        echo "<h2>";
        echo "Similar Models";
        echo "</h2>";
        echo "<div id='similar-models'>";
        echo "<div id='tex-grid'>";
        foreach ($similar as $s){
            echo make_grid_item($s);
        }
        echo "</div>";
        echo "</div>";
    }

}else{
    echo "<h1 class='coming-soon'>Coming soon :)</h1>";
}

/*
TODO:
    User renders
*/

if (!$GLOBALS['WORKING_LOCALLY']){
    echo "<hr class='disqus' />";
    include_disqus('tex_'.$slug);
}

echo "</div>";  // #page-wrapper
echo "</div>";  // #item-page

echo "<div id='page-data' slug='".$slug."'>".json_encode($preview_zooms)."</div>";
?>


<?php
include ($_SERVER['DOCUMENT_ROOT'].'/php/html/footer.php');
include ($_SERVER['DOCUMENT_ROOT'].'/php/html/end_html.php');
?>
