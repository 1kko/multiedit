<?php
$mongo=new MongoClient();
$mongodb=$mongo->selectDB("multiedit");
$mongoCollection=$mongodb->enginediff;
$ABS_FileUploadPath=realpath(dirname(__FILE__)).'/../uploads/';


function logerr($str) {
	file_put_contents('php://stderr',$str);
}


?>