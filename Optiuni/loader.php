<?php

session_start();

$_SESSION['culoare'] = $_POST['id'];

$id = $_SESSION["id"];
$render = getRender($_SESSION['culoare']);
$incluse = getIncluse($_SESSION['echipare']);
$optiuni = getOptiuni();

$promOpt = getProm();

$total = getTotal($_SESSION['echipare'], $_SESSION['motorizare'], $_SESSION['culoare']);

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

        if ( array_key_exists("optiuni" , $array[$i]) ){

            for ($j=0; $j < count($array[$i]['optiuni']['id']); $j++) { 
                array_push($rez, array( 'id' => $array[$i]['optiuni']['id'][$j], 'procent' => $array[$i]['optiuni']["procent"][$j]) );
            }
        }
        
    }

    return $rez;

}
function getRender($id){

    $rezultat = "../Resurse/3D Models/";
    
    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT path from culori where idCuloare=$id ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $rezultat .= $v["path"];   
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $rezultat;


}
function getOptiuni(){

    $servername = "localhost";
    $dbname = "proiect pi";

    $array = array();

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT idOptiune,nume,pret from optiuni";
        $stmt = $conn->prepare($sql);
        $stmt->execute();


        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $opt = array();
            array_push($opt, $v['idOptiune']);
            array_push($opt, $v['nume']);
            array_push($opt, $v['pret']);

            array_push($array, $opt);
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $array;

}
function getIncluse($id){

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
            $array = $array['optiuni'];
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
    return $array;

}

function getTotal($echipare, $motorizare, $culoare){

    $idd = $_SESSION['id'];
    $defColor = 0;

    $pEchip = 0;
    $pMotor = 0;
    $pCuloare = 0;


    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //gasire defColor
        $sql = "SELECT DefaultColor from masini where idMasina=$idd ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $defColor = $v['DefaultColor'];
        }

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
        <title>Optiuni</title>
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
    
            <div class=\"grup\">
                <model-viewer class=\"render\" src = \"". $render ."\" camera-controls > </model-viewer>
    
                <div class=\"echipari\">";


}

function fin(&$txt)
{

    global $total;
    global $optiuni;
    global $incluse;

    $inc = " [ ". implode(",",$incluse) ." ] ";

    $opt = "[";
    $itm = "";

    for ($i=0; $i < count($optiuni)-1; $i++) {

        $itm = "[";
        $itm .= $optiuni[$i][0] . ",";
        $itm .= "\"" . $optiuni[$i][1] . "\",";
        $itm .= $optiuni[$i][2] . "]";

        $opt .= $itm . ",";

    }

    $i = count($optiuni) - 1;
    $itm = "[";
    $itm .= $optiuni[$i][0] . ",";
    $itm .= "\"" . $optiuni[$i][1] . "\",";
    $itm .= $optiuni[$i][2] . "]";

    $opt .= $itm . "]";



    $fin = "</div>
    </div>


    <div class=\"bar\">
    <form id=\"fInapoi\" action=\"../Culoare/loader.php\" method=\"post\">
        <div class=\"inapoi\">Inapoi</div>
        <input style=\"display:none\" type=\"text\" name=\"id\" value=\" " . $_SESSION['motorizare'] . " \">
    </form>
        <div id=\"total\" style=\"font-size: 2em;\">Total: ". $total ."$</div>
        <div class=\"inainte\">Inainte</div>
    </div>


    <form method=\"post\" action=\"../Sumar/loader.php\">
        <button id=\"fin\" style=\"display:none\"  ></button>
        <input value=\"". $inc ."\" type=\"text\" name=\"id\" id=\"cod\" style=\"display: none;\">
    </form>


</div>

</body>

<script>

let alese = ". $inc .";
let optiuni = ". $opt .";

let total = ". intval($total) .";

$(document).ready(function(){

    let val = 0;
    // adauga/scade cand se apasa pe checkbox 
    $(\".check\").click(function(){

        id = parseInt($(this).val());
        
        
        if (! $(this).is(':checked')){
            for ( j=0 ; j<optiuni.length ; j++ ){
                if ( optiuni[j][0] == id-1 ){
                    alese = alese.filter(e => e !== id)
                    val = optiuni[j][2] * (-1);
                }
            }
        }
        else{
            for ( j=0 ; j<optiuni.length ; j++ ){
                
                if ( optiuni[j][0] == id-1 ){
                    alese.push(id);
                    val = optiuni[j][2];
                }
            }
        }
        
        total += val;   

        $(\"#cod\").attr(\"value\",alese);
        
        $(\"#total\").text(\"Total: \" + total + \"$\");

    });

    $(\".inainte\").click(function(){

        $(\"#fin\").click();

    });

    $(\".inapoi\").click(function () {

        $(\"#fInapoi\").submit();

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


    function item($optiune)
    {

        global $incluse;
        global $promOpt;
        
        if ( in_array($optiune[0],$incluse) ){

            $row = "<div class=\"item\">
            <input style=\"display: none;\" value=\"". $optiune[0] ."\" class=\"check\" type=\"checkbox\">
            <label class=\"nume\" for=\"checkbox\">". $optiune[1] ."</label>   
            </input>                 
            <div class=\"pret\">Inclusa</div>
        </div>";

        }
        else{

            $pret = intval($optiune[2]);

            for ($i=0; $i < count($promOpt); $i++) {

                if ( $promOpt[$i]['id'] == $optiune[0] ){

                    $pret = $pret * (100 - $promOpt[$i]['procent']) / 100;
                    
                }
            }

            $row = "<div class=\"item\">
            <input value=\"". $optiune[0] ."\" class=\"check\" type=\"checkbox\">
            <label class=\"nume\" for=\"checkbox\">". $optiune[1] ."</label>   
            </input>                 
            <div class=\"pret\">". "+".$pret ."$"."</div>
        </div>";
        }

        return $row;

    }

    function core(&$txt)
    {
        global $optiuni;

        for ($i=0; $i < count($optiuni); $i++) {
            $txt .= item($optiuni[$i]);
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