<!doctype html>
<html>    
    <?php
    $color = "#FFFFFF";

    try {
        if(isset($_GET["color"]) && $_GET["color"] && strlen($_GET["color"]) == 6)
        {
            $color = "#" . $_GET["color"];
            $userColor = true;
        }
        else {
            if(isset($_GET["color"])) {
                echo("<script>alert('Invalid color: " . $_GET["color"] . "')</script>");
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
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
    }
    ?>   

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php print($color); ?> - Random Color Every Day</title>
        <link rel="icon" type="image/png" href="color.png" />
        
        <meta name="theme-color" content="<?php print($color); ?>">
        
        <script>
            function toggleFullscreen(elem) {
                elem = elem || document.documentElement;
                if (!document.fullscreenElement && !document.mozFullScreenElement &&
                    !document.webkitFullscreenElement && !document.msFullscreenElement) {
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen();
                    } else if (elem.msRequestFullscreen) {
                        elem.msRequestFullscreen();
                    } else if (elem.mozRequestFullScreen) {
                        elem.mozRequestFullScreen();
                    } else if (elem.webkitRequestFullscreen) {
                        elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                }
            }
        </script>
    </head>

    <body style="background:<?php print($color); ?>; margin: 0px; position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px; " ondblclick="toggleFullscreen();">
        
    </body>
</html>