<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">ประเภท</h3>
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
                <a href="#">แบนเนอร์</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">แบนเนอร์</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="?page=product-add" id="add">
                            <i class="fa fa-plus"></i>
                            เพิ่มแบนเนอร์
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row slide-data"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="slideModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fw-mediumbold" id="slideModalLabel"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-slide">
                    <input type="hidden" id="fn" name="fn" />
                    <input type="hidden" id="slide_id" name="slide_id" />
                    <div class="text-center">
                        <img id="img" src="assets/img/image.png" alt="slide image" class="img-thumbnail"
                                style="width: 300px;" onerror="errorImage(this,'assets/img/image.png')">
                    </div>
                    <br>
                    <div class="mb-3">
                        <label for="slide_img" class="form-label">ภาพสไลด์</label>
                        <input type="file" class="form-control form-control-user" name="slide_img" id="slide_img" 
                            accept="img/*" onchange="imgf_change(this,'#img','assets/img/image.png')">
                    </div>
                    <div class="mb-3">
                        <label for="slide_name" class="form-label">หัวข้อ หรือชื่อเรื่อง</label>
                        <input type="text" class="form-control" id="slide_name" name="slide_name"
                            placeholder="กรุณากรอกหัวข้อ..." />
                    </div>
                    <div class="mb-3">
                        <label for="slide_detail" class="form-label">รายละเอียด</label>
                        <input type="hidden" name="slide_detail" />
                        <textarea id="slide_detail"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="slide_link" class="form-label">ลิงค์ (กรณีให้ไปหน้าอื่นเมื่อกดสไลด์)</label>
                        <input type="text" class="form-control" id="slide_link" name="slide_link"
                            placeholder="กรุณากรอกลิงค์..." />
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-success manage-slide" id="add-slide">
                    บันทึก
                </button>
                <button type="button" class="btn btn-warning manage-slide" id="edit-slide">
                    แก้ไข
                </button>
            </div>
        </div>
    </div>
</div>
