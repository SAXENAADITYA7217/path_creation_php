<?php
include('authentication.php');
include('includes/header.php');
?>

         <div class="container-fluid px-4">
                        
                       
                        <div class="row mt-4">

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                     <h4>Add Admin/User
                                        <a href="view-register.php" class="btn btn-danger float-end">BACK</a>
                                     </h4>
                                </div>
                                <div class="card-body">

                                <form action="code.php" method="POST">
                                          

                                             
                                               <div class="col-md-2 mb-3">
                                                  <label for="">Roll no.</label>
                                                  <input type="text" name="rollno" class="form-control">
                                               </div>

                                           <div class="row">
                                               <div class="col-md-6 mb-3">
                                                  <label for="">First Name</label>
                                                  <input type="text" name="fname" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Last Name</label>
                                                  <input type="text" name="lname" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Email Address</label>
                                                  <input type="text" name="email" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Password</label>
                                                  <input type="password" name="password" class="form-control">
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Role as</label>
                                                  <select name="role_as" required class="form-control">
                                                    <option value="">--Select Role--</option>
                                                    <option value="1" disabled>Admin</option>
                                                    <option value="0">User</option>
                                                  </select>
                                               </div>

                                               <div class="col-md-6 mb-3">
                                                  <label for="">Status</label>
                                                  <input type="checkbox" name="status" width="70px" height="70px"/>
                                               </div>


                                                <div class="col-md-6 mb-3">
                                                   <label for="class">Class</label>
                                                   <select name="class" id="class" class="form-control">
                                                      <option value="">--Select Class--</option>
                                                      <option value="6">Class 6</option>
                                                      <option value="7">Class 7</option>
                                                      <option value="8">Class 8</option>
                                                      <option value="9">Class 9</option>
                                                      <option value="10">Class 10</option>
                                                      <option value="11">Class 11 Sci (PCB)</option>
                                                      <option value="12">Class 11 Sci (PCM)</option>
                                                      <option value="13">Class 11 Comm.</option>
                                                      <option value="14">Class 11 Arts</option>
                                                      <option value="15">Class 12 Sci (PCB)</option>
                                                      <option value="16">Class 12 Sci (PCM)</option>
                                                      <option value="17">Class 12 Comm.</option>
                                                      <option value="18">Class 12 Arts</option>
                                                   </select>
                                                </div>

                                                


                                               <div class="col-md-12 mb-3">
                                                <button type="submit" name="add_user" class="btn btn-primary">Adds Admin/User</button>
                                               </div> 
                                            
                                           </div>
                                        </from>
                                
                                </div>
                            </div>
                        </div>
     
            </div>
        </div>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>