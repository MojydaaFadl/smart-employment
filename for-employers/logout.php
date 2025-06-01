<?php
session_start();

// إلغاء جميع متغيرات الجلسة
$_SESSION = array();

// إذا كنت تريد إنهاء الجلسة تمامًا، فقم أيضًا بحذف ملف تعريف الارتباط للجلسة
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// أخيرًا، قم بتدمير الجلسة
session_destroy();

// توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: employer-login.php");
exit();
?>