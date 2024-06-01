package sae;


import java.util.Objects;

public record Edge(Word word1, Word word2, float similarity) {

    public Word getWord1() {
        return word1; // Vous pourriez ajouter une logique personnalisée ici
    }

    public Word getWord2() {
        return word2; // Vous pourriez ajouter une logique personnalisée ici
    }

    public float getSimilarity() {
        // Exemple de formatage ou calcul avant le retour
        return Math.round(similarity * 100) / 100.0f;
    }

    public Word other(Word word) {
        if (word.equals(word1)) {
            return word2;
        } else if (word.equals(word2)) {
            return word1;
        } else {
            throw new IllegalArgumentException("Word is not part of the edge");
        }
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Edge edge = (Edge) o;
        return Float.compare(edge.similarity, similarity) == 0 &&
                Objects.equals(word1, edge.word1) &&
                Objects.equals(word2, edge.word2);
    }

    @Override
    public int hashCode() {
        return Objects.hash(word1, word2, similarity);
    }

    @Override
    public String toString() {
        return word1 + " - " + word2 + " : " + getSimilarity(); // Utilisation du getter personnalisé
    }

    public boolean contains(Word word) {
        return word1.equals(word) || word2.equals(word);
    }
}
