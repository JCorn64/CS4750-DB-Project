<?php

/**
 * CS4750
 * Hoo's Watching
 * Jessica Heavner (jlh9qv), Julian Cornejo Castro (jac9vn), Patrick Thomas (pwt5ca), & Solimar Kwa (swk3st)
 */

require_once("connect.php");
require_once("security.php");
require_once("user-funcs.php");

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Check if a username is already used.
 */
function check_user_exists($email)
{
    global $db;

    $sql = "SELECT count(*) FROM Users WHERE email=?";

    $statement = $db->prepare($sql);
    $statement->bind_param("s", $email);
    $results = $statement->execute();
    $user_exists = 0;
    $statement->bind_result($user_exists);
    if ($results) {
        $statement->fetch();
        return $user_exists == 1;
    }
    $statement->close();
    return false;
}

/**
 * Create a new user given an email and a password.
 */
function create_new_user($email, $password)
{
    global $db;

    // First check if the user already exists.
    if (check_user_exists($email)) {
        return false;
    }

    $password_hashed = movie_password_hash($password);
    $sql = "INSERT INTO Users (email, password) VALUES (?, ?)";

    $statement = $db->prepare($sql);

    $statement->bind_param("ss", $email, $password_hashed);
    $statement->execute();
    $statement->close();

    return true;
}

/**
 * Log into a user and set a session variable indicating what user is logged in currently.
 */
function login_user($email, $password)
{
    global $db;

    $sql = "SELECT password FROM pwt5ca.Users WHERE email=?";

    $statement = $db->prepare($sql);
    $statement->bind_param("s", $email);
    $statement->execute();

    $stored_password = null;
    $statement->bind_result($stored_password);
    $statement->fetch();
    if (!is_null($stored_password)) {
        if (movie_check_password($password, $stored_password)) {
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password; // This is a bad practice.
            $statement->close();

            return true;
        }
    }
    $statement->close();
    return false;
}

/**
 * Return true if the current user is logged in, otherwise return false.
 * 
 * From https://www.php.net/manual/en/mysqli-stmt.fetch.php
 */
function get_top_five_movies()
{
    global $db;
    $sql = "SELECT primaryTitle FROM pwt5ca.Titles ORDER BY startYear DESC LIMIT 5";
    $output = array();

    if ($stmt = $db->prepare($sql)) {

        /* execute statement */
        $stmt->execute();

        /* bind result variables */
        $title = "";
        $output = array();
        $stmt->bind_result($title);

        /* fetch values */
        while ($stmt->fetch()) {
            array_push($output, $title);
        }

        /* close statement */
        $stmt->close();
    }

    return $output;
}
