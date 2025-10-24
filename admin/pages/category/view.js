$(document).ready(function () {
    var dataTable = $("#category-table").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/category.php",
            "type": "POST",
            "data": function (d) {
                d.fn = "select_category";
            }
        },
        "columns": [{
                "data": function (row, type, val, meta) {
                    return meta.row + 1;
                }
            },
            {
                "data": "category_name"
            },
            {
                "data": function (row, type, val, meta) {
                    return '\
                        <button class="btn btn-outline-warning btn-sm edit_category"><i class="fas fa-edit"></i></button>\
                        <button class="btn btn-outline-danger btn-sm delete_category"><i class="fas fa-trash"></i></button>\
                    ';

                },
                "bSortable": false
            }
        ]
    });

    $('#addCategory').click(function (e) {
        e.preventDefault();
        $('.category-label').text('เพิ่มประเภทสินค้า');
        $('#fn').val('add_category');
        $('#category_id').val('');
        $('#category_name').val('');
        $('#add-category').show();
        $('#edit-category').hide();
        $('#categoryModal').modal('show');
    });


    $('#category-table tbody').on('click', '.edit_category', function () {
        var data = dataTable.row($(this).parents('tr')).data();
        $('.category-label').text('แก้ไขประเภทสินค้า');
        $('#fn').val('edit_category');
        $('#category_id').val(data.category_id);
        $('#category_name').val(data.category_name);
        $('#add-category').hide();
        $('#edit-category').show();
        $('#categoryModal').modal('show');
    });

    $('#add-category,#edit-category').click(function (e) {
        e.preventDefault();
        $('#form-category').submit();
    });

    $('#form-category').submit(function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "api/category.php",
            data: $(this).serialize(), // เก็บข้อมูลทั้งหมดในฟอร์ม
            dataType: "json",
            success: function (res) {
                if (res.status == "success") {
                    Swal.fire(res.title, res.message, res.icon).then((result) => {
                        dataTable.ajax.reload();
                        $('#categoryModal').modal('hide');
                    });
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            },
            error: function (xhr, status, error) {
                console.error("เกิดข้อผิดพลาด:", error);
            }
        });
    });

    $('#category-table tbody').on('click', '.delete_category', function () {
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
                    url: 'api/category.php',
                    dataType: 'json',
                    data: {
                        fn: "delete_category",
                        category_id: data.category_id
                    },
                    success: function (data) {
                        Swal.fire(data.title, data.message, data.icon).then(() => {
                            dataTable.ajax.reload();
                        });
                    }
                });

            }
        });
    });

    //----------------------------------บริษัทขนส่ง------------------------------------//


    var dataTable2 = $("#shipping-table").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/category.php",
            "type": "POST",
            "data": function (d) {
                d.fn = "select_shipping";
            }
        },
        "columns": [{
                "data": function (row, type, val, meta) {
                    return meta.row + 1;
                }
            },
            {
                "data": "transported_name"
            },
            {
                "data": function (row, type, val, meta) {
                    return '\
                        <button class="btn btn-outline-warning btn-sm edit_shipping"><i class="fas fa-edit"></i></button>\
                        <button class="btn btn-outline-danger btn-sm delete_shipping"><i class="fas fa-trash"></i></button>\
                    ';

                },
                "bSortable": false
            }
        ]
    });

    $('#addShipping').click(function (e) {
        e.preventDefault();
        $('.shipping-label').text('เพิ่มบริษัทขนส่ง');
        $('#fn_shipping').val('add_shipping');
        $('#transported_id').val('');
        $('#transported_name').val('');
        $('#add-shipping').show();
        $('#edit-shipping').hide();
        $('#shippingModal').modal('show');
    });


    $('#shipping-table tbody').on('click', '.edit_shipping', function () {
        var data = dataTable2.row($(this).parents('tr')).data();
        $('.shipping-label').text('แก้ไขบริษัทขนส่ง');
        $('#fn_shipping').val('edit_shipping');
        $('#transported_id').val(data.transported_id);
        $('#transported_name').val(data.transported_name);
        $('#add-shipping').hide();
        $('#edit-shipping').show();
        $('#shippingModal').modal('show');
    });

    $('#add-shipping,#edit-shipping').click(function (e) {
        e.preventDefault();
        $('#form-shipping').submit();
    });

    $('#form-shipping').submit(function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "api/category.php",
            data: $(this).serialize(), // เก็บข้อมูลทั้งหมดในฟอร์ม
            dataType: "json",
            success: function (res) {
                if (res.status == "success") {
                    Swal.fire(res.title, res.message, res.icon).then((result) => {
                        dataTable2.ajax.reload();
                        $('#shippingModal').modal('hide');
                    });
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            },
            error: function (xhr, status, error) {
                console.error("เกิดข้อผิดพลาด:", error);
            }
        });
    });

    $('#shipping-table tbody').on('click', '.delete_shipping', function () {
        var data = dataTable2.row($(this).parents('tr')).data();
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
                    url: 'api/category.php',
                    dataType: 'json',
                    data: {
                        fn: "delete_shipping",
                        transported_id: data.transported_id
                    },
                    success: function (data) {
                        Swal.fire(data.title, data.message, data.icon).then(() => {
                            dataTable2.ajax.reload();
                        });
                    }
                });

            }
        });
    });
});