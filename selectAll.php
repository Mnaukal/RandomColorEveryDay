<?php
try {
    $dbData = parse_ini_file("config.ini");

    $dbh = new PDO("mysql:host={$dbData['hostname']};dbname={$dbData['dbname']}", $dbData['username'], $dbData['password']);    /*** echo a message saying we have connected ***/
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