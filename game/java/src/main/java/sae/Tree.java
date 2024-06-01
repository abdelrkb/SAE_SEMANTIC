package sae;

import java.util.*;
import java.util.stream.Collectors;

public class Tree {

    private List<Edge> edges;
    private Word startWord;
    private Word endWord;

    // Constructeur pour initialiser un arbre avec des mots de départ et de fin et une liste d'arêtes
    public Tree(Word startWord, Word endWord, List<Edge> edges) {
        this.startWord = startWord;
        this.endWord = endWord;
        this.edges = edges != null ? new ArrayList<>(edges) : new ArrayList<>();
    }

    // Getter pour obtenir les arêtes de l'arbre
    public List<Edge> getEdges() {
        return new ArrayList<>(edges);  // Retourne une copie pour éviter la modification externe
    }

    // Getter pour le mot de départ
    public Word getStartWord() {
        return startWord;
    }

    // Getter pour le mot de fin
    public Word getEndWord() {
        return endWord;
    }

    // Méthode pour ajouter une arête à l'arbre
    public void addEdge(Edge edge) {
        if (!edges.contains(edge)) {
            edges.add(edge);
        }
    }

    // Méthode pour supprimer une arête du tree
    public void removeEdge(Edge edge) {
        // Tente de supprimer l'arête et vérifie si l'opération a réussi
        boolean isRemoved = edges.remove(edge);
        if (isRemoved) {
            System.out.println("Edge removed successfully.");
        } else {
            System.out.println("Edge not found in the tree.");
        }
    }

    // Méthode pour vérifier si une arête est dans l'arbre
    public boolean contains(Edge edge) {
        return edges.contains(edge);
    }

    // Représentation sous forme de chaîne de caractères de l'arbre
    @Override
    public String toString() {
        StringBuilder sb = new StringBuilder();
        for (Edge edge : edges) {
            sb.append(edge.toString()).append("\n");
        }
        return sb.toString();
    }

    // Méthode permettant de récupérer la liste de mots d'un Tree
    public Set<Word> getAllWords() {
        Set<Word> words = new HashSet<>();
        for (Edge edge : edges) {
            words.add(edge.getWord1());
            words.add(edge.getWord2());
        }
        return words; // Retourne un ensemble de mots sans doublons
    }

    /**
     * Vérifie si un mot spécifique est présent dans les arêtes de l'arbre.
     * @param word Le mot à vérifier.
     * @return true si le mot est présent dans l'une des arêtes du Tree, false sinon.
     */
    public boolean containsWord(Word word) {
        // Parcourir chaque arête de l'arbre pour vérifier si le mot est présent
        for (Edge edge : edges) {
            if (edge.getWord1().equals(word) || edge.getWord2().equals(word)) {
                return true; // Retourner true dès que le mot est trouvé
            }
        }
        return false; // Retourner false si le mot n'est trouvé dans aucune arête
    }
    /**
     * Vérifie si une arête spécifique est présente dans les arêtes de l'arbre.
     * @param edge L'arête à vérifier.
     * @return true si l'arête est présente dans l'arbre, false sinon.
     */
    public boolean containsEdge(Edge edge) {
        // Parcourir chaque arête de l'arbre pour vérifier si l'arête donnée est présente
        for (Edge existingEdge : edges) {
            if (existingEdge.equals(edge)) {
                return true; // Retourner true dès que l'arête est trouvée
            }
        }
        return false; // Retourner false si l'arête n'est trouvée dans aucune arête
    }

    public Edge findMinSimilarityEdge(List<Edge> edges) {
        if (edges == null || edges.isEmpty()) {
            return null; // Retourne null si la liste est vide ou non initialisée
        }
        // Utilisation de Stream pour trouver l'arête avec la similarité la plus faible
        Optional<Edge> minEdge = edges.stream()
                .min(Comparator.comparingDouble(Edge::getSimilarity));

        return minEdge.orElse(null); // Retourne l'arête trouvée ou null si la liste est vide
    }

    // Méthode pour trouver les arêtes qui forment un cycle en utilisant DFS
    public List<Edge> findCycleEdges() {
        Set<Word> visited = new HashSet<>();
        Map<Word, Word> parent = new HashMap<>();
        List<Edge> cycleEdges = new ArrayList<>();

        // Tentative de parcourir chaque mot non visité de l'arbre
        for (Word word : getAllWords()) {
            if (!visited.contains(word)) {
                if (dfs(word, visited, parent, cycleEdges, null)) {
                    System.out.println("la liste des edges formant un cycle: "+ cycleEdges);
                    return cycleEdges; // Si un cycle est trouvé, retourne immédiatement les arêtes du cycle
                }
            }
        }

        return cycleEdges; // Retourne une liste vide s'il n'y a pas de cycle
    }

    // Helper DFS pour trouver un cycle
    private boolean dfs(Word current, Set<Word> visited, Map<Word, Word> parent, List<Edge> cycleEdges, Word parentWord) {
        visited.add(current);
        parent.put(current, parentWord);

        // Explorer tous les voisins du mot actuel
        for (Edge edge : edges) {
            if (edge.contains(current)) {
                Word neighbor = edge.getWord1().equals(current) ? edge.getWord2() : edge.getWord1();

                if (!visited.contains(neighbor)) {
                    parent.put(neighbor, current);
                    if (dfs(neighbor, visited, parent, cycleEdges, current)) {
                        cycleEdges.add(edge);
                        return true; // Continue à remonter le cycle
                    }
                } else if (!neighbor.equals(parent.get(current))) {
                    // Si on visite un nœud déjà vu qui n'est pas le parent, on a trouvé un cycle
                    cycleEdges.add(edge);
                    return true; // Cycle détecté
                }
            }
        }
        return false;
    }

    /**
     * Utilise l'algorithme de Prim pour trouver le chemin de coût minimal entre startWord et endWord.
     * @return La liste des arêtes formant le chemin de coût minimal.
     */
    public List<Edge> findPathUsingPrim() {
        // Initialisation des structures pour l'algorithme de Prim
        Map<Word, Edge> edgeTo = new HashMap<>(); // Meilleure arête connue pour chaque mot
        Map<Word, Double> distTo = new HashMap<>(); // Distance minimale à l'arbre pour chaque mot
        PriorityQueue<Word> pq = new PriorityQueue<>(Comparator.comparing(distTo::get));
        Set<Word> visited = new HashSet<>(); // Ensemble pour suivre les mots déjà ajoutés à l'arbre

        // Initialiser les distances à l'infini sauf pour le startWord
        for (Word word : getAllWords()) {
            distTo.put(word, Double.POSITIVE_INFINITY);
        }
        distTo.put(startWord, 0.0); // La distance au startWord est zéro
        pq.add(startWord); // Commencer avec le startWord

        // Boucle principale de l'algorithme de Prim
        while (!pq.isEmpty()) {
            Word current = pq.poll(); // Extraire le mot avec la distance minimale à l'arbre
            visited.add(current); // Marquer comme visité (ajouté à l'arbre)
            updateDistances(current, edgeTo, distTo, pq, visited); // Mettre à jour les distances des voisins
        }

        // Construire et retourner le chemin résultant en suivant les arêtes à partir du endWord
        return buildPathTo(endWord, edgeTo);
    }

    /**
     * Met à jour les distances des voisins du mot courant pour l'algorithme de Prim.
     */
    private void updateDistances(Word current, Map<Word, Edge> edgeTo, Map<Word, Double> distTo,
                                 PriorityQueue<Word> pq, Set<Word> visited) {
        for (Edge edge : edges) {
            if (edge.contains(current)) {
                Word neighbor = edge.getWord1().equals(current) ? edge.getWord2() : edge.getWord1();
                if (visited.contains(neighbor)) continue; // Ignorer si déjà dans l'arbre
                double newDist = edge.getSimilarity();
                if (newDist < distTo.getOrDefault(neighbor, Double.POSITIVE_INFINITY)) {
                    distTo.put(neighbor, newDist);
                    edgeTo.put(neighbor, edge);
                    pq.remove(neighbor); // Mise à jour de la priorité dans la file
                    pq.add(neighbor);
                }
            }
        }
    }

    /**
     * Construit le chemin de coût minimal à partir du endWord en utilisant les arêtes stockées.
     */
    private List<Edge> buildPathTo(Word end, Map<Word, Edge> edgeTo) {
        LinkedList<Edge> path = new LinkedList<>();
        for (Word at = end; edgeTo.containsKey(at); at = edgeTo.get(at).other(at)) {
            path.addFirst(edgeTo.get(at)); // Ajouter l'arête au début de la liste pour inverser le chemin
        }
        return path;
    }

    /**
     * Calcule le score du chemin en prenant l'arête avec la plus petite similarité.
     * @param path La liste des arêtes constituant le chemin.
     * @return Le score du chemin, qui est la plus petite similarité trouvée dans les arêtes du chemin.
     */
    public float calculatePathScore(List<Edge> path) {
        if (path == null || path.isEmpty()) {
            throw new IllegalArgumentException("Path cannot be null or empty");
        }

        float minSimilarity = Float.MAX_VALUE; // Définir à une valeur initialement très haute

        // Parcourir chaque arête dans le chemin pour trouver la plus petite similarité
        for (Edge edge : path) {
            if (edge.getSimilarity() < minSimilarity) {
                minSimilarity = edge.getSimilarity();
            }
        }

        return minSimilarity; // Retourner la plus petite similarité comme score du chemin
    }


    /**
     * Construit un nouvel arbre en ajoutant des arêtes à partir d'une liste donnée après les avoir filtrées et triées.
     * @param score Le score minimum pour conserver une arête.
     * @param bannedWords La liste des mots bannis.
     * @param edgesOfC La liste initiale des arêtes à considérer.
     * @return Le tree après ajout des arêtes valides.
     */
    public Tree buildNewTree(double score, List<Word> bannedWords, List<Edge> edgesOfC) {
        System.out.println("le tree (print dans la fonction bnt avant traitement) : "+ this);
        // Filtrer les arêtes selon les critères spécifiés
        List<Edge> filteredEdges = edgesOfC.stream()
                .filter(edge -> edge.getSimilarity() >= score) // Supprimer les arêtes sous le score
                .filter(edge -> !bannedWords.contains(edge.getWord1()) && !bannedWords.contains(edge.getWord2())) // Supprimer les arêtes avec des mots bannis
                .filter(edge -> !this.contains(edge)) // Supprimer les arêtes déjà présentes dans l'arbre
                .collect(Collectors.toList());

        System.out.println("la liste filtrer des edges"+ filteredEdges);

        // Trier les arêtes restantes par ordre décroissant de similarité
        filteredEdges.sort((e1, e2) -> Double.compare(e2.getSimilarity(), e1.getSimilarity()));

        // Ajouter les arêtes triées à l'arbre en vérifiant les cycles
        for (Edge edge : filteredEdges) {
            this.addEdge(edge);
            if (this.findCycleEdges().size() > 0) { // Vérifier s'il y a un cycle
                List<Edge> lecycle= this.findCycleEdges();
                Edge minEdge = this.findMinSimilarityEdge(lecycle); // Trouver l'arête avec la similarité la plus faible dans le cycle
                System.out.println("l'edge min:"+ minEdge);
                this.removeEdge(minEdge); // Supprimer cette arête
            }
        }
        System.out.println("le tree (print dans la fonction bnt) : "+ this);
        return this;
    }

}




