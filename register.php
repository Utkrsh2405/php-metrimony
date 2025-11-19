<?php include_once("includes/basic_includes.php");?>
<?php include_once("functions.php"); ?>
<?php include_once("includes/dbconn.php"); ?>
<?php register(); ?>
<?php
// Fetch data from database for dropdowns
$states_query = mysqli_query($conn, "SELECT id, name FROM states WHERE status = 1 ORDER BY name");
$states = [];
$states_map = [];
while($state = mysqli_fetch_assoc($states_query)) {
    $states[] = $state['name'];
    $states_map[$state['name']] = $state['id'];
}

$cities_query = mysqli_query($conn, "SELECT name, state_id FROM cities WHERE status = 1 ORDER BY name");
$cities_list = [];
while($city = mysqli_fetch_assoc($cities_query)) {
    $cities_list[] = $city;
}

$religions_query = mysqli_query($conn, "SELECT DISTINCT religion FROM castes WHERE status = 1 AND religion IS NOT NULL ORDER BY religion");
$religions = [];
while($rel = mysqli_fetch_assoc($religions_query)) {
    if(!in_array($rel['religion'], $religions)) {
        $religions[] = $rel['religion'];
    }
}

$castes_query = mysqli_query($conn, "SELECT DISTINCT name, religion FROM castes WHERE status = 1 ORDER BY name");
$castes_list = [];
while($caste = mysqli_fetch_assoc($castes_query)) {
    $castes_list[] = $caste;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Find Your Perfect Partner - Matrimony
 | Register :: Matrimony
</title>
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
   <div class="breadcrumb1">
     <ul>
        <a href="index.php"><i class="fa fa-home home_1"></i></a>
        <span class="divider">&nbsp;|&nbsp;</span>
        <li class="current-page">Register</li>
     </ul>
   </div>
   <div class="services">
   	  <div class="col-sm-8 col-sm-offset-2 login_left">
	     <?php 
	     // Display registration result if form was submitted
	     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	         // The register() function output will appear here
	     }
	     ?>
	     <form action="" method="POST">
	     	<!-- Email -->
	  	    <div class="form-group">
		      <label for="edit-email">Email <span class="form-required" title="This field is required.">*</span></label>
		      <div class="input-group">
		        <input type="email" id="edit-email" name="email" value="" size="60" maxlength="60" class="form-control required" required>
		        <span class="input-group-btn">
		          <button class="btn btn-danger" type="button">Check Availability</button>
		        </span>
		      </div>
		    </div>
		    
		    <!-- Password -->
		    <div class="form-group">
		      <label for="edit-pass">Choose Password <span class="form-required" title="This field is required.">*</span></label>
		      <input type="password" id="edit-pass" name="pass" size="60" maxlength="128" class="form-control required" required minlength="6">
		    </div>
		    
		    <!-- Confirm Password -->
		    <div class="form-group">
		      <label for="edit-pass-confirm">Confirm Password <span class="form-required" title="This field is required.">*</span></label>
		      <input type="password" id="edit-pass-confirm" name="pass_confirm" size="60" maxlength="128" class="form-control required" required minlength="6">
		    </div>
		    
		    <!-- Name of Bride/Groom -->
		    <div class="form-group">
		      <label for="edit-name">Name of Bride / Groom <span class="form-required" title="This field is required.">*</span></label>
		      <input type="text" id="edit-name" name="name" value="" size="60" maxlength="60" class="form-control required" required>
		    </div>
		    
		    <!-- Gender -->
		    <div class="form-group">
                <label>Gender <span class="form-required">*</span></label>
                <div class="radios">
			        <label for="radio-01" class="label_radio">
			            <input type="radio" id="radio-01" name="gender" value="Male" checked> Male
			        </label>
			        <label for="radio-02" class="label_radio">
			            <input type="radio" id="radio-02" name="gender" value="Female"> Female
			        </label>
	            </div>
             </div>
             
             <!-- Height -->
             <div class="form-group">
		      <label for="edit-height">Height <span class="form-required">*</span></label>
		      <select id="edit-height" name="height" class="form-control" required>
		        <option value="">--- Select Height ---</option>
		        <option value="4.0">4'0" - 122 cm</option>
		        <option value="4.1">4'1" - 124 cm</option>
		        <option value="4.2">4'2" - 127 cm</option>
		        <option value="4.3">4'3" - 130 cm</option>
		        <option value="4.4">4'4" - 132 cm</option>
		        <option value="4.5">4'5" - 135 cm</option>
		        <option value="4.6">4'6" - 137 cm</option>
		        <option value="4.7">4'7" - 140 cm</option>
		        <option value="4.8">4'8" - 142 cm</option>
		        <option value="4.9">4'9" - 145 cm</option>
		        <option value="4.10">4'10" - 147 cm</option>
		        <option value="4.11">4'11" - 150 cm</option>
		        <option value="5.0">5'0" - 152 cm</option>
		        <option value="5.1">5'1" - 155 cm</option>
		        <option value="5.2">5'2" - 157 cm</option>
		        <option value="5.3">5'3" - 160 cm</option>
		        <option value="5.4">5'4" - 163 cm</option>
		        <option value="5.5">5'5" - 165 cm</option>
		        <option value="5.6">5'6" - 168 cm</option>
		        <option value="5.7">5'7" - 170 cm</option>
		        <option value="5.8">5'8" - 173 cm</option>
		        <option value="5.9">5'9" - 175 cm</option>
		        <option value="5.10">5'10" - 178 cm</option>
		        <option value="5.11">5'11" - 180 cm</option>
		        <option value="6.0">6'0" - 183 cm</option>
		        <option value="6.1">6'1" - 185 cm</option>
		        <option value="6.2">6'2" - 188 cm</option>
		        <option value="6.3">6'3" - 191 cm</option>
		        <option value="6.4">6'4" - 193 cm</option>
		        <option value="6.5">6'5" - 196 cm</option>
		        <option value="6.6">6'6" - 198 cm</option>
		        <option value="6.7">6'7" - 201 cm</option>
		      </select>
		    </div>
		    
		    <!-- Mother Tongue -->
		    <div class="form-group">
		      <label for="edit-mother-tongue">Mother Tongue <span class="form-required">*</span></label>
		      <select id="edit-mother-tongue" name="mother_tongue" class="form-control" required>
		        <option value="">Select Mother Tongue</option>
		        <option value="Hindi">Hindi</option>
		        <option value="English">English</option>
		        <option value="Bengali">Bengali</option>
		        <option value="Telugu">Telugu</option>
		        <option value="Marathi">Marathi</option>
		        <option value="Tamil">Tamil</option>
		        <option value="Gujarati">Gujarati</option>
		        <option value="Urdu">Urdu</option>
		        <option value="Kannada">Kannada</option>
		        <option value="Malayalam">Malayalam</option>
		        <option value="Punjabi">Punjabi</option>
		        <option value="Odia">Odia</option>
		        <option value="Assamese">Assamese</option>
		        <option value="Maithili">Maithili</option>
		        <option value="Santali">Santali</option>
		        <option value="Kashmiri">Kashmiri</option>
		        <option value="Nepali">Nepali</option>
		        <option value="Sindhi">Sindhi</option>
		        <option value="Konkani">Konkani</option>
		        <option value="Dogri">Dogri</option>
		        <option value="Manipuri">Manipuri</option>
		        <option value="Bodo">Bodo</option>
		        <option value="Sanskrit">Sanskrit</option>
		        <option value="Rajasthani">Rajasthani</option>
		        <option value="Bhojpuri">Bhojpuri</option>
		        <option value="Haryanvi">Haryanvi</option>
		        <option value="Chhattisgarhi">Chhattisgarhi</option>
		        <option value="Magahi">Magahi</option>
		        <option value="Marwari">Marwari</option>
		        <option value="Tulu">Tulu</option>
		        <option value="Kodava">Kodava</option>
		        <option value="Other">Other</option>
		      </select>
		    </div>
		    
		    <!-- Date of Birth -->
		    <div class="age_select">
		      <label for="edit-dob">Date of Birth <span class="form-required" title="This field is required.">*</span></label>
		        <div class="age_grid">
		         <div class="col-sm-4 form_box">
                  <div class="select-block1">
                    <select name="day" class="form-control" required>
	                    <option value="">Date</option>
	                    <?php for($i=1; $i<=31; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-4 form_box">
                    <div class="select-block1">
                        <select name="month" class="form-control" required>
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
                    <select name="year" class="form-control" required>
	                    <option value="">Year</option>
	                    <?php for($y=1970; $y<=2007; $y++) { echo "<option value='$y'>$y</option>"; } ?>
                    </select>
                   </div>
                  </div>
                  <div class="clearfix"> </div>
                 </div>
              </div>
              
             <!-- Religion -->
              <div class="form-group">
		      <label for="edit-religion">Religion <span class="form-required">*</span></label>
		      <select id="edit-religion" name="religion" class="form-control" required>
		        <option value="">--- Select Religion ---</option>
		        <?php foreach($religions as $religion): ?>
		        <option value="<?= htmlspecialchars($religion) ?>"><?= htmlspecialchars($religion) ?></option>
		        <?php endforeach; ?>
		        <option value="Other">Other</option>
		      </select>
		    </div>
		    
		    <!-- Caste -->
		    <div class="form-group">
		      <label for="edit-caste">Caste <span class="form-required">*</span></label>
		      <select id="edit-caste" name="caste" class="form-control" required>
		        <option value="">--- Select Caste ---</option>
		        <?php foreach($castes_list as $caste): ?>
		        <option value="<?= htmlspecialchars($caste['name']) ?>" data-religion="<?= htmlspecialchars($caste['religion']) ?>"><?= htmlspecialchars($caste['name']) ?></option>
		        <?php endforeach; ?>
		        <option value="Other">Other</option>
		        <option value="Caste No Bar">Caste No Bar</option>
		      </select>
		    </div>
		    
		    <!-- Caste no bar -->
		    <div class="form-group">
                <label>
                    <input type="checkbox" name="caste_no_bar" value="1"> Caste no Bar
                </label>
             </div>
             
             <!-- Sub Caste -->
             <div class="form-group">
		      <label for="edit-sub-caste">Sub Caste</label>
		      <input type="text" id="edit-sub-caste" name="sub_caste" placeholder="Sub Caste(optional)" class="form-control">
		    </div>
		    
		    <!-- Marital Status -->
		    <div class="form-group">
                <label>Marital Status <span class="form-required">*</span></label>
                <div class="radios">
			        <label class="label_radio">
			            <input type="radio" name="marital_status" value="Never Married" checked> Never Married
			        </label>
			        <label class="label_radio">
			            <input type="radio" name="marital_status" value="Divorced"> Divorced
			        </label>
			        <label class="label_radio">
			            <input type="radio" name="marital_status" value="Widowed"> Widowed
			        </label>
			        <label class="label_radio">
			            <input type="radio" name="marital_status" value="Separated"> Separated
			        </label>
	            </div>
             </div>
             
             <!-- No of Children -->
             <div class="form-group">
		      <label for="edit-children">No of Children</label>
		      <select id="edit-children" name="no_of_children" class="form-control">
		        <option value="0">No Children</option>
		        <option value="1">1</option>
		        <option value="2">2</option>
		        <option value="3">3</option>
		        <option value="4">4</option>
		        <option value="5+">5+</option>
		      </select>
		    </div>
		    
		    <!-- Living with me -->
		    <div class="form-group">
                <div class="radios">
			        <label class="label_radio">
			            <input type="radio" name="children_living" value="Living with me"> Living with me
			        </label>
			        <label class="label_radio">
			            <input type="radio" name="children_living" value="Not living with me"> Not living with me
			        </label>
	            </div>
             </div>
             
             <!-- Country -->
             <div class="form-group">
		      <label for="edit-country">Country <span class="form-required">*</span></label>
		      <select id="edit-country" name="country" class="form-control" required>
		        <option value="India">India</option>
		        <option value="USA">USA</option>
		        <option value="UK">UK</option>
		        <option value="Canada">Canada</option>
		        <option value="Australia">Australia</option>
		        <option value="UAE">UAE</option>
		        <option value="Saudi Arabia">Saudi Arabia</option>
		        <option value="Qatar">Qatar</option>
		        <option value="Kuwait">Kuwait</option>
		        <option value="Oman">Oman</option>
		        <option value="Bahrain">Bahrain</option>
		        <option value="Singapore">Singapore</option>
		        <option value="Malaysia">Malaysia</option>
		        <option value="New Zealand">New Zealand</option>
		        <option value="Germany">Germany</option>
		        <option value="France">France</option>
		        <option value="Italy">Italy</option>
		        <option value="Netherlands">Netherlands</option>
		        <option value="Switzerland">Switzerland</option>
		        <option value="South Africa">South Africa</option>
		        <option value="Other">Other</option>
		      </select>
		    </div>
		    
		    <!-- Select State -->
		    <div class="form-group">
		      <label for="edit-state">Select State <span class="form-required">*</span></label>
		      <select id="edit-state" name="state" class="form-control" required>
		        <option value="">--- Select State ---</option>
		        <?php foreach($states as $state): ?>
		        <option value="<?= htmlspecialchars($state) ?>" data-state-id="<?= $states_map[$state] ?>"><?= htmlspecialchars($state) ?></option>
		        <?php endforeach; ?>
		      </select>
		    </div>
		    
		    <!-- City -->
		    <div class="form-group">
		      <label for="edit-city">City <span class="form-required">*</span></label>
		      <select id="edit-city" name="city" class="form-control" required>
		        <option value="">--- Select City ---</option>
		        <?php foreach($cities_list as $city): ?>
		        <option value="<?= htmlspecialchars($city['name']) ?>" data-state-id="<?= $city['state_id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
		        <?php endforeach; ?>
		        <option value="Other">Other</option>
		      </select>
		    </div>
		    
		    <!-- Contact Address -->
		    <div class="form-group">
		      <label for="edit-address">Contact Address <span class="form-required">*</span></label>
		      <textarea id="edit-address" name="address" rows="3" class="form-control" required></textarea>
		    </div>
		    
		    <!-- Phone No -->
		    <div class="form-group">
		      <label for="edit-phone">Phone No.</label>
		      <div class="row">
		        <div class="col-xs-3">
		          <input type="text" name="phone_code" value="91" class="form-control" placeholder="Code">
		        </div>
		        <div class="col-xs-9">
		          <input type="tel" id="edit-phone" name="phone" class="form-control" placeholder="Phone Number">
		        </div>
		      </div>
		    </div>
		    
		    <!-- Mobile -->
		    <div class="form-group">
		      <label for="edit-mobile">Mobile <span class="form-required">*</span></label>
		      <input type="tel" id="edit-mobile" name="mobile" class="form-control" required pattern="[0-9]{10}" placeholder="10 digit mobile number">
		    </div>
		    
		    <!-- Citizenship -->
		    <div class="form-group">
		      <label for="edit-citizenship">Citizenship</label>
		      <select id="edit-citizenship" name="citizenship" class="form-control">
		        <option value="India">India</option>
		        <option value="USA">USA</option>
		        <option value="UK">UK</option>
		        <option value="Canada">Canada</option>
		        <option value="Australia">Australia</option>
		        <option value="Other">Other</option>
		      </select>
		    </div>
		    
		    <!-- NRI -->
		    <div class="form-group">
                <label>NRI</label>
                <div class="radios">
			        <label class="label_radio">
			            <input type="radio" name="nri" value="Yes"> Yes
			        </label>
			        <label class="label_radio">
			            <input type="radio" name="nri" value="No" checked> Not
			        </label>
	            </div>
             </div>
             
             <!-- reCAPTCHA -->
             <div class="form-group">
		      <div class="g-recaptcha" data-sitekey="your-site-key"></div>
		    </div>
		    
		    <!-- Terms & Conditions -->
		    <div class="form-group">
                <label>
                    <input type="checkbox" name="terms" value="1" required> 
                    Please tick this box to indicate that you have read and agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> of Service
                </label>
             </div>
			  
			  <div class="form-actions text-center">
			    <input type="submit" id="edit-submit" name="op" value="Register Now" class="btn btn-danger btn-lg">
			  </div>
		 </form>
	  </div>
	  <div class="clearfix"> </div>
   </div>
  </div>
</div>


<script>
// Cascading dropdown for State -> City
jQuery(document).ready(function($) {
    var allCities = $('#edit-city option').clone();
    
    $('#edit-state').on('change', function() {
        var selectedStateId = $(this).find(':selected').data('state-id');
        
        // Reset city dropdown
        $('#edit-city').empty().append('<option value="">--- Select City ---</option>');
        
        if (selectedStateId) {
            // Filter cities by selected state
            allCities.each(function() {
                var $option = $(this);
                if ($option.val() === '' || $option.val() === 'Other' || $option.data('state-id') == selectedStateId) {
                    $('#edit-city').append($option.clone());
                }
            });
        } else {
            // No state selected, show all cities
            $('#edit-city').append(allCities.clone());
        }
    });
    
    // Cascading dropdown for Religion -> Caste
    var allCastes = $('#edit-caste option').clone();
    
    $('#edit-religion').on('change', function() {
        var selectedReligion = $(this).val();
        
        // Reset caste dropdown
        $('#edit-caste').empty().append('<option value="">--- Select Caste ---</option>');
        
        if (selectedReligion) {
            // Filter castes by selected religion
            allCastes.each(function() {
                var $option = $(this);
                if ($option.val() === '' || $option.val() === 'Other' || $option.val() === 'Caste No Bar' || 
                    $option.data('religion') === selectedReligion) {
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

<?php include_once("footer.php");?>

