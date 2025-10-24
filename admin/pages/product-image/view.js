$(document).ready(function () {

    $('#main-button').hide();

    select_image();

    function select_image() {
        var product_id = $('#product_id').val();
        $.ajax({
            type: "post",
            url: "api/product-image.php",
            data: {
                fn: "select_image",
                product_id: product_id
            },
            dataType: "json",
            success: function (response) {
                var html = '';
                $.each(response.data, function (i, v) {
                    if (v.img_main == 1) {
                        var checked = 'checked=""';
                    } else {
                        var checked = '';
                    }
                    html += '<div class="col-6 col-sm-2">';
                    html += '<label class="imagecheck mb-4">';
                    html += '<input name="img_main" type="radio" value="' + v.img_id + '" class="imagecheck-input" ' + checked + '/>';
                    html += '<figure class="imagecheck-figure">';
                    html += '<img src="../file/product/' + v.img_name + '" alt="title" class="imagecheck-image" />';
                    html += '</figure>';
                    html += '<button type="button" class="btn btn-sm btn-danger delete_img" img-id="' + v.img_id + '"><i class="fas fa-trash-alt"></i></button>';
                    html += '<a class="btn btn-sm btn-primary" href="../file/product/' + v.img_name + '" target="_blank"><i class="fas fa-eye"></i></a>';
                    html += '</label>';
                    html += '</div>';
                });
                $('.image-data').html(html);
            }
        });
    }

    $('#img_name').on('change', function () {
        var fn = 'upload_image';
        var file = this.files[0];
        var product_id = $('#product_id').val();
        if (file) {
            var formData = new FormData();
            formData.append('fn', fn);
            formData.append('img_name', file);
            formData.append('product_id', product_id);
            $.ajax({
                url: 'api/product-image.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.data === 'y') {
                        select_image();
                    } else {
                        Swal.fire(response.title, response.message, response.icon);
                    }
                },
                error: function () {
                    Swal.fire("fail", "upload fail", "error");
                }
            });
        }
    });


    $(document).on('click', '.delete_img', function () {
        var img_id = $(this).attr('img-id');
        Swal.fire({
            title: 'คุณแน่ใจใช่ไหม?',
            text: "หากลบแล้วคุณจะไม่สามารถย้อนกลับได้!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่แล้ว ลบออก!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'api/product-image.php',
                    dataType: 'json',
                    data: {
                        fn: "delete_image",
                        img_id: img_id
                    },
                    success: function (data) {
                        Swal.fire(data.title, data.message, data.icon).then(() => {
                            select_image();
                        });
                    }
                });

            }
        });
    });

    $(document).on('change', 'input[name="img_main"]', function () {
        $('#main-button').show();
    });

    $('#save-image').click(function (e) {
        e.preventDefault();
        var img_id = $('input[name="img_main"]:checked').val();
        $.ajax({
            type: "post",
            url: "api/product-image.php",
            data: {
                fn: "change_main",
                img_id: img_id,
                product_id: $('#product_id').val()
            },
            dataType: "json",
            success: function (res) {
                Swal.fire(res.title, res.message, res.icon).then(() => {
                    $('#main-button').hide();
                });
            }
        });
    });

    var dataTable = $("#productTable").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/product-image.php",
            "type": "POST",
            "data": function (d) {
                d.fn = "select_stock";
                d.product_id = $('#product_id').val();
            }
        },
        "columns": [{
                "data": function (row, type, val, meta) {
                    return meta.row + 1;
                }
            },
            {
                "data": function (row, type, val, meta) {
                    if (row.img_name) {
                        return '\
                        <a href="../file/product/' + row.img_name + '" target="_blank">\
                            <img src="../file/product/' + row.img_name + '" width="80px"> \
                        </a>';
                    } else {
                        return '';
                    }

                }
            },
            {
                "data": "variant_color"
            },
            {
                "data": function (row, type, val, meta) {
                    return '\
                        <div style="width:85px;">\
                        <button class="btn btn-outline-warning btn-sm edit_variant"><i class="fas fa-edit"></i></button>\
                        </div>\
                    ';

                },
                "bSortable": false
            }
        ]
    });

    $('#productTable tbody').on('click', '.edit_variant', function () {
        var data = dataTable.row($(this).parents('tr')).data();
        $('#variant_id').val(data.variant_id);
        $('input[name="img_id"][value="' + data.img_id + '"]').prop('checked', true);
        $('#variant_color').val(data.variant_color);
        $('#variantModal').modal('show');
    });

    $('#variantModal').on('hidden.bs.modal', function () {
        $('input[name="img_id"]').prop('checked', false);
    });

    $('#edit-variant').click(function (e) {
        e.preventDefault();
        $('#form-variant').submit();
    });

    $('#form-variant').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($('input[name="img_id"]:checked').length === 0) {
            formData.append('img_id', 0);
        }
        $.ajax({
            url: "api/product-image.php",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                Swal.fire(res.title, res.message, res.icon).then((result) => {
                    dataTable.ajax.reload();
                    $('#variantModal').modal('hide');
                });
            }
        });
    });

});