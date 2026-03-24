const form = document.getElementById("reservationForm");
const confirmation = document.getElementById("confirmation");
const reserveBtn = document.getElementById("reserveBtn");

if (form && confirmation && reserveBtn) {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    reserveBtn.disabled = true;

    try {
      const response = await fetch("reserve.php", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest"
        },
        body: new FormData(form)
      });

      const data = await response.json();

      if (!data.ok) {
        throw new Error(data.error || "Unable to create reservation.");
      }

      confirmation.textContent = `${data.message} Reservation #${data.data.reservation_id}.`;
      confirmation.classList.remove("error-text");
      form.reset();
    } catch (error) {
      confirmation.textContent = error.message;
      confirmation.classList.add("error-text");
    } finally {
      reserveBtn.disabled = false;
    }
  });
}
