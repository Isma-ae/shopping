$(document).ready(function () {
    var dataTable = $("#productTable").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/product-stock.php",
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
                data: "product_api_id"
            },
            {
                "data": function (row, type, val, meta) {
                    if (row.img_name) {
                        return '\
                        <a href="../file/product/' + row.img_name + '" target="_blank">\
                            <img src="../file/product/' + row.img_name + '" width="80px"> \
                        </a>';
                    } else {
                        return '\
                        <a href="' + row.variant_img + '" target="_blank">\
                            <img src="' + row.variant_img + '" width="80px"> \
                        </a>';
                    }

                }
            },
            {
                "data": "variant_style"
            },
            {
                "data": "variant_color"
            },
            {
                "data": function (row, type, val, meta) {
                    return row.variant_size + ' (' + row.size_detail + ')';
                }
            },
            {
                "data": "variant_price"
            },
            {
                "data": "variant_sale"
            },
            {
                "data": "remaining_stock"
            },
            {
                "data": function (row, type, val, meta) {
                    return '\
                        <div style="width:85px;">\
                        <button class="btn btn-outline-warning btn-sm edit_variant"><i class="fas fa-edit"></i></button>\
                        <button class="btn btn-outline-danger btn-sm delete_variant"><i class="fas fa-trash"></i></button>\
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
        $('#color_img').val('');
        $('#variant_style').val(data.variant_style);
        $('#variant_color').val(data.variant_color);
        $('#variant_size').val(data.variant_size);
        $('#size_detail').val(data.size_detail);
        $('#variant_price').val(data.variant_price);
        $('#variant_sale').val(data.variant_sale);
        $('#variant_stock').val(data.variant_stock);
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
            url: "api/product-stock.php",
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

    $('#productTable tbody').on('click', '.delete_variant', function () {
        var data = dataTable.row($(this).parents('tr')).data();
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
                    url: 'api/product-stock.php',
                    dataType: 'json',
                    data: {
                        fn: "delete_variant",
                        variant_id: data.variant_id
                    },
                    success: function (res) {
                        Swal.fire(res.title, res.message, res.icon).then((result) => {
                            dataTable.ajax.reload();
                        });
                    }
                });

            }
        })
    });
});