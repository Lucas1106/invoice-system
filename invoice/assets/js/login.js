async function login() {
    const email = document.querySelector("#email").value.trim();
    const password = document.querySelector("#password").value.trim();
    const errorBox = document.querySelector("#error");

    errorBox.innerText = "";

    if (!email || !password) {
        errorBox.innerText = "Preencha todos os campos.";
        return;
    }

    try {
        const res = await fetch("api/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password })
        });

        const data = await res.json();

        if (!data.ok) {
            errorBox.innerText = "Credenciais inválidas.";
            return;
        }

        localStorage.setItem("lcr_token", data.token);

        document.cookie = `lcr_token=${data.token}; path=/; SameSite=Lax`;

        window.location.href = "LCR/dashboard.php";

    } catch (err) {
        errorBox.innerText = "Erro ao conectar ao servidor.";
    }
}
