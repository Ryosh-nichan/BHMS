<?php
// functions/views/activity.php

// 1. Include connection using proper path
$connectionPath = dirname(__DIR__, 2) . '/functions/connection.php';
if (!file_exists($connectionPath)) {
    die("Connection file not found at: " . $connectionPath);
}
require_once $connectionPath;

// 2. Verify database connection
if(!isset($db) || !($db instanceof PDO)) {
    die("Database connection not established. Check connection.php");
}

try {
    // 3. Single query with JOIN to get all needed data
    $sql = 'SELECT l.*, u.username, u.role 
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();

    // 4. Display data in table rows
    foreach ($results as $row) {
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td>
                <img class="rounded-circle me-2" width="30" height="30" 
                     src="https://bootdey.com/img/Content/avatar/avatar7.png">
                <?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?>
            </td>
            <td><?php echo htmlspecialchars($row['role'] ?? 'N/A'); ?></td>
            <td>
                <?php echo htmlspecialchars($row['logs']); ?><br>
                <small class="text-muted">Type: <?php echo htmlspecialchars($row['type'] ?? ''); ?></small>
            </td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
        </tr>
        <?php
    }
} catch (PDOException $e) {
    echo '<tr><td colspan="5" class="text-danger">Error loading activity logs: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
    error_log('Activity Log Error: ' . $e->getMessage());
}
?>
