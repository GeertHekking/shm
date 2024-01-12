<?php

require_once('class/abst_crud.class.php');

class cruddebt extends abst_crud  {
    
    protected function f_list()  {
        // Get maximum number of rows
        $this->f_setPage('cruddebt');
        echo "    <div class=\"banner\">Schulden&nbsp;&nbsp;&nbsp;Onderhoud</div>\n";
        $sql = "SELECT count(*) rowCount FROM debts WHERE user_code = '".$_SESSION['session_user']."'";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM debts WHERE user_code = '".$_SESSION['session_user']."' LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('Schuld', 'Omschrijving', 'Rekening', 'Dossier', 'Bedrag', 'Adres', 'Telefoon');
        $vFieldNames = array('debt', 'description', 'account', 'dossier', 'amount', 'address', 'tel');
        $vKeyFields = array('debt');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Schulden");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $vDebt = filter_input(INPUT_GET, 'debt');
        
        $this->f_detHead("Onderhoud&nbsp;Schulden");
        
        echo "  <form method=\"post\" action=\"start.php?function=cruddebt\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"cruddebt\">\n";
        echo "    <input type=\"hidden\" id=\"I_pageId\" name=\"I_pageId\" value=\"".$_SESSION['session_page']."\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $vDebt = filter_input(INPUT_GET, 'debt');
            $sql = "SELECT * FROM debts WHERE debts.user_code = ? AND debts.debt = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("ss", $_SESSION['session_user'], $vDebt);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_DebtOrig\" name=\"I_DebtOrig\" value=\"".$vDebt."\">\n";
        }
        
        // Export row values to crud class
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        // Row values to be displayed
        $this->f_formField('I_Debt', 'Schuld', 'debt', $vShowKey);
        $this->f_formField('I_Description', 'Omschrijving', 'description', true);
        $this->f_formField('I_Account', 'Rekening', 'account', true)."</td></tr>\n";
        $this->f_formField('I_Dossier', 'Dossier', 'dossier', true)."</td></tr>\n";
        $this->f_formField('I_Amount', 'Bedrag', 'amount', true)."</td></tr>\n";
        $this->f_formField('I_Address', 'Adres', 'address', true)."</td></tr>\n";
        $this->f_formField('I_Tel', 'Telefoon', 'tel', true)."</td></tr>\n";

        echo "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>\n";
        echo "  </form>\n";
        $this->f_detBottom();
        
    }

    private function f_formField($ip_fldGroup, $ip_label, $ip_rowField, $ip_ShowKey)  {
        echo "<div class=\"form-group\">\n";
        echo "    <label for=\"$ip_fldGroup\">$ip_label</label>\n";
        echo $this->f_dispField($ip_fldGroup, $ip_rowField, $ip_ShowKey)."\n";
        echo "</div>\n";

    }
    
    protected function f_valid()  {
        $op_value = true;
        $vCrud = $this->f_getCrud();
        // Only new records need to be checked.
        if ($vCrud == 'N')  {
            $op_value = true;
            $vDebt = filter_input(INPUT_POST, 'I_Debt');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM debts WHERE debts.user_code = ? AND debts.debt = ?";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    $stmt->bind_param("ss", $_SESSION['session_user'], $vDebt);
    			$stmt->execute();
    			$p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
      	        if ($num_rows > 0) {
                    $op_value = false;
                    $this->f_setError('Deze schuld bestaat reeds');
                }  else  {
                    $op_value = true;
                }
            }  else { echo "Prepare error<br />"; }
        }  
        return $op_value;
    }

    protected function f_update() {
        // Get fields from submitted form
        $vCrud     = $this->f_getCrud();
        $vDebtOrig = filter_input(INPUT_POST, 'I_DebtOrig');        // hidden field, original value
        $vDescript = filter_input(INPUT_POST, 'I_Description');
        $vAccount  = filter_input(INPUT_POST, 'I_Account');
        $vDossier  = filter_input(INPUT_POST, 'I_Dossier');
        $vAmount   = filter_input(INPUT_POST, 'I_Amount');
        $vAddress  = filter_input(INPUT_POST, 'I_Address');
        $vTel      = filter_input(INPUT_POST, 'I_Tel');
        if ($vCrud == 'N')  {
            $vDebt = filter_input(INPUT_POST, 'I_Debt');            // Updated field
         }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM debts WHERE debts.user_code = ? AND debts.debt = ?";
                $bindType = "ss";
                $bindValue = array($_SESSION['session_user'], $vDebtOrig);
                break;
            case "E":
                $sql  = "UPDATE debts SET description = ?, account = ?, dossier = ?,";
                $sql .= " amount = ?, address = ?, tel = ? WHERE user_code = ? AND debt = ?";
                $bindType = "sssdssss";
                $bindValue = array($vDescript, $vAccount, $vDossier, $vAmount, $vAddress, $vTel, $_SESSION['session_user'], $vDebtOrig);
                break;
            case "N":
                $sql  = "INSERT debts (user_code, debt, description, account, dossier, amount, address, tel) ";
                $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $bindType = "sssssdss";
                $bindValue = array($_SESSION['session_user'], $vDebt, $vDescript, $vAccount, $vDossier, $vAmount, $vAddress, $vTel);
                break;
        }
        
        if ($stmt->prepare($sql))  {
		    $stmt->bind_param($bindType, ...$bindValue);
            $stmt->execute();
            if ($vCrud == 'D')  {
                // Delete user session records and user roles
                $sql = "DELETE FROM debtapp WHERE debtapp.user_code = ? AND debtapp.debt = ?";
                if ($stmt->prepare($sql))  {
                    $stmt->bind_param("ss", $_SESSION['session_user'], $vDebtOrig);
                    $stmt->execute();
                }

                $sql = "DELETE FROM payment WHERE payment.user_code = ? AND payment.post = ?";
                if ($stmt->prepare($sql))  {
                    $stmt->bind_param("s", $userid);
                    $stmt->execute();
                }
            }
        } else { echo "Prepare error<br />"; }
    }
    
    
}

?>