<?php 
include('studentcode.php');
include('includes/header.php');
?>

<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6>Student Report</h6>
            <a href=""><?php echo $full_name; ?></a>
        </div>
        <div class="table-responsive">
            <?php if (mysqli_num_rows($user_report) > 0) { ?>
                <table class="table text-start align-middle table-bordered table-hover mb-0 responsive-table">
                    <thead>
                        <tr class="text-dark">
                            <th scope="col">Date</th>
                            <th scope="col">Aptitude IQ</th>
                            <th scope="col">Physical Strength</th>
                            <th scope="col">Hindi Read Marks</th>
                            <th scope="col">Hindi Write Marks</th>
                            <th scope="col">English Read Marks</th>
                            <th scope="col">English Write Marks</th>
                            <th scope="col">Typing Speed</th>
                            <th scope="col">Student Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($result_row = mysqli_fetch_assoc($user_report)) {
                            $formattedDate = date('d-m-Y', strtotime($result_row['date'])); // Format the date
                        ?>
                            <tr>
                                <td data-label="Date"><?php echo $formattedDate; ?></td>
                                <td data-label="Aptitude IQ"><?php echo $result_row['math_marks']; ?></td>
                                <td data-label="Physical Strength"><?php echo $result_row['science_marks']; ?></td>
                                <td data-label="Hindi Read Marks"><?php echo $result_row['hindi_read_marks']; ?></td>
                                <td data-label="Hindi Write Marks"><?php echo $result_row['hindi_write_marks']; ?></td>
                                <td data-label="English Read Marks"><?php echo $result_row['english_read_marks']; ?></td>
                                <td data-label="English Write Marks"><?php echo $result_row['english_write_marks']; ?></td>
                                <td data-label="Typing Speed"><?php echo $result_row['typing_speed']; ?></td>
                                <td data-label="Student Rating"><?php echo $result_row['student_rating']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No results found for the user.</p>
            <?php } ?>
        </div>
    </div>
</div>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
