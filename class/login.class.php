<?php

require_once('class/page.class.php');

class login extends page  {

    public function f_setRejection($ip_Reason)  {
        $this->m_Rejection = $ip_Reason;
    }
    
    private function f_getRejection()  {
        return $this->m_Rejection;
    }
    
    public function start()  {
        $this->f_setBootstrap('Bootstrap');
        $this->f_head();
        $this->f_banner('Financi&euml;el overzicht');
        $this->f_login();
        $this->f_footer();
    }
      
    private function f_login()  {
        $vRejection = $this->f_getRejection();
        echo "<div class=\"row\">\n";
        echo "  <div class=\"col-sm-1\" style=\"background-color:lavender;\"></div>\n";
        echo "  <div class=\"col-sm-6\" style=\"background-color:lavenderblush;\">\n";
        echo "      <form method=\"post\" action=\"start.php\">\n";
        if ($vRejection != '')  {
            echo "        <span class='rejection'>".$vRejection."</span><br />\n";
        }
        echo "        <div class=\"form-group\">\n";
        echo "          <label for=\"I_User\">User Code</label>\n";
        echo "          <input type=\"text\" class=\"form-control\" id=\"I_User\" name=\"I_User\" aria-describedby=\"emailHelp\" placeholder=\"Enter User Code\">\n";
        echo "          <small id=\"emailHelp\" class=\"form-text text-muted\">Uw Gebruikers Code</small>\n";
        echo "        </div>\n";
        echo "        <div class=\"form-group\">\n";
        echo "          <label for=\"I_Password\">Password</label>\n";
        echo "          <input type=\"password\" class=\"form-control\" id=\"I_Password\" name=\"I_Password\" placeholder=\"Password\">\n";
        echo "        </div>\n";
        echo "        <div class=\"form-check\">\n";
        echo "          <input type=\"checkbox\" class=\"form-check-input\" id=\"chk_beheerder\" name=\"chk_beheerder\">\n";
        echo "          <label class=\"form-check-label\" for=\"chk_beheerder\">Beheerder</label>\n";
        echo "        </div>\n";
        echo "        <button type=\"submit\" class=\"btn btn-primary\">Aanmelden</button>\n";
        echo "      </form>\n";
        echo "  </div>\n";
        echo "  <div class=\"col-sm-1\" style=\"background-color:lavender;\"></div>\n";
        echo "</div>\n";
    }

}

?>