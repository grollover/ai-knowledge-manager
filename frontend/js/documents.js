const uploadBtn = document.getElementById("uploadBtn");
const docList = document.getElementById("docList");

async function loadDocuments() {
    const token = localStorage.getItem("token");
    const docs = await apiRequest("/documents", "GET", null, token);

    docList.innerHTML = "";
    docs.forEach(doc => {
        const li = document.createElement("li");
        li.textContent = doc.title;
        docList.appendChild(li);
    });
}

uploadBtn.addEventListener("click", async () => {
    const title = document.getElementById("title").value;
    const file = document.getElementById("fileInput").files[0];
    const token = localStorage.getItem("token");

    const formData = new FormData();
    formData.append("title", title);
    formData.append("file", file);

    const response = await fetch(`${API_URL}/documents`, {
        method: "POST",
        headers: { "Authorization": `Bearer ${token}` },
        body: formData
    });

    if (!response.ok) {
        alert("Ошибка загрузки");
    } else {
        alert("Документ загружен");
        loadDocuments();
    }
});
