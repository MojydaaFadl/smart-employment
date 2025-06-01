// Page data
let pageData = {
  achievements: [],
  languages: [],
  interests: []
};

// Tracking variables
let currentlyEditing = {
  type: null,
  // 'achievement', 'language', 'interest'
  index: -1
};

// Check if text is in English
function isEnglish(text) {
  // Regular expression to match Arabic characters
  const arabicRegex = /[\u0600-\u06FF]/;
  return !arabicRegex.test(text);
}

// Initialize the page
function initPage() {
  loadSavedData();
  setupModals();
  setupEventListeners();
  updateUI();
}

// Load saved data
function loadSavedData() {
  const savedData = getFormData('step5');
  if (savedData) {
    pageData.achievements = Array.isArray(savedData.achievements) ? savedData.achievements: [];
    pageData.languages = Array.isArray(savedData.languages) ? savedData.languages: [];
    pageData.interests = Array.isArray(savedData.interests) ? savedData.interests: [];
  }
}

// Set up modals
function setupModals() {
  // Close modals when clicking on ×
  document.querySelectorAll('.close-modal').forEach(btn => {
    btn.onclick = function() {
      const modalId = this.getAttribute('data-modal');
      document.getElementById(modalId).style.display = 'none';
    }
  });

  // Close modals when clicking outside
  window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
      event.target.style.display = 'none';
    }
  }
}

// Open a modal
function openModal(modalId, type, index = -1) {
  currentlyEditing = {
    type,
    index
  };
  const modal = document.getElementById(modalId);

  // Fill data if editing
  if (index >= 0) {
    const item = pageData[type + 's'][index];
    if (type === 'achievement') {
      document.getElementById('modal-achievement-title').value = item.title;
      document.getElementById('modal-achievement-description').value = item.description;
      document.getElementById('save-achievement-btn').textContent = 'تحديث الإنجاز';
    } else if (type === 'language') {
      document.getElementById('modal-language-name').value = item.language;
      document.querySelector(`input[name="modal-language-level"][value="${item.level}"]`).checked = true;
      document.getElementById('save-language-btn').textContent = 'تحديث اللغة';
    } else if (type === 'interest') {
      document.getElementById('modal-interest-input').value = item;
      document.getElementById('save-interest-btn').textContent = 'تحديث الاهتمام';
    }
  } else {
    // Reset fields if adding new
    if (type === 'achievement') {
      document.getElementById('modal-achievement-title').value = '';
      document.getElementById('modal-achievement-description').value = '';
      document.getElementById('save-achievement-btn').textContent = 'حفظ الإنجاز';
    } else if (type === 'language') {
      document.getElementById('modal-language-name').value = '';
      document.querySelectorAll('input[name="modal-language-level"]').forEach(r => r.checked = false);
      document.getElementById('save-language-btn').textContent = 'حفظ اللغة';
    } else if (type === 'interest') {
      document.getElementById('modal-interest-input').value = '';
      document.getElementById('save-interest-btn').textContent = 'حفظ الاهتمام';
    }
  }

  modal.style.display = 'block';
}

// Set up event listeners
function setupEventListeners() {
  // Modal open buttons
  document.getElementById('add-achievement-btn').addEventListener('click', () => openModal('achievementModal', 'achievement'));
  document.getElementById('add-language-btn').addEventListener('click', () => openModal('languageModal', 'language'));
  document.getElementById('add-interest-btn').addEventListener('click', () => openModal('interestModal', 'interest'));

  // Save buttons
  document.getElementById('save-achievement-btn').addEventListener('click', handleAchievementSubmit);
  document.getElementById('save-language-btn').addEventListener('click', handleLanguageSubmit);
  document.getElementById('save-interest-btn').addEventListener('click', handleInterestSubmit);

  // Submit button
  document.getElementById('submit-btn').addEventListener('click', submitForm);
}

// Handle achievement add/edit
function handleAchievementSubmit() {
  const title = document.getElementById('modal-achievement-title').value.trim();
  const description = document.getElementById('modal-achievement-description').value.trim();

  // Hide all error messages first
  document.getElementById('achievement-title-error').style.display = 'none';
  document.getElementById('achievement-desc-error').style.display = 'none';
  document.getElementById('achievement-title-english-error').style.display = 'none';
  document.getElementById('achievement-desc-english-error').style.display = 'none';

  let isValid = true;

  // Validate title
  if (!title) {
    document.getElementById('achievement-title-error').style.display = 'block';
    isValid = false;
  } else if (!isEnglish(title)) {
    document.getElementById('achievement-title-english-error').style.display = 'block';
    isValid = false;
  }

  // Validate description
  if (!description) {
    document.getElementById('achievement-desc-error').style.display = 'block';
    isValid = false;
  } else if (!isEnglish(description)) {
    document.getElementById('achievement-desc-english-error').style.display = 'block';
    isValid = false;
  }

  // If there are errors, stop here
  if (!isValid) return;

  // If everything is correct, proceed
  const newItem = {
    title,
    description
  };
  updateData('achievement', newItem);
  document.getElementById('achievementModal').style.display = 'none';
}

// Handle language add/edit
function handleLanguageSubmit() {
  const language = document.getElementById('modal-language-name').value.trim();
  const level = document.querySelector('input[name="modal-language-level"]:checked')?.value;

  // Hide all error messages first
  document.getElementById('language-name-error').style.display = 'none';
  document.getElementById('language-level-error').style.display = 'none';
  document.getElementById('language-name-english-error').style.display = 'none';

  let isValid = true;

  // Validate language name
  if (!language) {
    document.getElementById('language-name-error').style.display = 'block';
    isValid = false;
  } else if (!isEnglish(language)) {
    document.getElementById('language-name-english-error').style.display = 'block';
    isValid = false;
  }

  // Validate level
  if (!level) {
    document.getElementById('language-level-error').style.display = 'block';
    isValid = false;
  }

  // If there are errors, stop here
  if (!isValid) return;

  // If everything is correct, proceed
  const newItem = {
    language,
    level
  };
  updateData('language', newItem);
  document.getElementById('languageModal').style.display = 'none';
}

// Handle interest add/edit
function handleInterestSubmit() {
  const interest = document.getElementById('modal-interest-input').value.trim();

  // Hide error message first
  document.getElementById('interest-error').style.display = 'none';
  document.getElementById('interest-english-error').style.display = 'none';

  // Validate interest
  if (!interest) {
    document.getElementById('interest-error').style.display = 'block';
    return;
  } else if (!isEnglish(interest)) {
    document.getElementById('interest-english-error').style.display = 'block';
    return;
  }

  // If everything is correct, proceed
  updateData('interest', interest);
  document.getElementById('interestModal').style.display = 'none';
}

// Update data
function updateData(type, newItem) {
  if (currentlyEditing.index >= 0) {
    pageData[type + 's'][currentlyEditing.index] = newItem;
  } else {
    // Prevent duplicates for interests
    if (type === 'interest' && pageData.interests.includes(newItem)) {
      alert('هذا الاهتمام مضاف مسبقاً');
      return;
    }
    pageData[type + 's'].push(newItem);
  }
  saveAndUpdate();
}

// Remove item
function removeItem(type, index) {
  if (confirm('هل أنت متأكد من الحذف؟')) {
    pageData[type + 's'].splice(index, 1);
    saveAndUpdate();
  }
}

// Save data and update UI
function saveAndUpdate() {
  saveFormData('step5', pageData);
  updateUI();
}

// Update UI
function updateUI() {
  // Achievements section
  const achievementsContainer = document.getElementById('achievements-container');
  achievementsContainer.innerHTML = '';
  if (pageData.achievements.length === 0) {
    achievementsContainer.innerHTML = '<div class="empty-placeholder">لم تتم إضافة أي إنجازات بعد</div>';
  } else {
    pageData.achievements.forEach((item, index) => {
      const div = document.createElement('div');
      div.className = 'achievement-item';
      div.innerHTML = `
      <div class="item-actions">
      <button class="edit-btn" onclick="openModal('achievementModal', 'achievement', ${index})">
      <i class="fas fa-edit"></i>
      </button>
      <button class="remove-btn" onclick="removeItem('achievement', ${index})">
      <i class="fas fa-times"></i>
      </button>
      </div>
      <div class="achievement-title">${item.title}</div>
      <div class="achievement-description">${item.description}</div>
      `;
      achievementsContainer.appendChild(div);
    });
  }

  // Languages section
  const languagesContainer = document.getElementById('languages-container');
  languagesContainer.innerHTML = '';
  if (pageData.languages.length === 0) {
    languagesContainer.innerHTML = '<div class="empty-placeholder">لم تتم إضافة أي لغات بعد</div>';
  } else {
    pageData.languages.forEach((item, index) => {
      const div = document.createElement('div');
      div.className = 'language-item';
      div.innerHTML = `
      <div class="item-actions">
      <button class="edit-btn" onclick="openModal('languageModal', 'language', ${index})">
      <i class="fas fa-edit"></i>
      </button>
      <button class="remove-btn" onclick="removeItem('language', ${index})">
      <i class="fas fa-times"></i>
      </button>
      </div>
      <div class="language-name">${item.language}</div>
      <div class="language-level">المستوى: ${item.level}</div>
      `;
      languagesContainer.appendChild(div);
    });
  }

  // Interests section
  const interestsContainer = document.getElementById('interests-container');
  interestsContainer.innerHTML = '';
  if (pageData.interests.length === 0) {
    interestsContainer.innerHTML = '<div class="empty-placeholder">لم تتم إضافة أي اهتمامات بعد</div>';
  } else {
    pageData.interests.forEach((item, index) => {
      const div = document.createElement('div');
      div.className = 'interest-item';
      div.innerHTML = `
      <div class="item-actions">
      <button class="edit-btn" onclick="openModal('interestModal', 'interest', ${index})">
      <i class="fas fa-edit"></i>
      </button>
      <button class="remove-btn" onclick="removeItem('interest', ${index})">
      <i class="fas fa-times"></i>
      </button>
      </div>
      <span class="interest-text">${item}</span>
      `;
      interestsContainer.appendChild(div);
    });
  }
}

// Helper function to render lists
function renderList(containerId, items, template, itemClass) {
  const container = document.getElementById(containerId);
  container.innerHTML = '';
  items.forEach((item, index) => {
    const div = document.createElement('div');
    div.className = itemClass;
    div.innerHTML = template(item, index);
    container.appendChild(div);
  });
}

// Submit form
function submitForm() {
  saveFormData('step5', pageData);
  window.location.href = 'review-cv.php';
}

// Make functions globally available
window.openModal = openModal;
window.removeItem = removeItem;

// Check if in edit mode when page loads
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const isEditMode = urlParams.has('edit');

  if (isEditMode) {
    // 1. Replace "Next" button with "Done"
    const nextBtn = document.getElementById('submit-btn');
    if (nextBtn) {
      nextBtn.innerHTML = 'تم';
      nextBtn.style.margin = 'auto';
      nextBtn.onclick = function() {
        saveFormData('step5', {
          experiences: experiences,
          selectedExperienceType: selectedExperienceType
        });
        window.location.href = 'review-cv.php';
      };
    }

    // 2. Hide "Previous" button
    const prevBtn = document.querySelector('.btn-prev');
    if (prevBtn) {
      prevBtn.style.display = 'none';
    }
  }
});

// Initialize the page on load
document.addEventListener('DOMContentLoaded', initPage);