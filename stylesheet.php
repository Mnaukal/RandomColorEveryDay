/*
Random Color Every Day
http://randomcoloreveryday.com/
*/
<?php 
header("Content-type: text/css");

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
    $iS = max(min($iS, 100), 0);

    $dS = $iS / 100.0; // Saturation: 0.0-1.0
    $dL = $iL / 100.0; // Lightness:  0.0-1.0
    $dC = (1 - abs(2 * $dL - 1)) * $dS;   // Chroma:     0.0-1.0
    $dH = $H2 / 60.0;  // H-Prime:    0.0-6.0
    $dT = $dH;       // Temp variable
    while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
    $dX = $dC * (1 - abs($dT - 1));

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

    $dM = $dL - $dC / 2;
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

$color = "#";

try {
    if(isset($_GET["color"]) && $_GET["color"] && strlen($_GET["color"]) == 6)
    {
        $color = "#" . $_GET["color"];
        $userColor = true;
    }
    else {
        if(isset($_GET["color"])) {
            echo("/* INVALID COLOR PARAMETER: " . $_GET["color"] . "*/ " . PHP_EOL . PHP_EOL);
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
.randomColorEveryDay {
color: <?php echo($color); ?>;
}
.randomColorEveryDay-back {
background: <?php echo($color); ?>;
}

/*Light or Dark*/
.foreground-text {
color: <?php 
    if($La >= 50)
    {
        echo("#000");
    }
    else
    {
        echo("#FFF");
    } ?>;
}
.foreground-text-back {
background: <?php 
    if($La >= 50)
    {
        echo("#000");
    }
    else
    {
        echo("#FFF");
    } ?>;
}
.foreground-text-gray {
color: <?php 
    if($La >= 50)
    {
        echo("#282828");
    }
    else
    {
        echo("#D7D7D7");
    } ?>;
}
.foreground-text-gray-back {
background: <?php 
    if($La >= 50)
    {
        echo("#282828");
    }
    else
    {
        echo("#D7D7D7");
    } ?>;
}

/*Shades*/
.s1 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.1)) .
                  sprintf("%02X", $G * (1 - 0.1)) .
                  sprintf("%02X", $B * (1 - 0.1))); ?>;
}
.s1-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.1)) .
                       sprintf("%02X", $G * (1 - 0.1)) .
                       sprintf("%02X", $B * (1 - 0.1)));
?>;
}
.s2 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.2)) .
                  sprintf("%02X", $G * (1 - 0.2)) .
                  sprintf("%02X", $B * (1 - 0.2))); ?>;
}
.s2-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.3)) .
                       sprintf("%02X", $G * (1 - 0.3)) .
                       sprintf("%02X", $B * (1 - 0.3)));
?>;
}
.s3 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.3)) .
                  sprintf("%02X", $G * (1 - 0.3)) .
                  sprintf("%02X", $B * (1 - 0.3))); ?>;
}
.s3-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.3)) .
                       sprintf("%02X", $G * (1 - 0.3)) .
                       sprintf("%02X", $B * (1 - 0.3)));
?>;
}
.s4 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.4)) .
                  sprintf("%02X", $G * (1 - 0.4)) .
                  sprintf("%02X", $B * (1 - 0.4))); ?>;
}
.s4-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.4)) .
                       sprintf("%02X", $G * (1 - 0.4)) .
                       sprintf("%02X", $B * (1 - 0.4)));
?>;
}
.s5 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.5)) .
                  sprintf("%02X", $G * (1 - 0.5)) .
                  sprintf("%02X", $B * (1 - 0.5))); ?>;
}
.s5-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.5)) .
                       sprintf("%02X", $G * (1 - 0.5)) .
                       sprintf("%02X", $B * (1 - 0.5)));
?>;
}
.s6 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.6)) .
                  sprintf("%02X", $G * (1 - 0.6)) .
                  sprintf("%02X", $B * (1 - 0.6))); ?>;
}
.s6-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.6)) .
                       sprintf("%02X", $G * (1 - 0.6)) .
                       sprintf("%02X", $B * (1 - 0.6)));
?>;
}
.s7 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.7)) .
                  sprintf("%02X", $G * (1 - 0.7)) .
                  sprintf("%02X", $B * (1 - 0.7))); ?>;
}
.s7-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.7)) .
                       sprintf("%02X", $G * (1 - 0.7)) .
                       sprintf("%02X", $B * (1 - 0.7)));
?>;
}
.s8 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.8)) .
                  sprintf("%02X", $G * (1 - 0.8)) .
                  sprintf("%02X", $B * (1 - 0.8))); ?>;
}
.s8-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.8)) .
                       sprintf("%02X", $G * (1 - 0.8)) .
                       sprintf("%02X", $B * (1 - 0.8)));
?>;
}
.s9 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 0.9)) .
                  sprintf("%02X", $G * (1 - 0.9)) .
                  sprintf("%02X", $B * (1 - 0.9))); ?>;
}
.s9-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 0.9)) .
                       sprintf("%02X", $G * (1 - 0.9)) .
                       sprintf("%02X", $B * (1 - 0.9)));
?>;
}
.s10 {
color: <?php echo("#" .
                  sprintf("%02X", $R * (1 - 1)) .
                  sprintf("%02X", $G * (1 - 1)) .
                  sprintf("%02X", $B * (1 - 1))); ?>;
}
.s10-back {
background: <?php echo("#" .
                       sprintf("%02X", $R * (1 - 1)) .
                       sprintf("%02X", $G * (1 - 1)) .
                       sprintf("%02X", $B * (1 - 1)));
?>
}

/*Tints*/
.t1 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.1 + $R * (1 - 0.1)) .
                  sprintf("%02X", 255 * 0.1 + $G * (1 - 0.1)) .
                  sprintf("%02X", 255 * 0.1 + $B * (1 - 0.1))); ?>;
}
.t1-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.1 + $R * (1 - 0.1)) .
                       sprintf("%02X", 255 * 0.1 + $G * (1 - 0.1)) .
                       sprintf("%02X", 255 * 0.1 + $B * (1 - 0.1)));
?>
}
.t2 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.2 + $R * (1 - 0.2)) .
                  sprintf("%02X", 255 * 0.2 + $G * (1 - 0.2)) .
                  sprintf("%02X", 255 * 0.2 + $B * (1 - 0.2))); ?>;
}
.t2-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.2 + $R * (1 - 0.2)) .
                       sprintf("%02X", 255 * 0.2 + $G * (1 - 0.2)) .
                       sprintf("%02X", 255 * 0.2 + $B * (1 - 0.2)));
?>
}
.t3 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.3 + $R * (1 - 0.3)) .
                  sprintf("%02X", 255 * 0.3 + $G * (1 - 0.3)) .
                  sprintf("%02X", 255 * 0.3 + $B * (1 - 0.3))); ?>;
}
.t3-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.3 + $R * (1 - 0.3)) .
                       sprintf("%02X", 255 * 0.3 + $G * (1 - 0.3)) .
                       sprintf("%02X", 255 * 0.3 + $B * (1 - 0.3)));
?>
}
.t4 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.4 + $R * (1 - 0.4)) .
                  sprintf("%02X", 255 * 0.4 + $G * (1 - 0.4)) .
                  sprintf("%02X", 255 * 0.4 + $B * (1 - 0.4))); ?>;
}
.t4-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.4 + $R * (1 - 0.4)) .
                       sprintf("%02X", 255 * 0.4 + $G * (1 - 0.4)) .
                       sprintf("%02X", 255 * 0.4 + $B * (1 - 0.4)));
?>
}
.t5 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.5 + $R * (1 - 0.5)) .
                  sprintf("%02X", 255 * 0.5 + $G * (1 - 0.5)) .
                  sprintf("%02X", 255 * 0.5 + $B * (1 - 0.5))); ?>;
}
.t5-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.5 + $R * (1 - 0.5)) .
                       sprintf("%02X", 255 * 0.5 + $G * (1 - 0.5)) .
                       sprintf("%02X", 255 * 0.5 + $B * (1 - 0.5)));
?>
}
.t6 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.6 + $R * (1 - 0.6)) .
                  sprintf("%02X", 255 * 0.6 + $G * (1 - 0.6)) .
                  sprintf("%02X", 255 * 0.6 + $B * (1 - 0.6))); ?>;
}
.t6-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.6 + $R * (1 - 0.6)) .
                       sprintf("%02X", 255 * 0.6 + $G * (1 - 0.6)) .
                       sprintf("%02X", 255 * 0.6 + $B * (1 - 0.6)));
?>
}
.t7 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.7 + $R * (1 - 0.7)) .
                  sprintf("%02X", 255 * 0.7 + $G * (1 - 0.7)) .
                  sprintf("%02X", 255 * 0.7 + $B * (1 - 0.7))); ?>;
}
.t7-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.7 + $R * (1 - 0.7)) .
                       sprintf("%02X", 255 * 0.7 + $G * (1 - 0.7)) .
                       sprintf("%02X", 255 * 0.7 + $B * (1 - 0.7)));
?>
}
.t8 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.8 + $R * (1 - 0.8)) .
                  sprintf("%02X", 255 * 0.8 + $G * (1 - 0.8)) .
                  sprintf("%02X", 255 * 0.8 + $B * (1 - 0.8))); ?>;
}
.t8-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.8 + $R * (1 - 0.8)) .
                       sprintf("%02X", 255 * 0.8 + $G * (1 - 0.8)) .
                       sprintf("%02X", 255 * 0.8 + $B * (1 - 0.8)));
?>
}
.t9 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 0.9 + $R * (1 - 0.9)) .
                  sprintf("%02X", 255 * 0.9 + $G * (1 - 0.9)) .
                  sprintf("%02X", 255 * 0.9 + $B * (1 - 0.9))); ?>;
}
.t9-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 0.9 + $R * (1 - 0.9)) .
                       sprintf("%02X", 255 * 0.9 + $G * (1 - 0.9)) .
                       sprintf("%02X", 255 * 0.9 + $B * (1 - 0.9)));
?>
}
.t10 {
color: <?php echo("#" .
                  sprintf("%02X", 255 * 1 + $R * (1 - 1)) .
                  sprintf("%02X", 255 * 1 + $G * (1 - 1)) .
                  sprintf("%02X", 255 * 1 + $B * (1 - 1))); ?>;
}
.t10-back {
background: <?php echo("#" .
                       sprintf("%02X", 255 * 1 + $R * (1 - 1)) .
                       sprintf("%02X", 255 * 1 + $G * (1 - 1)) .
                       sprintf("%02X", 255 * 1 + $B * (1 - 1)));
?>;
}

/*Tones*/
.to1 {
color: <?php echo(rotateHue(0, $H, $Sa - 60, $La)); ?>;
}
.to1-back {
background: <?php echo(rotateHue(0, $H, $Sa - 60, $La)); ?>;
}
.to2 {
color: <?php echo(rotateHue(0, $H, $Sa - 45, $La)); ?>;
}
.to2-back {
background: <?php echo(rotateHue(0, $H, $Sa - 45, $La)); ?>;
}
.to3 {
color: <?php echo(rotateHue(0, $H, $Sa - 30, $La)); ?>;
}
.to3-back {
background: <?php echo(rotateHue(0, $H, $Sa - 30, $La)); ?>;
}
.to4 {
color: <?php echo(rotateHue(0, $H, $Sa - 15, $La)); ?>;
}
.to4-back {
background: <?php echo(rotateHue(0, $H, $Sa - 15, $La)); ?>;
}
.to5 {
color: <?php echo(rotateHue(0, $H, $Sa, $La)); ?>;
}
.to5-back {
background: <?php echo(rotateHue(0, $H, $Sa, $La)); ?>;
}
.to6 {
color: <?php echo(rotateHue(0, $H, $Sa + 15, $La)); ?>;
}
.to6-back {
background: <?php echo(rotateHue(0, $H, $Sa + 15, $La)); ?>;
}
.to7 {
color: <?php echo(rotateHue(0, $H, $Sa + 30, $La)); ?>;
}
.to7-back {
background: <?php echo(rotateHue(0, $H, $Sa + 30, $La)); ?>;
}
.to8 {
color: <?php echo(rotateHue(0, $H, $Sa + 45, $La)); ?>;
}
.to8-back {
background: <?php echo(rotateHue(0, $H, $Sa + 45, $La)); ?>;
}
.to9 {
color: <?php echo(rotateHue(0, $H, $Sa + 60, $La)); ?>;
}
.to9-back {
background: <?php echo(rotateHue(0, $H, $Sa + 60, $La)); ?>;
}

/*Analogous*/
.ana1 {
color: <?php echo(rotateHue(-30, $H, $S, $La)); ?>;
}
.ana1-back {
background: <?php echo(rotateHue(-30, $H, $S, $La));
?>;
}
.ana2 {
color: <?php echo(rotateHue(-20, $H, $S, $La)); ?>;
}
.ana2-back {
background: <?php echo(rotateHue(-20, $H, $S, $La));
?>;
}
.ana3 {
color: <?php  echo(rotateHue(-10, $H, $S, $La)); ?>;
}
.ana3-back {
background: <?php echo(rotateHue(-10, $H, $S, $La));
?>;
}
.ana4 {
color: <?php  print($color); ?>;
}
.ana4-back {
background: <?php print($color);
?>;
}
.ana5 {
color: <?php  echo(rotateHue(10, $H, $S, $La)); ?>;
}
.ana5-back {
background: <?php echo(rotateHue(10, $H, $S, $La));
?>;
}
.ana6 {
color: <?php  echo(rotateHue(20, $H, $S, $La)); ?>;
}
.ana6-back {
background: <?php echo(rotateHue(20, $H, $S, $La));
?>;
}
.ana7 {
color: <?php  echo(rotateHue(30, $H, $S, $La)); ?>;
}
.ana7-back {
background: <?php echo(rotateHue(30, $H, $S, $La));
?>;
}

/*Complementary*/
.com1 {
color: <?php echo(rotateHue(150, $H, $S, $La)); ?>;
}
.com1-back {
background: <?php echo(rotateHue(150, $H, $S, $La));
?>;
}
.com2 {
color: <?php echo(rotateHue(160, $H, $S, $La)); ?>;
}
.com2-back {
background: <?php echo(rotateHue(160, $H, $S, $La));
?>;
}
.com3 {
color: <?php  echo(rotateHue(170, $H, $S, $La)); ?>;
}
.com3-back {
background: <?php echo(rotateHue(170, $H, $S, $La));
?>;
}
.com4 {
color: <?php echo(rotateHue(180, $H, $S, $La)); ?>;
}
.com4-back {
background: <?php echo(rotateHue(180, $H, $S, $La));
?>;
}
.com5 {
color: <?php  echo(rotateHue(190, $H, $S, $La)); ?>;
}
.com5-back {
background: <?php echo(rotateHue(190, $H, $S, $La));
?>;
}
.com6 {
color: <?php  echo(rotateHue(200, $H, $S, $La)); ?>;
}
.com6-back {
background: <?php echo(rotateHue(200, $H, $S, $La));
?>;
}
.com7 {
color: <?php  echo(rotateHue(210, $H, $S, $La)); ?>;
}
.com7-back {
background: <?php echo(rotateHue(210, $H, $S, $La));
?>;
}

/*Similar*/
.sim1 {
color: <?php echo(shiftRGB($R, $G, $B, 30, 0, 0)); ?>;
}
.sim1-back {
background: <?php echo(shiftRGB($R, $G, $B, 30, 0, 0)); ?>;
}
.sim2 {
color: <?php echo(shiftRGB($R, $G, $B, 0, 30, 0)); ?>;
}
.sim2-back {
background: <?php echo(shiftRGB($R, $G, $B, 0, 30, 0)); ?>;
}
.sim3 {
color: <?php echo(shiftRGB($R, $G, $B, 0, 0, 30)); ?>;
}
.sim3-back {
background: <?php echo(shiftRGB($R, $G, $B, 0, 0, 30)); ?>;
}
.sim4 {
color: <?php echo(shiftRGB($R, $G, $B, 0, 0, 0)); ?>;
}
.sim4-back {
background: <?php echo(shiftRGB($R, $G, $B, 0, 0, 0)); ?>;
}
.sim5 {
color: <?php echo(shiftRGB($R, $G, $B, -30, 0, 0)); ?>;
}
.sim5-back {
background: <?php echo(shiftRGB($R, $G, $B, -30, 0, 0)); ?>;
}
.sim6 {
color: <?php echo(shiftRGB($R, $G, $B, 0, -30, 0)); ?>;
}
.sim6-back {
background: <?php echo(shiftRGB($R, $G, $B, 0, -30, 0)); ?>;
}
.sim7 {
color: <?php echo(shiftRGB($R, $G, $B, 0, 0, -30)); ?>;
}
.sim7-back {
background: <?php echo(shiftRGB($R, $G, $B, 0, 0, -30)); ?>;
}

/*Triadic*/
.tri1 {
color: <?php echo(rotateHue(120, $H, $S, $La)); ?>;
}
.tri1-back {
background: <?php echo(rotateHue(120, $H, $S, $La)); ?>;
}
.tri2 {
color: <?php echo(rotateHue(120, $H, $S, $La, true)); ?>;
}
.tri2-back {
background: <?php echo(rotateHue(120, $H, $S, $La, true)); ?>;
}
.tri3 {
color: <?php print($color); ?>;
}
.tri3-back {
background: <?php print($color); ?>;
}
.tri4 {
color: <?php echo(rotateHue(0, $H, $S, $La, true)); ?>;
}
.tri4-back {
background: <?php echo(rotateHue(0, $H, $S, $La, true)); ?>;
}
.tri5 {
color: <?php echo(rotateHue(-120, $H, $S, $La)); ?>;
}
.tri5-back {
background: <?php echo(rotateHue(-120, $H, $S, $La)); ?>;
}
.tri6 {rota
color: <?php echo(rotateHue(-120, $H, $S, $La, true)); ?>;
}
.tri6-back {
background: <?php echo(rotateHue(-120, $H, $S, $La, true)); ?>;
}