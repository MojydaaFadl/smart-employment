<?php
session_start();
$logged_in = isset($_SESSION['employer_id']);

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

// جلب بيانات صاحب العمل
$employer_data = [];
if ($logged_in) {
  $employer_id = $_SESSION['employer_id'];
  $sql = "SELECT * FROM employers WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $employer_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $employer_data = $result->fetch_assoc();
  }
  $stmt->close();
}

// معالجة طلب تحديث الحساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
  if (!isset($_SESSION['employer_id'])) {
    die(json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']));
  }

  $employer_id = $_SESSION['employer_id'];
  $first_name = trim($_POST['first_name']);
  $last_name = trim($_POST['last_name']);
  $company_name = trim($_POST['company_name']);
  $country = trim($_POST['country']);
  $city = trim($_POST['city']);
  $company_size = trim($_POST['company_size']);
  $phone_code = trim($_POST['phone_code']);
  $phone = trim($_POST['phone']);
  $marketing_agree = isset($_POST['marketing_agree']) ? 1 : 0;

  // تحديث كلمة السر إذا تم تقديمها
  $password_update = '';
  $params = [$first_name,
    $last_name,
    $company_name,
    $country,
    $city,
    $company_size,
    $phone_code,
    $phone,
    $marketing_agree,
    $employer_id];

  if (!empty($_POST['password'])) {
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password_update = ", password = ?";
    array_splice($params, -1, 0, $hashed_password);
  }

  $sql = "UPDATE employers SET
            first_name = ?,
            last_name = ?,
            company_name = ?,
            country = ?,
            city = ?,
            company_size = ?,
            phone_code = ?,
            phone = ?,
            marketing_agree = ?
            $password_update
            WHERE id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(str_repeat('s', count($params)), ...$params);

  if ($stmt->execute()) {
    // تحديث بيانات الجلسة
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['company_name'] = $company_name;

    echo json_encode(['success' => true, 'message' => 'تم تحديث الحساب بنجاح']);
    exit();
  } else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الحساب']);
    exit();
  }
}

// معالجة طلب حذف الحساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
  if (!isset($_SESSION['employer_id'])) {
    die(json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']));
  }

  $employer_id = $_SESSION['employer_id'];
  $success = true;
  $message = '';

  // بدء المعاملة
  $conn->begin_transaction();

  try {
    // 1. حذف جميع الوظائف المرتبطة
    $sql = "DELETE FROM job_posts WHERE employer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employer_id);
    $stmt->execute();
    $stmt->close();

    // 2. حذف جميع طلبات التوظيف المرتبطة
    $sql = "DELETE FROM job_applications WHERE employer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employer_id);
    $stmt->execute();
    $stmt->close();

    // 3. حذف صاحب العمل نفسه
    $sql = "DELETE FROM employers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employer_id);
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

// معالجة طلب حذف جميع الوظائف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_jobs'])) {
  if (!isset($_SESSION['employer_id'])) {
    die(json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']));
  }

  $employer_id = $_SESSION['employer_id'];

  try {
    $sql = "DELETE FROM job_posts WHERE employer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employer_id);

    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'تم حذف جميع الوظائف بنجاح']);
      exit();
    } else {
      echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف الوظائف']);
      exit();
    }
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
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
  <title>التوظيف الذكي - إعدادات صاحب العمل</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/fontawesome/css/all.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
  <script src="../js/sweetalert2@11.js"></script>
  <link rel="stylesheet" href="css/employer-style.css">
  <style>
    .profile-container {
      width: 100%;
      max-width: 800px;
      margin: 20px auto;
      border: 1px solid #ddd;
      border-radius: 5px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      background: white;
      padding: 20px;
    }

    .section-title {
      margin: 20px 0;
      font-size: 1.5rem;
      color: #333;
      text-align: center;
    }

    .profile-info {
      margin-bottom: 20px;
    }

    .profile-info-item {
      display: flex;
      margin-bottom: 10px;
    }

    .profile-info-label {
      font-weight: bold;
      width: 150px;
    }

    .profile-info-value {
      flex-grow: 1;
    }

    .account-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 30px;
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

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .password-toggle {
      cursor: pointer;
      color: #666;
      margin-right: 10px;
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


  <!-- محتوى الصفحة -->
  <div class="container">
    <h2 class="section-title">الملف الشخصي لصاحب العمل</h2>

    <div class="profile-container">
      <div class="profile-info">
        <div class="profile-info-item">
          <div class="profile-info-label">
            الاسم:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['first_name'] . ' ' . $employer_data['last_name']); ?>
          </div>
        </div>

        <div class="profile-info-item">
          <div class="profile-info-label">
            البريد الإلكتروني:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['email']); ?>
          </div>
        </div>

        <div class="profile-info-item">
          <div class="profile-info-label">
            اسم الشركة:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['company_name']); ?>
          </div>
        </div>

        <div class="profile-info-item">
          <div class="profile-info-label">
            البلد:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['country']); ?>
          </div>
        </div>

        <div class="profile-info-item">
          <div class="profile-info-label">
            المدينة:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['city']); ?>
          </div>
        </div>

        <div class="profile-info-item">
          <div class="profile-info-label">
            حجم الشركة:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['company_size']); ?>
          </div>
        </div>

        <div class="profile-info-item">
          <div class="profile-info-label">
            رقم الهاتف:
          </div>
          <div class="profile-info-value">
            <?php echo htmlspecialchars($employer_data['phone_code']) . ' ' . htmlspecialchars($employer_data['phone']); ?>
          </div>
        </div>
      </div>

      <div class="account-actions">
        <button class="btn btn-primary" onclick="openEditModal()">
          <i class="fas fa-user-edit me-2"></i> تعديل الحساب
        </button>

        <button class="btn btn-danger" onclick="confirmDeleteAllJobs()">
          <i class="fas fa-trash-alt me-2"></i> حذف جميع الوظائف
        </button>

        <button class="btn btn-outline-danger" onclick="confirmAccountDeletion()">
          <i class="fas fa-user-times me-2"></i> حذف الحساب
        </button>
      </div>
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
          <input type="text" id="modal-first-name" name="first_name" value="<?php echo htmlspecialchars($employer_data['first_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="modal-last-name">اسم العائلة</label>
          <input type="text" id="modal-last-name" name="last_name" value="<?php echo htmlspecialchars($employer_data['last_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="modal-company-name">اسم الشركة</label>
          <input type="text" id="modal-company-name" name="company_name" value="<?php echo htmlspecialchars($employer_data['company_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="modal-country">البلد</label>
          <input type="text" id="modal-country" name="country" value="<?php echo htmlspecialchars($employer_data['country'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="modal-city">المدينة</label>
          <input type="text" id="modal-city" name="city" value="<?php echo htmlspecialchars($employer_data['city'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="modal-company-size">حجم الشركة</label>
          <select id="modal-company-size" name="company_size" required>
            <option value="small" <?php echo ($employer_data['company_size'] ?? '') == 'small' ? 'selected' : ''; ?>>صغيرة</option>
            <option value="medium" <?php echo ($employer_data['company_size'] ?? '') == 'medium' ? 'selected' : ''; ?>>متوسطة</option>
            <option value="large" <?php echo ($employer_data['company_size'] ?? '') == 'large' ? 'selected' : ''; ?>>كبيرة</option>
          </select>
        </div>

        <div class="form-group">
          <label for="modal-phone">رقم الهاتف</label>
          <div style="display: flex; gap: 10px;">
            <input type="text" id="modal-phone-code" name="phone_code" value="<?php echo htmlspecialchars($employer_data['phone_code'] ?? '+967'); ?>" style="width: 80px;">
            <input type="tel" id="modal-phone" name="phone" value="<?php echo htmlspecialchars($employer_data['phone'] ?? ''); ?>" style="flex-grow: 1;">
          </div>
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
          <label>
            <input type="checkbox" id="modal-marketing-agree" name="marketing_agree" <?php echo ($employer_data['marketing_agree'] ?? 0) ? 'checked' : ''; ?>>
            الموافقة على تلقي عروض تسويقية
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

  <script src="js/main.js"></script>
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    // فتح وإغلاق القائمة الجانبية
    function openSidebar() {
      document.getElementById("sidebar").style.right = "0";
    }

    function closeSidebar() {
      document.getElementById("sidebar").style.right = "-100%";
    }

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
      const type = input.getAttribute('type') === 'password' ? 'text': 'password';
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
        <li>جميع الوظائف المنشورة</li>
        <li>طلبات التوظيف المرتبطة</li>
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
      formData.append('delete_account',
        '1');

      fetch(window.location.href,
        {
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

    // تأكيد حذف جميع الوظائف
    function confirmDeleteAllJobs() {
      Swal.fire({
        title: 'هل أنت متأكد؟',
        html: `
        <div class="text-start">
        <p>سيتم حذف جميع الوظائف المنشورة من قبلك بما في ذلك:</p>
        <ul>
        <li>جميع عروض الوظائف</li>
        <li>طلبات التوظيف المرتبطة بها</li>
        </ul>
        <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه!</p>
        </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف جميع الوظائف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#d33',
        customClass: {
          confirmButton: 'btn btn-danger mx-3',
          cancelButton: 'btn btn-outline-secondary mx-3'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          deleteAllJobs();
        }
      });
    }

    // حذف جميع الوظائف
    function deleteAllJobs() {
      const formData = new FormData();
      formData.append('delete_all_jobs',
        '1');

      fetch(window.location.href,
        {
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