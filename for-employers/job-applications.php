<?php
require_once 'db_connection.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['employer_id'])) {
  header('Location: employer-login.php');
  exit();
}

// التحقق من وجود معرف الوظيفة في الرابط
if (!isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) {
  header('Location: my-job-posts.php');
  exit();
}

$jobId = $_GET['job_id'];
$employerId = $_SESSION['employer_id'];

// التحقق من أن الوظيفة تخص صاحب العمل الحالي
$stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $employerId]);
$job = $stmt->fetch();

if (!$job) {
  header('Location: my-job-posts.php');
  exit();
}

// جلب المتقدمين للوظيفة مع إصلاح مسار السيرة الذاتية
$stmt = $conn->prepare("
    SELECT ja.*, u.first_name, u.last_name, u.email, uc.file_name, uc.file_path
    FROM job_applications ja
    JOIN users u ON ja.user_id = u.user_id
    LEFT JOIN user_cvs uc ON u.user_id = uc.user_id
    WHERE ja.job_id = ?
    ORDER BY ja.applied_at DESC
  ");
$stmt->execute([$jobId]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التوظيف الذكي - المتقدمين للوظيفة</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/fontawesome/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="css/employer-style.css">
  <link rel="stylesheet" href="css/jop-applications-style.css">
  <script src="../js/pdf.min.js"></script>

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
      <!-- قسم معلومات الوظيفة والمتقدمين -->
      <div class="job-applicants-header mb-4 p-4 bg-white rounded-3 shadow-sm border">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h2 class="mb-2 text-primary">
              المتقدمين للوظيفة
            </h2>
            <h3 class="mb-3 fw-bold"><?php echo htmlspecialchars($job['job_title']); ?></h3>

            <div class="job-meta d-flex flex-wrap gap-2 mb-3">
              <span class="badge bg-primary text-white">
                <i class="fas fa-industry me-1"></i> <?php echo htmlspecialchars($job['industry']); ?>
              </span>
              <span class="badge bg-secondary text-white">
                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['country'].'_'.$job['city']); ?>
              </span>
              <span class="badge bg-info text-white">
                <i class="fas fa-briefcase me-1"></i> <?php echo htmlspecialchars($job['employment_type']); ?>
              </span>
              <?php if ($job['min_salary'] && $job['max_salary']): ?>
              <span class="badge bg-success text-white">
                <i class="fas fa-money-bill-wave me-1"></i>
                <?php echo $job['min_salary'] . ' - ' . $job['max_salary'] . ' ر.س'; ?>
              </span>
              <?php endif; ?>
            </div>
          </div>

          <div class="d-flex flex-column align-items-end">
            <a href="my-job-posts.php" class="btn btn-outline-primary btn-sm mb-2 px-3">
              <i class="fas fa-arrow-right me-1"></i> العودة
            </a>
            <span class="text-muted small">
              <i class="far fa-calendar-alt me-1"></i>
              <?php echo date('Y/m/d', strtotime($job['created_at'])); ?>
            </span>
          </div>
        </div>

        <!-- إحصائيات المتقدمين المصغرة -->
        <div class="applicants-stats-container">
          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <!-- بطاقة إجمالي المتقدمين -->
            <div class="stat-pill total">
              <div class="stat-icon">
                <i class="fas fa-users"></i>
              </div>
              <div class="stat-content">
                <span class="stat-label">المجموع</span>
                <span class="stat-value"><?php echo count($applications); ?></span>
              </div>
            </div>

            <!-- بطاقة إجمالي الشواغر -->
            <div class="stat-pill vacancies">
              <div class="stat-icon">
                <i class="fas fa-briefcase"></i>
              </div>
              <div class="stat-content">
                <span class="stat-label">عدد الشواغر</span>
                <span class="stat-value"><?php echo $job['vacancies']; ?></span>
              </div>
            </div>

            <!-- بطاقة قيد المراجعة -->
            <div class="stat-pill pending">
              <div class="stat-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="stat-content">
                <span class="stat-label">قيد المراجعة</span>
                <span class="stat-value">
                  <?php echo array_reduce($applications, function($carry, $app) {
                    return $carry + ($app['status'] == 'pending' ? 1 : 0);
                  }, 0); ?>
                </span>
              </div>
            </div>

            <!-- بطاقة المقبولين -->
            <div class="stat-pill accepted">
              <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="stat-content">
                <span class="stat-label">مقبولين</span>
                <span class="stat-value">
                  <?php echo array_reduce($applications, function($carry, $app) {
                    return $carry + ($app['status'] == 'accepted' ? 1 : 0);
                  }, 0); ?>
                </span>
              </div>
            </div>

            <!-- بطاقة المرفوضين -->
            <div class="stat-pill rejected">
              <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
              </div>
              <div class="stat-content">
                <span class="stat-label">مرفوضين</span>
                <span class="stat-value">
                  <?php echo array_reduce($applications, function($carry, $app) {
                    return $carry + ($app['status'] == 'rejected' ? 1 : 0);
                  }, 0); ?>
                </span>
              </div>
            </div>

            <!-- بطاقة حالة الوظيفة -->
            <div class="stat-pill status">
              <div class="stat-icon">
                <i class="fas fa-info-circle"></i>
              </div>
              <div class="stat-content">
                <span class="stat-label">حالة الوظيفة</span>
                <span class="stat-value">
                  <?php echo $job['status'] == 'active' ? 'نشطة' :
                  ($job['status'] == 'filled' ? 'تم شغلها' : 'غير نشطة'); ?>
                </span>
              </div>
            </div>
          </div>
        </div>
        <button class="cv-analysis btn btn-primary w-100 mt-3" onclick="analyzeCVs()">
          تحليل السير الذاتية للمتقدمين لاختيار افضل المرشحين
        </button>
      </div>

      <?php if (count($applications) > 0): ?>
      <div class="applications-list">
        <?php foreach ($applications as $application): ?>
        <div class="application-card">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h3 class="applicant-name">
                <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?>
              </h3>
              <div class="applicant-email">
                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($application['email']); ?>
              </div>
            </div>
            <span class="status-badge status-<?php echo $application['status']; ?>">
              <?php
              if ($application['status'] == 'pending') echo 'قيد المراجعة';
              elseif ($application['status'] == 'reviewed') echo 'تمت المراجعة';
              elseif ($application['status'] == 'accepted') echo 'مقبول';
              else echo 'مرفوض';
              ?>
            </span>
          </div>

          <div class="application-meta">
            <span><i class="far fa-calendar-alt"></i> تاريخ التقديم: <?php echo date('Y/m/d', strtotime($application['applied_at'])); ?></span>
          </div>

          <div class="application-actions">
            <?php if ($application['file_path']): ?>
            <?php
            // إصلاح مسار الملف بإضافة للرجوع للمجلد الرئيسي
            $corrected_path = $application['file_path'];
            $absolute_path = realpath($corrected_path);
            $file_exists = file_exists($absolute_path);
            ?>

            <?php if ($file_exists): ?>
            <button class="btn-cv" onclick="openCVModal('<?php echo htmlspecialchars($corrected_path); ?>', '<?php echo htmlspecialchars($application['file_name']); ?>')">
              <i class="fas fa-file-pdf"></i> عرض السيرة الذاتية
            </button>
            <?php else : ?>
            <a href="<?php echo htmlspecialchars($corrected_path); ?>" download="<?php echo htmlspecialchars($application['file_name']); ?>" class="btn-cv">
              <i class="fas fa-file-pdf"></i> تحميل السيرة الذاتية
            </a>
            <small class="file-error">(تعذر العثور على الملف للعرض المباشر)</small>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($application['status'] != 'accepted'): ?>
            <button class="btn-status btn-accept" onclick="updateStatus(<?php echo $application['id']; ?>, 'accepted')">
              <i class="fas fa-check"></i> قبول
            </button>
            <?php endif; ?>

            <?php if ($application['status'] != 'rejected'): ?>
            <button class="btn-status btn-reject" onclick="updateStatus(<?php echo $application['id']; ?>, 'rejected')">
              <i class="fas fa-times"></i> رفض
            </button>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else : ?>
      <div class="no-applications">
        <i class="fas fa-users-slash"></i>
        <h4>لا يوجد متقدمين لهذه الوظيفة بعد</h4>
        <p>
          لم يتقدم أي شخص لهذه الوظيفة حتى الآن.
        </p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- نافذة عرض السيرة الذاتية -->
  <div id="cvModal" class="cv-modal">
    <div class="cv-modal-content">
      <div class="cv-modal-header">
        <h4>عرض السيرة الذاتية</h4>
      </div>
      <div class="cv-modal-body">
        <canvas id="cv-viewer"></canvas>
      </div>
      <div class="cv-modal-footer">
        <button onclick="closeCVModal()" class="btn btn-secondary">إغلاق</button>
        <a id="downloadCvLink" href="#" download class="btn btn-primary">
          <i class="fas fa-download"></i> تحميل
        </a>
      </div>
    </div>
  </div>

  <!-- نافذة نتائج التحليل -->
  <div class="modal fade" id="analysisResultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">نتائج تحليل السير الذاتية</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="analysisResultsContent">
          <!-- سيتم ملء المحتوى هنا عبر JavaScript -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        </div>
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
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="js/employer-main.js"></script>
  <script>
    // دالة لفتح نافذة السيرة الذاتية
    function openCVModal(cvPath, fileName) {
      const modal = document.getElementById('cvModal');
      const downloadLink = document.getElementById('downloadCvLink');

      // تعيين رابط التحميل
      downloadLink.href = cvPath;
      downloadLink.download = fileName;

      // عرض النافذة
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';

      // تحميل وعرض ملف PDF
      pdfjsLib.GlobalWorkerOptions.workerSrc = '../js/pdf.worker.min.js';

      pdfjsLib.getDocument({
        url: cvPath,
        cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/cmaps/',
        cMapPacked: true
      }).promise.then(function(pdf) {
        return pdf.getPage(1);
      }).then(function(page) {
        const container = document.getElementById('cv-viewer');
        const containerWidth = container.parentElement.clientWidth;

        // حساب المقياس لملاءمة عرض الحاوية
        const viewport = page.getViewport({
          scale: 0.5
        });
        const scale = containerWidth / viewport.width;

        // تطبيق المقياس المحسوب
        const scaledViewport = page.getViewport({
          scale: scale
        });

        // تهيئة Canvas
        const canvas = document.getElementById('cv-viewer');
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
        document.getElementById('cv-viewer').parentElement.innerHTML = `
        <div class="alert alert-danger">
        <p>تعذر عرض السيرة الذاتية. يرجى <a href="${cvPath}" download>تنزيل الملف</a> لعرضه.</p>
        <p>تفاصيل الخطأ: ${error.message}</p>
        </div>
        `;
      });
    }

    // دالة لإغلاق نافذة السيرة الذاتية
    function closeCVModal() {
      document.getElementById('cvModal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    // إغلاق النافذة عند النقر خارجها
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('cvModal');
      if (event.target === modal) {
        closeCVModal();
      }
    });

    function updateStatus(applicationId, status) {
      if (confirm('هل أنت متأكد من تغيير حالة الطلب؟')) {
        $.ajax({
          url: 'update-application-status.php',
          type: 'POST',
          dataType: 'json',
          data: {
            id: applicationId,
            status: status
          },
          success: function(response) {
            if (response.success) {
              alert('تم تحديث الحالة بنجاح');
              location.reload();
            } else {
              alert('حدث خطأ: ' + (response.message || 'خطأ غير معروف'));
            }
          },
          error: function(xhr, status, error) {
            alert('حدث خطأ في الاتصال بالخادم: ' + error);
          }
        });
      }
    }

// دالة لتحليل السير الذاتية (معدلة)
function analyzeCVs() {
    const analyzeBtn = document.querySelector('.cv-analysis');
    analyzeBtn.disabled = true;
    analyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحليل...';

    const cvPaths = [];
    const applications = <?php echo json_encode($applications); ?>;
    
    applications.forEach(app => {
        if (app.file_path) {
            const corrected_path = app.file_path;
            cvPaths.push(corrected_path);
        }
    });

    if (cvPaths.length === 0) {
        alert('لا توجد سير ذاتية لتحليلها');
        analyzeBtn.disabled = false;
        analyzeBtn.innerHTML = 'تحليل السير الذاتية';
        return;
    }

    const jobDesc = `Position: <?php echo htmlspecialchars($job['job_title']); ?>

    Job Description:
    <?php echo !empty($job['job_description']) ? htmlspecialchars($job['job_description']) : 'No detailed description provided'; ?>

    Industry: <?php echo htmlspecialchars($job['industry']); ?>
    Key Requirements:
    - Education: <?php echo !empty($job['education_level']) ? htmlspecialchars($job['education_level']) : 'Not specified'; ?>
    - Experience: <?php echo !empty($job['experience_years']) ? htmlspecialchars($job['experience_years']) : 'Not specified'; ?>
    - Skills: <?php echo !empty($job['skills']) ? htmlspecialchars($job['skills']) : 'Not specified'; ?>`;

    fetch('http://localhost:8000/analyze-resumes/', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            job_description: jobDesc,
            cv_paths: cvPaths,
            job_id: "<?php echo $jobId; ?>",
            employer_id: "<?php echo $employerId; ?>"
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP error ' + response.status);
        return response.json();
    })
    .then(data => {
        // تخزين النتائج مؤقتًا في sessionStorage
        sessionStorage.setItem('cvAnalysisResults', JSON.stringify(data));
        // توجيه إلى صفحة النتائج
        window.location.href = `analysis-results.php?job_id=<?php echo $jobId; ?>`;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('فشل التحليل: ' + error.message);
    })
    .finally(() => {
        analyzeBtn.disabled = false;
        analyzeBtn.innerHTML = 'تحليل السير الذاتية';
    });
}

// دالة عرض النتائج (معدلة)
function displayAnalysisResults(data) {
    let html = `
    <div class="mb-4">
        <h4>الوظيفة: ${data.job_id}</h4>
        <p>تاريخ التحليل: ${new Date(data.analysis_date).toLocaleString()}</p>
        <p>عدد السير الذاتية: ${data.results.length}</p>
    </div>`;

    // أفضل المرشحين
    if (data.top_candidates.length > 0) {
        html += `<div class="alert alert-success">
            <h5><i class="fas fa-trophy"></i> أفضل 3 مرشحين</h5>
        </div>`;
        
        data.top_candidates.forEach((candidate, index) => {
            // معالجة البيانات المفقودة
            const name = candidate.candidate_info?.full_name || 'غير معروف';
            const score = (typeof candidate.matching_score === 'number' && !isNaN(candidate.matching_score)) 
                ? candidate.matching_score.toFixed(1) 
                : 'N/A';

            const email = candidate.candidate_info?.email || 'غير متوفر';
            const skills = candidate.skills_analysis?.technical_skills?.join(', ') || 'غير معروفة';
            
            html += `
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    #${index + 1}: ${name}
                     <span class="float-start">${score}%</span> 
                </div>
                <div class="card-body">
                    <p>البريد: ${email}</p>
                    <p>المهارات: ${skills}</p>
                    <p>الخبرة: ${candidate.work_experience?.length || 0} وظيفة</p>
                    ${candidate.cv_path ? `
                    <a href="${candidate.cv_path}" target="_blank" class="btn btn-sm btn-success">
                        <i class="fas fa-file-pdf"></i> عرض السيرة الذاتية
                    </a>` : ''}
                </div>
            </div>`;
        });
    }

    // جدول النتائج الكاملة
    html += `
    <div class="mt-4">
        <h5>جميع النتائج</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>النسبة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>`;
    
    data.results.forEach(result => {
        const hasError = !!result.error;
        const name = result.candidate_info?.full_name || 'غير معروف';
        const score = (typeof result.matching_score === 'number' && !isNaN(result.matching_score)) 
            ? result.matching_score.toFixed(1) 
            : 'N/A';
        const cvPath = result.cv_path || '#';
        
        html += `
        <tr>
            <td>${name}</td>
            <td>${hasError ? 'N/A' : (score !== 'N/A' ? `${score}%` : 'N/A')}</td>
            <td>${hasError ? '❌ خطأ' : '✅ ناجح'}</td>
            <td>
                ${!hasError && cvPath ? `
                <button class="btn btn-sm btn-info" onclick="window.open('${cvPath}')">
                    <i class="fas fa-eye"></i> عرض
                </button>
                ` : ''}
            </td>
        </tr>`;
    });
    
    html += `</tbody></table></div></div>`;

    document.getElementById('analysisResultsContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('analysisResultsModal')).show();
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