<?php
require 'phpqrcode/qrlib.php';

$qrContent = "TEST:123";
$qrFile = "qrcodes/test.png";

QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 5);

echo "QR generated";
?>
