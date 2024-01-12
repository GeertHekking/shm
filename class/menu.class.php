<?php

class menu {
    
    public function f_menu()  {
    
        echo "    <!-- Fixed navbar -->\n";
        echo "    <nav class=\"navbar navbar-default navbar-fixed-top\">\n";
        echo "      <div class=\"container-fluid\">\n";
        echo "        <div class=\"navbar-header\">\n";
        echo "          <button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#navbar\" aria-expanded=\"false\" aria-controls=\"navbar\">\n";
        echo "            <span class=\"sr-only\">Toggle navigation</span>\n";
        echo "            <span class=\"icon-bar\"></span>\n";
        echo "            <span class=\"icon-bar\"></span>\n";
        echo "            <span class=\"icon-bar\"></span>\n";
        echo "          </button>\n";
        echo "          <a class=\"navbar-brand\" href=\"http://www.shm.nl\">schuldhulp maatje</a>\n";
        echo "        </div>\n";
        echo "        <div id=\"navbar\" class=\"navbar-collapse collapse\">\n";
        echo "          <ul class=\"nav navbar-nav\">\n";
        echo "            <li class=\"active\"><a href=\"start.php?pageid=".$_SESSION['session_page']."\">Home</a></li>\n";
        echo "            <li class=\"dropdown\">\n";
        echo "              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Overzichten <span class=\"caret\"></span></a>\n";
        echo "              <ul class=\"dropdown-menu\">\n";
        
        echo "                <li class=\"dropdown-header\"> Financiële overzichten </li>\n";
        echo "                <li> <a href=\"start.php?function=finovz&pageid=".$_SESSION['session_page']."\"> Financieel overzicht </a> </li>\n";
        echo "                <li> <a href=\"start.php?function=finmndovz&pageid=".$_SESSION['session_page']."\"> Financieel Maand overzicht </a> </li>\n";
        echo "                <li> <a href=\"start.php?function=kostgeldmnt&pageid=".$_SESSION['session_page']."\"> Kostgeld overzicht </a> </li>\n";
        // echo "                <li role=\"separator\" class=\"divider\"></li>\n";
        // echo "                <li class=\"dropdown-header\"> Update </li>\n";
        // echo "                <li> <a href=\"start.php?function=crudposts&pageid=".$_SESSION['session_page']."\"> Posten </a> </li>\n";
        // echo "                <li role=\"separator\" class=\"divider\"></li>\n";
        // echo "                <li> <a href=\"#\"> One more separated link </a> </li>\n";
        // echo "                <li role=\"separator\" class=\"divider\"></li>\n";
        
        echo "              </ul>\n";
        echo "            </li>\n";
        echo "            <li><a href=\"start.php?function=inout&pageid=".$_SESSION['session_page']."\">In/Uit</a></li>\n";
        echo "            <li><a href=\"start.php?function=debts&pageid=".$_SESSION['session_page']."\">Schulden</a></li>\n";
        echo "            <li class=\"dropdown\">\n";
        echo "              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Stam Tabellen <span class=\"caret\"></span></a>\n";
        echo "              <ul class=\"dropdown-menu\">\n";
//        echo "                <li class=\"dropdown-header\"> Update </li>\n";
        echo "                <li> <a href=\"start.php?function=crudbank&pageid=".$_SESSION['session_page']."\"> Bank / Kas </a> </li>\n";
        echo "                <li> <a href=\"start.php?function=crudposts&pageid=".$_SESSION['session_page']."\"> Posten </a> </li>\n";
        echo "                <li> <a href=\"start.php?function=cruddebt&pageid=".$_SESSION['session_page']."\"> Schulden </a> </li>\n";
        if (isset($_SESSION['session_su']) && $_SESSION['session_su'] == 'SuperUser')  {
            echo "                <li role=\"separator\" class=\"divider\"></li>\n";
            echo "                <li class=\"dropdown-header\"> Algemene tabellen </li>\n";
            echo "                <li> <a href=\"start.php?function=cruduser&pageid=".$_SESSION['session_page']."\"> Gebruikers </a> </li>\n";
            echo "                <li> <a href=\"start.php?function=crudperiod&pageid=".$_SESSION['session_page']."\"> Periodes </a> </li>\n";
        }
        
        echo "              </ul>\n";
        echo "            </li>\n";
        echo "            <li class=\"dropdown\">\n";
        echo "              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Rapporten <span class=\"caret\"></span></a>\n";
        echo "              <ul class=\"dropdown-menu\">\n";
//        echo "                <li class=\"dropdown-header\"> Update </li>\n";
        echo "                <li> <a href=\"start.php?function=rap_inout&pageid=".$_SESSION['session_page']."&report=yes\" target=\"_blank\"> Inkomsten / Uitgaven </a> </li>\n";
        echo "              </ul>\n";
        echo "            </li>\n";
        echo "                <li> <a href=\"start.php?function=crudlenen&pageid=".$_SESSION['session_page']."\"> Lenen </a> </li>\n";
        echo "            <li><a href=\"start.php?function=logoff\">Afmelden</a></li>\n";
        echo "          </ul>\n";
        echo "          <ul class=\"nav navbar-nav navbar-right\">\n";
        echo "          </ul>\n";
        echo "        </div><!--/.nav-collapse -->\n";
        echo "      </div>\n";
        echo "    </nav>\n";
    }
    
}

?>