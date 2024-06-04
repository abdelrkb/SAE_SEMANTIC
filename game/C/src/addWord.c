#include "../includes/sem_fonctions.h"
#include "../includes/lev_fonctions.h"
#include "../includes/game.h"
#include "../includes/lex_offset.h"
#include "../includes/cstree.h"
#include "../includes/export.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

int main(int argc, char **argv) {
    if (argc == 1) {
        printf("Authors : \nChamsedine AMOUCHE\nELYAS MALOUM\nTHAMIZ SARBOUDINE\nABDELNOUR REKKAB\n");

        return EXIT_FAILURE;
    }

    if (argc==2 && strcmp("--help", argv[1])==0){
        printf("Usage:\n");
        printf(" %s dictionary lexicon_path ouput_dir word pseudo\n", argv[0]);
        printf(" where dictionary is the word2vec file (.bin), lexicon_path is the path to the lexicon file, output_dir is the directory for the output file, word is the word to add in the game and pseudo is the pseudo of the player.\n");
        return EXIT_FAILURE;
    }

    if (argc != 6) {
        return EXIT_FAILURE;
    }

    const char *output_dir = argv[3];
    const char *lexicon_path = argv[2];
    StaticTree st = readLexFile(lexicon_path);
    long newWordOffset = findWord(&st, argv[4]);

    // Récupérer le pseudo à partir des arguments du programme
    char *pseudo = argv[5];

    // Préparer le nom de fichier
    char filename[100]; // Assurez-vous que le tableau est assez grand
    sprintf(filename, "%s/game_data_%s.txt", output_dir, pseudo);
    FILE *file = fopen(filename, "r");
    if (!file) {
        fprintf(stderr, "Failed to open game data file: %s/game_data_%s.txt\n", output_dir, pseudo);
        return EXIT_FAILURE;
    }

    char line[256];
    char contentBeforeList[1000] = {0};
    char distanceContent[1000] = {0};
    WordInfo existingWords[MAX_WORDS];
    int existingWordCount = 0;

    rewind(file);
    bool readingWordsSection = false;
    bool reachedDistancesSection = false;
    while (fgets(line, sizeof(line), file)) {
        if (strcmp(line, "Liste des mots :\n") == 0) {
            readingWordsSection = true;
            continue;
        }

        if (strcmp(line, "Distance entre les mots :\n") == 0) {
            reachedDistancesSection = true;
            break; // Stop reading as we reached distances section
        }

        if (readingWordsSection && !reachedDistancesSection && line[0] != '\n') {
            char *word = strtok(line, ", ");
            char *offsetStr = strtok(NULL, " offset: \n");
            if (word && offsetStr && existingWordCount < MAX_WORDS) {
                long offset = atol(offsetStr);
                if (offset > 0) { // Vérification de l'offset
                    strcpy(existingWords[existingWordCount].word, word);
                    existingWords[existingWordCount].offset = offset;
                    //printf("Read word: %s, Offset: %ld\n", existingWords[existingWordCount].word, existingWords[existingWordCount].offset); // Debug line
                    existingWordCount++;
                }
            }
        }
    }

    // Lire et stocker le contenu jusqu'à "Liste des mots"
    rewind(file);
    while (fgets(line, sizeof(line), file)) {
        if (strcmp(line, "Liste des mots :\n") == 0) {
            strcat(contentBeforeList, line);
            break;
        }
        strcat(contentBeforeList, line);
    }

    // Lire et stocker le contenu de "Distance entre les mots"
    bool readingDistancesSection = false;
    rewind(file);  // Repositionner le curseur au début du fichier
    while (fgets(line, sizeof(line), file)) {
        if (strcmp(line, "Distance entre les mots :\n") == 0) {
            readingDistancesSection = true;
            continue;
        }

        if (readingDistancesSection) {
            strcat(distanceContent, line);
        }
    }

    fclose(file);

    // Réouverture du fichier en mode écriture
    file = fopen(filename, "w");
    if (!file) {
        perror("Failed to open game data file for writing");
        return EXIT_FAILURE;
    }

    // Écrire le contenu avant "Liste des mots"
    fputs(contentBeforeList, file);

    // Écrire la liste des mots existants
    for (int i = 0; i < existingWordCount; i++) {
        fprintf(file, "%s, offset: %ld\n", existingWords[i].word, existingWords[i].offset);
    }

    // Ajouter le nouveau mot et son offset
    fprintf(file, "%s, offset: %ld\n", argv[4], newWordOffset);

    // Écrire la section "Distance entre les mots"
    fprintf(file, "Distance entre les mots :\n");

    // Réécrire les distances existantes
    fputs(distanceContent, file);

    // Ajouter les nouvelles distances
    for (int i = 0; i < existingWordCount; i++) {
        double semanticDistance = computeSemanticSimilarity((char *)argv[1], (char *)lexicon_path, existingWords[i].word, (char *)argv[4]);
        int orthographicDistance = lev_similarity(existingWords[i].word, (char *)argv[4]);
        double averageDistance = (semanticDistance + orthographicDistance) / 2.0;

        fprintf(file, "%s-%s, distance: %.2f\n", existingWords[i].word, argv[4], averageDistance);
    }

    fclose(file);

    return EXIT_SUCCESS;
}
