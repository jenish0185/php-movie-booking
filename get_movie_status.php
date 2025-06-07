<?php
include 'db.php';

header("Content-Type: application/json");

$movies = ['Avengers: Endgame', 'Joker', 'Inception'];
$response = [];

foreach ($movies as $movie) {
    // Get all booked seats for this movie
    $stmt = $conn->prepare("SELECT seats FROM bookings WHERE movie_name = ?");
    $stmt->bind_param("s", $movie);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookedSeats = [];
    while ($row = $result->fetch_assoc()) {
        $bookedSeats = array_merge($bookedSeats, explode(',', $row['seats']));
    }

    $totalBooked = count(array_filter($bookedSeats)); // Remove empty strings
    $totalSeats = 60; // Total seats in theater

    $response[$movie] = [
        'status' => ($totalBooked >= $totalSeats) ? 'sold_out' : 'available',
        'booked_seats_count' => $totalBooked,
        'total_seats' => $totalSeats
    ];
}

echo json_encode($response);
?>