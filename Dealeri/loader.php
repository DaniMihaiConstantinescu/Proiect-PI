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
        <title>Dealeri</title>
        <link rel=\"stylesheet\" href=\"style.css\">
    </head>
    <body>
    
        <div class=\"meniu\">
            <div onclick=\"location.href='../Homepage/loader.php'\">Homepage</div>
            <div class=\"spatiu\"></div>
            <div>Dealeri</div>
            <div>Despre noi</div>
        </div>
    
        <div class=\"tabel\">
    
            <div class=\"head\">
    
                <div class=\"nume\">Nume</div>
                <div class=\"oras\">Oras</div>
                <div class=\"adresa\">Adresa</div>
    
            </div>
    ";


}

function fin(&$txt)
{

    $fin = "</div>

    </div>
    
</body>
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

    function item($nume,$oras,$adresa)
    {

        $row = "<div class=\"item\">

        <div class=\"nume\">". $nume ."</div>
        <div class=\"oras\">". $oras ."</div>
        <div class=\"adresa\">". $adresa ."</div>

    </div>";
        return $row;

    }

    function core(&$txt)
    {

        global $conn;

        $stmt = $conn->prepare("SELECT nume,oras,adresa FROM dealeri");
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $txt .= item($v["nume"],$v["oras"],$v["adresa"]);
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