<?php
require_once("../../../conexao.php");
$tabela = 'produtos';

if (isset($_POST['ids']) && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    $ids_array = explode(',', $ids);
    
    try {
        $pdo->beginTransaction();
        
        foreach ($ids_array as $id) {
            $id = intval(trim($id));
            if ($id > 0) {
                // Excluir foto se existir
                $query = $pdo->prepare("SELECT foto FROM $tabela WHERE id = :id");
                $query->bindValue(':id', $id);
                $query->execute();
                $res = $query->fetch(PDO::FETCH_ASSOC);
                
                if ($res && $res['foto'] != 'sem-foto.jpg') {
                    $foto = $res['foto'];
                    if (file_exists("../../images/produtos/$foto")) {
                        unlink("../../images/produtos/$foto");
                    }
                }
                
                // Excluir produto
                $stmt = $pdo->prepare("DELETE FROM $tabela WHERE id = :id");
                $stmt->bindValue(':id', $id);
                $stmt->execute();
            }
        }
        
        $pdo->commit();
        echo 'Excluído com Sucesso';
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo 'Erro ao excluir: ' . $e->getMessage();
    }
} else {
    echo 'Nenhum produto selecionado!';
}
?>