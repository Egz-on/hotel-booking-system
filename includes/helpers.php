<?php

function get_services($room) {
    global $pdo;
    $services = [];

    $sql = "SELECT services.* FROM services INNER JOIN room_service ON services.id = room_service.service_id INNER JOIN rooms ON rooms.id = room_service.room_id WHERE rooms.id = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([$room]);

    while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        $services[] = $row;
    }

    return $services;
}

function get_media($room) {
    global $pdo;
    $media = [];

    $sql = "SELECT * FROM `media` WHERE `room_id` = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([$room]);

    while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        $media[] = $row;
    }

    return $media;
}