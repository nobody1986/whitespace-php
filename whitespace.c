#include <stdio.h>
#include <stdlib.h>
#include "list.h"
#include "stack.h"

#define PUSH  1
#define COPY  2
#define COPYN  3
#define EXCHANGE  4
#define DROP  5
#define SLIDEOFF  6
#define ADD  7
#define SUB  8
#define MUL  9
#define DIV  10
#define MOD  11
#define STORE  12
#define READ  13
#define MARK  14
#define CALL  15
#define JUMP  16
#define JUMPNULL  17
#define JUMPDE  18
#define ENDFUNC  19
#define ENDLE  20
#define OUTCHAR  21
#define OUTNUM  22
#define INCHAR  23
#define INNUM  24

int eval(char *content, int size, Stack *stack, List *heap, int func_call, int index) {
    int i = index;
    int *c = (int *) content;
    int opcode = 0;
    int *data_p = NULL;
    int data = 0;
    int *data_pp = NULL;
    Stack *tmp = (Stack *) initStack();
    int n = 0;
    int size_int = size / sizeof (int);
    while (i < size_int) {
        opcode = *(c + i);
//        printf("opcode: %d\n",opcode);
        switch (opcode) {
            case PUSH:
                i += 1;
                data_p = (int *) malloc(sizeof (int));
                *data_p = *(c + i);
                push(stack, data_p);
                break;
            case COPYN:
                i += 1;
                data = *(c + i);
                for (n = 0; n < data; ++n) {
                    data_p = (int *) pop(stack);
                    push(tmp, data_p);
                }
                data_pp = (int *) pop(tmp);
                push(stack, data_pp);
                while (data_p = pop(tmp)) {
                    push(stack, data_p);
                }
                push(stack, data_pp);
                break;
            case SLIDEOFF:
                i += 1;
                data = *(c + i);
//                for (n = 0; n < data; ++n) {
//                    data_p = (int *) pop(stack);
//                    push(tmp, data_p);
//                }
//                data_pp = (int *) pop(tmp);
//                while (data_p = pop(tmp)) {
//                    push(stack, data_p);
//                }
                delN(stack,data);
                break;
            case MARK:
                i += 1;
                data = *(c + i);
//                printf("offset: %d\n",data);
                break;
            case CALL:
                i += 1;
                data = *(c + i);
//                printf("offset: %d\n",data);
                eval(content, size, stack, heap, 1, data);
                break;
            case JUMP:
                i += 1;
                data = *(c + i);
                i = data;
                continue;
                break;
            case JUMPNULL:
                i += 1;
                data = *(c + i);
                data_p = (int *) pop(stack);
                if (*data_p == 0) {
                    i = data;
                    continue;
                }
                break;
            case JUMPDE:
                i += 1;
                data = *(c + i);
                data_p = (int *) pop(stack);
                if (*data_p < 0) {
                    i = data;
                    continue;
                }
                break;
            case COPY:
                data_p = (int *)getTop(stack);
                push(stack,data_p);
                break;
            case EXCHANGE:
//                data_p = (int *)pop(stack);
//                data_pp = (int *)pop(stack);
//                push(stack,data_p);
//                push(stack,data_pp);
                exchangeTop(stack);
                break;
            case DROP:
                (int *)pop(stack);
                break;
            case ADD:
                data_p = (int *)pop(stack);
                data_pp = (int *)pop(stack);
                data = *data_pp + *data_p;
                data_p = (int *) malloc(sizeof (int));
                *data_p = data;
                push(stack,data_p);
                break;
            case SUB:
                data_p = (int *)pop(stack);
                data_pp = (int *)pop(stack);
                data = *data_pp  - *data_p;
                data_p = (int *) malloc(sizeof (int));
                *data_p = data;
                push(stack,data_p);
                break;
            case MUL:
                data_p = (int *)pop(stack);
                data_pp = (int *)pop(stack);
                data = *data_pp * *data_p;
                data_p = (int *) malloc(sizeof (int));
                *data_p = data;
                push(stack,data_p);
                break;
            case DIV:
                data_p = (int *)pop(stack);
                data_pp = (int *)pop(stack);
                data = *data_pp / *data_p;
                data_p = (int *) malloc(sizeof (int));
                *data_p = data;
                push(stack,data_p);
                break;
            case MOD:
                data_p = (int *)pop(stack);
                data_pp = (int *)pop(stack);
                data = *data_pp % *data_p;
                data_p = (int *) malloc(sizeof (int));
                *data_p = data;
                push(stack,data_p);
                break;
            case STORE:
                data_p = (int *)pop(stack);
                data_pp = (int *)pop(stack);
                insertToList(heap,data_p,*data_pp);
                break;
            case READ:
                data_p = (int *)pop(stack);
                data_pp = (int *)getItem(heap,*data_p);
                push(stack,data_pp);
                break;
            case ENDFUNC:
                if(func_call == 1){
                    return 0;
                }
                break;
            case ENDLE:
                exit(0);
                break;
            case OUTCHAR:
                printf("%c",*((char *)pop(stack)));
                break;
            case OUTNUM:
                printf("%d",*((int *)pop(stack)));
                break;
            case INCHAR:
                data_p = (int *) malloc(sizeof (int));
                *data_p = (int)getchar();
                data_pp = (int *) pop(stack);
                insertToList(heap,data_p,*data_pp);
//                printf("input: %d\n",*data_p);
//                push(stack,data_p);
                break;
            case INNUM:
                data_p = (int *) malloc(sizeof (int));
                scanf("%d",data_p);
                data_pp = (int *) pop(stack);
                insertToList(heap,data_p,*data_pp);
//                push(stack,data_p);
                break;
        }
        i += 1;
    }
    return 0;
}

int main(int argc, char** argv) {
    FILE * fp = fopen(argv[1], "r");
    fseek(fp, 0, SEEK_END);
    int filesize = ftell(fp) + 1;
    char *file_content = (char*) malloc((filesize + 1) * sizeof (char));
    memset(file_content, 0, (filesize + 1) * sizeof (char));
    rewind(fp);
    fread(file_content, sizeof (char), filesize, fp);


    Stack *stack = (Stack *) initStack();
    List *heap = (List *) initList();

    eval(file_content, filesize, stack, heap, 0, 0);

    return 0;
}
