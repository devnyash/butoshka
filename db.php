<?php 
$hostname = "185.180.230.121";
$name = "margo";
$password = "Nbf%Y.Z6wBudJ";
$dbname = "reg";

$conn = mysqli_connect($hostname, $name, $password, $dbname);

if(!$conn){
    die("Ошибка соединения".mysqli_connect_error());
}
?>