<?PHP

    class page  {

        private $m_Bootstrap = false;
        private $o_Bootstrap;
        private $m_Smallbanner = false;
        private $m_script = array();
        private $m_style = array();
        
        // --------------------------------------------- //
        // G E T T E R S   /   S E T T E R S             //
        // --------------------------------------------- //
        public function f_setBootstrap($ip_Bootstrap)  {
            if ($ip_Bootstrap == "yes"  ||
                $ip_Bootstrap == "Bootstrap")  {
                require_once('bootstrap.class.php');
                $this->m_Bootstrap = true;
                $this->oBootstrap = new bootstrap();
            }  else {
                $this->m_Bootstrap = false;
            }
        }
        
        public function f_getBootstrap()  {
            return $this->m_Bootstrap;
        }
        
        public function f_setScript($ip_script)  {
            $this->m_script[] = $ip_script;
        }
        
        public function f_getScript()  {
            return $this->m_script;
        }
        
        public function f_setStyle($ip_style)  {
            $this->m_style[] = $ip_style;
        }
        
        public function f_getStyle()  {
            return $this->m_style;
        }
        
        public function f_smallBanner()  {
            $this->m_Smallbanner = true;
        }
        
        // -------------------------------------------- //
        // Page functions                               //
        // -------------------------------------------- //
        public function f_head()  {
        
            require_once('class/bootstrap.class.php');
            $v_Bootstrap = $this->f_getBootstrap();
            
            echo "<!DOCTYPE html>\n";
            echo "<html>\n";
            echo "<head>\n";
            echo "   <!-- PAGE.CLASS.PHP f_head-->\n";
            echo "   <title>Huishouding</title>\n";
            echo "   <meta charset=\"utf-8\">\n";
            echo "   <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
            echo "        <script src=\"js/jquery-3.5.1.min.js\"></script>";
            if ($v_Bootstrap == true)  {
                $this->oBootstrap->f_header();
            }
            // Insert script(s)
            $arr_scripts = $this->f_getScript();
            foreach($arr_scripts AS $vScript)  {
                echo "    <script src=\"".$vScript."\"></script>\n";
            }
            $arr_styles = $this->f_getStyle();
            foreach($arr_styles AS $vStyle)  {
                echo "<link href=\"".$vStyle."\" rel=\"stylesheet\" />\n";
            }
            echo "</head>\n";
        }
        
        public function f_banner($ip_title)  {
        
            echo "<body>\n";
            echo "    <!-- PAGE.CLASS.PHP f_banner-->\n";
            echo "    <input type=\"hidden\" id=\"h_Result\" name=\"h_Result\">\n";
            echo "    <div class=\"container-fluid\">\n";
            echo "      <div class=\"row\">\n";
            echo "        <div class=\"col-sm-6\">\n";
            echo "              <br /><br />\n";
            echo "            <h1>".$ip_title."</h1>\n";
            if ($this->m_Smallbanner == true)  {
                echo "          <img src=\"img/logowide.png\" class=\"img-fluid\" width=\"100%\" height=\"auto\">\n";            
            }  else  {
                echo "          <img src=\"img/banner.jpg\" class=\"img-fluid\" width=\"100%\" height=\"auto\">\n";
            }
            echo "        </div>    <!-- col-sm-6 -->\n";
            echo "      </div>    <!-- row -->\n";
            echo "    </div>    <!-- container-fluid -->";
        
        }
        
        public function f_footer()   {

            echo "   <!-- PAGE.CLASS.PHP f_footer-->\n";
            $v_Bootstrap = $this->f_getBootstrap();

            if ($v_Bootstrap == true)  {
                $this->oBootstrap->f_script();
            }
            echo "  </body>\n";
            echo "</html>\n";

        }

    }
    
?>