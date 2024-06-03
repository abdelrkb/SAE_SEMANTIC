package sae.java;

import java.util.*;
import java.util.stream.Collectors;

public class Cycle {
    // Méthode DFS pour détecter un cycle et recueillir les arêtes du cycle
    private static boolean dfs(Word current, Word parent, Set<Word> visited, Tree mst, List<Edge> cycleEdges) {
        // Marquer le mot actuel comme visité
        visited.add(current);

        // Parcourir toutes les arêtes de l'arbre (MST)
        for (Edge edge : mst.getEdges()) {
            Word next = null;

            // Vérifier si l'arête courante contient le mot actuel et trouver le mot suivant
            if (edge.getWord1().equals(current)) {
                next = edge.getWord2();
            } else if (edge.getWord2().equals(current)) {
                next = edge.getWord1();
            }

            // Si un mot suivant est trouvé
            if (next != null) {
                // Vérifier si le mot suivant n'a pas déjà été visité
                if (!visited.contains(next)) {
                    // Continuer la recherche en profondeur à partir du mot suivant
                    // Si un cycle est trouvé, ajouter l'arête actuelle à la liste des arêtes du cycle
                    // et retourner true pour indiquer la détection d'un cycle
                    if (dfs(next, current, visited, mst, cycleEdges)) {
                        cycleEdges.add(edge);
                        return true;
                    }
                } else if (!next.equals(parent)) {
                    // Si le mot suivant a déjà été visité et n'est pas le parent actuel,
                    // cela signifie qu'un cycle a été détecté, ajouter l'arête actuelle au cycle
                    cycleEdges.add(edge);
                    return true;
                }
            }
        }

        // Si aucun cycle n'a été détecté à partir du mot actuel, retourner false
        return false;
    }


    // Méthode publique pour trouver les arêtes formant un cycle
    public static List<Edge> findCycleEdges(Tree mst) {
        // Créer un ensemble pour suivre les mots visités
        Set<Word> visited = new HashSet<>();

        // Créer une liste pour stocker les arêtes formant un cycle
        List<Edge> cycleEdges = new ArrayList<>();

        // Parcourir toutes les arêtes de l'arbre (MST)
        for (Edge edge : mst.getEdges()) {
            Word word1 = edge.getWord1();

            // Vérifier si le mot word1 n'a pas déjà été visité
            if (!visited.contains(word1)) {
                // Appeler la méthode dfs pour rechercher un cycle à partir de word1
                if (dfs(word1, null, visited, mst, cycleEdges)) {
                    // Si un cycle est trouvé, sortir de la boucle
                    break;
                }
            }
        }
        // Retourner la liste des arêtes formant un cycle
        return cycleEdges;
    }


    // Méthode pour supprimer l'arête avec la similarité la plus faible dans un cycle
    public static boolean removeLowestSimilarityEdgeInCycle(Tree mst) {
        List<Edge> cycleEdges = findCycleEdges(mst);
        if (!cycleEdges.isEmpty()) {
            // Trouver la similarité minimale parmi les arêtes du cycle
            double minSimilarity = cycleEdges.stream()
                    .mapToDouble(Edge::getSimilarity)
                    .min()
                    .orElse(Double.MAX_VALUE);

            // Filtrer les arêtes avec cette similarité minimale
            List<Edge> lowestSimilarityEdges = cycleEdges.stream()
                    .filter(edge -> edge.getSimilarity() == minSimilarity)
                    .collect(Collectors.toList());

            // Choisir et supprimer une arête au hasard parmi celles ayant la similarité la plus faible
            if (!lowestSimilarityEdges.isEmpty()) {
                Random random = new Random();
                Edge edgeToRemove = lowestSimilarityEdges.get(random.nextInt(lowestSimilarityEdges.size()));
                mst.removeEdge(edgeToRemove);
            }
            return true;
        }
        return false;
    }
}
