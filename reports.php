<?php
include 'db.php';
include 'auth.php';

$report_date = !empty($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : date('Y-m-d');

$day = $conn->query("SELECT
    COALESCE(SUM(total_amount),0) as total_sale,
    COALESCE(SUM(paid_amount),0) as received,
    COALESCE(SUM(balance_amount),0) as credit
    FROM sales WHERE DATE(sale_date)='$report_date'")->fetch_assoc();

$payment_breakdown = $conn->query("SELECT payment_method, COUNT(*) as orders, SUM(total_amount) as gross, SUM(paid_amount) as received
    FROM sales WHERE DATE(sale_date)='$report_date'
    GROUP BY payment_method
    ORDER BY received DESC");

$top_items = $conn->query("SELECT si.device_name, COUNT(*) as qty, SUM(si.price) as amount
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
    WHERE DATE(s.sale_date)='$report_date'
    GROUP BY si.device_name
    ORDER BY qty DESC, amount DESC
    LIMIT 10");

$low_stock = $conn->query("SELECT id, brand, model, stock_qty, reorder_level, vendor_name, vendor_phone
    FROM products
    WHERE stock_qty <= reorder_level
    ORDER BY stock_qty ASC, id DESC");

$khata_summary = $conn->query("SELECT
    COALESCE(SUM(total_amount),0) as total_credited,
    COALESCE(SUM(paid_amount),0) as total_recovered,
    COALESCE(SUM(balance_amount),0) as outstanding
    FROM udhaar_ledgers")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #090c10; color: #c9d1d9; }
        .card-glass { background: #161b22; border: 1px solid #30363d; border-radius: 14px; }
        .table > :not(caption) > * > * { color: #c9d1d9; border-bottom-color: #30363d; }
        .form-control-dark { background: #0d1117; border: 1px solid #30363d; color: #fff; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Business Reports</h3>
            <div class="small text-muted">Daily cashflow, payment mix, and low-stock alerts</div>
        </div>
        <div class="d-flex gap-2">
            <a href="khata.php" class="btn btn-outline-warning"><i class="fa fa-book me-1"></i> Khata</a>
            <a href="index.php" class="btn btn-outline-light"><i class="fa fa-arrow-left me-1"></i> POS</a>
        </div>
    </div>

    <form method="GET" class="card-glass p-3 mb-4 d-flex gap-2 align-items-end">
        <div>
            <label class="small text-muted mb-1">Report Date</label>
            <input type="date" name="date" class="form-control form-control-dark" value="<?php echo $report_date; ?>">
        </div>
        <button class="btn btn-primary">Load</button>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card-glass p-3"><div class="small text-muted">Total Sale (<?php echo date('d M Y', strtotime($report_date)); ?>)</div><div class="fs-3 fw-bold text-success">Rs. <?php echo number_format($day['total_sale']); ?></div></div></div>
        <div class="col-md-4"><div class="card-glass p-3"><div class="small text-muted">Cash In Hand / Received</div><div class="fs-3 fw-bold text-info">Rs. <?php echo number_format($day['received']); ?></div></div></div>
        <div class="col-md-4"><div class="card-glass p-3"><div class="small text-muted">New Credit (Udhaar)</div><div class="fs-3 fw-bold text-warning">Rs. <?php echo number_format($day['credit']); ?></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card-glass p-3"><div class="small text-muted">Khata Total Credited</div><div class="fs-4 fw-bold">Rs. <?php echo number_format($khata_summary['total_credited']); ?></div></div></div>
        <div class="col-md-4"><div class="card-glass p-3"><div class="small text-muted">Khata Recovered</div><div class="fs-4 fw-bold text-success">Rs. <?php echo number_format($khata_summary['total_recovered']); ?></div></div></div>
        <div class="col-md-4"><div class="card-glass p-3"><div class="small text-muted">Khata Outstanding</div><div class="fs-4 fw-bold text-warning">Rs. <?php echo number_format($khata_summary['outstanding']); ?></div></div></div>
    </div>

    <div class="card-glass p-3 mb-4">
        <h6 class="mb-3">Payment Method Breakdown</h6>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead><tr><th>Method</th><th>Orders</th><th>Gross</th><th>Received</th></tr></thead>
                <tbody>
                    <?php while($row = $payment_breakdown->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo (int)$row['orders']; ?></td>
                            <td>Rs. <?php echo number_format($row['gross']); ?></td>
                            <td class="text-success">Rs. <?php echo number_format($row['received']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-glass p-3 mb-4">
        <h6 class="mb-3">Top Selling Items (Daily)</h6>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead><tr><th>Device</th><th>Qty</th><th>Total</th></tr></thead>
                <tbody>
                    <?php while($item = $top_items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['device_name']); ?></td>
                            <td><?php echo (int)$item['qty']; ?></td>
                            <td class="text-success">Rs. <?php echo number_format($item['amount']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-glass p-3">
        <h6 class="mb-3 text-warning">Low Stock + Supplier Reorder</h6>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead><tr><th>Product</th><th>Stock</th><th>Reorder At</th><th>Supplier</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    <?php while($p = $low_stock->fetch_assoc()):
                        $supplierPhone = preg_replace('/[^0-9]/', '', (string)$p['vendor_phone']);
                        if (substr($supplierPhone, 0, 1) === '0') {
                            $supplierPhone = '92' . substr($supplierPhone, 1);
                        }
                        $msg = urlencode('Salam, ' . $p['model'] . ' ka stock low hai. Reorder required.');
                        $wa = (!empty($supplierPhone)) ? "https://wa.me/{$supplierPhone}?text={$msg}" : '#';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['brand'] . ' ' . $p['model']); ?></td>
                            <td><span class="badge bg-danger"><?php echo (int)$p['stock_qty']; ?></span></td>
                            <td><?php echo (int)$p['reorder_level']; ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($p['vendor_name'] ?: '-'); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($p['vendor_phone'] ?: 'No phone'); ?></small>
                            </td>
                            <td class="text-end">
                                <?php if ($wa !== '#'): ?>
                                    <a href="<?php echo $wa; ?>" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp me-1"></i>Reorder</a>
                                <?php else: ?>
                                    <a href="dashboard.php?tab=inventory" class="btn btn-sm btn-outline-light">Add Supplier Phone</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
