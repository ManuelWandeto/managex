<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(dirname(__FILE__) .'/../db/db.inc.php');
require_once(dirname(__FILE__) .'/../db/queries.inc.php');
require_once(dirname(__FILE__) .'/../utils/logger.php');
require_once(dirname(__FILE__) .'/../utils/respond.php');

$apiLogger->info('Register free trial customer request');
function get_plan(array $plans, int $plan_id) {
    $filteredPlan = array_filter($_SESSION['plans'], function ($plan) use($plan_id) {
        return $plan['id'] == $plan_id;
    });
    return array_values($filteredPlan)[0];
}
$plan = get_plan($_SESSION['plans'], $_POST['plan']);

try {
    $retries = 3;
    $success = false;
    $download = null;
    $sentmail = null;
    do {
        try {
            $pdo_conn->beginTransaction();

            $customer = register_customer($pdo_conn, $_POST, $dbLogger);
            // register download
            $ip = $_SESSION['localle']['ip'];
            $download = register_download($pdo_conn, [
                "ip" => $ip,
                "country_code" => $_SESSION['localle']['country']['iso_code'],
                "country_name" => $_SESSION['localle']['country']['name'],
                "referrer" => !empty($_SESSION['referer']) ? $_SESSION['referer'] : NULL,
                "customer_id" => $customer['id'],
                "plan_id" => $plan['id'],
                "is_paid" => false,
            ], $dbLogger);
            
            create_payment_request($pdo_conn, [
                "email" => $customer['email'],
                "currency" => $_SESSION['currency'],
                "amount" => (float)$plan["pricing"][0]['ONETIME'] * 0.86,
            ], $dbLogger);
            
            // send mail
            if($download && !$sentmail) {
                $confirmationMail = file_get_contents('../email_templates/trial_download.html');
                $parsedMail = str_replace([
                    "{customer_name}",
                    "plan_color",
                    "{download_id}",
                    "{customer_mail}",
                    "{current_date}"
                ], [
                    $customer['business_name'],
                    $plan['plan_color'],
                    !empty($download) ? $download['id'] : NULL,
                    $customer['email'],
                    date("d/m/Y")
                ], $confirmationMail);
            
                $sentmail = send_mail($email_conn, [
                    "to" => $customer['email'],
                    "about" => "Download Managex Trial Version",
                    "message" => $parsedMail
                ], $dbLogger);
            }

            $pdo_conn->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo_conn->rollBack();
            $retries--;
            if(!$retries) {
                throw $e;
            }
        }
    } while ($retries && !$success);
    
    echo json_encode(array_merge($customer, ['download_id' => $download['id']]));
} catch (PDOException $e) {
    $dbLogger->critical("Error registering free trial customer", ['message' => $e->getMessage()]);
    respondWith(500, "Internal error occured, please try again");
} catch(Exception $e) {
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}