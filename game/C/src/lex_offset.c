#include "../includes/lex_offset.h"


// Lecture d'un fichier .lex
StaticTree readLexFile(const char *filename) {
    // Tenter d'ouvrir le fichier .lex en mode lecture binaire
    FILE *file = fopen(filename, "rb");
    if (file == NULL) {
        printf("Fichier non trouvé.\n");
        exit(EXIT_FAILURE);
    }

    // Lire l'en-tête du fichier pour obtenir le nombre de nœuds dans l'arbre
    LexHeader header;
    if (fread(&header, sizeof(LexHeader), 1, file) != 1) {
        printf("Erreur de lecture de l'en-tête.\n");
        fclose(file);
        exit(EXIT_FAILURE);
    }

    // Allouer de la mémoire pour le tableau de nœuds en fonction du nombre de nœuds
    ArrayCell *nodeArray = (ArrayCell *)malloc(header.tableSize * sizeof(ArrayCell));
    if (nodeArray == NULL) {
        printf("Impossible d'allouer de la mémoire pour le tableau de nœuds.\n");
        fclose(file);
        exit(EXIT_FAILURE);
    }

    // Lire les données de nœud du fichier dans le tableau de nœuds alloué
    if (fread(nodeArray, sizeof(ArrayCell), header.tableSize, file) != header.tableSize) {
        printf("Erreur de lecture des nœuds.\n");
        free(nodeArray);
        fclose(file);
        exit(EXIT_FAILURE);
    }

    // Fermer le fichier après avoir terminé de lire les données
    fclose(file);

    // Construire la structure StaticTree avec les données lues et retourner
    StaticTree st;
    st.nodeArray = nodeArray;
    st.nNodes = header.tableSize;
    return st;
}

// Rechercher un mot dans un StaticTree et renvoyer l'offset du mot s'il est présent.
long findWord(StaticTree* st, const char* word) {
    int index = 0; // Commencer à la racine de l'arbre

    for (int wordIndex = 0; word[wordIndex] != '\0'; ++wordIndex) { // Boucle qui parcourt tous les caractères du mot
        // Si on est au début du mot, on doit chercher à partir de la racine
        // Sinon, on cherche à partir du premier enfant du nœud actuel
        int searchIndex = (wordIndex == 0) ? 0 : st->nodeArray[index].firstChild;
        int found = 0; // 0 signifie que le caractère n'a pas été trouvé

        // Si nous ne sommes pas au début du mot, le premier enfant devient le nœud de départ
        // Sinon, l'indice actuel est le nœud de départ
        int siblingsToCheck = (wordIndex == 0) ? st->nNodes : st->nodeArray[index].nSiblings;

        // Parcourir les frères du nœud actuel si nous ne sommes pas à la racine
        // ou tous les nœuds si nous sommes à la racine
        for (int i = 0; i <= siblingsToCheck + 1; ++i) {
            if (st->nodeArray[searchIndex].elem == word[wordIndex]) {
                found = 1; // Caractère trouvé
                index = searchIndex; // Mettre à jour l'indice pour le prochain tour de boucle
                break; // Sortir de la boucle
            }
            searchIndex++; // Passer à l'indice suivant dans la recherche
        }

        if (!found) {
            return -1; // Si le caractère n'est pas trouvé, retourner -1
        }
    }

    // Après avoir trouvé tous les caractères, vérifier le caractère terminal '\0'
    if (st->nodeArray[index].firstChild != -1 && 
        st->nodeArray[st->nodeArray[index].firstChild].elem == '\0') {
        return st->nodeArray[st->nodeArray[index].firstChild].offset;
    }

    return -1; // Si le caractère de fin '\0' n'est pas trouvé, retourner -1
}