<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login-form.php");
  exit();
}
$logged_in = isset($_SESSION['user_id']);
// print_r($_SESSION['user_id']);

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

    $appliedJobs = [];
    if ($logged_in) {
      $stmt = $conn->prepare("SELECT job_id FROM job_applications WHERE user_id = ?");
      $stmt->execute([$_SESSION['user_id']]);
      $appliedJobs = $stmt->fetchAll(PDO::FETCH_COLUMN);
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

    // إضافة متغير للحد الحالي
    $currentLimit = 20; // الحد الافتراضي
    if (isset($_GET['load_more'])) {
      $currentLimit = (int)$_GET['current_limit'] + 20;
    }

    // تعديل جزء SQL لاستخدام المتغير الجديد
    $sql .= " ORDER BY jp.created_at DESC LIMIT $currentLimit";
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

// معالجة إضافة/إزالة الوظائف المفضلة
if ($logged_in && isset($_POST['action']) && $_POST['action'] == 'toggle_favorite') {
  $jobId = (int)$_POST['job_id'];

  try {
    // التحقق مما إذا كانت الوظيفة مفضلة بالفعل
    $stmt = $conn->prepare("SELECT 1 FROM favorite_jobs WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$_SESSION['user_id'], $jobId]);
    $isFavorite = $stmt->fetch();

    if ($isFavorite) {
      // إزالة من المفضلة
      $stmt = $conn->prepare("DELETE FROM favorite_jobs WHERE user_id = ? AND job_id = ?");
      $stmt->execute([$_SESSION['user_id'], $jobId]);
      echo json_encode(['status' => 'removed']);
    } else {
      // إضافة إلى المفضلة
      $stmt = $conn->prepare("INSERT INTO favorite_jobs (user_id, job_id) VALUES (?, ?)");
      $stmt->execute([$_SESSION['user_id'], $jobId]);
      echo json_encode(['status' => 'added']);
    }
    exit();
  } catch (Exception $e) {
    error_log("Favorite error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
    exit();
  }
}

// جلب الوظائف المفضلة للمستخدم
$favoriteJobs = [];
// جلب تفاصيل الوظائف المفضلة (للعرض في قسم المفضلة)
$favoriteJobsDetails = [];

if ($logged_in) {
  try {
    // هذا للقلوب في نتائج البحث
    $stmt = $conn->prepare("SELECT job_id FROM favorite_jobs WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $favoriteJobs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // هذا لعرض قسم المفضلة
    $stmt = $conn->prepare("SELECT jp.*, e.company_name
                              FROM favorite_jobs fj
                              JOIN job_posts jp ON fj.job_id = jp.id
                              JOIN employers e ON jp.employer_id = e.id
                              WHERE fj.user_id = ? AND jp.status = 'active'
                              ORDER BY fj.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $favoriteJobsDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    error_log("Fetch favorites error: " . $e->getMessage());
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
  <link rel="stylesheet" href="css/employee-dashboard.css">
</head>
<body>
  <!-- منطقة الرسائل الثابتة -->
  <div class="message-container" id="messageContainer"></div>

  <!-- شريط التنقل -->
  <nav class="navbar">
    <div>
      <span class="open-btn" onclick="openSidebar()">&#9776;</span>
      <p class="navbar-brand">
        توظيف الذكي
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
    <a href="#">الرئيسية</a>

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

      <form id="searchForm" method="GET" action="employee-dashboard.php">
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
                  <button class="btn btn-sm btn-link text-muted p-0 favorite-btn" data-job-id="<?php echo $job['id']; ?>">
                    <i class="far fa-heart <?php echo in_array($job['id'], $favoriteJobs) ? 'fas text-danger' : 'far'; ?>"></i>
                  </button>
                  <button class="btn btn-sm btn-link text-muted p-0" onclick="openComments(<?php echo $job['id']; ?>)">
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
              <!-- إضافة الوصف الوظيفي -->
              <div class="job-description mb-3" style="display: none;" id="desc-<?php echo $job['id']; ?>">
                <h6>الوصف الوظيفي:</h6>
                <p>
                  <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                </p>
              </div>

              <!-- تعديل زر التقديم ليشمل زر عرض الوصف -->
              <div class="d-flex justify-content-between">
                <button onclick="toggleDescription(<?php echo $job['id']; ?>)"
                  class="btn btn-outline-secondary btn-sm">
                  عرض الوصف
                </button>
                <?php if ($logged_in): ?>
                <?php if (in_array($job['id'], $appliedJobs)): ?>
                <button class="btn btn-success btn-sm" disabled> تم التقديم</button>
                <?php else : ?>
                <form id="applyForm_<?php echo $job['id']; ?>" method="POST" action="process-application.php" style="display: none;">
                  <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                  <input type="hidden" name="employer_id" value="<?php echo $job['employer_id']; ?>">
                  <input type="hidden" name="cover_letter" value="أرغب في التقدم لهذه الوظيفة">
                  <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
                  <input type="hidden" name="search_country" value="<?php echo htmlspecialchars($selectedCountry); ?>">
                </form>
                <button onclick="submitApplication(<?php echo $job['id']; ?>)"
                  class="btn btn-primary btn-sm">
                  تقديم الآن
                </button>
                <?php endif; ?>
                <?php else : ?>
                <a href="login-form.php" class="btn btn-primary btn-sm">تسجيل الدخول للتقديم</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($searchResults) && count($searchResults) >= $currentLimit): ?>
        <div class="text-center mt-4 w-100">
          <button class="btn btn-outline-primary" id="loadMoreBtn"
            data-current-limit="<?php echo $currentLimit; ?>"
            data-search-query="<?php echo htmlspecialchars($searchQuery); ?>"
            data-selected-country="<?php echo htmlspecialchars($selectedCountry); ?>">
            عرض المزيد من الوظائف
          </button>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

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

  <section class="">
    <div class="nav mt-2 mb-0">
      <p href="#" class="active" onclick="showSection('section1')">
        الوظائف المتقدم لها
      </p>
      <p href="#" onclick="showSection('section2')">
        الوظائف المفضلة
      </p>
      <p href="#" onclick="showSection('section3')">
        الوظائف المقترحة
      </p>
    </div>

    <!-- قسم الوظائف المتقدم لها -->
    <div id="section1" class="content-section active p-2">
      <div class="container-fluid">
        <?php
        if ($logged_in) {
          $applicationsQuery = "SELECT ja.*, jp.job_title, jp.country, jp.city, e.company_name
                                  FROM job_applications ja
                                  JOIN job_posts jp ON ja.job_id = jp.id
                                  JOIN employers e ON jp.employer_id = e.id
                                  WHERE ja.user_id = :user_id
                                  ORDER BY ja.applied_at DESC";
          $stmt = $conn->prepare($applicationsQuery);
          $stmt->bindParam(':user_id', $_SESSION['user_id']);
          $stmt->execute();
          $applications = $stmt->fetchAll();

          if (count($applications) > 0) {
            echo '<div class="row g-3">';
            foreach ($applications as $application) {
              echo '<div class="col-12 col-md-6 col-lg-4">';
              echo '<div class="card job-card h-100">';
              echo '<div class="card-body d-flex flex-column">';
              echo '<div class="d-flex justify-content-between align-items-start mb-3">';
              echo '<h5 class="mb-0">' . htmlspecialchars($application['job_title']) . '</h5>';
              echo '<form method="POST" action="delete-job-application.php" onsubmit="return confirm(\'هل أنت متأكد من حذف هذا الطلب؟\');">';
              echo '<input type="hidden" name="application_id" value="' . $application['id'] . '">';
              echo '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
              echo '</form>';
              echo '</div>';
              echo '<small class="text-muted mb-2">' . htmlspecialchars($application['company_name']) . '</small>';
              echo '<ul class="list-unstyled text-muted mb-3 flex-grow-1">';
              echo '<li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>' . htmlspecialchars($application['city'] . '، ' . $application['country']) . '</li>';
              echo '<li class="mb-2"><i class="fas fa-clock me-2"></i>تم التقديم: ' . htmlspecialchars($application['applied_at']) . '</li>';
              echo '<li><i class="fas fa-info-circle me-2"></i>الحالة: ' . htmlspecialchars($application['status']) . '</li>';
              echo '</ul>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
            }
            echo '</div>';
          } else {
            echo '<div class="empty-state">';
            echo '<i class="fas fa-file-alt"></i>';
            echo '<h4>لا توجد وظائف متقدم لها</h4>';
            echo '<p>عندما تتقدم لوظائف جديدة، ستظهر هنا</p>';
            echo '<a href="#" class="btn btn-primary">تصفح الوظائف</a>';
            echo '</div>';
          }
        } else {
          echo '<script>showAlert("warning", "يجب تسجيل الدخول لعرض الوظائف المتقدم لها", "fa-exclamation-circle");</script>';
        }
        ?>
      </div>
    </div>

    <!-- قسم الوظائف المفضلة -->
    <div id="section2" class="content-section p-2">
      <div class="container-fluid">
        <?php
        if (!$logged_in) {
          echo '<script>showAlert("warning", "يجب تسجيل الدخول لعرض الوظائف المفضلة", "fa-exclamation-circle");</script>';
        } elseif (count($favoriteJobsDetails) > 0) {
          echo '<div class="row g-3">';

          foreach ($favoriteJobsDetails as $job) {
            echo '<div class="col-12 col-md-6 col-lg-4">';
            echo '<div class="card job-card h-100">';
            echo '<div class="card-body d-flex flex-column">';
            echo '<div class="d-flex justify-content-between align-items-start mb-3">';
            echo '<h5 class="mb-0">' . htmlspecialchars($job['job_title']) . '</h5>';
            echo '<div>';
            echo '<button class="btn btn-sm btn-link text-danger p-0 favorite-btn" data-job-id="' . $job['id'] . '">';
            echo '<i class="fas fa-heart"></i>';
            echo '</button>';
            echo '<button class="btn btn-sm btn-link text-muted p-0" onclick="openComments(' . $job['id'] . ')">';
            echo '<i class="fa-regular fa-comments"></i>';
            echo '</button>';
            echo '</div>';
            echo '</div>';
            echo '<small class="text-muted mb-2">' . htmlspecialchars($job['company_name']) . '</small>';
            echo '<ul class="list-unstyled text-muted mb-3 flex-grow-1">';
            echo '<li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>' . htmlspecialchars($job['city'] . '، ' . $job['country']) . '</li>';

            if (!empty($job['skills'])) {
              echo '<li class="skills-tags">';
              $skills = explode(',', $job['skills']);
              foreach ($skills as $skill) {
                if (!empty(trim($skill))) {
                  echo '<span class="badge">' . htmlspecialchars(trim($skill)) . '</span>';
                }
              }
              echo '</li>';
            }

            echo '</ul>';
            echo '<a href="#" class="btn btn-primary w-100">عرض التفاصيل</a>';
            echo '</div></div></div>';
          }

          echo '</div>';
        } else {
          echo '<div class="empty-state">';
          echo '<i class="fas fa-heart"></i>';
          echo '<h4>لا توجد وظائف مفضلة</h4>';
          echo '<p>عندما تقوم بإضافة وظائف إلى المفضلة، ستظهر هنا</p>';
          echo '<a href="#" class="btn btn-primary">تصفح الوظائف</a>';
          echo '</div>';
        }
        ?>
      </div>
    </div>

    <!-- قسم الوظائف المقترحة -->
    <div id="section3" class="content-section w-100">
      <section class="py-5 bg-white">
        <div class="container">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h2 class="fw-bold m-0">وظائف مقترحة لك</h2>
              <p class="text-muted m-0">
                وظائف تناسب خبراتك ومهاراتك
              </p>
            </div>
            <div>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active">الأحدث</button>
                <button type="button" class="btn btn-outline-primary">الأعلى راتباً</button>
                <button type="button" class="btn btn-outline-primary">عن بُعد</button>
              </div>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-md-6 col-lg-4 my-2">
              <div class="card job-card h-100 hover-shadow">
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-3">
                    <div>
                      <span class="badge bg-primary-light">full-time</span>
                      <span class="badge bg-primary-light">remote</span>
                    </div>
                    <div>
                      <button class="btn btn-sm btn-link text-muted p-0">
                        <i class="fa-regular fa-comments"></i>
                      </button>
                      <button class="btn btn-sm btn-link text-muted p-0">
                        <i class="far fa-heart"></i>
                      </button>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div>
                      <h5 class="m-0">مطور ويب</h5>
                      <small class="text-muted">شركة التقنية</small>
                    </div>
                  </div>
                  <ul class="list-unstyled text-muted mb-3">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> الرياض، السعودية</li>
                    <li class="mb-2"><i class="fas fa-clock me-2"></i> منذ 3 أيام</li>
                    <li><i class="fas fa-coins me-2"></i><span> 2000-5000 $</span></li>
                    <li>
                      <i class="fas fa-chalkboard-teacher me-2"></i>
                      الخبرة المطلوبة:
                      <span>3 سنوات</span>
                    </li>
                  </ul>
                  <div class="skills-tags mb-3">
                    <span class="badge bg-secondary-light text-dark me-2">HTML</span>
                    <span class="badge bg-secondary-light text-dark me-2">CSS</span>
                    <span class="badge bg-secondary-light text-dark">JavaScript</span>
                  </div>
                  <a href="#" class="btn btn-primary w-100">تقديم الآن</a>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center mt-4">
            <a href="#" class="btn btn-outline-primary px-4">عرض جميع الوظائف</a>
          </div>
        </div>
      </section>
    </div>
  </section>

  <!-- نافذة التعليقات -->
  <div class="comments-overlay" id="commentsOverlay"></div>
  <div class="comments-container" id="commentsContainer">
    <div class="comments-header">
      <h5>التعليقات</h5>
      <span class="close-btn" onclick="closeComments()">&times;</span>
    </div>
    <div class="comments-body" id="commentsBody">
      <!-- سيتم تحميل التعليقات هنا -->
    </div>
    <div class="comments-footer">
      <div class="comment-input">
        <input type="text" id="commentText" placeholder="أضف تعليقًا...">
        <button onclick="postComment()"><i class="fas fa-paper-plane"></i></button>
      </div>
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

  <!-- أيقونة الـ chatbot -->
  <a href="chatbot.php?employee=true" class="chatbot-icon" id="chatbotIcon">
    <img src="images/chatbot-icon.png" alt="Chatbot">
  </a>

  <script src="js/jquery.min.js"></script>
  <script src="js/main.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    // دالة لتحميل المزيد من الوظائف
// دالة لتحميل المزيد من الوظائف
function loadMoreJobs() {
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (!loadMoreBtn) return;

    loadMoreBtn.addEventListener('click', function() {
        const currentLimit = parseInt(this.getAttribute('data-current-limit'));
        const searchQuery = this.getAttribute('data-search-query');
        const selectedCountry = this.getAttribute('data-selected-country');

        // عرض مؤشر التحميل
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
        this.disabled = true;

        // إرسال طلب AJAX لتحميل المزيد من الوظائف
        $.ajax({
            url: 'employee-dashboard.php',
            method: 'GET',
            data: {
                search: searchQuery,
                country: selectedCountry,
                load_more: true,
                current_limit: currentLimit
            },
            success: function(response) {
                // إنشاء عنصر مؤقت لتحليل HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = response;
                
                // استخراج الوظائف الجديدة
                const newJobs = tempDiv.querySelector('.search-results .row').innerHTML;
                
                // استخراج زر "عرض المزيد" المحدث
                const updatedLoadMoreBtn = tempDiv.getElementById('loadMoreBtn');
                
                // إضافة الوظائف الجديدة إلى القائمة الحالية
                document.querySelector('.search-results .row').insertAdjacentHTML('beforeend', newJobs);
                
                // تحديث أو إزالة زر "عرض المزيد"
                if (updatedLoadMoreBtn) {
                    loadMoreBtn.setAttribute('data-current-limit', 
                        updatedLoadMoreBtn.getAttribute('data-current-limit'));
                } else {
                    loadMoreBtn.remove();
                }
            },
            error: function() {
                showAlert('danger', 'حدث خطأ أثناء تحميل المزيد من الوظائف', 'fa-exclamation-triangle');
                loadMoreBtn.innerHTML = 'عرض المزيد من الوظائف';
                loadMoreBtn.disabled = false;
            }
        });
    });
}

    // استدعاء الدالة عند تحميل الصفحة
    $(document).ready(function() {
      loadMoreJobs();
    });

    // إضافة هذه الدالة في قسم الـ script
    function toggleDescription(jobId) {
      const descElement = document.getElementById(`desc-${jobId}`);
      const button = event.currentTarget;

      if (descElement.style.display === 'none' || !descElement.style.display) {
        descElement.style.display = 'block';
        button.textContent = 'إخفاء الوصف';
      } else {
        descElement.style.display = 'none';
        button.textContent = 'عرض الوصف';
      }
    }

    // متغير لمنع الطلبات المكررة
    let isProcessing = false;

    // معالجة النقر على أيقونة القلب
    $(document).on('click', '.favorite-btn', function(e) {
      e.preventDefault();

      if (isProcessing) return;
      isProcessing = true;

      const jobId = $(this).data('job-id');
      const heartIcon = $(this).find('i');

      $.ajax({
        url: 'employee-dashboard.php',
        method: 'POST',
        data: {
          action: 'toggle_favorite', job_id: jobId
        },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'added') {
            heartIcon.removeClass('far').addClass('fas text-danger');
            showAlert('success', 'تمت إضافة الوظيفة إلى المفضلة بنجاح', 'fa-heart');
          } else if (response.status === 'removed') {
            heartIcon.removeClass('fas text-danger').addClass('far');
            showAlert('info', 'تمت إزالة الوظيفة من المفضلة', 'fa-heart');
          }

          // تحديث قسم المفضلات إذا كان مرئيًا
          if ($('#section2').hasClass('active')) {
            location.reload(); // إعادة تحميل الصفحة لتحديث البيانات
          }
        },
        complete: function() {
          isProcessing = false;
        },
        error: function(xhr, status, error) {
          console.error('حدث خطأ:', error);
          showAlert('danger', 'حدث خطأ أثناء معالجة طلبك', 'fa-exclamation-triangle');
          isProcessing = false;
        }
      });
    });

    // تحديث قسم الوظائف المفضلة عند النقر عليه
    function showSection(sectionId) {
      // إخفاء جميع الأقسام
      document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
      });

      // إزالة النشط من جميع أزرار التنقل
      document.querySelectorAll('.nav p').forEach(link => {
        link.classList.remove('active');
      });

      // إظهار القسم المطلوب
      document.getElementById(sectionId).classList.add('active');

      // إضافة النشط إلى الزر المحدد
      event.currentTarget.classList.add('active');
    }

    // دالة معدلة لعرض الرسائل
    function showAlert(type, message, icon = null) {
      // الرموز الافتراضية لكل نوع من الرسائل
      const icons = {
        'success': 'fa-check-circle',
        'info': 'fa-info-circle',
        'warning': 'fa-exclamation-circle',
        'danger': 'fa-times-circle'
      };

      // إذا لم يتم توفير أيقونة، استخدم الإفتراضي حسب النوع
      if (!icon) {
        icon = icons[type] || 'fa-info-circle';
      }

      const alertId = 'alert-' + Date.now();
      const alertHtml = `
      <div id="${alertId}" class="custom-alert alert-${type}">
      <button class="close-btn" onclick="document.getElementById('${alertId}').remove()">&times;</button>
      <i class="fas ${icon}"></i>
      ${message}
      </div>
      `;

      // إضافة الرسالة إلى حاوية الرسائل
      const container = document.getElementById('messageContainer');
      container.insertAdjacentHTML('afterbegin', alertHtml);

      // إخفاء الرسالة بعد 5 ثواني
      setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
          alert.style.opacity = '0';
          setTimeout(() => alert.remove(), 300);
        }
      },
        5000);
    }

    function submitApplication(jobId) {
      if (confirm('هل أنت متأكد من التقدم لهذه الوظيفة؟')) {
        document.getElementById('applyForm_' + jobId).submit();
      }
    }

    function confirmDelete(applicationId) {
      if (confirm('هل أنت متأكد من حذف هذا الطلب؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete-job-application.php';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'application_id';
        input.value = applicationId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
      }
      return false;
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


    // متغيرات التعليقات
    let currentJobId = null;

    // فتح نافذة التعليقات
    function openComments(jobId) {
      currentJobId = jobId;
      document.getElementById('commentsOverlay').style.display = 'block';
      document.getElementById('commentsContainer').style.display = 'flex';
      document.body.style.overflow = 'hidden';

      // تحميل التعليقات (هنا يمكنك إضافة AJAX لتحميل التعليقات الحقيقية)
      loadComments(jobId);
    }

    // إغلاق نافذة التعليقات
    function closeComments() {
      document.getElementById('commentsOverlay').style.display = 'none';
      document.getElementById('commentsContainer').style.display = 'none';
      document.body.style.overflow = 'auto';
      currentJobId = null;
    }

    // دالة لتحميل التعليقات من الخادم
    function loadComments(jobId) {
      $.ajax({
        url: 'load-comments.php',
        method: 'GET',
        data: {
          job_id: jobId
        },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            renderComments(response.comments);
          } else {
            showAlert('danger', 'حدث خطأ أثناء تحميل التعليقات', 'fa-exclamation-triangle');
            console.error(response.message);
          }
        },
        error: function(xhr, status, error) {
          showAlert('danger', 'حدث خطأ في الاتصال بالخادم', 'fa-exclamation-triangle');
          console.error('Error loading comments:', error);
        }
      });
    }

    // عرض التعليقات في الواجهة
    function renderComments(comments) {
      const commentsBody = document.getElementById('commentsBody');

      if (comments.length === 0) {
        commentsBody.innerHTML = '<div class="text-center py-3">لا توجد تعليقات بعد</div>';
        return;
      }

      let commentsHtml = '';
      comments.forEach(comment => {
        commentsHtml += `
        <div class="comment-item">
        <div class="d-flex align-items-center mb-2">
        <div class="comment-author">${comment.full_name}</div>
        </div>
        <div class="comment-text">${comment.comment_text}</div>
        <div class="comment-time">${comment.time_ago}</div>
        </div>
        `;
      });

      commentsBody.innerHTML = commentsHtml;
    }

    // نشر تعليق جديد
    function postComment() {
      const commentText = document.getElementById('commentText').value.trim();
      if (!commentText || !currentJobId) return;

      $.ajax({
        url: 'save-comment.php',
        method: 'POST',
        data: {
          job_id: currentJobId,
          comment_text: commentText
        },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            document.getElementById('commentText').value = '';
            // إضافة التعليق الجديد إلى القائمة
            const commentsBody = document.getElementById('commentsBody');
            const newCommentHtml = `
            <div class="comment-item">
            <div class="d-flex align-items-center mb-2">
            <div class="comment-author">${response.comment.full_name}</div>
            </div>
            <div class="comment-text">${response.comment.comment_text}</div>
            <div class="comment-time">${response.comment.time_ago}</div>
            </div>
            `;

            if (commentsBody.innerHTML.includes('لا توجد تعليقات بعد')) {
              commentsBody.innerHTML = newCommentHtml;
            } else {
              commentsBody.innerHTML = newCommentHtml + commentsBody.innerHTML;
            }
          } else {
            showAlert('danger', response.message, 'fa-exclamation-triangle');
          }
        },
        error: function(xhr, status, error) {
          showAlert('danger', 'حدث خطأ أثناء إرسال التعليق', 'fa-exclamation-triangle');
          console.error('Error posting comment:', error);
        }
      });
    }

    // عرض رسالة نجاح التقديم إذا كانت موجودة في الجلسة
    <?php if (isset($_GET['application_success'])): ?>
    showAlert('success', 'تم تقديم طلبك بنجاح!', 'fa-check-circle');
    <?php endif; ?>

    // عرض رسائل الجلسة إذا كانت موجودة
    <?php if (isset($_SESSION['success_message'])): ?>
    showAlert('success', '<?php echo $_SESSION['success_message']; ?>', 'fa-check-circle');
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    showAlert('danger', '<?php echo $_SESSION['error_message']; ?>', 'fa-exclamation-triangle');
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
  </script>
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