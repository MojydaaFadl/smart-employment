<?php
require_once 'db_connection.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($email) || empty($password)) {
    $error = 'البريد الإلكتروني وكلمة المرور مطلوبان';
  } else {
    $stmt = $conn->prepare("SELECT * FROM employers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['employer_id'] = $user['id'];
      $_SESSION['employer_email'] = $user['email'];
      $_SESSION['employer_name'] = $user['first_name'] . ' ' . $user['last_name'];
      $_SESSION['employer_company'] = $user['company_name'];

      header('Location: employer-dashboard.php');
      exit();
    } else {
      $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
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
    .login-container {
      max-width: 500px;
      margin: 30px auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }
    .login-container h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
      font-weight: 600;
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

    .input-group {
      position: relative;
      margin-bottom: 30px;
    }

    .input-group input {
      width: 100%;
      padding: 12px;
      border: 1px solid #868686;
      border-radius: 6px;
      transition: all 0.3s;
      font-size: 16px;
    }

    .input-group input:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .input-group label {
      position: absolute;
      right: 12px;
      top: 12px;
      color: #999;
      background-color: #fff;
      padding: 0 5px;
      transition: all 0.3s;
      pointer-events: none;
    }

    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
      top: -10px;
      font-size: 12px;
      color: #007bff;
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

    .forgot-password {
      text-align: right;
      margin-bottom: 20px;
    }

    .forgot-password a {
      color: #007bff;
      text-decoration: none;
      transition: color 0.3s;
    }

    .forgot-password a:hover {
      color: #0056b3;
      text-decoration: underline;
    }

    .btn-login {
      width: 100%;
      padding: 12px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.3s;
      font-size: 16px;
      margin-bottom: 20px;
    }

    .btn-login:hover {
      background-color: #0069d9;
    }

    .create-account {
      text-align: center;
      margin-top: 25px;
      color: #6c757d;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    .create-account a {
      color: #007bff;
      text-decoration: none;
      font-weight: 500;
    }

    .create-account a:hover {
      text-decoration: underline;
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

  <!-- نموذج تسجيل الدخول -->
  <div class="login-container">
    <h2>سجل الدخول إلى حساب صاحب العمل الخاص بك</h2>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="input-group">
        <input type="text" id="email" name="email" placeholder=" " required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <label for="email">عنوان البريد الإلكتروني أو اسم المستخدم</label>
      </div>

      <div class="input-group">
        <input type="password" id="password" name="password" placeholder=" " required>
        <label for="password">كلمة المرور</label>
        <span class="password-toggle" id="togglePassword">
          <i class="far fa-eye"></i>
        </span>
      </div>

      <div class="forgot-password">
        <a href="#">هل نسيت كلمة المرور؟</a>
      </div>

      <button type="submit" class="btn-login">دخول</button>
    </form>

    <div class="create-account">
      <p>
        صاحب عمل جديد؟ <a href="employer-register.php">إنشاء حساب</a>
      </p>
    </div>

    <div class="job-seeker-link">
      <a href="../register-form.php">هل تبحث عن وظيفة؟</a>
    </div>
  </div>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/main.js"></script>
  <script>
    // كود إظهار/إخفاء كلمة المرور
    document.getElementById('togglePassword').addEventListener('click', function() {
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
  </script>
</body>
</html>