<?php

require_once '/var/www/html/multiedit/php/common/define.php';
require_once '/var/www/html/multiedit/php/common/db.php';
require_once '/var/www/html/multiedit/php/common/util.php';

$mode = inputGet('mode');
$resultList = array('status' => 'ok');

switch ($mode) {
    case 'get':
        $results = $db->select("project");
        $resultList['list'] = $results;
        break;

    case 'add':
        $name = inputGet('name', 'empty');
        $insert = array(
            'name' => inputGet('name')
        );
        $db->insert("project", $insert);

        break;

    case 'mod':
        $update = array(
            "name" => inputGet('name', 'empty')
        );

        $where = 'id = :id';
        $bind = array(
            ":id" => inputGet('id')
        );

        $db->update("project", $update, $where, $bind);
        break;

    case 'del':
        $where = 'id = :id';
        $bind = array(
            ":id" => inputGet('id')
        );

        $db->delete('project', $where, $bind);
        break;

    default:
        $resultList['status'] = 'fail';
        $resultList['message'] = 'unknown mode';
        break;
}

echo json_encode($resultList);
