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
    <title>التوظيف الذكي - المهارات</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
    <link href="css/cv-style.css" rel="stylesheet">
    <style>
        /* أنماط واجهة المهارات */

        .section-divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 1.5rem 0;
        }
        .skills-list-container {
            width: 100%;
            height: auto;
            background-color: white;
            /* padding: 20px; */
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .skills-header {
            text-align: right;
            margin-bottom: 1rem;
            width: 100%;
        }

        .skills-header h2 {
            color: var(--text-color);
            margin-bottom: 5px;
            font-size: 1.5rem;
        }

        .skills-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .skills-content {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            width: 100%;
            justify-content: flex-end;
        }

        .skill-tag {
            background-color: #e3f2fd;
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 150px;
            flex-grow: 1;
        }

        .skill-tag .level {
            margin-right: 5px;
            font-size: 0.8rem;
            color: #666;
        }

        .no-skills {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px dashed #ddd;
            width: 100%;
            margin: 20px auto;
        }

        /* أنماط إضافة المهارات */
/* أنماط شريط الأزرار الثابت */
.form-actions {
    position: sticky;
    top: 0;
    z-index: 100;
    background-color: #fff;
    padding: 15px;
    border-bottom: 1px solid #eee;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
}

/* تعديلات على نافذة إضافة المهارات */
.add-skill-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow-y: auto;
    background-color: #fff;
    z-index: 1002;
}



.add-skill-content {
    flex: 1;
    overflow-y: auto;
    padding: 0 15px 20px;
}

/* رسائل الخطأ والتحذير */
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

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
    display: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
        .action-link {
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .cancel {
            color: #666;
        }

        .save {
            color: var(--primary-color);
        }

        .form-title {
            color: var(--text-color);
            text-align: right;
            margin: 20px 15px 10px;
            font-size: 1.3rem;
        }

        .form-subtitle {
            color: #666;
            text-align: right;
            margin: 0 15px 20px;
            font-size: 0.9rem;
        }

        .form-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0 15px 20px;
            width: 80%;
            margin: 0 auto;
        }

        .skill-levels {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .level-option {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .level-option:hover {
            background-color: #f5f5f5;
        }

        .level-option.selected {
            border-color: var(--primary-color);
            background-color: #e3f2fd;
        }

        @media (max-width: 768px) {
            .form-content {
                width: 90%;
            }
            
            .skill-levels {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .form-content {
                width: 95%;
            }
            
            .skill-levels {
                grid-template-columns: 1fr;
            }
            
            .skill-tag {
                min-width: 100%;
            }
        }

        .btn-prev {
            margin-right: auto;
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
        <div class="steps-header">الخطوة 4 من 5</div>
        <ul class="steps-list">
            <li class="step-item completed">
                <span class="step-number">1</span>
                <span class="step-label">المعلومات الشخصية</span>
            </li>
            <li class="step-item completed">
                <span class="step-number">2</span>
                <span class="step-label">الخبرة العملية</span>
            </li>
            <li class="step-item completed">
                <span class="step-number">3</span>
                <span class="step-label">المؤهلات التعليمية</span>
            </li>
            <li class="step-item active">
                <span class="step-number">4</span>
                <span class="step-label">المهارات</span>
            </li>
            <li class="step-item">
                <span class="step-number">5</span>
                <span class="step-label">الإنجازات واللغات</span>
            </li>
        </ul>
    </div>

    <!-- قسم المهارات -->
    <div class="form-container">
        <div class="skills-list-container">
            <h3 class="text-center">المهارات</h3>
            <p class="text-muted text-center">أضف مهاراتك الرئيسية لمساعدة أصحاب العمل على معرفة ما تجيده</p>
            <div class="section-divider"></div>

            <div class="skills-content" id="skillsContent">
                <!-- سيتم عرض المهارات هنا -->
                <div class="no-skills" id="noSkills">
                    <p>لم تتم إضافة أي مهارات بعد</p>
                </div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary p-2" id="addSkillBtn">
                    <i class="fas fa-plus"></i> إضافة مهارة
                </button>
            </div>
        </div>
    </div>

    <!-- أزرار التنقل -->
    <div class="navigation-buttons">
        <a href="step3.php" class="nav-btn btn-prev">
            <i class="fa-solid fa-angle-right"></i>
            السابق
        </a>
        
        <button id="nextBtn" class="nav-btn btn-next">
            التالي
            <i class="fas fa-angle-left"></i>
        </button>
    </div>

    <!-- رسالة الخطأ -->
    <div id="skillsError" class="warning-message" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        يجب إضافة 3 مهارات على الأقل قبل المتابعة
    </div>

    <!-- نافذة إضافة المهارات -->
    <div id="addSkillModal" class="add-skill-modal">
        <div class="form-actions">
            <a class="action-link cancel" id="cancelAddSkill">إلغاء</a>
            <a class="action-link save" id="saveSkill">حفظ</a>
        </div>

        <h2 class="form-title">إضافة مهارة</h2>
        <p class="form-subtitle">مجموعة شاملة ودقيقة من المهارات يمكن أن تزيد بشكل كبير من قيمة ملفك الشخصي لأصحاب العمل</p>

        <div class="form-content">
            <!-- قسم المهارة -->
            <div class="form-group">
                <label for="skillName" class="form-label">المهارة</label>
                <input type="text" id="skillName" class="form-input" placeholder="أدخل اسم المهارة">
                <div class="error-message" id="skillError">هذه المهارة مضافة بالفعل</div>
            </div>

            <!-- قسم مستوى المهارة -->
            <div class="form-group">
                <label class="form-label">اختر المستوى</label>
                <div class="skill-levels" id="skillLevels">
                    <div class="level-option">Beginner</div>
                    <div class="level-option">Intermediate</div>
                    <div class="level-option">Advanced</div>
                    <div class="level-option">Expert</div>
                </div>
                <input type="hidden" id="selectedSkillLevel">
            </div>
        </div>
    </div>

    <script src="js/cv-main.js"></script>
    <script src="js/storage.js"></script>
    <script src="js/step4.js"></script>
</body>
</html>