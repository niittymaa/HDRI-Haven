<?php
include ($_SERVER['DOCUMENT_ROOT'].'/php/functions.php');

// Parameters
// Defaults:
$slug = "none";
$category = "all";

// Get params (if they were passed)
if (isset($_GET["h"]) && trim($_GET["h"])){
    $slug = $_GET["h"];
}
if (isset($_GET["c"]) && trim($_GET["c"])){
    $category = $_GET["c"];
}

$slug = htmlspecialchars($slug);
$category = htmlspecialchars($category);

// Redirect if parameters not received
if (empty($_GET['h'])){
    header("Location: /hdris/");
}

$conn = db_conn_read_only();
$info = get_item_from_db($slug, $conn);

// Redirect to search if the HDRI is not in the DB.
if (sizeof($info) <= 1){
    header("Location: /hdris/category/?s=".$slug);
}

include_start_html("HDRI: {$info['name']}", $slug);
include ($_SERVER['DOCUMENT_ROOT'].'/php/html/header.php');

?>

<div id="lightbox-wrapper" class="hide">
    <img id="lightbox-img" src="#">
    <p id="lightbox-text">Downloads:
        <a href="/" download="" id="href-dlbp-pretty" target="_blank"><span class='button'><b>Pretty JPG</b> (as shown)</span></a>
        <a href="/" download="" id="href-dlbp-plain" target="_blank"><span class='button'><b>Plain JPG</b> (no adjustments)</span></a>
        <a href="/" download="" id="href-dlbp-raw" target="_blank"><span class='button'><b>RAW</b> (straight from camera)</span></a>
        License: <a href="/p/license.php">CC0</a>
    </p>
    <div class='item-info'>
    </div>
    <div id="lightbox-close"><i class="material-icons">close</i></div>
</div>

<?php

echo "<div id='hdri-page'>";

echo "<div id='hdri-preview' style='background-image: url(/files/hdri_images/tonemapped/1500/{$slug}.jpg)'>";

echo "<div class='darken'></div>";

echo "<div id='main-preview'>";
echo "<img src='/files/hdri_images/tonemapped/1500/{$slug}.jpg'>";
echo "<div class='button-overlay'>";
echo "<div class='button' id='btn-preview-360' title='360&deg; preview'><i class='material-icons'>panorama_horizontal</i></div>";
echo "<div class='button' id='btn-exposure-preview' title='Exposure preview'><i class='material-icons'>exposure</i></div>";
echo "</div>";  // .button-overlay

echo "<div id='exposure-wrapper'>";
echo "<img id='exposure-img' src='/files/hdri_images/exposure_preview/{$slug}/0.jpg' ev='0'>";
echo "<div class='button-overlay'>";
echo "<div class='button' id='btn-exposure-preview-exit'><i class='material-icons'>arrow_back</i></div>";
echo "</div>";  // .button-overlay
echo "<div class='button-overlay exposure-button-overlay'>";
echo "<div class='button btn-fixed-size btn-exposure-adj'>-</div>";
echo "<div class='button btn-fixed-size' id='btn-exposure-reset'>0 EVs</div>";
echo "<div class='button btn-fixed-size btn-exposure-adj'>+</div>";
echo "</div>";  // .exposure-button-overlay
echo "</div>";  // #exposure-wrapper

echo "</div>";  // #main-preview


echo "<div id='pannellum-wrapper'>";
echo "<iframe id='pannellum-frame' title='pannellum panorama viewer' style='border-style:none;' src=\"/files/hdri_images/pannellum/pannellum.htm?config={$slug}/config.json\" style='position: absolute; width: 100%;'></iframe>";
echo "<div class='button-overlay'>";
echo "<div class='button' id='btn-preview-360-exit'><i class='material-icons'>arrow_back</i></div>";
echo "</div>";  // .button-overlay
echo "</div>";  // #pannellum-wrapper


echo "</div>";  // #hdri-preview

echo "<h1>";
echo "<a href='/hdris/category/?c=all'>";
echo "HDRIs";
echo "</a>";
echo " >";
if ($category != "all"){
    echo " ";
    echo "<a href='/hdris/category/?c={$category}'>";
    echo nice_name($category, 'category');
    echo "</a>";
    echo " >";
}
echo "<br><b>{$info['name']}</b></h1>";

if($info['problem']){
    echo "<p class='red-text center' style='font-weight: 900'>";
    echo "Note: ".$info['problem'];
    echo "</p>";
}

echo "<div id='page-wrapper'>";
echo "<div id='hdri-renders'>";
echo "<img src='/files/hdri_images/spheres/{$slug}.jpg' class='spheres'>";
echo "</div>";  // .hdri-renders


if (is_in_the_past($info['date_published']) || $GLOBALS['WORKING_LOCALLY']){
    if ($GLOBALS['WORKING_LOCALLY'] && is_in_the_past($info['date_published']) == False){
        echo "<p style='text-align:center;opacity:0.5;'>(working locally on a yet-to-be-published HDRI)</p>";
    }
    echo "<div id='hdri-info'>";

    echo "<div class='col-2'>";
    echo "<h2>Download</h2>";
    echo "<div class='download-buttons'>";

    $ext = $info['ext'];
    foreach (array_keys($GLOBALS['STANDARD_RESOLUTIONS']) as $r){
        $local_file = join_paths($GLOBALS['SYSTEM_ROOT'], "files", "hdris", $slug.'_'.$r.'.'.$ext);
        if (file_exists($local_file)){
            $filesize = filesize($local_file)/1024/1024;  // size in MB
            if ($filesize > 10){
                $d = 0;
            }else if ($filesize > 1){
                $d = 1;
            }else{
                $d = 2;
            }
            $filesize = round($filesize, $d);

            echo "<a href='/hdri/download.php?h={$slug}&amp;r={$r}'>";
            echo "<div class='button";
            echo "'>";
            echo "<b>";
            echo $r;
            echo "</b>";
            echo " &sdot; ";
            echo $filesize." MB";
            echo " &sdot; ";
            echo strtoupper($ext);
            echo "</div>";
            echo "</a>";
        }
    }
    echo "</div>";
    echo "<p class='center'>License: <a href='/p/license.php'>CC0</a></p>";
    echo "</div>";

    echo "<div class='col-2'>";
    echo "<h2>Info</h2>";
    echo "<ul class='hdri-info-list'>";
    echo "<li>";
    $evs = $info['evs_cap'];
    if ($evs == 0){
        $evs= "Unknown, but unclipped";
    }else if ($evs < 10){
        $evs = $evs." <a href='/p/faq.php#stops'>EVs</a> (medium), unclipped";
    }else if ($evs < 14){
        $evs = $evs." <a href='/p/faq.php#stops'>EVs</a> (high), unclipped";
    }else if ($evs < 18){
        $evs = $evs." <a href='/p/faq.php#stops'>EVs</a> (very high), unclipped";
    }else{
        $evs = $evs." <a href='/p/faq.php#stops'>EVs</a> (extremely high), unclipped";
    }
    echo "<b>Dynamic Range:</b> {$evs}";
    echo "</li>";

    if ($info['whitebalance']){
        echo "<li>";
        echo "<b>Whitebalance:</b> ".$info['whitebalance'];
        echo "</li>";
    }

    echo "<li>";
    $taken_format = "d F Y H:i";
    if (ends_with($info['date_taken'], "00:00:00")){
        // Don't show time if it's the default (unknown). Note: if shooting at midnight, make sure it's not exactly midnight.
        $taken_format = "d F Y";
    }
    echo "<b>Taken:</b> ".date($taken_format, (strtotime($info['date_taken'])+($info['timezone_offset']*60*60)));
    echo "</li>";

    echo "<li>";
    echo "<b>Published:</b> ".date("d F Y", strtotime($info['date_published']))." (".time_ago($info['date_published']).")";
    echo "</li>";

    if ($info['coords'] && $info['coords'] != '0'){
        $coords = explode(",", $info['coords']);
        $cy = trim($coords[0]);
        $cx = trim($coords[1]);
        $map_url = "https://www.google.com/maps/place/".$cy.'+'.$cx;
        echo "<li>";
        echo "<b>Location:</b> ";
        echo "<a href=\"".$map_url."\", target=\"_blank\">";
        echo $cy.', '.$cx;
        echo "</a>";
        echo " (approx.)";
        echo "</li>";
    }

    echo "<li>";
    $category_str = "";
    $category_arr = explode(';', $info['categories']);
    sort($category_arr);
    foreach ($category_arr as $category) {
        $category_str .= '<a href="/hdris/category/?c='.$category.'">'.$category.'</a>, ';
    }
    $category_str = substr($category_str, 0, -2);  // Remove ", " at end
    echo "<b>Categories:</b> {$category_str}";
    echo "</li>";

    echo "<li>";
    $tag_str = "";
    $tag_arr = explode(';', $info['tags']);
    sort($tag_arr);
    if ($info['backplates']){
        array_push($tag_arr, "backplates");
    }
    foreach ($tag_arr as $tag) {
        $tag_str .= '<a href="/hdris/category/?s='.$tag.'">'.$tag.'</a>, ';
    }
    $tag_str = substr($tag_str, 0, -2);  // Remove ", " at end
    echo "<b>Tags:</b> {$tag_str}";
    echo "</li>";
    
    $downloads_per_day = round($info['download_count']/((time() - strtotime($info['date_published']))/86400));
    echo "<li title=\" (".$downloads_per_day." per day)\">";
    echo "<b>Downloads:</b> ".$info['download_count'];
    echo "</li>";
    echo "<li>";
    echo "<b>Author:</b> <a href=\"/hdris/category/?a=".$info['author']."\">".$info['author']."</a>";
    echo "</li>";
    echo "</ul>";

    echo "</div>";  // .col-2

    echo "</div>";  // .hdri-info

    echo "<div class='center'>";
    echo "<p class='small'>This HDRI is sponsored by:</p>";
    echo "<ul id='sponsor-list'>";
    $sponsors = get_sponsors($slug, $conn);
    if ($sponsors){
        foreach ($sponsors as $s){
            echo "<li>";
            if ($s['url'] != "none" && $s['url'] != ""){
                echo "<a href=\"{$s['url']}\">";
            }
            echo $s['sponsor'];
            if ($s['url'] != "none" && $s['url'] != ""){
                echo "</a>";
            }
            echo "</li>";
        }
    }else{
        echo "<li style='font-weight:300'>";
        echo "No one yet :(";
        echo "</li>";
    }
    echo "</ul>";
    echo "<p><a href='https://www.patreon.com/hdrihaven/overview'>Support HDRI Haven</a> and add your name here.</p>";
    echo "</div>";

    if ($info['backplates']){
        $backplates_scan_dir = $GLOBALS['SYSTEM_ROOT']."/files/backplates/".$slug."/thumbs/S/";
        if (file_exists($backplates_scan_dir)){
            $files = scandir($backplates_scan_dir);
            if (sizeof($files) > 2){  // In case files don't exist, only "files" in dir will be "." and ".."
                echo "<h2>Backplates</h2>";
                echo "<div id='backplates-grid'>";
                foreach ($files as $f) {
                    if (ends_with(strtolower($f), ".jpg")){
                        $basename = substr($f, 0, -4);
                        $thumb_s = "/files/backplates/".$slug."/thumbs/S/".$f;
                        $thumb_l = "/files/backplates/".$slug."/thumbs/L/".$f;
                        $dl_jpg_pretty = "/files/backplates/".$slug."/jpg_pretty/".$f;
                        $dl_jpg_plain = "/files/backplates/".$slug."/jpg_plain/".$f;

                        $raw_folder = "/files/backplates/".$slug."/raw/";
                        $possible_extensions = ["ARW", "DNG", "CR2"];
                        $dl_raw = "";
                        foreach ($possible_extensions as $ext){
                            $possible_path = $raw_folder.$basename.".".$ext;
                            if (file_exists($GLOBALS['SYSTEM_ROOT'].$possible_path)){
                                $dl_raw = $possible_path;
                                break;
                            }
                            $possible_path = $raw_folder.$basename.".".strtolower($ext);
                            if (file_exists($GLOBALS['SYSTEM_ROOT'].$possible_path)){
                                $dl_raw = $possible_path;
                                break;
                            }
                        }
                        
                        echo "<div class='item lightbox-trigger'";
                        echo " lightbox-src=\"".$thumb_l."\"";
                        echo " dlbp-pretty=\"".$dl_jpg_pretty."\"";
                        echo " dlbp-plain=\"".$dl_jpg_plain."\"";
                        echo " dlbp-raw=\"".$dl_raw."\"";
                        echo ">";
                        echo "<img src=\"".$thumb_s."\">";
                        echo "</div>";
                    }
                }
                echo "</div>";
            }
        }
    }

    $all_renders = get_gallery_renders(true, $conn);
    $renders = [];
    foreach ($all_renders as $r){
        if ($r['hdri_used'] == $slug){
            array_push($renders, $r);
        }
    }
    echo "<h2>";
    echo "User Renders ";
    echo "<a href=\"/gallery/submit.php?h=".$slug."\"><i class='material-icons'>add_circle_outline</i></a>";
    if (!$renders){
        echo " <sup style='font-size: 65%; color: black; font-style: italic; opacity: 0.5'>None yet, be the first!</sup>";
    }
    echo "</h2>";
    if ($renders){
        echo '<div id="user-renders">';
        echo '<div class="flex-images">';
        foreach ($renders as $r){
            if ($r['approval_pending'] == 0){
                $src = "/files/gallery/S/".$r['file_name'];
                $src_L = "/files/gallery/L/".$r['file_name'];
                $real_src = $GLOBALS['SYSTEM_ROOT'].$src;
                $size = getimagesize($real_src);
                if ($r['author_link'] == "" || $r['author_link'] == "none"|| $r['author_link'] == "http://"){
                    $r['author_link'] = "#";
                }
                echo "<div class='item user-render' data-w='".$size[0]."' data-h='".$size[1]."'>";
                echo "<div class='user-render-info'>";
                echo "<p>";
                echo "<i>{$r['artwork_name']}</i>";
                echo " by ";
                if ($r['author_link'] && $r['author_link'] !== "#"){
                    echo "<a target=\"_blank\" href=\"{$r['author_link']}\">{$r['author']}</a>";
                }else{
                    echo $r['author'];
                }
                echo "</p>";
                echo "</div>";
                echo "<a href=\"".$src_L."\" target=\"_blank\">";
                echo "<img src=\"".$src."\">";
                echo "</a>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "</div>";
        echo "<script type=\"text/javascript\">";
        echo "$('#user-renders').flexImages({rowHeight: 300});";
        echo "</script>";
    }

    $similar = get_similar($slug, $conn);
    if ($similar){
        echo "<h2>";
        echo "Similar HDRIs";
        echo "</h2>";
        echo "<div id='similar-hdris'>";
        echo "<div id='hdri-grid'>";
        foreach ($similar as $s){
            echo make_grid_item($s);
        }
        echo "</div>";
        echo "</div>";
    }

}else{
    echo "<h1 class='coming-soon'>Coming soon :)</h1>";
}

// echo "<hr class='disqus' />";
// echo "<p class='center'>Note: Comments have been temporarily disabled due to <a href='https://twitter.com/disqus'>Disqus</a> injecting multiple ads (including auto-playing videos).<br>I'm currently looking for an alternative, <a href='/p/about-contact.php'>let me know</a> if you can help.</p>";
if (!$GLOBALS['WORKING_LOCALLY']){
    echo "<hr class='disqus' />";
    include_disqus('hdri_'.$slug);
}

echo "</div>";  // #page-wrapper
echo "</div>";  // #hdri-page
?>


<?php
include ($_SERVER['DOCUMENT_ROOT'].'/php/html/footer.php');
include ($_SERVER['DOCUMENT_ROOT'].'/php/html/end_html.php');
?>
