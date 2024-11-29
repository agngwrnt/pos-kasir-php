<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartItems = json_decode(file_get_contents('php://input'), true);
    if (!empty($cartItems)) {
        $_SESSION['cartItems'] = $cartItems; // Simpan ke session
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Keranjang kosong']);
    }
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard POS Kasir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f9;
        }
        h1, h2 {
            text-align: center;
        }
        nav ul {
            list-style-type: none;
            text-align: center;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin: 0 10px;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
        }
        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        form input, form textarea, form button {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
            text-align: center;
        }
        th, td {
            padding: 10px;
        }
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="#input">Input Settlement</a></li>
            <li><a href="#reports">Lihat Laporan</a></li>
        </ul>
    </nav>

    <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <section id="input">
        <h2>Input Settlement</h2>
        <form action="" method="post">
            <label for="report_date">Tanggal:</label>
            <input type="date" id="report_date" name="report_date" required>
            
            <label for="total_income">Total Pendapatan:</label>
            <input type="number" id="total_income" name="total_income" step="0.01" required>
            
            <label for="total_expense">Total Pengeluaran:</label>
            <input type="number" id="total_expense" name="total_expense" step="0.01" required>
            
            <label for="final_balance">Saldo Akhir:</label>
            <input type="number" id="final_balance" name="final_balance" step="0.01" required>
            
            <label for="notes">Catatan:</label>
            <textarea id="notes" name="notes"></textarea>
            
            <button type="submit">Simpan</button>
        </form>
    </section>

    <section id="reports">
        <h2>Daftar Laporan Settlement</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Pendapatan</th>
                <th>Pengeluaran</th>
                <th>Saldo Akhir</th>
                <th>Catatan</th>
                <th>Dibuat</th>
            </tr>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?= $report['id'] ?></td>
                    <td><?= $report['report_date'] ?></td>
                    <td><?= number_format($report['total_income'], 2) ?></td>
                    <td><?= number_format($report['total_expense'], 2) ?></td>
                    <td><?= number_format($report['final_balance'], 2) ?></td>
                    <td><?= htmlspecialchars($report['notes']) ?></td>
                    <td><?= $report['created_at'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
</body>
</html>