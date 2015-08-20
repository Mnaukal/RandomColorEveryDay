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
    <body style="background: 
<?php
/*** mysql data ***/
$hostname = 'localhost';
$dbname = 'randomcoloroftheday';
$username = 'randomcoloruser';
$password = 'colorpassword123';

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    /*** echo a message saying we have connected ***/
    
    /*** The SQL SELECT statement ***/
    $sql = "SELECT * FROM colors
            ORDER BY ID DESC
            LIMIT 1;";
    foreach ($dbh->query($sql) as $row)
    {
        print '#'.$row['color'];
    }
    
    
    /*** close the database connection ***/
    $dbh = null; 
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
?>">
    </body>
</html>