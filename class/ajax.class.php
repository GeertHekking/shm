<?php

class ajax {

    private $m_connection;
    
    public function __construct()  {
        require_once('../../private_incl/shm.incl');
    }
    
    public function f_init($ip_function)  {
        if (isset($_GET['userid']) && isset($_GET['sessionId'])) {
            
            $vUser    = $_GET['userid'];
            $vSession = $_GET['sessionId'];
            
			// Check if user is (still) logged in
            $sql = "SELECT * FROM login WHERE login.user_code = ? AND login.sessionCode = ?";
            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($sql))  {
                $stmt->bind_param("ss", $vUser, $vSession);
                $stmt->execute();
                $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
                if ($num_rows > 0) {

                    switch($ip_function)  {
                        case 'Regeling':
                            $vResult = $this->f_appointment($_GET['debt']);
                            echo $vResult;
                            break;
                        case 'ovzRegeling':
                            $vResult = $this->f_getAppointments($_GET['debt']);
                            echo $vResult;
                            break;
                        case 'payed':
                            $vResult = $this->f_setPayed();
                            echo $vResult;
                            break;
                        case 'unpayed':
                            $vResult = $this->f_setUnPayed();
                            echo $vResult;
                            break;
                        case 'leenPayed':
                            $vResult = $this->f_getLeenPayed();
                            echo $vResult;
                            break;
                        case 'leenPayment':
                            $vResult = $this->f_leenPayment();
                            echo $vResult;
                            break;
                        case 'leenPayDel';
                            $vResult = $this->f_leenPayDel();
                            echo $vResult;
                            break;
                    default:
                            echo 'Onjuiste functie '.$ip_function;
                    }
                } else {
                    echo "Error Login ".$vUser." / ".$vSession;
                }
            }  else {
                echo "Prepare error".$sql;
            }
        } else {
            echo "userid / sessionId not set";
        }
    }
    
    private function f_appointment($ip_debt)  {
		// This function creates the agreed payments with their due date
        $vAppointments =  file_get_contents('php://input');
        $arr_Appt = json_decode($vAppointments, true);

        $vSQL =  "INSERT INTO debt_app (user_code, debt, date, amount, restamount, payed) VALUES ";
        $vSep = '';
        
        foreach($arr_Appt['regeling'] AS $arr_Month)  {
            $vRow  = '("'.$arr_Month['UserId'].'", "'.$arr_Month['Debt'].'", "'.$arr_Month['Date'].'", ';
            $vRow .= $arr_Month['Amount'].', '.$arr_Month['Rest'].', 0)';
            $vSQL .= $vSep.$vRow;
            $vSep  = ', ';
        }
         $vSQL .= " ON DUPLICATE KEY UPDATE amount = VALUES(amount), restamount = VALUES(restamount)";
        // echo $vSQL;
        mysqli_query($this->m_connection, $vSQL);
        echo "Regeling opgeslagen";       
    }
    
    private function f_setPayed()  {
        // This procudere marks a post as being payed on the current date.
        $vPayments =  file_get_contents('php://input');
        $arr_Payed = json_decode($vPayments, true);
        
        $vUserId  = $_GET['userid'];
        $vToday   = new DateTime();

		// Calculate open amount for post in this month.
		$vAmount = 0;
        $stmt = $this->m_connection->stmt_init();
		$sql  = " SELECT COALESCE(SUM(amount) , 0) AS qty ";
       	$sql .= "   FROM payments";
		$sql .= " WHERE payments.user_code = ? ";
		$sql .= "   AND payments.post = ? ";
		$sql .= "   AND payments.year = ? ";
		$sql .= "   AND payments.month = ? ";
		
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("ssii", $vUserId, $arr_Payed['post'], $arr_Payed['year'], $arr_Payed['month']);
            $stmt->execute();
			$p_Result = $stmt->get_result();
			$row = $p_Result->fetch_object();
			$vAmount = $row->qty;
        } else {
            echo "prepare error".$sql;
		}
		
		// Pay the open amount 
		if ($vAmount >= 0 && $vAmount <= $arr_Payed['amount']) {
			$vAmount = $arr_Payed['amount'] - $vAmount;
            // $vAmount is amount still to be payed this month.
            // If only partial payment, then pay only the part submitted.
            $vPartPay = $arr_Payed['partPay'];
            if ($vPartPay > 0 && $vPartPay < $vAmount) {
                $vAmount = $vPartPay;
            }
	        $vPayDate = $vToday->format('Y').'-'.$vToday->format('m').'-'.$vToday->format('d');
	        $sql  = "INSERT INTO `payments`(`user_code`, `post`, `year`, `month`, `date`, `amount`)";
	        $sql .= " VALUES (?, ?, ?, ?, ?, ?)";
	        if ($stmt->prepare($sql))  {
	            $stmt->bind_param("ssiisd", $vUserId, $arr_Payed['post'], $arr_Payed['year'], $arr_Payed['month'], $vPayDate, $vAmount);
	            $stmt->execute();
	        } else {
	            echo "prepare error".$sql;
	        }
	        echo "Payed ".$vAmount;
		}  else  {
			echo "Post has been payed already ".$vAmount." Basis bedrag ".$arr_Payed['amount'];
		}
    }
    
    private function f_setUnPayed()  {
        // This procudere marks a post as being payed on the current date.
        $vPayments =  file_get_contents('php://input');
        $arr_Payed = json_decode($vPayments, true);
        
        $vUserId  = $_GET['userid'];

		// Calculate open amount for post in this month.
        $stmt = $this->m_connection->stmt_init();
		$sql  = " DELETE FROM payments";
		$sql .= " WHERE payments.user_code = ? ";
		$sql .= "   AND payments.post = ? ";
		$sql .= "   AND payments.year = ? ";
		$sql .= "   AND payments.month = ? ";
		
        if ($stmt->prepare($sql))  {
            $stmt->bind_param("ssii", $vUserId, $arr_Payed['post'], $arr_Payed['year'], $arr_Payed['month']);
            $stmt->execute();
	        echo "UnPayed 0.00";
        } else {
            echo "prepare error".$sql;
		}
    }
    
    public function f_getAppointments()  {
		// This function shows the appointment that has been made for the current debt.        
        if (isset($_GET['userid']) && isset($_GET['debt']))  {
            $vJSON = "{'debts': [";
            $arr_JSON = array();
            $arr_JSON['debts'] = array();
            $vUserId = $_GET['userid'];
            $vDebt   = $_GET['debt'];
            $vSql    = "SELECT * FROM debt_app WHERE debt_app.user_code = ? AND debt_app.debt = ? ORDER BY debt_app.date";
            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($vSql))  {
                $stmt->bind_param("ss", $vUserId, $vDebt);
                $stmt->execute();
                $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
                if ($num_rows > 0) {
                    while ($row = $p_Result->fetch_object())  {
                        $vJSON .= "{'date': '".$row->date."', 'amount': ".$row->amount.", 'rest': ".$row->restamount."}";
                        $arr_Row = array('date' => $row->date, 'amount' => $row->amount, 'rest' => $row->restamount);
                        $arr_JSON['debts'][] = $arr_Row;
                    }
                }
            }
            $vJSON .= "]}";
            
        } else {
            $vJSON = "{'debts': [";
            if (isset($_GET['userid'])) {
                $vJSON .= "'userid': '".$_GET['userid'];
            } else {
                $vJSON .= "'userid': 'GEEN";
            }
            if (isset($_GET['debt']))  {
                $vJSON .= "', 'debt': ".$_GET['debt']."']}";
            }  else  {
                $vJSON .= "', 'debt': 'GEEN']}";
            }
            
        }
        echo json_encode($arr_JSON);
    }

    private function f_getLeenPayed() {

        $arr_JSON = array();
        $arr_JSON['payments'] = array();

        if (isset($_GET['leenId'])) {
            $vId   = $_GET['leenId'];
            $vSql  = "SELECT paylening.ID, lening.Van, lening.Aan, paylening.Datum, paylening.Bedrag, lening.Bedrag AS Total "; 
            $vSql .= " FROM `paylening`, lening ";
            $vSql .= " WHERE lening.ID = ? AND paylening.leningID = lening.ID";

            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($vSql))  {
                $stmt->bind_param("i", $vId);
                $stmt->execute();
                $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
                if ($num_rows > 0) {
                    while ($row = $p_Result->fetch_object())  {
                        $arr_Row = array('date' => $row->Datum, 'amount' => $row->Bedrag, 'from' => $row->Aan, 'to' => $row->Van, 'payID' => $row->ID);
                        $arr_JSON['payments'][] = $arr_Row;
                    }
                }
            }

        } 
        echo json_encode($arr_JSON);

    }

    private function f_leenPayment()  {
        if (isset($_GET['leenId']) && isset($_GET['payAmount'])) {
            $vId   = $_GET['leenId'];
            $vAmount = $_GET['payAmount'];
            $vToday   = new DateTime();
            $vDay  = $vToday->format('Y');
            $vDay .= '-'.$vToday->format('m').'-'.$vToday->format('d');
    
            $vSql  = "INSERT INTO `paylening` (leningId, Datum, Bedrag) VALUES (?, ?, ?)";

            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($vSql))  {
                $stmt->bind_param("isi", $vId, $vDay, $vAmount);
                $stmt->execute();
                $stmt->close();
            }
        }
        $this->f_getLeenPayed();
    }

    private function f_leenPayDel()  {
        if (isset($_GET['delId'])) {
            $vId   = $_GET['delId'];
    
            $vSql  = "DELETE FROM `paylening` WHERE ID = ?";

            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($vSql))  {
                $stmt->bind_param("i", $vId);
                $stmt->execute();
                $stmt->close();
            }
        }
        $this->f_getLeenPayed();
    }

}

?>