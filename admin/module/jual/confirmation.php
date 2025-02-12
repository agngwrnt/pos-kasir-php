<?php
session_start();
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

        h1 {
            margin-top: 0;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
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
            <li>Memuat keranjang...</li>
        </ul>
        <div class="total-price">
            Total Harga: <span id="total-price">0 IDR</span>
        </div>
        <div class="payment-method">
            <form action="process_payment.php" method="POST">
                <label><input type="radio" name="payment" value="credit-card" required> Kartu Kredit</label>
                <label><input type="radio" name="payment" value="bank-transfer" required> Transfer Bank</label>
                <label><input type="radio" name="payment" value="cash-on-delivery" required> Bayar di Tempat</label>
                <input type="hidden" name="totalPrice" id="totalPriceInput">
                <input type="hidden" name="cartItems" id="cartItemsInput">
                <button type="submit" class="pay-button">Bayar Sekarang</button>
            </form>
        </div>
    </div>

    <script>
        // Ambil data keranjang dari localStorage
        const cartItemsFromStorage = JSON.parse(localStorage.getItem('cartItems')) || {};
        const cartItemsList = document.getElementById('cart-items-list');
        const totalPriceElement = document.getElementById('total-price');
        const totalPriceInput = document.getElementById('totalPriceInput');
        const cartItemsInput = document.getElementById('cartItemsInput');

        if (Object.keys(cartItemsFromStorage).length > 0) {
            cartItemsList.innerHTML = ''; // Kosongkan elemen sebelumnya
            let totalPrice = 0;

            Object.values(cartItemsFromStorage).forEach(item => {
                const li = document.createElement('li');
                li.textContent = `${item.nama_barang} x ${item.quantity} - ${item.harga_jual}`;
                cartItemsList.appendChild(li);
                totalPrice += parseFloat(item.harga_jual.replace(/[^\d]/g, '')) * item.quantity;
            });

            totalPriceElement.textContent = totalPrice.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            totalPriceInput.value = totalPrice; // Set nilai total harga di input tersembunyi
            cartItemsInput.value = JSON.stringify(cartItemsFromStorage); // Set data keranjang di input tersembunyi
        } else {
            cartItemsList.innerHTML = '<li>Keranjang kosong.</li>';
        }

        document.querySelector('.pay-button').addEventListener('click', function(e) {
            if (!totalPriceInput.value || !cartItemsInput.value) {
                e.preventDefault(); // Cegah submit jika input kosong
                alert("Data keranjang atau total harga kosong. Silakan periksa kembali.");
            }
        });
    </script>
</body>
</html>
