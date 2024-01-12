<?PHP

  class bootstrap  {

      public function f_header()  {
        echo "<!-- Font Awesome -->\n";
        echo "<link \n";
        echo "  href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css\"\n";
        echo "    rel=\"stylesheet\" />\n";
        echo "<!-- Google Fonts -->\n";
        echo "<link\n";
        echo "  href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap\"\n";
        echo "    rel=\"stylesheet\" />\n";
        echo "<!-- MDB -->\n";
        echo "<link\n";
        echo "  href=\"https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.0.0/mdb.min.css\"\n";
//        echo "    rel=\"stylesheet\" />\n";
        echo "    <!-- Bootstrap -->\n";
        echo "    <link href=\"css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        echo "    \n";        
        echo "    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->\n";
        echo "    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->\n";
        echo "    <!--[if lt IE 9]>\n";
        echo "      <script src=\"https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js\"></script>\n";
        echo "      <script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>\n";
        echo "    <![endif]-->\n";
      }
      
      public function f_script()  {
        echo "<!-- MDB -->\n";
//        echo "<script\n";
//        echo "  type=\"text/javascript\"\n";
//        echo "  src=\"https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.0.0/mdb.min.js\">\n";
        echo    "<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->\n";
        echo    "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js\"></script>\n";
        echo    "<!-- Include all compiled plugins (below), or include individual files as needed -->\n";
        echo    "<script src=\"js/bootstrap.min.js\"></script>\n";
//        echo "</script>\n";
      }
  }
  
?>