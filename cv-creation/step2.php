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
  <title>التوظيف الذكي - الخبرة العملية</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
  <link href="css/cv-style.css" rel="stylesheet">
  <style>
    .experience-form-header {
      text-align: center;
    }

    .section-divider {
      height: 1px;
      background-color: var(--border-color);
      margin: 1.5rem 0;
    }

    .options-container {
      margin: 20px;
    }

    .option {
      display: flex;
      align-items: center;
      background-color: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 10px;
      margin: 20px 10px;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .option:hover {
      background-color: #f0f0f0;
      border-color: #ccc;
      transform: translateY(-2px);
    }

    .option input[type="radio"] {
      margin-right: 12px;
      cursor: pointer;
      width: 18px;
      height: 18px;
      order: 1;
    }

    .option-content {
      flex-grow: 1;
      text-align: right;
    }

    .option-title {
      font-weight: 600;
      margin-bottom: 5px;
      color: #444;
      font-size: 1rem;
    }

    .option-description {
      color: #666;
      font-size: 0.875rem;
    }


    .text-primary {
      color: #007bff !important;
      margin: 10px 0 0 0;
    }

    /* نمط نافذة الخبرة */
    .experience-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
    }

    .experience-window {
      background-color: white;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      padding: 20px 15px;
    }

    .experience-header {
      text-align: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border-color);
    }

    .experience-header h3 {
      color: var(--primary-color);
      font-size: 1.4rem;
      margin-bottom: 10px;
    }

    .experience-header p {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 0;
    }

    .experience-content {
      flex-grow: 1;
      overflow-y: auto;
      margin-bottom: 15px;
      padding: 0 5px;
    }

    .experience-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 15px;
      border-top: 1px solid var(--border-color);
      flex-wrap: wrap;
      gap: 10px;
    }

    .experience-buttons-right {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: flex-end;
      flex-grow: 1;
    }

    .btn {
      font-weight: 600;
      font-size: 0.9rem;
      padding: 10px 18px;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .btn-outline-secondary {
      border-color: #ddd;
    }

    .btn-outline-secondary:hover {
      background-color: #f8f9fa;
    }

    .experience-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 120px;
    }

    .experience-btn i {
      margin-right: 5px;
    }

    /* نمط نافذة إضافة الخبرة */
    .add-experience-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      overflow-y: auto;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1001;
    }

    /* أنماط شريط الأزرار الثابت */
    .form-actions {
      position: sticky;
      top: 0;
      z-index: 100;
      background-color: #fff;
      padding: 15px;
      border-bottom: 1px solid #eee;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }

    /* تعديلات على نافذة إضافة الخبرة لاستيعاب الشريط الثابت */
    .add-experience-window {
      display: flex;
      flex-direction: column;
      height: 100%;
      background: #fff;
    }

    .add-experience-content {
      flex: 1;
      overflow-y: auto;
      padding: 0 15px;
    }

    /* أنماط نموذج إدخال الخبرة الجديد */

    .action-link {
      color: #007bff;
      text-decoration: none;
      font-size: 16px;
      cursor: pointer;
      padding: 8px 15px;
      border-radius: 4px;
      transition: background-color 0.2s;
    }

    .action-link:hover {
      background-color: #f0f7ff;
    }

    .action-link.cancel {
      color: #666;
    }

    .form-title {
      color: var(--primary-color);
      margin-bottom: 5px;
      text-align: center;
      font-size: 1.4rem;
    }

    .form-subtitle {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 20px;
      text-align: center;
    }

    .date-container,
    .location-container {
      display: flex;
      gap: 10px;
    }

    .date-field,
    .location-field {
      flex: 1;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      margin: 15px 0;
    }

    .checkbox-label {
      margin-right: 10px;
      cursor: pointer;
    }

    .job-description {
      width: 100%;
      min-height: 150px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-family: 'Cairo', sans-serif;
      font-size: 16px;
    }

    .section-title {
      color: #444;
      margin: 20px 0 15px;
      font-size: 1.1rem;
    }

    /* عرض الخبرة المحفوظة */
    .experience-item {
      background-color: #f9f9f9;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      border: 1px solid #eee;
    }

    .experience-item h4 {
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .experience-item p {
      margin: 5px 0;
      color: #555;
    }

    .experience-item .period {
      color: #777;
      font-size: 0.9rem;
    }

    .experience-item .description {
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px dashed #ddd;
    }

    .btn-prev {
      margin-right: auto;
    }

    /* رسالة الخطأ */
    .error-message {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 5px;
      display: none;
    }

    .show-error {
      display: block;
    }

    /* رسالة التحذير */
    .warning-message {
      color: #ffc107;
      background-color: #fff8e1;
      padding: 10px;
      border-radius: 5px;
      margin: 15px 0;
      text-align: center;
      display: none;
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
      الخطوة 2 من 5
    </div>
    <ul class="steps-list">
      <li class="step-item completed">
        <span class="step-number">1</span>
        <span class="step-label">المعلومات الشخصية</span>
      </li>
      <li class="step-item active">
        <span class="step-number">2</span>
        <span class="step-label">الخبرة العملية</span>
      </li>
      <li class="step-item">
        <span class="step-number">3</span>
        <span class="step-label">التعليم</span>
      </li>
      <li class="step-item">
        <span class="step-number">4</span>
        <span class="step-label">المهارات</span>
      </li>
      <li class="step-item">
        <span class="step-number">5</span>
        <span class="step-label">الإنجازات واللغات</span>
      </li>
    </ul>
  </div>

  <!-- قسم الخبرة العملية -->
  <div class="form-container">
    <div class="experience-form-header">
      <h3 class="experience-titel fw-bold">الخبرة العملية</h3>
      <span class="text-muted text-center">أي من الخيارات التالية يصف خبرتك العملية؟</span>
      <div class="section-divider"></div>
    </div>

    <form id="experienceForm">
      <div class="options-container">
        <label class="option">
          <div class="option-content">
            <div class="option-title">
              لدي خبرة عملية
            </div>
            <div class="option-description">
              لدي خبرة سابقة في وظيفة واحدة أو أكثر وقمت بتطوير مهارات قيمة.
            </div>
          </div>
          <input type="radio" name="experience" value="experienced">
        </label>

        <label class="option">
          <div class="option-content">
            <div class="option-title">
              أنا في البداية
            </div>
            <div class="option-description">
              اختر هذا الخيار إذا كنت تدخل سوق العمل لأول مرة.
            </div>
          </div>
          <input type="radio" name="experience" value="beginner">
        </label>
      </div>
      <div id="experienceError" class="error-message text-center mt-3">
        الرجاء اختيار أحد الخيارات
      </div>
    </form>
  </div>

  <!-- نافذة الخبرة -->
  <div id="experienceModal" class="experience-modal">
    <div class="experience-window">
      <div class="experience-header">
        <h3 class="text-primary text-center fw-bold">الخبرة العملية</h3>
        <p>
          أضف خبرة عمل واحدة على الأقل لعرض إنجازاتك لأصحاب العمل.
        </p>
      </div>

      <div class="experience-content" id="savedExperiences">
        <!-- سيتم عرض الخبرات المحفوظة هنا -->
        <div id="no-experiences-message">
          <p>
            لم تتم إضافة أي خبرات بعد. انقر على "إضافة خبرة" للبدء.
          </p>
        </div>
      </div>

      <div class="experience-footer">
        <button id="backBtn" class="btn btn-outline-primary back-btn">
          <i class="fas fa-angle-right pe-3"></i>
          رجوع
        </button>

        <div class="experience-buttons-right">
          <button id="addExperienceBtn" class="btn btn-outline-primary experience-btn">
            إضافة خبرة
            <i class="fas fa-plus"></i>
          </button>

          <button id="nextExperienceBtn" class="btn btn-primary experience-btn">
            التالي
            <i class="fas fa-angle-left pe-3"></i>
          </button>
        </div>
      </div>

      <div id="minExperiencesWarning" class="warning-message">
        الرجاء إضافة 3 خبرات على الأقل قبل المتابعة
      </div>
    </div>
  </div>

  <!-- نافذة إضافة خبرة -->
  <div id="addExperienceModal" class="add-experience-modal">
    <div class="add-experience-window">
      <!-- شريط الأزرار الثابت -->
      <div class="form-actions">
        <a id="cancelAddExperienceBtn" class="action-link cancel fw-bold">إلغاء</a>
        <a id="saveExperienceBtn" class="action-link save fw-bold">حفظ</a>
      </div>

      <!-- محتوى النموذج القابل للتمرير -->
      <div class="add-experience-content">
        <h2 class="form-title text-primary">إضافة خبرة عمل</h2>
        <p class="form-subtitle">
          تذكر أن الوضوح والتفاصيل يساعدانك في التميز في سوق العمل التنافسي
        </p>

        <div class="form-group">
          <label for="jobTitle" class="form-label">المسمى الوظيفي</label>
          <input type="text" id="jobTitle" class="form-input" placeholder="مثال: محاسب">
          <div class="error-message" id="jobTitleError">
            الرجاء إدخال المسمى الوظيفي
          </div>
        </div>

        <div class="form-group">
          <label for="companyName" class="form-label">اسم الشركة</label>
          <input type="text" id="companyName" class="form-input" placeholder="مثال: أمازون">
          <div class="error-message" id="companyNameError">
            الرجاء إدخال اسم الشركة
          </div>
        </div>

        <div class="form-group">
          <label for="industry-display" class="form-label">المجال</label>
          <div class="select-wrapper">
            <input type="text" class="custom-select" id="industry-display" placeholder="اختر المجال"
            readonly onclick="openSelect('industry')">
            <input type="hidden" id="industry">
          </div>
          <div class="error-message" id="industryError">
            الرجاء اختيار المجال
          </div>
        </div>

        <h4 class="section-title">موقع العمل</h4>

        <div class="form-group">
          <label for="country-display" class="form-label">الدولة</label>
          <div class="select-wrapper">
            <input type="text" class="custom-select" id="country-display" placeholder="اختر الدولة" readonly
            onclick="openSelect('country')">
            <input type="hidden" id="country">
          </div>
          <div class="error-message" id="countryError">
            الرجاء اختيار الدولة
          </div>
        </div>

        <div class="form-group">
          <label for="city-display" class="form-label">المدينة</label>
          <div class="select-wrapper">
            <input type="text" class="custom-select" id="city-display" placeholder="اختر المدينة" readonly
            onclick="openSelect('city')">
            <input type="hidden" id="city">
          </div>
          <div class="error-message" id="cityError">
            الرجاء اختيار المدينة
          </div>
        </div>

        <h4 class="section-title">تاريخ البدء</h4>

        <div class="date-container">
          <div class="select-wrapper">
            <input type="text" class="custom-select" id="start-month-display" placeholder="الشهر" readonly
            onclick="openSelect('start-month')">
            <input type="hidden" id="start-month">
          </div>

          <div class="select-wrapper">
            <input type="text" class="custom-select" id="start-year-display" placeholder="السنة" readonly
            onclick="openSelect('start-year')">
            <input type="hidden" id="start-year">
          </div>
        </div>
        <div class="error-message" id="startDateError">
          الرجاء اختيار تاريخ البدء
        </div>

        <div class="checkbox-group">
          <input type="checkbox" id="still-working" name="still-working">
          <label for="still-working" class="checkbox-label">ما زلت أعمل هنا</label>
        </div>

        <div id="end-date-section">
          <h4 class="section-title">تاريخ الانتهاء</h4>

          <div class="date-container">
            <div class="select-wrapper">
              <input type="text" class="custom-select" id="end-month-display" placeholder="الشهر" readonly
              onclick="openSelect('end-month')">
              <input type="hidden" id="end-month">
            </div>

            <div class="select-wrapper">
              <input type="text" class="custom-select" id="end-year-display" placeholder="السنة" readonly
              onclick="openSelect('end-year')">
              <input type="hidden" id="end-year">
            </div>
          </div>
          <div class="error-message" id="endDateError">
            الرجاء اختيار تاريخ الانتهاء
          </div>
        </div>

        <h4 class="section-title mt-3">وصف الوظيفة (اختياري)</h4>

        <div class="form-group">
          <textarea id="jobDescription" class="job-description"
            placeholder="أضف وصفًا للوظيفة يتضمن مسؤولياتك وإنجازاتك"></textarea>
            <div class="error-message" id="jobDescriptionError">
          الرجاء ادخال وصف الوظيفة بالإنجليزي
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- نافذة الاختيار -->
  <div class="fullpage-select-overlay" id="selectOverlay"></div>
  <div class="fullpage-select-container" id="selectContainer">
    <div class="select-header">
      <h4 id="selectTitle">اختر</h4>
      <span class="close-select" onclick="closeSelect()">&times;</span>
    </div>
    <div class="select-search">
      <input type="text" id="selectSearch" placeholder="بحث..." oninput="filterOptions()">
    </div>
    <div class="select-options" id="selectOptions"></div>
  </div>

  <!-- أزرار التنقل -->
  <div class="navigation-buttons">
    <a href="step1.php" class="nav-btn btn-prev">
      <i class="fa-solid fa-angle-right"></i>
      السابق
    </a>

    <button id="nextBtn" class="nav-btn btn-next">
      التالي
      <i class="fas fa-angle-left"></i>
    </button>
  </div>

  <script src="js/cv-main.js"></script>
  <script src="js/storage.js"></script>
  <script src="js/step2.js"></script>

</body>
</html>