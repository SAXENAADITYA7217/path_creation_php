<?php
date_default_timezone_set('Asia/Kolkata');
include('authentication.php');
include('includes/header.php');

// Class Mapping
$classMapping = [
    '100' => 'Nursery',
    '101' => 'LKG',
    '102' => 'UKG',
    '1' => 'Class I',
    '2' => 'Class II',
    '3' => 'Class III',
    '4' => 'Class IV',
    '5' => 'Class V',
    '6' => 'Class VI',
    '7' => 'Class VII',
    '8' => 'Class VIII',
    '9' => 'Class IX',
    '10' => 'Class X',
    '11' => 'Class XI Sci (PCB)',
    '12' => 'Class XI Sci (PCM)',
    '13' => 'Class XI Comm. (with Math)',
    '14' => 'Class XI Comm. (without Math)',
    '15' => 'Class XI Arts',
    '16' => 'Class XII Sci (PCB)',
    '17' => 'Class XII Sci (PCM)',
    '18' => 'Class XII Comm. (with Math)',
    '19' => 'Class XII Comm. (without Math)',
    '20' => 'Class XII Arts'
];

$user_id = $_SESSION['auth_user']['user_id'] ?? '';
$user_code = $_SESSION['auth_user']['id'] ?? '';

// 1. Fetch assigned classes for this teacher
$classes = [];
$q_classes = mysqli_query($con, "SELECT DISTINCT class FROM class_teacher WHERE (user_id = '$user_code' OR user_id = '$user_id') AND (class_status = 0 OR class_status IS NULL)");
if ($q_classes) {
    while($row = mysqli_fetch_assoc($q_classes)) {
        if(!empty($row['class']) && !in_array((string)$row['class'], $classes)){
            $classes[] = (string)$row['class'];
        }
    }
}
if(empty($classes) && !empty($user_row['class'])){
    $classes[] = (string)$user_row['class'];
}

// 2. Class selection logic
if (isset($_GET['class']) && in_array((string)$_GET['class'], $classes)) {
    $selected_class = (string)$_GET['class'];
    $_SESSION['teacher_selected_class'] = $selected_class;
} elseif (isset($_SESSION['teacher_selected_class']) && in_array((string)$_SESSION['teacher_selected_class'], $classes)) {
    $selected_class = (string)$_SESSION['teacher_selected_class'];
} elseif (!empty($classes)) {
    $selected_class = (string)$classes[0];
    $_SESSION['teacher_selected_class'] = $selected_class;
} else {
    $selected_class = '';
}
?>

<!-- FontAwesome & Plus Jakarta Sans Font for exact theme match -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Prevent horizontal scroll */
html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
}

.page-wrapper, .page-content {
    overflow-x: hidden !important;
    max-width: 100% !important;
}

:root {
    --pca-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --pca-bg: #f8fafc;
    --pca-card-bg: #ffffff;
    --pca-text-main: #0f172a;
    --pca-text-muted: #64748b;
    --pca-border: #edf0f5;
    --pca-primary: #5e48eb;
    --pca-primary-hover: #4d38d6;
}

.attendance-page-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0.5rem 0.5rem 2rem 0.5rem;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box;
}

/* Monolithic White Card */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    width: 100% !important;
}

.pca-card-header {
    padding: 1.1rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 0.8rem;
}

.pca-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pca-card-body {
    padding: 1.5rem;
}

.form-label-clean {
    font-size: 0.8rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 0.4rem;
    display: block;
}

.form-control-clean {
    border-radius: 10px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.6rem 0.9rem !important;
    font-size: 0.9rem !important;
    color: #1e293b !important;
    background: #f8fafc !important;
    height: 44px;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

/* Holiday checkbox card */
.holiday-check-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.holiday-check-box input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #5e48eb;
}

.holiday-check-box label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    margin-bottom: 0;
}

/* Primary buttons */
.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.6rem 1.6rem;
    border: none;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}

.btn-view-link {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.86rem;
    padding: 0.5rem 1rem;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-view-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

/* Dynamic Table loaded by AJAX */
#usersList {
    width: 100%;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}

#usersList table {
    width: 100% !important;
    min-width: 580px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    overflow: hidden;
}

@media (max-width: 768px) {
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .pca-card-header .btn-primary-action,
    .pca-card-header .btn-view-link {
        width: 100% !important;
        justify-content: center !important;
    }
    .report-page-wrap {
        padding: 0.25rem 0.25rem 1.5rem 0.25rem !important;
    }
    .pca-card-body {
        padding: 1rem 0.85rem !important;
    }
}

#usersList table thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.9rem 1.1rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
}

#usersList table tbody td {
    padding: 0.85rem 1.1rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f1f5f9 !important;
    font-size: 0.88rem;
    color: #1e293b;
    background: #ffffff;
}

#usersList table tbody tr:hover td {
    background-color: #fbfbfe !important;
}

#usersList table tbody tr:last-child td {
    border-bottom: none !important;
}

#usersList select.form-select {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 0.4rem 0.8rem;
    font-size: 0.86rem;
    font-weight: 600;
    max-width: 160px;
}

#usersList .alert {
    border-radius: 12px;
    border: none;
    padding: 1.2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}
</style>

<div class="attendance-page-wrap">

    <!-- Flash message -->
    <?php include('message.php'); ?>

    <!-- Monolithic Card -->
    <div class="pca-white-card">
        
        <!-- Header -->
        <div class="pca-card-header">
            <h4 class="pca-card-title">
                <i class="fa-solid fa-clipboard-user text-primary"></i> Mark Attendance
            </h4>
            <a href="attendance-view.php<?= !empty($selected_class) ? '?class=' . urlencode($selected_class) : '' ?>" class="btn-view-link" id="viewAttendanceBtn">
                <i class="fa-regular fa-calendar-check me-2 text-primary"></i> View Attendance
            </a>
        </div>

        <!-- Body -->
        <div class="pca-card-body">
            
            <!-- Attendance Form -->
            <form action="teacher_code.php" method="POST" id="attendanceForm">
                
                <!-- Class & Date -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label-clean">Class</label>
                        <select name="class" class="form-select form-control-clean" id="classSelect" required>
                            <option value="">-- Select Class --</option>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= ($selected_class == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label-clean">Date</label>
                        <input type="date" name="date" id="attendanceDate" class="form-control form-control-clean" 
                               value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <!-- Holiday Option -->
                <div class="mb-3">
                    <div class="holiday-check-box">
                        <input type="checkbox" id="markHoliday" name="markHoliday" value="1">
                        <label for="markHoliday">
                            <i class="fa-regular fa-sun text-warning me-1"></i> Mark this day as Holiday
                        </label>
                    </div>
                </div>

                <!-- Students list or Holiday message (populated via AJAX) -->
                <div id="usersList" class="mb-4"></div>

                <!-- Save Button Wrapper -->
                <div class="pt-3 border-top" id="saveBtnWrapper">
                    <button type="submit" name="add_multiple_attendance" class="btn-primary-action">
                        <i class="fa-solid fa-check me-2"></i> Save Attendance
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<script>
function loadAttendanceList(date){
    var selectedClass = document.querySelector('select[name="class"]').value;
    var isHoliday = document.getElementById("markHoliday").checked;
    var day = new Date(date).getDay(); // 0 = Sunday

    // If no class selected
    if(!selectedClass){
        document.getElementById('usersList').innerHTML = 
            "<div class='alert alert-warning text-center' style='background: #fffbeb; color: #b45309;'><h6 class='mb-0'><i class='fa-solid fa-circle-exclamation me-2'></i>Please select a class first.</h6></div>";
        document.getElementById('saveBtnWrapper').style.display = "none";
        return;
    }

    // Handle Sunday or Holiday
    if(day === 0 || isHoliday){
        document.getElementById('usersList').innerHTML = 
            "<div class='alert alert-info text-center' style='background: #f0f9ff; color: #0369a1;'><h5 class='mb-0'><i class='fa-solid fa-umbrella-beach me-2'></i>Marked as Holiday</h5></div>";
        document.getElementById('saveBtnWrapper').style.display = "block"; 
    } else {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'teacher_code.php?selectedClass=' + encodeURIComponent(selectedClass) + '&date=' + encodeURIComponent(date), true);
        xhr.onreadystatechange = function() {
            if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200){
                document.getElementById('usersList').innerHTML = xhr.responseText;
                document.getElementById('saveBtnWrapper').style.display = "block"; 
            }
        };
        xhr.send();
    }
}

// Load initial data when page opens
window.onload = function() {
    var dateEl = document.querySelector("input[name='date']");
    if(dateEl) {
        loadAttendanceList(dateEl.value);
    }
};

// When date changes
document.getElementById("attendanceDate").addEventListener("change", function(){
    loadAttendanceList(this.value);
});

// When holiday checkbox changes
document.getElementById("markHoliday").addEventListener("change", function(){
    var date = document.querySelector("input[name='date']").value;
    loadAttendanceList(date);
});

// When class changes
document.getElementById("classSelect").addEventListener("change", function(){
    var date = document.querySelector("input[name='date']").value;
    loadAttendanceList(date);
    var viewBtn = document.getElementById("viewAttendanceBtn");
    if(viewBtn) {
        viewBtn.href = "attendance-view.php" + (this.value ? "?class=" + encodeURIComponent(this.value) : "");
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
