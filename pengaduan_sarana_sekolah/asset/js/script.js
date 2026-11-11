
document.addEventListener("DOMContentLoaded", () => {
    // ==============================
    // PENGATURAN TEMA
    // ==============================
    const savedTheme = localStorage.getItem("theme") || "light";

    function applyTheme(theme) {
        const normalized = theme === "dark" ? "dark" : "light";

        document.body.classList.remove(
            "light", "dark", "light-mode", "dark-mode"
        );

        document.body.classList.add(normalized);
        localStorage.setItem("theme", normalized);

        const button = document.getElementById("themeToggle");

        if (button) {
            button.textContent = normalized === "light" ? "🌙" : "☀️";
            button.setAttribute(
                "aria-label",
                normalized === "light"
                    ? "Aktifkan mode gelap"
                    : "Aktifkan mode terang"
            );
            button.title = normalized === "light"
                ? "Mode gelap"
                : "Mode terang";
        }

        document.dispatchEvent(
            new CustomEvent("themeChanged", {
                detail: { theme: normalized }
            })
        );
    }

    function createThemeButton() {
        let button = document.getElementById("themeToggle");

        if (!button) {
            button = document.createElement("button");
            button.id = "themeToggle";
            button.className = "theme-toggle";
            button.type = "button";
            document.body.appendChild(button);
        }

        if (button.dataset.themeBound !== "1") {
            button.dataset.themeBound = "1";

            button.addEventListener("click", () => {
                const current = document.body.classList.contains("dark")
                    ? "dark"
                    : "light";

                applyTheme(current === "light" ? "dark" : "light");
            });
        }
    }

    // ==============================
    // NOTIFIKASI DENGAN TOMBOL OK
    // ==============================
    function setupNotifications() {
        const selectors = [
            ".admin-alert",
            ".profile-alert",
            ".user-alert",
            ".uv3-alert"
        ];

        document.querySelectorAll(selectors.join(",")).forEach(alertBox => {
            if (alertBox.dataset.notificationReady === "1") return;

            alertBox.dataset.notificationReady = "1";

            // Buat tombol OK
            const okButton = document.createElement("button");
            okButton.type = "button";
            okButton.className = "notification-ok";
            okButton.textContent = "OK";

            okButton.addEventListener("click", () => {
                alertBox.remove();
            });

            alertBox.appendChild(okButton);
        });
    }

    createThemeButton();
    applyTheme(savedTheme);
    setupNotifications();
});
