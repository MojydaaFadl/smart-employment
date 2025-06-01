<?php
session_start();
$logged_in = isset($_SESSION['user_id']);
// print_r($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التوظيف الذكي - مراجعة السيرة الذاتية</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../css/fontawesome/css/all.css">
    <link rel="stylesheet" href="css/cv-style.css">

    <style>
        /* تنسيقات عامة */
        .review-section {
            background-color: white;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007bff !important;
            margin: 0;
        }
        
        /* تنسيق الأزرار */
        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        /* تنسيق عرض البيانات */
        .data-item {
            margin-bottom: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            text-align: right;
        }
        
        .data-item:last-child {
            border-bottom: none;
        }
        
        .data-label {
            color: #007bff;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .data-value {
            color: #555;
        }
        
        /* تنسيق القوائم */
        .items-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .list-item {
            background-color: #f0f7fc;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            align-items: center;
            gap: 6px;
        }
        
        /* أزرار الحذف */
        .delete-btn {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 0;
            margin-right: 5px;
        }
        
        /* صورة الملف الشخصي */
        .profile-pic-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 15px;
            border: 2px solid #007bff;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* أزرار الإجراءات */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        /* رسائل عدم وجود بيانات */
        .no-data {
            color: #999;
            text-align: center;
            padding: 10px;
            font-size: 0.9rem;
        }        
    </style>
</head>
<body>
     <!-- شريط التنقل -->
  <nav class="navbar">
    <div>
      <span class="open-btn" onclick="openSidebar()">&#9776;</span>
      <span class="navbar-brand">
        التوظيف الذكي
      </span>
    </div>
  </nav>

  <!-- القائمة الجانبية المعدلة -->
  <div id="sidebar" class="sidebar">
    <div class="sidebar-header">
      <div>
        <span class="close-btn" onclick="closeSidebar()"><i class="fa-solid fa-xmark"></i></span>
      </div>
      <div class="d-none">
        <span class="menu-btn"><i class="fa-regular fa-message"></i></span>
        <span class="menu-btn"><i class="fa-regular fa-bell"></i></span>
        <span class="menu-btn"><i class="fa-regular fa-circle-user"></i></span>
      </div>
    </div>
    <a href="../index22.php">الرئيسية</a>
    <a href="#">البحث عن وظيفة</a>
    <a href="<?php echo $logged_in ? '../upload-create-profile.php' : '../register-form.php'; ?>">إنشاء ملفك الشخصي</a>
    <a href="#">بريميوم</a>
    <a href="#">المدونة</a>
    <?php if (!$logged_in): ?>
    <a href="../for-employers/employer-dashboard.php">لأصحاب العمل</a>
    <?php endif; ?>


    <a href="#">English</a>

    <?php if ($logged_in): ?>
    <div class="sidebar-buttons">
      <span style="color: white; padding: 10px;">مرحباً <?php echo $_SESSION['first_name']; ?></span>
      <a href="../logout.php" class="btn btn-outline-danger">تسجيل خروج</a>
    </div>
    <?php else : ?>
    <div class="sidebar-buttons">
      <a href="../register-form.php" class="btn btn-primary">تسجيل</a>
      <a href="../login-form.php" class="btn btn-outline-primary">دخول</a>
    </div>
    <?php endif; ?>
  </div>
    
    <!-- عنوان الصفحة -->
    <div class="text-center mb-4">
        <h3 class="mt-5">مراجعة السيرة الذاتية</h3>
        <p class="text-muted">يرجى مراجعة معلوماتك قبل الإرسال</p>
    </div>
    
    <!-- قسم مراجعة البيانات -->
    <div class="form-container">
        
        <!-- المعلومات الشخصية -->
        <div class="review-section" id="personal-info-section">
            <div class="section-header">
                <h3 class="section-title">المعلومات الشخصية</h3>
                <button class="btn btn-primary" onclick="editSection('step1')">
                    <i class="fas fa-edit"></i> تعديل
                </button>
            </div>
            
            <div class="profile-pic-container" id="profile-pic-review">
                <!-- سيتم ملؤه بواسطة JavaScript -->
            </div>
            
            <div id="personal-data-container">
                <!-- سيتم ملؤه بواسطة JavaScript -->
            </div>
        </div>
        
        <!-- الخبرات العملية -->
        <div class="review-section" id="experience-section">
            <div class="section-header">
                <h3 class="section-title">الخبرات العملية</h3>
                <button class="btn btn-primary" onclick="editSection('step2')">
                    <i class="fas fa-edit"></i> تعديل
                </button>
            </div>
            
            <div id="experiences-container">
                <!-- سيتم ملؤه بواسطة JavaScript -->
            </div>
        </div>
        
        <!-- المؤهلات التعليمية -->
        <div class="review-section" id="education-section">
            <div class="section-header">
                <h3 class="section-title">المؤهلات التعليمية</h3>
                <button class="btn btn-primary" onclick="editSection('step3')">
                    <i class="fas fa-edit"></i> تعديل
                </button>
            </div>
            
            <div id="educations-container">
                <!-- سيتم ملؤه بواسطة JavaScript -->
            </div>
        </div>
        
        <!-- المهارات -->
        <div class="review-section" id="skills-section">
            <div class="section-header">
                <h3 class="section-title">المهارات</h3>
                <button class="btn btn-primary" onclick="editSection('step4')">
                    <i class="fas fa-edit"></i> تعديل
                </button>
            </div>
            
            <div class="items-list" id="skills-container">
                <!-- سيتم ملؤه بواسطة JavaScript -->
            </div>
        </div>
        
        <!-- الإنجازات واللغات والاهتمامات -->
        <div class="review-section" id="achievements-section">
            <div class="section-header">
                <h3 class="section-title">الإنجازات واللغات والاهتمامات</h3>
                <button class="btn btn-primary" onclick="editSection('step5')">
                    <i class="fas fa-edit"></i> تعديل
                </button>
            </div>
            
            <!-- الإنجازات -->
            <div class="sub-section mb-3">
                <h4 class="sub-title">الإنجازات:</h4>
                <div id="achievements-container" class="items-list">
                    <!-- سيتم ملؤه بواسطة JavaScript -->
                </div>
                <hr>
            </div>
            
            <!-- اللغات -->
            <div class="sub-section mb-3">
                <h4 class="sub-title">اللغات:</h4>
                <div id="languages-container" class="items-list">
                    <!-- سيتم ملؤه بواسطة JavaScript -->
                </div>
                <hr>
            </div>
            
            <!-- الاهتمامات -->
            <div class="sub-section">
                <h4 class="sub-title">الاهتمامات:</h4>
                <div id="interests-container" class="items-list">
                    <!-- سيتم ملؤه بواسطة JavaScript -->
                </div>
            </div>
        </div>        
        <!-- أزرار الإجراءات -->
<div class="action-buttons">
    <button class="btn btn-outline-primary" onclick="window.location.href='step5.php'">
        <i class="fas fa-arrow-left"></i> رجوع
    </button>
    <button class="btn btn-primary" id="submit-cv-btn" onclick="submitCV()">
        إرسال <i class="fas fa-paper-plane"></i> 
    </button>
</div>
    </div>

    <script src="js/cv-main.js"></script>
    <script src="js/storage.js"></script>
    <script src="js/review-cv.js"></script>
</body>
</html>