    // Update time in taskbar
    function updateTime() {
      const now = new Date();
      const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      document.getElementById('current-time').textContent = timeString;
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Feather icons
    feather.replace();

    // Start menu toggle
    const startBtn = document.querySelector(".xp-button");
    const startMenu = document.getElementById("start-menu");

    startBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      startMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", (e) => {
      if (!startMenu.contains(e.target) && !startBtn.contains(e.target)) {
        startMenu.classList.add("hidden");
      }
    });