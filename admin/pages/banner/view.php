<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">จัดการแบนเนอร์</h3>
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
                <a href="?page=banner">แบนเนอร์</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="card-title mb-0">แบนเนอร์หน้าแรก</h4>
                            <span class="badge badge-primary" id="bannerCountBadge">0 รายการ</span>
                        </div>
                        <button type="button" class="btn btn-primary btn-round" id="add">
                            <i class="fa fa-plus me-1"></i>เพิ่มแบนเนอร์
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="bn-tip-bar mb-4">
                        <i class="fas fa-info-circle"></i>
                        <span>แนะนำภาพแบนเนอร์แนวนอนขนาด <strong>1920 &times; 800 px</strong> (อัตราส่วน 12:5) รองรับไฟล์ JPG, PNG, WEBP เพื่อการแสดงผลที่คมชัดทั้งหน้าจอคอมพิวเตอร์และมือถือ</span>
                    </div>

                    <div class="row g-4 slide-data">
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i>กำลังโหลดแบนเนอร์...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit Banner Modal -->
<div class="modal fade" id="slideModal" tabindex="-1" aria-labelledby="slideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="slideModalLabel">เพิ่มแบนเนอร์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-slide" enctype="multipart/form-data">
                    <input type="hidden" id="fn" name="fn" value="add_slide" />
                    <input type="hidden" id="slide_id" name="slide_id" value="" />

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ตัวอย่างภาพแบนเนอร์</label>
                        <div class="bn-preview-box" id="bannerPreviewBox">
                            <img id="img" src="" alt="ตัวอย่างแบนเนอร์" class="d-none">
                            <div class="bn-preview-placeholder" id="bannerPlaceholder">
                                <i class="fas fa-image fa-2x mb-2"></i>
                                <div>ยังไม่ได้เลือกรูปภาพแบนเนอร์</div>
                                <small class="text-muted">แนะนำขนาด 1920 &times; 800 px</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="slide_img" class="form-label fw-semibold">
                            เลือกไฟล์ภาพแบนเนอร์ <span class="text-danger" id="imgReqMark">*</span>
                        </label>
                        <input type="file" class="form-control" name="slide_img" id="slide_img"
                            accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-muted d-none" id="imgEditHint">เว้นว่างไว้หากต้องการใช้ภาพเดิม</small>
                    </div>

                    <div class="mb-3">
                        <label for="slide_name" class="form-label fw-semibold">หัวข้อหรือชื่อแบนเนอร์</label>
                        <input type="text" class="form-control" id="slide_name" name="slide_name"
                            placeholder="เช่น โปรโมชันเสื้อที่ระลึกครบรอบ 55 ปี (ไม่บังคับ)" />
                    </div>

                    <div class="mb-3">
                        <label for="slide_detail" class="form-label fw-semibold">รายละเอียดเพิ่มเติม</label>
                        <input type="hidden" name="slide_detail" />
                        <textarea id="slide_detail"></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="slide_link" class="form-label fw-semibold">ลิงก์ปลายทางเมื่อคลิกแบนเนอร์</label>
                        <input type="text" class="form-control" id="slide_link" name="slide_link"
                            placeholder="เช่น ?page=product หรือ https://... (เว้นว่างได้)" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-success manage-slide" id="add-slide">
                    <i class="fas fa-save me-1"></i>บันทึกแบนเนอร์
                </button>
                <button type="button" class="btn btn-warning manage-slide" id="edit-slide">
                    <i class="fas fa-check me-1"></i>บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Full Image Preview Modal -->
<div class="modal fade" id="bannerZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="bannerZoomTitle">ตัวอย่างแบนเนอร์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light p-3">
                <img id="bannerZoomImg" src="" alt="Banner Preview" class="img-fluid rounded shadow-sm" style="max-height:75vh;">
            </div>
        </div>
    </div>
</div>
