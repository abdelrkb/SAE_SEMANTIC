#include "../includes/lex_offset.h"
#include "../includes/cstree.h"
#include "../includes/export.h"
#include <stdio.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <assert.h>
#include <math.h>

const long long max_size = 200;
const long long max_w = 50;

// Extrait le vecteur associé à un mot et le stocke dans le tableau outputVector
void extractVector(FILE *f, long offset, long size, float *outputVector) {

    // Initialisation de variables
    char wordBuffer[10];
    float wordVector[max_size];
    char c[50];

    // Se positionner à l'endroit spécifié du fichier
    fseek(f, offset, SEEK_SET);

    // Lire le mot (jusqu'à l'espace ou la fin du fichier)
    int i = 0;
    while (1) { 
    if (i >= sizeof(c) - 1) { // Vérifiez que i ne dépasse pas les limites du tableau
        break;
    }
    c[i] = fgetc(f); 
    if (feof(f) || (c[i] == ' ')) break; 
    if ((c[i] != '\n')) i++; 
    }
    c[i] = '\0'; // Assurez-vous que la chaîne est terminée par '\0'


    // Initialiser le vecteur à zéro
    for (int a = 0; a < max_size; a++) { 
        wordVector[a] = 0;   
    }

    // Lire le vecteur du fichier et le copier dans outputVector
    for (int i = 0; i < max_size; i++) {
        fread(&wordVector[i], sizeof(float), 1, f);
        outputVector[i] = wordVector[i];
    }
}

// Calcule le produit scalaire de deux vecteurs
double vectorDotProduct(float *vec1, float *vec2, int size) {
    double sum = 0;
    // Somme des produits des composantes correspondantes des deux vecteurs
    for (int i = 0; i < size; i++) {
        sum += vec1[i] * vec2[i];
    }
    return sum;
}

// Calcule la norme d'un vecteur
double vectorNorm(float *vec, int size) {
    double sum = 0;
    // Somme des carrés de chaque composante du vecteur
    for (int i = 0; i < size; i++) {
        sum += vec[i] * vec[i];
    }
    // Retourne la racine carrée de la somme
    return sqrt(sum);
}

// Calcule la similarité cosinus entre deux vecteurs
double calculateCosineSimilarity(float *vec1, float *vec2, int size) {
    // Calcul des normes des deux vecteurs
    double norm1 = vectorNorm(vec1, size);
    double norm2 = vectorNorm(vec2, size);

    // Calcul du produit scalaire des vecteurs
    double dot = vectorDotProduct(vec1, vec2, size);

    // Calcul de la similarité cosinus (produit scalaire / produit des normes)
    double cos_similarity = dot / (norm1 * norm2);

    // Normalisation entre 0 et 100
    return (cos_similarity + 1) * 50;
}

// Calcule la similarité sémantique entre deux mots
double computeSemanticSimilarity(char *vectorFile, char *lexiconFile, char *word1, char *word2) {
    // Ouverture du fichier contenant les vecteurs
    FILE *f = fopen(vectorFile, "rbc");
    if (f == NULL) {
        perror("Error opening file");
        return EXIT_FAILURE;
    }

    // Chargement du fichier lexique
    StaticTree stImported = readLexFile(lexiconFile);
    // Trouver les offsets des mots dans le fichier lexique
    long offset1 = findWord(&stImported, word1);
    long offset2 = findWord(&stImported, word2);

    // Initialisation des vecteurs pour les deux mots
    float vec1[max_size], vec2[max_size];

    // Extraction des vecteurs pour les deux mots
    extractVector(f, offset1, max_size, vec1);
    extractVector(f, offset2, max_size, vec2);

    // Calcul de la similarité sémantique
    double similarity = calculateCosineSimilarity(vec1, vec2, max_size);
    return similarity;
}