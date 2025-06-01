<?php
require_once 'db_connection.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // استقبال البيانات من النموذج
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $companyName = trim($_POST['companyName'] ?? '');
  $country = $_POST['country'] ?? '';
  $city = $_POST['city'] ?? '';
  $companySize = $_POST['companySize'] ?? '';
  $phoneCode = $_POST['country-code'] ?? '';
  $phone = trim($_POST['phone'] ?? '');
  $marketingAgree = isset($_POST['marketing-agree']) ? 1 : 0;

  // التحقق من صحة البيانات
  if (empty($firstName)) $errors['firstName'] = 'الاسم الأول مطلوب';
  elseif (strlen($firstName) < 2) $errors['firstName'] = 'الاسم الأول يجب أن يكون على الأقل حرفين';

  if (empty($lastName)) $errors['lastName'] = 'اسم العائلة مطلوب';
  elseif (strlen($lastName) < 2) $errors['lastName'] = 'اسم العائلة يجب أن يكون على الأقل حرفين';

  if (empty($email)) $errors['email'] = 'البريد الإلكتروني مطلوب';
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'البريد الإلكتروني غير صالح';
  else {
    $stmt = $conn->prepare("SELECT id FROM employers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $errors['email'] = 'البريد الإلكتروني مسجل مسبقاً';
  }

  if (empty($password)) $errors['password'] = 'كلمة المرور مطلوبة';
  elseif (strlen($password) < 8) $errors['password'] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';

  if (empty($companyName)) $errors['companyName'] = 'اسم الشركة مطلوب';
  elseif (strlen($companyName) < 3) $errors['companyName'] = 'اسم الشركة يجب أن يكون على الأقل 3 أحرف';

  if (empty($country)) $errors['country'] = 'البلد مطلوب';
  if (empty($city)) $errors['city'] = 'المدينة مطلوبة';
  if (empty($companySize)) $errors['companySize'] = 'حجم الشركة مطلوب';
  if (empty($phoneCode)) $errors['countryCode'] = 'الرمز مطلوب';

  if (empty($phone)) $errors['phone'] = 'رقم الهاتف مطلوب';
  elseif (!preg_match('/^[0-9]{8,15}$/', $phone)) $errors['phone'] = 'رقم الهاتف غير صالح';

  // إذا لم تكن هناك أخطاء، قم بتسجيل المستخدم
  if (empty($errors)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
      $stmt = $conn->prepare("INSERT INTO employers (first_name, last_name, email, password, company_name, country, city, company_size, phone_code, phone, marketing_agree) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $companyName, $country, $city, $companySize, $phoneCode, $phone, $marketingAgree]);

      $success = true;
    } catch (PDOException $e) {
      $errors['general'] = 'حدث خطأ أثناء التسجيل: ' . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل دخول صاحب العمل</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/fontawesome/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    @font-face {
      font-family: "Cairo";
      src: url("../fonts/Cairo\ Regular\ 400.ttf");
    }

    body {
      background-color: #f8f9fa;
      font-family: 'Cairo';
      text-align: right;
    }

    .register-container {
      max-width: 800px;
      margin: 30px auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: 4px;
    }

    .alert-danger {
      color: #721c24;
      background-color: #f8d7da;
      border-color: #f5c6cb;
    }

    .alert-success {
      color: #155724;
      background-color: #d4edda;
      border-color: #c3e6cb;
    }

    .form-group {
      position: relative;
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      font-family: 'Cairo', sans-serif;
      height: 46px;
      box-sizing: border-box;
    }

    .form-group input:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .password-toggle {
      position: absolute;
      left: 12px;
      top: 70%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #000000;
    }

    .phone-container {
      display: flex;
      flex-direction: row;
      gap: 10px;
      justify-content: space-between;
    }

    .phone-number-field {
      width: 100%;
    }

    .phone-code-field {
      width: 120px;
    }

    .agree-terms {
      display: flex;
      align-items: center;
      margin: 20px 0;
      padding: 10px;
      border-radius: 10px;
    }

    .agree-terms input {
      margin-left: 10px;
    }

    .btn-register {
      width: 100%;
      padding: 12px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      font-size: 16px;
    }

    .btn-register:hover {
      background-color: #0056b3;
    }

    .terms {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
      color: #666;
      border-top: 1px solid #eee;
      padding-top: 20px;
    }

    .terms a {
      color: #007bff;
      text-decoration: none;
    }

    .terms a:hover {
      text-decoration: underline;
    }

    /* أنماط الاختيار المشتركة */
    .custom-select,
    .phone-code-select {
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

    .select-wrapper,
    .phone-code-wrapper {
      position: relative;
      flex: 1;
    }

    .select-wrapper::after,
    .phone-code-wrapper::after {
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

    .job-seeker-link {
      text-align: center;
      margin-top: 15px;
    }

    .job-seeker-link a {
      color: #007bff;
      text-decoration: none;
    }

    .job-seeker-link a:hover {
      text-decoration: underline;
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

    .custom-select,
    .phone-code-select {
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      appearance: none !important;
      background-image: none !important;
      padding-right: 15px !important;
    }

    /* رسائل الخطأ المشتركة */
    .error-message {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-5px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .agree-terms input[type="checkbox"] {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      width: 15px;
      height: 18px;
      border: 2px solid #007bff;
      border-radius: 4px;
      outline: none;
      cursor: pointer;
    }

    .agree-terms input[type="checkbox"]:checked {
      background-color: #007bff;
      position: relative;
    }

    .agree-terms input[type="checkbox"]:checked::after {
      content: "\2713";
      position: absolute;
      color: white;
      font-size: 14px;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
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
      </p>
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
    <a href="../employee-dashboard.php">للباحثين عن العمل</a>
    <?php endif; ?>
    <a href="#">English</a>
  </div>

  <!--   register-container -->
  <div class="register-container">
    <div>
      <h2 class="text-center font-weight-bold">سجّل للحصول على حساب شركة</h2>
    </div>

    <div class="login-link text-center m-3">
      هل لديك حساب؟ <a href="employer-login.php">سجّل الدخول</a>
      <div class="job-seeker-link">
        <a href="../register-form.php">هل تبحث عن وظيفة؟</a>
      </div>
      <hr>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $error): ?>
      <p>
        <?php echo $error; ?>
      </p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success">
      تم التسجيل بنجاح! يمكنك الآن <a href="employer-login.php">تسجيل الدخول</a>.
    </div>
    <?php endif; ?>

    <form id="companyRegisterForm" method="POST">
      <div class="form-group">
        <label for="firstName" class="form-label">الاسم الأول</label>
        <input type="text" id="firstName" name="firstName" class="form-input" placeholder="أدخل الاسم الأول" value="<?php echo htmlspecialchars($firstName ?? ''); ?>">
        <div class="error-message" id="firstNameError"></div>
      </div>

      <div class="form-group">
        <label for="lastName" class="form-label">اسم العائلة</label>
        <input type="text" id="lastName" name="lastName" class="form-input" placeholder="أدخل اسم العائلة" value="<?php echo htmlspecialchars($lastName ?? ''); ?>">
        <div class="error-message" id="lastNameError"></div>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input type="email" id="email" name="email" class="form-input" placeholder="أدخل بريدك الإلكتروني" value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <div class="error-message" id="emailError"></div>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">كلمة المرور</label>
        <input type="password" id="password" name="password" placeholder="كلمة المرور">
        <span class="password-toggle" id="togglePassword">
          <i class="far fa-eye"></i>
        </span>
        <div class="error-message" id="passwordError"></div>
      </div>

      <div class="form-group">
        <label for="companyName" class="form-label">اسم الشركة</label>
        <input type="text" id="companyName" name="companyName" class="form-input" placeholder="أدخل اسم الشركة" value="<?php echo htmlspecialchars($companyName ?? ''); ?>">
        <div class="error-message" id="companyNameError"></div>
      </div>

      <h4 class="section-title">موقع الشركة</h4>
      <div class="form-group">
        <label for="country-display" class="form-label">البلد</label>
        <div class="select-wrapper">
          <input type="text" class="custom-select" id="country-display" placeholder="اختر بلد" readonly
          onclick="openSelect('country')" value="<?php echo isset($country) ? htmlspecialchars($country) : ''; ?>">
          <input type="hidden" id="country" name="country" value="<?php echo htmlspecialchars($country ?? ''); ?>">
        </div>
        <div class="error-message" id="countryError">
          يرجى اختيار البلد
        </div>
      </div>
      <div class="form-group">
        <label for="city-display" class="form-label">المدينة</label>
        <div class="select-wrapper">
          <input type="text" class="custom-select" id="city-display" placeholder="اختر مدينة" readonly
          onclick="openSelect('city')" value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>">
          <input type="hidden" id="city" name="city" value="<?php echo htmlspecialchars($city ?? ''); ?>">
        </div>
        <div class="error-message" id="cityError">
          يرجى اختيار المدينة
        </div>
      </div>

      <div class="form-group">
        <label for="company-size-display" class="form-label">حجم الشركة</label>
        <div class="select-wrapper">
          <input type="text" class="custom-select" id="company-size-display" placeholder="اختر حجم الشركة"
          readonly onclick="openSelect('companySize')" value="<?php echo isset($companySize) ? htmlspecialchars($companySize) : ''; ?>">
          <input type="hidden" id="companySize" name="companySize" value="<?php echo htmlspecialchars($companySize ?? ''); ?>">
        </div>
        <div class="error-message" id="companySizeError"></div>
      </div>

      <div class="form-group">
        <label class="form-label">رقم الهاتف</label>
        <div class="phone-container">
          <div class="phone-number-field">
            <input type="tel" id="phone" name="phone" class="form-input" placeholder="رقم الهاتف" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            <div class="error-message" id="phoneError"></div>
          </div>
          <div class="phone-code-field">
            <div class="phone-code-wrapper">
              <input type="text" class="phone-code-select" id="country-code-display" placeholder="+967"
              readonly onclick="openCountrySelect()" value="<?php echo isset($phoneCode) ? htmlspecialchars($phoneCode) : '+967'; ?>">
              <input type="hidden" id="country-code" name="country-code" value="<?php echo htmlspecialchars($phoneCode ?? ''); ?>">
              <div class="error-message" id="countryCodeError"></div>
            </div>
          </div>
        </div>

        <div class="agree-terms">
          <input type="checkbox" id="marketing-agree" name="marketing-agree" <?php echo isset($marketingAgree) && $marketingAgree ? 'checked' : ''; ?>>
          <label for="marketing-agree">أوافق على تلقي رسائل إلكترونية من التوظيف الذكي حول المنتجات والرسائل
            التسويقية والعروض</label>
        </div>

        <button type="submit" class="btn-register">سجّل الآن</button>

        <div class="terms">
          بالتسجيل في موقع التوظيف الذكي، فإنك توافق على <a href="#">شروط وأحكام استخدام الموقع</a>. يحترم التوظيف
          الذكي خصوصيتك ولن يشارك أي معلومات تخصك مع أي طرف آخر.
        </div>
      </form>
    </div>

    <div class="fullpage-select-overlay" id="selectOverlay"></div>
    <div class="fullpage-select-container" id="selectContainer">
      <div class="select-header">
        <h4 id="selectTitle">اختيار</h4>
        <span class="close-select" onclick="closeSelect()">&times;</span>
      </div>
      <div class="select-search">
        <input type="text" id="selectSearch" placeholder="ابحث...">
      </div>
      <div class="select-options" id="selectOptions"></div>
    </div>

    <script>
      // دوال شريط التنقل
      function openSidebar() {
        document.getElementById("sidebar").style.right = "0";
      }

      function closeSidebar() {
        document.getElementById("sidebar").style.right = "-100%";
      }

      function toggleDropdown(button) {
        const container = button.parentElement;
        const dropdown = container.querySelector(".dropdown-content");
        const icon = button.querySelector("i");

        button.classList.toggle("active");

        if (dropdown.style.display === "block") {
          dropdown.style.display = "none";
        } else {
          dropdown.style.display = "block";
        }
      }


      const selectData = {
        country: [{
          id: 'algeria',
          name: 'Algeria',
          code: '+213'
        },
          {
            id: 'bahrain',
            name: 'Bahrain',
            code: '+973'
          },
          {
            id: 'comoros',
            name: 'Comoros',
            code: '+269'
          },
          {
            id: 'djibouti',
            name: 'Djibouti',
            code: '+253'
          },
          {
            id: 'egypt',
            name: 'Egypt',
            code: '+20'
          },
          {
            id: 'iraq',
            name: 'Iraq',
            code: '+964'
          },
          {
            id: 'jordan',
            name: 'Jordan',
            code: '+962'
          },
          {
            id: 'kuwait',
            name: 'Kuwait',
            code: '+965'
          },
          {
            id: 'lebanon',
            name: 'Lebanon',
            code: '+961'
          },
          {
            id: 'libya',
            name: 'Libya',
            code: '+218'
          },
          {
            id: 'mauritania',
            name: 'Mauritania',
            code: '+222'
          },
          {
            id: 'morocco',
            name: 'Morocco',
            code: '+212'
          },
          {
            id: 'oman',
            name: 'Oman',
            code: '+968'
          },
          {
            id: 'palestine',
            name: 'Palestine',
            code: '+970'
          },
          {
            id: 'qatar',
            name: 'Qatar',
            code: '+974'
          },
          {
            id: 'saudi_arabia',
            name: 'Saudi Arabia',
            code: '+966'
          },
          {
            id: 'somalia',
            name: 'Somalia',
            code: '+252'
          },
          {
            id: 'sudan',
            name: 'Sudan',
            code: '+249'
          },
          {
            id: 'syria',
            name: 'Syria',
            code: '+963'
          },
          {
            id: 'tunisia',
            name: 'Tunisia',
            code: '+216'
          },
          {
            id: 'united_arab_emirates',
            name: 'United Arab Emirates',
            code: '+971'
          },
          {
            id: 'yemen',
            name: 'Yemen',
            code: '+967'
          }],
        city: {
          algeria: [{
            id: 'algiers',
            name: 'Algiers'
          },
            {
              id: 'oran',
              name: 'Oran'
            },
            {
              id: 'constantine',
              name: 'Constantine'
            },
            {
              id: 'annaba',
              name: 'Annaba'
            },
            {
              id: 'blida',
              name: 'Blida'
            }],
          bahrain: [{
            id: 'manama',
            name: 'Manama'
          },
            {
              id: 'muharraq',
              name: 'Muharraq'
            },
            {
              id: 'rifa',
              name: 'Rifa'
            },
            {
              id: 'hamad_town',
              name: 'Hamad Town'
            }],
          comoros: [{
            id: 'moroni',
            name: 'Moroni'
          },
            {
              id: 'mutsamudu',
              name: 'Mutsamudu'
            },
            {
              id: 'fomboni',
              name: 'Fomboni'
            }],
          djibouti: [{
            id: 'djibouti_city',
            name: 'Djibouti City'
          },
            {
              id: 'ali_sabieh',
              name: 'Ali Sabieh'
            },
            {
              id: 'tadjoura',
              name: 'Tadjoura'
            }],
          egypt: [{
            id: 'cairo',
            name: 'Cairo'
          },
            {
              id: 'alexandria',
              name: 'Alexandria'
            },
            {
              id: 'giza',
              name: 'Giza'
            },
            {
              id: 'shubra_el_kheima',
              name: 'Shubra El Kheima'
            },
            {
              id: 'port_said',
              name: 'Port Said'
            }],
          iraq: [{
            id: 'baghdad',
            name: 'Baghdad'
          },
            {
              id: 'basra',
              name: 'Basra'
            },
            {
              id: 'mosul',
              name: 'Mosul'
            },
            {
              id: 'erbil',
              name: 'Erbil'
            },
            {
              id: 'sulaymaniyah',
              name: 'Sulaymaniyah'
            }],
          jordan: [{
            id: 'amman',
            name: 'Amman'
          },
            {
              id: 'irbid',
              name: 'Irbid'
            },
            {
              id: 'zarqa',
              name: 'Zarqa'
            },
            {
              id: 'aqaba',
              name: 'Aqaba'
            },
            {
              id: 'madaba',
              name: 'Madaba'
            }],
          kuwait: [{
            id: 'kuwait_city',
            name: 'Kuwait City'
          },
            {
              id: 'al_ahmadi',
              name: 'Al Ahmadi'
            },
            {
              id: 'hawalli',
              name: 'Hawalli'
            },
            {
              id: 'al_farwaniyah',
              name: 'Al Farwaniyah'
            }],
          lebanon: [{
            id: 'beirut',
            name: 'Beirut'
          },
            {
              id: 'tripoli',
              name: 'Tripoli'
            },
            {
              id: 'sidon',
              name: 'Sidon'
            },
            {
              id: 'tyre',
              name: 'Tyre'
            },
            {
              id: 'nabatieh',
              name: 'Nabatieh'
            }],
          libya: [{
            id: 'tripoli',
            name: 'Tripoli'
          },
            {
              id: 'benghazi',
              name: 'Benghazi'
            },
            {
              id: 'misrata',
              name: 'Misrata'
            },
            {
              id: 'al_bayda',
              name: 'Al Bayda'
            }],
          mauritania: [{
            id: 'nouakchott',
            name: 'Nouakchott'
          },
            {
              id: 'nouadhibou',
              name: 'Nouadhibou'
            },
            {
              id: 'kaedi',
              name: 'Kaedi'
            }],
          morocco: [{
            id: 'casablanca',
            name: 'Casablanca'
          },
            {
              id: 'rabat',
              name: 'Rabat'
            },
            {
              id: 'fes',
              name: 'Fes'
            },
            {
              id: 'marrakesh',
              name: 'Marrakesh'
            },
            {
              id: 'tangier',
              name: 'Tangier'
            }],
          oman: [{
            id: 'muscat',
            name: 'Muscat'
          },
            {
              id: 'salalah',
              name: 'Salalah'
            },
            {
              id: 'sohar',
              name: 'Sohar'
            },
            {
              id: 'nizwa',
              name: 'Nizwa'
            }],
          palestine: [{
            id: 'jerusalem',
            name: 'Jerusalem'
          },
            {
              id: 'gaza',
              name: 'Gaza'
            },
            {
              id: 'ramallah',
              name: 'Ramallah'
            },
            {
              id: 'bethlehem',
              name: 'Bethlehem'
            },
            {
              id: 'hebron',
              name: 'Hebron'
            }],
          qatar: [{
            id: 'doha',
            name: 'Doha'
          },
            {
              id: 'al_rayyan',
              name: 'Al Rayyan'
            },
            {
              id: 'al_wakrah',
              name: 'Al Wakrah'
            },
            {
              id: 'al_khor',
              name: 'Al Khor'
            }],
          saudi_arabia: [{
            id: 'riyadh',
            name: 'Riyadh'
          },
            {
              id: 'jeddah',
              name: 'Jeddah'
            },
            {
              id: 'mecca',
              name: 'Mecca'
            },
            {
              id: 'medina',
              name: 'Medina'
            },
            {
              id: 'dammam',
              name: 'Dammam'
            }],
          somalia: [{
            id: 'mogadishu',
            name: 'Mogadishu'
          },
            {
              id: 'hargeisa',
              name: 'Hargeisa'
            },
            {
              id: 'bosaso',
              name: 'Bosaso'
            },
            {
              id: 'kismayo',
              name: 'Kismayo'
            }],
          sudan: [{
            id: 'khartoum',
            name: 'Khartoum'
          },
            {
              id: 'omdurman',
              name: 'Omdurman'
            },
            {
              id: 'port_sudan',
              name: 'Port Sudan'
            },
            {
              id: 'kassala',
              name: 'Kassala'
            }],
          syria: [{
            id: 'damascus',
            name: 'Damascus'
          },
            {
              id: 'aleppo',
              name: 'Aleppo'
            },
            {
              id: 'homs',
              name: 'Homs'
            },
            {
              id: 'latakia',
              name: 'Latakia'
            },
            {
              id: 'hama',
              name: 'Hama'
            }],
          tunisia: [{
            id: 'tunis',
            name: 'Tunis'
          },
            {
              id: 'sfax',
              name: 'Sfax'
            },
            {
              id: 'sousse',
              name: 'Sousse'
            },
            {
              id: 'kairouan',
              name: 'Kairouan'
            },
            {
              id: 'bizerte',
              name: 'Bizerte'
            }],
          united_arab_emirates: [{
            id: 'dubai',
            name: 'Dubai'
          },
            {
              id: 'abu_dhabi',
              name: 'Abu Dhabi'
            },
            {
              id: 'sharjah',
              name: 'Sharjah'
            },
            {
              id: 'al_ain',
              name: 'Al Ain'
            },
            {
              id: 'ajman',
              name: 'Ajman'
            }],
          yemen: [{
            id: 'sanaa',
            name: 'Sanaa'
          },
            {
              id: 'aden',
              name: 'Aden'
            },
            {
              id: 'taiz',
              name: 'Taiz'
            },
            {
              id: 'hodeidah',
              name: 'Hodeidah'
            },
            {
              id: 'ibbb',
              name: 'Ibb'
            }]
        },
        companySize: [{
          id: 'small_1_50_employees',
          name: 'Small (1-50 employees)'
        },
          {
            id: 'medium_51_200_employees',
            name: 'Medium (51-200 employees)'
          },
          {
            id: 'large_201_500_employees',
            name: 'Large (201-500 employees)'
          },
          {
            id: 'enterprise_500plus_employees',
            name: 'Enterprise (500+ employees)'
          }]
      };

      let currentSelectType = '';
      let currentSelectedId = '';
      let currentOptions = [];

      document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          passwordInput.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      });

      function openSelect(type) {
        currentSelectType = type;
        const selectContainer = document.getElementById('selectContainer');
        const selectTitle = document.getElementById('selectTitle');
        const selectOptions = document.getElementById('selectOptions');
        const selectOverlay = document.getElementById('selectOverlay');

        let title = '';
        switch (type) {
          case 'country': title = 'اختر البلد'; break;
          case 'city': title = 'اختر المدينة'; break;
          case 'companySize': title = 'اختر حجم الشركة'; break;
        }
        selectTitle.textContent = title;

        selectOptions.innerHTML = '';
        let options = [];

        if (type === 'city') {
          const country = document.getElementById('country').value;
          if (!country) {
            alert('يجب اختيار البلد أولاً');
            return;
          }
          options = selectData.city[country] || [];
        } else {
          options = selectData[type] || [];
        }

        currentOptions = options;

        options.forEach(option => {
          const optionElement = document.createElement('div');
          optionElement.className = 'select-option';
          optionElement.textContent = option.name;
          optionElement.dataset.id = option.id;
          optionElement.onclick = function () {
            selectOption(option);
          };
          selectOptions.appendChild(optionElement);
        });

        selectContainer.style.display = 'block';
        selectOverlay.style.display = 'block';
      }

      function openCountrySelect() {
        currentSelectType = 'countryCode';
        const selectContainer = document.getElementById('selectContainer');
        const selectTitle = document.getElementById('selectTitle');
        const selectOptions = document.getElementById('selectOptions');
        const selectOverlay = document.getElementById('selectOverlay');

        selectTitle.textContent = 'اختر رمز الدولة';
        selectOptions.innerHTML = '';

        const options = selectData.country;
        currentOptions = options;

        options.forEach(option => {
          const optionElement = document.createElement('div');
          optionElement.className = 'select-option';
          optionElement.textContent = `${option.name} (${option.code})`;
          optionElement.dataset.id = option.id;
          optionElement.onclick = function () {
            selectOption(option);
          };
          selectOptions.appendChild(optionElement);
        });

        selectContainer.style.display = 'block';
        selectOverlay.style.display = 'block';
      }

      function selectOption(option) {
        if (currentSelectType === 'countryCode') {
          document.getElementById('country-code-display').value = option.code;
          document.getElementById('country-code').value = option.id;
        } else {
          const displayId = currentSelectType === 'companySize' ? 'company-size-display': `${currentSelectType}-display`;
          const hiddenId = currentSelectType === 'companySize' ? 'companySize': currentSelectType;

          const displayField = document.getElementById(displayId);
          const hiddenField = document.getElementById(hiddenId);

          if (displayField && hiddenField) {
            displayField.value = option.name;
            hiddenField.value = option.id;

            if (currentSelectType === 'country') {
              document.getElementById('city-display').value = '';
              document.getElementById('city').value = '';
            }
          }
        }

        closeSelect();
      }

      function closeSelect() {
        document.getElementById('selectContainer').style.display = 'none';
        document.getElementById('selectOverlay').style.display = 'none';
      }

      document.getElementById('selectSearch').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const options = document.querySelectorAll('.select-option');

        options.forEach(option => {
          const text = option.textContent.toLowerCase();
          if (text.includes(searchTerm)) {
            option.style.display = 'block';
          } else {
            option.style.display = 'none';
          }
        });
      });

      document.getElementById('selectOverlay').addEventListener('click', closeSelect);

      function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        if (!errorElement) return;

        errorElement.textContent = message;
        errorElement.style.display = 'block';

        const inputField = document.getElementById(fieldId.replace('Error', ''));
        if (inputField) inputField.classList.add('error');
      }

      function hideError(fieldId) {
        const errorElement = document.getElementById(fieldId);
        if (!errorElement) return;

        errorElement.style.display = 'none';

        const inputField = document.getElementById(fieldId.replace('Error', ''));
        if (inputField) inputField.classList.remove('error');
      }

      document.getElementById('companyRegisterForm').addEventListener('submit', function (e) {
        let isValid = true;

        const firstName = document.getElementById('firstName').value.trim();
        if (!firstName) {
          showError('firstNameError', 'الاسم الأول مطلوب');
          isValid = false;
        } else if (firstName.length < 2) {
          showError('firstNameError', 'الاسم الأول يجب أن يكون على الأقل حرفين');
          isValid = false;
        } else {
          hideError('firstNameError');
        }

        const lastName = document.getElementById('lastName').value.trim();
        if (!lastName) {
          showError('lastNameError', 'اسم العائلة مطلوب');
          isValid = false;
        } else if (lastName.length < 2) {
          showError('lastNameError', 'اسم العائلة يجب أن يكون على الأقل حرفين');
          isValid = false;
        } else {
          hideError('lastNameError');
        }

        const email = document.getElementById('email').value.trim();
        if (!email) {
          showError('emailError', 'البريد الإلكتروني مطلوب');
          isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          showError('emailError', 'البريد الإلكتروني غير صالح');
          isValid = false;
        } else {
          hideError('emailError');
        }

        const password = document.getElementById('password').value;
        if (!password) {
          showError('passwordError', 'كلمة المرور مطلوبة');
          isValid = false;
        } else if (password.length < 8) {
          showError('passwordError', 'كلمة المرور يجب أن تكون 8 أحرف على الأقل');
          isValid = false;
        } else {
          hideError('passwordError');
        }

        const companyName = document.getElementById('companyName').value.trim();
        if (!companyName) {
          showError('companyNameError', 'اسم الشركة مطلوب');
          isValid = false;
        } else if (companyName.length < 3) {
          showError('companyNameError', 'اسم الشركة يجب أن يكون على الأقل 3 أحرف');
          isValid = false;
        } else {
          hideError('companyNameError');
        }

        const country = document.getElementById('country').value;
        if (!country) {
          showError('countryError', 'البلد مطلوب');
          isValid = false;
        } else {
          hideError('countryError');
        }

        const city = document.getElementById('city').value;
        if (!city) {
          showError('cityError', 'المدينة مطلوبة');
          isValid = false;
        } else {
          hideError('cityError');
        }

        const companySize = document.getElementById('companySize').value;
        if (!companySize) {
          showError('companySizeError', 'حجم الشركة مطلوب');
          isValid = false;
        } else {
          hideError('companySizeError');
        }

        const phone = document.getElementById('phone').value.trim();
        const countryCode = document.getElementById('country-code').value;

        if (!countryCode) {
          showError('countryCodeError', 'الرمز مطلوب');
          isValid = false;
        } else {
          hideError('countryCodeError');
        }

        if (!phone) {
          showError('phoneError', 'رقم الهاتف مطلوب');
          isValid = false;
        } else if (!/^[0-9]{8,15}$/.test(phone)) {
          showError('phoneError', 'رقم الهاتف غير صالح');
          isValid = false;
        } else {
          hideError('phoneError');
        }

        if (!isValid) {
          e.preventDefault();
        }
      });

      document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('input', function () {
          const fieldName = this.id;
          hideError(`${fieldName}Error`);
        });
      });

      document.querySelectorAll('.custom-select, .phone-code-select').forEach(select => {
        select.addEventListener('click', function () {
          const fieldName = this.id.replace('-display', '');
          hideError(`${fieldName}Error`);
        });
      });
    </script>
  </body>
</html>