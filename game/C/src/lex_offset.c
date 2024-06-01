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

// Fonction pour lire un ArrayCell à partir du fichier à un index spécifié
int readArrayCell(FILE *file, long index, ArrayCell *cell) {
    fseek(file, sizeof(LexHeader) + index * sizeof(ArrayCell), SEEK_SET);
    if (fread(cell, sizeof(ArrayCell), 1, file) != 1) {
        perror("Erreur de lecture du fichier .lex");
        return -1; // Indique une erreur
    }
    return 0; // Succès
}

long findWord(const char *filename, const char *word) {
    FILE *file = fopen(filename, "rb");
    if (file == NULL) {
        perror("Erreur d'ouverture du fichier .lex");
        return -1; // Indique une erreur
    }

    LexHeader header;
    if (fread(&header, sizeof(LexHeader), 1, file) != 1) {
        perror("Erreur de lecture de l'en-tête du fichier .lex");
        fclose(file);
        return -1; // Indique une erreur
    }

    long index = 0; // Commence à la racine
    ArrayCell cell;

    for (int wordIndex = 0; word[wordIndex] != '\0'; ++wordIndex) {
        int found = 0; // Indicateur de réussite

        // Si on est au début du mot, on commence à la racine; sinon, au premier enfant
        long searchIndex = (wordIndex == 0) ? 0 : cell.firstChild;
        
        while (searchIndex != -1) {
            if (readArrayCell(file, searchIndex, &cell) == -1) {
                fclose(file);
                return -1; // Indique une erreur
            }

            if (cell.elem == word[wordIndex]) {
                found = 1; // Caractère trouvé
                break; // Sortir de la boucle
            }
            searchIndex++; // Passez au frère suivant
        }

        if (!found) {
            fclose(file);
            return -1; // Mot non trouvé
        }
    }

    // Vérifie si le mot est terminé par '\0' dans le fichier .lex
    if (cell.firstChild != -1) {
        ArrayCell endCell;
        if (readArrayCell(file, cell.firstChild, &endCell) != -1 && endCell.elem == '\0') {
            fclose(file);
            return endCell.offset; // Retourne l'offset du mot
        }
    }

    fclose(file);
    return -1; // Mot non trouvé
}