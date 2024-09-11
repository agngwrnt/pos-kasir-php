<?php
session_start();
$cartItems = isset($_SESSION['cartItems']) ? $_SESSION['cartItems'] : [];
$totalPrice = 0;

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
            <!-- Daftar item keranjang akan ditambahkan di sini oleh JavaScript -->
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
                <input type="hidden" name="cartItems" id="hidden-cart-items">
                <button type="submit" class="pay-button">Bayar Sekarang</button>
            </form>
        </div>
    </div>

    <script>
        // Ambil data keranjang dari sessionStorage
        const cartItems = JSON.parse(sessionStorage.getItem('cartItems')) || [];
        
        // Tampilkan item keranjang
        const cartItemsList = document.getElementById('cart-items-list');
        cartItems.forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${item.title} x ${item.quantity} - ${item.price} IDR`;
            cartItemsList.appendChild(li);
        });

        // Set data keranjang ke input hidden
        document.getElementById('hidden-cart-items').value = JSON.stringify(cartItems);
    </script>
</body>
</html>
