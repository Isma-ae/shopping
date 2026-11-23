$(document).ready(function () {
    var $form = $('#search-order-form');
    var $input = $('#order-no-input');
    var $clearBtn = $('#clear-order-no');
    var $submitBtn = $('#search-order');
    var $orderList = $('#order-list');
    var lastSearchedOrderNo = '';

    function escHtml(str) {
        return String(str === null || str === undefined ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatBaht(amount) {
        var num = parseFloat(amount);
        if (isNaN(num)) num = 0;
        var hasFraction = Math.abs(num - Math.round(num)) > 0.001;
        return '฿' + num.toLocaleString('th-TH', {
            minimumFractionDigits: hasFraction ? 2 : 0,
            maximumFractionDigits: hasFraction ? 2 : 0
        });
    }

    function toggleClearButton() {
        if ($.trim($input.val()) !== '') {
            $clearBtn.addClass('is-visible');
        } else {
            $clearBtn.removeClass('is-visible');
        }
    }

    function renderInitialState() {
        $orderList.html(
            '<div class="so-state-card">' +
                '<div class="so-state-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></div>' +
                '<h3 class="so-state-title">กรอกเลขที่ใบสั่งซื้อเพื่อเริ่มค้นหา</h3>' +
                '<p class="so-state-desc">ตรวจสอบรายละเอียดสินค้า สถานะการชำระเงิน ช่องทางการรับสินค้า และเลขพัสดุได้จากที่นี่</p>' +
            '</div>'
        );
    }

    function renderLoadingState() {
        $orderList.html(
            '<div class="so-state-card">' +
                '<div class="so-state-icon"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>' +
                '<h3 class="so-state-title">กำลังค้นหาคำสั่งซื้อ...</h3>' +
                '<p class="so-state-desc">กรุณารอสักครู่ ระบบกำลังดึงข้อมูลล่าสุดของใบสั่งซื้อของคุณ</p>' +
            '</div>'
        );
    }

    function renderNotFoundState(query) {
        $orderList.html(
            '<div class="so-state-card">' +
                '<div class="so-state-icon is-warn"><i class="fa fa-search" aria-hidden="true"></i></div>' +
                '<h3 class="so-state-title">ไม่พบคำสั่งซื้อหมายเลข "' + escHtml(query) + '"</h3>' +
                '<p class="so-state-desc">กรุณาตรวจสอบความถูกต้องของเลขที่ใบสั่งซื้ออีกครั้ง (เช่น ตัวพิมพ์ใหญ่-เล็ก หรือช่องว่าง) หรือติดต่อเจ้าหน้าที่เพื่อช่วยตรวจสอบ</p>' +
                '<div class="so-state-actions">' +
                    '<button type="button" class="so-state-btn js-focus-search"><i class="fa fa-search mr-2" aria-hidden="true"></i>ลองค้นหาใหม่</button>' +
                    '<a href="?page=contact" class="so-state-btn so-state-btn--outline"><i class="fa fa-envelope-o mr-2" aria-hidden="true"></i>ติดต่อสอบถาม</a>' +
                '</div>' +
            '</div>'
        );
    }

    function getStatusMeta(statusId) {
        switch (statusId) {
            case 1:
                return { label: 'รอชำระเงิน', badgeClass: 'is-pending', icon: 'fa-clock-o' };
            case 2:
                return { label: 'รอจัดส่ง', badgeClass: 'is-shipping', icon: 'fa-archive' };
            case 3:
                return { label: 'จัดส่งแล้ว', badgeClass: 'is-completed', icon: 'fa-check-circle-o' };
            default:
                return { label: 'ยกเลิกแล้ว', badgeClass: 'is-cancelled', icon: 'fa-times-circle-o' };
        }
    }

    function renderStepper(statusId) {
        if (statusId < 1 || statusId > 3) return '';

        var steps = [
            { num: 1, label: 'รอชำระเงิน', icon: 'fa-qrcode' },
            { num: 2, label: 'เตรียมจัดส่ง', icon: 'fa-archive' },
            { num: 3, label: 'จัดส่งแล้ว', icon: 'fa-check' }
        ];

        var html = '<div class="so-stepper" aria-label="สถานะคำสั่งซื้อ">';
        steps.forEach(function (step, idx) {
            var cls = '';
            var dotIcon = step.icon;
            if (statusId > step.num || (statusId === 3 && step.num === 3)) {
                cls = 'is-done';
                dotIcon = 'fa-check';
            } else if (statusId === step.num) {
                cls = 'is-active';
            }

            html +=
                '<div class="so-step ' + cls + '">' +
                    '<span class="so-step-dot"><i class="fa ' + dotIcon + '" aria-hidden="true"></i></span>' +
                    '<span>' + step.label + '</span>' +
                '</div>';

            if (idx < steps.length - 1) {
                var lineCls = statusId > step.num ? ' is-done' : '';
                html += '<div class="so-step-line' + lineCls + '"></div>';
            }
        });
        html += '</div>';
        return html;
    }

    function renderOrderCard(order) {
        var statusId = parseInt(order.status_id, 10) || 4;
        var statusMeta = getStatusMeta(statusId);
        var orderNo = String(order.order_no || '');
        var orderId = parseInt(order.order_id, 10) || 0;
        var shipTypeCode = String(order.shipping_type || '1');
        var shipPrice = parseFloat(order.shipping_price) || 0;
        var totalPrice = parseFloat(order.total_price) || 0;
        var receiptLink = $.trim(String(order.receipt_link || ''));
        var transportName = $.trim(String(order.transported_name || ''));
        var parcelNum = $.trim(String(order.parcel_number || ''));
        var createDateFmt = order.create_date_fmt || '-';
        var deadlineFmt = order.payment_deadline_fmt || '-';

        var shipLabel = '';
        var shipIcon = 'fa-university';
        var receiverName = '';
        var receiverPhone = '';
        var addressLine = '';

        if (shipTypeCode === '1') {
            shipLabel = 'รับหน้าร้าน';
            shipIcon = 'fa-university';
            receiverName = $.trim(String(order.shipping_name || ''));
            receiverPhone = $.trim(String(order.shipping_phone || ''));
            addressLine = 'เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี';
        } else if (shipTypeCode === '2') {
            shipLabel = 'ส่งภายในวิทยาเขต';
            shipIcon = 'fa-paper-plane-o';
            receiverName = $.trim(String(order.shipping_name || ''));
            receiverPhone = $.trim(String(order.shipping_phone || ''));
            var dept = $.trim(String(order.shipping_department || ''));
            addressLine = dept !== '' ? 'คณะ / หน่วยงาน: ' + dept : '';
        } else {
            shipLabel = 'ส่งภายนอกวิทยาเขต';
            shipIcon = 'fa-truck';
            receiverName = $.trim(String(order.addr_name || order.shipping_name || ''));
            receiverPhone = $.trim(String(order.addr_phone || order.shipping_phone || ''));
            var addrParts = [];
            if (order.addr_detail) addrParts.push($.trim(String(order.addr_detail)));
            if (order.subdistrict_name_in_thai) addrParts.push('ตำบล' + $.trim(String(order.subdistrict_name_in_thai)));
            if (order.district_name_in_thai) addrParts.push('อำเภอ' + $.trim(String(order.district_name_in_thai)));
            if (order.province_name_in_thai) addrParts.push('จังหวัด' + $.trim(String(order.province_name_in_thai)));
            if (order.addr_zip) addrParts.push($.trim(String(order.addr_zip)));
            addressLine = addrParts.join(' ');
        }

        // Receiver strip
        var receiverHtml = '';
        if (receiverName !== '' || receiverPhone !== '') {
            receiverHtml += '<span class="order-ship-receiver">';
            if (receiverName !== '') {
                receiverHtml += '<strong>ผู้รับ:</strong> ' + escHtml(receiverName);
            }
            if (receiverName !== '' && receiverPhone !== '') {
                receiverHtml += '<span class="order-ship-sep">&middot;</span>';
            }
            if (receiverPhone !== '') {
                receiverHtml += '<strong>โทร:</strong> ' + escHtml(receiverPhone);
            }
            receiverHtml += '</span>';
        }

        var addressHtml = '';
        if (addressLine !== '') {
            addressHtml =
                '<div class="order-ship-address">' +
                    '<i class="fa fa-map-marker mr-1" aria-hidden="true"></i>' +
                    escHtml(addressLine) +
                '</div>';
        }

        var trackingHtml = '';
        if (transportName !== '' || parcelNum !== '') {
            trackingHtml += '<div class="order-tracking-bar"><i class="fa fa-barcode" aria-hidden="true"></i>';
            if (transportName !== '') {
                trackingHtml += '<span>จัดส่งโดย: <strong>' + escHtml(transportName) + '</strong></span>';
            }
            if (parcelNum !== '') {
                trackingHtml +=
                    '<span>หมายเลขพัสดุ: <strong>' + escHtml(parcelNum) + '</strong></span>' +
                    '<button type="button" class="order-copy-btn js-copy-text" data-copy="' + escHtml(parcelNum) + '" title="คัดลอกหมายเลขพัสดุ">' +
                        '<i class="fa fa-clone" aria-hidden="true"></i>' +
                    '</button>';
            }
            trackingHtml += '</div>';
        }

        // Items HTML
        var items = Array.isArray(order.details) ? order.details : [];
        var itemsHtml = '';
        items.forEach(function (item) {
            var colorCnt = parseInt(item.color_count, 10) || 0;
            var sizeCnt = parseInt(item.size_count, 10) || 0;
            var colorVal = $.trim(String(item.variant_color || ''));
            var sizeVal = $.trim(String(item.variant_size || ''));
            var sizeDet = $.trim(String(item.size_detail || ''));
            var isReserve = parseInt(item.order_type, 10) === 2;

            var imgName = $.trim(String(item.img_name || ''));
            var imgSrc = imgName !== '' ? 'file/product/' + encodeURIComponent(imgName) : 'images/icons/OARShopping.png';
            var detailHref = item.product_hash ? '?page=detail&product=' + encodeURIComponent(item.product_hash) : '?page=product';

            var variantsHtml = '';
            if (colorCnt > 1 || sizeCnt > 1 || isReserve) {
                variantsHtml += '<div class="order-item-variants">';
                if (colorCnt > 1) {
                    variantsHtml += '<span class="order-variant-pill"><strong>สี:</strong>' + escHtml(colorVal || 'มาตรฐาน') + '</span>';
                }
                if (sizeCnt > 1) {
                    var sizeLabel = sizeVal || 'มาตรฐาน';
                    if (sizeDet !== '') sizeLabel += ' (' + sizeDet + ')';
                    variantsHtml += '<span class="order-variant-pill"><strong>ขนาด:</strong>' + escHtml(sizeLabel) + '</span>';
                }
                if (isReserve) {
                    variantsHtml += '<span class="order-reserve-pill">สินค้าสั่งจอง</span>';
                }
                variantsHtml += '</div>';
            }

            var unitPrice = parseFloat(item.variant_price) || 0;
            var unitPriceHtml = unitPrice > 0 ? ' <span>(' + formatBaht(unitPrice) + ' / ชิ้น)</span>' : '';

            itemsHtml +=
                '<div class="order-item-row">' +
                    '<a href="' + escHtml(detailHref) + '" class="order-item-thumb">' +
                        '<img src="' + escHtml(imgSrc) + '" alt="' + escHtml(item.product_name || 'สินค้า') + '" ' +
                            'onerror="this.onerror=null;this.src=\'images/icons/OARShopping.png\';this.classList.add(\'is-fallback-img\');">' +
                    '</a>' +
                    '<div class="order-item-info">' +
                        '<a href="' + escHtml(detailHref) + '" class="order-item-title">' + escHtml(item.product_name || '') + '</a>' +
                        variantsHtml +
                        '<div class="order-item-qty">จำนวน ' + (parseInt(item.qty, 10) || 1) + ' ชิ้น' + unitPriceHtml + '</div>' +
                    '</div>' +
                    '<div class="order-item-price">' + formatBaht(item.total_price) + '</div>' +
                '</div>';
        });

        // Footer left
        var footerLeftHtml = '';
        if (statusId === 1) {
            footerLeftHtml =
                '<div class="order-deadline-note">' +
                    '<i class="fa fa-qrcode" aria-hidden="true"></i>' +
                    '<span>กรุณาชำระเงินภายใน <strong>' + escHtml(deadlineFmt) + ' น.</strong> ผ่าน Thai QR PromptPay</span>' +
                '</div>';
        } else if (receiptLink !== '') {
            footerLeftHtml =
                '<a href="' + escHtml(receiptLink) + '" target="_blank" rel="noopener noreferrer" class="order-receipt-btn">' +
                    '<i class="fa fa-file-text-o mr-2" aria-hidden="true"></i>เปิดดูใบเสร็จรับเงิน' +
                    '<i class="fa fa-external-link ml-2" aria-hidden="true"></i>' +
                '</a>';
        }

        // Actions for pending payment
        var actionsHtml = '';
        if (statusId === 1) {
            actionsHtml =
                '<div class="order-actions">' +
                    '<button type="button" class="order-btn-cancel js-cancel-order" data-order-id="' + orderId + '">ยกเลิกคำสั่งซื้อ</button>' +
                    '<a href="payment/?order_no=' + encodeURIComponent(orderNo) + '" class="order-btn-pay">' +
                        '<i class="fa fa-qrcode mr-2" aria-hidden="true"></i>ชำระเงินตอนนี้' +
                    '</a>' +
                '</div>';
        }

        return (
            '<article class="order-card">' +
                '<div class="order-card-head">' +
                    '<div class="order-head-left">' +
                        '<div class="order-no-group">' +
                            '<span class="order-no-label">รหัสใบสั่งซื้อ</span>' +
                            '<strong class="order-no-code">' + escHtml(orderNo) + '</strong>' +
                            '<button type="button" class="order-copy-btn js-copy-text" data-copy="' + escHtml(orderNo) + '" title="คัดลอกรหัสใบสั่งซื้อ">' +
                                '<i class="fa fa-clone" aria-hidden="true"></i>' +
                            '</button>' +
                        '</div>' +
                        '<span class="order-date-text"><i class="fa fa-calendar-o mr-1" aria-hidden="true"></i>สั่งซื้อเมื่อ ' + escHtml(createDateFmt) + ' น.</span>' +
                    '</div>' +
                    '<span class="order-status-pill ' + statusMeta.badgeClass + '">' +
                        '<i class="fa ' + statusMeta.icon + ' mr-1" aria-hidden="true"></i>' + statusMeta.label +
                    '</span>' +
                '</div>' +
                renderStepper(statusId) +
                '<div class="order-ship-strip">' +
                    '<div class="order-ship-row">' +
                        '<span class="order-ship-badge"><i class="fa ' + shipIcon + ' mr-1" aria-hidden="true"></i>' + shipLabel + '</span>' +
                        receiverHtml +
                    '</div>' +
                    addressHtml +
                    trackingHtml +
                '</div>' +
                '<div class="order-items-list">' + itemsHtml + '</div>' +
                '<div class="order-card-footer">' +
                    '<div class="order-footer-left">' + footerLeftHtml + '</div>' +
                    '<div class="order-footer-right">' +
                        '<div class="order-totals">' +
                            '<div class="order-total-row">' +
                                '<span>ค่าจัดส่ง:</span>' +
                                '<strong class="' + (shipPrice <= 0 ? 'is-free' : '') + '">' + (shipPrice <= 0 ? 'ฟรี' : formatBaht(shipPrice)) + '</strong>' +
                            '</div>' +
                            '<div class="order-total-row order-total-row--grand">' +
                                '<span>ยอดรวมทั้งหมด:</span>' +
                                '<strong>' + formatBaht(totalPrice) + '</strong>' +
                            '</div>' +
                        '</div>' +
                        actionsHtml +
                    '</div>' +
                '</div>' +
            '</article>'
        );
    }

    function executeSearch(orderNo) {
        var trimmed = $.trim(orderNo);
        if (trimmed === '') {
            swal('กรุณากรอกเลขที่ใบสั่งซื้อ', 'กรุณาระบุเลขที่ใบสั่งซื้อที่ต้องการค้นหา เช่น ORD2026...', 'warning');
            $input.trigger('focus');
            return;
        }

        lastSearchedOrderNo = trimmed;
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>กำลังค้นหา...</span>');
        renderLoadingState();

        $.ajax({
            type: 'post',
            url: 'api/search-order.php',
            data: { order_no: trimmed },
            dataType: 'json',
            success: function (res) {
                if (!Array.isArray(res) || res.length === 0) {
                    renderNotFoundState(trimmed);
                    return;
                }
                var html = '';
                res.forEach(function (order) {
                    html += renderOrderCard(order);
                });
                $orderList.html(html);
            },
            error: function () {
                swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง', 'error');
                renderInitialState();
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html('<i class="fa fa-search mr-2" aria-hidden="true"></i><span>ค้นหาคำสั่งซื้อ</span>');
            }
        });
    }

    $input.on('input', toggleClearButton);
    toggleClearButton();

    $clearBtn.on('click', function () {
        $input.val('').trigger('focus');
        toggleClearButton();
        renderInitialState();
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        executeSearch($input.val());
    });

    $(document).on('click', '.js-focus-search', function () {
        $input.trigger('focus').select();
    });

    // Copy order number or parcel tracking number
    $(document).on('click', '.js-copy-text', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var text = $btn.attr('data-copy') || '';
        if (!text) return;

        function markCopied() {
            var $icon = $btn.find('i');
            $icon.removeClass('fa-clone').addClass('fa-check');
            setTimeout(function () {
                $icon.removeClass('fa-check').addClass('fa-clone');
            }, 1300);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(markCopied);
        } else {
            var $tmp = $('<input>').val(text).appendTo('body').select();
            document.execCommand('copy');
            $tmp.remove();
            markCopied();
        }
    });

    // Cancel pending order
    $(document).on('click', '.js-cancel-order', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var orderId = $btn.attr('data-order-id');

        swal({
            title: 'ยืนยันยกเลิกคำสั่งซื้อ?',
            text: 'คุณต้องการยกเลิกคำสั่งซื้อนี้ใช่หรือไม่',
            icon: 'warning',
            buttons: ['ไม่ยกเลิก', 'ยืนยันยกเลิก'],
            dangerMode: true
        }).then(function (willCancel) {
            if (!willCancel) return;

            $btn.prop('disabled', true);
            $.ajax({
                type: 'post',
                url: 'api/checkout.php',
                data: {
                    fn: 'cancel_order',
                    order_id: orderId
                },
                dataType: 'json',
                success: function (res) {
                    swal({
                        title: res.title || 'ยกเลิกสำเร็จ',
                        text: res.msg || 'รายการสั่งซื้อของคุณถูกยกเลิกแล้ว',
                        icon: res.icon || 'success',
                        timer: 1400,
                        buttons: false
                    }).then(function () {
                        if (lastSearchedOrderNo !== '') {
                            executeSearch(lastSearchedOrderNo);
                        } else {
                            location.reload();
                        }
                    });
                },
                error: function () {
                    $btn.prop('disabled', false);
                    swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        });
    });

    // Auto-search if ?page=search-order&order_no=... is passed in URL
    var initialOrder = $.trim($orderList.attr('data-initial-order') || '');
    if (initialOrder !== '') {
        executeSearch(initialOrder);
    }
});
