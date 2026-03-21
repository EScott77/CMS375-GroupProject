// ========================= script.js =========================

const menuItems = [
  { name: "Burger", price: 10, category: "Main" },
  { name: "Pizza", price: 12, category: "Main" },
  { name: "Salad", price: 8, category: "Appetizer" },
  { name: "Pasta", price: 11, category: "Main" }
];

const menuList = document.getElementById("menuList");

function formatPrice(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

function displayMenu() {
  menuList.innerHTML = "";
  menuItems.forEach(item => {
    const div = document.createElement("div");
    div.className = "menu-item";
    div.setAttribute('role', 'listitem');
    div.innerHTML = `
      <h3>${item.name} <span class="price">${formatPrice(item.price)}</span></h3>
      <p>Delicious ${item.name.toLowerCase()} prepared fresh to order.</p>
      <span class="category">${item.category}</span>
    `;
    menuList.appendChild(div);
  });
}

displayMenu();

// RESERVATION FORM
const form = document.getElementById("reservationForm");
const confirmation = document.getElementById("confirmation");
const reserveBtn = document.getElementById("reserveBtn");

form.addEventListener("submit", function(e) {
  e.preventDefault();
  reserveBtn.disabled = true;

  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const date = document.getElementById("date").value;
  const time = document.getElementById("time").value;
  const guests = parseInt(document.getElementById("guests").value, 10) || 1;

  if (!name || !email || !date || !time || guests < 1) {
    confirmation.textContent = "Please complete all fields with valid values.";
    confirmation.style.color = "crimson";
    reserveBtn.disabled = false;
    return;
  }

  const reservation = {
    name, email, date, time, guests, createdAt: new Date().toISOString()
  };

  // store a simple history in localStorage
  const key = 'hb_reservations';
  const existing = JSON.parse(localStorage.getItem(key) || "[]");
  existing.push(reservation);
  localStorage.setItem(key, JSON.stringify(existing));

  confirmation.style.color = "";
  confirmation.textContent = `Reservation confirmed for ${name} on ${date} at ${time} for ${guests} guest(s). We'll email confirmation to ${email}.`;

  form.reset();
  setTimeout(() => {
    confirmation.textContent = "";
  }, 8000);

  reserveBtn.disabled = false;
});

// small helpers
document.getElementById('year').textContent = new Date().getFullYear();
