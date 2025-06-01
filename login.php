<?php
session_start();

// اتصال بقاعدة البيانات
$host = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "smart_employment";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// معالجة تسجيل الدخول
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember-me']) ? true : false;

    // التحقق من صحة البيانات المدخلة
    if (empty($email)) {
        header("Location: login-form.php?error=empty_email");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login-form.php?error=invalid_email");
        exit;
    }

    if (empty($password)) {
        header("Location: login-form.php?error=empty_password");
        exit;
    }

    if (strlen($password) < 6) {
        header("Location: login-form.php?error=short_password");
        exit;
    }

    // البحث عن المستخدم في قاعدة البيانات
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            // تسجيل الدخول ناجح
            
            // تعيين بيانات الجلسة
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            // إذا اختار المستخدم "تذكرني"
            if ($remember) {
                setcookie('remember_user', $user['user_id'], time() + (30 * 24 * 60 * 60), "/");
            }
            
            // التحقق من وجود CV للمستخدم
            $checkCV = $conn->prepare("SELECT * FROM user_cvs WHERE user_id = :user_id");
            $checkCV->bindParam(':user_id', $user['user_id']);
            $checkCV->execute();

            if ($checkCV->rowCount() > 0) {
                // إذا كان لديه CV، توجيه إلى لوحة التحكم
                header("Location: employee-dashboard.php");
            } else {
                // إذا لم يكن لديه CV، توجيه إلى صفحة إنشاء السيرة الذاتية
                header("Location: upload-create-profile.php");
            }
            exit();
        } else {
            header("Location: login-form.php?error=invalid_password");
            exit;
        }
    } else {
        header("Location: login-form.php?error=email_not_found");
        exit;
    }
}

// إذا تم الوصول إلى الصفحة مباشرة بدون بيانات POST
header("Location: login-form.php");
exit;
?>