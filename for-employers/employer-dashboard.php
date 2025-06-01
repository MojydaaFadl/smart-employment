<?php
require_once 'db_connection.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['employer_id'])) {
  header('Location: employer-login.php');
  exit();
}

// جلب بيانات المستخدم
$employerId = $_SESSION['employer_id'];
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$employerId]);
$employer = $stmt->fetch();

if (!$employer) {
  session_destroy();
  header('Location: employer-login.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التوظيف الذكي - الملف الشخصي</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/fontawesome/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="css/employer-style.css">
  <style>
    section {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin: 20px 5px;
      padding: 10px;
    }

    .section-title {
      font-size: 1.3rem;
    }

    .center-icon {
      font-size: 4.5rem;
    }

    .user-info {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .user-info p {
      margin: 5px 0;
    }

    /* انماط البحث عن السيرة الذاتية */
    .form-input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      height: 46px;
      box-sizing: border-box;
    }

    /* أنماط الاختيار المشتركة */
    .custom-select {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      background-color: white;
      text-align: right;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      height: 46px;
      box-sizing: border-box;
    }

    .select-wrapper {
      position: relative;
      flex: 1;
    }

    .select-wrapper::after {
      content: "\f078";
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #777;
      pointer-events: none;
      font-size: 14px;
    }

    .custom-select,
    .phone-code-select {
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      appearance: none !important;
      background-image: none !important;
      padding-right: 15px !important;
    }

    .custom-select {
      padding-right: 15px;
      padding-left: 30px;
    }

    /* نافذة الاختيار المشتركة */
    .fullpage-select-overlay {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 2000;
      display: none;
    }

    .fullpage-select-container {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 90%;
      max-width: 800px;
      max-height: 80vh;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      overflow: hidden;
      z-index: 2001;
      display: none;
    }

    .select-header {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .select-search {
      padding: 15px;
      border-bottom: 1px solid #eee;
    }

    .select-search input {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      text-align: right;
    }

    .select-options {
      overflow-y: auto;
      max-height: 60vh;
    }

    .select-option {
      padding: 15px;
      border-bottom: 1px solid #f5f5f5;
      cursor: pointer;
      transition: background 0.2s;
      text-align: right;
    }

    .select-option:hover {
      background-color: #f8f9fa;
    }

    .select-option.selected {
      background-color: #e3f2fd;
      color: #007bff;
    }

    .close-select {
      cursor: pointer;
      color: #007bff;
      font-size: 20px;
    }

    /* رسائل الخطأ المشتركة */
    .error-message {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
      animation: fadeIn 0.3s ease;
    }

    .form-input.error,
    .custom-select.error {
      border-color: #dc3545;
      background-color: #fff8f8;
    }

    /* أنماط Bottom Sheet */
    .bottom-sheet-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      display: none;
    }

    .bottom-sheet {
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: white;
      border-top-left-radius: 5px;
      border-top-right-radius: 5px;
      padding: 20px;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
      z-index: 1001;
      transform: translateY(100%);
      transition: transform 0.3s ease;
      max-height: 70vh;
      overflow-y: auto;
    }

    .bottom-sheet.show {
      transform: translateY(0);
    }

    .bottom-sheet-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .bottom-sheet-item {
      padding: 12px 0;
      border-bottom: 1px solid #f5f5f5;
      display: flex;
      align-items: center;
    }

    .bottom-sheet-item i {
      margin-left: 10px;
      color: #666;
      width: 24px;
      text-align: center;
    }

    .bottom-sheet-item:last-child {
      border-bottom: none;
    }

    /* أيقونة الـ chatbot */
    .chatbot-icon {
      position: fixed;
      bottom: 10px;
      right: 10px;
      width: 60px;
      height: 60px;
      background-color: #4285f4;
      border-radius: 50%;
      border: 5px solid #fff;

      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      transition: all 0.3s ease;
    }

    .chatbot-icon:hover {
      background-color: #3367d6;
      transform: scale(1.1);
    }

    .chatbot-icon img {
      width: 30px;
      height: 30px;
    }
  </style>
</head>
<body>
  <!-- شريط التنقل -->
  <nav class="navbar">
    <div>
      <span class="open-btn" onclick="openSidebar()">&#9776;</span>
      <p class="navbar-brand">
        التوظيف الذكي
        <span class="navbar-brand-sec">لأصحاب العمل</span>
      </p>

    </div>
  </nav>

  <!-- القائمة الجانبية المعدلة -->
  <div id="sidebar" class="sidebar">
    <div class="sidebar-header">
      <div>
        <span class="close-btn" onclick="closeSidebar()"><i class="fa-solid fa-xmark"></i></span>
      </div>
      <div>
        <span class="menu-btn"><i class="fa-regular fa-message"></i></span>
        <span class="menu-btn"><i class="fa-regular fa-bell"></i></span>
        <span class="menu-btn" onclick="openBottomSheet()"><i class="fa-regular fa-circle-user"></i></span>
      </div>
    </div>
    <a href="employer-dashboard.php">لوحة التحكم</a>

    <!--  -->
    <div class="dropdown-container">
      <button class="dropdown-btn" onclick="toggleDropdown(this)">
        اعلانات الوظائف
        <i class="fas fa-chevron-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="add-job-post.php" style="border-bottom: #bbb 1px solid;">اعلن عن وضيفتك</a>
        <a href="my-job-posts.php">الوظائف الخاصة بي</a>
      </div>
    </div>
    <!--  -->
    <div class="dropdown-container">
      <button class="dropdown-btn" onclick="toggleDropdown(this)">
        البحث عن السيرة الذاتية
        <i class="fas fa-chevron-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="#" style="border-bottom: #bbb 1px solid;">ابحث عن مرشحين</a>
        <a href="#">ابحاثي المحفوظة</a>
      </div>
    </div>

    <a href="#">رسائل</a>
    <a href="#">الأسعار</a>
    <a href="#">English</a>
  </div>

  <!-- معلومات المستخدم -->
  <div class="user-info">
    <p>
      مرحباً <?php echo htmlspecialchars($employer['first_name'] . ' ' . $employer['last_name']); ?>
    </p>
    <p>
      شركة: <?php echo htmlspecialchars($employer['company_name']); ?>
    </p>
    <p>
      البريد الإلكتروني: <?php echo htmlspecialchars($employer['email']); ?>
    </p>
  </div>

  <!-- طلبات التوظيف -->
  <section class="job-applications">
    <div class="">
      <div>
        <p class="section-title font-weight-bold">
          عرض كافة الطلبات
        </p>
      </div>
      <div class="d-flex flex-column align-items-center no-applicatons-placeholder">
        <span class="center-icon text"><i class="fas fa-chart-line"></i></span>
        <p>
          ما من طلبات جديدة قم بالاعلان عن وظيفة جديدة!
        </p>
        <button class="btn btn-primary">اعلن عن وظيفة</button>
      </div>
    </div>
    <div class="posted-applications">
      <!-- هنا سيتم عرض طلبات التوظيف المنشورة -->
    </div>
  </section>

  <!-- البحث عن السيرة الذاتية -->
  <section class="search-for-cv">
    <div>
      <div>
        <p class="section-title font-weight-bold">
          بحث سريع عن السيرة الذاتية
        </p>
      </div>
      <div>
        <form action="">
          <!-- شريط البحث -->
          <div class="form-group d-flex flex-column">
            <input type="text" id="filterWords" class="form-input"
            placeholder="ابحث حسب المهارة، الوظيفة، المسمى الوظيفي ....">
            <div class="error-message" id="filterWordsError"></div>
          </div>
          <!-- نوع البحث -->
          <div class="form-group">
            <div class="search-options d-flex flex-rwo gap-3">
              <div class="search-option mx-2">
                <input type="radio" id="allWords" name="search" value="allWords">
                <label for="allWords" class="search-label">جميع الكلمات</label>
              </div>
              <div class="search-option mx-2">
                <input type="radio" id="anyWord" name="search" value="anyWord">
                <label for="anyWord" class="search-label">اي كلمة</label>
              </div>
            </div>
            <div class="error-message" id="searchError"></div>
          </div>
          <!-- الموقع -->
          <div class="form-group">
            <label for="residence-display" class="form-label font-weight-bold">مكان الإقامة</label>
            <div class="select-wrapper">
              <input type="text" class="custom-select" id="residence-display"
              placeholder="اختر مكان الإقامة" readonly onclick="openSelect('residence')">
              <input type="hidden" id="residence">
            </div>
            <div class="error-message" id="residenceError"></div>
          </div>
          <!-- عدد سنوات الخبرة -->
          <div class="form-group">
            <label class="form-label font-weight-bold">عدد سنوات الخبرة</label>
            <div class="experience-date-container d-flex flex-rwo">
              <!-- الحد الادنى -->
              <div class="select-wrapper flex-grow-1" style="margin-left: 10px;">
                <input type="text" class="custom-select" id="experience-min-display"
                placeholder="الحد الادنى" readonly onclick="openSelect('ex-year')">
                <input type="hidden" id="experience-min">
              </div>
              <!-- الحد الاقصى -->
              <div class="select-wrapper flex-grow-1">
                <input type="text" class="custom-select" id="experience-max-display"
                placeholder="الحد الاقصى" readonly onclick="openSelect('ex-year')">
                <input type="hidden" id="experience-max">
              </div>
              <div class="error-message" id="experienceDateError"></div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary mt-3 w-100 px-4">ابحث عن السيرة الذاتية</button>
        </form>
      </div>
    </div>
  </section>
  <!-- تفاصيل المرشحين -->
  <section class="search-for-cv">
    <div>
      <div>
        <p class="section-title font-weight-bold">
          تفاصيل المرشحين
        </p>
      </div>
      <div>
        <div class="form-group">
          <div class="select-wrapper">
            <input type="text" class="custom-select" id="filter-display"
            placeholder=" اختر عامل لتفصية المتقدمين (الجنس، الخبرة، مكان الاقامة) " readonly
            onclick="openSelect('filter')">
            <input type="hidden" id="filter">
          </div>
          <div class="error-message" id="filterError"></div>
        </div>
        <div class="d-flex flex-column align-items-center no-requst-placeholder">
          <span class="center-icon text-primary"><i class="far fa-folder-open"></i></span>
          <p>
            لا يوجد بيانات متاحة
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Bottom Sheet Menu -->
  <div class="bottom-sheet-overlay" id="bottomSheetOverlay"></div>
  <div class="bottom-sheet" id="bottomSheet">
    <div class="bottom-sheet-header">
      <h5>إعدادات الحساب</h5>
      <span class="close-btn" onclick="closeBottomSheet()">
        <i class="fas fa-xmark"></i>
      </span>
    </div>

    <div class="bottom-sheet-item" onclick="navigateTo('employer-profile_settings.php')">
      <i class="fas fa-cog"></i>
      <span>إعدادات الحساب</span>
    </div>
    <div class="bottom-sheet-item" onclick="navigateTo('../employee-dashboard.php')">
      <i class="fas fa-briefcase"></i>
      <span>باحث عن العمل</span>
    </div>
    <div class="divider"></div>
    <div class="bottom-sheet-item" onclick="navigateTo('logout.php')">
      <i class="fas fa-sign-out-alt"></i>
      <span>تسجيل خروج</span>
    </div>
  </div>

  <!-- أيقونة الـ chatbot -->
  <a href="../chatbot.php?employer=true" class="chatbot-icon">
    <img src="../images/chatbot-icon.png" alt="Chatbot">
  </a>

  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="js/employer-main.js"></script>
  <script>
    //دوال bottom-sheet
    function openBottomSheet() {
      document.getElementById('bottomSheetOverlay').style.display = 'block';
      document.getElementById('bottomSheet').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeBottomSheet() {
      document.getElementById('bottomSheetOverlay').style.display = 'none';
      document.getElementById('bottomSheet').classList.remove('show');
      document.body.style.overflow = 'auto';
    }

    // إغلاق عند النقر خارج الـ Bottom Sheet
    document.getElementById('bottomSheetOverlay').addEventListener('click', closeBottomSheet);

    // وظائف التنقل
    function navigateTo(url) {
      window.location.href = url;
      closeBottomSheet();
    }
  </script>
</body>

</html>