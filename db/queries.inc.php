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
function getPlans(PDO $pdo_conn, string $currency, Logger $dbLogger) {
    try {
        $sql = 
            "SELECT 
                p.id,
                p.name,
                p.plan_color,
                CONCAT('[', GROUP_CONCAT( DISTINCT
                    JSON_OBJECT(pr.payment_frequency, pr.price)
                ), ']') AS pricing
            FROM plans p
            JOIN plan_pricing pr ON p.id = pr.plan
            GROUP BY p.id;";
        $plans =  $pdo_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $results = [];
        foreach ($plans as $plan) {
            $pricing = [];
            foreach (json_decode($plan['pricing']) as $price) {
                foreach ($price as $key => $value) {
                    $convertedPrice = convert_currrency($currency, $value, $dbLogger);
                    $pricing[] = [$key => $convertedPrice, 'localle_price' => format_money(
                            $currency === 'KES' ? 'KES' : 'USD', 
                            $currency === 'KES' ? 'en_KE' : 'en_US', 
                            $convertedPrice
                        )
                    ];
                }
            }
            $results[] = array_merge($plan, ['pricing' => $pricing]);
        }
        return $results;
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
            "INSERT INTO payments (client_email, client_phone, currency, amount_paid) VALUES (?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['email'],
            !empty($details['phone']) ? $details['phone'] : NULL,
            $details['currency'],
            $details['amount'],
        ]);
        $paymentId = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM payments WHERE payment_id = $paymentId")->fetch(PDO::FETCH_ASSOC);
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
            "INSERT INTO orders (plan, customer, expiry, currency, invoice_amount, merchant_ref, tracking_id )
            VALUES (?, ?, ?, ?, ?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['plan_id'],
            $details['customer_id'],
            !empty($details['expiry']) ? $details['expiry'] : NULL,
            $details['currency'],
            $details['invoice_amount'],
            $details['merchant_ref'],
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
            "INSERT INTO payment_requests (request_email, currency, amount)
            VALUES (?, ?, ?);";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([
            $details['email'],
            $details['currency'],
            $details['amount']
        ]);
        $id = $pdo_conn->lastInsertId();
        return $pdo_conn->query("SELECT * FROM payment_requests WHERE request_id = $id;")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbLogger->critical("Error creating payment request", ['message' => $e->getMessage()]);
        throw $e;
    }
}

function complete_order(PDO $pdo_conn, ?PDO $email_conn, array $details, Logger $dbLogger) {
    try {
        $completedOrder = null;
        $sentmail = null;
        $retries = 3;
        $success = false;
        do {
            try {
                $pdo_conn->beginTransaction();
                if(!$completedOrder) {
                    $stmt = $pdo_conn->prepare("UPDATE orders SET paid_amount = ?, download_id = ? WHERE merchant_ref = ? AND tracking_id = ?;");
                    $stmt->execute([
                        $details['amount'],
                        $details['download_id'],
                        $details['merchantRef'],
                        $details['trackingId']
                    ]);
                    $stmt = $pdo_conn->prepare(
                        "SELECT 
                            o.id as order_id,
                            o.paid_amount,
                            o.currency,
                            o.merchant_ref,
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
                        WHERE tracking_id = ? AND merchant_ref = ?;"
                    );
                    $stmt->execute(([$details['trackingId'], $details['merchantRef']]));
                    $completedOrder = $stmt->fetch(PDO::FETCH_ASSOC);

                    if(!$completedOrder) {
                        throw new Exception("UNCAUGHT error completing customer's order", 500);
                    }
                    if($completedOrder) {
                        register_payment($pdo_conn, [
                            "email" => $completedOrder['customer_mail'],
                            "phone" => $completedOrder['customer_phone'],
                            "currency" => $completedOrder['currency'],
                            "amount" => $completedOrder['paid_amount'],
                        ], $dbLogger);
                    }
                }
                // send confirmation mail
                if($completedOrder && !$sentmail) {
                    $confirmationMail = file_get_contents(__DIR__ . '/../email_templates/confirmation.html');
                    $parsedMail = str_replace([
                        "{customer_name}",
                        "{plan_name}",
                        "plan_color",
                        "{download_id}"
                    ], [
                        $completedOrder['customer_name'],
                        $completedOrder['plan_name'],
                        $completedOrder['plan_color'],
                        !empty($details['download_id']) ? $details['download_id'] : NULL,
                    ], $confirmationMail);

                    $sentmail = send_mail($email_conn, [
                        "to" => $completedOrder['customer_mail'],
                        "about" => "Order Confirmation",
                        "message" => $parsedMail
                    ], $dbLogger);
                }
                
                $success = true;
                $pdo_conn->commit();
            } catch (Exception $e) {  
                $pdo_conn->rollBack();  
                $retries--;
                if(!$retries) {
                    throw $e;
                }
            }
        } while($retries > 0 && !$success);
        
        return true;
    } catch (Exception $e) {
        $dbLogger->critical("Failed to complete customer's order", [
            'merchant_ref' => $details['merchantRef'],
            "tracking_id" => $details['trackingId'], 
            'message' => $e->getMessage()
        ]);
        throw $e;
    }
}
function complete_payment_request(PDO $pdo_conn, ?PDO $email_conn, array $details, Logger $dbLogger) {
    try {
        $completedRequest = null;
        $retries = 3;
        $success = false;
        do {
            try {
                $pdo_conn->beginTransaction();
                if(!$completedRequest) {
                    
                    $stmt = $pdo_conn->prepare("SELECT * FROM payment_requests WHERE tracking_id = ? AND merchant_ref = ?;");
                    $stmt->execute([
                        $details['trackingId'],
                        $details['merchantRef']
                    ]);
                    $record = $stmt->fetch(PDO::FETCH_ASSOC);
                    if(!$record) {
                        throw new Exception("No record found for the given tracking/merchant ref", 404);
                    }
                    $stmt = $pdo_conn->prepare("UPDATE payment_requests SET is_paid = ? WHERE merchant_ref = ? AND tracking_id = ?;");
                    $stmt->execute([
                        'Yes',
                        $details['merchantRef'],
                        $details['trackingId']
                    ]);
                    $stmt = $pdo_conn->prepare(
                        "SELECT * FROM payment_requests WHERE merchant_ref = ? AND tracking_id = ?;"
                    );
                    $stmt->execute(([$details['merchantRef'], $details['trackingId']]));
                    $completedRequest = $stmt->fetch(PDO::FETCH_ASSOC);

                    if(!$completedRequest) {
                        throw new Exception("UNCAUGHT error completing payment request", 500);
                    }
                    register_payment($pdo_conn, [
                        "email" => $completedRequest['request_email'],
                        "currency" => $completedRequest['currency'],
                        "amount" => $completedRequest['amount'],
                    ], $dbLogger);
                }
                // TODO: send confirmation mail
                $success = true;
                $pdo_conn->commit();
            } catch (Exception $e) {
                if($e->getCode() == 400) {
                    throw $e;
                }
                $pdo_conn->rollBack();  
                $retries--;
                if(!$retries) {
                    throw $e;
                }
            }
        } while($retries > 0 && !$success);
        
        return true;
    } catch (Exception $e) {
        $dbLogger->critical("Failed to complete payment request", [
            'merchant_ref' => $details['merchantRef'],
            "tracking_id" => $details['trackingId'], 
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