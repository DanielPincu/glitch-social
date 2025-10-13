document.addEventListener("DOMContentLoaded", () => {
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
          // Find the parent post container
          const postContainer = this.closest(".xp-window");
          const commentSection = postContainer.querySelector(".mt-4");

          // Insert the new comment HTML returned by server
          commentSection.insertAdjacentHTML("beforeend", data.html);

          // Reset and hide form
          const input = this.querySelector("input[name='comment_content']");
          input.value = "";
          this.closest(".comment-form").classList.add("hidden");

          // Reinitialize feather icons for new elements
          if (window.feather) {
            feather.replace();
          }

          // Reattach event listeners for edit/delete buttons dynamically
          attachCommentActionListeners();
        }
      });
    });
  });

  // Handle delete comment via AJAX
  document.querySelectorAll("form[action*='delete_comment'], form button[name='delete_comment']").forEach(button => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const form = this.closest("form");
      const commentId = form.querySelector("input[name='comment_id']").value;

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

  // Handle edit comment via AJAX
  document.querySelectorAll("form[id^='edit-form-']").forEach(form => {
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
          const textElem = document.getElementById(`comment-text-${commentId}`);
          textElem.textContent = newContent;

          // Hide edit form and show updated text
          this.classList.add("hidden");
          textElem.classList.remove("hidden");
        }
      });
    });
  });

  function attachCommentActionListeners() {
    // Delete comment
    document.querySelectorAll("form[action*='delete_comment'], form button[name='delete_comment']").forEach(button => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        const form = this.closest("form");
        const commentId = form.querySelector("input[name='comment_id']").value;

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

    // Edit comment
    document.querySelectorAll("form[id^='edit-form-']").forEach(form => {
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
            const textElem = document.getElementById(`comment-text-${commentId}`);
            textElem.textContent = newContent;

            // Hide edit form and show updated text
            this.classList.add("hidden");
            textElem.classList.remove("hidden");
          }
        });
      });
    });
  }
});