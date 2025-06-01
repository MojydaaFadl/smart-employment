<?php
session_start();
require_once 'db_connection.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
  header("Location: login-form.php?error=not_logged_in");
  exit();
}

// التحقق من البيانات المرسلة
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['job_id'])) {
  header("Location: employee-dashboard.php?error=invalid_request");
  exit();
}

$job_id = $_POST['job_id'];
$employer_id = $_POST['employer_id'];
$user_id = $_SESSION['user_id'];
$cover_letter = $_POST['cover_letter'] ?? 'أرغب في التقدم لهذه الوظيفة';

try {
  // التحقق من وجود جدول job_applications
  $stmt = $conn->query("SELECT 1 FROM job_applications LIMIT 1");
  if ($stmt === false) {
    throw new Exception("جدول طلبات التوظيف غير موجود");
  }

  // التحقق من أن المستخدم لم يتقدم للوظيفة من قبل
  $stmt = $conn->prepare("SELECT * FROM job_applications
                          WHERE job_id = ? AND user_id = ?");
  $stmt->execute([$job_id, $user_id]);

  if ($stmt->rowCount() > 0) {
    header("Location: employee-dashboard.php?error=already_applied");
    exit();
  }

  // جلب بيانات المستخدم الأساسية
  $stmt = $conn->prepare("SELECT user_id, first_name, last_name FROM users WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();

  if (!$user) {
    header("Location: employee-dashboard.php?error=user_not_found");
    exit();
  }

  // جلب السيرة الذاتية إذا كانت موجودة (اختياري)
  $cv_path = null;
  $stmt = $conn->prepare("SELECT file_path FROM user_cvs WHERE user_id = ? ORDER BY upload_date DESC LIMIT 1");
  $stmt->execute([$user_id]);
  $cv = $stmt->fetch();
  if ($cv) {
    $cv_path = $cv['file_path'];
  }

  // إدخال الطلب في قاعدة البيانات
  $stmt = $conn->prepare("INSERT INTO job_applications
                          (job_id, employer_id, user_id, cover_letter, status)
                          VALUES (?, ?, ?, ?, 'pending')");
  $stmt->execute([
    $job_id,
    $employer_id,
    $user_id,
    $cover_letter
  ]);

  // في نهاية الملف بعد نجاح التقديم:
  $redirect_url = "employee-dashboard.php?application_success=1";

  // إضافة معايير البحث إذا كانت موجودة
  if (isset($_POST['search_query'])) {
    $redirect_url .= "&search=" . urlencode($_POST['search_query']);
  }
  if (isset($_POST['search_country'])) {
    $redirect_url .= "&country=" . urlencode($_POST['search_country']);
  }

  header("Location: " . $redirect_url);
  exit();

} catch (PDOException $e) {
  error_log("Error submitting application: " . $e->getMessage());
  header("Location: employee-dashboard.php?error=database_error");
  exit();
} catch (Exception $e) {
  error_log("Error: " . $e->getMessage());
  header("Location: employee-dashboard.php?error=system_error");
  exit();
}
?>