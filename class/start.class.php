<?php

class start  {

    private $m_Rejection = '';
    private $m_DbConnection;
    private $m_PageId = "";
     
    private function f_setDbConnection($ip_connect)  {
        $this->m_DbConnection = $ip_connect;
    } 
    
    private function f_getDbConnection()  {
        return $this->m_DbConnection;
    }
    
    private function f_setRejection($ip_Reason)  {
        $this->m_Rejection = $ip_Reason;
    }
    
    private function f_getRejection()  {
        return $this->m_Rejection;
    }
    
    private function f_setPageId($ip_PageId)  {
        $this->m_PageId = $ip_PageId;
    }
    
    private function f_getPageId()  {
        return $this->m_PageId;
    }
    
    public function f_init()  {
        // Find the page to be displayed.
        // -- If not logged in
        // -- -- Page submitted? --> check login
        // -- -- -- Login ok --> Init menu
        // -- -- -- Go to login page
        // -- -- Go to login page
        // -- Requested page Indicated?
        // -- -- Go to requested page
        // -- -- Go to int page.
      
        require_once('class/authoris.class.php');
        $oAuthoris = new authoris();
        if (isset($_GET['report']) && $_GET['report'] == 'yes') {
            $oAuthoris->f_setRemain();
        }
        // Logged in?
        if ($oAuthoris->f_isLoggedIn() )  {
            $this->f_setDbConnection($oAuthoris->f_getDbConnection());
            // $this->f_setPageId($oAuthoris->f_getPageId());
            $this->f_startPage();
        }  else {
            // "Geen authorisatie<br />";
            $this->f_setRejection($oAuthoris->f_getReason());
            $this->f_login();
        }
    }
    
    private function f_login()  {
        require_once('class/login.class.php');
        
        $oLogin = new login();
        $oLogin->f_setRejection($this->f_getRejection());
        $oLogin->start();
    }
    
    private function f_startPage()  {
        // What page should be displayed
        // Available functions
        // Financial overview   finovz
        // Maintenance posts    crudpost
        // Budget               bugdet
        // Initial page         init
        $vFunction = 'init';
        if (isset($_GET['function']))  {
            $vFunction = $_GET['function'];
        }
        switch($vFunction)  {
            case 'finovz':
                require_once('class/finovz.class.php');
                $vPeriod = "Year";
                if (isset($_GET['period'])) {
                    $vPeriod = $_GET['period'];
                } 
                $oFinovz = new finovz($this->f_getDbConnection());
                $oFinovz->f_init($vPeriod);
                break;
            case 'finmndovz':
                require_once('class/monthovz.class.php');
                $oFinMndovz = new monthovz($this->f_getDbConnection());
                $oFinMndovz->f_init();
                break;
            case 'kostgeldmnt':
                require_once('class/kostgeld.class.php');
                $oDebts = new debts($this->f_getDbConnection());
                $oDebts->f_init();
                break;    
            case 'crudlenen':
                require_once('class/crudlenen.class.php');
                $oLenen = new crudlenen($this->f_getDbConnection());
                $oLenen->f_init();
                break;    
            case 'debts':
                require_once('class/debts.class.php');
                $oDebts = new debts($this->f_getDbConnection());
                $oDebts->f_init();
                break;    
            case 'inout':
                require_once('class/inout.class.php');
                $oInout = new inout($this->f_getDbConnection());
                $oInout->f_init();
                break;
            case 'crudposts':
                require_once('class/post.class.php');
                $oPost = new post($this->f_getDbConnection());
                $vLoginOk = $oPost->f_isLoggedIn();
                $oPost->f_init();
                if ($vLoginOk == false) {
                    require_once('class/initpage.class.php');
                    $oInit = new initpage();
                    $oInit->f_init();
                }
                break;
            case 'cruduser':
                require_once('class/user.class.php');
                $oUser = new user($this->f_getDbConnection());
                $vLoginOk = $oUser->f_isLoggedIn();
                $oUser->f_init();
                if ($vLoginOk == false) {
                echo "Login is false<br />";
                    require_once('class/initpage.class.php');
                    $oInit = new initpage();
                    $oInit->f_init();
                }
                break;
            case 'crudperiod':
                require_once('class/period.class.php');
                $oPeriod = new period($this->f_getDbConnection());
                $vLoginOk = $oPeriod->f_isLoggedIn();
                $oPeriod->f_init();
                if ($vLoginOk == false) {
                    require_once('class/initpage.class.php');
                    $oInit = new initpage();
                    $oInit->f_init();
                }
                break;
            case 'cruddebt':
                require_once('class/cruddebt.class.php');
                $oDebt = new cruddebt($this->f_getDbConnection());
                $vLoginOk = $oDebt->f_isLoggedIn();
                $oDebt->f_init();
                if ($vLoginOk == false) {
                    require_once('class/initpage.class.php');
                    $oInit = new initpage();
                    $oInit->f_init();
                }
                break;
            case 'crudbank':
                require_once('class/bank.class.php');
                $oBank = new bank($this->f_getDbConnection());
                $vLoginOk = $oBank->f_isLoggedIn();
                $oBank->f_init();
                if ($vLoginOk == false) {
                    require_once('class/initpage.class.php');
                    $oInit = new initpage();
                    $oInit->f_init();
                }
                break;
            case 'rap_inout':
                require_once('class/rep_inout.class.php');
                $oReport = new rep_inout($this->f_getDbConnection());
                $oReport->f_print();
                break;
			case 'afmelden':
				session_destroy();    
            default:
                require_once('class/initpage.class.php');
                $oInit = new initpage();
                $oInit->f_init();
                break;
        }
    }

}

    ?>