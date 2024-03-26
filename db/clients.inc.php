<?php 
use Monolog\Logger;
require_once('../../utils/constants.php');
require_once('utils.inc.php');
function addClient(PDO $conn, array $client, Logger $logger) {
    $logo_path = null;
    $uploadedImages = [];
    $clientsTable = CLIENTS_TABLE;
    try {
        $logo_path = uploadLogo($client['client_name']);
    } catch (Exception $e) {
        $logger->withName('uploads')->error('Error uploading client logo', ['message' => $e->getMessage()]);
        throw $e;
    }
    try {
        $uploadedImages = uploadImages($client['client_name']);
    } catch (Exception $e) {
        $logger->withName('uploads')->error('Error uploading client images', ['message' => $e->getMessage()]);
    }
    $clientsTable = CLIENTS_TABLE;
    try {
        $record = queryRow($conn, 'Client exists', "SELECT * FROM $clientsTable WHERE `name` = ?;", $client["client_name"]);
        if($record) {
            throw new Exception("Client with name {$client['client_name']} already exists!", 400);
        }
        $sql = "INSERT INTO $clientsTable (`name`, `testimonial`, `logo`, `installation_year`, `images`) VALUES (?, ?, ?, ?, ?);";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $client['client_name'],
            $client['testimonial'],
            $logo_path,
            strlen($client['installation_year']) ? $client['installation_year'] : null,
            count($uploadedImages) ? json_encode($uploadedImages) : null
        ]);
        $id = $conn->lastInsertId();
        $newClient = queryRow($conn, "Get new client", "SELECT * FROM $clientsTable WHERE id = ?;", $id);
        if(!$newClient) {
            throw new Exception('Could not get new client', 500);
        }
        $newClient['images'] = json_decode($newClient['images']);
        return $newClient;
    } catch (Exception $e) {
        $logger->error(`Error adding new client`, ['message'=>$e->getMessage()]);
        throw $e;
    }
}

function getClients(PDO $conn, Logger $logger) {
    $clientsTable = CLIENTS_TABLE;
    try {
        $results = $conn->query("SELECT * FROM $clientsTable;")->fetchAll(PDO::FETCH_ASSOC);
        $clients = [];
        if(count($results)) {
            foreach ($results as $row) {
                if($row['images']) {
                    $row['images'] = json_decode($row['images']);
                }
                $clients[] = $row;
            }
        }
        return $clients;
    } catch (Exception $e) {
        $logger->critical('Error getting clients', ['message'=>$e->getMessage()]);
        throw $e;
    }
}

function handleUploadError($file, $error) {
    switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
            throw new Exception("$file exceeds max allowed size", 400);

        case UPLOAD_ERR_PARTIAL:
            throw new Exception("$file was only partially uploaded", 500);

        case UPLOAD_ERR_NO_TMP_DIR:
            throw new Exception("$file missing a temporary folder", 500);

        case UPLOAD_ERR_NO_FILE:
            throw new Exception("$file was not uploaded", 500);
            
        case UPLOAD_ERR_CANT_WRITE:
            throw new Exception("Failed to write $file to disk", 500);
    
        default:
            throw new Exception("Uncaught upload error", 500);
    }
}
function uploadLogo($client) {
    $upload_dir = UPLOAD_PATH . $client . DIRECTORY_SEPARATOR;
    if (!isset($_FILES['client_logo']['name'])) {
        throw new Exception("client_logo is not set", 400);
    }
    $filename = $_FILES['client_logo']['name'];
    if(!$filename) {
        throw new Exception("Client logo is required", 400);
    }
    if($_FILES['client_logo']['error'] !== UPLOAD_ERR_OK ) {
        $file = $_FILES['client_logo']['name'];
        handleUploadError($file, $_FILES['client_logo']['error']);
    }
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $tempPath = $_FILES['client_logo']['tmp_name'];
    $filepath = $upload_dir . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    };
    if ($tempPath) {
        if(move_uploaded_file($tempPath, $filepath)) {
           return basename($filepath);
        } else {
            throw new Exception("error moving file to destination", 500);
        }
    } else {
        throw new Exception("error reading temp path of file", 500);
    }

}

function uploadImages($client) {
    if (!isset($_FILES['client_images']['name'])) {
        throw new Exception("client_images is not set", 500);
    }
    $files = array_filter($_FILES['client_images']['name']);
    $paths = [];
    $total = count($files);
    if(!$total) {
        return [];
    }
    $upload_dir = UPLOAD_PATH . $client . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;

    for ($i=0; $i < $total; $i++) { 
        if($_FILES['client_images']['error'][$i] === UPLOAD_ERR_OK) {
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = $_FILES['client_images']['name'][$i];
            $tempPath = $_FILES['client_images']['tmp_name'][$i];
            $filepath = $upload_dir . basename($_FILES['client_images']['name'][$i]);
            // check if file already exists
            if (file_exists($filepath)) {
                unlink($filepath);
            };
            // check if file is not of an accepted type
            if (strpos($_FILES['client_images']['type'][$i], 'image/') !== 0) {
                throw new Exception("$filename is not of an accepted type, only documents and images", 400);
            }
            if ($tempPath) {
                if(move_uploaded_file($tempPath, $filepath)) {
                    $paths[] = basename($filepath);
                } else {
                    throw new Exception("error moving file to destination", 500);
                }
            } else {
                throw new Exception("error reading temp path of file", 500);
            }
        } else {
            $file = $_FILES['client_images']['name'][$i];

            handleUploadError($file, $_FILES['client_images']['error'][$i]);
        }
    }
    return $paths;
}

function deleteImage(PDO $conn, array $data, Logger $logger) {
    $clientsTable = CLIENTS_TABLE;
    $filename = $data['filename'];
    $filepath = UPLOAD_PATH . $data['client'] . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR .  $filename;
    $record = queryRow($conn, "client exists", "SELECT * FROM $clientsTable WHERE id = ?;", $data['id']);
    if(!$record) {
        throw new Exception("client with given id doesn't exist", 404);
    }
    if (!file_exists($filepath)) {
        throw new Exception("$filename doesn't exist", 404);
    }
    $clientImageArr = json_decode($record['images']);
    $removedImage = null;
    foreach ($clientImageArr as $index => $image) {
        if($image == $filename) {
            $removedImage = array_splice($clientImageArr, $index, 1)[0];
        }
    }
    if(!$removedImage) {
        throw new Exception("$filename doesn't exist in client images", 404);
    }
    
    try {
        $stmt = $conn->prepare("UPDATE $clientsTable c SET c.images = ? WHERE id = ?;");
        $stmt->execute([
            count($clientImageArr) ? json_encode($clientImageArr) : null,
            $data['id']
        ]);
        unlink($filepath);
        return true;
    } catch (PDOException $e) {
        $logger->error('Error removing image record', ['message' => $e->getMessage()]);
        throw new Error('Error removing image record: '.$e->getMessage());
    }
}

function updateClient(PDO $conn, array $client, Logger $logger) {
    $logo_path = null;
    $uploadedImages = [];
    $clientsTable = CLIENTS_TABLE;
    if($_FILES['client_logo']['name']) {
        try {
            $logo_path = uploadLogo($client['client_name']);
        } catch (Exception $e) {
            $logger->withName('uploads')->error('Error uploading client logo', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
    if($_FILES['client_images']['name']) {
        try {
            $uploadedImages = uploadImages($client['client_name']);
        } catch (Exception $e) {
            $logger->withName('uploads')->error('Error uploading client images', ['message' => $e->getMessage()]);
        }
        $clientsTable = CLIENTS_TABLE;
    }
    try {
        $record = queryRow($conn, 'Client exists', "SELECT * FROM $clientsTable WHERE `id` = ?;", $client["id"]);
        if(!$record) {
            throw new Exception("Client with given id doesn't!", 400);
        }
        $sql = "UPDATE $clientsTable SET `name` = ?, `testimonial` = ?, `logo` = ?, `installation_year` = ?, `images` = ? WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $existingImages = json_decode($record['images']) ?? [];
        if($logo_path) {
            $filepath = UPLOAD_PATH . $record['name'] . DIRECTORY_SEPARATOR . $record['logo'];
            if(file_exists($filepath)) {
                unlink($filepath);
            }
        }
        $stmt->execute([
            $client['client_name'],
            $client['testimonial'],
            $logo_path ?? $record['logo'],
            strlen($client['installation_year']) ? $client['installation_year'] : null,
            count($uploadedImages) 
                ? json_encode(array_merge($existingImages, $uploadedImages)) 
                : (count($existingImages) ? $record['images'] : null ),
            $client["id"]
        ]);
        $updatedClient = queryRow($conn, "Get updated client", "SELECT * FROM $clientsTable WHERE id = ?;", $client["id"]);
        if(!$updatedClient) {
            throw new Exception('Could not get updated client', 500);
        }
        $updatedClient['images'] = json_decode($updatedClient['images']);
        return $updatedClient;
    } catch (Exception $e) {
        $logger->error(`Error updating client`, ['message'=>$e->getMessage()]);
        throw new Exception($e->getMessage(), 500);
    }
}

function deleteClient(PDO $conn, int $id, Logger $logger) {
    $clientsTable = CLIENTS_TABLE;

    try {
        $conn->beginTransaction();
        $record = queryRow($conn, 'Client exists', "SELECT * FROM $clientsTable WHERE `id` = ?;", $id);

        if(!$record) {
            throw new Exception('Client with given id not found', 400);
        }

        $sql = "DELETE FROM $clientsTable WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        try {
            // delete logo
            $filepath = UPLOAD_PATH . $record['name'] . DIRECTORY_SEPARATOR . $record['logo'];
            if(file_exists($filepath)) {
                unlink($filepath);
            }
    
            // delete images
            $images = json_decode($record['images']) ?? null;
            if($images) {
                $imagePath = UPLOAD_PATH . $record['name'] . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
                foreach ($images as $image) {
                    if(file_exists($imagePath . $image)) {
                        unlink($imagePath . $image);
                    }
                }
                rmdir($imagePath);
            }
    
            // delete client dir
            $clientDir = UPLOAD_PATH . $record['name'];
            if(file_exists($clientDir)) {
                rmdir($clientDir);
            }
        } catch (Exception $e) {
            $logger->withName('uploads')->error(`Error deleting client data`, ['message'=>$e->getMessage()]);
            throw $e;
        }
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        $logger->error(`Error deleting client`, ['message'=>$e->getMessage()]);
        throw new Exception($e->getMessage(), 500);
    }
}