#include "../includes/sem_fonctions.h"
#include "../includes/lev_fonctions.h"
#include "../includes/game.h"
#include "../includes/lex_offset.h"
#include "../includes/cstree.h"
#include "../includes/export.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <fcntl.h>   // Pour open, O_WRONLY, O_CREAT, O_TRUNC
#include <unistd.h>  // Pour close
#include <sys/types.h>
#include <sys/stat.h>

int main(int argc, char **argv) {
     if (argc == 1) {
        printf("Authors : \nChamsedine AMOUCHE\nCédric MARIYA-CONSTANTINE\nTHAMIZ SARBOUDINE\nYACINE ZEMOUCHE\n");

        return EXIT_FAILURE;
    }

    if (argc==2 && strcmp("--help", argv[1])==0){
        printf("Usage:\n");
        printf(" %s dictionary word1 word2 pseudo\n", argv[0]);
        printf(" where dictionary is the word2vec file (.bin), word1 and word2 are the words to start with and pseudo is the pseudo for the player.\n");
        return EXIT_FAILURE;
    }

    if (argc != 5) {
        return EXIT_FAILURE;
    }

    StaticTree st = readLexFile("C/arbre_lexicographique.lex");

    // Utilisez le premier argument comme nom de fichier
    int wordCount = argc - 3;
    WordInfo *words = (WordInfo *)malloc(wordCount * sizeof(WordInfo));
    if (!words) {
        perror("Failed to allocate memory for words");
        return EXIT_FAILURE;
    }

    // Remplir les informations pour chaque mot
    for (int i = 0; i < wordCount; i++) {
        strcpy(words[i].word, argv[i + 2]);
        // Vous pouvez utiliser la fonction findWord ici pour trouver l'offset
        words[i].offset = findWord(&st, words[i].word);
    }

    // Calculer les distances entre chaque paire de mots
    for (int i = 0; i < wordCount; i++) {
        for (int j = i + 1; j < wordCount; j++) {
            // Utilisez computeSemanticSimilarity et lev_similarity pour calculer les distances
            words[i].semanticDistance = computeSemanticSimilarity(argv[1], "C/arbre_lexicographique.lex", argv[2], argv[3]);
            words[j].orthographicDistance = lev_similarity(argv[2], argv[3]);
        }
    }

    // Créer et écrire dans le fichier de partie
    char *pseudo = argv[4];
    char filename[100];
    sprintf(filename, "partie/game_data_%s.txt", pseudo);

    mode_t old_umask = umask(000);

    int fd = open(filename, O_WRONLY | O_CREAT | O_TRUNC, 0666);
    if (fd == -1) {
        perror("Failed to open file for writing");
        umask(old_umask);  // Restaurer le umask
        free(words);
        return EXIT_FAILURE;
    }


    FILE *file = fdopen(fd, "w");
    if (!file) {
        perror("Failed to open file for writing");
        close(fd);
        free(words);
        return EXIT_FAILURE;
    }

    // Écrire les mots de départs
    fprintf(file, "Mots de départ :\n");
    for (int i = 0; i < wordCount; i++) {
        fprintf(file, "%s,%ld\n", words[i].word, words[i].offset);
    }

    // Écrire  la liste des mots
    fprintf(file, "Liste des mots :\n");
    for (int i = 0; i < wordCount; i++) {
        fprintf(file, "%s, offset: %ld\n", words[i].word, words[i].offset);
    }

    // Écrire les distances entre les mots
    fprintf(file, "Distance entre les mots :\n");
    for (int i = 0; i < wordCount; i++) {
        for (int j = i + 1; j < wordCount; j++) {
            double averageDistance = (words[i].semanticDistance + words[j].orthographicDistance) / 2.0;
            fprintf(file, "%s-%s, distance: %.2f\n", words[i].word, words[j].word, averageDistance);
        }
    }

    fclose(file);
    free(words);
    umask(old_umask);  // Restaurer le umask

    return EXIT_SUCCESS;
}