<?php

require_once('class/page.class.php');
require_once('class/menu.class.php');

class finovz extends page {
    
    // private $v_dbconnection;
    private $m_connection;
    
    public function __construct($ip_connection)  {
        $this->m_connection = $ip_connection;
    }
    
    public function f_init($ip_period)  {
        $this->f_setBootstrap('Bootstrap');
        $this->f_setScript('js/finovz.js');
        $this->f_setStyle('css/monthovz.css');
        $this->f_head();
        $oMenu = new menu();
        $oMenu->f_menu();
        $this->f_smallBanner();
        $this->f_banner('Financi&euml;el overzicht');

        if ($ip_period == 'Month')  {
            $this->f_monthOverview(1, 2021);
        }  else {
            $this->f_yearOverview();
        }
        $this->f_footer();
    }
    
    private function f_month($ip_month)  {
        $arr_maanden = array("Jan", "Feb", "Mrt", "April", "Mei", "Juni", "Juli", "Aug", "Sept", "Okt", "Nov", "Dec"); 
        if ($ip_month < 13)  {
            $vMonth = $arr_maanden[$ip_month-1];
        }  else  {
            $vMonth = $arr_maanden[$ip_month - 13];
        }
        return $vMonth;
    }
    
    private function f_yearOverview()  {

        $arr_totals = array();
        $arr_inctot = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $arr_expens = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $v_possess  = 0;
        
        $vToday = new DateTime();
        $vYear  = $vToday->format('Y');
        $vMonth = $vToday->format('m');
                  
        // --------------------------------------- //
        // T A B L E   P O S S E S S I O N S       //
        // --------------------------------------- //
        $vRekening = 'Rekening';
        for ($i=0; $i<15; $i++) {
            $vRekening .= '&nbsp;';
        }

        echo "<div class=\"container-fluid\">\n";
        echo "    <div class=\"row\">\n";
        echo "        <div class=\"col-sm-4\">";
        echo "          <table class=\"table table-striped w-auto\">\n";
        echo "            <thead>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Bezittingen&nbsp;\n";
        echo "                    <button type=\"button\" class=\"btn btn-default\" aria-label=\"Left Align\" onclick=\"f_hidePossess()\">\n";
        echo "                        <span class=\"glyphicon glyphicon-chevron-up\" aria-hidden=\"true\"></span>\n";
        echo "                    </button></th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">Bedrag</th>\n";
        echo "                <th scope=\"col\">".$vRekening."</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        
        $arr_possessions = $this->f_Possessions();
        foreach($arr_possessions as $vBank => $arr_Bank)  {
            echo "<tr class=\"Possess\"><td><b>".$vBank."</b></td><td class=\"text-right\">".$arr_Bank[1]."</td><td>".str_replace(" ", "&nbsp;", $arr_Bank[0])."</td></tr>\n";
            $v_possess += $arr_Bank[1];
        }
        $arr_totals['posses'] = $v_possess;
        echo "<tr><td>Total</td><td class=\"text-right bg-info\">".number_format($v_possess, 2, ',', '.')."</td><td>&nbsp;</td></tr>\n";
        
        echo "            </tbody>\n";
        echo "          </table>\n";
        echo "          <hr>";
        echo "        </div>\n";
        echo "    </div>\n";
        echo "</div>";

        // --------------------------------------- //
        // T A B L E   I N C O M E                 //
        // --------------------------------------- //
        $this->f_TableHead('', $vMonth);
        $arr_income = $this->f_Posts($vMonth, 1);
        // $arr_income contains per post an array "due" and an array "payed".
        // due has 12 entries with amounts due per mont. payed has 12 entries with amount payed per month.
        // [{post => {due => [n, n, ...]}, {payed => [n, n, ...]} }, ...]
        // NOTE: Payed is not yet calculated. It has an initial value of 0.00 per month.

        $arr_payed  = $this->f_monthPayed($vMonth, $vYear);
        // $arr_payed [{post => [{year => [{month => amount}, ...]}, ...]

        $vPrtMonth = $vMonth;
        $vPrtYear  = $vYear;
        echo "<tr>\n    <th colspan=\"13\">Inkomsten&nbsp;&nbsp;\n";
        echo "    <button type=\"button\" class=\"btn btn-default\" aria-label=\"Left Align\" onclick=\"f_hideIncome()\">\n";
        echo "        <span class=\"glyphicon glyphicon-chevron-up\" aria-hidden=\"true\"></span>\n";
        echo "    </button></th></tr>\n";
        echo "<br />";
        foreach($arr_income as $vPost => $arr_YearValues)  {
            // Add payed amounts to array $arr_Yearvalues[post][payed]
            if (array_key_exists ($vPost, $arr_payed)) {
                foreach($arr_payed[$vPost] as $vPayYear => $vPayMonth)  {
                    foreach($vPayMonth as $vMonthPayment => $vPayAmount)  {
                        if ($vMonthPayment < $vMonth) {
                            $i = 12 + $vMonthPayment - $vMonth;
                        } else {
                            $i = $vMonthPayment - $vMonth;
                        }
                        $arr_YearValues['payed'][$i] = $vPayAmount;
                        $arr_income[$vPost]['payed'][$i] = $vPayAmount;
                    }
                }
            }
            echo "<tr class=\"Income\">\n    <td>".$vPost."</td>\n";
            for ($i=0; $i<12; $i++)  {
                $vParams = $vPost.", ".$vPrtMonth.", ".$vPrtYear;
                $vId = 'img'.$vPrtYear.$vPrtMonth;
                if ($arr_YearValues['due'][$i] == $arr_YearValues['payed'][$i])  {
                    $vClass = " class=\"bg-success text-right\"";
                } else {
                    $vClass = " class=\"text-right\"";
                }
                echo "    <td$vClass>".number_format($arr_YearValues['due'][$i], 2, ',', '.');
                echo "<br /><span class=\"payed\">".number_format($arr_YearValues['payed'][$i], 2, ',', '.')."</span></td>\n";
                $arr_inctot[$i] += $arr_YearValues['due'][$i] - $arr_YearValues['payed'][$i];
                $vPrtMonth++;
                if ($vPrtMonth > 12) {
                    $vPrtMonth -= 12;
                    $vPrtYear++;
                }
            }
            echo "</tr>\n";
        }
        $arr_totals['income'] = $arr_inctot;
        echo "<tr>\n    <th>Totaal</th>\n";
        for ($i=0; $i<12; $i++)  {
            echo "<td class=\"text-right bg-info\">".number_format($arr_inctot[$i], 2, ',', '.')."</td>";
        }
        echo "</tr>\n";
        
        echo "<tr><td colspan=\"13\"><hr></td></tr>\n";

        // --------------------------------------- //
        // T A B L E   E X P E N S E S             //
        // --------------------------------------- //
        $arr_income = $this->f_Posts($vMonth, 0);
        echo "<tr>\n    <th colspan=\"13\">Uitgaven&nbsp;&nbsp;\n";
        echo "    <button type=\"button\" class=\"btn btn-default\" aria-label=\"Left Align\" onclick=\"f_hideExpense()\">\n";
        echo "        <span class=\"glyphicon glyphicon-chevron-up\" aria-hidden=\"true\"></span>\n";
        echo "    </button></th></tr>\n";
        foreach($arr_income as $vPost => $arr_YearValues)  {
            if (array_key_exists ($vPost, $arr_payed)) {
                foreach($arr_payed[$vPost] as $vPayYear => $vPayMonth)  {
                    foreach($vPayMonth as $vMonthPayment => $vPayAmount)  {
                        if ($vMonthPayment < $vMonth) {
                            $i = 12 + $vMonthPayment - $vMonth;
                        } else {
                            $i = $vMonthPayment - $vMonth;
                        }
                        $arr_YearValues['payed'][$i] = $vPayAmount;
                        $arr_income[$vPost]['payed'][$i] = $vPayAmount;
                    }
                }
            }
            echo "<tr class=\"Expense\">\n    <td>".$vPost."</td>";
            for ($i=0; $i<12; $i++)  {
                if ($arr_YearValues['due'][$i] == $arr_YearValues['payed'][$i])  {
                    $vClass = " class=\"bg-success text-right\"";
                } else {
                    $vClass = " class=\"text-right\"";
                }
                echo "    <td$vClass>".number_format($arr_YearValues['due'][$i], 2, ',', '.');
                echo "<br /><span class=\"payed\">".number_format($arr_YearValues['payed'][$i], 2, ',', '.')."</span></td>\n";
                $arr_expens[$i] += $arr_YearValues['due'][$i] - $arr_YearValues['payed'][$i];
            }
            echo "</tr>\n";
        }
        $arr_totals['expense'] = $arr_expens;
        echo "<tr>\n    <th>Totaal</th>\n";
        for ($i=0; $i<12; $i++)  {
            echo "    <td class=\"text-right bg-info\">".number_format($arr_expens[$i], 2, ',', '.')."</td>\n";
        }
        echo "</tr>\n";
        echo "<tr>\n    <td colspan=\"13\"><hr></td>\n</tr>\n";
        
        // --------------------------------------- //
        // T A B L E   D E P T S                   //
        // --------------------------------------- //
        $arr_debts = $this->f_Debts($vMonth, $vYear);
        $arr_appt = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        echo "<tr>\n    <th colspan=\"13\">Schulden&nbsp;&nbsp;\n";
        echo "    <button type=\"button\" class=\"btn btn-default\" aria-label=\"Left Align\" onclick=\"f_hideDebts()\">\n";
        echo "        <span class=\"glyphicon glyphicon-chevron-up\" aria-hidden=\"true\"></span>\n";
        echo "    </button></th></tr>\n";
        foreach($arr_debts as $vPost => $arr_YearValues)  {
            $arr_YearValues['payed'] = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            if (array_key_exists ($vPost, $arr_payed)) {
                foreach($arr_payed[$vPost] as $vPayYear => $vPayMonth)  {
                    foreach($vPayMonth as $vMonthPayment => $vPayAmount)  {
                        if ($vMonthPayment < $vMonth) {
                            $i = 12 + $vMonthPayment - $vMonth;
                        } else {
                            $i = $vMonthPayment - $vMonth;
                        }
                        $arr_YearValues['payed'][$i] = $vPayAmount;
                        $arr_income[$vPost]['payed'][$i] = $vPayAmount;
                    }
                }
            }
            echo "<tr class=\"Debts\">\n    <td>".$vPost."</td>\n";
            for ($i=0; $i<12; $i++)  {
                if ($arr_YearValues['due'][$i] == $arr_YearValues['payed'][$i])  {
                    $vClass = " class=\"bg-success text-right\"";
                } else {
                    $vClass = " class=\"text-right\"";
                }
                echo "    <td$vClass>".number_format($arr_YearValues['due'][$i], 2, ',', '.');
                echo "<br /><span class=\"payed\">".number_format($arr_YearValues['payed'][$i], 2, ',', '.')."</span></td>\n";
                $arr_expens[$i] += $arr_YearValues['due'][$i] - $arr_YearValues['payed'][$i];
                $arr_appt[$i]   += $arr_YearValues['due'][$i] - $arr_YearValues['payed'][$i];
            }
            echo "</tr>\n";
        }
        $arr_totals['expense'] = $arr_expens;
        echo "<tr>\n    <th>Aflossing</th>\n";
        for ($i=0; $i<12; $i++)  {
            echo "    <td class=\"text-right bg-info\">".number_format($arr_appt[$i], 2, ',', '.')."</td>\n";
        }
        echo "</tr>\n";
        echo "<tr>\n    <th>Totaal Uit</th>\n";
        for ($i=0; $i<12; $i++)  {
            echo "    <td class=\"text-right bg-info\">".number_format($arr_expens[$i], 2, ',', '.')."</td>\n";
        }
        echo "</tr>\n";
        
        echo "<tr>\n    <td colspan=\"13\"><hr></td>\n</tr>\n";

        // --------------------------------------- //
        // T A B L E   I N C R I M E N T           //
        // --------------------------------------- //
        echo "<tr>\n    <th colspan=\"13\">Cumulatief</th>\n</tr>\n";
        // Calculate Incremantal monthes
        echo "<tr>\n    <th>Maand totaal</th>\n";
        $v_possess = 0;
        for ($i=0; $i<12; $i++)  {
            if ($i == 0) {
                $vValue = $v_possess;
                $v_possess = $arr_totals['posses'];
            }
            $v_possess += $arr_inctot[$i];
            $v_possess -= $arr_expens[$i];
            echo "    <td class=\"text-right bg-info\">".number_format($v_possess, 2, ',', '.')."</td>\n";
        }
        echo "</tr>\n";

        echo "  </tbody>\n";
        echo "</table>\n";

        // Close Div defined in f_TableHead
        echo "        </div>  <!-- Col-sm-12 -->\n";
        echo "    </div>  <!-- Row -->\n";
        echo "</div>  <!-- Container -->\n";

    }
    
    private function f_TableHead($ip_Type, $vMonth)  {
    
        echo "<div class=\"container-fluid\">\n";
        echo "    <div class=\"row\">\n";
        echo "        <div class=\"col-sm-12\">";
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">".$ip_Type."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+0)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+1)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+2)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+3)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+4)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+5)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+6)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+7)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+8)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+9)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+10)."</th>\n";
        echo "                <th scope=\"col\" class=\"text-right\">".$this->f_month($vMonth+11)."</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";

    }
    
    private function f_Posts($ip_startmonth, $ip_inout)  {
        $arr_pos = array();
        // [{post => {due => [n, n, ...]}, {payed => [n, n, ...]} }, ...]
        $arr_mod = $this->f_getMods();
        $sql = "SELECT * FROM posts WHERE posts.user_code = ? AND posts.inkomst = ".$ip_inout;
        $stmt = $this->m_connection->stmt_init();
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_object())  {
                    $arr_postMod = array();
                    if (array_key_exists($row->post, $arr_mod)) {
                        $arr_postMod = $arr_mod[$row->post];
                    }
                    $arr_pos[$row->post]['due']   = $this->f_monthValues($row->bedrag, $row->period, $row->maand, $ip_startmonth, $arr_postMod);
                    $arr_pos[$row->post]['payed'] = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                }
            }  else {
                echo "No rows returned<br />";
            }
        } else {
            echo "Prepare error ".$sql."<br />";
        }
        return $arr_pos;
    }
    
    private function f_getMods() {
        $arr_mods = array();
        // [{post => [{month => modified amount}, ...]}, ...]
        $sql = "SELECT post, month(mod_date) as month, amount FROM posts_mod WHERE user_code = ?";
        $stmt = $this->m_connection->stmt_init();
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_object())  {
                    $vPost = $row->post;
                    // Check if key post exists. 
                    if (!array_key_exists ($vPost, $arr_mods)) {
                        $arr_mods[$vPost] = array();
                    }
                    $arr_mods[$vPost][$row->month] = $row->amount;
                }
            }
        }
        return $arr_mods;
    }

    private function f_Debts($ip_startmonth, $ip_startyear)  {
        $arr_pos = array();
        $sql = "SELECT * FROM debt_app WHERE debt_app.user_code = ?";
        $stmt = $this->m_connection->stmt_init();
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_object())  {
                    $vIndex = $this->f_Index($row->date, $ip_startmonth, $ip_startyear);
                    if ($vIndex > -1 && $vIndex < 12) {
                    
                        if (!array_key_exists ($row->debt, $arr_pos) )  {
                            $arr_pos[$row->debt] = array();
                            $arr_pos[$row->debt]['due'] = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                        }
                        $arr_pos[$row->debt]['due'][$vIndex] = $row->amount - $row->payed;
                    }
                }
            }
        }
        return $arr_pos;
    } 
    
    private function f_monthOverview($ip_Month, $ip_Year)  {
        
    }
    
    private function f_Possessions()  {
        $arr_pos = array();
        $sql = "SELECT * FROM bank WHERE bank.user_code = ?";
        $stmt = $this->m_connection->stmt_init();
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                // Session record is found. Now check if session has been expired.
                while($row = $p_Result->fetch_object())  {
                    $arr_pos[$row->bank] = array($row->account, $row->amount);
                }
            }
        }
        return $arr_pos;
    }
    
    private function f_monthPayed($ip_startmonth, $ip_startyear) {
        
        $arr_pay = array();
        $sql  = "SELECT `post`, `year`, `month`, sum(`amount`) AS amount ";
        $sql .= " FROM `payments`";
        $sql .= " WHERE `user_code` = ? ";
        $sql .= "   AND ((`year` = ? AND `month` >= ?) OR (`year` - 1 = ? AND `month` < ?)) ";
        $sql .= "GROUP BY `post`, `year`, `month`";
        $stmt = $this->m_connection->stmt_init();
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("siiii", $_SESSION['session_user'], $ip_startyear, $ip_startmonth, $ip_startyear, $ip_startmonth);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                // Session record is found. Now check if session has been expired.
                while($row = $p_Result->fetch_object())  {
                    // Is there an entry for current post?
                    if (!array_key_exists ($row->post, $arr_pay)) {
                        $arr_pay[$row->post] = array();
                    }
                    // Does year exist in $arr_pay[$row->post]?
                    if (!array_key_exists ($row->year, $arr_pay[$row->post])) {
                        $arr_pay[$row->post][$row->year] = array();
                    }
                    $arr_pay[$row->post][$row->year][$row->month] = $row->amount;
                }
            }
        }
        return $arr_pay;

    }
    
    private function f_monthValues($ip_bedrag, $ip_period, $ip_month, $ip_startmonth, $ip_postMod)  {
        // $ip_startmonth is the first month in the array. (Januari = 0)
        // $ip_month is the first month in which the amount is due. (Januari = 1)
        // $ip_postMod is the array with modifications during the year.
        $arr_Amounts = array();
        // {Bedrag, Bedrag, ...}
        $curMonth = $ip_startmonth;
        $dueMonth = $this->f_nextMonth($curMonth, $ip_month, $ip_period);
        for ($i=0; $i<12; $i++) {
            // See if the amount per month changes this month
            if (array_key_exists($curMonth, $ip_postMod)) {
                $ip_bedrag = $ip_postMod[$curMonth];
            }
            $vBedrag = 0.00;
            switch ($ip_period) {
                case 'jaar':
                    if ($curMonth == $ip_month) {
                        $vBedrag = $ip_bedrag;
                    }
                    $arr_Amounts[] = $vBedrag;
                    break;
                case 'halfjaar':
                case 'kwartaal':
                    if ($curMonth == $dueMonth) {
                        $vBedrag = $ip_bedrag;
                    }
                    $arr_Amounts[] = $vBedrag;
                    break;
                default :
                    $vBedrag = $ip_bedrag;
                    $arr_Amounts[] = $vBedrag;
            }
            if ($curMonth < 12)  {
                $curMonth++;
            }  else {
                $curMonth = 1;
            }
            $dueMonth = $this->f_nextMonth($curMonth, $ip_month, $ip_period);
        }
        return $arr_Amounts;
    }
    
    private function f_nextMonth($ip_Now, $ip_Start, $ip_period)  {
        
        switch($ip_period)  {
            case 'jaar':
                $ip_Return = $ip_Start;
                break;
            case 'halfjaar':
                if ($ip_Now > $ip_Start) {
                    $ip_Return = $ip_Start + 6;
                }  else {
                    $ip_Return = $ip_Start;
                }
                if ($ip_Return > 12) {
                    $ip_Start -= 12;
                }
                break;
            case 'kwartaal':
                $ip_Return = $ip_Start;
                while ($ip_Now > $ip_Return)  {
                    $ip_Return += 3;
                }
                if ($ip_Return > 12) {
                    $ip_Return -= 12;
                }
                break;
            default:
                $ip_Return = $ip_Now;
        }
        return $ip_Return;
    }
    
    private function f_Index($ip_date, $ip_startmonth, $ip_startyear)  {
        $vDate  = strtotime($ip_date);
        $vMonth = date('m', $vDate);
        $vYear  = date('Y', $vDate);
        if ($vYear < $ip_startyear || ($vYear == $ip_startyear) && $vMonth < $ip_startmonth) {
            $vReturn = -1;
        }  else {
            $vReturn = ($vYear - $ip_startyear) * 12 + $vMonth - $ip_startmonth;
        }
        return $vReturn;
    }

}

?>