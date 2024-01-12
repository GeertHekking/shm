<?php

require_once('class/base_report.class.php');

class rep_inout {
    
    private $pdf;
    private $dbConnection;
    
    public function __construct($ip_Connect)  {
        $this->dbConnection = $ip_Connect; 
    }
    
    public function f_print()  {
        $arr_data = array();
        // Instanciation of inherited class
        $pdf = new base_report();
        $pdf->f_setTitle('Overzicht Inkomsten / Uitgaven');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        // $pdf->SetFont('Times','',12);
        $pdf->SetFont('Arial','',10);
        $arr_data = $this->f_posten();
        $arr_header = array("Inkoms post", "bedrag", "", "Uitgave post", "bedrag");
        $pdf->f_table($arr_header, $arr_data);
        $arr_debt = $this->f_debts();
        $pdf->f_debts($arr_debt);
        $pdf->Output();
    }    

    private function f_posten()  {
        require_once('class/inout.class.php');
        $oInOut = new inout($this->dbConnection);
        $arr_posts = $oInOut->f_posts();

        // Devide posts to income and expense area.
        $arr_inout = array();
        $inCount = 0;
        $outCount = 0;
        foreach($arr_posts as $post)  {

            $add = "";
            if ($post['period'] != 'maand') {
                $add = " (".$post['period'].")";
            }

            if ($post['inkomst'] ==  true)  {
                if (!array_key_exists($inCount, $arr_inout)) {
                    $arr_inout[$inCount] = array("", 0, "", "", 0);
                }
                $arr_inout[$inCount][0] = $post['post'].$add;
                $arr_inout[$inCount][1] = $oInOut->f_getMonth($post['bedrag'], $post['period']);
                $inCount++;
             }  else {
                if (!array_key_exists($outCount, $arr_inout)) {
                    $arr_inout[$outCount] = array("", 0, "", "", 0);
                }
                $arr_inout[$outCount][3] = $post['post'].$add;
                $arr_inout[$outCount][4] = $oInOut->f_getMonth($post['bedrag'], $post['period']);
                $outCount++;
             }
        }
        return $arr_inout;
    }
    
    private function f_debts()  {
        require_once('class/cruddebt.class.php');
        $oDebts = new cruddebt($this->dbConnection);
        $arr_debts = $oDebts->f_allDebts();
        return $arr_debts;
    }

}

?>
