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
    '13' => 'Class 11 Comm.',
    '14' => 'Class 11 Arts',
    '15' => 'Class 12 Sci (PCB)',
    '16' => 'Class 12 Sci (PCM)',
    '17' => 'Class 12 Comm.',
    '18' => 'Class 12 Arts'
];

// Pagination Configuration
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Selected class filter
$selected_class = isset($_GET['selected_class']) ? trim($_GET['selected_class']) : '';

// Count Query
$whereClause = "WHERE role_as = 0";
if ($selected_class !== '') {
    $class_esc = mysqli_real_escape_string($con, $selected_class);
    $whereClause .= " AND class = '$class_esc'";
}

$countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$countResult = mysqli_query($con, $countQuery);
$totalRecords = ($countResult && mysqli_num_rows($countResult) > 0) ? (int)mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int)ceil($totalRecords / $limit));

if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Data Query
$query = "SELECT * FROM users $whereClause ORDER BY class ASC, rollno ASC LIMIT $offset, $limit";
$query_run = mysqli_query($con, $query);

function buildPageLink($p, $currentClass) {
    $params = ['page' => $p];
    if ($currentClass !== '') {
        $params['selected_class'] = $currentClass;
    }
    return 'view-register.php?' . http_build_query($params);
}
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Users</li>
    </ol>

    <!-- Page Header matching screenshot -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-user-friends"></i>
            </div>
            <div>
                <h2 class="page-main-heading">Registered Users</h2>
                <p class="page-sub-heading">Manage all registered users on the platform.</p>
            </div>
        </div>
        <div>
            <a href="register-add.php" class="btn-add-admin">
                <i class="fas fa-plus me-2"></i> Add Admin
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Filter Card matching screenshot -->
    <div class="filter-card-pastel">
        <label class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">Select Class:</label>
        <form method="GET" action="" class="d-flex flex-wrap align-items-center gap-3">
            <div class="position-relative" style="width: 300px; max-width: 100%;">
                <i class="fas fa-graduation-cap position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                <select name="selected_class" id="classSelect" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 44px; border: 1px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;">
                    <option value="">-- Select Class --</option>
                    <?php
                    foreach ($classMapping as $classId => $className) {
                        $selected = ($selected_class === (string)$classId) ? 'selected' : '';
                        echo "<option value='{$classId}' {$selected}>{$className}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-filter-custom">
                <i class="fas fa-filter me-2"></i> Filter
            </button>
            <?php if ($selected_class !== ''): ?>
                <a href="view-register.php" class="btn btn-outline-secondary d-inline-flex align-items-center" style="border-radius: 12px; height: 44px; padding: 0 1.2rem; font-size: 0.88rem;">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table Card matching screenshot -->
    <div class="users-table-card">
        <div class="table-responsive">
            <table class="table mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th class="users-table-header text-nowrap" style="width: 60px;">ID</th>
                        <th class="users-table-header text-nowrap">CLASS</th>
                        <th class="users-table-header text-nowrap">ROLL NO.</th>
                        <th class="users-table-header text-nowrap">FIRST NAME</th>
                        <th class="users-table-header text-nowrap">FATHER NAME</th>
                        <th class="users-table-header text-nowrap">EMAIL</th>
                        <th class="users-table-header text-nowrap" style="width: 120px;">ROLE</th>
                        <th class="users-table-header text-center text-nowrap" style="width: 70px;">EDIT</th>
                        <th class="users-table-header text-center text-nowrap" style="width: 70px;">DELETE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sr_no = $offset + 1;

                    if ($query_run && mysqli_num_rows($query_run) > 0) {
                        foreach ($query_run as $row) {
                            $displayClass = isset($classMapping[$row['class']]) ? $classMapping[$row['class']] : ($row['class'] !== '' ? $row['class'] : '0');
                            $fatherName = !empty($row['father_name']) ? $row['father_name'] : (!empty($row['fathername']) ? $row['fathername'] : '');
                            if (empty($fatherName)) {
                                try {
                                    $checkId = !empty($row['id']) ? $row['id'] : (!empty($row['user_id']) ? $row['user_id'] : '');
                                    if (!empty($checkId)) {
                                        $uIdEsc = mysqli_real_escape_string($con, $checkId);
                                        $stRes = mysqli_query($con, "SELECT father_name FROM student WHERE user_id = '$uIdEsc' LIMIT 1");
                                        if ($stRes && ($stRow = mysqli_fetch_assoc($stRes)) && !empty($stRow['father_name'])) {
                                            $fatherName = $stRow['father_name'];
                                        }
                                    }
                                } catch (\Throwable $e) {}
                            }
                            if (empty($fatherName)) {
                                $fatherName = '-';
                            }
                            ?>
                            <tr class="users-table-row">
                                <td class="fw-bold text-muted text-nowrap"><?= $sr_no++ ?></td>
                                <td class="text-nowrap">
                                    <span class="badge-class-pill text-nowrap"><?= htmlspecialchars($displayClass); ?></span>
                                </td>
                                <td class="fw-semibold text-secondary text-nowrap">
                                    <?= htmlspecialchars($row['rollno'] ?: '-'); ?>
                                </td>
                                <td class="fw-semibold text-dark text-nowrap">
                                    <?= htmlspecialchars($row['fname']); ?>
                                </td>
                                <td class="text-secondary text-nowrap">
                                    <?= htmlspecialchars($fatherName); ?>
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center text-dark text-nowrap">
                                        <i class="far fa-envelope text-primary me-2"></i>
                                        <span><?= htmlspecialchars($row['email']); ?></span>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <?php if ($row['role_as'] == '1'): ?>
                                        <span class="badge-role-admin text-nowrap">
                                            <i class="fas fa-shield-alt me-1"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-role-user text-nowrap">
                                            <i class="far fa-user me-1"></i> User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="register-edit.php?id=<?= $row['id']; ?>" class="btn-action-edit" title="Edit User">
                                        <i class="far fa-edit"></i>
                                    </a>
                                </td>
                                <td class="text-center text-nowrap">
                                    <form action="code.php" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <button type="submit" name="user_delete" value="<?= $row['id']; ?>" class="btn-action-delete" title="Delete User">
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
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fs-2 mb-3 d-block opacity-50"></i>
                                <h6 class="fw-bold">No Records Found</h6>
                                <p class="small mb-0">No users match your selected criteria.</p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($totalRecords > 0): ?>
            <div class="pagination-container-clean">
                <div class="text-muted small">
                    Showing <span class="fw-bold text-dark"><?= $offset + 1 ?></span> to <span class="fw-bold text-dark"><?= min($offset + $limit, $totalRecords) ?></span> of <span class="fw-bold text-dark"><?= $totalRecords ?></span> registered users
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav aria-label="User navigation">
                        <ul class="pagination pagination-clean mb-0">
                            <!-- Previous Page -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= ($page > 1) ? buildPageLink($page - 1, $selected_class) : '#' ?>" aria-label="Previous">
                                    &laquo; Previous
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . buildPageLink(1, $selected_class) . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link border-0">...</span></li>';
                                }
                            }

                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = ($i === $page) ? 'active' : '';
                                echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . buildPageLink($i, $selected_class) . '">' . $i . '</a></li>';
                            }

                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link border-0">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . buildPageLink($totalPages, $selected_class) . '">' . $totalPages . '</a></li>';
                            }
                            ?>

                            <!-- Next Page -->
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= ($page < $totalPages) ? buildPageLink($page + 1, $selected_class) : '#' ?>" aria-label="Next">
                                    Next &raquo;
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
