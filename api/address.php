<?php

    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'select_address'      : select_address();      break;
        case 'select_provinces'    : select_provinces();    break;
        case 'select_districts'    : select_districts();    break;
        case 'select_subdistricts' : select_subdistricts(); break;
        case 'select_zip_code'     : select_zip_code();     break;
        case 'insert_address'      : insert_address();      break;
        case 'update_address'      : update_address();      break;
        case 'delete_address'      : delete_address();      break;
        default: break;
    }

    function requireAddressAuth() {
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "status"  => "error",
                "data"    => "f",
                "title"   => "กรุณาเข้าสู่ระบบ",
                "message" => "เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        return $_SESSION["user_info"];
    }

    function select_address() {
        global $DB;
        $currentUser = requireAddressAuth();
        $userId = $DB->Escape((string)$currentUser['id']);
        $sql = "SELECT
                    tb_address.*,
                    provinces.province_name_in_thai,
                    districts.district_name_in_thai,
                    subdistricts.subdistrict_name_in_thai,
                    subdistricts.zip_code
                FROM tb_address
                INNER JOIN provinces
                    ON tb_address.province_id = provinces.province_id
                INNER JOIN districts
                    ON tb_address.district_id = districts.district_id
                INNER JOIN subdistricts
                    ON tb_address.subdistrict_id = subdistricts.subdistrict_id
                WHERE tb_address.user_id = '{$userId}'
                ORDER BY tb_address.id DESC";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function select_provinces() {
        global $DB;
        $sql = "SELECT * FROM provinces ORDER BY province_name_in_thai ASC";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function select_districts() {
        global $DB;
        $provinceId = (int)($_POST["province_id"] ?? 0);
        $sql = "SELECT * FROM districts WHERE province_id = {$provinceId} ORDER BY district_name_in_thai ASC";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function select_subdistricts() {
        global $DB;
        $districtId = (int)($_POST["district_id"] ?? 0);
        $sql = "SELECT * FROM subdistricts WHERE district_id = {$districtId} ORDER BY subdistrict_name_in_thai ASC";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function select_zip_code() {
        global $DB;
        $subdistrictId = (int)($_POST["subdistrict_id"] ?? 0);
        $sql = "SELECT * FROM subdistricts WHERE subdistrict_id = {$subdistrictId}";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function insert_address() {
        global $DB;
        $currentUser = requireAddressAuth();
        $userId = (int)$currentUser['id'];
        $name = trim((string)($_POST["name"] ?? ""));
        $phone = trim((string)($_POST["phone"] ?? ""));
        $addressAt = trim((string)($_POST["address_at"] ?? ""));
        $provinceId = (int)($_POST["province_id"] ?? 0);
        $districtId = (int)($_POST["district_id"] ?? 0);
        $subdistrictId = (int)($_POST["subdistrict_id"] ?? 0);

        if ($name === "" || $phone === "" || $addressAt === "" || $provinceId <= 0 || $districtId <= 0 || $subdistrictId <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณากรอกข้อมูลให้ครบ",
                "message" => "กรุณาระบุชื่อ เบอร์โทร ที่อยู่ และเลือกจังหวัด อำเภอ ตำบลให้ครบถ้วน",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $address_id = $DB->QueryMaxId('tb_address', 'id');
        $insert = $DB->QueryInsert('tb_address', [
            "id"             => $address_id,
            "name"           => $name,
            "phone"          => $phone,
            "province_id"    => $provinceId,
            "district_id"    => $districtId,
            "subdistrict_id" => $subdistrictId,
            "address_at"     => $addressAt,
            "user_id"        => $userId
        ]);
        if ($insert) {
            echo json_encode([
                "status"  => "success",
                "title"   => "บันทึกสำเร็จ",
                "message" => "เพิ่มที่อยู่จัดส่งใหม่เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "เกิดข้อผิดพลาด",
                "message" => "ไม่สามารถเพิ่มที่อยู่ได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function update_address() {
        global $DB;
        $currentUser = requireAddressAuth();
        $userId = (int)$currentUser['id'];
        $address_id = (int)($_POST["address_id"] ?? 0);
        $name = trim((string)($_POST["name"] ?? ""));
        $phone = trim((string)($_POST["phone"] ?? ""));
        $addressAt = trim((string)($_POST["address_at"] ?? ""));
        $provinceId = (int)($_POST["province_id"] ?? 0);
        $districtId = (int)($_POST["district_id"] ?? 0);
        $subdistrictId = (int)($_POST["subdistrict_id"] ?? 0);

        if ($address_id <= 0 || $name === "" || $phone === "" || $addressAt === "" || $provinceId <= 0 || $districtId <= 0 || $subdistrictId <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ครบถ้วน",
                "message" => "กรุณากรอกข้อมูลที่อยู่ให้ครบถ้วน",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $update = $DB->QueryUpdate('tb_address', [
            "name"           => $name,
            "phone"          => $phone,
            "province_id"    => $provinceId,
            "district_id"    => $districtId,
            "subdistrict_id" => $subdistrictId,
            "address_at"     => $addressAt
        ], "id = {$address_id} AND user_id = {$userId}");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "บันทึกสำเร็จ",
                "message" => "แก้ไขที่อยู่จัดส่งเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "เกิดข้อผิดพลาด",
                "message" => "ไม่สามารถแก้ไขที่อยู่ได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_address() {
        global $DB;
        $currentUser = requireAddressAuth();
        $userId = (int)$currentUser['id'];
        $address_id = (int)($_POST["address_id"] ?? 0);
        $delete = $DB->QueryDelete('tb_address', "id = {$address_id} AND user_id = {$userId}");
        if ($delete) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบที่อยู่จัดส่งเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "เกิดข้อผิดพลาด",
                "message" => "ไม่สามารถลบที่อยู่ได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
