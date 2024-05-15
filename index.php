<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["createTable"])) {
        $tableName = $_POST["tableName"];
        $columnNames = $_POST["columnName"];
        $columnTypes = $_POST["columnType"];

        if (!empty($columnNames) && !empty($columnTypes)) {
            // Construir la consulta SQL para crear la tabla
            $sql = "CREATE TABLE $tableName (id SERIAL PRIMARY KEY, ";
            foreach ($columnNames as $index => $columnName) {
                $columnType = $columnTypes[$index];
                $sql .= "$columnName $columnType, ";
            }
            $sql = rtrim($sql, ", "); // Eliminar la coma y el espacio finales
            $sql .= ")";

            // Ejecutar la consulta SQL
            try {
                $pdo = new PDO("pgsql:host=localhost;dbname=usuaris", "postgres", "postgres");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec($sql);

                // Enviar respuesta de éxito
                echo "Table $tableName created successfully.";
            } catch (PDOException $e) {
                echo "Error creating table: " . $e->getMessage();
            }
        } else {
            echo "Please provide column names and types.";
        }
    } elseif (isset($_POST["readTable"])) {
        $tableName = $_POST["tableName"];

        if ($tableName) {
            try {
                $pdo = new PDO("pgsql:host=localhost;dbname=usuaris", "postgres", "postgres");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                if (isset($_POST["rowId"]) && $_POST["rowId"] !== 'all') {
                    $rowId = $_POST["rowId"];

                    // Consulta SQL para seleccionar una fila específica de la tabla
                    $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id = :id");
                    $stmt->bindParam(':id', $rowId);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Mostrar la fila encontrada
                    if ($row) {
                        echo '<h3>Row from table ' . $tableName . ':</h3>';
                        echo '<table border="1">';
                        echo '<tr>';
                        foreach ($row as $key => $value) {
                            echo '<th>' . $key . '</th>';
                        }
                        echo '</tr>';
                        echo '<tr>';
                        foreach ($row as $value) {
                            echo '<td>' . $value . '</td>';
                        }
                        echo '</tr>';
                        echo '</table>';
                    } else {
                        echo 'No row found in table ' . $tableName . ' with ID ' . $rowId;
                    }
                } else {
                    // Consulta SQL para seleccionar todas las filas de la tabla
                    $stmt = $pdo->query("SELECT * FROM $tableName");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Mostrar los resultados
                    if ($rows) {
                        echo '<h3>Rows from table ' . $tableName . ':</h3>';
                        echo '<table border="1">';
                        echo '<tr>';
                        foreach ($rows[0] as $key => $value) {
                            echo '<th>' . $key . '</th>';
                        }
                        echo '</tr>';
                        foreach ($rows as $row) {
                            echo '<tr>';
                            foreach ($row as $value) {
                                echo '<td>' . $value . '</td>';
                            }
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo 'No rows found in table ' . $tableName;
                    }
                }
            } catch (PDOException $e) {
                echo "Error reading table: " . $e->getMessage();
            }
        } else {
            echo "Please provide a table name.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelForm</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            text-align: center;
        }

        input[type="text"] {
            padding: 10px;
            margin-right: 10px;
        }

        button {
            padding: 10px 20px;
        }

        h2 {
            font-size: 60px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>CRUD</h2>
    <form method="post">
        <input type="text" name="userInput" placeholder="Enter command">
        <button type="submit">Submit</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["userInput"])) {
        $commandParts = explode(" ", $_POST["userInput"]);
        $action = $commandParts[0];
        $tableName = isset($commandParts[1]) ? $commandParts[1] : null;

        if ($action === 'create' && $tableName !== null) {
            echo '
                <h3>Create ' . $tableName . ':</h3>
                <form method="post">
                    <input type="hidden" name="createTable" value="1">
                    <input type="hidden" name="tableName" value="' . $tableName . '">
                    Table Name: ' . $tableName . '<br>
                    <div id="columnInputs">
                        Column Name: <input type="text" name="columnName[]" placeholder="Column Name">
                        Data Type: 
                        <select name="columnType[]">
                            <option value="VARCHAR">VARCHAR</option>
                            <option value="INT">INT</option>
                            <option value="TEXT">TEXT</option>
                            <!-- Add more options as needed -->
                        </select><br>
                    </div>
                    <button type="button" id="addColumnBtn">Add Column</button>
                    <button type="submit">Create Table</button>
                </form>
            ';
        } elseif ($action === 'read' && $tableName !== null) {
            $idOrAll = isset($commandParts[2]) ? $commandParts[2] : null;
            if ($idOrAll === 'all') {
                echo '
                    <form method="post">
                        <input type="hidden" name="readTable" value="1">
                        <input type="hidden" name="tableName" value="' . $tableName . '">
                        <button type="submit">Read all rows from table ' . $tableName . '</button>
                    </form>
                ';
            } elseif (is_numeric($idOrAll)) {
                $rowId = $idOrAll;
                echo '
                    <form method="post">
                        <input type="hidden" name="readTable" value="1">
                        <input type="hidden" name="tableName" value="' . $tableName . '">
                        <input type="hidden" name="rowId" value="' . $rowId . '">
                        <button type="submit">Read row with ID ' . $rowId . ' from table ' . $tableName . '</button>
                    </form>
                ';
            }
        }
    }
    ?>

    <script>
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
                <!-- Add more options as needed -->
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
    </script>
</body>
</html>
