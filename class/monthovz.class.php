<?php

require_once('class/page.class.php');
require_once('class/menu.class.php');

class monthovz extends page  {
    
    private $m_connection;
    private $arr_reserve = array();

    public function __construct($ip_connection)  {
        $this->m_connection = $ip_connection;
    }
    
    private function f_getConnection()  {
        return $this->m_connection;
    }

    public function f_init()  {
        if (isset($_POST['SubBank'])) {
            // Bankupdate has been submitted
            $this->f_updBank();
        }
        $this->f_setBootstrap('Bootstrap');
        $this->f_setScript('js/monthovz.js');
        $this->f_setStyle('css/monthovz.css');
        $this->f_head();
        $oMenu = new menu();
        $oMenu->f_menu();
        $this->f_smallBanner();
        $this->f_banner('Maand overzicht');
        $this->f_maand();
        $this->f_footer();
    }
    
    private function f_maand()  {

        $vToday = new DateTime();
        $vYear  = $vToday->format('Y');
        $vMonth = $vToday->format('m');
        // Save userId
        echo "<input type=\"hidden\" id=\"I_UserId\"    name=\"I_UserId\"    value=\"".$_SESSION['session_user']."\">";
        echo "<input type=\"hidden\" id=\"I_SessionId\" name=\"I_SessionId\" value=\"".$_SESSION['session_page']."\">";
        echo "<input type=\"hidden\" id=\"I_PageId\"    name=\"I_PageId\"    value=\"".$_SESSION['session_page']."\">";
        echo "<input type=\"hidden\" id=\"I_Month\"     name=\"I_Month\"     value=\"".$vMonth."\">";
        echo "<input type=\"hidden\" id=\"I_Year\"      name=\"I_Year\"      value=\"".$vYear."\">";

        // Make Table
        echo "<div class=\"container-fluid\">\n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm-7\">\n";
        $this->f_getMonth($vYear, $vMonth);
        echo "    </div>\n";
        echo "    <div class=\"col-sm-5\">\n";
        $this->f_putReservations($vYear, $vMonth);
        echo "    </div>\n";
        echo "  </div>\n";
        echo "</div>\n";

    }

    private function f_getMonth($ip_Year, $ip_Month)  {

        $vAmtCum = 0;
        $stmt = $this->m_connection->stmt_init();
        // Make General container
        // Make container for possessions
        // ----------------------------------------------------- /
        // BANC DATA POSSESSIONS                                 /
        // ----------------------------------------------------- /
        echo "      <div class=\"container\">\n";
        echo "        <div class=\"row\">\n";
        echo "          <div class=\"col-sm-8\">\n";

        // Table Header Posessions
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th colspan=\"3\">Bezittingen</th></tr>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Bron</th>\n";
        echo "                <th scope=\"col\">Bedrag</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";

        // Get Posessions
        $sql = "SELECT * FROM bank WHERE bank.user_code = ?";
        $idNr = 0;
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("s", $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_object())  {
                    echo "<tr><td><span id=\"bank_".$idNr."\">".$row->bank."</span></td><td><span id=\"amount_".$idNr."\">".$row->amount."</span></td>";
                    echo "<td><button onclick=\"f_updBank(".$idNr.")\">Update</button></td></tr>";
                    $vAmtCum += $row->amount;
                    $idNr++;
                }
            }
        }

        echo "            </tbody>\n";
        echo "          </table>\n";
        echo "          <hr>\n";

        // Close container for possessions
        echo "              </div>   <!-- Column col-sm-6 --> \n";
        echo "          </div>   <!-- Row --> \n";
        echo "      </div>   <!-- Container --> \n";

        // Next row below possessions
        echo "      </div>   <!-- Column --> \n";
        echo "      <div class=\"col-sm\">    <!-- Empty Column --> \n";
        echo "          &nbsp;";
        echo "      </div>   <!-- Column --> \n";
        echo "  </div>   <!-- Row --> \n";
        echo "  <div class=\"row\">\n";
        echo "    <div class=\"col-sm-7\">\n";
                        
        // Create array per day with income and expenses
        // ----------------------------------------------------- /
        // DAY BY DAY FINANCIAL STATUS                           /
        // ----------------------------------------------------- /
        $arr_posts = array();
        for ($i=1; $i<32; $i++)  {
            $arr_posts[$i] = array('in' => array(), 'out' => array());
        }
        for ($i=1; $i<13; $i++)  {
            $this->arr_Reserve[$i] = array();
        }
        
        // Get Posts
        $sql  = "SELECT posts.*, ";
        $sql .= "(SELECT SUM(payments.amount) FROM payments WHERE payments.user_code = posts.user_code AND payments.post = posts.post AND payments.year = ? AND payments.month = ?) AS 'payed'";
        $sql .= "FROM posts WHERE posts.user_code = ? ORDER BY dag, inkomst DESC";
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("iis", $ip_Year, $ip_Month, $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_object())  {
                    $nextDueMonth = $this->f_nextDueMonth($row->period, $row->maand, $ip_Month);
                    $vDay = $row->dag;
                    if ($row->inkomst == 0) {
                        $vInOut = 'out';
                    }  else {
                        $vInOut = 'in';
                    }
                    if ($nextDueMonth == $ip_Month) {
                        $arr_posts[$vDay][$vInOut][] = array('post'=> $row->post, 'payed'=>$row->payed, 'amount' => $row->bedrag, 'type' => 'post');
                    }  else  {
                        $this->arr_Reserve[$nextDueMonth][] = array('post'=>$row->post, 'amount' => $row->bedrag, 'inout' => $row->inkomst, 'due' => $nextDueMonth);
                    }
                }
            }
        }

        // Get Debts
        if ($ip_Month < 12) {
            $vEndYear = $ip_Year;
            $vEndMonth = $ip_Month + 1;
        } else {
            $vEndYear  = $ip_Year + 1;
            $vEndMonth = 1;
        } 
        $vStartDate = $ip_Year.'-'.$ip_Month.'-'.'1';
        $vEndDate   = $vEndYear.'-'.$vEndMonth.'-'.'1';
        $sql  = "SELECT `debt` as 'post', DAY(`date`) as 'dag', `amount` as 'bedrag', ";
        $sql .= "(SELECT SUM(payments.amount) FROM payments WHERE payments.user_code = debt_app.user_code AND payments.post = debt_app.debt AND payments.year = ? AND payments.month = ?) AS 'payed' ";
        $sql .= "FROM debt_app ";
        $sql .= "WHERE debt_app.user_code = ? ";
        $sql .= "  AND debt_app.date >= '".$vStartDate."'";
        $sql .= "  AND debt_app.date < '".$vEndDate."' ";
        $sql .= "ORDER BY debt_app.date";
        // echo "Query: ".$sql."<br />";
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("iis", $ip_Year, $ip_Month, $_SESSION['session_user']);
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                while($row = $p_Result->fetch_object())  {
                    // $nextDueMonth = $this->f_nextDueMonth($row->period, $row->maand, $ip_Month);
                    $vDay = $row->dag;
                    $vInOut = 'out';
                    $arr_posts[$vDay][$vInOut][] = array('post' => $row->post, 'payed' => $row->payed, 'amount' => $row->bedrag, 'type' => 'debt');
                }
            }  else {
                echo "No rows $sql<br />";
            }
        }  else {
            echo "Prepare error $sql<br />";
        }

        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th colspan=\"3\">Maand verloop</th><th>Part</th><td colspan=\"2\"><input type=\"number\" id=\"I_PartPay\"></td></tr>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Dag</th>\n";
        echo "                <th scope=\"col\">Post</th>\n";
        echo "                <th scope=\"col\">Bedrag</th>\n";
        echo "                <th scope=\"col\">Saldo</th>\n";
        echo "                <th scope=\"col\">&nbsp;</th>\n";
        echo "                <th scope=\"col\">&nbsp;</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        // Print Posts
        $iCount = 0;
        for ($i=1; $i<32; $i++) {
            foreach ($arr_posts[$i]['in'] as $inRow)  {
                    $vAmtCum += $inRow['amount'];
                    if ($inRow['payed'] != null) {
                        $vAmtCum -= $inRow['payed'];
                    }
                    echo "<tr><th>".$i."</th><th class=\"bg-success\">".$inRow['post']."</th><td>".$inRow['amount']."<br /><span id=\"payed_$iCount\" class='payed'>".$inRow['payed']."</span></td><td>".$vAmtCum;
                    echo "</td><td><button type=\"button\" class=\"btn btn-success\" onclick=\"f_payed('".$inRow['post']."', ".$inRow['amount'].", $iCount)\">V</button></td>\n";
                    echo "<td><button type=\"button\" class=\"btn btn-danger\" onclick=\"f_open('".$inRow['post']."', ".$inRow['amount'].", $iCount)\">X</button></td></tr>\n";
                    $iCount++;
            }
            foreach ($arr_posts[$i]['out'] as $outRow)  {
                    $vAmtCum -= $outRow['amount'];
                    if ($outRow['payed'] != null) {
                        $vAmtCum += $outRow['payed'];
                    }
                    echo "<tr><th>".$i."</th><th class=\"bg-warning\">".$outRow['post']."</th><td>".$outRow['amount']."<br /><span id=\"payed_$iCount\" class='payed'>".$outRow['payed']."</span></td><td>".$vAmtCum;
                    echo "</td><td><button type=\"button\" class=\"btn btn-success\" onclick=\"f_payed('".$outRow['post']."', ".$outRow['amount'].", $iCount)\">V</button></td>\n";
                    echo "<td><button type=\"button\" class=\"btn btn-danger\" onclick=\"f_open('".$outRow['post']."', $iCount)\">X</button></td></tr>\n";
                    $iCount++;
            }
        }
        echo "            </tbody>\n";
        echo "          </table>\n";
        echo "          <hr>\n";
    }
    
    private function f_updBank()  {
        
        if (isset($_POST['I_UserId']) && isset($_POST['I_Bank']) && isset($_POST['I_Amount'])) {
            $vUserId = $_POST['I_UserId'];
            $vBank   = $_POST['I_Bank'];
            $vAmount = $_POST['I_Amount'];
            $sql     = "UPDATE bank SET amount = ? WHERE bank.user_code = ? AND bank.bank = ?";
            // echo "Query: ".$sql."<br />Amount: ".$vAmount." User: ".$vUserId." Bank: ".$vBank;
            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($sql))  {
                $stmt->bind_param("dss", $vAmount, $vUserId, $vBank);
                $stmt->execute();
            }
        }
    }
    
    private function f_putReservations()  {
        // ----------------------------------------------------- /
        // RESERVATIONS FOR COMING PERIOD                        /
        // ----------------------------------------------------- /
        echo "          <table class=\"table table-striped\">\n";
        echo "            <thead>\n";
        echo "              <tr><th colspan=\"3\">Reserves</th></tr>\n";
        echo "              <tr>\n";
        echo "                <th scope=\"col\">Maand</th>\n";
        echo "                <th scope=\"col\">Post</th>\n";
        echo "                <th scope=\"col\">Bedrag</th>\n";
        echo "              </tr>\n";
        echo "            </thead>\n";
        echo "            <tbody>\n";
        for ($i = 1; $i<13; $i++)  {
            foreach ($this->arr_Reserve[$i] as $vReserve)  {
                if ($vReserve['inout'] == 0) {
                    $vClass = 'bg-warning';
                } else {
                    $vClass = 'bg-success';
                }
                echo "<tr><th>".$i."</th><th class=\"".$vClass."\">".$vReserve['post']."</th><td>".$vReserve['amount']."</td></tr>";
            }
        }
        echo "            </tbody>\n";
        echo "          </table>\n";
        echo "          <hr>\n";
    }
    
    private function f_nextDueMonth($ip_period, $ip_Month, $ip_ThisMonth)  {
        switch($ip_period)  {
            case 'kwartaal':
                // First Due month
                $vReturn = $ip_Month;
                // If Current month is after Due month
                if ($ip_ThisMonth > $vReturn)  {
                    while ($vReturn < $ip_ThisMonth) {
                        // Get next due month
                        $vReturn += 3;
                    }
                }
                break;
            case 'halfjaar':
                // First Due month
                $vReturn = $ip_Month;
                // If Current month is after Due month
                if ($ip_ThisMonth > $vReturn)  {
                    while ($vReturn < $ip_ThisMonth) {
                        // Get next due month
                        $vReturn += 6;
                    }
                }
                break;
            case 'jaar':
                $vReturn = $ip_Month;
                break;
            case 'maand':
            default:
                $vReturn = $ip_ThisMonth;
        }
        return $vReturn;
    }

}
    
?>