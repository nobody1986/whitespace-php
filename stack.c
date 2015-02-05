#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <stdlib.h>
#include "stack.h"

Stack *initStack() {
    Stack *stack = malloc(sizeof (Stack));
    stack->cp = 0;
    stack->size = STACK_INI_SIZE;
    stack->block = 0;
    stack->pool = (SPool *) malloc(sizeof (SPool));
    stack->pool->pool = malloc(STACK_INI_SIZE * sizeof (void *));
    stack->pool->next = NULL;
    return stack;
}

void releaseStack(Stack *stack) {
    SPool *tmp = stack->pool;
    SPool *tmp_tmp = NULL;
    while (tmp != NULL) {
        tmp_tmp = tmp;
        tmp = tmp->next;
        free(tmp_tmp->pool);
        free(tmp_tmp);
    }
    free(stack);
}

void push(Stack *stack, void *p) {
    SPool *tmp = stack->pool;
    int cp = 0;
    if (stack->cp >= stack->size) {
        while (tmp->next != NULL) {
            tmp = tmp->next;
        }
        tmp->next = (SPool *) malloc(sizeof (SPool));
        tmp->next->next = NULL;
        tmp->next->pool = malloc(sizeof (void *) * STACK_STEP);
        stack->size += STACK_STEP;
        ++stack->block;
        *(tmp->next->pool) = p;
        ++stack->cp;
    } else {
        while (tmp->next != NULL) {
            tmp = tmp->next;
        }
        if (stack->cp < STACK_INI_SIZE) {
            cp = stack->cp;
        } else {
            cp = (stack->cp - STACK_INI_SIZE) % STACK_STEP;
        }
        *(tmp->pool + cp) = p;
        ++stack->cp;
    }
}

void *pop(Stack *stack) {
    SPool *tmp = stack->pool;
    int cp = 0;
    void *ret;
    if (stack->cp == 0) {
        return NULL;
    }
    while (tmp->next != NULL) {
        tmp = tmp->next;
    }
    if (stack->cp <= STACK_INI_SIZE) {
        cp = stack->cp - 1;
    } else {
        cp = (stack->cp - STACK_INI_SIZE) % STACK_STEP - 1;
        if (cp == -1) {
            cp = STACK_STEP - 1;
        }
    }
    ret = *(tmp->pool + cp);
    --stack->cp;
    return ret;
}

void *getTop(Stack *stack) {
    SPool *tmp = stack->pool;
    int cp = 0;
    void *ret;
    if (stack->cp == 0) {
        return NULL;
    }
    while (tmp->next != NULL) {
        tmp = tmp->next;
    }
    if (stack->cp <= STACK_INI_SIZE) {
        cp = stack->cp - 1;
    } else {
        cp = (stack->cp - STACK_INI_SIZE) % STACK_STEP - 1;
        if (cp == -1) {
            cp = STACK_STEP - 1;
        }
    }
    ret = *(tmp->pool + cp);
    return ret;
}

int stackSize(Stack *stack) {
    return stack->cp;
}

void *delN(Stack *stack, int n) {
    SPool *tmp = stack->pool;
    int cp = 0;
    void *ret;
    int index = stack->cp - n - 1;
    int position = 0;
    int i = 0,max = 0;
    void **prev = NULL;
    if (stack->cp <= n) {
        return NULL;
    }
    if (index <= STACK_INI_SIZE) {
        cp = index;
        position = 0;
        max = STACK_INI_SIZE;
    } else {
        position = (index - STACK_INI_SIZE) / STACK_STEP ;
        cp = (index - STACK_INI_SIZE) % STACK_STEP  ;
        max = STACK_STEP;
    }
    while (tmp != NULL) {
        if(position == 0){
            ret = *(tmp->pool + cp);
            for(i=cp;i<max-1;++i){
                *(tmp->pool + cp) = *(tmp->pool + cp + 1);
            }
            prev = tmp->pool + cp;
        }
        if(position < 0){
            *prev = *(tmp->pool);
            for(i=0;i<STACK_STEP-1;++i){
                *(tmp->pool + cp) = *(tmp->pool + cp + 1);
            }
            prev = tmp->pool + cp;
        }
        tmp = tmp->next;
        --position;
    }
    
    --stack->cp;
    return ret;
}

int exchangeTop(Stack *stack) {
    return exchange(stack,0,1);
}

int exchange(Stack *stack, int from, int to) {
    SPool *tmp = stack->pool;
    int cp = 0;
    int from_index = stack->cp - from - 1;
    int to_index = stack->cp - to - 1;
    int position = 0;
    int i = 0;
    void **prev = NULL;
    void *t = NULL;
    if (stack->cp <= from || stack->cp <= to) {
        return -1;
    }
//    printf("from:%d ,to: %d,cp:%d\n",from,to,stack->cp);
    if (from_index <= STACK_INI_SIZE) {
        cp = from_index;
        position = 0;
    } else {
        position = (from_index - STACK_INI_SIZE) / STACK_STEP ;
        cp = (from_index - STACK_INI_SIZE) % STACK_STEP;
    }
//    printf("position:%d ,cp:%d\n",position,cp);
    while (tmp != NULL) {
        if(position == 0){
            prev = tmp->pool + cp;
            break;
        }
        tmp = tmp->next;
        --position;
    }
    if (to_index <= STACK_INI_SIZE) {
        cp = to_index;
        position = 0;
    } else {
        position = (to_index - STACK_INI_SIZE) / STACK_STEP ;
        cp = (to_index - STACK_INI_SIZE) % STACK_STEP;
    }
//    printf("prv:%p \n",*prev);
    tmp = stack->pool;
//    printf("position:%d ,cp:%d\n",position,cp);
    while (tmp != NULL) {
        if(position == 0){
//            printf("prv:%p \n",*prev);
            t = *(tmp->pool + cp);
//            printf("cur:%p \n",t);
            *(tmp->pool + cp) = *prev;
            *prev = t;
            break;
        }
        tmp = tmp->next;
        --position;
    }
    
    return 0;
}