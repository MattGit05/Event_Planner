<?php
// recalc_budgets.php
require '../db_config.php';

// recompute for all events
$sql = "SELECT event_id, SUM(amount) AS s FROM event_expenses GROUP BY event_id";
$res = $conn->query($sql);

$conn->begin_transaction();
try {
  // set all totals to 0 first (optional)
  $conn->query("UPDATE event_budgets SET total_spent = 0");

  while ($r = $res->fetch_assoc()) {
    $ev = intval($r['event_id']);
    $s = floatval($r['s']);
    $stmt = $conn->prepare("UPDATE event_budgets SET total_spent = ? WHERE event_id = ?");
    $stmt->bind_param("di", $s, $ev);
    $stmt->execute();
    $stmt->close();
  }
  $conn->commit();
  echo "OK";
} catch(Exception $e){
  $conn->rollback();
  echo "ERR ".$e->getMessage();
}
