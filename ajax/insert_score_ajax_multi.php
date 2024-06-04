<?php
include("../conf.bkp.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['pseudo']) && isset($data['score'])) {
    $pseudo = $data['pseudo'];
    $score = $data['score'];

    try {
        // Fetch the user number
        $requestuser = $cnx->prepare("SELECT num_user FROM SAE_USERS WHERE pseudo=:pseudo");
        $requestuser->bindParam(':pseudo', $pseudo);
        $requestuser->execute();
        $result = $requestuser->fetch(PDO::FETCH_ASSOC);
        $requestuser->closeCursor();

        if ($result) {
            $numuser = $result['num_user'];

            // Insert the final score
            $requestAddFinalScore = $cnx->prepare("INSERT INTO SAE_SCORES (num_user, score) VALUES (:num_user, :score)");
            $requestAddFinalScore->bindParam(':num_user', $numuser, PDO::PARAM_INT);
            $requestAddFinalScore->bindParam(':score', $score, PDO::PARAM_STR);
            $requestAddFinalScore->execute();
            $requestAddFinalScore->closeCursor();

            // Trace function call
            trace($_SESSION['num_user'], "A Joué une partie multi", $cnx);

            // Log the received data
            error_log("Received score submission: Pseudo: $pseudo, Score: $score");

            echo json_encode(['status' => 'success', 'message' => 'Score submitted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
}
?>
