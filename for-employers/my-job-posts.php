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

// جلب الوظائف المنشورة مع عدد المتقدمين لكل وظيفة
$stmt = $conn->prepare("
    SELECT j.*, COUNT(a.id) as applicants_count
    FROM job_posts j
    LEFT JOIN job_applications a ON j.id = a.job_id
    WHERE j.employer_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
  ");
$stmt->execute([$employerId]);
$jobPosts = $stmt->fetchAll();

// معالجة حذف الوظيفة
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $deleteId = $_GET['delete'];

  try {
    $stmt = $conn->prepare("DELETE FROM job_posts WHERE id = ? AND employer_id = ?");
    $stmt->execute([$deleteId, $employerId]);

    $_SESSION['success_message'] = "تم حذف الوظيفة بنجاح";
    header('Location: my-job-posts.php');
    exit();
  } catch (PDOException $e) {
    $error = "حدث خطأ أثناء محاولة حذف الوظيفة: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التوظيف الذكي - الوظائف المنشورة</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/fontawesome/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="css/employer-style.css">
  <style>
    .job-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
      padding: 25px;
      margin-bottom: 25px;
      transition: all 0.3s ease;
      border: 1px solid #eee;
    }

    .job-card:hover {
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }

    .job-header {
      border-bottom: 1px solid #f0f0f0;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .job-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .job-meta {
      display: flex;
      gap: 15px;
      color: #666;
      font-size: 0.9rem;
    }

    .job-meta i {
      margin-left: 5px;
      color: #777;
    }

    .detail-section {
      margin-bottom: 20px;
    }

    .detail-section h5 {
      color: #2c3e50;
      font-size: 1.1rem;
      margin-bottom: 15px;
      padding-bottom: 8px;
      border-bottom: 1px dashed #eee;
    }

    .detail-section h5 i {
      margin-left: 8px;
      color: #007bff;
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 15px;
    }

    .detail-item {
      display: flex;
      align-items: center;
      padding: 8px 0;
    }

    .detail-label {
      font-weight: 600;
      color: #555;
      min-width: 120px;
      margin-left: 10px;
    }

    .detail-label i {
      margin-left: 5px;
      color: #777;
    }

    .detail-value {
      color: #333;
    }

    .skills-container {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
    }

    .skill-tag {
      background-color: #e9f5ff;
      color: #007bff;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
    }

    .job-description {
      color: #555;
      line-height: 1.7;
      white-space: pre-line;
      padding: 10px;
      background-color: #f9f9f9;
      border-radius: 5px;
      border-left: 3px solid #3498db;
    }

    .job-actions {
      display: flex;
      gap: 10px;
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid #f0f0f0;
    }

    .job-actions .btn {
      flex: 1;
      padding: 10px;
      font-size: 0.9rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-applicants {
      background-color: #e3f2fd;
      color: #1976d2;
      border: 1px solid #bbdefb;
    }

    .btn-edit {
      background-color: #fff8e1;
      color: #ff8f00;
      border: 1px solid #ffecb3;
    }

    .btn-delete {
      background-color: #ffebee;
      color: #d32f2f;
      border: 1px solid #ffcdd2;
    }

    .status-badge {
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 0.8rem;
      font-weight: bold;
    }

    .status-active {
      background-color: #e8f5e9;
      color: #2e7d32;
    }

    .status-inactive {
      background-color: #fff8e1;
      color: #ff8f00;
    }

    .status-filled {
      background-color: #ffebee;
      color: #c62828;
    }

    .btn-applicants .badge {
      background-color: white;
      color: #1976d2;
      margin-right: 5px;
      font-size: 0.8rem;
      padding: 3px 6px;
      border-radius: 10px;
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

  <!-- المحتوى الرئيسي -->
  <div class="main-content">
    <div class="container py-4">
      <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <?php endif; ?>

      <?php if (isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>الوظائف المنشورة</h2>
        <a href="add-job-post.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> إضافة وظيفة جديدة
        </a>
      </div>

      <?php if (count($jobPosts) > 0): ?>
      <div class="job-posts-list">
        <?php foreach ($jobPosts as $job): ?>
        <div class="job-card">
          <div class="job-header">
            <div class="d-flex justify-content-between align-items-start">
              <h3 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
              <span class="status-badge status-<?php echo $job['status']; ?>">
                <?php
                if ($job['status'] == 'active') echo 'نشطة';
                elseif ($job['status'] == 'inactive') echo 'غير نشطة';
                else echo 'تم شغلها';
                ?>
              </span>
            </div>
            <div>
              <span>عدد الشواغر:</span>
              <span><?php echo htmlspecialchars($job['vacancies']); ?></span>
            </div>
            <div class="job-meta">
              <span><i class="far fa-calendar-alt"></i> <?php echo date('Y/m/d', strtotime($job['created_at'])); ?></span>
              <span><i class="fas fa-industry"></i> <?php echo htmlspecialchars($job['industry']); ?></span>
            </div>
          </div>

          <div class="job-details">
            <div class="detail-section">
              <h5><i class="fas fa-info-circle"></i> معلومات الوظيفة</h5>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label"><i class="fas fa-briefcase"></i> نوع التوظيف:</span>
                  <span class="detail-value"><?php echo htmlspecialchars($job['employment_type']); ?></span>
                </div>
                <div class="detail-item">
                  <span class="detail-label"><i class="fas fa-laptop-house"></i> نوع الوظيفة:</span>
                  <span class="detail-value"><?php echo htmlspecialchars($job['job_type']); ?></span>
                </div>
                <div class="detail-item">
                  <span class="detail-label"><i class="fas fa-map-marker-alt"></i> الموقع:</span>
                  <span class="detail-value"><?php echo htmlspecialchars($job['city'] . '، ' . $job['country']); ?></span>
                </div>
                <div class="detail-item">
                  <span class="detail-label"><i class="fas fa-money-bill-wave"></i> الراتب:</span>
                  <span class="detail-value">
                    <?php
                    if ($job['min_salary'] && $job['max_salary']) {
                      echo $job['min_salary'] . ' - ' . $job['max_salary'] . ' ر.س';
                    } else {
                      echo 'غير محدد';
                    }
                    ?>
                  </span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h5><i class="fas fa-user-tie"></i> المتطلبات</h5>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label"><i class="fas fa-graduation-cap"></i> المؤهل:</span>
                  <span class="detail-value"><?php echo htmlspecialchars($job['education_level']); ?></span>
                </div>
                <div class="detail-item">
                  <span class="detail-label"><i class="fas fa-clock"></i> الخبرة:</span>
                  <span class="detail-value"><?php echo htmlspecialchars($job['experience_years']); ?></span>
                </div>
              </div>
            </div>

            <?php if (!empty($job['skills'])): ?>
            <div class="detail-section">
              <h5><i class="fas fa-tools"></i> المهارات المطلوبة</h5>
              <div class="skills-container">
                <?php
                $skills = explode(', ', $job['skills']);
                foreach ($skills as $skill):
                ?>
                <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($job['job_description'])): ?>
            <div class="detail-section">
              <h5><i class="fas fa-align-left"></i> الوصف الوظيفي</h5>
              <div class="job-description">
                <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div class="job-actions">
            <a href="job-applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-applicants">
              <i class="fas fa-users"></i> المتقدمين
              <?php if ($job['applicants_count'] > 0): ?>
              <span class="badge badge-light"><?php echo $job['applicants_count']; ?></span>
              <?php endif; ?>
            </a>
            <a href="edit-job-post.php?id=<?php echo $job['id']; ?>" class="btn btn-edit">
              <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="my-job-posts.php?delete=<?php echo $job['id']; ?>" class="btn btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذه الوظيفة؟')">
              <i class="fas fa-trash"></i> حذف
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else : ?>
      <div class="no-jobs">
        <i class="fas fa-briefcase fa-4x mb-3" style="color: #ddd;"></i>
        <h4>لا توجد وظائف منشورة</h4>
        <p class="text-muted">
          يمكنك نشر وظيفة جديدة بالضغط على زر "إضافة وظيفة جديدة"
        </p>
        <a href="add-job-post.php" class="btn btn-primary mt-3">
          <i class="fas fa-plus"></i> إضافة وظيفة جديدة
        </a>
      </div>
      <?php endif; ?>
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
  <script src="../js/bootstrap.min.js"></script>
  <script src="js/employer-main.js"></script>
  <script>
    // تأكيد الحذف
    function confirmDelete(jobId) {
      if (confirm('هل أنت متأكد من حذف هذه الوظيفة؟')) {
        window.location.href = 'my-job-posts.php?delete=' + jobId;
      }
    }

    // إغلاق رسائل التنبيه بعد 5 ثواني
    $(document).ready(function() {
      setTimeout(function() {
        $('.alert').alert('close');
      }, 5000);
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