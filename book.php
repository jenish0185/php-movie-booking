<?php
include 'db.php';

// Set content type for HTML output
header("Content-Type: text/html; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $movie = isset($_POST['movie_name']) ? $conn->real_escape_string(trim($_POST['movie_name'])) : '';
    $seats = isset($_POST['selected_seats']) ? $conn->real_escape_string(trim($_POST['selected_seats'])) : '';

    if (empty($movie) || empty($seats)) {
      die("🚫 Missing data! Either movie or seats were not sent.");
    }

    // Validate input
    if (empty($movie) || empty($seats)) {
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Booking Failed</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .error, .confirmation {
                    max-width: 500px;
                    margin: 60px auto;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    background-color: #ffe6e6;
                    color: red;
                    text-align: center;
                }
                .confirmation {
                    background-color: #e6f9e6;
                    color: green;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
                .back-btn:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="error">
                <h2>⚠️ Missing Data</h2>
                <p>Movie or seat information was not received.</p>
                <a href="index.html" class="back-btn">Go Back</a>
            </div>
        </body>
        </html>';
        exit;
    }

    // Optional: Prevent duplicate bookings
    $checkStmt = $conn->prepare("SELECT seats FROM bookings WHERE movie_name = ?");
    $checkStmt->bind_param("s", $movie);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    $existingSeats = [];

    while ($row = $checkResult->fetch_assoc()) {
        $existingSeats = array_merge($existingSeats, explode(',', $row['seats']));
    }

    $newSeats = array_map('trim', explode(',', $seats));
    $duplicates = array_intersect($newSeats, $existingSeats);

    if (!empty($duplicates)) {
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Duplicate Booking</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .error, .confirmation {
                    max-width: 500px;
                    margin: 60px auto;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    background-color: #ffe6e6;
                    color: red;
                    text-align: center;
                }
                .confirmation {
                    background-color: #e6f9e6;
                    color: green;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
                .back-btn:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="error">
                <h2>🚫 Duplicate Booking Detected</h2>
                <p>The following seats are already booked:</p>
                <strong>' . htmlspecialchars(implode(', ', $duplicates)) . '</strong>
                <br><br>
                <a href="javascript:history.back()" class="back-btn">Back to Seat Selection</a>
            </div>
        </body>
        </html>';
        exit;
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO bookings (movie_name, seats) VALUES (?, ?)");
    $stmt->bind_param("ss", $movie, $seats);

    if ($stmt->execute()) {
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Booking Confirmed</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .confirmation, .error {
                    max-width: 500px;
                    margin: 60px auto;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .confirmation {
                    background-color: #e6f9e6;
                    color: green;
                }
                .error {
                    background-color: #ffe6e6;
                    color: red;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
                .back-btn:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="confirmation">
                <h2>✅ Booking Confirmed!</h2>
                <p><strong>Movie:</strong> ' . htmlspecialchars($movie) . '</p>
                <p><strong>Seats Booked:</strong> ' . htmlspecialchars($seats) . '</p>
                <a href="index.html" class="back-btn">Book Another Show</a>

                <script>
                  localStorage.removeItem("selectedMovie");
                  setTimeout(() => {
                    window.location.href = "index.html";
                  }, 5000);
                </script>
            </div>
        </body>
        </html>';
    } else {
        error_log("DB Error: " . $stmt->error . " | Movie: $movie | Seats: $seats");

        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Error</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .error, .confirmation {
                    max-width: 500px;
                    margin: 60px auto;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .confirmation {
                    background-color: #e6f9e6;
                    color: green;
                }
                .error {
                    background-color: #ffe6e6;
                    color: red;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
                .back-btn:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="error">
                <h2>❌ Error Processing Booking</h2>
                <p>' . htmlspecialchars($stmt->error) . '</p>
                <p><strong>Tried to save:</strong></p>
                <ul style="list-style: none; padding: 0;">
                    <li>🎬 Movie: ' . htmlspecialchars($movie) . '</li>
                    <li>💺 Seats: ' . htmlspecialchars($seats) . '</li>
                </ul>
                <a href="index.html" class="back-btn">Try Again</a>
            </div>
        </body>
        </html>';
    }

    $stmt->close();
} else {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invalid Request</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .error, .confirmation {
                max-width: 500px;
                margin: 60px auto;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            .confirmation {
                background-color: #e6f9e6;
                color: green;
            }
            .error {
                background-color: #ffe6e6;
                color: red;
            }
            .back-btn {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
            }
            .back-btn:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>🚫 Invalid Request</h2>
            <p>This page should be accessed via form submission only.</p>
            <a href="index.html" class="back-btn">Back to Home</a>
        </div>
    </body>
    </html>';
}

$conn->close();
?>