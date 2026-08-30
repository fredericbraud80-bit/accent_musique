// Sélecteur du bouton
const toggleBtn = document.getElementById('theme-toggle');

if (toggleBtn) {
    // Charger le thème sauvegardé
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        toggleBtn.textContent = savedTheme === 'light' ? '🔆' : '🌙';
    }

    // Toggle du thème
    toggleBtn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const newTheme = current === 'light' ? 'dark' : 'light';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        toggleBtn.textContent = newTheme === 'light' ? '🔆' : '🌙';
    });
}

// Menu déroulant
const avatar = document.getElementById("user-menu-toggle");
const dropdown = document.getElementById("user-dropdown");

if (avatar && dropdown) {
    avatar.addEventListener("click", () => {
        dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
    });

    // Fermer si on clique ailleurs
    document.addEventListener("click", (e) => {
        if (!avatar.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = "none";
        }
    });
}
