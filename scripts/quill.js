   //Quill for CREATE POST
   
if (document.querySelector('#quill-editor')) {
    var quillCreate = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'color': ['#ffffff', '#ffea00', '#00ff9d', '#ffd1dc', '#ff6b6b', '#1a1a1a'] }],
            ]
        }
    });

    var createForm = document.getElementById('create-post-form');
    if (createForm) {
        createForm.addEventListener('submit', function() {
            document.getElementById('post-content').value = quillCreate.root.innerHTML;
        });
    }
}


   //Quill for EDIT POST
  
document.querySelectorAll('.quill-edit').forEach(function(editorDiv) {

    var postId = editorDiv.id.replace('quill-edit-', '');
    var hiddenInput = document.getElementById('quill-edit-input-' + postId);
    var initialContent = editorDiv.dataset.initial || '';

    var quillEdit = new Quill('#' + editorDiv.id, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'color': ['#ffffff', '#ffea00', '#00ff9d', '#ffd1dc', '#ff6b6b', '#1a1a1a'] }],
            ]
        }
    });

    quillEdit.root.innerHTML = initialContent;

    editorDiv.closest('form').addEventListener('submit', function() {
        hiddenInput.value = quillEdit.root.innerHTML;
    });
});