import { AUTH_API, DOCS_API } from "./config.js";

// Универсальный запрос к API
export async function apiRequest(service, endpoint, method = "GET", body = null, isJson = true) {
    const baseURL = service === "auth" ? AUTH_API : DOCS_API;
    const url = `${baseURL}${endpoint}`;
    const token = localStorage.getItem("token");

    const headers = {};
    if (isJson) headers["Content-Type"] = "application/json";
    if (token && service === "docs") headers["Authorization"] = `Bearer ${token}`;

    const options = { method, headers };
    if (body) options.body = isJson ? JSON.stringify(body) : body;

    const response = await fetch(url, options);

    // Если токен невалиден — выходим из сессии
    if (response.status === 401) {
        localStorage.removeItem("token");
        alert("Сессия истекла. Войдите заново.");
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
