<?php
use Monolog\Logger;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function isHtmlStatusCode($number) {
    return is_numeric($number) && $number >= 100 && $number <= 599;
}
function getPlan(PDO $pdo_conn, int $id, Logger $dbLogger) {
    try {
        $stmt = $pdo_conn->prepare("SELECT * FROM payment_plans WHERE id = ?;");
        $stmt->execute([$id]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$plan) {
            throw new Exception("Plan with ID: $id not found!", 404);
        }
        return $plan;
    } catch(PDOException $e) {
        $dbLogger->critical("Failed to get plan with id: $id", ["message", $e->getMessage()]);
    } catch (Exception $e) {
        if($e->getCode() !== 404) {
            $dbLogger->critical("Uncaught error getting plan with id: $id", ["message", $e->getMessage()]);
        }
        throw $e;
    }
}

function register_customer(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        // if customer by the same email exists, return that customer
        $sql = "SELECT * FROM customers WHERE email = ?";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([$details['email']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if($customer) {
            // check if customer is already enrolled to a plan
            $customerId = $customer['id'];
            $customer = $pdo_conn->query("SELECT * FROM customer_plans WHERE customer = $customerId;")->fetch(PDO::FETCH_ASSOC);
            if($customer) {
                throw new Exception("You are already enrolled to a plan, if you'd like to upgrade your plan, please do it within managex", 400);
            }
            return $customer;
        }
        $sql = 
            "INSERT INTO customers 
                (fullname, email, phone, address, city, state, postal_code)
            VALUES (?, ?, ?, ?, ?, ?, ?);";
        
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['fullname'],
            $details['email'],
            $details['phone'] ? $details['phone'] : null,
            $details['address'] ? $details['address'] : null,
            $details['city'] ? $details['city'] : null,
            $details['state'] ? $details['state'] : null,
            $details['postal_code'] ? $details['postal_code'] : null,
        ]);
        $customerId = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM customers WHERE id = $customerId;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error registering new customer", ['message' => $e->getMessage()]);
        throw $e;
    }
}

function enroll_customer(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $sql = 
            "INSERT INTO customer_plans (plan, customer, expiry, paid_amount)
            VALUES (?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['plan_id'],
            $details['customer_id'],
            !empty($details['expiry']) ? $details['expiry'] : NULL,
            $details['amount']
        ]);
        $id = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM customer_plans WHERE id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error enrolling customer to a plan", ['message' => $e->getMessage()]);
        throw $e;
    }
}