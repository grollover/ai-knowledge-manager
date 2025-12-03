const registerBtn = document.getElementById("registerBtn");
const loginBtn = document.getElementById("loginBtn");

registerBtn.addEventListener("click", async () => {
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  await apiRequest("/register", "POST", { email, password });
  alert("Регистрация успешна, теперь войдите.");
});

loginBtn.addEventListener("click", async () => {
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  const data = await apiRequest("/login_check", "POST", { email, password });
  localStorage.setItem("token", data.token);
  alert("Добро пожаловать!");
  document.getElementById("auth").style.display = "none";
  document.getElementById("upload").style.display = "block";
  loadDocuments();
});
