// متغيرات لتخزين الخبرات
let experiences = [];
let selectedExperienceType = null;
let editingExperienceIndex = null;

// عناصر DOM
const experienceForm = document.getElementById('experienceForm');
const experienceOptionsContainer = document.querySelector('.options-container');
const experienceOptions = document.querySelectorAll('input[name="experience"]');
const experienceError = document.getElementById('experienceError');
const nextBtn = document.getElementById('nextBtn');
const experienceModal = document.getElementById('experienceModal');
const backBtn = document.getElementById('backBtn');
const addExperienceBtn = document.getElementById('addExperienceBtn');
const nextExperienceBtn = document.getElementById('nextExperienceBtn');
const addExperienceModal = document.getElementById('addExperienceModal');
const cancelAddExperienceBtn = document.getElementById('cancelAddExperienceBtn');
const saveExperienceBtn = document.getElementById('saveExperienceBtn');
const savedExperiences = document.getElementById('savedExperiences');
const noExperiencesMessage = document.getElementById('no-experiences-message');
const minExperiencesWarning = document.getElementById('minExperiencesWarning');

// عناصر نموذج إضافة الخبرة
const jobTitle = document.getElementById('jobTitle');
const companyName = document.getElementById('companyName');
const industryDisplay = document.getElementById('industry-display');
const industry = document.getElementById('industry');
const countryDisplay = document.getElementById('country-display');
const country = document.getElementById('country');
const cityDisplay = document.getElementById('city-display');
const city = document.getElementById('city');
const startMonthDisplay = document.getElementById('start-month-display');
const startMonth = document.getElementById('start-month');
const startYearDisplay = document.getElementById('start-year-display');
const startYear = document.getElementById('start-year');
const endMonthDisplay = document.getElementById('end-month-display');
const endMonth = document.getElementById('end-month');
const endYearDisplay = document.getElementById('end-year-display');
const endYear = document.getElementById('end-year');
const stillWorking = document.getElementById('still-working');
const endDateSection = document.getElementById('end-date-section');
const jobDescription = document.getElementById('jobDescription');

// رسائل الخطأ
const jobTitleError = document.getElementById('jobTitleError');
const companyNameError = document.getElementById('companyNameError');
const industryError = document.getElementById('industryError');
const countryError = document.getElementById('countryError');
const cityError = document.getElementById('cityError');
const startDateError = document.getElementById('startDateError');
const endDateError = document.getElementById('endDateError');

// دالة للتحقق من أن النص إنجليزي
function isEnglish(text) {
  const englishRegex = /^[a-zA-Z\s.,!?;:'"-]+$/;
  return englishRegex.test(text);
}

// تهيئة الصفحة
function initExperiencePage() {
    // تحميل البيانات المحفوظة
    const savedData = getFormData('step2');
    if (savedData) {
        experiences = savedData.experiences || [];
        selectedExperienceType = savedData.selectedExperienceType || null;
        
        if (selectedExperienceType && experiences.length === 0) {
            document.querySelector(`input[name="experience"][value="${selectedExperienceType}"]`).checked = true;
        }
    }

    // التحقق من وضع التحرير
    const urlParams = new URLSearchParams(window.location.search);
    const isEditMode = urlParams.has('edit');

    // تحديث واجهة المستخدم بناءً على الخبرات
    updateUIBasedOnExperiences();

    // إعداد حقول الاختيار
    setupSelectFields();
    
    // إخفاء قسم تاريخ الانتهاء إذا كان لا يزال يعمل
    stillWorking.addEventListener('change', function() {
        endDateSection.style.display = this.checked ? 'none' : 'block';
    });
    
    // معالجة زر التالي بناءً على الوضع
    if (isEditMode) {
        nextBtn.innerHTML = 'تم <i class="fas fa-check"></i>';
        nextBtn.style.margin = 'auto';
        nextBtn.onclick = function() {
            saveFormData('step2', { 
                experiences: experiences, 
                selectedExperienceType: selectedExperienceType 
            });
            window.location.href = 'review-cv.php';
        };
        
        // إخفاء زر السابق في وضع التحرير
        document.querySelector('.btn-prev').style.display = 'none';
    } else {
        nextBtn.addEventListener('click', handleNextClick);
    }
    
    // سلوك زر الرجوع
    backBtn.addEventListener('click', () => {
        if (experiences.length > 0) {
            window.location.href = 'step1.html';
        } else {
            experienceModal.style.display = 'none';
        }
    });
    
    // زر إضافة خبرة
    addExperienceBtn.addEventListener('click', () => openAddExperience());
    
    // سلوك زر التالي في نافذة الخبرة
    if (isEditMode) {
        nextExperienceBtn.innerHTML = 'تم <i class="fas fa-check"></i>';
        nextExperienceBtn.onclick = function() {
            saveFormData('step2', { 
                experiences: experiences, 
                selectedExperienceType: selectedExperienceType 
            });
            window.location.href = 'review-cv.html';
        };
    } else {
        nextExperienceBtn.addEventListener('click', handleNextExperience);
    }
    
    // الأزرار الأخرى
    cancelAddExperienceBtn.addEventListener('click', () => {
        addExperienceModal.style.display = 'none';
    });
    saveExperienceBtn.addEventListener('click', saveExperienceHandler);

    // تحديث قائمة الخبرات عند التحميل
    updateExperiencesList();
}

// تحديث واجهة المستخدم بناءً على الخبرات
function updateUIBasedOnExperiences() {
    if (experiences.length > 0) {
        experienceOptionsContainer.style.display = 'none';
        selectedExperienceType = 'experienced';
        experienceModal.style.display = 'block';
        experienceError.classList.remove('show-error');
    } else {
        experienceOptionsContainer.style.display = 'block';
    }
}

// إعداد حقول الاختيار
function setupSelectFields() {
    industryDisplay.addEventListener('click', () => openSelect('industry'));
    countryDisplay.addEventListener('click', () => openSelect('country'));
    cityDisplay.addEventListener('click', () => openSelect('city'));
    startMonthDisplay.addEventListener('click', () => openSelect('start-month'));
    startYearDisplay.addEventListener('click', () => openSelect('start-year'));
    endMonthDisplay.addEventListener('click', () => openSelect('end-month'));
    endYearDisplay.addEventListener('click', () => openSelect('end-year'));
}

// معالجة النقر على زر التالي في الصفحة الرئيسية
function handleNextClick(e) {
    e.preventDefault();
    
    if (experiences.length > 0) {
        handleNextExperience();
        return;
    }
    
    const selectedOption = document.querySelector('input[name="experience"]:checked');
    
    if (!selectedOption) {
        experienceError.classList.add('show-error');
        return;
    }
    
    experienceError.classList.remove('show-error');
    selectedExperienceType = selectedOption.value;
    
    saveFormData('step2', {
        experiences,
        selectedExperienceType
    });
    
    if (selectedExperienceType === 'experienced') {
        experienceModal.style.display = 'block';
        updateExperiencesList();
    } else {
        window.location.href = 'step3.php';
    }
}

// فتح نافذة إضافة خبرة (مع تحرير اختياري)
function openAddExperience(index = null) {
    resetExperienceForm();
    
    const urlParams = new URLSearchParams(window.location.search);
    const isEditMode = urlParams.has('edit');
    
    if (index !== null) {
        editingExperienceIndex = index;
        const exp = experiences[index];
        
        jobTitle.value = exp.jobTitle;
        companyName.value = exp.companyName;
        industryDisplay.value = exp.industryDisplay;
        industry.value = exp.industry;
        countryDisplay.value = exp.countryDisplay;
        country.value = exp.country;
        cityDisplay.value = exp.cityDisplay;
        city.value = exp.city;
        startMonthDisplay.value = exp.startMonthDisplay;
        startMonth.value = exp.startMonth;
        startYearDisplay.value = exp.startYearDisplay;
        startYear.value = exp.startYear;
        
        if (exp.stillWorking) {
            stillWorking.checked = true;
            endDateSection.style.display = 'none';
        } else {
            endMonthDisplay.value = exp.endMonthDisplay;
            endMonth.value = exp.endMonth;
            endYearDisplay.value = exp.endYearDisplay;
            endYear.value = exp.endYear;
        }
        
        jobDescription.value = exp.description || '';
        
        document.querySelector('.form-title').textContent = 'تعديل الخبرة العملية';
    } else {
        editingExperienceIndex = null;
        document.querySelector('.form-title').textContent = 'إضافة خبرة عمل';
    }
    
    // تطبيق تعديلات وضع التحرير
    if (isEditMode) {
        backBtn.style.display = 'none';
        
        const modalNextBtn = document.querySelector('.add-experience-window .experience-btn');
        if (modalNextBtn) {
            modalNextBtn.innerHTML = 'تم <i class="fas fa-check"></i>';
            modalNextBtn.onclick = function() {
                saveFormData('step2', { 
                    experiences: experiences, 
                    selectedExperienceType: selectedExperienceType 
                });
                window.location.href = 'review-cv.html';
            };
        }
    }
    
    addExperienceModal.style.display = 'block';
}

// معالجة النقر على زر التالي في نافذة الخبرة
function handleNextExperience() {
    if (selectedExperienceType === 'experienced' && experiences.length < 3) {
        minExperiencesWarning.textContent = 'الرجاء إضافة 3 خبرات على الأقل قبل المتابعة';
        minExperiencesWarning.style.display = 'block';
        minExperiencesWarning.style.animation = 'shake 0.5s';
        setTimeout(() => {
            minExperiencesWarning.style.animation = '';
        }, 500);
        return;
    }

    const finalExperienceType = experiences.length > 0 ? 'experienced' : selectedExperienceType;
    
    saveFormData('step2', {
        experiences: experiences,
        selectedExperienceType: finalExperienceType
    });
    
    window.location.href = 'step3.php';
}

// معالجة حفظ الخبرة
function saveExperienceHandler() {
    if (validateExperienceForm()) {
        saveExperience();
        addExperienceModal.style.display = 'none';
        updateExperiencesList();
        updateUIBasedOnExperiences();
        
        saveFormData('step2', {
            experiences,
            selectedExperienceType: 'experienced'
        });
    }
}

// التحقق من صحة نموذج الخبرة
function validateExperienceForm() {
    let isValid = true;
    
    if (!jobTitle.value.trim()) {
        jobTitleError.style.display = 'block';
        isValid = false;
    } else if (!isEnglish(jobTitle.value.trim())) {
        jobTitleError.textContent = 'الرجاء إدخال المسمى الوظيفي بالإنجليزية  ';
        jobTitleError.style.display = 'block';
        isValid = false;
    } else {
        jobTitleError.style.display = 'none';
    }
    
    if (!companyName.value.trim()) {
        companyNameError.style.display = 'block';
        isValid = false;
    } else if (!isEnglish(companyName.value.trim())) {
        companyNameError.textContent = 'الرجاء إدخال اسم الشركة بالإنجليزية  ';
        companyNameError.style.display = 'block';
        isValid = false;
    } else {
        companyNameError.style.display = 'none';
    }
    
    if (!industry.value) {
        industryError.style.display = 'block';
        isValid = false;
    } else {
        industryError.style.display = 'none';
    }
    
    if (!country.value) {
        countryError.style.display = 'block';
        isValid = false;
    } else {
        countryError.style.display = 'none';
    }
    
    if (!city.value) {
        cityError.style.display = 'block';
        isValid = false;
    } else {
        cityError.style.display = 'none';
    }
    
    if (!startMonth.value || !startYear.value) {
        startDateError.style.display = 'block';
        isValid = false;
    } else {
        startDateError.style.display = 'none';
    }
    
    if (!stillWorking.checked && (!endMonth.value || !endYear.value)) {
        endDateError.style.display = 'block';
        isValid = false;
    } else {
        endDateError.style.display = 'none';
    }

    if (jobDescription.value.trim() && !isEnglish(jobDescription.value.trim())) {
      jobDescriptionError.style.display ='block';
    }else{
      jobDescriptionError.style.display ='none';
    }
    
    return isValid;
}

// حفظ الخبرة في المصفوفة
function saveExperience() {
    const newExperience = {
        jobTitle: jobTitle.value.trim(),
        companyName: companyName.value.trim(),
        industry: industry.value,
        industryDisplay: industryDisplay.value,
        country: country.value,
        countryDisplay: countryDisplay.value,
        city: city.value,
        cityDisplay: cityDisplay.value,
        startMonth: startMonth.value,
        startMonthDisplay: startMonthDisplay.value,
        startYear: startYear.value,
        startYearDisplay: startYearDisplay.value,
        endMonth: stillWorking.checked ? null : endMonth.value,
        endMonthDisplay: stillWorking.checked ? null : endMonthDisplay.value,
        endYear: stillWorking.checked ? null : endYear.value,
        endYearDisplay: stillWorking.checked ? null : endYearDisplay.value,
        stillWorking: stillWorking.checked,
        description: jobDescription.value.trim()
    };
    
    if (editingExperienceIndex !== null) {
        experiences[editingExperienceIndex] = newExperience;
    } else {
        experiences.push(newExperience);
    }
}

// تحديث قائمة الخبرات المعروضة
function updateExperiencesList() {
    if (experiences.length === 0) {
        noExperiencesMessage.style.display = 'block';
        savedExperiences.innerHTML = '';
        minExperiencesWarning.style.display = 'none';
    } else {
        noExperiencesMessage.style.display = 'none';
        
        let html = '';
        experiences.forEach((exp, index) => {
            const endDateText = exp.stillWorking ? 'حاليًا' : `${exp.endMonthDisplay} ${exp.endYearDisplay}`;
            
            html += `
                <div class="experience-item">
                    <h4>${exp.jobTitle}</h4>
                    <p>${exp.companyName} - ${exp.industryDisplay}</p>
                    <p class="period">${exp.startMonthDisplay} ${exp.startYearDisplay} - ${endDateText}</p>
                    <p>${exp.cityDisplay}, ${exp.countryDisplay}</p>
                    ${exp.description ? `<div class="description">${exp.description}</div>` : ''}
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm btn-primary" onclick="editExperience(${index})">تعديل</button>
                        <button class="btn btn-sm btn-danger" onclick="removeExperience(${index})">حذف</button>
                    </div>
                </div>
            `;
        });
        
        savedExperiences.innerHTML = html;
        
        if (experiences.length >= 3) {
            minExperiencesWarning.style.display = 'none';
        }
    }
}

// تعديل الخبرة
function editExperience(index) {
    openAddExperience(index);
}

// حذف الخبرة
function removeExperience(index) {
    if (confirm('هل أنت متأكد من رغبتك في حذف هذه الخبرة؟')) {
        experiences.splice(index, 1);
        updateExperiencesList();
        
        if (experiences.length === 0) {
            updateUIBasedOnExperiences();
            selectedExperienceType = null;
        }
        
        saveFormData('step2', {
            experiences,
            selectedExperienceType
        });
    }
}

// إعادة تعيين نموذج الخبرة
function resetExperienceForm() {
    jobTitle.value = '';
    companyName.value = '';
    industryDisplay.value = '';
    industry.value = '';
    countryDisplay.value = '';
    country.value = '';
    cityDisplay.value = '';
    city.value = '';
    startMonthDisplay.value = '';
    startMonth.value = '';
    startYearDisplay.value = '';
    startYear.value = '';
    endMonthDisplay.value = '';
    endMonth.value = '';
    endYearDisplay.value = '';
    endYear.value = '';
    stillWorking.checked = false;
    endDateSection.style.display = 'block';
    jobDescription.value = '';
    
    document.querySelectorAll('.error-message').forEach(el => {
        el.style.display = 'none';
    });
}

// تهيئة الصفحة
document.addEventListener('DOMContentLoaded', initExperiencePage);

// جعل الدوال متاحة عالميًا للاستدعاء من HTML
window.editExperience = editExperience;
window.removeExperience = removeExperience;