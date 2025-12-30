const togglePasswordButtons = document.querySelectorAll("[data-toggle-password]");

togglePasswordButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const targetId = button.getAttribute("data-toggle-password");
    const targetInput = document.getElementById(targetId);
    if (!targetInput) return;
    const isPassword = targetInput.type === "password";
    targetInput.type = isPassword ? "text" : "password";
    button.textContent = isPassword ? "Hide" : "Show";
  });
});

const editToggles = document.querySelectorAll("[data-edit-toggle]");

editToggles.forEach((toggle) => {
  toggle.addEventListener("click", () => {
    const targetId = toggle.getAttribute("data-edit-toggle");
    const form = document.getElementById(targetId);
    if (!form) return;
    const isEditable = form.classList.toggle("is-editing");
    form.querySelectorAll("input, textarea, select").forEach((field) => {
      if (field.dataset.locked === "true") {
        field.disabled = !isEditable;
      }
    });
  });
});

const uploadInputs = document.querySelectorAll("[data-preview-input]");

uploadInputs.forEach((input) => {
  input.addEventListener("change", (event) => {
    const targetId = input.getAttribute("data-preview-input");
    const preview = document.getElementById(targetId);
    if (!preview || !event.target.files.length) return;
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.onload = () => {
      preview.style.backgroundImage = `url(${reader.result})`;
      preview.textContent = "";
      preview.style.backgroundSize = "cover";
      preview.style.backgroundPosition = "center";
    };
    reader.readAsDataURL(file);
  });
});

const ratingButtons = document.querySelectorAll("[data-rating] button");

ratingButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const ratingGroup = button.closest("[data-rating]");
    const value = Number(button.getAttribute("data-value"));
    ratingGroup.querySelectorAll("button").forEach((btn) => {
      const btnValue = Number(btn.getAttribute("data-value"));
      btn.classList.toggle("active", btnValue <= value);
    });
  });
});
