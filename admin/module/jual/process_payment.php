<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pastikan session sudah memiliki ID member
if (!isset($_SESSION['admin']['id_member'])) {
    die('<p>Session ID member tidak ditemukan. Silakan login kembali.</p>');
}
$id_member = $_SESSION['admin']['id_member'];

// Include konfigurasi database
require_once '../../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $cartItems = json_decode($_POST['cartItems'], true);
    if (!$cartItems || count($cartItems) === 0) {
        die('<p>Keranjang kosong atau data JSON tidak valid.</p>');
    }

    $totalPrice = $_POST['totalPrice'];
    $paymentMethod = $_POST['payment'];

    try {
        // Mulai transaksi
        $config->beginTransaction();

        echo "<pre>";
        print_r($cartItems);
        echo "</pre>";

        foreach ($cartItems as $item) {
            $id_barang = $item['id'];
            $nama_barang = $item['nama_barang'];
            $jumlah = $item['quantity'];

            // Ambil stok dan harga jual dari tabel barang
            $stmtBarang = $config->prepare("SELECT stok, harga_jual FROM barang WHERE id_barang = ?");
            $stmtBarang->execute([$id_barang]);
            $barang = $stmtBarang->fetch(PDO::FETCH_ASSOC);

            if (!$barang) {
                throw new Exception("Barang dengan ID $id_barang tidak ditemukan.");
            }

            $stokBarang = $barang['stok'];
            $hargaJual = preg_replace('/[^\d]/', '', $barang['harga_jual']); // Bersihkan harga jual
            $total = $hargaJual * $jumlah;

            if ($stokBarang < $jumlah) {
                throw new Exception("Stok untuk barang $nama_barang tidak cukup! Stok tersedia: $stokBarang.");
            }

            // Kurangi stok barang
            $newStok = $stokBarang - $jumlah;
            $stmtUpdateBarang = $config->prepare("UPDATE barang SET stok = ? WHERE id_barang = ?");
            $stmtUpdateBarang->execute([$newStok, $id_barang]);

            // Simpan transaksi ke tabel nota
            $stmtNota = $config->prepare("
                INSERT INTO nota (id_barang, id_member, jumlah, total, tanggal_input, periode) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtNota->execute([$id_barang, $id_member, $jumlah, $total, date('Y-m-d H:i:s'), date('m-Y')]);
        }

        // Commit transaksi
        $config->commit();

        echo '<p>Pembayaran berhasil! Data telah disimpan.</p>';
        echo '<script>setTimeout(function(){ window.location.href = "../../../index.php"; }, 2000);</script>';
    } catch (Exception $e) {
        // Rollback jika ada error
        $config->rollBack();
        echo '<p>Terjadi kesalahan: ' . $e->getMessage() . '</p>';
        echo '<script>setTimeout(function(){ history.back(); }, 2000);</script>';
    }
} else {
    echo '<p>Metode tidak valid!</p>';
    echo '<script>setTimeout(function(){ history.back(); }, 2000);</script>';
}
?>
