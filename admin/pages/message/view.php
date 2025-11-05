<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">ข้อความ</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="#">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="?page=message-list">ข้อความทั้งหมด</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">ข้อความ</a>
            </li>
        </ul>
    </div>
    <?php
        $sql = "SELECT * FROM message WHERE message_id = ".$_GET["message_id"]."";
        $obj = $DB->QueryObj($sql);
    ?>
    <input type="hidden" id="message_id" value="<?=$_GET["message_id"];?>">
    <input type="hidden" id="email" value="<?=$obj[0]["email"];?>">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?=$obj[0]["email"];?></h4>
                </div>
                <div class="card-body">
                    <p><?=$obj[0]["msg"];?></p>
                    <hr>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="password">ข้อความตอบกลับ</label>
                            <textarea id="message_reply"><?=$obj[0]["message_reply"];?></textarea>
                        </div>
                    </div>
                    <button type="button" id="reply" class="btn btn-primary">
                        ส่งข้อความตอบกลับ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>