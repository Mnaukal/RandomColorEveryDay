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
        
        <!--       
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
-->
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
       <!-- Google -->
        <script src="https://apis.google.com/js/platform.js" async defer>
            {lang: 'cs'}
        </script>
       
       
        <div id="title">
            <h2>Today's random color is </h2>
            <h1><?php print($color); ?></h1>
            <div class="fb-share-button" data-href="http://random-color-of-the-day.funsite.cz/" data-layout="box_count"></div>
            <div class="g-plus" data-action="share" data-annotation="vertical-bubble" data-height="60" data-href="http://random-color-of-the-day.funsite.cz/"></div>
                
                 <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://random-color-of-the-day.funsite.cz/">Tweet</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        </div>

    </body>
</html>