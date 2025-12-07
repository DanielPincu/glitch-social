const CHAT_EMOJIS = [
  "😀","😃","😄","😁","😆","😂",
  "🤣","😊","🙂","😉","😍","😘",
  "😎","🤩","😢","😭","😡","😱",
  "🔥","✨","❤️","👍","👎","🙏"
];

function setupChatEmojiPicker() {
  const grid = document.getElementById("chat-emoji-grid");
  const panel = document.getElementById("chat-emoji-panel");
  const closeBtn = document.getElementById("chat-emoji-close");

  grid.innerHTML = "";

  CHAT_EMOJIS.forEach(e => {
    const btn = document.createElement("button");
    btn.textContent = e;
    btn.className = "hover:scale-125 transition";
    btn.onclick = () => {
      const input = document.getElementById("chatInput");
      input.value += e;
      panel.classList.add("hidden");
    };
    grid.appendChild(btn);
  });

  closeBtn.onclick = () => panel.classList.add("hidden");
}

function openChatEmojiPicker() {
  const panel = document.getElementById("chat-emoji-panel");
  panel.classList.remove("hidden");
}

document.addEventListener("DOMContentLoaded", setupChatEmojiPicker);