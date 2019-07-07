<?php
const API_URL = "https://shikimori.one/api/";
const SCHEDULE = "calendar";
function getSchedule(): ?array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_URL.SCHEDULE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $schedule = json_decode($output);
}