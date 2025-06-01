<?php
require_once 'db_connection.php';

// دالة لإضافة تعليق جديد
function addComment($jobId, $userId, $commentText) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO job_comments (job_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$jobId, $userId, $commentText]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error adding comment: " . $e->getMessage());
        return false;
    }
}

// دالة لجلب التعليقات لوظيفة معينة
function getCommentsForJob($jobId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT jc.*, u.first_name, u.last_name 
                              FROM job_comments jc
                              JOIN users u ON jc.user_id = u.user_id
                              WHERE jc.job_id = ?
                              ORDER BY jc.created_at DESC");
        $stmt->execute([$jobId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching comments: " . $e->getMessage());
        return [];
    }
}

// دالة لحذف تعليق
function deleteComment($commentId, $userId) {
    global $conn;
    
    try {
        // التحقق من أن المستخدم هو صاحب التعليق قبل الحذف
        $stmt = $conn->prepare("DELETE FROM job_comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentId, $userId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error deleting comment: " . $e->getMessage());
        return false;
    }
}
?>