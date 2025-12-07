<!-- View Event Modal -->
<link rel="stylesheet" href="modals/view_event_modal.css">
<div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title fw-bold" id="viewEventModalLabel">
          <i class="fas fa-calendar-alt me-2"></i>Event Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="eventDetails">
        <!-- Event details will be loaded here -->
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function viewEvent(eventId) {
  fetch(`get_event.php?id=${eventId}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }

      const isCreator = data.created_by == <?= json_encode($user_id) ?>;
      const roleBadge = isCreator ? '<span class="badge bg-success"><i class="fas fa-crown me-1"></i>Creator</span>' : '<span class="badge bg-info"><i class="fas fa-user me-1"></i>Attendee</span>';

      const details = `
        <div class="event-header mb-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="text-primary mb-0">${data.title}</h3>
            <span class="badge bg-primary fs-6">${data.category}</span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="info-card p-3 bg-light rounded">
                <div class="d-flex align-items-center">
                  <i class="fas fa-calendar-day text-primary me-3 fs-4"></i>
                  <div>
                    <small class="text-muted d-block">Date</small>
                    <strong class="text-dark">${data.date}</strong>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-card p-3 bg-light rounded">
                <div class="d-flex align-items-center">
                  <i class="fas fa-clock text-primary me-3 fs-4"></i>
                  <div>
                    <small class="text-muted d-block">Time</small>
                    <strong class="text-dark">${data.time}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="event-description mb-4">
          <h5 class="text-secondary mb-3">
            <i class="fas fa-align-left me-2"></i>Description
          </h5>
          <div class="description-content p-3 bg-light rounded">
            <p class="mb-0">${data.description || '<em class="text-muted">No description provided.</em>'}</p>
          </div>
        </div>

        <div class="event-attendees mb-4">
          <h5 class="text-secondary mb-3">
            <i class="fas fa-users me-2"></i>Attendees
          </h5>
          <div class="attendees-list p-3 bg-light rounded">
            ${data.attendees && data.attendees.length > 0
              ? `<div class="row g-2">${data.attendees.map(attendee =>
                  `<div class="col-md-6">
                    <div class="attendee-item d-flex align-items-center p-2 bg-white rounded border">
                      <i class="fas fa-user-circle text-primary me-2"></i>
                      <span>${attendee}</span>
                    </div>
                  </div>`
                ).join('')}</div>`
              : '<div class="text-muted"><em>No attendees yet</em></div>'
            }
          </div>
        </div>

        <div class="event-role text-center">
          <div class="role-indicator p-3 bg-gradient rounded text-white">
            <h6 class="mb-2">Your Role</h6>
            ${roleBadge}
          </div>
        </div>
      `;

      document.getElementById('eventDetails').innerHTML = details;
      new bootstrap.Modal(document.getElementById('viewEventModal')).show();
    })
    .catch(error => {
      console.error('Error fetching event details:', error);
      alert('Error loading event details.');
    });
}
</script>
