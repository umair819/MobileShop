<?php include 'db.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Live Counter POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .phone-card { transition: transform 0.2s; cursor: pointer; border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .phone-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.2); }
        .phone-img { height: 180px; object-fit: cover; border-top-left-radius: 10px; border-top-right-radius: 10px; }
        .badge-pta { position: absolute; top: 10px; right: 10px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4 mb-4">
    <a class="navbar-brand" href="index.php"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
    <span class="navbar-text text-white">Available Stock (Live View)</span>
</nav>

<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <input type="text" id="search" class="form-control form-control-lg rounded-pill" placeholder="🔍 Search Phone (iPhone, Samsung...)">
        </div>
    </div>

    <div class="row g-4">
        <?php
        // Sirf wo phones dikhao jo stock mein hain (qty > 0)
        $sql = "SELECT * FROM products WHERE stock_qty > 0 ORDER BY id DESC";
        $result = $conn->query($sql);

        while($row = $result->fetch_assoc()):
            // Status Color Logic
            $pta_color = ($row['pta_status'] == 'Approved') ? 'success' : 'danger';
            
            // WhatsApp Message Generator
            $wa_text = "Salam! Check out this phone:%0A";
            $wa_text .= "📱 Model: " . $row['model'] . "%0A";
            $wa_text .= "💾 Specs: " . $row['ram'] . " / " . $row['storage'] . "%0A";
            $wa_text .= "✅ PTA: " . $row['pta_status'] . "%0A";
            $wa_text .= "✨ Condition: " . $row['condition_status'] . "%0A";
            $wa_text .= "💰 Price: Rs. " . number_format($row['sale_price']);
        ?>
        
        <div class="col-md-3 col-sm-6">
            <div class="card phone-card h-100">
                <img src="<?php echo $row['device_image']; ?>" class="phone-img w-100" alt="Phone">
                <span class="badge bg-<?php echo $pta_color; ?> badge-pta"><?php echo $row['pta_status']; ?></span>
                
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?php echo $row['model']; ?></h5>
                    <p class="text-muted small mb-1">
                        <i class="fa fa-memory"></i> <?php echo $row['ram']; ?>/<?php echo $row['storage']; ?> | 
                        <i class="fa fa-star"></i> <?php echo $row['condition_status']; ?>
                    </p>
                    <h4 class="text-primary mt-2">Rs. <?php echo number_format($row['sale_price']); ?></h4>
                </div>

                <div class="card-footer bg-white border-0 d-flex justify-content-between pb-3">
                    <button class="btn btn-outline-dark btn-sm flex-grow-1 me-1"><i class="fa fa-shopping-cart"></i> Sell</button>
                    
                    <button onclick="shareWhatsApp('<?php echo $wa_text; ?>')" class="btn btn-success btn-sm flex-grow-1">
                        <i class="fab fa-whatsapp"></i> Share
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function shareWhatsApp(details) {
    let number = prompt("Enter Customer WhatsApp Number (e.g 923001234567):");
    if (number) {
        // Direct Chat Link
        let url = "https://wa.me/" + number + "?text=" + details;
        window.open(url, '_blank');
    }
}
</script>

</body>
</html>