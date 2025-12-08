<?php include_once("includes/basic_includes.php");?>
<?php include_once("functions.php"); ?>
<?php include_once("includes/dropdown_options.php"); ?>
<?php 
if(! isloggedin()){
   header("location:login.php");
   exit();
}
?>
<?php
$id=$_SESSION['id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        processprofile_form($id);
        $success_message = 'Profile updated successfully!';
    } catch (Exception $e) {
        $error_message = 'Error updating profile: ' . $e->getMessage();
    }
}

// Fetch current profile data
require_once("includes/dbconn.php");
$profile_sql = "SELECT * FROM customer WHERE cust_id = $id";
$profile_result = mysqli_query($conn, $profile_sql);
$profile_data = mysqli_fetch_assoc($profile_result);
?>


<!DOCTYPE HTML>
<html>
<head>
<title>Edit Profile | Make My Love</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<!-- Custom Theme files -->
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
<!--font-Awesome-->
<link href="css/font-awesome.css" rel="stylesheet"> 
<!--font-Awesome-->
<script>
$(document).ready(function(){
    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideDown("fast");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideUp("fast");
            $(this).toggleClass('open');       
        }
    );
});
</script>
</head>
<body>
<!-- ============================  Navigation Start =========================== -->
<?php include_once("includes/navigation.php");?>
<!-- ============================  Navigation End ============================ -->
<div class="grid_3">
  <div class="container">
   <?php if ($success_message): ?>
       <div class="alert alert-success" style="margin-top: 20px; border-radius: 8px;">
           <i class="fa fa-check-circle"></i> <?php echo $success_message; ?>
           <a href="view_profile.php?id=<?php echo $id; ?>" class="btn btn-sm btn-primary pull-right">
               <i class="fa fa-eye"></i> View Profile
           </a>
       </div>
   <?php endif; ?>
   
   <?php if ($error_message): ?>
       <div class="alert alert-danger" style="margin-top: 20px; border-radius: 8px;">
           <i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?>
       </div>
   <?php endif; ?>
   
   <div class="breadcrumb1">
     <ul>
        <a href="index.php"><i class="fa fa-home home_1"></i></a>
        <span class="divider">&nbsp;|&nbsp;</span>
        <li class="current-page">Edit Profile</li>
     </ul>
   </div>
   <div class="services">
   	  <div class="col-sm-12 login_left">
	     <form action="" method="POST">
	  	    <div class="form-group col-sm-6">
		      <label for="edit-name">First Name <span class="form-required" title="This field is required.">*</span></label>
		      <input type="text" id="edit-name" name="fname" value="<?php echo htmlspecialchars($profile_data['firstname'] ?? ''); ?>" size="60" maxlength="60" class="form-text required">
		    </div>
		    <div class="form-group col-sm-4">
		      <label for="edit-pass">Last Name <span class="form-required" title="This field is required.">*</span></label>
		      <input type="text" id="edit-last" name="lname" value="<?php echo htmlspecialchars($profile_data['lastname'] ?? ''); ?>" size="60" maxlength="128" class="form-text required">
		    </div>
		     <div class="form-group col-sm-2">
		      <label for="edit-name">Sex <span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="sex">
	                    <option value="Male" <?php echo (($profile_data['sex'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
	                    <option value="Female" <?php echo (($profile_data['sex'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-6">
		      <label for="edit-name">Email <span class="form-required" title="This field is required.">*</span></label>
		      <input type="text" id="edit-name" name="email" value="<?php echo htmlspecialchars($profile_data['email'] ?? ''); ?>" size="60" maxlength="60" class="form-text required">
		    </div>

	    <div class="form-group col-sm-6">
			    <div class="age_select">
			      <label for="edit-pass">
 Date Of Birth <span class="form-required" title="This field is required.">*</span></label>
			        <div class="age_grid">
			         <div class="col-sm-4 form_box">
	                  <div class="select-block1">
	                    <select name="day">
		                    <option value=""></option>
		                    <option value="1">1</option>
		                    <option value="2">2</option>
		                    <option value="3">3</option>
		                    <option value="4">4</option>
		                    <option value="5">5</option>
		                    <option value="6">6</option>
		                    <option value="7">7</option>
		                    <option value="8">8</option>
		                    <option value="9">9</option>
		                    <option value="10">10</option>
		                    <option value="11">11</option>
		                    <option value="12">12</option>
		                    <option value="13">13</option>
		                    <option value="14">14</option>
		                    <option value="15">15</option>
		                    <option value="16">16</option>
		                    <option value="17">17</option>
		                    <option value="18">18</option>
		                    <option value="19">19</option>
		                    <option value="20">20</option>
		                    <option value="21">21</option>
		                    <option value="22">22</option>
		                    <option value="23">23</option>
		                    <option value="24">24</option>
		                    <option value="25">25</option>
		                    <option value="26">26</option>
		                    <option value="27">27</option>
		                    <option value="28">28</option>
		                    <option value="29">29</option>
		                    <option value="30">30</option>
		                    <option value="31">31</option>
	                    </select>
	                  </div>
	            </div>
	            <div class="col-sm-4 form_box2">
	                   <div class="select-block1">
	                    <select name="month">
		                    <option value="">Month</option>
		                    <option value="01">January</option>
		                    <option value="02">February</option>
		                    <option value="03">March</option>
		                    <option value="04">April</option>
		                    <option value="05">May</option>
		                    <option value="06">June</option>
		                    <option value="07">July</option>
		                    <option value="08">August</option>
		                    <option value="09">September</option>
		                    <option value="10">October</option>
		                    <option value="11">November</option>
		                    <option value="12">December</option>
	                    </select>
	                  </div>
	                 </div>
	                 <div class="col-sm-4 form_box1">
	                   <div class="select-block1">
	                    <select name="year">
		                    <option value="">Year</option>
		                    <option value="1980">1980</option>
		                    <option value="1981">1981</option>
		                    <option value="1981">1981</option>
		                    <option value="1983">1983</option>
		                    <option value="1984">1984</option>
		                    <option value="1985">1985</option>
		                    <option value="1986">1986</option>
		                    <option value="1987">1987</option>
		                    <option value="1988">1988</option>
		                    <option value="1989">1989</option>
		                    <option value="1990">1990</option>
		                    <option value="1991">1991</option>
		                    <option value="1992">1992</option>
		                    <option value="1993">1993</option>
		                    <option value="1994">1994</option>
		                    <option value="1995">1995</option>
		                    <option value="1996">1996</option>
		                    <option value="1997">1997</option>
		                    <option value="1998">1998</option>
		                    <option value="1999">1999</option>
		                    <option value="2000">2000</option>
		                    <option value="2001">2001</option>
		                    <option value="2002">2002</option>
		                    <option value="2003">2003</option>
		                    <option value="2004">2004</option>
		                    <option value="2005">2005</option>
		                    <option value="2006">2006</option>
	                    </select>
	                   </div>
	                  </div>
	                  <div class="clearfix"> </div>
	                 </div>
	              </div>
            </div>
            <div class="form-group col-sm-6">
			    <div class="age_select">
			      <label for="edit-pass">Religion <span class="form-required" title="This field is required.">*</span></label>
			        <div class="age_grid">
			         <div class="col-sm-4 form_box">
	                  <div class="select-block1">
	                    <select name="religion" id="edit-religion">
		                    <option value="">Select Religion</option>
		                    <?php foreach($religions as $religion): ?>
		                    <option value="<?= htmlspecialchars($religion) ?>"><?= htmlspecialchars($religion) ?></option>
		                    <?php endforeach; ?>
	                    </select>
	                  </div>
	            </div>
	         
	            <div class="col-sm-4 form_box2">
	                   <div class="select-block1">
	                    <select name="caste" id="edit-caste">
		                    <option value="">Select Caste</option>
		                    <?php foreach($castes_list as $caste): ?>
		                    <option value="<?= htmlspecialchars($caste['name']) ?>" data-religion="<?= htmlspecialchars($caste['religion']) ?>"><?= htmlspecialchars($caste['name']) ?></option>
		                    <?php endforeach; ?>
	                    </select>
	                  </div>
	                 </div>
	                 <div class="col-sm-4 form_box1">
	                   <div class="select-block1">
	                    <select name="subcaste">
		                    <option value="Not Applicable">Not Applicable</option>
		                    <option value="sub cast1">sub cast1</option>
		                    <option value="sub caste2">sub caste2</option>
		                  
	                    </select>
	                   </div>
	                  </div>
	                  <div class="clearfix"> </div>
	                 </div>
	              </div>
            </div>

            <!-- Fourth Row starts -->
              <div class="form-group col-sm-6">
			    <div class="age_select">
			      <label for="edit-pass">Address <span class="form-required" title="This field is required.">*</span></label>
			        <div class="age_grid">
			         <div class="col-sm-4 form_box">
	                  <div class="select-block1">
	                    <select name="country">
		                    <option value="Not Applicable">Country</option>
		                    <option value="India">India</option>
		                    <option value="China">China</option>
		                    <option value="UAE">UAE</option>
		                    
	                    </select>
	                  </div>
	            </div>
	         
	            <div class="col-sm-4 form_box2">
	                   <div class="select-block1">
	                    <select name="state" id="edit-state">
		                    <option value="">Select State</option>
		                    <?php foreach($states as $state): ?>
		                    <option value="<?= htmlspecialchars($state) ?>" data-state-id="<?= $states_map[$state] ?>"><?= htmlspecialchars($state) ?></option>
		                    <?php endforeach; ?>
	                    </select>
	                  </div>
	                 </div>
	                 <div class="col-sm-4 form_box1">
	                   <div class="select-block1">
	                    <select name="district" id="edit-city"> <!-- Using district field for City -->
		                    <option value="">Select City</option>
		                    <?php foreach($cities_list as $city): ?>
		                    <option value="<?= htmlspecialchars($city['name']) ?>" data-state-id="<?= $city['state_id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
		                    <?php endforeach; ?>
	                    </select>
	                   </div>
	                  </div>
	                  <div class="clearfix"> </div>
	                 </div>
	              </div>
            </div>

            <!-- Fourth Row ends -->
            <!-- Fifth Row starts -->
            <div class="form-group col-sm-2">
		      <label for="edit-name">Age<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="age">
	                     <option value=""><?php $current_age = $profile_data['age'] ?? ''; ?>
</option>
		                    <option value="1">1</option>
		                    <option value="2">2</option>
		                    <option value="3">3</option>
		                    <option value="4">4</option>
		                    <option value="5">5</option>
		                    <option value="6">6</option>
		                    <option value="7">7</option>
		                    <option value="8">8</option>
		                    <option value="9">9</option>
		                    <option value="10">10</option>
		                    <option value="11">11</option>
		                    <option value="12">12</option>
		                    <option value="13">13</option>
		                    <option value="14">14</option>
		                    <option value="15">15</option>
		                    <option value="16">16</option>
		                    <option value="17">17</option>
		                    <option value="18">18</option>
		                    <option value="19">19</option>
		                    <option value="20">20</option>
		                    <option value="21">21</option>
		                    <option value="22">22</option>
		                    <option value="23">23</option>
		                    <option value="24">24</option>
		                    <option value="25">25</option>
		                    <option value="26">26</option>
		                    <option value="27">27</option>
		                    <option value="28">28</option>
		                    <option value="29">29</option>
		                    <option value="30">30</option>
		                    <option value="31">31</option>
	                </select>
			    </div>
		    </div>
             <div class="form-group col-sm-2">
		      <label for="edit-name">Marital status <span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="maritalstatus">
	                    <option value="Single" <?php echo (($profile_data['maritalstatus'] ?? '') == 'Single') ? 'selected' : ''; ?>>Single</option>
	                    <option value="Married" <?php echo (($profile_data['maritalstatus'] ?? '') == 'Married') ? 'selected' : ''; ?>>Married</option> 
	               		<option value="Divorsed" <?php echo (($profile_data['maritalstatus'] ?? '') == 'Divorsed') ? 'selected' : ''; ?>>Divorsed</option>
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Profile Created by <span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="profileby">
	                    <option value="Self" <?php echo (($profile_data['profileby'] ?? '') == 'Self') ? 'selected' : ''; ?>>Self</option>
	                    <option value="Son/Daughter" <?php echo (($profile_data['profileby'] ?? '') == 'Son/Daughter') ? 'selected' : ''; ?>>Son/Daughter</option> 
	               		<option value="Other" <?php echo (($profile_data['profileby'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Education <span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="education">
	                    <option value="Primary" <?php echo (($profile_data['education'] ?? '') == 'Primary') ? 'selected' : ''; ?>>Primary</option>
	                    <option value="Tenth level" <?php echo (($profile_data['education'] ?? '') == 'Tenth level') ? 'selected' : ''; ?>>Tenth level</option> 
	               		<option value="+2" <?php echo (($profile_data['education'] ?? '') == '+2') ? 'selected' : ''; ?>>+2</option> 
	               		<option value="Degree" <?php echo (($profile_data['education'] ?? '') == 'Degree') ? 'selected' : ''; ?>>Degree</option> 
	               		<option value="PG" <?php echo (($profile_data['education'] ?? '') == 'PG') ? 'selected' : ''; ?>>PG</option> 
	               		<option value="Doctorate" <?php echo (($profile_data['education'] ?? '') == 'Doctorate') ? 'selected' : ''; ?>>Doctorate</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Specialization <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="edudescr" value="<?php echo htmlspecialchars($profile_data['edudescr'] ?? ''); ?>" size="60" maxlength="60" class="form-text">
		    </div>
		     <div class="form-group col-sm-2">
		      <label for="edit-name">Body type<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="bodytype">
	                    <option value="Slim" <?php echo (($profile_data['bodytype'] ?? '') == 'Slim') ? 'selected' : ''; ?>>Slim</option>
	                    <option value="Fat" <?php echo (($profile_data['bodytype'] ?? '') == 'Fat') ? 'selected' : ''; ?>>Fat</option> 
	               		<option value="Average" <?php echo (($profile_data['bodytype'] ?? '') == 'Average') ? 'selected' : ''; ?>>Average</option> 
	                </select>
			    </div>
		    </div>
		     <div class="form-group col-sm-2">
		      <label for="edit-name">Physical Status<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="physicalstatus">
	                    <option value="No Problem" <?php echo (($profile_data['physicalstatus'] ?? '') == 'No Problem') ? 'selected' : ''; ?>>No Problem</option>
	                    <option value="Blind" <?php echo (($profile_data['physicalstatus'] ?? '') == 'Blind') ? 'selected' : ''; ?>>Blind</option> 
	               		<option value="Deaf" <?php echo (($profile_data['physicalstatus'] ?? '') == 'Deaf') ? 'selected' : ''; ?>>Deaf</option> 
	                </select>
			    </div>
		    </div>
            <!-- Fifth Row ends -->
            <!-- sixth Row starts-->
            <div class="form-group col-sm-2">
		      <label for="edit-name">Drinks<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="drink">
	                    <option value="No" <?php echo (($profile_data['drink'] ?? '') == 'No') ? 'selected' : ''; ?>>No</option>
	                    <option value="Yes" <?php echo (($profile_data['drink'] ?? '') == 'Yes') ? 'selected' : ''; ?>>Yes</option> 
	               		<option value="Sometimes" <?php echo (($profile_data['drink'] ?? '') == 'Sometimes') ? 'selected' : ''; ?>>Sometimes</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Smoke<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="smoke">
	                    <option value="No" <?php echo (($profile_data['smoke'] ?? '') == 'No') ? 'selected' : ''; ?>>No</option>
	                    <option value="Yes" <?php echo (($profile_data['smoke'] ?? '') == 'Yes') ? 'selected' : ''; ?>>Yes</option> 
	               		<option value="Sometimes" <?php echo (($profile_data['smoke'] ?? '') == 'Sometimes') ? 'selected' : ''; ?>>Sometimes</option>
	                </select>
			    </div>
		    </div>
		    
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Mother Tounge<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="mothertounge">
	                    <option value="Malayalam" <?php echo (($profile_data['mothertounge'] ?? '') == 'Malayalam') ? 'selected' : ''; ?>>Malayalam</option>
	                    <option value="Hindi" <?php echo (($profile_data['mothertounge'] ?? '') == 'Hindi') ? 'selected' : ''; ?>>Hindi</option> 
	               		<option value="English" <?php echo (($profile_data['mothertounge'] ?? '') == 'English') ? 'selected' : ''; ?>>English</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Blood Group<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="bloodgroup">
	                    <option value="O +ve" <?php echo (($profile_data['bloodgroup'] ?? '') == 'O +ve') ? 'selected' : ''; ?>>O +ve</option>
	                    <option value="O -ve" <?php echo (($profile_data['bloodgroup'] ?? '') == 'O -ve') ? 'selected' : ''; ?>>O -ve</option> 
	               		<option value="AB -ve" <?php echo (($profile_data['bloodgroup'] ?? '') == 'AB -ve') ? 'selected' : ''; ?>>AB -ve</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Weight <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="weight" value="<?php echo htmlspecialchars($profile_data['weight'] ?? ''); ?>" size="60" maxlength="60" class="form-text">
		    </div>
		    <!-- sixth Row ends-->
		    <!-- Seventh Row starts-->
		    <div class="col-lg-12">
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Height <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="height" value="<?php echo htmlspecialchars($profile_data['height'] ?? ''); ?>" size="60" maxlength="60" class="form-text">
		    </div>
		   	<div class="form-group col-sm-2">
		      <label for="edit-name">Colour<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="colour">
	                    <option value="Dark" <?php echo (($profile_data['colour'] ?? '') == 'Dark') ? 'selected' : ''; ?>>Dark</option>
	                    <option value="Fair" <?php echo (($profile_data['colour'] ?? '') == 'Fair') ? 'selected' : ''; ?>>Fair</option> 
	               		<option value="Normal" <?php echo (($profile_data['colour'] ?? '') == 'Normal') ? 'selected' : ''; ?>>Normal</option> 
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Diet<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="diet">
	                    <option value="Veg" <?php echo (($profile_data['diet'] ?? '') == 'Veg') ? 'selected' : ''; ?>>Veg</option>
	                    <option value="Non Veg" <?php echo (($profile_data['diet'] ?? '') == 'Non Veg') ? 'selected' : ''; ?>>Non Veg</option> 
	               		
	                </select>
			    </div>
		    </div>
		     <div class="form-group col-sm-2">
		      <label for="edit-name">Occupation <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="occupation" value="<?php echo htmlspecialchars($profile_data['occupation'] ?? ''); ?>" size="60" maxlength="60" class="form-text">
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Occupation Descr <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="occupationdescr" value="<?php echo htmlspecialchars($profile_data['occupationdescr'] ?? ''); ?>" size="130" maxlength="120" class="form-text">
		    </div>
		    <div class="form-group col-sm-2">
		      <label for="edit-name">Annual Income <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="income" value="<?php echo htmlspecialchars($profile_data['income'] ?? ''); ?>" size="60" maxlength="60" class="form-text">
		    </div>
		   
		   
		    
</div>


             <!-- Seventh Row ends-->
  
           <!-- eighth Row starts-->
           <div class="col-lg-12">
            <div class="form-group col-sm-3">
		    		<label for="edit-name">Fathers Occupation <span class="form-required" title="This field is required."></span></label>
			  		<input type="text" id="edit-name" name="fatheroccupation" value="<?php echo htmlspecialchars($profile_data['fatheroccupation'] ?? ''); ?>" size="60" maxlength="500" class="form-text">
		   </div>
           <div class="form-group col-sm-3">
		      <label for="edit-name">Mothers Occupation <span class="form-required" title="This field is required."></span></label>
			  <input type="text" id="edit-name" name="motheroccupation" value="<?php echo htmlspecialchars($profile_data['motheroccupation'] ?? ''); ?>" size="60" maxlength="500" class="form-text">
		    </div>
		    
          <div class="form-group col-sm-3">
		      <label for="edit-name">No . Of sisters<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="sis">
	                    <option value="1" <?php echo (($profile_data['sis'] ?? '') == '1') ? 'selected' : ''; ?>>1</option>
	                    <option value="2" <?php echo (($profile_data['sis'] ?? '') == '2') ? 'selected' : ''; ?>>2</option> 
	                    <option value="3" <?php echo (($profile_data['sis'] ?? '') == '3') ? 'selected' : ''; ?>>3</option> 
	                    <option value="4" <?php echo (($profile_data['sis'] ?? '') == '4') ? 'selected' : ''; ?>>4</option> 
	                    <option value="5" <?php echo (($profile_data['sis'] ?? '') == '5') ? 'selected' : ''; ?>>5</option> 	
	                </select>
			    </div>
		    </div>
		    <div class="form-group col-sm-3">
		      <label for="edit-name">No . Of brothers<span class="form-required" title="This field is required.">*</span></label>
			    <div class="select-block1">
	                <select name="bros">
	                    <option value="1" <?php echo (($profile_data['bros'] ?? '') == '1') ? 'selected' : ''; ?>>1</option>
	                    <option value="2" <?php echo (($profile_data['bros'] ?? '') == '2') ? 'selected' : ''; ?>>2</option> 
	                    <option value="3" <?php echo (($profile_data['bros'] ?? '') == '3') ? 'selected' : ''; ?>>3</option> 
	                    <option value="4" <?php echo (($profile_data['bros'] ?? '') == '4') ? 'selected' : ''; ?>>4</option> 
	                    <option value="5" <?php echo (($profile_data['bros'] ?? '') == '5') ? 'selected' : ''; ?>>5</option> 	
	                </select>
			    </div>
		    </div>
		    <div class="form-group">
		    	<label for="about me">About Me<span class="form-required" title="This field is required.">*</span></label>
		    	<textarea rows="5" name="aboutme" placeholder="Write about you" class="form-text"><?php echo htmlspecialchars($profile_data['aboutme'] ?? ''); ?></textarea>
		    </div>
		    <div class="form-actions">} else{
			    <input type="submit" id="edit-submit" name="op" value="Submit" class="btn_1 submit">
			  </div>
			  </div>
             <!-- eighth Row ends-->
         <hr/>
			  

		 </form>
	  </div>
	 
	  <div class="clearfix"> </div>
   </div>
  </div>
</div>


<?php include_once("footer.php");?>

<script>
// Cascading dropdown for State -> City
jQuery(document).ready(function($) {
    var allCities = $('#edit-city option').clone();
    var allCastes = $('#edit-caste option').clone();
    
    // Pre-select existing values
    <?php if (!empty($profile_data)): ?>
        $('select[name="day"]').val('<?php echo $profile_data['day'] ?? ''; ?>');
        $('select[name="month"]').val('<?php echo $profile_data['month'] ?? ''; ?>');
        $('select[name="year"]').val('<?php echo $profile_data['year'] ?? ''; ?>');
        $('select[name="age"]').val('<?php echo $profile_data['age'] ?? ''; ?>');
        $('select[name="religion"]').val('<?php echo $profile_data['religion'] ?? ''; ?>').trigger('change');
        $('select[name="subcaste"]').val('<?php echo $profile_data['subcaste'] ?? ''; ?>');
        $('select[name="country"]').val('<?php echo $profile_data['country'] ?? ''; ?>');
        $('select[name="state"]').val('<?php echo $profile_data['state'] ?? ''; ?>').trigger('change');
        
        // Pre-select caste and district after cascading filters load
        setTimeout(function() {
            $('select[name="caste"]').val('<?php echo $profile_data['caste'] ?? ''; ?>');
            $('select[name="district"]').val('<?php echo $profile_data['district'] ?? ''; ?>');
        }, 100);
    <?php endif; ?>
    
    $('#edit-state').on('change', function() {
        var selectedStateId = $(this).find(':selected').data('state-id');
        
        // Reset city dropdown
        $('#edit-city').empty().append('<option value="">Select City</option>');
        
        if (selectedStateId) {
            // Filter cities by selected state
            allCities.each(function() {
                var $option = $(this);
                if ($option.val() === '' || $option.data('state-id') == selectedStateId) {
                    $('#edit-city').append($option.clone());
                }
            });
        } else {
            // No state selected, show all cities
            $('#edit-city').append(allCities.clone());
        }
    });
    
    $('#edit-religion').on('change', function() {
        var selectedReligion = $(this).val();
        
        // Reset caste dropdown
        $('#edit-caste').empty().append('<option value="">Select Caste</option>');
        
        if (selectedReligion) {
            // Filter castes by selected religion
            allCastes.each(function() {
                var $option = $(this);
                if ($option.val() === '' || $option.data('religion') === selectedReligion) {
                    $('#edit-caste').append($option.clone());
                }
            });
        } else {
            // No religion selected, show all castes
            $('#edit-caste').append(allCastes.clone());
        }
    });
});
</script>

</body>
</html>	