<?php
include('authentication.php');
include('includes/header.php');

$categories = [
    'slider1' => ['name' => 'Hero Slider One', 'spec' => '2500 × 500 px', 'badge' => 'Slider 1'],
    'slider2' => ['name' => 'Hero Slider Two', 'spec' => '2500 × 500 px', 'badge' => 'Slider 2'],
    'slider3' => ['name' => 'Hero Slider Three', 'spec' => '2500 × 500 px', 'badge' => 'Slider 3'],
    'slider4' => ['name' => 'Rectangle Poster One', 'spec' => '3200 × 2500 px', 'badge' => 'Poster 1'],
    'slider5' => ['name' => 'Rectangle Poster Two', 'spec' => '3200 × 2500 px', 'badge' => 'Poster 2'],
    'slider6' => ['name' => 'Rectangle Poster Three', 'spec' => '3200 × 2500 px', 'badge' => 'Poster 3'],
    'gallery' => ['name' => 'Gallery Image', 'spec' => 'Showcase Photo', 'badge' => 'Gallery'],
    'event'   => ['name' => 'Event Image', 'spec' => 'Campus / Event Photo', 'badge' => 'Event']
];

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$edit_item = null;
if ($edit_id > 0) {
    $e_res = mysqli_query($con, "SELECT * FROM add_element WHERE id = '$edit_id' LIMIT 1");
    if ($e_res && mysqli_num_rows($e_res) > 0) {
        $edit_item = mysqli_fetch_assoc($e_res);
    }
}
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Banners & Posters</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-photo-video"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Web Elements & Media Manager</h2>
                <p class="page-sub-heading mb-0">Upload, edit, and manage hero banners, rectangular posters, gallery, and event photos.</p>
            </div>
        </div>
        <div>
            <a href="index.php" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Dashboard
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- ==========================================
         Upload or Edit Form Card
         ========================================== -->
    <div class="teacher-card mb-4" id="uploadFormCard">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="assign-classes-icon-box" style="background: <?= $edit_item ? '#ecfdf5; color: #059669;' : '#ede9fe; color: var(--pca-primary);' ?>">
                <i class="<?= $edit_item ? 'fas fa-pencil-alt' : 'fas fa-cloud-upload-alt'; ?>"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">
                    <?= $edit_item ? 'Edit Banner / Poster #' . $edit_item['id'] : 'Upload New Banner / Poster'; ?>
                </h5>
                <p class="text-muted small mb-0">
                    <?= $edit_item ? 'Replace the image file or change category.' : 'Select image category and upload your high-resolution banner image.'; ?>
                </p>
            </div>
        </div>

        <form action="code.php" method="POST" enctype="multipart/form-data">
            <?php if ($edit_item): ?>
                <input type="hidden" name="element_id" value="<?= $edit_item['id']; ?>">
                <input type="hidden" name="old_photo" value="<?= htmlspecialchars($edit_item['photo']); ?>">
                <input type="hidden" name="old_category" value="<?= htmlspecialchars($edit_item['img_type']); ?>">
            <?php endif; ?>

            <div class="row g-3">
                <!-- Category Select -->
                <div class="col-md-6">
                    <label for="imageCategory" class="form-label-custom">Select Image Category <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <i class="fas fa-folder position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                        <select name="imageCategory" id="imageCategory" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" required>
                            <option value="">-- Select type of image --</option>
                            <?php foreach ($categories as $catKey => $catData): ?>
                                <?php $selected = ($edit_item && $edit_item['img_type'] === $catKey) ? 'selected' : ''; ?>
                                <option value="<?= $catKey; ?>" <?= $selected; ?>>
                                    <?= htmlspecialchars($catData['name'] . ' (' . $catData['spec'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- File Input -->
                <div class="col-md-6">
                    <label for="imageFile" class="form-label-custom">
                        <?= $edit_item ? 'Select Replacement Image (Optional)' : 'Select Image File <span class="text-danger">*</span>'; ?>
                    </label>
                    <input type="file" name="imageFile" id="imageFile" class="form-control" style="border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; padding-top: 8px;" accept="image/*" <?= $edit_item ? '' : 'required'; ?>>
                </div>

                <!-- Preview Thumbnail in Edit Mode -->
                <?php if ($edit_item): ?>
                    <div class="col-12">
                        <label class="form-label-custom">Current Image Preview</label>
                        <div class="p-2 border rounded-3 bg-light d-inline-flex align-items-center gap-3">
                            <img src="../img/path_image/<?= $edit_item['img_type']; ?>/<?= $edit_item['photo']; ?>" alt="Current image" style="height: 70px; max-width: 150px; object-fit: contain; border-radius: 8px; background: #fff;" onerror="this.src='../assets/img/default.png'">
                            <div>
                                <div class="fw-semibold text-dark small"><?= htmlspecialchars($edit_item['photo']); ?></div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($edit_item['img_type']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="col-12 mt-4 d-flex align-items-center gap-2">
                    <?php if ($edit_item): ?>
                        <button type="submit" name="updateElementImage" class="btn-primary-action">
                            <i class="far fa-save me-2"></i> Update Image
                        </button>
                        <a href="webelement.php" class="btn-secondary-action">
                            Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="uploadElementImage" class="btn-primary-action">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload & Save
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- ==========================================
         Section 1: Hero Sliders (2500 X 500 px)
         ========================================== -->
    <div class="teacher-card mb-4 p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 border-bottom pb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #ede9fe; color: var(--pca-primary); font-size: 1.1rem;">
                    <i class="fas fa-images"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">Hero Slider Banners</h5>
                    <p class="text-muted small mb-0">Recommended dimension: 2500 × 500 px (Slider 1, 2, 3)</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php
            $slider_query = "SELECT * FROM add_element WHERE img_type IN ('slider1', 'slider2', 'slider3') ORDER BY img_type ASC, id DESC";
            $slider_query_run = mysqli_query($con, $slider_query);

            if ($slider_query_run && mysqli_num_rows($slider_query_run) > 0) {
                foreach ($slider_query_run as $row) {
                    $catMeta = $categories[$row['img_type']] ?? ['badge' => $row['img_type'], 'name' => ucfirst($row['img_type'])];
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="h-100 rounded-4 border overflow-hidden d-flex flex-column shadow-sm bg-white position-relative">
                            <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white" style="backdrop-filter: blur(4px); font-size: 0.78rem;">
                                <?= htmlspecialchars($catMeta['badge']); ?>
                            </span>
                            <div style="height: 170px; background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <img src="../img/path_image/<?= $row['img_type']; ?>/<?= $row['photo']; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($row['photo']); ?>" onerror="this.src='../assets/img/default.png'">
                            </div>
                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-1 small text-truncate" title="<?= htmlspecialchars($row['photo']); ?>">
                                        <?= htmlspecialchars($row['photo']); ?>
                                    </h6>
                                    <span class="text-muted" style="font-size: 0.75rem;">Category: <?= htmlspecialchars($catMeta['name']); ?></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                    <a href="webelement.php?edit_id=<?= $row['id']; ?>" class="btn-action-pill-edit" title="Edit this banner">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                    <form method="POST" action="code.php" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this banner?');">
                                        <input type="hidden" name="element_id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="delete_element_image" class="btn-action-pill-delete" title="Delete this banner">
                                            <i class="far fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center py-4 text-muted"><i class="fas fa-image fs-3 mb-2 d-block opacity-40"></i>No hero slider banners uploaded yet.</div>';
            }
            ?>
        </div>
    </div>

    <!-- ==========================================
         Section 2: Promotional Rectangle Sliders (3200 X 2500 px)
         ========================================== -->
    <div class="teacher-card mb-4 p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 border-bottom pb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #ede9fe; color: var(--pca-primary); font-size: 1.1rem;">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">Promotional Rectangle Posters</h5>
                    <p class="text-muted small mb-0">Recommended dimension: 3200 × 2500 px (Slider 4, 5, 6)</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php
            $slider_rect = "SELECT * FROM add_element WHERE img_type IN ('slider4', 'slider5', 'slider6') ORDER BY img_type ASC, id DESC";
            $slider_rect_run = mysqli_query($con, $slider_rect);

            if ($slider_rect_run && mysqli_num_rows($slider_rect_run) > 0) {
                foreach ($slider_rect_run as $row) {
                    $catMeta = $categories[$row['img_type']] ?? ['badge' => $row['img_type'], 'name' => ucfirst($row['img_type'])];
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="h-100 rounded-4 border overflow-hidden d-flex flex-column shadow-sm bg-white position-relative">
                            <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white" style="backdrop-filter: blur(4px); font-size: 0.78rem;">
                                <?= htmlspecialchars($catMeta['badge']); ?>
                            </span>
                            <div style="height: 190px; background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <img src="../img/path_image/<?= $row['img_type']; ?>/<?= $row['photo']; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($row['photo']); ?>" onerror="this.src='../assets/img/default.png'">
                            </div>
                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-1 small text-truncate" title="<?= htmlspecialchars($row['photo']); ?>">
                                        <?= htmlspecialchars($row['photo']); ?>
                                    </h6>
                                    <span class="text-muted" style="font-size: 0.75rem;">Category: <?= htmlspecialchars($catMeta['name']); ?></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                    <a href="webelement.php?edit_id=<?= $row['id']; ?>" class="btn-action-pill-edit" title="Edit this poster">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                    <form method="POST" action="code.php" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this poster?');">
                                        <input type="hidden" name="element_id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="delete_element_image" class="btn-action-pill-delete" title="Delete this poster">
                                            <i class="far fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center py-4 text-muted"><i class="fas fa-image fs-3 mb-2 d-block opacity-40"></i>No promotional rectangle posters uploaded yet.</div>';
            }
            ?>
        </div>
    </div>

    <!-- ==========================================
         Section 3: Gallery & Event Images
         ========================================== -->
    <div class="teacher-card mb-4 p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 border-bottom pb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #ede9fe; color: var(--pca-primary); font-size: 1.1rem;">
                    <i class="fas fa-camera-retro"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">Gallery & Event Photos</h5>
                    <p class="text-muted small mb-0">Showcase photos displayed in the website gallery and campus events.</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php
            $media_query = "SELECT * FROM add_element WHERE img_type IN ('gallery', 'event') ORDER BY id DESC";
            $media_query_run = mysqli_query($con, $media_query);

            if ($media_query_run && mysqli_num_rows($media_query_run) > 0) {
                foreach ($media_query_run as $row) {
                    $catMeta = $categories[$row['img_type']] ?? ['badge' => $row['img_type'], 'name' => ucfirst($row['img_type'])];
                    $badgeBg = ($row['img_type'] === 'event') ? 'bg-primary' : 'bg-success';
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="h-100 rounded-4 border overflow-hidden d-flex flex-column shadow-sm bg-white position-relative">
                            <span class="position-absolute top-0 start-0 m-2 badge <?= $badgeBg; ?>" style="font-size: 0.78rem;">
                                <?= htmlspecialchars($catMeta['badge']); ?>
                            </span>
                            <div style="height: 160px; background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <img src="../img/path_image/<?= $row['img_type']; ?>/<?= $row['photo']; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($row['photo']); ?>" onerror="this.src='../assets/img/default.png'">
                            </div>
                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-1 small text-truncate" title="<?= htmlspecialchars($row['photo']); ?>">
                                        <?= htmlspecialchars($row['photo']); ?>
                                    </h6>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($catMeta['name']); ?></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                    <a href="webelement.php?edit_id=<?= $row['id']; ?>" class="btn-action-pill-edit" title="Edit this photo">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                    <form method="POST" action="code.php" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this photo?');">
                                        <input type="hidden" name="element_id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="delete_element_image" class="btn-action-pill-delete" title="Delete this photo">
                                            <i class="far fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center py-4 text-muted"><i class="fas fa-images fs-3 mb-2 d-block opacity-40"></i>No gallery or event photos uploaded yet.</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
