package sae.java;

public class Edge {
    private Word word1; // Premier mot de l'arête
    private Word word2; // Deuxième mot de l'arête
    private double similarity; // Pourcentage de similarité entre les deux mots

    // Constructeur pour créer une arête à partir de deux mots et d'un pourcentage de similarité
    public Edge(Word word1, Word word2, double similarity) {
        this.word1 = word1;
        this.word2 = word2;
        this.similarity = similarity;
    }

    // Getter pour word1
    public Word getWord1() {
        return word1;
    }

    // Setter pour word1
    public void setWord1(Word word1) {
        this.word1 = word1;
    }

    // Getter pour word2
    public Word getWord2() {
        return word2;
    }

    // Setter pour word2
    public void setWord2(Word word2) {
        this.word2 = word2;
    }

    // Getter pour similarity
    public double getSimilarity() {
        return similarity;
    }

    // Setter pour similarity
    public void setSimilarity(double similarity) {
        this.similarity = similarity;
    }

    // Méthode pour afficher une arête
    @Override
    public String toString() {
        return getWord1() + " - " + getWord2() + " : " + getSimilarity();
    }
}