<?php
session_start();
include 'db.php';

$movie = $_POST['movie'];
$seats = $_POST['seats'];

$stmt = $conn->prepare("DELETE FROM bookings WHERE movie_name = ? AND seats = ?");
$stmt->bind_param("ss", $movie, $seats);

if ($stmt->execute()) {
  echo "✅ Booking canceled!";
} else {
  echo "❌ Error: " . $stmt->error;
}
?>