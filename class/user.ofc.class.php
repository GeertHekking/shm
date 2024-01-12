<?php

require_once('class/abst_crud.class.php');

class user extends abst_crud  {
    
    protected function f_list()  {
        // Get maximum number of rows
        echo "    <div class=\"banner\">Gebruikers&nbsp;&nbsp;&nbsp;Onderhoud<br />One for Christ</div>\n";
        $sql = "SELECT count(*) rowCount FROM user";
        $this->f_setRows($sql);
        $vOffset = $this->f_getOffset();
        $vLimit = $this->f_getLimit();
        $sql = "SELECT * FROM user LIMIT ".$vLimit." OFFSET ".$vOffset;
        $vHeaders = array('User Id', 'Name', 'Email', 'Tel', 'Actief');
        $vFieldNames = array('user_code', 'Name', 'email', 'tel', 'active');
        $vKeyFields = array('user_code');
        $vOk = $this->f_listResults($sql, $vHeaders, $vFieldNames, $vKeyFields, "Users");
    }
    
    protected function f_detail($ip_updated)  {
        // Edit of Delete
        $vCrud = $this->f_getCrud();
        $vError = $this->f_getError();
        
        $dbConnection = $this->f_getConnect();
        $vUser = '';
        $row = '';
        $userid = filter_input(INPUT_GET, 'userid');
        
        echo "    <div class=\"banner\">Gebruiker&nbsp;&nbsp;&nbsp;Onderhoud<br />One for Christ</div>\n";
        echo "<div class=\"detail\">\n";
        echo "  <form method=\"post\" action=\"ofc.php\">\n";
        echo "    <input type=\"hidden\" id=\"I_Page\" name=\"I_Page\" value=\"user\">\n";
        echo "    <input type=\"hidden\" id=\"I_Crud\" name=\"I_Crud\" value=\"".$vCrud."\">\n";
        if ($vError != '')  {
            echo "<span class=\"inputError\">".$vError."</span>\n";
        }
        if ($vCrud != "N") {
            $userid = filter_input(INPUT_GET, 'userid');
            $sql = "SELECT * FROM user WHERE user.userid = ?";
       		$stmt = $dbConnection->stmt_init();
		    if ($stmt->prepare($sql))  {
		        $stmt->bind_param("s", $userid);
			    $stmt->execute();
			    $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
  	            if ($num_rows > 0) {
                    $row = $p_Result->fetch_object();
                }
            }
            echo "    <input type=\"hidden\" id=\"I_Userid\" name=\"I_Userid\" value=\"".$userid."\">\n";
        }
        
        echo "    <table>\n";
        $this->f_setRow($row);
        $vShowKey = false;
        if ($vCrud == 'N') {
            $vShowKey = true;
        }
        echo "      <tr><th>Gebruiker</th><td>".$this->f_dispField('I_User', 'userid', $vShowKey)."</td></tr>\n";
        echo "      <tr><th>Naam</th><td>".$this->f_dispField('I_Name', 'name', true)."</td></tr>\n";
        echo "      <tr><th>Email</th><td>".$this->f_dispField('I_Email', 'email', true)."</td></tr>\n";
        echo "      <tr><th>Telefoon</th><td>".$this->f_dispField('I_Tel', 'tel', true)."</td></tr>\n";
        if ($vCrud == 'N' || $vCrud == 'E')  {
            echo "      <tr><th>Wachtwoord</th><td>".$this->f_dispField('I_Password', '', true)."</td></tr>\n";
        }
        echo "    </table>\n";
        echo "    <br /><br /><input type=\"submit\" id=\"submit\" name=\"submit\" value=\"submit\">\n";
        echo "  </form>\n";
        echo "</div>\n";
        
        // Roles per user
        $sql  = "SELECT role.role role, userid FROM role ";
        $sql .= "LEFT JOIN usrrole ON usrrole.role = role.role AND usrrole.userid = '".$userid."'";
        $p_Result = mysqli_query($dbConnection, $sql) or die ("Error in query: $sql. ".mysqli_error($dbConnection));
        echo "<div class=\"userRoles\">\n";
        echo "  <h4>Rollen</h4>\n";
        echo "  <table>\n";
        while ($row = $p_Result->fetch_object())  {
            $vCheckName = "chk-".$row->role;
            $vChecked = '';
            if ($row->userid != null) {
                $vChecked = ' checked';
            }
            echo "      <tr><td><input type=\"checkbox\" id=\"".$vCheckName."\" name=\"".$vCheckName."\"".$vChecked;
            echo " onclick=\"f_clicked(this)\">".$row->role."</td></tr>\n";
        }
        echo "  </table>\n"; 
        echo "  <script>\n";
        echo "    function f_clicked(e) {\n";
        echo "      vChecked = 'not';\n";
        echo "      if (document.getElementById(e.id).checked) {\n";
        echo "          vChecked = 'not';\n";
        echo "      }  else {\n";
        echo "          vChecked = 'checked';\n";
        echo "      }\n";
        echo "      vUser    = document.getElementById('I_User').value;\n";

        echo "	    var url = 'updRole.php?page=user&user=' + vUser + '&id=' + e.id + '&checked=' + vChecked + '&userid=' + \"".$_SESSION['session_user']."\" + '&session=' + \"".$_SESSION['session_id']."\";\n";
        echo "  	var xhr = new XMLHttpRequest();\n";
        echo "	    console.log('Start Ajax call ' + url);\n";
	
        echo "  	xhr.open('GET', url, true);\n";
        echo "      xhr.onreadystatechange = function () {\n";
        echo "     		if(xhr.readyState == 4 && xhr.status == 200) {\n";
        echo "	    		console.log('Result:' + xhr.responseText);\n";
        echo "              vResponse = xhr.responseText;\n";
        echo "              if (vResponse == 'error') {\n";
        echo "                  alert('Rol niet bijgewerkt voor deze gebruiker')\n";
        echo "              }\n";
        echo "  		}\n";
        echo "	    }\n";
	
        echo "  	xhr.send();\n";

        echo "    }\n";
        echo "  </script>";
        echo "</div>\n";
    }

    protected function f_valid()  {
        $op_value = true;
        $vCrud = $this->f_getCrud();
        // Only new records need to be checked.
        if ($vCrud == 'N')  {
            $op_value = true;
            $userid = filter_input(INPUT_POST, 'I_User');
            $dbConnection = $this->f_getConnect();
            $sql = "SELECT * FROM user WHERE user.userid = ?";
       		$stmt = $dbConnection->stmt_init();
    		if ($stmt->prepare($sql))  {
    		    $stmt->bind_param("s", $userid);
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
        $vCrud    = $this->f_getCrud();
        $userid   = filter_input(INPUT_POST, 'I_Userid');
        $name     = filter_input(INPUT_POST, 'I_Name');
        $tel      = filter_input(INPUT_POST, 'I_Tel');
        $email    = filter_input(INPUT_POST, 'I_Email');
        $password = filter_input(INPUT_POST, 'I_Password');
        if ($vCrud == 'N')  {
            $userid = filter_input(INPUT_POST, 'I_User');
         }
        $dbConnection = $this->f_getConnect();
        $stmt = $dbConnection->stmt_init();
        switch ($vCrud)  {
            case "D":
                $sql = "DELETE FROM user WHERE userid = ?";
                $bindType = "s";
                $bindValue = array($userid);
                break;
            case "E":
                if ($password != '') {
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE user SET name = ?, email = ?, tel = ?, password = ? WHERE userid = ?";
                    $bindType = "sssss";
                    $bindValue = array($name, $email, $tel, $password, $userid);
                }  else {
                    $sql = "UPDATE user SET name = ?, email = ?, tel = ? WHERE userid = ?";
                    $bindType = "ssss";
                    $bindValue = array($name, $email, $tel, $userid);
                }
                break;
            case "N":
                $sql = "INSERT user (name, email, tel, userid, password) VALUES (?, ?, ?, ?, ?)";
                $bindType = "sssss";
                $password = password_hash($password, PASSWORD_DEFAULT);
                $bindValue = array($name, $email, $tel, $userid, $password);
                $this->f_addRules($name);
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
    
    private function f_addRules($ip_Name)  {
        $dbConnection = $this->f_getConnect();
        $vValues = '';
        $vSeparator = '';
        
        $sqlR  = "SELECT role.role FROM role ";
        $p_Result = mysqli_query($dbConnection, $sqlR) or die ("Error in query: $sqlR. ".mysqli_error($dbConnection));
        while ($row = $p_Result->fetch_object())  {
            $vCheckName = "chk-".$row->role;
            $vChecked = '';
            if (isset($_POST[$vCheckName])) {
                $vValues .= $vSeparator."('".$ip_Name."', '".$row->role."')";
                $vSeparator = ", ";
            }
        }
        if ($vValues != '') {
            $sqlR = "INSERT INTO usrrole (userid, role) VALUES ".$vValues;
            $p_Result = mysqli_query($dbConnection, $sqlR) or die ("Error in query: $sqlR. ".mysqli_error($dbConnection));
        }
    }
    
}

?>