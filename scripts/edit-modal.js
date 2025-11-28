function openEditModal(id) {
  const modal = document.getElementById('edit-form-' + id);
  if (!modal) return;

  modal.classList.remove('hidden');

  document.body.classList.add('overflow-hidden');
}

function closeEditModal(id) {
  const modal = document.getElementById('edit-form-' + id);
  if (!modal) return;

  modal.classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
}