<?php
include('authentication.php');

///////////////////////////////////////////////////////

if(isset($_POST['add_subject_teachers'])) {

    // 1️⃣ Get teacher_id
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if(!$teacher_id) {
        $_SESSION['message'] = "Please select a teacher.";
        header("Location: add-teacher-subject.php");
        exit(0);
    }

    // 2️⃣ Get class_code[] and subject[]
    $class_codes = $_POST['class_code'] ?? [];
    $subjects = $_POST['subject'] ?? [];

    // 3️⃣ Validate arrays length
    if(count($class_codes) !== count($subjects)) {
        $_SESSION['message'] = "Mismatch between class and subject.";
        header("Location: add-teacher-subject.php?teacher_id=" . $teacher_id);
        exit(0);
    }

    $errors = 0;

    // 4️⃣ Loop and insert each row
    $stmt = mysqli_prepare($con, "INSERT INTO teacher_subject (teacher_id, class_code, subject) VALUES (?, ?, ?)");
    if(!$stmt) {
        die("Prepare failed: ".mysqli_error($con));
    }

    for($i=0; $i<count($class_codes); $i++) {
        $class_code = intval($class_codes[$i]);
        $subject = trim($subjects[$i]);

        if($class_code <= 0 || empty($subject)) continue;

        // Prevent duplicate insertion for same teacher, class, and subject
        $check_stmt = mysqli_prepare($con, "SELECT id FROM teacher_subject WHERE teacher_id = ? AND class_code = ? AND subject = ? LIMIT 1");
        mysqli_stmt_bind_param($check_stmt, "iis", $teacher_id, $class_code, $subject);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if(mysqli_stmt_num_rows($check_stmt) == 0) {
            mysqli_stmt_bind_param($stmt, "iis", $teacher_id, $class_code, $subject);
            if(!mysqli_stmt_execute($stmt)) {
                $errors++;
            }
        }
        mysqli_stmt_close($check_stmt);
    }

    if($errors === 0) {
        $_SESSION['message'] = "Teacher subjects added successfully!";
        header("Location: view-assign-subject.php?teacher_id=" . $teacher_id);
        exit(0);
    } else {
        $_SESSION['message'] = "$errors row(s) failed to insert!";
        header("Location: view-assign-subject.php?teacher_id=" . $teacher_id);
        exit(0);
    }

   
}



// ✅ Handle Update Subject Request
if (isset($_POST['update_subject_teacher'])) {
    $assign_id = intval($_POST['assign_id'] ?? 0);
    $teacher_id = intval($_POST['teacher_id'] ?? 0);
    $class_code = intval($_POST['class_code'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');

    if ($assign_id > 0 && $teacher_id > 0 && $class_code > 0 && !empty($subject)) {
        // Prevent duplicate assignment: another row with same teacher, class, and subject
        $check_stmt = mysqli_prepare($con, "SELECT id FROM teacher_subject WHERE teacher_id = ? AND class_code = ? AND subject = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($check_stmt, "iisi", $teacher_id, $class_code, $subject, $assign_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['message'] = "This subject is already assigned to this teacher for this class.";
        } else {
            $stmt = mysqli_prepare($con, "UPDATE teacher_subject SET teacher_id = ?, class_code = ?, subject = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "iisi", $teacher_id, $class_code, $subject, $assign_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Teacher subject updated successfully!";
            } else {
                $_SESSION['message'] = "Failed to update teacher subject.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check_stmt);
    } else {
        $_SESSION['message'] = "Please select a valid class and subject.";
    }

    header("Location: view-assign-subject.php?teacher_id=" . $teacher_id);
    exit(0);
}

// ✅ Handle Delete Subject Request


if (isset($_POST['delete_assign-subject'])) {
    $subject_id = intval($_POST['subject_id']);
    $teacher_id = intval($_POST['teacher_id']);

    $query = "DELETE FROM teacher_subject WHERE id = '$subject_id'";
    $result = mysqli_query($con, $query);

    if ($result) {
        // ✅ Use header() for reliable redirect
        $_SESSION['message'] = "Subject deleted successfully!";
        header("Location: view-assign-subject.php?teacher_id=$teacher_id");
        exit(0);
    } else {
        $_SESSION['message'] = "Failed to delete subject. Please try again.";
        header("Location: view-assign-subject.php?teacher_id=$teacher_id");
        exit(0);
    }
}





////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST['add_multiple_teachers'])) {
    $fnames = $_POST['fname'];
    $fathernames = $_POST['fathername'];
    $phones = $_POST['phone'];
    $classes = $_POST['class']; // this is class[][]

    $role_as = 2;
    $status = 0;
    $successCount = 0;

    for ($i = 0; $i < count($fnames); $i++) {
        $fname = mysqli_real_escape_string($con, trim($fnames[$i]));
        $fathername = mysqli_real_escape_string($con, trim($fathernames[$i]));
        $phone_no = mysqli_real_escape_string($con, trim($phones[$i]));

        // array of class for this teacher
        $classList = $classes[$i]; 

        if (!empty($fname) && !empty($phone_no) && !empty($classList)) {

            // Generate custom user_id
            $query = "SELECT user_id FROM users WHERE user_id LIKE 'P%' ORDER BY user_id DESC LIMIT 1";
            $result = mysqli_query($con, $query);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $last_id = intval(substr($row['user_id'], 1)); // Remove 'P'
                $new_id = $last_id + 1;
                $user_id = "P" . str_pad($new_id, 6, "0", STR_PAD_LEFT);
            } else {
                $user_id = "P000001";
            }
            // Create email & password
            $clean_fname = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($fname));
            $last4 = substr($phone_no, -4);
            $email = $clean_fname . $last4 . "@pathcreationacademy.com";
            $password = $phone_no;

            // Insert in users table
            $query = "INSERT INTO users (user_id, fname, father_name, email, password, role_as, status, phone_no) 
                      VALUES ('$user_id','$fname','$fathername','$email','$password','$role_as','$status','$phone_no')";
            $query_run = mysqli_query($con, $query);

            if ($query_run) {
                // Insert each class of teacher in class_teacher table
                foreach ($classList as $classVal) {
                    $classVal = mysqli_real_escape_string($con, $classVal);
                    mysqli_query($con, "INSERT INTO class_teacher (user_id, class) VALUES ('$user_id', '$classVal')");
                }

                $successCount++;
            }
        }
    }

    $_SESSION['message'] = $successCount > 0 
        ? "$successCount Teacher(s) Added Successfully"
        : "No Teacher Added!";
    header('Location: view-teacher.php');
    exit(0);
}


// ---------------------------
if(isset($_POST['update_teacher'])) {
    $teacher_id = mysqli_real_escape_string($con, $_POST['teacher_id']);
    $user_id = isset($_POST['user_id']) ? mysqli_real_escape_string($con, trim($_POST['user_id'])) : '';
    $fname = mysqli_real_escape_string($con, trim($_POST['fname']));
    $father_name = mysqli_real_escape_string($con, trim($_POST['father_name']));
    $phone_no = mysqli_real_escape_string($con, trim($_POST['phone_no']));

    // Fetch existing teacher record to verify/get user_id
    if (empty($user_id)) {
        $check_teacher = mysqli_query($con, "SELECT user_id FROM users WHERE id='$teacher_id' LIMIT 1");
        if ($check_teacher && mysqli_num_rows($check_teacher) > 0) {
            $t_row = mysqli_fetch_assoc($check_teacher);
            $user_id = !empty($t_row['user_id']) ? $t_row['user_id'] : '';
        }
    }

    // If teacher doesn't have a user_id yet, generate one
    if (empty($user_id)) {
        $gen_query = "SELECT user_id FROM users WHERE user_id LIKE 'P%' ORDER BY user_id DESC LIMIT 1";
        $gen_result = mysqli_query($con, $gen_query);
        if ($gen_result && mysqli_num_rows($gen_result) > 0) {
            $row = mysqli_fetch_assoc($gen_result);
            $last_id = intval(substr($row['user_id'], 1));
            $user_id = "P" . str_pad($last_id + 1, 6, "0", STR_PAD_LEFT);
        } else {
            $user_id = "P" . str_pad($teacher_id, 6, "0", STR_PAD_LEFT);
        }
    }

    $clean_fname = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($fname));
    $last4 = substr($phone_no, -4);
    $email = $clean_fname . $last4 . "@pathcreationacademy.com";
    $status = isset($_POST['status']) ? '1' : '0';

    // Process assigned classes array
    $submitted_classes = isset($_POST['class']) ? (array)$_POST['class'] : [];
    $clean_classes = [];
    foreach ($submitted_classes as $c) {
        $c = trim($c);
        if ($c !== '' && !in_array($c, $clean_classes)) {
            $clean_classes[] = mysqli_real_escape_string($con, $c);
        }
    }

    $primary_class = !empty($clean_classes) ? $clean_classes[0] : '';

    $query = "UPDATE users 
              SET user_id='$user_id', fname='$fname', father_name='$father_name', email='$email', password='$phone_no', phone_no='$phone_no', status='$status', class='$primary_class' 
              WHERE id='$teacher_id' AND role_as='2'"; // role_as=2 for teachers

    $query_run = mysqli_query($con, $query);

    if($query_run) {
        // Remove previous assigned classes from class_teacher
        $del_query = "DELETE FROM class_teacher WHERE user_id = '$user_id'";
        if (!empty($teacher_id) && $teacher_id !== $user_id) {
            $del_query .= " OR user_id = '$teacher_id'";
        }
        mysqli_query($con, $del_query);

        // Insert updated assigned classes
        foreach ($clean_classes as $classVal) {
            mysqli_query($con, "INSERT INTO class_teacher (user_id, class) VALUES ('$user_id', '$classVal')");
        }

        $_SESSION['message'] = "Teacher Updated Successfully";
    } else {
        $_SESSION['message'] = "Teacher Update Failed: ".mysqli_error($con);
    }

    header('Location: view-teacher.php');
    exit(0);
}

// ---------------------------
// DELETE TEACHER
// ---------------------------
if(isset($_POST['delete_teacher'])) {
    $teacher_id = mysqli_real_escape_string($con, $_POST['teacher_id']);
    $user_id = isset($_POST['user_id']) ? mysqli_real_escape_string($con, trim($_POST['user_id'])) : '';

    // First, delete from class_teacher table
    $delete_class_teacher_query = "DELETE FROM class_teacher WHERE user_id = '$teacher_id'";
    if (!empty($user_id) && $user_id !== $teacher_id) {
        $delete_class_teacher_query .= " OR user_id = '$user_id'";
    }
    $delete_class_teacher_run = mysqli_query($con, $delete_class_teacher_query);

    // Delete teacher from users table
    $delete_query = "DELETE FROM users WHERE (id = '$teacher_id' OR user_id = '$teacher_id'";
    if (!empty($user_id) && $user_id !== $teacher_id) {
        $delete_query .= " OR user_id = '$user_id'";
    }
    $delete_query .= ") AND role_as='2'";
    $delete_run = mysqli_query($con, $delete_query);

    if($delete_run) {
        $_SESSION['message'] = "Teacher Deleted Successfully";
    } else {
        $_SESSION['message'] = "Failed to Delete Teacher: ".mysqli_error($con);
    }

    header('Location: view-teacher.php');
    exit(0);
}


// add subject


// Add Multiple Subjects
if(isset($_POST['add_multiple_subjects'])){
    $class = $_POST['class'];
    $status = 1;
    $subjects = $_POST['subject'];

    foreach($subjects as $subject){
        // Trim whitespace and escape special chars
        $subject = mysqli_real_escape_string($con, trim($subject));

        // Skip empty subjects after trimming
        if(empty($subject)) continue;

        // Prevent duplicates (case-insensitive)
        $check = mysqli_query($con, "SELECT * FROM class_subject_info 
                                     WHERE class_code='$class' 
                                     AND LOWER(subject)=LOWER('$subject')");
        if(mysqli_num_rows($check) == 0){
            mysqli_query($con, "INSERT INTO class_subject_info(class_code, subject, status, created_at) 
                                VALUES('$class','$subject','$status', NOW())");
        }
    }

    $_SESSION['success'] = "Subjects added successfully!";
    header("Location: view-subject.php?class=$class");
    exit();
}

// Update Subject
if(isset($_POST['update_subject'])){
    $subject_id = $_POST['subject_id'];
    $class_code = $_POST['class_code'];
    $subject = mysqli_real_escape_string($con, trim($_POST['subject']));
    $status = $_POST['status'];

    if(empty($subject)){
        $_SESSION['error'] = "Subject name cannot be empty!";
        header("Location: edit-subject.php");
        exit();
    }

    // Prevent duplicate subjects in same class
    $check = mysqli_query($con, "SELECT * FROM class_subject_info 
                                 WHERE class_code='$class_code' 
                                 AND LOWER(subject)=LOWER('$subject') 
                                 AND id != '$subject_id'");
    if(mysqli_num_rows($check) > 0){
        $_SESSION['error'] = "This subject already exists in the selected class!";
        $_SESSION['edit_subject'] = ['id'=>$subject_id, 'subject'=>$subject, 'status'=>$status];
        $_SESSION['edit_class_code'] = $class_code;
        header("Location: edit-subject.php");
        exit();
    }

    // Update subject
    $update = mysqli_query($con, "UPDATE class_subject_info 
                                  SET subject='$subject', status='$status' 
                                  WHERE id='$subject_id'");
    if($update){
        $_SESSION['success'] = "Subject updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update subject!";
        $_SESSION['edit_subject'] = ['id'=>$subject_id, 'subject'=>$subject, 'status'=>$status];
        $_SESSION['edit_class_code'] = $class_code;
    }

    header("Location: view-subject.php?class=$class_code");
    exit();
}

// Delete Subject
if(isset($_POST['delete_subject'])){
    $subject_id = $_POST['subject_id'];
    $class_code = $_POST['class_code'];

    mysqli_query($con, "DELETE FROM class_subject_info WHERE id='$subject_id'");
    $_SESSION['success'] = "Subject deleted successfully!";
    header("Location: view-subject.php?class=$class_code");
    exit(0);
}





///////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_POST['writenotes_delete_btn']))
{
    $post_id = $_POST['writenotes_delete_btn'];

    // Fetch the post to get the image filename
    $query = "SELECT image FROM writenotes WHERE id='$post_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);
    $post_data = mysqli_fetch_array($query_run);
    $image = $post_data['image'];

    // Delete the post from the database
    $delete_query = "DELETE FROM writenotes WHERE id='$post_id'";
    $delete_query_run = mysqli_query($con, $delete_query);

    if($delete_query_run)
    {
        // Delete the image file from the server if it exists
        if(file_exists('../uploads/writenotes/' . $image))
        {
            unlink('../uploads/writenotes/' . $image);
        }
        $_SESSION['message'] = "Notes Deleted Successfully";
        header('Location: writenotes-view.php');
        exit(0);
    }
    else
    {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: writenotes-view.php');
        exit(0);
    }
}






if (isset($_POST['writenotes_update'])) {
    // Connect to the database
    include 'db_connection.php';

    // Escape user inputs for security
    $post_id = mysqli_real_escape_string($con, $_POST['post_id']);
    $class = mysqli_real_escape_string($con, $_POST['class']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $slug = mysqli_real_escape_string($con, $_POST['slug']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $meta_title = mysqli_real_escape_string($con, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($con, $_POST['meta_description']);
    $meta_keyword = mysqli_real_escape_string($con, $_POST['meta_keyword']);
    $status = isset($_POST['status']) ? '1' : '0';

    $old_image = mysqli_real_escape_string($con, $_POST['old_image']);
    $new_image = $_FILES['image']['name'];

    if ($new_image != "") {
        $image_extension = pathinfo($new_image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_extension;
    } else {
        $filename = $old_image;
    }

    $query = "UPDATE writenotes SET class='$class', subject='$subject', name='$name', slug='$slug', description='$description', 
              image='$filename', meta_title='$meta_title', meta_description='$meta_description', meta_keyword='$meta_keyword', status='$status' 
              WHERE id='$post_id'";

    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        if ($new_image != "") {
            $target_file = '../uploads/writenotes/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                if (file_exists('../uploads/writenotes/' . $old_image)) {
                    unlink('../uploads/writenotes/' . $old_image);
                }
            } else {
                $_SESSION['message'] = "Image upload failed!";
                header('Location: writenotes-edit.php?id=' . $post_id);
                exit(0);
            }
        }
        $_SESSION['message'] = "Notes Updated Successfully";
        header('Location: writenotes-edit.php?id=' . $post_id);
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong: " . mysqli_error($con);
        header('Location: writenotes-edit.php?id=' . $post_id);
        exit(0);
    }
}





if (isset($_POST['writenotes_add'])) {
    // Connect to the database
    include 'db_connection.php';

    // Escape user inputs for security
    $class = mysqli_real_escape_string($con, $_POST['class']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $slug = mysqli_real_escape_string($con, $_POST['slug']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $meta_title = mysqli_real_escape_string($con, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($con, $_POST['meta_description']);
    $meta_keyword = mysqli_real_escape_string($con, $_POST['meta_keyword']);
    $status = isset($_POST['status']) ? '1' : '0';

    // File upload logic
    $image = $_FILES['image']['name'];
    if ($image) {
        $image_extension = pathinfo($image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_extension;
        $target_file = '../uploads/writenotes/' . $filename;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $_SESSION['message'] = "Image upload failed!";
            header('Location: writenotes-add.php');
            exit(0);
        }
    } else {
        $filename = "";
    }

    // Insert data into the database
    $query = "INSERT INTO writenotes (class, subject, name, slug, description, image, meta_title, meta_description, meta_keyword, status) 
              VALUES ('$class', '$subject', '$name', '$slug', '$description', '$filename', '$meta_title', '$meta_description', '$meta_keyword', '$status')";
    if (mysqli_query($con, $query)) {
        $_SESSION['message'] = "Notes Created Successfully";
        header('Location: writenotes-view.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong: " . mysqli_error($con);
        header('Location: writenotes-add.php');
        exit(0);
    }
}








if(isset($_POST['test_delete_btn']))
{
    $post_id = $_POST['test_delete_btn'];

    // Fetch the post to get the image filename
    $query = "SELECT image FROM test WHERE id='$post_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);
    $post_data = mysqli_fetch_array($query_run);
    $image = $post_data['image'];

    // Delete the post from the database
    $delete_query = "DELETE FROM test WHERE id='$post_id'";
    $delete_query_run = mysqli_query($con, $delete_query);

    if($delete_query_run)
    {
        // Delete the image file from the server if it exists
        if(file_exists('../uploads/test/' . $image))
        {
            unlink('../uploads/test/' . $image);
        }
        $_SESSION['message'] = "Test Deleted Successfully";
        header('Location: test-view.php');
        exit(0);
    }
    else
    {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: test-view.php');
        exit(0);
    }
}


if (isset($_POST['test_update'])) {
    $post_id = $_POST['post_id'];
    $class = mysqli_real_escape_string($con, $_POST['class']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $slug = mysqli_real_escape_string($con, $_POST['slug']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $meta_title = mysqli_real_escape_string($con, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($con, $_POST['meta_description']);
    $meta_keyword = mysqli_real_escape_string($con, $_POST['meta_keyword']);
    $status = isset($_POST['status']) ? '1' : '0';

    $old_image = $_POST['old_image'];
    $new_image = $_FILES['image']['name'];

    if ($new_image != "") {
        $image_extension = pathinfo($new_image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_extension;
    } else {
        $filename = $old_image;
    }

    $query = "UPDATE test SET class='$class', subject='$subject', name='$name', slug='$slug', description='$description', 
              image='$filename', meta_title='$meta_title', meta_description='$meta_description', meta_keyword='$meta_keyword', status='$status' 
              WHERE id='$post_id'";

    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        if ($new_image != "") {
            $upload_success = move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/test/' . $filename);
            if ($upload_success && file_exists('../uploads/test/' . $old_image)) {
                unlink('../uploads/test/' . $old_image);
            }
        }
        $_SESSION['message'] = "Test Updated Successfully";
        header('Location: test-view.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: test-edit.php?id=' . $post_id);
        exit(0);
    }
}


if (isset($_POST['test_add'])) {
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];

    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];

    $image = $_FILES['image']['name'];
    $image_extension = pathinfo($image, PATHINFO_EXTENSION);
    $filename = time() . '.' . $image_extension;

    $status = isset($_POST['status']) ? '1' : '0';

    // Escape special characters in strings
    $class = mysqli_real_escape_string($con, $class);
    $subject = mysqli_real_escape_string($con, $subject);
    $name = mysqli_real_escape_string($con, $name);
    $slug = mysqli_real_escape_string($con, $slug);
    $description = mysqli_real_escape_string($con, $description);
    $meta_title = mysqli_real_escape_string($con, $meta_title);
    $meta_description = mysqli_real_escape_string($con, $meta_description);
    $meta_keyword = mysqli_real_escape_string($con, $meta_keyword);

    $query = "INSERT INTO test (class, subject, name, slug, description, image, meta_title, meta_description, meta_keyword, status) 
              VALUES ('$class', '$subject', '$name', '$slug', '$description', '$filename', '$meta_title', '$meta_description', '$meta_keyword', '$status')";

    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/test/' . $filename);
        $_SESSION['message'] = "Test Created Successfully";
        header('Location: test-add.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: test-add.php');
        exit(0);
    }
}










if (isset($_POST['course_delete_btn'])) {
    $course_id = $_POST['course_delete_btn'];

    $query = "SELECT * FROM add_courses WHERE id='$course_id'";
    $query_run = mysqli_query($con, $query);
    $course = mysqli_fetch_array($query_run);
    $image_path = '../uploads/courses/' . $course['image'];

    if (file_exists($image_path)) {
        unlink($image_path);
    }

    $delete_query = "DELETE FROM add_courses WHERE id='$course_id'";
    $delete_query_run = mysqli_query($con, $delete_query);

    if ($delete_query_run) {
        $_SESSION['message'] = "Course Deleted Successfully";
        header('Location: courses-view.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong";
        header('Location: courses-view.php');
        exit(0);
    }
}



if(isset($_POST['course_update'])) {
    $course_id = $_POST['course_id'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $mrp_price = $_POST['mrp_price'];
    $discounted_price = $_POST['discounted_price'];
    $mode_of_classes = $_POST['mode_of_classes'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];
    $status = isset($_POST['status']) ? '1' : '0';

    // Handle the image upload
    $new_image = $_FILES['image']['name'];
    $old_image = $_POST['old_image'];

    if($new_image != '') {
        // Rename the image
        $image_extension = pathinfo($new_image, PATHINFO_EXTENSION);
        $filename = time().'.'.$image_extension;
        $upload_path = '../uploads/courses/'.$filename;
    } else {
        $filename = $old_image;
    }

    $query = "UPDATE add_courses SET name='$name', class='$class', mrp_price='$mrp_price', discounted_price='$discounted_price', mode_of_classes='$mode_of_classes', slug='$slug', description='$description', meta_title='$meta_title', meta_description='$meta_description', meta_keyword='$meta_keyword', image='$filename', status='$status' WHERE id='$course_id'";
    
    $query_run = mysqli_query($con, $query);

    if($query_run) {
        if($new_image != '') {
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
            if($old_image != '' && file_exists('../uploads/courses/'.$old_image)) {
                unlink('../uploads/courses/'.$old_image);
            }
        }
        $_SESSION['message'] = "Course Updated Successfully";
        header('Location: courses-view.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong";
        header('Location: courses-view.php');
        exit(0);
    }
}





if (isset($_POST['course_add'])) {
    $name = $_POST['name'];
    $class = $_POST['class'];
    $mrp_price = $_POST['mrp_price'];
    $discounted_price = $_POST['discounted_price'];
    $mode_of_classes = $_POST['mode_of_classes'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];
    $status = isset($_POST['status']) ? '1' : '0';

    // Handle the image upload
    $image = $_FILES['image']['name'];
    if ($image != '') {
        // Rename the image
        $image_extension = pathinfo($image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_extension;
        $upload_path = '../uploads/courses/' . $filename;
    } else {
        $filename = ''; // Set filename to empty string if no image is uploaded
    }

    // Insert data into the database
    $query = "INSERT INTO add_courses (name, class, mrp_price, discounted_price, mode_of_classes, slug, description, meta_title, meta_description, meta_keyword, image, status) 
              VALUES ('$name', '$class', '$mrp_price', '$discounted_price', '$mode_of_classes', '$slug', '$description', '$meta_title', '$meta_description', '$meta_keyword', '$filename', '$status')";
    
    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        if ($image != '') {
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
        }
        $_SESSION['message'] = "Course Added Successfully";
        header('Location: courses-add.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: courses-add.php');
        exit(0);
    }
}




if (isset($_POST['uploadElementImage'])) {
    $imageCategory = $_POST['imageCategory'];
    $imageFile = $_FILES['imageFile'];

    // Check if a valid image category is selected
    if (empty($imageCategory)) {
        $_SESSION['message'] = "Please select a valid image category!";
        $_SESSION['msg_type'] = "danger";
        header('Location: webelement.php');
        exit();
    }

    // Check if the file was uploaded without errors
    if ($imageFile['error'] == 0) {
        $uploadDir = '../img/path_image/' . $imageCategory . '/';
        $uploadFile = $uploadDir . basename($imageFile['name']);

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // If the category is slider1, slider2, or slider3, delete the old image
        if (in_array($imageCategory, ['slider1', 'slider2', 'slider3'])) {
            $query = "SELECT * FROM add_element WHERE img_type='$imageCategory'";
            $query_run = mysqli_query($con, $query);
            if (mysqli_num_rows($query_run) > 0) {
                $row = mysqli_fetch_assoc($query_run);
                $oldImage = $row['photo'];
                $oldImagePath = $uploadDir . $oldImage;

                // Delete the old image file if it exists
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }

                // Delete the old image record from the database
                $deleteQuery = "DELETE FROM add_element WHERE img_type='$imageCategory'";
                mysqli_query($con, $deleteQuery);
            }
        }

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($imageFile['tmp_name'], $uploadFile)) {
            // Store image details in the database
            $fileName = basename($imageFile['name']);
            $query = "INSERT INTO add_element (img_type, photo) VALUES ('$imageCategory', '$fileName')";
            if (mysqli_query($con, $query)) {
                $_SESSION['message'] = "Image uploaded and saved successfully!";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to save image details in the database!";
                $_SESSION['msg_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Failed to upload image!";
            $_SESSION['msg_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Error uploading image!";
        $_SESSION['msg_type'] = "danger";
    }

    header('Location: webelement.php'); 
    exit();
}








// ✅ Handle Update Element Image (Banner / Poster / Gallery / Event)
if (isset($_POST['updateElementImage'])) {
    $id = intval($_POST['element_id'] ?? 0);
    $imageCategory = mysqli_real_escape_string($con, trim($_POST['imageCategory'] ?? ''));
    $old_photo = mysqli_real_escape_string($con, trim($_POST['old_photo'] ?? ''));
    $old_category = mysqli_real_escape_string($con, trim($_POST['old_category'] ?? ''));
    $imageFile = $_FILES['imageFile'] ?? null;

    if ($id > 0 && !empty($imageCategory)) {
        $uploadDir = '../img/path_image/' . $imageCategory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // If a new image file was uploaded
        if ($imageFile && isset($imageFile['error']) && $imageFile['error'] === 0 && !empty($imageFile['name'])) {
            // Remove old file if it existed
            if (!empty($old_photo)) {
                $oldFilePath = '../img/path_image/' . $old_category . '/' . $old_photo;
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $newFileName = time() . '_' . basename($imageFile['name']);
            $targetPath = $uploadDir . $newFileName;
            if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                $stmt = mysqli_prepare($con, "UPDATE add_element SET img_type = ?, photo = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ssi", $imageCategory, $newFileName, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['message'] = "Banner / Poster updated successfully!";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to upload new image file!";
                $_SESSION['msg_type'] = "danger";
            }
        } else {
            // Only category changed, move existing file if folder differs
            if ($imageCategory !== $old_category && !empty($old_photo)) {
                $oldFilePath = '../img/path_image/' . $old_category . '/' . $old_photo;
                $newFilePath = $uploadDir . $old_photo;
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    rename($oldFilePath, $newFilePath);
                }
            }
            $stmt = mysqli_prepare($con, "UPDATE add_element SET img_type = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $imageCategory, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['message'] = "Image details updated successfully!";
            $_SESSION['msg_type'] = "success";
        }
    } else {
        $_SESSION['message'] = "Please provide a valid image category!";
        $_SESSION['msg_type'] = "danger";
    }

    header('Location: webelement.php');
    exit();
}

// ✅ Handle Delete Element Image (Banner / Poster / Gallery / Event)
if (isset($_POST['delete_element_image'])) {
    $id = intval($_POST['element_id'] ?? 0);
    if ($id > 0) {
        $res = mysqli_query($con, "SELECT img_type, photo FROM add_element WHERE id = '$id' LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $filePath = '../img/path_image/' . $row['img_type'] . '/' . $row['photo'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
            mysqli_query($con, "DELETE FROM add_element WHERE id = '$id'");
            $_SESSION['message'] = "Image deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Image record not found!";
            $_SESSION['msg_type'] = "danger";
        }
    }

    header('Location: webelement.php');
    exit();
}

if (isset($_POST['delete_result_btn'])) {
    $id = $_POST['id'];

    // Determine which table to delete data from based on the result ID
    // Assuming you have a mapping or way to find the class based on ID
    $query = "DELETE FROM result WHERE id='$id'";
    $result = mysqli_query($con, $query);

    if ($result) {
        $_SESSION['message'] = "Result deleted successfully!";
        header("Location: result-view.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Failed to delete result.";
        header("Location: result-view.php");
        exit(0);
    }
} 








if (isset($_POST['marks_update'])) {
    $id = $_POST['id'];
    $class = $_POST['class'];
    $user_id = $_POST['user_id'];
    $exam_type = $_POST['exam_type'];
    $date = $_POST['date'];

    // Collect marks for subjects based on class
    $subjects = [];
    if ($class === '8' || $class === '9' || $class === '10') {
        $subjects = ['english', 'hindi', 'maths', 'science', 'sst', 'computer_it'];
    } elseif ($class === '11' || $class === '15') {
        $subjects = ['english', 'hindi', 'physics', 'chemistry', 'biology', 'physical_education'];
    } elseif ($class === '12' || $class === '16') {
        $subjects = ['english', 'hindi', 'physics', 'chemistry', 'maths', 'physical_education'];
    } elseif ($class === '13' || $class === '17') {
        $subjects = ['english', 'hindi', 'account', 'economics', 'business', 'physical_education'];
    } elseif ($class === '14' || $class === '18') {
        $subjects = ['english', 'hindi', 'pol_science', 'economics', 'history', 'physical_education'];
    }

    $marks = [];
    foreach ($subjects as $subject) {
        $marks[$subject] = isset($_POST[$subject]) ? $_POST[$subject] : 0;
    }

    // Determine which table to update data into based on class
    $table = '';
    if ($class === '8' || $class === '9' || $class === '10') {
        $table = 'result';
    } elseif ($class === '11' || $class === '15') {
        $table = 'results_pcb';
    } elseif ($class === '12' || $class === '16') {
        $table = 'results_pcm';
    } elseif ($class === '13' || $class === '17') {
        $table = 'results_comm';
    } elseif ($class === '14' || $class === '18') {
        $table = 'results_arts';
    }

    // Update the result data in the appropriate database table
    $updateQuery = "UPDATE $table SET date='$date'";
    foreach ($marks as $subject => $mark) {
        $updateQuery .= ", $subject='$mark'";
    }
    $updateQuery .= " WHERE id='$id'";

    $result = mysqli_query($con, $updateQuery);

    if ($result) {
        $_SESSION['message'] = "Result updated successfully!";
        header("Location: result-view.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Failed to update result.";
        header("Location: result-edit.php?id=$id");
        exit(0);
    }
} 


if (isset($_POST['marks_add'])) {
    // Process the form data
    $class = $_POST['class'];
    $user_id = $_POST['id'];
    $exam_type = $_POST['exam_type'];
    $date = $_POST['date'];

    // Collect marks for subjects based on class
    $subjects = [];
    if ($class === '8' || $class === '9' || $class === '10') {
        $subjects = ['english', 'hindi', 'maths', 'science', 'sst', 'computer_it'];
    } elseif ($class === '11' || $class === '15') {
        $subjects = ['english', 'hindi', 'physics', 'chemistry', 'biology', 'physical_education'];
    } elseif ($class === '12' || $class === '16') {
        $subjects = ['english', 'hindi', 'physics', 'chemistry', 'maths', 'physical_education'];
    } elseif ($class === '13' || $class === '17') {
        $subjects = ['english', 'hindi', 'account', 'economics', 'business', 'physical_education'];
    } elseif ($class === '14' || $class === '18') {
        $subjects = ['english', 'hindi', 'pol_science', 'economics', 'history', 'physical_education'];
    }

    $marks = [];
    foreach ($subjects as $subject) {
        $marks[$subject] = isset($_POST[$subject]) ? $_POST[$subject] : 0;
    }

    // Determine which table to insert data into based on class
    $table = '';
    if ($class === '8' || $class === '9' || $class === '10') {
        $table = 'result';
    } elseif ($class === '11' || $class === '15') {
        $table = 'results_pcb';
    } elseif ($class === '12' || $class === '16') {
        $table = 'results_pcm';
    } elseif ($class === '13' || $class === '17') {
        $table = 'results_comm';
    } elseif ($class === '14' || $class === '18') {
        $table = 'results_arts';
    }

    // Now insert these details into the appropriate database table
    $columns = implode(", ", array_keys($marks));
    $values = implode(", ", array_values($marks));

    $query = "INSERT INTO $table (user_id, class, exam_type, date, $columns) VALUES ('$user_id', '$class', '$exam_type', '$date', $values)";
    $result = mysqli_query($con, $query);

    if ($result) {
        $_SESSION['message'] = "Result added successfully!";
        header("Location: result-view.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Failed to add result.";
        header("Location: add_result.php");
        exit(0);
    }
}


if (isset($_GET['selectedClass'])) {
    // Get the selected class from the request
    $selectedClass = $_GET['selectedClass'];

    // SQL query to fetch users based on selected class
    $usersQuery = "SELECT id, fname, lname FROM users WHERE class = '$selectedClass'";

    // Execute the query
    $usersResult = mysqli_query($con, $usersQuery);

    // Loop through the result and generate HTML markup
    $usersHtml = '';
    while ($user = mysqli_fetch_assoc($usersResult)) {
        $usersHtml .= "<div class='mb-3'><label>{$user['fname']} {$user['lname']}</label> <select name='attendance_status[{$user['id']}]' class='form-control'><option value='0'>Absent</option><option value='1'>Present</option></select></div>";
    }

    // Send the HTML markup back as response
    echo $usersHtml;
}


if(isset($_POST['class'])) {
    $class = $_POST['class'];

    $studentsQuery = "SELECT id, fname, lname, rollno FROM users WHERE class = '$class' AND role_as = '0'";
    $studentsResult = mysqli_query($con, $studentsQuery);

    if (mysqli_num_rows($studentsResult) > 0) {
        echo '<option value="">-- Select User --</option>';
        while ($student = mysqli_fetch_assoc($studentsResult)) {
            echo '<option value="'.$student['id'].'">'.$student['rollno'].' - '.$student['fname'].' '.$student['lname'].'</option>';
        }
    } else {
        echo '<option value="">No students available in this class</option>';
    }
}

if(isset($_POST['profile_delete'])) {
    $profile_id = $_POST['profile_delete']; 

    // First, fetch the photo filename to delete from the server
    $photo_query = "SELECT photo FROM student WHERE id = '$profile_id'";
    $photo_result = mysqli_query($con, $photo_query);
    $photo_row = mysqli_fetch_assoc($photo_result);
    $photo_filename = $photo_row['photo'];

    // Delete the profile from the database
    $delete_query = "DELETE FROM student WHERE id = '$profile_id'";
    $delete_result = mysqli_query($con, $delete_query);

    if($delete_result) {
        // If the profile was deleted successfully, delete the photo from the server
        if(file_exists('../uploads/students/'.$photo_filename)) {
            unlink("../uploads/students/".$photo_filename);
        }

        $_SESSION['message'] = "Profile deleted successfully";
        header('Location: profile-view.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Failed to delete profile. Please try again.";
        header('Location: profile-view.php');
        exit(0);
    }
} 




if(isset($_POST['profile_update'])) {
    $profile_id = $_POST['profile_id'];
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $student_email = $_POST['student_email'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $nationality = $_POST['nationality'];
    $old_photo = $_POST['old_photo'];
    
    $photo = $_FILES['photo']['name'];
    $photo_tmp_name = $_FILES['photo']['tmp_name'];
    $update_filename = $old_photo;

    if ($photo != NULL) {
        $photo_extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
        $filename = time() . '.' . $photo_extension;

        if ($photo_extension == 'jpeg' || $photo_extension == 'jpg') {
            list($width, $height) = getimagesize($photo_tmp_name);

            if ($width != 200 || $height != 200) {
                $image = imagecreatefromjpeg($photo_tmp_name);
                $resized_image = imagecreatetruecolor(200, 200);
                imagecopyresampled($resized_image, $image, 0, 0, 0, 0, 200, 200, $width, $height);
                imagejpeg($resized_image, $photo_tmp_name, 100);
                imagedestroy($image);
                imagedestroy($resized_image);
            }

            $update_filename = $filename;
        } else {
            $_SESSION['message'] = "Invalid Photo Format. Please upload a JPEG image.";
            header('Location: profile-edit.php?id=' . $profile_id);
            exit(0);
        }
    }

    $query = "UPDATE student SET name='$name', father_name='$father_name', student_email='$student_email', address='$address', gender='$gender', dob='$dob', nationality='$nationality', photo='$update_filename' WHERE id='$profile_id'";
    $query_run = mysqli_query($con, $query);

    if($query_run) {
        if($photo != NULL) {
            if(file_exists('../uploads/students/'.$old_photo)){
                unlink("../uploads/students/".$old_photo);
            }
            move_uploaded_file($photo_tmp_name, '../uploads/students/'.$update_filename);
        }
        $_SESSION['message'] = "Profile Updated Successfully";
        header('Location: profile-edit.php?id='.$profile_id);
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong.!";
        header('Location: profile-edit.php?id='.$profile_id);
        exit(0);
    }
}





if(isset($_POST['profile_add'])) {
    $user_id = $_POST['user_id'];
    $father_name = $_POST['father_name'];
    $student_email = $_POST['student_email'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $nationality = $_POST['nationality'];
    
    // Check if the user_id already exists in the student table
    $checkUserIdQuery = "SELECT * FROM student WHERE user_id = '$user_id'";
    $checkUserIdResult = mysqli_query($con, $checkUserIdQuery);
    
    if (mysqli_num_rows($checkUserIdResult) > 0) {
        // User ID already exists, return an error message
        $_SESSION['message'] = "User ID already exists. Profile not created.";
        header('Location: profile-add.php');
        exit(0);
    }

    // Fetch user details to form the full name and get class and rollno
    $userQuery = "SELECT fname, lname, class, rollno FROM users WHERE id = '$user_id'";
    $userResult = mysqli_query($con, $userQuery);
    $user = mysqli_fetch_assoc($userResult);
    
    $name = $user['fname'] . ' ' . $user['lname'];
    $class = $user['class'];
    $rollno = $user['rollno'];

    $photo = $_FILES['photo']['name'];
    $photo_tmp_name = $_FILES['photo']['tmp_name'];
    $photo_extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
    $filename = time() . '.' . $photo_extension;

    // Ensure the uploaded image is a JPEG
    if ($photo_extension == 'jpeg' || $photo_extension == 'jpg') {
        list($width, $height) = getimagesize($photo_tmp_name);

        // If dimensions are not 200x200, resize the image
        if ($width != 200 || $height != 200) {
            $image = imagecreatefromjpeg($photo_tmp_name);
            $resized_image = imagecreatetruecolor(200, 200);
            imagecopyresampled($resized_image, $image, 0, 0, 0, 0, 200, 200, $width, $height);
            imagejpeg($resized_image, $photo_tmp_name, 100);
            imagedestroy($image);
            imagedestroy($resized_image);
        }

        // Proceed to insert the profile data into the database
        $query = "INSERT INTO student (user_id, name, class, rollno, father_name, student_email, photo, address, gender, dob, nationality)
                  VALUES ('$user_id', '$name', '$class', '$rollno', '$father_name', '$student_email', '$filename', '$address', '$gender', '$dob', '$nationality')";
        
        $query_run = mysqli_query($con, $query);

        if($query_run) {
            move_uploaded_file($photo_tmp_name, '../uploads/students/'.$filename);
            $_SESSION['message'] = "Profile Created Successfully";
            header('Location: profile-add.php');
            exit(0);
        } else {
            $_SESSION['message'] = "Something Went Wrong.!";
            header('Location: profile-add.php');
            exit(0);
        }
    } else {
        $_SESSION['message'] = "Invalid Photo Format. Please upload a JPEG image.";
        header('Location: profile-add.php');
        exit(0);
    }
}





if (isset($_POST['notes_delete_btn'])) {
    $note_id = mysqli_real_escape_string($con, $_POST['notes_delete_btn']);

    // Fetch the file path from the database
    $query = "SELECT file_path FROM notes WHERE id='$note_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);
    $note = mysqli_fetch_array($query_run);
    $filePath = $note['file_path'];

    // Delete the file from the server
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete the record from the database
    $deleteQuery = "DELETE FROM notes WHERE id='$note_id'";
    if (mysqli_query($con, $deleteQuery)) {
        $_SESSION['message'] = "Note Deleted Successfully";
    } else {
        $_SESSION['message'] = "Database Error: Unable to delete note.";
    }

    header('Location: notes-view.php');
    exit();
}





if (isset($_POST['update_pdf'])) {
    $note_id = mysqli_real_escape_string($con, $_POST['note_id']);
    $class = mysqli_real_escape_string($con, $_POST['class']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $pdfName = mysqli_real_escape_string($con, trim($_POST['pdf_name'])); // Get the custom PDF name and trim it
    $status = isset($_POST['status']) ? 1 : 0;

    $safePdfName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pdfName); // Remove potentially dangerous characters

    // Fetch the current file path
    $query = "SELECT file_path FROM notes WHERE id='$note_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);
    $note = mysqli_fetch_array($query_run);
    $currentFilePath = $note['file_path'];

    $updateQuery = "UPDATE notes SET class='$class', subject='$subject', pdf_name='$safePdfName', status='$status'";

    if ($_FILES['result_file']['name']) {
        $file = $_FILES['result_file'];
        $fileTmpName = $file['tmp_name']; 
        $fileType = $file['type'];
        $fileError = $file['error'];

        if ($fileType == 'application/pdf') {
            if ($fileError === 0) {
                $fileNewName = $safePdfName . "_" . uniqid() . ".pdf"; // Append a unique ID to make the filename unique
                $fileDestination = '../uploads/notes/' . $fileNewName; // Construct the file destination path

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Delete the old file if it exists
                    if (file_exists($currentFilePath)) {
                        unlink($currentFilePath);
                    }
                    $updateQuery .= ", file_path='$fileDestination'";
                } else {
                    $_SESSION['message'] = "Failed to move the uploaded file.";
                    header('Location: notes-edit.php?id='.$note_id);
                    exit();
                }
            } else {
                $_SESSION['message'] = "Error uploading file!";
                header('Location: notes-edit.php?id='.$note_id);
                exit();
            }
        } else {
            $_SESSION['message'] = "Please upload a PDF file.";
            header('Location: notes-edit.php?id='.$note_id);
            exit();
        }
    }

    $updateQuery .= " WHERE id='$note_id'";

    if (mysqli_query($con, $updateQuery)) {
        $_SESSION['message'] = "PDF Updated Successfully";
    } else {
        $_SESSION['message'] = "Database Error: Unable to update PDF.";
    }

    header('Location: notes-view.php');
    exit();
}






if (isset($_POST['upload_pdf'])) {
    

    $class = mysqli_real_escape_string($con, $_POST['class']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $pdfName = mysqli_real_escape_string($con, trim($_POST['pdf_name'])); // Get the custom PDF name and trim it
    $status = isset($_POST['status']) ? 1 : 0; 

    // Handle file upload
    $file = $_FILES['result_file'];
    $fileTmpName = $file['tmp_name']; 
    $fileType = $file['type'];
    $fileError = $file['error'];

    if ($fileType == 'application/pdf') {
        if ($fileError === 0) {
            // Sanitize and create a safe file name
            $safePdfName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pdfName); // Remove potentially dangerous characters
            $fileNewName = $safePdfName . "_" . uniqid() . ".pdf"; // Append a unique ID to make the filename unique
            $fileDestination = '../uploads/notes/' . $fileNewName; // Construct the file destination path

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // SQL query to insert file information into the database
                $insertQuery = "INSERT INTO notes (class, subject, pdf_name, file_path, status) VALUES ('$class', '$subject', '$safePdfName', '$fileDestination', '$status')";
                if (mysqli_query($con, $insertQuery)) {
                    $_SESSION['message'] = "PDF Uploaded Successfully";
                } else {
                    $_SESSION['message'] = "Database Error: Unable to upload PDF.";
                }
            } else {
                $_SESSION['message'] = "Failed to move the uploaded file.";
            }
        } else {
            $_SESSION['message'] = "Error uploading file!";
        }
    } else {
        $_SESSION['message'] = "Please upload a PDF file.";
    }
    header('Location: notes-add.php'); // Redirect back to the add notes page
    exit();
}









if (isset($_POST['report_add'])) {
    // Database connection

    // Get form data
    $user_id = $_POST['id'];
    $math_marks = $_POST['mathmarks'];
    $science_marks = $_POST['sciencemarks'];
    $hindi_read_marks = $_POST['hindireadmarks'];
    $hindi_write_marks = $_POST['hindiwritemarks'];
    $english_read_marks = $_POST['englishreadmarks'];
    $english_write_marks = $_POST['englishwritemarks'];
    $typing_speed = $_POST['typingspeed'];
    $student_rating = $_POST['studentrating'];
    $date = $_POST['date'];

    // Retrieve fname and lname based on user_id
    $getUserInfoQuery = "SELECT fname, lname FROM users WHERE id = '$user_id'";
    $userInfoReport = mysqli_query($con, $getUserInfoQuery);

    if ($userInfoReport && mysqli_num_rows($userInfoReport) > 0) {
        $userInfo = mysqli_fetch_assoc($userInfoReport);
        $fname = $userInfo['fname'];
        $lname = $userInfo['lname'];

        // Insert data into the 'reports' table
        $insert_query = "INSERT INTO reports (id, fname, lname, math_marks, science_marks, hindi_read_marks, hindi_write_marks, english_read_marks, english_write_marks, typing_speed, student_rating, date)
        VALUES ('$user_id', '$fname', '$lname', '$math_marks', '$science_marks', '$hindi_read_marks', '$hindi_write_marks', '$english_read_marks', '$english_write_marks', '$typing_speed', '$student_rating', '$date')";

        $report = mysqli_query($con, $insert_query);

        if ($report) {
            $_SESSION['message'] = "Report Added Successfully";
            header('Location: report-view.php');
            exit();
        } else {
            $_SESSION['message'] = "Failed to Add Report. Something Went Wrong!";
            header('Location: report-view.php');
            exit();
        }
    } else {
        $_SESSION['message'] = "User Information Not Found!";
        header('Location: report-view.php');
        exit();
    }
}





if (isset($_POST['update_attendance'])) {
    $attendance_id = mysqli_real_escape_string($con, $_POST['attendance_id']);
    $date = mysqli_real_escape_string($con, $_POST['date']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $class = isset($_POST['class']) ? mysqli_real_escape_string($con, $_POST['class']) : '';

    $query = "UPDATE attendance SET date='$date', status='$status' WHERE id='$attendance_id'";
    if (mysqli_query($con, $query)) {
        $_SESSION['message'] = "Attendance record updated successfully.";
    } else {
        $_SESSION['message'] = "Database Error: Unable to update attendance record.";
    }

    if (!empty($class)) {
        header("Location: attendance-view.php?class=" . urlencode($class) . "&date=" . urlencode($date));
    } else {
        header('Location: attendance-view.php');
    }
    exit();
}

if (isset($_POST['attendance_delete_btn'])) {
    $attendance_id = mysqli_real_escape_string($con, $_POST['attendance_delete_btn']);
    $class = isset($_POST['class']) ? mysqli_real_escape_string($con, $_POST['class']) : '';
    $date = isset($_POST['date']) ? mysqli_real_escape_string($con, $_POST['date']) : '';

    // Delete the record from the database
    $deleteQuery = "DELETE FROM attendance WHERE id='$attendance_id'";
    if (mysqli_query($con, $deleteQuery)) {
        $_SESSION['message'] = "Attendance record deleted successfully.";
    } else {
        $_SESSION['message'] = "Database Error: Unable to delete attendance record.";
    }

    if (!empty($class) && !empty($date)) {
        header("Location: attendance-view.php?class=" . urlencode($class) . "&date=" . urlencode($date));
    } else {
        header('Location: attendance-view.php');
    }
    exit();
}

// Quick AJAX toggle for individual attendance status
if (isset($_POST['toggle_attendance_status'])) {
    header('Content-Type: application/json');
    $attendance_id = intval($_POST['attendance_id'] ?? 0);
    if ($attendance_id > 0) {
        $q = mysqli_query($con, "SELECT status FROM attendance WHERE id='$attendance_id' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $curr = mysqli_fetch_assoc($q);
            $newStatus = ((int)$curr['status'] === 1) ? 0 : 1;
            mysqli_query($con, "UPDATE attendance SET status='$newStatus' WHERE id='$attendance_id'");
            echo json_encode(['status' => 'success', 'new_status' => $newStatus]);
            exit();
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Attendance record not found.']);
    exit();
}

if (isset($_POST['add_multiple_attendance'])) {
    $date = mysqli_real_escape_string($con, $_POST['date']);
    $class = isset($_POST['class']) ? mysqli_real_escape_string($con, $_POST['class']) : '';
    $attendances = $_POST['attendance_status'] ?? [];

    if (!empty($attendances) && is_array($attendances)) {
        foreach ($attendances as $user_id => $status) {
            $user_id = intval($user_id);
            $status = intval($status);

            // Upsert: check if attendance for this user and date already exists
            $check = mysqli_query($con, "SELECT id FROM attendance WHERE user_id='$user_id' AND date='$date' LIMIT 1");
            if ($check && mysqli_num_rows($check) > 0) {
                $row = mysqli_fetch_assoc($check);
                $att_id = $row['id'];
                mysqli_query($con, "UPDATE attendance SET status='$status' WHERE id='$att_id'");
            } else {
                mysqli_query($con, "INSERT INTO attendance (user_id, date, status) VALUES ('$user_id', '$date', '$status')");
            }
        }
        $_SESSION['message'] = "All student attendances recorded successfully!";
    } else {
        $_SESSION['message'] = "No students selected for attendance.";
    }

    if (!empty($class)) {
        header("Location: attendance-view.php?class=" . urlencode($class) . "&date=" . urlencode($date));
    } else {
        header('Location: attendance-view.php');
    }
    exit();
}



/*

if (isset($_POST['marks_add'])) {
    // Database connection

    // Get form data
    $user_id = $_POST['id'];
    $math_marks = $_POST['mathmarks'];
    $science_marks = $_POST['sciencemarks'];
    $date = $_POST['date'];

    // Retrieve fname and lname based on user_id
    $getUserInfoQuery = "SELECT fname, lname FROM users WHERE id = '$user_id'";
    $userInfoResult = mysqli_query($con, $getUserInfoQuery);

    if ($userInfoResult && mysqli_num_rows($userInfoResult) > 0) {
        $userInfo = mysqli_fetch_assoc($userInfoResult);
        $fname = $userInfo['fname'];
        $lname = $userInfo['lname'];

        // Insert data into the 'results' table
        $insert_query = "INSERT INTO results (id, fname, lname, mathmarks, sciencemarks, date)
                         VALUES ('$user_id', '$fname', '$lname', '$math_marks', '$science_marks', '$date')";

        $result = mysqli_query($con, $insert_query);

        if ($result) {
            $_SESSION['message'] = "Result Added Successfully";
            header('Location: result-view.php');
            exit();
        } else {
            $_SESSION['message'] = "Failed to Add Result. Something Went Wrong!";
            header('Location: result-view.php');
            exit();
        }
    } else {
        $_SESSION['message'] = "User Information Not Found!";
        header('Location: result-view.php');
        exit();
    }

}


*/


if(isset($_POST['post_delete_btn']))
{
    $post_id = $_POST['post_delete_btn'];

    $check_img_query = "SELECT * FROM posts WHERE id='$post_id' LIMIT 1";
    $img_res = mysqli_query($con, $check_img_query);
    $res_data = mysqli_fetch_array($img_res);

    $image = $res_data['image'];

    
    $query = "DELETE FROM posts WHERE id='$post_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if($query_run) 
    {
            if(file_exists('../uploads/posts/'.$image)){
               unlink("../uploads/posts/".$image);
            }
       
       $_SESSION['message'] = "Post Deleted Successfully";
       header('Location: post-view.php');
       exit(0);
    }else{
       $_SESSION['message'] = "Something Went Wrong.!";
       header('Location: post-view.php');
       exit(0);
    } 
}


if (isset($_POST['post_update'])) {
    $post_id = $_POST['post_id'];

    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];

    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];

    $old_filename = $_POST['old_image'];
    $image = $_FILES['image']['name'];

    $update_filename = $old_filename;
    if ($image != NULL) {
        // Rename this Image
        $image_extension = pathinfo($image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_extension;
        $update_filename = $filename;
    }

    $status = isset($_POST['status']) && $_POST['status'] == '1' ? '1' : '0';

    // Escape special characters in strings
    $category_id = mysqli_real_escape_string($con, $category_id);
    $name = mysqli_real_escape_string($con, $name);
    $slug = mysqli_real_escape_string($con, $slug);
    $description = mysqli_real_escape_string($con, $description);
    $meta_title = mysqli_real_escape_string($con, $meta_title);
    $meta_description = mysqli_real_escape_string($con, $meta_description);
    $meta_keyword = mysqli_real_escape_string($con, $meta_keyword);

    $query = "UPDATE posts SET 
                category_id='$category_id', 
                name='$name', 
                slug='$slug', 
                description='$description', 
                image='$update_filename',
                meta_title='$meta_title', 
                meta_description='$meta_description', 
                meta_keyword='$meta_keyword', 
                status='$status' 
              WHERE id='$post_id'";    

    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        if ($image != NULL) {
            if (file_exists('../uploads/posts/' . $old_filename)) {
                unlink('../uploads/posts/' . $old_filename);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/posts/' . $update_filename);
        }
        $_SESSION['message'] = "Post Updated Successfully";
        header('Location: post-edit.php?id=' . $post_id);
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: post-edit.php?id=' . $post_id);
        exit(0);
    }
}


if (isset($_POST['post_add'])) {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];

    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];

    $image = $_FILES['image']['name'];
    // Rename the Image
    $image_extension = pathinfo($image, PATHINFO_EXTENSION);
    $filename = time() . '.' . $image_extension;

    $status = isset($_POST['status']) ? '1' : '0';

    // Escape special characters in strings
    $category_id = mysqli_real_escape_string($con, $category_id);
    $name = mysqli_real_escape_string($con, $name);
    $slug = mysqli_real_escape_string($con, $slug);
    $description = mysqli_real_escape_string($con, $description);
    $meta_title = mysqli_real_escape_string($con, $meta_title);
    $meta_description = mysqli_real_escape_string($con, $meta_description);
    $meta_keyword = mysqli_real_escape_string($con, $meta_keyword);

    $query = "INSERT INTO posts (category_id, name, slug, description, image, meta_title, meta_description, meta_keyword, status) 
              VALUES ('$category_id', '$name', '$slug', '$description', '$filename', '$meta_title', '$meta_description', '$meta_keyword', '$status')";

    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/posts/' . $filename);
        $_SESSION['message'] = "Post Created Successfully";
        header('Location: post-add.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong!";
        header('Location: post-add.php');
        exit(0);
    }
}



if(isset($_POST['category_delete']))
{
       $category_id = $_POST['category_delete'];

       // 2= delete
       $query = "UPDATE categories SET status='2' WHERE id='$category_id' LIMIT 1";
       $query_run = mysqli_query($con, $query);

       
       if($query_run)
       {
          $_SESSION['message'] = "Category Deleted Successfully";
          header('Location: category-view.php');
          exit(0);
       }else{
          $_SESSION['message'] = "Something Went Wrong.!";
          header('Location: category-view.php');
          exit(0);
       }
}

if(isset($_POST['category_update']))
{
    $category_id =$_POST['category_id'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];

    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];

    $navbar_status = $_POST['navbar_status'] == true ? '1':'0';
    $status = $_POST['status'] == true ? '1':'0';

    $query = "UPDATE categories SET name='$name', slug='$slug', description='$description', meta_title='$meta_title', 
              meta_description='$meta_description', meta_keyword='$meta_keyword', navbar_status='$navbar_status', 
              status='$status' WHERE id='$category_id' ";

    $query_run = mysqli_query($con, $query);

    if($query_run)
    {
        $_SESSION['message'] = "Category Updated Successfully";
        header('Location: category-edit.php?id='.$category_id);
        exit(0);
    }else{
        $_SESSION['message'] = "Something Went Wrong.!";
        header('Location: category-edit.php?id='.$category_id);
        exit(0);
    }
}

if(isset($_POST['category_add']))
{
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];

    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keyword = $_POST['meta_keyword'];

    $navbar_status = $_POST['navbar_status'] == true ? '1':'0';
    $status = $_POST['status'] == true ? '1':'0';
    

    $query = "INSERT INTO categories (name, slug, description, meta_title, meta_description, meta_keyword, navbar_status, status) VALUES 
    ('$name', '$slug', '$description', '$meta_title', '$meta_description', '$meta_keyword', '$navbar_status', '$status')";
    $query_run = mysqli_query($con, $query);

    if($query_run)
    {
        $_SESSION['message'] = "Category Added Successfully";
        header('Location: category-add.php');
        exit(0);
    }else{
        $_SESSION['message'] = "Something Went Wrong.!";
        header('Location: category-add.php');
        exit(0);
    }
}

if(isset($_POST['user_delete']))
{
   $user_id = $_POST['user_delete'];

   $query = "DELETE FROM users WHERE id='$user_id'";
   $query_run = mysqli_query($con, $query);

   if($query_run)
   {
       $_SESSION['message'] = "User/Admin Deleted Successfully";
       header('Location: view-register.php');
       exit(0);
   }else{
       $_SESSION['message'] = "Something Went Wrong.!";
       header('Location: view-register.php');
       exit(0);
   }
}


if(isset($_POST['add_user']))
{
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role_as = $_POST['role_as'];
    $class = $_POST['class'];
    $rollno = $_POST['rollno'];
    $status = $_POST['status'] == true ? '1':'0';
    
    $query = "INSERT INTO users (fname,lname,email,password,role_as,status, class, rollno) VALUES ('$fname','$lname','$email','$password','$role_as','$status', '$class','$rollno')";
    $query_run = mysqli_query($con, $query);

    if($query_run)
    {
        $_SESSION['message'] = "Admin/User Added Successfully";
        header('Location: view-register.php');
        exit(0);
    }else{
        $_SESSION['message'] = "Something Went Wrong.!";
        header('Location: view-register.php');
        exit(0);
    }
}

if(isset($_POST['update_user']))
{
    $user_id = $_POST['user_id'];
    $rollno = $_POST['rollno'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role_as = $_POST['role_as'];
    $status = $_POST['status'] == true ? '1':'0';
    $class = $_POST['class'];

    $query = "UPDATE users SET fname='$fname', lname='$lname', email='$email', password='$password', role_as='$role_as', status='$status', rollno='$rollno', class='$class' 
             WHERE id='$user_id' ";

    $query_run = mysqli_query($con, $query);

    if($query_run)
    {
       $_SESSION['message'] = "Updated Successfully"; 
       header('Location: view-register.php');
       exit(0);
    }
}


?>
