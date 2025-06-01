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
  <title>التوظيف الذكي - الإنجازات واللغات</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/fontawesome/css/all.css">
  <link rel="stylesheet" href="css/cv-style.css">

  <style>
    /* تنسيقات الأقسام */
    .section-container {
      background-color: #fff;
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      border: 1px solid #eee;
      transition: all 0.3s ease;
    }

    .section-container:hover {
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    #achievements-section,#languages-section,#interests-section {
      background-color: #fff;
      border-right: 5px solid #007bff;
    }

    .section-title {
      font-size: 1.25rem;
      color: #2c3e50;
      margin-bottom: 1.5rem;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
      position: relative;
      font-weight: 600;
    }

    .section-title:after {
      content: "";
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 80px;
      height: 2px;
      background-color: var(--primary-color);
    }

    /* العناصر الداخلية */
    .achievement-item,
    .language-item,
    .interest-item {
      background-color: #fff;
      border-radius: 8px;
      padding: 40px 20px 20px 20px;
      margin-bottom: 10px;
      border: 1px solid #eee;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
      position: relative;
      transition: all 0.3s ease;
    }

    .achievement-item:hover,
    .language-item:hover,
    .interest-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .achievement-title {
      font-weight: 600;
      color: #2980b9;
      margin-bottom: 8px;
      font-size: 1.05rem;
    }

    .achievement-description {
      color: #555;
      font-size: 0.95rem;
    }

    .language-name {
      font-weight: 600;
      color: #2980b9;
      margin-bottom: 5px;
    }

    .language-level {
      color: #555;
      font-size: 0.95rem;
    }

    .interest-text {
      color: #333;
      font-size: 0.95rem;
    }

    /* أزرار الإضافة */
    #add-achievement-btn,
    #add-language-btn,
    #add-interest-btn {
      width: 100%;
      margin-top: 15px;
      padding: 10px 20px;
      font-weight: 600;
      border-radius: 6px;
      transition: all 0.3s ease;
      border: none;
    }

    #add-achievement-btn:hover,
    #add-language-btn:hover,
    #add-interest-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    /* أزرار التعديل والحذف */
    .item-actions {
      position: absolute;
      left: 5px;
      top: 5px;
      display: flex;
      gap: 5px;
    }

    .edit-btn, .remove-btn {
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      color: white;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .edit-btn {
      background-color: #007bff;
    }

    .remove-btn {
      background-color: #e74c3c;
    }

    .edit-btn:hover {
      background-color: #2980b9;
      transform: scale(1.1);
    }

    .remove-btn:hover {
      background-color: #c0392b;
      transform: scale(1.1);
    }

    /* نوافذ التعديل */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 25px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .close-modal {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      transition: color 0.2s;
    }

    .close-modal:hover {
      color: #333;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #444;
    }

    .form-input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      transition: border 0.3s;
    }

    .form-input:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    textarea.form-input {
      min-height: 120px;
      resize: vertical;
    }

    /* مستوى اللغة */
    .language-level-options {
      display: flex;
      gap: 15px;
      margin: 15px 0;
      flex-wrap: wrap;
    }

    .level-option {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .level-option input {
      margin: 0;
    }

    /* رسائل الخطأ */
    .error-message {
      color: #e74c3c;
      font-size: 0.85rem;
      margin-top: 5px;
      display: none;
    }

    /* رسالة التحقق من اللغة الإنجليزية */
    .english-error {
      color: #e74c3c;
      font-size: 0.85rem;
      margin-top: 5px;
      display: none;
    }

    .empty-placeholder {
      text-align: center;
      padding: 30px;
      color: #777;
      font-size: 1rem;
      background-color: #f9f9f9;
      border-radius: 8px;
      border: 1px dashed #ddd;
      margin-bottom: 10px;
    }

    .btn-prev {
      margin-right: auto;
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

  <!-- مؤشر الخطوات -->
  <div class="steps-container">
    <div class="steps-header">
      الخطوة 5 من 5
    </div>
    <ul class="steps-list">
      <li class="step-item completed">
        <span class="step-number">1</span>
        <span class="step-label">المعلومات الشخصية</span>
      </li>
      <li class="step-item completed">
        <span class="step-number">2</span>
        <span class="step-label">الخبرات</span>
      </li>
      <li class="step-item completed">
        <span class="step-number">3</span>
        <span class="step-label">التعليم</span>
      </li>
      <li class="step-item completed">
        <span class="step-number">4</span>
        <span class="step-label">المهارات</span>
      </li>
      <li class="step-item active">
        <span class="step-number">5</span>
        <span class="step-label">الإنجازات واللغات</span>
      </li>
    </ul>
  </div>

  <!-- محتوى الصفحة -->
  <div class="form-container">
    <h3 class="text-center">الإنجازات، اللغات والاهتمامات</h3>
    <p class="text-center text-muted">
      أكمل سيرتك الذاتية بإضافة معلومات إضافية تبرز شخصيتك
    </p>

    <!-- قسم الإنجازات الرئيسية -->
    <div class="section-container" id="achievements-section">
      <h4 class="section-title">الإنجازات الرئيسية</h4>
      <div id="achievements-container">
        <!-- الإنجازات المضافوة تظهر هنا -->
      </div>
      <button id="add-achievement-btn" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة إنجاز
      </button>
    </div>

    <!-- قسم اللغات -->
    <div class="section-container" id="languages-section">
      <h4 class="section-title">اللغات</h4>
      <div id="languages-container">
        <!-- اللغات المضافوة تظهر هنا -->
      </div>
      <button id="add-language-btn" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة لغة
      </button>
    </div>

    <!-- قسم الاهتمامات -->
    <div class="section-container" id="interests-section">
      <h4 class="section-title">الاهتمامات</h4>
      <div id="interests-container">
        <!-- الاهتمامات المضافة تظهر هنا -->
      </div>
      <button id="add-interest-btn" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة اهتمام
      </button>
    </div>
  </div>

  <!-- نافذة إضافة الإنجازات -->
  <div id="achievementModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" data-modal="achievementModal">&times;</span>
      <h3>إضافة إنجاز جديد</h3>

      <div class="form-group">
        <label for="modal-achievement-title">عنوان الإنجاز</label>
        <input type="text" id="modal-achievement-title" class="form-input" placeholder="مثال: تطوير نظام إدارة المشاريع">
        <div class="error-message" id="achievement-title-error">
          عنوان الإنجاز مطلوب
        </div>
        <div class="english-error" id="achievement-title-english-error">
          يجب إدخال البيانات باللغة الإنجليزية فقط
        </div>
      </div>

      <div class="form-group">
        <label for="modal-achievement-description">وصف الإنجاز</label>
        <textarea id="modal-achievement-description" class="form-input"
          placeholder="صف ما قمت به والتأثير الذي أحدثه..."></textarea>
        <div class="error-message" id="achievement-desc-error">
          وصف الإنجاز مطلوب
        </div>
        <div class="english-error" id="achievement-desc-english-error">
          يجب إدخال البيانات باللغة الإنجليزية فقط
        </div>
      </div>

      <button id="save-achievement-btn" class="btn btn-primary">
        حفظ الإنجاز
      </button>
    </div>
  </div>

  <!-- نافذة إضافة لغة -->
  <div id="languageModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" data-modal="languageModal">&times;</span>
      <h3>إضافة لغة جديدة</h3>

      <div class="form-group">
        <label for="modal-language-name">اللغة</label>
        <input type="text" id="modal-language-name" class="form-input" placeholder="مثال: English">
        <div class="error-message" id="language-name-error">
          اسم اللغة مطلوب
        </div>
        <div class="english-error" id="language-name-english-error">
          يجب إدخال البيانات باللغة الإنجليزية فقط
        </div>
      </div>

      <div class="form-group">
        <label>مستوى الإتقان</label>
        <div class="language-level-options">
          <label class="level-option">
            <input type="radio" name="modal-language-level" value="Beginner">
            <span>مبتدئ</span>
          </label>
          <label class="level-option">
            <input type="radio" name="modal-language-level" value="Intermediate">
            <span>متوسط</span>
          </label>
          <label class="level-option">
            <input type="radio" name="modal-language-level" value="Advanced">
            <span>متقدم</span>
          </label>
          <label class="level-option">
            <input type="radio" name="modal-language-level" value="Native">
            <span>لغتي الأم</span>
          </label>
        </div>
        <div class="error-message" id="language-level-error">
          مستوى الإتقان مطلوب
        </div>
      </div>

      <button id="save-language-btn" class="btn btn-primary">
        حفظ اللغة
      </button>
    </div>
  </div>

  <!-- نافذة إضافة اهتمام -->
  <div id="interestModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" data-modal="interestModal">&times;</span>
      <h3>إضافة اهتمام جديد</h3>

      <div class="form-group">
        <label for="modal-interest-input">الاهتمام</label>
        <input type="text" id="modal-interest-input" class="form-input" placeholder="مثال: Reading, Travel...">
        <div class="error-message" id="interest-error">
          الاهتمام مطلوب
        </div>
        <div class="english-error" id="interest-english-error">
          يجب إدخال البيانات باللغة الإنجليزية فقط
        </div>
      </div>

      <button id="save-interest-btn" class="btn btn-primary">
        حفظ الاهتمام
      </button>
    </div>
  </div>

  <!-- أزرار التنقل -->
  <div class="navigation-buttons">
    <a href="step4.php" class="nav-btn btn-prev">
      <i class="fa-solid fa-angle-right"></i>
      السابق
    </a>

    <button id="submit-btn" class="nav-btn btn-next">
      إنهاء
      <i class="fas fa-angle-left"></i>
    </button>
  </div>

  <script src="js/cv-main.js"></script>
  <script src="js/storage.js"></script>
  <script src="js/step5.js"></script>
</body>
</html>