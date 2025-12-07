<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Send Message for Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="messageForm">
          <input type="hidden" id="eventId" name="event_id">
          <div class="mb-3">
            <label for="eventTitleDisplay" class="form-label">Event</label>
            <input type="text" class="form-control" id="eventTitleDisplay" readonly>
          </div>
          <div class="mb-3">
            <label for="messageText" class="form-label">Message</label>
            <textarea class="form-control" id="messageText" name="message" rows="4" placeholder="Enter your message..." required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="sendMessageBtn">Send Message</button>
      </div>
    </div>
  </div>
</div>

<script>
function openMessageModal(eventId, eventTitle) {
  document.getElementById('eventId').value = eventId;
  document.getElementById('eventTitleDisplay').value = eventTitle;
  document.getElementById('messageText').value = '';
  new bootstrap.Modal(document.getElementById('messageModal')).show();
}

document.getElementById('sendMessageBtn').addEventListener('click', function() {
  const eventId = document.getElementById('eventId').value;
  const message = document.getElementById('messageText').value.trim();

  if (!message) {
    alert('Please enter a message.');
    return;
  }

  // Send message to server
  fetch('../send_message.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      event_id: eventId,
      message: message
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Message sent successfully!');
      bootstrap.Modal.getInstance(document.getElementById('messageModal')).hide();
    } else {
      alert('Error sending message: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Network error while sending message.');
  });
});
</script>
