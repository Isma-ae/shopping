<style>
.my-msg {
    width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
<script>
$(document).ready(function() {
    $('#logout').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "../login/action.php",
            data: {
                fn: "logout"
            },
            dataType: "json",
            success: function(response) {
                if (response.data == 't') {
                    location.reload();
                } else {
                    alert('error');
                }
            }
        });
    });

    $('#read-message').click(function(e) {
        e.preventDefault();
        var message_id = $(this).attr('msg-id');
        $.ajax({
            type: "post",
            url: "api/message_list.php",
            data: {
                fn: "change_read",
                message_id: message_id
            },
            dataType: "json",
            success: function(res) {
                if (res.status == 'success') {
                    window.location.href = "?page=message&message_id=" + message_id;
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            }
        });
    });
});
</script>
<div class="main-header">
    <div class="main-header-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="./" class="logo">
                <h2 class="text-white">OAR Shopping</h2>
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-icon dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-envelope"></i>
                        <?php
                            $sqls = "SELECT COUNT(message_id) AS count_msg FROM message WHERE `read` = 0";
                            $objs = $DB->QueryObj($sqls);
                            if ($objs[0]["count_msg"] > 0) {
                        ?>
                        <span class="notification"><?=$objs[0]["count_msg"];?></span>
                        <?php } ?>
                    </a>
                    <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                        <li>
                            <div class="dropdown-title d-flex justify-content-between align-items-center">
                                ข้อความ
                            </div>
                        </li>
                        <li>
                            <div class="message-notif-scroll scrollbar-outer">
                                <div class="notif-center">
                                    <?php
                                        $sqlm = "SELECT * FROM message WHERE `read` = 0 ORDER BY message_id DESC LIMIT 4";
                                        $objm = $DB->QueryObj($sqlm);
                                        $now = new DateTime();
                                        foreach ($objm  as $rowm) {
                                            $send_date = $rowm["send_date"];
                                            $sended = new DateTime($send_date);
                                            $diff = $now->diff($sended);

                                            if ($diff->i < 1 && $diff->h == 0 && $diff->d == 0) {
                                                $time_ago = $diff->s . ' วินาทีที่แล้ว';
                                            } elseif ($diff->h < 1 && $diff->d == 0) {
                                                $time_ago = $diff->i . ' นาทีที่แล้ว';
                                            } elseif ($diff->d < 1) {
                                                $time_ago = $diff->h . ' ชั่วโมงที่แล้ว';
                                            } else {
                                                $time_ago = $diff->d . ' วันที่แล้ว';
                                            }

                                    ?>
                                    <a href="#" msg-id="<?=$rowm["message_id"];?>" id="read-message">
                                        <div class="notif-img">
                                            <img src="assets/img/chat.png" alt="Img Profile" />
                                        </div>
                                        <div class="notif-content">
                                            <span class="subject"><?=$rowm["email"];?></span>
                                            <span class="block my-msg"><?=$rowm["msg"];?></span>
                                            <span class="time"><?=$time_ago;?></span>
                                        </div>
                                    </a>
                                    <?php }?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="see-all" href="?page=message-list">ดูข้อความทั้งหมด<i
                                    class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item topbar-user dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                        <div class="avatar-sm">
                            <img src="assets/img/admin.png" alt="..." class="avatar-img rounded-circle" />
                        </div>
                        <span class="profile-username">
                            <span class="op-7">สวัสดี,</span>
                            <span class="fw-bold"><?=htmlspecialchars($currentUser['name']);?></span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <div class="dropdown-user-scroll scrollbar-outer">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        <img src="assets/img/admin.png" alt="image profile"
                                            class="avatar-img rounded" />
                                    </div>
                                    <div class="u-text">
                                        <h4><?=htmlspecialchars($currentUser['name']);?></h4>
                                        <p class="text-muted"><?=htmlspecialchars($currentUser['email']);?></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../">หน้าหลัก</a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" id="logout">ออกจากระบบ</a>
                            </li>
                        </div>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</div>