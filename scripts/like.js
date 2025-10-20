document.addEventListener("DOMContentLoaded", () => {
  // Reusable AJAX helper
  async function sendAjax(data) {
    const res = await fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams(data).toString()
    });
    return res.json();
  }

  // Single delegated click listener for all like buttons
  document.body.addEventListener("click", async (e) => {
    const btn = e.target.closest(".like-btn");
    if (!btn) return;

    const postId = btn.dataset.postId;
    const action = btn.dataset.liked === "true" ? "unlike" : "like";

    try {
      const data = await sendAjax({ ajax: "like", post_id: postId, action });
      if (data.success) {
        btn.dataset.liked = (action === "like").toString();
        btn.innerHTML = `${action === "like" ? "❤️" : "🤍"} ${data.likes} Like${data.likes != 1 ? "s" : ""}`;
      }
    } catch (err) {
      console.error("Like failed:", err);
    }
  });
});