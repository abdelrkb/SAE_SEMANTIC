#ifndef EXPORT_H
#define EXPORT_H

#include <stddef.h> // Pour inclure size_t
#include "cstree.h"


// Structure pour le header du fichier .lex
typedef struct {
    size_t tableSize; // Taille du tableau
} LexHeader;


// Déclarez ici les prototypes des fonctions d'exportation
void exportStaticTreeToFile(StaticTree* st, const char* filename);

#endif // EXPORT_H