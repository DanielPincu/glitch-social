document.addEventListener("DOMContentLoaded", () => {
  // ===== Helpers =====
  function findCommentItem(target) {
    return target.closest("[data-comment-id]");
  }
  function extractCommentId(root) {
    return root ? root.getAttribute("data-comment-id") : null;
  }
  function findCommentText(root, id) {
    if (!root) return null;
    if (id) {
      const byId = root.querySelector(`#comment-text-${id}`);
      if (byId) return byId;
    }
    const explicit = root.querySelector("[data-comment-text]");
    if (explicit) return explicit;
    const candidates = root.querySelectorAll(".comment-text, .whitespace-pre-wrap, p, .text-sm, .text-base");
    for (const el of candidates) {
      if (el.closest("form, button, .actions")) continue;
      if (el.textContent && el.textContent.trim().length) return el;
    }
    return null;
  }
  function sendAjax(data) {
    return fetch("index.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams(data).toString()
    }).then(res => res.json());
  }

  // ===== New Comment Submission (delegated) =====
  document.body.addEventListener("submit", e => {
    const form = e.target.closest(".comment-form form");
    if (!form) return;
    e.preventDefault();

    const postId = form.querySelector("input[name='post_id']")?.value;
    const contentInput = form.querySelector("input[name='comment_content'], textarea[name='comment_content']");
    const content = contentInput?.value.trim();
    if (!postId || !content) return;

    sendAjax({ ajax: "comment", post_id: postId, content }).then(data => {
      if (data && data.success) {
        const postContainer = form.closest(".xp-window") || document;
        const commentSection = postContainer.querySelector(".mt-4") || document.querySelector(".mt-4");
        if (commentSection) commentSection.insertAdjacentHTML("beforeend", data.html);

        contentInput.value = "";
        const cf = form.closest(".comment-form");
        if (cf) cf.classList.add("hidden");

        if (window.feather) feather.replace();
      }
    }).catch(() => {});
  });

  // ===== Inline Edit (delegated) =====
  document.body.addEventListener("click", e => {
    const editBtn = e.target.closest("[data-action='edit-comment'], .edit-comment-btn, button[name='edit_comment']");
    if (!editBtn) return;
    e.preventDefault();

    const commentItem = findCommentItem(editBtn);
    if (!commentItem) return;
    const commentId = extractCommentId(commentItem);
    const textElem = findCommentText(commentItem, commentId);
    if (!textElem || commentItem.querySelector(".inline-editor")) return;

    const originalText = textElem.textContent.trim().replace(/\s+/g, " ");

    const editorWrap = document.createElement("div");
    editorWrap.className = "inline-editor flex items-center w-full";

    const textarea = document.createElement("textarea");
    textarea.className = "w-full bg-gray-800 text-white text-sm px-3 py-2 rounded border border-gray-600 focus:outline-none";
    textarea.value = originalText;

    const saveBtn = document.createElement("button");
    saveBtn.type = "button";
    saveBtn.className = "ml-2 px-3 py-1 text-sm bg-blue-600 text-white rounded";
    saveBtn.textContent = "Save";

    const cancelBtn = document.createElement("button");
    cancelBtn.type = "button";
    cancelBtn.className = "ml-2 px-3 py-1 text-sm bg-gray-500 text-white rounded";
    cancelBtn.textContent = "Cancel";

    editorWrap.appendChild(textarea);
    editorWrap.appendChild(saveBtn);
    editorWrap.appendChild(cancelBtn);

    textElem.classList.add("hidden");
    textElem.insertAdjacentElement("afterend", editorWrap);
    textarea.focus();

    const restore = () => {
      editorWrap.remove();
      textElem.classList.remove("hidden");
    };

    cancelBtn.addEventListener("click", restore);

    saveBtn.addEventListener("click", () => {
      const newContent = textarea.value.trim();
      if (!newContent || newContent === originalText) { restore(); return; }
      if (!commentId) { restore(); return; }

      sendAjax({ ajax: "update_comment", comment_id: commentId, new_content: newContent })
        .then(data => {
          if (data && data.success) {
            textElem.textContent = newContent;
          }
          restore();
        })
        .catch(() => restore());
    });
  });

  // ===== Delegated Delete =====
  document.body.addEventListener("click", e => {
    const deleteButton = e.target.closest("form button[name='delete_comment']");
    if (!deleteButton) return;
    e.preventDefault();

    const form = deleteButton.closest("form");
    const commentId = form?.querySelector("input[name='comment_id'], input[name='commentId']")?.value;
    if (!commentId) return;

    if (!confirm("Are you sure you want to delete this comment?")) return;

    sendAjax({ ajax: "delete_comment", comment_id: commentId })
      .then(data => {
        if (data && data.success) {
          const commentElement = form.closest("[data-comment-id]");
          if (commentElement) commentElement.remove();
        }
      })
      .catch(() => {});
  });
});