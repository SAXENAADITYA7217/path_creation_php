<?php
include('authentication.php');

$class = $_GET['class'] ?? '';
if($class == '') exit;

$class_esc = mysqli_real_escape_string($con, $class);

// JSON fetch for duplicate check
if(isset($_GET['fetchNames'])){
    $subjects = [];
    $resNames = mysqli_query($con, "SELECT LOWER(subject) as subject FROM class_subject_info WHERE class_code='$class_esc'");
    if($resNames) {
        while($row = mysqli_fetch_assoc($resNames)){
            $subjects[] = $row['subject'];
        }
    }
    echo json_encode($subjects);
    exit;
}

// Regular fetch for view-subject.php
$res = mysqli_query($con, "SELECT * FROM class_subject_info WHERE class_code='$class_esc' ORDER BY id ASC");

$classMapping = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4', '5' => 'Class 5',
    '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8', '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (With math)', '14' => 'Class 11 Comm. (Without math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (With math)', '19' => 'Class 12 Comm. (Without math)',
    '20' => 'Class 12 Arts'
];

$className = $classMapping[$class] ?? ("Class " . $class);
$total = ($res) ? mysqli_num_rows($res) : 0;

if ($total > 0) {
    echo '<div class="teacher-card p-0 overflow-hidden mb-4">';
    echo '  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 border-bottom" style="background: #ffffff;">';
    echo '      <div class="d-flex align-items-center gap-3">';
    echo '          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background: #ede9fe; color: var(--pca-primary); font-size: 1.2rem; flex-shrink: 0;">';
    echo '              <i class="fas fa-layer-group"></i>';
    echo '          </div>';
    echo '          <div>';
    echo '              <h5 class="mb-0 fw-bold" style="color: #0f1222; font-size: 1.15rem;">Class: <span style="color: var(--pca-primary);">' . htmlspecialchars($className) . '</span></h5>';
    echo '              <p class="text-muted small mb-0 mt-1">Total ' . $total . ' subjects configured</p>';
    echo '          </div>';
    echo '      </div>';
    echo '      <div>';
    echo '          <a href="add-subject.php?class=' . urlencode($class) . '" class="btn-outline-back" style="padding: 0.55rem 1.3rem; font-size: 0.88rem;">';
    echo '              <i class="fas fa-plus me-1"></i> Add More Subjects';
    echo '          </a>';
    echo '      </div>';
    echo '  </div>';

    echo '  <div class="table-responsive">';
    echo '      <table class="table mb-0 text-nowrap align-middle">';
    echo '          <thead>';
    echo '              <tr>';
    echo '                  <th class="users-table-header text-nowrap" style="width: 60px;">#</th>';
    echo '                  <th class="users-table-header text-nowrap">SUBJECT NAME</th>';
    echo '                  <th class="users-table-header text-nowrap" style="width: 140px;">STATUS</th>';
    echo '                  <th class="users-table-header text-center text-nowrap" style="width: 180px;">ACTION</th>';
    echo '              </tr>';
    echo '          </thead>';
    echo '          <tbody>';

    $i = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $isActive = ($row['status'] == 1);
        $statusBadge = $isActive 
            ? '<span class="badge-role-user text-nowrap" style="background: #ecfdf5; color: #059669;"><i class="fas fa-check-circle me-1"></i> Active</span>'
            : '<span class="badge-role-user text-nowrap" style="background: #f1f5f9; color: #64748b;"><i class="fas fa-minus-circle me-1"></i> Inactive</span>';

        echo '<tr class="users-table-row">';
        echo '  <td class="fw-bold text-muted text-nowrap">' . $i . '</td>';
        echo '  <td class="text-nowrap">';
        echo '      <div class="d-flex align-items-center">';
        echo '          <div class="subject-icon-box me-3"><i class="fas fa-book-open"></i></div>';
        echo '          <span class="fw-bold text-dark" style="font-size: 0.95rem;">' . htmlspecialchars($row['subject']) . '</span>';
        echo '      </div>';
        echo '  </td>';
        echo '  <td class="text-nowrap">' . $statusBadge . '</td>';
        echo '  <td class="text-center text-nowrap">';
        echo '      <div class="d-inline-flex align-items-center gap-2">';
        echo '          <a href="edit-subject.php?id=' . $row['id'] . '" class="btn-action-pill-edit text-nowrap" title="Edit Subject">';
        echo '              <i class="fas fa-pencil-alt"></i> Edit';
        echo '          </a>';
        echo '          <form method="POST" action="code.php" class="d-inline m-0" onsubmit="return confirm(\'Are you sure you want to delete this subject?\');">';
        echo '              <input type="hidden" name="subject_id" value="' . $row['id'] . '">';
        echo '              <input type="hidden" name="class_code" value="' . htmlspecialchars($class) . '">';
        echo '              <button type="submit" name="delete_subject" class="btn-action-pill-delete text-nowrap" title="Delete Subject">';
        echo '                  <i class="far fa-trash-alt"></i> Delete';
        echo '              </button>';
        echo '          </form>';
        echo '      </div>';
        echo '  </td>';
        echo '</tr>';
        $i++;
    }

    echo '          </tbody>';
    echo '      </table>';
    echo '  </div>';
    echo '</div>';
} else {
    echo '<div class="teacher-card text-center py-5">';
    echo '  <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #ede9fe; color: var(--pca-primary); font-size: 1.5rem;">';
    echo '      <i class="fas fa-book-open"></i>';
    echo '  </div>';
    echo '  <h5 class="fw-bold text-dark mb-1">No Subjects Configured</h5>';
    echo '  <p class="text-muted small mb-3">No subjects have been registered for <strong>' . htmlspecialchars($className) . '</strong> yet.</p>';
    echo '  <a href="add-subject.php?class=' . urlencode($class) . '" class="btn-primary-action text-decoration-none">';
    echo '      <i class="fas fa-plus me-1"></i> Add Subjects to this Class';
    echo '  </a>';
    echo '</div>';
}
?>
