#ifndef CSTREE_H
#define CSTREE_H
// Définition du type child Sibling Tree (CSTree)

typedef char Element;

//constante pour firstChild si aucun enfant
#define NONE -1 

typedef struct node{
    Element elem;
    unsigned long offset; // Nouveau champ pour stocker l'offset.
    struct node* firstChild;
    struct node* nextSibling;
} Node;
typedef Node* CSTree;

typedef struct {
    Element elem;
    unsigned int firstChild;
    unsigned int nSiblings;
    unsigned long offset; // Nouveau champ pour stocker l'offset.
} ArrayCell;
typedef struct {
    ArrayCell* nodeArray;
    unsigned int nNodes;
} StaticTree;

CSTree newCSTreeOld(Element elem, CSTree firstChild, CSTree  nextSibling);

CSTree newCSTree(Element elem, unsigned long offset,CSTree firstChild, CSTree  nextSibling);

CSTree example();

void insertWord(CSTree *tree, const char *word, long offset);


void printPrefix(CSTree t);

int size(CSTree t);

int nSiblingsFunction(CSTree t);

int nChildren(CSTree t);

void fill_array_cells(StaticTree* st, CSTree t, int index_for_t, int nSiblings, int* reserved_cells, long current_offset);

StaticTree exportStaticTree(CSTree t);

void printNicePrefixStaticTree_aux(StaticTree* st, int index, int depth);

void printNicePrefixStaticTree(StaticTree* st);

void printDetailsStaticTree(StaticTree* st);

int siblingLookupStatic(StaticTree* st, Element e, int from, int len);

#endif