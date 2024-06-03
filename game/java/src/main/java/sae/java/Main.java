package sae.java;

import java.io.IOException;
import java.util.*;

public class Main {

    public static void main(String[] args) throws IOException {
        // Vérifier si les arguments sont passés au programme
        if (args.length < 2) {
            System.out.println("Veuillez passer les arguments suivants : <fichier C> <fichier Java>");
            return;
        }

        // Mots de départ et d’arrivée passé en argument au programme
        String filenameC = args[0]; // Fichier créé par le programme C
        String filenameJava = args[1]; // Fichier à créer par ce programme

        // Lecture de l'arbre MST à partir du fichier
        LoadingData loadingData = TreeUtils.loadMst(filenameC); // startWord, endWord, words, edges
        // Construction de l'arbre MST
        Tree tree = TreeUtils.buildTree(loadingData.startWord(), loadingData.endWord(), loadingData.words(), loadingData.edges());

        // Mettre à jour le fichier filenameC avec les arêtes de l'arbre MST initial
        TreeUtils.updateFileC(tree, filenameC);

        // Vérification du dernier mot
        boolean lastWordAdded = TreeUtils.checkLastWordInTree(tree, loadingData.words());

        // Trouver le meilleur chemin entre startWord et endWord
        // Remplacez ceci par votre implémentation pour trouver le chemin
        List<Edge> path = bfsFindPath(tree, loadingData.startWord(), loadingData.endWord()); // bfsFindPath est à implémenter

        // Calculer le score du chemin (similarité minimale entre deux mots consécutifs)
        // Remplacez ceci par votre implémentation pour calculer le score
        int score = calculatePathScore(path); // calculatePathScore est à implémenter

        // Afficher le chemin trouvé et le score associé
        // Remplacez ceci par votre implémentation pour afficher le chemin et le score
        printPathAndScore(path, score); // printPathAndScore est à implémenter

        // Écrire les informations dans le fichier
        TreeUtils.writeToFile(tree, score, lastWordAdded, filenameJava);
    }

    private static int calculatePathScore(List<Edge> path) {
        // Initialiser le score avec la valeur maximale possible
        int score = Integer.MAX_VALUE;

        // Parcourir chaque arête du chemin
        for (Edge edge : path) {
            // Mettre à jour le score avec la similarité minimale trouvée
            score = (int) Math.min(score, edge.getSimilarity());
        }

        // Si aucun chemin n'est trouvé (chemin vide), retourner 0 ou une autre valeur par défaut
        return path.isEmpty() ? 0 : score;
    }

    private static void printPathAndScore(List<Edge> path, int score) {
        // Vérifier si le chemin est vide
        if (path.isEmpty()) {
            System.out.println("Aucun chemin trouvé.");
            return;
        }

        // Afficher le chemin
        System.out.println("Chemin :");
        for (Edge edge : path) {
            System.out.println(edge.getWord1().word() + " - " + edge.getWord2().word());
        }

        // Afficher le score du chemin
        System.out.println("Score (similarité minimale) : " + score);
    }

    // Méthode auxiliaire pour trouver l'arête entre deux mots dans l'arbre MST
    private static Edge findEdgeBetween(Tree mst, Word word1, Word word2) {
        for (Edge edge : mst.getEdges()) {
            if ((edge.getWord1().equals(word1) && edge.getWord2().equals(word2)) ||
                    (edge.getWord1().equals(word2) && edge.getWord2().equals(word1))) {
                return edge;
            }
        }
        return null;
    }


    private static List<Edge> constructPath(Map<Word, Word> parentMap, Word startWord, Word endWord, Tree mst) {
        List<Edge> path = new ArrayList<>();

        // Commencer par le mot de fin et remonter jusqu'au mot de départ
        Word current = endWord;
        while (current != null && !current.equals(startWord)) {
            Word parent = parentMap.get(current);

            // Si aucun parent n'est trouvé, cela signifie que le chemin est incomplet
            if (parent == null) {
                break;
            }

            // Trouver l'arête entre le mot actuel et son parent
            Edge edge = findEdgeBetween(mst, parent, current);
            if (edge != null) {
                // Ajouter l'arête au début de la liste pour que le chemin soit dans le bon ordre
                path.add(0, edge);
            }

            // Passer au mot parent pour continuer à remonter le chemin
            current = parent;
        }

        return path;
    }

    private static List<Edge> bfsFindPath(Tree mst, Word startWord, Word endWord) {
        // Carte pour garder une trace du mot parent pour chaque mot visité
        Map<Word, Word> parentMap = new HashMap<>();
        // File d'attente pour le parcours BFS
        Queue<Word> queue = new LinkedList<>();
        // Ensemble pour suivre les mots déjà visités
        Set<Word> visited = new HashSet<>();

        // Initialiser la file d'attente et les ensembles
        queue.add(startWord);
        visited.add(startWord);
        parentMap.put(startWord, null);

        // Parcours BFS
        while (!queue.isEmpty()) {
            Word current = queue.remove();

            // Arrêter si le mot de fin est atteint
            if (current.equals(endWord)) {
                break;
            }

            // Vérifier chaque arête de l'arbre
            for (Edge edge : mst.getEdges()) {
                Word neighbor = null;

                // Trouver le voisin du mot actuel
                if (edge.getWord1().equals(current)) {
                    neighbor = edge.getWord2();
                } else if (edge.getWord2().equals(current)) {
                    neighbor = edge.getWord1();
                }

                // Si le voisin n'a pas encore été visité, l'ajouter à la file d'attente
                if (neighbor != null && !visited.contains(neighbor)) {
                    queue.add(neighbor);
                    visited.add(neighbor);
                    parentMap.put(neighbor, current);
                }
            }
        }

        // Construire et retourner le chemin trouvé
        return constructPath(parentMap, startWord, endWord, mst);
    }
}
