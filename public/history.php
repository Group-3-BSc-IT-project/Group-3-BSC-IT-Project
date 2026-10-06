<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireAnyRole(['customer', 'admin']);

$repairs = $repairController->all();

// Map repair status to a badge style class defined in styled.css
function repairStatusBadgeClass($status) {
    switch ($status) {
        case 'Completed':
            return 'badge'; // default badge is green
        case 'In Progress':
            return 'badge yellow';
        case 'Pending':
            return 'badge yellow';
        case 'Canceled':
            return 'badge yellow';
        default:
            return 'badge blue';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Service History | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="admin.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="mechanic_create.php"><span class="icon">🧑‍🔧</span>Create Mechanic</a>
            <a href="vehicle_create.php"><span class="icon">➕</span>Add Vehicle</a>
            <a href="vehicles.php"><span class="icon">🚗</span>Vehicles</a>
            <a href="services.php"><span class="icon">🔧</span>Services</a>
            <a class="active" href="service_history.php"><span class="icon">📋</span>Service History</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Service History</h1>
                <p>All repair records across every booking</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section">
            <h2>Repairs</h2>
            <div class="table-card">
                <table class="table">
                    <tr>
                        <th>Repair ID</th>
                        <th>Booking ID</th>
                        <th>Mechanic ID</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Cost</th>
                        <th>Repair Date</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($repairs as $r): ?>
                        <tr>
                            <td><?= e($r['repair_id']) ?></td>
                            <td><?= e($r['booking_id']) ?></td>
                            <td><?= e($r['mechanic_id']) ?></td>
                            <td><?= e($r['description']) ?></td>
                            <td><span class="<?= repairStatusBadgeClass($r['status']) ?>"><?= e($r['status']) ?></span></td>
                            <td><?= $r['cost'] !== null ? 'R' . number_format((float)$r['cost'], 2) : '—' ?></td>
                            <td><?= e($r['repair_date'] ?? 'NULL') ?></td>
                            <td><?= e($r['created_at']) ?></td>
                            <td>
                                <div class="actions">
                                    <a class="small-btn" href="repair_edit.php?repair_id=<?= $r['repair_id'] ?>" title="Edit">✏️</a>
                                    <a class="small-btn" href="repair_copy.php?repair_id=<?= $r['repair_id'] ?>" title="Copy">📄</a>
                                    <form method="post" action="repair_delete.php" onsubmit="return confirm('Delete this repair record?');" style="display:inline">
                                        <input type="hidden" name="repair_id" value="<?= e($r['repair_id']) ?>">
                                        <button type="submit" class="small-btn" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($repairs)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center;color:var(--muted)">No service history yet.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>

</div>
</body>
</html>