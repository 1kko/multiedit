<?php

function inputGet($name, $defaultValue = '') {
    $result = filter_input(INPUT_GET, $name);
    if ($result == null || $result === '') {
        $result = $defaultValue;
    }

    return $result;
}

function inputPost($name, $defaultValue = '') {
    $result = filter_input(INPUT_POST, $name);
    if ($result == null || $result === '') {
        $result = $defaultValue;
    }

    return $result;
}

function pr($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}
