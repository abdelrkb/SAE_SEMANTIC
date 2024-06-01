#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "../includes/cstree.h"
#include "../includes/export.h"

const long long max_size = 200; //dimension
const long long max_w = 50;

int main(int argc, char **argv) {
    if (argc == 1) {
        printf("Authors : \nChamsedine AMOUCHE\nCédric MARIYA-CONSTANTINE\nTHAMIZ SARBOUDINE\nYACINE ZEMOUCHE\n");

        return EXIT_FAILURE;
    }

    if (argc==2 && strcmp("--help", argv[1])==0){
        printf("Usage:\n");
        printf(" %s dictionary\n", argv[0]);
        printf(" where dictionary is the word2vec file (.bin)\n");
        return EXIT_FAILURE;
    }

    if (argc != 2) {
        return 1;
    }

    FILE *f;
    char file_name[max_w];
    long long words, size, a;
    CSTree t = NULL;

    strcpy(file_name, argv[1]);
    f = fopen(file_name, "rb");
    if (f == NULL) {
        printf("Fichier non trouvé.\n");
        return -1;
    }

    fscanf(f, "%lld %lld", &words, &size);

    char *vocab = (char *)malloc((long long)words * max_w * sizeof(char));
    if (vocab == NULL) {
        printf("Erreur d'allocation mémoire pour vocab.\n");
        fclose(f);
        return -1;
    }

    for (a = 0; a < words; a++) {
        long long b = 0;
        while (1) {
            vocab[a * max_w + b] = fgetc(f);
            if (feof(f) || (vocab[a * max_w + b] == ' ')) break;
            if ((b < max_w) && (vocab[a * max_w + b] != '\n')) b++;
        }
        vocab[a * max_w + b] = 0;

        fseek(f, sizeof(float) * size, SEEK_CUR); // Ignorer les vecteurs

        long offset = ftell(f) - strlen(&vocab[a * max_w]) - 1;
        insertWord(&t, &vocab[a * max_w], offset);
    }

    fclose(f);
    free(vocab);

    StaticTree st = exportStaticTree(t);
    exportStaticTreeToFile(&st, "arbre_lexicographique.lex");

    printf("Le fichier .lex a été créé avec succès.\n");

    return 0;
}
