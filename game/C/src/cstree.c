#include <stdlib.h>
#include <stdio.h>
#include <limits.h>
#include "../includes/cstree.h"

//constante pour firstChild si aucun enfant
#define NONE -1

CSTree newCSTree(Element elem, unsigned long offset, CSTree firstChild, CSTree  nextSibling) {
    CSTree a = malloc(sizeof(Node));
    if (a == NULL) {
    	exit(EXIT_FAILURE);
    }
    a->elem = elem;
    a->offset = offset;
    a->firstChild = firstChild;
    a->nextSibling = nextSibling;

    return a;
}

void insertWord(CSTree *tree, const char *word, long offset) {
    if (*tree == NULL) {
        // Si l'arbre est nul, créez un nouveau nœud avec la première lettre du mot
        *tree = newCSTree(word[0], 0, NULL, NULL);
        tree = &((*tree)->firstChild); // Pointez vers le premier enfant
        word++; // Passez à la lettre suivante du mot
    }

    CSTree *current = tree;

    while (*word != '\0') {
        char letter = *word;

        // Recherchez le nœud correspondant dans les enfants du nœud actuel
        while (*current != NULL && (*current)->elem != letter) {
            current = &((*current)->nextSibling);
        }

        if (*current == NULL) {
            // Si le nœud n'a pas été trouvé, créez-le
            *current = newCSTree(letter, 0, NULL, NULL); // L'offset est temporairement mis à 0
        }

        // Avancez dans le mot et dans l'arbre
        word++;
        current = &((*current)->firstChild);

        // Si c'est la fin du mot, ajoutez le nœud '\0' avec l'offset correct
        if (*word == '\0') {
            if (*current == NULL) {
                // Créez le nœud '\0' s'il n'existe pas
                *current = newCSTree('\0', offset, NULL, NULL);
            } else {
                // Mettez à jour l'offset du nœud '\0'
                (*current)->offset = offset;
            }
        }
    }
}

// Imprime t en ordre préfixe 
void printPrefix(CSTree t){
	if (t==NULL) return;
	printf("%c ", t->elem);
	printPrefix(t->firstChild);
	printPrefix(t->nextSibling);
}

//Q4 Compte le nombre de noeuds dans l’arbre t.
int size(CSTree t){
    if (t == NULL) return 0;
    return 1 + size(t->firstChild) + size(t->nextSibling);
}

//Q5 Compte le nombre d’enfants du nœud t.
int nSiblingsFunction(CSTree t) {
	if (t==NULL) return 0;
	return 1 + nSiblingsFunction(t->nextSibling);
}

int nChildren(CSTree t) {
    if (t==NULL || t->firstChild==NULL) return 0;
    return nSiblingsFunction(t->firstChild);
}

//Q6 Fonction récursive auxiliaire pour exportStaticTree
// paramètres:
//  *st : un static tree partiellement rempli
//  t  : un noeud du CSTree original
//  index_for_t : la position à laquelle t doit être enregistré
//  nSiblings : le nombre de frères du noeud courant
//  *reserved_cells : le nombre de cellules "réservées" à cet état du parcours (passée par pointeur)
//  NB : au moment d'entrer dans la fonction, les cellules pour ce noeud et ses frères sont déjà réservervées, mais pas pour leurs enfants
void fill_array_cells(StaticTree* st, CSTree t, int index_for_t, int nSiblings, int* reserved_cells, long current_offset) {
    //printf("inserting node %c at position %d (%d siblings, %d reserved cells)\n", t->elem, index_for_t, nSiblings, *reserved_cells);
    
    int firstChild_index;
    if (t->firstChild != NULL) {
        firstChild_index = *reserved_cells;
    } else {
        firstChild_index = NONE;
    }
    st->nodeArray[index_for_t].elem = t->elem;
    st->nodeArray[index_for_t].nSiblings = nSiblings;
    st->nodeArray[index_for_t].firstChild = firstChild_index;
    
    // Stockez l'offset uniquement pour les nœuds étiquetés par '\0'
    if (t->elem == '\0') {
        st->nodeArray[index_for_t].offset = current_offset;
    } else {
        st->nodeArray[index_for_t].offset = -1; // Pour les autres nœuds, l'offset est -1
    }
    
    *reserved_cells += nChildren(t);
    
    /* Appel récursif */
    // Sur le frère
    if (t->nextSibling != NULL) {
        fill_array_cells(st, t->nextSibling, index_for_t+1, nSiblings-1, reserved_cells, t->nextSibling->offset);
    }
    // Sur le fils
    if (t->firstChild != NULL) {
        // Utilisez l'offset actuel pour le fils
        fill_array_cells(st, t->firstChild, firstChild_index, nChildren(t)-1, reserved_cells, t->firstChild->offset);
    }
}

//Crée un arbre statique avec le même contenu que t.
StaticTree exportStaticTree(CSTree t){ 
    StaticTree st={NULL, 0};
    int reserved_cells= 0;
    
    st.nNodes = size(t);
    st.nodeArray = malloc(st.nNodes * sizeof(ArrayCell));
    reserved_cells = nSiblingsFunction(t);
    
    int current_offset = t->offset; // Ajoutez une variable pour stocker l'offset actuel
    
    fill_array_cells(&st, t, 0, reserved_cells-1, &reserved_cells, current_offset);
    
    if (reserved_cells != st.nNodes && t!=NULL){
        printf("erreur lors de la création de l'arbre statique, taille finale incorrecte\n");
        exit(EXIT_FAILURE);
    }
    return st;   
}

//Fonctions d'impression d'un arbre statique:
// * version "jolie" avec un noeud par ligne, chaque noeud indenté sous son parent 
void printNicePrefixStaticTree_aux(StaticTree* st, int index, int depth){
    if (index==NONE)
        return;
    for (int i=0; i<depth; i++)
        printf("    ");
    printf("%c\n", st->nodeArray[index].elem);
    printNicePrefixStaticTree_aux(st, st->nodeArray[index].firstChild, depth+1);
    if (st->nodeArray[index].nSiblings>0)
        printNicePrefixStaticTree_aux(st, index+1, depth);    
}

void printNicePrefixStaticTree(StaticTree* st){
    if (st->nNodes>0) 
        printNicePrefixStaticTree_aux(st, 0, 0);

}
// *version "brute": imprime le contenu du tableau, dans l'ordre des cellules
void printDetailsStaticTree(StaticTree* st){
    int i;
    printf("elem     \t");
    for (i=0; i< st->nNodes; i++) 
        printf("%c\t", st->nodeArray[i].elem);
    printf("\nfirstChild\t");
    for (i=0; i< st->nNodes; i++) 
        printf("%d\t", st->nodeArray[i].firstChild);
    printf("\nnSiblings\t");
    for (i=0; i< st->nNodes; i++) 
        printf("%d\t", st->nodeArray[i].nSiblings);
    printf("\noffset    \t");
    for (i=0; i< st->nNodes; i++) 
        printf("%ld\t", st->nodeArray[i].offset);
    printf("\n");
}

//Q9 Recherche l’élément e parmi les éléments consécutifs de t aux positions from,..., from+len-1, 
//    renvoie la position de cet élément s’il existe, NONE sinon.
//    Si len=NONE, parcourir la cellule from et tous ses frères suivants 
//    cette fonction peut être itérative
int siblingLookupStatic(StaticTree* st, Element e, int from, int len){
    if(len == NONE){
        len = st->nodeArray[from].nSiblings+1;
    }
    for (int i = from; i<from+len; i++){
        if (st->nodeArray[i].elem == e){
            return i;
        }
    }
    return NONE;
}