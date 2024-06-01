#ifndef SEM_SIMILARITY_H
#define SEM_SIMILARITY_H
#include "cstree.h"
#include "export.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>

StaticTree readLexFile(const char *filename);

long findWord(const char *filename, const char *word);

int readArrayCell(FILE *file, long index, ArrayCell *cell);

#endif