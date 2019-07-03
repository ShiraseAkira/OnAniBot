<?php
const dbHost = "eu-cdbr-west-02.cleardb.net";
const dbUsername = "b2b48db1e8befd";
const dbPasswd = "8113a8b7";
const dbName = "heroku_717c9367403bbb5";
function getDatabaseConnection() {

    $mysqlli = new mysqli(dbHost, dbUsername, dbPasswd, dbName);
    if($mysqlli->connect_errno) {
        error_log("Ошибка: " . $mysqlli->connect_errno);
        return NULL;
    } else {
        return $mysqlli;
    }
}
