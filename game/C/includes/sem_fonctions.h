#ifndef EXPORT_H
#define EXPORT_H
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <assert.h>
#include <math.h>

// Extrait le vecteur d'un mot
void extractVector(FILE *f, long offset, long size, float *outputVector);

// Calcule la norme d'un vecteur
double vectorNorm(float *vec, int size);

// Calcule le produit scalaire de deux vecteurs
double vectorDotProduct(float *vec1, float *vec2, int size);

// Calcule la similarité cosinus entre deux vecteurs
double calculateCosineSimilarity(float *vec1, float *vec2, int size);

// Calcule la similarité sémantique entre deux mots
double computeSemanticSimilarity(char *vectorFile, char *lexiconFile, char *word1, char *word2);

#endif