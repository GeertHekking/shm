<?php

require_once('class/page.class.php');
require_once('class/menu.class.php');

class debts extends page  {
    
    private $m_connection;

    public function __construct($ip_connection)  {
        $this->m_connection = $ip_connection;
    }
    
    private function f_getConnection()  {
        return $this->m_connection;
    }

    public function f_init()  {
        $this->f_setBootstrap('Bootstrap');
        $this->f_setScript('js/debts.js');
        $this->f_head();
        $this->f_smallBanner();
        $this->f_banner('Debtors overzicht');
        $oMenu =  new menu();
        $oMenu->f_menu();
        $this->f_debts();
        $this->f_footer();
    }
    
    private function f_debts()  {
        // Save userId
        echo "<input type=\"hidden\" id=\"I_UserId\" value=\"".$_SESSION['session_user']."\">";
        echo "<input type=\"hidden\" id=\"I_SessionId\" value=\"".$_SESSION['session_page']."\">";
        // Make 2 columns
        echo "<div class=\"container\">\n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm-6\">\n";
        $this->f_debtors();
        echo "    </div>\n";
        echo "    <div class=\"col-sm-6\">\n";
        $this->f_details();
        $this->f_plan();
        echo "    </div>\n";
        echo "  </div>\n";
        echo "</div>\n";
    }
    
    private function f_debtors()  {
        // Select all debtors
        $vConnection = $this->f_getConnection();
        $sql = "SELECT * FROM debts WHERE debts.user_code = ?";
        $stmt = $this->m_connection->stmt_init();
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                
                echo "<table class=\"table\">\n";
                echo "  <thead>\n";
                echo "    <tr>\n";
                echo "     <th scope=\"col\">Schulden</th>\n";
                echo "     <th scope=\"col\">Detail</th>\n";
                echo "     <th scope=\"col\">Afspraak</th>\n";
                echo "   </tr>\n";
                echo "  </thead>\n";
                echo "  <tbody>\n";
                $rowCount = 0;
                while($row = $p_Result->fetch_object())  {
                    echo "<tr><td>".$row->debt."</td><td><button class=\"btn btn-primary\" onclick=\"f_detail(".$rowCount.")\">Detail</button>\n";
                    echo "<td><button class=\"btn btn-success\" onclick=\"f_afspraak(".$rowCount.")\">Afspraak</button>\n";
                    echo "<input type=\"hidden\" id=\"I_debt".$rowCount."\" value=\"".$row->debt."\">\n";
                    echo "<input type=\"hidden\" id=\"I_description".$rowCount."\" value=\"".$row->description."\">\n";
                    echo "<input type=\"hidden\" id=\"I_dossier".$rowCount."\" value=\"".$row->dossier."\">\n";
                    echo "<input type=\"hidden\" id=\"I_account".$rowCount."\" value=\"".$row->account."\">\n";
                    echo "<input type=\"hidden\" id=\"I_amount".$rowCount."\" value=\"".$row->amount."\">\n";
                    echo "<input type=\"hidden\" id=\"I_tel".$rowCount."\" value=\"".$row->tel."\">\n";
                    echo "<input type=\"hidden\" id=\"I_address".$rowCount."\" value=\"".$row->address."\">\n";
                    echo "</td></tr>\n";
                    $rowCount++;
                }
                
                echo "  </tbody>\n";
                echo "</table>\n";
                
            }
        }
    }
    
    private function f_details()  {
        echo "<table class=\"table\" style=\"display:none\" id=\"tableDet\">\n";
        echo "  <thead class=\"thead-dark\">\n";
        echo "    <tr>\n";
        echo "     <th scope=\"col\" colspan=\"2\">Detail schuld</th>\n";
        echo "   </tr>\n";
        echo "  </thead>\n";
        echo "  <tbody>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">Schuld</th>\n";
        echo "      <td><span id=\"I_debt\"></span></td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">Omschrijving</th>\n";
        echo "      <td><textarea id=\"I_description\" cols=\"60\" rows=\"4\"></textarea></td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">dossier</th>\n";
        echo "      <td><span id=\"I_dossier\"></span></td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">Rekening</th>\n";
        echo "      <td><span id=\"I_account\"></span></td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">Bedrag</th>\n";
        echo "      <td><span id=\"I_amount\"></span></td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">telefoon</th>\n";
        echo "      <td><span id=\"I_tel\"></span></td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"row\">adres</th>\n";
        echo "      <td><span id=\"I_address\"></span></td>\n";
        echo "    </tr>\n";
        echo "  </tbody>\n";
        echo "</table>\n";
    }
    
    private function f_plan()  {

        echo "<div style=\"display: none\" id=\"tableAppoint\">\n";
        echo "<div class=\"container\">\n";
        echo "  <input type=\"hidden\" id=\"I_Debt\" value=\"\">\n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm\">\n";
        echo "        Totale schuld<br />\n";
        echo "        <input type=\"text\" id=\"I_TotDebt\">\n";
        echo "    </div>\n";
        echo "    <div class=\"col-sm\">\n";
        echo "        Aantal termijnen<br />\n";
        echo "        <input type=\"number\" id=\"I_QtyPeriods\">\n";
        echo "    </div>\n";
        echo "    <div class=\"col-sm\">\n";
        echo "        Periode Bedrag<br />\n";
        echo "        <input type=\"number\" id=\"I_AmountPeriods\">\n";
        echo "    </div>\n";
        echo "    <div class=\"col-sm\">\n";
        echo "        Start Datum<br />\n";
        echo "        <input type=\"date\" id=\"I_StartDate\">\n";
        echo "    </div>\n";
        echo "  </div>\n";
        echo "  <br /><button onclick=\"f_fillAppointments();\" class=\"btn btn-primary\">Start</button>&nbsp;&nbsp;\n";
        echo "        <button onclick=\"f_saveAppointments();\" class=\"btn btn-success\">Bijwerken</button>\n";
        echo "</div>\n";
        echo "<hr />\n";
        echo "<table class=\"table table-striped\">\n";
        echo "  <thead class=\"thead-dark\">\n";
        echo "    <tr>\n";
        echo "     <th scope=\"col\" colspan=\"2\">Regeling</th>\n";
        echo "   </tr>\n";
        echo "  </thead>\n";
        echo "  <tbody id=\"planBody\">\n";
        echo "  </tbody>\n";
        echo "</table>\n";
        echo "</div>\n";

    }
}

?>