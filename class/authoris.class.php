<?php

class authoris  {

    private $m_connection;
    private $m_Reason = '';
    private $m_remain = false;
    private static $m_lastPage = "";
    
    public function __construct()  {
        if (debug_backtrace()[1]['function'] == 'f_init')  {
            session_start();
            require_once('../../private_incl/shm.incl');
        }
    }
    
    public function f_getReason()  {
        return $this->m_Reason;
    }
    
    public function f_setDbConnection($ip_connection)  {
        $this->m_connection = $ip_connection;
    }
    
    public function f_getDbConnection()  {
        return $this->m_connection;
    }
    
    private function f_setReject($ip_reason) {
        $this->m_Reason = $ip_reason;
    }
    
    private function f_setPage($ip_page)  {
        if (self::$m_lastPage == "")  {
            self::$m_lastPage = $ip_page;
        }
    }
    
    private function f_getPage()  {
        return self::$m_lastPage;
    }
    
    public function f_setRemain()  {
        $this->m_remain = true;
    }

    public function f_isLoggedIn()  {
        // A user is logged in if
        // -- session_userid has an active record
        // -- page code is correct
        //    or 
        // -- login credentials are correct (username, password)
        $v_LoggedIn = false;
        $sql = "";
        $vPageId = "";
        if (isset($_POST['I_pageId']))  {
            $vPageId = $_POST['I_pageId'];
        }  else {
            if (isset($_GET['pageid'])) {
                $vPageId = $_GET['pageid'];
            }
        }

        if (isset($_SESSION['session_user']) && $vPageId != "" ) {
            // See if session is still active
            if ($vPageId == $this->f_getPage())  {
                $v_LoggedIn = true;
            }  else  {
                $v_LoggedIn = $this->f_activeSessionCheck($vPageId);
            }
        }  else {
            // Check login credentials
            $v_LoggedIn = $this->f_LoginCreditCheck();
        }
        return $v_LoggedIn;
    }

    private function f_activeSessionCheck($ip_PageId)  {
        // The session id has been found. Now check if the sessioCode is equal to the page id.
        $vActive = false;
        $is_page_refreshed = (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0');
        if($is_page_refreshed ) {
            $sql = "SELECT * FROM login WHERE login.user_code = ?";
        }  else  {
            $sql = "SELECT * FROM login WHERE login.user_code = ? AND login.sessionCode = ?";
        }
        $stmt = $this->m_connection->stmt_init();

        if ($stmt->prepare($sql))  {
            if($is_page_refreshed ) {
                $stmt->bind_param("s", $_SESSION['session_user']);
            }  else  {
                $stmt->bind_param("ss", $_SESSION['session_user'], $ip_PageId);
            }
            $stmt->execute();
            $p_Result = $stmt->get_result();
            $num_rows = mysqli_num_rows($p_Result);
            if ($num_rows > 0) {
                // Session record is found. Now check if session has been expired.
                // LastAction contains the time of the last action
                $row = $p_Result->fetch_object();
                $vLastAction = strtotime($row->LastAction);
                $date        = new DateTime();
                $vCurTime    = $date->getTimestamp();
                // Difference less than 30 minutes?
                if ($vLastAction + (60 * 30) > $vCurTime) {
                    // Session ok and valid. 
                    $vActive = true;
                    // Set new time stamp and get new sessionCode
                    if ($this->m_remain === false) {
                        $_SESSION['session_page'] = $this->f_sessionCode();
                    }
                    $sql = "UPDATE login Set `sessionCode` = ?, LastAction = now() WHERE `user_code` = ?";
                    if ($stmt->prepare($sql))  {
                        $stmt->bind_param("ss", $_SESSION['session_page'], $_SESSION['session_user']);
                        $stmt->execute();
                        $this->f_setPage($ip_PageId);
                    }  else {
                        echo "Prepare error ".$sql." ".$_SESSION['session_page']." ".$_SESSION['session_user']."<br />";
                    }
                }  else {
                    $this->f_setReject('Session expired');
                } 
            }  else {
                $this->f_setReject('No active session');
            }
        }  else {
            $this->f_setReject('SQL Error');
        }
        return $vActive;    
    }
    
    private function f_LoginCreditCheck()  {
        // Session variable not set Check login credentials
        $v_LoggedIn = false;
        if (isset($_POST['I_User']) && isset($_POST['I_Password']))  {
            $_SESSION['session_su'] = "";
            $sql  = "SELECT user.Name, user.password, login.user_code AS login_user, user.su";
            $sql .= "  FROM user ";
            $sql .= "LEFT JOIN login ON login.user_code = user.user_code ";
            $sql .= " WHERE user.user_code = ?";
            // AND (user.password = ? OR user.password = ?)";
            $stmt = $this->m_connection->stmt_init();
            if ($stmt->prepare($sql))  {
                $vPassword = $_POST['I_Password'];
                $stmt->bind_param("s", $_POST['I_User']);
                $stmt->execute();
                $p_Result = $stmt->get_result();
                $num_rows = mysqli_num_rows($p_Result);
                if ($num_rows > 0) {
                    // Login ok
                    $row = $p_Result->fetch_object();
                    if (password_verify($vPassword, $row->password) || $vPassword = $row->password)  {
                        $v_LoggedIn = true;

                        // Set session variables
                        $_SESSION['session_page'] = $this->f_sessionCode();
                        $_SESSION['session_user'] = $_POST['I_User'];
                        if (isset($_POST['chk_beheerder']) && $row->su)  {
                            $_SESSION['session_su'] = 'SuperUser';
                        }
                        
                        // Create login information
                        if ($row->login_user == NULL) {
                            $sql = "INSERT INTO login (`user_code`, `LoginTime`, `LastAction`, `sessionCode`) VALUES (?, now(), now(), ?)";                        
                            if ($stmt->prepare($sql))  {
                                $stmt->bind_param("ss", $_POST['I_User'], $_SESSION['session_page']);
                            }
                        }  else {
                            $sql = "UPDATE login Set `sessionCode` = ?, LastAction = now() WHERE `user_code` = ?";
                            if ($stmt->prepare($sql))  {
                                $stmt->bind_param("ss", $_SESSION['session_page'], $_POST['I_User']);
                            }    else  {
                                echo "Prepare Error<br />";
                            }
                        }
                        $stmt->execute();

                    }   else  {
                        $v_LoggedIn = false;
                    }
                }  else  {
                    // Incorrecte login
                    $this->f_setReject('Incorrect username password combination');
                }
            }  else {
                $this->f_setReject('SQL error '.$sql);
            }
        }
        return $v_LoggedIn;
    }
    
    private function f_sessionCode() {
        $vSessionCode = '';
        $vCharacters  = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@$%^';
        $vStrLength   = strlen($vCharacters);
        for ($i = 0; $i < 12; $i++)  {
            $vSessionCode .= substr($vCharacters, rand(0, $vStrLength), 1);
        }
        return $vSessionCode;
    }
    
}

?>