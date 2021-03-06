<?php
/**
 * Check if a hunt session exists with this pin
 * @param $pin
 * @return bool
 *
 * @author Jakub Kwak
 */
function findGame($pin)
{
    $filename = '../hunt_sessions/' . $pin . '.json';
    if (file_exists($filename))
    {
        return true;
    } else {
        return false;
    }
}


/**
 * Check if the game in session still exists, and echo it back if it does
 *
 * @author Jakub Kwak
 */
function checkGame()
{
    session_start();
    
    if ($_POST['type'] == "host" && isset($_SESSION['hostGameID']) && isset($_SESSION['username'])) {
        $hostGameID = $_SESSION['hostGameID'];
        $username = $_SESSION['username'];
        echo json_encode(array("status" => "success", "hostGameID" => $hostGameID, "username" => $username));
        return;
    }
    if ($_POST['type'] == "play" && isset($_SESSION['gameID']) && isset($_SESSION['nickname'])) {
        $gameID = $_SESSION['gameID'];
        $nickname = $_SESSION['nickname'];
        if (findGame($gameID)) {
            if (isset($_SESSION['teamName'])) {
                $teamName = $_SESSION['teamName'];

                if (isset($_SESSION['game'])) {
                    echo json_encode(array("status" => "success", "gameID" => $gameID, "nickname" => $nickname, "teamName" => $teamName, "game" => "active"));
                }
                else {
                    echo json_encode(array("status" => "success", "gameID" => $gameID, "nickname" => $nickname, "teamName" => $teamName, "game" => "inactive"));
                }
                return;
            } else {
                echo json_encode(array("status" => "success", "gameID" => $gameID, "nickname" => $nickname, "teamName" => null, "game" => "inactive"));
                return;
            }
        }
        unset($_SESSION['gameID']);
        unset($_SESSION['nickname']);
        unset($_SESSION['teamName']);

        if (isset($_SESSION['game'])) {
            echo json_encode(array("status" => "fail", "gameID" => null, "nickname" => null, "teamName" => null, "game" => "active"));
        } else {
            echo json_encode(array("status" => "fail", "gameID" => null, "nickname" => null, "teamName" => null, "game" => "inactive"));
        }
        return;
    }
    if (isset($_SESSION['game'])) {
        echo json_encode(array("status" => "fail", "gameID" => null, "nickname" => null, "teamName" => null, "game" => "active"));
    } else {
        echo json_encode(array("status" => "fail", "gameID" => null, "nickname" => null, "teamName" => null, "game" => "inactive"));
    }
}


checkGame();

?>
