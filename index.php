<?php
/**
 * Statistik Siddiq - Admin Interface
 * Menampilkan statistik dan visualisasi data perpustakaan dengan Filter
 * Pattern: Dual-mode (Parent + Iframe)
 */

// SECURITY LAYER 1: Authentication
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// Access global objects
global $dbs, $sysconf;

// Start session and check authentication
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

// SECURITY LAYER 2: Authorization
$can_read = utility::havePrivilege('reporting', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You do not have permission to access this module!') . '</div>');
}

// Check mode: Parent (with filter) atau Iframe (report view)
$isIframeMode = isset($_GET['reportView']) && $_GET['reportView'] === 'true';

if (!$isIframeMode) {
    // PARENT MODE - Tampilkan filter + iframe
    $base_url = $_SERVER['PHP_SELF'] . '?mod=' . $_GET['mod'] . '&id=' . $_GET['id'] . '&reportView=true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Siddiq</title>
    <link rel="stylesheet" href="<?php echo SWB . 'plugins/statistik-siddiq/assets/dashboard.css'; ?>">
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .filter-section { background: #fff; padding: 12px 20px; margin: 0; border-bottom: 2px solid #dee2e6; }
        .filter-form { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        .filter-form label { font-weight: 600; color: #495057; margin: 0; white-space: nowrap; font-size: 0.9em; }
        .filter-form select { padding: 6px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9em; min-width: 120px; }
        .btn { padding: 6px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em; text-decoration: none; display: inline-block; white-space: nowrap; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .report-iframe { width: 100%; height: calc(100vh - 60px); border: none; }
        
        @media (max-width: 768px) {
            .filter-form { gap: 8px; }
            .filter-form label { font-size: 0.85em; }
            .filter-form select { min-width: 100px; font-size: 0.85em; padding: 5px 10px; }
            .btn { padding: 5px 12px; font-size: 0.85em; }
        }
    </style>
</head>
<body>
    <div class="filter-section">
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView" class="filter-form">
            <input type="hidden" name="mod" value="<?php echo $_GET['mod']; ?>">
            <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
            <input type="hidden" name="reportView" value="true">
            
            <label>📊 Statistik Siddiq</label>
            <span style="color: #6c757d; margin: 0 5px;">|</span>
            <label style="font-weight: 500;">Data s.d Tahun:</label>
            <select name="year" class="form-control">
                <option value="">Semua Data</option>
                <?php
                // Ambil semua tahun yang ada di database (exclude 0 dan NULL)
                $query_years = "SELECT DISTINCT YEAR(input_date) as year FROM (
                                SELECT input_date FROM biblio WHERE input_date IS NOT NULL AND input_date != '0000-00-00'
                                UNION ALL
                                SELECT input_date FROM item WHERE input_date IS NOT NULL AND input_date != '0000-00-00'
                               ) as combined 
                               WHERE YEAR(input_date) IS NOT NULL 
                               AND YEAR(input_date) > 0
                               AND YEAR(input_date) <= " . date('Y') . "
                               ORDER BY year DESC";
                $result_years = $dbs->query($query_years);
                if ($result_years && $result_years->num_rows > 0) {
                    while ($row_year = $result_years->fetch_assoc()) {
                        $year = intval($row_year['year']);
                        if ($year > 0) {
                            $selected = (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : '';
                            echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                        }
                    }
                } else {
                    // Fallback jika tidak ada data
                    $currentYear = date('Y');
                    echo '<option value="' . $currentYear . '">' . $currentYear . '</option>';
                }
                ?>
            </select>
            
            <button type="submit" class="btn btn-success">Terapkan</button>
            <a href="<?php echo $base_url; ?>" target="reportView" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    
    <iframe name="reportView" src="<?php echo $base_url . '&year=' . date('Y'); ?>" class="report-iframe"></iframe>
</body>
</html>
<?php
    exit; // Stop execution di parent mode
}

// IFRAME MODE - Generate report
ob_start();
?>
<link rel="stylesheet" href="<?php echo SWB . 'plugins/statistik-siddiq/assets/dashboard.css'; ?>">
<style>
    @media print {
        * { 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
        }
        body { 
            margin: 0;
            padding: 0;
            font-size: 10pt;
        }
        .non-printable { display: none !important; }
        
        /* Header judul */
        h2 { 
            font-size: 14pt;
            margin: 0 0 10px 0;
            padding: 10px 0;
            border-bottom: 2px solid #333;
            text-align: center;
        }
        
        /* Dashboard container */
        .dashboard-container {
            padding: 0;
            margin: 0;
        }
        
        /* Row cards - lebih compact */
        .dashboard-row {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 8px !important;
            margin-bottom: 12px !important;
            page-break-inside: avoid;
        }
        
        /* Stat cards */
        .stat-card {
            padding: 8px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px;
            background: #f8f9fa !important;
            page-break-inside: avoid;
        }
        .stat-header {
            margin-bottom: 4px !important;
        }
        .stat-title {
            font-size: 8pt !important;
            font-weight: 600;
        }
        .stat-value {
            font-size: 16pt !important;
            font-weight: bold;
            color: #333 !important;
        }
        .stat-menu { display: none !important; }
        
        /* Chart row - 3 kolom untuk chart */
        .chart-row {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
        }
        
        /* Chart cards */
        .chart-card {
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px;
            background: white !important;
            page-break-inside: avoid;
        }
        .chart-header {
            margin-bottom: 8px !important;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
        }
        .chart-title {
            font-size: 9pt !important;
            font-weight: 600;
        }
        .chart-menu { display: none !important; }
        
        /* Donut chart - lebih kecil */
        .donut-chart {
            max-width: 150px !important;
            margin: 0 auto 8px auto !important;
        }
        .donut-center {
            font-size: 8pt !important;
        }
        .donut-total {
            font-size: 14pt !important;
        }
        .donut-label {
            font-size: 7pt !important;
        }
        
        /* Legend - lebih compact */
        .chart-legend {
            max-height: none !important;
        }
        .legend-item {
            padding: 2px 0 !important;
            font-size: 7pt !important;
        }
        .legend-color {
            width: 10px !important;
            height: 10px !important;
        }
        .legend-value {
            font-size: 7pt !important;
        }
        
        /* Progress table */
        .progress-card {
            margin-top: 12px !important;
            page-break-inside: avoid;
        }
        .progress-table {
            width: 100% !important;
            font-size: 8pt !important;
        }
        .progress-table th,
        .progress-table td {
            padding: 4px !important;
            border: 1px solid #ddd !important;
        }
        .progress-table th {
            background: #f8f9fa !important;
            font-weight: 600;
        }
        
        /* Footer */
        .metabase-footer {
            margin-top: 15px !important;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 7pt !important;
            text-align: center;
            color: #666 !important;
        }
        .metabase-logo {
            height: 12px !important;
        }
        
        /* Page break hints */
        .chart-row {
            page-break-before: auto;
            page-break-after: auto;
        }
    }
</style>

<?php
// Proses filter tahun
// Data kumulatif dari awal sampai akhir tahun yang dipilih
$filterYear = isset($_GET['year']) && !empty($_GET['year']) ? intval($_GET['year']) : null;

// Build WHERE clause untuk filter tahun
$dateFilter = "";
if ($filterYear) {
    $dateFilter = " AND YEAR(input_date) <= $filterYear";
    echo '<div style="background: #e7f3ff; padding: 10px 20px; margin: 0 0 20px 0; border-left: 4px solid #007bff;">';
    
    $currentYear = date('Y');
    if ($filterYear < $currentYear) {
        // Tahun sudah lewat
        echo '<strong>Filter Aktif:</strong> Data s.d Akhir Tahun ' . $filterYear;
    } else {
        // Tahun berjalan atau masa depan
        echo '<strong>Filter Aktif:</strong> Data Tahun ' . $filterYear . ' (s.d ' . date('d F Y') . ')';
    }
    
    echo '</div>';
}

// QUERY 1: Total Koleksi (judul biblio)
$query_total_koleksi = "SELECT COUNT(DISTINCT biblio_id) as total FROM biblio WHERE 1=1 $dateFilter";
$result_total_koleksi = $dbs->query($query_total_koleksi);
$total_koleksi = $result_total_koleksi->fetch_assoc()['total'];

// QUERY 2: Total Koleksi Bereksemplar (biblio yang punya item)
$query_koleksi_bereksemplar = "SELECT COUNT(DISTINCT b.biblio_id) as total 
                               FROM biblio b 
                               INNER JOIN item i ON b.biblio_id = i.biblio_id
                               WHERE 1=1" . str_replace('input_date', 'b.input_date', $dateFilter);
$result_koleksi_bereksemplar = $dbs->query($query_koleksi_bereksemplar);
$total_koleksi_bereksemplar = $result_koleksi_bereksemplar->fetch_assoc()['total'];

// QUERY 3: Total Eksemplar
$query_total_eksemplar = "SELECT COUNT(item_id) as total FROM item WHERE 1=1 $dateFilter";
$result_total_eksemplar = $dbs->query($query_total_eksemplar);
$total_eksemplar = $result_total_eksemplar->fetch_assoc()['total'];

// QUERY 4: Total Eksemplar Tanpa Judul (item tanpa biblio_id atau biblio yang dihapus)
$query_eksemplar_tanpa_judul = "SELECT COUNT(i.item_id) as total 
                                FROM item i 
                                LEFT JOIN biblio b ON i.biblio_id = b.biblio_id 
                                WHERE b.biblio_id IS NULL";
$result_eksemplar_tanpa_judul = $dbs->query($query_eksemplar_tanpa_judul);
$total_eksemplar_tanpa_judul = $result_eksemplar_tanpa_judul->fetch_assoc()['total'];

// QUERY 5: Distribusi Koleksi Berdasarkan GMD
$query_gmd = "SELECT 
                COALESCE(g.gmd_name, 'Other') as gmd_name,
                COUNT(b.biblio_id) as total,
                ROUND((COUNT(b.biblio_id) * 100.0 / (SELECT COUNT(*) FROM biblio WHERE biblio_id IN (SELECT DISTINCT biblio_id FROM item))), 2) as percentage
              FROM biblio b
              LEFT JOIN mst_gmd g ON b.gmd_id = g.gmd_id
              WHERE b.biblio_id IN (SELECT DISTINCT biblio_id FROM item)
              GROUP BY g.gmd_name
              ORDER BY total DESC";
$result_gmd = $dbs->query($query_gmd);

// QUERY 6: Distribusi Koleksi Berdasarkan Subjek/Topic
$query_topic = "SELECT 
                  t.topic,
                  COUNT(DISTINCT bt.biblio_id) as total,
                  ROUND((COUNT(DISTINCT bt.biblio_id) * 100.0 / (SELECT COUNT(*) FROM biblio WHERE biblio_id IN (SELECT DISTINCT biblio_id FROM item))), 2) as percentage
                FROM biblio_topic bt
                INNER JOIN mst_topic t ON bt.topic_id = t.topic_id
                WHERE bt.biblio_id IN (SELECT DISTINCT biblio_id FROM item)
                GROUP BY t.topic
                ORDER BY total DESC
                LIMIT 15";
$result_topic = $dbs->query($query_topic);

// QUERY 6b: Distribusi Koleksi Berdasarkan Tipe Koleksi/Collection Type (SEMUA)
$query_coll_type = "SELECT 
                      COALESCE(ct.coll_type_name, 'Unknown') as coll_type_name,
                      COUNT(DISTINCT i.biblio_id) as total,
                      ROUND((COUNT(DISTINCT i.biblio_id) * 100.0 / (SELECT COUNT(DISTINCT biblio_id) FROM item)), 2) as percentage
                    FROM item i
                    LEFT JOIN mst_coll_type ct ON i.coll_type_id = ct.coll_type_id
                    GROUP BY ct.coll_type_name
                    ORDER BY total DESC";
$result_coll_type = $dbs->query($query_coll_type);

// QUERY 7: Total Anggota
$query_total_member = "SELECT COUNT(member_id) as total FROM member WHERE 1=1" . str_replace('input_date', 'register_date', $dateFilter);
$result_total_member = $dbs->query($query_total_member);
$total_member = $result_total_member->fetch_assoc()['total'];

// QUERY 8: Total Anggota Aktif (yang pernah melakukan peminjaman)
$query_member_aktif = "SELECT COUNT(DISTINCT member_id) as total FROM loan WHERE 1=1" . str_replace('input_date', 'loan_date', $dateFilter);
$result_member_aktif = $dbs->query($query_member_aktif);
$total_member_aktif = $result_member_aktif->fetch_assoc()['total'];

// QUERY 9: Total Transaksi
$query_total_transaksi = "SELECT COUNT(loan_id) as total FROM loan WHERE 1=1" . str_replace('input_date', 'loan_date', $dateFilter);
$result_total_transaksi = $dbs->query($query_total_transaksi);
$total_transaksi = $result_total_transaksi->fetch_assoc()['total'];

// QUERY 10: Total Transaksi Aktif (belum dikembalikan)
$query_transaksi_aktif = "SELECT COUNT(loan_id) as total FROM loan WHERE is_return = 0" . str_replace('input_date', 'loan_date', $dateFilter);
$result_transaksi_aktif = $dbs->query($query_transaksi_aktif);
$total_transaksi_aktif = $result_transaksi_aktif->fetch_assoc()['total'];

// QUERY 11: Progres Peminjaman per tahun
$display_year = $filterYear ? $filterYear : date('Y');
$query_progres = "SELECT 
                    MONTH(loan_date) as bulan,
                    COUNT(loan_id) as total
                  FROM loan
                  WHERE YEAR(loan_date) = ?
                  GROUP BY MONTH(loan_date)
                  ORDER BY bulan";
$stmt_progres = $dbs->prepare($query_progres);
$stmt_progres->bind_param('i', $display_year);
$stmt_progres->execute();
$result_progres = $stmt_progres->get_result();

$progres_data = array_fill(1, 12, 0);
while ($row = $result_progres->fetch_assoc()) {
    $progres_data[$row['bulan']] = $row['total'];
}

// PREPARE CHART DATA FOR SESSION
// Ambil data per tahun yang benar-benar ada (exclude 0 dan invalid dates)
$query_years_chart = "SELECT DISTINCT YEAR(input_date) as year FROM (
                      SELECT input_date FROM biblio WHERE input_date IS NOT NULL AND input_date != '0000-00-00'
                      UNION ALL
                      SELECT input_date FROM item WHERE input_date IS NOT NULL AND input_date != '0000-00-00'
                     ) as combined 
                     WHERE YEAR(input_date) IS NOT NULL 
                     AND YEAR(input_date) > 0
                     AND YEAR(input_date) <= " . date('Y') . "
                     ORDER BY year ASC";
$result_years_chart = $dbs->query($query_years_chart);

$chart_years = [];
$chart_koleksi = [];
$chart_eksemplar = [];

if ($result_years_chart) {
    while ($row_year_chart = $result_years_chart->fetch_assoc()) {
        $year = $row_year_chart['year'];
        
        // Query total koleksi sampai tahun ini (kumulatif)
        $query_chart_koleksi = "SELECT COUNT(DISTINCT biblio_id) as total 
                                FROM biblio 
                                WHERE YEAR(input_date) <= $year";
        $result_chart_koleksi = $dbs->query($query_chart_koleksi);
        $total_chart_koleksi = $result_chart_koleksi ? $result_chart_koleksi->fetch_assoc()['total'] : 0;
        
        // Query total eksemplar sampai tahun ini (kumulatif)
        $query_chart_eksemplar = "SELECT COUNT(item_id) as total 
                                  FROM item 
                                  WHERE YEAR(input_date) <= $year";
        $result_chart_eksemplar = $dbs->query($query_chart_eksemplar);
        $total_chart_eksemplar = $result_chart_eksemplar ? $result_chart_eksemplar->fetch_assoc()['total'] : 0;
        
        $chart_years[] = strval($year);
        $chart_koleksi[] = intval($total_chart_koleksi);
        $chart_eksemplar[] = intval($total_chart_eksemplar);
    }
}

// Store chart data in session
$chart = [];
$chart['xAxis'] = $chart_years;
$chart['data'] = [
    'Total_Koleksi_(Judul)' => $chart_koleksi,
    'Total_Eksemplar_(Item)' => $chart_eksemplar
];
$chart['title'] = 'Grafik Pertumbuhan Koleksi & Eksemplar';
// TIDAK set chart_type agar fillColor transparan (hanya garis, tanpa area)
$_SESSION['chart'] = $chart;

?>

<div style="padding: 20px; background: #fff;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Statistik Siddiq</h2>
        <div style="display: flex; gap: 10px;" class="non-printable">
            <a class="s-btn btn btn-default printReport" onclick="window.print()" href="#">🖨️ <?php echo __('Cetak Halaman Ini'); ?></a>
            <a class="s-btn btn btn-default notAJAX openPopUp" href="<?php echo MWB . 'reporting/pop_chart.php'; ?>" width="900" height="600">📈 <?php echo __('Tampilkan Grafik Pertumbuhan'); ?></a>
        </div>
    </div>
</div>

<div class="dashboard-container">
    <!-- Row 1: Summary Cards -->
    <div class="dashboard-row">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Koleksi</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_koleksi, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Koleksi Bereksemplar</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_koleksi_bereksemplar, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Eksemplar</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_eksemplar, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Eksemplar Tanpa Judul</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_eksemplar_tanpa_judul, 0, ',', '.'); ?></div>
        </div>
    </div>

    <!-- Row 2: Distribusi GMD dan Subjek -->
    <div class="dashboard-row chart-row">
        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-title">Distribusi Koleksi Berdasarkan GMD</span>
                <span class="chart-menu">⋮</span>
            </div>
            <div class="chart-content">
                <div class="donut-chart">
                    <svg viewBox="0 0 200 200" class="donut-svg">
                        <?php
                        $colors = ['#4A90E2', '#85B8E8', '#5A9BD5', '#3B72B8', '#2C5A9A', '#64B5F6', '#1976D2', '#0D47A1', '#90CAF9', '#42A5F5'];
                        
                        // Hitung total untuk GMD
                        $total_gmd = 0;
                        $result_gmd->data_seek(0);
                        while ($row_temp = $result_gmd->fetch_assoc()) {
                            $total_gmd += $row_temp['total'];
                        }
                        
                        $centerX = 100;
                        $centerY = 100;
                        $outerRadius = 80;
                        $innerRadius = 50;
                        $currentAngle = 0;
                        $color_index = 0;
                        
                        $result_gmd->data_seek(0);
                        while ($row = $result_gmd->fetch_assoc()) {
                            if ($row['total'] > 0 && $total_gmd > 0) {
                                $percentage = ($row['total'] / $total_gmd);
                                $angle = $percentage * 360;
                                
                                $startAngle = $currentAngle - 90;
                                $endAngle = $startAngle + $angle;
                                
                                // Outer arc
                                $x1_outer = $centerX + $outerRadius * cos(deg2rad($startAngle));
                                $y1_outer = $centerY + $outerRadius * sin(deg2rad($startAngle));
                                $x2_outer = $centerX + $outerRadius * cos(deg2rad($endAngle));
                                $y2_outer = $centerY + $outerRadius * sin(deg2rad($endAngle));
                                
                                // Inner arc
                                $x1_inner = $centerX + $innerRadius * cos(deg2rad($startAngle));
                                $y1_inner = $centerY + $innerRadius * sin(deg2rad($startAngle));
                                $x2_inner = $centerX + $innerRadius * cos(deg2rad($endAngle));
                                $y2_inner = $centerY + $innerRadius * sin(deg2rad($endAngle));
                                
                                $largeArc = $angle > 180 ? 1 : 0;
                                $color = $colors[$color_index % count($colors)];
                                ?>
                                <path
                                    d="M <?php echo $x1_outer; ?>,<?php echo $y1_outer; ?> 
                                       A <?php echo $outerRadius; ?>,<?php echo $outerRadius; ?> 0 <?php echo $largeArc; ?>,1 <?php echo $x2_outer; ?>,<?php echo $y2_outer; ?>
                                       L <?php echo $x2_inner; ?>,<?php echo $y2_inner; ?>
                                       A <?php echo $innerRadius; ?>,<?php echo $innerRadius; ?> 0 <?php echo $largeArc; ?>,0 <?php echo $x1_inner; ?>,<?php echo $y1_inner; ?> Z"
                                    fill="<?php echo $color; ?>"
                                />
                                <?php
                                $currentAngle += $angle;
                                $color_index++;
                            }
                        }
                        ?>
                    </svg>
                    <div class="donut-center">
                        <div class="donut-total"><?php echo number_format($total_gmd, 0, ',', '.'); ?></div>
                        <div class="donut-label">Total</div>
                    </div>
                </div>
                <div class="chart-legend">
                    <?php
                    $result_gmd->data_seek(0);
                    $color_index = 0;
                    while ($row = $result_gmd->fetch_assoc()) {
                        if ($row['total'] > 0 && $total_gmd > 0) {
                            $color = $colors[$color_index % count($colors)];
                            $pct = ($row['total'] / $total_gmd) * 100;
                            ?>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: <?php echo $color; ?>"></span>
                                <span class="legend-label"><?php echo htmlspecialchars($row['gmd_name']); ?></span>
                                <span class="legend-value"><?php echo number_format($pct, 2); ?>%</span>
                            </div>
                            <?php
                            $color_index++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-title">Distribusi Koleksi Berdasarkan Subjek</span>
                <span class="chart-menu">⋮</span>
            </div>
            <div class="chart-content">
                <div class="donut-chart">
                    <svg viewBox="0 0 200 200" class="donut-svg">
                        <?php
                        $colors_topic = ['#4A90E2', '#85B8E8', '#5A9BD5', '#E57373', '#F48FB1', '#FFB74D', '#81C784', '#64B5F6', '#9575CD', '#A1887F', '#90A4AE', '#FFCC80', '#CE93D8', '#80CBC4', '#C5E1A5'];
                        $centerX = 100;
                        $centerY = 100;
                        $outerRadius = 80;
                        $innerRadius = 50;
                        $currentAngle = 0;
                        $color_index = 0;
                        
                        while ($row = $result_topic->fetch_assoc()) {
                            if ($row['total'] > 0 && $total_koleksi_bereksemplar > 0) {
                                $percentage = ($row['total'] / $total_koleksi_bereksemplar);
                                $angle = $percentage * 360;
                                
                                $startAngle = $currentAngle - 90;
                                $endAngle = $startAngle + $angle;
                                
                                // Outer arc
                                $x1_outer = $centerX + $outerRadius * cos(deg2rad($startAngle));
                                $y1_outer = $centerY + $outerRadius * sin(deg2rad($startAngle));
                                $x2_outer = $centerX + $outerRadius * cos(deg2rad($endAngle));
                                $y2_outer = $centerY + $outerRadius * sin(deg2rad($endAngle));
                                
                                // Inner arc
                                $x1_inner = $centerX + $innerRadius * cos(deg2rad($startAngle));
                                $y1_inner = $centerY + $innerRadius * sin(deg2rad($startAngle));
                                $x2_inner = $centerX + $innerRadius * cos(deg2rad($endAngle));
                                $y2_inner = $centerY + $innerRadius * sin(deg2rad($endAngle));
                                
                                $largeArc = $angle > 180 ? 1 : 0;
                                $color = $colors_topic[$color_index % count($colors_topic)];
                                ?>
                                <path
                                    d="M <?php echo $x1_outer; ?>,<?php echo $y1_outer; ?> 
                                       A <?php echo $outerRadius; ?>,<?php echo $outerRadius; ?> 0 <?php echo $largeArc; ?>,1 <?php echo $x2_outer; ?>,<?php echo $y2_outer; ?>
                                       L <?php echo $x2_inner; ?>,<?php echo $y2_inner; ?>
                                       A <?php echo $innerRadius; ?>,<?php echo $innerRadius; ?> 0 <?php echo $largeArc; ?>,0 <?php echo $x1_inner; ?>,<?php echo $y1_inner; ?> Z"
                                    fill="<?php echo $color; ?>"
                                />
                                <?php
                                $currentAngle += $angle;
                                $color_index++;
                            }
                        }
                        ?>
                    </svg>
                    <div class="donut-center">
                        <div class="donut-total"><?php echo number_format($total_koleksi_bereksemplar, 0, ',', '.'); ?></div>
                        <div class="donut-label">Total</div>
                    </div>
                </div>
                <div class="chart-legend">
                    <?php
                    $result_topic->data_seek(0);
                    $color_index = 0;
                    while ($row = $result_topic->fetch_assoc()) {
                        if ($row['total'] > 0) {
                            $color = $colors_topic[$color_index % count($colors_topic)];
                            ?>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: <?php echo $color; ?>"></span>
                                <span class="legend-label"><?php echo htmlspecialchars($row['topic']); ?></span>
                                <span class="legend-value"><?php echo number_format($row['percentage'], 2); ?>%</span>
                            </div>
                            <?php
                            $color_index++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-title">Distribusi Koleksi Berdasarkan Tipe Koleksi</span>
                <span class="chart-menu">⋮</span>
            </div>
            <div class="chart-content">
                <div class="donut-chart">
                    <svg viewBox="0 0 200 200" class="donut-svg">
                        <?php
                        $colors_coll = ['#4A90E2', '#E57373', '#81C784', '#FFB74D', '#9575CD', '#64B5F6', '#F48FB1', '#80CBC4', '#FFCC80', '#CE93D8', '#A1887F', '#90A4AE', '#C5E1A5', '#85B8E8', '#5A9BD5', '#3B72B8'];
                        
                        // Hitung total untuk percentage
                        $total_items_coll = 0;
                        $result_coll_type_temp = $dbs->query($query_coll_type);
                        while ($row = $result_coll_type_temp->fetch_assoc()) {
                            $total_items_coll += $row['total'];
                        }
                        
                        $result_coll_type->data_seek(0);
                        $centerX = 100;
                        $centerY = 100;
                        $outerRadius = 80;
                        $innerRadius = 50;
                        $currentAngle = 0;
                        $color_index = 0;
                        
                        // Draw SEMUA categories
                        while ($row = $result_coll_type->fetch_assoc()) {
                            if ($row['total'] > 0 && $total_items_coll > 0) {
                                $percentage = ($row['total'] / $total_items_coll);
                                $angle = $percentage * 360;
                                
                                $startAngle = $currentAngle - 90;
                                $endAngle = $startAngle + $angle;
                                
                                // Outer arc
                                $x1_outer = $centerX + $outerRadius * cos(deg2rad($startAngle));
                                $y1_outer = $centerY + $outerRadius * sin(deg2rad($startAngle));
                                $x2_outer = $centerX + $outerRadius * cos(deg2rad($endAngle));
                                $y2_outer = $centerY + $outerRadius * sin(deg2rad($endAngle));
                                
                                // Inner arc
                                $x1_inner = $centerX + $innerRadius * cos(deg2rad($startAngle));
                                $y1_inner = $centerY + $innerRadius * sin(deg2rad($startAngle));
                                $x2_inner = $centerX + $innerRadius * cos(deg2rad($endAngle));
                                $y2_inner = $centerY + $innerRadius * sin(deg2rad($endAngle));
                                
                                $largeArc = $angle > 180 ? 1 : 0;
                                $color = $colors_coll[$color_index % count($colors_coll)];
                                ?>
                                <path
                                    d="M <?php echo $x1_outer; ?>,<?php echo $y1_outer; ?> 
                                       A <?php echo $outerRadius; ?>,<?php echo $outerRadius; ?> 0 <?php echo $largeArc; ?>,1 <?php echo $x2_outer; ?>,<?php echo $y2_outer; ?>
                                       L <?php echo $x2_inner; ?>,<?php echo $y2_inner; ?>
                                       A <?php echo $innerRadius; ?>,<?php echo $innerRadius; ?> 0 <?php echo $largeArc; ?>,0 <?php echo $x1_inner; ?>,<?php echo $y1_inner; ?> Z"
                                    fill="<?php echo $color; ?>"
                                />
                                <?php
                                $currentAngle += $angle;
                                $color_index++;
                            }
                        }
                        ?>
                    </svg>
                    <div class="donut-center">
                        <div class="donut-total"><?php echo number_format($total_items_coll, 0, ',', '.'); ?></div>
                        <div class="donut-label">Total</div>
                    </div>
                </div>
                <div class="chart-legend">
                    <?php
                    $result_coll_type->data_seek(0);
                    $color_index = 0;
                    while ($row = $result_coll_type->fetch_assoc()) {
                        if ($row['total'] > 0) {
                            $color = $colors_coll[$color_index % count($colors_coll)];
                            ?>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: <?php echo $color; ?>"></span>
                                <span class="legend-label"><?php echo htmlspecialchars($row['coll_type_name']); ?></span>
                                <span class="legend-value"><?php echo number_format($row['percentage'], 2); ?>%</span>
                            </div>
                            <?php
                            $color_index++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Anggota dan Transaksi -->
    <div class="dashboard-row">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Anggota</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_member, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Anggota Aktif</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_member_aktif, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Transaksi</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_transaksi, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Transaksi Aktif</span>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-value"><?php echo number_format($total_transaksi_aktif, 0, ',', '.'); ?></div>
        </div>
    </div>

    <!-- Row 4: Progres Peminjaman -->
    <div class="dashboard-row full-width">
        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-title">Progres Peminjaman <?php echo $display_year; ?></span>
                <span class="chart-menu">⋮</span>
            </div>
            <div class="chart-content">
                <table class="progress-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;"></th>
                            <?php
                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            foreach ($months as $month) {
                                echo "<th>{$month}</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Mahasiswa</strong></td>
                            <?php
                            foreach ($progres_data as $bulan => $total) {
                                echo "<td>" . number_format($total, 0, ',', '.') . "</td>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><strong>Dosen</strong></td>
                            <?php
                            // Contoh data dummy untuk dosen (bisa disesuaikan dengan query real)
                            for ($i = 1; $i <= 12; $i++) {
                                echo "<td>0</td>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><strong>Pegawai</strong></td>
                            <?php
                            // Contoh data dummy untuk pegawai (bisa disesuaikan dengan query real)
                            for ($i = 1; $i <= 12; $i++) {
                                echo "<td>0</td>";
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require SB . 'admin/admin_template/pop_iframe_tpl.php';
?>
