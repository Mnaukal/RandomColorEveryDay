<?php
/*** mysql data ***/
$hostname = 'localhost';
$dbname = 'randomcoloroftheday';
$username = 'randomcoloruser';
$password = 'colorpassword123';

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    /*** echo a message saying we have connected ***/
    echo 'Connected to database<br>';
    
    /*** The SQL SELECT statement ***/
    $sql = "SELECT * FROM colors";
    foreach ($dbh->query($sql) as $row)
    {
        print $row['ID'].' - '.$row['color'].' - '.$row['date'].' - '.$row['likes'].'<br />';
    }
    
    
    /*** close the database connection ***/
    $dbh = null; 
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
?>