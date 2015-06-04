<?php

require_once '/var/www/html/multiedit/php/common/define.php';
require_once '/var/www/html/multiedit/php/common/db.php';
require_once '/var/www/html/multiedit/php/common/util.php';

$mode = inputGet('mode');
$resultList = array('status' => 'ok');

switch ($mode) {
    case 'get':
        $id = inputGet('id', '');
        $categoryId = inputGet('categoryId');
        $projectId = inputGet('projectId');

        if (!$categoryId || !$projectId) {
            json_encode(array('status' => 'fail'));
        }
        
        $where = 'projectId = :projectId and categoryId = :categoryId';
        $bind = array(
            ":projectId" => $projectId,
            ":categoryId" => $categoryId
        );

        if (empty($id)) {
            $limit = ' limit 1';
        } else {
            $limit = '';
        }

        $results = $db->select("contents", $where, $bind, '*', 'id desc ' . $limit);
        $resultList['list'] = $results;
        break;

    case 'add':
        $text = inputPost('text', '');
        $categoryId = inputPost('categoryId', '0');
        $projectId = inputPost('projectId', '0');
        
        if (empty($categoryId) || empty($projectId)) {
            json_encode(array('status' => 'fail'));
        }
        
        $insert = array(
            'text' => $text,
            'categoryId' => $categoryId,
            'projectId' => $projectId
        );
        $db->insert("contents", $insert);

        break;

    case 'mod':
        $text = inputPost('text', '');
        $categoryId = inputPost('categoryId', '0');
        $projectId = inputPost('projectId', '0');
        
        if (empty($categoryId) || empty($projectId)) {
            json_encode(array('status' => 'fail'));
        }

        $update = array(
            'text' => $text,
            'categoryId' => $categoryId,
            'projectId' => $projectId
        );

        $where = 'id = :id';
        $bind = array(
            ":id" => inputGet('id')
        );

        $db->update("contents", $update, $where, $bind);
        break;

    case 'del':
        $where = 'id = :id';
        $bind = array(
            ":id" => inputGet('id')
        );

        $db->delete('contents', $where, $bind);
        break;

    default:
        $resultList['status'] = 'fail';
        $resultList['message'] = 'unknown mode';
        break;
}

echo json_encode($resultList);
