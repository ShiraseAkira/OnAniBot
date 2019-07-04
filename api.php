<?php
const apiUrl = "https://shikimori.one/api/";
const schedule = "calendar";
function getSchedule(): ?object {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, apiUrl.schedule);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $schedule = json_decode($output);
}