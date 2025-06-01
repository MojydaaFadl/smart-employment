// متغيرات لتخزين المؤهلات التعليمية
let educations = [];

// عناصر DOM
const addEducationBtn = document.getElementById('addEducationBtn');
const nextBtn = document.getElementById('nextBtn');
const addEducationModal = document.getElementById('addEducationModal');
const cancelAddEducationBtn = document.getElementById('cancelAddEducationBtn');
const saveEducationBtn = document.getElementById('saveEducationBtn');
const savedEducations = document.getElementById('savedEducations');
const noEducationsMessage = document.getElementById('no-educations-message');

// عناصر نموذج إضافة المؤهل التعليمي
const degree = document.getElementById('degree');
const institution = document.getElementById('institution');
const fieldOfStudy = document.getElementById('fieldOfStudy');
const countryDisplayEdu = document.getElementById('country-display-edu');
const countryEdu = document.getElementById('country-edu');
const cityDisplayEdu = document.getElementById('city-display-edu');
const cityEdu = document.getElementById('city-edu');
const graduationYearDisplay = document.getElementById('graduation-year-display');
const graduationYear = document.getElementById('graduation-year');
const currentlyStudying = document.getElementById('currently-studying');

// رسائل الخطأ
const degreeError = document.getElementById('degreeError');
const institutionError = document.getElementById('institutionError');
const fieldOfStudyError = document.getElementById('fieldOfStudyError');
const countryEduError = document.getElementById('countryEduError');
const cityEduError = document.getElementById('cityEduError');
const graduationYearError = document.getElementById('graduationYearError');

// متغير لتتبع المؤهل الذي يتم تعديله
let editingIndex = null;

// دالة للتحقق من أن النص إنجليزي
function isEnglish(text) {
  const englishRegex = /^[a-zA-Z\s.,!?;:'"-]+$/;
  return englishRegex.test(text);
}

// تهيئة الصفحة
function initEducationPage() {
  // تحميل البيانات المحفوظة
  const savedData = getFormData('step3');
  if (savedData) {
    educations = savedData.educations || [];
    updateEducationsList();
  }

  // أحداث الصفحة
  setupEventListeners();
  setupSelectFields();
  toggleGraduationYear();

  // التحقق من وضع التحرير عند التحميل
  checkEditMode();
}

// إعداد مستمعي الأحداث
function setupEventListeners() {
  // النقر على إضافة مؤهل
  addEducationBtn.addEventListener('click', function() {
    resetEducationForm();
    addEducationModal.style.display = 'block';
  });

  // النقر على التالي
  nextBtn.addEventListener('click', function(e) {
    handleNextClick(e);
  });

  // النقر على إلغاء
  cancelAddEducationBtn.addEventListener('click', function() {
    addEducationModal.style.display = 'none';
  });

  // النقر على حفظ
  saveEducationBtn.addEventListener('click', function() {
    saveEducationHandler();
  });

  // تغيير حالة "ما زلت أدرس"
  currentlyStudying.addEventListener('change', toggleGraduationYear);
}

// التحقق من وضع التحرير
function checkEditMode() {
  const urlParams = new URLSearchParams(window.location.search);
  const isEditMode = urlParams.has('edit');

  if (isEditMode) {
    // استبدال زر "التالي" بـ "تم"
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) {
      nextBtn.innerHTML = 'تم  ';
      nextBtn.style.margin = 'auto';

      nextBtn.onclick = function() {
        saveFormData('step3', {
          educations: educations
        });
        window.location.href = 'review-cv.php';
      };
    }

    // إخفاء زر "السابق"
    const prevBtn = document.querySelector('.btn-prev');
    if (prevBtn) {
      prevBtn.style.display = 'none';
    }
  }
}

// معالجة النقر على زر التالي
function handleNextClick(e) {
  const educationError = document.getElementById('educationError');

  if (educations.length === 0) {
    e.preventDefault();
    educationError.style.display = 'flex';

    setTimeout(() => {
      educationError.style.display = 'none';
    }, 5000);

    educationError.scrollIntoView({
      behavior: 'smooth', block: 'center'
    });
  } else {
    // حفظ البيانات قبل المتابعة
    saveFormData('step3', {
      educations
    });
    window.location.href = 'step4.php';
  }
}

// إعداد حقول الاختيار
function setupSelectFields() {
  countryDisplayEdu.addEventListener('click', function() {
    openSelect('country-edu');
  });

  cityDisplayEdu.addEventListener('click', function() {
    openSelect('city-edu');
  });

  graduationYearDisplay.addEventListener('click', function() {
    openSelect('graduation-year');
  });
}

// التحقق من صحة نموذج المؤهل التعليمي
function validateEducationForm() {
  let isValid = true;

  if (!degree.value) {
    degreeError.style.display = 'block';
    isValid = false;
  } else {
    degreeError.style.display = 'none';
  }

  if (!institution.value.trim()) {
    institutionError.style.display = 'block';
    isValid = false;
  } else if (!isEnglish(institution.value.trim())) {
    institutionError.textContent = 'الرجاء إدخال اسم المؤسسة بالإنجليزية   ';
    institutionError.style.display = 'block';
    isValid = false;
  } else {
    institutionError.style.display = 'none';
  }

  if (!fieldOfStudy.value.trim()) {
    fieldOfStudyError.style.display = 'block';
    isValid = false;
  } else if (!isEnglish(fieldOfStudy.value.trim())) {
    fieldOfStudyError.textContent = 'الرجاء إدخال مجال الدراسة بالإنجليزية   ';
    fieldOfStudyError.style.display = 'block';
    isValid = false;
  } else {
    fieldOfStudyError.style.display = 'none';
  }

  if (!countryEdu.value) {
    countryEduError.style.display = 'block';
    isValid = false;
  } else {
    countryEduError.style.display = 'none';
  }

  if (!cityEdu.value) {
    cityEduError.style.display = 'block';
    isValid = false;
  } else {
    cityEduError.style.display = 'none';
  }

  if (!currentlyStudying.checked && !graduationYear.value) {
    graduationYearError.style.display = 'block';
    isValid = false;
  } else {
    graduationYearError.style.display = 'none';
  }

  return isValid;
}

// معالجة حفظ المؤهل التعليمي
function saveEducationHandler() {
  if (validateEducationForm()) {
    if (editingIndex !== null) {
      updateEducation(editingIndex);
      editingIndex = null;
    } else {
      saveEducation();
    }
    addEducationModal.style.display = 'none';
    updateEducationsList();
    saveFormData('step3', {
      educations
    });
  }
}

// حفظ المؤهل التعليمي
function saveEducation() {
  const newEducation = {
    degree: degree.options[degree.selectedIndex].text,
    degreeValue: degree.value,
    institution: institution.value.trim(),
    fieldOfStudy: fieldOfStudy.value.trim(),
    country: countryDisplayEdu.value,
    countryValue: countryEdu.value,
    city: cityDisplayEdu.value,
    cityValue: cityEdu.value,
    graduationYear: currentlyStudying.checked ? 'حاليًا': graduationYearDisplay.value,
    graduationYearValue: currentlyStudying.checked ? null: graduationYear.value,
    currentlyStudying: currentlyStudying.checked
  };

  educations.push(newEducation);
}

// تعديل المؤهل التعليمي
function updateEducation(index) {
  educations[index] = {
    degree: degree.options[degree.selectedIndex].text,
    degreeValue: degree.value,
    institution: institution.value.trim(),
    fieldOfStudy: fieldOfStudy.value.trim(),
    country: countryDisplayEdu.value,
    countryValue: countryEdu.value,
    city: cityDisplayEdu.value,
    cityValue: cityEdu.value,
    graduationYear: currentlyStudying.checked ? 'حاليًا': graduationYearDisplay.value,
    graduationYearValue: currentlyStudying.checked ? null: graduationYear.value,
    currentlyStudying: currentlyStudying.checked
  };
}

// تحميل المؤهل التعليمي للتعديل
function loadEducationForEdit(index) {
  const edu = educations[index];
  degree.value = edu.degreeValue;
  institution.value = edu.institution;
  fieldOfStudy.value = edu.fieldOfStudy;
  countryDisplayEdu.value = edu.country;
  countryEdu.value = edu.countryValue;
  cityDisplayEdu.value = edu.city;
  cityEdu.value = edu.cityValue;

  if (edu.currentlyStudying) {
    currentlyStudying.checked = true;
    graduationYearDisplay.value = '';
    graduationYear.value = '';
  } else {
    currentlyStudying.checked = false;
    graduationYearDisplay.value = edu.graduationYear;
    graduationYear.value = edu.graduationYearValue;
  }

  toggleGraduationYear();
}

// تحديث قائمة المؤهلات التعليمية
function updateEducationsList() {
  if (educations.length === 0) {
    noEducationsMessage.style.display = 'block';
    savedEducations.innerHTML = '';
  } else {
    noEducationsMessage.style.display = 'none';

    let html = '';
    educations.forEach((edu, index) => {
      html += `
      <div class="education-item">
      <h4>${edu.degree} في ${edu.fieldOfStudy}</h4>
      <p>${edu.institution}</p>
      <p>${edu.city}, ${edu.country}</p>
      <p class="period">سنة التخرج: ${edu.graduationYear}</p>
      <div class="d-flex">
      <button class="btn btn-sm btn-primary" onclick="editEducation(${index})">تعديل</button>
      <button class="btn btn-sm btn-danger" onclick="removeEducation(${index})">حذف</button>
      </div>
      </div>
      `;
    });

    savedEducations.innerHTML = html;
  }
}

// بدء التعديل على المؤهل
function editEducation(index) {
  editingIndex = index;
  loadEducationForEdit(index);
  addEducationModal.style.display = 'block';
}

// إعادة تعيين نموذج المؤهل التعليمي
function resetEducationForm() {
  degree.value = '';
  institution.value = '';
  fieldOfStudy.value = '';
  countryDisplayEdu.value = '';
  countryEdu.value = '';
  cityDisplayEdu.value = '';
  cityEdu.value = '';
  graduationYearDisplay.value = '';
  graduationYear.value = '';
  currentlyStudying.checked = false;
  editingIndex = null;

  document.querySelector('.form-group:has(#graduation-year-display)').style.display = 'block';

  document.querySelectorAll('.error-message').forEach(el => {
    el.style.display = 'none';
  });
}

// حذف المؤهل التعليمي
function removeEducation(index) {
  if (confirm('هل أنت متأكد من رغبتك في حذف هذا المؤهل التعليمي؟')) {
    educations.splice(index, 1);
    updateEducationsList();

    // حفظ البيانات بعد الحذف
    saveFormData('step3', {
      educations
    });
  }
}

// تبديل عرض حقل سنة التخرج
function toggleGraduationYear() {
  const graduationYearField = document.querySelector('.form-group:has(#graduation-year-display)');

  if (currentlyStudying.checked) {
    graduationYearField.style.display = 'none';
    graduationYear.value = '';
    graduationYearDisplay.value = '';
  } else {
    graduationYearField.style.display = 'block';
  }
}

// جعل الدوال متاحة عالميًا للاستدعاء من HTML
window.editEducation = editEducation;
window.removeEducation = removeEducation;

// تهيئة الصفحة عند تحميل DOM
document.addEventListener('DOMContentLoaded', initEducationPage);