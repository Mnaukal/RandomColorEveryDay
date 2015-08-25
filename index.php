<!doctype html>
<html>    
    <?php
function clamp($value, $min, $max){
    if($value >= $max)
        $value -= $max;
    if($value < $min)
        $value += $max;
    return $value;
}

function rotateHue($amount, $iH, $iS, $iV, $changeValue = false){
    if($changeValue){
        if($iV < 50)
            $iV += 20;
        else
            $iV -= 20;
    }
    
    $H2 = clamp($iH + $amount, 0, 360);
    
    $dS = $iS/100.0; // Saturation: 0.0-1.0
        $dV = $iV/100.0; // Lightness:  0.0-1.0
        $dC = $dV*$dS;   // Chroma:     0.0-1.0
        $dH = $H2/60.0;  // H-Prime:    0.0-6.0
        $dT = $dH;       // Temp variable
        while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
        $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link
        switch($dH) {
            case($dH >= 0.0 && $dH < 1.0):
                $dR = $dC; $dG = $dX; $dB = 0.0; break;
            case($dH >= 1.0 && $dH < 2.0):
                $dR = $dX; $dG = $dC; $dB = 0.0; break;
            case($dH >= 2.0 && $dH < 3.0):
                $dR = 0.0; $dG = $dC; $dB = $dX; break;
            case($dH >= 3.0 && $dH < 4.0):
                $dR = 0.0; $dG = $dX; $dB = $dC; break;
            case($dH >= 4.0 && $dH < 5.0):
                $dR = $dX; $dG = 0.0; $dB = $dC; break;
            case($dH >= 5.0 && $dH < 6.0):
                $dR = $dC; $dG = 0.0; $dB = $dX; break;
            default:
                $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
        }
        $dM  = $dV - $dC;
        $dR += $dM; $dG += $dM; $dB += $dM;
        $dR *= 255; $dG *= 255; $dB *= 255;
    
    return "#".sprintf("%02X", round($dR)).sprintf("%02X", round($dG)).sprintf("%02X", round($dB));
}

function shiftRGB($iR, $iG, $iB, $dR, $dG, $dB) {
    return "#" .
        sprintf("%02X", max(min($iR + $dR, 255), 0)) .
        sprintf("%02X", max(min($iG + $dG, 255), 0)) .
        sprintf("%02X", max(min($iB + $dB, 255), 0));
}

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

    //HSL -------------------------------------
    $Ha = $H;
    if((200 - $S) * $V < 10000)
        $Sa = $S * $V / ((200 - $S) * $V);
    else
        $Sa = $S * $V / (20000 - (200 - $S) * $V) * 100;
        
    $La = (200 - $S) * $V / 200;
    
    $Sa = round($Sa);
    $La = round($La);
    //------------------------------------------------

    

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

   
        <style>
            html, body {
                color: <?php 
if($La > 50)
    echo("#000");
else 
    echo("#FFF"); ?>
            }
        </style>
    </head>
    <!--       
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
-->

    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "8c7bdcce-3269-4889-97c4-c5b8d8cc770e", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

    </head>

<body style="background:<?php print($color) ?>">
    <div id="top"></div>
   
   
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

    <!--<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">
        stLight.options({
            publisher:'12345',
        });
    </script>-->


    <div id="title">
        <h2>Today's random color is </h2>
        <h1><?php print($color); ?></h1>

        <!--<span st_url="http://random-color-of-the-day.funsite.cz/" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color of the Day" class='st_sharethis_vcount' displayText='ShareThis'></span>-->
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
    
    <div id="menu">

        <ul>
            <li><a href="#top"><?php print($color); ?></a></li>
            <li><a href="#info">Color info</a></li>
            <li><a href="#schemes">Color Schemes</a></li>
            <li><a href="#generate">Generate</a></li>
            <li><a href="#about">About</a></li>
        </ul>
    </div>
    
    <div id="info">
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
                <tr><td>Value:</td><td><?php print($V) ?></td></tr>
            </table>
        </div>
        
        <div id="HSL">
            <table border="0">
                <tr><td>Hue:</td><td><?php print($Ha) ?></td></tr>
                <tr><td>Saturation:</td><td><?php print($Sa) ?></td></tr>
                <tr><td>Lightness:</td><td><?php print($La) ?></td></tr>
            </table>
        </div>
    </div>

    <div id="schemes">
        <h1>Color Schemes</h1>
        <div class="scheme" id="shades">
            <h3>Shades</h3>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.1)) .
                                                             sprintf("%02X", $G * (1 - 0.1)) .
                                                             sprintf("%02X", $B * (1 - 0.1))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.1)) .
                           sprintf("%02X", $G * (1 - 0.1)) .
                           sprintf("%02X", $B * (1 - 0.1)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.2)) .
                                                             sprintf("%02X", $G * (1 - 0.2)) .
                                                             sprintf("%02X", $B * (1 - 0.2))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.3)) .
                           sprintf("%02X", $G * (1 - 0.3)) .
                           sprintf("%02X", $B * (1 - 0.3)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.3)) .
                                                             sprintf("%02X", $G * (1 - 0.3)) .
                                                             sprintf("%02X", $B * (1 - 0.3))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.3)) .
                           sprintf("%02X", $G * (1 - 0.3)) .
                           sprintf("%02X", $B * (1 - 0.3)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.4)) .
                                                             sprintf("%02X", $G * (1 - 0.4)) .
                                                             sprintf("%02X", $B * (1 - 0.4))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.4)) .
                           sprintf("%02X", $G * (1 - 0.4)) .
                           sprintf("%02X", $B * (1 - 0.4)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.5)) .
                                                             sprintf("%02X", $G * (1 - 0.5)) .
                                                             sprintf("%02X", $B * (1 - 0.5))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.5)) .
                           sprintf("%02X", $G * (1 - 0.5)) .
                           sprintf("%02X", $B * (1 - 0.5)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.6)) .
                                                             sprintf("%02X", $G * (1 - 0.6)) .
                                                             sprintf("%02X", $B * (1 - 0.6))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.6)) .
                           sprintf("%02X", $G * (1 - 0.6)) .
                           sprintf("%02X", $B * (1 - 0.6)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.7)) .
                                                             sprintf("%02X", $G * (1 - 0.7)) .
                                                             sprintf("%02X", $B * (1 - 0.7))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.7)) .
                           sprintf("%02X", $G * (1 - 0.7)) .
                           sprintf("%02X", $B * (1 - 0.7)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.8)) .
                                                             sprintf("%02X", $G * (1 - 0.8)) .
                                                             sprintf("%02X", $B * (1 - 0.8))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.8)) .
                           sprintf("%02X", $G * (1 - 0.8)) .
                           sprintf("%02X", $B * (1 - 0.8)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 0.9)) .
                                                             sprintf("%02X", $G * (1 - 0.9)) .
                                                             sprintf("%02X", $B * (1 - 0.9))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 0.9)) .
                           sprintf("%02X", $G * (1 - 0.9)) .
                           sprintf("%02X", $B * (1 - 0.9)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", $R * (1 - 1)) .
                                                             sprintf("%02X", $G * (1 - 1)) .
                                                             sprintf("%02X", $B * (1 - 1))); ?>">
                <?php echo("#" .
                           sprintf("%02X", $R * (1 - 1)) .
                           sprintf("%02X", $G * (1 - 1)) .
                           sprintf("%02X", $B * (1 - 1)));
                ?>
            </div>
        </div>        
        
        <div class="scheme" id="tints">
            <h3>Tints</h3>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.1 + $R * (1 - 0.1)) .
                                                             sprintf("%02X", 255 * 0.1 + $G * (1 - 0.1)) .
                                                             sprintf("%02X", 255 * 0.1 + $B * (1 - 0.1))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.1 + $R * (1 - 0.1)) .
                           sprintf("%02X", 255 * 0.1 + $G * (1 - 0.1)) .
                           sprintf("%02X", 255 * 0.1 + $B * (1 - 0.1)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.2 + $R * (1 - 0.2)) .
                                                             sprintf("%02X", 255 * 0.2 + $G * (1 - 0.2)) .
                                                             sprintf("%02X", 255 * 0.2 + $B * (1 - 0.2))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.2 + $R * (1 - 0.3)) .
                           sprintf("%02X", 255 * 0.2 + $G * (1 - 0.3)) .
                           sprintf("%02X", 255 * 0.2 + $B * (1 - 0.3)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.3 + $R * (1 - 0.3)) .
                                                             sprintf("%02X", 255 * 0.3 + $G * (1 - 0.3)) .
                                                             sprintf("%02X", 255 * 0.3 + $B * (1 - 0.3))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.3 + $R * (1 - 0.3)) .
                           sprintf("%02X", 255 * 0.3 + $G * (1 - 0.3)) .
                           sprintf("%02X", 255 * 0.3 + $B * (1 - 0.3)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.4 + $R * (1 - 0.4)) .
                                                             sprintf("%02X", 255 * 0.4 + $G * (1 - 0.4)) .
                                                             sprintf("%02X", 255 * 0.4 + $B * (1 - 0.4))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.4 + $R * (1 - 0.4)) .
                           sprintf("%02X", 255 * 0.4 + $G * (1 - 0.4)) .
                           sprintf("%02X", 255 * 0.4 + $B * (1 - 0.4)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.5 + $R * (1 - 0.5)) .
                                                             sprintf("%02X", 255 * 0.5 + $G * (1 - 0.5)) .
                                                             sprintf("%02X", 255 * 0.5 + $B * (1 - 0.5))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.5 + $R * (1 - 0.5)) .
                           sprintf("%02X", 255 * 0.5 + $G * (1 - 0.5)) .
                           sprintf("%02X", 255 * 0.5 + $B * (1 - 0.5)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.6 + $R * (1 - 0.6)) .
                                                             sprintf("%02X", 255 * 0.6 + $G * (1 - 0.6)) .
                                                             sprintf("%02X", 255 * 0.6 + $B * (1 - 0.6))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.6 + $R * (1 - 0.6)) .
                           sprintf("%02X", 255 * 0.6 + $G * (1 - 0.6)) .
                           sprintf("%02X", 255 * 0.6 + $B * (1 - 0.6)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.7 + $R * (1 - 0.7)) .
                                                             sprintf("%02X", 255 * 0.7 + $G * (1 - 0.7)) .
                                                             sprintf("%02X", 255 * 0.7 + $B * (1 - 0.7))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.7 + $R * (1 - 0.7)) .
                           sprintf("%02X", 255 * 0.7 + $G * (1 - 0.7)) .
                           sprintf("%02X", 255 * 0.7 + $B * (1 - 0.7)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.8 + $R * (1 - 0.8)) .
                                                             sprintf("%02X", 255 * 0.8 + $G * (1 - 0.8)) .
                                                             sprintf("%02X", 255 * 0.8 + $B * (1 - 0.8))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.8 + $R * (1 - 0.8)) .
                           sprintf("%02X", 255 * 0.8 + $G * (1 - 0.8)) .
                           sprintf("%02X", 255 * 0.8 + $B * (1 - 0.8)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 0.9 + $R * (1 - 0.9)) .
                                                             sprintf("%02X", 255 * 0.9 + $G * (1 - 0.9)) .
                                                             sprintf("%02X", 255 * 0.9 + $B * (1 - 0.9))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 0.9 + $R * (1 - 0.9)) .
                           sprintf("%02X", 255 * 0.9 + $G * (1 - 0.9)) .
                           sprintf("%02X", 255 * 0.9 + $B * (1 - 0.9)));
                ?>
            </div>
            <div class="color" style="background: <?php echo("#" .
                                                             sprintf("%02X", 255 * 1 + $R * (1 - 1)) .
                                                             sprintf("%02X", 255 * 1 + $G * (1 - 1)) .
                                                             sprintf("%02X", 255 * 1 + $B * (1 - 1))); ?>">
                <?php echo("#" .
                           sprintf("%02X", 255 * 1 + $R * (1 - 1)) .
                           sprintf("%02X", 255 * 1 + $G * (1 - 1)) .
                           sprintf("%02X", 255 * 1 + $B * (1 - 1)));
                ?>
            </div>
    </div>

        <div class="scheme" id="analogous">
            <h3>Analogous</h3>
            <div class="color" style="background: <?php echo(rotateHue(-30, $H, $S, $V)); ?>">
                <?php echo(rotateHue(-30, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(-20, $H, $S, $V)); ?>">
                <?php echo(rotateHue(-20, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(-10, $H, $S, $V)); ?>">
                <?php echo(rotateHue(-10, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(0, $H, $S, $V)); ?>">
                <?php echo(rotateHue(0, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(10, $H, $S, $V)); ?>">
                <?php echo(rotateHue(10, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(20, $H, $S, $V)); ?>">
                <?php echo(rotateHue(20, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(30, $H, $S, $V)); ?>">
                <?php echo(rotateHue(30, $H, $S, $V));
                ?>
            </div>
        </div>       
       
        <div class="scheme" id="complementary">
            <h3>Complementary</h3>
            <div class="color" style="background: <?php echo(rotateHue(150, $H, $S, $V)); ?>">
                <?php echo(rotateHue(150, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(160, $H, $S, $V)); ?>">
                <?php echo(rotateHue(160, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(170, $H, $S, $V)); ?>">
                <?php echo(rotateHue(170, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(180, $H, $S, $V)); ?>">
                <?php echo(rotateHue(180, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(190, $H, $S, $V)); ?>">
                <?php echo(rotateHue(190, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(200, $H, $S, $V)); ?>">
                <?php echo(rotateHue(200, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(210, $H, $S, $V)); ?>">
                <?php echo(rotateHue(210, $H, $S, $V));
                ?>
            </div>
        </div>
        
        <div class="scheme" id="similar">
            <h3>Similar</h3>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, 30, 0, 0)); ?>">
                <?php echo(shiftRGB($R, $G, $B, 30, 0, 0));
                ?>
            </div>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, 0, 30, 0)); ?>">
                <?php echo(shiftRGB($R, $G, $B, 0, 30, 0));
                ?>
            </div>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, 0, 0, 30)); ?>">
                <?php echo(shiftRGB($R, $G, $B, 0, 0, 30));
                ?>
            </div>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, 0, 0, 0)); ?>">
                <?php echo(shiftRGB($R, $G, $B, 0, 0, 0));
                ?>
            </div>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, -30, 0, 0)); ?>">
                <?php echo(shiftRGB($R, $G, $B, -30, 0, 0));
                ?>
            </div>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, 0, -30, 0)); ?>">
                <?php echo(shiftRGB($R, $G, $B, 0, -30, 0));
                ?>
            </div>
            <div class="color" style="background: <?php echo(shiftRGB($R, $G, $B, 0, 0, -30)); ?>">
                <?php echo(shiftRGB($R, $G, $B, 0, 0, -30));
                ?>
            </div>
        </div>
        
        <div class="scheme" id="triadic">
            <h3>Triadic</h3>
            <div class="color" style="background: <?php echo(rotateHue(120, $H, $S, $V)); ?>">
                <?php echo(rotateHue(120, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(120, $H, $S, $V, true)); ?>">
                <?php echo(rotateHue(120, $H, $S, $V, true));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(0, $H, $S, $V)); ?>">
                <?php echo(rotateHue(0, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(0, $H, $S, $V, true)); ?>">
                <?php echo(rotateHue(0, $H, $S, $V, true));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(-120, $H, $S, $V)); ?>">
                <?php echo(rotateHue(-120, $H, $S, $V));
                ?>
            </div>
            <div class="color" style="background: <?php echo(rotateHue(-120, $H, $S, $V, true)); ?>">
                <?php echo(rotateHue(-120, $H, $S, $V, true));
                ?>
            </div>
        </div>
        
        <script>
            var colors = document.getElementsByClassName("color");
            for (var i = 0; i < colors.length; i++)
                colors[i].addEventListener("click", click);

            function click() {
                var text = this.innerText;
                window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
            }
        </script>
</body>
</html>