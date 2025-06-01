<?php
session_start();
require_once 'comment-functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'يجب تسجيل الدخول']);
    exit();
}

if (!isset($_POST['job_id']) || !isset($_POST['comment_text'])) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات غير مكتملة']);
    exit();
}

$jobId = (int)$_POST['job_id'];
$commentText = trim($_POST['comment_text']);
$userId = $_SESSION['user_id'];

if (empty($commentText)) {
    echo json_encode(['status' => 'error', 'message' => 'نص التعليق لا يمكن أن يكون فارغًا']);
    exit();
}

$commentId = addComment($jobId, $userId, $commentText);

if ($commentId) {
    // جلب بيانات التعليق الجديد لعرضها
    $stmt = $GLOBALS['conn']->prepare("SELECT jc.*, u.first_name, u.last_name 
                                      FROM job_comments jc
                                      JOIN users u ON jc.user_id = u.user_id
                                      WHERE jc.id = ?");
    $stmt->execute([$commentId]);
    $newComment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $newComment['time_ago'] = 'الآن';
    $newComment['full_name'] = $newComment['first_name'] . ' ' . $newComment['last_name'];
    
    echo json_encode(['status' => 'success', 'comment' => $newComment]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'فشل في إضافة التعليق']);
}
?>