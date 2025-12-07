<?php
/**
 * Send QR code email to attendee
 * @param string $to Attendee email
 * @param string $eventTitle Event title
 * @param string $qrCodeUrl URL to QR code image
 * @param array $eventDetails Event details array
 * @return bool Success status
 */
function sendQREmail($to, $eventTitle, $qrCodeUrl, $eventDetails = []) {
    $subject = "Your QR Code for Event: {$eventTitle}";

    $message = "
    <html>
    <head>
        <title>Your Event QR Code</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
            .qr-code { text-align: center; margin: 20px 0; }
            .event-details { background-color: #f8f9fa; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>{$eventTitle}</h1>
                <p>Your personal QR code for attendance tracking</p>
            </div>

            <div class='event-details'>
                <h3>Event Details:</h3>
                <p><strong>Date:</strong> {$eventDetails['date']}</p>
                <p><strong>Time:</strong> {$eventDetails['time']}</p>
                <p><strong>Category:</strong> {$eventDetails['category']}</p>
                " . (!empty($eventDetails['description']) ? "<p><strong>Description:</strong> {$eventDetails['description']}</p>" : "") . "
            </div>

            <div class='qr-code'>
                <h3>Scan this QR code at the event:</h3>
                <img src='{$qrCodeUrl}' alt='Event QR Code' style='max-width: 200px; height: auto;'>
            </div>

            <div class='footer'>
                <p>This QR code is unique to you and will be used to track your attendance.</p>
                <p>Please bring this email or save the QR code on your phone.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Event Planner <noreply@eventplanner.local>" . "\r\n";

    // Send email
    return mail($to, $subject, $message, $headers);
}

/**
 * Send QR codes to all attendees of an event
 * @param int $eventId Event ID
 * @param array $qrCodes Array of QR code data from generateEventQRCodes
 * @param array $eventDetails Event details
 * @return array Results of email sending
 */
function sendQRCodesToAttendees($eventId, $qrCodes, $eventDetails) {
    $results = [];

    foreach ($qrCodes as $qrData) {
        $success = sendQREmail(
            $qrData['attendee_email'],
            $eventDetails['title'],
            $qrData['qr_code_url'],
            $eventDetails
        );

        $results[] = [
            'email' => $qrData['attendee_email'],
            'success' => $success,
            'tracking_code' => $qrData['tracking_code']
        ];
    }

    return $results;
}
?>
