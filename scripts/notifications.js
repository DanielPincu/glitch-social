      document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("notif-button");
        const dropdown = document.getElementById("notif-dropdown");
        btn.addEventListener("click", () => {
          dropdown.classList.toggle("hidden");
        });
        document.addEventListener("click", (e) => {
          if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add("hidden");
          }
        });
      });
  
   
    document.addEventListener("DOMContentLoaded", () => {
      const deleteAllBtn = document.querySelector("#delete-all-notifications");
      if (deleteAllBtn) {
        deleteAllBtn.addEventListener("click", () => {
          fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "ajax=delete_all_notifications"
          })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                // Replace notifications with friendly message
                const notifList = document.querySelector("#notif-dropdown .max-h-64");
                if (notifList) {
                  notifList.innerHTML = `<div class="p-3 text-xs text-gray-400 text-center">💤 No notifications yet — you're all caught up!</div>`;
                }

                // Hide delete button
                deleteAllBtn.style.display = "none";

                // Remove badge
                const badge = document.querySelector("#notif-button span");
                if (badge) badge.remove();
              }
            })
            .catch(err => console.error("Delete all notifications error:", err));
        });
      }
    });