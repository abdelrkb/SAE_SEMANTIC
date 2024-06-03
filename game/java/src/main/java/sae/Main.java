package sae;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;


public class Main {
    public static List<Edge> main(String[] args) {
        if (args.length < 2) {
            System.err.println("Usage: java Main <pathToJavaFile> <pathToCFile>");
            System.exit(1);
        }

        String pathToJavaFile = args[0];
        String pathToCFile = args[1];

        System.out.println("Début du programme");

        try {
            System.out.println("Tentative de chargement des données du fichier Java...");
            LoadingFromJava loadedJavaData = null;
            try {
                loadedJavaData = LoadingFromJava.loadFromFile(pathToJavaFile);
                if (loadedJavaData != null) {
                    System.out.println("Données Java chargées avec succès.");
                } else {
                    System.out.println("Le fichier Java est vide ou les données sont incomplètes.");
                }
            } catch (IOException e) {
                System.out.println("Fichier Java vide ou non lisible, ou erreur de lecture: " + e.getMessage());
            }

            if (loadedJavaData == null) {
                System.out.println("Chargement des données du fichier C Si java null:");
                LoadingFromC loadedCData = LoadingFromC.loadMst(pathToCFile);
                System.out.println();
                Word startWordC = loadedCData.getStartWord();
                System.out.println("fichier C startword: " + startWordC);
                Word endWordC = loadedCData.getEndWord();
                System.out.println("fichier C endWord: " + endWordC);
                List<Edge> edgesC = loadedCData.getEdges();
                System.out.println("fichier C les edges: " + edgesC);

                Tree tree = new Tree(startWordC, endWordC, edgesC);
                System.out.println("Arbre créé à partir des données du fichier C.");

                double score = tree.calculatePathScore(edgesC);
                System.out.println("Score calculé: " + score);

                LoadingFromJava.DataWriter.writeJavaFile(pathToJavaFile, score, startWordC, endWordC, edgesC, new ArrayList<>());
                System.out.println("Fichier Java réécrit avec les nouvelles données initiales.");

                return edgesC;
            } else {
                double javaScore = loadedJavaData.getScore();
                System.out.println("Fichier Java javaScore: " + javaScore);
                Tree javaTree = loadedJavaData.getTree();
                System.out.println("Fichier Java le tree: " + javaTree);
                List<Word> javaBannedWords = loadedJavaData.getBannedWords();
                System.out.println("Fichier Java mot bannis: " + javaBannedWords);
                System.out.println("Données Java existantes utilisées.");

                LoadingFromC loadedCData = LoadingFromC.loadMst(pathToCFile);
                List<Edge> edgesC = loadedCData.getEdges();
                System.out.println("Fichier C edges of C: " + edgesC);
                System.out.println("Données du fichier C chargées pour mise à jour.");

                javaTree = javaTree.buildNewTree(javaScore, javaBannedWords, edgesC);
                System.out.println("Fichier Java javaTree apres détection de cycle: " + javaTree);
                System.out.println("Arbre mis à jour avec les nouvelles arêtes.");

                List<Edge> optimalPath = javaTree.findPathUsingPrim();
                System.out.println("Fichier Java OptimalPath: " + optimalPath);
                double newScore = javaTree.calculatePathScore(optimalPath);
                System.out.println("Nouveau score calculé après mise à jour: " + newScore);

                Word lastWordFromC = loadedCData.getWords().get(loadedCData.getWords().size() - 1);
                System.out.println("Fichier C last word fromC: " + lastWordFromC);
                if (!optimalPath.stream().anyMatch(edge -> edge.contains(lastWordFromC))) {
                    javaBannedWords.add(lastWordFromC);
                    System.out.println("Nouveau mot banni ajouté: " + lastWordFromC);
                }

                LoadingFromJava.DataWriter.writeJavaFile(pathToJavaFile, newScore, javaTree.getStartWord(), javaTree.getEndWord(), javaTree.getEdges(), javaBannedWords);
                System.out.println("Fichier Java réécrit avec les données mises à jour.");

                return optimalPath;
            }
        } catch (IOException e) {
            System.err.println("Error processing files: " + e.getMessage());
            e.printStackTrace();
        }

        System.out.println("Fin du programme.");
        return null;
    }
}