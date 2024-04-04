<?php
use Monolog\Logger;

function getDownloadStats(PDO $pdo_conn, Logger $dbLogger) {
    try {
        //code...
        $sql = "
           WITH total_downloads AS (
            SELECT COUNT(*) AS total_downloads FROM downloads
           ),
           paid_downloads AS (
            SELECT COUNT(*) AS paid_downloads FROM downloads WHERE is_paid = 1
           ),
           free_downloads AS (
            SELECT COUNT(*) AS free_downloads FROM downloads WHERE is_paid = 0
           ),
           completed_downloads AS (
            SELECT COUNT(*) AS completed_downloads FROM downloads WHERE `status` = 'COMPLETED'
           ),
           pending_downloads AS (
            SELECT COUNT(*) AS pending_downloads FROM downloads WHERE `status` = 'PENDING'
           )
           SELECT total_downloads, paid_downloads, free_downloads, completed_downloads, pending_downloads
           FROM total_downloads, paid_downloads, free_downloads, completed_downloads, pending_downloads;
        ";
        return $pdo_conn->query($sql)->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->error("Failed to get general download stats");
        throw $e;
    }
}

function getDownloadsPerDay(PDO $pdo_conn, array $filters, Logger $dbLogger) {
    $timeUnit = $filters['unit'] === 'day' ? 'DATE' : 'MONTH';
    $where = '';
    $params = [];
    if(!isset($filters['all-time'])) {
        if(isset($filters['from']) && !isset($filters['to'])) {
            $where = 'WHERE created_at >= :from';
            $params['from'] = $filters['from'];
        }
        if(!isset($filters['from']) && isset($filters['to'])) {
            $where = 'WHERE created_at <= :to';
            $params['to'] = $filters['to'];
        }
        if (isset($filters['from']) && isset($filters['to'])) {
            $where = 'WHERE created_at BETWEEN :from AND :to';
            $params['from'] = $filters['from'];
            $params['to'] = $filters['to'];
        }
    }
    try {
        $sql = 
            "
            SELECT 
                $timeUnit(created_at) as time_unit, 
                IF(is_paid, 'Paid', 'Free') AS `value`,
                count(*) as downloads
            FROM downloads 
            $where
            GROUP BY time_unit, `value`;
            ";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute($params);
        $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $report;
    } catch (PDOException $e) {
        $dbLogger->critical('Could not get downloads per day report', ['message' => $e->getMessage()]);
        throw new Error('Could not get downloads per day report: '.$e->getMessage(), 500);
    }
}

function getDownloadsPerBusinessType(PDO $pdo_conn, Logger $dbLogger) {
    try {
        $sql = "
            SELECT 
                b.`id`,
                b.`name` AS business_type,
                COUNT(d.`id`) AS downloads
            FROM downloads d
            INNER JOIN customers c ON d.`customer` = c.`id`
            INNER JOIN business_types b ON c.`business_type` = b.`id`
            GROUP BY b.`id`
            ORDER BY downloads DESC;
        ";
        return $pdo_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical('Could not get downloads per business type report', ['message' => $e->getMessage()]);
        throw new Error('Could not get downloads per business type report: '.$e->getMessage(), 500);
    }   
}