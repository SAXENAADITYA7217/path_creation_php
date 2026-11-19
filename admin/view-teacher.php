<?php
include('authentication.php'); 
include('includes/header.php'); 

// Class mapping array
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
    '13' => 'Class 11 Comm. (with Math)',
    '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)',
    '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)',
    '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

// Pagination Configuration
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Selected class filter
$selected_class = isset($_GET['selected_class']) ? trim($_GET['selected_class']) : '';

// Build WHERE Clause
if ($selected_class !== '') {
    $class_esc = mysqli_real_escape_string($con, $selected_class);
    $whereClause = "WHERE (class='$class_esc' OR user_id IN (SELECT user_id FROM class_teacher WHERE class='$class_esc') OR id IN (SELECT user_id FROM class_teacher WHERE class='$class_esc')) AND role_as='2'";
} else {
    $whereClause = "WHERE role_as='2'";
}

// Count Query for Pagination
$countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$countResult = mysqli_query($con, $countQuery);
$totalRecords = ($countResult && mysqli_num_rows($countResult) > 0) ? (int)mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int)ceil($totalRecords / $limit));

if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Data Query with Pagination
$query = "SELECT * FROM users $whereClause ORDER BY class ASC, id ASC LIMIT $offset, $limit";
$query_run = mysqli_query($con, $query);

function buildTeacherPageLink($p, $currentClass) {
    $params = ['page' => $p];
    if ($currentClass !== '') {
        $params['selected_class'] = $currentClass;
    }
    return 'view-teacher.php?' . http_build_query($params);
}
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Teachers</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Registered Teachers</h2>
                <p class="page-sub-heading mb-0">Manage all registered teachers and view their assigned classes.</p>
            </div>
        </div>
        <div>
            <a href="add-teacher.php" class="btn-primary-action text-decoration-none">
                <i class="fas fa-plus me-2"></i> Add Teacher
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Pastel Class Filter Card -->
    <div class="filter-card-pastel mb-4">
        <label class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">Select Class:</label>
        <form method="GET" action="" class="d-flex flex-wrap align-items-center gap-3">
            <div class="position-relative" style="width: 320px; max-width: 100%;">
                <i class="fas fa-graduation-cap position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                <select name="selected_class" id="classSelect" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 44px; border: 1px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;">
                    <option value="">-- Select Class --</option>
                    <?php
                    foreach ($classMapping as $classId => $className) {
                        $selected = ($selected_class === (string)$classId) ? 'selected' : '';
                        echo "<option value='{$classId}' {$selected}>" . htmlspecialchars($className) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-filter-custom">
                <i class="fas fa-filter me-2"></i> Filter
            </button>
            <?php if ($selected_class !== ''): ?>
                <a href="view-teacher.php" class="btn btn-outline-secondary d-inline-flex align-items-center" style="border-radius: 12px; height: 44px; padding: 0 1.2rem; font-size: 0.88rem;">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table Card matching theme -->
    <div class="users-table-card">
        <div class="table-responsive">
            <table class="table mb-0 text-nowrap align-middle">
                <thead>
                    <tr>
                        <th class="users-table-header text-nowrap" style="width: 60px;">#</th>
                        <th class="users-table-header text-nowrap">FIRST NAME</th>
                        <th class="users-table-header text-nowrap">EMAIL</th>
                        <th class="users-table-header text-nowrap">PHONE</th>
                        <th class="users-table-header text-nowrap">ASSIGNED CLASSES</th>
                        <th class="users-table-header text-center text-nowrap" style="width: 70px;">EDIT</th>
                        <th class="users-table-header text-center text-nowrap" style="width: 70px;">DELETE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sr_no = $offset + 1;

                    if ($query_run && mysqli_num_rows($query_run) > 0) {
                        foreach ($query_run as $row) {
                            $user_id = $row['user_id'];

                            $teacher_class = "SELECT class FROM class_teacher WHERE user_id = '$user_id'";
                            $run_teacher_class = mysqli_query($con, $teacher_class);

                            $classes = [];
                            if ($run_teacher_class) {
                                while ($row_class = mysqli_fetch_assoc($run_teacher_class)) {
                                    $classes[] = $row_class['class']; 
                                }
                            }

                            // Fallback if class_teacher had records under id
                            if (empty($classes) && !empty($row['id'])) {
                                $run_teacher_class_id = mysqli_query($con, "SELECT class FROM class_teacher WHERE user_id = '{$row['id']}'");
                                if ($run_teacher_class_id) {
                                    while ($row_class = mysqli_fetch_assoc($run_teacher_class_id)) {
                                        $classes[] = $row_class['class']; 
                                    }
                                }
                            }

                            // Fallback if only users.class was populated
                            if (empty($classes) && !empty($row['class'])) {
                                $classes[] = $row['class'];
                            }
                            ?>
                            <tr class="users-table-row">
                                <td class="fw-bold text-muted text-nowrap"><?= $sr_no++ ?></td>
                                <td class="fw-semibold text-dark text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: #ede9fe; color: var(--pca-primary); font-weight: 700; font-size: 0.8rem;">
                                            <?= strtoupper(substr($row['fname'] ?: 'T', 0, 1)); ?>
                                        </div>
                                        <span><?= htmlspecialchars($row['fname']); ?></span>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center text-dark text-nowrap">
                                        <i class="far fa-envelope text-primary me-2"></i>
                                        <span><?= htmlspecialchars($row['email']); ?></span>
                                    </div>
                                </td>
                                <td class="fw-semibold text-secondary text-nowrap">
                                    <i class="fas fa-phone-alt text-muted me-1" style="font-size: 0.78rem;"></i>
                                    <?= htmlspecialchars($row['phone_no'] ?: '-'); ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php if (!empty($classes)): ?>
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            <?php foreach ($classes as $c): ?>
                                                <?php $cName = isset($classMapping[$c]) ? $classMapping[$c] : $c; ?>
                                                <span class="badge-class-pill text-nowrap"><?= htmlspecialchars($cName); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">No class assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="teacher-edit.php?id=<?= $row['id']; ?>" class="btn-action-edit" title="Edit Teacher">
                                        <i class="far fa-edit"></i>
                                    </a>
                                </td>
                                <td class="text-center text-nowrap">
                                    <form action="code.php" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                        <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($row['user_id'] ?: $row['id']); ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id']); ?>">
                                        <button type="submit" name="delete_teacher" class="btn-action-delete" title="Delete Teacher">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-chalkboard-teacher fs-2 mb-3 d-block opacity-50"></i>
                                <h6 class="fw-bold">No Teachers Found</h6>
                                <p class="small mb-0">No teachers match your selected criteria.</p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Clean Pagination Footer -->
        <?php if ($totalRecords > 0): ?>
            <div class="pagination-container-clean">
                <div class="text-muted small">
                    Showing <span class="fw-bold text-dark"><?= min($offset + 1, $totalRecords); ?></span>
                    to <span class="fw-bold text-dark"><?= min($offset + $limit, $totalRecords); ?></span>
                    of <span class="fw-bold text-dark"><?= $totalRecords; ?></span> teachers
                </div>
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Teachers pagination">
                        <ul class="pagination pagination-clean mb-0">
                            <!-- Prev -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= buildTeacherPageLink($page - 1, $selected_class); ?>" aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= buildTeacherPageLink($i, $selected_class); ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= buildTeacherPageLink($page + 1, $selected_class); ?>" aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include('includes/footer.php'); 
include('includes/scripts.php'); 
?>
