<?php

session_start();

$_SESSION['err'] = 0;

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
        <script src=\"https://code.jquery.com/jquery-3.6.1.min.js\" integrity=\"sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=\" crossorigin=\"anonymous\"></script>
        <title>Homepage</title>
        <link rel=\"stylesheet\" href=\"style.css\">
    
    </head>
    <body>
    
        <div class=\"main\">
    
            <div class=\"meniu\">
                <div></div>
                <div>Homepage</div>
                <div></div>
                <div onclick=\"location.href='../Dealeri/loader.php'\">Dealeri</div>
                <div>Despre noi</div>
                <div></div>
            </div>
    
            <div class=\"slider\">
                <img class=\"promotie\" src=";


    global $conn;

    $stmt = $conn->prepare("SELECT poza FROM promotii LIMIT 1");
    $stmt->execute();


    $l = "";

    foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
        $l .= item($v["poza"]);
    }

    $txt .= $l;

    $txt .= "alt=\"\">
            </div>
    
            <div></div>
    
            <form action=\"../Modele/loader.php\">
                <div class=\"config\" >
                    <div class=\"buton\">Configureaza</div>
                    <button style=\"display:none;\" ></button>
                </div>
            </form>
    
    
        </div>
        
    </body>
    
    <script>
    
        let image_array = [";


}

function fin(&$txt)
{

    $fin = "];

    let pic = $('.slider');

    let i = 0;
    setInterval(function() {

        i = (i + 1) % image_array.length;
        $(document).ready(function(){
            pic.fadeOut(1000, () => {
                $('.promotie').attr(\"src\", image_array[i]);
                pic.fadeIn(1000);
            });
        });

    }, 10000);
    
    $(document).ready(function(){
        $(\".buton\").click(function(){
            $('button').click();
        })
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

    function item($poza)
    {

        $row = "'../Resurse/Promotii/" . $poza . "'" . ",\n";
        return $row;

    }

    function core(&$txt)
    {

        global $conn;

        $stmt = $conn->prepare("SELECT poza FROM promotii");
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $txt .= item($v["poza"]);
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