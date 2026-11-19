<?php
// COMPLETE PAGE: Student report with horizontal Chart.js (lowest at bottom),
// subjects sorted low->high, and top-3 lowest suggestions in English.
// Assumes studentcode.php sets $con and session management
include('studentcode.php');
include('includes/header.php');

// --- SESSION / DB checks ---
if (!isset($_SESSION['auth_user']['user_id'])) {
    echo "<div class='alert alert-danger'>Authentication error. User session not found.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}
$user_id = $_SESSION['auth_user']['user_id'];

if (!isset($con) || !$con) {
    echo "<div class='alert alert-danger'>Database connection failed.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}

// Fetch user's class
$user_query = "SELECT class FROM users WHERE id=? LIMIT 1";
$stmt = mysqli_prepare($con, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if ($res && mysqli_num_rows($res) == 1) {
    $user_row = mysqli_fetch_assoc($res);
    $class = $user_row['class'];
} else {
    echo "<div class='alert alert-danger'>User details not found or invalid ID.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}

// --- Exam types ---
$all_exam_types = [
    1 => 'Unit Test 1',
    2 => 'Unit Test 2',
    5 => 'Half Year Exam',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4',
    6 => 'Final Exam',
];
?>

<style>
:root{
  --primary-classic:#6a0572;
  --accent-light:#f7e7f9;
  --accent-dark:#3b0042;
  --progress-color:#9c27b0;
  --bg-main:#fcfcfc;
}
.elegant-report-box{
  background:var(--bg-main);
  border-radius:18px;
  padding:26px;
  box-shadow:0 10px 30px rgba(0,0,0,0.08);
  border:2px solid var(--accent-light);
}
.report-title{font-family:Georgia,serif;color:var(--primary-classic);margin-bottom:18px;border-bottom:3px solid var(--accent-light);padding-bottom:10px;}
.exam-selection-form{background:var(--accent-light);padding:16px;border-radius:12px;margin-bottom:20px;}
#marksChart{width:100% !important; display:block;}
.list-group-item strong{color:var(--primary-classic);}
.suggestion .subject-name{font-weight:700;color:var(--primary-classic);}
</style>

<div class="container-fluid pt-4 px-4">
  <div class="elegant-report-box">
    <h4 class="report-title">📘 Student Report</h4>

    <form method="POST" class="row exam-selection-form align-items-end">
      <div class="col-md-4 col-sm-12">
        <label class="form-label fw-bold" style="color:var(--accent-dark)">Select Exam Type</label>
        <select name="exam_type" class="form-select" required>
          <option value="">-- Select Exam Type --</option>
          <?php foreach($all_exam_types as $k=>$v){
              $sel = (isset($_POST['exam_type']) && intval($_POST['exam_type'])==intval($k)) ? 'selected' : '';
              echo "<option value='".intval($k)."' $sel>".htmlspecialchars($v)."</option>";
          } ?>
        </select>
      </div>
      <div class="col-md-2 col-sm-12">
        <button type="submit" name="view_result" class="btn btn-primary w-100">View Results</button>
      </div>
    </form>

<?php
if (isset($_POST['view_result'])) {
    if (empty($_POST['exam_type'])) {
        echo "<div class='alert alert-warning'>Please select an exam type to view results.</div>";
    } else {
        $selected_exam = intval($_POST['exam_type']);
        $exam_name = $all_exam_types[$selected_exam] ?? "Unknown Exam";

        echo "<h5 class='mb-3'>Showing Results for <span style='color:var(--progress-color)'>".htmlspecialchars($exam_name)."</span></h5>";

        // fetch subjects
        $subject_query = "SELECT subject FROM class_subject_info WHERE class_code=? AND status=1 ORDER BY subject ASC";
        $sstmt = mysqli_prepare($con, $subject_query);
        mysqli_stmt_bind_param($sstmt, "s", $class);
        mysqli_stmt_execute($sstmt);
        $subject_result = mysqli_stmt_get_result($sstmt);
        mysqli_stmt_close($sstmt);

        if (!$subject_result || mysqli_num_rows($subject_result) == 0) {
            echo "<div class='alert alert-warning'>No subjects configured for your class.</div>";
        } else {
            $marks_data = [];

            // prepare marks query (user_id i, class s, subject s, test_code i)
            $marks_query = "SELECT marks_obtained, total_marks, practical_marks, total_practical FROM student_score WHERE student_id=? AND class_code=? AND subject=? AND test_code=? LIMIT 1";
            $mstmt = mysqli_prepare($con, $marks_query);

            while ($srow = mysqli_fetch_assoc($subject_result)) {
                $subject = $srow['subject'];

                mysqli_stmt_bind_param($mstmt, "issi", $user_id, $class, $subject, $selected_exam);
                mysqli_stmt_execute($mstmt);
                $mres = mysqli_stmt_get_result($mstmt);

                if ($mres && mysqli_num_rows($mres) > 0) {
                    $r = mysqli_fetch_assoc($mres);

                    $show_practical = in_array($selected_exam, [5,6]);

                    $theory_obtained = floatval($r['marks_obtained']);
                    $theory_total = floatval($r['total_marks']);
                    $practical_obtained = floatval($r['practical_marks']);
                    $practical_total = floatval($r['total_practical']);

                    $total_obt = $theory_obtained + ($show_practical ? $practical_obtained : 0);
                    $total_marks = $theory_total + ($show_practical ? $practical_total : 0);

                    $percent = ($total_marks>0) ? round(($total_obt/$total_marks)*100, 2) : 0.0;

                    $marks_data[] = [
                        'subject' => $subject,
                        'obtained' => $total_obt,
                        'total' => $total_marks,
                        'percent' => $percent
                    ];
                }
            } // end while
            mysqli_stmt_close($mstmt);

            if (empty($marks_data)) {
                echo "<div class='alert alert-info'>Results for $exam_name are not yet available.</div>";
            } else {
                // Sort ascending (lowest -> highest)
                usort($marks_data, function($a,$b){ return $a['percent'] <=> $b['percent']; });

                // For chart we want labels array and percentages in same order.
                // We'll keep ascending order so lowest is first, then we reverse axis display so lowest shows at bottom.
                $labels = array_column($marks_data, 'subject');
                $percentages = array_column($marks_data, 'percent');

                $labels_json = json_encode($labels, JSON_UNESCAPED_UNICODE);
                $percentages_json = json_encode($percentages);

                // --- Chart (horizontal, ensure lowest at bottom using scale.reverse = true) ---
                ?>
                <div class="chart-container mb-4">
                    <canvas id="marksChart"></canvas>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                (function(){
                    const labelsFull = <?php echo $labels_json; ?> || [];
                    const dataPoints = <?php echo $percentages_json; ?> || [];

                    const canvas = document.getElementById('marksChart');

                    // dynamic height: ~52px per subject
                    canvas.height = Math.max(240, labelsFull.length * 52);

                    // displayed labels truncated for axis if too long
                    const displayedLabels = labelsFull.map(l => {
                        if (typeof l !== 'string') return String(l);
                        return (l.length > 22) ? (l.substring(0,20) + '…') : l;
                    });

                    if (window._marksChart instanceof Chart) window._marksChart.destroy();

                    window._marksChart = new Chart(canvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: displayedLabels,
                            datasets: [{
                                label: 'Percentage (%)',
                                data: dataPoints,
                                borderWidth: 1,
                                backgroundColor: dataPoints.map(v => {
                                    // gradient simple: low -> red-ish, mid -> orange, high -> purple
                                    if (v < 40) return 'rgba(220,53,69,0.85)'; // danger
                                    if (v < 60) return 'rgba(255,159,67,0.85)'; // warning
                                    return 'rgba(156,39,176,0.85)'; // good
                                })
                            }]
                        },
                        options: {
                            indexAxis: 'y', // horizontal bars
                            responsive: true,
                            maintainAspectRatio: false,
                            elements: {
                                bar: {
                                    barThickness: 18,
                                    maxBarThickness: 48,
                                    minBarLength: 3
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(val) { return val + '%'; }
                                    },
                                    title: { display: true, text: 'Percentage' }
                                },
                                y: {
                                    // reverse the vertical order so first element (lowest) appears at bottom
                                    reverse: true,
                                    ticks: {
                                        autoSkip: false
                                    },
                                    title: { display: true, text: 'Subjects' }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: function(items) {
                                            const idx = items[0].dataIndex;
                                            return labelsFull[idx] || displayedLabels[idx];
                                        },
                                        label: function(context) {
                                            const parsed = context.parsed;
                                            let val = parsed.x !== undefined ? parsed.x : parsed;
                                            return ' ' + val + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })();
                </script>

                <!-- SUBJECT LIST (lowest -> highest) -->
                <h4 class="mt-3">📘 Subjects (Lowest → Highest)</h4>
                <ul class="list-group mt-2 mb-3">
                    <?php
                    foreach ($marks_data as $row) {
                        $obt = rtrim(rtrim(number_format($row['obtained'],2,'.',''), '0'), '.');
                        $tot = rtrim(rtrim(number_format($row['total'],2,'.',''), '0'), '.');
                        $pct = rtrim(rtrim(number_format($row['percent'],2,'.',''), '0'), '.');
                        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                <div><strong>".htmlspecialchars($row['subject'])."</strong></div>
                                <div><span class='me-3'>{$obt} / {$tot}</span><span class='badge bg-secondary'>{$pct}%</span></div>
                              </li>";
                    }
                    ?>
                </ul>

                <!-- SUGGESTIONS: TOP 3 LOWEST (English) -->
                <h4 class="mt-4 text-danger">📌 Suggestions — Top 3 Lowest Subjects</h4>
                <?php
                $lowest_three = array_slice($marks_data, 0, min(3, count($marks_data)));
                foreach ($lowest_three as $low) {
                    $sub = htmlspecialchars($low['subject']);
                    $pct = rtrim(rtrim(number_format($low['percent'],2,'.',''), '0'), '.');

                    // suggestions in English tailored by percent
                    $advice = [];
                    if ($low['percent'] < 40) {
                        $advice[] = "Spend at least 45–60 minutes daily on this subject, focusing on fundamentals.";
                        $advice[] = "Make concise one-page notes for each major topic and revise them daily.";
                        $advice[] = "Attend doubt clearing sessions with your teacher and solve past papers.";
                    } elseif ($low['percent'] < 60) {
                        $advice[] = "Spend 20–40 minutes daily for targeted revision (theory + practice).";
                        $advice[] = "Identify 3–4 weak topics and practice related questions every day.";
                        $advice[] = "Take short self-tests to track improvement and time yourself.";
                    } else {
                        $advice[] = "Organize a weekly revision plan and gradually increase practice questions.";
                        $advice[] = "Time your attempts to build speed and accuracy before the next exam.";
                        $advice[] = "Discuss tricky problems with classmates or teachers to solidify understanding.";
                    }

                    echo "<div class='alert alert-warning suggestion'>
                            <div class='subject-name'>✳️ {$sub} — <small>{$pct}%</small></div>
                            <ul class='mt-2 mb-0'>";
                    foreach ($advice as $a) {
                        echo "<li>".htmlspecialchars($a)."</li>";
                    }
                    echo    "</ul></div>";
                }
                // end else marks_data not empty
            }
        }
    }
} // end if view_result
?>

  </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
