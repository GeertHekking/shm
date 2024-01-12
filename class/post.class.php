<?php

require_once('class/abst_crud.class.php');

class post extends abst_crud  {
    
    protected function f_list()  {
        // Get maximum number of rows
        $this->f_setPage('crudposts');
        echo "    <div class=\"banner\">Posten&nbsp;&nbsp;&nbsp;Onderhoud</div>\n";
        $sql = "SELECT count(*) rowCount FROM posts WHERE user_code = '".$_SESSION['session_user']."'";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM posts WHERE user_code = '".$_SESSION['session_user']."' LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('Post', 'Omschrijving', 'Bedrag', 'In/Uit', 'Periode');
        $vFieldNames = array('post', 'description', 'bedrag', 'inkomst', 'period');
        $vKeyFields = array('post');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Posts");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $vPost = filter_input(INPUT_GET, 'post');
        
        $this->f_detHead("Onderhoud&nbsp;Posten");
        
        echo "  <form method=\"post\" action=\"start.php?function=crudposts\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"crudposts\">\n";
        echo "    <input type=\"hidden\" id=\"I_pageId\" name=\"I_pageId\" value=\"".$_SESSION['session_page']."\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $userid = filter_input(INPUT_GET, 'userid');
            $sql = "SELECT * FROM posts WHERE posts.user_code = ? AND posts.post = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("ss", $_SESSION['session_user'], $vPost);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_PostOrig\" name=\"I_PostOrig\" value=\"".$vPost."\">\n";
        }
        
        // Export row values to crud class
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        // Row values to be displayed
        $arr_Field = array('type' => 'text', 'width' => 24);
        $this->f_formField('I_Post', 'Post', 'post', $vShowKey, $arr_Field);

        $arr_Field = array('type' => 'text', 'width' => 80);
        $this->f_formField('I_Description', 'Omschrijving', 'description', true, $arr_Field);

        $arr_Field = array('type' => 'checkbox');
        $this->f_formField('I_Inkomst', 'Inkomst', 'inkomst', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'number', 'width' => 10, 'decimal' => 2);
        $this->f_formField('I_Bedrag', 'Bedrag', 'bedrag', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'combobox', 'width' => 40, 'table' => array('name' => 'period', 'key' => 'period', 'value' => 'period', 'default' => 'maand'));
        $this->f_formField('I_Period', 'Periode', 'period', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'number', 'width' => 8, 'decimal' => 0);
        $this->f_formField('I_Dag', 'Dag', 'dag', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'number', 'width' => 8, 'decimal' => 0);
        $this->f_formField('I_Maand', 'Maand', 'maand', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'text', 'width' => 40);
        $this->f_formField('I_Rekening', 'Rekening', 'rekening', true, $arr_Field)."</td></tr>\n";

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
            $vPost = filter_input(INPUT_POST, 'I_Post');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM posts WHERE posts.user_code = ? AND posts.post = ?";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    $stmt->bind_param("ss", $_SESSION['session_user'], $vPost);
    			$stmt->execute();
    			$p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
      	        if ($num_rows > 0) {
                    $op_value = false;
                    $this->f_setError('Deze post bestaat reeds');
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
        $vPostOrig = filter_input(INPUT_POST, 'I_PostOrig');        // hidden field, original value
        $vDescript = filter_input(INPUT_POST, 'I_Description');
        // $vInkomst  = filter_input(INPUT_POST, 'I_Inkomst');
        $vBedrag   = filter_input(INPUT_POST, 'I_Bedrag');
        $vPeriod   = filter_input(INPUT_POST, 'I_Period');
        $vDag      = filter_input(INPUT_POST, 'I_Dag');
        $vMaand    = filter_input(INPUT_POST, 'I_Maand');
        $vRekening = filter_input(INPUT_POST, 'I_Rekening');
        if (isset($_POST['I_Inkomst']))  {
            $vInkomst = 1;
        }  else {
            $vInkomst = 0;
        }
        if ($vCrud == 'N')  {
            $vPost = filter_input(INPUT_POST, 'I_Post');            // Updated field
         }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM posts WHERE posts.user_code = ? AND posts.post = ?";
                $bindType = "ss";
                $bindValue = array($_SESSION['session_user'], $vPostOrig);
                break;
            case "E":
                $sql  = "UPDATE posts SET description = ?, inkomst = ?, bedrag = ?,";
                $sql .= " period = ?, dag = ?, maand = ?, rekening = ? WHERE user_code = ? AND post = ?";
                $bindType = "ssdsiisss";
                $bindValue = array($vDescript, $vInkomst, $vBedrag, $vPeriod, $vDag, $vMaand, $vRekening, $_SESSION['session_user'], $vPostOrig);
                break;
            case "N":
                $sql  = "INSERT posts (user_code, post, description, inkomst, bedrag, period, dag, maand, rekening) ";
                $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $bindType = "ssssdsiis";
                $bindValue = array($_SESSION['session_user'], $vPost, $vDescript, $vInkomst, $vBedrag, $vPeriod, $vDag, $vMaand, $vRekening);
                break;
        }
        
        if ($stmt->prepare($sql))  {
		    $stmt->bind_param($bindType, ...$bindValue);
            $stmt->execute();
            if ($vCrud == 'D')  {
                // Delete user session records and user roles
                $sql = "DELETE FROM usrrole WHERE userid = ?";
                if ($stmt->prepare($sql))  {
                    $stmt->bind_param("s", $userid);
                    $stmt->execute();
                }

                $sql = "DELETE FROM session WHERE userid = ?";
                if ($stmt->prepare($sql))  {
                    $stmt->bind_param("s", $userid);
                    $stmt->execute();
                }
            }
        } else { echo "Prepare error<br />"; }
    }
    
}

?>