#include "../includes/lex_offset.h"
#include "../includes/cstree.h"
#include "../includes/export.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>

int main(int argc, char **argv) {
    if (argc == 1) {
        printf("Authors : \nChamsedine AMOUCHE\nCédric MARIYA-CONSTANTINE\nTHAMIZ SARBOUDINE\nYACINE ZEMOUCHE\n");

        return EXIT_FAILURE;
    }

    if (argc==2 && strcmp("--help", argv[1])==0){
        printf("Usage:\n");
        printf(" %s dictionary word\n", argv[0]);
        printf(" where dictionary is the lexicographic tree (.lex) and word is the word to look for.\n");
        return EXIT_FAILURE;
    }

    if (argc != 3) {
        return EXIT_FAILURE;
    }

    // Utilisez le premier argument comme nom de fichier
    StaticTree st = readLexFile(argv[1]);

    long offset = findWord(&st, argv[2]);

    if (offset != -1) {
        printf("%ld\n", offset);
    } else {
        printf("%d\n", -1);
    }

    return 0;   
}