import { apiRequest } from "./api.js";

const uploadBtn = document.getElementById("uploadBtn");
const docList = document.getElementById("docList");

export async function loadDocuments() {
    try {
        const docs = await apiRequest("docs", "/documents", "GET");
        docList.innerHTML = "";
        docs.forEach(doc => {
            const li = document.createElement("li");
            li.textContent = `${doc.title} (${new Date(doc.createdAt).toLocaleString("ru-RU", { hour12: false })})`;
            docList.appendChild(li);
        });
    } catch (err) {
        console.error("Ошибка загрузки документов:", err);
    }
}

uploadBtn.addEventListener("click", async () => {
    const title = document.getElementById("title").value;
    const file = document.getElementById("fileInput").files[0];
    if (!title || !file) return alert("Введите название и выберите файл");

    const formData = new FormData();
    formData.append("title", title);
    formData.append("file", file);

    try {
        await apiRequest("docs", "/documents", "POST", formData, false);
        alert("Документ загружен!");
        loadDocuments();
    } catch (err) {
        alert("Ошибка: " + err.message);
    }
});
