$(document).ready(function () {

    tinymce.init({
        selector: 'textarea#product_detail',
        height: 300,
        promotion: false,
        statusbar: false
    });

    let selectedVariants = new Set();

    var table = $('#shirtTable').DataTable({
        ajax: {
            url: "api/api_proxy.php", // ดึงข้อมูลจาก PHP proxy
            type: "POST", // ใช้ POST
            dataSrc: function (json) {
                if (json.status === "success") {
                    return json.data;
                } else {
                    return []; // ถ้า error ให้ return array ว่าง
                }
            }
        },
        columns: [{
                data: null,
                render: function (data, type, row) {
                    const checked = selectedVariants.has(String(row.id)) ? "checked" : "";
                    return '<div class="form-check">\
                                <input class="form-check-input variant-check" type="checkbox" value="' + row.id + '">\
                            </div>';
                },
                orderable: false
            },
            {
                data: "id"
            },
            {
                data: function (row, type, val, meta) {
                    if (row.picture) {
                        return '<img src="' + row.picture + '" width="100px"> ' + row.name_product;
                    } else {
                        return row.name_product;
                    }

                }
            },
            {
                data: "detail"
            },
            {
                data: "style_shirt"
            },
            {
                data: "color_shirt"
            },
            {
                data: "size_shirt"
            },
            {
                data: "price"
            },
            {
                data: "price_sale"
            },
            {
                data: "number_stock"
            }
        ],
        pageLength: 10,
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        }
    });

    table.on('draw', function () {
        // Restore checkbox ที่เคยเลือก
        $('#shirtTable .variant-check').each(function () {
            const id = $(this).val();
            if (selectedVariants.has(id)) {
                $(this).prop('checked', true);
            }
        });
    });

    $('#shirtTable').on('change', '.variant-check', function () {
        const id = $(this).val();
        if ($(this).is(':checked')) {
            selectedVariants.add(id);
        } else {
            selectedVariants.delete(id);
        }
    });

    $('#btnSaveGroup').on('click', function () {
        let selected = getSelectedVariants();
        if (selected.length === 0) {
            alert("กรุณาเลือกสินค้าที่ต้องการจัดกลุ่มก่อน");
            return;
        }
        window.selectedVariants = selected;
        $('#img').attr('src', 'assets/img/image.png');
        $('#img_name').val('');
        $('#product_name').val('');
        $('#category_id').val(0);
        tinymce.get("product_detail").setContent('');
        $('[name="product_detail"]').val('');
        $('#productModal').modal('show');
    });

    function getSelectedVariants() {
        let selectedRows = [];
        const allData = $('#shirtTable').DataTable().rows().data();

        allData.each(function (row) {
            if (selectedVariants.has(String(row.id))) {
                selectedRows.push(row);
            }
        });

        return selectedRows;
    }

    $('#add-product').click(function (e) {
        e.preventDefault();
        var product_detail = tinymce.get("product_detail").getContent();
        $('[name="product_detail"]').val(product_detail);
        $('#form-product').submit();
    });

    $('#form-product').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append("variants", JSON.stringify(getSelectedVariants()));

        $.ajax({
            url: "api/save_group.php",
            type: "POST",
            data: formData,
            processData: false, // สำคัญ
            contentType: false, // สำคัญ
            dataType: "json",
            success: function (res) {
                if (res.data == 'y') {
                    Swal.fire(res.title, res.message, res.icon).then((result) => {
                        window.location.href = '?page=products';
                    });
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            }
        });
    });
});