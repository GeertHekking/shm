<?php

require_once('class/page.class.php');
require_once('class/bootstrap.class.php');
require_once('class/menu.class.php');

class initpage extends page  {
    
    public function f_init()  {
        // Paint init page, using bootstrap
        $this->f_setBootstrap('Bootstrap');
        $this->f_head();
        $this->f_banner('Financi&euml;el overzicht');
    
        $oMenu =  new menu();
        $oMenu->f_menu();
    
        $this->f_footer();
    }

}

?>