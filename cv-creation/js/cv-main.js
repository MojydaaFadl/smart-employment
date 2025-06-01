// Common data and variables shared across all pages
let currentSelectType = '';

// Nationalities data
const nationalities =
[
  "Saudi Arabian",
  "Egyptian",
  "Yemeni",
  "Syrian",
  "Iraqi",
  "Jordanian",
  "Lebanese",
  "Palestinian",
  "Algerian",
  "Moroccan",
  "Tunisian",
  "Libyan",
  "Sudanese",
  "Emirati",
  "Kuwaiti",
  "Qatari",
  "Omani",
  "Bahraini",
  "Mauritanian",
  "Somali",
  "Djiboutian",
  "Comorian"
];

// locations data
const locations = [
  "Yemen","Saudi Arabia", "United Arab Emirates", "Qatar", "Bahrain", "Kuwait",
  "Oman", "Egypt", "Jordan", "Syria", "Lebanon",
  "Iraq", "Palestine", "Sudan", "Libya", "Tunisia",
  "Algeria", "Morocco", "Mauritania", "Comoros"
];

// Country codes data
const countryCodes = [{
  code: "+967",
  name: "Yemen",
  flag: "🇾🇪"
},
  {
    code: "+966",
    name: "Saudi Arabia",
    flag: "🇸🇦"
  },
  {
    code: "+971",
    name: "UAE",
    flag: "🇦🇪"
  },
  {
    code: "+20",
    name: "Egypt",
    flag: "🇪🇬"
  },
  // Add more countries as needed
];

// Cities data
const cities = {
  "Saudi Arabia": ["Riyadh", "Jeddah", "Mecca", "Medina", "Dammam", "Taif", "Al Khobar", "Buraydah", "Hail", "Tabuk"],
  "United Arab Emirates": ["Dubai", "Abu Dhabi", "Sharjah", "Ajman", "Ras Al Khaimah", "Fujairah", "Umm Al Quwain", "Al Ain"],
  "Egypt": ["Cairo", "Alexandria", "Giza", "Port Said", "Suez", "Luxor", "Aswan", "Ismailia", "Fayoum", "Hurghada"],
  "Jordan": ["Amman", "Zarqa", "Irbid", "Aqaba", "Salt", "Mafraq", "Madaba", "Jerash", "Ma'an", "Tafilah"],
  "Qatar": ["Doha", "Al Rayyan", "Al Wakrah", "Al Khor", "Al Shahaniya", "Umm Salal Muhammad", "Al Daayen"],
  "Oman": ["Muscat", "Salalah", "Nizwa", "Sohar", "Ibri", "Rustaq", "Buraimi", "Sur"],
  "Kuwait": ["Kuwait City", "Al Ahmadi", "Hawalli", "Salmiya", "Al Farwaniyah", "Fahaheel", "Jahra"],
  "Iraq": ["Baghdad", "Basra", "Mosul", "Erbil", "Kirkuk", "Najaf", "Karbala", "Suleymaniyeh", "Nasiriyah", "Dahuk"],
  "Yemen": ["Sana'a", "Aden", "Taiz", "Hodeidah", "Mukalla", "Ibb", "Dhamar", "Al Mukha", "Say'un", "Marib"],
  "Lebanon": ["Beirut", "Tripoli", "Sidon", "Tyre", "Nabatieh", "Zahle", "Baalbek", "Jounieh"],
  "Syria": ["Damascus", "Aleppo", "Homs", "Hama", "Latakia", "Tartus", "Al-Hasakah", "Deir ez-Zor", "Raqqa", "Idlib"],
  "Palestine": ["Jerusalem", "Gaza City", "Hebron", "Nablus", "Ramallah", "Bethlehem", "Khan Yunis", "Jenin", "Tulkarm", "Rafah"],
  "Libya": ["Tripoli", "Benghazi", "Misrata", "Tobruk", "Sabha", "Khoms", "Zawiya", "Ajdabiya", "Sirte", "Al Bayda"],
  "Tunisia": ["Tunis", "Sfax", "Sousse", "Kairouan", "Bizerte", "Gabès", "Ariana", "Gafsa", "Kasserine", "Monastir"],
  "Algeria": ["Algiers", "Oran", "Constantine", "Annaba", "Blida", "Setif", "Tlemcen", "Bejaia", "Batna", "Skikda"],
  "Morocco": ["Rabat", "Casablanca", "Fes", "Marrakech", "Tangier", "Meknes", "Oujda", "Agadir", "Tetouan", "Safi"],
  "Sudan": ["Khartoum", "Omdurman", "Khartoum North", "Port Sudan", "Kassala", "El Obeid", "Wad Madani", "Al Qadarif", "Damazin", "El Fasher"],
  "Somalia": ["Mogadishu", "Hargeisa", "Kismayo", "Bosaso", "Garowe", "Baidoa", "Burao", "Marka", "Jowhar", "Berbera"],
  "Mauritania": ["Nouakchott", "Nouadhibou", "Rosso", "Kaédi", "Zouérat", "Kiffa", "Sélibaby", "Atar", "Aleg", "Akjoujt"],
  "Djibouti": ["Djibouti City", "Ali Sabieh", "Tadjoura", "Obock", "Dikhil", "Arta", "Holhol", "Galafi"],
  "Comoros": ["Moroni", "Mutsamudu", "Fomboni", "Domoni", "Ouani", "Sima", "Mitsamiouli", "Mbéni"],
  "Bahrain": ["Manama", "Riffa", "Muharraq", "Hamad Town", "Isa Town", "Sitrah", "Budaiya", "Jidhafs"]
};


// Industries data
const industries = [
  "Accounting & Finance",
  "Management & Business",
  "Technology & IT",
  "Education & Training",
  "Healthcare",
  "Retail & Sales",
  "Engineering",
  "Marketing & Media",
  "Government Services",
  "Hospitality & Tourism"
];

// Days data
const days = Array.from({
  length: 31
}, (_, i) => (i + 1).toString());

// Months data
const months = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

// Years data (from current year to 100 years back)
const currentYear = new Date().getFullYear();
const years = Array.from({
  length: 100
}, (_, i) => (currentYear - i).toString());

function openSelect(type) {
  document.body.style.overflow = 'hidden';
  currentSelectType = type;
  document.getElementById('selectOverlay').style.display = 'block';
  document.getElementById('selectContainer').style.display = 'block';

  // Set appropriate title
  const titles = {
    'day': 'Select Day',
    'month': 'Select Month',
    'year': 'Select Year',
    'nationality': 'Select Nationality',
    'location': 'Select location',
    'industry': 'Select Industry',
    'country': 'Select Country',
    'country-edu': 'Select Country',
    'country-locattion': 'Select Country',
    'city': 'Select City',
    'city-edu': 'Select City',
    'city-locattion': 'Select City',
    'start-month': 'Select Start Month',
    'start-year': 'Select Start Year',
    'end-month': 'Select End Month',
    'end-year': 'Select End Year',
    'graduation-year': 'Select Graduation Year',
  };
  document.getElementById('selectTitle').textContent = titles[type];

  let dataToShow = [];

  if (type === 'day') {
    dataToShow = days;
  } else if (type === 'month' || type.includes('month')) {
    dataToShow = months;
  } else if (type === 'year' || type.includes('year') || type === 'graduation-year') {
    dataToShow = years;
  } else if (type === 'nationality') {
    dataToShow = nationalities;
  } else if (type === 'location') {
    dataToShow = locations;
  } else if (type === 'industry') {
    dataToShow = industries;
  } else if (type === 'country' || type === 'country-edu' || type === 'country-locattion') {
    dataToShow = Object.keys(cities).sort();
  } else if (type === 'city' || type === 'city-edu' || type === 'city-locattion') {
    const selectedCountry = document.getElementById(
      type === 'city' ? 'country-display':
      type === 'city-edu' ? 'country-display-edu':
      'country-display-locattion').value;
    dataToShow = cities[selectedCountry] || [];
  } else if (type === 'job-level') {
    dataToShow = jobLevels.map(item => item.name);
  } else if (type === 'job-location') {
    dataToShow = jobLocations.map(item => item.name);
  }

  renderOptions(dataToShow);
  document.getElementById('selectSearch').focus();
}

// Close selection list
function closeSelect() {
  document.body.style.overflow = '';
  document.getElementById('selectOverlay').style.display = 'none';
  document.getElementById('selectContainer').style.display = 'none';
}

// Display options in the list
function renderOptions(optionsToShow) {
  const container = document.getElementById('selectOptions');
  container.innerHTML = '';

  if (optionsToShow.length === 0) {
    const emptyMsg = document.createElement('div');
    emptyMsg.className = 'select-option';
    emptyMsg.textContent = 'No results found';
    container.appendChild(emptyMsg);
    return;
  }

  optionsToShow.forEach(option => {
    const optionElement = document.createElement('div');
    optionElement.className = 'select-option';

    // Handle both string and object options
    const displayText = typeof option === 'object' ? option.name || option.toString(): option.toString();
    optionElement.textContent = displayText;

    optionElement.onclick = function() {
      // Store the actual value (object or string)
      const selectedValue = typeof option === 'object' ? option.name || option.toString(): option.toString();
      setSelectedValue(selectedValue);
      closeSelect();

      // If a country is selected, update the cities list
      if (currentSelectType.includes('country')) {
        const cityDisplayId =
        currentSelectType === 'country' ? 'city-display':
        currentSelectType === 'country-edu' ? 'city-display-edu':
        'city-display-locattion';
        document.getElementById(cityDisplayId).value = '';
        document.getElementById(cityDisplayId.replace('-display', '')).value = '';
      }
    };
    container.appendChild(optionElement);
  });
}

function setSelectedValue(value) {
  const displayFields = {
    'nationality': 'nationality-display',
    'location': 'location-display',
    'industry': 'industry-display', // تم إضافة هذا
    'country': 'country-display',
    'country-edu': 'country-display-edu',
    'country-locattion': 'country-display-locattion',
    'city': 'city-display',
    'city-edu': 'city-display-edu',
    'city-locattion': 'city-display-locattion',
    'start-month': 'start-month-display',
    'start-year': 'start-year-display',
    'end-month': 'end-month-display',
    'end-year': 'end-year-display',
    'graduation-year': 'graduation-year-display',
    'job-level': 'job-level-display',
    'job-location': 'job-location-display'
  };

  const hiddenFields = {
    'location': 'location',
    'industry': 'industry', // تم التأكد من وجود هذا
    'country': 'country',
    'country-edu': 'country-edu',
    'country-locattion': 'country-locattion',
    'city': 'city',
    'city-edu': 'city-edu',
    'city-locattion': 'city-locattion',
    'start-month': 'start-month',
    'start-year': 'start-year',
    'end-month': 'end-month',
    'end-year': 'end-year',
    'graduation-year': 'graduation-year',
    'job-level': 'job-level',
    'job-location': 'job-location'
  };

  // تحديث حقل العرض
  if (displayFields[currentSelectType]) {
    document.getElementById(displayFields[currentSelectType]).value = value;
  }

  // تحديث الحقل المخفي
  if (hiddenFields[currentSelectType]) {
    document.getElementById(hiddenFields[currentSelectType]).value = value;
  }

  // إذا تم اختيار بلد، قم بتحديث قائمة المدن
  if (currentSelectType.includes('country')) {
    const cityDisplayId =
    currentSelectType === 'country' ? 'city-display':
    currentSelectType === 'country-edu' ? 'city-display-edu':
    'city-display-locattion';
    if (document.getElementById(cityDisplayId)) {
      document.getElementById(cityDisplayId).value = '';
    }
    if (document.getElementById(cityDisplayId.replace('-display', ''))) {
      document.getElementById(cityDisplayId.replace('-display', '')).value = '';
    }
  }
}

// Filter options by search
function filterOptions() {
  const searchTerm = document.getElementById('selectSearch').value.toLowerCase();

  let dataToFilter = [];

  if (currentSelectType === 'day') {
    dataToFilter = days;
  } else if (currentSelectType === 'month' || currentSelectType.includes('month')) {
    dataToFilter = months;
  } else if (currentSelectType === 'year' || currentSelectType.includes('year') || currentSelectType === 'graduation-year') {
    dataToFilter = years;
  } else if (currentSelectType === 'nationality') {
    dataToFilter = nationalities;
  } else if (currentSelectType === 'location') {
    dataToFilter = locations;
  } else if (currentSelectType === 'industry') {
    dataToFilter = industries;
  } else if (currentSelectType === 'country' || currentSelectType === 'country-edu' || currentSelectType === 'country-locattion') {
    dataToFilter = Object.keys(cities);
  } else if (currentSelectType === 'city' || currentSelectType === 'city-edu' || currentSelectType === 'city-locattion') {
    const selectedCountry = document.getElementById(
      currentSelectType === 'city' ? 'country-display':
      currentSelectType === 'city-edu' ? 'country-display-edu':
      'country-display-locattion').value;
    dataToFilter = cities[selectedCountry] || [];
  } else if (currentSelectType === 'job-level') {
    dataToFilter = jobLevels;
  } else if (currentSelectType === 'job-location') {
    dataToFilter = jobLocations;
  }

  const filtered = dataToFilter.filter(function(item) {
    // Handle both string and object items
    const itemText = typeof item === 'object' ? item.name || item.toString(): item.toString();
    return itemText.toLowerCase().includes(searchTerm);
  });

  renderOptions(filtered);
}

// Open country list
function openCountrySelect() {
  document.body.style.overflow = 'hidden';
  document.getElementById('countryOverlay').style.display = 'block';
  document.getElementById('countrySelect').style.display = 'block';
  renderCountries(countryCodes);
  document.getElementById('countrySearch').focus();
}

// Close country list
function closeCountrySelect() {
  document.body.style.overflow = '';
  document.getElementById('countryOverlay').style.display = 'none';
  document.getElementById('countrySelect').style.display = 'none';
}

// Display countries in the list
function renderCountries(countriesToShow) {
  const container = document.getElementById('countryOptions');
  container.innerHTML = '';

  if (countriesToShow.length === 0) {
    const emptyMsg = document.createElement('div');
    emptyMsg.className = 'select-option';
    emptyMsg.textContent = 'No results found';
    container.appendChild(emptyMsg);
    return;
  }

  countriesToShow.forEach(country => {
    const option = document.createElement('div');
    option.className = 'select-option';
    option.innerHTML = `${country.flag} ${country.name} <span style="color: #007bff; margin-right: 10px;">${country.code}</span>`;
    option.onclick = () => {
      document.getElementById('country-code-display').value = country.code;
      document.getElementById('country-code').value = country.code;
      closeCountrySelect();
    };
    container.appendChild(option);
  });
}

// Filter countries by search
function filterCountries() {
  const searchTerm = document.getElementById('countrySearch').value.toLowerCase();
  const filtered = countryCodes.filter(country =>
    country.name.toLowerCase().includes(searchTerm) ||
    country.code.includes(searchTerm)
  );
  renderCountries(filtered);
}

// Navigation bar functions
function openSidebar() {
  document.getElementById("sidebar").style.right = "0";
}

function closeSidebar() {
  document.getElementById("sidebar").style.right = "-100%";
}

function toggleDropdown(button) {
  const container = button.parentElement;
  const dropdown = container.querySelector(".dropdown-content");
  const icon = button.querySelector("i");

  button.classList.toggle("active");

  if (dropdown.style.display === "block") {
    dropdown.style.display = "none";
  } else {
    dropdown.style.display = "block";
  }
}