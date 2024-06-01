#ifndef GAME_H
#define GAME_H

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define MAX_WORDS 10

// Structure pour stocker les informations sur chaque mot
typedef struct {
    char word[100]; // Assurez-vous que la longueur est suffisante pour tous les mots
    long offset;
    double semanticDistance; // Distance sémantique avec le mot ajouté
    int orthographicDistance; // Distance orthographique avec le mot ajouté
} WordInfo;

#endif