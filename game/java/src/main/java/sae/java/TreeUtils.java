package sae.java;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.nio.file.attribute.PosixFilePermission;
import java.nio.file.attribute.PosixFilePermissions;
import java.util.*;

public class TreeUtils {

    // Méthode pour retourner une liste avec touts les mots du tree
    public static Set<Word> getUniqueWords(Tree mst) {
        Set<Word> uniqueWords = new HashSet<>();

        for (Edge edge : mst.getEdges()) {
            uniqueWords.add(edge.getWord1());
            uniqueWords.add(edge.getWord2());
        }

        return uniqueWords;
    }

    //methode temporaire utilisé à la place du langage c
    public static ArrayList<Edge> createAndSortSimilarityEdges(Tree mst, String newWord) {
        Set<Word> uniqueWords = getUniqueWords(mst);
        ArrayList<Edge> similarityEdges = new ArrayList<>();

        // Trouver la similarité la plus faible dans l'arbre actuel
        double minSimilarityInMST = mst.getEdges().stream()
                .mapToDouble(Edge::getSimilarity)
                .min()
                .orElse(Double.MIN_VALUE);

        // Calculer la similarité entre le nouveau mot et chaque mot unique dans l'arbre
        for (Word word : uniqueWords) {
            Edge edge = SimilarityCalculator.enterSimilarity(newWord, word.word());

            // Ajouter l'arête seulement si sa similarité est supérieure ou égale à la similarité minimale
            if (edge.getSimilarity() >= minSimilarityInMST) {
                similarityEdges.add(edge);
            }
        }

        // Trier la liste en ordre décroissant de similarité
        Collections.sort(similarityEdges, new Comparator<Edge>() {
            @Override
            public int compare(Edge e1, Edge e2) {
                return Double.compare(e2.getSimilarity(), e1.getSimilarity());
            }
        });

        return similarityEdges;
    }

    public static void printEdgesBeautifully(ArrayList<Edge> edges) {
        System.out.println("Liste des arêtes avec similarité :");
        System.out.println("----------------------------------");
        System.out.printf("%-20s %-20s %-15s\n", "Mot de Départ", "Mot de Fin", "Similarité (%)");
        System.out.println("----------------------------------");

        for (Edge edge : edges) {
            System.out.printf("%-20s %-20s %-15d\n",
                    edge.getWord1().word(),
                    edge.getWord2().word(),
                    edge.getSimilarity());
        }
    }

    public static Boolean checkLastWordInTree(Tree tree, List<Word> words) {
        if (words.isEmpty()) {
            return true;
        }

        Word lastWord = words.get(words.size() - 1);

        for (Edge edge : tree.getEdges()) {
            if (edge.getWord1().equals(lastWord) || edge.getWord2().equals(lastWord)) {
                return true;
            }
        }
        return false;
    }

    public static Tree buildTree(Word startWord, Word endWord, List<Word> words, List<Edge> edges) {
        Tree tree = new Tree(startWord, endWord);

        // Trouver et ajouter l'arête qui contient le startWord et endWord
        for (Edge edge : new ArrayList<>(edges)) {
            if ((edge.getWord1().equals(startWord) && edge.getWord2().equals(endWord)) ||
                    (edge.getWord1().equals(endWord) && edge.getWord2().equals(startWord))) {
                tree.addWord(new ArrayList<>(Collections.singletonList(edge)));
                edges.remove(edge);
                break;
            }
        }

        // Ajouter les autres arêtes en suivant l'ordre des mots dans la liste words
        for (int i = 2; i < words.size(); i++) {
            Word currentWord = words.get(i);
            for (Edge edge : new ArrayList<>(edges)) {
                if (edge.getWord1().equals(currentWord) || edge.getWord2().equals(currentWord)) {
                    if (tree.getUniqueWords(tree).contains(edge.getWord1()) ||
                            tree.getUniqueWords(tree).contains(edge.getWord2())) {
                        tree.addWord(new ArrayList<>(Collections.singletonList(edge)));
                        edges.remove(edge);
                    }
                }
            }
        }

        return tree;
    }

    public static LoadingData loadMst(String filename) throws IOException {
        Word startWord = null;
        Word endWord = null;
        List<Word> words = new ArrayList<>();
        List<Edge> edges = new ArrayList<>();

        Path path = Path.of(filename);
        try (BufferedReader br = Files.newBufferedReader(path)) {
            String line;
            double distance;
            br.readLine(); // Lire et ignorer la première ligne
            startWord = new Word(br.readLine().split(",")[0].trim()); // Lire le mot de départ (2ème ligne)
            endWord = new Word(br.readLine().split(",")[0].trim()); // Lire le mot de fin (3ème ligne)

            while ((line = br.readLine()) != null) {
                if (line.startsWith("Liste des mots :")) {
                    while (!(line = br.readLine()).startsWith("Distance entre les mots :")) {
                        String word = line.split(",")[0].trim();
                        words.add(new Word(word));
                    }
                }
                if (line.startsWith("Distance entre les mots :")) {
                    while ((line = br.readLine()) != null && !line.isEmpty()) {
                        String[] parts = line.split("-");
                        String[] wordsPart = {parts[0].trim(), parts[1].split(",")[0].trim()};

                        if (parts[1].contains("distance")) {
                            distance = Double.parseDouble(parts[1].split(":")[1].trim());
                        } else {
                            distance = Double.parseDouble(parts[1].split(",")[1].trim());
                        }

                        Edge edge = new Edge(new Word(wordsPart[0]), new Word(wordsPart[1]), distance);
                        edges.add(edge);
                    }
                }
            }
        }
        return new LoadingData(startWord, endWord, words, edges);
    }

    // Méthode pour mettre à jour le fichier filenameC après avoir supprimé un Edge de l'arbre
    public static void updateFileC(Tree mst, String filenameC) throws IOException {
        // Créer une liste pour stocker les lignes du fichier filenameC
        List<String> lines = new ArrayList<>();

        // Lire le contenu du fichier filenameC
        try (BufferedReader br = Files.newBufferedReader(Paths.get(filenameC))) {
            String line;
            while ((line = br.readLine()) != null) {
                // Ajouter chaque ligne du fichier à la liste (sauf les lignes correspondant aux arêtes supprimées)
                if (!shouldRemoveLine(line, mst)) {
                    lines.add(line);
                }
            }
        }

        // Écrire les lignes mises à jour dans le fichier filenameC
        try (BufferedWriter bw = Files.newBufferedWriter(Paths.get(filenameC))) {
            for (String line : lines) {
                bw.write(line);
                bw.newLine();
            }
        }
    }

    // Méthode pour vérifier si une ligne doit être supprimée en fonction de l'arbre MST
    private static boolean shouldRemoveLine(String line, Tree mst) {
        // Vérifier si la ligne correspond à une arête
        if (line.contains("-") && line.contains("distance")) {
            String[] parts = line.split(",");
            String wordPair = parts[0].trim();

            // Séparer les deux mots de l'arête
            String[] words = wordPair.split("-");
            String word1 = words[0].trim();
            String word2 = words[1].trim();

            // Vérifier si l'arête existe toujours dans l'arbre MST
            for (Edge edge : mst.getEdges()) {
                if ((edge.getWord1().word().equals(word1) && edge.getWord2().word().equals(word2)) ||
                        (edge.getWord1().word().equals(word2) && edge.getWord2().word().equals(word1))) {
                    return false; // Ne pas supprimer la ligne car l'arête est toujours présente
                }
            }
            // Si l'arête n'est pas dans l'arbre MST, la ligne doit être supprimée
            return true;
        }
        // Si ce n'est pas une ligne d'arête, ne pas la supprimer
        return false;
    }

    public static void writeToFile(Tree tree, int minScore, boolean lastWordAdded, String filename) throws IOException {
        Path path = Paths.get(filename);

        // Création ou réécriture du fichier
        try (BufferedWriter writer = Files.newBufferedWriter(path, StandardOpenOption.CREATE, StandardOpenOption.WRITE)) {
            // Écrire le score minimal sur la première ligne
            writer.write("Score minimal: " + minScore);
            writer.newLine();

            // Écrire l'état d'ajout du dernier mot sur la deuxième ligne
            writer.write("Dernier mot ajouté: " + lastWordAdded);
            writer.newLine();

            // Écrire chaque arête de l'arbre ligne par ligne
            List<Edge> edges = tree.getEdges();
            for (Edge edge : edges) {
                writer.write(edge.getWord1().toString() + "-" + edge.getWord2().toString() + ", " + edge.getSimilarity());
                writer.newLine();
            }
        }

        // Définir les permissions du fichier en rw-rw-rw
        // Cela ne fonctionnera que sur des systèmes Unix-like
        try {
            Set<PosixFilePermission> perms = PosixFilePermissions.fromString("rw-rw-rw-");
            Files.setPosixFilePermissions(path, perms);
        } catch (UnsupportedOperationException e) {
            System.out.println("Le système de fichiers ne supporte pas les permissions POSIX.");
        }
    }
}
