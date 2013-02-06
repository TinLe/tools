PROG=eatmem
CC=gcc
CFLAGS=-O -g

.c.o:
	$(CC) $(CFLAGS) -c $<

$PROG: eatmem.o
	$(CC) $(LDFLAGS) -o $(PROG) *.o
