<?php
include 'db.php';
include 'auth.php';

if (isset($_POST['add_manual_ledger'])) {
    $name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['customer_phone'] ?? '');
    $amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    $due_date = !empty($_POST['due_date']) ? mysqli_real_escape_string($conn, $_POST['due_date']) : null;
    $notes = !empty($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : null;

    if ($name !== '' && $phone !== '' && $amount > 0) {
        $due_sql = $due_date ? "'$due_date'" : "NULL";
        $notes_sql = $notes ? "'$notes'" : "NULL";
        $conn->query("INSERT INTO udhaar_ledgers (customer_name, customer_phone, total_amount, paid_amount, balance_amount, status, due_date, notes)
                     VALUES ('$name', '$phone', '$amount', 0, '$amount', 'Unpaid', $due_sql, $notes_sql)");
    }
    header("Location: khata.php");
    exit;
}

if (isset($_POST['add_payment'])) {
    $ledger_id = (int)($_POST['ledger_id'] ?? 0);
    $pay_amount = isset($_POST['pay_amount']) ? (float)$_POST['pay_amount'] : 0;
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'Cash');
    $notes = !empty($_POST['payment_notes']) ? mysqli_real_escape_string($conn, $_POST['payment_notes']) : null;

    if ($ledger_id > 0 && $pay_amount > 0) {
        $ledger_q = $conn->query("SELECT * FROM udhaar_ledgers WHERE id=$ledger_id LIMIT 1");
        if ($ledger_q && $ledger_q->num_rows > 0) {
            $ledger = $ledger_q->fetch_assoc();

            $remaining = (float)$ledger['balance_amount'];
            if ($remaining > 0) {
                if ($pay_amount > $remaining) {
                    $pay_amount = $remaining;
                }

                $conn->begin_transaction();
                try {
                    $note_sql = $notes ? "'$notes'" : "NULL";

                    if (!$conn->query("INSERT INTO khata_payments (ledger_id, amount, payment_method, notes)
                                      VALUES ('$ledger_id', '$pay_amount', '$payment_method', $note_sql)")) {
                        throw new Exception($conn->error);
                    }

                    $new_paid = (float)$ledger['paid_amount'] + $pay_amount;
                    $new_balance = max((float)$ledger['total_amount'] - $new_paid, 0);
                    $new_status = ($new_balance <= 0) ? 'Paid' : (($new_paid > 0) ? 'Partial' : 'Unpaid');

                    if (!$conn->query("UPDATE udhaar_ledgers SET paid_amount='$new_paid', balance_amount='$new_balance', status='$new_status' WHERE id=$ledger_id")) {
                        throw new Exception($conn->error);
                    }

                    if (!empty($ledger['sale_id'])) {
                        $sale_id = (int)$ledger['sale_id'];
                        if (!$conn->query("UPDATE sales SET paid_amount='$new_paid', balance_amount='$new_balance' WHERE id=$sale_id")) {
                            throw new Exception($conn->error);
                        }
                    }

                    $conn->commit();
                } catch (Throwable $e) {
                    $conn->rollback();
                }
            }
        }
    }

    header("Location: khata.php");
    exit;
}

$summary = $conn->query("SELECT
    COALESCE(SUM(balance_amount), 0) AS total_due,
    COALESCE(SUM(CASE WHEN balance_amount > 0 THEN 1 ELSE 0 END), 0) AS active_accounts,
    COALESCE(SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() AND balance_amount > 0 THEN 1 ELSE 0 END), 0) AS overdue_accounts
    FROM udhaar_ledgers")->fetch_assoc();

$ledgers = $conn->query("SELECT ul.*, MAX(kp.paid_on) AS last_payment_on
    FROM udhaar_ledgers ul
    LEFT JOIN khata_payments kp ON kp.ledger_id = ul.id
    GROUP BY ul.id
    ORDER BY ul.balance_amount DESC, ul.id DESC");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Udhaar Khata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #090c10; color: #c9d1d9; }
        .card-glass { background: #161b22; border: 1px solid #30363d; border-radius: 14px; }
        .table > :not(caption) > * > * { color: #c9d1d9; border-bottom-color: #30363d; }
        .form-control-dark, .form-select-dark { background: #0d1117; border: 1px solid #30363d; color: #fff; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Udhaar Khata</h3>
            <div class="text-muted small">Recovery + reminders + payment posting</div>
        </div>
        <div class="d-flex gap-2">
            <a href="reports.php" class="btn btn-outline-info"><i class="fa fa-chart-column me-1"></i> Reports</a>
            <a href="index.php" class="btn btn-outline-light"><i class="fa fa-arrow-left me-1"></i> POS</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card-glass p-3"><div class="text-muted small">Total Recoverable</div><div class="fs-3 fw-bold text-warning">Rs. <?php echo number_format($summary['total_due']); ?></div></div></div>
        <div class="col-md-4"><div class="card-glass p-3"><div class="text-muted small">Active Accounts</div><div class="fs-3 fw-bold text-info"><?php echo (int)$summary['active_accounts']; ?></div></div></div>
        <div class="col-md-4"><div class="card-glass p-3"><div class="text-muted small">Overdue</div><div class="fs-3 fw-bold text-danger"><?php echo (int)$summary['overdue_accounts']; ?></div></div></div>
    </div>

    <div class="card-glass p-3 mb-4">
        <h6 class="mb-3 text-warning">Add Manual Udhaar</h6>
        <form method="POST" class="row g-2">
            <div class="col-md-3"><input name="customer_name" class="form-control form-control-dark" placeholder="Customer name" required></div>
            <div class="col-md-2"><input name="customer_phone" class="form-control form-control-dark" placeholder="Phone" required></div>
            <div class="col-md-2"><input name="total_amount" type="number" step="0.01" min="1" class="form-control form-control-dark" placeholder="Amount" required></div>
            <div class="col-md-2"><input name="due_date" type="date" class="form-control form-control-dark"></div>
            <div class="col-md-2"><input name="notes" class="form-control form-control-dark" placeholder="Note"></div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary" name="add_manual_ledger">Add</button></div>
        </form>
    </div>

    <div class="card-glass p-3">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Last Payment</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $ledgers->fetch_assoc()):
                        $msg = "Assalam-o-Alaikum {$row['customer_name']}, aap ka baki udhaar Rs. " . number_format($row['balance_amount']) . " hai. Meharbani kar ke payment clear karein. Shukriya.";
                        $wa_link = "https://wa.me/92" . ltrim(preg_replace('/[^0-9]/', '', $row['customer_phone']), '0') . "?text=" . urlencode($msg);
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($row['customer_phone']); ?></div>
                        </td>
                        <td>Rs. <?php echo number_format($row['total_amount']); ?></td>
                        <td class="text-success">Rs. <?php echo number_format($row['paid_amount']); ?></td>
                        <td class="text-warning fw-bold">Rs. <?php echo number_format($row['balance_amount']); ?></td>
                        <td>
                            <?php if($row['status'] === 'Paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif($row['status'] === 'Partial'): ?>
                                <span class="badge bg-info">Partial</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['due_date'] ? date('d M Y', strtotime($row['due_date'])) : '-'; ?></td>
                        <td><?php echo $row['last_payment_on'] ? date('d M, h:i A', strtotime($row['last_payment_on'])) : '-'; ?></td>
                        <td class="text-end">
                            <?php if((float)$row['balance_amount'] > 0): ?>
                                <button class="btn btn-sm btn-primary" onclick="openPaymentModal(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($row['customer_name'], ENT_QUOTES); ?>', <?php echo (float)$row['balance_amount']; ?>)"><i class="fa fa-money-bill me-1"></i>Receive</button>
                                <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp me-1"></i>Reminder</a>
                            <?php endif; ?>
                            <?php if(!empty($row['sale_id'])): ?>
                                <a href="invoice.php?sale_id=<?php echo (int)$row['sale_id']; ?>" target="_blank" class="btn btn-sm btn-outline-light">Invoice</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Receive Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="modal-body">
                <input type="hidden" name="ledger_id" id="pay_ledger_id">
                <div class="mb-2 small text-muted">Customer: <span class="text-white" id="pay_cust_name"></span></div>
                <div class="mb-3 small text-muted">Remaining: <span class="text-warning" id="pay_remaining"></span></div>
                <div class="mb-3">
                    <label class="small mb-1">Amount</label>
                    <input type="number" step="0.01" min="1" name="pay_amount" id="pay_amount" class="form-control form-control-dark" required>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Method</label>
                    <select name="payment_method" class="form-select form-select-dark">
                        <option>Cash</option><option>JazzCash</option><option>EasyPaisa</option><option>Raast QR</option><option>Bank Transfer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Note</label>
                    <input name="payment_notes" class="form-control form-control-dark" placeholder="optional note">
                </div>
                <button name="add_payment" class="btn btn-success w-100">Save Payment</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openPaymentModal(id, name, remaining) {
    document.getElementById('pay_ledger_id').value = id;
    document.getElementById('pay_cust_name').innerText = name;
    document.getElementById('pay_remaining').innerText = 'Rs. ' + Number(remaining).toLocaleString();
    const amountInput = document.getElementById('pay_amount');
    amountInput.max = remaining;
    amountInput.value = remaining;
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
</script>
</body>
</html>
