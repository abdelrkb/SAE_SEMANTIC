#include "../includes/cstree.h"
#include "../includes/export.h"
#include <stdio.h>
#include <stdlib.h>

// Fonction d'export
void exportStaticTreeToFile(StaticTree* st, const char* filename) {
    // Ouvrir le fichier en mode écriture binaire
    FILE* file = fopen(filename, "wb");
    
    if (file == NULL) {
        perror("Erreur lors de l'ouverture du fichier pour l'exportation");
        exit(EXIT_FAILURE);
    }
    
    // Créez et remplissez la structure LexHeader avec les métadonnées nécessaires
    LexHeader header;
    header.tableSize = st->nNodes;
    
    // Écrire le header dans le fichier
    size_t header_written = fwrite(&header, sizeof(LexHeader), 1, file);
    
    if (header_written != 1) {
        perror("Erreur lors de l'écriture du header dans le fichier");
        fclose(file);
        exit(EXIT_FAILURE);
    }
    
    // Écrire le contenu du tableau d'arbres statiques dans le fichier
    size_t elements_written = fwrite(st->nodeArray, sizeof(ArrayCell), st->nNodes, file);
    
    if (elements_written != st->nNodes) {
        perror("Erreur lors de l'écriture des données dans le fichier");
        fclose(file);
        exit(EXIT_FAILURE);
    }
    
    // Fermer le fichier
    fclose(file);
}