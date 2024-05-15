document.getElementById('addColumnBtn').addEventListener('click', function() {
    var columnInputsDiv = document.getElementById('columnInputs');

    var newColumnNameInput = document.createElement('input');
    newColumnNameInput.type = 'text';
    newColumnNameInput.name = 'columnName[]';
    newColumnNameInput.placeholder = 'Column Name';
    columnInputsDiv.appendChild(newColumnNameInput);
    
    var newColumnTypeInput = document.createElement('select');
    newColumnTypeInput.name = 'columnType[]';
    newColumnTypeInput.innerHTML = `
        <option value="VARCHAR">VARCHAR</option>
        <option value="INT">INT</option>
        <option value="TEXT">TEXT</option>
    `;
    columnInputsDiv.appendChild(newColumnTypeInput);
    
    columnInputsDiv.appendChild(document.createElement('br'));
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault();
        var userInput = document.querySelector('input[name="userInput"]').value.trim();
        var commandParts = userInput.split(" ");
        var action = commandParts[0];
        var tableName = commandParts[1];
        var id = commandParts[2];

        if (action === 'read' && id === 'all') {
            // Envía una solicitud POST al servidor para leer todas las filas de la tabla
            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    // Inserta la respuesta en el DOM
                    var responseDiv = document.createElement('div');
                    responseDiv.innerHTML = xhr.responseText;
                    document.body.appendChild(responseDiv);
                } else {
                    console.error('Error al procesar la solicitud.');
                }
            };
            xhr.onerror = function() {
                console.error('Error al realizar la solicitud.');
            };
            xhr.send('readTable=1&tableName=' + tableName);
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form[name="updateRowForm"]').addEventListener('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);

        // Envía una solicitud POST al servidor para actualizar la fila en la tabla
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // Inserta la respuesta en el DOM
                var responseDiv = document.createElement('div');
                responseDiv.innerHTML = xhr.responseText;
                document.body.appendChild(responseDiv);
            } else {
                console.error('Error al procesar la solicitud.');
            }
        };
        xhr.onerror = function() {
            console.error('Error al realizar la solicitud.');
        };
        xhr.send(formData);
    });
});


document.getElementById('confirmDelete').addEventListener('click', function() {
    var deleteForm = document.getElementById('deleteForm');
    var tableName = deleteForm.querySelector('input[name="tableName"]').value;
    var deleteId = deleteForm.querySelector('input[name="deleteId"]').value;

    if (confirm("Are you sure you want to delete row with ID " + deleteId + " from table " + tableName + "?")) {
        // Si se confirma la eliminación, enviar el formulario
        deleteForm.submit();
    } else {
        // Si se cancela la eliminación, no hacer nada
        return;
    }
});

