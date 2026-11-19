<?php
include('studentcode.php');
include('includes/header.php');

// ✅ Get the logged-in student's class name
// Assuming $class is defined and contains the class name
$class_name = isset($class) ? mysqli_real_escape_string($con, $class) : 'Your Class';
?>

<div class="container-fluid pt-4 px-4">
    <h4 class="mb-4 text-primary fw-bold">📚 Study Material Sections for <?php echo htmlspecialchars($class_name); ?></h4>

    <div class="row g-4 justify-content-center align-items-stretch">

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card text-center shadow-lg border-0 material-box h-100 assignment"
                onclick="window.location.href='Assignments.php'">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 mb-3">📝</div>
                    <h5 class="fw-bold text-success mt-auto">Assignments</h5>
                    <p class="text-muted mt-2 mb-0">Check and download assignments</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card text-center shadow-lg border-0 material-box h-100 notes"
                onclick="window.location.href='notes.php'">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 mb-3">📘</div>
                    <h5 class="fw-bold text-primary mt-auto">Notes</h5>
                    <p class="text-muted mt-2 mb-0">View and download class notes</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card text-center shadow-lg border-0 material-box h-100 question"
                onclick="window.location.href='question_paper.php'">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 mb-3">📄</div>
                    <h5 class="fw-bold text-danger mt-auto">Question Papers</h5>
                    <p class="text-muted mt-2 mb-0">Previous year and sample papers</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card text-center shadow-lg border-0 material-box h-100 ppt"
                onclick="window.location.href='ppt.php'">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 mb-3">📊</div>
                    <h5 class="fw-bold text-warning mt-auto">PPTs</h5>
                    <p class="text-muted mt-2 mb-0">View class presentations</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card text-center shadow-lg border-0 material-box h-100 syllabus"
                onclick="window.location.href='syllabus.php'">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 mb-3">📚</div>
                    <h5 class="fw-bold text-info mt-auto">Syllabus</h5>
                    <p class="text-muted mt-2 mb-0">View and download syllabus</p>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.material-box {
    /* Use a slightly larger border-radius for a softer look */
    border-radius: 18px; 
    transition: 0.3s;
    cursor: pointer;
    /* Increase initial shadow slightly */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08) !important; 
}
.material-box:hover {
    transform: translateY(-8px); /* Slightly more lift */
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2) !important; /* Pronounced shadow on hover */
}

/* 🎨 Soft background colors for each type - Made the gradients slightly lighter */
.assignment { background: linear-gradient(135deg, #f0fff4, #e6ffe8); } /* Lighter green */
.notes { background: linear-gradient(135deg, #f0f8ff, #e6f7ff); } /* Lighter blue */
.question { background: linear-gradient(135deg, #fff0f0, #ffeded); } /* Lighter red/pink */
.ppt { background: linear-gradient(135deg, #fffaf0, #fffcf5); } /* Lighter yellow/cream */
.syllabus { background: linear-gradient(135deg, #f0faff, #e8f7ff); } /* Lighter cyan/info */

/* Ensure text colors are consistent */
.card-body h5 {
    /* Make the heading color a little darker for contrast if needed, or rely on the utility classes */
    /* color: #212529; */ 
}
.card-body p {
    color: #6c757d !important; /* Ensure muted text is consistently grey */
}

/* Custom alignment for card content using flexbox (applied via Bootstrap classes in HTML) */
.card-body.d-flex.flex-column {
    min-height: 100%; /* Important for h-100 to work correctly */
}
.card-body h5.mt-auto {
    /* Optional: Ensure the heading is always aligned nicely above the paragraph */
    margin-top: auto;
}
</style>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>