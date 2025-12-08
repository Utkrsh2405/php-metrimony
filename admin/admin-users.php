<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Verify admin status
$user_id = intval($_SESSION['id']);
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: /index.php");
    exit();
}

// Handle form submissions
$message = '';
$error = '';

// Create new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'create') {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $password = trim($_POST['password']);
        
        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            // Check if username exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
            if (mysqli_num_rows($check) > 0) {
                $error = 'Username already exists.';
            } else {
                // Check if email exists
                $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
                if (mysqli_num_rows($check) > 0) {
                    $error = 'Email already exists.';
                } else {
                    // Create admin user
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $query = "INSERT INTO users (username, password, email, userlevel, profilestat, dateofbirth, gender, account_status) 
                              VALUES ('$username', '$hashed_password', '$email', 1, 1, '1990-01-01', 'M', 'active')";
                    
                    if (mysqli_query($conn, $query)) {
                        $message = "Admin user '$username' created successfully!";
                    } else {
                        $error = 'Failed to create admin user: ' . mysqli_error($conn);
                    }
                }
            }
        }
    }
    
    // Remove admin privileges (demote to regular user)
    if ($_POST['action'] === 'demote' && isset($_POST['admin_id'])) {
        $admin_id = intval($_POST['admin_id']);
        
        // Prevent self-demotion
        if ($admin_id === $user_id) {
            $error = 'You cannot remove your own admin privileges.';
        } else {
            $query = "UPDATE users SET userlevel = 0 WHERE id = $admin_id";
            if (mysqli_query($conn, $query)) {
                $message = 'Admin privileges removed successfully.';
            } else {
                $error = 'Failed to remove admin privileges.';
            }
        }
    }
    
    // Promote user to admin
    if ($_POST['action'] === 'promote' && isset($_POST['user_id'])) {
        $promote_id = intval($_POST['user_id']);
        $query = "UPDATE users SET userlevel = 1 WHERE id = $promote_id";
        if (mysqli_query($conn, $query)) {
            $message = 'User promoted to admin successfully.';
        } else {
            $error = 'Failed to promote user.';
        }
    }
    
    // Delete admin user
    if ($_POST['action'] === 'delete' && isset($_POST['admin_id'])) {
        $admin_id = intval($_POST['admin_id']);
        
        // Prevent self-deletion
        if ($admin_id === $user_id) {
            $error = 'You cannot delete your own account.';
        } else {
            $query = "DELETE FROM users WHERE id = $admin_id AND userlevel = 1";
            if (mysqli_query($conn, $query) && mysqli_affected_rows($conn) > 0) {
                $message = 'Admin user deleted successfully.';
            } else {
                $error = 'Failed to delete admin user.';
            }
        }
    }
}

// Get all admin users
$admins_query = mysqli_query($conn, "SELECT id, username, email, last_login, account_status FROM users WHERE userlevel = 1 ORDER BY id ASC");
$admins = [];
while ($row = mysqli_fetch_assoc($admins_query)) {
    $admins[] = $row;
}

// Get regular users (for promotion dropdown)
$users_query = mysqli_query($conn, "SELECT id, username, email FROM users WHERE userlevel = 0 AND account_status = 'active' ORDER BY username ASC LIMIT 100");
$users = [];
while ($row = mysqli_fetch_assoc($users_query)) {
    $users[] = $row;
}

include("../includes/admin-header.php");
?>

<div class="page-header">
    <h2><i class="fa fa-user-secret"></i> Admin User Management</h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#createAdminModal">
        <i class="fa fa-plus"></i> Create New Admin
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible" style="margin-bottom: 20px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible" style="margin-bottom: 20px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Current Admins -->
<div class="card">
    <h3 style="margin-bottom: 20px;"><i class="fa fa-users"></i> Current Admin Users</h3>
    
    <div class="admin-table">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admins)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 30px;">No admin users found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td>#<?php echo $admin['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                        <?php if ($admin['id'] == $user_id): ?>
                        <span class="badge badge-primary" style="margin-left: 5px;">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    <td>
                        <?php if ($admin['account_status'] === 'active'): ?>
                        <span class="badge badge-success">Active</span>
                        <?php else: ?>
                        <span class="badge badge-warning"><?php echo ucfirst($admin['account_status']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $admin['last_login'] ? date('M d, Y h:i A', strtotime($admin['last_login'])) : 'Never'; ?>
                    </td>
                    <td>
                        <?php if ($admin['id'] != $user_id): ?>
                        <button class="btn btn-warning btn-xs" onclick="confirmDemote(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                            <i class="fa fa-arrow-down"></i> Demote
                        </button>
                        <button class="btn btn-danger btn-xs" onclick="confirmDelete(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Promote User to Admin -->
<div class="card" style="margin-top: 24px;">
    <h3 style="margin-bottom: 20px;"><i class="fa fa-level-up"></i> Promote User to Admin</h3>
    
    <form method="POST" class="form-inline">
        <input type="hidden" name="action" value="promote">
        <div class="form-group" style="margin-right: 15px;">
            <label for="user_id" style="margin-right: 10px;">Select User:</label>
            <select name="user_id" id="user_id" class="form-control" required style="min-width: 300px;">
                <option value="">-- Select a user --</option>
                <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to promote this user to admin?');">
            <i class="fa fa-arrow-up"></i> Promote to Admin
        </button>
    </form>
    
    <?php if (empty($users)): ?>
    <p class="text-muted" style="margin-top: 15px;">
        <i class="fa fa-info-circle"></i> No regular users available for promotion.
    </p>
    <?php endif; ?>
</div>

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Create New Admin User</h4>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="username">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               placeholder="Enter username" maxlength="20" pattern="[a-zA-Z0-9_]+">
                        <small class="text-muted">Letters, numbers, and underscores only. Max 20 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="Enter email address">
                    </div>
                    <div class="form-group">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Enter password" minlength="6">
                        <small class="text-muted">Minimum 6 characters.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Demote Modal -->
<div class="modal fade" id="demoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-warning" style="color: #f0ad4e;"></i> Confirm Demotion</h4>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="demote">
                <input type="hidden" name="admin_id" id="demote_admin_id">
                <div class="modal-body">
                    <p>Are you sure you want to remove admin privileges from <strong id="demote_username"></strong>?</p>
                    <p class="text-warning">This user will become a regular member and lose all admin access.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-arrow-down"></i> Demote to Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle" style="color: #d9534f;"></i> Confirm Deletion</h4>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="admin_id" id="delete_admin_id">
                <div class="modal-body">
                    <p>Are you sure you want to <strong>permanently delete</strong> admin user <strong id="delete_username"></strong>?</p>
                    <p class="text-danger"><i class="fa fa-warning"></i> This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDemote(adminId, username) {
    $('#demote_admin_id').val(adminId);
    $('#demote_username').text(username);
    $('#demoteModal').modal('show');
}

function confirmDelete(adminId, username) {
    $('#delete_admin_id').val(adminId);
    $('#delete_username').text(username);
    $('#deleteModal').modal('show');
}
</script>

<?php include("../includes/admin-footer.php"); ?>
