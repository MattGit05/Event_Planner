<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Send Message to Event Creator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="messageForm">

          <!-- Hidden fields -->
          <input type="hidden" id="eventId" name="event_id">
          <input type="hidden" id="receiver_id" name="receiver_id">

          <div class="mb-3">
            <label class="form-label">Event</label>
            <input type="text" class="form-control" id="eventTitleDisplay" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" id="messageText" name="message" rows="4" required></textarea>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="sendMessageBtn">Send</button>
      </div>

    </div>
  </div>
</div>

<script>
function openMessageModal(eventId, eventTitle, creatorId) {

  // Fill hidden inputs correctly
  document.getElementById('eventId').value = eventId;
  document.getElementById('receiver_id').value = creatorId;
  document.getElementById('eventTitleDisplay').value = eventTitle;

  // Clear previous message
  document.getElementById('messageText').value = '';

  // Show modal
  new bootstrap.Modal(document.getElementById('messageModal')).show();
}

document.getElementById('sendMessageBtn').addEventListener('click', function() {

  const formData = new FormData(document.getElementById('messageForm'));

  fetch('../send_message.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() === 'success') {
      alert('Message sent!');
      bootstrap.Modal.getInstance(document.getElementById('messageModal')).hide();
    } else {
      alert('Error sending message: ' + response);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Network error');
  });
});
</script>
