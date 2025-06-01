<?php
require_once 'db_connection.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['employer_id'])) {
  header('Location: employer-login.php');
  exit();
}

// جلب بيانات صاحب العمل
$employerId = $_SESSION['employer_id'];
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$employerId]);
$employer = $stmt->fetch();

if (!$employer) {
  session_destroy();
  header('Location: employer-login.php');
  exit();
}

// معالجة إرسال النموذج
// في بداية ملف add-job-post.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // جمع البيانات من النموذج
  $jobTitle = $_POST['job_title'];
  $industry = $_POST['industry']; // سيكون هذا اسم القطاع وليس ID
  $vacancies = $_POST['vacancies'];
  $country = $_POST['country']; // اسم البلد
  $city = $_POST['city']; // اسم المدينة
  $minSalary = $_POST['min_salary'];
  $maxSalary = $_POST['max_salary'];
  $employmentType = $_POST['employment_type'];
  $jobType = $_POST['job_type'];
  $skills = isset($_POST['skills']) ? $_POST['skills'] : ''; // سلسلة JSON
  $educationLevel = $_POST['education_level'];
  $experienceYears = $_POST['experience_years'];
  $jobDescription = $_POST['job_description'];

  // تحويل المهارات من JSON إلى سلسلة نصية
  $skillsArray = json_decode($skills, true);
  $skillsString = is_array($skillsArray) ? implode(', ', $skillsArray) : '';

  // إدراج البيانات في قاعدة البيانات
  try {
    $stmt = $conn->prepare("INSERT INTO job_posts
        (employer_id, job_title, industry, vacancies, country, city, min_salary, max_salary,
         employment_type, job_type, skills, education_level, experience_years, job_description,
         created_at, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')");

    $stmt->execute([
      $employerId,
      $jobTitle,
      $industry,
      $vacancies,
      $country,
      $city,
      $minSalary,
      $maxSalary,
      $employmentType,
      $jobType,
      $skillsString,
      $educationLevel,
      $experienceYears,
      $jobDescription
    ]);

    // إعادة التوجيه إلى لوحة التحكم مع رسالة نجاح
    $_SESSION['success_message'] = "تم نشر إعلان الوظيفة بنجاح!";
    header('Location: employer-dashboard.php');
    exit();

  } catch (PDOException $e) {
    $error = "حدث خطأ أثناء محاولة نشر الوظيفة: " . $e->getMessage();
  }
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
      margin: 20px auto;
      padding: 10px;
      max-width: 800px;
    }

    .section-title {
      font-size: 1rem;
    }

    .center-icon {
      font-size: 4.5rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      height: 46px;
      box-sizing: border-box;
    }

    .job-description {
      width: 100%;
      min-height: 150px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-family: 'Cairo', sans-serif;
      font-size: 16px;
      resize: none;
    }

    .normal-select {
      border: 1px solid #ddd;
      background-color: #fff;
      border-radius: 5px;
      padding: 12px 15px;
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      appearance: none !important;
      background-image: none !important;
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
      padding-left: 30px;
    }

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

    .skills-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      min-height: 60px;
    }

    .skill-tag {
      background-color: #e9f5ff;
      color: #007bff;
      padding: 5px 15px;
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      font-size: 14px;
      margin: 3px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .skill-tag-remove {
      margin-left: 8px;
      cursor: pointer;
      font-weight: bold;
      color: red;
    }

    .no-skills-placeholder {
      color: #777;
      padding: 20px;
      text-align: center;
      background-color: #f8f9fa;
      border-radius: 8px;
      width: 100%;
      margin: 10px 0;
    }

    /* إضافة إلى قسم الـ style */
    .skills-error {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
      text-align: center;
    }

    .search-options {
      position: relative;
    }

    .error-border {
      border: 1px solid #dc3545 !important;
    }

    .radio-error {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
    }

    .skills-modal {
      display: none;
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 2000;
    }

    .skills-modal-content {
      position: fixed;
      top: 30%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 90%;
      max-width: 500px;
      background: white;
      border-radius: 10px;
      padding: 20px;
    }

    .salary-range {
      display: flex;
      gap: 10px;
    }

    .salary-range input {
      flex: 1;
    }

    #addSkillsBtn {
      display: block;
      margin: 20px auto;
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 16px;
      width: 200px;
      text-align: center;
    }

    .skills-container {
      min-height: 100px;
      border: 1px dashed #ddd;
      border-radius: 8px;
      padding: 7px;
      margin-top: 15px;
      background-color: #f9f9f9;
    }

    /* نمط رسائل الخطأ في نافذة المهارات */
    #newSkillError {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
      animation: fadeIn 0.3s ease;
    }

    .skills-modal-content .error {
      border-color: #dc3545 !important;
      background-color: #fff8f8 !important;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    /* في قسم الأنماط */
    .vacancies-input {
      width: 100px;
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
  <!--  -->
  <section>
    <div>
      <div class="text-center">
        <h3>أعلن عن وظيفيتك</h3>
        <hr>
      </div>
      <div>
        <form action="add-job-post.php" method="POST" id="jobPostForm">
          <!-- المسمى الوظيفي -->
          <div class="form-group d-flex flex-column">
            <label class=" font-weight-bold">المسمى الوظيفي</label>
            <input type="text" name="job_title" id="jobTitle" class="form-input" placeholder="مثل: Accuontant">
            <div class="error-message" id="jobTitleError"></div>
          </div>

          <!-- قطاع الوظيفة -->
          <div class="form-group">
            <label for="industry-display" class="font-weight-bold">قطاع الوظيفة</label>
            <div class="select-wrapper">
              <input type="text" class="custom-select" id="industry-display" name="industry_display"
              placeholder="اختر قطاع الوظيفة" readonly onclick="openSelect('industry')">
              <input type="hidden" id="industry" name="industry">
            </div>
            <div class="error-message" id="industryError">
              يرجى اختيار قطاع الشركة
            </div>
          </div>

          <!-- عدد الشواغر -->
          <div class="form-group">
            <label for="vacancies" class="font-weight-bold">عدد الشواغر</label>
            <input type="number" name="vacancies" id="vacancies" class="form-input"
            min="1" value="1" placeholder="أدخل عدد الشواغر">
            <div class="error-message" id="vacanciesError"></div>
          </div>

          <!-- مكان الوظيفية -->
          <h4 class="section-title font-weight-bold">مكان الوظيفة</h4>
          <div class="form-group">
            <label for="country-display" class="form-label">البلد</label>
            <div class="select-wrapper">
              <input type="text" class="custom-select" id="country-display" name="country_display"
              placeholder="اختر بلد" readonly onclick="openSelect('country')">
              <input type="hidden" id="country" name="country">
            </div>
            <div class="error-message" id="countryError">
              يرجى اختيار البلد
            </div>
          </div>
          <div class="form-group">
            <label for="city-display" class="form-label">المدينة</label>
            <div class="select-wrapper">
              <input type="text" class="custom-select" id="city-display" name="city_display"
              placeholder="اختر مدينة" readonly onclick="openSelect('city')">
              <input type="hidden" id="city" name="city">
            </div>
            <div class="error-message" id="cityError">
              يرجى اختيار المدينة
            </div>
          </div>

          <!-- نطاق الراتب -->
          <div class="form-group">
            <label class="font-weight-bold">نطاق الراتب الشهري</label>
            <div class="salary-range">
              <input type="number" class="form-input" placeholder="الحد الأدنى (ريال)" id="minSalary" name="min_salary">
              <input type="number" class="form-input" placeholder="الحد الأقصى (ريال)" id="maxSalary" name="max_salary">
            </div>
            <div class="error-message" id="salaryError"></div>
          </div>

          <!-- نوع التوظيف -->
          <h4 class="section-title font-weight-bold">نوع التوظيف</h4>
          <div class="form-group">
            <div class="search-options d-flex flex-rwo flex-wrap  gap-3">
              <div class="search-option mx-2">
                <input type="radio" id="full-time" name="employment_type" value="full-time">
                <label for="full-time" class="search-label">full-time</label>
              </div>
              <div class="search-option mx-2">
                <input type="radio" id="part-time" name="employment_type" value="part-time">
                <label for="part-time" class="search-label">part-time</label>
              </div>
              <div class="search-option mx-2">
                <input type="radio" id="contract" name="employment_type" value="contract">
                <label for="contract" class="search-label">contract</label>
              </div>
              <div class="search-option mx-2">
                <input type="radio" id="temporary" name="employment_type" value="temporary">
                <label for="temporary" class="search-label">temporary</label>
              </div>
              <div class="search-option mx-2">
                <input type="radio" id="training" name="employment_type" value="training">
                <label for="training" class="search-label">training</label>
              </div>
            </div>
            <div class="error-message" id="employmentTypeError"></div>
          </div>

          <!-- نوع الوظيفة -->
          <h4 class="section-title font-weight-bold">نوع الوظيفة</h4>
          <div class="form-group">
            <div class="search-options d-flex flex-rwo gap-3">
              <div class="search-option mx-2">
                <input type="radio" id="in-office" name="job_type" value="in-office ">
                <label for="in-office" class="search-label">in-office</label>
              </div>
              <div class="search-option mx-2">
                <input type="radio" id="remote" name="job_type" value=" remote">
                <label for="remote" class="search-label"> remote</label>
              </div>
            </div>
            <div class="error-message" id="jobTypeError"></div>
          </div>

          <!-- المهارات المرغوبة -->
          <h4 class="section-title mt-3 font-weight-bold"> المهارات المرغوبة </h4>
          <div class="form-group">
            <div class="skills-container w-100">
              <div class="skills-tags" id="skillsTagsContainer">
                <div id="noSkillsPlaceholder" class="no-skills-placeholder">
                  لا توجد مهارات مرغوبة مضافة
                </div>
              </div>
            </div>
            <div class="skills-error" id="skillsError">
              يرجى إضافة مهارة واحدة على الأقل
            </div>
            <button type="button" class="btn btn-outline-primary" id="addSkillsBtn" onclick="openSkillsModal()">
              <i class="fas fa-plus"></i> إضافة مهارة
            </button>
            <input type="hidden" id="skillsInput" name="skills">
          </div>

          <!-- المؤهل العلمي -->
          <div class="form-group">
            <label for="educationLevel" class="font-weight-bold">المؤهل العلمي المطلوب</label>
            <div class="select-wrapper">
              <select class="normal-select w-100" style="border: 1px solid #ddd;" id="educationLevel" name="education_level">
                <option value="" selected disabled>اختر المؤهل العلمي</option>
                <option value="No qualification required">No qualification required</option>
                <option value="High school diploma">High school diploma</option>
                <option value="Diploma">Diploma</option>
                <option value="Bachelor's degree">Bachelor's degree</option>
                <option value="Master's degree">Master's degree</option>
                <option value="PhD">PhD</option>
              </select>
            </div>
          </div>

          <!-- سنوات الخبرة -->
          <div class="form-group">
            <label for="experienceYears" class="font-weight-bold">سنوات الخبرة المطلوبة</label>
            <div class="select-wrapper">
              <select class="normal-select w-100" id="experienceYears" name="experience_years">
                <option value="" selected disabled>اختر سنوات الخبرة</option>
                <option value=" No experience required"> No experience required</option>
                <option value=" Less than 1 year"> Less than 1 year</option>
                <option value="1-3 years">1-3 years</option>
                <option value="3-5 years">3-5 years</option>
                <option value="5-10 years">5-10 years</option>
                <option value=" More than 10 years"> More than 10 years</option>
              </select>
            </div>
          </div>
          <hr>

          <!-- الوصف الوظيفي -->
          <h4 class="section-title mt-3 font-weight-bold">الوصف الوظيفي (اختياري)</h4>
          <div class="form-group">
            <textarea id="jobDescription" class="job-description" name="job_description"
              placeholder="اضف وصفاً وظيفياً"></textarea>
          </div>

          <!-- الازرار -->
          <div class="d-flex justify-content-between w-100 p-3">
            <button id="cancelBtn" class="btn btn-outline-secondary" type="button">
              الغاء
            </button>
            <div>
              <button type="submit" id="publishBtn" class="btn btn-primary">
                <span class="mx-3">نشر</span>
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- نافذة إضافة المهارات -->
  <div id="skillsModal" class="skills-modal">
    <div class="skills-modal-content">
      <div class="select-header p-3">
        <h5>إضافة مهارة جديدة</h5>
        <span class="close-select" onclick="closeSkillsModal()">&times;</span>
      </div>
      <div class="select-search">
        <input type="text" id="newSkillInput" placeholder="اكتب المهارة ثم اضغط إضافة">
      </div>
      <div class="mt-3 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" onclick="closeSkillsModal()">إلغاء</button>
        <button type="button" class="btn btn-primary" onclick="addNewSkill()">إضافة</button>
      </div>
    </div>
  </div>

  <div class="fullpage-select-overlay" id="selectOverlay"></div>
  <div class="fullpage-select-container" id="selectContainer">
    <div class="select-header">
      <h4 id="selectTitle">chose</h4>
      <span class="close-select" onclick="closeSelect()">&times;</span>
    </div>
    <div class="select-search">
      <input type="text" id="selectSearch" placeholder="search..." oninput="filterOptions()">
    </div>
    <div class="select-options" id="selectOptions"></div>
  </div>



  <!-- Bottom Sheet Menu -->
  <div class="bottom-sheet-overlay" id="bottomSheetOverlay"></div>
  <div class="bottom-sheet" id="bottomSheet">
    <div class="bottom-sheet-header">
      <h5>إعدادات الحساب</h5>
      <span class="close-btn" onclick="closeBottomSheet()">
        <i class="fas fa-xmark"></i>
      </span>
    </div>

    <div class="bottom-sheet-item" onclick="navigateTo('profile_settings.php')">
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


  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../cv-creation/js/cv-main.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="js/employer-main.js"></script>
  <script>
    // وظائف لإدارة المهارات
    let selectedSkills = [];

    function openSkillsModal() {
      document.getElementById('skillsModal').style.display = 'block';
      document.getElementById('newSkillInput').value = '';
      document.getElementById('newSkillInput').focus();
    }

    function closeSkillsModal() {
      document.getElementById('skillsModal').style.display = 'none';
    }

    function addNewSkill() {
      const skillInput = document.getElementById('newSkillInput');
      const skill = skillInput.value.trim();

      if (skill && !selectedSkills.includes(skill)) {
        selectedSkills.push(skill);
        updateSkillsTags();
        closeSkillsModal();
      } else if (!skill) {
        alert("يرجى إدخال مهارة صالحة");
      } else {
        alert("هذه المهارة موجودة بالفعل");
      }
    }

    function removeSkill(skillToRemove) {
      selectedSkills = selectedSkills.filter(skill => skill !== skillToRemove);
      updateSkillsTags();
    }

    function updateSkillsTags() {
      const container = document.getElementById('skillsTagsContainer');
      container.innerHTML = '';

      if (selectedSkills.length === 0) {
        container.innerHTML = `
        <div id="noSkillsPlaceholder" class="no-skills-placeholder">
        لا توجد مهارات مرغوبة مضافة
        </div>
        `;
      } else {
        selectedSkills.forEach(skill => {
          const tag = document.createElement('div');
          tag.className = 'skill-tag';
          tag.innerHTML = `
          <span class="skill-tag-remove" onclick="removeSkill('${skill}')">&times;</span>
          ${skill}
          `;
          container.appendChild(tag);
        });
      }

      // تحديث حقل الإدخال المخفي بالمهارات كـ JSON
      document.getElementById('skillsInput').value = JSON.stringify(selectedSkills);
    }

    function isEnglish(text) {
      return /^[A-Za-z0-9\s\-\_\.\,\!\?\@\#\$\%\&\*\(\)\+\=\[\]\{\}\:\;\"\'\<\>\/\\\|]+$/.test(text);
    }

    function showEnglishOnlyWarning(element, message) {
      const warningId = element.id + 'EnglishWarning';
      let warningElement = document.getElementById(warningId);

      if (!warningElement) {
        warningElement = document.createElement('div');
        warningElement.id = warningId;
        warningElement.className = 'error-message';
        warningElement.style.color = '#ff9800';
        element.parentNode.insertBefore(warningElement, element.nextSibling);
      }

      warningElement.textContent = message;
      warningElement.style.display = 'block';
      element.classList.add('error');
    }

    function hideEnglishOnlyWarning(element) {
      const warningId = element.id + 'EnglishWarning';
      const warningElement = document.getElementById(warningId);

      if (warningElement) {
        warningElement.style.display = 'none';
        element.classList.remove('error');
      }
    }

    function showError(elementId, message) {
      const errorElement = document.getElementById(elementId);
      if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
      }
    }

    function hideError(elementId) {
      const errorElement = document.getElementById(elementId);
      if (errorElement) {
        errorElement.style.display = 'none';
      }
    }

    // دالة التحقق من صحة النموذج
    function validateForm() {
      let isValid = true;
      const errors = [];

      // التحقق من المسمى الوظيفي (إنجليزي فقط)
      const jobTitle = document.getElementById('jobTitle').value.trim();
      if (jobTitle === '') {
        showError('jobTitleError', 'يرجى إدخال المسمى الوظيفي');
        document.getElementById('jobTitle').classList.add('error');
        errors.push('jobTitle');
        isValid = false;
      } else if (!isEnglish(jobTitle)) {
        showEnglishOnlyWarning(document.getElementById('jobTitle'), 'يرجى إدخال المسمى الوظيفي باللغة الإنجليزية فقط');
        errors.push('jobTitle');
        isValid = false;
      } else {
        hideError('jobTitleError');
        hideEnglishOnlyWarning(document.getElementById('jobTitle'));
        document.getElementById('jobTitle').classList.remove('error');
      }

      // التحقق من قطاع الوظيفة
      const industry = document.getElementById('industry').value;
      if (industry === '') {
        showError('industryError', 'يرجى اختيار قطاع الوظيفة');
        document.getElementById('industry-display').classList.add('error');
        errors.push('industry-display');
        isValid = false;
      } else {
        hideError('industryError');
        document.getElementById('industry-display').classList.remove('error');
      }

      // في دالة validateForm()
      const vacancies = document.getElementById('vacancies').value;
      if (!vacancies || parseInt(vacancies) < 1) {
        showError('vacanciesError', 'يرجى إدخال عدد شواغر صحيح (1 على الأقل)');
        document.getElementById('vacancies').classList.add('error');
        isValid = false;
      } else {
        hideError('vacanciesError');
        document.getElementById('vacancies').classList.remove('error');
      }

      // التحقق من البلد
      const country = document.getElementById('country').value;
      if (country === '') {
        showError('countryError', 'يرجى اختيار البلد');
        document.getElementById('country-display').classList.add('error');
        errors.push('country-display');
        isValid = false;
      } else {
        hideError('countryError');
        document.getElementById('country-display').classList.remove('error');
      }

      // التحقق من المدينة
      const city = document.getElementById('city').value;
      if (city === '') {
        showError('cityError', 'يرجى اختيار المدينة');
        document.getElementById('city-display').classList.add('error');
        errors.push('city-display');
        isValid = false;
      } else {
        hideError('cityError');
        document.getElementById('city-display').classList.remove('error');
      }

      // التحقق من نطاق الراتب
      const minSalary = document.getElementById('minSalary').value;
      const maxSalary = document.getElementById('maxSalary').value;
      if (minSalary === '' || maxSalary === '') {
        showError('salaryError', 'يرجى إدخال نطاق الراتب');
        document.getElementById('minSalary').classList.add('error');
        document.getElementById('maxSalary').classList.add('error');
        errors.push(minSalary === '' ? 'minSalary': 'maxSalary');
        isValid = false;
      } else if (parseInt(minSalary) > parseInt(maxSalary)) {
        showError('salaryError', 'الحد الأدنى يجب أن يكون أقل من الحد الأقصى');
        document.getElementById('minSalary').classList.add('error');
        document.getElementById('maxSalary').classList.add('error');
        errors.push('minSalary');
        isValid = false;
      } else {
        hideError('salaryError');
        document.getElementById('minSalary').classList.remove('error');
        document.getElementById('maxSalary').classList.remove('error');
      }

      // التحقق من نوع التوظيف
      const employmentType = document.querySelector('input[name="employment_type"]:checked');
      if (!employmentType) {
        showError('employmentTypeError', 'يرجى اختيار نوع التوظيف');
        errors.push('full-time');
        isValid = false;
      } else {
        hideError('employmentTypeError');
      }

      // التحقق من نوع الوظيفة
      const jobType = document.querySelector('input[name="job_type"]:checked');
      if (!jobType) {
        showError('jobTypeError', 'يرجى اختيار نوع الوظيفة');
        errors.push('in-office');
        isValid = false;
      } else {
        hideError('jobTypeError');
      }

      // التحقق من المهارات (إنجليزي فقط)
      if (selectedSkills.length === 0) {
        showError('skillsError', 'يرجى إضافة مهارة واحدة على الأقل');
        errors.push('addSkillsBtn');
        isValid = false;
      } else {
        // التحقق من أن جميع المهارات مكتوبة بالإنجليزية
        const hasArabicSkills = selectedSkills.some(skill => !isEnglish(skill));
        if (hasArabicSkills) {
          showError('skillsError', 'يرجى إدخال المهارات باللغة الإنجليزية فقط');
          errors.push('addSkillsBtn');
          isValid = false;
        } else {
          hideError('skillsError');
        }
      }

      // التحقق من المؤهل العلمي
      const educationLevel = document.getElementById('educationLevel').value;
      if (!educationLevel) {
        showError('educationLevelError', 'يرجى اختيار المؤهل العلمي');
        document.getElementById('educationLevel').classList.add('error');
        errors.push('educationLevel');
        isValid = false;
      } else {
        hideError('educationLevelError');
        document.getElementById('educationLevel').classList.remove('error');
      }

      // التحقق من سنوات الخبرة
      const experienceYears = document.getElementById('experienceYears').value;
      if (!experienceYears) {
        showError('experienceYearsError', 'يرجى اختيار سنوات الخبرة');
        document.getElementById('experienceYears').classList.add('error');
        errors.push('experienceYears');
        isValid = false;
      } else {
        hideError('experienceYearsError');
        document.getElementById('experienceYears').classList.remove('error');
      }

      if (!isValid) {
        // إظهار رسائل الخطأ
        errors.forEach(error => {
          if (error === 'full-time' || error === 'in-office') {
            // لا يوجد عنصر محدد لهذه الأخطاء
          } else if (error === 'addSkillsBtn') {
            document.getElementById('skillsError').style.display = 'block';
          } else {
            const errorElement = document.getElementById(error + 'Error');
            if (errorElement) {
              errorElement.style.display = 'block';
            }
          }
        });

        // التمرير إلى أول حقل به خطأ
        const firstError = document.querySelector('.error');
        if (firstError) {
          firstError.scrollIntoView({
            behavior: 'smooth', block: 'center'
          });
        }
      }

      return isValid;
    }

    function addNewSkill() {
      const skillInput = document.getElementById('newSkillInput');
      const skill = skillInput.value.trim();
      const skillErrorElement = document.getElementById('newSkillError') || createSkillErrorElement(skillInput);

      // إخفاء رسالة الخطأ السابقة
      hideSkillError(skillInput);

      // التحقق من وجود قيمة
      if (!skill) {
        showSkillError(skillInput, 'يرجى إدخال مهارة صالحة');
        return;
      }

      // التحقق من أن المهارة مكتوبة بالإنجليزية
      if (!isEnglish(skill)) {
        showSkillError(skillInput, 'يرجى إدخال المهارة باللغة الإنجليزية فقط');
        return;
      }

      // التحقق من عدم تكرار المهارة
      if (selectedSkills.includes(skill)) {
        showSkillError(skillInput, 'هذه المهارة موجودة بالفعل');
        return;
      }

      // التحقق من طول المهارة
      if (skill.length > 50) {
        showSkillError(skillInput, 'يجب ألا تتجاوز المهارة 50 حرفاً');
        return;
      }

      // التحقق من عدد المهارات
      if (selectedSkills.length >= 15) {
        showSkillError(skillInput, 'يمكنك إضافة 15 مهارة كحد أقصى');
        return;
      }

      // إذا اجتازت جميع الشروط، أضف المهارة
      selectedSkills.push(skill);
      updateSkillsTags();
      closeSkillsModal();
      skillInput.value = ''; // تفريغ حقل الإدخال بعد الإضافة
    }

    // دوال مساعدة لرسائل الخطأ
    function createSkillErrorElement(inputElement) {
      const errorElement = document.createElement('div');
      errorElement.id = 'newSkillError';
      errorElement.className = 'error-message';
      errorElement.style.color = '#dc3545';
      errorElement.style.marginTop = '5px';
      inputElement.parentNode.insertBefore(errorElement, inputElement.nextSibling);
      return errorElement;
    }

    function showSkillError(inputElement, message) {
      const errorElement = document.getElementById('newSkillError') || createSkillErrorElement(inputElement);
      errorElement.textContent = message;
      errorElement.style.display = 'block';
      inputElement.classList.add('error');
      inputElement.focus();
    }

    function hideSkillError(inputElement) {
      const errorElement = document.getElementById('newSkillError');
      if (errorElement) {
        errorElement.style.display = 'none';
      }
      inputElement.classList.remove('error');
    }

    // دالة لإظهار رسالة الخطأ
    function showError(elementId, message) {
      const errorElement = document.getElementById(elementId);
      if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
      }
    }

    // دالة لإخفاء رسالة الخطأ
    function hideError(elementId) {
      const errorElement = document.getElementById(elementId);
      if (errorElement) {
        errorElement.style.display = 'none';
      }
    }

    // إضافة مستمع حدث للنموذج
    document.getElementById('jobPostForm').addEventListener('submit', function(e) {
      if (!validateForm()) {
        e.preventDefault();
      }
    });

    // إضافة مستمع حدث للزر إلغاء
    document.getElementById('cancelBtn').addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('هل أنت متأكد من إلغاء النموذج؟ سيتم فقدان جميع البيانات المدخلة.')) {
        window.location.href = 'employer-dashboard.php';
      }
    });

    // إضافة مستمعات أحداث لإزالة حالة الخطأ عند التعديل
    document.getElementById('jobTitle').addEventListener('input', function() {
      hideError('jobTitleError');
      this.classList.remove('error');
    });

    document.getElementById('minSalary').addEventListener('input', function() {
      hideError('salaryError');
      this.classList.remove('error');
      document.getElementById('maxSalary').classList.remove('error');
    });

    document.getElementById('maxSalary').addEventListener('input', function() {
      hideError('salaryError');
      this.classList.remove('error');
      document.getElementById('minSalary').classList.remove('error');
    });

    // السماح بإضافة المهارة بالضغط على Enter
    document.getElementById('newSkillInput').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        addNewSkill();
      }
    });
    
    
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