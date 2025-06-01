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
  <title>التوظيف الذكي - المعلومات الشخصية</title>

  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/fontawesome/css/all.css">
  <link rel="stylesheet" href="css/cv-style.css">
  <style>
    .summary {
      Width: 100%;
      Min-height: 150px;
      Padding: 15px;
      Border: 1px solid #ddd;
      Border-radius: 5px;
      Font-family: inherit;
      Font-size: 16px;
      Resize: none;
    }

    .phone-container {
      Display: flex;
      Gap: 10px;
    }

    .phone-number-field {
      Flex: 1;
    }

    .phone-code-field {
      Width: 120px;
    }

    .gender-options {
      Display: flex;
      Gap: 15px;
      Margin-top: 8px;
    }


    .btn-prev {
      margin: auto;
    }

    /* تنسيقات إضافية للصورة الشخصية */
    .profile-picture {
      Width: 150px;
      Height: 150px;
      Border-radius: 50%;
      Overflow: hidden;
      Border: 2px solid #ddd;
      Transition: all 0.3s ease;
    }

    .profile-picture.drag-over {
      Border: 2px dashed #4CAF50;
      Background-color: rgba(76, 175, 80, 0.1);
    }

    .remove-picture-btn:hover {
      Transform: scale(1.1);
      Transition: all 0.2s ease;
    }

    .profile-picture-placeholder {
      Width: 100%;
      Height: 100%;
      Display: flex;
      Align-items: center;
      Justify-content: center;
      Background-color: #f5f5f5;
      Color: #999;
      Font-size: 40px;
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
  
  <div class="steps-container">
    <div class="steps-header">
      الخطوة 1 من 5
    </div>
    <ul class="steps-list">
      <li class="step-item active">
        <span class="step-number">1</span>
        <span class="step-label">المعلومات الشخصية</span>
      </li>
      <li class="step-item">
        <span class="step-number">2</span>
        <span class="step-label">الخبرات</span>
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

  <div class="form-container">
    <div class="text-center">
      <h3>المعلومات الشخصية</h3>
      <span class="text-muted">شارك بعض المعلومات عن نفسك للبدء</span>
      <hr>
    </div>

    <div class="form-group profile-picture-container">
      <div class="profile-picture" id="profilePicture">
        <div class="profile-picture-placeholder">
          <i class="fas fa-user"></i>
        </div>
      </div>
      <input type="file" id="profilePictureInput" class="profile-picture-input" accept="image/*">
      <label id="profilePicturelabel" for="profilePictureInput" class="profile-picture-label">إضافة صورة شخصية</label>
    </div>

    <div class="form-group">
      <label for="firstName" class="form-label">الاسم الأول</label>
      <input type="text" id="firstName" class="form-input" placeholder="أدخل الاسم الأول" dir="rtl">
      <div class="error-message" id="firstNameError"></div>
    </div>

    <div class="form-group">
      <label for="lastName" class="form-label">الاسم الأخير</label>
      <input type="text" id="lastName" class="form-input" placeholder="أدخل الاسم الأخير" dir="rtl">
      <div class="error-message" id="lastNameError"></div>
    </div>

    <div class="form-group">
      <label for="jobTitle" class="form-label">المسمى الوظيفي</label>
      <input type="text" id="jobTitle" class="form-input" placeholder="أدخل المسمى الوظيفي" dir="rtl">
      <div class="error-message" id="jobTitleError"></div>
    </div>

    <h4 class="section-title mt-3">ملخص</h4>
    <div class="form-group">
      <textarea id="summary" class="summary" dir="rtl"
        placeholder="أضف ملخصًا عن مسؤولياتك وإنجازاتك"></textarea>
      <div class="error-message" id="summaryError"></div>
    </div>

    <h4>موقع الإقامة</h4>
    <div class="form-group">
      <label for="country-display-locattion" class="form-label">الدولة</label>
      <div class="select-wrapper">
        <input type="text" class="custom-select" id="country-display-locattion" placeholder="اختر الدولة"
        readonly onclick="openSelect('country-locattion')" dir="rtl">
        <input type="hidden" id="country-locattion">
      </div>
      <div class="error-message" id="countrylocattionError">
        الرجاء اختيار الدولة
      </div>
    </div>

    <div class="form-group">
      <label for="city-display-locattion" class="form-label">المدينة</label>
      <div class="select-wrapper">
        <input type="text" class="custom-select" id="city-display-locattion" placeholder="اختر المدينة" readonly
        onclick="openSelect('city-locattion')" dir="rtl">
        <input type="hidden" id="city-locattion">
      </div>
      <div class="error-message" id="citylocattionError">
        الرجاء اختيار المدينة
      </div>
    </div>

    <div class="form-group">
      <label for="email" class="form-label">البريد الإلكتروني</label>
      <input type="email" id="email" class="form-input" placeholder="أدخل بريدك الإلكتروني" dir="rtl">
      <div class="error-message" id="emailError"></div>
    </div>

    <div class="form-group">
      <label class="form-label">رقم الهاتف</label>
      <div class="phone-container">
      <div class="phone-number-field">
          <input type="tel" id="phone" class="form-input" placeholder="رقم الهاتف" dir="rtl">
        </div>
        <div class="phone-code-field">
          <div class="phone-code-wrapper">
            <input type="text" class="phone-code-select" id="country-code-display" placeholder="+966" readonly
            onclick="openCountrySelect()" dir="rtl">
            <input type="hidden" id="country-code">
          </div>
        </div>
      </div>
      <div class="error-message" id="phoneError"></div>
      <div class="error-message" id="countryCodeError"></div>
    </div>
  </div>

  <div class="fullpage-select-overlay" id="selectOverlay"></div>
  <div class="fullpage-select-container" id="selectContainer">
    <div class="select-header">
      <h4 id="selectTitle">اختيار</h4>
      <span class="close-select" onclick="closeSelect()">&times;</span>
    </div>
    <div class="select-search">
      <input type="text" id="selectSearch" placeholder="بحث..." oninput="filterOptions()" dir="rtl">
    </div>
    <div class="select-options" id="selectOptions"></div>
  </div>

  <div class="fullpage-select-overlay" id="countryOverlay"></div>
  <div class="fullpage-select-container" id="countrySelect">
    <div class="select-header">
      <h4>اختر رمز الدولة</h4>
      <span class="close-select" onclick="closeCountrySelect()">&times;</span>
    </div>
    <div class="select-search">
      <input type="text" id="countrySearch" placeholder="ابحث عن دولة..." oninput="filterCountries()" dir="rtl">
    </div>
    <div class="select-options" id="countryOptions"></div>
  </div>

  <div class="navigation-buttons">
    <button onclick="navigateTo()" class="nav-btn btn-prev">
      <i class="fa-solid fa-angle-right"></i>
      إلغاء
    </button>

    <button id="nextBtn" class="nav-btn btn-next">
      التالي
      <i class="fas fa-angle-left"></i>
    </button>
  </div>

  <script src="js/cv-main.js"></script>
  <script src="js/storage.js"></script>
  <script src="js/step1.js"></script>
</body>
</html>