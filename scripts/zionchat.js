document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("chatForm");
  const input = document.getElementById("chatInput");
  const messagesDiv = document.getElementById("chatMessages");

  // Inline SVG for generic avatar silhouette
  const genericAvatarSVG = `data:image/svg+xml;utf8,<svg width="32" height="32" xmlns="http://www.w3.org/2000/svg"><g><circle cx="16" cy="12" r="8" fill="%23ccc"/><ellipse cx="16" cy="26" rx="12" ry="6" fill="%23eee"/></g></svg>`;

  // Fetch recent messages
  function loadMessages() {
    fetch("index.php?ajax=fetch_chat")
      .then(res => {
        if (!res.ok) throw new Error('Failed to fetch messages');
        return res.json();
      })
      .then(data => {
        if (!data.success || !Array.isArray(data.messages)) {
          // Clear loading message or any previous content silently
          messagesDiv.innerHTML = "";
          return; // Do not update DOM if data is not valid or no messages
        }
        messagesDiv.innerHTML = "";
        data.messages.forEach(msg => {
          const div = document.createElement("div");
          div.className = "mb-2";

          const username = msg.username ? msg.username : "Unknown";
          const messageText = msg.message || msg.content || "(no text)";
          const time = msg.created_at ? new Date(msg.created_at).toLocaleTimeString() : "";

          // Encode generic avatar SVG safely
          const encodedAvatar = encodeURIComponent(
            `<svg width="32" height="32" xmlns="http://www.w3.org/2000/svg"><g><circle cx="16" cy="12" r="8" fill="#ccc"/><ellipse cx="16" cy="26" rx="12" ry="6" fill="#eee"/></g></svg>`
          );
          const fallbackAvatar = `data:image/svg+xml;utf8,${encodedAvatar}`;
          const avatarUrl =
            msg.avatar_url && msg.avatar_url.trim() ? msg.avatar_url : fallbackAvatar;

          div.innerHTML = `
            <div class="flex items-center gap-2">
              <a href="${msg.profile_url || '#'}" class="flex items-center gap-2">
                <img src="${avatarUrl}" class="w-8 h-8 rounded-full cursor-pointer" 
                     onerror="this.onerror=null;this.src='${fallbackAvatar}'" alt="${username}">
                <span class="font-semibold text-blue-800 hover:underline">${username}</span>
              </a>
              <span class="text-xs text-gray-500">${time}</span>
            </div>
            <p class="ml-10 text-gray-800">${messageText}</p>
          `;
          messagesDiv.appendChild(div);
        });
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
      })
      .catch(err => {
        console.error("Error loading messages:", err);
        // Optionally show an error message in the UI
      });
  }

  // Send message
  form.addEventListener("submit", e => {
    e.preventDefault();
    const message = input.value.trim();
    if (!message) return;

    const params = "message=" + encodeURIComponent(message);

    fetch("index.php?ajax=zion_chat", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params
    })
      .then(res => {
        if (!res.ok) throw new Error('Failed to send message');
        input.value = "";
        loadMessages();
      })
      .catch(err => {
        console.error("Error sending message:", err);
      });
  });

  // Auto-refresh every 10 seconds
  setInterval(loadMessages, 10000);
  loadMessages();
});