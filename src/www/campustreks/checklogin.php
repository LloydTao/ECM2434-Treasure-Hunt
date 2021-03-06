<?php
/**
 * Opens session and checks if the user is logged in
 *
 * @return boolean true if logged in, otherwise false
 * @author Jakub Kwak
 */
function CheckLogin()
{
    //ignore session errors for the sake of unit testing
    @session_start();
    if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] == false) {
        return false;
    }
    return true;
}
