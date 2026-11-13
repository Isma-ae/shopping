$(document).ready(function () {
    const THAI_MONTHS_SHORT = [
        "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
        "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
    ];

    const CATEGORY_PALETTE = [
        "#1572E8", "#31CE36", "#FFAD46", "#6861CE", "#F25961", "#48ABF7", "#9E9E9E"
    ];

    const STATUS_META = {
        1: { label: "รอชำระเงิน", badgeClass: "bg-warning text-dark", color: "#FFAD46" },
        2: { label: "ชำระแล้ว รอจัดส่ง", badgeClass: "bg-info text-dark", color: "#48ABF7" },
        3: { label: "จัดส่งแล้ว", badgeClass: "bg-success", color: "#31CE36" },
        4: { label: "ยกเลิก", badgeClass: "bg-secondary", color: "#6C757D" }
    };

    const SHIPPING_LABELS = {
        "1": "รับหน้าร้าน",
        "2": "ส่งภายในวิทยาเขต",
        "3": "ส่งภายนอกวิทยาเขต"
    };

    let currentPeriod = "all";
    let bestSellerMode = "product";
    let latestData = null;
    const chartInstances = {};

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatNumber(num) {
        return Number(num || 0).toLocaleString("th-TH", { maximumFractionDigits: 0 });
    }

    function formatCurrency(num) {
        const val = Number(num || 0);
        const hasDecimal = Math.abs(val % 1) > 0.001;
        return "฿ " + val.toLocaleString("th-TH", {
            minimumFractionDigits: hasDecimal ? 2 : 0,
            maximumFractionDigits: hasDecimal ? 2 : 0
        });
    }

    function formatTrendLabel(rawKey, granularity) {
        if (!rawKey) return "";
        const parts = String(rawKey).split("-");
        if (granularity === "month" && parts.length >= 2) {
            const yearTh = Number(parts[0]) + 543;
            const monthIdx = Number(parts[1]) - 1;
            const mName = THAI_MONTHS_SHORT[monthIdx] || parts[1];
            return mName + " " + String(yearTh).slice(-2);
        }
        if (parts.length >= 3) {
            const day = Number(parts[2]);
            const monthIdx = Number(parts[1]) - 1;
            const mName = THAI_MONTHS_SHORT[monthIdx] || parts[1];
            return day + " " + mName;
        }
        return rawKey;
    }

    function formatDateTimeTh(dtStr) {
        if (!dtStr) return "-";
        const d = new Date(String(dtStr).replace(" ", "T"));
        if (isNaN(d.getTime())) return dtStr;
        const day = d.getDate();
        const m = THAI_MONTHS_SHORT[d.getMonth()];
        const y = String(d.getFullYear() + 543).slice(-2);
        const hh = String(d.getHours()).padStart(2, "0");
        const mm = String(d.getMinutes()).padStart(2, "0");
        return `${day} ${m} ${y} ${hh}:${mm}`;
    }

    function destroyChart(key) {
        if (chartInstances[key]) {
            chartInstances[key].destroy();
            delete chartInstances[key];
        }
    }

    function prepareChartCanvas(key, $canvas, $empty, hasData) {
        destroyChart(key);
        if (!hasData) {
            $canvas.addClass("d-none");
            $empty.removeClass("d-none");
            return null;
        }
        $canvas.removeClass("d-none");
        $empty.addClass("d-none");
        return $canvas.get(0).getContext("2d");
    }

    function integerTickCallback(value) {
        if (Math.floor(value) === value) return value;
    }

    function createDoughnutChart(ctx, labels, values, colors, labelFormatter) {
        return new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 62,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, chartData) {
                            const name = chartData.labels[tooltipItem.index] || "";
                            const val = Number(chartData.datasets[0].data[tooltipItem.index] || 0);
                            return labelFormatter(name, val);
                        }
                    }
                }
            }
        });
    }

    function renderLegendRows($list, items) {
        const html = items.map(item => `
            <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                <div class="d-flex align-items-center text-truncate me-2">
                    <span class="legend-dot" style="background:${item.color};"></span>
                    <span class="text-truncate">${escapeHtml(item.label)}</span>
                </div>
                <div class="fw-semibold text-nowrap">${item.valueHtml}</div>
            </div>`).join("");
        $list.html(html);
    }

    function setLoading(isLoading) {
        const $page = $(".dashboard-page");
        const $icon = $("#btn-refresh-dashboard i");
        if (isLoading) {
            $page.addClass("is-loading");
            $icon.addClass("fa-spin");
        } else {
            $page.removeClass("is-loading");
            $icon.removeClass("fa-spin");
        }
    }

    function showError(msg) {
        $("#dashboard-error-text").text(msg || "เกิดข้อผิดพลาดในการโหลดข้อมูล");
        $("#dashboard-error-banner").removeClass("d-none");
    }

    function hideError() {
        $("#dashboard-error-banner").addClass("d-none");
    }

    function renderKpis(data) {
        const kpi = data.kpi || {};
        const alerts = data.alerts || {};

        $("#dashboard-period-badge").text(data.period_label || "ข้อมูลทั้งหมด");
        const now = new Date();
        $("#dashboard-last-updated").text(
            "อัปเดตล่าสุด " +
            String(now.getHours()).padStart(2, "0") + ":" +
            String(now.getMinutes()).padStart(2, "0") + " น."
        );

        $("#kpi-total-revenue").text(formatCurrency(kpi.total_revenue));
        $("#kpi-revenue-breakdown").text(
            "ค่าสินค้า " + formatCurrency(kpi.product_revenue) + " · ค่าส่ง " + formatCurrency(kpi.shipping_revenue)
        );
        const profitNote = Number(kpi.costed_items_sold || 0) > 0
            ? "กำไรขั้นต้นประมาณ " + formatCurrency(kpi.est_profit)
            : "กำไรขั้นต้น ฿ 0 (ยังไม่ระบุราคาทุน)";
        $("#kpi-est-profit").text(profitNote);

        $("#kpi-order-count").text(formatNumber(kpi.order_count));
        $("#kpi-avg-order").text("เฉลี่ย " + formatCurrency(kpi.avg_order_value) + " / ออเดอร์");
        $("#kpi-buyer-split").text(
            "สมาชิก " + formatNumber(kpi.member_orders) + " · Guest " + formatNumber(kpi.guest_orders)
        );

        $("#kpi-pending-shipping").text(formatNumber(alerts.pending_shipping));
        $("#kpi-waiting-pay").text("รอชำระเงิน " + formatNumber(alerts.waiting_payment) + " รายการ");
        $("#kpi-pending-docs").text(
            "ค้างใบเสร็จ " + formatNumber(alerts.missing_receipt) + " · ค้างเลขพัสดุ " + formatNumber(alerts.missing_parcel)
        );

        $("#kpi-total-members").text(formatNumber(kpi.total_members) + " คน");
        $("#kpi-products-count").text(
            "เปิดขาย " + formatNumber(kpi.active_products) + " · รอจำหน่าย " + formatNumber(kpi.draft_products)
        );
        $("#kpi-items-sold").text("ขายแล้วในช่วงนี้ " + formatNumber(kpi.items_sold) + " ชิ้น");

        // Operational strip
        $("#alert-pending-shipping").text(formatNumber(alerts.pending_shipping) + " รายการ");
        const docsTotal = Number(alerts.missing_receipt || 0) + Number(alerts.missing_parcel || 0);
        $("#alert-docs-pending").text(formatNumber(docsTotal) + " รายการ");
        $("#alert-unread-msg").text(formatNumber(alerts.unread_messages) + " ข้อความ");
        $("#alert-evidence-reserve").text(
            formatNumber(alerts.open_evidence) + " / " + formatNumber(alerts.active_reserves) + " ชิ้น"
        );
    }

    function renderSalesTrend(data) {
        const rows = data.trend || [];
        const granularity = data.granularity || "month";
        const $canvas = $("#salesTrendChart");
        const $empty = $("#salesTrendEmpty");

        $("#trend-subtitle").text(
            granularity === "day"
                ? "แสดงยอดขายรายวัน (บาท) เทียบจำนวนออเดอร์ที่ชำระเงินแล้ว"
                : "แสดงยอดขายรายเดือน (บาท) เทียบจำนวนออเดอร์ที่ชำระเงินแล้ว"
        );

        const ctx = prepareChartCanvas("trend", $canvas, $empty, rows.length > 0);
        if (!ctx) return;

        const labels = rows.map(r => formatTrendLabel(r.label_key, granularity));
        const revenues = rows.map(r => Number(r.revenue || 0));
        const orders = rows.map(r => Number(r.orders || 0));

        chartInstances.trend = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "ยอดขาย (บาท)",
                        data: revenues,
                        yAxisID: "y-axis-revenue",
                        backgroundColor: "rgba(21, 114, 232, 0.78)",
                        borderColor: "#1572E8",
                        borderWidth: 1,
                        order: 2
                    },
                    {
                        label: "จำนวนออเดอร์",
                        data: orders,
                        type: "line",
                        yAxisID: "y-axis-orders",
                        borderColor: "#FFAD46",
                        backgroundColor: "rgba(255, 173, 70, 0.15)",
                        borderWidth: 2.5,
                        pointRadius: 3.5,
                        pointBackgroundColor: "#FFAD46",
                        fill: false,
                        lineTension: 0.25,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    mode: "index",
                    intersect: false,
                    callbacks: {
                        label: function (tooltipItem, chartData) {
                            const dsLabel = chartData.datasets[tooltipItem.datasetIndex].label || "";
                            if (tooltipItem.datasetIndex === 0) {
                                return dsLabel + ": " + formatCurrency(tooltipItem.yLabel);
                            }
                            return dsLabel + ": " + formatNumber(tooltipItem.yLabel) + " รายการ";
                        }
                    }
                },
                legend: {
                    display: true,
                    position: "top"
                },
                scales: {
                    xAxes: [{
                        gridLines: { display: false }
                    }],
                    yAxes: [
                        {
                            id: "y-axis-revenue",
                            position: "left",
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    return "฿" + Number(value).toLocaleString("th-TH");
                                }
                            }
                        },
                        {
                            id: "y-axis-orders",
                            position: "right",
                            gridLines: { drawOnChartArea: false },
                            ticks: {
                                beginAtZero: true,
                                callback: integerTickCallback
                            }
                        }
                    ]
                }
            }
        });
    }

    function renderCategorySales(data) {
        const rows = data.category_sales || [];
        const $canvas = $("#categorySalesChart");
        const $empty = $("#categorySalesEmpty");
        const $list = $("#categorySalesList");

        const totalRev = rows.reduce((acc, r) => acc + Number(r.revenue || 0), 0);
        const ctx = prepareChartCanvas("category", $canvas, $empty, rows.length > 0 && totalRev > 0);
        if (!ctx) {
            $list.html('<div class="text-muted text-center py-2">ไม่มีรายการขายตามหมวดหมู่</div>');
            return;
        }

        const labels = rows.map(r => r.category_name);
        const values = rows.map(r => Number(r.revenue || 0));
        const colors = rows.map((_, i) => CATEGORY_PALETTE[i % CATEGORY_PALETTE.length]);

        chartInstances.category = createDoughnutChart(ctx, labels, values, colors, (name, val) => {
            const pct = totalRev > 0 ? ((val / totalRev) * 100).toFixed(1) : "0.0";
            return `${name}: ${formatCurrency(val)} (${pct}%)`;
        });

        renderLegendRows($list, rows.map((r, idx) => {
            const val = Number(r.revenue || 0);
            const pct = totalRev > 0 ? ((val / totalRev) * 100).toFixed(1) : "0.0";
            return {
                color: colors[idx],
                label: `${r.category_name} (${formatNumber(r.qty)} ชิ้น)`,
                valueHtml: `${formatCurrency(val)} <span class="text-muted">(${pct}%)</span>`
            };
        }));
    }

    function renderOrderStatus(data) {
        const rows = data.status_breakdown || [];
        const $canvas = $("#orderStatusChart");
        const $empty = $("#orderStatusEmpty");
        const $list = $("#orderStatusList");

        const totalOrders = rows.reduce((acc, r) => acc + Number(r.order_count || 0), 0);
        const ctx = prepareChartCanvas("status", $canvas, $empty, totalOrders > 0);
        if (!ctx) {
            $list.html('<div class="text-muted text-center py-2">ไม่มีคำสั่งซื้อในช่วงเวลาที่เลือก</div>');
            return;
        }

        const nonZero = rows.filter(r => Number(r.order_count || 0) > 0);
        const labels = nonZero.map(r => (STATUS_META[r.status_id] || {}).label || r.label);
        const counts = nonZero.map(r => Number(r.order_count || 0));
        const colors = nonZero.map(r => (STATUS_META[r.status_id] || {}).color || "#9E9E9E");

        chartInstances.status = createDoughnutChart(ctx, labels, counts, colors, (name, val) => {
            return `${name}: ${formatNumber(val)} รายการ`;
        });

        renderLegendRows($list, rows.map(r => {
            const meta = STATUS_META[r.status_id] || { label: r.label, color: "#9E9E9E" };
            return {
                color: meta.color,
                label: meta.label,
                valueHtml: `${formatNumber(r.order_count)} <span class="text-muted">(${formatCurrency(r.total_amount)})</span>`
            };
        }));
    }

    function renderShippingBreakdown(data) {
        const rows = data.shipping_breakdown || [];
        const $canvas = $("#shippingTypeChart");
        const $empty = $("#shippingTypeEmpty");
        const $list = $("#shippingTypeList");

        const total = rows.reduce((acc, r) => acc + Number(r.order_count || 0), 0);
        const ctx = prepareChartCanvas("shipping", $canvas, $empty, rows.length > 0 && total > 0);
        if (!ctx) {
            $list.html('<div class="text-muted text-center py-2">ไม่มีข้อมูลการจัดส่งในช่วงเวลานี้</div>');
            return;
        }

        const labels = rows.map(r => r.label);
        const counts = rows.map(r => Number(r.order_count || 0));
        const colors = ["#1572E8", "#31CE36", "#FFAD46", "#6C757D"];

        chartInstances.shipping = new Chart(ctx, {
            type: "horizontalBar",
            data: {
                labels: labels,
                datasets: [{
                    label: "จำนวนออเดอร์",
                    data: counts,
                    backgroundColor: colors.slice(0, rows.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: integerTickCallback
                        }
                    }],
                    yAxes: [{
                        gridLines: { display: false }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem) {
                            return `จำนวน: ${formatNumber(tooltipItem.xLabel)} ออเดอร์`;
                        }
                    }
                }
            }
        });

        renderLegendRows($list, rows.map((r, idx) => {
            const pct = total > 0 ? ((Number(r.order_count) / total) * 100).toFixed(1) : "0.0";
            return {
                color: colors[idx % colors.length],
                label: r.label,
                valueHtml: `${formatNumber(r.order_count)} (${pct}%) · <span class="text-muted">${formatCurrency(r.revenue)}</span>`
            };
        }));
    }

    function renderLowStock(data) {
        const rows = data.low_stock || [];
        const $container = $("#lowStockList");

        if (!rows.length) {
            $container.html('<div class="text-center text-muted py-4">สต๊อกสินค้าทุกรายการยังมากกว่า 10 ชิ้น</div>');
            return;
        }

        let html = "";
        rows.forEach(r => {
            const rem = Number(r.remaining_stock || 0);
            let badgeClass = "bg-warning text-dark";
            if (rem <= 0) badgeClass = "bg-danger";
            else if (rem <= 3) badgeClass = "bg-warning text-dark";

            const variantMeta = [r.variant_color, r.variant_size].filter(Boolean).join(" / ");
            const isActive = Number(r.product_status) === 2;
            const statusTag = isActive
                ? '<span class="badge bg-success-subtle text-success ms-1">เปิดขาย</span>'
                : '<span class="badge bg-secondary-subtle text-secondary ms-1">รอจำหน่าย</span>';

            html += `
                <div class="low-stock-item">
                    <div class="text-truncate">
                        <div class="fw-semibold text-truncate">${escapeHtml(r.product_name)}</div>
                        <div class="small text-muted text-truncate">
                            ${escapeHtml(variantMeta || "-")} ${statusTag}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="badge ${badgeClass}">เหลือ ${formatNumber(rem)}</span>
                        <a href="?page=product-stock&amp;product_id=${encodeURIComponent(r.product_id || "")}"
                           class="btn btn-xs btn-outline-secondary" title="แก้ไขสต๊อก">
                            <i class="fas fa-sliders-h"></i>
                        </a>
                    </div>
                </div>`;
        });
        $container.html(html);
    }

    function renderBestSellers() {
        if (!latestData) return;
        const rows = bestSellerMode === "variant"
            ? (latestData.top_variants || [])
            : (latestData.top_products || []);
        const $tbody = $("#bestSellersBody");

        if (!rows.length) {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลการขายในช่วงเวลานี้</td></tr>');
            return;
        }

        let html = "";
        rows.forEach((r, idx) => {
            let subInfo = "";
            if (bestSellerMode === "variant") {
                const specs = [r.variant_color, r.variant_size].filter(Boolean).join(" · ");
                subInfo = specs ? `<div class="small text-muted">${escapeHtml(specs)}</div>` : "";
            } else {
                const cat = r.category_name ? `หมวด: ${escapeHtml(r.category_name)}` : "";
                const prof = Number(r.est_profit || 0) > 0 ? ` · กำไร ~${formatCurrency(r.est_profit)}` : "";
                subInfo = (cat || prof) ? `<div class="small text-muted">${cat}${prof}</div>` : "";
            }

            html += `
                <tr>
                    <td class="text-muted fw-bold">${idx + 1}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(r.product_name)}</div>
                        ${subInfo}
                    </td>
                    <td class="text-end fw-semibold">${formatNumber(r.qty)}</td>
                    <td class="text-end text-nowrap">${formatCurrency(r.revenue)}</td>
                </tr>`;
        });
        $tbody.html(html);
    }

    function renderRecentOrders(data) {
        const rows = data.recent_orders || [];
        const $tbody = $("#recentOrdersBody");

        if (!rows.length) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีคำสั่งซื้อในระบบ</td></tr>');
            return;
        }

        let html = "";
        rows.forEach(r => {
            const sid = Number(r.status_id || 0);
            const meta = STATUS_META[sid] || { label: "ไม่ระบุ", badgeClass: "bg-light text-dark" };
            const shipLabel = SHIPPING_LABELS[String(r.shipping_type)] || "-";
            const guestTag = Number(r.user_id) === 0 ? ' <span class="badge bg-light text-dark border">Guest</span>' : "";

            html += `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(r.order_no)}</div>
                        <div class="small text-muted">${escapeHtml(formatDateTimeTh(r.create_date))}</div>
                    </td>
                    <td>
                        <div>${escapeHtml(r.buyer_name)}${guestTag}</div>
                        <div class="small text-muted">${escapeHtml(shipLabel)}</div>
                    </td>
                    <td class="text-end text-nowrap fw-semibold">${formatCurrency(r.total_price)}</td>
                    <td><span class="badge ${meta.badgeClass}">${escapeHtml(meta.label)}</span></td>
                    <td class="text-end">
                        <a href="?page=order-detail&amp;order_id=${encodeURIComponent(r.order_id)}"
                           class="btn btn-xs btn-outline-primary" title="ดูรายละเอียด">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>`;
        });
        $tbody.html(html);
    }

    function renderPendingFulfillment(data) {
        const rows = data.pending_fulfillment || [];
        const $tbody = $("#pendingFulfillmentBody");
        $("#tab-fulfillment-badge").text(formatNumber(rows.length));

        if (!rows.length) {
            $tbody.html('<tr><td colspan="4" class="text-center text-success py-4"><i class="fas fa-check-circle me-1"></i>ไม่มีรายการค้างดำเนินการ</td></tr>');
            return;
        }

        let html = "";
        rows.forEach(r => {
            const tasks = [];
            if (Number(r.status_id) === 2) {
                tasks.push('<span class="badge bg-warning text-dark">รอจัดส่ง</span>');
            }
            if (Number(r.receipt_id) === 1 && !String(r.receipt_link || "").trim()) {
                tasks.push('<span class="badge bg-danger">รอแนบลิงก์ใบเสร็จ</span>');
            }
            if (String(r.shipping_type) === "3" && !String(r.parcel_number || "").trim()) {
                tasks.push('<span class="badge bg-info text-dark">รอเลขพัสดุ</span>');
            }

            html += `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(r.order_no)}</div>
                        <div class="small text-muted">${escapeHtml(formatDateTimeTh(r.create_date))}</div>
                    </td>
                    <td>
                        <div>${escapeHtml(r.buyer_name)}</div>
                        <div class="small text-muted">${formatCurrency(r.total_price)}</div>
                    </td>
                    <td><div class="d-flex flex-wrap gap-1">${tasks.join("")}</div></td>
                    <td class="text-end">
                        <a href="?page=order-detail&amp;order_id=${encodeURIComponent(r.order_id)}"
                           class="btn btn-xs btn-primary">จัดการ</a>
                    </td>
                </tr>`;
        });
        $tbody.html(html);
    }

    function renderReviewsAndHealth(data) {
        const reviews = data.recent_reviews || [];
        const nullSales = data.null_sale_list || [];
        const alerts = data.alerts || {};
        const $container = $("#reviewsAndHealthBody");

        const healthIssuesCount =
            Number(alerts.status_mismatch || 0) +
            Number(alerts.null_sale_variants || 0) +
            Number(alerts.invalid_cart_qty || 0);
        $("#tab-health-badge").text(formatNumber(healthIssuesCount));

        let html = '<h6 class="fw-bold mb-2">แจ้งเตือนสุขภาพข้อมูล (Data Health)</h6>';
        html += '<div class="list-group list-group-flush mb-3 border rounded">';

        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center small">
                <span>ออเดอร์ส่งแล้ว/จ่ายแล้ว ที่ <code>order_status</code> ยังค้างคำว่า "รอการชำระเงิน"</span>
                <span class="badge ${Number(alerts.status_mismatch) > 0 ? "bg-warning text-dark" : "bg-success"}">${formatNumber(alerts.status_mismatch)} รายการ</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center small">
                <span>ตัวเลือกสินค้า (Variant) ที่ยังไม่มีราคาขาย (<code>variant_sale</code>)</span>
                <span class="badge ${Number(alerts.null_sale_variants) > 0 ? "bg-danger" : "bg-success"}">${formatNumber(alerts.null_sale_variants)} รายการ</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center small">
                <span>ตะกร้าสินค้าที่จำนวนผิดปกติ (<code>cart_qty &le; 0</code>)</span>
                <span class="badge ${Number(alerts.invalid_cart_qty) > 0 ? "bg-danger" : "bg-success"}">${formatNumber(alerts.invalid_cart_qty)} รายการ</span>
            </div>`;
        html += "</div>";

        if (nullSales.length) {
            html += '<div class="small text-muted mb-3">ตัวอย่าง Variant ไม่มีราคาขาย: ';
            html += nullSales.map(v => {
                const label = [v.product_name, v.variant_color, v.variant_size].filter(Boolean).join(" / ");
                return `<a href="?page=product-stock&amp;product_id=${encodeURIComponent(v.product_id || "")}" class="badge bg-light text-danger border text-decoration-none me-1">${escapeHtml(label)}</a>`;
            }).join("");
            html += "</div>";
        }

        html += '<h6 class="fw-bold mb-2">รีวิวสินค้าล่าสุด</h6>';
        if (!reviews.length) {
            html += '<div class="text-muted small">ยังไม่มีรีวิวจากลูกค้า</div>';
        } else {
            html += '<div class="list-group list-group-flush border rounded">';
            reviews.forEach(rv => {
                const rating = Math.max(0, Math.min(5, Number(rv.review_rating || 0)));
                const stars = "★".repeat(rating) + "☆".repeat(5 - rating);
                html += `
                    <div class="list-group-item small">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">${escapeHtml(rv.product_name)}</span>
                            <span class="text-warning">${stars}</span>
                        </div>
                        <div class="text-dark mt-1">${escapeHtml(rv.review_detail || "-")}</div>
                        <div class="text-muted mt-1">โดย ${escapeHtml(rv.reviewer_name)}</div>
                    </div>`;
            });
            html += "</div>";
        }

        $container.html(html);
    }

    function renderAll(data) {
        latestData = data;
        renderKpis(data);
        renderSalesTrend(data);
        renderCategorySales(data);
        renderOrderStatus(data);
        renderShippingBreakdown(data);
        renderLowStock(data);
        renderBestSellers();
        renderRecentOrders(data);
        renderPendingFulfillment(data);
        renderReviewsAndHealth(data);
    }

    function loadDashboard(period) {
        setLoading(true);
        hideError();
        $.ajax({
            type: "POST",
            url: "api/dashboard.php",
            data: {
                fn: "get_dashboard",
                period: period
            },
            dataType: "json",
            success: function (res) {
                if (res && res.status === "success") {
                    renderAll(res);
                } else {
                    showError((res && res.message) ? res.message : "ไม่สามารถอ่านข้อมูลแดชบอร์ดได้");
                }
            },
            error: function (xhr) {
                let msg = "ไม่สามารถเชื่อมต่อ API แดชบอร์ดได้";
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showError(msg);
            },
            complete: function () {
                setLoading(false);
            }
        });
    }

    // Period filter clicks
    $(".dashboard-period-group").on("click", "[data-period]", function () {
        const period = $(this).data("period");
        if (!period || period === currentPeriod) return;
        currentPeriod = period;
        $(".dashboard-period-group [data-period]")
            .removeClass("btn-primary active")
            .addClass("btn-outline-primary");
        $(this)
            .removeClass("btn-outline-primary")
            .addClass("btn-primary active");
        loadDashboard(currentPeriod);
    });

    // Refresh button
    $("#btn-refresh-dashboard").on("click", function () {
        loadDashboard(currentPeriod);
    });

    // Best seller mode switcher
    $(".js-bestseller-mode").on("click", function () {
        const mode = $(this).data("mode");
        if (!mode || mode === bestSellerMode) return;
        bestSellerMode = mode;
        $(".js-bestseller-mode")
            .removeClass("btn-secondary active")
            .addClass("btn-outline-secondary");
        $(this)
            .removeClass("btn-outline-secondary")
            .addClass("btn-secondary active");
        renderBestSellers();
    });

    // Open target tab when clicking operational alert strip
    $(".js-open-tab").on("click", function (e) {
        e.preventDefault();
        const tabSelector = $(this).data("target-tab");
        const tabEl = document.querySelector(tabSelector);
        if (tabEl) {
            if (window.bootstrap && window.bootstrap.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(tabEl).show();
            } else {
                $(tabEl).trigger("click");
            }
        }
        const opsEl = document.getElementById("ops-panel");
        if (opsEl && opsEl.scrollIntoView) {
            opsEl.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });

    // Initial fetch
    loadDashboard(currentPeriod);
});
