#ifndef LEV_FONCTIONS_H
#define LEV_FONCTIONS_H
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <assert.h>

typedef struct {
    int lenS;
    int lenT;
    int * tab;
}
LevArray;

int min(int a, int b);

LevArray init(int lenS, int lenT);

void set(LevArray a, int indexS, int indexT, int val);

int get(LevArray a, int indexS, int indexT);

int levenshtein(char * S, char * T);

int lev_similarity(char *word1, char *word2);


#endif