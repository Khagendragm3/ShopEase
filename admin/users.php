<?php
$pageTitle = "Manage Users";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Handle user status update
if (isset($_GET['activate']) && !empty($_GET['activate'])) {
    $userId = (int) sanitize($_GET['activate']);
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Check if status column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        if ($checkColumn->num_rows > 0) {
            // Status column exists, update it
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                flash('success', 'User activated successfully.');
            } else {
                flash('error', 'Failed to activate user.');
            }
        } else {
            // Status column doesn't exist, just show a message
            flash('info', 'User activation feature is not available in this database schema.');
        }
    } else {
        flash('error', 'User not found.');
    }
    
    redirect('admin/users.php');
}

if (isset($_GET['deactivate']) && !empty($_GET['deactivate'])) {
    $userId = (int) sanitize($_GET['deactivate']);
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Check if status column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        if ($checkColumn->num_rows > 0) {
            // Status column exists, update it
            $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                flash('success', 'User deactivated successfully.');
            } else {
                flash('error', 'Failed to deactivate user.');
            }
        } else {
            // Status column doesn't exist, just show a message
            flash('info', 'User deactivation feature is not available in this database schema.');
        }
    } else {
        flash('error', 'User not found.');
    }
    
    redirect('admin/users.php');
}

// Handle user role update
if (isset($_GET['make_admin']) && !empty($_GET['make_admin'])) {
    $userId = (int) sanitize($_GET['make_admin']);
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Make user admin
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            flash('success', 'User promoted to admin successfully.');
        } else {
            flash('error', 'Failed to promote user to admin.');
        }
    } else {
        flash('error', 'User not found.');
    }
    
    redirect('admin/users.php');
}

if (isset($_GET['remove_admin']) && !empty($_GET['remove_admin'])) {
    $userId = (int) sanitize($_GET['remove_admin']);
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Prevent removing admin role from the last admin
        $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
        $stmt->execute();
        $result = $stmt->get_result();
        $adminCount = $result->fetch_assoc()['admin_count'];
        
        if ($adminCount <= 1 && $user['role'] === 'admin') {
            flash('error', 'Cannot remove admin role from the last admin.');
            redirect('admin/users.php');
        }
        
        // Remove admin role
        $stmt = $conn->prepare("UPDATE users SET role = 'customer' WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            flash('success', 'Admin role removed successfully.');
        } else {
            flash('error', 'Failed to remove admin role.');
        }
    } else {
        flash('error', 'User not found.');
    }
    
    redirect('admin/users.php');
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['user_ids'])) {
    $action = sanitize($_POST['bulk_action']);
    $userIds = $_POST['user_ids'];
    
    if (!empty($userIds)) {
        switch ($action) {
            case 'activate':
                // Check if status column exists
                $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
                if ($checkColumn->num_rows > 0) {
                    $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id IN ($placeholders)");
                    $types = str_repeat('i', count($userIds));
                    $stmt->bind_param($types, ...$userIds);
                    
                    if ($stmt->execute()) {
                        flash('success', count($userIds) . ' users activated successfully.');
                    } else {
                        flash('error', 'Failed to activate users.');
                    }
                } else {
                    flash('info', 'User activation feature is not available in this database schema.');
                }
                break;
                
            case 'deactivate':
                // Check if status column exists
                $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
                if ($checkColumn->num_rows > 0) {
                    $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                    $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE user_id IN ($placeholders)");
                    $types = str_repeat('i', count($userIds));
                    $stmt->bind_param($types, ...$userIds);
                    
                    if ($stmt->execute()) {
                        flash('success', count($userIds) . ' users deactivated successfully.');
                    } else {
                        flash('error', 'Failed to deactivate users.');
                    }
                } else {
                    flash('info', 'User deactivation feature is not available in this database schema.');
                }
                break;
                
            case 'make_admin':
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE user_id IN ($placeholders)");
                $types = str_repeat('i', count($userIds));
                $stmt->bind_param($types, ...$userIds);
                
                if ($stmt->execute()) {
                    flash('success', count($userIds) . ' users promoted to admin successfully.');
                } else {
                    flash('error', 'Failed to promote users to admin.');
                }
                break;
                
            case 'remove_admin':
                // Prevent removing admin role from all admins
                $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
                $stmt->execute();
                $result = $stmt->get_result();
                $adminCount = $result->fetch_assoc()['admin_count'];
                
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $stmt = $conn->prepare("SELECT COUNT(*) as selected_admin_count FROM users WHERE role = 'admin' AND user_id IN ($placeholders)");
                $types = str_repeat('i', count($userIds));
                $stmt->bind_param($types, ...$userIds);
                $stmt->execute();
                $result = $stmt->get_result();
                $selectedAdminCount = $result->fetch_assoc()['selected_admin_count'];
                
                if ($adminCount <= $selectedAdminCount) {
                    flash('error', 'Cannot remove admin role from all admins.');
                    redirect('admin/users.php');
                }
                
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE users SET role = 'customer' WHERE user_id IN ($placeholders)");
                $types = str_repeat('i', count($userIds));
                $stmt->bind_param($types, ...$userIds);
                
                if ($stmt->execute()) {
                    flash('success', 'Admin role removed from selected users successfully.');
                } else {
                    flash('error', 'Failed to remove admin role from users.');
                }
                break;
        }
    }
    
    redirect('admin/users.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $countQuery .= " AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
}

if (!empty($role)) {
    $query .= " AND role = ?";
    $countQuery .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $countQuery .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

// Order by
$query .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get users
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get total users for pagination
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    // Remove the last two parameters (offset and limit) for the count query
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Users</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add-user.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-plus"></i> Add New User
                    </a>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="users.php" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="customer" <?php echo $role == 'customer' ? 'selected' : ''; ?>>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="users.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="users.php">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th width="5%">ID</th>
                                        <th width="15%">Username</th>
                                        <th width="20%">Name</th>
                                        <th width="20%">Email</th>
                                        <th width="10%">Role</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No users found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="user_ids[]" value="<?php echo $user['user_id']; ?>" class="user-checkbox">
                                            </td>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo isset($user['status']) && $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($user['status'] ?? 'inactive'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="user-details.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if (isset($user['status']) && $user['status'] == 'active'): ?>
                                                <a href="users.php?deactivate=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to deactivate this user?');">
                                                    <i class="fas fa-user-slash"></i>
                                                </a>
                                                <?php else: ?>
                                                <a href="users.php?activate=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to activate this user?');">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($user['role'] == 'customer'): ?>
                                                <a href="users.php?make_admin=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-dark" onclick="return confirm('Are you sure you want to make this user an admin?');">
                                                    <i class="fas fa-user-shield"></i>
                                                </a>
                                                <?php else: ?>
                                                <a href="users.php?remove_admin=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to remove admin privileges from this user?');">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select name="bulk_action" class="form-select">
                                        <option value="">Bulk Actions</option>
                                        <option value="activate">Activate</option>
                                        <option value="deactivate">Deactivate</option>
                                        <option value="make_admin">Make Admin</option>
                                        <option value="remove_admin">Remove Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to perform this action?');">Apply</button>
                                </div>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>">Previous</a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>">Next</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- User Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Registered Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Admins</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        // Check if the role column exists
                                        $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
                                        if ($checkColumn->num_rows > 0) {
                                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
                                        } else {
                                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
                                        }
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New Users (Last 30 Days)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        // Check if the created_at column exists
                                        $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
                                        if ($checkColumn->num_rows > 0) {
                                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                                        } else {
                                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
                                        }
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAll = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    
    selectAll.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
    
    // Update select all checkbox when individual checkboxes change
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(userCheckboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 