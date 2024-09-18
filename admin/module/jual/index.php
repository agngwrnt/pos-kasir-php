<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_POST['cartItems'])) {
    $_SESSION['cartItems'] = json_decode($_POST['cartItems'], true);
    header('Location: /admin/module/jual/confirmation.php');
    exit();
}
$_SESSION['cartItems'] = $cartItems;
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require 'config.php'; // Pastikan ini benar dan koneksi database sudah tersambung

// Menjalankan query dan mendapatkan hasilnya
$stmt = $config->query("SELECT * FROM barang");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$results) {
    die('Query Error');
}

// echo '<pre>';
// print_r($results);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Page</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .image-item {
            flex: 1 1 calc(25% - 20px); /* Setiap item mengambil 25% dari lebar kontainer */
            border: 1px solid #ccc;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            border-radius: 5px;
            background-color: #fff;
            position: relative;
            text-align: center;
        }

        .image-item img {
            width: 100px;
            height: 100px;
            margin-right: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
        }

        .quantity-controls button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
        }

        .cart-popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            border-radius: 8px;
            display: none;
            z-index: 1000;
        }

        .cart-popup h3 {
            margin: 0 0 10px;
        }

        .cart-popup ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
        }

        .cart-popup ul li {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }

        .cart-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #28a745;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 1000;
        }

        .cart-icon img {
            width: 24px;
            height: 24px;
        }

        .proceed-button {
            margin-top: 10px;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }

        #product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px; /* Jarak antar kotak */
        }
    </style>
/head>
<body>

<div class="content">
    <h2>Daftar Barang:</h2>
    <div id="product-list">
        <?php foreach ($results as $row): ?>
            <div class="image-item">
                <!-- Anda dapat menyesuaikan URL gambar berdasarkan informasi gambar yang Anda miliki -->
                <img src="path/to/default-image.jpg" alt="<?php echo $row['nama_barang']; ?>" style="width: 100px; height: 100px;">
                <p><?php echo $row['nama_barang']; ?> (<?php echo $row['merk']; ?>)</p>
                <p>Harga: Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></p>
                <div class="quantity-controls">
                    <button onclick="updateQuantity(<?php echo $row['id']; ?>, -1)">-</button>
                    <span id="quantity-<?php echo $row['id']; ?>">0</span>
                    <button onclick="updateQuantity(<?php echo $row['id']; ?>, 1)">+</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Pop-Up Cart -->
<div id="cart-popup" class="cart-popup">
    <h3>Keranjang</h3>
    <ul id="cart-items">
        <!-- Item dalam keranjang akan muncul di sini -->
    </ul>
    <button id="proceed-button" class="proceed-button" onclick="proceedToCheckout()">Proses</button>
</div>

<!-- Icon Keranjang -->
<div id="cart-icon" class="cart-icon" onclick="toggleCart()">
    <img src="assets/img/cart.webp" alt="Cart">
</div>

<script>
    // Data gambar dan harga
    const products = [
        { id: 1, title: 'Image 1', category: 'Nature', url: 'path/to/image1.jpg', price: 50000 },
        { id: 2, title: 'Image 2', category: 'Urban', url: 'path/to/image2.jpg', price: 75000 },
        { id: 3, title: 'Image 3', category: 'Nature', url: 'path/to/image3.jpg', price: 60000 },
        { id: 4, title: 'Image 4', category: 'Abstract', url: 'path/to/image4.jpg', price: 80000 },
    ];

    let cartItems = {};
    let totalPrice = 0;

    // Tampilkan daftar produk
    function displayProducts() {
        const productList = document.getElementById('product-list');
        products.forEach(product => {
            const productItem = document.createElement('div');
            productItem.className = 'image-item';
            productItem.innerHTML = `
                <img src="${product.url}" alt="${product.title}">
                <p>${product.title}</p>
                <div class="quantity-controls">
                    <button onclick="updateQuantity(${product.id}, -1)">-</button>
                    <span id="quantity-${product.id}">0</span>
                    <button onclick="updateQuantity(${product.id}, 1)">+</button>
                </div>
            `;
            productList.appendChild(productItem);
        });
    }

    // Update quantity dan total
    function updateQuantity(id, change) {
        const quantitySpan = document.getElementById(`quantity-${id}`);
        let currentQuantity = parseInt(quantitySpan.textContent);
        currentQuantity += change;

        if (currentQuantity < 0) currentQuantity = 0;
        quantitySpan.textContent = currentQuantity;

        const product = products.find(item => item.id === id);

        if (currentQuantity > 0) {
            cartItems[id] = { ...product, quantity: currentQuantity };
        } else {
            delete cartItems[id];
        }
        console.log(cartItems);
        updateCart();
    }

    // Tampilkan dan sembunyikan pop-up keranjang
    function toggleCart() {
        const cartPopup = document.getElementById('cart-popup');
        cartPopup.style.display = cartPopup.style.display === 'none' || cartPopup.style.display === '' ? 'block' : 'none';
    }

    // Update isi keranjang
    function updateCart() {
        const cartList = document.getElementById('cart-items');

        if (!cartList) {
        console.error('Element with id "cart-items" not found');
        return;
    }

    if (Object.keys(cartItems).length === 0) {
        const li = document.createElement('li');
        li.textContent = 'Keranjang kosong';
        cartList.appendChild(li);
        return;
    }

        cartList.innerHTML = '';

        Object.values(cartItems).forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${item.title} x ${item.quantity}`;
            cartList.appendChild(li);
        });
    }

    // Mengarahkan ke halaman konfirmasi
    function proceedToCheckout() {
        if (Object.keys(cartItems).length === 0) {
            alert('Keranjang kosong, silakan tambahkan item.');
            return;
        }
        // Menyimpan data ke sessionStorage untuk halaman konfirmasi
        sessionStorage.setItem('cartItems', JSON.stringify(cartItems));
        window.location.href = '/admin/module/jual/confirmation.php';
        alert('Cart items:', sessionStorage.getItem('cartItems'));
        // alert('Cart items before redirect:', JSON.stringify(cartItems));
    }

    // Inisialisasi daftar produk
    displayProducts();
</script>

</body>
</html>
