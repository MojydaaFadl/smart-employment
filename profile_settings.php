<?php
session_start();
$logged_in = isset($_SESSION['user_id']);

// اتصال بقاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$dbname = "smart_employment";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
  die("فشل الاتصال: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// جلب معلومات السيرة الذاتية للمستخدم
$cv_path = null;
$cv_file_name = null;
$file_exists = false;
$file_info = '';
if ($logged_in) {
  $user_id = $_SESSION['user_id'];
  
  // جلب بيانات السيرة الذاتية
  $sql = "SELECT file_path, file_name FROM user_cvs WHERE user_id = ? ORDER BY upload_date DESC LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $original_path = $row['file_path'];
    $cv_file_name = $row['file_name'];

    // إصلاح مسار الملف
    $cv_path = str_replace('../', '', $original_path);

    // التحقق من وجود الملف
    $absolute_path = realpath($cv_path);
    $file_exists = file_exists($absolute_path);
  }
  $stmt->close();
  
  // جلب بيانات المستخدم
  $user_data = [];
  $sql = "SELECT * FROM users WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
      $user_data = $result->fetch_assoc();
  }
  $stmt->close();
}

// معالجة طلب حذف السيرة الذاتية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cv'])) {
  if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']));
  }

  $user_id = $_SESSION['user_id'];
  $success = false;
  $message = '';

  // جلب معلومات الملف من قاعدة البيانات
  $sql = "SELECT file_path FROM user_cvs WHERE user_id = ? ORDER BY upload_date DESC LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $file_path = str_replace('../', '', $row['file_path']);

    // حذف الملف من الخادم
    if (file_exists($file_path)) {
      if (unlink($file_path)) {
        // حذف السجل من قاعدة البيانات
        $delete_sql = "DELETE FROM user_cvs WHERE user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);

        if ($delete_stmt->execute()) {
          $success = true;
          $message = 'تم حذف السيرة الذاتية بنجاح';
        } else {
          $message = 'حدث خطأ أثناء حذف السجل من قاعدة البيانات';
        }
        $delete_stmt->close();
      } else {
        $message = 'حدث خطأ أثناء حذف الملف من الخادم';
      }
    } else {
      $message = 'الملف غير موجود على الخادم';
      // مع ذلك نستمر في حذف السجل من قاعدة البيانات
      $delete_sql = "DELETE FROM user_cvs WHERE user_id = ?";
      $delete_stmt = $conn->prepare($delete_sql);
      $delete_stmt->bind_param("i", $user_id);
      $delete_stmt->execute();
      $delete_stmt->close();
      $success = true;
    }
  } else {
    $message = 'لا يوجد سيرة ذاتية لحذفها';
    $success = true;
  }

  $stmt->close();

  // إعادة التوجيه بعد الحذف
  if ($success) {
    header('Location: index22.php?cv_delete=success');
    exit();
  } else {
    header('Location: profile_settings.php?error=' . urlencode($message));
    exit();
  }
}

// معالجة طلب تحديث الحساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
  if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']));
  }

  $user_id = $_SESSION['user_id'];
  $first_name = trim($_POST['first_name']);
  $last_name = trim($_POST['last_name']);
  $major = trim($_POST['major']); // إضافة التخصص العام
  $phone_code = trim($_POST['phone_code']);
  $phone_number = trim($_POST['phone_number']);
  $is_public = isset($_POST['is_public']) ? 1 : 0;
  
  // تحديث كلمة السر إذا تم تقديمها
  $password_update = '';
  $params = [$first_name, $last_name, $major, $phone_code, $phone_number, $is_public, $user_id];
  
  if (!empty($_POST['password'])) {
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password_update = ", password = ?";
    array_splice($params, -1, 0, $hashed_password);
  }
  
  $sql = "UPDATE users SET 
          first_name = ?,
          last_name = ?,
          major = ?,
          phone_code = ?,
          phone_number = ?,
          is_public = ?
          $password_update
          WHERE user_id = ?";
  
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(str_repeat('s', count($params)), ...$params);
  
  if ($stmt->execute()) {
    // تحديث بيانات الجلسة
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['major'] = $major;
    
    echo json_encode(['success' => true, 'message' => 'تم تحديث الحساب بنجاح']);
    exit();
  } else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الحساب']);
    exit();
  }
}

// معالجة طلب حذف الحساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
  if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']));
  }

  $user_id = $_SESSION['user_id'];
  $success = true;
  $message = '';
  
  // بدء المعاملة
  $conn->begin_transaction();
  
  try {
    // 1. حذف السيرة الذاتية من الخادم
    $sql = "SELECT file_path FROM user_cvs WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
      $file_path = str_replace('../', '', $row['file_path']);
      if (file_exists($file_path)) {
        unlink($file_path);
      }
    }
    $stmt->close();
    
    // 2. حذف جميع البيانات المرتبطة من قاعدة البيانات
    $tables = ['user_cvs', 'favorite_jobs', 'job_applications', 'job_comments'];
    
    foreach ($tables as $table) {
      $sql = "DELETE FROM $table WHERE user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $stmt->close();
    }
    
    // 3. حذف المستخدم نفسه
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    
    // إغلاق الجلسة
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'تم حذف الحساب بنجاح']);
    exit();
    
  } catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف الحساب: ' . $e->getMessage()]);
    exit();
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التوظيف الذكي - الملف الشخصي</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/fontawesome/css/all.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <script src="js/pdf.min.js"></script>
  <script src="js/sweetalert2@11.js"></script>
  <style>
    /* أنماط حقل التخصص */
    .form-group select {
      width: 100%;
      padding: 12px;
      border: 1px solid #868686;
      border-radius: 4px;
      box-sizing: border-box;
      transition: all 0.3s;
      background-color: white;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }

    /* بقية الأنماط كما هي */
    .pdf-container,.delet-account-container {
      width: 100%;
      max-width: 800px;
      direction: ltr;
      margin: 20px auto;
      border: 1px solid #ddd;
      border-radius: 5px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      background: white;
    }

    #pdf-viewer {
      width: 100%;
      display: block;
      image-rendering: -webkit-optimize-contrast;
      image-rendering: crisp-edges;
    }
    .section-title {
      margin: 20px 0;
      font-size: 1.5rem;
      color: #333;
      text-align: center;
    }

    .pdf-note {
      text-align: center;
      color: #666;
      margin-top: 10px;
      font-size: 0.9rem;
    }

    .download-btn {
      display: block;
      width: 200px;
      margin: 20px auto;
      text-align: center;
    }
    .btn-spacing {
      margin-right: 8px;
    }
    .no-cv-message {
      padding: 20px;
      text-align: center;
      color: #666;
      font-size: 1.1rem;
    }
    .file-debug-info {
      margin: 15px auto;
      max-width: 800px;
      padding: 15px;
      border-radius: 5px;
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

    /* أنماط الـ Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      display: none;
    }
    
    .modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      z-index: 1001;
      display: none;
    }
    
    .modal-header {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-body {
      padding: 20px;
    }
    
    .modal-footer {
      padding: 15px;
      border-top: 1px solid #eee;
      text-align: left;
    }
    
    .close-modal {
      cursor: pointer;
      font-size: 24px;
      color: #777;
    }
    
    /* أنماط النموذج داخل الـ Modal */
    .modal .form-group {
      margin-bottom: 20px;
    }
    
    .modal .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    
    .modal .form-group input, 
    .modal .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    /* أنماط حقول الهاتف */
    .phone-input {
      display: flex;
      gap: 10px;
    }
    
    .phone-code-select {
      width: 100px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      cursor: pointer;
    }
    
    /* أنماط زر عرض/إخفاء كلمة السر */
    .password-toggle {
      cursor: pointer;
      color: #666;
      margin-right: 10px;
    }
    
    /* أنماط زر الحذف */
    .delete-account-btn {
      background-color: #f8f9fa;
      border: 1px solid #dc3545;
      color: #dc3545;
      padding: 10px;
      border-radius: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 20px;
    }
    
    .delete-account-btn:hover {
      background-color: #f1f1f1;
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
      <div class="profile-actions">
        <span class="menu-btn"><i class="fa-regular fa-message"></i></span>
        <span class="menu-btn"><i class="fa-regular fa-bell"></i></span>
        <span class="menu-btn" onclick="openBottomSheet()"><i class="fa-regular fa-circle-user"></i></span>
      </div>
    </div>
    <a href="employee-dashboard.php">الرئيسية</a>

    <!-- القائمة المنسدلة الجديدة مع الأيقونة -->
    <div class="dropdown-container">
      <button class="dropdown-btn" onclick="toggleDropdown(this)">
        إبحث عن وظيفة
        <i class="fas fa-chevron-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="#">البحث عن عمل</a>
        <a href="#">الدولة أو المدينة</a>
        <a href="#">الشركات المُعلنة عن وظائف</a>
        <a href="#">وظائف المستوى التنفيذي</a>
        <a href="#">فرص عمل من المنزل</a>
      </div>
    </div>
    <a href="#">بريميوم</a>
    <a href="#">المدونة</a>
  </div>

  <!-- قسم عرض السيرة الذاتية -->
  <div class="container">
    <h2 class="section-title">سيرتي الذاتية</h2>
    <div class="pdf-container" id="pdfContainer">
      <?php if ($cv_path && $file_exists): ?>
      <canvas id="pdf-viewer"></canvas>
      <hr>
      <div class="cv-actions w-100 mb-1 d-flex flex-column p-1">
        <div class="d-flex w-100 gap-2">
          <button class="edit-cv-btn btn btn-primary flex-grow-1 mx-1">
            <i class="fas fa-edit me-2"></i> تعديل
          </button>
          <button class="btn btn-outline-danger flex-grow-1 mx-1" onclick="confirmDelete()">
            <i class="fas fa-trash-alt me-2"></i> حذف
          </button>
          <form id="deleteForm" method="POST" style="display: none;">
            <input type="hidden" name="delete_cv" value="1">
          </form>
        </div>
        <div class="mt-1 w-100 d-flex">
          <a href="<?php echo $cv_path; ?>" download="<?php echo $cv_file_name; ?>" class="btn btn-outline-secondary flex-grow-1 mx-1">
            <i class="fas fa-download me-2"></i> تحميل
          </a>
        </div>
      </div>
      <?php elseif ($cv_path && !$file_exists): ?>
      <div class="no-cv-message alert alert-danger">
        <p>
          ملف السيرة الذاتية غير موجود في المسار المحدد!
        </p>
        <p>
          مسار الملف: <?php echo htmlspecialchars($cv_path); ?>
        </p>
        <a href="upload-create-profile.php" class="btn btn-primary mt-3">
          <i class="fas fa-plus me-2"></i> إعادة رفع السيرة الذاتية
        </a>
      </div>
      <?php else : ?>
      <div class="no-cv-message">
        <p>
          لا يوجد سيرة ذاتية مرفقة حالياً
        </p>
        <a href="upload-create-profile.php" class="btn btn-primary mt-3">
          <i class="fas fa-plus me-2"></i> إضافة سيرة ذاتية
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- قسم إدارة الحساب -->
  <div class="container">
    <div class="delet-account-container d-flex flex-column">
      <h2 class="section-title">إدارة الحساب</h2>
      
      <button class="btn btn-primary my-3 mx-3" onclick="openEditModal()">
        <i class="fas fa-user-edit me-2"></i> تعديل الحساب
      </button>
      
      <button class="btn btn-outline-danger my-3 mx-3" onclick="confirmAccountDeletion()">
        <i class="fas fa-trash-alt me-2"></i> حذف الحساب
      </button>
    </div>
  </div>

  <!-- Modal تعديل الحساب -->
  <div class="modal-overlay" id="editModalOverlay"></div>
  <div class="modal" id="editModal">
    <div class="modal-header">
      <h4>تعديل الحساب</h4>
      <span class="close-modal" onclick="closeEditModal()">&times;</span>
    </div>
    <div class="modal-body">
      <form id="updateAccountForm">
        <div class="form-group">
          <label for="modal-first-name">الاسم الأول</label>
          <input type="text" id="modal-first-name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
          <label for="modal-last-name">اسم العائلة</label>
          <input type="text" id="modal-last-name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
          <label for="modal-major">التخصص العام</label>
          <select id="modal-major" name="major" required>
            <option value="" disabled>اختر التخصص العام</option>
<option value="Information Technology" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
<option value="Engineering" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
<option value="Medicine and Healthcare" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Medicine and Healthcare') ? 'selected' : ''; ?>>Medicine and Healthcare</option>
<option value="Accounting and Finance" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Accounting and Finance') ? 'selected' : ''; ?>>Accounting and Finance</option>
<option value="Marketing and Sales" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Marketing and Sales') ? 'selected' : ''; ?>>Marketing and Sales</option>
<option value="Education" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Education') ? 'selected' : ''; ?>>Education</option>
<option value="Arts and Design" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Arts and Design') ? 'selected' : ''; ?>>Arts and Design</option>
<option value="Law" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Law') ? 'selected' : ''; ?>>Law</option>
<option value="Science" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Science') ? 'selected' : ''; ?>>Science</option>
<option value="Business and Management" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Business and Management') ? 'selected' : ''; ?>>Business and Management</option>
<option value="Agriculture" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Agriculture') ? 'selected' : ''; ?>>Agriculture</option>
<option value="Tourism and Hospitality" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Tourism and Hospitality') ? 'selected' : ''; ?>>Tourism and Hospitality</option>
<option value="Other" <?php echo (isset($user_data['major']) && $user_data['major'] == 'Other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="modal-email">البريد الإلكتروني</label>
          <input type="email" id="modal-email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" readonly>
        </div>
        
        <div class="form-group">
          <label for="modal-password">كلمة السر الجديدة (اتركها فارغة إذا لم ترغب في التغيير)</label>
          <div style="display: flex; align-items: center;">
            <input type="password" id="modal-password" name="password" style="flex-grow: 1;">
            <span class="password-toggle" onclick="togglePassword('modal-password', this)">
              <i class="far fa-eye"></i>
            </span>
          </div>
        </div>
        
        <div class="form-group">
          <label>رقم الهاتف</label>
          <div class="phone-input">
            <input type="tel" id="modal-phone" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>" required style="flex-grow: 1;">
            <input type="text" class="phone-code-select" id="modal-country-code-display" 
                   value="<?php echo htmlspecialchars($user_data['phone_code'] ?? '+967'); ?>" readonly onclick="openCountrySelect('modal')">
            <input type="hidden" id="modal-country-code" name="phone_code" value="<?php echo htmlspecialchars($user_data['phone_code'] ?? '+967'); ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label>
            <input type="checkbox" id="modal-public-profile" name="is_public" <?php echo ($user_data['is_public'] ?? 1) ? 'checked' : ''; ?>>
            تفعيل الملف الشخصي العام
          </label>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeEditModal()">إلغاء</button>
      <button type="button" class="btn btn-primary" onclick="updateAccount()">حفظ التغييرات</button>
    </div>
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
    <div class="bottom-sheet-item" onclick="navigateTo('for-employers/employer-dashboard.php')">
      <i class="fas fa-briefcase"></i>
      <span>لأصحاب العمل</span>
    </div>
    <div class="bottom-sheet-item" onclick="navigateTo('logout.php')">
      <i class="fas fa-sign-out-alt"></i>
      <span>تسجيل خروج</span>
    </div>
  </div>

  <script src="js/main.js"></script>
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    // فتح وإغلاق Bottom Sheet
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

    // تحذير عند النقر على زر التعديل
    document.querySelector('.edit-cv-btn')?.addEventListener('click', function(e) {
      e.preventDefault();

      // عرض نافذة تأكيد
      Swal.fire({
        title: 'تحذير!',
        html: `
        <div class="text-start">
        <p>سيتم حذف ملف السيرة الذاتية الحالي واستبداله بملف جديد.</p>
        <p>هل أنت متأكد أنك تريد المتابعة؟</p>
        </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم',
        cancelButtonText: 'إلغاء',
        customClass: {
          confirmButton: 'btn btn-danger mx-3',
          cancelButton: 'btn btn-outline-secondary mx-3'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          // إذا وافق المستخدم، نقله إلى صفحة إنشاء السيرة الذاتية
          window.location.href = 'cv-creation/step1.php?recreate=true';
        }
      });
    });

    // تأكيد الحذف للسيرة الذاتية
    function confirmDelete() {
      Swal.fire({
        title: 'هل أنت متأكد؟',
        text: "لن تتمكن من استعادة السيرة الذاتية بعد الحذف!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم',
        cancelButtonText: 'إلغاء'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('deleteForm').submit();
        }
      });
    }

    // عرض ملف PDF
    <?php if ($cv_path && $file_exists): ?>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'js/pdf.worker.min.js';

    pdfjsLib.getDocument({
      url: '<?php echo $cv_path; ?>',
      cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/cmaps/',
      cMapPacked: true
    }).promise.then(function(pdf) {
      return pdf.getPage(1);
    }).then(function(page) {
      const container = document.getElementById('pdfContainer');
      const containerWidth = container.clientWidth;

      // حساب المقياس لملاءمة عرض الحاوية
      const viewport = page.getViewport({
        scale: 0.25
      });
      const scale = containerWidth / viewport.width;

      // تطبيق المقياس المحسوب
      const scaledViewport = page.getViewport({
        scale: scale
      });

      // تهيئة Canvas
      const canvas = document.getElementById('pdf-viewer');
      const context = canvas.getContext('2d');

      // ضبط أبعاد Canvas
      canvas.width = scaledViewport.width;
      canvas.height = scaledViewport.height;

      // عرض الصفحة
      page.render({
        canvasContext: context,
        viewport: scaledViewport
      });
    }).catch(function(error) {
      console.error('Error loading PDF:', error);
      document.getElementById('pdfContainer').innerHTML = `
      <div class="alert alert-danger">
      <p>تعذر عرض السيرة الذاتية. يرجى <a href="<?php echo $cv_path; ?>" download>تنزيل الملف</a> لعرضه.</p>
      <p>تفاصيل الخطأ: ${error.message}</p>
      </div>
      `;
    });
    <?php endif; ?>

    // فتح وإغلاق Modal تعديل الحساب
    function openEditModal() {
      document.getElementById('editModalOverlay').style.display = 'block';
      document.getElementById('editModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
    
    function closeEditModal() {
      document.getElementById('editModalOverlay').style.display = 'none';
      document.getElementById('editModal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }
    
    // إغلاق Modal عند النقر خارجها
    document.getElementById('editModalOverlay').addEventListener('click', closeEditModal);
    
    // تبديل عرض كلمة السر
    function togglePassword(inputId, toggleElement) {
      const input = document.getElementById(inputId);
      const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
      input.setAttribute('type', type);
      
      const icon = toggleElement.querySelector('i');
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    }
    
    // تحديث الحساب
    function updateAccount() {
      const formData = new FormData(document.getElementById('updateAccountForm'));
      formData.append('update_account', '1');
      
      fetch(window.location.href, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'نجاح',
            text: data.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            closeEditModal();
            // تحديث الصفحة لرؤية التغييرات
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: data.message
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'خطأ',
          text: 'حدث خطأ أثناء الاتصال بالخادم'
        });
      });
    }
    
    // تأكيد حذف الحساب
    function confirmAccountDeletion() {
      Swal.fire({
        title: 'هل أنت متأكد؟',
        html: `
          <div class="text-start">
            <p>سيتم حذف حسابك بشكل نهائي مع جميع البيانات المرتبطة به بما في ذلك:</p>
            <ul>
              <li>سيرتك الذاتية</li>
              <li>طلبات التوظيف</li>
              <li>الوظائف المفضلة</li>
              <li>جميع البيانات الشخصية</li>
            </ul>
            <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه!</p>
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف الحساب',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#d33',
        customClass: {
          confirmButton: 'btn btn-danger mx-3',
          cancelButton: 'btn btn-outline-secondary mx-3'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          deleteAccount();
        }
      });
    }
    
    // حذف الحساب
    function deleteAccount() {
      const formData = new FormData();
      formData.append('delete_account', '1');
      
      fetch(window.location.href, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'نجاح',
            text: data.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            // توجيه المستخدم إلى الصفحة الرئيسية بعد الحذف
            window.location.href = 'index22.php';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: data.message
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'خطأ',
          text: 'حدث خطأ أثناء الاتصال بالخادم'
        });
      });
    }

    // فتح قائمة الدول
    function openCountrySelect(prefix) {
      // يمكنك إضافة كود فتح قائمة الدول هنا إذا كنت بحاجة إليها
      alert('يجب تنفيذ كود فتح قائمة الدول حسب احتياجاتك');
    }
  </script>
</body>
</html>