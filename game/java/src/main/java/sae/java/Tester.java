package sae.java;

import java.util.ArrayList;
import java.util.List;
import java.util.Scanner;

public class Tester {
    public static void main(String[] args) {
        List<Edge> edges = new ArrayList<>();
        Tree tree = new Tree(new Word("A"), new Word("B"));
        edges = new ArrayList<>();
        edges.add(new Edge(new Word("A"), new Word("B"), 10));
        tree.addWord(edges);
        edges.clear();
        // Ajouter "C" à l'arbre
        edges.add(new Edge(new Word("C"), new Word("A"), 30));
        edges.add(new Edge(new Word("C"), new Word("B"), 20));
        tree.addWord(edges);
        edges.clear();

        // Ajouter "D" à l'arbre
        edges.add(new Edge(new Word("D"), new Word("A"), 5));
        edges.add(new Edge(new Word("D"), new Word("B"), 50));
        edges.add(new Edge(new Word("D"), new Word("C"), 40));
        tree.addWord(edges);
        edges.clear();

        System.out.println(tree);
    }
}
