// دوال شريط التنقل
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

        // بيانات الدول
const countries = [
  { value: "All", name: "All countries" },
  { value: "Palestine", name: "Palestine" },
  { value: "yemen", name: "Yemen" },
  { value: "saudi arabia", name: "Saudi Arabia" },
  { value: "egypt", name: "Egypt" },
  { value: "morocco", name: "Morocco" },
  { value: "jordan", name: "Jordan" },
  { value: "lebanon", name: "Lebanon" },
  { value: "syria", name: "Syria" },
  { value: "iraq", name: "Iraq" },
  { value: "algeria", name: "Algeria" },
  { value: "tunisia", name: "Tunisia" },
  { value: "libya", name: "Libya" },
  { value: "sudan", name: "Sudan" },
  { value: "oman", name: "Oman" },
  { value: "kuwait", name: "Kuwait" },
  { value: "qatar", name: "Qatar" },
  { value: "bahrain", name: "Bahrain" },
  { value: "United Arab Emirates", name: "United Arab Emirates" },
  { value: "palestine", name: "Palestine" },
  { value: "mauritania", name: "Mauritania" },
  { value: "comoros", name: "Comoros" },
  { value: "djibouti", name: "Djibouti" },
  { value: "somalia", name: "Somalia" },
];

        // فتح قائمة الدول
        function openCountrySelect() {
            document.getElementById('countryOverlay').style.display = 'block';
            document.getElementById('countrySelect').style.display = 'block';
            renderCountries(countries);
            document.getElementById('countrySearch').focus();
        }

        // إغلاق قائمة الدول
        function closeCountrySelect() {
            document.getElementById('countryOverlay').style.display = 'none';
            document.getElementById('countrySelect').style.display = 'none';
        }

        // عرض الدول في القائمة
        function renderCountries(countriesToShow) {
            const container = document.getElementById('countryOptions');
            container.innerHTML = '';
            
            if (countriesToShow.length === 0) {
                const emptyMsg = document.createElement('div');
                emptyMsg.className = 'select-option';
                emptyMsg.textContent = 'لا توجد نتائج';
                container.appendChild(emptyMsg);
                return;
            }
            
            countriesToShow.forEach(country => {
                const option = document.createElement('div');
                option.className = 'select-option';
                option.textContent = country.name;
                option.onclick = () => {
                    document.getElementById('country-display').value = country.name;
                    document.getElementById('country-value').value = country.value;
                    closeCountrySelect();
                };
                container.appendChild(option);
            });
        }

        // تصفية الدول حسب البحث
        function filterCountries() {
            const searchTerm = document.getElementById('countrySearch').value.toLowerCase();
            const filtered = countries.filter(country => 
                country.name.toLowerCase().includes(searchTerm)
            );
            renderCountries(filtered);
        }

        // إغلاق القائمة عند النقر خارجها
        document.getElementById('countryOverlay').onclick = closeCountrySelect;