<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// إعدادات سجل الأخطاء
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'pdf_upload_errors.log');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    error_log("[" . date('Y-m-d H:i:s') . "] محاولة رفع بدون تسجيل دخول");
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
    exit;
}

// قراءة البيانات المرسلة
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// التحقق من صحة البيانات
if (!$data || !isset($data['file_data']) || !isset($data['file_name'])) {
    error_log("[" . date('Y-m-d H:i:s') . "] بيانات غير صالحة: " . print_r($data, true));
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
    exit;
}

// اتصال بقاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$dbname = "smart_employment";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");

    // 1. حذف الملف القديم إذا وجد
    $user_id = $_SESSION['user_id'];
    $old_file_path = null;
    
    // جلب معلومات الملف القديم
    $sql_select = "SELECT file_path FROM user_cvs WHERE user_id = ? ORDER BY upload_date DESC LIMIT 1";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $user_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $old_file_path = $row['file_path'];
        
        // حذف الملف القديم من الخادم
        if (file_exists($old_file_path)) {
            if (!unlink($old_file_path)) {
                throw new Exception("فشل حذف الملف القديم");
            }
        }
        
        // حذف السجل القديم من قاعدة البيانات
        $sql_delete = "DELETE FROM user_cvs WHERE user_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $user_id);
        if (!$stmt_delete->execute()) {
            throw new Exception("فشل حذف سجل الملف القديم من قاعدة البيانات");
        }
        $stmt_delete->close();
    }
    $stmt_select->close();

    // 2. إنشاء مجلد التحميل إذا لم يكن موجوداً
    $uploadDir = "../uploads/cvs/";
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("لا يمكن إنشاء مجلد التحميل");
        }
    }

    // التحقق من قابلية الكتابة على المجلد
    if (!is_writable($uploadDir)) {
        throw new Exception("مجلد التحميل غير قابل للكتابة");
    }

    // 3. إنشاء اسم فريد للملف الجديد
    $fileExt = pathinfo($data['file_name'], PATHINFO_EXTENSION) ?: 'pdf';
    $newFileName = "cv_" . $user_id . "_" . time() . "." . $fileExt;
    $filePath = $uploadDir . $newFileName;

    // 4. فك تشفير بيانات الملف
    $fileData = base64_decode($data['file_data']);
    if ($fileData === false) {
        throw new Exception("فشل فك تشفير بيانات الملف");
    }

    // 5. حفظ الملف الجديد على الخادم
    $bytesWritten = file_put_contents($filePath, $fileData);
    if ($bytesWritten === false) {
        throw new Exception("فشل حفظ الملف على الخادم");
    }

    // 6. حفظ معلومات الملف الجديد في قاعدة البيانات
    $sql_insert = "INSERT INTO user_cvs (user_id, file_name, file_path, upload_date) 
                  VALUES (?, ?, ?, NOW())";
    
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        throw new Exception("فشل تحضير استعلام SQL: " . $conn->error);
    }

    $originalFileName = $data['file_name']; // اسم الملف الأصلي الذي أدخله المستخدم
    $stmt_insert->bind_param("iss", $user_id, $originalFileName, $filePath);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("فشل تنفيذ استعلام SQL: " . $stmt_insert->error);
    }

    // 7. إرجاع رسالة النجاح مع معلومات الملف
    echo json_encode([
        'success' => true,
        'message' => 'تم رفع السيرة الذاتية بنجاح واستبدال الملف القديم',
        'file_path' => str_replace('../', '', $filePath), // مسار يمكن الوصول إليه من الواجهة
        'file_name' => $originalFileName,
        'file_size' => $bytesWritten,
        'old_file_deleted' => ($old_file_path !== null)
    ]);

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] خطأ: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>