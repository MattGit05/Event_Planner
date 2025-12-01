<?php
// modals/edit_event_modal.php
// The modal loads minimal UI; form fields will be filled by JS via AJAX when opened.
?>
<div class="modal-content" style="max-width:700px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;">
  <h3>Edit Event</h3>
  <form action="update_event.php" method="post" id="editEventForm">
    <input type="hidden" name="id" id="editEventId">
    <div style="margin:8px 0;">
      <label>Title</label><br>
      <input type="text" name="title" id="editTitle" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
    </div>
    <div style="display:flex;gap:10px;">
      <div style="flex:1;">
        <label>Date</label><br>
        <input type="date" name="date" id="editDate" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
      </div>
      <div style="flex:1;">
        <label>Time</label><br>
        <input type="time" name="time" id="editTime" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
      </div>
    </div>
    <div style="margin:8px 0;">
      <label>Category</label><br>
      <select name="category" id="editCategory" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
        <option>Meeting</option>
        <option>Party</option>
        <option>Conference</option>
        <option>Workshop</option>
        <option>Other</option>
      </select>
    </div>
    <div style="margin:8px 0;">
      <label>Description</label><br>
      <textarea name="description" id="editDescription" rows="4" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;"></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button type="button" onclick="document.getElementById('editEventModal').style.display='none'" class="action-btn">Cancel</button>
      <button type="submit" class="edit-btn">Save</button>
    </div>
  </form>
</div>

<script>
/* The parent page will set editEventId, then we'll call this helper to fetch event data */
function loadEventToEdit(id){
  fetch('events_feed.php?single=' + id)
    .then(r => r.json())
    .then(data => {
      if (!data || data.error) return alert('Event not found.');
      document.getElementById('editEventId').value = data.id;
      document.getElementById('editTitle').value = data.title;
      document.getElementById('editDate').value = data.date;
      document.getElementById('editTime').value = data.time;
      document.getElementById('editCategory').value = data.category;
      document.getElementById('editDescription').value = data.description;
    })
    .catch(e => console.error(e));
}
</script>
