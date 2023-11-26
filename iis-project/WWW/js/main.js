document.addEventListener('DOMContentLoaded', () => {
    naja.initialize();

    document.getElementById('save_changes').addEventListener('click', () => {
        const systemId = document.getElementById('system_name').getAttribute('data-id');
        const systemName = document.getElementById('system_name').innerText;
        const systemDescription = document.getElementById('system_description').innerText;

        naja.makeRequest('POST', '/systeminfo', {
            systemId: systemId,
            systemName: systemName,
            systemDescription: systemDescription,
        }).then(response => {
            if (response.success) {
                // Handle successful update
            } else {
                // Handle error
            }
        }).catch(error => {
            console.error('Chyba AJAX požadavku: ', error);
            // Handle AJAX request error
        });
    });
});
