// scripts/image-previewer.js
function previewImage(event, postId = null) {
  const file = event.target.files[0];

  const previewDiv = document.getElementById(postId ? `imagePreview-${postId}` : 'imagePreview');
  const previewImg = document.getElementById(postId ? `previewImg-${postId}` : 'previewImg');
  const fileNameSpan = document.getElementById(postId ? `fileName-${postId}` : 'fileName');

  if (file) {
    previewDiv.classList.remove('hidden');
    previewImg.src = URL.createObjectURL(file);
    fileNameSpan.textContent = file.name;
  } else {
    previewDiv.classList.add('hidden');
    previewImg.src = "";
    fileNameSpan.textContent = "";
  }
}