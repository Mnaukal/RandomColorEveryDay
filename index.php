<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title>Random Color of the Day</title>
        <!--
        <link rel="stylesheet" href="style.css">        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        -->
    </head>
    
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
   
    <body style="background:<?php print($color) ?>">
    <div id="title">
        <h2>Today's random color is </h2>
    <h1><?php 
    print($color);
?>
        </h1></div>
    
    </body>
</html>