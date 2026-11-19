<?php
include('authentication.php');
include('includes/header.php');

?>

                <div class="container-fluid px-4">
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <?php include('message.php'); ?>
                            <div class="card">
                                <div class="card-header">
                                    <h4>View Notes
                                        <a href="notes-add.php" class="btn btn-primary float-end">Add Notes</a>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered"> 
                                        <thead>
                                            <tr>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>PDF Name</th> <!-- Added new header for PDF Name -->
                                                <th>View PDF</th>
                                                <th>Date Added</th>
                                                <th>Status</th>
                                                <th>Edit</th> <!-- Corrected these -->
                                                <th>Delete</th> <!-- Corrected these -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM notes ORDER BY created_at DESC";
                                            $query_run = mysqli_query($con, $query);

                                            if (mysqli_num_rows($query_run) > 0) {
                                                while ($row = mysqli_fetch_assoc($query_run)) {

                                                    $classMapping = [
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
                                                    $class = isset($classMapping[$row['class']]) ? $classMapping[$row['class']] : 'Unknown Class';
                                                    ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($class); ?></td>
                                                        <td><?= htmlspecialchars($row['subject']); ?></td>
                                                        <td><?= str_replace('_', ' ', htmlspecialchars($row['pdf_name'])); ?></td> <!-- Replace underscores with spaces -->
                                                        <td>
                                                            <a href="<?= htmlspecialchars($row['file_path']); ?>" target="_blank">View PDF</a>
                                                        </td>
                                                        <td><?= date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                                        <td><?= $row['status'] == 1 ? 'Active' : 'Inactive'; ?></td>
                                                        <td>
                                                            <a href="notes-edit.php?id=<?= $row['id'] ?>" class="btn btn-success">Edit</a>
                                                        </td>
                                                        <td>
                                                            <form action="code.php" method="POST">
                                                                <button type="submit" name="notes_delete_btn" value="<?= $row['id'] ?>" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                <tr>
                                                    <td colspan="7">No results found</td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
