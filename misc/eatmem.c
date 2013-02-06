#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define EATMEMSIZE    96

int main (int argc, char *argv[]) {
    int n = 0;
    char *p;
    int mem = 0, maxmem;

    if (argc > 0) {
        if (sscanf(argv[1], "%d", &maxmem) != 1)
            maxmem = EATMEMSIZE;
    }
    printf("Asking for %dGB....\n", maxmem);
    while (1) {
        if ((p = malloc(1<<20)) == NULL) {
             printf("malloc failure after %d MiB\n", n);
             return 0;
        }
        memset (p, 0, (1<<20));
        printf ("got %d MiB\n", ++n);
        mem++;
        if (mem > (maxmem * 1024))
             break;
    }
    printf("Total memory use is %d\n", mem);
    while (1) {
         printf("\r");
         sleep(30);
    }
}
