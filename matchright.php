<!-- <div class="profile_search1">
	   <form>
		  <input type="text" class="m_1" name="ne" size="30" required="" placeholder="Enter Profile ID :">
		  <input type="submit" value="Go">
	   </form>
  </div> -->
  <section class="slider">
	 <h3>Happy Marriage</h3>
	 <div class="flexslider">
		<ul class="slides">
		  <li>
			<img src="images/s2.jpg" alt=""/>
			<h4>Jhon & Mary</h4>
			<p>It is a long established fact that a reader will be distracted by the readable</p>
		  </li>
		  <li>
			<img src="images/s1.jpg" alt=""/>
			<h4>Annie & Williams</h4>
			<p>It is a long established fact that a reader will be distracted by the readable</p>
		  </li>
		  <li>
			<img src="images/s3.jpg" alt=""/>
			<h4>Ram & Isha</h4>
			<p>It is a long established fact that a reader will be distracted by the readable</p>
		  </li>
	    </ul>
	  </div>
   </section>

   <div class="view_profile view_profile2">
        	<h3>View Recent Profiles</h3>
    <?php
     // Exclude deleted and suspended users from recent profiles
     $gender_filter = "";
     if(isset($_SESSION['id'])) {
         $logged_user_id = intval($_SESSION['id']);
         $my_gender_sql = "SELECT sex FROM customer WHERE cust_id = ".$logged_user_id;
         if(function_exists('mysqlexec')) {
             $my_gender_result = mysqlexec($my_gender_sql);
         } else {
             global $conn;
             $my_gender_result = mysqli_query($conn, $my_gender_sql);
         }
         
         if($my_gender_result && mysqli_num_rows($my_gender_result) > 0) {
             $my_gender_row = mysqli_fetch_assoc($my_gender_result);
             $my_user_gender = $my_gender_row['sex'];
             $my_opposite_gender = (strtolower($my_user_gender) == 'male') ? 'Female' : 'Male';
             $gender_filter = " AND LOWER(TRIM(c.sex)) = LOWER('$my_opposite_gender')";
         }
     }

     $sql="SELECT c.* FROM customer c
           INNER JOIN users u ON c.cust_id = u.id
           WHERE u.account_status = 'active' AND u.userlevel = 0 $gender_filter
           ORDER BY c.profilecreationdate DESC";
      $result=mysqlexec($sql);
      $count=1;
      while($row=mysqli_fetch_assoc($result)){
            $profid=$row['cust_id'];
          //getting photo
          $sql="SELECT * FROM photos WHERE cust_id=$profid";
          $result2=mysqlexec($sql);
          $photo=mysqli_fetch_assoc($result2);
          $pic=$photo['pic1'];
          echo "<ul class=\"profile_item\">";
            echo"<a href=\"view_profile.php?id={$profid}\">";
              echo "<li class=\"profile_item-img\"><img src=\"profile/". $profid."/".$pic ."\"" . "class=\"img-responsive\" alt=\"\"/></li>";
               echo "<li class=\"profile_item-desc\">";
                  echo "<h4>" . $row['firstname'] . " " . $row['lastname'] . "</h4>";
                  echo "<p>" . $row['age']. "Yrs," . $row['religion'] . "</p>";
                  echo "<h5>" . "View Full Profile" . "</h5>";
               echo "</li>";
      echo "</a>";
      echo "</ul>";
      $count++;
      }
     ?>
           
</div>