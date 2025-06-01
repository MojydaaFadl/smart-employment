<?php
session_start();
// print_r($_SESSION['user_id']);


// اتصال بقاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$dbname = "smart_employment";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// معالجة رفع الملف
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["cv_file"])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES["cv_file"];
    
    // معلومات الملف
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    
    // التحقق من عدم وجود أخطاء
    if ($fileError === 0) {
        // التحقق من حجم الملف (2MB كحد أقصى)
        if ($fileSize <= 2 * 1024 * 1024) {
            // أنواع الملفات المسموح بها
            $allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];
            
            if (in_array($fileType, $allowedTypes)) {
                // إنشاء اسم فريد للملف
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = "cv_" . $user_id . "_" . time() . "." . $fileExt;
                $fileDestination = "uploads/cvs/" . $newFileName;
                
                // إنشاء المجلد إذا لم يكن موجوداً
                if (!file_exists("uploads/cvs")) {
                    mkdir("uploads/cvs", 0777, true);
                }
                
                // نقل الملف إلى المجلد المخصص
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // حفظ معلومات الملف في قاعدة البيانات
                    $dbFilePath = "../" . $fileDestination;
                    $sql = "INSERT INTO user_cvs (user_id, file_name, file_path, upload_date) 
                            VALUES (?, ?, ?, NOW()) 
                            ON DUPLICATE KEY UPDATE 
                            file_name = VALUES(file_name), 
                            file_path = VALUES(file_path), 
                            upload_date = NOW()";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user_id, $newFileName, $dbFilePath);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "تم رفع السيرة الذاتية بنجاح!";
                        header("Location: employee-dashboard.php");
                        exit();
                    } else {
                        $_SESSION['error_message'] = "حدث خطأ أثناء حفظ معلومات الملف في قاعدة البيانات.";
                    }
                    
                    $stmt->close();
                } else {
                    $_SESSION['error_message'] = "حدث خطأ أثناء رفع الملف.";
                }
            } else {
                $_SESSION['error_message'] = "نوع الملف غير مدعوم. الرجاء تحميل ملف PDF أو Word أو TXT";
            }
        } else {
            $_SESSION['error_message'] = "حجم الملف كبير جداً. الحد الأقصى المسموح به هو 2MB";
        }
    } else {
        $_SESSION['error_message'] = "حدث خطأ أثناء رفع الملف: " . $fileError;
    }
}

// إذا كان الطلب GET ويريد إنشاء سيرة ذاتية جديدة
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'create_new') {
    // جلب بيانات المستخدم من الجلسة أو قاعدة البيانات
    $user_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'] ?? '';
    $last_name = $_SESSION['last_name'] ?? '';
    $email = $_SESSION['email'] ?? '';
    
    // تخزين البيانات مؤقتاً لنقلها للصفحة التالية
    $_SESSION['cv_data'] = [
        'user_id' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email
    ];
    
    header("Location: cv-creation/step1.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التوظيف الذكي - إنشاء السيرة الذاتية</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/fontawesome/css/all.css" rel="stylesheet">
    <link href="css/upload-create-profile.css" rel="stylesheet">
</head>

<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index22.php">التوظيف الذكي</a>
        </div>
    </nav>
    
    <!-- عرض رسائل الخطأ أو النجاح -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <h3 class="page-title">أنشئ سيرتك الذاتية في أقل من 5 دقائق</h3>

    <section>
        <div class="option-card" onclick="showUploadModal()">
            <h5 class="text-primary">موصى به</h5>
            <h3>تحميل سيرتي الذاتية</h3>
            <p>سننسخ معلوماتك الشخصية من سيرتك الذاتية مباشرة.</p>
            <button class="btn-primary">رفع الملف</button>
        </div>
    </section>

    <div class="divider"></div>

    <section>
        <div class="option-card" onclick="window.location.href='?action=create_new'">
            <h5 class="text-primary">ابتدئ من نقطة البداية</h5>
            <h3>إنشاء سيرة ذاتية جديدة</h3>
            <p>سنساعدك على إنشاء سيرة ذاتية متكاملة، خطوة بخطوة.</p>
            <button class="btn-primary">ابدأ الإنشاء</button>
        </div>
    </section>

    <!-- نافذة رفع الملف -->
    <div class="upload-modal" id="uploadModal">
        <div class="upload-container">
            <div class="upload-header">
                <h3>إرفاق السيرة الذاتية</h3>
            </div>
            <form action="upload-create-profile.php" method="POST" enctype="multipart/form-data">
                <div class="upload-body" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #007bff;"></i>
                    <p>اختر ملفاً لتحميله</p>
                    <div class="file-info">
                        <p>الحد الأقصى لحجم ملف التحميل: 2MB</p>
                        <p>أنواع الملفات المسموح بها: PDF, DOC, DOCX, TXT</p>
                    </div>
                    <div id="fileInfoContainer" style="margin-top: 15px; display: none;">
                        <p><strong>الملف المختار:</strong> <span id="fileName"></span></p>
                        <p><strong>حجم الملف:</strong> <span id="fileSize"></span></p>
                    </div>
                    <input type="file" id="fileInput" name="cv_file" accept=".pdf,.doc,.docx,.txt" style="display: none;" onchange="handleFileUpload(this.files)">
                </div>
                <div class="upload-actions">
                    <button type="button" class="btn-secondary" onclick="hideUploadModal()">إلغاء</button>
                    <button type="submit" class="btn-primary" id="uploadBtn" disabled>تحميل السيرة الذاتية</button>
                </div>
            </form>
        </div>
    </div>

    <div class="divider"></div>

    <section>
        <div class="privacy-links">
            <a href="privacy-policy.html">سياسة الخصوصية</a>
            <a href="cookie-policy.html">سياسة ملفات تعريف الارتباط</a>
            <a href="cookie-settings.html">إعدادات ملفات تعريف الارتباط</a>
            <a href="terms.html">شروط الاستخدام</a>
            <a href="safety.html">احمِ نفسك</a>
        </div>
    </section>

    <div class="copyright">
        التوظيف الذكي 2025 © جميع الحقوق محفوظة.
    </div>

    <script>
        // دالة لعرض نافذة رفع الملف
        function showUploadModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }

        // دالة لإخفاء نافذة رفع الملف
        function hideUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
            document.getElementById('fileInfoContainer').style.display = 'none';
            document.getElementById('fileInput').value = '';
            document.getElementById('uploadBtn').disabled = true;
        }

        // دالة لمعالجة رفع الملف
        function handleFileUpload(files) {
            if (files.length > 0) {
                const file = files[0];
                const uploadBtn = document.getElementById('uploadBtn');

                // التحقق من حجم الملف (2MB كحد أقصى)
                if (file.size > 2 * 1024 * 1024) {
                    alert('حجم الملف كبير جداً. الحد الأقصى المسموح به هو 2MB');
                    return;
                }

                // التحقق من نوع الملف
                const allowedTypes = ['application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain'];
                if (!allowedTypes.includes(file.type)) {
                    alert('نوع الملف غير مدعوم. الرجاء تحميل ملف PDF أو Word أو TXT');
                    return;
                }

                // عرض معلومات الملف
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                document.getElementById('fileInfoContainer').style.display = 'block';

                uploadBtn.disabled = false;
            }
        }

        // دالة مساعدة لتنسيق حجم الملف
        function formatFileSize(bytes) {
            if (bytes < 1024) {
                return bytes + ' بايت';
            } else if (bytes < 1024 * 1024) {
                return (bytes / 1024).toFixed(2) + ' كيلوبايت';
            } else {
                return (bytes / (1024 * 1024)).toFixed(2) + ' ميجابايت';
            }
        }
    </script>

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>