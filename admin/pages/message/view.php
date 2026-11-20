<?php
    $msgId = (int)($_GET["message_id"] ?? 0);
    $obj = $msgId > 0 ? $DB->QueryObj("SELECT * FROM message WHERE message_id = {$msgId} LIMIT 1") : [];
    if (count($obj) > 0 && (int)($obj[0]["read"] ?? 0) === 0) {
        $DB->QueryUpdate("message", ["read" => 1], "message_id = {$msgId}");
    }
    $msgRow = count($obj) > 0 ? $obj[0] : null;
    $hasReply = $msgRow && trim((string)($msgRow["message_reply"] ?? "")) !== "";
?>
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">รายละเอียดข้อความ</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="./">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="?page=message-list">ข้อความติดต่อ</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">รายละเอียดข้อความ</a>
            </li>
        </ul>
    </div>

    <?php if (!$msgRow) { ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <h4 class="mb-2">ไม่พบข้อความที่ต้องการ</h4>
                <p class="text-muted mb-3">ข้อความนี้อาจถูกลบออกไปแล้ว</p>
                <a href="?page=message-list" class="btn btn-primary">กลับไปหน้ารายการข้อความ</a>
            </div>
        </div>
    <?php } else { ?>
        <input type="hidden" id="message_id" value="<?= (int)$msgRow["message_id"]; ?>">
        <input type="hidden" id="email" value="<?= htmlspecialchars((string)$msgRow["email"], ENT_QUOTES, "UTF-8"); ?>">

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <?= htmlspecialchars((string)$msgRow["email"], ENT_QUOTES, "UTF-8"); ?>
                            </h4>
                            <small class="text-muted">
                                ส่งเมื่อ: <?= htmlspecialchars((string)($msgRow["send_date"] ?? "-"), ENT_QUOTES, "UTF-8"); ?>
                            </small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($hasReply) { ?>
                                <span class="badge badge-success">ตอบกลับแล้ว</span>
                            <?php } else { ?>
                                <span class="badge badge-warning">รอตอบกลับ</span>
                            <?php } ?>
                            <a href="?page=message-list" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>กลับหน้ารายการ
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="p-3 mb-4 rounded" style="background:#f8f9fa; border:1px solid #e9ecef;">
                            <div class="text-muted small mb-1">ข้อความจากลูกค้า:</div>
                            <div style="white-space: pre-wrap; font-size: 15px; line-height: 1.65;">
                                <?= htmlspecialchars((string)$msgRow["msg"], ENT_QUOTES, "UTF-8"); ?>
                            </div>
                        </div>

                        <div class="form-group px-0">
                            <label for="message_reply" class="fw-bold mb-2">ข้อความตอบกลับ (จะส่งไปยังอีเมลลูกค้า)</label>
                            <textarea id="message_reply"><?= htmlspecialchars((string)($msgRow["message_reply"] ?? ""), ENT_QUOTES, "UTF-8"); ?></textarea>
                        </div>

                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <a href="?page=message-list" class="btn btn-light border">ยกเลิก</a>
                            <button type="button" id="reply" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>ส่งข้อความตอบกลับ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
