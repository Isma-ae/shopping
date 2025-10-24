<?php 
    include_once("../config/all.php");

    $PAGE = $_GET["page"] ?? "home";
    $replaces = [">", "<", "'", '"', ";", "(", ")", " ", "/", "\\", "="];
    foreach($replaces as $val) {
        $PAGE = str_replace($val, "", $PAGE);
    }

	if (!isset($_SESSION["user_info"])) {
		header("Location: ../login/");
	} else {
		$currentUser = $_SESSION['user_info'];
		if (htmlspecialchars($currentUser['role']) != 'admin') {
			header("Location: ../");
		} else {
            $role = $DB->QueryObj("SELECT admin_type FROM users WHERE id = '".htmlspecialchars($currentUser['id'])."'");
        }
	}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>OAR Shopping - <?=$PAGE;?></title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="../images/logo.png" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Sarabun&display=swap" rel="stylesheet">
    <script>
    WebFont.load({
        google: {
            families: ["Public Sans:300,400,500,600,700"]
        },
        custom: {
            families: [
                "Font Awesome 5 Solid",
                "Font Awesome 5 Regular",
                "Font Awesome 5 Brands",
                "simple-line-icons",
            ],
            urls: ["assets/css/fonts.min.css"],
        },
        active: function() {
            sessionStorage.fonts = true;
        },
    });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="assets/css/demo.css" />
    <style>
    body,
    p,
    a,
    h3,
    h4,
    h5 {
        font-family: "Sarabun", sans-serif !important;
        font-weight: 500;
        font-style: normal;
    }
    </style>

    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>

    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="assets/tinymce/tinymce.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>

    <script src="assets/js/setting-demo2.js"></script>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include_once("master/sidebar.php");?>
        <!-- End Sidebar -->

        <div class="main-panel">
            <?php include_once("master/header.php");?>

            <div class="container">
                <?php
					if( ChkPermit() ) {
						echo '
							<link href="pages/'.$PAGE.'/view.css" rel="stylesheet">
							<script src="pages/'.$PAGE.'/view.js"></script>
						';
						include_once("pages/".$PAGE."/view.php");
					} else {
						echo '
							<link href="pages/404/view.css" rel="stylesheet">
							<script src="pages/404/view.js"></script>
						';
						include_once("pages/404/view.php");
					}

					function ChkPermit() {
						global $PAGE;
						if( !file_exists("pages/".$PAGE."/view.php") ) return false;
						if( !file_exists("pages/".$PAGE."/view.css") ) return false;
						if( !file_exists("pages/".$PAGE."/view.js") ) return false;
						return true;
					}
				?>
            </div>

            <?php include_once("master/footer.php");?>
        </div>

    </div>
</body>

</html>