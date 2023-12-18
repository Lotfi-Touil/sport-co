const test1 = document.querySelector('#loginbtn')
test1.addEventListener("submit", function (e) {
    console.log("la "+ this.action)
    e.preventDefault();

    var email = document.getElementById('inputEmail').value;
    var password = document.getElementById('inputPassword').value;
    

    var data = {
        username: email,
        password: password,
        
    };

    fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message);
                })
            }
            return response.json();
    })
    .then(data => {
       
        location.reload();
        // Redirigez l'utilisateur ou mettez à jour l'interface utilisateur ici
    })
        .catch(error => {
            const errorElement = document.getElementById('loginError');
            errorElement.textContent = error.message;
            errorElement.classList.remove('hidden'); // Assurez-vous qu'il est visible
        console.error('Erreur:', error);
    });
});

