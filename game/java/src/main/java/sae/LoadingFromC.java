package sae;

import java.io.BufferedReader;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.ArrayList;
import java.util.List;

public class LoadingFromC {
    private Word startWord;
    private Word endWord;
    private List<Word> words;
    private List<Edge> edges;

    public LoadingFromC(Word startWord, Word endWord, List<Word> words, List<Edge> edges) {
        this.startWord = startWord;
        this.endWord = endWord;
        this.words = words;
        this.edges = edges;
    }
    /**
     * Retourne le mot de départ.
     * @return le mot de départ utilisé dans le MST.
     */
    public Word getStartWord() {
        return startWord;
    }

    /**
     * Retourne le mot de fin.
     * @return le mot de fin utilisé dans le MST.
     */
    public Word getEndWord() {
        return endWord;
    }

    /**
     * Retourne la liste des mots.
     * @return la liste de tous les mots concernés par le MST.
     */
    public List<Word> getWords() {
        return words;
    }

    /**
     * Retourne la liste des arêtes.
     * @return la liste des arêtes qui composent le MST.
     */
    public List<Edge> getEdges() {
        return edges;
    }

    public static LoadingFromC loadMst(String filename) throws IOException {
        Word startWord = null;
        Word endWord = null;
        List<Word> words = new ArrayList<>();
        List<Edge> edges = new ArrayList<>();

        Path path = Path.of(filename);
        try (BufferedReader br = Files.newBufferedReader(path)) {
            String line;
            br.readLine(); // Ignore "Mots de départ :"
            startWord = new Word(br.readLine().split(",")[0].trim()); // Read start word
            endWord = new Word(br.readLine().split(",")[0].trim()); // Read end word

            // Skipping to "Liste des mots :"
            while ((line = br.readLine()) != null && !line.trim().equals("Liste des mots :")) {}

            // Read words until "Distance entre les mots :"
            while ((line = br.readLine()) != null && !line.trim().equals("Distance entre les mots :")) {
                if (!line.isEmpty()) {
                    String word = line.split(",")[0].trim();
                    words.add(new Word(word));
                }
            }

            // Read distances
            while ((line = br.readLine()) != null && !line.isEmpty()) {
                String[] parts = line.split(",");
                String[] wordsPart = parts[0].split("-");
                double distance;
                if (parts.length > 1 && parts[1].contains("distance")) {
                    distance = Double.parseDouble(parts[1].split(":")[1].trim());
                } else {
                    distance = Double.parseDouble(parts[1].trim());
                }

                Word word1 = new Word(wordsPart[0].trim());
                Word word2 = new Word(wordsPart[1].trim());

                edges.add(new Edge(word1, word2, (float) distance));
            }
        }

        return new LoadingFromC(startWord, endWord, words, edges);
    }
}
