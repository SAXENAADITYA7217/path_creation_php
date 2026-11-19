<?php
include('authentication.php');

$class = mysqli_real_escape_string($con, trim($_GET['class'] ?? ''));
$date = mysqli_real_escape_string($con, trim($_GET['date'] ?? date('Y-m-d')));

if (empty($class)) {
    echo '<div class="teacher-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px; background: #ede9fe; color: var(--pca-primary); font-size: 1.35rem;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Select a Class Above</h5>
            <p class="text-muted small mb-0">Choose a class from the dropdown above to load the student roster.</p>
          </div>';
    exit();
}

$studentsQuery = "
    SELECT u.id, u.rollno, u.fname, u.lname, u.class,
           a.id AS attendance_id, a.status AS attendance_status
    FROM users u
    LEFT JOIN attendance a ON a.user_id = u.id AND a.date = '$date'
    WHERE u.class = '$class' AND u.role_as = 0
    ORDER BY CAST(u.rollno AS UNSIGNED) ASC, u.fname ASC
";
$res = mysqli_query($con, $studentsQuery);
$total = ($res) ? mysqli_num_rows($res) : 0;

if ($total === 0) {
    echo '<div class="teacher-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px; background: #fef2f2; color: #ef4444; font-size: 1.35rem;">
                <i class="fas fa-user-slash"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">No Students Found</h5>
            <p class="text-muted small mb-0">There are no registered students in this class yet.</p>
          </div>';
    exit();
}

$students = [];
$alreadyRecorded = false;
$recordedPresent = 0;
$recordedAbsent = 0;

while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
    if ($row['attendance_id'] !== null) {
        $alreadyRecorded = true;
        if ((int)$row['attendance_status'] === 1) {
            $recordedPresent++;
        } else {
            $recordedAbsent++;
        }
    }
}
?>

<!-- Students Table Card -->
<div class="users-table-card mb-4">
    <!-- Top Action & Counter Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 border-bottom" style="background: #ffffff;">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold" style="border-radius: 8px; font-size: 0.85rem;">
                <i class="fas fa-users text-primary me-1"></i> Total: <strong><?= $total; ?></strong> Students
            </span>
            <span class="badge badge-att-present" id="livePresentBadge">
                <i class="fas fa-check-circle"></i> Present: <strong id="livePresentCount">0</strong>
            </span>
            <span class="badge badge-att-absent" id="liveAbsentBadge">
                <i class="fas fa-times-circle"></i> Absent: <strong id="liveAbsentCount">0</strong>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAllStatus(1)" style="border-radius: 8px; font-weight: 700;">
                <i class="fas fa-check-double me-1"></i> Mark All Present
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAllStatus(0)" style="border-radius: 8px; font-weight: 700;">
                <i class="fas fa-user-times me-1"></i> Mark All Absent
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table mb-0 text-nowrap align-middle">
            <thead>
                <tr>
                    <th class="users-table-header text-nowrap" style="width: 60px;">#</th>
                    <th class="users-table-header text-nowrap" style="width: 130px;">ROLL NO.</th>
                    <th class="users-table-header text-nowrap">STUDENT NAME</th>
                    <th class="users-table-header text-center text-nowrap" style="width: 220px;">MARK STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sr = 1;
                foreach ($students as $st):
                    // If attendance exists for date, use recorded status; otherwise default to 1 (Present)
                    $status = ($st['attendance_id'] !== null) ? (int)$st['attendance_status'] : 1;
                    $uid = $st['id'];
                    $roll = !empty($st['rollno']) ? $st['rollno'] : '-';
                    $fullName = trim($st['fname'] . ' ' . $st['lname']);
                ?>
                    <tr class="users-table-row">
                        <td class="fw-bold text-muted text-nowrap"><?= $sr++; ?></td>
                        <td class="text-nowrap">
                            <span class="badge bg-light text-secondary border px-2 py-1 fw-bold" style="border-radius: 6px; font-size: 0.83rem;">
                                <?= htmlspecialchars($roll); ?>
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 34px; height: 34px; background: #ede9fe; font-size: 0.85rem; flex-shrink: 0;">
                                    <?= strtoupper(substr($st['fname'], 0, 1)); ?>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark" style="font-size: 0.93rem;"><?= htmlspecialchars($fullName); ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="att-toggle-group">
                                <input type="radio" 
                                       class="att-radio" 
                                       name="attendance_status[<?= $uid; ?>]" 
                                       id="p_<?= $uid; ?>" 
                                       value="1" 
                                       <?= ($status === 1) ? 'checked' : ''; ?> 
                                       onchange="updateLiveAttendanceCounts()">
                                <label class="att-toggle-label att-label-present" for="p_<?= $uid; ?>">
                                    <i class="fas fa-check"></i> Present
                                </label>

                                <input type="radio" 
                                       class="att-radio" 
                                       name="attendance_status[<?= $uid; ?>]" 
                                       id="a_<?= $uid; ?>" 
                                       value="0" 
                                       <?= ($status === 0) ? 'checked' : ''; ?> 
                                       onchange="updateLiveAttendanceCounts()">
                                <label class="att-toggle-label att-label-absent" for="a_<?= $uid; ?>">
                                    <i class="fas fa-times"></i> Absent
                                </label>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bottom Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
    <div class="d-flex align-items-center gap-2">
        <button type="submit" name="add_multiple_attendance" class="btn-primary-action px-4">
            <i class="far fa-save me-2"></i> Save Attendance
        </button>
        <a href="attendance-view.php?class=<?= urlencode($class); ?>&date=<?= urlencode($date); ?>" class="btn-outline-back text-decoration-none">
            <i class="fas fa-times me-2"></i> Cancel
        </a>
    </div>
    <div>
        <span class="text-muted small">
            <i class="fas fa-shield-alt text-primary me-1"></i> Duplicate records for the same student on this date are automatically merged & updated.
        </span>
    </div>
</div>
