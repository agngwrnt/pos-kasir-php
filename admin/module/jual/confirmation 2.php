<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config.php'; // File koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST); // Debug data yang dikirimkan melalui form
    echo "</pre>";
    exit; // Hentikan sementara untuk melihat hasil
    // Proses pembayaran
    $cartItems = json_decode($_POST['cartItems'], true);
    // if (json_last_error() !== JSON_ERROR_NONE) {
    //     die("JSON Decode Error: " . json_last_error_msg());
    // }
    // echo "<pre>";
    // print_r($cartItems);
    // echo "</pre>";
    // exit;
    $totalPrice = $_POST['totalPrice'];
    $paymentMethod = $_POST['payment'];
    $id_member = $_SESSION['admin']['id_member']; // ID member dari session
    $tanggal_input = date('Y-m-d H:i:s');
    $periode = date('m-Y');

    function extractHarga($harga) {
        return (int) preg_replace('/[^0-9]/', '', $harga);
    }

    if ($cartItems && count($cartItems) > 0) {
        $conn->beginTransaction(); // Mulai transaksi

        try {
            foreach ($cartItems as $item) {
                $id_barang = $item['id_barang'];
                $nama_barang = $item['nama_barang'];
                $harga = extractHarga($item['harga_jual']);
                $jumlah = $item['quantity'];
                $total = $item['harga_jual'] * $jumlah;

                echo "Barang: $nama_barang, Harga: $harga, Jumlah: $jumlah, Total: $total<br>";

                // Masukkan data ke tabel nota
                $stmtNota = $conn->prepare("
                    INSERT INTO nota (id_barang, id_member, jumlah, total, tanggal_input, periode) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtNota->execute([$id_barang, $id_member, $jumlah, $total, $tanggal_input, $periode]);

                // Kurangi stok pada tabel barang
                $stmtBarang = $conn->prepare("SELECT stok FROM barang WHERE id_barang = ?");
                $stmtBarang->execute([$id_barang]);
                $stokBarang = $stmtBarang->fetchColumn();

                if ($stokBarang < $jumlah) {
                    throw new Exception("Stok untuk barang $nama_barang tidak cukup!");
                }

                $newStok = $stokBarang - $jumlah;
                $stmtUpdateBarang = $conn->prepare("UPDATE barang SET stok = ? WHERE id_barang = ?");
                $stmtUpdateBarang->execute([$newStok, $id_barang]);
            }

            $conn->commit(); // Commit transaksi
            echo '<script>alert("Pembayaran berhasil! Data sudah disimpan."); window.location.href = "confirmation.php";</script>';
        } catch (Exception $e) {
            $conn->rollBack(); // Rollback transaksi
            echo '<script>alert("Terjadi kesalahan: ' . $e->getMessage() . '"); history.back();</script>';
        }
    } else {
        echo '<script>alert("Keranjang kosong!"); history.back();</script>';
    }
    exit;
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
            <form action="" method="POST">
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
        const totalPriceInput = document.getElementById('totalPriceInput'); // belum terconsume
        const cartItemsInput = document.getElementById('cartItemsInput'); // belum terconsume

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
            if (totalPriceInput) {
                totalPriceInput.value = totalPrice; // Set nilai total harga di input tersembunyi
            }
            if (cartItemsInput) {
                cartItemsInput.value = JSON.stringify(cartItemsFromStorage); // Set data keranjang di input tersembunyi
            }
        } else {
            cartItemsList.innerHTML = '<li>Keranjang kosong.</li>';
        }
    </script>
</body>
</html>
