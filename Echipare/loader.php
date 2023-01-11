<?php

session_start();
$_SESSION['id'] = $_POST['id'];

$id = $_POST["id"];
$render = "";
$echipari = getEchipari($id, $render)["id"];

function getEchipari($id, &$render){

    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT echipari,render from masini where idMasina=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $array = json_decode($v["echipari"], true);
            $render = $v["render"];
            
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $array;

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$myfile = fopen("index.html", "w");
$txt = "";
$total = 0;

$conn = "";

function main(&$txt)
{
    global $render;

    $render = "../Resurse/3D Models/" . $render;

    $txt = "<!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Echipare</title>
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
    
            <model-viewer class=\"render\" src = \"". $render ."\" camera-controls > </model-viewer>
            
            <div class=\"echipari\">
    
    ";


}

function fin(&$txt)
{

    $fin = "</div>

    <form method=\"post\" action=\"../Motorizare/loader.php\">
        <button id=\"fin\" style=\"display:none\"  ></button>
        <input value=\"0\" type=\"text\" name=\"id\" id=\"cod\" style=\"display: none;\">
    </form>

    <div class=\"bar\">
        <div class=\"inapoi\" onclick=\"location.href='../Modele/loader.php'\">Inapoi</div>
    </div>


</div>

</body>

<script>

function apasa(but_id){

    $(\"#cod\").attr(\"value\",but_id);
    $(\"#fin\").click();

}

$(document).ready(function(){
    $(\".info\").click(function(){

        $(\"#page-mask\").fadeIn();
        $(\".popup\").fadeIn();

    });

    $(\".close\").click(function(){

        $(\"#page-mask\").fadeOut();
        $(\".popup\").fadeOut();

    });

});


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

    function getMotorizari($motorizari){
        global $conn;
        $rezultat = "";

        foreach ( $motorizari as $k => $e ){

            $sql = "SELECT nume from motorizari where idMotorizare=$e ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
                $rezultat .= $v["nume"] . "<br>";
            }
        }
        return $rezultat;
    }

    function getOptiuni($motorizari){
        global $conn;
        $rezultat = "";

        foreach ( $motorizari as $k => $e ){

            $sql = "SELECT nume from optiuni where idOptiune=$e ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
                $rezultat .= $v["nume"] . "<br>";
            }
        }
        return $rezultat;
    }

    function item($id)
    {

        global $conn;
        $sql = "SELECT nume,dotari,pret from echipari where idEchipare=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $dotari = array();
        $nume = "";
        $pret = 0;
        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $dotari = json_decode($v["dotari"], true);
            $nume = $v["nume"];
            $pret = $v["pret"];
            
        }

        $motorizari = getMotorizari($dotari["motorizari"]);
        $optiuni = getOptiuni($dotari["optiuni"]);



        $row = "<div class=\"item\">
        <div class=\"nume\"><u>". $nume ."</u></div>
        <div class=\"descriere\">
            <b> Motorizari </b> <br>
            " . $motorizari . "        
            <br> <b> Optiuni Incluse </b> <br>
            " . $optiuni . "
        </div>
        <div class=\"pret\" id=\"". $id ."\" onclick=\"apasa(this.id);\">de la ". $pret ."$</div>
    </div>";

        return $row;

    }

    function core(&$txt)
    {
        global $echipari;

        for ($i=0; $i < count($echipari); $i++) {
            $txt .= item($echipari[$i]);
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