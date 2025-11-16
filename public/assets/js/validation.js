document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('flightSearchForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const origin = document.getElementById('origin').value;
        const destination = document.getElementById('destination').value;
        const date = document.getElementById('date').value;

        // Quick client-side checks
        if (!origin || !destination || !date) {
            alert('Please fill all required fields.');
            e.preventDefault();
            return;
        }

        if (origin === destination) {
            alert('Origin and destination cannot be the same.');
            e.preventDefault();
            return;
        }

        // Allow submission — results will be shown on results page
    });
});
