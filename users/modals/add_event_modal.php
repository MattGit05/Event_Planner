<?php
// modals/add_event_modal.php
// (This file is included inside user_dashboard.php)
?>
<div class="modal-content" style="max-width:700px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;">
  <h3>Create Event</h3>
  <form action="create_event.php" method="post" id="addEventForm">
    <input type="hidden" name="created_by" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
    <div style="margin:8px 0;">
      <label>Title</label><br>
      <input type="text" name="title" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
    </div>
    <div style="display:flex;gap:10px;">
      <div style="flex:1;">
        <label>Date</label><br>
        <input type="date" name="date" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
      </div>
      <div style="flex:1;">
        <label>Time</label><br>
        <input type="time" name="time" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
      </div>
    </div>
    <div style="margin:8px 0;">
      <label>Category</label><br>
      <select name="category" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;">
        <option value="Meeting">Meeting</option>
        <option value="Party">Party</option>
        <option value="Conference">Conference</option>
        <option value="Workshop">Workshop</option>
        <option value="Other">Other</option>
      </select>
    </div>
    <div style="margin:8px 0;">
      <label>Description</label><br>
      <textarea name="description" rows="4" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;"></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button type="button" onclick="document.getElementById('addEventModal').style.display='none'" class="action-btn">Cancel</button>
      <button type="submit" class="add-btn">Create</button>
    </div>
  </form>
</div>