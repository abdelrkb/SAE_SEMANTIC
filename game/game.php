<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['pseudo'])) {
    header('Location: ./');
    exit();
}
include("game_fonctions.php");
if (!isset($_SESSION['paires'])) {
    startGame();
}
$paires = $_SESSION['paires'];
$words = $_SESSION['words'];
$firstWord = $words[0];
$lastWord = $words[1];

$messagesErreur = [
    1 => "Le mot n'est pas dans le dictionnaire.",
    2 => "Le mot est déjà dans la chaîne.",
    3 => "La similarité est trop faible."
];
// Si un message d’erreur est passé dans l'URL, sinon message vide erreur = 0
$erreur = $_GET['erreur'] ?? 0;

// Utilisation d’un tableau associatif pour stocker les relations entre les mots
$relations = [];

foreach ($paires as $paire) {
    $mot1 = $paire["mot1"];
    $mot2 = $paire["mot2"];
    $nombre = $paire["nombre"];

    // Si la relation entre mot1 et mot2 n'existe pas encore
    if (!isset($relations[$mot1][$mot2])) {
        $relations[$mot1][$mot2] = $nombre;
    } else {
        // Si la relation existe déjà, additionner le nombre
        $relations[$mot1][$mot2] += $nombre;
    }
}

$nodes = [];
$links = [];

// Création des nodes et des links à partir des relations
foreach ($relations as $mot1 => $rel) {
    $mot1 = ucfirst($mot1);
    $nodes[] = ["id" => $mot1];

    foreach ($rel as $mot2 => $nombre) {
        $mot2 = ucfirst($mot2);
        $nodes[] = ["id" => $mot2];
        $links[] = [
            "source" => $mot1,
            "target" => $mot2,
            "linkTextPath" => $nombre
        ];
    }
}
$highchartsData = createDataForGraph($paires);

include("../conf.bkp.php");
$scoreRequest = $cnx->prepare("SELECT MAX(score) AS maxS FROM SAE_SCORES s, SAE_USERS u WHERE u.num_user = s.num_user AND u.pseudo = :pseudo");
$scoreRequest->bindParam(':pseudo', $_SESSION['pseudo']);
$scoreRequest->execute();
$scoreResult = $scoreRequest->fetch(PDO::FETCH_OBJ);
$scoreRequest->closeCursor();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
<link rel="icon" href="../img/sinje_magique.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Classement - Wacky Monkey's Words</title>
	<meta name="description" content="Venez Jouez à Wacky Monkey's Words (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Wacky Monkey's Words ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
	<meta name="keywords" content="Wacky Monkey's Words, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
	<link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/css_game.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/networkgraph.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        * {
            color: white;
        }
        label {
            color: black;
        }
    </style>
</head>

    <body class="black">
    <div class="div4">
        <img src="../img/sinje_off.png" alt="Logo">
    </div>
    <div class="parent">
        <div class="div1" id="container"></div> <!-- Le graphique sera affiché ici -->
        <!-- Formulaire pour ajouter un nouveau mot -->
        <div class="div2">
            <form id="add" action="addWordGraph.php" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="word" name="word" placeholder="Nouveau mot" required>
                    <label for="word">Nouveau mot</label>
                </div>
                <input type="submit" value="Insérer un nouveau mot" class="btn btn-success<?php if (count($words) >= 7) { echo " disabled"; } ?>">
            </form>
            <div id="end">
                <a href="#" class="btn btn-primary" id="endGameButton">Finir la partie</a>
            </div>
        </div>
        <div class="div3">
            <p>Score actuel : <?php echo calculateScore(); ?></p>
            <p>Nombre de mots : <?php echo count($words); ?></p>
            <p>Dernier mot : <?php if (count($_SESSION['words']) > 2) echo ucfirst($_SESSION['words'][count($_SESSION['words']) - 1]); else echo "Aucun mot entré"; ?></p>
            <p>Nombre de mots restants : <?php echo 7 - count($words) ?></p>
        </div>
        <div class="div6">
            <h3>Mots de la partie :</h3>
            <?php
            foreach ($_SESSION['words'] as $word) {
                $class = "";
                if (isWordBanned($word)) {
                    $class = "class='banned'";
                }
                echo "<p $class>".ucfirst($word)."</p>";
            }
            ?>
        </div>
        <div class="modal fade" id="endGameModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fin de la Partie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bravo ! Votre score final est de <?php echo calculateScore(); ?> point(s).<br> Relèverez-vous le défi à nouveau ? </p>
                        <p>Votre meilleur score est de <?php echo $scoreResult->maxS; ?> point(s).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closeModalButton" data-bs-dismiss="modal">Retourner à l'accueil</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    if ($erreur != 0) {
        echo "<div id='adminToast' class='toast'><i class='fa-solid fa-circle-exclamation'></i>".$messagesErreur[$erreur]."</div>";
        echo '<script>document.addEventListener("DOMContentLoaded", function() { showToast(); });</script>';
    }
    ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showToast() {
        var toast = document.getElementById("adminToast");
        toast.className = "toast show";
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

    // Fonction pour charger les données depuis PHP
    function loadData() {
        // Charger les données depuis le fichier PHP
        var nodes = <?php echo json_encode($highchartsData["nodes"]); ?>;
        var links = <?php echo json_encode($highchartsData["links"]); ?>;
        var highchartsData = {
            "nodes": nodes,
            "links": links
        };
        let chart = createChart(highchartsData); // Créer ou mettre à jour le graphique
    }

    function createChart(highchartsData) {
        let chart = Highcharts.chart('container', {
            chart: {
                type: 'networkgraph',
                plotBorderWidth: 0,
                animation: true
            },
            title: {
                text: ''
            },
            plotOptions: {
                networkgraph: {
                    keys: ['from', 'to'],
                    layoutAlgorithm: {
                        enableSimulation: true,
                        linkLength: 150,
                        integration: 'verlet'
                    },
                    marker: {
                        radius: 10
                    },
                    link: {
                        width: 2,
                        color: '#C0C0C0' // Couleur des liens - Argenté
                    },
                    node: {
                        marker: {
                            fillColor: '#000000', // Couleur de remplissage des nœuds - Noir
                            lineWidth: 2,
                            lineColor: '#C0C0C0' // Couleur de la bordure des nœuds - Argenté
                        },
                        events: {
                            add: function(event) {
                                event.point.graphic.animate({
                                    radius: 15
                                }, {
                                    duration: 1000,
                                    easing: 'elastic'
                                });
                            }
                        }
                    }
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'K3',
                dataLabels: {
                    enabled: true,
                    linkFormat: '{point.weight}',
                    style: {
                        fontSize: '0.8rem',
                        fontWeight: 'normal'
                    }
                },
                data: highchartsData.links.map(function(link) {
                    return {
                        from: link.source,
                        to: link.target,
                        weight: link.linkTextPath
                    };
                }),
                nodes: highchartsData.nodes
            }]
        });

        return chart;
    }

    // Charger les données et créer le graphique
    loadData();

    const myModal = document.getElementById('endGameModal');
    const myInput = document.getElementById('myInput');

    myModal.addEventListener('shown.bs.modal', () => {
        if(myInput) {
            myInput.focus();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Récupérez le modal par son ID
        var endGameModal = new bootstrap.Modal(document.getElementById('endGameModal'));

        // Récupérez le bouton "Finir la partie" par son ID (Assurez-vous que cet ID existe dans votre HTML)
        var endGameButton = document.getElementById("endGameButton");

        // Attachez un gestionnaire d'événements au clic sur le bouton "Finir la partie"
        endGameButton.addEventListener('click', function () {
            // Affichez le modal lorsque le bouton "Finir la partie" est cliqué
            endGameModal.show();
        });

        var words = <?php echo json_encode($words); ?>;
        if (words.length >= 7) {
            endGameModal.show();
        }

        // Récupérez le bouton "Fermer" du modal par son ID
        var closeModalButton = document.getElementById("closeModalButton");

        // Attachez un gestionnaire d'événements au clic sur le bouton "Fermer" du modal
        closeModalButton.addEventListener('click', function () {
            // Effectuez la redirection vers end_game.php lorsque le bouton "Fermer" du modal est cliqué
            window.location.href = "end_game.php";
        });
    });

</script>
</body>

</html>
