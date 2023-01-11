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
        <title>Programare</title>
        <link rel=\"stylesheet\" href=\"style.css\">
        <script src=\"https://code.jquery.com/jquery-3.6.1.min.js\"
            integrity=\"sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=\" crossorigin=\"anonymous\"></script>
    
        <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css\">
        <script src=\"https://cdn.jsdelivr.net/npm/flatpickr\"></script>
    
    </head>
    
    <body>
    
        <body>
    
    
            <div class=\"meniu\">
                <div onclick=\"location.href='../Homepage/loader.php'\">Homepage</div>
                <div class=\"spatiu\"></div>
                <div>Dealeri</div>
                <div>Despre noi</div>
            </div>
    
            <div class=\"main\">
                <form method=\"post\" action=\"action.php\">
    
                <div class=\"date\">
                <div style=\"float: left;\">
                    <label for=\"nume\">Nume:</label>
                    <input name=\"nume\" type=\"text\" id=\"nume\" placeholder=\"\">
                </div>

                <div style=\"float: left;\">
                    <label for=\"email\">Email:</label>
                    <input name=\"email\" type=\"text\" id=\"email\" placeholder=\"\">
                </div>

            </div>
    
                    <label for=\"dealer\">Dealer:</label>
                    <select name=\"dealer\">
                        <option selected disabled>Alegeti un dealer</option>";


}

function fin(&$txt)
{

    $fin = "</select>


    <div class=\"date\">
                    <div style=\"float: left;\">
                        <label id=\"ldata\" for=\"data\">Data:</label>
                        <input name=\"data\" type=\"date\" id=\"data\" placeholder=\"Selectati data\">
                    </div>

                    <div style=\"float: left;\">
                        <label for=\"ora\">Ora:</label>
                        <input name=\"ora\" type=\"time\" id=\"ora\" placeholder=\"Selectati ora\">
                    </div>

                </div>

    <input type=\"submit\" value=\"Trimiteti\" class=\"buton\">

</form>
</div>

</body>

<script>

        let error = ". $_SESSION["err"] .";
        
        $(document).ready(function () {

            if (error == 1) {
                alert(\"Email invalid\");
            }
            if (error == 4) {
                alert(\"Nu a fost selectat nici un dealer\");
            }
            if (error == 3) {
                alert(\"Data sau ora nu a fost selectata\");
            }
            if (error == 2) {
                alert(\"Exista deja o programare pentru data si ora aleasa \\nVa rugam sa alegeti alta data sau ora \");
            }
            if (error == 5) {
                alert(\"Nu ati completat numele\");
            }
            

        });

        configData = {

            dateFormat: \"d-m-Y\",
            minDate: \"today\",

            \"locale\": {
                \"firstDayOfWeek\": 1
            },

            \"disable\": [
                function (date) {
                    return (date.getDay() === 6 || date.getDay() === 0);

                }
            ],

        }

        configOra = {
            enableTime: true,
            noCalendar: true,
            dateFormat: \"H:i\",
            time_24hr: true,
            minTime: \"09:00\",
            maxTime: \"16:00\",
        }

        flatpickr(\"#data\", configData);
        flatpickr(\"#ora\", configOra)

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

    function item($id,$nume,$oras)
    {

        $loc = $nume . "," . $oras;
        $row = "<option value=\"". $id ."\">".$loc."</option>";
        return $row;

    }

    function core(&$txt)
    {

        global $conn;

        $stmt = $conn->prepare("SELECT idDealer,nume,oras FROM dealeri order by nume");
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {
            $txt .= item($v['idDealer'], $v["nume"],$v["oras"]);
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