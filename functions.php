<?php
function mysqlexec($sql){
	// HOSTINGER CREDENTIALS - ACTIVE
	$host = "localhost";
	$username = "u166093127_dbuser";
	$password = "Uttu@2005";
	$db_name = "u166093127_matrimony";

	// Connect to server and select database.
	$conn = mysqli_connect($host, $username, $password) or die("Cannot connect: " . mysqli_connect_error());
	mysqli_set_charset($conn, "utf8mb4");
	mysqli_select_db($conn, $db_name) or die("Cannot select DB '" . $db_name . "': " . mysqli_error($conn));

	if ($result = mysqli_query($conn, $sql)){
		return $result;
	} else {
		echo mysqli_error($conn);
	}

}
function searchid(){
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$profid=$_POST['profid'];
		$sql="SELECT * FROM customer WHERE id=$profid";
		$result = mysqlexec($sql);
    	return $result;
	}
}

function search(){
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $agemin=$_POST['agemin'];
    $agemax=$_POST['agemax'];
    $maritalstatus=$_POST['maritalstatus'];
    $country=$_POST['country'];
    $state=$_POST['state'];
    $religion=$_POST['religion'];
    $mothertounge=$_POST['mothertounge'];
    $sex = $_POST['sex'];

    $sql="SELECT * FROM customer WHERE 
    sex='$sex' 
    AND age>='$agemin'
    AND age<='$agemax'
    AND maritalstatus = '$maritalstatus'
    AND country = '$country'
    AND state = '$state'
    AND religion = '$religion'
    AND mothertounge = '$mothertounge'
    ";

    $result = mysqlexec($sql);
    return $result;

  }
}
function writepartnerprefs($id){
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		require_once("includes/dbconn.php");
		
		// Sanitize ID
		$id = intval($id);
		
		// Sanitize all POST inputs
		$agemin = intval($_POST['agemin'] ?? 0);
		$agemax = intval($_POST['agemax'] ?? 0);
		$maritalstatus = mysqli_real_escape_string($conn, $_POST['maritalstatus'] ?? '');
		$complexion = mysqli_real_escape_string($conn, $_POST['colour'] ?? '');
		$height = mysqli_real_escape_string($conn, $_POST['height'] ?? '');
		$diet = mysqli_real_escape_string($conn, $_POST['diet'] ?? '');
		$religion = mysqli_real_escape_string($conn, $_POST['religion'] ?? '');
		$caste = mysqli_real_escape_string($conn, $_POST['caste'] ?? '');
		$mothertounge = mysqli_real_escape_string($conn, $_POST['mothertounge'] ?? '');
		$education = mysqli_real_escape_string($conn, $_POST['education'] ?? '');
		$occupation = mysqli_real_escape_string($conn, $_POST['occupation'] ?? '');
		$country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');
		$descr = mysqli_real_escape_string($conn, $_POST['descr'] ?? '');

		$sql = "UPDATE partnerprefs 
				SET
				   agemin = $agemin,
				   agemax = $agemax,
				   maritalstatus = '$maritalstatus',
				   complexion = '$complexion',
				   height = '$height',
				   diet = '$diet',
				   religion = '$religion',
				   caste = '$caste',
				   mothertounge = '$mothertounge',
				   education = '$education',
				   descr = '$descr',
				   occupation = '$occupation',
				   country = '$country' 
				WHERE custId = $id";

		$result = mysqlexec($sql);
		if ($result) {
			echo "<script>alert(\"Successfully updated Partner Preference\")</script>";
			echo "<script> window.location=\"userhome.php?id=$id\"</script>";

		}
		else{
			echo "Error";
		}

	}
}
function register(){
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require_once("includes/dbconn.php");
	global $conn; // Make database connection available in function scope
	
	// Enable detailed error reporting for this function
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	// Collect and sanitize all form inputs
	$uname = mysqli_real_escape_string($conn, trim($_POST['name']));
	$pass = $_POST['pass'];
	$pass_confirm = $_POST['pass_confirm'] ?? '';
	$email = mysqli_real_escape_string($conn, trim($_POST['email']));
	
	// Date of birth
	$day = $_POST['day'] ?? '';
	$month = $_POST['month'] ?? '';
	$year = $_POST['year'] ?? '';
	$dob = $year . "-" . $month . "-" . $day;
	
	// Calculate age
	$age = date('Y') - $year;
	
	// Basic info
	$gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? 'Male');
	$height_raw = $_POST['height'] ?? '0';
	// Convert height from decimal (5.6) to integer for storage (multiply by 10: 5.6 -> 56)
	$height = (int)(floatval($height_raw) * 10);
	$mother_tongue = mysqli_real_escape_string($conn, $_POST['mother_tongue'] ?? '');
	
	// Religion & Caste
	$religion = mysqli_real_escape_string($conn, $_POST['religion'] ?? '');
	$caste = mysqli_real_escape_string($conn, $_POST['caste'] ?? '');
	$sub_caste = mysqli_real_escape_string($conn, $_POST['sub_caste'] ?? '');
	
	// Marital status
	$marital_status = mysqli_real_escape_string($conn, $_POST['marital_status'] ?? 'Never Married');
	$no_of_children = mysqli_real_escape_string($conn, $_POST['no_of_children'] ?? '0');
	$children_living = mysqli_real_escape_string($conn, $_POST['children_living'] ?? '');
	
	// Location
	$country = mysqli_real_escape_string($conn, $_POST['country'] ?? 'India');
	$state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
	$city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
	$address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
	
	// Contact
	$phone_code = mysqli_real_escape_string($conn, $_POST['phone_code'] ?? '91');
	$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
	$mobile = mysqli_real_escape_string($conn, $_POST['mobile'] ?? '');
	
	// Additional fields
	$citizenship = mysqli_real_escape_string($conn, $_POST['citizenship'] ?? 'India');
	$nri = mysqli_real_escape_string($conn, $_POST['nri'] ?? 'No');
	
	// Validate required inputs
	if (empty($uname) || empty($pass) || empty($email)) {
		echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
		return;
	}
	
	// Validate password confirmation
	if ($pass !== $pass_confirm) {
		echo "<div class='alert alert-danger'>Passwords do not match!</div>";
		return;
	}
	
	// Validate password length
	if (strlen($pass) < 6) {
		echo "<div class='alert alert-danger'>Password must be at least 6 characters long.</div>";
		return;
	}
	
	// Validate date of birth
	if (empty($day) || empty($month) || empty($year)) {
		echo "<div class='alert alert-danger'>Please select your complete date of birth.</div>";
		return;
	}
	
	// Validate email format
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo "<div class='alert alert-danger'>Please enter a valid email address.</div>";
		return;
	}
	
	// Check if username already exists
	$check_sql = "SELECT id FROM users WHERE username = '$uname'";
	$check_result = mysqli_query($conn, $check_sql);
	if (mysqli_num_rows($check_result) > 0) {
		echo "<div class='alert alert-danger'>Username already exists. Please choose another.</div>";
		return;
	}
	
	// Check if email already exists in users table
	$check_email_sql = "SELECT id FROM users WHERE email = '$email'";
	$check_email_result = mysqli_query($conn, $check_email_sql);
	if (mysqli_num_rows($check_email_result) > 0) {
		echo "<div class='alert alert-danger'>Email already registered. Please use another email or <a href='login.php'>login</a>.</div>";
		return;
	}
	
	// Check if email already exists in customer table
	$check_customer_email = "SELECT id FROM customer WHERE email = '$email'";
	$check_customer_result = mysqli_query($conn, $check_customer_email);
	if (mysqli_num_rows($check_customer_result) > 0) {
		echo "<div class='alert alert-danger'>Email already registered. Please use another email or <a href='login.php'>login</a>.</div>";
		return;
	}
	
	// Hash the password (using old MD5 to match existing system - should upgrade to bcrypt later)
	$hashed_password = md5($pass);

	// Insert into users table
	$sql = "INSERT INTO users (profilestat, username, password, email, dateofbirth, gender, userlevel) 
			VALUES (0, '$uname', '$hashed_password', '$email', '$dob', '$gender', 0)";

	if (mysqli_query($conn, $sql)) {
		// Get the inserted user ID
		$user_id = mysqli_insert_id($conn);
		
		// Insert into customer table with detailed profile
		$customer_sql = "INSERT INTO customer (
			cust_id, email, age, height, sex, religion, caste, subcaste, 
			district, state, country, maritalstatus, profilecreatedby, 
			education, education_sub, firstname, lastname, body_type, 
			physical_status, drink, mothertounge, colour, weight, 
			blood_group, diet, smoke, dateofbirth, occupation, 
			occupation_descr, annual_income, fathers_occupation, 
			mothers_occupation, no_bro, no_sis, aboutme, profilecreationdate
		) VALUES (
			'$user_id', '$email', '$age', '$height', '$gender', '$religion', 
			'$caste', '$sub_caste', '$city', '$state', '$country', 
			'$marital_status', 'Self', '', '', '$uname', '', '', 
			'', '', '$mother_tongue', '', 0, '', '', '', '$dob', '', 
			'', '', '', '', 0, 0, '', CURDATE()
		)";
		
		if (mysqli_query($conn, $customer_sql)) {
			echo "<div class='alert alert-success' style='margin-bottom: 20px;'>";
			echo "<i class='fa fa-check-circle'></i> <strong>Successfully Registered!</strong><br>";
			echo "Your account has been created with username: <strong>" . htmlspecialchars($uname) . "</strong><br>";
			echo "Email: <strong>" . htmlspecialchars($email) . "</strong><br><br>";
			echo "<a href='login.php' class='btn btn-primary'>Login to Your Account</a>";
			echo "</div>";
		} else {
			// Customer insert failed, remove user record
			mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
			echo "<div class='alert alert-danger'>Error creating profile: " . mysqli_error($conn) . "</div>";
		}
	} else {
		echo "<div class='alert alert-danger'>Error creating account: " . mysqli_error($conn) . "</div>";
	}
}
}

function isloggedin(){
	if(!isset($_SESSION['id'])){
	 	return false;
	}
	else{
		return true;
	}

}


function processprofile_form($id){
	require_once("includes/dbconn.php");
	
	// Sanitize ID
	$id = intval($id);
	
	// Sanitize all POST inputs
	$fname = mysqli_real_escape_string($conn, $_POST['fname'] ?? '');
	$lname = mysqli_real_escape_string($conn, $_POST['lname'] ?? '');
	$sex = mysqli_real_escape_string($conn, $_POST['sex'] ?? '');
	$email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
	
	$day = mysqli_real_escape_string($conn, $_POST['day'] ?? '');
	$month = mysqli_real_escape_string($conn, $_POST['month'] ?? '');
	$year = mysqli_real_escape_string($conn, $_POST['year'] ?? '');
	$dob = $year . "-" . $month . "-" . $day;
	
	$religion = mysqli_real_escape_string($conn, $_POST['religion'] ?? '');
	$caste = mysqli_real_escape_string($conn, $_POST['caste'] ?? '');
	$subcaste = mysqli_real_escape_string($conn, $_POST['subcaste'] ?? '');
	
	$country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');
	$state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
	$district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
	$age = intval($_POST['age'] ?? 0);
	$maritalstatus = mysqli_real_escape_string($conn, $_POST['maritalstatus'] ?? '');
	$profileby = mysqli_real_escape_string($conn, $_POST['profileby'] ?? '');
	$education = mysqli_real_escape_string($conn, $_POST['education'] ?? '');
	$edudescr = mysqli_real_escape_string($conn, $_POST['edudescr'] ?? '');
	$bodytype = mysqli_real_escape_string($conn, $_POST['bodytype'] ?? '');
	$physicalstatus = mysqli_real_escape_string($conn, $_POST['physicalstatus'] ?? '');
	$drink = mysqli_real_escape_string($conn, $_POST['drink'] ?? '');
	$smoke = mysqli_real_escape_string($conn, $_POST['smoke'] ?? '');
	$mothertounge = mysqli_real_escape_string($conn, $_POST['mothertounge'] ?? '');
	$bloodgroup = mysqli_real_escape_string($conn, $_POST['bloodgroup'] ?? '');
	$weight = intval($_POST['weight'] ?? 0);
	$height = mysqli_real_escape_string($conn, $_POST['height'] ?? '');
	$colour = mysqli_real_escape_string($conn, $_POST['colour'] ?? '');
	$diet = mysqli_real_escape_string($conn, $_POST['diet'] ?? '');
	$occupation = mysqli_real_escape_string($conn, $_POST['occupation'] ?? '');
	$occupationdescr = mysqli_real_escape_string($conn, $_POST['occupationdescr'] ?? '');
	$fatheroccupation = mysqli_real_escape_string($conn, $_POST['fatheroccupation'] ?? '');
	$motheroccupation = mysqli_real_escape_string($conn, $_POST['motheroccupation'] ?? '');
	$income = mysqli_real_escape_string($conn, $_POST['income'] ?? '');
	$bros = intval($_POST['bros'] ?? 0);
	$sis = intval($_POST['sis'] ?? 0);
	$aboutme = mysqli_real_escape_string($conn, $_POST['aboutme'] ?? '');

	$sql = "SELECT cust_id FROM customer WHERE cust_id = $id";
	$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) >= 1){
	//there is already a profile in this table for loggedin customer
	//update the data
	$sql = "UPDATE customer 
		SET
		   email = '$email',
		   age = $age,
		   sex = '$sex',
		   religion = '$religion',
		   caste = '$caste',
		   subcaste = '$subcaste',
		   district = '$district',
		   state = '$state',
		   country = '$country',
		   maritalstatus = '$maritalstatus',
		   profilecreatedby = '$profileby',
		   education  = '$education',
		   education_sub = '$edudescr',
		   firstname = '$fname',
		   lastname = '$lname',
		   body_type = '$bodytype',
		   physical_status = '$physicalstatus',
		   drink =  '$drink',
		   mothertounge = '$mothertounge',
		   colour = '$colour',
		   weight = $weight,
		   height = '$height',
		   blood_group = '$bloodgroup',
		   diet = '$diet',
		   smoke = '$smoke',
		   dateofbirth = '$dob', 
		   occupation = '$occupation', 
		   occupation_descr = '$occupationdescr', 
		   annual_income = '$income', 
		   fathers_occupation = '$fatheroccupation',
		   mothers_occupation = '$motheroccupation',
		   no_bro = $bros, 
		   no_sis = $sis, 
		   aboutme = '$aboutme'
		WHERE cust_id = $id";
		   
   if (mysqli_query($conn, $sql)) {
   	echo "<script>alert(\"Successfully Updated Profile\")</script>";
   	echo "<script> window.location=\"userhome.php?id=$id\"</script>";
   } else {
   	echo "Error updating profile: " . mysqli_error($conn);
   }
}else{
	//Insert the data
	$sql = "INSERT INTO customer
				   (cust_id, email, age, sex, religion, caste, subcaste, district, state, country, maritalstatus, profilecreatedby, education, education_sub, firstname, lastname, body_type, physical_status, drink, mothertounge, colour, weight, height, blood_group, diet, smoke, dateofbirth, occupation, occupation_descr, annual_income, fathers_occupation, mothers_occupation, no_bro, no_sis, aboutme, profilecreationdate) 
				VALUES
				   ($id, '$email', $age, '$sex', '$religion', '$caste', '$subcaste', '$district', '$state', '$country', '$maritalstatus', '$profileby', '$education', '$edudescr', '$fname', '$lname', '$bodytype', '$physicalstatus', '$drink', '$mothertounge', '$colour', $weight, '$height', '$bloodgroup', '$diet', '$smoke', '$dob', '$occupation', '$occupationdescr', '$income', '$fatheroccupation', '$motheroccupation', $bros, $sis, '$aboutme', CURDATE())";
			
	if (mysqli_query($conn, $sql)) {
	  echo "Successfully Created profile";
	  echo "<a href=\"userhome.php?id={$id}\">";
	  echo "Back to home";
	  echo "</a>";
	  //creating a slot for partner prefernce table for prefs details with cust id
	  $sql2 = "INSERT INTO partnerprefs (custId) VALUES($id)";
	  mysqli_query($conn, $sql2);
	  $sql2 = "UPDATE users SET profilestat = 1 WHERE id = $id";
	  mysqli_query($conn, $sql2);
	} else {
	  echo "Error: " . mysqli_error($conn);
	}
}

	 
}

//function for upload photo

function uploadphoto($id){
	// Sanitize ID
	$id = intval($id);
	if ($id <= 0) {
		die("Invalid user ID");
	}
	
	$target = "profile/" . $id . "/";
	if (!file_exists($target)) {
		mkdir($target, 0777, true);
	}
	
	// Validate and sanitize file uploads
	$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
	$max_size = 5 * 1024 * 1024; // 5MB
	
	$pics = [];
	for ($i = 1; $i <= 4; $i++) {
		$field = 'pic' . $i;
		if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
			// Validate file type
			if (!in_array($_FILES[$field]['type'], $allowed_types)) {
				die("Invalid file type for $field. Only JPG, PNG, and GIF allowed.");
			}
			// Validate file size
			if ($_FILES[$field]['size'] > $max_size) {
				die("File $field is too large. Maximum 5MB allowed.");
			}
			// Sanitize filename
			$filename = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES[$field]['name']));
			$pics[$i] = $filename;
		} else {
			$pics[$i] = '';
		}
	}

	$sql = "SELECT id FROM photos WHERE cust_id = $id";
	$result = mysqlexec($sql);

	//code part to check weather a photo already exists
	if(mysqli_num_rows($result) == 0) {
		// no photo for current user, do stuff...
		$sql = "INSERT INTO photos (cust_id, pic1, pic2, pic3, pic4) VALUES ($id, '" . 
			   mysqli_real_escape_string($GLOBALS['conn'], $pics[1]) . "', '" .
			   mysqli_real_escape_string($GLOBALS['conn'], $pics[2]) . "', '" .
			   mysqli_real_escape_string($GLOBALS['conn'], $pics[3]) . "', '" .
			   mysqli_real_escape_string($GLOBALS['conn'], $pics[4]) . "')";
		mysqlexec($sql);
	} else {
		// There is a photo for customer so update
		$sql = "UPDATE photos SET pic1 = '" . mysqli_real_escape_string($GLOBALS['conn'], $pics[1]) . "', " .
			   "pic2 = '" . mysqli_real_escape_string($GLOBALS['conn'], $pics[2]) . "', " .
			   "pic3 = '" . mysqli_real_escape_string($GLOBALS['conn'], $pics[3]) . "', " .
			   "pic4 = '" . mysqli_real_escape_string($GLOBALS['conn'], $pics[4]) . "' " .
			   "WHERE cust_id = $id";
		mysqlexec($sql);
	}

	// Writes the photo to the server
	$upload_success = true;
	for ($i = 1; $i <= 4; $i++) {
		$field = 'pic' . $i;
		if (!empty($pics[$i]) && isset($_FILES[$field])) {
			$target_file = $target . $pics[$i];
			if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target_file)) {
				$upload_success = false;
			}
		}
	}

	if ($upload_success) {
		echo "The files have been uploaded, and your information has been added to the directory";
	} else {
		echo "Sorry, there was a problem uploading your file.";
	}

}//end uploadphoto function

?>