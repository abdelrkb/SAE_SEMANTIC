package sae;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.ArrayList;
import java.util.List;

public class LoadingFromJava {
    private double score;
    private Tree tree;
    private List<Word> bannedWords;

    // Constructeur
    public LoadingFromJava(double score, Tree tree, List<Word> bannedWords) {
        this.score = score;
        this.tree = tree;
        this.bannedWords = bannedWords;
    }

    // Getters
    public double getScore() {
        return score;
    }

    public Tree getTree() {
        return tree;
    }

    public List<Word> getBannedWords() {
        return bannedWords;
    }

    /**
     * Charge les données à partir du fichier spécifié.
     * @param filename Chemin vers le fichier à lire.
     * @return Un nouvel objet LoadingFromJava si le fichier est lisible, sinon null.
     * @throws IOException Si une erreur d'entrée/sortie se produit.
     */

    public static LoadingFromJava loadFromFile(String filename) throws IOException {
        Path path = Paths.get(filename);
        if (!Files.exists(path)) {
            return null;  // Retourne null si le fichier n'existe pas
        }

        try (BufferedReader br = Files.newBufferedReader(path)) {
            double score = 0.0;
            List<Word> startEndWords = new ArrayList<>();
            List<Edge> edges = new ArrayList<>();
            List<Word> bannedWords = new ArrayList<>();
            String line;

            // Lecture du score
            line = br.readLine();
            if (line != null && !line.isEmpty()) {
                line = line.trim();
                score = Double.parseDouble(line.split(":")[1].trim());
            }

            // Ignorer l'entête "Mots de départ :"
            br.readLine();

            // Lecture des mots de départ
            while ((line = br.readLine()) != null && !line.trim().equals("Distance entre les mots :")) {
                if (!line.isEmpty()) {
                    startEndWords.add(new Word(line.split(",")[0].trim()));
                }
            }

            if (line == null) {
                return null; // Stop processing if file is not as expected
            }

            // Lecture des distances entre les mots
            while ((line = br.readLine()) != null && !line.trim().equals("Mots bannis:")) {
                if (!line.isEmpty()) {
                    String[] parts = line.split(", distance: ");
                    String[] wordsPart = parts[0].split("_");
                    double distance = Double.parseDouble(parts[1].trim());
                    Word word1 = new Word(wordsPart[0].trim());
                    Word word2 = new Word(wordsPart[1].trim());
                    edges.add(new Edge(word1, word2, (float) distance));
                }
            }

            if (line == null) {
                return null; // Stop processing if file is not as expected
            }

            // Lecture des mots bannis
            while ((line = br.readLine()) != null) {
                if (!line.trim().isEmpty()) {
                    bannedWords.add(new Word(line.split(",")[0].trim()));
                }
            }

            // Création de l'arbre avec les deux premiers mots comme mots de départ et de fin, si possible
            if (startEndWords.size() >= 2) {
                Tree tree = new Tree(startEndWords.get(0), startEndWords.get(1), edges);
                return new LoadingFromJava(score, tree, bannedWords);
            }
        }

        return null;  // Retourne null si les données ne sont pas suffisantes pour créer une instance valide
    }


    public static class DataWriter {

        /**
         * Écrit les données dans un fichier Java.
         * @param filename Le nom du fichier à écrire.
         * @param score Le score à enregistrer.
         * @param startWord1 Le premier mot de départ.
         * @param startWord2 Le deuxième mot de départ.
         * @param edges La liste des arêtes à écrire.
         * @param bannedWords La liste des mots bannis à écrire.
         * @throws IOException Si une erreur d'écriture se produit.
         */


        public static void writeJavaFile(String filename, double score, Word startWord1, Word startWord2,
                                         List<Edge> edges, List<Word> bannedWords) throws IOException {
            Path path = Paths.get(filename);
            try (BufferedWriter writer = Files.newBufferedWriter(path)) {
                // Écrire le score
                writer.write("Score: " + score);
                writer.newLine();

                // Écrire les mots de départ
                writer.write("Mots de départ :");
                writer.newLine();
                writer.write(startWord1.toString());
                writer.newLine();
                writer.write(startWord2.toString());
                writer.newLine();

                // Écrire la distance entre les mots
                writer.write("Distance entre les mots :");
                writer.newLine();
                for (Edge edge : edges) {
                    writer.write(edge.getWord1() + "_" + edge.getWord2() + ", distance: " + edge.getSimilarity());
                    writer.newLine();
                }

                // Écrire les mots bannis
                writer.write("Mots bannis:");
                writer.newLine();
                for (Word word : bannedWords) {
                    writer.write(word.toString());
                    writer.newLine();
                }
            }
        }

    }
}
