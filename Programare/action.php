<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();
$_SESSION['err'] = 0;

verifyPost();
sentData($_POST['nume'], $_POST['email'], $_POST['dealer'], $_POST['data'], $_POST['ora'], $_SESSION['config']);

header("location:../Homepage/loader.php");
exit();


function verifyPost()
{
    if ($_POST['nume'] == "") {
        $_SESSION['err'] = 5;
        header("location:loader.php");
        exit();
    }

    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['err'] = 1;
        header("location:loader.php");
        exit();
    }

    if (!isset($_POST['dealer'])) {
        $_SESSION['err'] = 4;
        header("location:loader.php");
        exit();
    }

    if ($_POST['data'] == "" || $_POST['ora'] == "") {

        $_SESSION['err'] = 3;
        header("location:loader.php");
        exit();

    }

    if (!verifyDate($_POST['data'], $_POST['ora'])) {

        $_SESSION['err'] = 2;
        header("location:loader.php");
        exit();

    }
}

function verifyDate($data, $ora)
{
    $dealer = $_POST['dealer'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT DATE_FORMAT(Data, '%d-%m-%Y') data, TIME_FORMAT(ora, '%H:%i') ora FROM programari where idDealer=$dealer");
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            if ($v['data'] == $data || $v['ora'] == $ora)
                return false;

        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }


    return true;

}

function sentData($nume, $email, $dealer, $data, $ora, $config)
{

    $to = $email;
    $subject = "Programare intalnire dealer";

    $numeDealer = "";
    $adresa = "";
    getData($dealer, $numeDealer, $adresa);

    $message = "<h1>Detalii programare intalnire cu un dealer auto pentru discutii despre configurare/test-drive.</h1>";
    $message .= "<br><br>";
    $message .= "<p>Nume dealer: " . $numeDealer . "</p>";
    $message .= "<p>Adresa dealer: " . $adresa . "</p>";
    $message .= "<p>Data: " . $data . " ; Ora: " . $ora . "</p>";
    $message .= "<p>Configuratie: " . $config . "</p>";
    $message .= "<br><br><br><br>";

    $header = "From:configurator@auto.com \r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-type: text/html\r\n";

    updateData($nume, $dealer, $data, $ora, $config);

    sendMail($to, $subject, $message, $header);

}

function sendMail($to, $subject, $message, $header)
{


    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP(); //Send using SMTP
        $mail->Host = 'smtp.yahoo.com'; //Set the SMTP server to send through
        $mail->SMTPAuth = true; //Enable SMTP authentication
        $mail->Username = 'carconfigdealer@yahoo.com'; //SMTP username
        $mail->Password = 'Dani20$dani'; //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
        $mail->Port = 465; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('from@example.com', 'Car configurator');
        $mail->addAddress($to); //Add a recipient

        //Content
        $mail->isHTML(true); //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

}

function getData($id, &$nume, &$adresa)
{

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "proiect pi";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT nume,adresa FROM dealeri where idDealer=$id");
        $stmt->execute();

        foreach (new RecursiveArrayIterator($stmt->fetchAll()) as $k => $v) {

            $nume = $v['nume'];
            $adresa = $v['adresa'];
            return;
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

function updateData($nume, $dealer, $data, $ora, $config)
{

    $dd = date_create($data);
    $dd = date_format($dd, "Y/m/d");


    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "proiect pi";

    try {

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("INSERT INTO programari (idDealer,NumeClient,Data,Ora,Configuratie) VALUES (?,?,?,?,?) ");

        $stmt->execute([$dealer, $nume, $dd, $ora, $config]);

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}


?>