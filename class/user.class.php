<?php

require_once('class/abst_crud.class.php');

class user extends abst_crud  {
    
    protected function f_list()  {
        $this->f_setPage('cruduser');
        // Get maximum number of rows
        echo "    <div class=\"banner\">Gebruikers&nbspOnderhoud</div>\n";
        $sql = "SELECT count(*) rowCount FROM user";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM user LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('User Code', 'Name', 'Email', 'Tel', 'Actief', 'SU');
        $vFieldNames = array('user_code', 'Name', 'email', 'tel', 'active', 'su');
        $vKeyFields = array('user_code');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Gebruikers");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $vUser = filter_input(INPUT_GET, 'user_code');
        
        $this->f_detHead("Onderhoud&nbsp;Gebruiker");
        
        echo "  <form method=\"post\" action=\"start.php?function=cruduser\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"user\">\n";
        echo "    <input type=\"hidden\" id=\"I_pageId\" name=\"I_pageId\" value=\"".$_SESSION['session_page']."\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $vUser = filter_input(INPUT_GET, 'user_code');
            $sql = "SELECT * FROM user WHERE user.user_code = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("s", $vUser);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_UserOrig\" name=\"I_UserOrig\" value=\"".$vUser."\">\n";
        }

        // Export row values to crud class
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        // Row values to be displayed
        $arr_Field = array('type' => 'text', 'width' => 24);
        $this->f_formField('I_User', 'Gebuiker code', 'user_code', $vShowKey, $arr_Field);

        $arr_Field = array('type' => 'text', 'width' => 40);
        $this->f_formField('I_Name', 'Naam', 'Name', true, $arr_Field);

        $arr_Field = array('type' => 'text', 'width' => 80);
        $this->f_formField('I_Email', 'Email', 'email', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'text', 'width' => 40);
        $this->f_formField('I_Tel', 'Telefoon', 'tel', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'text', 'width' => 40);
        $this->f_formField('I_Password', 'Password', '', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'checkbox');
        $this->f_formField('I_Active', 'Active', 'active', true, $arr_Field)."</td></tr>\n";

        $arr_Field = array('type' => 'checkbox');
        $this->f_formField('I_Su', 'Super User', 'su', true, $arr_Field)."</td></tr>\n";

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
            $vUser = filter_input(INPUT_POST, 'I_User');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM user WHERE user.user_code = ?";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    $stmt->bind_param("s", $vUser);
    			$stmt->execute();
    			$p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
      	        if ($num_rows > 0) {
                    $op_value = false;
                    $this->f_setError('Deze gebruiker bestaat reeds');
                }  else  {
                    $op_value = true;
                }
            }  else { echo "Prepare error<br />"; }
        }  
        return $op_value;
    }

    protected function f_update() {
        $vCrud     = $this->f_getCrud();
        $vUserOrig = filter_input(INPUT_POST, 'I_UserOrig');
        $vName     = filter_input(INPUT_POST, 'I_Name');
        $vTel      = filter_input(INPUT_POST, 'I_Tel');
        $vEmail    = filter_input(INPUT_POST, 'I_Email');
        $vPassword = filter_input(INPUT_POST, 'I_Password');
//        $vActive   = filter_input(INPUT_POST, 'I_Active');
        $vActive = 0;
        if (isset($_POST['I_Active']))  {
            $vActive = 1;
        }
        $vSu = 0;
        if (isset($_POST['I_Su']))  {
            $vSu = 1;
        }
        if ($vCrud == 'N')  {
            $vUser = filter_input(INPUT_POST, 'I_User');
        }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM user WHERE user_code = ?";
                $bindType = "s";
                $bindValue = array($vUserOrig);
                break;
            case "E":
                if ($vPassword != '') {
                echo "Password: ".$vPassword;
                    $vPassword = password_hash($vPassword, PASSWORD_DEFAULT);
                    $sql = "UPDATE user SET Name = ?, email = ?, tel = ?, active = ?, su = ?, password = ? WHERE user_code = ?";
                    $bindType = "sssssss";
                    $bindValue = array($vName, $vEmail, $vTel, $vActive, $vSu, $vPassword, $vUserOrig);
                }  else {
                    $sql = "UPDATE user SET Name = ?, email = ?, tel = ?, active = ?, su = ? WHERE user_code = ?";
                    $bindType = "ssssss";
                    $bindValue = array($vName, $vEmail, $vTel, $vActive, $vSu, $vUserOrig);
                }
                break;
            case "N":
                $sql = "INSERT user (Name, email, tel, user_code, su, password) VALUES (?, ?, ?, ?, ?, ?)";
                $bindType = "ssssss";
                $password = password_hash($vPassword, PASSWORD_DEFAULT);
                $bindValue = array($vName, $vEmail, $vTel, $vUser, $vSu, $vPassword);
                // $this->f_addRules($vName);
                break;
        }
        
        if ($stmt->prepare($sql))  {
		    $stmt->bind_param($bindType, ...$bindValue);
            $stmt->execute();
        } else { echo "Prepare error<br />"; }
    }
    
}

?>