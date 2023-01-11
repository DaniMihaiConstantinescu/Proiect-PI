<?php

session_start();

$_SESSION['optiuni'] = $_POST['id'];

$id = $_SESSION["id"];
$nume = "";
$render = getRender($_SESSION['culoare']);
$incluse = getIncluse($_SESSION['echipare']);
$optiuni = explode(",", $_POST['id']);


$detalii = array();

$total = getTotal($_SESSION['echipare'], $_SESSION['motorizare'], $_SESSION['culoare'], $nume, $optiuni, $incluse, $detalii);

$url = generateURL($detalii);
$_SESSION['config'] = $url;


function generateURL($data)
{
    $encoded = array();

    array_push($encoded, "id" . '=' . $_SESSION['id'] );
    array_push($encoded, "echipare" . '=' . $_SESSION['echipare'] );
    array_push($encoded, "motorizare" . '=' . $_SESSION['motorizare'] );
    array_push($encoded, "culoare" . '=' . $_SESSION['culoare'] );
    array_push($encoded, "optiuni" . '=' . $_SESSION['optiuni'] );

    return "http://localhost/Sumar/reload.php" . '?' . join('&', $encoded);
}
function extrageOptiuni($text)
{

    $rezultat = array();

    $text = substr_replace($text, "", -1);
    $text = substr_replace($text, "", -1);
    $text = substr($text, 1);
    $text = substr($text, 1);

    $rezultat = explode(",", $text);
    return $rezultat;

}
function getRender($id)
{

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
function getIncluse($id)
{

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

function getOptiuni($motorizari)
{

    $rezultat = "";
    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach ($motorizari as $k => $e) {

            $sql = "SELECT nume from optiuni where idOptiune=$e ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
                $rezultat .= $v["nume"] . "<br>";
            }
        }
        return $rezultat;


    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();

    }
}

function getTotal($echipare, $motorizare, $culoare, &$nume, $optiuni, $incluse, &$detalii)
{

    $idd = $_SESSION['id'];
    $defColor = 0;

    $pEchip = 0;
    $pMotor = 0;
    $pCuloare = 0;
    $pOptiuni = 0;

    $dEchip = array();
    $dMotor = array();
    $dCuloare = array();



    $servername = "localhost";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //gasire defColor
        $sql = "SELECT nume,DefaultColor from masini where idMasina=$idd ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $defColor = $v['DefaultColor'];
            $nume = $v['nume'];
        }

        //calculare pret echipare
        $sql = "SELECT nume,dotari,pret from echipari where idEchipare=$echipare ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $pEchip = $v['pret'];

            $dotari = json_decode($v["dotari"], true);
            $dEchip = array(getOptiuni($dotari['optiuni']), $v['nume'], $pEchip);
        }

        //calculare pret motorizare
        $sql = "SELECT nume,capacitate,hp,cuplu,consum,cutie,combustibil,pret from motorizari where idMotorizare=$motorizare ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $pMotor = $v['pret'];

            $descriere = "
            <b> HP </b> <br>
            " . $v['hp'] . "        
            <br> <b> Cuplu </b> <br>
            " . $v['cuplu'] . "        
            <br> <b> Consum </b> <br>
            " . $v['consum'] . "        
            <br> <b> Transmisie </b> <br>
            " . $v['cutie'] . "        
            <br> <b> Capacitate </b> <br>
            " . $v['capacitate'] . "        
            <br> <b> Combustibil </b> <br>
            " . $v['combustibil'] . "<br>";

            $dMotor = array($descriere, $v['nume'], $pMotor);

        }

        if ($culoare != $defColor) {
            //calculare pret culoare
            $sql = "SELECT nume,poza,pret from culori where idCuloare=$culoare ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
                $pCuloare = $v['pret'];

                $dCuloare = array($v['poza'], $v['nume'], $pCuloare);
            }
        }

        $detalii = array($dEchip, $dMotor, $dCuloare);

        $sql = "SELECT idOptiune,pret from optiuni";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            if (in_array($v['idOptiune'], $optiuni)) {
                if (!in_array($v['idOptiune'], $incluse)) {
                    $pOptiuni += $v['pret'];
                }
            }
        }


    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;

    return ($pEchip + $pMotor + $pCuloare + $pOptiuni);

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$myfile = fopen("index.html", "w");
$txt = "";
$conn = "";

function main(&$txt)
{
    global $render, $nume;
    global $detalii;

    $txt = "<!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Sumar</title>
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
    
            <div class=\"denumire\"><u>" . $nume . "</u></div>
    
            <model-viewer class=\"render\" src=\"" . $render . "\" camera-controls></model-viewer>
    
            <div class=\"detalii\">
    
                <div class=\"denumire\" style=\"font-size: 1.6em;\"><u>Detalii Configurare</u></div>
    
                <div class=\"item\">
                    <div class=\"info1\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon icon-tabler icon-tabler-info-circle\" width=\"44\" height=\"44\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"#000000\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\">
                            <path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/>
                            <circle cx=\"12\" cy=\"12\" r=\"9\" />
                            <line x1=\"12\" y1=\"8\" x2=\"12.01\" y2=\"8\" />
                            <polyline points=\"11 12 12 12 12 16 13 16\" />
                          </svg>
                    </div>
                    <div class=\"nume\">Echipare: " . $detalii[0][1] . " </div>
                    <div class=\"pret\">" . (($detalii[0][2] == 0) ? "Standard" : ("+" . $detalii[0][2] . "$")) . "</div>
                </div>
    
                <div class=\"item\">
                    <div class=\"info2\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon icon-tabler icon-tabler-info-circle\" width=\"44\" height=\"44\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"#000000\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\">
                            <path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/>
                            <circle cx=\"12\" cy=\"12\" r=\"9\" />
                            <line x1=\"12\" y1=\"8\" x2=\"12.01\" y2=\"8\" />
                            <polyline points=\"11 12 12 12 12 16 13 16\" />
                          </svg>
                    </div>
                    <div class=\"nume\">Motorizare: " . $detalii[1][1] . "</div>
                    <div class=\"pret\">" . (($detalii[1][2] == 0) ? "Standard" : ("+" . $detalii[1][2] . "$")) . "</div>
                </div>
                <div class=\"item\">
                    <div class=\"imag\" style=\"background-color: " . $detalii[2][0] . ";\"> </div>
                    <div class=\"nume\">Culoare: " . $detalii[2][1] . "</div>
                    <div class=\"pret\">" . (($detalii[2][2] == 0) ? "Standard" : ("+" . $detalii[2][2] . "$")) . "</div>
                </div>
            </div>
    
            <div class=\"detalii\">
                <div class=\"denumire\" style=\"font-size: 1.6em;\"><u>Optiuni Alese</u></div>
    ";


}

function fin(&$txt)
{

    global $detalii;
    global $total;
    global $url;

    $fin = "</div>

    <div class=\"total\"> Total: ". $total ."$ </div>

    <div class=\"link\">

            Link Configuratie:
            <input id=\"url\" type=\"text\" value=\"". $url ."\" readonly>
            <div id=\"copy\">Copiati</div>

        </div>

    <div class=\"programare\" onclick=\"location.href='../Programare/loader.php'\">Programati o intalnire cu un dealer</div>

    
    <div id=\"page-mask\"></div>
    <div class=\"popup1 popup\">
        <svg class=\"close1 close\" xmlns=\"http://www.w3.org/2000/svg\" class=\"icon icon-tabler icon-tabler-square-x\"
            width=\"44\" height=\"44\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"#000000\" fill=\"none\"
            stroke-linecap=\"round\" stroke-linejoin=\"round\">
            <path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" />
            <rect x=\"4\" y=\"4\" width=\"16\" height=\"16\" rx=\"2\" />
            <path d=\"M10 10l4 4m0 -4l-4 4\" />
        </svg>
        <br><br><br>
        <u>Dotari Incluse</u>
        <br><br><br>
        ". $detalii[0][0] ."
    </div>
    <div class=\"popup2 popup\">
        <svg class=\"close2 close\" xmlns=\"http://www.w3.org/2000/svg\" class=\"icon icon-tabler icon-tabler-square-x\"
            width=\"44\" height=\"44\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"#000000\" fill=\"none\"
            stroke-linecap=\"round\" stroke-linejoin=\"round\">
            <path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" />
            <rect x=\"4\" y=\"4\" width=\"16\" height=\"16\" rx=\"2\" />
            <path d=\"M10 10l4 4m0 -4l-4 4\" />
        </svg>
        <br><br><br>        
        <u>Detalii tehnice</u>
        <br><br><br>
        ". $detalii[1][0] ."


</body>

<script>

$(document).ready(function () {
    $(\".info1\").click(function () {

        $(\"#page-mask\").fadeIn();
        $(\".popup1\").fadeIn();

    });
    $(\".close1\").click(function () {

        $(\"#page-mask\").fadeOut();
        $(\".popup1\").fadeOut();

    });


    $(\".info2\").click(function () {

        $(\"#page-mask\").fadeIn();
        $(\".popup2\").fadeIn();

    });
    $(\".close2\").click(function () {

        $(\"#page-mask\").fadeOut();
        $(\".popup2\").fadeOut();

    });

    $(\"#copy\").click(function () {

        navigator.clipboard.writeText($(\"#url\").val());

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

        global $conn;
        global $incluse;

        $sql = "SELECT nume,pret from optiuni where idOptiune=$optiune ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            if (in_array($optiune, $incluse)) {

                $row = "<div class=\"item\">
                <div class=\"nume\">". $v['nume'] ." </div>
                <div class=\"pret\">Inclusa</div>
            </div>";
    
            } else {
                $row = "<div class=\"item\">
                <div class=\"nume\">". $v['nume'] ."</div>
                <div class=\"pret\">+". $v['pret'] ."$</div>
            </div>";
            }
            
        }

        return $row;

    }

    function core(&$txt)
    {
        global $optiuni;

        for ($i = 0; $i < count($optiuni); $i++) {
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