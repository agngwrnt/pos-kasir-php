<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// $cartItems = isset($_SESSION['cartItems']) ? $_SESSION['cartItems'] : [];
$totalPrice = 0;
// $cartItems = $_SESSION['cartItems'];

if (isset($_SESSION['cartItems']) && is_array($_SESSION['cartItems'])) {
    $cartItems = $_SESSION['cartItems'];
} else {
    $cartItems = [];
}

// Hitung total harga
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .confirmation-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .payment-method {
            margin-top: 20px;
        }

        .payment-method label {
            margin-right: 15px;
        }

        .pay-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .pay-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h1>Konfirmasi Pesanan</h1>
        <ul id="cart-items-list">
            <!-- PHP Loop to Display Cart Items -->
            <?php if (!empty($cartItems)) : ?>
                <?php foreach ($cartItems as $item) : ?>
                    <li><?php echo htmlspecialchars($item['title']); ?> x <?php echo $item['quantity']; ?> - <?php echo number_format($item['price'], 2); ?> IDR</li>
                <?php endforeach; ?>
            <?php else : ?>
                <li>Keranjang kosong.</li>
            <?php endif; ?>
        </ul>
        <div class="total-price">
            Total Harga: <span id="total-price"><?php echo number_format($totalPrice, 2); ?> IDR</span>
        </div>
        <div class="payment-method">
            <form action="process_payment.php" method="post">
                <label><input type="radio" name="payment" value="credit-card"> Kartu Kredit</label>
                <label><input type="radio" name="payment" value="bank-transfer"> Transfer Bank</label>
                <label><input type="radio" name="payment" value="cash-on-delivery"> Bayar di Tempat</label>
                <input type="hidden" name="totalPrice" value="<?php echo number_format($totalPrice, 2); ?>">
                <input type="hidden" name="cartItems" value='<?php echo json_encode($cartItems); ?>'>
                <button type="submit" class="pay-button">Bayar Sekarang</button>
            </form>
        </div>
    </div>

    <script>
        // (Optional) Recheck Cart Data from sessionStorage
        const cartItemsFromSession = JSON.parse(sessionStorage.getItem('cartItems')) || [];

        // If sessionStorage exists, update the list in JS (for dynamic front-end display)
        if (cartItemsFromSession.length > 0) {
            const cartItemsList = document.getElementById('cart-items-list');
            cartItemsList.innerHTML = '';  // Clear existing items

            cartItemsFromSession.forEach(item => {
                const li = document.createElement('li');
                li.textContent = `${item.title} x ${item.quantity} - ${item.price} IDR`;
                cartItemsList.appendChild(li);
            });

            // Optionally update the total price
            const totalPrice = cartItemsFromSession.reduce((total, item) => total + item.price * item.quantity, 0);
            document.getElementById('total-price').textContent = totalPrice.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + ' IDR';
        }
    </script>
</body>
</html>
