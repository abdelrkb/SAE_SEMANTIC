package sae.java;

import java.util.Arrays;
import java.util.Random;
import java.util.Scanner;

public class SimilarityCalculator {
    // Méthode pour créer une arête (Edge) avec un pourcentage de similarité aléatoire entre deux mots
    public static Edge calculateRandomSimilarity(String word1, String word2) {
        Random random = new Random();
        int similarityPercentage = random.nextInt(101); // Génère un entier aléatoire entre 0 et 100

        return new Edge(new Word(word1), new Word(word2), similarityPercentage);
    }

    public static void main(String[] args) {
        // Créer une arête avec deux mots et un pourcentage de similarité aléatoire
        Edge edge = calculateRandomSimilarity("chat", "chien");
        // Afficher l'arête
        System.out.println(edge);
    }

    private static final Scanner scanner = new Scanner(System.in);

    // Méthode pour créer un Edge avec la similarité saisie manuellement
    public static Edge enterSimilarity(String word1, String word2) {
        System.out.println("Entrez la similarité entre \"" + word1 + "\" et \"" + word2 + "\":");
        int similarity = -1;

        while (similarity < 0 || similarity > 100) {
            try {
                similarity = Integer.parseInt(scanner.nextLine());
                if (similarity < 0 || similarity > 100) {
                    System.out.println("Veuillez entrer une valeur valide (entre 0 et 100).");
                }
            } catch (NumberFormatException e) {
                System.out.println("Entrée invalide. Veuillez entrer un nombre.");
            }
        }

        // Créer et retourner un nouvel Edge avec les mots et la similarité entrée
        return new Edge(new Word(word1), new Word(word2), similarity);
    }

    // Méthode pour créer un Edge avec la similarité et les deux mots passés en argument
    public static Edge enterSimilarity(String word1, String word2, int similarity) {
        return new Edge(new Word(word1), new Word(word2), similarity);
    }
}
