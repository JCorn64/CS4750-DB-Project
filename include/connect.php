<?php

/**
 * CS4750
 * Hoo's Watching
 * Jessica Heavner (jlh9qv), Julian Cornejo Castro (jac9vn), Patrick Thomas (pwt5ca), & Solimar Kwa (swk3st)
 */

require_once("debug.php");
require_once("secret.php");

// define("USERNAME", "pwt5ca");
// define("PASSWORD", ""); // Secret
define("HOST", "usersrv01.cs.virginia.edu");
define("DB_NAME", "pwt5ca");

// Connect to the database.
// From https://www.php.net/manual/en/mysqli.query.php
$db = new mysqli(HOST, DB_OTHER_USER, DB_OTHER_PASSWORD, DB_NAME);
if ($db->connect_errno) {
    printf("Database connection failed: %s\n", $mysqli->connect_error);
    die();
}

$db_users = new mysqli(HOST, DB_USERS_USER, DB_USERS_PASSWORD, DB_NAME);
if ($db_users->connect_errno) {
    printf("Database connection failed: %s\n", $mysqli->connect_error);
    die();
}
