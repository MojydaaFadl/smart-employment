<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login-form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    $applicationId = $_POST['application_id'];
    $userId = $_SESSION['user_id'];

    try {
        // التحقق من أن الطلب مملوك للمستخدم قبل الحذف
        $stmt = $conn->prepare("DELETE FROM job_applications WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $applicationId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "تم حذف طلب التوظيف بنجاح";
        } else {
            $_SESSION['error_message'] = "لم يتم العثور على طلب التوظيف أو ليس لديك صلاحية حذفه";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء محاولة حذف طلب التوظيف";
    }

    header("Location: employee-dashboard.php");
    exit;
}
?>