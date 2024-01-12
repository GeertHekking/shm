<?php

require_once('class/ajax.class.php');

if (isset($_GET['functie']))  {
    $oAjax = new ajax();
    $oAjax->f_init($_GET['functie']);
}  else  {
    
    $vName = $_GET['user'];
    $vPassword = $_GET['password'];
    $vReply = "ERROR";
    
    if ($vName == 'gghit@gghekking.nl' && $vPassword == "Geert")  {
        $vReply = "OK";
    }
    
    echo $vReply;
}

?>