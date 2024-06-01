#ifndef SEM_SIMILARITY_H
#define SEM_SIMILARITY_H
#include "cstree.h"
#include "export.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>

StaticTree readLexFile(const char *filename);

long findWord(StaticTree* st, const char* word);


#endif