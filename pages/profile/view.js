$(function () {
    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    $('.js-example-basic-single').select2({
        dropdownParent: $('#addModal'),
        width: '100%'
    });

    // ------------------------------------------------------------------
    // 1. Update Personal Info
    // ------------------------------------------------------------------
    $('#form_profile').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn_save_profile');
        var name = $.trim($('#profile_name').val());
        var phone = $.trim($('#profile_phone').val());

        if (name === '') {
            swal('กรุณากรอกชื่อ-นามสกุล', 'ชื่อ-นามสกุลไม่สามารถเว้นว่างได้', 'warning');
            $('#profile_name').trigger('focus');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>กำลังบันทึก...</span>');

        $.ajax({
            type: 'post',
            url: 'api/profile.php',
            data: {
                fn: 'update_profile',
                name: name,
                user_phone: phone
            },
            dataType: 'json',
            success: function (res) {
                swal(res.title, res.message, res.icon);
                if (res.status === 'success' && res.user) {
                    var newName = res.user.name || '';
                    var newPhone = res.user.user_phone || '';
                    $('#profileHeroName').text(newName);
                    $('#profileAvatarChar').text(newName ? newName.charAt(0).toUpperCase() : 'U');
                    $('#profileSidebarPhone').text(newPhone !== '' ? newPhone : 'ยังไม่ระบุ');
                    // Update name in desktop/mobile topbar immediately
                    $('#profileDesk span').text(newName);
                    $('.oar-topbar-m-user span').text(newName);
                }
            },
            error: function () {
                swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-check mr-2" aria-hidden="true"></i><span>บันทึกข้อมูลส่วนตัว</span>');
            }
        });
    });

    // ------------------------------------------------------------------
    // 2. Change Password (for local registered accounts)
    // ------------------------------------------------------------------
    $('#form_password').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn_change_password');
        var oldPass = $('#old_password').val();
        var newPass = $('#new_password').val();
        var confirmPass = $('#confirm_password').val();

        if (!oldPass || !newPass || !confirmPass) {
            swal('กรุณากรอกข้อมูลให้ครบ', 'กรุณากรอกรหัสผ่านเดิมและรหัสผ่านใหม่ให้ครบถ้วน', 'warning');
            return;
        }
        if (newPass.length < 6) {
            swal('รหัสผ่านสั้นเกินไป', 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'warning');
            return;
        }
        if (newPass !== confirmPass) {
            swal('รหัสผ่านไม่ตรงกัน', 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน', 'warning');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>กำลังบันทึก...</span>');

        $.ajax({
            type: 'post',
            url: 'api/profile.php',
            data: {
                fn: 'change_password',
                old_password: oldPass,
                new_password: newPass,
                confirm_password: confirmPass
            },
            dataType: 'json',
            success: function (res) {
                swal(res.title, res.message, res.icon);
                if (res.status === 'success') {
                    $('#form_password').trigger('reset');
                }
            },
            error: function () {
                swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-key mr-2" aria-hidden="true"></i><span>อัปเดตรหัสผ่าน</span>');
            }
        });
    });

    // ------------------------------------------------------------------
    // 3. Shipping Address Book (synced with Checkout via api/address.php)
    // ------------------------------------------------------------------
    function select_address() {
        $.ajax({
            type: 'post',
            url: 'api/address.php',
            data: { fn: 'select_address' },
            dataType: 'json',
            success: function (res) {
                if (res.data === 'f') {
                    window.location.href = './login/';
                    return;
                }
                var rows = Array.isArray(res.data) ? res.data : [];
                var $count = $('#profileAddrCount');

                if (rows.length === 0) {
                    $count.addClass('d-none');
                    var emptyHtml =
                        '<div class="profile-addr-empty">' +
                            '<div class="profile-addr-empty-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></div>' +
                            '<h4>ยังไม่มีที่อยู่จัดส่งที่บันทึกไว้</h4>' +
                            '<p>เพิ่มที่อยู่จัดส่งไว้ล่วงหน้า เพื่อให้เลือกใช้ได้ทันทีในหน้าชำระเงิน</p>' +
                            '<button type="button" class="profile-btn-add-addr add-address">' +
                                '<i class="fa fa-plus mr-2" aria-hidden="true"></i>เพิ่มที่อยู่จัดส่งแรกของคุณ' +
                            '</button>' +
                        '</div>';
                    $('#profileAddressList').html(emptyHtml);
                    return;
                }

                $count.removeClass('d-none').text(rows.length);
                var html = '<div class="profile-addr-list">';
                $.each(rows, function (i, v) {
                    var isPrimary = (i === 0);
                    html += '<article class="profile-addr-item"' +
                        ' data-id="' + escapeHtml(v.id) + '"' +
                        ' data-name="' + escapeHtml(v.name) + '"' +
                        ' data-phone="' + escapeHtml(v.phone) + '"' +
                        ' data-address="' + escapeHtml(v.address_at) + '"' +
                        ' data-province="' + escapeHtml(v.province_id) + '"' +
                        ' data-district="' + escapeHtml(v.district_id) + '"' +
                        ' data-subdistrict="' + escapeHtml(v.subdistrict_id) + '"' +
                        ' data-zipcode="' + escapeHtml(v.zip_code) + '">';
                    html += '  <div class="profile-addr-main">';
                    html += '    <div class="profile-addr-pin"><i class="fa fa-map-marker" aria-hidden="true"></i></div>';
                    html += '    <div>';
                    html += '      <div class="profile-addr-head">';
                    html += '        <strong>' + escapeHtml(v.name) + '</strong>';
                    html += '        <span class="profile-addr-phone"><i class="fa fa-phone mr-1" aria-hidden="true"></i>' + escapeHtml(v.phone) + '</span>';
                    if (isPrimary) {
                        html += '        <span class="profile-addr-default-tag">ค่าเริ่มต้นในหน้าชำระเงิน</span>';
                    }
                    html += '      </div>';
                    html += '      <p class="profile-addr-text">' +
                        escapeHtml(v.address_at) +
                        ' ตำบล' + escapeHtml(v.subdistrict_name_in_thai) +
                        ' อำเภอ' + escapeHtml(v.district_name_in_thai) +
                        ' จังหวัด' + escapeHtml(v.province_name_in_thai) +
                        ' ' + escapeHtml(v.zip_code) +
                        '</p>';
                    html += '    </div>';
                    html += '  </div>';
                    html += '  <div class="profile-addr-actions">';
                    html += '    <button type="button" class="profile-addr-btn edit-address"><i class="fa fa-pencil mr-1" aria-hidden="true"></i>แก้ไข</button>';
                    html += '    <button type="button" class="profile-addr-btn profile-addr-btn--delete delete-address" data-id="' + escapeHtml(v.id) + '"><i class="fa fa-trash-o mr-1" aria-hidden="true"></i>ลบ</button>';
                    html += '  </div>';
                    html += '</article>';
                });
                html += '</div>';

                $('#profileAddressList').html(html);
            },
            error: function () {
                $('#profileAddressList').html('<p class="text-danger text-center py-4 mb-0">ไม่สามารถโหลดรายการที่อยู่ได้</p>');
            }
        });
    }

    function resetAddressForm() {
        var $form = $('#form_address');
        $form.trigger('reset').attr('data-mode', 'add');
        $('#address_id').val('');
        $('#province_id').val('').trigger('change.select2');
        $('#district_id').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', true).trigger('change.select2');
        $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
        $('#zip_code').val('');
    }

    $(document).on('click', '.add-address', function (e) {
        e.preventDefault();
        resetAddressForm();
        // Pre-fill receiver name & phone from profile for convenience
        $('#name').val($.trim($('#profile_name').val()));
        $('#phone').val($.trim($('#profile_phone').val()));
        $('#addModalLabel').text('เพิ่มที่อยู่จัดส่งใหม่');
        $('#btn_submit_address').text('บันทึกที่อยู่');
        $('#addModal').modal('show');
    });

    $(document).on('click', '.edit-address', function (e) {
        e.preventDefault();
        var $card = $(this).closest('.profile-addr-item');

        var data = {
            id: $card.attr('data-id'),
            name: $card.attr('data-name'),
            phone: $card.attr('data-phone'),
            address: $card.attr('data-address'),
            province: $card.attr('data-province'),
            district: $card.attr('data-district'),
            subdistrict: $card.attr('data-subdistrict'),
            zipcode: $card.attr('data-zipcode')
        };

        $('#address_id').val(data.id);
        $('#name').val(data.name);
        $('#phone').val(data.phone);
        $('#address_at').val(data.address);
        $('#zip_code').val(data.zipcode);

        $('#province_id').val(data.province).trigger('change.select2');
        select_districts(data.province, data.district);
        select_subdistricts(data.district, data.subdistrict);

        $('#addModalLabel').text('แก้ไขที่อยู่จัดส่ง');
        $('#btn_submit_address').text('บันทึกการแก้ไข');
        $('#form_address').attr('data-mode', 'edit');
        $('#addModal').modal('show');
    });

    function select_provinces() {
        $.ajax({
            type: 'post',
            url: 'api/address.php',
            data: { fn: 'select_provinces' },
            dataType: 'json',
            success: function (res) {
                var html = '<option value="">-- เลือกจังหวัด --</option>';
                $.each(res.data || [], function (i, v) {
                    html += '<option value="' + v.province_id + '">' + escapeHtml(v.province_name_in_thai) + '</option>';
                });
                $('#province_id').html(html);
                $('#district_id').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', true);
                $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true);
                $('#zip_code').val('');
                $('#province_id').select2({
                    dropdownParent: $('#addModal'),
                    width: '100%'
                });
            }
        });
    }

    function select_districts(province_id, selected_id) {
        $.ajax({
            type: 'post',
            url: 'api/address.php',
            data: {
                fn: 'select_districts',
                province_id: province_id
            },
            dataType: 'json',
            success: function (res) {
                var html = '<option value="">-- เลือกอำเภอ --</option>';
                $.each(res.data || [], function (i, v) {
                    var selected = (selected_id && String(selected_id) === String(v.district_id)) ? 'selected' : '';
                    html += '<option value="' + v.district_id + '" ' + selected + '>' + escapeHtml(v.district_name_in_thai) + '</option>';
                });
                $('#district_id').html(html).prop('disabled', false).trigger('change.select2');
            }
        });
    }

    function select_subdistricts(district_id, selected_id) {
        $.ajax({
            type: 'post',
            url: 'api/address.php',
            data: {
                fn: 'select_subdistricts',
                district_id: district_id
            },
            dataType: 'json',
            success: function (res) {
                var html = '<option value="">-- เลือกตำบล --</option>';
                $.each(res.data || [], function (i, v) {
                    var selected = (selected_id && String(selected_id) === String(v.subdistrict_id)) ? 'selected' : '';
                    html += '<option value="' + v.subdistrict_id + '" ' + selected + '>' + escapeHtml(v.subdistrict_name_in_thai) + '</option>';
                });
                $('#subdistrict_id').html(html).prop('disabled', false).trigger('change.select2');
            }
        });
    }

    function select_zip_code(subdistrict_id) {
        $.ajax({
            type: 'post',
            url: 'api/address.php',
            data: {
                fn: 'select_zip_code',
                subdistrict_id: subdistrict_id
            },
            dataType: 'json',
            success: function (res) {
                if (res.data && res.data.length > 0) {
                    $('#zip_code').val(res.data[0].zip_code);
                } else {
                    $('#zip_code').val('');
                }
            }
        });
    }

    $('#province_id').on('change', function () {
        var province_id = $(this).val();
        if (province_id) {
            $('#district_id').prop('disabled', false);
            select_districts(province_id);
            $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
            $('#zip_code').val('');
        } else {
            $('#district_id').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', true).trigger('change.select2');
            $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
            $('#zip_code').val('');
        }
    });

    $('#district_id').on('change', function () {
        var district_id = $(this).val();
        if (district_id) {
            $('#subdistrict_id').prop('disabled', false);
            select_subdistricts(district_id);
            $('#zip_code').val('');
        } else {
            $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
            $('#zip_code').val('');
        }
    });

    $('#subdistrict_id').on('change', function () {
        var subdistrict_id = $(this).val();
        if (subdistrict_id) {
            select_zip_code(subdistrict_id);
        } else {
            $('#zip_code').val('');
        }
    });

    $('#btn_submit_address').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $form = $('#form_address');
        var mode = $form.attr('data-mode');
        var fn = (mode === 'edit') ? 'update_address' : 'insert_address';

        var shipping_name = $.trim($('#name').val());
        var shipping_phone = $.trim($('#phone').val());
        var address_at = $.trim($('#address_at').val());
        var subdistrict_id = $('#subdistrict_id').val();
        var district_id = $('#district_id').val();
        var province_id = $('#province_id').val();
        var zip_code = $.trim($('#zip_code').val());

        if (
            shipping_name === '' ||
            shipping_phone === '' ||
            address_at === '' ||
            !subdistrict_id ||
            !district_id ||
            !province_id ||
            zip_code === ''
        ) {
            swal({
                title: 'กรุณากรอกข้อมูลให้ครบ',
                text: 'กรุณากรอกชื่อ เบอร์โทร ที่อยู่ และเลือกจังหวัด อำเภอ ตำบล ให้ครบก่อนบันทึก',
                icon: 'warning'
            });
            return;
        }

        $btn.prop('disabled', true);
        $.ajax({
            type: 'post',
            url: 'api/address.php',
            data: $form.serialize() + '&fn=' + fn,
            dataType: 'json',
            success: function (res) {
                swal({
                    title: res.title,
                    text: res.message,
                    icon: res.icon,
                    button: 'ตกลง'
                }).then(function () {
                    if (res.status === 'success') {
                        $('#addModal').modal('hide');
                        resetAddressForm();
                        select_address();
                    }
                });
            },
            error: function () {
                swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.delete-address', function (e) {
        e.preventDefault();
        var address_id = $(this).data('id');

        swal({
            title: 'ยืนยันการลบที่อยู่?',
            text: 'คุณต้องการลบที่อยู่จัดส่งนี้ใช่หรือไม่',
            icon: 'warning',
            buttons: ['ยกเลิก', 'ลบเลย'],
            dangerMode: true
        }).then(function (willDelete) {
            if (!willDelete) return;
            $.ajax({
                type: 'post',
                url: 'api/address.php',
                data: {
                    fn: 'delete_address',
                    address_id: address_id
                },
                dataType: 'json',
                success: function (res) {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        timer: 1400,
                        buttons: false
                    });
                    select_address();
                },
                error: function () {
                    swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        });
    });

    select_provinces();
    select_address();
});
