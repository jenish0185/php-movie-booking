let selectedSeats = [];
let pricePerSeat = 150;

// Get movie name from localStorage
const movieName = localStorage.getItem("selectedMovie") || "Unknown Movie";

// Set hidden input field for movie name (if exists)
const movieInput = document.getElementById("movieNameInput");
if (movieInput) {
  movieInput.value = movieName;
} else {
  console.error("❌ #movieNameInput not found. Make sure it's in your HTML.");
}

// Generate seat layout
function generateSeats(bookedSeats = []) {
  const seatContainer = document.getElementById("seatsContainer");
  if (!seatContainer) {
    console.error("❌ #seatsContainer not found. Check your HTML.");
    return;
  }

  seatContainer.innerHTML = "";

  for (let i = 1; i <= 60; i++) {
    const seat = document.createElement("div");
    seat.classList.add("seat");

    if (bookedSeats.includes(i.toString())) {
      // Already booked
      seat.classList.add("booked");
      seat.title = "Already Booked";
    } else {
      // Available
      seat.dataset.seat = i;
      seat.addEventListener("click", () => toggleSeat(seat));
    }

    seatContainer.appendChild(seat);
  }
}

// Toggle seat selection
function toggleSeat(seat) {
  const seatNum = seat.dataset.seat;
  if (seat.classList.contains("selected")) {
    seat.classList.remove("selected");
    selectedSeats = selectedSeats.filter(s => s !== seatNum);
  } else {
    seat.classList.add("selected");
    selectedSeats.push(seatNum);
  }
  updatePrice();
}

// Update total price and hidden input
function updatePrice() {
  const totalPriceEl = document.getElementById("totalPrice");
  const selectedSeatsInput = document.getElementById("selectedSeatsInput");

  if (totalPriceEl) {
    totalPriceEl.textContent = selectedSeats.length * pricePerSeat;
  }

  if (selectedSeatsInput) {
    selectedSeatsInput.value = selectedSeats.join(",");
  }
}

// Validate and log data before submitting
document.querySelector("form")?.addEventListener("submit", function (e) {
  const movieNameValue = document.getElementById("movieNameInput")?.value.trim();
  const seatsValue = document.getElementById("selectedSeatsInput")?.value.trim();

  if (!movieNameValue || !seatsValue) {
    e.preventDefault(); // Stop form submission
    alert("⚠️ Missing Data: Please make sure you've selected a movie and at least one seat.");
    console.warn("🚫 Form submission blocked due to missing data:", {
      movie: movieNameValue,
      seats: seatsValue,
    });
  } else {
    console.log("✅ Submitting booking:", {
      movie: movieNameValue,
      seats: seatsValue,
    });
  }
});

// Load already booked seats from database
if (movieName && movieName !== "Unknown Movie") {
  fetch(`get_booked_seats.php?movie=${encodeURIComponent(movieName)}&_=${Date.now()}`)
    .then((res) => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then((bookedSeats) => {
      generateSeats(bookedSeats);
    })
    .catch((err) => {
      console.error("Error fetching booked seats:", err);
      generateSeats(); // fallback
    });
} else {
  console.warn("⚠️ No valid movie name found. Using fallback.");
  generateSeats(); // fallback if no movie is selected
}