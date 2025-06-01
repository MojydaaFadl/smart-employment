// عرض جميع البيانات المحفوظة
function displayAllData() {
    displayPersonalInfo();
    displayExperiences();
    displayEducations();
    displaySkills();
    displayAchievementsAndLanguages();
}

// عرض المعلومات الشخصية
function displayPersonalInfo() {
    const personalData = getFormData('step1');
    if (!personalData) return;
    
    // عرض الصورة الشخصية إذا وجدت
    const profilePicContainer = document.getElementById('profile-pic-review');
    if (personalData.profilePicture) {
        // تنظيف عنوان URL من أي إضافات غير مرغوبة
        let imageUrl = personalData.profilePicture;
        
        // إزالة 'url(' و')' إذا كانت موجودة
        if (imageUrl.startsWith('url("') && imageUrl.endsWith('")')) {
            imageUrl = imageUrl.substring(5, imageUrl.length - 2);
        } else if (imageUrl.startsWith('url(') && imageUrl.endsWith(')')) {
            imageUrl = imageUrl.substring(4, imageUrl.length - 1);
        }
        
        // إزالة أي علامات تنصيص متبقية
        imageUrl = imageUrl.replace(/^["']|["']$/g, '');
        
        profilePicContainer.innerHTML = `<img src="${imageUrl}" class="profile-pic" alt="الصورة الشخصية">`;
    } else {
        profilePicContainer.innerHTML = `<div style="width:100%;height:100%;background:#eee;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-user" style="font-size:2rem;color:#999;"></i>
        </div>`;
    }
    
    // عرض البيانات الشخصية
    const container = document.getElementById('personal-data-container');
    let html = `
        <div class="data-item">
            <div class="data-label">الاسم الكامل</div>
            <div class="data-value">${personalData.firstName} ${personalData.lastName}</div>
        </div>
        <div class="data-item">
            <div class="data-label">المسمى الوظيفي</div>
            <div class="data-value">${personalData.firstName} ${personalData.jobTitle}</div>
        </div>
        <div class="data-item">
            <div class="data-label">ملخص</div>
            <div class="data-value">${personalData.summary}</div>
        </div>
        <div class="data-item">
            <div class="data-label">الموقع</div>
            <div class="data-value">
            ${personalData.countryLocationDisplay || personalData.countryLocation}
            -
            ${personalData.cityLocationDisplay || personalData.cityLocation}
            </div>
        </div>
        <div class="data-item">
            <div class="data-label">البريد الإلكتروني</div>
            <div class="data-value">${personalData.email}</div>
        </div>
        <div class="data-item">
            <div class="data-label">رقم الهاتف</div>
            <div class="data-value"> ${personalData.countryCode} ${personalData.phone}</div>
        </div>
    `;
    
    container.innerHTML = html;
}

// عرض الخبرات العملية
function displayExperiences() {
    const step2Data = getFormData('step2');
    const container = document.getElementById('experiences-container');
    
    if (!step2Data || !step2Data.experiences || step2Data.experiences.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">لا توجد خبرات مضافة</p>';
        return;
    }
    
    let html = '';
    step2Data.experiences.forEach((exp, index) => {
        const endDateText = exp.stillWorking ? 'حتى الآن' : `${exp.endMonthDisplay} ${exp.endYearDisplay}`;
        
        html += `
            <div class="data-item">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div class="data-label">${exp.jobTitle}</div>
                        <div class="data-value">${exp.companyName} - ${exp.industryDisplay || exp.industry}</div>
                        <div class="data-value">${exp.cityDisplay || exp.city}, ${exp.countryDisplay || exp.country}</div>
                        <div class="data-value">${exp.startMonthDisplay} ${exp.startYearDisplay} - ${endDateText}</div>
                    </div>
                </div>
                ${exp.description ? `<div class="data-value" style="margin-top:10px;">${exp.description}</div>` : ''}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// عرض المؤهلات التعليمية
function displayEducations() {
    const step3Data = getFormData('step3');
    const container = document.getElementById('educations-container');
    
    if (!step3Data || !step3Data.educations || step3Data.educations.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">لا توجد مؤهلات مضافة</p>';
        return;
    }
    
    let html = '';
    step3Data.educations.forEach((edu, index) => {
        html += `
            <div class="data-item">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div class="data-label">${edu.degree}</div>
                        <div class="data-value">${edu.institution}</div>
                        <div class="data-value">${edu.fieldOfStudy}</div>
                        <div class="data-value">${edu.city}, ${edu.country}</div>
                        <div class="data-value">سنة التخرج: ${edu.graduationYear}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// عرض المهارات
function displaySkills() {
    const step4Data = getFormData('step4');
    const container = document.getElementById('skills-container');
    
    if (!step4Data || !step4Data.skills || step4Data.skills.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">لا توجد مهارات مضافة</p>';
        return;
    }
    
    let html = '';
    step4Data.skills.forEach((skill, index) => {
        html += `
            <div class="list-item">
                ${skill.name} (${skill.level})
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// عرض الإنجازات واللغات والاهتمامات
function displayAchievementsAndLanguages() {
    const step5Data = getFormData('step5');
    if (!step5Data) {
        document.getElementById('achievements-container').innerHTML = '<p class="text-muted">لا توجد بيانات مضافة</p>';
        document.getElementById('languages-container').innerHTML = '<p class="text-muted">لا توجد بيانات مضافة</p>';
        document.getElementById('interests-container').innerHTML = '<p class="text-muted">لا توجد بيانات مضافة</p>';
        return;
    }

    // عرض الإنجازات
    const achievementsContainer = document.getElementById('achievements-container');
    if (step5Data.achievements && step5Data.achievements.length > 0) {
        let html = '';
        step5Data.achievements.forEach((achievement, index) => {
            html += `
                <div class="list-item">
                    <div>
                        <strong class="text-primary">${achievement.title}:</strong>
                    </div>
                    <div>
                        <p>${achievement.description}</p>
                    </div>                                      
                </div>
            `;
        });
        achievementsContainer.innerHTML = html;
    } else {
        achievementsContainer.innerHTML = '<p class="text-muted">لا توجد إنجازات مضافة</p>';
    }

    // عرض اللغات
    const languagesContainer = document.getElementById('languages-container');
    if (step5Data.languages && step5Data.languages.length > 0) {
        let html = '';
        step5Data.languages.forEach((language, index) => {
            html += `
                <div class="list-item">
                    ${language.language} (${language.level})                   
                </div>
            `;
        });
        languagesContainer.innerHTML = html;
    } else {
        languagesContainer.innerHTML = '<p class="text-muted">لا توجد لغات مضافة</p>';
    }

    // عرض الاهتمامات
    const interestsContainer = document.getElementById('interests-container');
    if (step5Data.interests && step5Data.interests.length > 0) {
        let html = '';
        step5Data.interests.forEach((interest, index) => {
            html += `
                <div class="list-item">
                    ${interest}
                </div>
            `;
        });
        interestsContainer.innerHTML = html;
    } else {
        interestsContainer.innerHTML = '<p class="text-muted">لا توجد اهتمامات مضافة</p>';
    }
}

// الانتقال إلى قسم للتعديل
function editSection(step) {
    window.location.href = `${step}.php?edit=true`;
}

// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    displayAllData();
});

// إرسال السيرة الذاتية
function submitCV() {
    // جمع جميع البيانات من الخطوات
    const cvData = {
        personalInfo: getFormData('step1'),
        experiences: getFormData('step2')?.experiences || [],
        educations: getFormData('step3')?.educations || [],
        skills: getFormData('step4')?.skills || [],
        achievements: getFormData('step5')?.achievements || [],
        languages: getFormData('step5')?.languages || [],
        interests: getFormData('step5')?.interests || []
    };

    // تخزين البيانات مؤقتًا للوصول إليها في save_cv.php
    localStorage.setItem('cvDataForPDF', JSON.stringify(cvData));

    // الانتقال إلى صفحة إنشاء PDF
    window.location.href = 'save_cv.php';
}