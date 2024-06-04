#include "../includes/sem_fonctions.h"
#include "../includes/lex_offset.h"
#include "../includes/cstree.h"
#include "../includes/export.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <assert.h>
#include <math.h>

int main(int argc, char **argv) {
    if (argc == 1) {
        printf("Authors : \nChamsedine AMOUCHE\nCédric MARIYA-CONSTANTINE\nTHAMIZ SARBOUDINE\nYACINE ZEMOUCHE\n");

        return EXIT_FAILURE;
    }

    if (argc==2 && strcmp("--help", argv[1])==0){
        printf("Usage:\n");
        printf(" %s dictionary dictionary2 word1 word2\n", argv[0]);
        printf(" where dictionary is the word2vec file (.bin), dictionary2 is the lexicographic tree (.lex) and word1, word2 are the words for which you want to calculate semantic similarity\n");
        return EXIT_FAILURE;
    }

    if(argc != 5){
        return EXIT_FAILURE;
    }

    // Récupération des arguments de ligne de commande
    char *vectorFile = argv[1];
    char *lexiconFile = argv[2];
    char *word1 = argv[3];
    char *word2 = argv[4];

    // Calcul de la similarité sémantique entre les deux mots
    double semanticSimilarity = computeSemanticSimilarity(vectorFile, lexiconFile, word1, word2);

    // Affichage de la similarité sémantique
    printf("%f\n", semanticSimilarity);

    return EXIT_SUCCESS;
}