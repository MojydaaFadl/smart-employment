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
    <title>تسجيل الدخول</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/fontawesome/css/all.css">
    <link rel="stylesheet" href="css/style.css">
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

        .input-group input:focus+label,
        .input-group input:not(:placeholder-shown)+label {
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

        .form-group .checkbox {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .form-group .checkbox input {
            margin-left: 10px;
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
        }

        .btn-login:hover {
            background-color: #0069d9;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
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

        .create-account {
            text-align: center;
            margin-top: 25px;
            color: #6c757d;
        }

        .create-account a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .create-account a:hover {
            text-decoration: underline;
        }

        /* أنماط جديدة لرسائل الخطأ */
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .input-group.error input {
            border-color: #dc3545;
        }

        .input-group.success input {
            border-color: #28a745;
        }
    </style>
</head>

<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div>
            <span class="open-btn" onclick="openSidebar()">&#9776;</span>
            <p class="navbar-brand">التوظيف الذكي</p>
        </div>
    </nav>

    <!-- القائمة الجانبية المعدلة -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div><span class="close-btn" onclick="closeSidebar()"><i class="fa-solid fa-xmark"></i></span></div>
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
        <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="for-employers/employer-dashboard.php">لأصحاب العمل</a>
        <?php endif; ?>
        <a href="#">English</a>
    </div>

    <!-- نموذج تسجيل الدخول -->
    <div class="login-container">
        <h2>تسجيل الدخول إلى حسابك</h2>
        <form id="loginForm" action="login.php" method="POST">
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <label for="email">البريد الإلكتروني</label>
                <div class="error-message" id="email-error">
                    <?php 
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'empty_email') {
                            echo 'يجب إدخال البريد الإلكتروني';
                        } elseif ($_GET['error'] == 'invalid_email') {
                            echo 'البريد الإلكتروني غير صحيح';
                        } elseif ($_GET['error'] == 'email_not_found') {
                            echo 'البريد الإلكتروني غير مسجل';
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " minlength="8">
                <label for="password">كلمة المرور</label>
                <span class="password-toggle" id="togglePassword">
                    <i class="far fa-eye"></i>
                </span>
                <div class="error-message" id="password-error">
                    <?php 
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'empty_password') {
                            echo 'كلمة المرور مطلوبة';
                        } elseif ($_GET['error'] == 'short_password') {
                            echo 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل';
                        } elseif ($_GET['error'] == 'invalid_password') {
                            echo 'كلمة المرور غير صحيحة';
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="remember-me" name="remember-me" <?php echo isset($_POST['remember-me']) ? 'checked' : ''; ?>>
                <label for="remember-me">تذكر بياناتي</label>
            </div>

            <button type="submit" class="btn-login">تسجيل الدخول</button>
        </form>

        <div class="forgot-password">
            <a href="forgot-password.php">هل نسيت كلمة المرور؟</a>
        </div>

        <div class="create-account">
            <p>ليس لديك حساب؟ <a href="register-form.php">إنشاء حساب جديد</a></p>
        </div>
    </div>

    <script>
        // اظهار واخفاء كلمة المرور
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // دوال شريط التنقل والقائمة الجانبية
        function openSidebar() {
            document.getElementById("sidebar").style.right = "0";
        }

        function closeSidebar() {
            document.getElementById("sidebar").style.right = "-100%";
        }

        // عرض رسائل الخطأ عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            
            if (error) {
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                const emailError = document.getElementById('email-error');
                const passwordError = document.getElementById('password-error');
                
                switch(error) {
                    case 'empty_email':
                        showError(emailInput, emailError, 'يجب إدخال البريد الإلكتروني');
                        break;
                    case 'invalid_email':
                        showError(emailInput, emailError, 'البريد الإلكتروني غير صحيح');
                        break;
                    case 'email_not_found':
                        showError(emailInput, emailError, 'البريد الإلكتروني غير مسجل');
                        break;
                    case 'empty_password':
                        showError(passwordInput, passwordError, 'كلمة المرور مطلوبة');
                        break;
                    case 'short_password':
                        showError(passwordInput, passwordError, 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل');
                        break;
                    case 'invalid_password':
                        showError(passwordInput, passwordError, 'كلمة المرور غير صحيحة');
                        break;
                }
            }
        });

        // التحقق من صحة البيانات أثناء الكتابة
        document.getElementById('email').addEventListener('input', validateEmail);
        document.getElementById('password').addEventListener('input', validatePassword);

        function validateEmail() {
            const email = document.getElementById('email');
            const error = document.getElementById('email-error');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email.value.trim() === '') {
                showError(email, error, 'يجب إدخال البريد الإلكتروني');
                return false;
            } else if (!emailRegex.test(email.value)) {
                showError(email, error, 'البريد الإلكتروني غير صحيح');
                return false;
            } else {
                showSuccess(email, error);
                return true;
            }
        }

        function validatePassword() {
            const password = document.getElementById('password');
            const error = document.getElementById('password-error');
            
            if (password.value.trim() === '') {
                showError(password, error, 'كلمة المرور مطلوبة');
                return false;
            } else if (password.value.length < 8) {
                showError(password, error, 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل');
                return false;
            } else {
                showSuccess(password, error);
                return true;
            }
        }

        // عرض رسالة الخطأ
        function showError(input, errorElement, message) {
            const inputGroup = input.closest('.input-group');
            inputGroup.classList.add('error');
            inputGroup.classList.remove('success');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        // عرض حالة النجاح
        function showSuccess(input, errorElement) {
            const inputGroup = input.closest('.input-group');
            inputGroup.classList.remove('error');
            inputGroup.classList.add('success');
            errorElement.style.display = 'none';
        }
    </script>
</body>
</html>