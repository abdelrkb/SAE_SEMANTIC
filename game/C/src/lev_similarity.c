#include "../includes/lev_fonctions.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <assert.h>

int main(int argc, char *argv[]) {
    if (argc == 1) {
        printf("Authors : \nChamsedine AMOUCHE\nCédric MARIYA-CONSTANTINE\nTHAMIZ SARBOUDINE\nYACINE ZEMOUCHE\n");

        return EXIT_FAILURE;
    }

    if (argc==2 && strcmp("--help", argv[1])==0){
        printf("Usage:\n");
        printf(" %s word1 word2\n", argv[0]);
        printf(" where word1, word2 are the words for which you want to calculate spelling similarity\n");
        return EXIT_FAILURE;
    }

    if (argc != 3) {
        return 1;
    }
    
    char *word1 = argv[1];
    char *word2 = argv[2];

    int similarity_score = lev_similarity(word1, word2);
    printf("%d%%\n", similarity_score);

    return 0;
}

