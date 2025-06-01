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
