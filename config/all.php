<?php
    session_start();
    include_once("class.database.php");
    include_once("func.php");
    include_once("class.texcel.php");
    include('scb_class.php');
	include('connect_scb.php');
    include('payment_success.php');
    include('ssp.customized.class.php');

    $VERSION = "1.0.10";

    date_default_timezone_set("Asia/Bangkok");
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

   $host = "localhost";
	$user = "root";
	$pass = "";
	$dbname = "shopping";
    /* $host = "localhost";
	$user = "shopping";
	$pass = "**123shopping321**";
	$dbname = "shopping";*/
    $DB = new Database($host, $user, $pass, $dbname);
    define( 'HOST', 'localhost' );
    define( 'USER', 'payment' );
    define( 'PASS', '**123payment321**' );
    define( 'DBNAME', 'payment' );
    $con = new Database(HOST, USER, PASS, DBNAME, 'UTF8');

    
    function getFileType($fileName) {
        $arr = explode(".", $fileName);
        if (sizeof($arr) == 1) return "";
        return strtolower(end($arr));
    }


	function uploadFile($dir,$file,$rename="") {
		if( !isset($file["name"]) || !isset($file["name"][0]) ) return "";
		if( !isset($file["tmp_name"]) || !isset($file["tmp_name"][0]) ) return "";
		if (is_array($file["name"]) ) {
			$fileName = $file["name"][0];
			$fileTmp = $file["tmp_name"][0];
		} else {
			$fileName = $file["name"];
			$fileTmp = $file["tmp_name"];
		}
		$fileType = getFileType($fileName);
		$fileNameNew = "";
		if( $rename=="" ) $fileNameNew = $fileName;
		else $fileNameNew = $rename.".".$fileType;
		if( move_uploaded_file($fileTmp, $dir.$fileNameNew) ) {
			return $fileNameNew;
		}
		return "";
 	}
 	function deleteFile($dir, $filename) {
 		if ($filename!="" && file_exists($dir.$filename)) {
		    unlink($dir.$filename);
		}
 	}

    function uploadFileFTP($ftp_server, $ftp_user, $ftp_pass, $ftp_dir, $file, $rename = "") {
        if (!isset($file["name"]) || !isset($file["tmp_name"])) return "";

        // รองรับทั้ง array และ single upload
        if (is_array($file["name"])) {
            $fileName = $file["name"][0];
            $fileTmp   = $file["tmp_name"][0];
        } else {
            $fileName = $file["name"];
            $fileTmp   = $file["tmp_name"];
        }

        // ตั้งชื่อไฟล์ใหม่ (ถ้ามี)
        $fileType = getFileType($fileName);
        $fileNameNew = $rename == "" ? $fileName : $rename . "." . $fileType;

        // เชื่อมต่อ FTP
        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id) return "";

        // ล็อกอิน
        $login = @ftp_login($conn_id, $ftp_user, $ftp_pass);
        if (!$login) {
            ftp_close($conn_id);
            return "";
        }

        ftp_pasv($conn_id, true); // เปิดโหมด passive

        // อัปโหลดไฟล์ไปยัง FTP
        $remote_path = rtrim($ftp_dir, "/") . "/" . $fileNameNew;
        $upload = ftp_put($conn_id, $remote_path, $fileTmp, FTP_BINARY);

        ftp_close($conn_id);

        if ($upload) return $fileNameNew;
        return "";
    }

    function deleteFileFTP($ftp_server, $ftp_user, $ftp_pass, $ftp_dir, $filename) {
        if ($filename == "") return false;

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id) return false;

        $login = @ftp_login($conn_id, $ftp_user, $ftp_pass);
        if (!$login) {
            ftp_close($conn_id);
            return false;
        }

        ftp_pasv($conn_id, true);

        $remote_path = rtrim($ftp_dir, "/") . "/" . $filename;
        $result = @ftp_delete($conn_id, $remote_path);

        ftp_close($conn_id);
        return $result;
    }

    function parseSize($sizeString) {
    if (preg_match('/^([A-Za-z0-9]+)\((.+)\)$/u', $sizeString, $matches)) {
        $size_shirt  = strtoupper($matches[1]); // เช่น M
        $size_detail = trim($matches[2]);       // เช่น "อก36นิ้ว ยาว26นิ้ว"
    } else {
        $size_shirt  = strtoupper($sizeString);
        $size_detail = "";
    }

    $orderMap = [
        "XS" => 0,
        "SS" => 0,
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

    if (!function_exists('assetVer')) {
        function assetVer($path) {
            $cleanPath = explode('?', $path, 2)[0];
            $scriptDir = !empty($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : getcwd();
            $fullPath = $scriptDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);
            if (!file_exists($fullPath)) {
                $fullPath = $cleanPath;
            }
            $v = file_exists($fullPath) ? filemtime($fullPath) : time();
            $sep = strpos($path, '?') === false ? '?' : '&';
            return $path . $sep . 'v=' . $v;
        }
    }

    