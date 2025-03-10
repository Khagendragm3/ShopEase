<?php
$pageTitle = "Contact Messages";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Handle message status update
if (isset($_GET['mark_as_read']) && !empty($_GET['mark_as_read'])) {
    $id = (int)$_GET['mark_as_read'];
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('success', 'Message marked as read.');
    } else {
        flash('error', 'Failed to update message status.');
    }
    
    redirect('contact-messages.php');
}

// Handle message status update to replied
if (isset($_GET['mark_as_replied']) && !empty($_GET['mark_as_replied'])) {
    $id = (int)$_GET['mark_as_replied'];
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('success', 'Message marked as replied.');
    } else {
        flash('error', 'Failed to update message status.');
    }
    
    redirect('contact-messages.php');
}

// Handle message deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('success', 'Message deleted successfully.');
    } else {
        flash('error', 'Failed to delete message.');
    }
    
    redirect('contact-messages.php');
}

// Get message for viewing
$message = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $id = (int)$_GET['view'];
    
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = $result->fetch_assoc();
        
        // If message is new, mark it as read
        if ($message['status'] === 'new') {
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message['status'] = 'read';
        }
    } else {
        flash('error', 'Message not found.');
        redirect('contact-messages.php');
    }
}

// Check if contact_messages table exists
$tableExists = false;
$checkTable = $conn->query("SHOW TABLES LIKE 'contact_messages'");
if ($checkTable && $checkTable->num_rows > 0) {
    $tableExists = true;
}

// Create table if it doesn't exist
if (!$tableExists) {
    $sql = "CREATE TABLE `contact_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `subject` varchar(255) NOT NULL,
        `message` text NOT NULL,
        `status` enum('new','read','replied') NOT NULL DEFAULT 'new',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
}

// Get all messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter by status if provided
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = '';
$params = [];
$types = '';

if (!empty($statusFilter) && in_array($statusFilter, ['new', 'read', 'replied'])) {
    $whereClause = " WHERE status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Count total messages
$countSql = "SELECT COUNT(*) as total FROM contact_messages" . $whereClause;
$countStmt = $conn->prepare($countSql);

if (!empty($types)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$totalMessages = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalMessages / $limit);

// Get messages for current page
$sql = "SELECT * FROM contact_messages" . $whereClause . " ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);

$params[] = $offset;
$params[] = $limit;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Contact Messages</h1>
            </div>
            
            <?php flash(); ?>
            
            <?php if ($message): ?>
                <!-- View Message -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Message Details</h6>
                        <div>
                            <a href="contact-messages.php" class="btn btn-sm btn-secondary">Back to List</a>
                            <?php if ($message['status'] !== 'replied'): ?>
                            <a href="contact-messages.php?mark_as_replied=<?php echo $message['id']; ?>" class="btn btn-sm btn-success">Mark as Replied</a>
                            <?php endif; ?>
                            <a href="contact-messages.php?delete=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="message-header mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)</p>
                                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $message['status'] === 'new' ? 'danger' : 
                                                ($message['status'] === 'read' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="message-content p-3 bg-light rounded">
                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        </div>
                        <div class="message-actions mt-4">
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>" class="btn btn-primary">
                                <i class="fas fa-reply"></i> Reply via Email
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Messages List -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="m-0 font-weight-bold text-primary">All Messages</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="float-md-end">
                                    <div class="btn-group">
                                        <a href="contact-messages.php" class="btn btn-sm btn-<?php echo empty($statusFilter) ? 'primary' : 'outline-primary'; ?>">All</a>
                                        <a href="contact-messages.php?status=new" class="btn btn-sm btn-<?php echo $statusFilter === 'new' ? 'danger' : 'outline-danger'; ?>">New</a>
                                        <a href="contact-messages.php?status=read" class="btn btn-sm btn-<?php echo $statusFilter === 'read' ? 'warning' : 'outline-warning'; ?>">Read</a>
                                        <a href="contact-messages.php?status=replied" class="btn btn-sm btn-<?php echo $statusFilter === 'replied' ? 'success' : 'outline-success'; ?>">Replied</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Name</th>
                                        <th width="20%">Email</th>
                                        <th width="25%">Subject</th>
                                        <th width="15%">Date</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($messages)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No messages found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($messages as $item): ?>
                                        <tr class="<?php echo $item['status'] === 'new' ? 'table-warning' : ''; ?>">
                                            <td><?php echo $item['id']; ?></td>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['email']); ?></td>
                                            <td><?php echo htmlspecialchars($item['subject']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $item['status'] === 'new' ? 'danger' : 
                                                        ($item['status'] === 'read' ? 'warning' : 'success'); 
                                                ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="contact-messages.php?view=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($item['status'] === 'new'): ?>
                                                    <a href="contact-messages.php?mark_as_read=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning" title="Mark as Read">
                                                        <i class="fas fa-envelope-open"></i>
                                                    </a>
                                                    <?php elseif ($item['status'] === 'read'): ?>
                                                    <a href="contact-messages.php?mark_as_replied=<?php echo $item['id']; ?>" class="btn btn-sm btn-success" title="Mark as Replied">
                                                        <i class="fas fa-reply"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <a href="contact-messages.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalPages > 1): ?>
                        <div class="mt-4">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo ($page <= 1) ? '#' : 'contact-messages.php?page=' . ($page - 1) . (!empty($statusFilter) ? '&status=' . $statusFilter : ''); ?>">Previous</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="contact-messages.php?page=<?php echo $i; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo ($page >= $totalPages) ? '#' : 'contact-messages.php?page=' . ($page + 1) . (!empty($statusFilter) ? '&status=' . $statusFilter : ''); ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 