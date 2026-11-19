<?php
include('authentication.php');
include('includes/header.php');
?>

         <div class="container-fluid px-4">
                        <h4 class="mt-4">Users</h4>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                            <li class="breadcrumb-item">Users</li>
                        </ol>
                        <div class="row">

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                     <h4>Edit User</h4>
                                </div>
                                <div class="card-body">

                                      <?php
                                      if(isset($_GET['id']))
                                      {
                                        $user_id = $_GET['id'];
                                        $users = "SELECT * FROM users Where id='$user_id'";
                                        $users_run = mysqli_query($con, $users);

                                        if(mysqli_num_rows($users_run) > 0)
                                        {
                                            foreach($users_run as $user){
                                          ?>
                                            

                                        <form action="code.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?=$user['id'];?>">

                                             
                                            <div class="col-md-2 mb-3">
                                                  <label for="">Roll no.</label>
                                                  <input type="text" name="rollno" value="<?=$user['rollno'];?>" class="form-control">
                                               </div>

                                           <div class="row">
                                               <div class="col-md-6 mb-3">
                                                  <label for="">First Name</label>
                                                  <input type="text" name="fname" value="<?=$user['fname'];?>" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Last Name</label>
                                                  <input type="text" name="lname" value="<?=$user['lname'];?>" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Email Address</label>
                                                  <input type="text" name="email" value="<?=$user['email'];?>" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Password</label>
                                                  <input type="text" name="password" value="<?=$user['password'];?>" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Role as</label>
                                                  <select name="role_as" required class="form-control">
                                                    <option value="">--Select Role--</option>
                                                    <option value="1" <?= $user['role_as'] == '1' ? 'selected disabled' : 'disabled' ?>>Admin</option>
                                                    <option value="0" <?=$user['role_as']=='0' ? 'selected':''?>>User</option>
                                                  </select>
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Status</label>
                                                  <input type="checkbox" name="status" <?=$user['status']=='1' ? 'checked':''?> width="70px" height="70px"/>
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                   <label for="class">Class</label>
                                                   <select name="class" id="class" class="form-control">
                                                      <option value="">--Select Class--</option>
                                                      <option value="0" <?=$user['class']=='0' ? 'selected':''?>>None</option>
                                                      <option value="6"<?=$user['class']=='6' ? 'selected':''?>>6th</option>
                                                      <option value="7"<?=$user['class']=='7' ? 'selected':''?>>7th</option>
                                                      <option value="8"<?=$user['class']=='8' ? 'selected':''?>>8th</option>
                                                      <option value="9"<?=$user['class']=='9' ? 'selected':''?>>9th</option>
                                                      <option value="10"<?=$user['class']=='10' ? 'selected':''?>>10th</option>
                                                      <option value="11"<?=$user['class']=='11' ? 'selected':''?>>Class 11 Sci (PCB)</option>
                                                      <option value="12"<?=$user['class']=='12' ? 'selected':''?>>Class 11 Sci (PCM)</option>
                                                      <option value="13"<?=$user['class']=='13' ? 'selected':''?>>Class 11 Comm.</option>
                                                      <option value="14"<?=$user['class']=='14' ? 'selected':''?>>Class 11 Arts</option>
                                                      <option value="15"<?=$user['class']=='15' ? 'selected':''?>>Class 12 Sci (PCB)</option>
                                                      <option value="16"<?=$user['class']=='16' ? 'selected':''?>>Class 12 Sci (PCM)</option>
                                                      <option value="17"<?=$user['class']=='17' ? 'selected':''?>>Class 12 Comm.</option>
                                                      <option value="18"<?=$user['class']=='18' ? 'selected':''?>>Class 12 Arts</option>
                                                   </select>
                                                </div>

                                               <div class="col-md-12 mb-3">
                                                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                                               </div> 
                                            
                                           </div>
                                        </from>
                                
                                    <?php
                                           }
                                        }else{
                                            ?>
                                            <h4>No Record Found</h4>
                                            <?php
                                        }
                                      }
                                      ?>
  
                                    
                                </div>
                            </div>
                        </div>
     
            </div>
        </div>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>