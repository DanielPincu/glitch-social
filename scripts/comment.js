document.addEventListener("DOMContentLoaded", () => {
  function getFormCsrf(form) {
    const hidden = form.querySelector("input[name='csrf_token']");
    return hidden ? hidden.value : "";
  }
  // ===== Helpers =====
  function findCommentItem(target) {
    return target.closest("[data-comment], .comment-item, .flex.items-start");
  }
  function extractCommentId(root) {
    return root ? root.getAttribute("data-comment-id") : null;
  }
  function findCommentText(root) {
    return root.querySelector("[data-comment-text]");
  }

  // ===== New Comment Submission =====
  document.querySelectorAll(".comment-form form").forEach(form => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const postId = this.querySelector("input[name='post_id']").value;
      const contentInput = this.querySelector(".comment-hidden-input");
      const content = contentInput ? contentInput.value.trim() : "";
      if (!content) return;

      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `ajax=comment&post_id=${encodeURIComponent(postId)}&content=${encodeURIComponent(content)}&csrf_token=${encodeURIComponent(getFormCsrf(this))}`
      })
      .then(res => res.json())
      .then(data => {
        // Show warning on failure (CSRF, cooldown, etc.)
        if (data && data.success === false) {
          const warn = document.createElement("div");
          warn.className = "bg-red-200 text-red-800 p-2 rounded border border-red-400 shadow-md mb-2";
          warn.textContent = data.message || "Something went wrong.";

          const postContainer = this.closest(".xp-window") || document;
          const commentSection = postContainer.querySelector(".mt-4") || document.querySelector(".mt-4");

          commentSection.prepend(warn);

          // Auto-remove after 2 seconds
          setTimeout(() => warn.remove(), 5000);
          return;
        }
        if (data && data.success) {
          const postContainer = this.closest(".xp-window") || document;
          const commentSection = postContainer.querySelector(".mt-4") || document.querySelector(".mt-4");

          // Insert server-rendered HTML for the new comment
          commentSection.insertAdjacentHTML("beforeend", data.html);

          // Auto-remove cooldown message inserted as HTML
          const cd = commentSection.querySelector(".cooldown-message");
          if (cd) {
            setTimeout(() => {
              if (cd) cd.remove();
            }, 5000);
          }

          // Reset and hide composer
          if (contentInput) contentInput.value = "";
          const editorEl = this.querySelector("[data-comment-editor]");
          if (editorEl && editorEl._quill) {
            editorEl._quill.root.innerHTML = "";
          }
          const cf = this.closest(".comment-form");
          if (cf) cf.classList.add("hidden");

          // Re-init icons if present
          if (window.feather) feather.replace();
        }
      })
      .catch(() => {});
    });
  });

  // ===== Inline Edit (delegated, robust) =====
  document.body.addEventListener("click", function(e) {
    const editBtn = e.target.closest("[data-action='edit-comment'], .edit-comment-btn, button[name='edit_comment']");
    if (!editBtn) return;
    e.preventDefault();

    const commentItem = findCommentItem(editBtn);
    if (!commentItem) return;
    const commentId = extractCommentId(commentItem);
    const textElem = findCommentText(commentItem);
    if (!textElem) return; // nothing to edit

    // If already editing, ignore
    if (commentItem.querySelector(".inline-editor")) return;

    const originalHtml = textElem.innerHTML;

    const editorWrap = document.createElement("div");
    editorWrap.className = "inline-editor w-full";

    const quillContainer = document.createElement("div");
    quillContainer.className = "quill-inline-editor bg-slate-300 text-black border border-gray-600 p-2";
    quillContainer.style.minHeight = "80px";

    const saveBtn = document.createElement("button");
    saveBtn.type = "button";
    saveBtn.className = "mt-2 px-3 py-1 text-sm bg-blue-600 text-white rounded";
    saveBtn.textContent = "Save";

    const cancelBtn = document.createElement("button");
    cancelBtn.type = "button";
    cancelBtn.className = "ml-2 mt-2 px-3 py-1 text-sm bg-gray-500 text-white rounded";
    cancelBtn.textContent = "Cancel";

    editorWrap.appendChild(quillContainer);
    editorWrap.appendChild(saveBtn);
    editorWrap.appendChild(cancelBtn);

    textElem.classList.add("hidden");
    textElem.insertAdjacentElement("afterend", editorWrap);


    // I wish I could move this in quill.js but oh well... it needs to be here forever.
    const quillEdit = new Quill(quillContainer, {
      theme: "snow",
      modules: {
        toolbar: {
          container: [
            ["bold", "italic", "underline"],
            [{ 'color': ['#000000', '#FF6B6B', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffffff'] }],
            ["gif"]
          ],
          handlers: {
            gif: function () {
              if (window.openGifPanelForQuill) {
                window.openGifPanelForQuill(quillEdit);
              }
            }
          }
        }
      }
    });

    quillEdit.root.innerHTML = originalHtml;

    const restore = () => {
      editorWrap.remove();
      textElem.classList.remove("hidden");
    };

    cancelBtn.addEventListener("click", restore);

    saveBtn.addEventListener("click", () => {
      const newContent = quillEdit.root.innerHTML.trim();
      if (!newContent || newContent === originalHtml) { restore(); return; }

      const cid = commentId || extractCommentId(commentItem);
      if (!cid) { restore(); return; }

      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `ajax=update_comment&comment_id=${encodeURIComponent(cid)}&new_content=${encodeURIComponent(newContent)}&csrf_token=${encodeURIComponent(getFormCsrf(commentItem))}`
      })
      .then(res => res.json())
      .then(data => {
        if (data && data.success) {
          textElem.innerHTML = newContent;
        }
        restore();
      })
      .catch(() => restore());
    });
  });

  // ===== Delegated Delete =====
  document.body.addEventListener("click", function(e) {
    const deleteButton = e.target.closest("form button[name='delete_comment']");
    if (!deleteButton) return;
    e.preventDefault();

    const form = deleteButton.closest("form");
    const hidden = form.querySelector("input[name='comment_id'], input[name='commentId']");
    const commentId = hidden ? hidden.value : null;
    if (!commentId) return;

    if (!confirm("Are you sure you want to delete this comment?")) return;

    fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `ajax=delete_comment&comment_id=${encodeURIComponent(commentId)}&csrf_token=${encodeURIComponent(getFormCsrf(form))}`
    })
    .then(res => res.json())
    .then(data => {
      if (data && data.success) {
        const commentElement = form.closest("[data-comment], .comment-item, .flex.items-start");
        if (commentElement) commentElement.remove();
      }
    })
    .catch(() => {});
  });
});