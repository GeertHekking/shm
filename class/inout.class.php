<?PHP

require_once('class/page.class.php');
require_once('class/menu.class.php');

class inout extends page  {
    
    private $m_connection;
    private $m_totIn;
    private $m_totOut;
    private $m_qtyRows;
    
    public function __construct($ip_connection)  {
        $this->m_connection = $ip_connection;
    }
    
    private function f_getConnection()  {
        return $this->m_connection;
    }
    
    private function f_setIn($ip_In)  {
        $this->m_totIn = $ip_In;
    }
    
    private function f_getIn()  {
        return $this->m_totIn;
    }
    
    private function f_setOut($ip_Out)  {
        $this->m_totOut = $ip_Out;
    }
    
    private function f_getOut()  {
        return $this->m_totOut;
    }
    
    private function f_setQtyRows($ip_qty) {
        $this->m_qtyRows = $ip_qty;
    }

    private function f_getQtyRows() {
        return $this->m_qtyRows;
    }

    public function f_init()  {
        $this->f_setBootstrap('Bootstrap');
        $this->f_setScript('js/inout.js');
        $this->f_setStyle('css/inout.css?id=3s');
        $this->f_head();
        $this->f_smallBanner();
        $this->f_banner('Inkomsten Uitgaven overzicht');
        $oMenu =  new menu();
        $oMenu->f_menu();
        $this->f_overzicht();
        $this->f_footer();
    }
    
    public function f_posts() {
        return $this->f_getPosts();
    }
    
    private function f_overzicht()  {

        // Save userId
        echo "<input type=\"hidden\" id=\"I_UserId\"    name=\"I_UserId\"    value=\"".$_SESSION['session_user']."\">";
        echo "<input type=\"hidden\" id=\"I_PageId\"    name=\"I_PageId\"    value=\"".$_SESSION['session_page']."\">";

        // Collect all posts
        $arr_posts = $this->f_getPosts();
        // Make Table
        echo "<div class=\"container-fluid\">\n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm-1\">\n";
        echo "      <button type=\"button\" class=\"btn btn-default\" aria-label=\"Left Align\" onclick=\"f_hideTables()\">\n";
        echo "        <span class=\"glyphicon glyphicon-chevron-up\" aria-hidden=\"true\"></span>\n";
        echo "      </button><br /><p class=\"font-weight-light\">Open / Sluit details</p>\n";
        echo "    </div>\n";
        echo "    <div class=\"col-sm-5 detailTable\">\n";
        $this->f_setTable($arr_posts, true, "Inkomsten");
        reset($arr_posts);
        echo "    </div>\n";
        echo "    <div class=\"col-sm-5 detailTable\">\n";
        $this->f_setTable($arr_posts, false, "Uitgaven");
        reset($arr_posts);
        echo "    </div>  <!-- col-sm-5 --> \n";
        echo "  </div>  <!-- row --> \n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm-6\">\n";
        $this->f_differences();
        echo "    </div>  <!-- col-sm-6 --> \n";
        echo "  </div>  <!-- row --> \n";
        echo "</div>  <!-- container --> \n";

        echo "<div class=\"container-fluid\">\n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm\">\n";
        $this->f_agreements();
        echo "    </div>  <!-- col-sm --> \n";
        echo "  </div>  <!-- row --> \n";
        echo "</div>  <!-- container --> \n";

    }

    private function f_getPosts()  {

        $arr_Return = array();
        $tot_In     = 0;
        $tot_Out    = 0;
        $qty_In     = 0;
        $qty_Out    = 0;
        
        $stmt = $this->m_connection->stmt_init();
        $sql  = "SELECT * FROM posts WHERE posts.user_code = ?";
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            // echo "Aantal rijen ".$num_rows."<br />";
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_assoc())  {
                    $arr_Return[] = $row;
                    // Accumulate totals
                    $vMonth = $this->f_getMonth($row['bedrag'], $row['period']);
                    if ($row['inkomst'] == true) {
                        $tot_In += $vMonth;
                        $qty_In++;
                    } else {
                        $tot_Out += $vMonth;
                        $qty_Out++;
                    }
                }
            }
        }
        
        $this->f_setIn($tot_In);
        $this->f_setOut($tot_Out);
        $this->f_setQtyRows(Max($qty_In, $qty_Out));
        return $arr_Return;
    }
    
    private function f_setTable($arr_posts, $ip_type, $ip_Head)  {
        $iRow = 0;
        $vQtyRows = $this->f_getQtyRows();
        
        $this->f_tableHead($ip_Head);
        foreach($arr_posts as $post) {
            if ($post['inkomst'] == $ip_type) {
                $this->f_tableRow($post);
                $iRow++;
            }
        }
        $this->f_tableFoot($iRow, $ip_type);
    }
    
    private function f_tableRow($arr_Post)  {
        $vMonth = $this->f_getMonth($arr_Post['bedrag'], $arr_Post['period']);
        $vYear  = $vMonth * 12;
        echo "<tr><td>".$arr_Post['post']."</td>\n";
        echo "<td>".number_format($vMonth, 2, ',','.')."</td><td><span class='yearVal'>".number_format($vYear, 2, ',', '.')."</span></td>\n";
        echo "<td>".$arr_Post['period']."</td></tr>\n";
    }
    
    private function f_differences()  {
        $vYearIn  = $this->m_totIn  * 12;
        $vYearOut = $this->m_totOut * 12;
        $vDiff = $this->m_totIn - $this->m_totOut;
        $vDiffYear = $vYearIn - $vYearOut;
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th colspan=\"3\">Inkomsten / Uitgaven</th></tr>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Totalen</th>\n";
        echo "                <th scope=\"col\">Maand</th>\n";
        echo "                <th scope=\"col\">Jaar</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        echo "<tr><td>Inkomsten</td><td>".number_format($this->m_totIn, 2, ",", ".")."</td><td>".$vYearIn."</td></tr>\n";
        echo "<tr><td>Uitgaven</td><td>".number_format($this->m_totOut, 2, ",", ".")."</td><td>".number_format($vYearOut, 2, ",", ".")."</td></tr>\n";
        echo "<tr><th>Verschil</th><th>".number_format($vDiff, 2, ",", ".")."</th><th>".number_format($vDiffYear, 2, ",", ".")."</th></tr>\n";
        echo "            </tbody>\n";
        echo "        </table>\n";
    }
    
    private function f_agreements()  {
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th colspan=\"3\">Planning betalingsregeling</th></tr>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Schuld</th>\n";
        echo "                <th scope=\"col\">Totaal</th>\n";
        echo "                <th scope=\"col\">Rest</th>\n";
        echo "                <th scope=\"col\">Maand</th>\n";
        for ($iCount = 1; $iCount < 13; $iCount++) {
            echo "                <th scope=\"col\">Termijn $iCount</th>\n";
        }
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        // Fill debts already planned
        $arr_debts = $this->f_getDebts();
        $arr_Totals = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $vIndex = 0;
        foreach ($arr_debts as $vPost => $arr_PostValues)  {
            echo "<tr><th>$vPost</th><td><span id=\"schuld_$vIndex\">".$arr_PostValues['amount']."</span></td>";
            echo "<td><span id=\"rest_$vIndex\">".$arr_PostValues['rest']."</span></td>";
            echo "<td><input type=\"number\" id=\"monthly_$vIndex\" value=\"".$arr_PostValues['appt']."\"></td>";
            for ($iCount = 0; $iCount < 12; $iCount++)  {
                echo "<td><input type=\"number\" id=\"month".$iCount."_".$vIndex."\" value=\"".$arr_PostValues['monthly'][$iCount]."\"</td>";
                $arr_Totals[$iCount] += $arr_PostValues['monthly'][$iCount];
            }
            echo "</tr>\n";
        }
        echo "<tr><th colspan=\"4\">Total</th>";
        for ($vIndex = 0; $vIndex < 12; $vIndex++) {
            echo "<td>".$arr_Totals[$vIndex]."</td>";
        }
        echo "</tr>\n";
        echo "            </tbody>\n";
        echo "        </table>\n";
        
    }
    
    private function f_getDebts()  {

        $vToday = new DateTime();
        $vYear  = $vToday->format('Y');
        $vMonth = $vToday->format('m');

        $stmt = $this->m_connection->stmt_init();
        $arr_appt = array();
        // Collect all agreed arrangements
        $sql  = "SELECT debt as post, month(date) as month, year(date) as year, amount, restamount ";
        $sql .= "  FROM debt_app ";
        $sql .= " WHERE user_code = ? AND (YEAR(date) -1 = ? OR (YEAR(date) = ? AND MONTH(date) >= ?))";
        $sql .= " ORDER BY debt, year, month";
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("siii", $_SESSION['session_user'], $vYear, $vYear, $vMonth);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_assoc())  {
                    if (!array_key_exists($row['post'], $arr_appt))  {
                        $arr_appt[$row['post']] = array('amount' => $row['restamount'], 'rest' => $row['restamount'], 'appt' => $row['amount'], 'monthly' => array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
                    }
                    // Maximum 1 year
                    if ($row['year'] = $vYear || $row['month'] < $vMonth)  {
                        $vIndex = $row['month'] - $vMonth;
                        if ($vIndex < 0) {
                            $vIndex += 12;
                        }
                        $arr_appt[$row['post']]['monthly'][$vIndex] = $row['amount'];
                    }
                }
            }
        }
        $sql = "SELECT * FROM debts WHERE user_code = ?";
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_assoc())  {
                    if (!array_key_exists($row['debt'], $arr_appt))  {
                        $arr_month = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                        $arr_appt[$row['debt']] = array('amount'=>$row['amount'], 'rest' => $row['amount'], 'appt' => 0, 'monthly' => $arr_month);
                    }  else {
                        $arr_appt[$row['debt']]['amount'] = $row['amount'];
                    }
                }
            }
        }
        return $arr_appt;
    }
    
    public function f_getMonth($ip_amount, $ip_period)  {
        $vReturn =  $ip_amount;
        switch ($ip_period)  {
            case 'kwartaal':
                $vReturn = $vReturn / 3;
                break;
            case 'halfjaar':
                $vReturn = $vReturn / 6;
                break;
            case 'jaar':
                $vReturn = $vReturn / 12;
                break;
        }
        return $vReturn;
    }
    
    private function f_setTotals()  {
        $vMonthIn  = $this->f_getIn();
        $vYearIn   = $vMonthIn * 12;
        $vMonthOut = $this->f_getOut();
        $vYearOut  = $vMonthOut * 12;
        echo "    <div class=\"col-sm-6\">\n";
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th><span class=\"totals\">Totaal In</span></th><td>".$vMonthIn."</td><td>".$vYearIn."</td></tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        $this->f_tableFoot();
        echo "    </div>\n";
        echo "    <div class=\"col-sm-6\">\n";
        echo "    <div class=\"col-sm-6\">\n";
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th><span class=\"totals\">Totaal&nbsp;Uit</span></th><td>".$vMonthOut."</td><td>".$vYearOut."</td></tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        $this->f_tableFoot();
        echo "    </div>  <!-- col-sm-6 --> \n";
    }
    
    private function f_tableHead($ip_Header)  {
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th colspan=\"3\">$ip_Header</th></tr>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Post</th>\n";
        echo "                <th scope=\"col\">Bedrag</th>\n";
        echo "                <th scope=\"col\">Jaarbasis</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
    }
    
    private function f_tableFoot($ip_row, $ip_inOut)  {
        $iRow  = $ip_row;
        $vQtyRows = $this->f_getQtyRows();
        // Print dummy lines, to the total in and out appear on the same row.
        while ($iRow < $vQtyRows) {
            echo "<tr class=\"hiderow\"><td colspan=\"4\">&nbsp;</td></tr>\n";
            $iRow++;
        }

        if ($ip_inOut == true)  {
            $ip_inOutTxt = "Inkomsten";
            $vMonthTot  = $this->f_getIn();
        } else {
            $ip_inOutTxt = "Uitgaven";
            $vMonthTot = $this->f_getOut();
        }
        $vYearTot = $vMonthTot * 12;
        echo "              <tr><th><span class=\"totals\">Totaal ".$ip_inOutTxt."</span></th>";
        echo "<td>".number_format($vMonthTot, 2, ',', '.')."</td>";
        echo "<td>".number_format($vYearTot, 2, ',', '.')."</td></tr>\n";
        echo "            </tbody>\n";
        echo "          </table>\n";
    }
    
    private function f_updateApp($ip_debt)  {
        $sql  = "UPDATE `debt_app` ";
        $sql .= "SET `restamount` = (SELECT debt_app.amount - (SELECT SUM(da.`amount`) ";
        $sql .=                                               "  FROM debt_app da ";
        $sql .=                                               " WHERE da.user_code = debt_app.user_code ";
        $sql .=                                               "   AND da.debt = debt_app.debt ";
        $sql .=                                               "   AND da.date <= debt_app.date) ) ";
        $sql .= "WHERE debt_app.user_code = ? ";
        $sql .= "  AND debt_app.debt = ?";
    }

}

?>