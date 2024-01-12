<?php

require_once('class/page.class.php');
require_once('class/menu.class.php');

abstract class abst_crud extends page {

    protected $m_connection;
    private   $mConnection;
    private   $mOffset     = 0;
    private   $mLimit      = 20;
    private   $mCountRows  = 1;
    private   $mShowRows   = 20;
    private   $mPage;
    private   $mCrud       = 'L';
    private   $mRow;
    private   $mError      = '';
    private   $mNoNew      = 'show';
    private   $mFieldType  = 'text';
    private   $mAltDb      = '';
    private   $mPageId     = '';
    private   $mIsLoggedIn = false;
    private   $mDetails    = false;
    private   $mDetailProg = '';

    // ----------------------------------------------------------------------
    // I N I T I L I S A T I O N
    // ----------------------------------------------------------------------
    function __construct($ip_Conn)  {
        $this->f_setConnect($ip_Conn);
        if ($this->f_checkUser() == false)  {
            $vReason = $this->f_getError();
            echo "You are not logged in.<br />".$vReason."Please login again<br /><br />";
            $this->mIsLoggedIn = false;
        } else {
            $this->mIsLoggedIn = true;
            $this->f_setStyle('css/crud.css?id=2');
        }
        $this->f_setOffset();
    }
    
    public function f_init()  {
        
        $vReturn = true;
        // if (!isset($_POST['submit'])) {
        if (!isset($_POST['I_pageId'])) {
            $vCrud = 'L';
            if (isset($_GET['crud'])) {
                $vCrud = $_GET['crud'];
            }
            $this->f_setCrud($vCrud);
            if ($vCrud == 'L')  {
                $this->f_list();
            } else {
                $this->f_detail(false);
            }
        }  else  {
            // Page has been submitted.
            // First check validation of the input
            $vCrud = 'L';
            if (isset($_POST['I_Crud']))  {
                $vCrud = $_POST['I_Crud'];
            }
            $this->f_setCrud($vCrud);
            // f_valid in main class, overriding abstract fuction.
            if ($this->f_valid($vCrud)) {
                if ($vCrud != 'L')  {
                    $this->f_update($vCrud);
                }
                // f_list in main class overriding abstract function.
                $this->f_list();
            }  else {
                $this->f_detail(true);  // retry;
            }
        }
    }

    // ----------------------------------------------------------------------
    // S E T T E R S / G E T T E R S
    // ----------------------------------------------------------------------
    protected function f_setConnect($ip_Connection)  {
        $this->mConnection = $ip_Connection;
    }
    
    private function f_setOffset()  {
        $vOffset = 0;
        if (isset($_GET['offset'])) {
            $vOffset = $_GET['offset'];
        }
        $this->mOffset = $vOffset;
    }
    
    protected function f_setLimit($ip_limit)  {
        $this->mLimit = $ip_limit;
    }
    
    protected function f_getLimit()  {
        return $this->mLimit;
    }
    
    protected function f_setRows($ip_sql)  {
        $dbConnection = $this->mConnection;
        // m_connection
        $p_Result = mysqli_query($dbConnection, $ip_sql);        
        if (mysqli_num_rows($p_Result) > 0) {
            $row = $p_Result->fetch_object();
            $this->f_CountRows($row->rowCount);
        }
    }
    
    public function f_setPage($ip_Page)  {
        $this->mPage = $ip_Page;
    }
    
    public function f_getPage()  {
        return $this->mPage;
    }

    public function f_setCrud($ip_Crud)  {
        $this->mCrud = $ip_Crud;
    }
    
    public function f_getCrud()  {
        return $this->mCrud;
    }

    protected function f_setRow($ip_Row)  {
        $this->mRow = $ip_Row;
    }
    
    protected function f_getRow()  {
        return $this->mRow;
    }
    
    protected function f_setError($ip_error)  {
        $this->mError = $ip_error;
    }
    
    protected function f_getError()  {
        return $this->mError;
    }
    
    public function f_isLoggedIn() {
        return $this->mIsLoggedIn;
    }

    protected function f_CountRows($ip_rows)  {
        $this->mCountRows = $ip_rows;
    }
    
    protected function f_getConnect()  {
        return $this->mConnection;
    }
    
    protected function f_getOffset()  {
        $vOffset = $this->mOffset;
        $vNavVal = '';
        if (isset($_GET['nav']))  {
            $vNavVal = $_GET['nav'];
        }
        // if (isset($_GET['prev'])) {
        if ($vNavVal == 'prev') {
            $vOffset = max($vOffset - $this->mShowRows, 0);
        }
        // if (isset($_GET['next'])) {
        if ($vNavVal == 'next') {
            $vOffset = min($vOffset + $this->mShowRows, $this->mCountRows - 1);
            // $vOffset = 100 * $this->mCountRows + $this->mShowRows;
        }
        return $vOffset;
    }
    
    protected function f_setNoNew()  {
        $this->mNoNew = 'NoShow';
    }
    
    public function f_setAltDb($ip_dbConn)  {
        $this->mAltDb = $ip_dbConn;
    }

    public function f_setDetail($ip_Detail)  {
        $this->mDetails    = true;
        $this->mDetailProg = $ip_Detail;
    }

    protected function f_getAltDb()  {
        if ($this->mAltDb == '')  {
            $op_conn = $this->f_getConnect();
        }  else  {
            $op_conn = $this->mAltDb;
        }
        return $op_conn;
    }

    protected function f_setFieldType($ip_Type)  {
        $this->mFieldType = $ip_Type;
    }
    
    protected function f_getFieldType()  {
        return $this->mFieldType;
    }
    
    // private function f_setPageId($ip_PageId)  {
    //     $this->mPageId = $ip_PageId;
    // }
    
    private function f_getPageId()  {
        return $this->mPageId;
    }


    // ----------------------------------------------------------------------
    // A U T E N T I C A T I O N
    // To avoid that a page is started directly and not from the menu.
    // ----------------------------------------------------------------------
    private function f_checkUser() {
        
        require_once('class/authoris.class.php');
        $oAuthoris = new authoris();
        // Logged in?
        if ($oAuthoris->f_isLoggedIn() )  {
            $op_value = true;
        }  else {
            $op_value = false;
            $this->f_setError($oAuthoris->f_getReason());
        }
        
        return $op_value;
    }
    
    // ----------------------------------------------------------------------
    // P A G E   C R E A T I O N
    // ----------------------------------------------------------------------
    protected function f_tableDiv($ip_title)  {
        echo "<div class=\"container-fluid\">\n";
        echo "    <div class=\"row\">\n";
        echo "        <div class=\"col-sm-9\">";
        echo "          <table class=\"table table-striped w-auto\" id=\"MainTable\">\n";
        echo "            <thead>\n";
        echo "              <tr><td colspan=\"4\">$ip_title</td>\n";
    }
    
    protected function f_tableDivEnd()  {
        $vPage = $this->f_getPage();
        echo "            </tbody>\n";
        echo "          </table>\n";
        echo "        </div>    <!-- col-sm-9 -->\n";
        echo "    </div>    <!-- row -->\n";
        echo "</div>    <!-- container-fluid -->\n";
        if ($this->mNoNew != 'NoShow')  {
            echo "  <div class=\"newButton\"><a href=\"start.php?function=".$vPage."&crud=N&pageid=".$_SESSION['session_page']."\">Nieuw</a></div>";
        }
//        echo "</div>\n";
    }

    protected function f_navigation() {
        $vPage = $this->f_getPage();
        $vCrud = $this->f_getCrud();
        $vAncer = "<a href=\"start.php?function=".$vPage."&crud=".$vCrud."&userid=".$_SESSION['session_user']."&pageid=".$_SESSION['session_page'];
        echo "<!-- ABST_CRUD.CLASS.PHP f_navigation -->\n";
        echo "<div class=\"whiteline\">&nbsp;</div>\n";
        echo "<div class=\"navigation\">\n";
        echo "  <ul class=\"navtable\">\n";
        echo "    <li>".$vAncer."&nav=first\">first</a></li>\n";
        echo "    <li>".$vAncer."&nav=prev\">prev</a></li>\n";
        echo "    <li>".$vAncer."&nav=next\">next</a></li>\n";
        echo "    <li>".$vAncer."&nav=last\">last</a></li>\n";
        echo "  </ul>\n";
        echo "</div>\n";
    }
    
    protected function f_listResults($ip_sql, $ip_Headers, $ip_FieldNames, $ip_Keyfields, $ip_title)  {
        $this->f_setBootstrap('Bootstrap');
        // $this->f_setScript('js/finovz.js');
        $this->f_head();
        $this->f_smallBanner();
        $this->f_banner($ip_title);
        $oMenu =  new menu();
        $oMenu->f_menu();

        // Execute query
        $dbConn = $this->mConnection;
        $p_Result = mysqli_query($dbConn, $ip_sql)  or die ("Error in query: $ip_sql. ".mysqli_error($dbConn));
        // Div in which table will be created.
        $this->f_tableDiv($ip_title);
        echo "        <tr>";
        foreach($ip_Headers as $vHeader)  {
            echo "<th>".$vHeader."</th>";
        }
        echo "        <th>action</th></tr>\n";    
        if (mysqli_num_rows($p_Result) > 0) {
            // Close table head defined in f_tableDiv
            echo "      </thead>\n";
            echo "      <tbody>\n";
            $vRowcount = 0;
            // Export all lines
			while ($row = $p_Result->fetch_object()) {
					
				$vKey = "";
				$vConnector = "?";
					
				echo "        <tr>";
				// Reset arr_fields
				reset ($ip_FieldNames);
				// Print the value of each field
				foreach ($ip_FieldNames as $vField)   {
					echo "<td>".$row->$vField."</td>";  
				}
                $vParams = '';
                $vConnector = '&';
                $vDetKey = '';
                $vDetCon = '';
                echo "<td>";
                foreach($ip_Keyfields as $vField)  {
                    $vParams .= $vConnector.$vField.'='.$row->$vField;
                    $vDetKey .= $vDetCon.$row->$vField;
                    $vDetCon  = ', ';
                }
                $vPage = $this->f_getPage();
				echo "<span class=\"text-nowrap\"><a href=\"start.php?function=";
                echo $vPage.$vParams."&pageid=".$_SESSION['session_page']."&crud=E\">edit</a> | ";
                echo "<a href=\"start.php?function=".$vPage.$vParams."&pageid=".$_SESSION['session_page']."&crud=D\">delete</a>";
                if ($this->mDetails == true) {
                    echo "  <button type=\"button\" class=\"btn btn-default\" aria-label=\"Left Align\" onclick=\"f_details(".$vRowcount.", ".$vDetKey.")\">\n";
                    echo "     <span class=\"glyphicon glyphicon-chevron-down\" aria-hidden=\"true\"></span>\n";
                    echo "  </button></th>\n";
                    $vRowcount++;
                }
                echo "</span></td></tr>\n";
			}
            
            // Table end.
        }
        $this->f_tableDivEnd();
        // navigation.
        $this->f_navigation();        
        $this->f_footer();
    }

    protected function f_detHead($ip_title)  {
        $this->f_setBootstrap('Bootstrap');
        // $this->f_setScript('js/finovz.js');
        // $this->f_setStyle('css/monthovz.css');
        $this->f_head();
        $this->f_smallBanner();
        $this->f_banner($ip_title);
        $oMenu =  new menu();
        $oMenu->f_menu();
        echo "      <div class=\"container-fluid\">\n";
        echo "          <div class=\"row\">\n";
        echo "              <div class=\"col-sm-7\">\n";
    }
    
    protected function f_detBottom()  {
        echo "              <div class=\"col-sm-7\">\n";
        echo "          <div class=\"row\">\n";
        echo "      <div class=\"container-fluid\">\n";
        $this->f_footer();
    }
    
    protected function f_formField($ip_fldGroup, $ip_label, $ip_rowField, $ip_ShowKey, $ip_FldSettings)  {
        echo "<div class=\"form-group\">\n";
        echo "    <label for=\"$ip_fldGroup\">$ip_label</label>\n";
        echo $this->f_dispField($ip_fldGroup, $ip_rowField, $ip_ShowKey, $ip_FldSettings)."\n";
        echo "</div>\n";
    }
    
    protected function f_dispField($ip_name, $ip_field, $ip_upd, $ip_FldSettings)  {
        $row = $this->f_getRow();
        $vCrud = $this->f_getCrud();
        $vValue = '';
        if (!isset($_POST['submit'])) {
            // Get current value from current row. (Edit or Delete)
            if (($vCrud == 'E' || $vCrud == 'D') && $row != '' && $ip_field != '')  {
                $vValue = $row->$ip_field;
            }
        } else {
            // Get current value from submitted field
            $vValue = filter_input(INPUT_POST, $ip_name);
        }
        // New and Edit needs an input field. Delete only display
        if ($vCrud == 'D')  {
            $op_value = $vValue;
        }  else  {
            $vDisabled = '';
            if (!$ip_upd) {
                $vDisabled = ' disabled';
            }
            // $vType = "text";
            $vType = $this->f_getFieldType();
            if (array_key_exists('type', $ip_FldSettings)) {
                $vType = $ip_FldSettings['type'];
            }
            $pos = strrpos($ip_name, "Passw");
            if ($pos !== false) { 
                // not found...
                $vType = 'password';
            }
            switch ($vType)  {
                case 'textArea':
                    $vRows = 4;
                    $vCols = 60;
                    if (array_key_exists('row', $ip_FldSettings))  {
                        $vRows = $ip_FldSettings['row'];
                    }
                    if (array_key_exists('cal', $ip_FldSettings))  {
                        $vCols = $ip_FldSettings['col'];
                    }
                    $op_value  = "<textarea id=\"".$ip_name."\" name=\"".$ip_name."\" rows=\"$vRows\" cols=\"$vCols\">";
                    $op_value .= $vValue;
                    $op_value .= "</textarea>";
                    break;
                case 'checkbox':
                    if ($vValue == 1) {
                        $vChecked = 'checked';
                    }  else  {
                        $vChecked = '';
                    }
                    $op_value  = "<input type=\"checkbox\"  id=\"".$ip_name."\" name=\"".$ip_name."\" $vChecked>";
                    break;
                case 'combobox':
                    if (array_key_exists('table', $ip_FldSettings))  {
                        // Get the combo values from the table
                        $arr_Table = $ip_FldSettings['table'];
                        $sql = "SELECT ".$arr_Table['key']." AS keyValue, ".$arr_Table['value']." AS dispValue FROM ".$arr_Table['name'];
                        $dbConn = $this->mConnection;
                        $p_Result = mysqli_query($dbConn, $sql)  or die ("Error in query: $sql. ".mysqli_error($dbConn));
                        $op_value = "<select id=\"".$ip_name."\" name=\"".$ip_name."\">\n";
                        if (mysqli_num_rows($p_Result) > 0) {
                            if ($vValue == '' && array_key_exists('default', $arr_Table))  {
                                $vValue = $arr_Table['default'];
                            }
                			while ($row = $p_Result->fetch_object()) {
                                $vSelect = "";
                                if ($vValue == $row->keyValue) {
                                    $vSelect = " selected";
                                }
                                $op_value .= "    <option value=\"".$row->keyValue."\"$vSelect>".$row->dispValue."</option>";
                            }
                        }
                        $op_value .= "</select>\n";
                    }
                    break;
                default:    
                    $vWidth = '';
                    if (array_key_exists('width', $ip_FldSettings))  {
                        $vWidth = " size=\"".$ip_FldSettings['width']."\"";
                    }
                    $vStep = '';
                    if ($vType == 'number' && array_key_exists('decimal', $ip_FldSettings))  {
                        if ($ip_FldSettings['decimal'] > 0)  {
                            $vStep = " pattern=\"^\d+(?:\.\d{1,2})?$\" min=\"-99999\" step=\"0.01\"";
                        }
                    }
                    $op_value = "<input type=\"".$vType."\"$vStep id=\"".$ip_name."\" name=\"".$ip_name."\" value=\"".$vValue."\"".$vWidth.$vDisabled.">";
            }
            $this->f_setFieldType('text');
        }
        return $op_value;
    }
    
    // Force Extending class to define this method
    abstract protected function f_list();
    abstract protected function f_detail($ip_update);
    abstract protected function f_valid();
    abstract protected function f_update();

}

?>