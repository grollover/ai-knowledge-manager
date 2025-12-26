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
  document.getElementById('ask').style.display = 'none';
}

export function showApp() {
  document.getElementById("auth").style.display = "none";
  document.getElementById("upload").style.display = "block";
  document.getElementById('ask').style.display = 'block';
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


// ======== AI-вопросы ========

const askSection = document.getElementById('ask');
const askBtn = document.getElementById('askBtn');
const questionInput = document.getElementById('question');
const answerBox = document.getElementById('answer');

if (askBtn) {
  askBtn.addEventListener('click', async () => {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const question = questionInput.value.trim();

    if (!token) {
      alert('Сначала войдите в систему');
      return;
    }
    if (!question) {
      alert('Введите вопрос');
      return;
    }

    askBtn.disabled = true;
    answerBox.textContent = 'Думаю...';

    try {
      const response = await fetch('http://ai-service.ddev.site/api/query', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ question, userId: user.id })
      });

      const data = await response.json();

      if (response.ok) {
        answerBox.textContent = data.answer || '[Ответ не получен]';
      } else {
        answerBox.textContent = 'Ошибка: ' + (data.error || response.statusText);
      }
    } catch (err) {
      answerBox.textContent = 'Ошибка подключения: ' + err.message;
    } finally {
      askBtn.disabled = false;
    }
  });
}
