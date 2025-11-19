// Tenor GIF search config
var TENOR_API_KEY = window.TENOR_API_KEY;
const TENOR_CLIENT_KEY = 'glitch-social';

function setupGifPanel() {
    const gifPanel = document.getElementById('gif-panel');
    const gifForm = document.getElementById('gif-search-form');
    const gifInput = document.getElementById('gif-search-input');
    const gifResults = document.getElementById('gif-results');
    const gifClose = document.getElementById('gif-close-btn');

    if (!gifPanel || !gifForm || !gifInput || !gifResults || !gifClose) {
        return;
    }

    function openPanel(quillInstance) {
        window.activeQuillForGif = quillInstance;
        gifPanel.classList.remove('hidden');
        gifInput.focus();
        gifResults.innerHTML = '<p class="col-span-3 text-center text-gray-400 italic">Type a keyword and press Search.</p>';
    }

    function closePanel() {
        gifPanel.classList.add('hidden');
        window.activeQuillForGif = null;
    }

    gifClose.addEventListener('click', closePanel);

    gifPanel.addEventListener('click', function (e) {
        if (e.target === gifPanel) closePanel();
    });

    gifForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const query = gifInput.value.trim();
        if (!query) return;

        gifResults.innerHTML = '<p class="col-span-3 text-center text-gray-400 italic">Searching...</p>';

        fetch(`https://tenor.googleapis.com/v2/search?q=${encodeURIComponent(query)}&key=${TENOR_API_KEY}&client_key=${TENOR_CLIENT_KEY}&limit=24&media_filter=gif`)
            .then(r => r.json())
            .then(data => {
                gifResults.innerHTML = '';
                if (!data.results || !data.results.length) {
                    gifResults.innerHTML = '<p class="col-span-3 text-center text-gray-400 italic">No GIFs found.</p>';
                    return;
                }

                data.results.forEach(result => {
                    const media = result.media_formats && (result.media_formats.tinygif || result.media_formats.gif);
                    if (!media || !media.url) return;

                    const url = media.url;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'relative border border-[#7AA0E0] rounded overflow-hidden hover:brightness-110 transition';
                    btn.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;

                    btn.addEventListener('click', () => {
                        if (window.activeQuillForGif) {
                            const q = window.activeQuillForGif;
                            let index = q.getLength();
                            q.insertEmbed(index, 'image', url, 'user');
                            q.setSelection(index + 1, 0);
                        }
                        closePanel();
                    });

                    gifResults.appendChild(btn);
                });
            })
            .catch(() => {
                gifResults.innerHTML = '<p class="col-span-3 text-center text-red-400 italic">Error loading GIFs.</p>';
            });
    });

    window.openGifPanelForQuill = openPanel;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupGifPanel);
} else {
    setupGifPanel();
}

   //Quill for CREATE POST
   
if (document.querySelector('#quill-editor')) {
    var quillCreate = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    ['bold', 'italic', 'underline'],
                    [{ 'color': ['#ffffff', '#ffea00', '#00ff9d', '#ffd1dc', '#ff6b6b', '#1a1a1a'] }],
                    ['gif']
                ],
                handlers: {
                    gif: function () {
                        if (window.openGifPanelForQuill) {
                            window.openGifPanelForQuill(quillCreate);
                        }
                    }
                }
            }
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
            toolbar: {
                container: [
                    ['bold', 'italic', 'underline'],
                    [{ 'color': ['#ffffff', '#ffea00', '#00ff9d', '#ffd1dc', '#ff6b6b', '#1a1a1a'] }],
                    ['gif']
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

    quillEdit.root.innerHTML = initialContent;

    editorDiv.closest('form').addEventListener('submit', function() {
        hiddenInput.value = quillEdit.root.innerHTML;
    });
});

/* ===== Quill for COMMENTS ===== */

document.querySelectorAll("[data-comment-editor]").forEach(function(el) {

    var hiddenInput = el.parentElement.querySelector(".comment-hidden-input");

    var toolbar = [
        ["bold", "italic", "underline"], 
        [{ 'color': ['#000000', '#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffffff'] }],
        ["gif"]
    ];

    var q = new Quill(el, {
        theme: "snow",
        modules: {
            toolbar: {
                container: toolbar,
                handlers: {
                    gif: function () {
                        if (window.openGifPanelForQuill) {
                            window.openGifPanelForQuill(q);
                        }
                    }
                }
            }
        }
    });

    q.on("text-change", function () {
        if (hiddenInput) {
            hiddenInput.value = q.root.innerHTML;
        }
    });
});