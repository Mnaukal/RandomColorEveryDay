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

    function rotateHue($amount, $iH, $iS, $iL, $changeLightness = false){
        if($changeLightness){
            if($iL < 50)
                $iL += 30;
            else
                $iL -= 30;
        }

        $H2 = clamp($iH + $amount, 0, 360); //0-360

        $dS = $iS/100.0; // Saturation: 0.0-1.0
        $dL = $iL/100.0; // Lightness:  0.0-1.0
        $dC = (1 - abs(2 * $dL - 1)) * $dS;   // Chroma:     0.0-1.0
        $dH = $H2/60.0;  // H-Prime:    0.0-6.0
        $dT = $dH;       // Temp variable
        while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
        $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link

        if($dH >= 0.0 && $dH < 1.0) {
            $dR = $dC; $dG = $dX; $dB = 0.0;
        } else if($dH >= 1.0 && $dH < 2.0) {
            $dR = $dX; $dG = $dC; $dB = 0.0;
        } else if($dH >= 2.0 && $dH < 3.0) {
            $dR = 0.0; $dG = $dC; $dB = $dX;
        } else if($dH >= 3.0 && $dH < 4.0) {
            $dR = 0.0; $dG = $dX; $dB = $dC;
        } else if($dH >= 4.0 && $dH < 5.0) {
            $dR = $dX; $dG = 0.0; $dB = $dC;
        } else if($dH >= 5.0 && $dH < 6.0) {
            $dR = $dC; $dG = 0.0; $dB = $dX;
        } else {
            $dR = 0.0; $dG = 0.0; $dB = 0.0;
        } 

        $dM  = $dL - $dC / 2;
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

    $color = "#FFFFFF";
    
    try {
        if(isset($_GET["color"]) && $_GET["color"] && strlen($_GET["color"]) == 6)
        {
            $color = "#" . $_GET["color"];
            $userColor = true;
        }
        else {
            if(isset($_GET["color"])) {
                echo("<script>alert('Invalid color')</script>");
            }

            $userColor = false;

            $dbData = parse_ini_file("config.ini");
                        
            $dbh = new PDO("mysql:host={$dbData['hostname']};dbname={$dbData['dbname']}", $dbData['username'], $dbData['password']);
            /*** echo a message saying we have connected ***/

            /*** The SQL SELECT statement ***/
            $sql = "SELECT * FROM colors
                ORDER BY ID DESC
                LIMIT 1;";
            $stm = $dbh->query($sql);

            $result = $stm->fetch(PDO::FETCH_OBJ);

            $color = "#".$result->color;

            /*** close the database connection ***/
            $dbh = null; 
        }

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

        $Sad = $S/100;
        $Vad = $V/100;    

        if((2 - $Sad) * $Vad < 1)
            $Sa = $Sad * $Vad / ((2 - $Sad) * $Vad);
        else
            if($Sad == 0 && $Vad == 1) {
                $Sa = 0;
            } else {
                $Sa = $Sad * $Vad / (2 - (2 - $Sad) * $Vad) * 1;
            }

        $La = (2 - $Sad) * $Vad / 2;

        $Sa = round($Sa * 100);
        $La = round($La * 100);
        //------------------------------------------------
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
    }
    ?>   

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title><?php print($color); ?> - Random Color Every Day</title>
        <link rel="stylesheet" href="style.css"> 
        <link rel="icon" type="image/png" href="color.png" />

        <meta property="fb:app_id" content="424757477717430" />
        <meta property="og:url"           content="http://random-color-of-the-day.funsite.cz" />
        <meta property="og:type"          content="website" />
        <meta property="og:title"         content="Today's random color is <?php print($color); ?>" />
        <meta property="og:description"   content="Random Color Every Day" />
        <meta property="og:image"         content="http://random-color-of-the-day.funsite.cz/color.png" />

        <style>
            html, body {
                color: <?php 
                    if($La >= 50)
                        echo("#000");
                    else 
                        echo("#FFF"); ?>
            }
            
            a {
                color: <?php echo(rotateHue(180, $H, $S, $La, true)); ?>;
            }
        </style>
    <!--       
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
-->
    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "8c7bdcce-3269-4889-97c4-c5b8d8cc770e", doNotHash: true, doNotCopy: true, hashAddressBar: false});</script>

    </head>

<body style="background:<?php print($color) ?>">
    <div id="top"></div>

    <div id="title">
        <h2><?php echo(!$userColor ? "Today's random color is " : "Generated schemes for "); ?></h2>
        <h1><?php print($color); ?></h1>

        <span class='st_sharethis_large' displayText='ShareThis'   st_url="random-color-of-the-day.funsite.cz" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color Every Day"></span>
        <span class='st_facebook_large' displayText='Facebook'     st_url="random-color-of-the-day.funsite.cz" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color Every Day"></span>
        <span class='st_googleplus_large' displayText='Google +'   st_url="random-color-of-the-day.funsite.cz" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color Every Day"></span>
        <span class='st_twitter_large' displayText='Tweet'         st_url="random-color-of-the-day.funsite.cz" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color Every Day"></span>
        <span class='st_pinterest_large' displayText='Pinterest'   st_url="random-color-of-the-day.funsite.cz" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color Every Day"></span>
        <span class='st_email_large' displayText='Email'           st_url="random-color-of-the-day.funsite.cz" st_title="Today's random color is <?php print($color); ?>" st_image="http://random-color-of-the-day.funsite.cz/color.png" st_summary="Random Color Every Day"></span>

    </div>

    <div id="menu">

        <ul>
            <li><a href="#top"><?php print($color); ?></a></li>
            <li><a href="#info">Color info</a></li>
            <li><a href="#schemes">Color Schemes</a></li>
            <li><a href="#generate">Generate Schemes</a></li>
            <li><a href="https://github.com/Mnaukal/RandomColorEveryDay" target="_blank">Use this on your website</a></li>
            <li><a href="#about">About</a></li>
        </ul>
    </div>

    <div id="page">
        <div id="info">
            <div id="colorInfo">
                <?php 
                if($La >= 50)
                {
                    echo("This color is light, we recommend dark (black) text.");
                }
                else {
                    echo("This color is dark, we recommend light (white) text.");
                } ?> 
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
                <h2>Shades</h2>
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
                <h2>Tints</h2>
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
                               sprintf("%02X", 255 * 0.2 + $R * (1 - 0.2)) .
                               sprintf("%02X", 255 * 0.2 + $G * (1 - 0.2)) .
                               sprintf("%02X", 255 * 0.2 + $B * (1 - 0.2)));
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
                <h2>Analogous</h2>
                <div class="color" style="background: <?php echo(rotateHue(-30, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(-30, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(-20, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(-20, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(-10, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(-10, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php print($color); ?>">
                    <?php print($color);
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(10, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(10, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(20, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(20, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(30, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(30, $H, $S, $La));
                    ?>
                </div>
            </div>       

            <div class="scheme" id="complementary">
                <h2>Complementary</h2>
                <div class="color" style="background: <?php echo(rotateHue(150, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(150, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(160, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(160, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(170, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(170, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(180, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(180, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(190, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(190, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(200, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(200, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(210, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(210, $H, $S, $La));
                    ?>
                </div>
            </div>

            <div class="scheme" id="similar">
                <h2>Similar</h2>
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
                <h2>Triadic</h2>
                <div class="color" style="background: <?php echo(rotateHue(120, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(120, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(120, $H, $S, $La, true)); ?>">
                    <?php echo(rotateHue(120, $H, $S, $La, true));
                    ?>
                </div>
                <div class="color" style="background: <?php print($color); ?>">
                    <?php print($color); ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(0, $H, $S, $La, true)); ?>">
                    <?php echo(rotateHue(0, $H, $S, $La, true));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(-120, $H, $S, $La)); ?>">
                    <?php echo(rotateHue(-120, $H, $S, $La));
                    ?>
                </div>
                <div class="color" style="background: <?php echo(rotateHue(-120, $H, $S, $La, true)); ?>">
                    <?php echo(rotateHue(-120, $H, $S, $La, true));
                    ?>
                </div>
            </div>

        </div>

        <div id="generate">
            <h1>Generate schemes</h1>
            <form method="get">
                <p>
                    #<input name="color" type="text" maxlength="6">
                    <input type="submit" value="Generate">
                </p>
            </form>
        </div>
        
        <div id="usage">
            <h1>I WANT THIS ON MY WEBSITE!</h1>
            <p>You can have it. For free. Documentation and source code is available on GitHub: <a href="https://github.com/Mnaukal/RandomColorEveryDay" target="_blank">https://github.com/Mnaukal/RandomColorEveryDay</a></p>
            <p>I would also feel free to let me know if you want to use it (you don't have to, but I am just curious) or if you have any problems (you can find contact on GitHub or <a href="http://thetopfer.funsite.cz/" target="_blank">my website</a>).</p>
        </div>
        
        <div id="about">
            <h1>About</h1>
            <p>Well, what to say here... It's just my project to play with and learn some PHP. If you like it, you can find more on <a href="" target="_blank">my GitHub.</a> and <a href="http://thetopfer.funsite.cz/" target="_blank">my website</a></p>
        </div>

        <div id="advertisement">
            <h2>Advertisement</h2>
            <p> <endora/> </p>
        </div>
        
        <div id="previous">
            <h1>Previous daily random colors</h1>
            <?php
                try {
                    $dbData = parse_ini_file("config.ini");

                    $dbh = new PDO("mysql:host={$dbData['hostname']};dbname={$dbData['dbname']}", $dbData['username'], $dbData['password']);    /*** echo a message saying we have connected ***/

                    /*** The SQL SELECT statement ***/
                    $sql = "SELECT * FROM colors
                            ORDER BY ID DESC";
                    foreach ($dbh->query($sql) as $row)
                    {
                        $R = hexdec(substr($row['color'], 0, 2));
                        $G = hexdec(substr($row['color'], 2, 2));
                        $B = hexdec(substr($row['color'], 4, 2));

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
                        }   

                        $La = (2 - $S) * $V / 2;

                        $La = round($La * 100);

                        if($La >= 50)
                        {
                            $foreground = "#000";
                        }
                        else
                        {
                            $foreground = "#FFF";
                        }                        
                        
                        echo("
                        <a href=index.php?color={$row['color']}>
                            <div class='previousColor' style='background: #{$row['color']}; color: $foreground'>
                                <span>
                                    #{$row['color']}<br>
                                    {$row['date']}
                                </span>
                            </div>
                        </a>
                        ");
                    }


                    /*** close the database connection ***/
                    $dbh = null; 
                }
                catch(PDOException $e)
                {
                    echo $e->getMessage();
                }
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