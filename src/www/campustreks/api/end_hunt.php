<?php
/**
 * Script for ending a hunt and updating highscore in the database
 * @author Jakub Kwak
 */
include "../utils/connection.php";

session_start();

if (isset($_SESSION['username']) && isset($_SESSION['hostGameID'])) {
    //get Hunt ID
    $jsonData = file_get_contents('../hunt_sessions/' . $_SESSION['hostGameID'] . '.json');
    if ($_SESSION['username'] == json_decode($jsonData, true)["gameinfo"]["master"]){
        $huntID = json_decode($jsonData, true)["gameinfo"]["huntID"];

        //close hunt
        if (endHunt($_SESSION['hostGameID'], $huntID)) {
            if (isset($_POST['highscore']) && isset($_POST['teamName'])) {
                //update highscore
                compareHighscore($huntID, $_POST['highscore'], $_POST['teamName']);
            } else {
                unset($_SESSION["hostGameID"]);
                successResponse('Highscore not updated');
            }
        } else {
            errorResponse('Could not end hunt');
        }
    }
    else{
        unset($_SESSION["hostGameID"]);
        errorResponse('Unauthorised');
    }
} else {
    errorResponse('Invalid request');
}

/**
 * echo error response
 * @param string $error
 */
function errorResponse(string $error)
{
    echo json_encode([
        'status' => 'error',
        'message' => $error,
    ]);
    exit;
}

/**
 * echo success response
 * @param string $message
 */
function successResponse(string $message)
{
    echo json_encode([
        'status' => 'ok',
        'message' => $message,
    ]);
    exit;
}

/**
 * Compare the highscore with highscore in the database
 * @param $huntID
 * @param $highscore
 * @param $teamName
 */
function compareHighscore($huntID, $highscore, $teamName)
{
    $conn = openCon();

    $sql = $conn->prepare("SELECT `Highscore` FROM `hunt` WHERE `HuntID` = ?");
    $sql->bind_param('i', $huntID);
    $sql->execute();
    $result = $sql->get_result();
    $row = $result->fetch_assoc();
    if ($row > 0) {
        if ($row['Highscore'] == null) {
            //if current highscore does not exist
            updateHighscore($huntID, $highscore, $teamName, $conn);
        } else if ($row['Highscore'] < $highscore) {
            //if current highscore is lower than new highscore
            updateHighscore($huntID, $highscore, $teamName, $conn);
        } else {
            $conn->close();
            unset($_SESSION["hostGameID"]);
            successResponse('Highscore unchanged');
        }
    } else {
        $conn->close();
        errorResponse('Hunt not found');
    }
}

/**
 * update the highscore and best team in the database
 * @param $huntID
 * @param $highscore
 * @param $teamName
 * @param $conn
 */
function updateHighscore($huntID, $highscore, $teamName, $conn)
{
    $sql = $conn->prepare("UPDATE `Hunt` SET `Highscore` = ?, `BestTeam` = ? WHERE `HuntID` = ?");
    $sql->bind_param('isi', $highscore, $teamName, $huntID);
    if ($sql->execute()) {
        $conn->close();
        unset($_SESSION["hostGameID"]);
        successResponse('Highscore updated');
    } else {
        $conn->close();
        unset($_SESSION["hostGameID"]);
        errorResponse('Update unsuccessful');
    }
}

/**
 * end the hunt by storing the json in the database and then deleting it
 * @param $gameID
 * @param $huntID
 * @return bool success
 */
function endHunt($gameID, $huntID)
{
    $conn = openCon();
    $jsonData = file_get_contents('../hunt_sessions/' . $gameID . '.json');

    $sql = $conn->prepare("INSERT INTO `huntdata` (`HuntID`, `json`) VALUES (?, ?)");
    $sql->bind_param("is", $huntID, $jsonData);
    if ($sql->execute()) {
        unlink('../hunt_sessions/' . $gameID . '.json');
        $conn->close();
        return true;
    } else {
        $conn->close();
        return false;
    }
}