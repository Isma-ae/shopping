$(document).ready(function () {
    var $form = $('#evidence-form');
    var $orderInput = $('#order_no');
    var $fileInput = $('#evidence_slip');
    var $dropzone = $('#ev-dropzone');
    var $emptyBox = $('#ev-dropzone-empty');
    var $previewWrap = $('#ev-preview-wrap');
    var $previewImg = $('#ev-preview-img');
    var $previewName = $('#ev-preview-name');
    var $previewSize = $('#ev-preview-size');
    var $submitBtn = $('#ev-submit-btn');

    var selectedFile = null;
    var previewObjectUrl = null;
    var maxBytes = 5 * 1024 * 1024;
    var allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function clearFile() {
        selectedFile = null;
        $fileInput.val('');
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
        $previewImg.attr('src', '');
        $previewName.text('');
        $previewSize.text('');
        $previewWrap.hide();
        $emptyBox.show();
        $dropzone.removeClass('has-file');
    }

    function handleFile(file) {
        if (!file) return;

        var ext = (file.name.split('.').pop() || '').toLowerCase();
        var validExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'].indexOf(ext) !== -1;
        var validMime = !file.type || allowedTypes.indexOf(file.type) !== -1;

        if (!validExt || !validMime) {
            swal('รูปแบบไฟล์ไม่รองรับ', 'กรุณาเลือกไฟล์ภาพนามสกุล JPG, PNG, WEBP หรือ GIF เท่านั้น', 'warning');
            clearFile();
            return;
        }

        if (file.size > maxBytes) {
            swal('ไฟล์มีขนาดใหญ่เกินไป', 'กรุณาเลือกไฟล์ภาพที่มีขนาดไม่เกิน 5 MB', 'warning');
            clearFile();
            return;
        }

        selectedFile = file;
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
        }
        previewObjectUrl = URL.createObjectURL(file);
        $previewImg.attr('src', previewObjectUrl);
        $previewName.text(file.name);
        $previewSize.text(formatSize(file.size));
        $emptyBox.hide();
        $previewWrap.css('display', 'flex');
        $dropzone.addClass('has-file');
    }

    // Quick-pick pending order chip
    $(document).on('click', '.js-pick-order', function () {
        var $chip = $(this);
        var no = $chip.attr('data-order-no') || '';
        $orderInput.val(no);
        $('.js-pick-order').removeClass('is-selected');
        $chip.addClass('is-selected');
    });

    $orderInput.on('input', function () {
        var current = $.trim($(this).val());
        $('.js-pick-order').each(function () {
            var $c = $(this);
            $c.toggleClass('is-selected', ($c.attr('data-order-no') || '') === current);
        });
    });

    // Dropzone click & drag-and-drop
    $dropzone.on('click', function (e) {
        if ($dropzone.hasClass('has-file')) return;
        if ($(e.target).closest('button').length) return;
        $fileInput.trigger('click');
    });

    $('#ev-change-file').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $fileInput.trigger('click');
    });

    $('#ev-remove-file').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        clearFile();
    });

    $fileInput.on('change', function () {
        if (this.files && this.files[0]) {
            handleFile(this.files[0]);
        }
    });

    $dropzone.on('dragover dragenter', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.addClass('is-dragover');
    });

    $dropzone.on('dragleave dragend drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.removeClass('is-dragover');
    });

    $dropzone.on('drop', function (e) {
        var dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            handleFile(dt.files[0]);
        }
    });

    // Submit only #evidence-form (does NOT hijack navbar search form)
    $form.on('submit', function (e) {
        e.preventDefault();

        var orderNo = $.trim($orderInput.val());
        if (orderNo === '') {
            swal('กรุณากรอกเลขที่ใบสั่งซื้อ', 'กรุณาระบุเลขที่ใบสั่งซื้อที่ต้องการแจ้งหลักฐาน เช่น ORD2026...', 'warning').then(function () {
                $orderInput.trigger('focus');
            });
            return;
        }

        var fileToUpload = selectedFile || ($fileInput[0].files && $fileInput[0].files[0]);
        if (!fileToUpload) {
            swal('กรุณาแนบหลักฐานการโอนเงิน', 'กรุณาเลือกไฟล์ภาพสลิปการโอนเงินก่อนส่ง', 'warning');
            return;
        }

        var formData = new FormData();
        formData.append('order_no', orderNo);
        formData.append('evidence_slip', fileToUpload);

        var origHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>กำลังส่งหลักฐาน...</span>');

        $.ajax({
            url: 'api/evidence.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res && res.status === 'success') {
                    swal(
                        res.title || 'ส่งหลักฐานสำเร็จ',
                        res.message || 'หลักฐานการชำระเงินถูกส่งไปยังผู้ดูแลระบบแล้ว',
                        res.icon || 'success'
                    ).then(function () {
                        window.location.href = res.redirect_url || '?page=shipping_list';
                    });
                } else {
                    swal(
                        (res && res.title) || 'ไม่สำเร็จ',
                        (res && res.message) || 'ไม่สามารถส่งหลักฐานการชำระเงินได้',
                        (res && res.icon) || 'error'
                    );
                }
            },
            error: function () {
                swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง', 'error');
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html(origHtml);
            }
        });
    });
});
