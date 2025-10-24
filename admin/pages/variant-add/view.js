$(document).ready(function () {

    tinymce.init({
        selector: 'textarea#product_detail',
        height: 300,
        promotion: false,
        statusbar: false
    });

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
                    return '<div class="form-check">\
                                <label class="colorinput">\
                                    <input name="color" type="checkbox" value="' + row.id + '" class="colorinput-input variant-check"/>\
                                    <span class="colorinput-color bg-info"></span>\
                                </label>\
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

    // ตัวแปรเก็บรายการที่เลือกไว้ทั้งหมด
    let selectedRows = new Set();

    // เมื่อคลิก checkbox
    $('#shirtTable').on('change', '.variant-check', function () {
        let row = $('#shirtTable').DataTable().row($(this).closest('tr')).data();
        let id = row.id; // ควรมีคีย์ unique เช่น variant_id หรือ id

        if ($(this).is(':checked')) {
            selectedRows.add(id);
        } else {
            selectedRows.delete(id);
        }
    });

    // เมื่อเปลี่ยนหน้า (paginate)
    $('#shirtTable').on('draw.dt', function () {
        let table = $('#shirtTable').DataTable();
        table.rows().every(function () {
            let data = this.data();
            let checkbox = $(this.node()).find('.variant-check');
            if (selectedRows.has(data.id)) {
                checkbox.prop('checked', true);
            } else {
                checkbox.prop('checked', false);
            }
        });
    });

    // ฟังก์ชันดึงรายการที่เลือกไว้จริง ๆ
    function getSelectedVariants() {
        let table = $('#shirtTable').DataTable();
        let variants = [];

        table.rows().every(function () {
            let data = this.data();
            if (selectedRows.has(data.id)) {
                variants.push(data);
            }
        });

        return variants;
    }

    // เมื่อกดปุ่มเลือก
    $('#choose-variant').click(function (e) {
        e.preventDefault();

        let selected = getSelectedVariants();
        if (selected.length === 0) {
            Swal.fire("แจ้งเตือน", "กรุณาเลือกสินค้าที่ต้องการก่อน", "warning");
            return;
        }

        window.selectedVariants = selected;
        var product_id = $('#product_id').val();

        $.ajax({
            type: "post",
            url: "api/variant_add.php",
            data: {
                product_id: product_id,
                variants: JSON.stringify(selected)
            },
            dataType: "json",
            success: function (res) {
                Swal.fire(res.title, res.message, res.icon).then(() => {
                    if (res.data === 'y') {
                        window.location.href = "?page=product-stock&product_id=" + product_id;
                    }
                });
            }
        });
    });

});