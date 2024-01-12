<?php

require_once('class/abst_crud.class.php');

class crudlenen extends abst_crud  {
    
    protected function f_list()  {
        // Get maximum number of rows
        $this->f_setPage('crudlenen');
        echo "    <div class=\"banner\">Schulden&nbsp;&nbsp;&nbsp;Onderhoud</div>\n";
        $sql = "SELECT count(*) rowCount FROM lening";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM lening LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('Datum', 'Van', 'Aan', 'Post', 'Bedrag');
        $vFieldNames = array('Datum', 'Van', 'Aan', 'Post', 'Bedrag');
        $vKeyFields = array('ID');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Lening");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $vID = filter_input(INPUT_GET, 'ID');
        
        $this->f_detHead("Onderhoud&nbsp;Lenen");
        
        echo "  <form method=\"post\" action=\"start.php?function=crudlenen\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"crudlenen\">\n";
        echo "    <input type=\"hidden\" id=\"I_pageId\" name=\"I_pageId\" value=\"".$_SESSION['session_page']."\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $vID = filter_input(INPUT_GET, 'ID');
            $sql = "SELECT * FROM lenen WHERE lenen.ID = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("i", $_SESSION['session_user'], $vID);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_ID\" name=\"I_ID\" value=\"".$vID."\">\n";
        }
        
        // Export row values to crud class
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        // Row values to be displayed
        $arr_Field = array('type' => 'number', 'width' => 10);
        $this->f_formField('I_ID', 'ID', 'ID', $vShowKey, $arr_Field);

        $arr_Field = array('type' => 'date', 'width' => 24);
        $this->f_formField('I_Datum', 'Datum', 'Datum', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'text', 'width' => 60);
        $this->f_formField('I_Post', 'Post', 'Post', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'text', 'width' => 30);
        $this->f_formField('I_Van', 'Van', 'Van', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'text', 'width' => 30);
        $this->f_formField('I_Aan', 'Aan', 'Aan', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'number', 'width' => 8, 'decimal' => 2);
        $this->f_formField('I_Bedrag', 'Bedrag', 'Bedrag', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'textArea', 'row' => 5, 'col' => 60);
        $this->f_formField('I_Toelichting', 'Toelichting', 'Toelichting', true, $arr_Field);

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
            $vID = filter_input(INPUT_POST, 'I_ID');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM lenen";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    // $stmt->bind_param("ss", $_SESSION['session_user'], $vDebt);
    			$stmt->execute();
    			$p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
      	        if ($num_rows > 0) {
                    $op_value = false;
                    $this->f_setError('Deze lening bestaat reeds');
                }  else  {
                    $op_value = true;
                }
            }  else { echo "Prepare error<br />"; }
        }  
        return $op_value;
    }

    protected function f_update() {
        // Get fields from submitted form
        $vCrud        = $this->f_getCrud();
        $vID_Orig     = filter_input(INPUT_POST, 'I_ID');        // hidden field, original value
        $vDatum       = filter_input(INPUT_POST, 'I_Datum');
        $vPost        = filter_input(INPUT_POST, 'I_Post');
        $vVan         = filter_input(INPUT_POST, 'I_Van');
        $vAan         = filter_input(INPUT_POST, 'I_Aan');
        $vBedrag      = filter_input(INPUT_POST, 'I_Bedrag');
        $vToelichting = filter_input(INPUT_POST, 'I_Toelichting');
        // if ($vCrud == 'N')  {
        //    $vDebt = filter_input(INPUT_POST, 'I_Debt');            // Updated field
        // }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM lening WHERE lening.ID = ?";
                $bindType = "i";
                $bindValue = array($vID);
                break;
            case "E":
                $sql  = "UPDATE lening SET Datum = ?, Post = ?, Van = ?, Aan = ?, Bedrag = ?, Toelichting = ?, WHERE ID = ?";
                $bindType = "ssssisi";
                $bindValue = array($vDatum, $vPost, $vVan, $vAan, $vBedrag, $vToelichting, $vID_Orig);
                break;
            case "N":
                $sql  = "INSERT lening (Datum, Post, Van, Aan, Bedrag, Toelichting) ";
                $sql .= "VALUES (?, ?, ?, ?, ?, ?)";
                $bindType = "ssssis";
                $bindValue = array($vDatum, $vPost, $vVan, $vAan, $vBedrag, $vToelichting);
                break;
        }
        
        if ($stmt->prepare($sql))  {
		    $stmt->bind_param($bindType, ...$bindValue);
            $stmt->execute();
        } else { echo "Prepare error<br />"; }
    }
    
    public function f_allDebts()  {
        $arr_Debts = array();
        $dbConnection = $this->f_getConnect();
        $sql = "SELECT * FROM debts WHERE user_code = '".$_SESSION['session_user']."'";
        $sql  = "SELECT debts.`debt`, `dossier`, debts.`amount`, debt_app.amount AS monthly, debt_app.restamount, MIN(debt_app.date) AS nextdate";
        $sql .= " FROM `debts`, debt_app ";
        $sql .= " WHERE debts.`user_code` = '".$_SESSION['session_user']."'";
        $sql .= "   AND debt_app.user_code = debts.user_code";
        $sql .= "   AND debt_app.debt = debts.debt";
        $sql .= "   AND debt_app.date > CURRENT_DATE()";
        $sql .= " GROUP BY debt_app.user_code, debt_app.debt";
        $result = mysqli_query($dbConnection, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $arr_Debts[] = $row;
        }
        return $arr_Debts;
    }
}

?>

