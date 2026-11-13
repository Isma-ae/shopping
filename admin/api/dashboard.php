<?php
    include("../../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "ไม่มีสิทธิ์เข้าถึงข้อมูลแดชบอร์ด"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fn = $_REQUEST["fn"] ?? "get_dashboard";
    if ($fn !== "get_dashboard") {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid action"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Execute a SELECT query safely and return all associative rows.
     */
    function fetchAllRows($DB, $sql) {
        $res = $DB->Query($sql);
        if (!$res || $res === true) {
            return [];
        }
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Execute a SELECT query safely and return the first row.
     */
    function fetchOneRow($DB, $sql) {
        $rows = fetchAllRows($DB, $sql);
        return $rows[0] ?? [];
    }

    /**
     * Build whitelisted date filter SQL condition and chart granularity.
     */
    function resolvePeriodConfig($period) {
        $configs = [
            "all" => [
                "label" => "ข้อมูลทั้งหมด",
                "cond" => "",
                "granularity" => "month"
            ],
            "year" => [
                "label" => "ปีนี้ (" . (date("Y") + 543) . ")",
                "cond" => " AND YEAR(o.create_date) = YEAR(CURDATE())",
                "granularity" => "month"
            ],
            "month" => [
                "label" => "เดือนนี้",
                "cond" => " AND YEAR(o.create_date) = YEAR(CURDATE()) AND MONTH(o.create_date) = MONTH(CURDATE())",
                "granularity" => "day"
            ],
            "30d" => [
                "label" => "30 วันล่าสุด",
                "cond" => " AND o.create_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)",
                "granularity" => "day"
            ],
            "7d" => [
                "label" => "7 วันล่าสุด",
                "cond" => " AND o.create_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)",
                "granularity" => "day"
            ]
        ];
        if (!isset($configs[$period])) {
            $period = "all";
        }
        return [$period, $configs[$period]];
    }

    $requestedPeriod = $_REQUEST["period"] ?? "all";
    list($period, $periodCfg) = resolvePeriodConfig($requestedPeriod);
    $dateCond = $periodCfg["cond"];
    $granularity = $periodCfg["granularity"];

    // 1. Revenue & Order KPIs for paid/shipped orders in selected period
    $orderSummary = fetchOneRow($DB, "
        SELECT
            COUNT(o.order_id) AS order_count,
            COALESCE(SUM(o.total_price), 0) AS total_revenue,
            COALESCE(SUM(o.total_price - COALESCE(o.shipping_price, 0)), 0) AS product_revenue,
            COALESCE(SUM(COALESCE(o.shipping_price, 0)), 0) AS shipping_revenue,
            SUM(CASE WHEN o.user_id > 0 THEN 1 ELSE 0 END) AS member_orders,
            SUM(CASE WHEN o.user_id = 0 OR o.user_id IS NULL THEN 1 ELSE 0 END) AS guest_orders
        FROM orders o
        WHERE o.status_id IN (2, 3) {$dateCond}
    ");

    // 2. Line-item quantity & estimated gross margin (separate query to avoid order total double counting)
    $itemSummary = fetchOneRow($DB, "
        SELECT
            COALESCE(SUM(od.qty), 0) AS items_sold,
            COALESCE(SUM(CASE WHEN v.variant_price IS NOT NULL AND v.variant_price > 0 THEN (od.variant_price - v.variant_price) * od.qty ELSE 0 END), 0) AS est_profit,
            COALESCE(SUM(CASE WHEN v.variant_price IS NOT NULL AND v.variant_price > 0 THEN v.variant_price * od.qty ELSE 0 END), 0) AS est_cost,
            COALESCE(SUM(CASE WHEN v.variant_price IS NOT NULL AND v.variant_price > 0 THEN od.qty ELSE 0 END), 0) AS costed_items_sold,
            COALESCE(SUM(CASE WHEN od.order_type = 2 THEN od.qty ELSE 0 END), 0) AS reserved_items_sold
        FROM order_detail od
        INNER JOIN orders o ON od.order_id = o.order_id
        LEFT JOIN variants v ON od.variant_id = v.variant_id
        WHERE o.status_id IN (2, 3) {$dateCond}
    ");

    // 3. Status breakdown in selected period
    $statusRows = fetchAllRows($DB, "
        SELECT
            o.status_id,
            COUNT(o.order_id) AS order_count,
            COALESCE(SUM(o.total_price), 0) AS total_amount
        FROM orders o
        WHERE 1=1 {$dateCond}
        GROUP BY o.status_id
    ");
    $statusMap = [
        1 => ["status_id" => 1, "label" => "รอชำระเงิน", "order_count" => 0, "total_amount" => 0.0],
        2 => ["status_id" => 2, "label" => "ชำระเงินแล้ว รอจัดส่ง", "order_count" => 0, "total_amount" => 0.0],
        3 => ["status_id" => 3, "label" => "จัดส่งแล้ว", "order_count" => 0, "total_amount" => 0.0],
        4 => ["status_id" => 4, "label" => "ยกเลิก", "order_count" => 0, "total_amount" => 0.0]
    ];
    foreach ($statusRows as $row) {
        $sid = (int)($row["status_id"] ?? 0);
        if (isset($statusMap[$sid])) {
            $statusMap[$sid]["order_count"] = (int)$row["order_count"];
            $statusMap[$sid]["total_amount"] = (float)$row["total_amount"];
        }
    }

    // 4. All-time Operational Queue & Alerts (never hidden by date filters)
    $opsOrders = fetchOneRow($DB, "
        SELECT
            SUM(CASE WHEN o.status_id = 1 THEN 1 ELSE 0 END) AS waiting_payment,
            SUM(CASE WHEN o.status_id = 2 THEN 1 ELSE 0 END) AS pending_shipping,
            SUM(CASE WHEN o.status_id = 3 THEN 1 ELSE 0 END) AS shipped,
            SUM(CASE WHEN o.status_id = 4 THEN 1 ELSE 0 END) AS cancelled,
            SUM(CASE WHEN o.status_id IN (2, 3) AND o.receipt_id = 1 AND (o.receipt_link IS NULL OR TRIM(o.receipt_link) = '') THEN 1 ELSE 0 END) AS missing_receipt,
            SUM(CASE WHEN o.status_id IN (2, 3) AND o.shipping_type = '3' AND (o.parcel_number IS NULL OR TRIM(o.parcel_number) = '') THEN 1 ELSE 0 END) AS missing_parcel,
            SUM(CASE WHEN o.status_id = 3 AND o.order_status = 'รอการชำระเงิน' THEN 1 ELSE 0 END) AS status_mismatch
        FROM orders o
    ");

    $userSummary = fetchOneRow($DB, "
        SELECT
            COUNT(id) AS total_members,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS total_admins
        FROM users
    ");

    $productSummary = fetchOneRow($DB, "
        SELECT
            COUNT(product_id) AS total_products,
            SUM(CASE WHEN product_status = 2 THEN 1 ELSE 0 END) AS active_products,
            SUM(CASE WHEN product_status = 1 THEN 1 ELSE 0 END) AS draft_products
        FROM product
    ");

    $msgSummary = fetchOneRow($DB, "
        SELECT
            COUNT(message_id) AS total_messages,
            SUM(CASE WHEN `read` = 0 OR `read` IS NULL THEN 1 ELSE 0 END) AS unread_messages,
            SUM(CASE WHEN message_reply IS NULL OR TRIM(message_reply) = '' THEN 1 ELSE 0 END) AS unreplied_messages
        FROM message
    ");

    $reserveSummary = fetchOneRow($DB, "
        SELECT COALESCE(SUM(od.qty), 0) AS active_reserves
        FROM order_detail od
        INNER JOIN orders o ON od.order_id = o.order_id
        WHERE od.order_type = 2 AND o.status_id IN (1, 2)
    ");

    $evidenceSummary = fetchOneRow($DB, "
        SELECT
            COUNT(e.evidence_id) AS total_evidence,
            SUM(CASE WHEN o.status_id IN (1, 2) THEN 1 ELSE 0 END) AS open_evidence
        FROM evidence e
        LEFT JOIN orders o ON e.order_no = o.order_no
    ");

    $cartHealth = fetchOneRow($DB, "
        SELECT SUM(CASE WHEN cart_qty <= 0 THEN 1 ELSE 0 END) AS invalid_cart_qty
        FROM cart
    ");

    $variantHealth = fetchOneRow($DB, "
        SELECT SUM(CASE WHEN variant_sale IS NULL OR variant_sale <= 0 THEN 1 ELSE 0 END) AS null_sale_variants
        FROM variants
    ");

    // 5. Sales Trend Series
    $dateFormat = $granularity === "day" ? "%Y-%m-%d" : "%Y-%m";
    $trendRows = fetchAllRows($DB, "
        SELECT
            DATE_FORMAT(o.create_date, '{$dateFormat}') AS label_key,
            COUNT(o.order_id) AS orders,
            COALESCE(SUM(o.total_price), 0) AS revenue,
            COALESCE(SUM(o.total_price - COALESCE(o.shipping_price, 0)), 0) AS product_revenue,
            COALESCE(SUM(COALESCE(o.shipping_price, 0)), 0) AS shipping_revenue
        FROM orders o
        WHERE o.status_id IN (2, 3) AND o.create_date IS NOT NULL {$dateCond}
        GROUP BY DATE_FORMAT(o.create_date, '{$dateFormat}')
        ORDER BY label_key ASC
    ");

    // 6. Category Sales Breakdown
    $categoryRows = fetchAllRows($DB, "
        SELECT
            COALESCE(c.category_id, 0) AS category_id,
            COALESCE(c.category_name, 'ไม่ระบุหมวดหมู่') AS category_name,
            COALESCE(SUM(od.total_price), 0) AS revenue,
            COALESCE(SUM(od.qty), 0) AS qty
        FROM order_detail od
        INNER JOIN orders o ON od.order_id = o.order_id
        LEFT JOIN variants v ON od.variant_id = v.variant_id
        LEFT JOIN product p ON v.product_id = p.product_id
        LEFT JOIN category c ON p.category_id = c.category_id
        WHERE o.status_id IN (2, 3) {$dateCond}
        GROUP BY c.category_id, c.category_name
        ORDER BY revenue DESC
    ");

    // 7. Shipping Method Breakdown
    $shippingRows = fetchAllRows($DB, "
        SELECT
            COALESCE(NULLIF(TRIM(o.shipping_type), ''), '0') AS shipping_type,
            COUNT(o.order_id) AS order_count,
            COALESCE(SUM(o.total_price), 0) AS revenue
        FROM orders o
        WHERE o.status_id IN (2, 3) {$dateCond}
        GROUP BY COALESCE(NULLIF(TRIM(o.shipping_type), ''), '0')
        ORDER BY order_count DESC
    ");
    $shippingLabels = [
        "1" => "รับหน้าร้าน",
        "2" => "ส่งภายในวิทยาเขต",
        "3" => "ส่งภายนอกวิทยาเขต"
    ];
    $shippingBreakdown = [];
    foreach ($shippingRows as $row) {
        $typeKey = (string)$row["shipping_type"];
        $shippingBreakdown[] = [
            "shipping_type" => $typeKey,
            "label" => $shippingLabels[$typeKey] ?? "อื่นๆ",
            "order_count" => (int)$row["order_count"],
            "revenue" => (float)$row["revenue"]
        ];
    }

    // 8. Top Selling Products (product-level & variant-level)
    $topProducts = fetchAllRows($DB, "
        SELECT
            COALESCE(p.product_id, '') AS product_id,
            COALESCE(NULLIF(TRIM(p.product_name), ''), od.product_name) AS product_name,
            COALESCE(c.category_name, '-') AS category_name,
            COALESCE(SUM(od.qty), 0) AS qty,
            COALESCE(SUM(od.total_price), 0) AS revenue,
            COALESCE(SUM(CASE WHEN v.variant_price IS NOT NULL AND v.variant_price > 0 THEN (od.variant_price - v.variant_price) * od.qty ELSE 0 END), 0) AS est_profit
        FROM order_detail od
        INNER JOIN orders o ON od.order_id = o.order_id
        LEFT JOIN variants v ON od.variant_id = v.variant_id
        LEFT JOIN product p ON v.product_id = p.product_id
        LEFT JOIN category c ON p.category_id = c.category_id
        WHERE o.status_id IN (2, 3) {$dateCond}
        GROUP BY p.product_id, COALESCE(NULLIF(TRIM(p.product_name), ''), od.product_name), c.category_name
        ORDER BY qty DESC, revenue DESC
        LIMIT 8
    ");

    $topVariants = fetchAllRows($DB, "
        SELECT
            od.variant_id,
            COALESCE(v.product_id, '') AS product_id,
            COALESCE(NULLIF(TRIM(p.product_name), ''), od.product_name) AS product_name,
            COALESCE(od.variant_color, '') AS variant_color,
            COALESCE(od.variant_size, '') AS variant_size,
            COALESCE(SUM(od.qty), 0) AS qty,
            COALESCE(SUM(od.total_price), 0) AS revenue
        FROM order_detail od
        INNER JOIN orders o ON od.order_id = o.order_id
        LEFT JOIN variants v ON od.variant_id = v.variant_id
        LEFT JOIN product p ON v.product_id = p.product_id
        WHERE o.status_id IN (2, 3) {$dateCond}
        GROUP BY od.variant_id, v.product_id, COALESCE(NULLIF(TRIM(p.product_name), ''), od.product_name), od.variant_color, od.variant_size
        ORDER BY qty DESC, revenue DESC
        LIMIT 8
    ");

    // 9. Low Stock Alert List (real stock formula: variant_stock - SUM(qty where status_id IN (2, 3)))
    $lowStock = fetchAllRows($DB, "
        SELECT
            v.variant_id,
            v.product_id,
            v.product_api_id,
            COALESCE(NULLIF(TRIM(p.product_name), ''), v.variant_style) AS product_name,
            COALESCE(v.variant_color, '') AS variant_color,
            COALESCE(v.variant_size, '') AS variant_size,
            COALESCE(v.variant_stock, 0) AS variant_stock,
            COALESCE(SUM(CASE WHEN o.status_id IN (2, 3) THEN od.qty ELSE 0 END), 0) AS sold_qty,
            (COALESCE(v.variant_stock, 0) - COALESCE(SUM(CASE WHEN o.status_id IN (2, 3) THEN od.qty ELSE 0 END), 0)) AS remaining_stock,
            COALESCE(p.product_status, 0) AS product_status,
            COALESCE(p.reserve_id, 2) AS reserve_id
        FROM variants v
        LEFT JOIN product p ON v.product_id = p.product_id
        LEFT JOIN order_detail od ON v.variant_id = od.variant_id
        LEFT JOIN orders o ON od.order_id = o.order_id
        GROUP BY v.variant_id, v.product_id, v.product_api_id, p.product_name, v.variant_style,
                 v.variant_color, v.variant_size, v.variant_stock, p.product_status, p.reserve_id
        HAVING remaining_stock <= 10
        ORDER BY (COALESCE(p.product_status, 0) = 2) DESC, remaining_stock ASC, v.variant_id ASC
        LIMIT 12
    ");

    // 10. Recent Orders (latest 10)
    $recentOrders = fetchAllRows($DB, "
        SELECT
            o.order_id,
            o.order_no,
            o.user_id,
            COALESCE(NULLIF(TRIM(o.shipping_name), ''), NULLIF(TRIM(a.name), ''), NULLIF(TRIM(u.name), ''), 'ไม่ระบุชื่อ') AS buyer_name,
            o.total_price,
            o.shipping_price,
            o.shipping_type,
            o.status_id,
            o.order_status,
            o.receipt_id,
            o.receipt_link,
            o.parcel_number,
            o.create_date
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN tb_address a ON o.address_id = a.id
        ORDER BY o.order_id DESC
        LIMIT 10
    ");

    // 11. Pending Fulfillment / Receipt / Parcel Queue
    $pendingFulfillment = fetchAllRows($DB, "
        SELECT
            o.order_id,
            o.order_no,
            o.user_id,
            COALESCE(NULLIF(TRIM(o.shipping_name), ''), NULLIF(TRIM(a.name), ''), NULLIF(TRIM(u.name), ''), 'ไม่ระบุชื่อ') AS buyer_name,
            o.total_price,
            o.shipping_type,
            o.status_id,
            o.receipt_id,
            o.receipt_link,
            o.parcel_number,
            o.create_date
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN tb_address a ON o.address_id = a.id
        WHERE
            o.status_id = 2
            OR (o.status_id IN (2, 3) AND o.receipt_id = 1 AND (o.receipt_link IS NULL OR TRIM(o.receipt_link) = ''))
            OR (o.status_id IN (2, 3) AND o.shipping_type = '3' AND (o.parcel_number IS NULL OR TRIM(o.parcel_number) = ''))
        ORDER BY (o.status_id = 2) DESC, o.order_id DESC
        LIMIT 15
    ");

    // 12. Recent Reviews & Data Health Issues
    $recentReviews = fetchAllRows($DB, "
        SELECT
            r.review_id,
            r.review_rating,
            r.review_detail,
            r.product_id,
            COALESCE(p.product_name, r.product_id) AS product_name,
            COALESCE(u.name, 'สมาชิก') AS reviewer_name
        FROM review r
        LEFT JOIN product p ON r.product_id = p.product_id
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.review_id DESC
        LIMIT 5
    ");

    $nullSaleList = fetchAllRows($DB, "
        SELECT
            v.variant_id,
            v.product_id,
            v.product_api_id,
            COALESCE(NULLIF(TRIM(p.product_name), ''), v.variant_style) AS product_name,
            v.variant_color,
            v.variant_size
        FROM variants v
        LEFT JOIN product p ON v.product_id = p.product_id
        WHERE v.variant_sale IS NULL OR v.variant_sale <= 0
        LIMIT 5
    ");

    $orderCount = (int)($orderSummary["order_count"] ?? 0);
    $totalRevenue = (float)($orderSummary["total_revenue"] ?? 0);

    echo json_encode([
        "status" => "success",
        "period" => $period,
        "period_label" => $periodCfg["label"],
        "granularity" => $granularity,
        "kpi" => [
            "total_revenue" => $totalRevenue,
            "product_revenue" => (float)($orderSummary["product_revenue"] ?? 0),
            "shipping_revenue" => (float)($orderSummary["shipping_revenue"] ?? 0),
            "order_count" => $orderCount,
            "avg_order_value" => $orderCount > 0 ? ($totalRevenue / $orderCount) : 0.0,
            "member_orders" => (int)($orderSummary["member_orders"] ?? 0),
            "guest_orders" => (int)($orderSummary["guest_orders"] ?? 0),
            "items_sold" => (int)($itemSummary["items_sold"] ?? 0),
            "est_profit" => (float)($itemSummary["est_profit"] ?? 0),
            "est_cost" => (float)($itemSummary["est_cost"] ?? 0),
            "costed_items_sold" => (int)($itemSummary["costed_items_sold"] ?? 0),
            "reserved_items_sold" => (int)($itemSummary["reserved_items_sold"] ?? 0),
            "total_members" => (int)($userSummary["total_members"] ?? 0),
            "total_admins" => (int)($userSummary["total_admins"] ?? 0),
            "total_products" => (int)($productSummary["total_products"] ?? 0),
            "active_products" => (int)($productSummary["active_products"] ?? 0),
            "draft_products" => (int)($productSummary["draft_products"] ?? 0)
        ],
        "alerts" => [
            "waiting_payment" => (int)($opsOrders["waiting_payment"] ?? 0),
            "pending_shipping" => (int)($opsOrders["pending_shipping"] ?? 0),
            "shipped" => (int)($opsOrders["shipped"] ?? 0),
            "cancelled" => (int)($opsOrders["cancelled"] ?? 0),
            "missing_receipt" => (int)($opsOrders["missing_receipt"] ?? 0),
            "missing_parcel" => (int)($opsOrders["missing_parcel"] ?? 0),
            "status_mismatch" => (int)($opsOrders["status_mismatch"] ?? 0),
            "unread_messages" => (int)($msgSummary["unread_messages"] ?? 0),
            "unreplied_messages" => (int)($msgSummary["unreplied_messages"] ?? 0),
            "active_reserves" => (int)($reserveSummary["active_reserves"] ?? 0),
            "total_evidence" => (int)($evidenceSummary["total_evidence"] ?? 0),
            "open_evidence" => (int)($evidenceSummary["open_evidence"] ?? 0),
            "invalid_cart_qty" => (int)($cartHealth["invalid_cart_qty"] ?? 0),
            "null_sale_variants" => (int)($variantHealth["null_sale_variants"] ?? 0)
        ],
        "trend" => $trendRows,
        "status_breakdown" => array_values($statusMap),
        "category_sales" => $categoryRows,
        "shipping_breakdown" => $shippingBreakdown,
        "top_products" => $topProducts,
        "top_variants" => $topVariants,
        "low_stock" => $lowStock,
        "recent_orders" => $recentOrders,
        "pending_fulfillment" => $pendingFulfillment,
        "recent_reviews" => $recentReviews,
        "null_sale_list" => $nullSaleList
    ], JSON_UNESCAPED_UNICODE);
?>

