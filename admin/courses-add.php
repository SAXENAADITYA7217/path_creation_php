<?php
include('authentication.php');
include('includes/header.php');
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="courses-view.php">Courses</a></li>
        <li class="breadcrumb-item active">Add Course</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Add Course</h2>
                <p class="page-sub-heading mb-0">Create and publish a new course curriculum on the platform.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="courses-view.php" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <a href="courses-view.php" class="btn-secondary-action">
                <i class="fas fa-eye me-2"></i> View Courses
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Main Course Card -->
    <div class="teacher-card">
        <form action="code.php" method="POST" enctype="multipart/form-data" id="courseForm">
            <!-- Section 1: Basic Information -->
            <div class="mb-4">
                <h5 class="fw-bold text-dark mb-3" style="font-size: 1.05rem;">
                    <i class="fas fa-info-circle text-primary me-2"></i> Course Information
                </h5>
                <div class="row g-3">
                    <!-- Course Name -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Course Name <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-heading input-icon"></i>
                            <input type="text" name="name" id="courseNameInput" class="form-control custom-input" placeholder="e.g. Complete Mathematics Mastery" required>
                        </div>
                    </div>

                    <!-- Class -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Target Class <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-chalkboard-teacher input-icon"></i>
                            <input type="text" name="class" class="form-control custom-input" placeholder="e.g. Class 12 or Class 10" required>
                        </div>
                    </div>

                    <!-- Mode of Classes -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Class Mode <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-laptop-house input-icon"></i>
                            <input type="text" name="mode_of_classes" class="form-control custom-input" placeholder="e.g. Online, Offline, or Hybrid" required>
                        </div>
                    </div>

                    <!-- Slug (URL) -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Slug (URL) <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-link input-icon"></i>
                            <input type="text" name="slug" id="courseSlugInput" class="form-control custom-input" placeholder="e.g. complete-mathematics-mastery" required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label-custom">Course Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" style="border-radius: 12px; border: 1.5px solid #dcdff0; font-size: 0.9rem;" placeholder="Write a detailed description of this course..." required></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pricing Details Sub-Box -->
            <div class="assign-classes-box mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="assign-classes-icon-box">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <h5 class="assign-classes-title mb-0">Pricing & Discounts</h5>
                        <p class="assign-classes-subtitle mb-0">Set the standard and promotional fees for this course.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">MRP Price (₹) <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-rupee-sign input-icon"></i>
                            <input type="text" name="mrp_price" class="form-control custom-input" placeholder="e.g. 4999" required oninput="this.value=this.value.replace(/[^0-9.]/g,'');">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Discounted / Offer Price (₹) <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-tags input-icon"></i>
                            <input type="text" name="discounted_price" class="form-control custom-input" placeholder="e.g. 2999" required oninput="this.value=this.value.replace(/[^0-9.]/g,'');">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Media & Visibility -->
            <div class="mb-4">
                <h5 class="fw-bold text-dark mb-3" style="font-size: 1.05rem;">
                    <i class="fas fa-image text-primary me-2"></i> Media & Visibility
                </h5>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-custom">Course Banner / Thumbnail Image</label>
                        <input type="file" name="image" id="courseImageInput" class="form-control" style="border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; padding-top: 8px;" accept="image/*">
                        <small class="text-muted">Recommended aspect ratio: 16:9 or square (JPG, PNG, WEBP)</small>
                    </div>

                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="status" id="courseStatus" style="width: 48px; height: 24px; cursor: pointer;">
                            <label class="form-check-label ms-2 fw-semibold text-dark" for="courseStatus" style="padding-top: 3px; cursor: pointer;">
                                Visible & Active
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: SEO & Metadata Sub-Box -->
            <div class="assign-classes-box mb-4" style="background: #ffffff; border: 1.5px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="assign-classes-icon-box" style="background: #f1f5f9; color: #475569;">
                        <i class="fas fa-search"></i>
                    </div>
                    <div>
                        <h5 class="assign-classes-title mb-0">Search Engine Optimization (SEO)</h5>
                        <p class="assign-classes-subtitle mb-0">Optimize search engine visibility and social shares.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label-custom">Meta Title <span class="text-danger">*</span></label>
                        <input type="text" name="meta_title" maxlength="191" class="form-control custom-input" placeholder="e.g. Best Class 12 Mathematics Course | Path Creation Academy" style="height: 46px;" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Meta Description <span class="text-danger">*</span></label>
                        <textarea name="meta_description" class="form-control" rows="3" style="border-radius: 12px; border: 1.5px solid #dcdff0; font-size: 0.9rem;" placeholder="Brief synopsis for Google search results..." required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Meta Keywords <span class="text-danger">*</span></label>
                        <textarea name="meta_keyword" class="form-control" rows="3" style="border-radius: 12px; border: 1.5px solid #dcdff0; font-size: 0.9rem;" placeholder="maths, class 12, cbse, coaching..." required></textarea>
                    </div>
                </div>
            </div>

            <!-- Save Action Button -->
            <div class="d-flex align-items-center gap-3">
                <button type="submit" name="course_add" class="btn-primary-action">
                    <i class="far fa-save me-2"></i> Save Course
                </button>
                <a href="courses-view.php" class="btn-secondary-action">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-generate URL slug from course name
document.getElementById('courseNameInput').addEventListener('input', function() {
    let slugInput = document.getElementById('courseSlugInput');
    if (!slugInput.dataset.manual) {
        let text = this.value;
        slugInput.value = text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }
});

document.getElementById('courseSlugInput').addEventListener('input', function() {
    this.dataset.manual = "true";
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
