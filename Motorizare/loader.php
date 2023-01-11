<?php

session_start();

$_SESSION['echipare'] = $_POST['id'];

$id = $_SESSION["id"];
$echipare = $_POST['id'];
$render = getRender($id);
$motorizari = getMotorizari($echipare);

function getRender($id){

    $rezultat = "../Resurse/3D Models/";
    
    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT render from masini where idMasina=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $rezultat .= $v["render"];
            
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $rezultat;


}
function getMotorizari($id){

    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT dotari from echipari where idEchipare=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $array = json_decode($v["dotari"], true);
            $array = $array["motorizari"];
            
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

    $txt = "<!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Motorizare</title>
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

    <div class=\"bar\">
        <form id=\"fInapoi\" action=\"../Echipare/loader.php\" method=\"post\">
            <div class=\"inapoi\">Inapoi</div>
            <input style=\"display:none\" type=\"text\" name=\"id\" value=\" " . $_SESSION['id'] . " \">
        </form>
    </div>


</div>

<form method=\"post\" action=\"../Culoare/loader.php\">
    <button id=\"fin\" style=\"display:none\"  ></button>
    <input value=\"0\" type=\"text\" name=\"id\" id=\"cod\" style=\"display: none;\">
</form>

</body>

<script>

    function apasa(but_id){

        $(\"#cod\").attr(\"value\",but_id);
        $(\"#fin\").click();

    }

    $(\".inapoi\").click(function () {

        $(\"#fInapoi\").submit();

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


    function item($id)
    {

        global $conn;
        $sql = "SELECT nume,capacitate,hp,cuplu,consum,cutie,combustibil,pret from motorizari where idMotorizare=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $descriere = "";
        $nume = "";
        $pret = 0;
        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $nume = $v["nume"];
            $pret = $v["pret"];

            
            $descriere = "
            <b> HP </b> <br>
            " . $v['hp'] . "        
            <br> <b> Cuplu </b> <br>
            " . $v['cuplu']  . "        
            <br> <b> Consum </b> <br>
            " . $v['consum'] . "        
            <br> <b> Transmisie </b> <br>
            " . $v['cutie'] . "        
            <br> <b> Capacitate </b> <br>
            " . $v['capacitate'] . "        
            <br> <b> Combustibil </b> <br>
            " . $v['combustibil'] . "<br>" ;
            
        }



        $row = "<div class=\"item\">
        <div class=\"nume\"><u>". $nume ."</u></div>
        <div class=\"descriere\">
            ". $descriere ."
        </div>
        <div class=\"pret\" id=\"". $id ."\" onclick=\"apasa(this.id);\">+". $pret ."$</div>
    </div>";

        return $row;

    }

    function core(&$txt)
    {
        global $motorizari;

        for ($i=0; $i < count($motorizari); $i++) {
            $txt .= item($motorizari[$i]);
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