document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const isEditMode = urlParams.has('edit');

  initPage(isEditMode);
  setupNextBtn(isEditMode);
  setupProfilePictureUpload();
});

function isEnglish(text) {
  const englishRegex = /^[a-zA-Z\s.,!?;:'"-]+$/;
  return englishRegex.test(text);
}

function initPage(isEditMode) {
  const savedData = getFormData('step1');
  if (savedData) {
    loadSavedData(savedData);
  }

  if (isEditMode) {
    const prevButton = document.querySelector('.btn-prev');
    if (prevButton) prevButton.style.display = 'none';
  }
}

function setupNextBtn(isEditMode) {
  const nextBtn = document.getElementById('nextBtn');
  if (!nextBtn) return;

  if (isEditMode) {
    nextBtn.innerHTML = ' تم';
    nextBtn.onclick = handleDoneClick;
  } else {
    nextBtn.innerHTML = ' التالي <i class="fas fa-angle-left"></i>';
    nextBtn.onclick = handleNextClick;
  }
}

function handleDoneClick(e) {
  e.preventDefault();
  if (validateForm()) {
    const formData = collectFormData();
    saveFormData('step1', formData);
    window.location.href = 'review-cv.php';
  }
}

function handleNextClick(e) {
  e.preventDefault();
  if (validateForm()) {
    const formData = collectFormData();
    saveFormData('step1', formData);
    window.location.href = 'step2.php';
  }
}

function collectFormData() {
  const profilePicture = document.getElementById('profilePicture');
  const pictureUrl = profilePicture.style.backgroundImage || '';

  return {
    firstName: document.getElementById('firstName').value,
    lastName: document.getElementById('lastName').value,
    jobTitle: document.getElementById('jobTitle').value,
    summary: document.getElementById('summary').value,
    countryLocation: document.getElementById('country-locattion').value,
    countryLocationDisplay: document.getElementById('country-display-locattion').value,
    cityLocation: document.getElementById('city-locattion').value,
    cityLocationDisplay: document.getElementById('city-display-locattion').value,
    email: document.getElementById('email').value,
    phone: document.getElementById('phone').value,
    countryCode: document.getElementById('country-code').value,
    countryCodeDisplay: document.getElementById('country-code-display').value,
    profilePicture: pictureUrl
  };
}

function loadSavedData(savedData) {
  document.getElementById('firstName').value = savedData.firstName || '';
  document.getElementById('lastName').value = savedData.lastName || '';
  document.getElementById('jobTitle').value = savedData.jobTitle || '';
  document.getElementById('email').value = savedData.email || '';
  document.getElementById('phone').value = savedData.phone || '';

  if (savedData.summary) {
    document.getElementById('summary').value = savedData.summary || '';
  }

  if (savedData.countryLocation) {
    document.getElementById('country-locattion').value = savedData.countryLocation || '';
    document.getElementById('country-display-locattion').value = savedData.countryLocationDisplay || '';
  }

  if (savedData.cityLocation) {
    document.getElementById('city-locattion').value = savedData.cityLocation || '';
    document.getElementById('city-display-locattion').value = savedData.cityLocationDisplay || '';
  }

  if (savedData.countryCode) {
    document.getElementById('country-code').value = savedData.countryCode || '';
    document.getElementById('country-code-display').value = savedData.countryCodeDisplay || '';
  }

  if (savedData.profilePicture) {
    const profilePicture = document.getElementById('profilePicture');
    profilePicture.style.backgroundImage = savedData.profilePicture;
    profilePicture.style.backgroundSize = 'cover';
    profilePicture.style.backgroundPosition = 'center';
    profilePicture.style.backgroundRepeat = 'no-repeat';
    profilePicture.innerHTML = '';
    addRemovePictureButton();
  }
}

function validateForm() {
  let isValid = true;
  clearErrorMessages();

  const firstName = document.getElementById('firstName').value.trim();
  if (!firstName) {
    showError('firstNameError', 'الاسم الأول مطلوب');
    isValid = false;
  } else if (!isEnglish(firstName)) {
    showError('firstNameError', 'الرجاء إدخال الاسم الأول بالإنجليزية فقط');
    isValid = false;
  }

  const lastName = document.getElementById('lastName').value.trim();
  if (!lastName) {
    showError('lastNameError', 'الاسم الأخير مطلوب');
    isValid = false;
  } else if (!isEnglish(lastName)) {
    showError('lastNameError', 'الرجاء إدخال الاسم الأخير بالإنجليزية فقط');
    isValid = false;
  }

  const jobTitle = document.getElementById('jobTitle').value.trim();
  if (!jobTitle) {
    showError('jobTitleError', 'المسمى الوظيفي مطلوب');
    isValid = false;
  } else if (!isEnglish(jobTitle)) {
    showError('jobTitleError', 'الرجاء إدخال المسمى الوظيفي بالإنجليزية فقط');
    isValid = false;
  }

  const summary = document.getElementById('summary').value;
  if (!summary) {
    showError('summaryError', 'الملخص مطلوب');
    isValid = false;
  } else if (!isEnglish(summary)) {
    showError('summaryError', 'الرجاء إدخال الملخص بالإنجليزية فقط');
    isValid = false;
  }

  const countryLocation = document.getElementById('country-locattion').value;
  if (!countryLocation) {
    showError('countrylocattionError', 'الدولة مطلوبة');
    isValid = false;
  }

  const cityLocation = document.getElementById('city-locattion').value;
  if (!cityLocation) {
    showError('citylocattionError', 'المدينة مطلوبة');
    isValid = false;
  }

  const email = document.getElementById('email').value.trim();
  if (!email) {
    showError('emailError', 'البريد الإلكتروني مطلوب');
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showError('emailError', 'صيغة البريد الإلكتروني غير صحيحة');
    isValid = false;
  }

  const phone = document.getElementById('phone').value.trim();
  const countryCode = document.getElementById('country-code').value;

  if (!countryCode) {
    showError('countryCodeError', 'رمز الدولة مطلوب');
    isValid = false;
  }

  if (!phone) {
    showError('phoneError', 'رقم الهاتف مطلوب');
    isValid = false;
  } else if (!/^\d+$/.test(phone)) {
    showError('phoneError', 'يجب أن يحتوي رقم الهاتف على أرقام فقط');
    isValid = false;
  } else if (phone.length < 7) {
    showError('phoneError', 'رقم الهاتف قصير جدًا');
    isValid = false;
  }

  return isValid;
}

function clearErrorMessages() {
  const errorElements = document.querySelectorAll('.error-message');
  errorElements.forEach(element => {
    element.textContent = '';
    element.style.display = 'none';
  });
}

function showError(elementId, message) {
  const errorElement = document.getElementById(elementId);
  if (errorElement) {
    errorElement.textContent = message;
    errorElement.style.display = 'block';
  }
}

function setupProfilePictureUpload() {
  const profilePictureInput = document.getElementById('profilePictureInput');
  const profilePicture = document.getElementById('profilePicture');

  if (profilePictureInput && profilePicture) {

    profilePictureInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        if (!file.type.match('image.*')) {
          alert('Please select an image file only (JPEG, PNG, etc.)');
          return;
        }

        if (file.size > 2 * 1024 * 1024) {
          alert('Image size is too large (maximum 2MB)');
          return;
        }

        previewProfilePicture(file, profilePicture);
      }
    });


  }
}

function previewProfilePicture(file, profilePictureElement) {
  const reader = new FileReader();

  reader.onload = function(e) {
    profilePictureElement.style.backgroundImage = `url(${e.target.result})`;
    profilePictureElement.style.backgroundSize = 'cover';
    profilePictureElement.style.backgroundPosition = 'center';
    profilePictureElement.style.backgroundRepeat = 'no-repeat';
    profilePictureElement.innerHTML = '';
    addRemovePictureButton();

    const formData = collectFormData();
    saveFormData('step1',
      formData);
  };

  reader.readAsDataURL(file);
}

function addRemovePictureButton() {
  const removeBtn = document.createElement('button');
  removeBtn.innerHTML = 'حذف الصورة';
  removeBtn.classList.add('remove-picture-btn');
  removeBtn.classList.add('btn');
  removeBtn.classList.add('btn-outline-danger');

  removeBtn.style.position = 'absolute';
  removeBtn.style.bottom = '-40px';
  removeBtn.style.left = '50%';
  removeBtn.style.transform = 'translateX(-50%)';
  removeBtn.style.cursor = 'pointer';
  removeBtn.style.display = 'flex';
  removeBtn.style.alignItems = 'center';
  removeBtn.style.justifyContent = 'center';
  removeBtn.style.zIndex = '10';
  removeBtn.style.whiteSpace = 'nowrap';
  removeBtn.style.border = 'none';

  removeBtn.onclick = function(e) {
    e.stopPropagation();

    const profilePicture = document.getElementById('profilePicture');
    const profilePictureInput = document.getElementById('profilePictureInput');

    profilePicture.style.backgroundImage = '';
    profilePicture.style.backgroundSize = '';
    profilePictureInput.value = '';
    profilePicture.innerHTML = '<div class="profile-picture-placeholder"><i class="fas fa-user"></i></div>';


    const formData = collectFormData();
    formData.profilePicture = '';
    saveFormData('step1',
      formData);

    removeBtn.remove();
  };

  const container = document.querySelector('.profile-picture-container');
  container.style.position = 'relative';
  container.appendChild(removeBtn);
}

// دالة العودة الى الموقع الصحيح
let  isRecreateMode = false;
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  isRecreateMode = urlParams.has('recreate');
});

console.log(isRecreateMode);
function navigateTo() {
  if (isRecreateMode) {
    window.location.href = '../profile_settings.php';
  } else {
    window.location.href = '../upload-create-profile.php';
  }
}