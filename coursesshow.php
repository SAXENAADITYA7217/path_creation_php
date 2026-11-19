<?php 
include('includes/header.php');
include('message.php');
?>

     
        <?php 
        include('includes/silder_a.php');
        ?>


<div class="py-5">
    <div class="container">
        <div class="row">

            <div class="col-md-9">
                <?php
                    if(isset($_GET['title']))
                    {
                        $slug = mysqli_real_escape_string($con, $_GET['title']);

                        $courses = "SELECT * FROM add_courses WHERE slug='$slug' ";
                        $courses_run = mysqli_query($con, $courses);

                        if(mysqli_num_rows($courses_run) > 0)
                        {

                                foreach($courses_run as $coursesItems)
                                {
                                    ?>
                                            
                                            
                                            <div class="card shadow-sm mb-4">
                                                        <div class="card-header">
                                                            <h5><?= $coursesItems['name']; ?></h5>

                                                        </div>

                                                        <div class="card-body">
                                                               <h2 class="text-center">Student Registration Form</h2>
                                                                <p class="text-center">Fill out the form for registration of <?= $coursesItems['class']; ?></p>
                                                                <form id="registrationForm">
                                                                    <div class="row">
                                                                        <div class="form-group col-md-6">
                                                                            <label for="firstName">First Name</label>
                                                                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First">
                                                                        </div>
                                                                        <div class="form-group col-md-6">
                                                                            <label for="lastName">Last Name</label>
                                                                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="form-group col-md-6">
                                                                            <label for="birthDate">Birth Date</label>
                                                                            <input type="date" class="form-control" id="birthDate" name="birthDate">
                                                                        </div>
                                                                        <div class="form-group col-md-6">
                                                                            <label for="gender">Gender</label>
                                                                            <select class="form-control" id="gender" name="gender">
                                                                                <option value="">Please Select</option>
                                                                                <option value="male">Male</option>
                                                                                <option value="female">Female</option>
                                                                                <option value="other">Other</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="address1">House no.</label>
                                                                        <input type="text" class="form-control" id="address1" name="address1" placeholder="Address line 1">
                                                                    </div>
                                                                        
                                                                    <div class="form-group">
                                                                      <label for="address1">Address</label>
                                                                        <input type="text" class="form-control" id="address2" name="address2" placeholder="Address line 2">
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <label for="city">City</label>
                                                                            <input type="text" class="form-control" id="city" name="city" placeholder="City">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="region">State</label>
                                                                            <select class="form-control" id="region" name="region">
                                                                                <option value="">Please Select</option>
                                                                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                                                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                                                                <option value="Assam">Assam</option>
                                                                                <option value="Bihar">Bihar</option>
                                                                                <option value="Chhattisgarh">Chhattisgarh</option>
                                                                                <option value="Goa">Goa</option>
                                                                                <option value="Gujarat">Gujarat</option>
                                                                                <option value="Haryana">Haryana</option>
                                                                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                                                                <option value="Jharkhand">Jharkhand</option>
                                                                                <option value="Karnataka">Karnataka</option>
                                                                                <option value="Kerala">Kerala</option>
                                                                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                                                                <option value="Maharashtra">Maharashtra</option>
                                                                                <option value="Manipur">Manipur</option>
                                                                                <option value="Meghalaya">Meghalaya</option>
                                                                                <option value="Mizoram">Mizoram</option>
                                                                                <option value="Nagaland">Nagaland</option>
                                                                                <option value="Odisha">Odisha</option>
                                                                                <option value="Punjab">Punjab</option>
                                                                                <option value="Rajasthan">Rajasthan</option>
                                                                                <option value="Sikkim">Sikkim</option>
                                                                                <option value="Tamil Nadu">Tamil Nadu</option>
                                                                                <option value="Telangana">Telangana</option>
                                                                                <option value="Tripura">Tripura</option>
                                                                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                                                                <option value="Uttarakhand">Uttarakhand</option>
                                                                                <option value="West Bengal">West Bengal</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="postalCode">Postal / Zip Code</label>
                                                                            <input type="text" class="form-control" id="postalCode" name="postalCode" placeholder="Postal / Zip Code">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="country">Country</label>
                                                                        <select class="form-control" id="country" name="country">
                                                                            <option value="United States">India</option>
                                                                            <!-- Add more countries as needed -->
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="email">Email</label>
                                                                        <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@example.com">
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="form-group col-md-6">
                                                                            <label for="phone">Whatsapp Phone no.</label>
                                                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="98XXXXXX42">
                                                                        </div>
                                                                        <div class="form-group col-md-6">
                                                                            <label for="workPhone">Phone no.</label>
                                                                            <input type="tel" class="form-control" id="workPhone" name="workPhone" placeholder="99XXXXXX71">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="company">School Name</label>
                                                                        <input type="text" class="form-control" id="company" name="company" placeholder="XYZ internation school">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="courses">Courses</label>
                                                                        <input type="text" class="form-control" name="courses" placeholder="<?= $coursesItems['name']; ?>" disabled>

                                                                    </div>
                                                                   <br>
                                                                    
                                                                    <button type="submit" class="btn btn-primary">Submit Application</button>
                                                                </form>
                                                        </div>
                                                    </div>
                                               
                                    <?php
                                }
                           
                        }
                        else
                        {
                            ?>
                            <h4>No Such Post Found</h4>
                            <?php
                        }
                    }else
                    {
                        ?>
                        <h4>No Such URL Found</h4>
                        <?php
                    }
                    ?>  
             </div>
 
             <div class="col-md-3">
             <div class="card">
                        <div class="card-header">
                            <h4>Advertise Area</h4>
                        </div>
                        <div class="card-body">
                            your advertise
                        </div>
                    </div>
            </div>
           
        </div>
    </div>
</div>


<div class="py-5 bg-white">
    <div class="container">
        <div class="row">

        <div class="col-md-9">
                  <h3 class="text-dark">Latest Posts</h3>
                  <div style="height: 4px; width: 50px; background-color: red; margin-bottom: 20px;"></div>


                  <?php
           $homePosts = "SELECT * FROM posts WHERE status='0' ORDER BY id DESC LIMIT 12";
           $homePosts_run = mysqli_query($con, $homePosts);

           if(mysqli_num_rows($homePosts_run) > 0)
           {
              foreach($homePosts_run as $homePostItems)
              {
                ?>
                    <div class="mb-4">
                      <a class="text-decoration-none" href="post.php?title=<?= $homePostItems['slug'] ?>">
                      <div class="card card-body bg-light">
                        <?= $homePostItems['name'] ?>
                        
                      </div>
                       </a>
                    </div>
                <?php
              }
           }
        ?>
        </div>
       <div class="col-md-3">
        <div class="card">
          <div class="card-header">
            <h4>Reach Us</h4>
          </div>
          <div class="card-body">
            info@example.com
          </div>
        </div>
       </div>


        </div>
    </div>
</div>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
