<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// Fetch all events including description
$query = "SELECT id, title, category, date, time, description 
          FROM events 
          ORDER BY date ASC";

$result = $conn->query($query);

$total_events = 0;
$upcoming = 0;
$due_today = 0;
$categories = [];
$upcoming_events = [];

$today = date('Y-m-d');

while ($row = $result->fetch_assoc()) {

    $total_events++;

    $event_date = date('Y-m-d', strtotime($row['date']));

    // Count due today
    if ($event_date == $today) {
        $due_today++;
    }

    // Count upcoming
    elseif ($event_date > $today) {
        $upcoming++;

        // Store first 5 upcoming events
        if (count($upcoming_events) < 5) {
            $upcoming_events[] = [
                "title" => $row['title'],
                "date" => $row['date'],
                "time" => $row['time'],
                "category" => $row['category'],
                "description" => $row['description'] ?: ""
            ];
        }
    }

    // Count categories
    $cat = !empty($row['category']) ? $row['category'] : "Others";

    if (!isset($categories[$cat])) {
        $categories[$cat] = 0;
    }
    $categories[$cat]++;
}

// Return JSON response
echo json_encode([
    "success" => true,
    "total_events" => $total_events,
    "upcoming" => $upcoming,
    "due_today" => $due_today,
    "categories" => $categories,
    "upcoming_events" => $upcoming_events
]);
?>
