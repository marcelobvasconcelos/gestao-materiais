<?php
require_once 'config.php';

try {
    $conn = getDbConnection();
    $sql = file_get_contents('scripts_sql/criar_tabela_locais_empresas.sql');
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "Tabela criada com sucesso!";
    } else {
        echo "Erro ao criar tabela: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
