<?php

session_start();

$_SESSION['motorizare'] = $_POST['id'];

$id = $_SESSION["id"];
$defColor = 0;
$render = getRender($id,$defColor);
$culori = getCulori($id);
$currentColor = $defColor;

$promOpt = getProm();

$total = getTotal($_SESSION['echipare'], $_SESSION['motorizare'], $currentColor);

function getProm(){

    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT criterii from promotii";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $array = array();
        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $aux = json_decode($v["criterii"], true);
            array_push($array, $aux);
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }
    $conn = null;

    $rez = array();
    for ($i=0; $i < count($array) ; $i++) {

        if ( array_key_exists("culori" , $array[$i]) ){

            for ($j=0; $j < count($array[$i]['culori']['id']); $j++) { 
                array_push($rez, array( 'id' => $array[$i]['culori']['id'][$j], 'procent' => $array[$i]['culori']["procent"][$j]) );
            }
        }
        
    }

    return $rez;

}
function getRender($id,&$defColor){

    $rezultat = "../Resurse/3D Models/";
    
    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT render,DefaultColor from masini where idMasina=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $defColor = $v['DefaultColor'];
            $rezultat .= $v["render"];
            
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $rezultat;


}
function getCulori($id){

    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT culori from masini where idMasina=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $array = json_decode($v["culori"], true);
            $array = $array['id'];
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $array;

}

function getTotal($echipare, $motorizare, $culoare){

    global $defColor;

    $pEchip = 0;
    $pMotor = 0;
    $pCuloare = 0;


    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //calculare pret echipare
        $sql = "SELECT pret from echipari where idEchipare=$echipare ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $pEchip = $v['pret'];
        }

        //calculare pret motorizare
        $sql = "SELECT pret from motorizari where idMotorizare=$motorizare ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $pMotor = $v['pret'];
        }

        if( $culoare != $defColor ){
            //calculare pret culoare
            $sql = "SELECT pret from culori where idCuloare=$culoare ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
                $pCuloare = $v['pret'];
            }
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;

    return ($pEchip + $pMotor + $pCuloare);

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$myfile = fopen("index.html", "w");
$txt = "";

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
        <title>Culoare</title>
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

    global $total;
    global $currentColor;

    $fin = "</div>

    <div class=\"bar\">
            <form id=\"fInapoi\" action=\"../Motorizare/loader.php\" method=\"post\">
                <div class=\"inapoi\">Inapoi</div>
                <input style=\"display:none\" type=\"text\" name=\"id\" value=\" " . $_SESSION['echipare'] . " \">
            </form>
            <div style=\"font-size: 2em;\">Total: ". $total ."$</div>
            <div class=\"inainte\" onclick=\"trimite(this.id);\">Inainte</div>
        </div>


</div>

<form method=\"post\" action=\"../Optiuni/loader.php\">
    <button id=\"fin\" style=\"display:none\"  ></button>
    <input value=\"". $currentColor ."\" type=\"text\" name=\"id\" id=\"cod\" style=\"display: none;\">
</form>

<form method=\"post\" action=\"../Culoare/reload.php\">
        <button id=\"rel\" style=\"display:none\"  ></button>
        <input value=\"0\" type=\"text\" name=\"id\" id=\"culoare\" style=\"display: none;\">
</form>

</body>

<script>

    function apasa(but_id){

        $(\"#culoare\").attr(\"value\",but_id);
        $(\"#rel\").click();

    }

    function trimite(){

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

        global $conn,$defColor;
        global $promOpt;
        $sql = "SELECT nume,poza,pret from culori where idCuloare=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $pret = 0;
        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $nume = $v["nume"];
            $pret = $v["pret"];
            $poza = $v["poza"];
            
        }

        if($id == $defColor){
            $pret = "Inclus";
        }
        else{

            $p = intval($pret);

            for ($i=0; $i < count($promOpt); $i++) {

                if ( $promOpt[$i]['id'] == $id ){

                    $p = $p * (100 - $promOpt[$i]['procent']) / 100;
                }
            }

            $pret = "+" . $p . "$";

        }

        $row = "<div class=\"item\" id=\"". $id ."\" onclick=\"apasa(this.id);\">
        <div class=\"imag\" style=\"background-color: ". $poza .";\"> </div>
        <div class=\"nume\">". $nume ."</div>
        <div class=\"pret\">". $pret . "</div>
    </div>";

        return $row;

    }

    function core(&$txt)
    {
        global $culori;

        for ($i=0; $i < count($culori); $i++) {
            $txt .= item($culori[$i]);
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