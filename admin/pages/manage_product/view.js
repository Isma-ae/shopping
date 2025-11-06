$(document).ready(function () {
    var dataTable = $("#add-row").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/products.php",
            "type": "POST",
            "data": function (d) {
                d.fn = "select_product";
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
                            <img src="../file/product/' + row.img_name + '" width="100px"> \
                        </a>\
                        ' + row.product_name + '';
                    } else {
                        return row.product_name;
                    }

                }
            },
            {
                "data": "category_name"
            },
            {
                "data": "remaining_stock"
            },
            {
                "data": function (row, type, val, meta) {
                    if (row.product_status == 1) {

                        return '<div class="form-group has-error">\
                                    <select class="form-select form-control product_status">\
                                        <option value="1" selected>รอวางจำหน่าย</option>\
                                        <option value="2" style="color:green">วางจำหน่าย</option>\
                                    </select>\
                                </div>';
                    } else {
                        return '<div class="form-group has-success">\
                                    <select class="form-select form-control product_status">\
                                        <option value="1" style="color:red">รอวางจำหน่าย</option>\
                                        <option value="2" selected>วางจำหน่าย</option>\
                                    </select>\
                                </div>';
                    }

                }
            },
            {
                "data": function (row, type, val, meta) {
                    return '<a href="?page=product-image&product_id=' + row.product_id + '" class="btn btn-outline-success btn-sm"><i class="fas fa-images"></i></a>';
                },
                "bSortable": false
            }
        ]
    });

    tinymce.init({
        selector: 'textarea#product_detail',
        height: 300,
        promotion: false,
        statusbar: false
    });

    $('#add-row tbody').on('change', '.product_status', function () {
        var data = dataTable.row($(this).parents('tr')).data();
        var product_id = data.product_id;
        var status_id = $(this).val();
        $.ajax({
            type: "post",
            url: "api/products.php",
            data: {
                fn: "change_status",
                product_id: product_id,
                status_id: status_id
            },
            dataType: "json",
            success: function (res) {
                if (res.status == 'success') {
                    dataTable.ajax.reload();
                }
            }
        });
    });



    $('#add-row tbody').on('click', '.edit_product', function () {
        var data = dataTable.row($(this).parents('tr')).data();
        $('#fn').val('edit_product');
        $('#product_id').val(data.product_id);
        $('#product_name').val(data.product_name);
        $('#category_id').val(data.category_id);
        tinymce.get("product_detail").setContent(data.product_detail);
        $('[name="product_detail"]').val(data.product_detail);
        $('[name="reserve_id"][value="' + data.reserve_id + '"]').prop('checked', true);
        $('#add-product').hide();
        $('#edit-product').show();
        $('#productModal').modal('show');
    });

    $('#edit-product').click(function (e) {
        e.preventDefault();
        var product_detail = tinymce.get("product_detail").getContent();
        $('[name="product_detail"]').val(product_detail);
        $('#form-product').submit();
    });

    $('#form-product').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "api/products.php",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                Swal.fire(res.title, res.message, res.icon).then((result) => {
                    dataTable.ajax.reload();
                    $('#productModal').modal('hide');
                });
            }
        });
    });

});