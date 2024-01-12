<?php

require_once('class/abst_crud.class.php');

class period extends abst_crud  {
    
    protected function f_list()  {
        // Get maximum number of rows
        $this->f_setPage('crudperiod');
        echo "    <div class=\"banner\">Periodes&nbsp;&nbsp;&nbsp;Onderhoud</div>\n";
        $sql = "SELECT count(*) rowCount FROM period";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM period LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('Period', 'Weken', 'Maanden', 'Jaren');
        $vFieldNames = array('period', 'weken', 'maanden', 'jaren');
        $vKeyFields = array('period');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Periodes");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $vPeriod = filter_input(INPUT_GET, 'period');
        
        $this->f_detHead("Onderhoud&nbsp;Periodes");
        
        echo "  <form method=\"post\" action=\"start.php?function=crudperiod\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"crudperiod\">\n";
        echo "    <input type=\"hidden\" id=\"I_pageId\" name=\"I_pageId\" value=\"".$_SESSION['session_page']."\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $userid = filter_input(INPUT_GET, 'userid');
            $sql = "SELECT * FROM period WHERE period.period = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("s", $vPeriod);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_PeriodOrig\" name=\"I_PeriodOrig\" value=\"".$vPeriod."\">\n";
        }
        
        // Export row values to crud class
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        // Row values to be displayed
        $arr_Field = array('type' => 'text', 'width' => 24);
        $this->f_formField('I_Period', 'Period', 'period', $vShowKey, $arr_Field);

        $arr_Field = array('type' => 'number', 'width' => 4);
        $this->f_formField('I_Weeks', 'Weken', 'weken', true, $arr_Field);

        $arr_Field = array('type' => 'number', 'width' => 4);
        $this->f_formField('I_Monthes', 'Maanden', 'maanden', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'number', 'width' => 4);
        $this->f_formField('I_Years', 'Jaren', 'jaren', true, $arr_Field)."</td></tr>\n";
        
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
            $vPeriod = filter_input(INPUT_POST, 'I_Period');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM period WHERE period.period = ?";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    $stmt->bind_param("s", $vPeriod);
    			$stmt->execute();
    			$p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
      	        if ($num_rows > 0) {
                    $op_value = false;
                    $this->f_setError('Deze periode bestaat reeds');
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
        $vPeriodOrig = filter_input(INPUT_POST, 'I_PeriodOrig');        // hidden field, original value
        $vYears      = filter_input(INPUT_POST, 'I_Years');
        $vMonthes    = filter_input(INPUT_POST, 'I_Monthes');
        $vWeeks      = filter_input(INPUT_POST, 'I_Weeks');
        if ($vCrud == 'N')  {
            $vPeriod = filter_input(INPUT_POST, 'I_Period');            // Updated field
         }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM period WHERE period.period = ?";
                $bindType = "s";
                $bindValue = array($vPeriodOrig);
                break;
            case "E":
                $sql  = "UPDATE period SET jaren = ?, maanden = ?, weken = ? WHERE period = ?";
                $bindType = "iiis";
                $bindValue = array($vYears, $vMonthes, $vWeeks, $vPeriodOrig);
                break;
            case "N":
                $sql  = "INSERT period (period, weken, maanden, jaren) ";
                $sql .= "VALUES (?, ?, ?, ?)";
                $bindType = "siii";
                $bindValue = array($vPeriod, $vWeeks, $vMonthes, $vYears);
                break;
        }
        
        if ($stmt->prepare($sql))  {
		    $stmt->bind_param($bindType, ...$bindValue);
            $stmt->execute();
        } else { echo "Prepare error<br />"; }
    }
    
}

?>