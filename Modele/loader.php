<?php

session_start();

$myfile = fopen("index.html", "w");
$txt = "";
$total = 0;

$conn = "";

function main(&$txt)
{
    $txt = "<!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Modele</title>
        <link rel=\"stylesheet\" href=\"style.css\">
        <script type=\"module\" src=\"https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js\"></script>
        <script src=\"https://code.jquery.com/jquery-3.6.1.min.js\" integrity=\"sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=\" crossorigin=\"anonymous\"></script>
    
    </head>
    <body>
    
        <div class=\"main\">
    
            <div class=\"meniu\">
                <div></div>
                <div onclick=\"location.href='../Homepage/loader.php'\">Homepage</div>
                <div></div>
                <div>Dealeri</div>
                <div>Despre noi</div>
                <div></div>
            </div>
    
            <div></div>
    
            <div class=\"modele\">
    ";


}

function fin(&$txt)
{

    $fin = "</div>

    <div></div>

</div>

<form method=\"post\" action=\"../Echipare/loader.php\">
    <button id=\"fin\" style=\"display:none\"  ></button>
    <input value=\"0\" type=\"text\" name=\"id\" id=\"cod\" style=\"display: none;\">
</form>

</body>

<script>

    function apasa(but_id){

        $(\"#cod\").attr(\"value\",but_id);
        $(\"#fin\").click();

    }

    /*
    $(\".alege\").click(function () {
        $('button').click();
    });
    */

</script>

</html>";

    $txt .= $fin;

}


//conectare la database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proiect pi";

try {

    global $conn;

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function item($id,$nume,$render)
    {
        $render = "../Resurse/3D Models/" . $render;

        $row = "<div class=\"item\">

        <model-viewer class=\"imagine\" src = \"".$render."\" camera-controls > </model-viewer>

        <div class=\"descriere\">
            <span>".$nume."</span>
        </div> 
        <div class=\"alege\" id=\"". $id ."\" onclick=\"apasa(this.id);\" >Configurati</div>

        </div>";
        return $row;

    }

    function core(&$txt)
    {

        global $conn;

        $stmt = $conn->prepare("SELECT idMasina,nume,render FROM masini");
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $txt .= item($v["idMasina"],$v["nume"],$v["render"]);
        }

    }




} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}




main($txt);

core($txt);
$conn = null;

fin($txt);

fwrite($myfile, $txt);
fclose($myfile);

header("location:index.html");
exit();

?>