const menuList = document.getElementById("menuList");
const filterPills = document.querySelectorAll(".filter-pill");
const form = document.getElementById("reservationForm");
const confirmation = document.getElementById("confirmation");
const reserveBtn = document.getElementById("reserveBtn");
const year = document.getElementById("year");
const menuItemSelect = document.getElementById("menu_item_id");
const menuNameInput = document.getElementById("menu_name");
const menuDescriptionInput = document.getElementById("menu_description");
const menuPriceInput = document.getElementById("menu_price");
const menuCategorySelect = document.getElementById("menu_category");
const menuStatusSelect = document.getElementById("menu_status");

filterPills.forEach((pill) => {
  pill.addEventListener("click", () => {
    filterPills.forEach((button) => button.classList.remove("active"));
    pill.classList.add("active");

    if (!menuList) {
      return;
    }

    const filter = pill.dataset.filter || "all";
    const items = menuList.querySelectorAll("[data-category]");

    items.forEach((item) => {
      const isVisible = filter === "all" || item.dataset.category === filter;
      item.style.display = isVisible ? "" : "none";
    });
  });
});

if (year) {
  year.textContent = new Date().getFullYear();
}

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

if (
  menuItemSelect &&
  menuNameInput &&
  menuDescriptionInput &&
  menuPriceInput &&
  menuCategorySelect &&
  menuStatusSelect
) {
  menuItemSelect.addEventListener("change", () => {
    const selectedOption = menuItemSelect.options[menuItemSelect.selectedIndex];

    if (!selectedOption || selectedOption.value === "0") {
      menuNameInput.value = "";
      menuDescriptionInput.value = "";
      menuPriceInput.value = "";
      menuCategorySelect.value = "";
      menuStatusSelect.value = "available";
      return;
    }

    menuNameInput.value = selectedOption.dataset.name || "";
    menuDescriptionInput.value = selectedOption.dataset.description || "";
    menuPriceInput.value = selectedOption.dataset.price || "";
    menuCategorySelect.value = selectedOption.dataset.category || "";
    menuStatusSelect.value = selectedOption.dataset.status || "available";
  });
}
