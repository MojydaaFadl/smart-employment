<?php
session_start();
$logged_in = isset($_SESSION['user_id']);

// التحقق من وجود سيرة ذاتية للمستخدم المسجل
if ($logged_in) {
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
  $user_id = $_SESSION['user_id'];
  $sql = "SELECT file_path FROM user_cvs WHERE user_id = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $cv_path = str_replace('../', '', $row['file_path']);

    // التحقق من وجود الملف فعلياً
    if (file_exists($cv_path)) {
      header("Location: employee-dashboard.php");
      exit();
    }
  }

  $stmt->close();
  $conn->close();
}

// معالجة البحث عن الوظائف
require_once 'db_connection.php';
$searchResults = [];
$searchQuery = '';
$selectedCountry = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['search']) || isset($_GET['country']))) {
  $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
  $selectedCountry = isset($_GET['country']) ? trim($_GET['country']) : '';

  try {
    $sql = "SELECT jp.*, e.company_name
            FROM job_posts jp
            JOIN employers e ON jp.employer_id = e.id
            WHERE jp.status = 'active'";

    $params = [];

    // استبعاد الوظائف التي تقدم لها المستخدم إذا كان مسجلاً
    if ($logged_in) {
      $sql .= " AND jp.id NOT IN (
                SELECT ja.job_id
                FROM job_applications ja
                WHERE ja.user_id = ?
              )";
      $params[] = $_SESSION['user_id'];
    }

    if (!empty($searchQuery)) {
      $sql .= " AND (jp.job_title LIKE ? OR jp.skills LIKE ? OR jp.job_description LIKE ? OR e.company_name LIKE ?)";
      $searchTerm = "%$searchQuery%";
      $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    // تعديل شرط البحث حسب الدولة
    if (!empty($selectedCountry) && $selectedCountry !== 'All') {
      $sql .= " AND jp.country = ?";
      $params[] = $selectedCountry;
    }

    $sql .= " ORDER BY jp.created_at DESC LIMIT 20";

    $stmt = $conn->prepare($sql);

    // ربط المعلمات
    foreach ($params as $key => $value) {
      $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
      $stmt->bindValue($key + 1, $value, $paramType);
    }

    $stmt->execute();
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    $searchResults = [];
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التوظيف الذكي - الرئيسية</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/fontawesome/css/all.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/index.css">
  <style>
    /* أنماط بطاقات الوظائف */
    .job-card {
      border-radius: 12px;
      border: 1px #eee solid;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s;
      margin-bottom: 20px;
    }

    .job-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .job-card .card-body {
      padding: 25px;
    }

    .job-card h5 {
      color: #007bff;
      margin-bottom: 15px;
    }

    .job-card .badge {
      background: #e3f2fd;
      color: #007bff;
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: normal;
      margin-right: 5px;
    }

    .skills-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      margin-bottom: 15px;
    }

    .skills-tags .badge {
      background-color: #f8f9fa;
      color: #495057;
    }

    .search-results {
      margin-top: 30px;
    }

    .search-results-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .no-results {
      text-align: center;
      padding: 40px 20px;
      background-color: #f8f9fa;
      border-radius: 8px;
      margin-top: 20px;
    }

    .no-results i {
      font-size: 50px;
      color: #6c757d;
      margin-bottom: 15px;
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
    <a href="<?php echo $logged_in ? 'upload-create-profile.php' : 'register-form.php'; ?>">إنشاء ملفك الشخصي</a>
    <a href="#">بريميوم</a>
    <a href="#">المدونة</a>
    <?php if (!$logged_in): ?>
    <a href="for-employers/employer-dashboard.php">لأصحاب العمل</a>
    <?php endif; ?>


    <a href="#">English</a>

    <?php if ($logged_in): ?>
    <div class="sidebar-buttons">
      <span style="color: white; padding: 10px;">مرحباً <?php echo $_SESSION['first_name']; ?></span>
      <a href="logout.php" class="btn btn-outline-danger">تسجيل خروج</a>
    </div>
    <?php else : ?>
    <div class="sidebar-buttons">
      <a href="register-form.php" class="btn btn-primary">تسجيل</a>
      <a href="login-form.php" class="btn btn-outline-primary">دخول</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- قسم البحث -->
  <section class="search-hero shadow">
    <div class="container">
      <div>
        <h3 class="text-center p-3">ابدأ رحلتك الوظيفية معنا</h3>
        <p class="text-center fs-5">
          تمتع بمزايا البحث الذكي، التوصيات الشخصية، إشعارات الوظائف الجديدة - كل ما
          تحتاجه للعثور على الوظيفة المثالية في مكان واحد.
        </p>
      </div>

      <form id="searchForm" method="GET" action="index22.php">
        <div class="form-group search-input">
          <input type="text" id="search-input" name="search" placeholder="ابحث عن الوظائف والمهارات والشركات"
          value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>
        <div class="form-group search-select">
          <input type="text" class="country-select" id="country-display"
          placeholder="اختر بلدا" readonly onclick="openCountrySelect()"
          value="<?php echo htmlspecialchars($selectedCountry); ?>">
          <input type="hidden" id="country-value" name="country" value="<?php echo htmlspecialchars($selectedCountry); ?>">
          <button type="submit" class="btn btn-primary search-btn"><i class="fas fa-search"></i></button>
        </div>
      </form>
    </div>
  </section>

  <!-- عرض نتائج البحث -->
  <?php if (!empty($searchQuery) || !empty($selectedCountry)): ?>
  <section class="search-results">
    <div class="container">
      <div class="search-results-header">
        <h3>نتائج البحث</h3>
        <p>
          <?php echo count($searchResults); ?> وظيفة متاحة
        </p>
      </div>

      <?php if (empty($searchResults)): ?>
      <div class="no-results">
        <i class="fas fa-search"></i>
        <h4>لا توجد نتائج مطابقة للبحث</h4>
        <p>
          حاول تعديل كلمات البحث أو معايير البحث الخاصة بك
        </p>
      </div>
      <?php else : ?>
      <div class="row">
        <?php foreach ($searchResults as $job): ?>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card job-card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between mb-3">
                <div>
                  <span class="badge"><?php echo htmlspecialchars($job['employment_type']); ?></span>
                  <span class="badge"><?php echo htmlspecialchars($job['job_type']); ?></span>

                </div>
                <div>
                  <?php if ($logged_in): ?>
                  <button class="btn btn-sm btn-link text-muted p-0">
                    <i class="far fa-heart"></i>
                  </button>
                  <button class="btn btn-sm btn-link text-muted p-0">
                    <i class="fa-regular fa-comments"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </div>
              <div class="mb-3">
                <h5><?php echo htmlspecialchars($job['job_title']); ?></h5>
                <small class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></small>
              </div>
              <ul class="list-unstyled text-muted mb-3">
                <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>
                  <?php echo htmlspecialchars($job['city'] . '، ' . $job['country']); ?>
                </li>
                <li class="mb-2"><i class="fas fa-clock me-2"></i>
                  <?php
                  $createdAt = new DateTime($job['created_at']);
                  echo 'منذ ' . humanTiming($createdAt->getTimestamp());
                  ?>
                </li>
                <li><i class="fas fa-coins me-2"></i>
                  <?php
                  if (!empty($job['min_salary']) && !empty($job['max_salary'])) {
                    echo htmlspecialchars($job['min_salary'] . '-' . $job['max_salary'] . ' ر.س');
                  } elseif (!empty($job['min_salary'])) {
                    echo 'من ' . htmlspecialchars($job['min_salary']) . ' ر.س';
                  } elseif (!empty($job['max_salary'])) {
                    echo 'حتى ' . htmlspecialchars($job['max_salary']) . ' ر.س';
                  } else {
                    echo 'الراتب غير محدد';
                  }
                  ?>
                </li>
                <li>
                  <i class="fas fa-chalkboard-teacher me-2"></i>
                  الخبرة :
                  <span><?php echo htmlspecialchars($job['experience_years']); ?></span>
                </li>
              </ul>
              <?php if (!empty($job['skills'])): ?>
              <div class="skills-tags">
                <?php
                $skills = explode(',', $job['skills']);
                foreach ($skills as $skill):
                if (!empty(trim($skill))):
                ?>
                <span class="badge"><?php echo htmlspecialchars(trim($skill)); ?></span>
                <?php
                endif;
                endforeach;
                ?>
              </div>
              <?php endif; ?>
              <?php if ($logged_in): ?>
              <a href="apply-job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary w-100">تقديم الآن</a>
              <?php else : ?>
              <a href="login-form.php" class="btn btn-primary w-100">تسجيل الدخول للتقديم</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- قسم التخصصات المطلوبة -->
  <section class="py-5 bg-white animate-on-scroll">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">التخصصات المطلوبة</h2>
        <p class="text-muted">
          تصفح الوظائف حسب مجال خبرتك
        </p>
      </div>

      <div class="row g-4">
        <div class="col-md-3 col-6">
          <div class="category-card p-4 text-center border rounded-3 h-100 hover-shadow">
            <div class="icon-box bg-primary-light mb-3 mx-auto">
              <i class="fas fa-laptop-code text-primary fa-2x"></i>
            </div>
            <h5 class="fw-bold">تكنولوجيا المعلومات</h5>
            <p class="text-muted small">
              1,234 وظيفة متاحة
            </p>
            <a href="index22.php?search=تكنولوجيا+المعلومات" class="btn btn-sm btn-outline-primary">استكشف</a>
          </div>
        </div>

        <div class="col-md-3 col-6">
          <div class="category-card p-4 text-center border rounded-3 h-100 hover-shadow">
            <div class="icon-box bg-primary-light mb-3 mx-auto">
              <i class="fas fa-chart-line text-primary fa-2x"></i>
            </div>
            <h5 class="fw-bold">المحاسبة والمالية</h5>
            <p class="text-muted small">
              876 وظيفة متاحة
            </p>
            <a href="index22.php?search=المحاسبة" class="btn btn-sm btn-outline-primary">استكشف</a>
          </div>
        </div>

        <div class="col-md-3 col-6">
          <div class="category-card p-4 text-center border rounded-3 h-100 hover-shadow">
            <div class="icon-box bg-primary-light mb-3 mx-auto">
              <i class="fas fa-user-tie text-primary fa-2x"></i>
            </div>
            <h5 class="fw-bold">الموارد البشرية</h5>
            <p class="text-muted small">
              543 وظيفة متاحة
            </p>
            <a href="index22.php?search=الموارد+البشرية" class="btn btn-sm btn-outline-primary">استكشف</a>
          </div>
        </div>

        <div class="col-md-3 col-6">
          <div class="category-card p-4 text-center border rounded-3 h-100 hover-shadow">
            <div class="icon-box bg-primary-light mb-3 mx-auto">
              <i class="fas fa-bullhorn text-primary fa-2x"></i>
            </div>
            <h5 class="fw-bold">التسويق</h5>
            <p class="text-muted small">
              765 وظيفة متاحة
            </p>
            <a href="index22.php?search=التسويق" class="btn btn-sm btn-outline-primary">استكشف</a>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- قسم نصائح التوظيف -->
  <section class="py-5 bg-light animate-on-scroll">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">نصائح لنجاحك الوظيفي</h2>
        <p class="text-muted">
          تعلم كيفية تطوير مسيرتك المهنية
        </p>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card border-0 h-100 hover-shadow">

            <div class="card-body">
              <h5 class="card-title">كيف تكتب سيرة ذاتية جذابة؟</h5>
              <p class="card-text text-muted">
                تعلم أهم النصائح لإنشاء سيرة ذاتية تلفت انتباه مسؤولي
                التوظيف
              </p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="#" class="btn btn-sm btn-primary">اقرأ المزيد</a>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 h-100 hover-shadow">

            <div class="card-body">
              <h5 class="card-title">10 نصائح لاجتياز المقابلة الوظيفية</h5>
              <p class="card-text text-muted">
                كيف تترك انطباعاً جيداً وتزيد فرصك في القبول
              </p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="#" class="btn btn-sm btn-primary">اقرأ المزيد</a>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 h-100 hover-shadow">
            <div class="card-body">
              <h5 class="card-title">كيف تطور مسيرتك المهنية؟</h5>
              <p class="card-text text-muted">
                خطوات عملية للارتقاء في سلم النجاح الوظيفي
              </p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="#" class="btn btn-sm btn-primary">اقرأ المزيد</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- التذييل -->
  <footer class="footer">
    <div class="container">
      <p>
        &copy; 2025 التوظيف الذكي. جميع الحقوق محفوظة.
      </p>
      <p>
        <a href="#">سياسة الخصوصية</a> |
        <a href="#">شروط الاستخدام</a> |
        <a href="#">اتصل بنا</a>
      </p>
    </div>
  </footer>

  <!-- قائمة الدول الكاملة مع بحث -->
  <div class="fullpage-select-overlay" id="countryOverlay"></div>

  <div class="fullpage-select-container" id="countrySelect">
    <div class="select-header">
      <h4>اختر دولة</h4>
      <span class="close-select" onclick="closeCountrySelect()">&times;</span>
    </div>
    <div class="select-search">
      <input type="text" id="countrySearch" placeholder="ابحث عن دولة..." oninput="filterCountries()">
    </div>
    <div class="select-options" id="countryOptions">
      <!-- سيتم ملء الخيارات بواسطة الجافاسكريبت -->
    </div>
  </div>

  <script src="js/main.js"></script>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
// دالة لتحويل التاريخ إلى صيغة "منذ x وقت"
function humanTiming($time) {
  $time = time() - $time;
  $time = ($time < 1) ? 1 : $time;
  $tokens = array(
    31536000 => 'سنة',
    2592000 => 'شهر',
    604800 => 'أسبوع',
    86400 => 'يوم',
    3600 => 'ساعة',
    60 => 'دقيقة',
    1 => 'ثانية'
  );

  foreach ($tokens as $unit => $text) {
    if ($time < $unit) continue;
    $numberOfUnits = floor($time / $unit);
    return $numberOfUnits.' '.$text.(($numberOfUnits > 1)?'':'');
  }
}
?>