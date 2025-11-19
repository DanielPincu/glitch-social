var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ 'color': ['#ffffff', '#ffea00', '#00ff9d', '#ffd1dc', '#ff6b6b', '#000000'] }],
      ]
    }
  });
  document.getElementById('create-post-form').addEventListener('submit', function() {
    document.getElementById('post-content').value = quill.root.innerHTML;
  });