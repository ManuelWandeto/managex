<?php
use Monolog\Logger;
require_once(dirname(__FILE__) . "/../utils/currency_convert.php");
require_once(dirname(__FILE__) . "/../utils/random.php");
require_once(dirname(__FILE__) . "/../utils/load_env.php");
require_once(dirname(__FILE__) . "/../db/db.inc.php");
require_once(dirname(__FILE__) . "/../db/queries.inc.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function isHtmlStatusCode($number) {
    return is_numeric($number) && $number >= 100 && $number <= 599;
}
function getPlan(PDO $pdo_conn, int $id, Logger $dbLogger) {
    try {
        $stmt = $pdo_conn->prepare("SELECT * FROM plans WHERE id = ?;");
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

function getBusinessTypes(PDO $pdo_conn, Logger $dbLogger) {
    try {
        return $pdo_conn->query("SELECT * FROM business_types;")->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $dbLogger->critical("Error getting business types", ['message' => $e->getMessage()]);
        throw new Exception("Could not get business types!", 500);
    }
}
function getPlans(PDO $pdo_conn, Logger $dbLogger) {
    try {
        $sql = 
            "SELECT 
                id,
                name,
                plan_color,
                price
            FROM plans;";
        return $pdo_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error getting plans", ['message' => $e->getMessage()]);
        throw new Exception("Could not get pricing plans!", 500);
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
            // $customerId = $customer['id'];
            // $plan = $pdo_conn->query("SELECT * FROM orders WHERE customer = $customerId;")->fetch(PDO::FETCH_ASSOC);
            // if($plan) {
            //     $_SESSION['customer'] = $customer;
            //     throw new Exception("You are already enrolled to a plan, if you'd like to upgrade your plan, please do it within managex", 400);
            // }
            return $customer;
        }
        $sql = 
            "INSERT INTO customers 
                (business_name, business_type, email, phone)
            VALUES (?, ?, ?, ?);";
        
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['business_name'],
            $details['business_type'],
            $details['email'],
            !empty($details['phone']) ? $details['phone'] : null
        ]);
        $customerId = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM customers WHERE id = $customerId;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error registering new customer", ['message' => $e->getMessage()]);
        throw $e;
    }
}
function register_payment(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $sql = 
            "INSERT INTO payments (reference_code, client_email, client_phone, currency, amount_paid) VALUES (?, ?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['confirmation_code'],
            $details['email'],
            !empty($details['phone']) ? $details['phone'] : NULL,
            $details['currency'],
            $details['amount'],
        ]);
        $paymentId = $pdo_conn->lastInsertId();
        return $pdo_conn->query(
            "
            SELECT
                p.`payment_id`,
                c.`business_name` AS customer_name,
                c.`email` AS customer_mail,
                p.`reference_code` AS confirmation_code,
                p.`amount_paid` AS paid_amount,
                p.`currency`
            FROM payments p
            JOIN customers c ON p.`client_email` = c.`email`
            WHERE payment_id = $paymentId;
            "
        )->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error registering new payment", ['message' => $e->getMessage(), "email" => $details['email'], "amount" => $details['amount']]);
        throw $e;
    }
}
function register_download(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $sql =
        "INSERT INTO downloads (`ip`, `country_code`, `country_name`, `referrer`, `customer`, `plan`, `order`, `is_paid`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['ip'],
            $details['country_code'],
            $details['country_name'],
            !empty($details['referrer']) ? $details['referrer'] : NULL,
            $details['customer_id'],
            $details['plan_id'],
            !empty($details['order_id']) ? $details['order_id'] : NULL,
            // status => pending
            $details['is_paid']
        ]);
        $id = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM downloads WHERE id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->error("Error registering new download", ['message' => $e->getMessage()]);
        if($_ENV['APP_ENV'] === 'development') {
            throw $e;
        } else {
            return null;
        }
    }
}
function send_mail(?PDO $email_conn, array $details, Logger $dbLogger) {
    try {
        if(!$email_conn) {
            throw new Exception("Failed to connect to email db", 500);
        }
        $now = new Datetime();
        $sql = 
            "INSERT INTO direct_emails (
                creation_date,
                email_address,
                email_subject,
                email_body,
                confirm_send,
                email_sent
            ) VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $email_conn->prepare($sql);
        $stmt->execute([
            $now->format('Y-m-d H:i:s'),
            $details['to'],
            $details['about'],
            $details['message'],
            'yes',
            'no',
        ]);
        $id = $email_conn->lastInsertId();
        return $email_conn->query("SELECT * FROM direct_emails WHERE email_id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error sending mail", ['message' => $e->getMessage(), "to" => $details['to'], "about" => $details['about'], "email_content" => $details['message']]);
        if($_ENV['APP_ENV'] === 'development') {
            // throw $e;
            return null;
        } else {
            return null;
        }
    }
}
function create_order(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $sql = 
            "INSERT INTO orders (plan, customer, expiry, currency, invoice_amount, tracking_id )
            VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['plan_id'],
            $details['customer_id'],
            !empty($details['expiry']) ? $details['expiry'] : NULL,
            $details['currency'],
            $details['invoice_amount'],
            $details['tracking_id'],
        ]);
        $id = $pdo_conn->lastInsertId();
        if(count($details['discounts'])) {
            for ($i=0; $i < count($details['discounts']); $i++) { 
                $sql = 
                    "INSERT INTO order_discounts (`order`, `discount`) VALUES (?, ?);";
                $stmt = $pdo_conn->prepare($sql);
                $stmt->execute([$id, $details['discounts'][$i]['discount_id']]);
                $stmt->closeCursor();
            }
        }
        return $pdo_conn->query("SELECT * FROM orders WHERE id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error creating customer order", ['message' => $e->getMessage()]);
        throw $e;
    }
}
function create_payment_request(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $sql = 
            "INSERT INTO payment_requests (request_email, currency, amount, tracking_id)
            VALUES (?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['email'],
            $details['currency'],
            $details['amount'],
            getRandomString(40)
        ]);
        $id = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM payment_requests WHERE request_id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error creating payment request", ['message' => $e->getMessage()]);
        throw $e;
    }
}
function confirm_mpesa_payment(PDO $mpesa_conn, string $confirmation_code, Logger $dbLogger) {
    try {
        $stmt = $mpesa_conn->prepare("SELECT * FROM offline_mpesa_data WHERE client_id = ? AND confirmation_code = ?;");
        $stmt->execute([
            "b16m",
            $confirmation_code
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Failed to confirm mpesa payments", ["confirmation_code" => $confirmation_code, "message" => $e->getMessage()]);
        throw $e;
    }
}
function complete_order(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $completedOrder = null;
        $retries = 3;
        do {
            try {
                if(!$completedOrder) {
                    $stmt = $pdo_conn->prepare("UPDATE orders SET paid_amount = ?, confirmation_code = ?, download_id = ? WHERE tracking_id = ?;");
                    $stmt->execute([
                        !empty($details['paid_amount']) ? $details['paid_amount'] : NULL,
                        $details['confirmation_code'],
                        $details['download_id'],
                        $details['tracking_id']
                    ]);
                    $stmt = $pdo_conn->prepare(
                        "SELECT 
                            o.id as order_id,
                            o.invoice_amount,
                            o.paid_amount,
                            o.confirmation_code,
                            o.currency,
                            o.tracking_id,
                            c.id as customer_id,
                            c.business_name as customer_name,
                            c.email as customer_mail,
                            c.phone as customer_phone,
                            p.id as plan_id,
                            p.`name` as plan_name,
                            p.plan_color
                        FROM orders o
                        JOIN customers c ON c.id = o.customer
                        JOIN plans p ON p.id = o.plan
                        WHERE tracking_id = ?;"
                    );
                    $stmt->execute(([$details['tracking_id']]));
                    $completedOrder = $stmt->fetch(PDO::FETCH_ASSOC);

                    if(!$completedOrder) {
                        throw new Exception("UNCAUGHT error completing customer's order", 500);
                    }
                }
                return $completedOrder;
            } catch (Exception $e) {    
                $retries--;
                if(!$retries) {
                    throw $e;
                }
            }
        } while($retries > 0);
        
        return true;
    } catch (Exception $e) {
        $dbLogger->critical("Failed to complete customer's order", [
            "tracking_id" => $details['tracking_id'], 
            'message' => $e->getMessage()
        ]);
        throw $e;
    }
}
function complete_payment_request(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {

        $stmt = $pdo_conn->prepare("UPDATE payment_requests SET is_paid = ?, confirmation_code = ? WHERE tracking_id = ?;");
        $stmt->execute([
            !empty($details['is_paid']) ? $details['is_paid'] : 'no',
            $details['confirmation_code'],
            $details['tracking_id']
        ]);
        $stmt = $pdo_conn->prepare(
            "SELECT 
                c.`business_name` AS customer_name,
                c.`phone` AS customer_phone,
                pr.`request_email` AS customer_mail,
                pr.`amount` AS invoice_amount,
                pr.`currency`,
                pr.`confirmation_code`,
                pr.`tracking_id`
            FROM payment_requests pr
            JOIN customers c ON pr.`request_email` = c.`email`  
            WHERE tracking_id = ?;"
        );
        $stmt->execute(([$details['tracking_id']]));
        $completedRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$completedRequest) {
            throw new Exception("UNCAUGHT error completing payment request", 500);
        }
        return $completedRequest;
    } catch (Exception $e) {
        $dbLogger->critical("Failed to complete payment request", [
            "tracking_id" => $details['tracking_id'], 
            'message' => $e->getMessage()
        ]);
        throw $e;
    }
}
function create_discount(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $sql =
            "INSERT INTO discounts (code, description, fraction, expiry) VALUES (?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['code'],
            $details['description'],
            $details['fraction'],
            $details['expiry'],
        ]);
        $id = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM discounts WHERE id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->error("Failed to create refferral discount", ['customer' => $_SESSION['customer']['id'], 'plan' => $_SESSION['plan']['id'], 'message' => $e->getMessage()]);
        throw $e;
    }
}

function add_payment(PDO $pdo_conn, array $details, Logger $dbLogger) {
    try {
        $record = null;
        $stmt = $pdo_conn->prepare(
            "SELECT * FROM orders WHERE confirmation_code = ?;"
        );
        $stmt->execute([$details['confirmation_code']]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($record['paid_amount'])) {
            throw new Exception("Payment for this order has already been verified!", 400);
        }
        if(!$record) {
            $stmt = $pdo_conn->prepare("SELECT * FROM payment_requests WHERE confirmation_code = ?;");
            $stmt->execute([$details['confirmation_code']]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            if($record && strtolower($record['is_paid']) === 'yes') {
                throw new Exception("Payment for this request has already been verified!", 400);
            }
        }
        
        if(!$record) {
            throw new Exception("No order or payment request found for the given confirmation code", 404);
        }
        if(!empty($record['request_id'])) {
            $completedRequest = complete_payment_request($pdo_conn, [
                "is_paid" => 'Yes',
                "tracking_id" => $record['tracking_id'],
                "confirmation_code" => $record['confirmation_code']
            ], $dbLogger);
            if(!$completedRequest) {
                throw new Exception("UNCAUGHT error completing payment request");
            }
            return register_payment($pdo_conn, [
                "confirmation_code" => $details['confirmation_code'],
                "phone" => $completedRequest['customer_phone'],
                "email" => $record['request_email'],
                "currency" => $record['currency'],
                "amount" => $details['amount']
            ], $dbLogger);
            // add payment
        } else {
            $completedOrder = complete_order($pdo_conn, [
                "paid_amount" => $details['amount'],
                "tracking_id" => $record['tracking_id'],
                "confirmation_code" => $record['confirmation_code'],
                "download_id" => $record['download_id']
            ], $dbLogger);
            if(!$completedOrder) {
                throw new Exception("UNCAUGHT error completing order");
            }
            return register_payment($pdo_conn, [
                "confirmation_code" => $details['confirmation_code'],
                "email" => $completedOrder['customer_mail'],
                "phone" => $completedOrder['customer_phone'],
                "currency" => $completedOrder['currency'],
                "amount" => $completedOrder['paid_amount']
            ], $dbLogger);
        }
    } catch (Exception $e) {
        $dbLogger->critical("Failed to add payment", [
            "confirmation_code" => $details['confirmation_code'], 
            'message' => $e->getMessage()
        ]);
        throw $e;
    }
}

function get_pending_codes(PDO $pdo_conn, Logger $dbLogger) {
    try {
        $sql = 
            "
            SELECT 
                created_at,
                invoice_amount,  
                confirmation_code,
                paid_amount,
                NULL AS is_paid,
                currency
            FROM orders
            WHERE paid_amount IS NULL AND confirmation_code IS NOT NULL
            UNION
            SELECT 
                creation_date AS created_at,
                amount AS invoice_amount,
                confirmation_code,
                NULL AS paid_amount,
                is_paid,
                currency
            FROM payment_requests pr
            WHERE is_paid = 'no' AND confirmation_code IS NOT NULL;
            ";
        return $pdo_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->error("Failed to get unverified payments", [ 
            'message' => $e->getMessage()
        ]);
        throw $e;
    }
}