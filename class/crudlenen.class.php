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
        $vHeaders    = array('Datum', 'Van', 'Aan', 'Post', 'Bedrag');
        $vFieldNames = array('Datum', 'Van', 'Aan', 'Post', 'Bedrag');
        $vKeyFields  = array('ID');
        // echo "<br /><br /><br /><br />Set Detail".PHP_EOL;
        $this->f_setScript('js/lenen.js?id=6');
        $this->f_setDetail('https://www.gghekking.nl/shm/leenDetail.php');
        // Save userId
        echo "<input type=\"hidden\" id=\"I_UserId\" value=\"".$_SESSION['session_user']."\">";
        echo "<input type=\"hidden\" id=\"I_SessionId\" value=\"".$_SESSION['session_page']."\">";
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "lening");
        $this->f_grouptotals();
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
            $sql = "SELECT * FROM lening WHERE lening.ID = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("i", $vID);
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
        // if ($vCrud == 'N')  {
        //     $op_value = true;
        //     $vID = filter_input(INPUT_POST, 'I_ID');
        //     $dbConnection = $this->f_getConnect();
        //     $sql = "SELECT * FROM lening";
       	// 	$stmt = $dbConnection->stmt_init();
    	// 	if ($stmt->prepare($sql))  {
    	// 	    // $stmt->bind_param("ss", $_SESSION['session_user'], $vDebt);
    	// 		$stmt->execute();
    	// 		$p_Result = $stmt->get_result();
        //         $num_rows = mysqli_num_rows($p_Result);
      	//         if ($num_rows > 0) {
        //            $op_value = false;
        //             $this->f_setError('Deze lening bestaat reeds');
        //         }  else  {
        //             $op_value = true;
        //         }
        //     }  else { echo "Prepare error<br />"; }
        // }  
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
                $bindValue = array($vID_Orig);
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

    private function f_grouptotals()  {
        // $sql  = "SELECT `Van`, sum(`Bedrag`) AS \"total\"\n";
        // $sql .= "FROM `lening` \n";
        // $sql .= "GROUP BY `Van`;";

        $arr_totals = array();
        $sql  = "SELECT lening.ID, lening.Van, lening.Aan, lening.Post, lening.Bedrag, paylening.`Bedrag` AS 'Voldaan' ";
        $sql .= "FROM lening LEFT JOIN paylening ";
        $sql .= "ON paylening.leningID = lening.ID;";

        // Execute query
        $dbConnection = $this->f_getConnect();
        $p_Result = mysqli_query($dbConnection, $sql)  or die ("Error in query: $sql. ".mysqli_error($dbConnection));
        $vLastPost = '';
        if (mysqli_num_rows($p_Result) > 0) {
            while ($row = $p_Result->fetch_object()) {

                if (!array_key_exists($row->Van, $arr_totals)) {
                    $arr_totals[$row->Van] = array('Leenbedrag' => 0, 'Betaald' => 0, 'Ontvangen' => 0);
                }
                if (!array_key_exists($row->Aan, $arr_totals)) {
                    $arr_totals[$row->Aan] = array('Leenbedrag' => 0, 'Betaald' => 0, 'Ontvangen' => 0);
                }

                if ($row->Post != $vLastPost)  {
                    $arr_totals[$row->Van]['Leenbedrag'] += $row->Bedrag;
                    $arr_totals[$row->Aan]['Leenbedrag'] -= $row->Bedrag;
                    $vLastPost = $row->Post;
                }

                if ($row->Voldaan != null) {
                    $arr_totals[$row->Van]['Ontvangen']  += $row->Voldaan;
                    $arr_totals[$row->Aan]['Betaald']    += $row->Voldaan;
                }

            }
            
        }

        echo "<table class=\"table table-dark\">\n";
        echo "  <thead>\n";
        echo "    <tr>\n";
        echo "      <th scope=\"col\">Persoon</th>\n";
        echo "      <th scope=\"col\">Geleend</th>\n";
        echo "      <th scope=\"col\">Afgelost</th>\n;";
        echo "      <th scope=\"col\">Terug Ontvangen</th>\n";
        echo "      <th scope=\"col\">Verschil</th>\n";
        echo "    </tr>\n";
        echo "  </thead>\n";
        echo "  <tbody>\n";
        foreach ($arr_totals AS $Persoon => $Bedragen) {
            echo "  <tr><td>".$Persoon."</td><td>".$Bedragen['Leenbedrag']."</td><td>".$Bedragen['Betaald']."</td><td>".$Bedragen['Ontvangen']."</td><td>".$Bedragen['Leenbedrag'] + $Bedragen['Betaald'] - $Bedragen['Ontvangen']."</td></tr>\n"; 
        }
        echo "  </tbody>\n";
        echo "</table>\n";

    }

}


?>

