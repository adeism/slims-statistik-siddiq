<?php
/**
 * Plugin Name: Statistik Siddiq
 * Plugin URI: https://github.com/adeism/slims-statistik-siddiq
 * Description: Dashboard statistik perpustakaan dengan visualisasi data koleksi, anggota, dan transaksi (terinspirasi dari Postingan Pak Hendro Wicaksono di grup WhatsApp SLiMS Community).
 * Version: 1.0.0
 * Author: Ade Ismail Siregar
 */

use SLiMS\Plugins;

// Get plugin instance
$plugin = Plugins::getInstance();

// Register menu di kategori 'reporting'
$plugin->registerMenu('reporting', 'Statistik Siddiq', __DIR__ . '/index.php');
