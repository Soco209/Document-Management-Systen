<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

try {
    $user_id = getCurrentUserId();
    
    $database = new Database();
    $db = $database->getConnection();

    $stats = [];

    if (isAdmin()) {
        // Admin: Get counts for all requests
        $total_stmt = $db->query("SELECT COUNT(*) as count FROM requests");
        $stats['total_requests'] = (int)$total_stmt->fetch()['count'];

        $pending_stmt = $db->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'");
        $stats['pending_requests'] = (int)$pending_stmt->fetch()['count'];

        $processing_stmt = $db->query("SELECT COUNT(*) as count FROM requests WHERE status = 'processing'");
        $stats['processing_requests'] = (int)$processing_stmt->fetch()['count'];

        $completed_stmt = $db->query("SELECT COUNT(*) as count FROM requests WHERE status = 'completed'");
        $stats['completed_requests'] = (int)$completed_stmt->fetch()['count'];

    } else {
        // Student: Get counts for their own requests
        $pending_stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM requests 
            WHERE student_id = ? AND status IN ('pending', 'processing')
        ");
        $pending_stmt->execute([$user_id]);
        $stats['pending_requests'] = (int)$pending_stmt->fetch()['count'];
        
        $completed_stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM requests 
            WHERE student_id = ? AND status = 'completed'
        ");
        $completed_stmt->execute([$user_id]);
        $stats['completed_requests'] = (int)$completed_stmt->fetch()['count'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => $stats
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>