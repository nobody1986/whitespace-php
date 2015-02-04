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