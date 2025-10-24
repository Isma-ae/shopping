$(document).ready(function () {

    tinymce.init({
        selector: 'textarea#slide_detail',
        height: 300,
        promotion: false,
        statusbar: false
    });


    select_slide();

    function select_slide() {
        $.ajax({
            type: "post",
            url: "api/slide.php",
            data: {
                fn: "load_slide",
            },
            success: function (r) {
                $(".slide-data").html(r);
                $(".slide-data").find('.edit-slide').click(function (event) {
                    event.preventDefault();
                    var data_json = JSON.parse($(this).closest('.data-slide').attr("data-json"));
                    edit_slide(data_json);
                });

                $(".slide-data").find('.del-slide').click(function (event) {
                    event.preventDefault();
                    var data_json = JSON.parse($(this).closest('.data-slide').attr("data-json"));
                    delete_slide(data_json);
                });
            },
        });
    }

    $("#add").click(function (e) {
        e.preventDefault();
        $('#fn').val('add_slide');
        $('#slide_id').val('');
        $('#img').attr('src', '');
        $('#slide_img').val('');
        $('#slide_name').val('');
        tinymce.get("slide_detail").setContent('');
        $('#slide_link').val('');
        $('#add-slide').show();
        $('#edit-slide').hide();
        $('#slideModal').modal('show');
    });

    function edit_slide(data) {
        $('#fn').val('edit_slide');
        $('#slide_id').val(data.slide_id);
        $('#img').attr('src', '../file/banner/' + data.slide_img);
        $('#slide_img').val('');
        $('#slide_name').val(data.slide_name);
        tinymce.get("slide_detail").setContent(data.slide_detail);
        $('#slide_link').val(data.slide_link);
        $('#add-slide').hide();
        $('#edit-slide').show();
        $('#slideModal').modal('show');
    }

    $('.manage-slide').click(function (e) {
        e.preventDefault();
        var slide_detail = tinymce.get("slide_detail").getContent();
        $('[name="slide_detail"]').val(slide_detail);
        $('#form-slide').submit();
    });

    $('#form-slide').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "api/slide.php",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                if (data == "t") {
                    Swal.fire({
                        icon: 'success',
                        type: 'success',
                        title: 'สำเร็จ'
                    }).then((result) => {
                        $('#slideModal').modal('hide');
                        select_slide();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        type: 'error',
                        title: 'ไม่สำเร็จ',
                        text: data
                    })
                }
            }
        });
    });

    function delete_slide(data) {
        Swal.fire({
            title: 'คุณแน่ใจไหม?',
            text: "คุณจะไม่สามารถกู้รูปเดิมกลับได้อีก!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบสไลด์!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                var slide_id = data.slide_id;
                $.ajax({
                    url: "api/slide.php",
                    method: "POST",
                    data: {
                        fn: "delete_slide",
                        slide_id: slide_id
                    },
                    success: function (data) {

                        if (data == "t") {
                            Swal.fire({
                                title: "สำเร็จ",
                                text: "ลบสไลด์เรียบร้อย",
                                icon: 'success',
                                type: "success"
                            }).then(function () {
                                select_slide();
                            });
                        } else {
                            Swal.fire({
                                title: "ไม่สำเร็จ",
                                text: "ลบไม่สำเร็จ",
                                type: "error"
                            });
                        }
                    }

                })
            }

        })
    }
});