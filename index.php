<!doctype html>
<html>    
    <?php
/*** mysql data ***/
$hostname = 'localhost';
$dbname = 'randomcoloroftheday';
$username = 'randomcoloruser';
$password = 'colorpassword123';

$color = "#";

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    /*** echo a message saying we have connected ***/

    /*** The SQL SELECT statement ***/
    $sql = "SELECT * FROM colors
            ORDER BY ID DESC
            LIMIT 1;";
    $stm = $dbh->query($sql);

    $result = $stm->fetch(PDO::FETCH_OBJ);

    $color = "#".$result->color;
    
    // RGB -------------------------------------
    $R = hexdec(substr($color, 1, 2));
    $G = hexdec(substr($color, 3, 2));
    $B = hexdec(substr($color, 5, 2));
    
    // HSV -------------------------------------
    $var_R = ($R / 255);
    $var_G = ($G / 255);
    $var_B = ($B / 255);

    $var_Min = min($var_R, $var_G, $var_B);
    $var_Max = max($var_R, $var_G, $var_B);
    $del_Max = $var_Max - $var_Min;

    $V = $var_Max;

    if ($del_Max == 0)
    {
        $H = 0;
        $S = 0;
    }
    else
    {
        $S = $del_Max / $var_Max;

        $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
        $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
        $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

        if      ($var_R == $var_Max) $H = $del_B - $del_G;
        else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
            else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;

            if ($H<0) $H++;
            if ($H>1) $H--;
    }   
    $H = round($H * 360);
    $S = round($S * 100);
    $V = round($V * 100);
    
    

    /*** close the database connection ***/
    $dbh = null; 
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
    ?>   

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title><?php print($color); ?> - Random Color of the Day</title>
        <link rel="stylesheet" href="style.css"> 
        <link rel="icon" type="image/png" href="color.png" />
        
        
        <meta property="og:url"           content="http://random-color-of-the-day.funsite.cz" />
        <meta property="og:type"          content="website" />
        <meta property="og:title"         content="Today's random color is <?php print($color); ?>" />
        <meta property="og:description"   content="Random Color of the Day" />
        <meta property="og:image"         content="http://random-color-of-the-day.funsite.cz/color.png" />
        
    </head>
        <!--       
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
-->
   
    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "8c7bdcce-3269-4889-97c4-c5b8d8cc770e", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
    
    </head>

    <body style="background:<?php print($color) ?>">
        <!-- Facebook -->
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/cs_CZ/sdk.js#xfbml=1&version=v2.4&appId=424757477717430";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
       <!-- Google 
        <script src="https://apis.google.com/js/platform.js" async defer>
            {lang: 'cs'}
        </script>-->
        
        <span class="st_sharethis" st_url="http://random-color-of-the-day.funsite.cz/" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color of the Day"></span>

        <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
        <script type="text/javascript">
            stLight.options({
                publisher:'12345',
            });
        </script>
       
       
        <div id="title">
            <h2>Today's random color is </h2>
            <h1><?php print($color); ?></h1>
            
            <span class='st_sharethis_vcount' displayText='ShareThis'></span>
            <span class='st_facebook_vcount' displayText='Facebook'></span>
            <span class='st_googleplus_vcount' displayText='Google +'></span>
            <span class='st_twitter_vcount' displayText='Tweet'></span>
            <span class='st_pinterest_vcount' displayText='Pinterest'></span>
            <span class='st_email_vcount' displayText='Email'></span>
            
            
            
            <div class="fb-share-button" data-href="http://random-color-of-the-day.funsite.cz/" data-layout="box_count"></div>
            <div class="g-plus" data-action="share" data-annotation="vertical-bubble" data-height="60" data-href="http://random-color-of-the-day.funsite.cz/"></div> <!--
                
                 <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://random-color-of-the-day.funsite.cz/">Tweet</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        
        <br><br><br>
            <div class="a2a_kit a2a_kit_size_32 a2a_default_style" data-a2a-url="http://random-color-of-the-day.funsite.cz" data-a2a-title="Today's random color is <?php print($color); ?>">
                <a class="a2a_button_facebook"></a>
                <a class="a2a_button_twitter"></a>
                <a class="a2a_button_google_plus"></a>
                <a class="a2a_dd" href="https://www.addtoany.com/share_save"></a>
            </div>

            <script async src="//static.addtoany.com/menu/page.js"></script>-->
        </div>
        
        <div id="RGB">
           <table border="0">
               <tr><td>Red:</td><td><?php print($R) ?></td></tr>
               <tr><td>Green:</td><td><?php print($G) ?></td></tr>
               <tr><td>Blue:</td><td><?php print($B) ?></td></tr>
            </table>
        </div>
        
        <div id="HSV">
            <table border="0">
                <tr><td>Hue:</td><td><?php print($H) ?></td></tr>
                <tr><td>Saturation:</td><td><?php print($S) ?></td></tr>
                <tr><td>Brightness:</td><td><?php print($V) ?></td></tr>
            </table>
        </div>

    </body>
</html>