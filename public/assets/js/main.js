/* Show Flight Results */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('flightSearchForm');
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');

    // Dummy data
    const mockFlights = [
        {
            origin: 'Nairobi',
            destination: 'Mombasa',
            date: '2025-06-12',
            airline: 'Kenya Airways',
            time: '10:30 AM',
        },
        {
            origin: 'Nairobi',
            destination: 'Mombasa',
            date: '2025-06-12',
            airline: 'Fly540',
            time: '1:15 PM',
        },
    ];

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const origin = document.getElementById('origin').value.trim();
        const destination = document.getElementById('destination').value.trim();
        const date = document.getElementById('flightDate').value;

        if (!origin || !destination || !date) return;

        const matches = mockFlights.filter(
            (f) =>
                f.origin.toLowerCase() === origin.toLowerCase() &&
                f.destination.toLowerCase() === destination.toLowerCase() &&
                f.date === date,
        );

        resultsContainer.innerHTML = '';

        if (matches.length === 0) {
            resultsContainer.innerHTML = `<p>No flights found.</p>`;
        } else {
            matches.forEach((f) => {
                resultsContainer.innerHTML += `
          <div class="flight-card">
            <div class="info">
              <p><strong>${f.origin} → ${f.destination}</strong></p>
              <p>${f.airline} • ${f.time}</p>
              <p>${f.date}</p>
            </div>
            <button class="book-btn" onclick="goToBooking()">Book</button>
          </div>`;
            });
        }

        resultsSection.classList.remove('hidden');
    });
});

function goToBooking() {
    window.location.href = 'booking.php'; // Stub
}
