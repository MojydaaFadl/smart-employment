<?php
require_once 'db_connection.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['employer_id'])) {
  header('Location: employer-login.php');
  exit();
}

// التحقق من وجود معرف الوظيفة
if (!isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) {
  header('Location: my-job-posts.php');
  exit();
}

$jobId = $_GET['job_id'];
$employerId = $_SESSION['employer_id'];

// جلب معلومات الوظيفة
$stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $employerId]);
$job = $stmt->fetch();

if (!$job) {
  header('Location: my-job-posts.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التوظيف الذكي - نتائج تحليل السير الذاتية</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/fontawesome/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="css/employer-style.css">
  <style>
    .candidate-card {
      transition: all 0.3s;
    }
    .candidate-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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

  <!-- المحتوى الرئيسي -->
  <div class="main-content">
    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>نتائج تحليل السير الذاتية</h2>
        <a href="job-applications.php?job_id=<?php echo $jobId; ?>" class="btn btn-outline-primary">
          <i class="fas fa-arrow-right me-1"></i> العودة إلى المتقدمين
        </a>
      </div>

      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h4>معلومات الوظيفة</h4>
        </div>
        <div class="card-body">
          <h5><?php echo htmlspecialchars($job['job_title']); ?></h5>
          <p class="mb-1"><strong>المجال:</strong> <?php echo htmlspecialchars($job['industry']); ?></p>
          <p class="mb-1"><strong>نوع الوظيفة:</strong> <?php echo htmlspecialchars($job['employment_type']); ?></p>
          <p><?php echo htmlspecialchars($job['job_description']); ?></p>
        </div>
      </div>

      <div id="analysisResultsContainer">
        <!-- سيتم ملء هذا القسم عبر JavaScript -->
      </div>
    </div>
  </div>

  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const results = JSON.parse(sessionStorage.getItem('cvAnalysisResults'));
      
      if (!results || !results.results) {
        document.getElementById('analysisResultsContainer').innerHTML = `
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> لا توجد نتائج تحليل متاحة.
          </div>
        `;
        return;
      }

      renderAnalysisResults(results);
    });

    function renderAnalysisResults(data) {
      let html = `
        <div class="mb-4">
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> تم تحليل ${data.results.length} سيرة ذاتية
          </div>
        </div>`;

      // أفضل المرشحين
      if (data.results.length > 0) {
        const topCandidates = data.results
          .filter(r => !r.error)
          .sort((a, b) => (b.matching_score || 0) - (a.matching_score || 0))
          .slice(0, 3);

        if (topCandidates.length > 0) {
          html += `
            <div class="card mb-4">
              <div class="card-header bg-success text-white">
                <h4><i class="fas fa-trophy"></i> أفضل المرشحين</h4>
              </div>
              <div class="card-body">
                <div class="row">`;

          topCandidates.forEach((candidate, index) => {
            const name = candidate.candidate_info?.full_name || 'غير معروف';
            const score = candidate.matching_score?.toFixed(1) || 'N/A';
            const email = candidate.candidate_info?.email || 'غير متوفر';
            const skills = candidate.skills_analysis?.technical_skills?.join(', ') || 'غير معروفة';

            html += `
              <div class="col-md-4 mb-3">
                <div class="card h-100 candidate-card">
                  <div class="card-header bg-light">
                    <h5>#${index + 1}: ${name}</h5>
                  </div>
                  <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="badge bg-primary">${score}%</span>
                      <small class="text-muted">${email}</small>
                    </div>
                    <p class="mb-2"><strong>المهارات:</strong> ${skills}</p>
                    ${candidate.cv_path ? `
                    <a href="${candidate.cv_path}" target="_blank" class="btn btn-sm btn-primary w-100 mt-2">
                      <i class="fas fa-file-pdf"></i> عرض السيرة الذاتية
                    </a>` : ''}
                  </div>
                </div>
              </div>`;
          });

          html += `</div></div></div>`;
        }
      }

      // جميع النتائج
      html += `
        <div class="card">
          <div class="card-header">
            <h4>جميع النتائج</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>النسبة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                  </tr>
                </thead>
                <tbody>`;

      data.results.forEach((result, index) => {
        const hasError = !!result.error;
        const name = result.candidate_info?.full_name || 'غير معروف';
        const score = result.matching_score?.toFixed(1) || 'N/A';
        const cvPath = result.cv_path || '#';

        html += `
          <tr>
            <td>${index + 1}</td>
            <td>${name}</td>
            <td>${hasError ? 'N/A' : score}%</td>
            <td>${hasError ? '<span class="badge bg-danger">خطأ</span>' : '<span class="badge bg-success">ناجح</span>'}</td>
            <td>
              ${!hasError && cvPath ? `
              <a href="${cvPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i> عرض
              </a>` : ''}
            </td>
          </tr>`;
      });

      html += `</tbody></table></div></div></div>`;

      document.getElementById('analysisResultsContainer').innerHTML = html;
    }
  </script>
</body>
</html>