<?php
// add_expense.php
require '../db_config.php';
header('Content-Type: application/json');

$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
$item_name = trim($_POST['item_name'] ?? '');
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$category = trim($_POST['category'] ?? 'Misc');

if (!$event_id || !$item_name || $amount <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid input']);
    exit;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO event_expenses (event_id, item_name, amount, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $event_id, $item_name, $amount, $category);
    $stmt->execute();
    $stmt->close();

    // update total_spent (increment)
    $stmt2 = $conn->prepare("UPDATE event_budgets SET total_spent = total_spent + ? WHERE event_id = ?");
    $stmt2->bind_param("di", $amount, $event_id);
    $stmt2->execute();
    $stmt2->close();

    // fetch updated budget
    $stmt3 = $conn->prepare("SELECT allocated_budget, total_spent FROM event_budgets WHERE event_id = ?");
    $stmt3->bind_param("i", $event_id);
    $stmt3->execute();
    $res = $stmt3->get_result();
    $bud = $res->fetch_assoc();
    $stmt3->close();

    if (!$bud) {
      // if budget row did not exist, create it (fallback)
      $stmt4 = $conn->prepare("INSERT INTO event_budgets (event_id, allocated_budget, total_spent) VALUES (?, 0, ?)");
      $stmt4->bind_param("id", $event_id, $amount);
      $stmt4->execute();
      $stmt4->close();

      $bud = ['allocated_budget'=>0, 'total_spent'=>$amount];
    }

    $conn->commit();

    $allocated = floatval($bud['allocated_budget']);
    $total_spent = floatval($bud['total_spent']);
    $remaining = $allocated - $total_spent;
    $pct = $allocated > 0 ? min(100, round(($total_spent / $allocated) * 100)) : ($total_spent > 0 ? 100 : 0);

    echo json_encode([
      'success' => true,
      'item_name' => $item_name,
      'amount' => $amount,
      'category' => $category,
      'created_at' => date('Y-m-d H:i:s'),
      'total_spent' => $total_spent,
      'remaining' => $remaining,
      'pct' => $pct
    ]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
}
