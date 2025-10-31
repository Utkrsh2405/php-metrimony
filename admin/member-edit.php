<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Verify admin status
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: /index.php");
    exit();
}

// Get member ID
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($member_id == 0) {
    header("Location: /admin/members.php");
    exit();
}

// Get member data
$query = "SELECT u.*, c.* FROM users u 
          LEFT JOIN customer c ON u.id = c.cust_id 
          WHERE u.id = $member_id AND u.userlevel = 0";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: /admin/members.php");
    exit();
}

$member = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $age = intval($_POST['age']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $marital_status = mysqli_real_escape_string($conn, $_POST['marital_status']);
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $caste = mysqli_real_escape_string($conn, $_POST['caste']);
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $income = mysqli_real_escape_string($conn, $_POST['income']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $complexion = mysqli_real_escape_string($conn, $_POST['complexion']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);
    $account_status = mysqli_real_escape_string($conn, $_POST['account_status']);
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    $profile_completeness = intval($_POST['profile_completeness']);
    
    // Update users table
    $update_user = "UPDATE users SET 
                    username = '$username',
                    email = '$email',
                    account_status = '$account_status',
                    profile_completeness = $profile_completeness
                    WHERE id = $member_id";
    
    // Update customer table
    $update_customer = "UPDATE customer SET
                        firstname = '$firstname',
                        lastname = '$lastname',
                        sex = '$sex',
                        age = $age,
                        state = '$state',
                        mobile = '$mobile',
                        marital_status = '$marital_status',
                        religion = '$religion',
                        caste = '$caste',
                        education = '$education',
                        occupation = '$occupation',
                        income = '$income',
                        height = '$height',
                        weight = '$weight',
                        complexion = '$complexion',
                        about = '$about',
                        is_verified = $is_verified
                        WHERE cust_id = $member_id";
    
    if (mysqli_query($conn, $update_user) && mysqli_query($conn, $update_customer)) {
        $success_message = "Member updated successfully!";
        // Refresh member data
        $result = mysqli_query($conn, $query);
        $member = mysqli_fetch_assoc($result);
    } else {
        $error_message = "Error updating member: " . mysqli_error($conn);
    }
}

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <div class="row">
        <div class="col-md-12">
            <h1>Edit Member: <?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?></h1>
            <a href="/admin/members.php" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Members
            </a>
            <hr>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="POST" action="">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($member['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email']); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($member['firstname']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($member['lastname']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="sex" class="form-control">
                                        <option value="Male" <?php echo $member['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $member['sex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" class="form-control" value="<?php echo $member['age']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($member['mobile']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($member['state']); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Personal Details -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Personal Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="marital_status" class="form-control">
                                <option value="Never Married" <?php echo $member['marital_status'] == 'Never Married' ? 'selected' : ''; ?>>Never Married</option>
                                <option value="Divorced" <?php echo $member['marital_status'] == 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Widowed" <?php echo $member['marital_status'] == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Separated" <?php echo $member['marital_status'] == 'Separated' ? 'selected' : ''; ?>>Separated</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Religion</label>
                                    <input type="text" name="religion" class="form-control" value="<?php echo htmlspecialchars($member['religion']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Caste</label>
                                    <input type="text" name="caste" class="form-control" value="<?php echo htmlspecialchars($member['caste']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Height</label>
                                    <input type="text" name="height" class="form-control" value="<?php echo htmlspecialchars($member['height']); ?>" placeholder="e.g., 5'8&quot;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Weight</label>
                                    <input type="text" name="weight" class="form-control" value="<?php echo htmlspecialchars($member['weight']); ?>" placeholder="e.g., 65 kg">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Complexion</label>
                            <select name="complexion" class="form-control">
                                <option value="Fair" <?php echo $member['complexion'] == 'Fair' ? 'selected' : ''; ?>>Fair</option>
                                <option value="Wheatish" <?php echo $member['complexion'] == 'Wheatish' ? 'selected' : ''; ?>>Wheatish</option>
                                <option value="Dark" <?php echo $member['complexion'] == 'Dark' ? 'selected' : ''; ?>>Dark</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Professional Details -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Professional Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Education</label>
                            <input type="text" name="education" class="form-control" value="<?php echo htmlspecialchars($member['education']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Occupation</label>
                            <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($member['occupation']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Annual Income</label>
                            <input type="text" name="income" class="form-control" value="<?php echo htmlspecialchars($member['income']); ?>" placeholder="e.g., 5-10 Lakhs">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Settings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Account Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Account Status</label>
                            <select name="account_status" class="form-control">
                                <option value="active" <?php echo $member['account_status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="suspended" <?php echo $member['account_status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                <option value="deleted" <?php echo $member['account_status'] == 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Profile Completeness (%)</label>
                            <input type="number" name="profile_completeness" class="form-control" min="0" max="100" value="<?php echo $member['profile_completeness'] ?: 0; ?>">
                        </div>
                        
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_verified" value="1" <?php echo $member['is_verified'] == 1 ? 'checked' : ''; ?>>
                                <strong>Verified Profile</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- About Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>About / Bio</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea name="about" class="form-control" rows="5"><?php echo htmlspecialchars($member['about']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                        <a href="/admin/members.php" class="btn btn-default btn-lg">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include("../includes/admin-footer.php"); ?>
