<?php

require_once '/var/www/html/multiedit/php/common/define.php';
require_once '/var/www/html/multiedit/php/common/db.php';
require_once '/var/www/html/multiedit/php/common/util.php';

$mode = inputGet('mode');
$resultList = array('status' => 'ok');

switch ($mode) {
    case 'get':
        $results = $db->select("category", '', '', '*', 'orderNum asc');
        
        $jsTree = array();
        foreach($results as &$list) {
            $temp = array();
            
            $temp['id'] = $list['id'];
            $temp['text'] = $list['name'];
            $temp['parent'] = ($list['parentId'] == 0) ? '#' : $list['parentId'];
            
            $jsTree[] = $temp;
        }
        unset($list);
        
        $resultList['list'] = $jsTree;
        break;

    case 'add':
        $name = inputGet('name', 'empty');
        $insert = array(
            'name' => inputGet('name'),
            'parentId' => inputGet('parentId', 0),
            'orderNum' => inputGet('orderNum', 0)
        );
        $db->insert("category", $insert);
        
        $id = $db->getLastInsertId();
        $resultList['id'] = $id;
        
        break;

    case 'mod':
        $update = array(
            "name" => inputGet('name', 'empty'),
            'parentId' => inputGet('parentId', 0),
            'orderNum' => inputGet('orderNum', 0)
        );

        $where = 'id = :id';
        $bind = array(
            ":id" => inputGet('id')
        );

        $db->update("category", $update, $where, $bind);
        break;

    case 'del':
        $where = 'id = :id';
        $bind = array(
            ":id" => inputGet('id')
        );

        $db->delete('category', $where, $bind);
        break;

    default:
        $resultList['status'] = 'fail';
        $resultList['message'] = 'unknown mode';
        break;
}

echo json_encode($resultList);
