<?php
// بدء جلسة
session_start();

// رسالة نجاح التسجيل
if (isset($_SESSION['registration_success'])) {
  echo '<div class="alert-success">' . $_SESSION['registration_success'] . '</div>';
  unset($_SESSION['registration_success']);
}

// معالجة التسجيل إذا تم إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // اتصال بقاعدة البيانات
  $host = "localhost";
  $username = "root";
  $password = "";
  $dbname = "smart_employment";

  try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // تنظيف المدخلات
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $major = trim($_POST['major']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phoneCode = trim($_POST['phone_code']);
    $phoneNumber = trim($_POST['phone_number']);
    $isPublic = isset($_POST['is_public']) ? 1 : 0;

    // التحقق من البريد الإلكتروني
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            showEmailError("البريد الإلكتروني مسجل مسبقاً");
                        });
                    </script>';
    } else {
      // تسجيل المستخدم
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO users (first_name, last_name, major, email, password, phone_code, phone_number, is_public)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $conn->prepare($sql);
      $stmt->execute([$firstName, $lastName, $major, $email, $hashedPassword, $phoneCode, $phoneNumber, $isPublic]);

      // تعيين بيانات الجلسة
      $_SESSION['user_id'] = $conn->lastInsertId();
      $_SESSION['email'] = $email;
      $_SESSION['first_name'] = $firstName;
      $_SESSION['last_name'] = $lastName;
      $_SESSION['major'] = $major;
      $_SESSION['registration_success'] = "تم تسجيلك بنجاح! مرحبا بك.";

      header("Location: upload-create-profile.php");
      exit();
    }
  } catch(PDOException $e) {
    echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        alert("حدث خطأ في الخادم: ' . addslashes($e->getMessage()) . '");
                    });
                </script>';
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إنشاء حساب جديد - التوظيف الذكي</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/fontawesome/css/all.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Register */
    .register-container {
      max-width: 500px;
      margin: 30px auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .register-header {
      text-align: center;
      margin-bottom: 25px;
    }

    .register-header h2 {
      margin-bottom: 5px;
    }

    .register-header p {
      color: #666;
    }

    /* تعديلات حقول الإدخال */
    .form-group {
      margin-bottom: 25px;
      position: relative;
    }

    .form-group label {
      position: absolute;
      right: 12px;
      top: 12px;
      color: #999;
      background-color: #fff;
      padding: 0 5px;
      transition: all 0.3s;
      pointer-events: none;
    }

    .form-group input, .form-group select {
      width: 100%;
      padding: 12px;
      border: 1px solid #868686;
      border-radius: 4px;
      box-sizing: border-box;
      transition: all 0.3s;
    }

    .form-group input:focus, .form-group select:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-group input:focus+label,
    .form-group input:not(:placeholder-shown)+label,
    .form-group select:focus+label,
    .form-group select:not([value=""]):not(:focus)+label {
      top: -10px;
      font-size: 12px;
      color: #007bff;
    }

    /* أنماط حقل التخصص */
    .form-group select {
      background-color: white;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }

    /* تعديلات حقل كلمة المرور */
    .password-field {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #999;
    }

    .password-toggle:hover {
      color: #555;
    }

    .phone-input {
      display: flex;
    }

    .phone-code-select {
      width: 100px !important;
      min-width: 80px !important;
      max-width: 80px !important;
      padding: 10px !important;
      font-size: 14px !important;
      border: 1px solid #868686;
      border-radius: 4px;
      background-color: white;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      text-align: right;
    }

    .phone-input {
      position: relative;
    }

    .phone-input::after {
      content: "\f078";
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      position: absolute;
      top: 50%;
      left: 10px;
      transform: translateY(-50%);
      color: #777;
      pointer-events: none;
      font-size: 12px;
    }

    .phone-code-input {
      width: 100px;
      padding: 12px;
      border: 1px solid #868686;
      border-radius: 4px;
      display: none;
    }

    .phone-number input {
      border-radius: 4px;
      width: 100%;
    }

    .profile-public {
      display: flex;
      align-items: center;
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 4px;
      margin: 20px 0;
      font-size: 14px;
    }

    .profile-public input[type="checkbox"] {
      margin-left: 10px;
      width: 18px;
      height: 18px;
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
    }

    .terms a {
      color: #007bff;
      text-decoration: none;
    }

    .terms a:hover {
      text-decoration: underline;
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
    }

    .login-link a {
      color: #007bff;
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    /* أنماط رسائل الخطأ */
    .error-message {
      color: #dc3545;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }

    .form-group.error input,
    .form-group.error select {
      border-color: #dc3545;
    }

    .form-group.success input,
    .form-group.success select {
      border-color: #28a745;
    }

    .phone-input.error {
      border: 1px solid #dc3545;
      border-radius: 4px;
      padding: 1px;
    }

    .phone-input.success {
      border: 1px solid #28a745;
      border-radius: 4px;
      padding: 1px;
    }

    /* تأثير الهزة عند الخطأ */
    @keyframes shake {
      0%, 100% {
        transform: translateX(0);
      }
      20%, 60% {
        transform: translateX(-5px);
      }
      40%, 80% {
        transform: translateX(5px);
      }
    }

    .form-group.error input,
    .form-group.error select {
      animation: shake 0.5s;
    }

    /* أنماط القائمة المنسدلة */
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
      max-width: 500px;
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

    /* رسالة النجاح */
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      display: none;
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
    <a href="index22.php">الرئيسية</a>
    <a href="#">البحث عن وظيفة</a>
    <a href="<?php echo isset($_SESSION['user_id']) ? 'upload-create-profile.php' : 'register-form.php'; ?>">إنشاء ملفك الشخصي</a>
    <a href="#">بريميوم</a>
    <a href="#">المدونة</a>
    <?php if (!isset($_SESSION['user_id'])): ?>
    <a href="for-employers/employer-dashboard.php">لأصحاب العمل</a>
    <?php endif; ?>
    <a href="#">English</a>
  </div>

  <!-- نموذج تسجيل الدخول-->
  <div class="register-container">
    <div class="register-header">
      <h2>إنشاء حساب جديد</h2>
      <p>
        هل لديك حساب؟ <a href="login-form.php">سجّل الدخول</a>
      </p>
    </div>

    <form id="registerForm" method="POST" novalidate>
      <div class="form-group">
        <input type="text" id="first-name" name="first_name" placeholder=" " required>
        <label for="first-name">الاسم الأول</label>
        <div class="error-message" id="first-name-error">
          الاسم الأول مطلوب ويجب أن يحتوي على أحرف فقط
        </div>
      </div>

      <div class="form-group">
        <input type="text" id="last-name" name="last_name" placeholder=" " required>
        <label for="last-name">اسم العائلة</label>
        <div class="error-message" id="last-name-error">
          اسم العائلة مطلوب ويجب أن يحتوي على أحرف فقط
        </div>
      </div>

      <div class="form-group">
        <select id="major" name="major" required>
          <option value="" disabled selected>اختر التخصص العام</option>
          <option value="Information Technology">Information Technology</option>
          <option value="Engineering">Engineering</option>
          <option value="Medicine and Healthcare">Medicine and Healthcare</option>
          <option value="Accounting and Finance">Accounting and Finance</option>
          <option value="Marketing and Sales">Marketing and Sales</option>
          <option value="Education">Education</option>
          <option value="Arts and Design">Arts and Design</option>
          <option value="Law">Law</option>
          <option value="Science">Science</option>
          <option value="Business and Management">Business and Management</option>
          <option value="Agriculture">Agriculture</option>
          <option value="Tourism and Hospitality">Tourism and Hospitality</option>
          <option value="Other">Other</option>
        </select>
        <label for="major">التخصص العام</label>
        <div class="error-message" id="major-error">
          التخصص العام مطلوب
        </div>
      </div>

      <div class="form-group">
        <input type="email" id="email" name="email" placeholder=" " required>
        <label for="email">البريد الإلكتروني</label>
        <div class="error-message" id="email-error">
          البريد الإلكتروني مطلوب ويجب أن يكون صالحاً
        </div>
      </div>

      <div class="form-group">
        <div class="password-field">
          <input type="password" id="password" name="password" placeholder=" " required minlength="8">
          <label for="password">كلمة السر</label>
          <span class="password-toggle" id="togglePassword">
            <i class="far fa-eye"></i>
          </span>
        </div>
        <div class="error-message" id="password-error">
          كلمة السر مطلوبة ويجب أن تحتوي على 8 أحرف على الأقل
        </div>
      </div>

      <div class="form-group">
        <div class="phone-input" id="phone-input-container">
          <input type="tel" id="phone" name="phone_number" placeholder="رقم الهاتف" required>
          <input type="text" class="phone-code-select" id="country-code-display" name="phone_code"
          placeholder="+967" readonly onclick="openCountrySelect()">
          <input type="hidden" id="country-code">
        </div>
        <div class="error-message" id="phone-error">
          رقم الهاتف مطلوب ويجب أن يكون صالحاً
        </div>
      </div>

      <div class="profile-public">
        <input type="checkbox" id="public-profile" name="is_public" checked>
        <label for="public-profile">تفعيل الملف الشخصي العام</label>
      </div>

      <button type="submit" class="btn-register">إنشاء حسابي</button>
    </form>

    <div class="terms">
      بالضغط على "إنشاء حسابي"، فإنك توافق على <a href="#">شروط الاستخدام</a> و <a href="#">بيان الخصوصية</a>
      الخاص بنا.
    </div>
  </div>

  <!-- قائمة الدول الكاملة مع بحث -->
  <div class="fullpage-select-overlay" id="countryOverlay"></div>

  <div class="fullpage-select-container" id="countrySelect">
    <div class="select-header">
      <h4>اختر رمز الدولة</h4>
      <span class="close-select" onclick="closeCountrySelect()">&times;</span>
    </div>
    <div class="select-search">
      <input type="text" id="countrySearch" placeholder="ابحث عن دولة..." oninput="filterCountries()">
    </div>
    <div class="select-options" id="countryOptions">
      <!-- سيتم ملء الخيارات بواسطة الجافاسكريبت -->
    </div>
  </div>

  <script>
    // بيانات رموز الدول
    const countryCodes = [{
      code: "+967",
      name: "اليمن",
      flag: "🇾🇪"
    },
      {
        code: "+966",
        name: "السعودية",
        flag: "🇸🇦"
      },
      {
        code: "+971",
        name: "الإمارات",
        flag: "🇦🇪"
      },
      {
        code: "+20",
        name: "مصر",
        flag: "🇪🇬"
      },
      {
        code: "+962",
        name: "الأردن",
        flag: "🇯🇴"
      },
      {
        code: "+974",
        name: "قطر",
        flag: "🇶🇦"
      },
      {
        code: "+968",
        name: "عمان",
        flag: "🇴🇲"
      },
    ];

    // فتح قائمة الدول
    function openCountrySelect() {
      document.getElementById('countryOverlay').style.display = 'block';
      document.getElementById('countrySelect').style.display = 'block';
      renderCountries(countryCodes);
      document.getElementById('countrySearch').focus();
    }

    // إغلاق قائمة الدول
    function closeCountrySelect() {
      document.getElementById('countryOverlay').style.display = 'none';
      document.getElementById('countrySelect').style.display = 'none';
    }

    // عرض الدول في القائمة
    function renderCountries(countriesToShow) {
      const container = document.getElementById('countryOptions');
      container.innerHTML = '';

      if (countriesToShow.length === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'select-option';
        emptyMsg.textContent = 'لا توجد نتائج';
        container.appendChild(emptyMsg);
        return;
      }

      countriesToShow.forEach(country => {
        const option = document.createElement('div');
        option.className = 'select-option';
        option.innerHTML = `${country.flag} ${country.name} <span style="color: #007bff; margin-right: 10px;">${country.code}</span>`;
        option.onclick = () => {
          document.getElementById('country-code-display').value = country.code;
          document.getElementById('country-code').value = country.code;
          closeCountrySelect();
          validatePhone();
        };
        container.appendChild(option);
      });
    }

    // تصفية الدول حسب البحث
    function filterCountries() {
      const searchTerm = document.getElementById('countrySearch').value.toLowerCase();
      const filtered = countryCodes.filter(country =>
        country.name.toLowerCase().includes(searchTerm) ||
        country.code.includes(searchTerm)
      );
      renderCountries(filtered);
    }

    // إغلاق القائمة عند النقر خارجها
    document.getElementById('countryOverlay').onclick = closeCountrySelect;

    // اظهار واخفاء كلمة السر
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text': 'password';
      password.setAttribute('type', type);
      this.querySelector('i').classList.toggle('fa-eye');
      this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // دوال شريط التنقل
    function openSidebar() {
      document.getElementById("sidebar").style.right = "0";
    }

    function closeSidebar() {
      document.getElementById("sidebar").style.right = "-100%";
    }

    // التحقق من صحة البيانات
    document.getElementById('registerForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const isValidFirstName = validateFirstName();
      const isValidLastName = validateLastName();
      const isValidMajor = validateMajor();
      const isValidEmail = validateEmail();
      const isValidPassword = validatePassword();
      const isValidPhone = validatePhone();

      if (isValidFirstName && isValidLastName && isValidMajor && isValidEmail && isValidPassword && isValidPhone) {
        this.submit();
      }
    });

    // التحقق من الاسم الأول
    function validateFirstName() {
      const firstName = document.getElementById('first-name');
      const error = document.getElementById('first-name-error');
      // const regex = /^[\u0600-\u06FF\s]+$/; // للغة العربية فقط

      if (firstName.value.trim() === '') {
        showError(firstName, error, 'الاسم الأول مطلوب');
        return false;
      } else {
        showSuccess(firstName, error);
        return true;
      }
    }

    // التحقق من اسم العائلة
    function validateLastName() {
      const lastName = document.getElementById('last-name');
      const error = document.getElementById('last-name-error');
      // const regex = /^[\u0600-\u06FF\s]+$/; // للغة العربية فقط

      if (lastName.value.trim() === '') {
        showError(lastName, error, 'اسم العائلة مطلوب');
        return false;
      } else {
        showSuccess(lastName, error);
        return true;
      }
    }

    // التحقق من التخصص العام
    function validateMajor() {
      const major = document.getElementById('major');
      const error = document.getElementById('major-error');

      if (major.value === '') {
        showError(major, error, 'التخصص العام مطلوب');
        return false;
      } else {
        showSuccess(major, error);
        return true;
      }
    }

    // التحقق من البريد الإلكتروني
    function validateEmail() {
      const email = document.getElementById('email');
      const error = document.getElementById('email-error');
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (email.value.trim() === '') {
        showError(email, error, 'البريد الإلكتروني مطلوب');
        return false;
      } else if (!regex.test(email.value)) {
        showError(email, error, 'البريد الإلكتروني غير صالح');
        return false;
      } else {
        showSuccess(email, error);
        return true;
      }
    }

    // التحقق من كلمة السر
    function validatePassword() {
      const password = document.getElementById('password');
      const error = document.getElementById('password-error');

      if (password.value.trim() === '') {
        showError(password, error, 'كلمة السر مطلوبة');
        return false;
      } else if (password.value.length < 8) {
        showError(password, error, 'كلمة السر يجب أن تحتوي على 8 أحرف على الأقل');
        return false;
      } else {
        showSuccess(password, error);
        return true;
      }
    }

    // التحقق من رقم الهاتف
    function validatePhone() {
      const phone = document.getElementById('phone');
      const phoneContainer = document.getElementById('phone-input-container');
      const error = document.getElementById('phone-error');
      const countryCode = document.getElementById('country-code-display').value;
      const regex = /^[0-9]+$/;

      if (phone.value.trim() === '') {
        showError(phoneContainer, error, 'رقم الهاتف مطلوب');
        return false;
      } else if (!regex.test(phone.value)) {
        showError(phoneContainer, error, 'رقم الهاتف يجب أن يحتوي على أرقام فقط');
        return false;
      } else if (countryCode.trim() === '') {
        showError(phoneContainer, error, 'رمز الدولة مطلوب');
        return false;
      } else {
        showSuccess(phoneContainer, error);
        return true;
      }
    }

    // عرض رسالة الخطأ للبريد الإلكتروني
    function showEmailError(message) {
      const emailInput = document.getElementById('email');
      const emailError = document.getElementById('email-error');

      emailInput.parentElement.classList.add('error');
      emailError.textContent = message;
      emailError.style.display = 'block';
      emailInput.focus();

      // هزة بصرية للإشارة إلى الخطأ
      emailInput.style.animation = 'shake 0.5s';
      setTimeout(() => {
        emailInput.style.animation = '';
      }, 500);
    }

    // عرض رسالة الخطأ
    function showError(input, errorElement, message) {
      const formGroup = input.closest('.form-group') || input.parentElement.closest('.form-group');
      formGroup.classList.add('error');
      formGroup.classList.remove('success');
      errorElement.textContent = message;
      errorElement.style.display = 'block';
    }

    // عرض حالة النجاح
    function showSuccess(input, errorElement) {
      const formGroup = input.closest('.form-group') || input.parentElement.closest('.form-group');
      formGroup.classList.remove('error');
      formGroup.classList.add('success');
      errorElement.style.display = 'none';
    }

    // إضافة أحداث التحقق أثناء الكتابة
    document.getElementById('first-name').addEventListener('input', validateFirstName);
    document.getElementById('last-name').addEventListener('input', validateLastName);
    document.getElementById('major').addEventListener('change', validateMajor);
    document.getElementById('email').addEventListener('input', validateEmail);
    document.getElementById('password').addEventListener('input', validatePassword);
    document.getElementById('phone').addEventListener('input', validatePhone);
    document.getElementById('country-code-display').addEventListener('change', validatePhone);

    // تعيين رمز الدولة الافتراضي
    window.onload = function () {
      document.getElementById('country-code-display').value = '+967';
      document.getElementById('country-code').value = '+967';
    };
  </script>
</body>

</html>