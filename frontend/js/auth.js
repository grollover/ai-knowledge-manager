import { apiRequest } from "./api.js";
import { loadDocuments } from "./documents.js";

const registerBtn = document.getElementById("registerBtn");
const loginBtn = document.getElementById("loginBtn");
const logoutBtn = document.getElementById("logoutBtn");

registerBtn.addEventListener("click", async () => {
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  if (!email || !password) return alert("Введите email и пароль");

  try {
    await apiRequest("auth", "/register", "POST", { email, password });
    alert("Регистрация успешна! Теперь войдите.");
  } catch (err) {
    alert("Ошибка: " + err.message);
  }
});

loginBtn.addEventListener("click", async () => {
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  if (!email || !password) return alert("Введите email и пароль");

  try {
    const data = await apiRequest("auth", "/login_check", "POST", { email, password });
    localStorage.setItem("token", data.token);
    showApp();
  } catch (err) {
    alert("Ошибка входа: " + err.message);
  }
});

logoutBtn.addEventListener("click", () => {
  localStorage.removeItem("token");
  showAuth();
});

export function showAuth() {
  document.getElementById("auth").style.display = "block";
  document.getElementById("upload").style.display = "none";
}

export function showApp() {
  document.getElementById("auth").style.display = "none";
  document.getElementById("upload").style.display = "block";
  loadDocuments();
}

// Автоинициализация
window.addEventListener("DOMContentLoaded", () => {
  const token = localStorage.getItem("token");
  if (token) {
    showApp();
  } else {
    showAuth();
  }
});
