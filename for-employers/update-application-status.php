<?php
require_once 'db_connection.php';
session_start();

header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['employer_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit();
}

// التحقق من البيانات المرسلة
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit();
}

$applicationId = $_POST['id'];
$status = $_POST['status'];
$employerId = $_SESSION['employer_id'];

try {
    // التحقق من أن الطلب يخص صاحب العمل الحالي
    $stmt = $conn->prepare("
        SELECT ja.id 
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.id
        WHERE ja.id = ? AND jp.employer_id = ?
    ");
    $stmt->execute([$applicationId, $employerId]);
    $application = $stmt->fetch();

    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'طلب غير موجود أو غير مسموح بالتعديل']);
        exit();
    }

    // تحديث حالة الطلب
    $stmt = $conn->prepare("
        UPDATE job_applications 
        SET status = ?, reviewed_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$status, $applicationId]);
    
    echo json_encode(['success' => true, 'message' => 'تم تحديث الحالة بنجاح']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage()]);
}