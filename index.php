<?php
include 'db.php';
include 'auth.php';

// Fetch Only Active Stock
$result = $conn->query("SELECT * FROM products WHERE stock_qty > 0 ORDER BY id DESC");
$cust_res = $conn->query("SELECT * FROM customers ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title><?php echo $shop_name; ?> | POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --bg-dark: #090c10; --card-bg: #161b22; --neon: #58a6ff;
            --border-color: #30363d; --header-bg: rgba(22, 27, 34, 0.85);
        }
        body { background: var(--bg-dark); color: #c9d1d9; font-family: 'Inter', sans-serif; overflow: hidden; }
        .product-area { height: 100vh; overflow-y: auto; padding: 25px; display: flex; flex-direction: column; }
        .glass-header {
            background: var(--header-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 12px 25px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3); margin-bottom: 30px;
        }

        .phone-card {
            background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;
            overflow: hidden; cursor: pointer; transition: all 0.25s; position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .phone-card:not(.on-hold):hover { transform: translateY(-5px); border-color: var(--neon); box-shadow: 0 10px 25px rgba(88, 166, 255, 0.15); }

        .img-container {
            height: 140px; background: radial-gradient(circle at center, #1c2128 0%, #0d1117 100%);
            display: flex; align-items: center; justify-content: center; position: relative; padding: 10px;
        }
        .phone-img { max-height: 100%; max-width: 100%; object-fit: contain; filter: drop-shadow(0 5px 10px rgba(0,0,0,0.5)); }

        .card-body-custom { padding: 12px 15px; }
        .model-title { font-size: 0.95rem; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
        .brand-text { font-size: 0.75rem; color: #8b949e; text-transform: uppercase; margin-bottom: 8px; }
        .specs-row { display: flex; justify-content: space-between; font-size: 0.75rem; color: #c9d1d9; background: rgba(255,255,255,0.03); padding: 4px 8px; border-radius: 6px; margin-bottom: 8px; }
        .price-text { font-size: 1.1rem; font-weight: 700; color: #3fb950; }

        .badge-pta { position: absolute; top: 8px; left: 8px; font-size: 9px; padding: 4px 8px; border-radius: 6px; z-index: 5; font-weight: 600; }
        .badge-cond { position: absolute; top: 8px; right: 8px; font-size: 10px; background: rgba(0,0,0,0.7); color: white; padding: 3px 7px; border-radius: 6px; z-index: 5; font-weight: 600; border: 1px solid rgba(255,255,255,0.2); }

        .float-cart { position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px; background: linear-gradient(135deg, #1f6feb, #58a6ff); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 8px 25px rgba(31, 111, 235, 0.5); border: none; z-index: 100; transition: transform 0.2s; }
        .float-cart:hover { transform: scale(1.1); }
        .cart-count { position: absolute; top: 0; right: 0; background: #d32f2f; font-size: 13px; padding: 4px 9px; border-radius: 50%; border: 3px solid var(--bg-dark); font-weight: bold; }

        .offcanvas { background: #0d1117; border-left: 1px solid var(--border-color); width: 400px; }
        .offcanvas.wide-drawer { width: 950px !important; }
        .cart-item { background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; margin-bottom: 8px; display: flex; align-items: center; font-size: 0.9rem; }
        .form-control-dark, .form-select-dark { background: #010409; border: 1px solid var(--border-color); color: white; }
        .form-control-dark:focus, .form-select-dark:focus { background: #010409; color: white; border-color: var(--neon); box-shadow: none; }
        .cat-btn { font-size: 0.85rem; font-weight: 500; border-color: var(--border-color); color: #8b949e; }
        .cat-btn:hover, .cat-btn.active { background: #21262d; color: white; border-color: #8b949e; }

        .app-footer { margin-top: auto; padding: 20px 0; text-align: center; color: #6c757d; font-size: 0.8rem; border-top: 1px solid var(--border-color); }
        .app-footer a { color: #ffc107; text-decoration: none; font-weight: 600; letter-spacing: 0.5px; }
        .app-footer a:hover { text-decoration: underline; color: #ffca2c; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="product-area">

        <div class="glass-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px;"><i class="fa fa-bolt text-white fs-5"></i></div>
                <div><h5 class="m-0 fw-bold text-white"><?php echo $shop_name; ?></h5><div class="small text-muted" style="font-size: 0.75rem;">POS Terminal</div></div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="d-none d-md-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark text-light rounded-pill px-3 active cat-btn" onclick="filterStock('all', this)">All</button>
                    <button class="btn btn-sm btn-outline-dark text-light rounded-pill px-3 cat-btn" onclick="filterStock('New', this)">New</button>
                    <button class="btn btn-sm btn-outline-dark text-light rounded-pill px-3 cat-btn" onclick="filterStock('Used', this)">Used</button>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary border-end-0 rounded-start-pill ps-3"><i class="fa fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control form-control-dark border-start-0 rounded-end-pill" placeholder="Search device..." style="width: 200px;">
                </div>
                <a href="reports.php" class="btn btn-dark border-secondary rounded-circle shadow-sm" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center" title="Reports"><i class="fa fa-chart-column"></i></a>
                <a href="khata.php" class="btn btn-dark border-secondary rounded-circle shadow-sm" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center" title="Udhaar Khata"><i class="fa fa-book"></i></a>
                <a href="dashboard.php" class="btn btn-dark border-secondary rounded-circle shadow-sm" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center" title="Dashboard"><i class="fa fa-cog"></i></a>
                <a href="logout.php" class="btn btn-danger border-danger rounded-circle shadow-sm" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center" title="Logout"><i class="fa fa-power-off"></i></a>
            </div>
        </div>

        <div class="row g-4" id="phoneGrid">
            <?php while($row = $result->fetch_assoc()):
                $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                $is_hold = ($row['availability_status'] == 'On Approval');
                $is_new = ($row['type'] == 'New');
                $pta_color = ($row['pta_status'] == 'Approved' || $row['pta_status'] == 'VIP Approved') ? 'success' : 'danger';
                $pta_text = ($row['pta_status'] == 'Non-PTA') ? 'NON-PTA' : 'PTA OK';
                $borderColor = $is_hold ? '#6c757d' : (($pta_color == 'success') ? '#2ea043' : '#da3633');
            ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 filter-item" data-type="<?php echo $row['type']; ?>">
                <div class="phone-card h-100 <?php echo $is_hold ? 'on-hold' : ''; ?>"
                     onclick="<?php echo $is_hold ? '' : 'viewProduct('.$json.')'; ?>"
                     style="border-top: 3px solid <?php echo $borderColor; ?>; <?php echo $is_hold ? 'opacity: 0.6; cursor: not-allowed;' : ''; ?>">

                    <span class="badge bg-<?php echo $pta_color; ?> badge-pta shadow"><?php echo $pta_text; ?></span>
                    <?php if(!$is_new): ?><span class="badge-cond"><?php echo $row['condition_status']; ?></span><?php endif; ?>

                    <div class="img-container">
                        <img src="<?php echo $row['device_image']; ?>" class="phone-img"
                             style="<?php echo $is_hold ? 'filter: grayscale(100%);' : ''; ?>"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgc3R5bGU9ImJhY2tncm91bmQ6IzMzMyI+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM2NjYiPkltZzwvdGV4dD48L3N2Zz4='">
                        <?php if($is_hold): ?><div class="position-absolute badge bg-dark border border-warning text-warning shadow px-3 py-2"><i class="fa fa-lock me-1"></i> ON HOLD</div><?php endif; ?>
                    </div>

                    <div class="card-body-custom">
                        <div class="brand-text"><?php echo $row['brand']; ?></div>
                        <div class="model-title" title="<?php echo $row['model']; ?>"><?php echo $row['model']; ?></div>
                        <div class="specs-row"><span><i class="fa fa-hdd me-1"></i><?php echo $row['storage']; ?></span><span><?php echo $row['color']; ?></span></div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="price-text">Rs. <?php echo number_format($row['sale_price']); ?></div>
                            <div class="status-text">
                                <?php if($is_new): ?><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">NEW</span><?php else: ?><span class="text-warning small"><i class="fa fa-battery-half"></i> <?php echo $row['battery_health']; ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <footer class="app-footer">
            &copy; <?php echo date('Y'); ?> <?php echo $shop_name; ?>. Developed by <a href="https://wa.me/923001234567" target="_blank"><i class="fab fa-whatsapp me-1"></i>TechBrain</a>
        </footer>
    </div>
</div>

<button class="float-cart" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer"><i class="fa fa-shopping-bag"></i><span class="cart-count" id="cartCountBtn">0</span></button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="cartDrawer">
    <div class="d-flex h-100">
        <div class="cart-panel d-flex flex-column" style="width:100%; transition: width 0.5s;">
            <div class="offcanvas-header border-bottom border-secondary py-3"><h6 class="offcanvas-title fw-bold">Shopping Cart</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
            <div class="flex-grow-1 overflow-auto p-3" id="cartList"><div class="text-center text-muted mt-5 small">Cart is Empty</div></div>
            <div class="p-3 border-top border-secondary bg-dark"><div class="d-flex justify-content-between mb-3"><span>Total:</span> <span class="fw-bold text-success fs-5" id="grandTotal1">Rs. 0</span></div><button class="btn btn-primary w-100 fw-bold py-2 rounded-3" id="nextBtn" onclick="expandDrawer()">PROCEED TO CHECKOUT <i class="fa fa-arrow-right ms-2"></i></button></div>
        </div>
        <div class="buyer-panel">
            <div class="offcanvas-header border-bottom border-secondary py-3"><h6 class="offcanvas-title text-primary">Customer Information</h6><button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="collapseDrawer()">Back</button></div>
            <div class="p-4 overflow-auto flex-grow-1">
                <form action="save_sale.php" method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100" id="saleForm">
                    <div id="hiddenInputs"></div>
                    <input type="hidden" name="sale_total" id="sale_total" value="0">
                    <div class="mb-3"><div class="d-flex justify-content-between"><label class="small text-muted mb-1">Customer Name</label><div id="custStatus"></div></div><div class="input-group"><input list="seller_list" id="seller_search" name="c_name" class="form-control form-control-dark" placeholder="Type to search..." required><button type="button" class="btn btn-dark border-secondary" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fa fa-plus"></i></button></div><datalist id="seller_list"><?php $cust_res->data_seek(0); while($c = $cust_res->fetch_assoc()): ?><option value="<?php echo $c['name']; ?>"><?php echo $c['phone']; ?></option><?php endwhile; ?></datalist></div>
                    <div class="row g-3 mb-3"><div class="col-6"><label class="small text-muted mb-1">Phone</label><input type="text" name="c_phone" id="c_phone" class="form-control form-control-dark" placeholder="0300..." required></div><div class="col-6"><label class="small text-muted mb-1">CNIC</label><input type="text" name="c_cnic" id="c_cnic" class="form-control form-control-dark" placeholder="42101..." required></div></div>

                    <div class="p-3 bg-black rounded-3 border border-secondary mb-3 bg-opacity-25">
                        <h6 class="text-warning small mb-3">CNIC Evidence</h6>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between"><label class="small text-muted">Front</label><span id="status_front" class="small text-danger" style="font-size:10px;">* Required</span></div>
                            <input type="file" name="buyer_front" id="input_front" class="form-control form-control-dark form-control-sm mt-1" required>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between"><label class="small text-muted">Back</label><span id="status_back" class="small text-danger" style="font-size:10px;">* Required</span></div>
                            <input type="file" name="buyer_back" id="input_back" class="form-control form-control-dark form-control-sm mt-1" required>
                        </div>
                    </div>

                    <div class="p-3 bg-black rounded-3 border border-secondary mb-3 bg-opacity-25">
                        <h6 class="text-info small mb-3">Payment</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small text-muted mb-1">Method</label>
                                <select name="payment_method" id="payment_method" class="form-select form-select-dark">
                                    <option value="Cash">Cash</option>
                                    <option value="JazzCash">JazzCash</option>
                                    <option value="EasyPaisa">EasyPaisa</option>
                                    <option value="Raast QR">Raast QR</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Udhaar">Udhaar</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">Paid Now</label>
                                <input type="number" name="paid_amount" id="paid_amount" class="form-control form-control-dark" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <label class="small text-muted mb-1">Due Date (if Udhaar)</label>
                                <input type="date" name="due_date" id="due_date" class="form-control form-control-dark">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">Notes</label>
                                <input type="text" name="sale_notes" class="form-control form-control-dark" placeholder="optional note">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3 border-top border-secondary pt-2">
                            <span class="small text-muted">Remaining</span>
                            <span class="fw-bold text-warning" id="balancePreview">Rs. 0</span>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top border-secondary"><div class="d-flex justify-content-between mb-2"><span>Payable:</span> <span class="fw-bold text-success fs-5" id="grandTotal2">Rs. 0</span></div><button type="submit" class="btn btn-success w-100 fw-bold py-3 rounded-3 shadow">CONFIRM SALE & PRINT</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content" style="background: #161b22; color: white; border: 1px solid #30363d; border-radius: 15px; overflow: hidden;"><div class="modal-body p-0"><button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 10;"></button><div class="row g-0"><div class="col-md-5 bg-black d-flex align-items-center justify-content-center p-4"><img id="detail_img" src="" style="max-width: 100%; max-height: 400px; object-fit: contain;"></div><div class="col-md-7 p-4 d-flex flex-column"><div class="mb-3"><div class="text-primary small text-uppercase fw-bold" id="detail_brand">Brand</div><h2 class="fw-bold m-0" id="detail_model">Model Name</h2></div><div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-3" style="background: rgba(88, 166, 255, 0.08); border: 1px solid rgba(88, 166, 255, 0.2);"><div><div class="small text-muted text-uppercase">Price</div><div class="fs-2 fw-bold text-success" id="detail_price">Rs. 0</div></div><div class="text-end"><span class="badge bg-light text-dark fs-6 border" id="detail_type">USED</span></div></div><div class="row g-3 mb-4"><div class="col-6"><div class="p-2 border border-secondary rounded bg-dark bg-opacity-50"><small class="text-muted d-block mb-1">Storage</small><span class="fw-bold fs-5" id="detail_storage"></span></div></div><div class="col-6"><div class="p-2 border border-secondary rounded bg-dark bg-opacity-50"><small class="text-muted d-block mb-1">Color</small><span class="fw-bold fs-5" id="detail_color"></span></div></div><div class="col-4"><div class="p-2 border border-secondary rounded bg-dark bg-opacity-50"><small class="text-muted d-block">PTA</small><span class="fw-bold text-warning" id="detail_pta"></span></div></div><div class="col-4"><div class="p-2 border border-secondary rounded bg-dark bg-opacity-50"><small class="text-muted d-block">Condition</small><span class="fw-bold" id="detail_cond"></span></div></div><div class="col-4"><div class="p-2 border border-secondary rounded bg-dark bg-opacity-50"><small class="text-muted d-block">Battery</small><span class="fw-bold text-success" id="detail_bh"></span></div></div></div><div class="mt-auto d-flex gap-2"><button class="btn btn-primary flex-grow-1 py-3 fw-bold rounded-3 shadow" id="btn_add_cart"><i class="fa fa-cart-plus me-2"></i> Add to Cart</button><button class="btn btn-success flex-grow-1 py-3 fw-bold rounded-3 shadow" onclick="shareProduct()"><i class="fab fa-whatsapp me-2"></i> Share on WhatsApp</button></div></div></div></div></div></div></div>
<div class="modal fade" id="addCustomerModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-body text-black">Use Dashboard to Add Customer</div></div></div></div>

<script>
    let cart = [];
    let currentProduct = null;
    let shareData = null;
    let cartTotal = 0;

    const drawerEl = document.getElementById('cartDrawer');
    let bsOffcanvas;

    document.addEventListener('DOMContentLoaded', function() {
        bsOffcanvas = new bootstrap.Offcanvas(drawerEl);
        document.getElementById('payment_method').addEventListener('change', syncPaymentFields);
        document.getElementById('paid_amount').addEventListener('input', updateBalancePreview);
        syncPaymentFields();
    });

    function viewProduct(p) {
        currentProduct = p;
        shareData = p;
        document.getElementById('detail_img').src = p.device_image;
        document.getElementById('detail_model').innerText = p.model;
        document.getElementById('detail_brand').innerText = p.brand;
        document.getElementById('detail_price').innerText = "Rs. " + parseInt(p.sale_price).toLocaleString();
        document.getElementById('detail_type').innerText = p.type;
        document.getElementById('detail_storage').innerText = p.storage;
        document.getElementById('detail_color').innerText = p.color;
        document.getElementById('detail_pta').innerText = p.pta_status;
        document.getElementById('detail_bh').innerText = (p.type === 'New') ? '100%' : p.battery_health;
        document.getElementById('detail_cond').innerText = p.condition_status;
        document.getElementById('btn_add_cart').onclick = function() { addToCart(currentProduct); bootstrap.Modal.getInstance(document.getElementById('productDetailModal')).hide(); };
        new bootstrap.Modal(document.getElementById('productDetailModal')).show();
    }

    async function shareProduct() {
        if (!shareData) return;
        const text = `Assalam-o-Alaikum!\nCheck out this device:\n\n${shareData.model}\n${shareData.storage} | ${shareData.color}\nPrice: Rs. ${parseInt(shareData.sale_price).toLocaleString()}`;
        try {
            const response = await fetch(shareData.device_image);
            const blob = await response.blob();
            const file = new File([blob], "phone.jpg", { type: blob.type });
            if (navigator.share) {
                await navigator.share({ title: shareData.model, text: text, files: [file] });
            } else {
                window.open(`https://wa.me/?text=${encodeURIComponent(text)}`);
            }
        } catch (error) {
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`);
        }
    }

    function expandDrawer() {
        if(cart.length === 0) return alert("Empty!");
        drawerEl.classList.add('wide-drawer');
        document.querySelector('.cart-panel').style.width = '40%';
        document.querySelector('.buyer-panel').style.width = '60%';
        document.querySelector('.buyer-panel').style.opacity = '1';
        document.getElementById('nextBtn').style.display = 'none';
    }

    function collapseDrawer() {
        drawerEl.classList.remove('wide-drawer');
        document.querySelector('.cart-panel').style.width = '100%';
        document.querySelector('.buyer-panel').style.width = '0';
        document.querySelector('.buyer-panel').style.opacity = '0';
        setTimeout(() => { document.getElementById('nextBtn').style.display = 'block'; }, 500);
    }

    drawerEl.addEventListener('hidden.bs.offcanvas', function () { collapseDrawer(); });

    function addToCart(p) {
        if (cart.some(i => i.id === p.id)) return alert("Already Added!");
        cart.push(p);
        render();
        if(bsOffcanvas) bsOffcanvas.show();
    }

    function removeFromCart(i) {
        cart.splice(i, 1);
        render();
        if(cart.length === 0) collapseDrawer();
    }

    function render() {
        let list = document.getElementById('cartList');
        let inputs = document.getElementById('hiddenInputs');
        list.innerHTML = "";
        inputs.innerHTML = "";

        cartTotal = 0;
        cart.forEach((i, idx) => {
            cartTotal += parseFloat(i.sale_price);
            list.innerHTML += `<div class="cart-item"><img src="${i.device_image}" style="width:35px;height:35px;border-radius:4px;object-fit:cover" class="me-3"><div class="flex-grow-1"><div class="fw-bold">${i.model}</div></div><div class="text-end text-success fw-bold">Rs.${parseInt(i.sale_price).toLocaleString()} <i class="fa fa-times text-danger mt-1" style="cursor:pointer" onclick="removeFromCart(${idx})"></i></div></div>`;
            inputs.innerHTML += `<input type="hidden" name="device[]" value="${i.model}"><input type="hidden" name="imei[]" value="${i.imei_number}"><input type="hidden" name="price[]" value="${i.sale_price}">`;
        });

        document.getElementById('sale_total').value = cartTotal.toFixed(2);
        document.getElementById('grandTotal1').innerText = "Rs. " + cartTotal.toLocaleString();
        document.getElementById('grandTotal2').innerText = "Rs. " + cartTotal.toLocaleString();
        document.getElementById('cartCountBtn').innerText = cart.length;

        syncPaymentFields();
    }

    function syncPaymentFields() {
        const method = document.getElementById('payment_method').value;
        const paidInput = document.getElementById('paid_amount');
        const dueDate = document.getElementById('due_date');

        if (method === 'Udhaar') {
            paidInput.value = 0;
            dueDate.required = true;
        } else if (!paidInput.dataset.edited) {
            paidInput.value = cartTotal.toFixed(2);
            dueDate.required = false;
        } else {
            dueDate.required = false;
        }

        updateBalancePreview();
    }

    function updateBalancePreview() {
        const paidInput = document.getElementById('paid_amount');
        const paid = parseFloat(paidInput.value || 0);
        const balance = Math.max(cartTotal - paid, 0);
        paidInput.dataset.edited = '1';
        document.getElementById('balancePreview').innerText = "Rs. " + balance.toLocaleString();
    }

    // CHECK DB & TOGGLE REQUIRED (SMART LOGIC)
    function checkDB(type, value) {
        let statusSpan = document.getElementById('custStatus');
        let frontStatus = document.getElementById('status_front');
        let backStatus = document.getElementById('status_back');
        let inputFront = document.getElementById('input_front');
        let inputBack = document.getElementById('input_back');

        let formData = new FormData();
        formData.append('action', 'find_customer');
        formData.append('type', type);
        formData.append('val', value);

        fetch('api_handler.php', { method: 'POST', body: formData }).then(res => res.json()).then(d => {
            if(d.status === 'found') {
                if(type !== 'name') document.getElementById('seller_search').value = d.data.name;
                if(type !== 'phone') document.getElementById('c_phone').value = d.data.phone;
                if(type !== 'cnic') document.getElementById('c_cnic').value = d.data.cnic;
                statusSpan.innerHTML = '<span class="badge bg-success">Found</span>';

                if(d.has_front) { frontStatus.innerHTML = '<span class="text-success fw-bold"><i class="fa fa-check-circle"></i> On File</span>'; inputFront.required = false; }
                else { frontStatus.innerHTML = '<span class="text-danger fw-bold">* Required</span>'; inputFront.required = true; }

                if(d.has_back) { backStatus.innerHTML = '<span class="text-success fw-bold"><i class="fa fa-check-circle"></i> On File</span>'; inputBack.required = false; }
                else { backStatus.innerHTML = '<span class="text-danger fw-bold">* Required</span>'; inputBack.required = true; }
            } else {
                statusSpan.innerHTML = '<span class="badge bg-warning text-dark">New</span>';
                frontStatus.innerHTML = '<span class="text-danger fw-bold">* Required</span>'; inputFront.required = true;
                backStatus.innerHTML = '<span class="text-danger fw-bold">* Required</span>'; inputBack.required = true;
            }
        });
    }

    document.getElementById('seller_search').addEventListener('input', function() { if(this.value.length > 2) checkDB('name', this.value); });
    document.getElementById('c_phone').addEventListener('keyup', function() { if(this.value.length > 5) checkDB('phone', this.value); });
    document.getElementById('c_cnic').addEventListener('keyup', function() { if(this.value.length > 5) checkDB('cnic', this.value); });
    document.getElementById('searchInput').addEventListener('keyup', function(){ let v=this.value.toLowerCase(); document.querySelectorAll('.filter-item').forEach(i=>i.style.display=i.innerText.toLowerCase().includes(v)?'block':'none'); });

    function filterStock(t, btn) {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');
        document.querySelectorAll('.filter-item').forEach(i => i.style.display = (t === 'all' || i.getAttribute('data-type') === t) ? 'block' : 'none');
    }
</script>
</body>
</html>
