<?php
include "utils/connection.php";        

function getNextLoc($conn, $id){
    $sql = $conn->prepare("SELECT Longitude, Latitude FROM location WHERE ObjectiveID = ?;");
    $sql->bind_param("i", $id);
    $sql->execute();
    $row = $sql->get_result()->fetch_assoc();
    $sql->close();
    $conn->close();
    echo json_encode(array("coords"=>array("latitude"=>$row["Latitude"], "longitude"=>$row["Longitude"])));
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
    getNextLoc(opencon(), $_GET["ID"]);
}

?>