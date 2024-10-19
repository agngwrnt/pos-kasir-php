<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php'; // Pastikan ini benar dan koneksi database sudah tersambung

// Query untuk mengambil kategori dari tabel 'kategori'
$categoryStmt = $config->query("SELECT * FROM kategori");
$categoryResults = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$categoryResults) {
    die('Query Error: Unable to fetch categories');
}

// Query untuk mengambil barang dari tabel 'barang'
$stmt = $config->query("SELECT * FROM barang");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$results) {
    die('Query Error: Unable to fetch products');
}

// Mengelompokkan barang berdasarkan kategori
$categories = [];
foreach ($results as $row) {
    $categories[$row['id_kategori']][] = $row;
}
?>

<?php 
	// @ob_start();
	// session_start();

	// if(!empty($_SESSION['admin'])){
	// 	require 'config.php';
	// 	include $view;
	// 	$lihat = new view($config);
	// 	$toko = $lihat -> toko();
	// 	//  admin
	// 		include 'admin/template/header.php';
	// 		include 'admin/template/sidebar.php';
	// 			if(!empty($_GET['page'])){
	// 				include 'admin/module/'.$_GET['page'].'/index.php';
	// 			}else{
	// 				include 'admin/template/home.php';
	// 			}
	// 		include 'admin/template/footer.php';
	// 	// end admin
	// }else{
	// 	echo '<script>window.location="login.php";</script>';
	// 	exit;
	// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang per Kategori</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .category-bar {
            display: flex;
            flex-direction: row;
            background-color: #f4f4f4;
            padding: 10px;
            gap: 15px;
            border-bottom: 2px solid #ddd;
        }

        .category-bar button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .category-bar button.active {
            background-color: #28a745;
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

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
        }

        .product-item {
            flex: 1 1 calc(25% - 20px);
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            text-align: center;
        }

        .product-item img {
            width: 100px;
            height: 100px;
        }

        .hidden {
            display: none;
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
    </style>
</head>
<body>

<div id="cart-icon" class="cart-icon" onclick="toggleCart()">
    <img src="assets/img/cart.webp" alt="Cart">
</div>

<div class="category-bar">
    <!-- Generate category buttons dynamically from database -->
    <?php foreach ($categoryResults as $category): ?>
        <button onclick="showCategory(<?php echo $category['id_kategori']; ?>)">
            <?php echo htmlspecialchars($category['nama_kategori']); ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- Display products by category -->
<?php foreach ($categories as $categoryId => $items): ?>
    <div class="product-list hidden" id="category-<?php echo $categoryId; ?>">
        <?php foreach ($items as $item): ?>
            <div class="product-item category-product" data-id="<?php echo $item['id']; ?>">
    <img src="path/to/image.jpg" alt="<?php echo htmlspecialchars($item['nama_barang']); ?>">
    <p><?php echo htmlspecialchars($item['nama_barang']); ?> (<?php echo htmlspecialchars($item['merk']); ?>)</p>
    <p>Harga: Rp <?php echo number_format($item['harga_jual'], 0, ',', '.'); ?></p>
    <div class="quantity-controls">
        <button onclick="updateQuantity('<?php echo $item['id']; ?>', -1)">-</button>
        <span id="quantity-<?php echo $item['id']; ?>">0</span>
        <button onclick="updateQuantity('<?php echo $item['id']; ?>', 1)">+</button>
    </div>
</div>

        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<!-- Pop-Up Cart -->
<div id="cart-popup" class="cart-popup">
    <h3>Keranjang</h3>
    <ul id="cart-items">
        <!-- Item dalam keranjang akan muncul di sini -->
    </ul>
    <button id="proceed-button" class="proceed-button" onclick="proceedToCheckout()">Proses</button>
</div>

<script>
    let currentCategory = null;
    let cartItems = {};

    function showCategory(categoryId) {
        // Hide the currently displayed category
        if (currentCategory !== null) {
            const categoryElement = document.getElementById('category-' + currentCategory);
            if (categoryElement) {
                categoryElement.classList.add('hidden');
            }
        }

        // Show the new category
        const newCategoryElement = document.getElementById('category-' + categoryId);
        if (newCategoryElement) {
            newCategoryElement.classList.remove('hidden');
            currentCategory = categoryId;
        }

        // Update active button style
        document.querySelectorAll('.category-bar button').forEach(button => {
            button.classList.remove('active');
        });
        if (event && event.target) {
            event.target.classList.add('active');
        }
    }

    // Automatically show the first category on page load
    document.addEventListener('DOMContentLoaded', () => {
        const firstCategoryButton = document.querySelector('.category-bar button');
        if (firstCategoryButton) {
            firstCategoryButton.click();
        }
    });

    function toggleCart() {
        const cartPopup = document.getElementById('cart-popup');
        cartPopup.style.display = cartPopup.style.display === 'none' || cartPopup.style.display === '' ? 'block' : 'none';
    }

    function openCart() {
    const cartPopup = document.getElementById('cart-popup');
    cartPopup.style.display = 'block';
}

    // Close the cart
    function closeCart() {
        const cartPopup = document.getElementById('cart-popup');
        cartPopup.style.display = 'none';
    }

    // Update quantity dan total
    function updateQuantity(id, change) {
    const quantitySpan = document.getElementById(`quantity-${id}`);
    let currentQuantity = parseInt(quantitySpan.textContent);
    currentQuantity += change;

    if (currentQuantity < 0) currentQuantity = 0;
    quantitySpan.textContent = currentQuantity;

    // Ambil informasi produk berdasarkan ID dari kategori yang aktif
    const productElement = document.querySelector(`#category-${currentCategory} .product-item[data-id="${id}"]`);
    const productName = productElement.querySelector('p:first-of-type').textContent;
    const productPrice = productElement.querySelector('p:last-of-type').textContent;

    const product = {
        id: id,
        nama_barang: productName,
        harga_jual: productPrice,
        quantity: currentQuantity
    };

    if (currentQuantity > 0) {
        cartItems[id] = product;  // Simpan produk dengan informasi lengkap
    } else {
        delete cartItems[id];  // Hapus jika jumlah 0
    }

    console.log(cartItems);
    updateCart();

    // Check if there are any items in the cart, keep it open if true
    if (Object.keys(cartItems).length > 0) {
        openCart();
    } else {
        closeCart();
    }
}


    // Update isi keranjang
    function updateCart() {
    // Cari elemen dengan tombol aktif
    
    const currentCategoryElement = document.querySelector('.category-bar  button.active');
    // console.log(currentCategoryElement);
    
    // Cari elemen 'cart-items' di dalam cart-popup
    const cartList = document.querySelector('#cart-items');
    // console.log(cartList);

    // Jika cartList tidak null, perbarui isinya
    if (cartList) {
        cartList.innerHTML = '';  // Kosongkan isi sebelumnya
        Object.values(cartItems).forEach(item => {
            const li = document.createElement('li');
            // Perhatikan bahwa ini akan menghasilkan kesalahan, karena PHP hanya dieksekusi di server
            // li.textContent = ` echo htmlspecialchars($item['nama_barang']); ?> x ${item.quantity}`;  // Pastikan untuk menggunakan data dari cartItems
            li.textContent = `${item.nama_barang} x ${item.quantity}`;
            cartList.appendChild(li);
        });
    } else {
        console.error('Cart list element not found.');
    }
}



    // Mengarahkan ke halaman konfirmasi
    function proceedToCheckout() {
        if (Object.keys(cartItems).length === 0) {
            alert('Keranjang kosong, silakan tambahkan item.');
            return;
        }
        // Menyimpan data ke sessionStorage untuk halaman konfirmasi
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
alert('Cart items before redirect:', localStorage.getItem('cartItems'));

        window.location.href = '/admin/module/jual/confirmation.php';
        // alert('Cart items:', sessionStorage.getItem('cartItems'));
        // alert('Cart items before redirect:', JSON.stringify(cartItems));
    }
</script>

</body>
</html>
