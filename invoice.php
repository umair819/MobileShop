<?php
include 'db.php';
include 'auth.php';

if (!isset($_GET['sale_id'])) { header("Location: index.php"); exit; }
$sale_id = (int)$_GET['sale_id'];

$sale = $conn->query("SELECT * FROM sales WHERE id = $sale_id")->fetch_assoc();
if (!$sale) { die("Sale not found."); }

$setting = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();
$is_thermal = ($setting['printer_type'] == 'Thermal');

$sale_total = (float)($sale['total_amount'] ?? 0);
$sale_paid = isset($sale['paid_amount']) ? (float)$sale['paid_amount'] : $sale_total;
$sale_balance = isset($sale['balance_amount']) ? (float)$sale['balance_amount'] : 0;
$payment_method = !empty($sale['payment_method']) ? $sale['payment_method'] : 'Cash';

$sql_items = "SELECT si.*, p.storage, p.color, p.pta_status, p.condition_status, p.type
              FROM sale_items si
              LEFT JOIN products p ON si.imei_number = p.imei_number
              WHERE si.sale_id = $sale_id";
$items_q = $conn->query($sql_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $sale_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Inconsolata:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #555; font-family: 'Inter', sans-serif; padding: 20px; }

        .invoice-a4 {
            max-width: 800px; margin: auto; background: white; padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); min-height: 1000px; position: relative;
        }
        .header-title { font-weight: 800; font-size: 32px; text-transform: uppercase; color: #000; letter-spacing: 1px; }
        .invoice-badge { background: #000; color: white; padding: 5px 15px; font-weight: bold; float: right; font-size: 14px; }
        .table-a4 th { background: #f4f4f4; text-transform: uppercase; font-size: 11px; font-weight: 700; padding: 12px; border-bottom: 2px solid #000; }
        .table-a4 td { padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; }
        .spec-badge { font-size: 10px; background: #eee; padding: 2px 6px; border-radius: 4px; margin-right: 3px; border: 1px solid #ddd; }

        .invoice-thermal {
            width: 80mm; margin: auto; background: white; padding: 10px 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); color: #000;
            font-family: 'Inconsolata', monospace;
        }
        .th-header { text-align: center; margin-bottom: 10px; }
        .shop-name { font-size: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: -0.5px; line-height: 1; }
        .th-divider { border-top: 2px dashed #000; margin: 8px 0; }
        .th-divider-light { border-top: 1px dashed #888; margin: 5px 0; }

        .th-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 2px; }

        .th-item-box { margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px dotted #ccc; }
        .th-item-name { font-size: 13px; font-weight: 700; }
        .th-item-meta { font-size: 10px; color: #444; }

        .th-total-box { background: #000; color: white; padding: 5px 10px; margin-top: 10px; border-radius: 4px; }
        .th-total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; }

        .barcode {
            height: 30px; width: 100%; margin: 10px 0;
            background: repeating-linear-gradient(to right, #000 0px, #000 2px, #fff 2px, #fff 4px, #000 4px, #000 5px, #fff 5px, #fff 7px);
        }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .invoice-a4, .invoice-thermal { box-shadow: none; margin: 0; width: 100%; max-width: 100%; }

            <?php if($is_thermal): ?>
                @page { size: 80mm auto; margin: 0; }
                body { width: 80mm; }
                .invoice-thermal { padding: 5px; }
            <?php else: ?>
                @page { size: A4; margin: 10mm; }
            <?php endif; ?>
        }
    </style>
</head>
<body>

<?php if(!$is_thermal): ?>
<div class="invoice-a4">
    <div class="row mb-5 border-bottom pb-4">
        <div class="col-7">
            <div class="header-title"><?php echo $setting['shop_name']; ?></div>
            <div class="text-muted small mt-2">
                <i class="fa fa-map-marker-alt me-1"></i> <?php echo nl2br($setting['shop_address']); ?><br>
                <i class="fa fa-phone me-1"></i> <?php echo $setting['shop_contact']; ?>
            </div>
        </div>
        <div class="col-5 text-end">
            <div class="invoice-badge">INVOICE #<?php echo str_pad($sale_id, 4, '0', STR_PAD_LEFT); ?></div>
            <div class="mt-2 text-muted small">Date: <?php echo date('d M Y, h:i A', strtotime($sale['sale_date'])); ?></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 bg-light p-3 rounded">
            <h6 class="fw-bold text-uppercase text-secondary small mb-1">Customer Details:</h6>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold fs-5 text-dark"><?php echo $sale['customer_name']; ?></span>
                    <span class="ms-3 text-muted"><i class="fa fa-phone me-1"></i><?php echo $sale['customer_phone']; ?></span>
                </div>
                <div class="font-monospace bg-white border px-2 rounded">CNIC: <?php echo $sale['customer_cnic']; ?></div>
            </div>
        </div>
    </div>

    <table class="table table-a4 w-100 mb-4">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="50%">Device Description</th>
                <th width="25%">IMEI / Serial</th>
                <th width="20%" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; $items_q->data_seek(0); while($item = $items_q->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td>
                    <div class="fw-bold text-dark" style="font-size: 15px;"><?php echo $item['device_name']; ?></div>
                    <div class="mt-1">
                        <span class="spec-badge"><?php echo $item['storage']; ?></span>
                        <span class="spec-badge"><?php echo $item['color']; ?></span>
                        <span class="spec-badge"><?php echo $item['pta_status']; ?></span>
                        <?php if($item['type'] == 'Used'): ?>
                            <span class="spec-badge bg-warning text-dark border-warning">Cond: <?php echo $item['condition_status']; ?></span>
                        <?php else: ?>
                            <span class="spec-badge bg-success text-white border-success">BOX PACK</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="font-monospace small text-muted"><?php echo $item['imei_number']; ?></td>
                <td class="text-end fw-bold fs-6">Rs. <?php echo number_format($item['price']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="row justify-content-end mb-5">
        <div class="col-5">
            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                <span class="text-muted">Sub Total</span>
                <span class="fw-bold">Rs. <?php echo number_format($sale_total); ?></span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                <span class="text-muted">Payment Method</span>
                <span class="fw-bold"><?php echo htmlspecialchars($payment_method); ?></span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                <span class="text-muted">Paid</span>
                <span class="fw-bold text-success">Rs. <?php echo number_format($sale_paid); ?></span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                <span class="text-muted">Balance</span>
                <span class="fw-bold text-warning">Rs. <?php echo number_format($sale_balance); ?></span>
            </div>
            <div class="d-flex justify-content-between bg-dark text-white p-3 rounded shadow-sm">
                <span class="fw-bold">GRAND TOTAL</span>
                <span class="fw-bold fs-4">Rs. <?php echo number_format($sale_total); ?></span>
            </div>
        </div>
    </div>

    <div class="position-absolute bottom-0 start-0 w-100 p-5">
        <div class="row align-items-end">
            <div class="col-8">
                <div class="small text-muted" style="font-size: 11px; line-height: 1.6;">
                    <strong>Terms & Conditions:</strong><br>
                    1. Goods once sold will not be returned or exchanged.<br>
                    2. Check your device physically before leaving the counter.<br>
                    3. No warranty for dead, screen damage, or water damage devices.<br>
                    4. Software warranty is valid for 3 days only.
                </div>
            </div>
            <div class="col-4 text-center">
                <div style="border-top: 1px solid #000; padding-top: 5px; font-weight: bold; font-size: 12px;">Authorized Signature</div>
            </div>
        </div>
        <div class="text-center mt-4 text-muted small" style="font-size: 10px;">
            Software Developed by <strong class="text-warning">TechBrain</strong>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($is_thermal): ?>
<div class="invoice-thermal">

    <div class="th-header">
        <div class="shop-name"><?php echo $setting['shop_name']; ?></div>
        <div style="font-size: 10px; margin-top: 5px;"><?php echo $setting['shop_address']; ?></div>
        <div style="font-size: 12px; font-weight: bold; margin-top: 3px;"><?php echo $setting['shop_contact']; ?></div>
    </div>

    <div class="th-divider"></div>

    <div class="th-row">
        <span>Invoice: <strong>#<?php echo $sale_id; ?></strong></span>
        <span><?php echo date('d-M-y'); ?></span>
    </div>
    <div class="th-row">
        <span>Time: <?php echo date('h:i A'); ?></span>
    </div>

    <div class="th-divider-light"></div>

    <div class="th-row">
        <span>Customer:</span>
        <span class="fw-bold"><?php echo $sale['customer_name']; ?></span>
    </div>
    <div class="th-row">
        <span>Phone:</span>
        <span><?php echo $sale['customer_phone']; ?></span>
    </div>

    <div class="th-divider"></div>

    <div class="th-row text-uppercase" style="font-weight: 800; font-size: 11px;">
        <span>Item Description</span>
        <span>Amount</span>
    </div>
    <div class="th-divider"></div>

    <?php $items_q->data_seek(0); while($item = $items_q->fetch_assoc()): ?>
    <div class="th-item-box">
        <div class="th-row">
            <span class="th-item-name"><?php echo $item['device_name']; ?></span>
            <span class="fw-bold">Rs. <?php echo number_format($item['price']); ?></span>
        </div>
        <div class="th-item-meta">
            <?php echo $item['storage']; ?> | <?php echo $item['color']; ?> | <?php echo $item['pta_status']; ?>
            <?php if($item['type'] == 'Used') echo "| Cond: " . $item['condition_status']; ?>
        </div>
        <div class="th-item-meta font-monospace">IMEI: <?php echo $item['imei_number']; ?></div>
    </div>
    <?php endwhile; ?>

    <div class="th-divider"></div>
    <div class="th-row"><span>Method</span><span><?php echo htmlspecialchars($payment_method); ?></span></div>
    <div class="th-row"><span>Paid</span><span>Rs. <?php echo number_format($sale_paid); ?></span></div>
    <div class="th-row"><span>Balance</span><span>Rs. <?php echo number_format($sale_balance); ?></span></div>

    <div class="th-total-box">
        <div class="th-total-row">
            <span>NET TOTAL</span>
            <span>Rs. <?php echo number_format($sale_total); ?></span>
        </div>
    </div>

    <div style="text-align: center; margin-top: 15px; font-size: 10px;">
        <p>*** NO RETURN / NO EXCHANGE ***</p>
        <p>Software warranty 3 days only.<br>Check device before leaving.</p>

        <div class="barcode"></div>
        <div style="font-size: 9px; margin-top: 5px;">Powered by <b>TechBrain</b></div>
    </div>

</div>
<?php endif; ?>

<div class="text-center mt-4 mb-5 no-print gap-2">
    <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow"><i class="fa fa-print me-2"></i> Print Invoice</button>
    <a href="index.php" class="btn btn-dark fw-bold px-4 shadow"><i class="fa fa-home me-2"></i> New Sale</a>
    <?php if($sale_balance > 0): ?>
        <a href="khata.php" class="btn btn-warning fw-bold px-4 shadow"><i class="fa fa-book me-2"></i> Open Khata</a>
    <?php endif; ?>
</div>

</body>
</html>
