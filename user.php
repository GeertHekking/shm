<?php

require_once('class/user.class.php');
require_once('../../private_incl/shm.incl');

$oUser = new user($m_connection);
$oUser->f_init();

?>