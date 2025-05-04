<?php
include 'db.php';

$movie = isset($_GET['movie']) ? $_GET['movie'] : '';

if (!$movie) {
    echo json_encode([]);
    exit;
}

// Query all bookings for the selected movie
$stmt = $conn->prepare("SELECT seats FROM bookings WHERE movie_name = ?");
$stmt->bind_param("s", $movie);
$stmt->execute();
$result = $stmt->get_result();

$bookedSeats = [];

while ($row = $result->fetch_assoc()) {
    // Explode each row's seat string (e.g., "1,2,3")
    $seats = explode(',', $row['seats']);
    
    // Sanitize and add to global array
    foreach ($seats as $seat) {
        $cleanedSeat = trim($seat);
        if (!empty($cleanedSeat)) {
            $bookedSeats[] = $cleanedSeat;
        }
    }
}

// Remove duplicates and reindex
$bookedSeats = array_values(array_unique($bookedSeats));

// Output JSON
echo json_encode($bookedSeats);
?>