<?php
    session_start();
    include_once("class.database.php");
    include_once("func.php");
    include_once("class.texcel.php");
    include('scb_class.php');
	include('connect_scb.php');

    $host = "localhost";
	$user = "root";
	$pass = "";
	$dbname = "db_shopping";
    /*$host = "localhost";
	$user = "shopping";
	$pass = "**123shopping321**";
	$dbname = "shopping";*/
    $DB = new Database($host, $user, $pass, $dbname);

    function getFileType($fileName) {
		$arr = explode(".", $fileName);
		if( sizeof($arr)==1 ) return "";
		return $arr[ sizeof($arr)-1 ];
	}
	function uploadFile($dir, $file, $rename = "") {
        if (!isset($file["name"]) || !isset($file["tmp_name"])) return "";

        $fileName = is_array($file["name"]) ? $file["name"][0] : $file["name"];
        $fileTmp = is_array($file["tmp_name"]) ? $file["tmp_name"][0] : $file["tmp_name"];

        $fileType = getFileType($fileName);
        $fileNameNew = ($rename == "") ? $fileName : $rename . "." . $fileType;

        if (move_uploaded_file($fileTmp, $dir . $fileNameNew)) {
            return $fileNameNew;
        }
        return "";
    }
 	function deleteFile($dir, $filename) {
 		if ($filename!="" && file_exists($dir.$filename)) {
		    unlink($dir.$filename);
		}
 	}

    function parseSize($sizeString) {
    if (preg_match('/^([A-Za-z]+)\((.+)\)$/u', $sizeString, $matches)) {
        $size_shirt  = strtoupper($matches[1]); // เช่น M
        $size_detail = trim($matches[2]);       // เช่น "อก36นิ้ว ยาว26นิ้ว"
    } else {
        $size_shirt  = strtoupper($sizeString);
        $size_detail = "";
    }

    $orderMap = [
        "XS" => 0,
        "S"  => 1,
        "M"  => 2,
        "L"  => 3,
        "XL" => 4,
        "XXL" => 5,
        "2XL" => 5
    ];
    $size_order = $orderMap[$size_shirt] ?? 99;

    return [
        "size_shirt"  => $size_shirt,
        "size_detail" => $size_detail,
        "size_order"  => $size_order
    ];
}

    