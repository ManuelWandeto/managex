<?php 

function queryRow(
    PDO $conn, 
    string $queryName,
    string $sql,
    ...$params
) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

