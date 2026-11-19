<?php
include('authentication.php');
include('includes/header.php');

$classMapping = [
    '100' => 'Nursery',
    '101' => 'LKG',
    '102' => 'UKG',
    '1' => 'Class 1',
    '2' => 'Class 2',
    '3' => 'Class 3',
    '4' => 'Class 4',
    '5' => 'Class 5',
    '6' => 'Class 6',
    '7' => 'Class 7',
    '8' => 'Class 8',
    '9' => 'Class 9',
    '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)',
    '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (With math)',
    '14' => 'Class 11 Comm. (Without math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)',
    '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (With math)',
    '19' => 'Class 12 Comm. (Without math)',
    '20' => 'Class 12 Arts'
];

$selectedClass = isset($_GET['class']) ? trim($_GET['class']) : '';
$selectedDate = isset($_GET['date']) && !empty($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">View Attendance</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Student Attendance Records</h2>
                <p class="page-sub-heading mb-0">View, inspect, and update student attendance by class and date.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="attendance-add.php<?= (!empty($selectedClass)) ? '?class=' . urlencode($selectedClass) . '&date=' . urlencode($selectedDate) : ''; ?>" class="btn-primary-action text-decoration-none">
                <i class="fas fa-plus me-2"></i> Mark Attendance
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Filter Card -->
    <div class="teacher-card mb-4">
        <form action="attendance-view.php" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <!-- Class Selection -->
                <div class="col-md-5">
                    <label for="class" class="form-label-custom mb-2">
                        <i class="fas fa-graduation-cap text-primary me-1"></i> Select Class <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <i class="fas fa-school position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                        <select name="class" id="class" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" required>
                            <option value="">-- Choose Class --</option>
                            <?php foreach ($classMapping as $c_id => $c_name): ?>
                                <option value="<?= $c_id; ?>" <?= ($selectedClass === (string)$c_id) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Date Picker -->
                <div class="col-md-4">
                    <label for="date" class="form-label-custom mb-2">
                        <i class="far fa-calendar-alt text-primary me-1"></i> Select Date <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <i class="fas fa-calendar-day position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                        <input type="date" 
                               name="date" 
                               id="date" 
                               value="<?= htmlspecialchars($selectedDate); ?>" 
                               class="form-control" 
                               style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" 
                               required>
                    </div>
                </div>

                <!-- Submit & Quick Buttons -->
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-filter-custom w-100" style="height: 46px;">
                            <i class="fas fa-search me-1"></i> Show Attendance
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Date Jump Links -->
            <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                <span class="text-muted small fw-semibold">Quick Date:</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setFilterDate('<?= date('Y-m-d'); ?>')" style="border-radius: 8px; font-size: 0.8rem;">
                    Today (<?= date('d M'); ?>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setFilterDate('<?= date('Y-m-d', strtotime('-1 day')); ?>')" style="border-radius: 8px; font-size: 0.8rem;">
                    Yesterday (<?= date('d M', strtotime('-1 day')); ?>)
                </button>
                <?php if (!empty($selectedClass)): ?>
                    <a href="attendance-view.php" class="btn btn-sm btn-outline-danger ms-auto" style="border-radius: 8px; font-size: 0.8rem;">
                        <i class="fas fa-redo me-1"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Attendance Display Area -->
    <?php
    if (!empty($selectedClass) && !empty($selectedDate)):
        $class_esc = mysqli_real_escape_string($con, $selectedClass);
        $date_esc  = mysqli_real_escape_string($con, $selectedDate);

        $attendanceQuery = "
            SELECT a.id AS attendance_id, a.date, a.status, 
                   u.id AS user_id, u.fname, u.lname, u.rollno, u.class 
            FROM attendance a 
            JOIN users u ON a.user_id = u.id 
            WHERE u.class = '$class_esc' 
            AND a.date = '$date_esc'
            ORDER BY CAST(u.rollno AS UNSIGNED) ASC, u.fname ASC
        ";

        $attendanceResult = mysqli_query($con, $attendanceQuery);
        $totalFound = ($attendanceResult) ? mysqli_num_rows($attendanceResult) : 0;
        $className = $classMapping[$selectedClass] ?? ("Class " . $selectedClass);
        $formattedDate = date('d F Y', strtotime($selectedDate));

        if ($totalFound > 0):
            $presentCount = 0;
            $absentCount = 0;
            $records = [];
            while ($row = mysqli_fetch_assoc($attendanceResult)) {
                $records[] = $row;
                if ((int)$row['status'] === 1) {
                    $presentCount++;
                } else {
                    $absentCount++;
                }
            }
            $presentPct = ($totalFound > 0) ? round(($presentCount / $totalFound) * 100, 1) : 0;
    ?>
            <!-- Stats Banner -->
            <div class="teacher-card p-3 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px; background: #ede9fe; font-size: 1.25rem;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="color: #0f1222;">
                                <?= htmlspecialchars($className); ?> &mdash; <span class="text-primary"><?= htmlspecialchars($formattedDate); ?></span>
                            </h5>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.82rem;">
                                    <i class="fas fa-users text-primary me-1"></i> Total: <strong><?= $totalFound; ?></strong>
                                </span>
                                <span class="badge badge-att-present" id="bannerPresentBadge">
                                    <i class="fas fa-check-circle"></i> Present: <strong id="bannerPresentCount"><?= $presentCount; ?></strong> (<?= $presentPct; ?>%)
                                </span>
                                <span class="badge badge-att-absent" id="bannerAbsentBadge">
                                    <i class="fas fa-times-circle"></i> Absent: <strong id="bannerAbsentCount"><?= $absentCount; ?></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="attendance-add.php?class=<?= urlencode($selectedClass); ?>&date=<?= urlencode($selectedDate); ?>" class="btn-outline-back text-decoration-none">
                            <i class="fas fa-pencil-alt me-1"></i> Edit Full Class Attendance
                        </a>
                    </div>
                </div>
            </div>

            <!-- Attendance Records Table Card -->
            <div class="users-table-card mb-4">
                <div class="table-responsive">
                    <table class="table mb-0 text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th class="users-table-header text-nowrap" style="width: 60px;">#</th>
                                <th class="users-table-header text-nowrap" style="width: 120px;">ROLL NO.</th>
                                <th class="users-table-header text-nowrap">STUDENT NAME</th>
                                <th class="users-table-header text-nowrap" style="width: 140px;">CLASS</th>
                                <th class="users-table-header text-nowrap" style="width: 140px;">DATE</th>
                                <th class="users-table-header text-nowrap" style="width: 140px;">STATUS</th>
                                <th class="users-table-header text-center text-nowrap" style="width: 170px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($records as $att):
                                $isPresent = ((int)$att['status'] === 1);
                                $roll = !empty($att['rollno']) ? $att['rollno'] : '-';
                                $fullName = trim($att['fname'] . ' ' . $att['lname']);
                                $attId = $att['attendance_id'];
                            ?>
                                <tr class="users-table-row" id="row_<?= $attId; ?>">
                                    <td class="fw-bold text-muted text-nowrap"><?= $i++; ?></td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-light text-secondary border px-2 py-1 fw-bold" style="border-radius: 6px; font-size: 0.83rem;">
                                            <?= htmlspecialchars($roll); ?>
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 34px; height: 34px; background: #ede9fe; font-size: 0.85rem; flex-shrink: 0;">
                                                <?= strtoupper(substr($att['fname'], 0, 1)); ?>
                                            </div>
                                            <span class="fw-bold text-dark" style="font-size: 0.93rem;"><?= htmlspecialchars($fullName); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge-class-pill text-nowrap"><?= htmlspecialchars($className); ?></span>
                                    </td>
                                    <td class="text-nowrap text-secondary font-monospace" style="font-size: 0.88rem;">
                                        <?= date('d M Y', strtotime($att['date'])); ?>
                                    </td>
                                    <td class="text-nowrap" id="statusCell_<?= $attId; ?>">
                                        <?php if ($isPresent): ?>
                                            <span class="badge-att-present">
                                                <i class="fas fa-check-circle"></i> Present
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-att-absent">
                                                <i class="fas fa-times-circle"></i> Absent
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Quick Status Toggle -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary text-nowrap" 
                                                    onclick="quickToggleAttendance(<?= $attId; ?>)" 
                                                    title="Quick Toggle (Present / Absent)" 
                                                    style="border-radius: 8px; font-size: 0.8rem; padding: 0.28rem 0.6rem;">
                                                <i class="fas fa-exchange-alt me-1"></i> Toggle
                                            </button>

                                            <!-- Edit Attendance Record -->
                                            <a href="attendance-edit.php?id=<?= $attId; ?>&class=<?= urlencode($selectedClass); ?>&date=<?= urlencode($selectedDate); ?>" 
                                               class="btn-action-edit" 
                                               title="Edit Student Attendance">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <!-- Delete Attendance Record -->
                                            <form action="code.php" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                                <input type="hidden" name="class" value="<?= htmlspecialchars($selectedClass); ?>">
                                                <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate); ?>">
                                                <button type="submit" 
                                                        name="attendance_delete_btn" 
                                                        value="<?= $attId; ?>" 
                                                        class="btn-action-delete" 
                                                        title="Delete Attendance">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- No Attendance Found Card -->
            <div class="teacher-card text-center py-5">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 58px; height: 58px; background: #fffbeb; color: #d97706; font-size: 1.5rem;">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No Attendance Records Found</h5>
                <p class="text-muted small mb-3">Attendance has not been recorded yet for <strong><?= htmlspecialchars($className); ?></strong> on <strong><?= htmlspecialchars($formattedDate); ?></strong>.</p>
                <div>
                    <a href="attendance-add.php?class=<?= urlencode($selectedClass); ?>&date=<?= urlencode($selectedDate); ?>" class="btn-primary-action text-decoration-none">
                        <i class="fas fa-plus me-2"></i> Mark Attendance for this Date
                    </a>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Initial Blank State -->
        <div class="teacher-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 58px; height: 58px; background: #ede9fe; color: var(--pca-primary); font-size: 1.5rem;">
                <i class="fas fa-filter"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Please Select Class and Date Above</h5>
            <p class="text-muted small mb-0">Choose a class and date to preview, audit, or update student attendance records.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function setFilterDate(dateStr) {
    document.getElementById('date').value = dateStr;
    if (document.getElementById('class').value !== '') {
        document.getElementById('filterForm').submit();
    }
}

// Quick AJAX toggle for student attendance status
function quickToggleAttendance(attId) {
    let cell = document.getElementById('statusCell_' + attId);
    if (!cell) return;

    let originalHtml = cell.innerHTML;
    cell.innerHTML = '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>';

    let formData = new FormData();
    formData.append('toggle_attendance_status', '1');
    formData.append('attendance_id', attId);

    fetch('code.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (parseInt(data.new_status) === 1) {
                cell.innerHTML = '<span class="badge-att-present"><i class="fas fa-check-circle"></i> Present</span>';
            } else {
                cell.innerHTML = '<span class="badge-att-absent"><i class="fas fa-times-circle"></i> Absent</span>';
            }
            // Recount live badges on table
            recalculateTableCounts();
        } else {
            alert(data.message || 'Failed to update attendance status.');
            cell.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        alert('Network error updating attendance.');
        cell.innerHTML = originalHtml;
    });
}

function recalculateTableCounts() {
    let presentCount = document.querySelectorAll('#attendance-view .badge-att-present, .table .badge-att-present').length;
    let absentCount = document.querySelectorAll('#attendance-view .badge-att-absent, .table .badge-att-absent').length;
    let total = presentCount + absentCount;

    let pBadge = document.getElementById('bannerPresentCount');
    let aBadge = document.getElementById('bannerAbsentCount');
    if (pBadge) pBadge.textContent = presentCount;
    if (aBadge) aBadge.textContent = absentCount;
}
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
