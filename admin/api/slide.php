<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'load_slide'	: load_slide(); 	break;
        case 'add_slide'	: add_slide(); 	break;
        case 'edit_slide'	: edit_slide(); 	break;
        case 'delete_slide'	: delete_slide(); 	break;
		default: break;
	}

    function load_slide() {
        global $DB;
        $obj = $DB->QueryObj("SELECT * FROM slide");
        foreach ($obj as $row) {
            echo '
                <div class="col-md-4 col-sm-12 data-slide" data-json="'.$DB->Escape(json_encode($row),'display').'">
                    <div class="card">
                        <img src="../file/banner/'.$row["slide_img"].'" class="card-img-top" alt="..." />
                        <div class="card-body">
                            <h5 class="card-title">'.$row["slide_name"].'</h5>
                            <p class="card-text">'.$row["slide_detail"].'</p>
                            <a href="#" class="btn btn-warning edit-slide"><i class="fas fa-edit"></i></a>
                            <a href="#" class="btn btn-danger del-slide"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </div>
                </div>
            ';
        }
    }


    function add_slide() {
        global $DB;
        $dir = "../../file/banner/";
        $slide_id = $DB->QueryMaxId("slide","slide_id",'sl',11);
        $img = $_FILES['slide_img'];
        $slide_img = uploadFile($dir,$img,"slide_".$slide_id);
        $add = $DB->QueryInsert('slide',[
            'slide_id' => $slide_id,
            'slide_name' => $_POST["slide_name"],
            'slide_detail' => $_POST["slide_detail"],
            'slide_link' => $_POST["slide_link"],
            'slide_img' => $slide_img
        ]);

        if ($add) {
            echo 't';
        } else {
            echo "cann't upload";
        }
        
    }

    function edit_slide() {
        global $DB;
        $dir = "../../file/banner/";
        $slide_id = $_POST["slide_id"];
        $slide_img = @$_FILES["slide_img"];
		$img = uploadFile($dir,$slide_img,"slide_".$slide_id);
		$update_img = ($img=="") ? "" : " ,slide_img ='".$img."'";
        $sql = "UPDATE slide 
			SET slide_name = '".$DB->Escape($_POST["slide_name"])."',
				slide_detail = '".$DB->Escape($_POST["slide_detail"])."',
                slide_link = '".$DB->Escape($_POST["slide_link"])."'
				".$update_img." 
			WHERE slide_id='".$slide_id."'";
		$run_query = $DB->Query($sql);
		if($run_query){
			echo "t";
		}else{
			echo $sql;
		}
    }

    function delete_slide() {
        global $DB;
        $dir = "../../file/banner/";
        $slide_id = $_POST["slide_id"];
        $obj = $DB->QueryObj("SELECT * FROM slide WHERE slide_id='".$slide_id."'");
        $delete = $DB->QueryDelete("slide","slide_id='".$slide_id."'");
        if ($delete) {
            deleteFile($dir,$obj[0]["slide_img"]);
	        echo "t";
        } else {
            echo $delete;
        }
        
    }
?>