// js/storage.js

// دالة لحفظ البيانات
function saveFormData(stepName, formData) {
    // 1. نجلب كل البيانات المحفوظة مسبقاً
    const allData = JSON.parse(localStorage.getItem('cv_data')) || {};
    
    // 2. نحدث البيانات للخطوة الحالية
    allData[stepName] = formData;
    
    // 3. نحفظ الكل في الذاكرة
    localStorage.setItem('cv_data', JSON.stringify(allData));
}

// دالة لاسترجاع البيانات
function getFormData(stepName) {
    // 1. نجلب كل البيانات المحفوظة
    const allData = JSON.parse(localStorage.getItem('cv_data')) || {};
    
    // 2. نعيد فقط بيانات الخطوة المطلوبة
    return allData[stepName] || null;
}

// دالة لمسح البيانات
function clearFormData() {
    localStorage.removeItem('cv_data');
}

// جعل الدالة متاحة للاستيراد/الاستخدام
// export { saveFormData, getFormData, clearFormData };