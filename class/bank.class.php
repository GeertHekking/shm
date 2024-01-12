<?php

require_once('class/abst_crud.class.php');

class bank extends abst_crud  {
    
    protected function f_list()  {
        // Get maximum number of rows
        $this->f_setPage('crudbank');
        echo "    <div class=\"banner\">Bezittingen&nbsp;Onderhoud</div>\n";
        $sql = "SELECT count(*) rowCount FROM bank WHERE user_code = '".$_SESSION['session_user']."'";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM bank WHERE user_code = '".$_SESSION['session_user']."' LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('Bank', 'Rekening', 'Bedrag');
        $vFieldNames = array('bank', 'account', 'amount');
        $vKeyFields = array('bank');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Bezittingen");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $vBank = filter_input(INPUT_GET, 'bank');
        
        $this->f_detHead("Onderhoud&nbsp;Bezittingen");
        
        echo "  <form method=\"post\" action=\"start.php?function=crudbank\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"crudbank\">\n";
        echo "    <input type=\"hidden\" id=\"I_pageId\" name=\"I_pageId\" value=\"".$_SESSION['session_page']."\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $userid = filter_input(INPUT_GET, 'userid');
            $sql = "SELECT * FROM bank WHERE user_code = ? AND bank.bank = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("ss", $_SESSION['session_user'], $vBank);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_BankOrig\" name=\"I_BankOrig\" value=\"".$vBank."\">\n";
        }
        
        // Export row values to crud class
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        // Row values to be displayed
        $arr_Field = array('type' => 'text', 'width' => 40);
        $this->f_formField('I_Bank', 'Bank/Locatie', 'bank', $vShowKey, $arr_Field);

        $arr_Field = array('type' => 'text', 'width' => 80);
        $this->f_formField('I_Account', 'Rekening', 'account', true, $arr_Field);

        $arr_Field = array('type' => 'number', 'width' => 8, 'decimal' => 2);
        $this->f_formField('I_Amount', 'Bedrag', 'amount', true, $arr_Field)."</td></tr>\n";

        echo "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>\n";
        echo "  </form>\n";
        $this->f_detBottom();
        
    }
    
    protected function f_valid()  {
        $op_value = true;
        $vCrud = $this->f_getCrud();
        // Only new records need to be checked.
        if ($vCrud == 'N')  {
            $op_value = true;
            $vPeriod = filter_input(INPUT_POST, 'I_Bank');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM bank WHERE bank.user_code = ? AND bank.bank = ?";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    $stmt->bind_param("ss", $_SESSION['session_user'], $vBank);
    			$stmt->execute();
    			$p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
      	        if ($num_rows > 0) {
                    $op_value = false;
                    $this->f_setError('Deze rekening bestaat reeds');
                }  else  {
                    $op_value = true;
                }
            }  else { echo "Prepare error<br />"; }
        }  
        return $op_value;
    }

    protected function f_update() {
        // Get fields from submitted form
        $vCrud       = $this->f_getCrud();
        $vBankOrig   = filter_input(INPUT_POST, 'I_BankOrig');        // hidden field, original value
        $vAccount    = filter_input(INPUT_POST, 'I_Account');
        $vAmount     = filter_input(INPUT_POST, 'I_Amount');
        if ($vCrud == 'N')  {
            $vBank = filter_input(INPUT_POST, 'I_Bank');              // Updated field
         }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM bank WHERE bank.user_code = ? AND bank.bank = ?";
                $bindType = "ss";
                $bindValue = array($_SESSION['session_user'], $vBankOrig);
                break;
            case "E":
                $sql  = "UPDATE bank SET account = ?, amount = ? WHERE user_code = ? AND bank = ?";
                $bindType = "sdss";
                $bindValue = array($vAccount, $vAmount, $_SESSION['session_user'], $vBankOrig);
                break;
            case "N":
                $sql  = "INSERT bank (user_code, bank, account, amount) ";
                $sql .= "VALUES (?, ?, ?, ?)";
                $bindType = "sssd";
                $bindValue = array($_SESSION['session_user'], $vBank, $vAccount, $vAmount);
                break;
        }
        
        if ($stmt->prepare($sql))  {
		    $stmt->bind_param($bindType, ...$bindValue);
            $stmt->execute();
        } else { echo "Prepare error<br />"; }
    }
    
}

?>