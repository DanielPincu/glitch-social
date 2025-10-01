document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".like-btn").forEach(button => {
    button.addEventListener("click", function () {
      const postId = this.dataset.postId;
      const action = this.dataset.liked === "true" ? "unlike" : "like";

      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `ajax=like&post_id=${postId}&action=${action}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          this.dataset.liked = (action === "like").toString();
          this.innerHTML = `${action === "like" ? "❤️" : "🤍"} ${data.likes} Like${data.likes != 1 ? 's' : ''}`;
        }
      });
    });
  });
});