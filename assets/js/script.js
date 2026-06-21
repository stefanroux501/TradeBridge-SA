document.addEventListener("DOMContentLoaded", function() {

    // --- 1. AUTO-VANISHING ALERTS ---
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(function() {
                alert.remove();
            }, 500); 
        }, 4000);
    });

    // --- 2. DARK MODE TOGGLE ---
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement; 
    
    const savedTheme = localStorage.getItem('siteTheme');
    if (savedTheme) {
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if (savedTheme === 'dark' && darkModeToggle) {
            darkModeToggle.innerHTML = '☀️ Light Mode';
        }
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            if (currentTheme === 'dark') {
                htmlElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('siteTheme', 'light');
                this.innerHTML = '🌙 Dark Mode';
            } else {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('siteTheme', 'dark');
                this.innerHTML = '☀️ Light Mode';
            }
        });
    }

    // --- 3. DANGER CONFIRMATION BUTTONS ---
    const dangerButtons = document.querySelectorAll('.btn-danger');
    dangerButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            const userConfirmed = confirm("Are you absolutely sure you want to do this? This action cannot be undone.");
            if (!userConfirmed) {
                event.preventDefault();
            }
        });
    });
    
    
});