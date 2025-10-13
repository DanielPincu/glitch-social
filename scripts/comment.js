document.addEventListener("DOMContentLoaded", () => {
  // Handle new comment submission for both tabs
  document.querySelectorAll(".comment-form form").forEach(form => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const postId = this.querySelector("input[name='post_id']").value;
      const content = this.querySelector("input[name='comment_content']").value.trim();
      if (!content) return;

      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `ajax=comment&post_id=${postId}&content=${encodeURIComponent(content)}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const postContainer = this.closest(".xp-window");
          const commentSection = postContainer.querySelector(".mt-4");

          // Insert new comment HTML returned by server
          commentSection.insertAdjacentHTML("beforeend", data.html);

          // Reset and hide the form
          const input = this.querySelector("input[name='comment_content']");
          input.value = "";
          this.closest(".comment-form").classList.add("hidden");

          // Reinitialize feather icons for new elements
          if (window.feather) feather.replace();

          // Reattach event listeners for edit/delete buttons dynamically
          attachCommentActionListeners();
        }
      });
    });
  });

  // Function to handle comment delete/edit re-binding
  function attachCommentActionListeners() {
    // DELETE comment for both tabs
    document.querySelectorAll("form button[name='delete_comment']").forEach(button => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        const form = this.closest("form");
        const commentId = form.querySelector("input[name='comment_id']").value;

        if (!confirm("Are you sure you want to delete this comment?")) return;

        fetch("index.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `ajax=delete_comment&comment_id=${commentId}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const commentElement = form.closest(".flex.items-start");
            if (commentElement) commentElement.remove();
          }
        });
      });
    });

    // EDIT comment for both tabs
    document.querySelectorAll("form[id^='hot-edit-form-'], form[id^='following-edit-form-']").forEach(form => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const commentId = this.querySelector("input[name='comment_id']").value;
        const newContent = this.querySelector("input[name='new_comment_content']").value.trim();
        if (!newContent) return;

        fetch("index.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `ajax=update_comment&comment_id=${commentId}&new_content=${encodeURIComponent(newContent)}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const tabPrefix = this.id.startsWith("hot-") ? "hot" : "following";
            const textElem = document.getElementById(`${tabPrefix}-comment-text-${commentId}`);
            if (textElem) {
              textElem.textContent = newContent;
              this.classList.add("hidden");
              textElem.classList.remove("hidden");
            }
          }
        });
      });
    });
  }

  // Initial listener attachment
  attachCommentActionListeners();
});