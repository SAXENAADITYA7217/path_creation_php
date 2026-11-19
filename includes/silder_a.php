<div id="myCarousel22" class="carousel22">
  <div class="carousel22-inner22">
    <?php
    $imageTypes = [
        ['large' => 'slider1', 'small' => 'slider4'],
        ['large' => 'slider2', 'small' => 'slider5'],
        ['large' => 'slider3', 'small' => 'slider6']
    ];

    foreach ($imageTypes as $types) {
        echo '<div class="carousel22-item22">';

        foreach ($types as $size => $type) {
            $homeCategory = "SELECT * FROM add_element WHERE img_type='$type'";
            $homeCategory_run = mysqli_query($con, $homeCategory);

            if (mysqli_num_rows($homeCategory_run) > 0) {
                while ($row = mysqli_fetch_assoc($homeCategory_run)) {
                    $class = $size === 'large' ? 'largeImg' : 'small-screen-only';
                    $imgPath = "img/path_image/$type/{$row['photo']}";
                    $altText = $size === 'large' ? 'Slide for large screen' : 'Slide for small screen';
                    echo "<img class='$class' src='$imgPath' alt='$altText'>";
                }
            } else {
                echo "<p>No slider images found for $type.</p>";
            }
        }

        echo '</div>';
    }
    ?>
  </div>
  <div class="carousel22-hots22"></div>
</div>
