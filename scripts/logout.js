function openLogoutModal() {
    const modal = document.getElementById("logout-modal");
    const startMenu = document.getElementById("start-menu");
    if (modal) {
      modal.classList.remove("hidden");  // show modal
    }
    if (startMenu) {
      startMenu.classList.add("hidden"); // close start menu if open
    }
  }

  function closeLogoutModal() {
    const modal = document.getElementById("logout-modal");
    if (modal) {
      modal.classList.add("hidden"); // hide modal
    }
  }