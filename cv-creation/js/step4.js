// متغيرات لتخزين المهارات وحالة التعديل
let skills = [];
let editingSkillIndex = null;

// عناصر DOM
const addSkillBtn = document.getElementById('addSkillBtn');
const nextBtn = document.getElementById('nextBtn');
const addSkillModal = document.getElementById('addSkillModal');
const cancelAddSkill = document.getElementById('cancelAddSkill');
const saveSkill = document.getElementById('saveSkill');
const skillsContent = document.getElementById('skillsContent');
const noSkills = document.getElementById('noSkills');
const skillsError = document.getElementById('skillsError');
const skillName = document.getElementById('skillName');
const skillLevels = document.getElementById('skillLevels');
const selectedSkillLevel = document.getElementById('selectedSkillLevel');
const skillError = document.getElementById('skillError');
const skillModalTitle = document.querySelector('.form-title');
const skillModalSubtitle = document.querySelector('.form-subtitle');

// دالة للتحقق من أن النص إنجليزي
function isEnglish(text) {
  const englishRegex = /^[a-zA-Z\s.,!?;:'"-]+$/;
  return englishRegex.test(text);
}

// تهيئة الصفحة
function initSkillsPage() {
  // استرجاع البيانات المحفوظة
  const savedData = getFormData('step4');
  if (savedData) {
    skills = savedData.skills || [];
    updateSkillsList();
  }

  // التحقق من وضع التحرير
  const urlParams = new URLSearchParams(window.location.search);
  const isEditMode = urlParams.has('edit');

  if (isEditMode) {
    setupEditMode();
  }

  // إعداد أحداث الصفحة
  setupEventListeners();
  setupSkillLevels();
}

// إعداد وضع التحرير
function setupEditMode() {
  // 1. تعديل زر التالي
  if (nextBtn) {
    nextBtn.innerHTML = 'تم  ';
    nextBtn.classList.add('btn-done');
    nextBtn.style.margin = 'auto';

    nextBtn.onclick = function(e) {
      handleDoneClick(e);
    };
  }

  // 2. إخفاء زر السابق
  const prevBtn = document.querySelector('.btn-prev');
  if (prevBtn) {
    prevBtn.style.display = 'none';
  }
}

// إعداد مستمعي الأحداث
function setupEventListeners() {
  // النقر على إضافة مهارة
  addSkillBtn?.addEventListener('click', function() {
    resetSkillForm();
    addSkillModal.style.display = 'block';
  });

  // النقر على التالي (إذا لم يكن في وضع التحرير)
  if (nextBtn && !window.location.search.includes('edit=true')) {
    nextBtn.onclick = function(e) {
      handleNextClick(e);
    };
  }

  // النقر على إلغاء
  cancelAddSkill?.addEventListener('click', function() {
    addSkillModal.style.display = 'none';
  });

  // النقر على حفظ
  saveSkill?.addEventListener('click', function() {
    saveSkillHandler();
  });
}

// معالجة النقر على زر التالي (الوضع العادي)
function handleNextClick(e) {
  if (skills.length < 3) {
    e.preventDefault();
    showSkillsError();
  } else {
    saveAndNavigate('step5.php');
  }
}

// معالجة النقر على زر تم (وضع التحرير)
function handleDoneClick(e) {
  e.preventDefault();
  if (skills.length < 3) {
    showSkillsError();
  } else {
    saveAndNavigate('review-cv.php');
  }
}

// عرض رسالة خطأ المهارات
function showSkillsError() {
  skillsError.style.display = 'flex';
  setTimeout(() => {
    skillsError.style.display = 'none';
  }, 5000);
  skillsError.scrollIntoView({
    behavior: 'smooth', block: 'center'
  });
}

// حفظ البيانات والانتقال
function saveAndNavigate(targetPage) {
  saveFormData('step4', {
    skills
  });
  window.location.href = targetPage;
}

// إعداد أحداث مستويات المهارة
function setupSkillLevels() {
  const levelOptions = skillLevels?.querySelectorAll('.level-option');

  levelOptions?.forEach(option => {
    option.addEventListener('click', function() {
      levelOptions.forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      selectedSkillLevel.value = this.textContent;
    });
  });
}

// التحقق من صحة نموذج المهارة
function validateSkillForm() {
  let isValid = true;

  if (!skillName?.value.trim()) {
    skillError.textContent = 'الرجاء إدخال اسم المهارة';
    skillError.style.display = 'block';
    isValid = false;
  } else if (!isEnglish(skillName.value.trim())) {
    skillError.textContent = 'الرجاء إدخال اسم المهارة بالإنجليزية    ';
    skillError.style.display = 'block';
    isValid = false;
  } else if (isSkillDuplicate(skillName.value.trim())) {
    skillError.textContent = 'هذه المهارة مضافة بالفعل';
    skillError.style.display = 'block';
    isValid = false;
  } else {
    skillError.style.display = 'none';
  }

  if (!selectedSkillLevel?.value) {
    isValid = false;
  }

  return isValid;
}

// التحقق من وجود مهارة مكررة
function isSkillDuplicate(skill) {
  return skills.some((s, index) =>
    s.name.toLowerCase() === skill.toLowerCase() &&
    index !== editingSkillIndex
  );
}

// معالجة حفظ المهارة
function saveSkillHandler() {
  if (validateSkillForm()) {
    if (editingSkillIndex !== null) {
      updateSkill(editingSkillIndex);
    } else {
      saveSkillToArray();
    }
    addSkillModal.style.display = 'none';
    updateSkillsList();
    saveFormData('step4', {
      skills
    });
  }
}

// حفظ المهارة في المصفوفة
function saveSkillToArray() {
  const newSkill = {
    name: skillName.value.trim(),
    level: selectedSkillLevel.value
  };
  skills.push(newSkill);
}

// تحديث المهارة الموجودة
function updateSkill(index) {
  skills[index] = {
    name: skillName.value.trim(),
    level: selectedSkillLevel.value
  };
  editingSkillIndex = null;
}

// تحميل المهارة للتعديل
function loadSkillForEdit(index) {
  const skill = skills[index];
  skillName.value = skill.name;
  selectedSkillLevel.value = skill.level;

  // تمييز المستوى المحدد
  const levelOptions = skillLevels.querySelectorAll('.level-option');
  levelOptions.forEach(opt => {
    opt.classList.remove('selected');
    if (opt.textContent === skill.level) {
      opt.classList.add('selected');
    }
  });

  editingSkillIndex = index;
  skillModalTitle.textContent = 'تعديل المهارة';
  skillModalSubtitle.textContent = 'قم بتحديث معلومات المهارة';
  addSkillModal.style.display = 'block';
}

// تحديث قائمة المهارات
function updateSkillsList() {
  if (skills.length === 0) {
    noSkills.style.display = 'block';
    skillsContent.innerHTML = '';
  } else {
    noSkills.style.display = 'none';

    let html = '<div class="skills-tags">';
    skills.forEach((skill, index) => {
      html += `
      <div class="skill-tag">
      <div class="skill-info">
      <span class="skill-name">${skill.name}</span>
      <span class="level">${skill.level}</span>
      </div>
      <div class="skill-actions">
      <button class="btn btn-sm btn-primary" onclick="editSkill(${index})">
      تعديل
      </button>
      <button class="btn btn-sm btn-danger" onclick="removeSkill(${index})">
      حذف
      </button>
      </div>
      </div>
      `;
    });
    html += '</div>';
    skillsContent.innerHTML = html;
  }
}

// تعديل المهارة
function editSkill(index) {
  editingSkillIndex = index;
  loadSkillForEdit(index);
}

// حذف المهارة
function removeSkill(index) {
  if (confirm('هل أنت متأكد من رغبتك في حذف هذه المهارة؟')) {
    skills.splice(index, 1);
    updateSkillsList();
    saveFormData('step4', {
      skills
    });
  }
}

// إعادة تعيين النموذج
function resetSkillForm() {
  skillName.value = '';
  selectedSkillLevel.value = '';
  skillError.style.display = 'none';
  editingSkillIndex = null;

  const levelOptions = skillLevels?.querySelectorAll('.level-option');
  levelOptions?.forEach(opt => opt.classList.remove('selected'));

  skillModalTitle.textContent = 'إضافة مهارة';
  skillModalSubtitle.textContent = 'مجموعة شاملة ودقيقة من المهارات يمكن أن تزيد بشكل كبير من قيمة ملفك الشخصي لأصحاب العمل';
}

// جعل الدوال متاحة عالميًا للاستدعاء من HTML
window.editSkill = editSkill;
window.removeSkill = removeSkill;

// تهيئة الصفحة عند تحميل DOM
document.addEventListener('DOMContentLoaded', initSkillsPage);