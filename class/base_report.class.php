    <?php

require_once('../fpdf/fpdf.php');

class base_report extends FPDF {
    
    private $mTitle;
    
    public function f_setTitle($ip_Title)  {
        $this->mTitle = $ip_Title;
    }
    
    public function f_getTitle()  {
        return $this->mTitle;
    }

    // Page header
    function Header()  {
        // Logo
        $this->Image('img/logo.png',10,6,30);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $vTitle = $this->f_getTitle();
        $vTitle = 'Inkomsten / Uitgaven overzicht';
        $this->Cell(80, 10, $vTitle, 1, 0, 'C');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Table
    public function f_table($header, $data)  {
        // Column widths
        $w = array(57, 25, 6, 57, 25);
        // Header
        for($i=0; $i<count($header); $i++)  {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        }
        $this->Ln();
        // Data
        $sum_in = 0;
        $sum_out = 0;
        foreach($data as $row)  {
            if ($row[0] == "" ) {
                // Blank cells
                $this->Cell($w[0], 6, "", 'LR');
                $this->Cell($w[1], 6, "", 'LR', 0, 'R');
            }  else {
                $this->Cell($w[0], 6, $row[0], 'LR');
                $this->Cell($w[1], 6, number_format($row[1], 2, ",", "."), 'LR', 0, 'R');
                $sum_in += $row[1];
            }
            $this->Cell($w[2], 6, '', 'LR');
            if ($row[3] == "" ) {
                // Blank cells
                $this->Cell($w[3], 6, "", 'LR');
                $this->Cell($w[4], 6, "", 'LR', 0, 'R');
            }  else {
                $this->Cell($w[3], 6, $row[3], 'LR');
                $this->Cell($w[4], 6, number_format($row[4], 2, ",", "."), 'LR', 0, 'R');
                $sum_out += $row[4];
            }
            $this->Ln();
        }
        // Closing line
        $this->Cell($w[0], 7, "Totaal", 1, 0, 'L');
        $this->Cell($w[1], 7, number_format($sum_in, 2, ",", "."), 1, 0, 'R');
        $this->Cell($w[2], 7, "", 1, 0, 'C');
        $this->Cell($w[3], 7, "Totaal", 1, 0, 'L');
        $this->Cell($w[4], 7, number_format($sum_out, 2, ",", "."), 1, 0, 'R');
        
        $this->Ln();
        $this->Ln();
        // Summary
        $this->Cell(50, 6, "Totaal Inkomsten", 'LT', 0, 'L');
        $this->Cell(20, 6, number_format($sum_in, 2, ",", "."), 'TR', 0, 'R');
        $this->Ln();
        $this->Cell(50, 6, "Totaal Uitgaven", 'L', 0, 'L');
        $this->Cell(20, 6, number_format($sum_out, 2, ",", "."), 'BR', 0, 'R');
        $this->Ln();
        $this->Cell(50, 6, "Verschil", 'LB', 0, 'L');
        $this->Cell(20, 6, number_format(($sum_in - $sum_out), 2, ",", "."), 'BR', 0, 'R');
        $this->Ln();
        
    }

    public function f_debts($arr_debts) {
        $this->Ln();
        $this->Cell(150, 6, "Schulden overzicht", 'B', 1);
        $this->Ln(12);
        $vTotal = 0;
        // debt, dossier, amount, monthly, restamount, nextdate
        // Arial italic 8
        $this->SetFont('Arial','I',10);
        foreach($arr_debts as $debt)  {
            // Name
            $this->Cell(50, 6, $debt['debt'], 0, 0);
            // Description
            $this->Cell(50, 6, $debt['dossier'], 0, 0);
            // Monthly amount
            $this->Cell(30, 6, number_format($debt['monthly'], 2, ",", "."), 0, 0, 'R');
            // amount
            $this->Cell(30, 6, number_format($debt['amount'], 2, ",", "."), 0, 0, 'R');
            $vTotal += $debt['monthly'];
            $this->Ln();
        }
        $this->Cell(130, 6, number_format($vTotal, 2, ",", "."), 'T', 0, 'R');
    }    
}

?>