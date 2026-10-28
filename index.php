<?php
/**
 * Electricity Bill Calculator
 * Slab rates:
 *   First 50 units   -> Rs. 3.50/unit
 *   Next 100 units   -> Rs. 4.00/unit
 *   Next 100 units   -> Rs. 5.20/unit
 *   Above 250 units  -> Rs. 6.50/unit
 */

$units = null;
$error = "";
$breakdown = [];
$total = 0.0;

function calculateBill(float $units): array {
    $slabs = [
        ["label" => "First 50 units",  "cap" => 50,  "rate" => 3.50],
        ["label" => "Next 100 units",  "cap" => 100, "rate" => 4.00],
        ["label" => "Next 100 units",  "cap" => 100, "rate" => 5.20],
        ["label" => "Above 250 units", "cap" => INF, "rate" => 6.50],
    ];

    $remaining = $units;
    $breakdown = [];
    $total = 0.0;

    foreach ($slabs as $slab) {
        if ($remaining <= 0) {
            break;
        }
        $slabUnits = min($remaining, $slab["cap"]);
        $slabCost  = $slabUnits * $slab["rate"];

        $breakdown[] = [
            "label" => $slab["label"],
            "units" => $slabUnits,
            "rate"  => $slab["rate"],
            "cost"  => $slabCost,
        ];

        $total     += $slabCost;
        $remaining -= $slabUnits;
    }

    return ["breakdown" => $breakdown, "total" => $total];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $raw = trim($_POST["units"] ?? "");

    if ($raw === "" || !is_numeric($raw)) {
        $error = "Enter a valid number of units.";
    } elseif ((float)$raw < 0) {
        $error = "Units can't be negative.";
    } elseif ((float)$raw > 100000) {
        $error = "That number looks too high — check and try again.";
    } else {
        $units  = (float)$raw;
        $result = calculateBill($units);
        $breakdown = $result["breakdown"];
        $total     = $result["total"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Electricity bill calculator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #14171C;
    --panel: #1C2128;
    --panel-border: #2B313B;
    --input-bg: #101317;
    --accent: #F2B705;
    --accent-dark: #14171C;
    --teal: #37B6A0;
    --text: #EDEFF2;
    --text-muted: #8B93A0;
    --danger: #E2564D;
    --danger-bg: #2A1918;
    --font-display: 'Space Grotesk', 'Inter', sans-serif;
    --font-mono: 'JetBrains Mono', 'Courier New', monospace;
    --font-body: 'Inter', sans-serif;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-body);
    min-height: 100vh;
    padding: 32px 18px 60px;
    background-image:
      linear-gradient(var(--panel-border) 1px, transparent 1px),
      linear-gradient(90deg, var(--panel-border) 1px, transparent 1px);
    background-size: 34px 34px;
    background-position: center top;
  }

  .wrap {
    max-width: 880px;
    margin: 0 auto;
  }

  header {
    text-align: center;
    margin-bottom: 30px;
  }

  .eyebrow {
    font-family: var(--font-mono);
    font-size: 12px;
    letter-spacing: 2px;
    color: var(--teal);
    text-transform: uppercase;
  }

  h1 {
    font-family: var(--font-display);
    font-size: clamp(26px, 4vw, 38px);
    font-weight: 700;
    margin: 8px 0 6px;
  }

  .subtitle {
    color: var(--text-muted);
    font-size: 14.5px;
    max-width: 480px;
    margin: 0 auto;
  }

  .layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 22px;
  }

  @media (min-width: 760px) {
    .layout { grid-template-columns: 1fr 1.15fr; align-items: start; }
  }

  .card {
    background: var(--panel);
    border: 1px solid var(--panel-border);
    border-radius: 16px;
    padding: 26px;
  }

  .card h2 {
    font-family: var(--font-display);
    font-size: 17px;
    font-weight: 600;
    margin: 0 0 18px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .bolt { color: var(--accent); }

  label {
    display: block;
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 8px;
  }

  .input-row {
    display: flex;
    gap: 10px;
  }

  input[type="number"] {
    flex: 1;
    background: var(--input-bg);
    border: 1px solid var(--panel-border);
    border-radius: 10px;
    padding: 13px 14px;
    color: var(--text);
    font-family: var(--font-mono);
    font-size: 17px;
    outline: none;
    transition: border-color 0.15s ease;
  }

  input[type="number"]:focus {
    border-color: var(--accent);
  }

  button {
    background: var(--accent);
    color: var(--accent-dark);
    border: none;
    border-radius: 10px;
    padding: 0 22px;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 14.5px;
    cursor: pointer;
    transition: transform 0.1s ease, filter 0.15s ease;
  }

  button:hover { filter: brightness(1.08); }
  button:active { transform: scale(0.97); }

  .error {
    margin-top: 12px;
    background: var(--danger-bg);
    border: 1px solid var(--danger);
    color: #F3B4AF;
    font-size: 13px;
    padding: 10px 12px;
    border-radius: 8px;
  }

  .rate-table {
    margin-top: 22px;
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .rate-table th, .rate-table td {
    text-align: left;
    padding: 8px 6px;
    border-bottom: 1px solid var(--panel-border);
    color: var(--text-muted);
  }

  .rate-table th {
    font-family: var(--font-display);
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text);
  }

  .rate-table td.rate-value {
    font-family: var(--font-mono);
    color: var(--text);
  }

  /* Meter / result display */
  .meter {
    text-align: center;
    padding: 22px 0 8px;
  }

  .meter-label {
    font-family: var(--font-mono);
    font-size: 11.5px;
    letter-spacing: 2px;
    color: var(--text-muted);
    text-transform: uppercase;
  }

  .meter-value {
    font-family: var(--font-mono);
    font-size: clamp(38px, 8vw, 52px);
    font-weight: 700;
    color: var(--accent);
    letter-spacing: 1px;
    margin: 6px 0 0;
    word-break: break-word;
  }

  .meter-units {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 4px;
  }

  .breakdown {
    margin-top: 22px;
    border-top: 1px solid var(--panel-border);
    padding-top: 16px;
  }

  .breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 9px 0;
    border-bottom: 1px dashed var(--panel-border);
    font-size: 13.5px;
  }

  .breakdown-row:last-child { border-bottom: none; }

  .breakdown-row .slab-label {
    color: var(--text);
  }

  .breakdown-row .slab-meta {
    color: var(--text-muted);
    font-family: var(--font-mono);
    font-size: 12.5px;
  }

  .breakdown-row .slab-cost {
    font-family: var(--font-mono);
    color: var(--teal);
    font-weight: 500;
  }

  .empty-state {
    text-align: center;
    color: var(--text-muted);
    font-size: 13.5px;
    padding: 40px 10px;
  }

  .empty-state .bolt-big {
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
    color: var(--panel-border);
  }

  footer {
    text-align: center;
    margin-top: 34px;
    color: var(--text-muted);
    font-size: 12px;
  }
</style>
</head>
<body>
<div class="wrap">

  <header>
    <div class="eyebrow">Slab-rate billing</div>
    <h1>Electricity bill calculator</h1>
    <p class="subtitle">Enter your monthly consumption in units (kWh) to see a slab-wise breakdown of your bill.</p>
  </header>

  <div class="layout">

    <div class="card">
      <h2><span class="bolt">&#9889;</span> Enter consumption</h2>
      <form method="POST" action="">
        <label for="units">Units consumed this month</label>
        <div class="input-row">
          <input
            type="number"
            id="units"
            name="units"
            placeholder="e.g. 180"
            step="0.01"
            min="0"
            value="<?= isset($_POST['units']) ? htmlspecialchars($_POST['units']) : '' ?>"
            required
          >
          <button type="submit">Calculate</button>
        </div>
        <?php if ($error): ?>
          <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
      </form>

      <table class="rate-table">
        <thead>
          <tr><th>Slab</th><th>Rate</th></tr>
        </thead>
        <tbody>
          <tr><td>First 50 units</td><td class="rate-value">Rs. 3.50 / unit</td></tr>
          <tr><td>Next 100 units</td><td class="rate-value">Rs. 4.00 / unit</td></tr>
          <tr><td>Next 100 units</td><td class="rate-value">Rs. 5.20 / unit</td></tr>
          <tr><td>Above 250 units</td><td class="rate-value">Rs. 6.50 / unit</td></tr>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h2>Bill summary</h2>

      <?php if ($units !== null): ?>
        <div class="meter">
          <div class="meter-label">Total payable</div>
          <div class="meter-value">Rs. <?= number_format($total, 2) ?></div>
          <div class="meter-units">for <?= rtrim(rtrim(number_format($units, 2), '0'), '.') ?> units consumed</div>
        </div>

        <div class="breakdown">
          <?php foreach ($breakdown as $row): ?>
            <div class="breakdown-row">
              <div>
                <div class="slab-label"><?= htmlspecialchars($row['label']) ?></div>
                <div class="slab-meta"><?= rtrim(rtrim(number_format($row['units'], 2), '0'), '.') ?> &times; Rs. <?= number_format($row['rate'], 2) ?></div>
              </div>
              <div class="slab-cost">Rs. <?= number_format($row['cost'], 2) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <span class="bolt-big">&#9889;</span>
          Your bill breakdown will appear here once you enter units and calculate.
        </div>
      <?php endif; ?>
    </div>

  </div>

  <footer>Rates are illustrative slab values — replace them in <code>calculateBill()</code> to match your local tariff.</footer>

</div>
</body>
</html>
