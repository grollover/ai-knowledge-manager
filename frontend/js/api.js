const API_URL = "http://ai-knowledge-manager.ddev.site/api";

async function apiRequest(endpoint, method = "GET", body = null, isJson = true) {
    const token = localStorage.getItem("token");
    const headers = {};

    if (isJson) headers["Content-Type"] = "application/json";
    if (token) headers["Authorization"] = `Bearer ${token}`;

    const options = { method, headers };

    if (body) options.body = isJson ? JSON.stringify(body) : body;

    const response = await fetch(`${API_URL}${endpoint}`, options);

    // Если токен просрочен или невалиден — разлогиниваем
    if (response.status === 401) {
        localStorage.removeItem("token");
        alert("Сессия истекла, войдите снова.");
        showAuth();
        throw new Error("Unauthorized");
    }

    if (!response.ok) {
        const text = await response.text();
        throw new Error(`Ошибка ${response.status}: ${text}`);
    }

    try {
        return await response.json();
    } catch {
        return {};
    }
}
