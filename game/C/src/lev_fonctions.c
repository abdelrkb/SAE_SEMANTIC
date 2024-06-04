#include "../includes/lev_fonctions.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <assert.h>

//minimum de deux entiers
int min(int a, int b) {
    return a < b ? a : b;
}

//initialiser un tableau pour des chaînes d'une taille donnée
LevArray init(int lenS, int lenT) {
    LevArray a;
    //on stocke les dimensions
    a.lenS = lenS;
    a.lenT = lenT;
    //allocation d'un tableau (1D) de lenS*lenT entiers
    a.tab = malloc(lenS * lenT * sizeof(int));
    //on vérifie que l'allocation s'est bien passée
    assert(a.tab != NULL); 
    return a;

}

//set: insérer une valeur dans le tableau
void set(LevArray a, int indexS, int indexT, int val) {
    //vérification des indices
    assert(indexS >= 0 && indexS < a.lenS && indexT >= 0 && indexT < a.lenT);
    assert(a.tab!=NULL); 
    a.tab[indexT * a.lenS + indexS] = val;
}

//Q1 get: renvoie la valeur correspondant à des indices donnés
// i+1 pour les requêtes du type get(a, -1, i) ou get (a, i, -1)
int get(LevArray a, int indexS, int indexT) {
     if (indexS == -1) {
         return indexT+1;
    } else if (indexT == -1) {
         return indexS+1;
    }
    return a.tab[indexT * a.lenS + indexS];
}

//Fonction pour calculer la distance de levenshtein
int levenshtein(char * S, char * T) {
    //utiliser strlen pour obtenir la longueur des chaˆınes
    int sizeS = strlen( S );
    int sizeT = strlen( T );
    //cr ́eer un tableau du type LevArray
    LevArray a = init(sizeS, sizeT);
    //parcourir le tableau en le remplissant au fur et `a mesure
    for (int i=0; i<sizeS; i++) {
        for (int j=0; j<sizeT; j++) {
            int val;
            if (S[i]==T[j]) {
                val = get(a, i-1, j-1);
            } else {
                val = get(a, i-1, j-1) + 1;
            }
            val = min(val, get(a, -1, j) +1);

            val = min(val, get(a, i, j-1) + 1);

            set(a, i, j, val);
        }
    }
    //r ́ecup ́erer la distance `a la fin du tableau
    return get(a, sizeS-1, sizeT-1);
}

// Fonction pour calculer la similarité orthographique
int lev_similarity(char *word1, char *word2) {
    int len1 = strlen(word1);
    int len2 = strlen(word2);
    int longest = len1 > len2 ? len1 : len2;

    if (longest == 0) return 100; // Si les deux mots sont vides, ils sont identiques

    int distance = levenshtein(word1, word2);
    return 100 * (1 - (double)distance / longest);
}