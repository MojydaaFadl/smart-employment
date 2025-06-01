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
    <title>التوظيف الذكي - المؤهلات التعليمية</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
    <link href="css/cv-style.css" rel="stylesheet">
    <style>
        /* أنماط التعليم */

        .section-divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 1.5rem 0;
        }

        .education-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .education-item h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .education-item p {
            margin: 5px 0;
            color: #555;
        }

        .education-item .period {
            color: #777;
            font-size: 0.9rem;
        }

        #no-educations-message p {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px dashed #ddd;
            width: 100%;
            margin: 20px auto;
        }

        .education-item .description {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ddd;
        }

        /* نافذة إضافة التعليم */
        .add-education-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow-y: auto;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1002;
        }


        /* أنماط نموذج إدخال التعليم الجديد */

        .action-link {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .action-link:hover {
            background-color: #f0f7ff;
        }

        .action-link.cancel {
            color: #666;
        }

        .form-title {
            color: var(--primary-color);
            margin-bottom: 5px;
            text-align: center;
            font-size: 1.4rem;
        }

        .form-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: center;
        }



        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }

        .checkbox-label {
            margin-right: 10px;
            cursor: pointer;
        }

        .job-description {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
        }

        .section-title {
            color: #444;
            margin: 20px 0 15px;
            font-size: 1.1rem;
        }

        .form-input.select-style {
            height: 55px;
        }

        .btn-prev {
            margin-right: auto;
        }

        /* أنماط شريط الأزرار الثابت */
        .form-actions {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: #fff;
            padding: 15px;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
        }

        /* تعديلات على نافذة إضافة التعليم لاستيعاب الشريط الثابت */
        .add-education-window {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .add-education-content {
            flex: 1;
            overflow-y: auto;
            padding: 0 15px;
        }

        /* رسالة الخطأ */
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }

        .show-error {
            display: block;
        }

        /* رسالة التحذير */
        .warning-message {
            background-color: #fff8e1;
            color: #ff6d00;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #ffe0b2;
            animation: fadeIn 0.3s ease;
        }

        .warning-message i {
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
    <div>
      <span class="open-btn" onclick="openSidebar()">&#9776;</span>
      <span class="navbar-brand">
        التوظيف الذكي
      </span>
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
    <a href="../for-employers/employer-dashboard.php">لأصحاب العمل</a>
    <?php endif; ?>


    <a href="#">English</a>

    <?php if ($logged_in): ?>
    <div class="sidebar-buttons">
      <span style="color: white; padding: 10px;">مرحباً <?php echo $_SESSION['first_name']; ?></span>
      <a href="../logout.php" class="btn btn-outline-danger">تسجيل خروج</a>
    </div>
    <?php else : ?>
    <div class="sidebar-buttons">
      <a href="../register-form.php" class="btn btn-primary">تسجيل</a>
      <a href="../login-form.php" class="btn btn-outline-primary">دخول</a>
    </div>
    <?php endif; ?>
  </div>

    <!-- مؤشر الخطوات -->
    <div class="steps-container">
        <div class="steps-header">الخطوة 3 من 5</div>
        <ul class="steps-list">
            <li class="step-item completed">
                <span class="step-number">1</span>
                <span class="step-label">المعلومات الشخصية</span>
            </li>
            <li class="step-item completed">
                <span class="step-number">2</span>
                <span class="step-label">الخبرة العملية</span>
            </li>
            <li class="step-item active">
                <span class="step-number">3</span>
                <span class="step-label">المؤهلات التعليمية</span>
            </li>
            <li class="step-item">
                <span class="step-number">4</span>
                <span class="step-label">المهارات</span>
            </li>
            <li class="step-item">
                <span class="step-number">5</span>
                <span class="step-label">الإنجازات واللغات</span>
            </li>
        </ul>
    </div>

    <!-- قسم المؤهلات التعليمية -->
    <div class="form-container">
        <div class="education-container">
            <h3 class="text-center fw-bold">المؤهلات التعليمية</h3>
            <p class="text-center text-muted">أضف شهاداتك التعليمية ودرجاتك العلمية</p>
            <div class="section-divider"></div>

            <div id="savedEducations">
                <!-- سيتم عرض المؤهلات المحفوظة هنا -->
                <div id="no-educations-message">
                    <p class="text-center">لم تتم إضافة أي مؤهلات بعد. انقر على "إضافة مؤهل" للبدء.</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <button id="addEducationBtn" class="btn btn-primary p-2">
                    <i class="fas fa-plus"></i> إضافة مؤهل تعليمي
                </button>
            </div>
        </div>
    </div>

    <!-- أزرار التنقل -->
    <div class="navigation-buttons">
        <a href="step2.php" class="nav-btn btn-prev">
            <i class="fa-solid fa-angle-right"></i>
            السابق
        </a>

        <button id="nextBtn" class="nav-btn btn-next">
            التالي
            <i class="fas fa-angle-left"></i>
        </button>
    </div>

    <div id="educationError" class="warning-message" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        يجب إضافة مؤهل تعليمي واحد على الأقل قبل المتابعة
    </div>

    <!-- نافذة إضافة المؤهل التعليمي -->
    <div id="addEducationModal" class="add-education-modal">
        <div class="add-education-window">
            <!-- شريط الأزرار -->
            <div class="form-actions">
                <a id="cancelAddEducationBtn" class="action-link cancel fw-bold">إلغاء</a>
                <a id="saveEducationBtn" class="action-link save fw-bold">حفظ</a>
            </div>

            <!-- محتوى النافذة -->
            <div class="form-container" style="padding-top: 0;">
                <p class="form-subtitle px-3"
                    style="margin-bottom: 25px; margin-top: 25px; color: #333;">
                    المتقدمون الحاصلون على مؤهلات تعليمية مناسبة لديهم فرصة أكبر في الحصول على الوظيفة. أضف خبرتك التعليمية هنا:
                </p>

                <h4 class="section-title" style="margin-top: 5px;">المستوى التعليمي</h4>

                <div class="form-group">
                 <select id="degree" class="form-input select-style" placeholder="sgfdgsfg">
                        <option value="">Select Education Level</option>
                        <option value="high_school">High School or equivalent</option>
                        <option value="diploma">Diploma</option>
                        <option value="bachelor">Bachelor's Degree</option>
                        <option value="higher_diploma">Higher Diploma</option>
                        <option value="master">Master's Degree</option>
                        <option value="phd">PhD</option>
                    </select>
                    <div class="error-message" id="degreeError">الرجاء اختيار المستوى التعليمي</div>
                </div>

                <h4 class="section-title">الجامعة أو المؤسسة التعليمية</h4>

                <div class="form-group">
                    <input type="text" id="institution" class="form-input" placeholder="مثال: جامعة هارفارد">
                    <div class="error-message" id="institutionError">الرجاء إدخال المؤسسة التعليمية</div>
                </div>

                <div class="form-group">
                    <input type="text" id="fieldOfStudy" class="form-input"
                        placeholder="مجال الدراسة مثل: علوم الحاسوب">
                    <div class="error-message" id="fieldOfStudyError">الرجاء إدخال مجال الدراسة</div>
                </div>

                <div class="form-group">
                    <label for="country-display-edu" class="form-label">الدولة</label>
                    <div class="select-wrapper">
                        <input type="text" class="custom-select" id="country-display-edu" placeholder="اختر الدولة"
                            readonly onclick="openSelect('country-edu')">
                        <input type="hidden" id="country-edu">
                    </div>
                    <div class="error-message" id="countryEduError">الرجاء اختيار الدولة</div>
                </div>

                <div class="form-group">
                    <label for="city-display-edu" class="form-label">المدينة</label>
                    <div class="select-wrapper">
                        <input type="text" class="custom-select" id="city-display-edu" placeholder="اختر المدينة" readonly
                            onclick="openSelect('city-edu')">
                        <input type="hidden" id="city-edu">
                    </div>
                    <div class="error-message" id="cityEduError">الرجاء اختيار المدينة</div>
                </div>

                <h4 class="section-title">سنة التخرج</h4>

                <div class="form-group">
                    <div class="select-wrapper">
                        <input type="text" class="custom-select" id="graduation-year-display"
                            placeholder="اختر سنة التخرج" readonly onclick="openSelect('graduation-year')">
                        <input type="hidden" id="graduation-year">
                    </div>
                    <div class="error-message" id="graduationYearError">الرجاء اختيار سنة التخرج</div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="currently-studying" name="currently-studying">
                    <label for="currently-studying" class="checkbox-label">أنا ما زلت أدرس</label>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة الاختيار العامة -->
    <div class="fullpage-select-overlay" id="selectOverlay"></div>
    <div class="fullpage-select-container" id="selectContainer">
        <div class="select-header">
            <h4 id="selectTitle">اختر</h4>
            <span class="close-select" onclick="closeSelect()">&times;</span>
        </div>
        <div class="select-search">
            <input type="text" id="selectSearch" placeholder="بحث..." oninput="filterOptions()">
        </div>
        <div class="select-options" id="selectOptions"></div>
    </div>

    <script src="js/cv-main.js"></script>
    <script src="js/storage.js"></script>
    <script src="js/step3.js"></script>
</body>
</html>