<?php
// print_invoice.php
$servername = "localhost"; $username = "root"; $password = ""; $dbname = "universal_mobile_shop";
$conn = new mysqli($servername, $username, $password, $dbname);

// Bill ID check karo
if (!isset($_GET['id'])) { die("Invalid Invoice ID"); }
$id = $_GET['id'];

// Sale Data Uthao
$sale = $conn->query("SELECT * FROM sales WHERE id = $id")->fetch_assoc();

// Shop Settings Uthao
$shop = $conn->query("SELECT * FROM shop_settings WHERE id = 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $id; ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace; /* Receipt Font */
            background: #eee;
            padding: 20px;
        }
        .ticket {
            width: 300px; /* Thermal Printer Width */
            max-width: 300px;
            background: white;
            margin: 0 auto;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .centered { text-align: center; align-content: center; }
        .ticket h2 { font-size: 22px; margin-bottom: 5px; text-transform: uppercase;}
        .ticket p { font-size: 12px; margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        .table th { text-align: left; border-bottom: 1px solid black; }
        .table td { padding: 5px 0; }
        .total-row { border-top: 2px dashed black; font-weight: bold; font-size: 14px; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; }
        
        @media print {
            body { background: white; padding: 0; }
            .ticket { box-shadow: none; width: 100%; }
            button { display: none; } /* Hide print button on paper */
        }
    </style>
</head>
<body onload="window.print()"> <div class="ticket">
        <div class="centered">
            <h2><?php echo $shop['shop_name']; ?></h2>
            <p><?php echo $shop['address']; ?></p>
            <p>Phone: <?php echo $shop['shop_phone']; ?></p>
            <br>
            <p><strong>INVOICE RECEIPT</strong></p>
            <p>Date: <?php echo date('d-M-Y h:i A', strtotime($sale['sale_date'])); ?></p>
            <p>Bill No: #<?php echo $sale['id']; ?></p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align:right">Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo $sale['device_name']; ?><br>
                        <small>IMEI: <?php echo $sale['imei_number']; ?></small>
                    </td>
                    <td style="text-align:right">
                        <?php echo number_format($sale['price']); ?>
                    </td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td style="text-align:right">
                        <?php echo $shop['currency']; ?> <?php echo number_format($sale['price']); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Software Developed by 0300-XXXXXXX</p> <br>
            <p>*** NO WARRANTY / NO RETURN ***</p>
        </div>
        
        <br>
        <button onclick="window.print()" style="width:100%; padding:10px; cursor:pointer;">Print Again</button>
        <button onclick="window.location.href='index.php'" style="width:100%; padding:10px; cursor:pointer; margin-top:5px;">Back to Dashboard</button>
    </div>

</body>
</html>