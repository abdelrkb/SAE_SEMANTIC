package sae.java;

import java.util.*;

public class Tree {

    private final List<Edge> edges; // Liste des arêtes de l'arbre
    private Word startWord; // Mot de départ de l'arbre
    private Word endWord;   // Mot de fin de l'arbre

    // Constructeur pour créer un arbre avec des mots de départ et de fin spécifiés
    public Tree(Word startWord, Word endWord) {
        this.edges = new ArrayList<>();
        this.startWord = startWord;
        this.endWord = endWord;
    }

    public Tree(List<Edge> edges, Word startWord, Word endWord) {
        this.edges = edges;
        this.startWord = startWord;
        this.endWord = endWord;
    }

    public void addEdge(Edge edge) {
        edges.add(edge);
    }

    // Methode pour remove un edge
    public void removeEdge(String word1, String word2) {
        Iterator<Edge> iterator = edges.iterator();

        while (iterator.hasNext()) {
            Edge edge = iterator.next();
            if ((edge.getWord1().word().equals(word1) && edge.getWord2().word().equals(word2)) ||
                    (edge.getWord1().word().equals(word2) && edge.getWord2().word().equals(word1))) {
                iterator.remove();
                break;
            }
        }
    }

    // Getter pour obtenir les arêtes de l'arbre
    public List<Edge> getEdges() {
        return edges;
    }

    // Getter et Setter pour le mot de départ
    public Word getStartWord() {
        return startWord;
    }

    public void setStartWord(Word startWord) {
        this.startWord = startWord;
    }

    // Getter et Setter pour le mot de fin
    public Word getEndWord() {
        return endWord;
    }

    public void setEndWord(Word endWord) {
        this.endWord = endWord;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Tree tree = (Tree) o;
        return edges.equals(tree.edges);
    }

    @Override
    public int hashCode() {
        return Objects.hash(edges);
    }

    public void removeEdge(Edge edgeToRemove) {
        edges.removeIf(edge -> edge.equals(edgeToRemove));
    }

    @Override
    public String toString() {
        StringBuilder sb = new StringBuilder();
        for (Edge edge : edges) {
            sb.append(edge.toString()).append("\n");
        }
        return sb.toString();
    }

    public Set<Word> getUniqueWords(Tree mst) {
        Set<Word> uniqueWords = new HashSet<>();

        for (Edge edge : mst.getEdges()) {
            uniqueWords.add(edge.getWord1());
            uniqueWords.add(edge.getWord2());
        }

        return uniqueWords;
    }

    // Methode pour ajouter un mot prend en parametre une liste de edges
    public void addWord(List<Edge> similarityEdges) {
        // Trouver la similarité minimale dans l'arbre actuel
        double minSimilarityInMST = this.getEdges().stream()
                .mapToDouble(Edge::getSimilarity)
                .min()
                .orElse(Double.MIN_VALUE);

        // Filtrer les arêtes dont la similarité est inférieure à la similarité minimale de l'arbre
        similarityEdges.removeIf(edge -> edge.getSimilarity() < minSimilarityInMST);

        // Trier les arêtes restantes en ordre décroissant de similarité
        Collections.sort(similarityEdges, Comparator.comparingDouble(Edge::getSimilarity).reversed());

        // Ajouter les arêtes une par une en vérifiant les cycles
        for (Edge edgeToAdd : similarityEdges) {
            this.addEdge(edgeToAdd);

            // Vérifier la présence de cycles
            if (!Cycle.findCycleEdges(this).isEmpty()) {
                // Supprimer l’arête la plus faible du cycle
                Cycle.removeLowestSimilarityEdgeInCycle(this);
            }
        }
    }

    // Méthode pour récupérer deux mots de l'arbre au hasard
    public List<Word> getRandomWords() {
        // Créer une liste pour stocker les mots de l'arbre
        List<Word> words = new ArrayList<>();

        // Parcourir toutes les arêtes de l'arbre
        for (Edge edge : this.getEdges()) {
            // Ajouter les deux mots de l'arête à la liste
            words.add(edge.getWord1());
            words.add(edge.getWord2());
        }

        // Mélanger la liste pour obtenir un ordre aléatoire
        Collections.shuffle(words);

        // Retourner les deux premiers mots de la liste
        return words.subList(0, 2);
    }
}

