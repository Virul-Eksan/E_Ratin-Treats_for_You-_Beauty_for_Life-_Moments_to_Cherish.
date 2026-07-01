<?php
require_once 'db.php';

$date_from = $_GET['date_from'] ?? null;
$date_to   = $_GET['date_to']   ?? null;

$is_custom = false;
if ($date_from && $date_to) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        $is_custom = true;
    }
}

// --- Fetch Products ---
$products = $pdo->query("SELECT id, name, category, stock, price, cost FROM products ORDER BY category, name")->fetchAll();

if ($is_custom) {
    // --- Period Sales ---
    $period_stmt = $pdo->prepare("SELECT cart_details FROM orders WHERE is_deleted=0 AND DATE(created_at) BETWEEN ? AND ?");
    $period_stmt->execute([$date_from, $date_to]);
    $period_orders = $period_stmt->fetchAll();
    $today_map = [];
    foreach ($period_orders as $o) {
        foreach (json_decode($o['cart_details'], true) ?: [] as $item) {
            $pid = $item['id'] ?? null;
            if ($pid) $today_map[$pid] = ($today_map[$pid] ?? 0) + (int)($item['quantity'] ?? 0);
        }
    }

    // --- Monthly Sales for months included in period ---
    $start_m = date('Y-m-01', strtotime($date_from));
    $end_m   = date('Y-m-t', strtotime($date_to));
    $month_stmt = $pdo->prepare("SELECT cart_details FROM orders WHERE is_deleted=0 AND DATE(created_at) BETWEEN ? AND ?");
    $month_stmt->execute([$start_m, $end_m]);
    $month_orders = $month_stmt->fetchAll();
    $month_map = [];
    foreach ($month_orders as $o) {
        foreach (json_decode($o['cart_details'], true) ?: [] as $item) {
            $pid = $item['id'] ?? null;
            if ($pid) $month_map[$pid] = ($month_map[$pid] ?? 0) + (int)($item['quantity'] ?? 0);
        }
    }
} else {
    // --- Today's Sales ---
    $today_orders = $pdo->query("SELECT cart_details FROM orders WHERE is_deleted=0 AND DATE(created_at)=CURDATE()")->fetchAll();
    $today_map = [];
    foreach ($today_orders as $o) {
        foreach (json_decode($o['cart_details'], true) ?: [] as $item) {
            $pid = $item['id'] ?? null;
            if ($pid) $today_map[$pid] = ($today_map[$pid] ?? 0) + (int)($item['quantity'] ?? 0);
        }
    }

    // --- Monthly Sales ---
    $month_orders = $pdo->query("SELECT cart_details FROM orders WHERE is_deleted=0 AND YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")->fetchAll();
    $month_map = [];
    foreach ($month_orders as $o) {
        foreach (json_decode($o['cart_details'], true) ?: [] as $item) {
            $pid = $item['id'] ?? null;
            if ($pid) $month_map[$pid] = ($month_map[$pid] ?? 0) + (int)($item['quantity'] ?? 0);
        }
    }
}

// --- Build Report ---
$by_cat = [];
$grand_today_sold = 0; $grand_today_rev = 0; $grand_today_prof = 0;
$grand_month_sold = 0; $grand_month_rev = 0; $grand_month_prof = 0;
foreach ($products as $p) {
    $pid = $p['id'];
    $ts  = $today_map[$pid] ?? 0;
    $ms  = $month_map[$pid] ?? 0;
    $tr  = round($ts * (float)$p['price'], 2);
    $tc  = round($ts * (float)$p['cost'], 2);
    $tp  = $tr - $tc;
    $mr  = round($ms * (float)$p['price'], 2);
    $mc  = round($ms * (float)$p['cost'], 2);
    $mp  = $mr - $mc;
    $grand_today_sold += $ts; $grand_today_rev += $tr; $grand_today_prof += $tp;
    $grand_month_sold += $ms; $grand_month_rev += $mr; $grand_month_prof += $mp;
    $by_cat[$p['category']][] = [
        'name' => $p['name'], 'price' => (float)$p['price'], 'cost' => (float)$p['cost'],
        'stock' => (int)$p['stock'],
        'morning_stock' => (int)$p['stock'] + $ts,
        'today_sold' => $ts, 'today_rev' => $tr, 'today_prof' => $tp,
        'month_sold' => $ms, 'month_rev' => $mr, 'month_prof' => $mp,
    ];
}

$cat_colors = ['Chocolates'=>['#8b5a2b','#d4956a'], 'Cosmetics'=>['#9b3f6f','#e07fa8'], 'Nuts'=>['#4a7c2f','#8bc34a']];

if ($is_custom) {
    $report_date  = date('F j, Y', strtotime($date_from)) . ' to ' . date('F j, Y', strtotime($date_to));
    $start_m_str  = date('F Y', strtotime($date_from));
    $end_m_str    = date('F Y', strtotime($date_to));
    $report_month = ($start_m_str === $end_m_str) ? $start_m_str : "$start_m_str to $end_m_str";
} else {
    $report_date  = date('F j, Y');
    $report_month = date('F Y');
}

// --- SVG Bar Chart Data ---
$chart_items = []; $max_sold = 1;
foreach ($products as $p) {
    $s = $today_map[$p['id']] ?? 0;
    $chart_items[] = ['name'=>$p['name'], 'sold'=>$s, 'cat'=>$p['category']];
    if ($s > $max_sold) $max_sold = $s;
}
$bar_w = 36; $bar_gap = 8; $chart_h = 200; $chart_pad = 50;
$svg_w = max(600, count($chart_items) * ($bar_w + $bar_gap) + $chart_pad * 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report — E RATIN — <?php echo $report_date; ?></title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
  :root {
    --gold:#d4a017; --dark:#0f172a; --card:#1e293b; --border:#334155;
    --green:#34d399; --yellow:#fbbf24; --red:#f87171; --blue:#60a5fa;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Inter',sans-serif; background:var(--dark); color:#e2e8f0; font-size:13px; }

  /* ---- SCREEN STYLES ---- */
  .print-btn {
    position:fixed; bottom:30px; right:30px; z-index:999;
    background:var(--gold); color:#0f172a; border:none; padding:14px 28px;
    border-radius:50px; font-size:15px; font-weight:700; cursor:pointer;
    box-shadow:0 4px 24px rgba(212,160,23,0.5); transition:transform .2s;
  }
  .print-btn:hover { transform:scale(1.05); }

  .page { max-width:900px; margin:0 auto; padding:30px 20px 80px; }

  /* ---- HEADER ---- */
  .report-header {
    background:linear-gradient(135deg,#1e293b,#0f172a);
    border:1px solid var(--border); border-radius:16px;
    padding:30px 36px; margin-bottom:28px;
    display:flex; justify-content:space-between; align-items:center;
    border-top:4px solid var(--gold);
  }
  .header-left { display:flex; align-items:center; gap:20px; }
  .logo-circle { width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid var(--gold); }
  .brand-name { font-size:26px; font-weight:800; color:var(--gold); letter-spacing:1px; }
  .brand-sub  { color:#94a3b8; font-size:13px; margin-top:3px; }
  .header-right { text-align:right; }
  .report-title { font-size:18px; font-weight:700; color:#fff; }
  .report-meta  { color:#94a3b8; font-size:12px; margin-top:6px; }

  /* ---- STAT CARDS ---- */
  .stat-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:28px; }
  .stat-card { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; text-align:center; }
  .stat-num   { font-size:22px; font-weight:800; }
  .stat-label { font-size:11px; color:#94a3b8; margin-top:4px; }

  /* ---- SECTION TITLE ---- */
  .section-title {
    display:flex; align-items:center; gap:12px;
    background:var(--card); border-left:5px solid var(--gold);
    border-radius:0 10px 10px 0; padding:12px 20px; margin-bottom:20px;
  }
  .section-title h2 { font-size:15px; font-weight:700; color:#fff; }
  .section-title span { font-size:12px; color:#94a3b8; margin-left:auto; }

  /* ---- CATEGORY BLOCK ---- */
  .cat-block { margin-bottom:24px; }
  .cat-header {
    padding:10px 16px; border-radius:8px 8px 0 0;
    font-weight:700; font-size:13px; color:#fff; letter-spacing:.5px;
  }

  /* ---- TABLE ---- */
  table { width:100%; border-collapse:collapse; background:var(--dark); border-radius:0 0 8px 8px; overflow:hidden; }
  thead th { background:#1e293b; padding:9px 12px; text-align:center; font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; }
  thead th:first-child { text-align:left; }
  tbody tr:nth-child(even) { background:rgba(255,255,255,0.03); }
  tbody td { padding:9px 12px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.04); }
  tbody td:first-child { text-align:left; font-weight:500; }
  .subtotal-row td { font-weight:700; background:rgba(212,160,23,0.1); color:var(--gold); border-top:1px solid rgba(212,160,23,0.3); }
  .grand-row td { font-weight:800; background:rgba(212,160,23,0.18); color:var(--gold); font-size:13px; }
  .stock-ok    { color:var(--green); font-weight:600; }
  .stock-low   { color:var(--yellow); font-weight:600; }
  .stock-zero  { color:var(--red); font-weight:600; }
  .sold-num    { color:var(--yellow); font-weight:600; }

  /* ---- CHART ---- */
  .chart-wrap { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:20px; margin-bottom:28px; overflow-x:auto; }
  .chart-wrap h3 { font-size:13px; color:#fff; margin-bottom:16px; }
  svg text { font-family:'Inter',sans-serif; }

  /* ---- PRINT ---- */
  @media print {
    @page { size:A4; margin:14mm 12mm; }
    body { background:#fff !important; color:#111 !important; font-size:11px; }
    .print-btn { display:none !important; }
    .page { padding:0; max-width:100%; }
    .report-header { background:#fff !important; border:1.5px solid #ddd; border-radius:10px; }
    .brand-name { color:#b8860b !important; }
    .report-title { color:#111 !important; }
    .report-meta, .brand-sub, .header-right p { color:#555 !important; }
    .stat-card { background:#f8f9fa !important; border:1px solid #dee2e6 !important; }
    .stat-num { color:#111 !important; }
    .stat-label { color:#555 !important; }
    .section-title { background:#f1f5f9 !important; border-left:5px solid #b8860b !important; }
    .section-title h2 { color:#111 !important; }
    .section-title span { color:#555 !important; }
    table { background:#fff !important; }
    thead th { background:#f1f5f9 !important; color:#333 !important; }
    tbody td { border-bottom:1px solid #eee !important; color:#111 !important; }
    tbody tr:nth-child(even) { background:#fafafa !important; }
    .subtotal-row td { background:#fff9e6 !important; color:#b8860b !important; }
    .grand-row td { background:#fff3cd !important; color:#856404 !important; }
    .stock-ok  { color:#16a34a !important; }
    .stock-low { color:#ca8a04 !important; }
    .stock-zero{ color:#dc2626 !important; }
    .sold-num  { color:#d97706 !important; }
    .chart-wrap { background:#f8f9fa !important; border:1px solid #ddd !important; page-break-inside:avoid; }
    .cat-block { page-break-inside:avoid; }
  }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>

<div class="page">

  <!-- HEADER -->
  <div class="report-header">
    <div class="header-left">
      <img src="logo.jpg" class="logo-circle" alt="E RATIN Logo">
      <div>
        <div class="brand-name">E RATIN</div>
        <div class="brand-sub">Premium Store — Sales Analytics</div>
      </div>
    </div>
    <div class="header-right">
      <div class="report-title">📊 Full Sales Report</div>
      <p class="report-meta">Date: <?php echo $report_date; ?></p>
      <p class="report-meta">Month: <?php echo $report_month; ?></p>
      <p class="report-meta">Generated: <?php echo date('h:i A'); ?></p>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="stat-row" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card">
      <div class="stat-num" style="color:var(--yellow)"><?php echo $grand_today_sold; ?></div>
      <div class="stat-label"><?php echo $is_custom ? 'Units Sold (Period)' : 'Units Sold Today'; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:var(--green)">$<?php echo number_format($grand_today_rev,2); ?></div>
      <div class="stat-label"><?php echo $is_custom ? 'Period Revenue' : "Today's Revenue"; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:#ec4899">$<?php echo number_format($grand_today_prof,2); ?></div>
      <div class="stat-label"><?php echo $is_custom ? 'Period Profit' : "Today's Profit"; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:var(--blue)"><?php echo $grand_month_sold; ?></div>
      <div class="stat-label"><?php echo $is_custom ? 'Units Sold in Months' : 'Units Sold This Month'; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:#c084fc">$<?php echo number_format($grand_month_rev,2); ?></div>
      <div class="stat-label"><?php echo $is_custom ? 'Months Revenue' : 'Monthly Revenue'; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:#f43f5e">$<?php echo number_format($grand_month_prof,2); ?></div>
      <div class="stat-label"><?php echo $is_custom ? 'Months Profit' : 'Monthly Profit'; ?></div>
    </div>
  </div>

  <!-- ============ SECTION 1: DAILY SALES ============ -->
  <div class="section-title">
    <h2>📅 Section 1 — <?php echo $is_custom ? 'Period' : 'Daily'; ?> Sales Report</h2>
    <span><?php echo $report_date; ?></span>
  </div>

  <?php foreach ($by_cat as $cat => $items):
    $cc = $cat_colors[$cat] ?? ['#334155','#64748b'];
    $cat_ts = array_sum(array_column($items,'today_sold'));
    $cat_tr = array_sum(array_column($items,'today_rev'));
    $cat_tp = array_sum(array_column($items,'today_prof'));
  ?>
  <div class="cat-block">
    <div class="cat-header" style="background:<?php echo $cc[0]; ?>">📦 <?php echo htmlspecialchars($cat); ?></div>
    <table>
      <thead>
        <tr>
          <th>Product</th><th>Unit Price</th><th>Morning Stock</th>
          <th><?php echo $is_custom ? 'Sold in Period' : 'Sold Today'; ?></th>
          <th><?php echo $is_custom ? 'Period Revenue' : 'Today Revenue'; ?></th>
          <th><?php echo $is_custom ? 'Period Profit' : 'Today Profit'; ?></th>
          <th>Remaining Stock</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $r):
          $sc = $r['stock']<=0 ? 'stock-zero' : ($r['stock']<=5 ? 'stock-low' : 'stock-ok');
        ?>
        <tr>
          <td><?php echo htmlspecialchars($r['name']); ?></td>
          <td>$<?php echo number_format($r['price'],2); ?></td>
          <td><?php echo $r['morning_stock']; ?></td>
          <td class="sold-num"><?php echo $r['today_sold']; ?></td>
          <td>$<?php echo number_format($r['today_rev'],2); ?></td>
          <td>$<?php echo number_format($r['today_prof'],2); ?></td>
          <td class="<?php echo $sc; ?>"><?php echo $r['stock']; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="subtotal-row">
          <td>Subtotal</td><td></td><td></td>
          <td><?php echo $cat_ts; ?></td>
          <td>$<?php echo number_format($cat_tr,2); ?></td>
          <td>$<?php echo number_format($cat_tp,2); ?></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php endforeach; ?>

  <!-- Daily Grand Total -->
  <table style="margin-bottom:28px; border-radius:8px; overflow:hidden;">
    <tbody>
      <tr class="grand-row">
        <td>🏆 <?php echo $is_custom ? 'PERIOD' : 'DAILY'; ?> GRAND TOTAL</td><td></td><td></td>
        <td><?php echo $grand_today_sold; ?> units</td>
        <td>$<?php echo number_format($grand_today_rev,2); ?></td>
        <td>$<?php echo number_format($grand_today_prof,2); ?></td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <!-- ============ SECTION 2: DAILY CHART ============ -->
  <div class="section-title">
    <h2>📈 Section 2 — <?php echo $is_custom ? 'Period' : 'Daily'; ?> Sales Chart</h2>
    <span><?php echo $is_custom ? 'Units sold during selected period per product' : 'Units sold today per product'; ?></span>
  </div>

  <div class="chart-wrap">
    <h3><?php echo $is_custom ? 'Period Sales by Product' : "Today's Sales by Product"; ?></h3>
    <?php
      $ci = array_filter($chart_items, fn($i) => true);
      $ci = array_values($ci);
      $count = count($ci);
      $svg_w = max(600, $count * ($bar_w + $bar_gap) + $chart_pad * 2);
      $cat_hex = ['Chocolates'=>'#d4956a','Cosmetics'=>'#e07fa8','Nuts'=>'#8bc34a'];
    ?>
    <svg width="100%" viewBox="0 0 <?php echo $svg_w; ?> <?php echo $chart_h + 80; ?>" style="display:block;">
      <!-- Y-axis grid lines -->
      <?php for ($g = 0; $g <= 4; $g++):
        $gy = $chart_pad + ($chart_h - $chart_pad) * $g / 4;
        $gv = round($max_sold * (4 - $g) / 4);
      ?>
        <line x1="<?php echo $chart_pad; ?>" y1="<?php echo $gy; ?>"
              x2="<?php echo $svg_w - 10; ?>" y2="<?php echo $gy; ?>"
              stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
        <text x="<?php echo $chart_pad - 6; ?>" y="<?php echo $gy + 4; ?>"
              font-size="9" fill="#64748b" text-anchor="end"><?php echo $gv; ?></text>
      <?php endfor; ?>

      <?php foreach ($ci as $idx => $item):
        $bh = $max_sold > 0 ? round(($item['sold'] / $max_sold) * ($chart_h - $chart_pad)) : 0;
        $bx = $chart_pad + $idx * ($bar_w + $bar_gap);
        $by = $chart_pad + ($chart_h - $chart_pad) - $bh;
        $fill = $cat_hex[$item['cat']] ?? '#60a5fa';
        $lbl = mb_strlen($item['name']) > 10 ? mb_substr($item['name'],0,9).'…' : $item['name'];
      ?>
        <rect x="<?php echo $bx; ?>" y="<?php echo $by; ?>"
              width="<?php echo $bar_w; ?>" height="<?php echo max($bh,1); ?>"
              fill="<?php echo $fill; ?>" opacity="0.85" rx="4"/>
        <?php if ($item['sold'] > 0): ?>
          <text x="<?php echo $bx + $bar_w/2; ?>" y="<?php echo $by - 4; ?>"
                font-size="10" fill="#fff" text-anchor="middle" font-weight="bold">
            <?php echo $item['sold']; ?>
          </text>
        <?php endif; ?>
        <text x="<?php echo $bx + $bar_w/2; ?>"
              y="<?php echo $chart_pad + $chart_h - $chart_pad + 14; ?>"
              font-size="9" fill="#94a3b8" text-anchor="middle"
              transform="rotate(-35,<?php echo $bx+$bar_w/2; ?>,<?php echo $chart_pad+$chart_h-$chart_pad+14; ?>)">
          <?php echo htmlspecialchars($lbl); ?>
        </text>
      <?php endforeach; ?>

      <!-- X baseline -->
      <line x1="<?php echo $chart_pad; ?>" y1="<?php echo $chart_pad + $chart_h - $chart_pad; ?>"
            x2="<?php echo $svg_w - 10; ?>" y2="<?php echo $chart_pad + $chart_h - $chart_pad; ?>"
            stroke="#334155" stroke-width="1.5"/>

      <!-- Legend -->
      <?php $lx = $chart_pad; $li = 0; foreach ($cat_hex as $cn => $chex): ?>
        <rect x="<?php echo $lx + $li * 100; ?>" y="<?php echo $chart_h + 56; ?>" width="10" height="10" fill="<?php echo $chex; ?>" rx="2"/>
        <text x="<?php echo $lx + $li * 100 + 14; ?>" y="<?php echo $chart_h + 65; ?>" font-size="10" fill="#94a3b8"><?php echo $cn; ?></text>
      <?php $li++; endforeach; ?>
    </svg>
  </div>

  <!-- ============ SECTION 3: MONTHLY SALES ============ -->
  <div class="section-title">
    <h2>📅 Section 3 — Monthly Sales Report</h2>
    <span><?php echo $report_month; ?></span>
  </div>

  <?php foreach ($by_cat as $cat => $items):
    $cc = $cat_colors[$cat] ?? ['#334155','#64748b'];
    $cat_ms = array_sum(array_column($items,'month_sold'));
    $cat_mr = array_sum(array_column($items,'month_rev'));
    $cat_mp = array_sum(array_column($items,'month_prof'));
  ?>
  <div class="cat-block">
    <div class="cat-header" style="background:<?php echo $cc[0]; ?>">📦 <?php echo htmlspecialchars($cat); ?></div>
    <table>
      <thead>
        <tr>
          <th>Product</th><th>Unit Price</th>
          <th>Sold This Month</th><th>Monthly Revenue</th><th>Monthly Profit</th><th>Remaining Stock</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $r):
          $sc = $r['stock']<=0 ? 'stock-zero' : ($r['stock']<=5 ? 'stock-low' : 'stock-ok');
        ?>
        <tr>
          <td><?php echo htmlspecialchars($r['name']); ?></td>
          <td>$<?php echo number_format($r['price'],2); ?></td>
          <td class="sold-num"><?php echo $r['month_sold']; ?></td>
          <td>$<?php echo number_format($r['month_rev'],2); ?></td>
          <td>$<?php echo number_format($r['month_prof'],2); ?></td>
          <td class="<?php echo $sc; ?>"><?php echo $r['stock']; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="subtotal-row">
          <td>Subtotal</td><td></td>
          <td><?php echo $cat_ms; ?></td>
          <td>$<?php echo number_format($cat_mr,2); ?></td>
          <td>$<?php echo number_format($cat_mp,2); ?></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php endforeach; ?>

  <!-- Monthly Grand Total -->
  <table style="margin-bottom:28px; border-radius:8px; overflow:hidden;">
    <tbody>
      <tr class="grand-row">
        <td>🏆 MONTHLY GRAND TOTAL</td><td></td>
        <td><?php echo $grand_month_sold; ?> units</td>
        <td>$<?php echo number_format($grand_month_rev,2); ?></td>
        <td>$<?php echo number_format($grand_month_prof,2); ?></td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <div style="text-align:center; color:#475569; font-size:11px; padding-top:10px; border-top:1px solid #1e293b;">
    E RATIN Store — Confidential Sales Report — Generated <?php echo date('F j, Y \a\t h:i A'); ?>
  </div>

</div>
</body>
</html>
