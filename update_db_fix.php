<?php
require_once 'config.php';

$conn = getDbConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM movimentacoes_saida LIKE 'local_origem_id'");
if ($check->num_rows > 0) {
    echo "Column local_origem_id already exists.\n";
} else {
    // Add column
    $sql = "ALTER TABLE movimentacoes_saida ADD COLUMN local_origem_id INT DEFAULT NULL AFTER empresa_solicitante_id";
    if ($conn->query($sql) === TRUE) {
        echo "Column local_origem_id added successfully.\n";
        
        // Add foreign key
        $sql_fk = "ALTER TABLE movimentacoes_saida ADD CONSTRAINT fk_mov_saida_local FOREIGN KEY (local_origem_id) REFERENCES locais_armazenamento(id)";
        if ($conn->query($sql_fk) === TRUE) {
            echo "Foreign key added successfully.\n";
        } else {
            echo "Error adding foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
}

$conn->close();
?>
