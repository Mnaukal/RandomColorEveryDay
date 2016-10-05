<?php
// require codebird
require_once('codebird-php/codebird.php');

try {
    $dbData = parse_ini_file("config.ini");

    $dbh = new PDO("mysql:host={$dbData['hostname']};dbname={$dbData['dbname']}", $dbData['username'], $dbData['password']);    /*** echo a message saying we have connected ***/
    echo 'Connected to database<br>';
    
    /*** The SQL SELECT statement ***/
    /*** The SQL SELECT statement ***/
    $sql = "SELECT * FROM colors
                ORDER BY ID DESC
                LIMIT 1;";
    $stm = $dbh->query($sql);

    $result = $stm->fetch(PDO::FETCH_OBJ);

    $color = "#".$result->color;
    
    // post to Twitter - @theTopfer
    \Codebird\Codebird::setConsumerKey($dbData['twitter_ConsumerKey'], $dbData['twitter_ConsumerSecret']);
    $cb = \Codebird\Codebird::getInstance();
    $cb->setToken($dbData['twitter_AccessToken'], $dbData['twitter_AccessTokenSecret']);

    $params = array(
        'status' => "Today's random color is " . $color . " http://randomcoloreveryday.com/ #RandomColorEveryDay",
        'media[]' => 'color.png'
    );
    $reply = $cb->statuses_updateWithMedia($params);
    
    var_dump($reply);    
    
    /*** close the database connection ***/
    $dbh = null; 
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
?>